<?php

namespace App\Http\Controllers;

use App\Models\InventoryBatch;
use App\Models\OwnerSetting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Reports module: sales, forecast accuracy, inventory turnover, spoilage and profit & loss.
 *
 * Every report works on a date range in the user's business time zone (Settings > Language & Region),
 * so "November 1-11" means the business days, not UTC days. Ranges come from either
 * ?period=YYYY-MM | last_30 or an explicit ?start_date=&end_date=.
 *
 * Stock write-offs share the sales tables: sales_transactions.type = sale | spoilage | wastage | return |
 * transfer | sample | other | adjustment (see InventoryController::stockOut / adjustStock).
 */
class ReportsController extends Controller
{
    /** Write-off types that count as spoilage. */
    private const SPOILAGE_TYPES = ['spoilage', 'wastage'];

    /** Spoilage prediction levels that mean "act now". */
    private const HIGH_RISK_LEVELS = ['High Risk', 'Critical'];

    public function page()
    {
        $tz = $this->timezone();
        $now = Carbon::now($tz);

        $periods = [['value' => $now->format('Y-m'), 'label' => $now->format('F Y')]];
        for ($i = 1; $i <= 5; $i++) {
            $m = $now->copy()->startOfMonth()->subMonthsNoOverflow($i);
            $periods[] = ['value' => $m->format('Y-m'), 'label' => $m->format('F Y')];
        }
        $periods[] = ['value' => 'last_30', 'label' => 'Last 30 days'];

        return view('pages.reports', [
            'periods' => $periods,
            'defaultPeriod' => $periods[0]['value'],
        ]);
    }

    // ------------------------------------------------------------------ sales

