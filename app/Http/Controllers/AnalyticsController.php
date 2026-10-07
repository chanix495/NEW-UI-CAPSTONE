<?php

namespace App\Http\Controllers;

use App\Models\SalesTransaction;
use App\Models\SalesItem;
use App\Models\InventoryItem;
use App\Models\InventoryBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    /**
     * Display the analytics page.
     */
    public function page()
    {
        return view('pages.analytics');
    }

    /**
     * Get KPI metrics.
     */
    public function getKPIs()
    {
        try {
            // Total revenue (all time)
            $totalRevenue = SalesTransaction::completed()->sum('total_amount');

            // Last month revenue
            $lastMonth = Carbon::now()->subMonth();
            $lastMonthRevenue = SalesTransaction::completed()
                ->whereMonth('created_at', $lastMonth->month)
                ->whereYear('created_at', $lastMonth->year)
                ->sum('total_amount');

            // Previous month revenue (for trend)
            $previousMonth = Carbon::now()->subMonths(2);
            $previousMonthRevenue = SalesTransaction::completed()
                ->whereMonth('created_at', $previousMonth->month)
                ->whereYear('created_at', $previousMonth->year)
                ->sum('total_amount');

            // Revenue change percentage
            $revenueChangePct = 0;
            if ($previousMonthRevenue > 0) {
                $revenueChangePct = (($lastMonthRevenue - $previousMonthRevenue) / $previousMonthRevenue) * 100;
            } elseif ($lastMonthRevenue > 0) {
                $revenueChangePct = 100;
            }

            // COGS calculation
            $cogs = SalesItem::whereHas('saleTransaction', function($q) {
                    $q->completed();
                })
                ->with('inventoryBatch')
                ->get()
                ->sum(function($item) {
                    return $item->quantity * ($item->inventoryBatch?->price_per_unit ?? 0);
                });

            // Gross profit and margin
            $grossProfit = $totalRevenue - $cogs;
            $profitMargin = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;

            // Profit change (last month vs previous month)
            $lastMonthCogs = SalesItem::whereHas('saleTransaction', function($q) use ($lastMonth) {
                    $q->completed()
                        ->whereMonth('created_at', $lastMonth->month)
                        ->whereYear('created_at', $lastMonth->year);
                })
                ->with('inventoryBatch')
                ->get()
                ->sum(function($item) {
                    return $item->quantity * ($item->inventoryBatch?->price_per_unit ?? 0);
                });

            $previousMonthCogs = SalesItem::whereHas('saleTransaction', function($q) use ($previousMonth) {
                    $q->completed()
                        ->whereMonth('created_at', $previousMonth->month)
                        ->whereYear('created_at', $previousMonth->year);
                })
                ->with('inventoryBatch')
                ->get()
                ->sum(function($item) {
                    return $item->quantity * ($item->inventoryBatch?->price_per_unit ?? 0);
                });

            $lastMonthProfit = $lastMonthRevenue - $lastMonthCogs;
            $previousMonthProfit = $previousMonthRevenue - $previousMonthCogs;

            $profitChangePct = 0;
            if ($previousMonthProfit > 0) {
                $profitChangePct = (($lastMonthProfit - $previousMonthProfit) / $previousMonthProfit) * 100;
            } elseif ($lastMonthProfit > 0) {
                $profitChangePct = 100;
            }

            // Sales volume (all time)
            $salesVolume = SalesItem::whereHas('saleTransaction', function($q) {
                    $q->completed();
                })
                ->sum('quantity');

            // Waste reduction calculation (current month expired vs 6-month average)
            $currentMonth = Carbon::now();
            $currentMonthExpired = InventoryBatch::whereMonth('expiry_date', $currentMonth->month)
                ->whereYear('expiry_date', $currentMonth->year)
                ->where(function($q) {
                    $q->where('status', 'expired')
                        ->orWhere('expiry_date', '<', now());
                })
                ->sum('quantity');

            // Calculate 6-month average expired quantity
            $sixMonthsAgo = Carbon::now()->subMonths(6);
            $avgExpiredLast6Months = InventoryBatch::where('expiry_date', '>=', $sixMonthsAgo)
                ->where('expiry_date', '<', $currentMonth->startOfMonth())
                ->where(function($q) {
                    $q->where('status', 'expired')
                        ->orWhere('expiry_date', '<', now());
                })
                ->selectRaw('AVG(monthly_expired) as avg')
                ->from(DB::raw('(SELECT SUM(quantity) as monthly_expired, YEAR(expiry_date) as year, MONTH(expiry_date) as month FROM inventory_batches WHERE expiry_date >= ? AND expiry_date < ? AND (status = "expired" OR expiry_date < NOW()) GROUP BY YEAR(expiry_date), MONTH(expiry_date)) as monthly_data'))
                ->setBindings([$sixMonthsAgo, $currentMonth->startOfMonth()])
                ->value('avg');

            $wasteReducedPct = 0;
            if ($avgExpiredLast6Months > 0) {
                $wasteReducedPct = (($avgExpiredLast6Months - $currentMonthExpired) / $avgExpiredLast6Months) * 100;
            } elseif ($currentMonthExpired == 0) {
                $wasteReducedPct = 100;
            }

            // Forecast accuracy placeholder
            $forecastAccuracy = 96.4;

            return response()->json([
                'total_revenue' => round($totalRevenue, 2),
                'gross_profit' => round($grossProfit, 2),
                'profit_margin' => round($profitMargin, 2),
                'sales_volume' => round($salesVolume, 2),
                'waste_reduced_pct' => round(max(0, min(100, $wasteReducedPct)), 1),
                'forecast_accuracy' => $forecastAccuracy,
                'revenue_change_pct' => round($revenueChangePct, 1),
                'profit_change_pct' => round($profitChangePct, 1),
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics KPIs error: ' . $e->getMessage());
            return response()->json([
                'total_revenue' => 0,
                'gross_profit' => 0,
                'profit_margin' => 0,
                'sales_volume' => 0,
                'waste_reduced_pct' => 0,
                'forecast_accuracy' => 96.4,
                'revenue_change_pct' => 0,
                'profit_change_pct' => 0,
            ]);
        }
    }

    /**
     * Get revenue trend for last 6 months.
     */
    public function getRevenueTrend()
    {
        try {
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            // Get revenue by month
            $revenueByMonth = SalesTransaction::completed()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('YEAR(created_at) as year'),
                    DB::raw('MONTH(created_at) as month'),
                    DB::raw('SUM(total_amount) as revenue')
                )
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get()
                ->keyBy(function($item) {
                    return $item->year . '-' . str_pad($item->month, 2, '0', STR_PAD_LEFT);
                });

            // Get COGS by month
            $cogsByMonth = SalesItem::whereHas('saleTransaction', function($q) use ($startDate, $endDate) {
                    $q->completed()->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->with('inventoryBatch', 'saleTransaction')
                ->get()
                ->groupBy(function($item) {
                    $date = Carbon::parse($item->saleTransaction->created_at);
                    return $date->format('Y-m');
                })
                ->map(function($items) {
                    return $items->sum(function($item) {
                        return $item->quantity * ($item->inventoryBatch?->price_per_unit ?? 0);
                    });
                });

            // Build 6-month array
            $labels = [];
            $revenue = [];
            $cogs = [];
            $profit = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthKey = $date->format('Y-m');
                $labels[] = $date->format('M');
                
                $monthRevenue = $revenueByMonth->get($monthKey)?->revenue ?? 0;
                $monthCogs = $cogsByMonth->get($monthKey) ?? 0;
                $monthProfit = $monthRevenue - $monthCogs;

                $revenue[] = round($monthRevenue, 2);
                $cogs[] = round($monthCogs, 2);
                $profit[] = round($monthProfit, 2);
            }

            return response()->json([
                'labels' => $labels,
                'revenue' => $revenue,
                'cogs' => $cogs,
                'profit' => $profit,
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics revenue trend error: ' . $e->getMessage());
            return response()->json([
                'labels' => [],
                'revenue' => [],
                'cogs' => [],
                'profit' => [],
            ]);
        }
    }

    /**
     * Get sales by fruit (top 4 + Others).
     */
    public function getSalesByFruit()
    {
        try {
            $salesByProduct = SalesItem::whereHas('saleTransaction', function($q) {
                    $q->completed();
                })
                ->select(
                    'inventory_item_id',
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->with('inventoryItem')
                ->groupBy('inventory_item_id')
                ->orderByDesc('total_sales')
                ->get()
                ->filter(function($item) {
                    return $item->inventoryItem !== null;
                });

            $totalSales = $salesByProduct->sum('total_sales');

            if ($totalSales == 0) {
                return response()->json([]);
            }

            $colors = ['#7C3AED', '#10B981', '#8B5CF6', '#3B82F6', '#9CA3AF'];
            $result = [];

            // Top 4 products
            $top4 = $salesByProduct->take(4);
            foreach ($top4 as $index => $item) {
                $result[] = [
                    'name' => $item->inventoryItem->name,
                    'amount' => round($item->total_sales, 2),
                    'percentage' => round(($item->total_sales / $totalSales) * 100, 1),
                    'color' => $colors[$index] ?? '#9CA3AF',
                ];
            }

            // Others
            if ($salesByProduct->count() > 4) {
                $othersTotal = $salesByProduct->skip(4)->sum('total_sales');
                $result[] = [
                    'name' => 'Others',
                    'amount' => round($othersTotal, 2),
                    'percentage' => round(($othersTotal / $totalSales) * 100, 1),
                    'color' => '#9CA3AF',
                ];
            }

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Analytics sales by fruit error: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    /**
     * Get weekly sales for last 8 weeks.
     */
    public function getWeeklySales()
    {
        try {
            $startDate = Carbon::now()->subWeeks(8)->startOfWeek();
            $endDate = Carbon::now()->endOfWeek();

            $salesByWeek = SalesTransaction::completed()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select(
                    DB::raw('YEARWEEK(created_at, 1) as yearweek'),
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->groupBy('yearweek')
                ->orderBy('yearweek', 'asc')
                ->get()
                ->keyBy('yearweek');

            // Build 8-week array
            $labels = [];
            $data = [];

            for ($i = 7; $i >= 0; $i--) {
                $date = Carbon::now()->subWeeks($i);
                $yearWeek = $date->format('oW'); // ISO-8601 week
                $weekNumber = 8 - $i;
                $labels[] = 'W' . $weekNumber;
                
                $weekSales = 0;
                // Match yearweek from database
                foreach ($salesByWeek as $week => $sales) {
                    $weekDate = Carbon::now()->setISODate(substr($week, 0, 4), substr($week, 4));
                    if ($weekDate->isSameWeek($date)) {
                        $weekSales = $sales->total_sales;
                        break;
                    }
                }

                $data[] = round($weekSales, 2);
            }

            return response()->json([
                'labels' => $labels,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics weekly sales error: ' . $e->getMessage());
            return response()->json([
                'labels' => ['W1', 'W2', 'W3', 'W4', 'W5', 'W6', 'W7', 'W8'],
                'data' => [0, 0, 0, 0, 0, 0, 0, 0],
            ]);
        }
    }

    /**
     * Get cumulative revenue for current month.
     */
    public function getCumulativeRevenue()
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth();
            $today = Carbon::now();

            $salesByDay = SalesTransaction::completed()
                ->whereBetween('created_at', [$startOfMonth, $today->endOfDay()])
                ->select(
                    DB::raw('DATE(created_at) as date'),
                    DB::raw('SUM(total_amount) as daily_sales')
                )
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get()
                ->keyBy('date');

            $labels = [];
            $data = [];
            $cumulative = 0;
            $daysInMonth = $today->diffInDays($startOfMonth) + 1;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $date = $startOfMonth->copy()->addDays($day - 1);
                $dateKey = $date->format('Y-m-d');
                
                $labels[] = $day;
                $dailySales = $salesByDay->get($dateKey)?->daily_sales ?? 0;
                $cumulative += $dailySales;
                $data[] = round($cumulative, 2);
            }

            return response()->json([
                'labels' => $labels,
                'data' => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics cumulative revenue error: ' . $e->getMessage());
            return response()->json([
                'labels' => [],
                'data' => [],
            ]);
        }
    }

    /**
     * Get waste reduction trend (spoilage rate last 6 months).
     */
    public function getWasteReduction()
    {
        try {
            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            $labels = [];
            $data = [];
            $colors = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();
                
                $labels[] = $date->format('M');

                // Total inventory received in this month
                $totalReceived = InventoryBatch::whereBetween('received_date', [$monthStart, $monthEnd])
                    ->sum('quantity');

                // Expired in this month
                $expired = InventoryBatch::whereBetween('expiry_date', [$monthStart, $monthEnd])
                    ->where(function($q) {
                        $q->where('status', 'expired')
                            ->orWhere('expiry_date', '<', now());
                    })
                    ->sum('quantity');

                $spoilageRate = 0;
                if ($totalReceived > 0) {
                    $spoilageRate = ($expired / $totalReceived) * 100;
                }

                $data[] = round($spoilageRate, 1);

                // Color based on rate (green = low, red = high)
                if ($spoilageRate <= 5) {
                    $colors[] = 'rgba(16,185,129,.65)'; // green
                } elseif ($spoilageRate <= 10) {
                    $colors[] = 'rgba(132,204,22,.65)'; // lime
                } elseif ($spoilageRate <= 15) {
                    $colors[] = 'rgba(234,179,8,.65)'; // yellow
                } elseif ($spoilageRate <= 20) {
                    $colors[] = 'rgba(249,115,22,.65)'; // orange
                } else {
                    $colors[] = 'rgba(239,68,68,.65)'; // red
                }
            }

            return response()->json([
                'labels' => $labels,
                'data' => $data,
                'colors' => $colors,
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics waste reduction error: ' . $e->getMessage());
            return response()->json([
                'labels' => [],
                'data' => [],
                'colors' => [],
            ]);
        }
    }

    /**
     * Get stacked sales by product (last 6 months).
     */
    public function getStackedSales()
    {
        try {
            // Get top 4 products by all-time sales
            $topProducts = SalesItem::whereHas('saleTransaction', function($q) {
                    $q->completed();
                })
                ->select(
                    'inventory_item_id',
                    DB::raw('SUM(total_amount) as total_sales')
                )
                ->with('inventoryItem')
                ->groupBy('inventory_item_id')
                ->orderByDesc('total_sales')
                ->limit(4)
                ->get()
                ->filter(function($item) {
                    return $item->inventoryItem !== null;
                });

            if ($topProducts->isEmpty()) {
                return response()->json([
                    'labels' => [],
                    'datasets' => [],
                ]);
            }

            $startDate = Carbon::now()->subMonths(5)->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();

            // Get sales by product and month
            $salesData = SalesItem::whereHas('saleTransaction', function($q) use ($startDate, $endDate) {
                    $q->completed()->whereBetween('created_at', [$startDate, $endDate]);
                })
                ->whereIn('inventory_item_id', $topProducts->pluck('inventory_item_id'))
                ->with('saleTransaction')
                ->get()
                ->groupBy('inventory_item_id')
                ->map(function($items) {
                    return $items->groupBy(function($item) {
                        $date = Carbon::parse($item->saleTransaction->created_at);
                        return $date->format('Y-m');
                    })->map(function($monthItems) {
                        return $monthItems->sum('total_amount');
                    });
                });

            // Build labels (last 6 months)
            $labels = [];
            for ($i = 5; $i >= 0; $i--) {
                $labels[] = Carbon::now()->subMonths($i)->format('M');
            }

            // Build datasets
            $colors = ['#7C3AED', '#10B981', '#3B82F6', '#8B5CF6'];
            $datasets = [];

            foreach ($topProducts as $index => $product) {
                $productData = [];
                
                for ($i = 5; $i >= 0; $i--) {
                    $monthKey = Carbon::now()->subMonths($i)->format('Y-m');
                    $sales = $salesData->get($product->inventory_item_id)?->get($monthKey) ?? 0;
                    $productData[] = round($sales, 2);
                }

                $datasets[] = [
                    'label' => $product->inventoryItem->name,
                    'data' => $productData,
                    'backgroundColor' => $colors[$index] ?? '#9CA3AF',
                ];
            }

            return response()->json([
                'labels' => $labels,
                'datasets' => $datasets,
            ]);

        } catch (\Exception $e) {
            Log::error('Analytics stacked sales error: ' . $e->getMessage());
            return response()->json([
                'labels' => [],
                'datasets' => [],
            ]);
        }
    }
}
