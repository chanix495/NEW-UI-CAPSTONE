<x-app-layout>
<x-slot name="title">Dashboard — FreshTrack</x-slot>

{{-- HERO BANNER --}}
<div class="relative rounded-3xl p-7 mb-7 overflow-hidden shadow-xl fade-up"
     style="background: linear-gradient(135deg, #7C3AED 0%, #6D28D9 50%, #5B21B6 100%)">
    {{-- Dot grid texture --}}
    <div class="absolute inset-0 opacity-10"
         style="background-image:radial-gradient(circle,rgba(255,255,255,.8) 1px,transparent 1px);background-size:32px 32px"></div>
    {{-- Decorative SVG shapes --}}
    <div class="absolute right-6 top-0 bottom-0 flex items-center pointer-events-none select-none opacity-10">
        <svg class="w-48 h-48 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 14H9V8h2v8zm4 0h-2V8h2v8z"/>
        </svg>
    </div>
    <div class="absolute right-40 bottom-2 pointer-events-none select-none opacity-[0.07]">
        <svg class="w-32 h-32 text-white" fill="currentColor" viewBox="0 0 24 24">
            <path d="M17 8C8 10 5.9 16.17 3.82 21.34L5.71 22l1-2.3A4.49 4.49 0 008 20C19 20 22 3 22 3c-1 2-8 2-8 2 .29-1.18.85-2.26 1.64-3.16A9.5 9.5 0 0017 8z"/>
        </svg>
    </div>
    <div class="relative">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0 backdrop-blur-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3m0 0V11"/>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between">
                    <h1 class="text-2xl font-black text-white">Good {{ now($prefs->timezone)->hour < 12 ? 'morning' : (now($prefs->timezone)->hour < 18 ? 'afternoon' : 'evening') }}, {{ auth()->user()->name }}!</h1>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <a href="{{ route('reports') }}"
                           class="flex items-center gap-2 bg-white/15 border border-white/25 text-white text-[13px] font-semibold px-4 py-2.5 rounded-xl hover:bg-white/25 transition-all">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Export Report
                        </a>
                        <a href="{{ route('forecast') }}"
                           class="flex items-center gap-2 bg-white text-violet-700 text-[13px] font-bold px-4 py-2.5 rounded-xl hover:bg-violet-50 transition-all shadow-lg">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            View Sales Forecasting
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-white/75 text-[14.5px] mb-4">
            {{ now($prefs->timezone)->format('l') }}, {{ $prefs->formatDate(now()) }} · Here's your executive summary for today.
        </p>
        <div class="flex flex-wrap gap-3">
            <span class="flex items-center gap-1.5 text-[12px] bg-white/15 text-white font-semibold px-3.5 py-1.5 rounded-full backdrop-blur-sm border border-white/20">
                <span class="w-1.5 h-1.5 bg-violet-200 rounded-full" style="animation:pd 2.2s infinite"></span>
                Live Data
            </span>
            <span class="flex items-center gap-1.5 text-[12px] bg-white/15 text-white font-semibold px-3.5 py-1.5 rounded-full border border-white/20">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                FreshTrack {{ config('services.openweather.city') }} · {{ now()->format('F Y') }}
            </span>
            <span class="flex items-center gap-1.5 text-[12px] bg-white/15 text-white font-semibold px-3.5 py-1.5 rounded-full border border-white/20">
                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                </svg>
                <span id="heroWeather">Weather loading…</span>
            </span>
        </div>
    </div>
</div>

