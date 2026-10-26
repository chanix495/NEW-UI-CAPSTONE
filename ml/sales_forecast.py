"""SARIMAX sales forecast.

For each fruit, models daily units sold as a seasonal ARIMA process (weekly
seasonality, s=7) with weather as exogenous regressors:

    sales_t = SARIMA(p,d,q)(P,D,Q,7) + b1*temperature_t + b2*humidity_t + b3*rainfall_t

The order is picked by AIC from a small candidate grid. Accuracy is measured on
a 14-day hold-out before the final model is refit on all history and used to
forecast the next HORIZON days with OpenWeather's forecast as future exog values.

Usage: python ml/sales_forecast.py [--horizon 7]
"""
import argparse
import warnings
from datetime import datetime

import numpy as np
import pandas as pd
from statsmodels.tools.sm_exceptions import ConvergenceWarning
from statsmodels.tsa.statespace.sarimax import SARIMAX

from common import (WEATHER_COLS, connect, emit, fail, load_weather, local_today, query_df,
                    save_run, to_local_date, weather_for)

warnings.simplefilter("ignore", ConvergenceWarning)
warnings.simplefilter("ignore", UserWarning)
warnings.simplefilter("ignore", RuntimeWarning)

MIN_HISTORY_DAYS = 28
MIN_SALES_DAYS = 10
HOLDOUT_DAYS = 14
MAX_HISTORY_DAYS = 365
ORDERS = [(1, 0, 1), (2, 0, 1), (1, 1, 1)]
SEASONAL_ORDERS = [(1, 0, 1, 7), (0, 1, 1, 7)]


def load_sales(conn):
    df = query_df(conn, """
        SELECT si.inventory_item_id, ii.name, ii.price_per_unit, st.created_at, si.quantity
        FROM sales_items si
        JOIN sales_transactions st ON st.id = si.sale_transaction_id
        JOIN inventory_items ii ON ii.id = si.inventory_item_id
        WHERE st.type = 'sale' AND st.status = 'completed' AND si.quantity > 0
    """)
    if df.empty:
        return df
    df["date"] = to_local_date(df["created_at"])
    return df


def scale_exog(train_exog, *others):
    mean = train_exog.mean()
    std = train_exog.std().replace(0, 1).fillna(1)
    return [(x - mean) / std for x in (train_exog, *others)]


def fit(y, exog, order, seasonal_order):
    model = SARIMAX(
        y, exog=exog, order=order, seasonal_order=seasonal_order,
        trend="c" if order[1] == 0 and seasonal_order[1] == 0 else "n",
        enforce_stationarity=False, enforce_invertibility=False,
    )
    return model.fit(disp=False, maxiter=200)


def select_order(y, exog):
    best = None
    for order in ORDERS:
        for seasonal in SEASONAL_ORDERS:
            try:
                res = fit(y, exog, order, seasonal)
            except Exception:
                continue
            if np.isfinite(res.aic) and (best is None or res.aic < best[0]):
                best = (res.aic, order, seasonal)
    if best is None:
        raise RuntimeError("no SARIMAX order converged")
    return best[1], best[2]


def evaluate(y, exog, order, seasonal):
    """Fit on all but the last HOLDOUT_DAYS and score the forecast against them."""
    y_train, y_test = y.iloc[:-HOLDOUT_DAYS], y.iloc[-HOLDOUT_DAYS:]
    x_train, x_test = scale_exog(exog.iloc[:-HOLDOUT_DAYS], exog.iloc[-HOLDOUT_DAYS:])
    res = fit(y_train, x_train, order, seasonal)
    pred = np.clip(res.get_forecast(HOLDOUT_DAYS, exog=x_test).predicted_mean.to_numpy(), 0, None)
    actual = y_test.to_numpy()

    err = actual - pred
    mae = float(np.mean(np.abs(err)))
    rmse = float(np.sqrt(np.mean(err ** 2)))
    # Weighted APE: robust when some days have zero sales (plain MAPE divides by zero)
    wape = float(np.sum(np.abs(err)) / max(np.sum(actual), 1e-9))
    nonzero = actual > 0
    mape = float(np.mean(np.abs(err[nonzero] / actual[nonzero])) * 100) if nonzero.any() else None
    accuracy = max(0.0, min(100.0, (1 - wape) * 100))
    return {"mae": round(mae, 3), "rmse": round(rmse, 3), "mape": None if mape is None else round(mape, 2),
            "wape": round(wape * 100, 2), "accuracy": round(accuracy, 2)}


def forecast_item(series, weather, future_dates):
    y = series.astype(float)
    exog = weather_for(weather, y.index)
    future_exog = weather_for(weather, future_dates)

    order, seasonal = select_order(y.iloc[:-HOLDOUT_DAYS], scale_exog(exog.iloc[:-HOLDOUT_DAYS])[0])
    metrics = evaluate(y, exog, order, seasonal)

    x_all, x_future = scale_exog(exog, future_exog)
    res = fit(y, x_all, order, seasonal)
    fc = res.get_forecast(len(future_dates), exog=x_future)
    mean = np.clip(fc.predicted_mean.to_numpy(), 0, None)
    ci = np.clip(fc.conf_int(alpha=0.05).to_numpy(), 0, None)

    coefs = {c: round(float(res.params.get(c, 0.0)), 4) for c in WEATHER_COLS}
    pvalues = {c: round(float(res.pvalues.get(c, 1.0)), 4) for c in WEATHER_COLS}
    metrics.update({"order": list(order), "seasonal_order": list(seasonal), "aic": round(float(res.aic), 2),
                    "weather_coefficients": coefs, "weather_pvalues": pvalues})
    return mean, ci[:, 0], ci[:, 1], future_exog, metrics


