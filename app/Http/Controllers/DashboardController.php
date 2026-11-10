<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use App\Models\SalesTransaction;
use App\Models\SalesItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        // Get real-time metrics
        $metrics = $this->getDashboardMetrics();
        
        return view('pages.dashboard', $metrics + ['dash' => $this->dashboardViewData()]);
    }

    /**
     * Get dashboard metrics.
     */
    public function getDashboardMetrics()
    {
        // Today's sales
        $todaySales = SalesTransaction::today()->completed()->sum('total_amount');
        
        // This week's sales
        $weekSales = SalesTransaction::thisWeek()->completed()->sum('total_amount');
        
        // This month's sales
        $monthSales = SalesTransaction::thisMonth()->completed()->sum('total_amount');
        
        // Calculate week vs last week growth
        $lastWeekSales = SalesTransaction::completed()
            ->whereBetween('created_at', [
                now()->subWeek()->startOfWeek(),
                now()->subWeek()->endOfWeek()
            ])
            ->sum('total_amount');
        
        $weekGrowth = 0;
        if ($lastWeekSales > 0) {
            $weekGrowth = (($weekSales - $lastWeekSales) / $lastWeekSales) * 100;
        } elseif ($weekSales > 0) {
            $weekGrowth = 100;
        }

        // Total inventory value
        $inventoryValue = InventoryBatch::where('status', 'available')
            ->get()
            ->sum(function($batch) {
                return $batch->quantity * $batch->price_per_unit;
            });

        // Active products
        $activeProducts = InventoryItem::active()->count();
        
        // Total batches
        $totalBatches = InventoryBatch::where('status', 'available')->count();

        // Low stock alerts
        $lowStockCount = InventoryItem::lowStock()->count();
        
        // Expiring soon (within 7 days)
        $expiringCount = InventoryBatch::expiringSoon(7)->count();
        
        // Out of stock
        $outOfStockCount = InventoryItem::outOfStock()->count();

        // Today's transactions
        $todayTransactions = SalesTransaction::today()->completed()->count();

        // Top selling products this week
        $topProducts = SalesItem::select('inventory_item_id', DB::raw('SUM(quantity) as total_qty'))
            ->whereHas('saleTransaction', function($q) {
                $q->completed()->thisWeek();
            })
            ->with('inventoryItem')
            ->groupBy('inventory_item_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->filter(function($item) {
                return $item->inventoryItem !== null; // Filter out items with no inventory item
            })
            ->map(function($item) {
                return [
                    'name' => $item->inventoryItem->name,
                    'quantity' => $item->total_qty,
                ];
            })
            ->values(); // Reset array keys after filter

        // Recent sales (last 10)
        $recentSales = SalesTransaction::with(['salesItems.inventoryItem', 'user'])
            ->completed()
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($sale) {
                return [
                    'transaction_code' => $sale->transaction_code,
                    'total_amount' => $sale->total_amount,
                    'items_count' => $sale->salesItems->count(),
                    'created_at' => $sale->created_at->format('M d, Y h:i A'),
                    'user_name' => $sale->user ? $sale->user->name : 'N/A',
                ];
            });

        // Expiring items list
        $expiringItems = InventoryBatch::with('inventoryItem')
            ->expiringSoon(7)
            ->where('status', 'available')
            ->where('quantity', '>', 0) // Only include batches with stock
            ->orderBy('expiry_date', 'asc')
            ->limit(10)
            ->get()
            ->filter(function($batch) {
                return $batch->inventoryItem !== null; // Filter out batches with no inventory item
            })
            ->map(function($batch) {
                return [
                    'product_name' => $batch->inventoryItem->name,
                    'batch_code' => $batch->batch_code,
                    'quantity' => $batch->quantity,
                    'expiry_date' => $batch->expiry_date->format('M d, Y'),
                    'days_remaining' => $batch->remaining_shelf_life,
                ];
            })
            ->values(); // Reset array keys after filter

        // Low stock items
        $lowStockItems = InventoryItem::lowStock()
            ->orderBy('stock_quantity', 'asc')
            ->limit(10)
            ->get()
            ->map(function($item) {
                return [
                    'name' => $item->name,
                    'current_stock' => $item->stock_quantity,
                    'reorder_level' => $item->reorder_level,
                    'deficit' => $item->reorder_level - $item->stock_quantity,
                ];
            });

        // Daily sales for the last 7 days (for charts)
        $dailySales = SalesTransaction::completed()
            ->where('created_at', '>=', now()->subDays(7))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        // Fill in missing dates with 0
        $salesChart = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $salesChart[$date] = $dailySales[$date] ?? 0;
        }

        return [
            'today_sales' => $todaySales,
            'week_sales' => $weekSales,
            'month_sales' => $monthSales,
            'week_growth' => round($weekGrowth, 1),
            'inventory_value' => $inventoryValue,
            'active_products' => $activeProducts,
            'total_batches' => $totalBatches,
            'low_stock_count' => $lowStockCount,
            'expiring_count' => $expiringCount,
            'out_of_stock_count' => $outOfStockCount,
            'today_transactions' => $todayTransactions,
            'top_products' => $topProducts,
            'recent_sales' => $recentSales,
            'expiring_items' => $expiringItems,
            'low_stock_items' => $lowStockItems,
            'sales_chart' => $salesChart,
        ];
    }

    /**
     * Everything the dashboard page renders that is not a plain metric:
     * KPI cards with sparklines, forecast / spoilage summaries, chart series and tables.
     */
    private function dashboardViewData(): array
    {
        $today = Carbon::today();
        $days = [];
        for ($i = 29; $i >= 0; $i--) {
            $days[] = $today->copy()->subDays($i)->toDateString();
        }

        // Daily revenue / cost (last 30 days)
        $revenueByDay = DB::table('sales_transactions')
            ->where('status', 'completed')->where('type', 'sale')
            ->whereDate('created_at', '>=', $days[0])
            ->selectRaw('DATE(created_at) as d, SUM(total_amount) as total')
            ->groupBy('d')->pluck('total', 'd');

        $costByDay = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_batches as b', 'b.id', '=', 'si.inventory_batch_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereDate('st.created_at', '>=', $days[0])
            ->selectRaw('DATE(st.created_at) as d, SUM(si.quantity * b.price_per_unit) as cost')
            ->groupBy('d')->pluck('cost', 'd');

        $rev = fn (string $d) => (float) ($revenueByDay[$d] ?? 0);
        $profit = fn (string $d) => $rev($d) - (float) ($costByDay[$d] ?? 0);
        $last7 = array_slice($days, -7);

        $pct = function (float $now, float $before): ?float {
            if ($before > 0) {
                return round(($now - $before) / $before * 100, 1);
            }
            return $now > 0 ? 100.0 : null;
        };
        $fmtPct = fn (?float $v) => $v === null ? '—' : ($v >= 0 ? '+' : '') . $v . '%';

        // Month figures
        $monthStart = $today->copy()->startOfMonth();
        $prevMonthStart = $monthStart->copy()->subMonth();
        $periodSales = fn ($from, $to) => (float) DB::table('sales_transactions')
            ->where('status', 'completed')->where('type', 'sale')
            ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)
            ->sum('total_amount');
        $periodCost = fn ($from, $to) => (float) DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_batches as b', 'b.id', '=', 'si.inventory_batch_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereDate('st.created_at', '>=', $from)->whereDate('st.created_at', '<=', $to)
            ->sum(DB::raw('si.quantity * b.price_per_unit'));

        $prevMonthEnd = $monthStart->copy()->subDay();
        $monthRevenue = $periodSales($monthStart, $today);
        $prevMonthRevenue = $periodSales($prevMonthStart, $prevMonthEnd);
        $monthSpark = [];
        for ($i = 6; $i >= 0; $i--) {
            $ms = $monthStart->copy()->subMonths($i);
            $monthSpark[] = $periodSales($ms, $ms->copy()->endOfMonth());
        }

        $monthProfit = $monthRevenue - $periodCost($monthStart, $today);
        $projectedProfit = $monthProfit / max($today->day, 1) * $today->daysInMonth;
        $prevMonthProfit = $prevMonthRevenue - $periodCost($prevMonthStart, $prevMonthEnd);

        // Stock history, rebuilt backwards from today's stock (per item, last 7 days)
        $items = DB::table('inventory_items')->where('status', 'active')
            ->get(['id', 'name', 'unit', 'stock_quantity', 'reorder_level']);
        $outflow = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->where('st.status', 'completed')->where('st.type', '!=', 'adjustment')->where('si.quantity', '>', 0)
            ->whereDate('st.created_at', '>=', $last7[0])
            ->selectRaw('si.inventory_item_id as item_id, DATE(st.created_at) as d, SUM(si.quantity) as qty')
            ->groupBy('item_id', 'd')->get()->groupBy('item_id');
        $inflow = DB::table('inventory_batches')
            ->whereDate('created_at', '>=', $last7[0])
            ->selectRaw('inventory_item_id as item_id, DATE(created_at) as d, SUM(initial_quantity) as qty')
            ->groupBy('item_id', 'd')->get()->groupBy('item_id');

        $totalStock = array_fill(0, 7, 0.0);
        $lowCount = array_fill(0, 7, 0);
        foreach ($items as $item) {
            $stock = (float) $item->stock_quantity;
            $out = collect($outflow[$item->id] ?? [])->pluck('qty', 'd');
            $in = collect($inflow[$item->id] ?? [])->pluck('qty', 'd');
            for ($k = 6; $k >= 0; $k--) {
                $totalStock[$k] += $stock;
                $lowCount[$k] += $stock <= (float) $item->reorder_level ? 1 : 0;
                // stock at the end of the previous day
                $stock = $stock - (float) ($in[$last7[$k]] ?? 0) + (float) ($out[$last7[$k]] ?? 0);
            }
        }
        $stockNow = $totalStock[6];
        $unit = $items->pluck('unit')->unique()->count() === 1 ? $items->first()->unit : 'kg';
        $lowNow = $lowCount[6];
        $outOfStock = $items->filter(fn ($i) => (float) $i->stock_quantity <= 0)->count();

        // Spoilage rate (spoiled + wasted qty / received qty) per week
        $spoilRate = function ($from, $to) {
            $spoiled = (float) DB::table('sales_items as si')
                ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
                ->whereIn('st.type', ['spoilage', 'wastage'])->where('st.status', 'completed')->where('si.quantity', '>', 0)
                ->whereDate('st.created_at', '>=', $from)->whereDate('st.created_at', '<=', $to)->sum('si.quantity');
            $received = (float) DB::table('inventory_batches')
                ->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to)->sum('initial_quantity');
            return $received > 0 ? round($spoiled / $received * 100, 1) : 0.0;
        };
        $spoilSpark = [];
        for ($i = 6; $i >= 0; $i--) {
            $end = $today->copy()->subDays($i * 7);
            $spoilSpark[] = $spoilRate($end->copy()->subDays(6), $end);
        }
        $spoilNow = $spoilSpark[6];
        $spoilDiff = round($spoilNow - $spoilSpark[5], 1);

        // AI model results
        $accRuns = DB::table('ml_model_runs')->where('model', 'SARIMAX')->where('status', 'success')
            ->orderByDesc('id')->limit(7)->get()
            ->map(fn ($r) => (float) (json_decode($r->metrics ?? 'null', true)['overall_accuracy'] ?? 0))
            ->filter(fn ($v) => $v > 0)->reverse()->values();
        $accuracy = $accRuns->last();
        $accChange = $accRuns->count() > 1 ? round($accRuns->last() - $accRuns[$accRuns->count() - 2], 1) : null;

        $predDate = DB::table('spoilage_predictions')->max('prediction_date');
        $avgRisk = $predDate
            ? (float) DB::table('spoilage_predictions')->where('prediction_date', $predDate)->where('horizon_day', 0)->avg('risk_score')
            : null;
        $scoreParts = array_values(array_filter([$accuracy, $avgRisk === null ? null : 100 - $avgRisk], fn ($v) => $v !== null));
        $aiScore = $scoreParts ? (int) round(array_sum($scoreParts) / count($scoreParts)) : null;

        $spark = fn (array $v) => count($v) > 1 ? array_map(fn ($x) => round((float) $x, 2), $v) : array_fill(0, 2, count($v) ? round((float) $v[0], 2) : 0);
        $money = fn (float $v) => '₱' . number_format($v);

        $todayRev = $rev($days[29]);
        $c1 = $pct($todayRev, $rev($days[28]));
        $c2 = $pct($monthRevenue, $prevMonthRevenue);
        $c3 = $pct($stockNow, $totalStock[0]);
        $c7 = $pct($projectedProfit, $prevMonthProfit);

        // [label, value, change, isUp, subtitle, sparkline]
        $kpis1 = [
            ["Today's Sales",   $money($todayRev),     $fmtPct($c1), ($c1 ?? 0) >= 0, 'vs yesterday',  $spark(array_map($rev, $last7))],
            ['Monthly Revenue', $money($monthRevenue), $fmtPct($c2), ($c2 ?? 0) >= 0, 'vs last month', $spark($monthSpark)],
            ['Total Inventory', number_format($stockNow) . ' ' . $unit, $fmtPct($c3), ($c3 ?? 0) >= 0, 'vs last week', $spark($totalStock)],
            ['Forecast Acc.',   $accuracy === null ? 'N/A' : $accuracy . '%', $accChange === null ? '—' : ($accChange >= 0 ? '+' : '') . $accChange . '%', ($accChange ?? 0) >= 0, 'model confidence', $spark($accRuns->all())],
        ];
        $kpis2 = [
            ['Spoilage Rate',   $spoilNow . '%', ($spoilDiff >= 0 ? '+' : '') . $spoilDiff . '%', $spoilDiff <= 0, 'last 7 days', $spark($spoilSpark)],
            ['Low Stock Items', $lowNow . ' ' . ($lowNow === 1 ? 'Item' : 'Items'), $outOfStock . ' out', $outOfStock === 0, 'needs attention', $spark($lowCount)],
            ['Expected Profit', $money(max($projectedProfit, 0)), $fmtPct($c7), ($c7 ?? 0) >= 0, 'projected this month', $spark(array_map($profit, $last7))],
            ['AI Score',        $aiScore === null ? 'N/A' : $aiScore . '/100', '—', true, 'recommendations', $spark($aiScore === null ? [] : array_fill(0, 7, $aiScore))],
        ];

        // Forecast summary (SARIMAX output)
        $tzToday = Carbon::now(config('services.openweather.timezone'))->toDateString();
        $forecastRows = DB::table('ai_forecasts as f')
            ->leftJoin('inventory_items as i', 'i.id', '=', 'f.inventory_item_id')
            ->where('f.forecast_date', '>=', $tzToday)
            ->get(['f.fruit_name', 'f.inventory_item_id', 'f.predicted_sales_qty', 'i.stock_quantity', 'i.unit']);
        $recentQty = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->where('st.type', 'sale')->where('st.status', 'completed')->where('si.quantity', '>', 0)
            ->whereDate('st.created_at', '>=', $today->copy()->subDays(7))
            ->selectRaw('si.inventory_item_id as item_id, SUM(si.quantity) as qty')->groupBy('item_id')->pluck('qty', 'item_id');

        $fruits = $forecastRows->groupBy('fruit_name')->map(function ($rows, $name) use ($recentQty) {
            $perDay = round($rows->avg('predicted_sales_qty'), 1);
            $current = ((float) ($recentQty[$rows->first()->inventory_item_id] ?? 0)) / 7;
            return [
                'name' => $name,
                'per_day' => $perDay,
                'unit' => $rows->first()->unit ?? 'kg',
                'change' => $current > 0 ? (int) round(($perDay - $current) / $current * 100) : null,
                'short' => (int) round(max($rows->sum('predicted_sales_qty') - (float) $rows->first()->stock_quantity, 0)),
            ];
        })->sortByDesc('per_day')->values();

        $forecast = [
            'has_data' => $fruits->isNotEmpty(),
            'accuracy' => $accuracy,
            'labels' => $fruits->pluck('name')->all(),
            'values' => $fruits->pluck('per_day')->all(),
            'highest' => $fruits->first(),
            'lowest' => $fruits->count() > 1 ? $fruits->last() : null,
            'restock' => $fruits->filter(fn ($f) => $f['short'] > 0)->sortByDesc('short')->take(3)->values()->all(),
        ];

        // Spoilage table (XGBoost / shelf-life rule output: riskiest batches today)
        $spoilage = [];
        if ($predDate) {
            $spoilage = DB::table('spoilage_predictions as p')
                ->join('inventory_batches as b', 'b.id', '=', 'p.inventory_batch_id')
                ->join('inventory_items as i', 'i.id', '=', 'p.inventory_item_id')
                ->where('p.prediction_date', $predDate)->where('p.horizon_day', 0)
                ->where('b.status', 'available')->where('b.quantity', '>', 0)
                ->orderByDesc('p.risk_score')->limit(5)
                ->get(['b.batch_code', 'b.quantity', 'b.expiry_date', 'i.name', 'i.unit', 'p.risk_score', 'p.risk_level', 'p.recommendation'])
                ->map(function ($r) use ($today) {
                    $left = $r->expiry_date ? $today->diffInDays(Carbon::parse($r->expiry_date), false) : null;
                    $badge = ['Critical' => 'badge-red', 'High Risk' => 'badge-orange', 'Moderate' => 'badge-amber'][$r->risk_level] ?? 'badge-green';
                    return [
                        $r->batch_code, $r->name, $this->qty($r->quantity) . ' ' . $r->unit,
                        (int) round($r->risk_score),
                        $left === null ? '—' : ($left <= 0 ? 'Expired' : ($left === 1 ? '24h' : $left . ' days')),
                        $r->risk_level, $badge, $r->recommendation ?: '—',
                    ];
                })->all();
        }

        // Sales trend (30 days; the page slices it for the 7D / 14D / 30D buttons)
        $trend = [
            'labels' => array_map(fn ($d) => Carbon::parse($d)->format('M j'), $days),
            'values' => array_map($rev, $days),
        ];

        // Inventory distribution: top 3 fruits by stock + Others
        $palette = ['#7C3AED', '#8B5CF6', '#A78BFA', '#C4B5FD'];
        $inStock = $items->filter(fn ($i) => $i->stock_quantity > 0)->sortByDesc('stock_quantity')->values();
        $stockTotal = (float) $inStock->sum('stock_quantity');
        $slices = $inStock->take(3)->map(fn ($i) => ['name' => $i->name, 'qty' => (float) $i->stock_quantity])->all();
        if ($inStock->count() > 3) {
            $slices[] = ['name' => 'Others', 'qty' => (float) $inStock->slice(3)->sum('stock_quantity')];
        }
        $distribution = [];
        foreach ($slices as $n => $slice) {
            $share = $stockTotal > 0 ? (int) round($slice['qty'] / $stockTotal * 100) : 0;
            $distribution[] = [$slice['name'], $share . '%', $palette[$n], $share];
        }

        // Weekly forecast vs actual revenue (last 5 weeks)
        $weekLabels = $weekActual = $weekForecast = [];
        $thisWeek = $today->copy()->startOfWeek();
        for ($w = 4; $w >= 0; $w--) {
            $ws = $thisWeek->copy()->subWeeks($w);
            $we = $w === 0 ? $today : $ws->copy()->endOfWeek();
            $weekLabels[] = $w === 0 ? 'This week' : 'Wk of ' . $ws->format('M j');
            $weekActual[] = round($periodSales($ws, $we), 2);
            $weekForecast[] = round((float) DB::table('ai_forecasts')
                ->whereDate('forecast_date', '>=', $ws)->whereDate('forecast_date', '<=', $we)->sum('predicted_revenue'), 2);
        }

        // Revenue per fruit this month
        $fruitRevenue = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_items as i', 'i.id', '=', 'si.inventory_item_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')
            ->whereDate('st.created_at', '>=', $monthStart)
            ->selectRaw('i.name, SUM(si.total_amount) as revenue')
            ->groupBy('i.id', 'i.name')->orderByDesc('revenue')->limit(7)->get();

        // Today's transactions and top sellers
        $iconStyles = [
            ['bg-violet-100', 'text-violet-600'], ['bg-green-100', 'text-green-600'], ['bg-yellow-100', 'text-yellow-600'],
            ['bg-orange-100', 'text-orange-600'], ['bg-pink-100', 'text-pink-600'], ['bg-blue-100', 'text-blue-600'],
        ];
        $boxIcon = 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4';
        $transactions = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->leftJoin('inventory_items as i', 'i.id', '=', 'si.inventory_item_id')
            ->where('st.type', 'sale')->whereDate('st.created_at', $today)
            ->orderByDesc('st.created_at')->limit(6)
            ->get(['i.name', 'i.unit', 'si.quantity', 'si.total_amount', 'st.created_at', 'st.status'])
            ->values()
            ->map(fn ($r, $n) => [
                $iconStyles[$n % 6][0], $boxIcon, $iconStyles[$n % 6][1], $r->name ?? 'Item',
                trim($this->qty($r->quantity) . ' ' . ($r->unit ?? '')),
                '₱' . number_format($r->total_amount), Carbon::parse($r->created_at)->format('h:i A'),
                ucfirst($r->status), $r->status === 'completed' ? 'badge-green' : 'badge-amber',
            ])->all();

        $topColors = ['#7C3AED', '#10B981', '#3B82F6', '#F59E0B'];
        $topToday = DB::table('sales_items as si')
            ->join('sales_transactions as st', 'st.id', '=', 'si.sale_transaction_id')
            ->join('inventory_items as i', 'i.id', '=', 'si.inventory_item_id')
            ->where('st.status', 'completed')->where('st.type', 'sale')->whereDate('st.created_at', $today)
            ->selectRaw('i.name, SUM(si.total_amount) as revenue')->groupBy('i.id', 'i.name')
            ->orderByDesc('revenue')->limit(4)->get();
        $topMax = (float) ($topToday->first()->revenue ?? 0);
        $topSellers = $topToday->values()->map(fn ($r, $n) => [
            $r->name, '₱' . number_format($r->revenue), $topColors[$n], $topMax > 0 ? (int) round($r->revenue / $topMax * 100) : 0,
        ])->all();

        // Low stock table (lowest stock level first)
        $lowStock = InventoryItem::lowStock()->active()->get()
            ->map(fn ($i) => ['item' => $i, 'pct' => $i->reorder_level > 0 ? (int) min(round($i->stock_quantity / $i->reorder_level * 100), 100) : 0])
            ->sortBy('pct')->take(3)->values()
            ->map(fn ($r, $n) => [
                $iconStyles[($n + 3) % 6][0], $iconStyles[($n + 3) % 6][1], $r['item']->name,
                $this->qty($r['item']->stock_quantity) . ' ' . $r['item']->unit,
                $this->qty($r['item']->reorder_level) . ' ' . $r['item']->unit,
                $r['pct'], $r['pct'] < 20 ? 'Critical' : 'Low Stock', $r['pct'] < 20 ? 'badge-red' : 'badge-amber',
            ])->all();

        return [
            'kpis1' => $kpis1,
            'kpis2' => $kpis2,
            'forecast' => $forecast,
            'spoilage' => $spoilage,
            'trend' => $trend,
            'distribution' => $distribution,
            'week' => ['labels' => $weekLabels, 'actual' => $weekActual, 'forecast' => $weekForecast],
            'fruit_revenue' => [
                'labels' => $fruitRevenue->pluck('name')->all(),
                'values' => $fruitRevenue->pluck('revenue')->map(fn ($v) => round((float) $v, 2))->all(),
            ],
            'transactions' => $transactions,
            'top_sellers' => $topSellers,
            'low_stock' => $lowStock,
        ];
    }

    /** 12.50 -> "12.5", 15.00 -> "15" */
    private function qty($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, '.', ''), '0'), '.');
    }

    /**
     * Get dashboard data as JSON (for AJAX updates).
     */
    public function getData()
    {
        $metrics = $this->getDashboardMetrics();
        return response()->json($metrics);
    }

    /**
     * Get sales trend data.
     */
    public function getSalesTrend(Request $request)
    {
        $days = $request->input('days', 30);
        
        $sales = SalesTransaction::completed()
            ->where('created_at', '>=', now()->subDays($days))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total_amount) as total_sales'),
                DB::raw('COUNT(*) as transaction_count')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return response()->json($sales);
    }

    /**
     * Get inventory health summary.
     */
    public function getInventoryHealth()
    {
        $total = InventoryItem::count();
        $available = InventoryItem::where('stock_quantity', '>', 0)->count();
        $lowStock = InventoryItem::lowStock()->count();
        $outOfStock = InventoryItem::outOfStock()->count();

        $healthPercentage = $total > 0 ? round((($available - $lowStock) / $total) * 100, 1) : 0;

        return response()->json([
            'total_products' => $total,
            'available' => $available,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'health_percentage' => $healthPercentage,
        ]);
    }
}