{{-- KPI CARDS ROW 1 — with sparklines --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
@php
$kpis1 = [
    [$dash['kpis1'][0][0], $dash['kpis1'][0][1], $dash['kpis1'][0][2], $dash['kpis1'][0][3], 'g-violet', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', $dash['kpis1'][0][4], '#7C3AED', 'salesSparkline', $dash['kpis1'][0][5]],
    [$dash['kpis1'][1][0], $dash['kpis1'][1][1], $dash['kpis1'][1][2], $dash['kpis1'][1][3], 'g-violet', 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', $dash['kpis1'][1][4], '#8B5CF6', 'revenueSparkline', $dash['kpis1'][1][5]],
    [$dash['kpis1'][2][0], $dash['kpis1'][2][1], $dash['kpis1'][2][2], $dash['kpis1'][2][3], 'g-violet', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', $dash['kpis1'][2][4], '#A78BFA', 'inventorySparkline', $dash['kpis1'][2][5]],
    [$dash['kpis1'][3][0], $dash['kpis1'][3][1], $dash['kpis1'][3][2], $dash['kpis1'][3][3], 'g-violet', 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', $dash['kpis1'][3][4], '#6D28D9', 'forecastSparkline', $dash['kpis1'][3][5]],
];
@endphp
@foreach($kpis1 as [$label,$val,$chg,$up,$grad,$iconPath,$sub,$color,$canvasId,$sparkData])
<div class="card shimmer card-lift p-5">
    <div class="flex items-start justify-between mb-3">
        <div class="icon-ring">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
            </svg>
        </div>
        <span class="badge {{ $up ? 'bg-violet-100 text-violet-700' : 'bg-purple-100 text-purple-700' }} text-[11px] flex items-center gap-1">
            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $up ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
            </svg>
            {{ $chg }}
        </span>
    </div>
    <p class="text-[26px] font-black text-gray-900 leading-none mb-1">{{ $val }}</p>
    <p class="text-[12.5px] font-semibold text-gray-600">{{ $label }}</p>
    <p class="text-[11.5px] text-gray-400 mt-0.5 mb-3">{{ $sub }}</p>
    {{-- Sparkline --}}
    <canvas id="{{ $canvasId }}" height="36"
            data-values="{{ implode(',', $sparkData) }}"
            data-color="{{ $color }}"
            data-up="{{ $up ? '1' : '0' }}"
            class="sparkline-canvas w-full"></canvas>
</div>
@endforeach
</div>

{{-- KPI CARDS ROW 2 --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-7">
@php
$kpis2 = [
    [$dash['kpis2'][0][0], $dash['kpis2'][0][1], $dash['kpis2'][0][2], $dash['kpis2'][0][3], 'g-violet', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', $dash['kpis2'][0][4], '#9333EA', 'spoilageSparkline', $dash['kpis2'][0][5]],
    [$dash['kpis2'][1][0], $dash['kpis2'][1][1], $dash['kpis2'][1][2], $dash['kpis2'][1][3], 'g-violet', 'M13 17h8m0 0V9m0 8l-8-8-4 4-6-6', $dash['kpis2'][1][4], '#A855F7', 'lowstockSparkline', $dash['kpis2'][1][5]],
    [$dash['kpis2'][2][0], $dash['kpis2'][2][1], $dash['kpis2'][2][2], $dash['kpis2'][2][3], 'g-violet', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', $dash['kpis2'][2][4], '#7C3AED', 'profitSparkline', $dash['kpis2'][2][5]],
    [$dash['kpis2'][3][0], $dash['kpis2'][3][1], $dash['kpis2'][3][2], $dash['kpis2'][3][3], 'g-violet', 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', $dash['kpis2'][3][4], '#6D28D9', 'aiSparkline', $dash['kpis2'][3][5]],
];
@endphp
@foreach($kpis2 as [$label,$val,$chg,$up,$grad,$iconPath,$sub,$color,$canvasId,$sparkData])
<div class="card shimmer card-lift p-5 fade-up">
    <div class="flex items-start justify-between mb-3">
        <div class="icon-ring">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
            </svg>
        </div>
        <span class="badge {{ $up ? 'bg-violet-100 text-violet-700' : 'bg-purple-100 text-purple-700' }} text-[11px] flex items-center gap-1">
            <svg class="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $up ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"/>
            </svg>
            {{ $chg }}
        </span>
    </div>
    <p class="text-[26px] font-black text-gray-900 leading-none mb-1">{{ $val }}</p>
    <p class="text-[12.5px] font-semibold text-gray-600">{{ $label }}</p>
    <p class="text-[11.5px] text-gray-400 mt-0.5 mb-3">{{ $sub }}</p>
    <canvas id="{{ $canvasId }}" height="36"
            data-values="{{ implode(',', $sparkData) }}"
            data-color="{{ $color }}"
            data-up="{{ $up ? '1' : '0' }}"
            class="sparkline-canvas w-full"></canvas>
</div>
@endforeach
</div>

{{-- FORECAST DEMAND SUMMARY (SARIMAX) --}}
<div class="card p-6 mb-7 fade-up">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-[18px] font-black text-gray-900">Forecast Demand Summary (SARIMAX)</h2>
                <p class="text-[12px] text-gray-400 mt-0.5">7-day sales prediction · Model accuracy: {{ $dash['forecast']['accuracy'] !== null ? $dash['forecast']['accuracy'] . '%' : 'N/A' }}</p>
            </div>
        </div>
        <a href="{{ route('forecast') }}" class="flex items-center gap-2 text-[13px] font-semibold text-violet-600 hover:text-violet-700">
            Full Forecast
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-5">
        <div class="lg:col-span-3">
            <div style="height: 280px;">
                <canvas id="forecastDemandChart"></canvas>
            </div>
        </div>
        <div class="space-y-3">
            <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl p-4 border border-violet-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-violet-600 uppercase tracking-wide">Highest Demand</span>
                    <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 15l7-7 7 7"/>
                    </svg>
                </div>
                @php $hiF = $dash['forecast']['highest']; @endphp
                <p class="text-[20px] font-black text-gray-900">{{ $hiF['name'] ?? '—' }}</p>
                <p class="text-[13px] text-gray-600 mt-1">Forecasted: <span class="font-bold text-violet-700">{{ $hiF ? $hiF['per_day'] . ' ' . $hiF['unit'] . '/day' : 'No forecast yet' }}</span></p>
                <p class="text-[11px] text-gray-400 mt-1">{{ !$hiF ? 'Run the model once sales data exists' : ($hiF['change'] === null ? 'No recent sales to compare' : ($hiF['change'] >= 0 ? '↑ ' : '↓ ') . abs($hiF['change']) . '% vs last 7 days') }}</p>
            </div>
            
            <div class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-2xl p-4 border border-gray-200">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-gray-600 uppercase tracking-wide">Lowest Demand</span>
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                @php $loF = $dash['forecast']['lowest']; @endphp
                <p class="text-[20px] font-black text-gray-900">{{ $loF['name'] ?? '—' }}</p>
                <p class="text-[13px] text-gray-600 mt-1">Forecasted: <span class="font-bold text-gray-700">{{ $loF ? $loF['per_day'] . ' ' . $loF['unit'] . '/day' : 'No forecast yet' }}</span></p>
                <p class="text-[11px] text-gray-400 mt-1">{{ !$loF ? 'Needs at least two fruits forecasted' : ($loF['change'] === null ? 'No recent sales to compare' : ($loF['change'] >= 0 ? '↑ ' : '↓ ') . abs($loF['change']) . '% vs last 7 days') }}</p>
            </div>

            <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl p-4 border border-violet-200">
                <span class="text-[11px] font-bold text-violet-600 uppercase tracking-wide mb-2 block">Recommended Action</span>
                <p class="text-[13px] text-gray-700 font-semibold mb-2">Replenish Inventory</p>
                <div class="space-y-1.5">
                    @forelse($dash['forecast']['restock'] as $r)
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-gray-500">{{ $r['name'] }}</span>
                        <span class="font-bold text-gray-900">+{{ $r['short'] }} {{ $r['unit'] }}</span>
                    </div>
                    @empty
                    <div class="flex items-center justify-between text-[12px]">
                        <span class="text-gray-500">{{ $dash['forecast']['has_data'] ? 'Stock covers forecast demand' : 'No forecast yet' }}</span>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SPOILAGE PREDICTION SUMMARY (XGBoost) --}}
<div class="card p-6 mb-7 fade-up">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-violet-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-[18px] font-black text-gray-900">Spoilage Prediction Summary (XGBoost)</h2>
                <p class="text-[12px] text-gray-400 mt-0.5">Real-time spoilage risk analysis · Sensor-powered predictions</p>
            </div>
        </div>
        <a href="{{ route('spoilage') }}" class="flex items-center gap-2 text-[13px] font-semibold text-purple-600 hover:text-purple-700">
            View Details
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full min-w-full">
            <thead>
                <tr class="border-b border-gray-200">
                    <th class="text-left py-3 px-3 text-[11px] font-bold text-gray-500 uppercase tracking-wide w-[100px]">Batch ID</th>
                    <th class="text-left py-3 px-3 text-[11px] font-bold text-gray-500 uppercase tracking-wide w-[120px]">Fruit</th>
                    <th class="text-left py-3 px-3 text-[11px] font-bold text-gray-500 uppercase tracking-wide w-[100px]">Quantity</th>
                    <th class="text-left py-3 px-3 text-[11px] font-bold text-gray-500 uppercase tracking-wide w-[180px]">Spoilage Risk</th>
                    <th class="text-left py-3 px-3 text-[11px] font-bold text-gray-500 uppercase tracking-wide w-[100px]">Shelf Life</th>
                    <th class="text-left py-3 px-3 text-[11px] font-bold text-gray-500 uppercase tracking-wide w-[120px]">Status</th>
                    <th class="text-left py-3 px-3 text-[11px] font-bold text-gray-500 uppercase tracking-wide w-[120px]">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                $spoilageData = $dash['spoilage'];
                @endphp
                @foreach($spoilageData as [$batch, $fruit, $qty, $risk, $shelf, $status, $badge, $action])
                <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
                    <td class="py-4 px-3">
                        <span class="text-[13px] font-bold text-gray-700">{{ $batch }}</span>
                    </td>
                    <td class="py-4 px-3">
                        <span class="text-[13px] text-gray-600">{{ $fruit }}</span>
                    </td>
                    <td class="py-4 px-3">
                        <span class="text-[13px] font-semibold text-gray-900">{{ $qty }}</span>
                    </td>
                    <td class="py-4 px-3">
                        <div class="flex items-center gap-3">
                            <div class="w-32 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full {{ $risk >= 70 ? 'bg-purple-600' : ($risk >= 50 ? 'bg-purple-500' : ($risk >= 30 ? 'bg-violet-400' : 'bg-violet-300')) }}" 
                                     style="width: {{ $risk }}%"></div>
                            </div>
                            <span class="text-[13px] font-bold {{ $risk >= 70 ? 'text-purple-600' : ($risk >= 50 ? 'text-purple-500' : ($risk >= 30 ? 'text-violet-500' : 'text-violet-400')) }} min-w-[40px]">
                                {{ $risk }}%
                            </span>
                        </div>
                    </td>
                    <td class="py-4 px-3">
                        <span class="text-[12.5px] text-gray-500">{{ $shelf }}</span>
                    </td>
                    <td class="py-4 px-3">
                        <span class="badge bg-purple-100 text-purple-700 text-[11px]">{{ $status }}</span>
                    </td>
                    <td class="py-4 px-3">
                        <span class="text-[12.5px] font-semibold text-violet-600">{{ $action }}</span>
                    </td>
                </tr>
                @endforeach
                @if(empty($spoilageData))
                <tr><td colspan="7" class="py-6 px-3 text-center text-[12.5px] text-gray-400">No spoilage predictions yet. They appear once batches exist and the model has run.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- WEATHER IMPACT --}}
<div class="card p-6 mb-7 fade-up">
    <div class="flex items-center justify-between mb-5">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-violet-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-[18px] font-black text-gray-900">Weather Impact Analysis</h2>
                <p class="text-[12px] text-gray-400 mt-0.5">Real-time weather conditions affecting inventory quality</p>
            </div>
        </div>
        <span class="flex items-center gap-2 text-[13px] font-semibold text-gray-600">
            <svg class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            Live Data
        </span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        {{-- Current Weather --}}
        <div class="bg-violet-600 rounded-2xl p-6 text-white shadow-lg">
            <p class="text-[11px] font-bold text-white/80 uppercase tracking-wide mb-3">📍 <span id="wxCity">{{ config('services.openweather.city') }}</span> · <span id="wxStatus">Loading…</span></p>
            <div class="mb-4">
                <p class="text-[52px] font-black leading-none mb-1" id="wxTemp">--</p>
                <p class="text-[15px] font-semibold text-white/90" id="wxCond">--</p>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-4">
                <div class="bg-white/10 rounded-xl p-3 border border-white/20">
                    <p class="text-[10px] text-white/70 uppercase font-bold mb-1">💧 Humidity</p>
                    <p class="text-[18px] font-black" id="wxHumidity">--</p>
                </div>
                <div class="bg-white/10 rounded-xl p-3 border border-white/20">
                    <p class="text-[10px] text-white/70 uppercase font-bold mb-1">🌧️ Rain (1h)</p>
                    <p class="text-[18px] font-black" id="wxRain">--</p>
                </div>
                <div class="bg-white/10 rounded-xl p-3 border border-white/20">
                    <p class="text-[10px] text-white/70 uppercase font-bold mb-1">🌡️ Feels</p>
                    <p class="text-[18px] font-black" id="wxFeels">--</p>
                </div>
                <div class="bg-white/10 rounded-xl p-3 border border-white/20">
                    <p class="text-[10px] text-white/70 uppercase font-bold mb-1">🕒 Updated</p>
                    <p class="text-[18px] font-black" id="wxTime">--</p>
                </div>
            </div>
        </div>

        {{-- Impact Explanation --}}
        <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl p-5 border-2 border-violet-200">
            <h3 class="text-[14px] font-black text-gray-900 mb-3 flex items-center gap-2">
                <svg class="w-4.5 h-4.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Weather Effects on Spoilage
            </h3>
            <div class="space-y-3">
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[12.5px] font-bold text-gray-900" id="effTempTitle">Temperature</p>
                        <p class="text-[11.5px] text-gray-600 leading-snug" id="effTempText">Waiting for weather data</p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 bg-violet-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[12.5px] font-bold text-gray-900" id="effHumTitle">Humidity</p>
                        <p class="text-[11.5px] text-gray-600 leading-snug" id="effHumText">Waiting for weather data</p>
                    </div>
                </div>
                <div class="flex items-start gap-2.5">
                    <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[12.5px] font-bold text-gray-900" id="effRiskTitle">Current Risk Level</p>
                        <p class="text-[11.5px] text-gray-600 leading-snug" id="effRiskText">Waiting for weather data</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Weather Recommendations --}}
        <div class="space-y-3">
            <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-2xl p-4 border-2 border-purple-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4.5 h-4.5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="badge bg-purple-100 text-purple-700 text-[9.5px] font-bold" id="recAlertBadge">INFO</span>
                </div>
                <h4 class="text-[13px] font-black text-gray-900 mb-1.5" id="recAlertTitle">Weather Alert</h4>
                <p class="text-[11.5px] text-gray-600 leading-snug mb-2.5" id="recAlertText">Waiting for weather data</p>
                <a href="{{ route('spoilage') }}" class="btn btn-sm bg-purple-600 text-white hover:bg-purple-700 w-full text-[11px] font-bold py-1.5 text-center block">
                    View Batches
                </a>
            </div>

            <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl p-4 border-2 border-violet-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4.5 h-4.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <span class="badge bg-violet-100 text-violet-700 text-[9.5px] font-bold">ACTION</span>
                </div>
                <h4 class="text-[13px] font-black text-gray-900 mb-1.5">Temperature Control</h4>
                <p class="text-[11.5px] text-gray-600 leading-snug mb-2.5" id="recTempText">Keep storage cool and well ventilated.</p>
                <button class="btn btn-sm bg-violet-600 text-white hover:bg-violet-700 w-full text-[11px] font-bold py-1.5">
                    Guidelines
                </button>
            </div>

            <div class="bg-gradient-to-br from-violet-50 to-purple-50 rounded-2xl p-4 border-2 border-violet-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4.5 h-4.5 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    <span class="badge bg-violet-100 text-violet-700 text-[9.5px] font-bold">48H</span>
                </div>
                <h4 class="text-[13px] font-black text-gray-900 mb-1.5">Forecast Outlook</h4>
                <p class="text-[11.5px] text-gray-600 leading-snug" id="recOutlookText">Waiting for forecast data</p>
            </div>
        </div>
    </div>
</div>

{{-- CHARTS ROW 1 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
    {{-- Sales Trend --}}
    <div class="card p-6 fade-up">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="font-bold text-gray-900 text-[15px]">Sales Revenue Trend</h3>
                <p class="text-[12px] text-gray-400 mt-0.5" id="trendSub">Daily revenue — last 14 days</p>
            </div>
            <div class="flex gap-1 bg-gray-100 rounded-xl p-1">
                @foreach(['7D','14D','30D'] as $i => $p)
                <button data-range="{{ (int) $p }}" class="trend-range text-[12px] font-semibold px-3 py-1.5 rounded-lg transition-all {{ $i===1 ? 'bg-white text-violet-700 shadow-sm' : 'text-gray-400 hover:text-gray-700' }}">{{ $p }}</button>
                @endforeach
            </div>
        </div>
        <div style="height: 180px;">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    {{-- Inventory Distribution --}}
    <div class="card p-6 fade-up">
        <h3 class="font-bold text-gray-900 text-[15px] mb-1">Inventory Distribution</h3>
        <p class="text-[12px] text-gray-400 mb-4">By fruit category</p>
        <div class="flex items-center gap-6">
            <div style="width: 180px; height: 180px;">
                <canvas id="donutChart"></canvas>
            </div>
            <div class="flex-1 space-y-3">
                @forelse($dash['distribution'] as [$n,$p,$c])
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:{{ $c }}"></span>
                        <span class="text-[13.5px] text-gray-700 font-semibold">{{ $n }}</span>
                    </div>
                    <span class="text-[13.5px] font-black text-gray-900">{{ $p }}</span>
                </div>
                @empty
                <p class="text-[12.5px] text-gray-400">No stock recorded yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- CHARTS ROW 2 --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-7">
    <div class="card p-6 fade-up">
        <h3 class="font-bold text-gray-900 text-[15px] mb-1">Forecast vs Actual Sales</h3>
        <p class="text-[12px] text-gray-400 mb-4">Weekly comparison — last 5 weeks</p>
        <div style="height: 180px;">
            <canvas id="forecastBarChart"></canvas>
        </div>
    </div>
    <div class="card p-6 fade-up">
        <h3 class="font-bold text-gray-900 text-[15px] mb-1">Fruit Category Revenue</h3>
        <p class="text-[12px] text-gray-400 mb-4">This month breakdown</p>
        <div style="height: 180px;">
            <canvas id="fruitBarChart"></canvas>
        </div>
    </div>
</div>

{{-- BOTTOM: Transactions + Top Sellers --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-7">
    {{-- Transactions table --}}
    <div class="lg:col-span-2 card overflow-hidden fade-up">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <div>
                <h3 class="font-bold text-gray-900 text-[15px]">Recent Transactions</h3>
                <p class="text-[12px] text-gray-400 mt-0.5">Today's sales activity · {{ date('M d, Y') }}</p>
            </div>
            <a href="{{ route('sales') }}" class="text-[12.5px] text-violet-600 font-semibold hover:text-violet-700 flex items-center gap-1">
                View all
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <table class="tbl w-full">
            <thead><tr>
                <th class="text-left">Fruit</th>
                <th class="text-left">Qty</th>
                <th class="text-left">Total</th>
                <th class="text-left">Time</th>
                <th class="text-left">Status</th>
            </tr></thead>
            <tbody>
            @php
            $transactions = $dash['transactions'];
            @endphp
            @foreach($transactions as [$iconBg,$iconPath,$iconColor,$name,$qty,$total,$time,$status,$badge])
            <tr>
                <td>
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 {{ $iconBg }} rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
                            </svg>
                        </div>
                        <span class="font-semibold text-gray-800 text-[13.5px]">{{ $name }}</span>
                    </div>
                </td>
                <td class="text-gray-500 text-[13px]">{{ $qty }}</td>
                <td class="font-bold text-gray-900 text-[13.5px]">{{ $total }}</td>
                <td class="text-gray-400 text-[12.5px]">{{ $time }}</td>
                <td><span class="badge {{ $badge }} text-[11px]">{{ $status }}</span></td>
            </tr>
            @endforeach
            @if(empty($transactions))
            <tr><td colspan="5" class="text-center text-gray-400 text-[12.5px] py-6">No sales recorded today</td></tr>
            @endif
            </tbody>
        </table>
    </div>

    {{-- Top Sellers --}}
    <div class="card p-5 fade-up">
        <div class="flex items-center gap-2 mb-4">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-gray-100 border border-gray-200 text-gray-400 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 3l14 9-14 9V3z"/>
                </svg>
            </div>
            <h3 class="font-bold text-gray-900 text-[14px]">Top Sellers Today</h3>
        </div>
        <div class="space-y-3">
            @forelse($dash['top_sellers'] as $i => [$n,$v,$c,$p])
            <div class="flex items-center gap-3">
                <span class="text-[12px] font-black text-gray-300 w-4">{{ $i+1 }}</span>
                <div class="w-7 h-7 rounded-lg flex items-center justify-center flex-shrink-0" style="background:{{ $c }}20">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="{{ $c }}" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[12.5px] font-semibold text-gray-700">{{ $n }}</span>
                        <span class="text-[12.5px] font-bold text-gray-900">{{ $v }}</span>
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:{{ $p }}%;background:{{ $c }}"></div>
                    </div>
                </div>
            </div>
            @empty
            <p class="text-[12.5px] text-gray-400">No sales yet today</p>
            @endforelse
        </div>
    </div>
</div>

{{-- LOW STOCK ALERTS --}}
<div class="card overflow-hidden fade-up mb-7">
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="icon-ring">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div>
                <h3 class="font-bold text-gray-900 text-[15px]">Low Stock Alert</h3>
                <p class="text-[12px] text-gray-400">Immediate restocking required</p>
            </div>
        </div>
        <a href="{{ route('inventory') }}" class="text-[12.5px] text-violet-600 font-semibold hover:text-violet-700 flex items-center gap-1">
            Manage
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    <table class="tbl w-full">
        <thead><tr>
            <th class="text-left">Fruit</th><th class="text-left">Stock</th>
            <th class="text-left">Required</th><th class="text-left">Level</th>
            <th class="text-left">Status</th><th class="text-left">Action</th>
        </tr></thead>
        <tbody>
        @foreach($dash['low_stock'] as [$iconBg,$iconColor,$name,$stock,$req,$pct,$status,$badge])
        <tr>
            <td>
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 {{ $iconBg }} rounded-xl flex items-center justify-center">
                        <svg class="w-4 h-4 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-gray-800 text-[13.5px]">{{ $name }}</span>
                </div>
            </td>
            <td class="font-bold {{ $pct < 20 ? 'text-red-600' : 'text-amber-600' }} text-[13.5px]">{{ $stock }}</td>
            <td class="text-gray-500 text-[13px]">{{ $req }}</td>
            <td>
                <div class="flex items-center gap-2">
                    <div class="progress-bar w-20">
                        <div class="progress-fill {{ $pct < 20 ? 'bg-red-500' : 'bg-amber-400' }}" style="width:{{ $pct }}%"></div>
                    </div>
                    <span class="text-[12px] font-semibold text-gray-500">{{ $pct }}%</span>
                </div>
            </td>
            <td><span class="badge {{ $badge }} text-[11px]">{{ $status }}</span></td>
            <td><button class="btn btn-violet btn-sm text-[12px]" onclick="window.location='{{ route('inventory') }}'">Restock</button></td>
        </tr>
        @endforeach
        @if(empty($dash['low_stock']))
        <tr><td colspan="6" class="text-center text-gray-400 text-[12.5px] py-6">All items are above their reorder level</td></tr>
        @endif
        </tbody>
    </table>
</div>

{{-- QUICK ACTIONS --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-7 fade-up">
    @php
    $quickActions = [
        ['Add Sale', 'pos', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'g-violet'],
        ['Update Inventory', 'inventory', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'g-violet'],
        ['Generate Report', 'reports', 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'g-violet'],
        ['View Analytics', 'analytics', 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z', 'g-violet'],
    ];
    @endphp
    @foreach($quickActions as [$label, $route, $icon, $gradient])
    <a href="{{ route($route) }}" class="card p-5 hover:shadow-xl transition-all group">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 {{ $gradient }} rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-[13.5px] font-bold text-gray-900">{{ $label }}</p>
                <p class="text-[11px] text-gray-400">Quick access</p>
            </div>
        </div>
    </a>
    @endforeach
</div>

@push('scripts')
<script>
// Live weather (OpenWeather via /api/weather)
(function loadWeather() {
    const $ = id => document.getElementById(id);
    const set = (id, v) => { const el = $(id); if (el) el.textContent = v; };
    const offline = msg => {
        set('heroWeather', 'Weather unavailable');
        set('wxStatus', 'Offline');
        set('wxTemp', '--'); set('wxCond', msg);
        ['wxHumidity','wxRain','wxFeels','wxTime'].forEach(id => set(id, '--'));
        set('effTempText', msg); set('effHumText', msg); set('effRiskText', msg);
        set('recAlertText', msg); set('recOutlookText', msg);
    };

    fetch('/api/weather', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(res => {
            const w = res.current;
            if (!w) return offline(res.error ? 'Weather service not configured yet' : 'No weather data yet');

            const hot = w.temperature >= 32, humid = w.humidity >= 80, rainy = w.rainfall > 0;
            set('wxCity', w.city || res.city);
            set('wxStatus', 'Live');
            set('wxTemp', Math.round(w.temperature) + '°C');
            set('wxCond', w.description ? w.description.replace(/\b\w/g, c => c.toUpperCase()) : (w.condition || '--'));
            set('wxHumidity', Math.round(w.humidity) + '%');
            set('wxRain', w.rainfall + ' mm');
            set('wxFeels', Math.round(w.feels_like) + '°C');
            set('wxTime', (w.observed_at || '').slice(11, 16) || '--');
            set('heroWeather', Math.round(w.temperature) + '°C · ' + (w.condition || ''));

            set('effTempTitle', hot ? 'High Temperature' : 'Temperature Normal');
            set('effTempText', hot ? 'Heat accelerates ripening and spoilage' : 'No heat stress on stored produce');
            set('effHumTitle', humid ? 'High Humidity' : 'Humidity Normal');
            set('effHumText', humid ? 'Promotes mold and decay on stored fruit' : 'Humidity is within a safe range');
            const level = (hot && humid) ? 'High' : (hot || humid || rainy) ? 'Elevated' : 'Low';
            set('effRiskTitle', 'Current Risk Level: ' + level);
            set('effRiskText', level === 'Low' ? 'Routine monitoring is enough' : 'Enhanced monitoring required');

            set('recAlertBadge', level === 'Low' ? 'OK' : (level === 'High' ? 'URGENT' : 'WATCH'));
            set('recAlertTitle', humid ? 'High Humidity Warning' : (hot ? 'High Temperature Warning' : 'Conditions Normal'));
            set('recAlertText', humid || hot
                ? `${Math.round(w.humidity)}% humidity at ${Math.round(w.temperature)}°C raises spoilage risk. Check the Spoilage page for at-risk batches.`
                : 'No weather-driven spoilage alerts right now.');
            set('recTempText', hot ? 'Hot day: keep storage at 18-22°C with proper ventilation.' : 'Keep storage at 18-22°C with proper ventilation.');

            const days = (res.forecast || []).slice(0, 2);
            if (days.length) {
                const avg = k => Math.round(days.reduce((a, d) => a + Number(d[k]), 0) / days.length);
                const rain = days.reduce((a, d) => a + Number(d.rainfall || 0), 0);
                set('recOutlookText', `Next ${days.length} day(s): ~${avg('temperature')}°C, ${avg('humidity')}% humidity` + (rain > 0 ? `, ${rain.toFixed(1)} mm rain expected.` : ', no rain expected.'));
            } else {
                set('recOutlookText', 'No forecast stored yet. Run weather:sync.');
            }
        })
        .catch(() => offline('Could not load weather'));
})();

// Chart defaults
const gc = 'rgba(124,58,237,.06)';
Chart.defaults.font.size = 11;
Chart.defaults.color = '#9CA3AF';

// Sparkline charts - Fixed height rendering
document.querySelectorAll('.sparkline-canvas').forEach(canvas => {
    const ctx = canvas.getContext('2d');
    const values = canvas.dataset.values.split(',').map(Number);
    const color = canvas.dataset.color;
    const up = canvas.dataset.up === '1';
    
    // Force canvas dimensions
    const parent = canvas.parentElement;
    const width = parent.offsetWidth;
    canvas.width = width;
    canvas.height = 36;
    canvas.style.width = width + 'px';
    canvas.style.height = '36px';
    canvas.style.display = 'block';
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: values.map((_, i) => ''),
            datasets: [{
                data: values,
                borderColor: color,
                backgroundColor: color + '20',
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointRadius: 0,
                pointHoverRadius: 0,
                pointHitRadius: 0
            }]
        },
        options: {
            responsive: false,
            maintainAspectRatio: false,
            animation: {
                duration: 750,
                easing: 'easeInOutQuart'
            },
            interaction: {
                mode: 'index',
                intersect: false
            },
            plugins: { 
                legend: { display: false },
                tooltip: { enabled: false }
            },
            scales: {
                x: { 
                    display: false,
                    grid: { display: false }
                },
                y: { 
                    display: false,
                    grid: { display: false },
                    beginAtZero: false
                }
            },
            elements: {
                line: {
                    borderWidth: 2
                },
                point: {
                    radius: 0
                }
            }
        }
    });
});

// Sales Trend Chart
const trendAll = @json($dash['trend']);
const fmtK = v => v >= 1000 ? '₱' + (v/1000) + 'k' : '₱' + v;
const salesChart = new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: trendAll.labels.slice(-14),
        datasets: [{
            label: 'Revenue',
            data: trendAll.values.slice(-14),
            borderColor: '#7C3AED',
            backgroundColor: 'rgba(124,58,237,.1)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointRadius: 4,
            pointHoverRadius: 6,
            pointBackgroundColor: '#fff',
            pointBorderWidth: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => '₱' + ctx.parsed.y.toLocaleString()
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { grid: { color: gc }, border: { display: false }, ticks: { callback: fmtK } }
        }
    }
});

// Donut Chart
new Chart(document.getElementById('donutChart'), {
    type: 'doughnut',
    data: {
        labels: @json(array_column($dash['distribution'], 0)),
        datasets: [{
            data: @json(array_column($dash['distribution'], 3)),
            backgroundColor: ['#7C3AED','#8B5CF6','#A78BFA','#C4B5FD'],
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false }
        }
    }
});

// Forecast vs Actual
new Chart(document.getElementById('forecastBarChart'), {
    type: 'bar',
    data: {
        labels: @json($dash['week']['labels']),
        datasets: [
            { label: 'Forecast', data: @json($dash['week']['forecast']), backgroundColor: 'rgba(124,58,237,.3)', borderRadius: 8 },
            { label: 'Actual', data: @json($dash['week']['actual']), backgroundColor: '#7C3AED', borderRadius: 8 }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: gc }, border: { display: false }, ticks: { callback: fmtK } }
        }
    }
});

// Fruit Revenue Bar
new Chart(document.getElementById('fruitBarChart'), {
    type: 'bar',
    data: {
        labels: @json($dash['fruit_revenue']['labels']),
        datasets: [{
            label: 'Revenue',
            data: @json($dash['fruit_revenue']['values']),
            backgroundColor: ['#7C3AED','#8B5CF6','#A78BFA','#C4B5FD','#DDD6FE','#9333EA','#A855F7'],
            borderRadius: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: gc }, border: { display: false }, ticks: { callback: fmtK } }
        }
    }
});

// Forecast Demand Chart (SARIMAX)
new Chart(document.getElementById('forecastDemandChart'), {
    type: 'bar',
    data: {
        labels: @json($dash['forecast']['labels']),
        datasets: [{
            label: 'Forecasted Demand (kg/day)',
            data: @json($dash['forecast']['values']),
            backgroundColor: 'rgba(59,130,246,.8)',
            borderRadius: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => ctx.parsed.y + ' kg/day'
                }
            }
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { 
                grid: { color: gc }, 
                border: { display: false }, 
                ticks: { 
                    font: { size: 10 },
                    callback: v => v + ' kg' 
                } 
            }
        }
    }
});

// Sales trend range buttons (7D / 14D / 30D)
document.querySelectorAll('.trend-range').forEach(btn => btn.addEventListener('click', () => {
    const n = parseInt(btn.dataset.range, 10);
    salesChart.data.labels = trendAll.labels.slice(-n);
    salesChart.data.datasets[0].data = trendAll.values.slice(-n);
    salesChart.update();
    document.getElementById('trendSub').textContent = 'Daily revenue — last ' + n + ' days';
    document.querySelectorAll('.trend-range').forEach(b => {
        const on = b === btn;
        b.classList.toggle('bg-white', on);
        b.classList.toggle('text-violet-700', on);
        b.classList.toggle('shadow-sm', on);
        b.classList.toggle('text-gray-400', !on);
        b.classList.toggle('hover:text-gray-700', !on);
    });
}));
</script>
@endpush
</x-app-layout>