    public function salesReport(Request $request)
    {
        $c = $this->context($request);

        $current = $this->salesTotals($c['from_utc'], $c['to_utc']);
        $previous = $this->salesTotals($c['prev_from_utc'], $c['prev_to_utc']);

        $days = $c['days'];
        $prevDays = $c['days'];
        $avgDaily = $days > 0 ? $current['revenue'] / $days : 0;
        $prevAvgDaily = $prevDays > 0 ? $previous['revenue'] / $prevDays : 0;

        $itemsBase = fn (string $from, string $to) => DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_items as i', 'i.id', '=', 'si.inventory_item_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$from, $to]);

        $prevRevenueByItem = $itemsBase($c['prev_from_utc'], $c['prev_to_utc'])
            ->selectRaw('si.inventory_item_id as item_id, SUM(si.total_amount) as revenue')
            ->groupBy('si.inventory_item_id')->pluck('revenue', 'item_id');

        $rows = $itemsBase($c['from_utc'], $c['to_utc'])
            ->selectRaw('si.inventory_item_id as item_id, i.name, i.unit, SUM(si.quantity) as qty, SUM(si.total_amount) as revenue, COUNT(DISTINCT si.sale_transaction_id) as tx')
            ->groupBy('si.inventory_item_id', 'i.name', 'i.unit')
            ->orderByDesc('revenue')
            ->get();

        $itemsRevenue = (float) $rows->sum('revenue');
        $topProducts = $rows->map(function ($r) use ($itemsRevenue, $prevRevenueByItem) {
            $prev = (float) ($prevRevenueByItem[$r->item_id] ?? 0);
            $change = $this->change((float) $r->revenue, $prev);

            return [
                'product_name' => $r->name,
                'unit' => $r->unit,
                'quantity_sold' => round((float) $r->qty, 2),
                'total_sales' => round((float) $r->revenue, 2),
                'transaction_count' => (int) $r->tx,
                'average_price' => $r->qty > 0 ? round($r->revenue / $r->qty, 2) : 0,
                'share_percent' => $itemsRevenue > 0 ? round($r->revenue / $itemsRevenue * 100, 1) : 0,
                'change_percent' => $change,
                'trend' => $change === null ? ($r->revenue > 0 ? 'up' : 'flat') : ($change > 5 ? 'up' : ($change < -5 ? 'down' : 'flat')),
            ];
        })->values();

        $byDate = DB::table('sales_transactions as st')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->selectRaw("{$c['local_date']} as d, SUM(st.total_amount) as total, COUNT(*) as tx")
            ->groupBy('d')->get()->keyBy('d');

        $salesByDate = [];
        for ($d = $c['from']->copy(); $d->lte($c['to']); $d->addDay()) {
            $key = $d->toDateString();
            $salesByDate[] = [
                'date' => $key,
                'label' => $d->format('M j'),
                'total_sales' => round((float) ($byDate[$key]->total ?? 0), 2),
                'transaction_count' => (int) ($byDate[$key]->tx ?? 0),
            ];
        }

        $paymentMethods = DB::table('sales_transactions as st')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->selectRaw('payment_method, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('payment_method')->get();

        $best = $topProducts->first();

        return response()->json([
            'period' => $this->periodPayload($c),
            'summary' => [
                'total_sales' => round($current['revenue'], 2),
                'total_transactions' => $current['count'],
                'average_transaction' => $current['count'] > 0 ? round($current['revenue'] / $current['count'], 2) : 0,
                'total_items_sold' => round((float) $rows->sum('qty'), 2),
                'avg_daily_sales' => round($avgDaily, 2),
                'best_product' => $best ? ['name' => $best['product_name'], 'share_percent' => $best['share_percent']] : null,
            ],
            'change' => [
                'revenue' => $this->change($current['revenue'], $previous['revenue']),
                'transactions' => $this->change($current['count'], $previous['count']),
                'avg_daily' => $this->change($avgDaily, $prevAvgDaily),
            ],
            'sales_by_date' => $salesByDate,
            'top_products' => $topProducts,
            'payment_methods' => $paymentMethods,
        ]);
    }

    /** Printable sales report (opened in a new tab and sent to the printer / saved as PDF). */
    public function exportSalesPDF(Request $request)
    {
        $c = $this->context($request);
        $totals = $this->salesTotals($c['from_utc'], $c['to_utc']);

        $topProducts = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_items as i', 'i.id', '=', 'si.inventory_item_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->selectRaw('i.name, i.unit, SUM(si.quantity) as quantity, SUM(si.total_amount) as revenue')
            ->groupBy('si.inventory_item_id', 'i.name', 'i.unit')
            ->orderByDesc('revenue')->limit(25)->get();

        $html = view('reports.sales-pdf', [
            'startDate' => $c['from'],
            'endDate' => $c['to'],
            'generatedAt' => Carbon::now($c['tz']),
            'totalSales' => $totals['revenue'],
            'totalTransactions' => $totals['count'],
            'topProducts' => $topProducts,
        ])->render();

        return response($html)->header('Content-Type', 'text/html');
    }

    // --------------------------------------------------------------- forecast

    /**
     * Forecast vs actual: ai_forecasts keeps every past forecast (only future days are replaced on each run),
     * so finished days in the range can be scored against what was really sold.
     */
    public function forecastReport(Request $request)
    {
        $c = $this->context($request);
        $today = Carbon::now($c['tz'])->toDateString();
        $lastDay = min($c['to']->toDateString(), Carbon::now($c['tz'])->subDay()->toDateString());
        $firstDay = $c['from']->toDateString();

        $forecasts = $lastDay >= $firstDay
            ? DB::table('ai_forecasts')->whereBetween('forecast_date', [$firstDay, $lastDay])
                ->selectRaw('inventory_item_id as item_id, fruit_name, forecast_date as d, SUM(predicted_revenue) as predicted')
                ->groupBy('inventory_item_id', 'fruit_name', 'forecast_date')->get()
            : collect();

        $actuals = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->selectRaw("si.inventory_item_id as item_id, {$c['local_date']} as d, SUM(si.total_amount) as revenue")
            ->groupBy('si.inventory_item_id', 'd')->get()
            ->groupBy('item_id')->map(fn ($g) => $g->pluck('revenue', 'd'));

        $accuracy = fn (float $forecast, float $actual) => $actual > 0 ? max(0.0, round(100 - abs($forecast - $actual) / $actual * 100, 1)) : null;

        $fruits = $forecasts->groupBy('item_id')->map(function ($rows) use ($actuals, $accuracy) {
            $itemActuals = $actuals[$rows->first()->item_id] ?? collect();
            $forecast = (float) $rows->sum('predicted');
            $actual = (float) $rows->sum(fn ($r) => (float) ($itemActuals[$r->d] ?? 0));

            return [
                'fruit' => $rows->first()->fruit_name,
                'forecast' => round($forecast, 2),
                'actual' => round($actual, 2),
                'error_percent' => $actual > 0 ? round(($forecast - $actual) / $actual * 100, 1) : null,
                'accuracy' => $accuracy($forecast, $actual),
                'days' => $rows->count(),
            ];
        })->sortByDesc('actual')->values();

        // Weekly accuracy for the three best-selling fruits
        $weeks = max(1, (int) ceil($c['days'] / 7));
        $labels = [];
        for ($w = 0; $w < $weeks; $w++) {
            $labels[] = 'Week ' . ($w + 1);
        }
        $topIds = $fruits->take(3)->map(fn ($f) => $forecasts->firstWhere('fruit_name', $f['fruit'])->item_id);
        $series = [];
        foreach ($topIds as $itemId) {
            $rows = $forecasts->where('item_id', $itemId);
            $itemActuals = $actuals[$itemId] ?? collect();
            $data = [];
            for ($w = 0; $w < $weeks; $w++) {
                $wFrom = $c['from']->copy()->addDays($w * 7)->toDateString();
                $wTo = $c['from']->copy()->addDays($w * 7 + 6)->toDateString();
                $bucket = $rows->filter(fn ($r) => $r->d >= $wFrom && $r->d <= $wTo);
                $f = (float) $bucket->sum('predicted');
                $a = (float) $bucket->sum(fn ($r) => (float) ($itemActuals[$r->d] ?? 0));
                $data[] = $accuracy($f, $a);
            }
            $series[] = ['fruit' => $rows->first()->fruit_name, 'data' => $data];
        }

        $scored = $fruits->filter(fn ($f) => $f['accuracy'] !== null);
        $totalActual = (float) $scored->sum('actual');
        $overall = $totalActual > 0 ? $accuracy((float) $scored->sum('forecast'), $totalActual) : null;

        // Model health comes from the latest SARIMAX training run
        $run = DB::table('ml_model_runs')->where('model', 'SARIMAX')->orderByDesc('id')->first();
        $metrics = $run ? (json_decode($run->metrics ?? 'null', true) ?: []) : [];
        $mapes = collect($metrics['per_fruit'] ?? [])->pluck('mape')->filter(fn ($v) => $v !== null);

        return response()->json([
            'period' => $this->periodPayload($c),
            'summary' => [
                'overall_accuracy' => $overall,
                'model_accuracy' => $metrics['overall_accuracy'] ?? null,
                'mape' => $mapes->isNotEmpty() ? round($mapes->avg(), 1) : null,
                'model' => $run ? 'SARIMAX' : null,
                'model_status' => $run->status ?? null,
                'trained_at' => $run ? Carbon::parse($run->created_at)->setTimezone($c['tz'])->format('M j, Y') : null,
                'training_rows' => $run->training_rows ?? null,
                'fruits_modeled' => $metrics['fruits_modeled'] ?? null,
                'scored_days' => $forecasts->pluck('d')->unique()->count(),
            ],
            'fruits' => $fruits,
            'weekly' => ['labels' => $labels, 'series' => $series],
        ]);
    }

    // -------------------------------------------------------------- inventory

    /**
     * Inventory turnover for the range.
     * closing = stock now - received after the range + net stock-outs after the range
     * opening = closing - received in the range + net stock-outs in the range
     * (net stock-outs = sales, spoilage, other write-offs and negative adjustments minus positive adjustments)
     */
    public function inventoryReport(Request $request)
    {
        $c = $this->context($request);
        $fromDate = $c['from']->toDateString();
        $toDate = $c['to']->toDateString();

        $received = fn (string $op, string $a, ?string $b = null) => DB::table('inventory_batches')
            ->when($op === 'between', fn ($q) => $q->whereBetween('received_date', [$a, $b]), fn ($q) => $q->where('received_date', '>', $a))
            ->selectRaw('inventory_item_id as item_id, SUM(COALESCE(initial_quantity, quantity)) as qty')
            ->groupBy('inventory_item_id')->pluck('qty', 'item_id');

        $receivedIn = $received('between', $fromDate, $toDate);
        $receivedAfter = $received('after', $toDate);

        $net = "CASE WHEN st.type = 'adjustment' THEN -si.quantity ELSE si.quantity END";
        $moves = fn (callable $where) => $where(DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->where('st.status', 'completed'))
            ->selectRaw("si.inventory_item_id as item_id, SUM({$net}) as qty")
            ->groupBy('si.inventory_item_id')->pluck('qty', 'item_id');

        $outIn = $moves(fn ($q) => $q->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']]));
        $outAfter = $moves(fn ($q) => $q->where('st.created_at', '>', $c['to_utc']));
        $soldIn = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->selectRaw('si.inventory_item_id as item_id, SUM(si.quantity) as qty')
            ->groupBy('si.inventory_item_id')->pluck('qty', 'item_id');

        $batchValue = DB::table('inventory_batches')->where('status', 'available')
            ->selectRaw('inventory_item_id as item_id, SUM(quantity * price_per_unit) as value, COUNT(*) as batches')
            ->groupBy('inventory_item_id')->get()->keyBy('item_id');

        $items = DB::table('inventory_items')->orderBy('name')->get();

        $report = $items->map(function ($item) use ($receivedIn, $receivedAfter, $outIn, $outAfter, $soldIn, $batchValue) {
            $now = (float) $item->stock_quantity;
            $in = (float) ($receivedIn[$item->id] ?? 0);
            $closing = max(0, $now - (float) ($receivedAfter[$item->id] ?? 0) + (float) ($outAfter[$item->id] ?? 0));
            $opening = max(0, $closing - $in + (float) ($outIn[$item->id] ?? 0));
            $sold = (float) ($soldIn[$item->id] ?? 0);
            $average = ($opening + $closing) / 2;

            return [
                'product_name' => $item->name,
                'category' => $item->category,
                'unit' => $item->unit,
                'opening_stock' => round($opening, 2),
                'received' => round($in, 2),
                'sold' => round($sold, 2),
                'closing_stock' => round($closing, 2),
                'turnover' => $average > 0 ? round($sold / $average, 1) : null,
                'total_stock' => round($now, 2),
                'total_value' => round((float) ($batchValue[$item->id]->value ?? 0), 2),
                'batch_count' => (int) ($batchValue[$item->id]->batches ?? 0),
                'reorder_level' => (float) $item->reorder_level,
                'stock_status' => $now <= 0 ? 'out_of_stock' : ($now <= (float) $item->reorder_level ? 'low_stock' : 'in_stock'),
            ];
        });

        // Only products with any stock or movement in the period
        $rows = $report->filter(fn ($r) => $r['opening_stock'] > 0 || $r['received'] > 0 || $r['sold'] > 0 || $r['closing_stock'] > 0)
            ->sortByDesc('sold')->values();
        $turnovers = $rows->pluck('turnover')->filter(fn ($v) => $v !== null);

        return response()->json([
            'period' => $this->periodPayload($c),
            'summary' => [
                'total_products' => $items->count(),
                'total_stock_quantity' => round((float) $report->sum('total_stock'), 2),
                'total_inventory_value' => round((float) $report->sum('total_value'), 2),
                'low_stock_items' => $report->whereIn('stock_status', ['low_stock', 'out_of_stock'])->count(),
                'total_received' => round((float) $rows->sum('received'), 2),
                'total_sold' => round((float) $rows->sum('sold'), 2),
                'average_turnover' => $turnovers->isNotEmpty() ? round($turnovers->avg(), 1) : null,
            ],
            'items' => $rows,
        ]);
    }

    // --------------------------------------------------------------- spoilage

    public function spoilageReport(Request $request)
    {
        $c = $this->context($request);

        $writeOffs = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_items as i', 'i.id', '=', 'si.inventory_item_id')
            ->leftJoin('inventory_batches as b', 'b.id', '=', 'si.inventory_batch_id')
            ->where('st.status', 'completed')->whereIn('st.type', self::SPOILAGE_TYPES)
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->selectRaw('si.inventory_item_id as item_id, i.name, i.unit, st.type, st.notes, si.quantity, COALESCE(b.price_per_unit, si.unit_price) as unit_value')
            ->get();

        $soldQty = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->selectRaw('si.inventory_item_id as item_id, SUM(si.quantity) as qty')
            ->groupBy('si.inventory_item_id')->pluck('qty', 'item_id');

        $fruits = $writeOffs->groupBy('item_id')->map(function ($rows, $itemId) use ($soldQty) {
            $qty = (float) $rows->sum('quantity');
            $sold = (float) ($soldQty[$itemId] ?? 0);
            $cause = $rows->map(fn ($r) => trim((string) $r->notes) !== '' && $r->notes !== 'Stock out transaction' ? trim($r->notes) : ucfirst($r->type))
                ->countBy()->sortDesc()->keys()->first();

            return [
                'fruit' => $rows->first()->name,
                'unit' => $rows->first()->unit,
                'quantity' => round($qty, 2),
                'loss_value' => round((float) $rows->sum(fn ($r) => $r->quantity * $r->unit_value), 2),
                'spoilage_percent' => ($qty + $sold) > 0 ? round($qty / ($qty + $sold) * 100, 1) : 0,
                'cause' => $cause,
            ];
        })->sortByDesc('loss_value')->values();

        $totalQty = (float) $fruits->sum('quantity');
        $totalSold = (float) $soldQty->sum();

        // Revenue from batches that had already been flagged High Risk / Critical when they were sold
        $local = $c['local_date'];
        $prevented = (float) DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->whereExists(function ($q) use ($local) {
                $q->select(DB::raw(1))->from('spoilage_predictions as p')
                    ->whereColumn('p.inventory_batch_id', 'si.inventory_batch_id')
                    ->whereIn('p.risk_level', self::HIGH_RISK_LEVELS)
                    ->where('p.horizon_day', 0)
                    ->whereRaw("p.prediction_date <= {$local}");
            })->sum('si.total_amount');

        return response()->json([
            'period' => $this->periodPayload($c),
            'summary' => [
                'total_spoilage_qty' => round($totalQty, 2),
                'spoilage_rate' => ($totalQty + $totalSold) > 0 ? round($totalQty / ($totalQty + $totalSold) * 100, 1) : 0,
                'financial_loss' => round((float) $fruits->sum('loss_value'), 2),
                'prevented_loss' => round($prevented, 2),
                'unit' => 'kg',
            ],
            'fruits' => $fruits,
        ]);
    }

    /** Batches about to expire (used by other modules / API consumers). */
    public function expiryReport(Request $request)
    {
        $days = (int) $request->input('days', 30);

        $expiringBatches = InventoryBatch::with('inventoryItem')
            ->where('status', 'available')
            ->where('quantity', '>', 0)
            ->whereBetween('expiry_date', [now()->toDateString(), now()->addDays($days)->toDateString()])
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->map(function ($batch) {
                $remaining = $batch->remaining_shelf_life;

                return [
                    'product_name' => $batch->inventoryItem->name,
                    'batch_code' => $batch->batch_code,
                    'quantity' => $batch->quantity,
                    'unit' => $batch->inventoryItem->unit,
                    'expiry_date' => $batch->expiry_date->format('Y-m-d'),
                    'days_remaining' => $remaining,
                    'value_at_risk' => $batch->quantity * $batch->price_per_unit,
                    'urgency' => $remaining <= 3 ? 'critical' : ($remaining <= 7 ? 'high' : ($remaining <= 14 ? 'medium' : 'low')),
                    'supplier' => $batch->supplier,
                ];
            });

        return response()->json([
            'summary' => [
                'total_batches_expiring' => $expiringBatches->count(),
                'total_value_at_risk' => round($expiringBatches->sum('value_at_risk'), 2),
                'critical_items' => $expiringBatches->where('urgency', 'critical')->count(),
                'high_priority_items' => $expiringBatches->where('urgency', 'high')->count(),
            ],
            'expiring_batches' => $expiringBatches,
        ]);
    }

    // ----------------------------------------------------------------- profit

    /**
     * Profit & loss. Cost of goods = quantity x the cost of the batch it was sold from (inventory_batches.price_per_unit,
     * the same basis the dashboard uses). Sales without a linked batch (e.g. imported history) have no known cost, so
     * "cost_coverage" tells how much of the revenue the cost figure actually covers.
     */
    public function profitLossReport(Request $request)
    {
        $c = $this->context($request);

        $revenue = (float) DB::table('sales_transactions as st')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])->sum('st.total_amount');

        $lines = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_items as i', 'i.id', '=', 'si.inventory_item_id')
            ->leftJoin('inventory_batches as b', 'b.id', '=', 'si.inventory_batch_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$c['from_utc'], $c['to_utc']])
            ->selectRaw('si.inventory_item_id as item_id, i.name, SUM(si.total_amount) as revenue, '
                . 'SUM(CASE WHEN b.id IS NULL THEN 0 ELSE si.quantity * b.price_per_unit END) as cost, '
                . 'SUM(CASE WHEN b.id IS NULL THEN 0 ELSE si.total_amount END) as costed_revenue')
            ->groupBy('si.inventory_item_id', 'i.name')->get();

        $cogs = (float) $lines->sum('cost');
        $itemsRevenue = (float) $lines->sum('revenue');
        $coverage = $itemsRevenue > 0 ? round($lines->sum('costed_revenue') / $itemsRevenue * 100, 1) : null;
        $grossProfit = $revenue - $cogs;

        // Six months ending with the selected range
        $monthStart = $c['to']->copy()->startOfMonth()->subMonthsNoOverflow(5);
        $monthStartUtc = $monthStart->copy()->setTimezone(config('app.timezone'))->format('Y-m-d H:i:s');
        $month = "DATE_FORMAT(CONVERT_TZ(st.created_at, '{$c['app_offset']}', '{$c['biz_offset']}'), '%Y-%m')";

        $monthRevenue = DB::table('sales_transactions as st')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$monthStartUtc, $c['to_utc']])
            ->selectRaw("{$month} as m, SUM(st.total_amount) as revenue")->groupBy('m')->pluck('revenue', 'm');
        $monthCost = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_batches as b', 'b.id', '=', 'si.inventory_batch_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereBetween('st.created_at', [$monthStartUtc, $c['to_utc']])
            ->selectRaw("{$month} as m, SUM(si.quantity * b.price_per_unit) as cost")->groupBy('m')->pluck('cost', 'm');

        $monthly = [];
        for ($i = 0; $i < 6; $i++) {
            $m = $monthStart->copy()->addMonthsNoOverflow($i);
            $r = (float) ($monthRevenue[$m->format('Y-m')] ?? 0);
            $k = (float) ($monthCost[$m->format('Y-m')] ?? 0);
            $monthly[] = ['label' => $m->format('M'), 'revenue' => round($r, 2), 'cogs' => round($k, 2), 'profit' => round($r - $k, 2)];
        }

        $productProfits = $lines->map(function ($l) {
            $profit = $l->revenue - $l->cost;

            return [
                'product_name' => $l->name,
                'revenue' => round((float) $l->revenue, 2),
                'cost' => round((float) $l->cost, 2),
                'profit' => round((float) $profit, 2),
                'margin_percentage' => $l->revenue > 0 ? round($profit / $l->revenue * 100, 2) : 0,
            ];
        })->sortByDesc('profit')->values();

        return response()->json([
            'period' => $this->periodPayload($c),
            'summary' => [
                'total_revenue' => round($revenue, 2),
                'cost_of_goods_sold' => round($cogs, 2),
                'gross_profit' => round($grossProfit, 2),
                'gross_margin_percentage' => $revenue > 0 ? round($grossProfit / $revenue * 100, 2) : 0,
                'cost_coverage' => $coverage,
            ],
            'monthly' => $monthly,
            'product_profits' => $productProfits,
        ]);
    }

    // ---------------------------------------------------------------- helpers

    private function timezone(): string
    {
        return OwnerSetting::forUser(Auth::id())->timezone ?: config('services.openweather.timezone', 'Asia/Manila');
    }

    /**
     * Resolve the requested range into business-day bounds (local) and the matching UTC bounds used for queries,
     * plus the immediately preceding range of the same length for comparisons.
     */
    private function context(Request $request): array
    {
        $tz = $this->timezone();
        $now = Carbon::now($tz);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $request->validate([
                'start_date' => 'date',
                'end_date' => 'date|after_or_equal:start_date',
            ]);
            $from = Carbon::parse($request->input('start_date'), $tz)->startOfDay();
            $to = Carbon::parse($request->input('end_date'), $tz)->endOfDay();
        } elseif (preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', (string) $request->input('period'))) {
            $from = Carbon::createFromFormat('Y-m-d', $request->input('period') . '-01', $tz)->startOfDay();
            $to = $from->copy()->endOfMonth();
        } elseif ($request->input('period') === 'last_30') {
            $to = $now->copy()->endOfDay();
            $from = $now->copy()->subDays(29)->startOfDay();
        } else {
            $from = $now->copy()->startOfMonth();
            $to = $now->copy()->endOfMonth();
        }

        if ($to->gt($now)) {
            $to = $now->copy()->endOfDay();          // nothing has happened in the future
        }
        if ($from->gt($to)) {
            $from = $to->copy()->startOfDay();
        }
        if ($from->diffInDays($to) > 366) {
            $from = $to->copy()->subDays(365)->startOfDay();
        }

        $days = (int) $from->diffInDays($to->copy()->startOfDay()) + 1;
        $prevTo = $from->copy()->subDay()->endOfDay();
        $prevFrom = $prevTo->copy()->subDays($days - 1)->startOfDay();

        $appTz = config('app.timezone');
        $utc = fn (Carbon $d) => $d->copy()->setTimezone($appTz)->format('Y-m-d H:i:s');
        $appOffset = Carbon::now($appTz)->format('P');
        $bizOffset = $now->format('P');

        return [
            'tz' => $tz,
            'from' => $from,
            'to' => $to,
            'days' => $days,
            'from_utc' => $utc($from),
            'to_utc' => $utc($to),
            'prev_from_utc' => $utc($prevFrom),
            'prev_to_utc' => $utc($prevTo),
            'app_offset' => $appOffset,
            'biz_offset' => $bizOffset,
            'local_date' => "DATE(CONVERT_TZ(st.created_at, '{$appOffset}', '{$bizOffset}'))",
        ];
    }

    private function periodPayload(array $c): array
    {
        $from = $c['from'];
        $to = $c['to'];
        $label = ($from->isSameMonth($to) && $from->isSameYear($to))
            ? $from->format('F j') . '–' . $to->format('j, Y')
            : $from->format('M j, Y') . ' – ' . $to->format('M j, Y');

        return [
            'start_date' => $from->toDateString(),
            'end_date' => $to->toDateString(),
            'days' => $c['days'],
            'label' => $label,
            'timezone' => $c['tz'],
        ];
    }

    private function salesTotals(string $fromUtc, string $toUtc): array
    {
        $row = DB::table('sales_transactions')
            ->where('status', 'completed')->where('type', 'sale')
            ->whereBetween('created_at', [$fromUtc, $toUtc])
            ->selectRaw('COALESCE(SUM(total_amount), 0) as revenue, COUNT(*) as cnt')->first();

        return ['revenue' => (float) $row->revenue, 'count' => (int) $row->cnt];
    }

    /** Percentage change from $previous to $current, or null when there is nothing to compare with. */
    private function change(float $current, float $previous): ?float
    {
        return $previous > 0 ? round(($current - $previous) / $previous * 100, 1) : null;
    }
}