def baseline_item(series, weather, future_dates):
    """Too little history for SARIMAX: same-weekday average of recent sales."""
    recent = series.iloc[-28:]
    by_dow = recent.groupby(recent.index.dayofweek).mean()
    overall = recent.mean() if len(recent) else 0.0
    mean = np.array([by_dow.get(d.dayofweek, overall) for d in future_dates], dtype=float)
    spread = recent.std() if len(recent) > 1 else overall * 0.5
    spread = 0.0 if np.isnan(spread) else spread
    future_exog = weather_for(weather, future_dates) if not weather.empty else pd.DataFrame(index=future_dates, columns=WEATHER_COLS)
    return mean, np.clip(mean - 1.96 * spread, 0, None), mean + 1.96 * spread, future_exog


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--horizon", type=int, default=7)
    args = parser.parse_args()

    conn = connect()
    sales = load_sales(conn)
    if sales.empty:
        save_run(conn, "SARIMAX", "skipped", message="No completed sales to train on.")
        fail("No completed sales to train on.")

    weather = load_weather(conn)
    today = local_today()
    # Today is still in progress, so history ends yesterday and the forecast starts today
    history_end = today - pd.Timedelta(days=1)
    future_dates = pd.date_range(today, periods=args.horizon, freq="D")

    rows, per_fruit, skipped = [], {}, []
    for (item_id, name), grp in sales.groupby(["inventory_item_id", "name"]):
        price = float(grp["price_per_unit"].iloc[0])
        daily = grp.groupby("date")["quantity"].sum()
        start = max(daily.index.min(), history_end - pd.Timedelta(days=MAX_HISTORY_DAYS - 1))
        series = daily.reindex(pd.date_range(start, history_end, freq="D"), fill_value=0.0)

        use_sarimax = len(series) >= MIN_HISTORY_DAYS + HOLDOUT_DAYS and (series > 0).sum() >= MIN_SALES_DAYS
        model_name = "SARIMAX"
        try:
            if not use_sarimax:
                raise ValueError(f"only {len(series)} days / {(series > 0).sum()} sales days of history")
            mean, lo, hi, fut_exog, metrics = forecast_item(series, weather, future_dates)
            per_fruit[name] = metrics
            accuracy = metrics["accuracy"]
            note = f"SARIMAX{tuple(metrics['order'])}x{tuple(metrics['seasonal_order'])} AIC={metrics['aic']}"
        except Exception as exc:
            model_name = "Baseline"
            mean, lo, hi, fut_exog = baseline_item(series, weather, future_dates)
            accuracy = 0
            note = f"Weekday average (SARIMAX not used: {exc})"
            skipped.append({"fruit": name, "reason": str(exc)})

        for i, d in enumerate(future_dates):
            w = fut_exog.iloc[i] if len(fut_exog) else {}
            rows.append((
                int(item_id), name, d.date(), round(float(mean[i]), 2), round(float(lo[i]), 2), round(float(hi[i]), 2),
                round(float(mean[i]) * price, 2), accuracy,
                None if pd.isna(w.get("temperature")) else round(float(w["temperature"]), 2),
                None if pd.isna(w.get("humidity")) else round(float(w["humidity"]), 2),
                None if pd.isna(w.get("rainfall")) else round(float(w["rainfall"]), 2),
                model_name, note,
            ))

    now = datetime.now()
    cur = conn.cursor()
    cur.execute("DELETE FROM ai_forecasts WHERE forecast_date >= %s", (today.date(),))
    cur.executemany(
        "INSERT INTO ai_forecasts (inventory_item_id, fruit_name, forecast_date, predicted_sales_qty, lower_bound, "
        "upper_bound, predicted_revenue, forecast_accuracy, temperature, humidity, rainfall, model, notes, created_at, updated_at) "
        "VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)",
        [r + (now, now) for r in rows],
    )
    conn.commit()
    cur.close()

    accuracies = [m["accuracy"] for m in per_fruit.values()]
    summary = {
        "horizon_days": args.horizon,
        "fruits_modeled": len(per_fruit),
        "fruits_baseline": len(skipped),
        "overall_accuracy": round(float(np.mean(accuracies)), 2) if accuracies else None,
        "mean_mae": round(float(np.mean([m["mae"] for m in per_fruit.values()])), 3) if per_fruit else None,
        "mean_rmse": round(float(np.mean([m["rmse"] for m in per_fruit.values()])), 3) if per_fruit else None,
        "per_fruit": per_fruit,
        "baseline": skipped,
    }
    status = "success" if per_fruit else "skipped"
    save_run(conn, "SARIMAX", status, training_rows=len(sales), metrics=summary,
             message=None if per_fruit else "Not enough history for SARIMAX; used weekday averages.")
    conn.close()
    emit({"success": True, "model": "SARIMAX", **summary})


if __name__ == "__main__":
    try:
        main()
    except SystemExit:
        raise
    except Exception as exc:
        fail(f"{type(exc).__name__}: {exc}")
