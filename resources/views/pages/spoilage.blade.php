<x-app-layout>
<x-slot name="title">Spoilage Prediction — FreshTrack</x-slot>

{{-- ── PAGE HEADER ── --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7 fade-up">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 bg-gray-100 border border-gray-200 rounded-2xl flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div>
            <h1 class="text-[26px] font-black text-gray-900">Spoilage Prediction</h1>
            <p class="text-[13.5px] text-gray-500 mt-0.5">AI-powered spoilage risk · Sensor data updated every 30 minutes</p>
        </div>
    </div>
    <span class="flex items-center gap-2 badge badge-red px-4 py-2.5 text-[12.5px]">
        <span class="w-2 h-2 bg-red-500 rounded-full pulse-dot"></span>
        3 High Risk Items
    </span>
</div>

{{-- ── RISK SUMMARY CARDS ── --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-7 fade-up delay-1">
@php
$riskCards = [
    ['High Risk',       '3 items',  'g-rose',   'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'Immediate action'],
    ['Moderate Risk',   '5 items',  'g-orange',  'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z', 'Monitor closely'],
    ['Low Risk / Fresh','18 items', 'g-green',   'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                          'Within safe range'],
    ['Avg Spoilage',    '24%',      'g-blue',    'M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',                                              'Across inventory'],
];
@endphp
@foreach($riskCards as [$label,$val,$grad,$iconPath,$sub])
<div class="card shimmer card-lift p-5">
    <div class="icon-ring mb-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
        </svg>
    </div>
    <p class="text-[24px] font-black text-gray-900">{{ $val }}</p>
    <p class="text-[12.5px] font-semibold text-gray-600">{{ $label }}</p>
    <p class="text-[11.5px] text-gray-400 mt-0.5">{{ $sub }}</p>
</div>
@endforeach
</div>

{{-- ── CHARTS ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-7 fade-up delay-2">
    <div class="card p-6">
        <h3 class="font-bold text-gray-900 text-[15px] mb-1">Spoilage Risk by Fruit</h3>
        <p class="text-[12px] text-gray-400 mb-4">Current risk % per batch</p>
        <canvas id="spoilageBarChart" height="220"></canvas>
    </div>
    <div class="card p-6">
        <h3 class="font-bold text-gray-900 text-[15px] mb-1">Risk Timeline</h3>
        <p class="text-[12px] text-gray-400 mb-4">Projected spoilage over 7 days</p>
        <canvas id="riskTimelineChart" height="220"></canvas>
    </div>
    <div class="card p-6">
        <h3 class="font-bold text-gray-900 text-[15px] mb-1">Priority Distribution</h3>
        <p class="text-[12px] text-gray-400 mb-4">Risk level breakdown</p>
        <canvas id="priorityDonut" height="170"></canvas>
        <div class="mt-4 space-y-2">
            @foreach([['High Risk','3','#EF4444'],['Moderate','5','#F97316'],['Low Risk','8','#FACC15'],['Fresh','10','#10B981']] as [$r,$c,$col])
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" style="background:{{ $col }}"></span>
                    <span class="text-[12.5px] text-gray-600">{{ $r }}</span>
                </div>
                <span class="text-[12.5px] font-bold text-gray-800">{{ $c }} items</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ── SPOILAGE GAUGE CARDS ── --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-7 fade-up delay-3">
@php
$gauges = [
    ['Mango MNG-002',   78, 'bg-red-500',    'text-red-600',    'Critical', 'badge-red',    'border-red-400',    'Sell immediately or refrigerate',     'g-rose'],
    ['Durian DUR-112',  65, 'bg-orange-500', 'text-orange-600', 'High Risk','badge-orange', 'border-orange-400', 'Move to cooler and sell today',        'g-orange'],
    ['Mango MNG-001',   45, 'bg-amber-500',  'text-amber-600',  'Moderate', 'badge-amber',  'border-amber-400',  'Apply 15% discount today',            'g-amber'],
];
$fruitIconPath = 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4';
@endphp
@foreach($gauges as [$name,$pct,$bar,$tc,$status,$badge,$borderColor,$rec,$grad])
<div class="card p-5 border-l-4 {{ $borderColor }}">
    <div class="flex items-start justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-gray-100 border border-gray-200 text-gray-400 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $fruitIconPath }}"/>
                </svg>
            </div>
            <div>
                <p class="font-bold text-gray-900 text-[14px]">{{ $name }}</p>
                <span class="badge {{ $badge }} text-[10px]">{{ $status }}</span>
            </div>
        </div>
        <div class="text-right">
            <p class="text-[26px] font-black {{ $tc }}">{{ $pct }}%</p>
            <p class="text-[10.5px] text-gray-400">Spoilage Risk</p>
        </div>
    </div>
    <div class="progress-bar mb-3">
        <div class="{{ $bar }} h-full rounded-full transition-all" style="width:{{ $pct }}%"></div>
    </div>
    <div class="bg-gray-50 rounded-xl p-3 border border-gray-100 flex items-center gap-2">
        <svg class="w-4 h-4 text-violet-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
        </svg>
        <p class="text-[12px] font-semibold text-gray-700">{{ $rec }}</p>
    </div>
</div>
@endforeach
</div>

{{-- ── SENSOR TABLE ── --}}
<div class="card overflow-hidden fade-up delay-4">
    <div class="px-6 py-4 border-b border-gray-100">
        <h3 class="font-bold text-gray-900 text-[15px]">Sensor Data & Spoilage Analysis</h3>
        <p class="text-[12px] text-gray-400 mt-0.5">Real-time environmental monitoring with AI spoilage prediction</p>
    </div>
    <div class="overflow-x-auto">
        <table class="tbl w-full">
            <thead><tr>
                <th class="text-left">Fruit / Batch</th>
                <th class="text-left">
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10"/></svg>
                        Temp
                    </div>
                </th>
                <th class="text-left">
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                        Humidity
                    </div>
                </th>
                <th class="text-left">
                    <div class="flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/></svg>
                        Weather
                    </div>
                </th>
                <th class="text-left">Spoilage %</th>
                <th class="text-left">Priority</th>
                <th class="text-left">Status</th>
                <th class="text-left">Recommendation</th>
            </tr></thead>
            <tbody>
@php
$rows = [
    ['Mango MNG-002',  'g-rose',   '31°C','82%','Hot & Humid',78,95,'Critical','Sell immediately'],
    ['Durian DUR-112', 'g-orange', '28°C','75%','Partly Cloudy',65,82,'High Risk','Sell today'],
    ['Mango MNG-001',  'g-amber',  '26°C','70%','Sunny',45,68,'High Risk','Apply 15% off'],
    ['Lanzones LNZ-055','g-amber', '29°C','78%','Hot & Humid',38,55,'Moderate', 'Sell in 2 days'],
    ['Mangosteen MGS-078','g-teal','25°C','65%','Clear',22,38,'Moderate', 'Check tomorrow'],
    ['Pomelo POM-034', 'g-blue',   '24°C','60%','Partly Cloudy',18,30,'Low Risk', 'Monitor humidity'],
    ['Pineapple PNA-019','g-green','23°C','58%','Sunny',12,20,'Low Risk', 'Normal sales'],
    ['Banana BNA-041', 'g-emerald','22°C','55%','Clear',8, 12,'Fresh',    'Optimal condition'],
];
$statusColors = ['Critical'=>'badge-red','High Risk'=>'badge-orange','Moderate'=>'badge-amber','Low Risk'=>'badge-blue','Fresh'=>'badge-green'];
@endphp
@foreach($rows as $r)
<tr>
    <td>
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl flex items-center justify-center bg-gray-100 border border-gray-200 text-gray-400 flex-shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $fruitIconPath }}"/>
                </svg>
            </div>
            <span class="font-medium text-gray-800 text-[13px]">{{ $r[0] }}</span>
        </div>
    </td>
    <td class="font-semibold text-[13px] {{ (int)$r[2] > 28 ? 'text-red-600' : 'text-gray-600' }}">{{ $r[2] }}</td>
    <td class="text-[13px] {{ (int)$r[3] > 75 ? 'text-amber-600 font-semibold' : 'text-gray-600' }}">{{ $r[3] }}</td>
    <td class="text-gray-600 text-[12.5px] font-medium">{{ $r[4] }}</td>
    <td>
        <div class="flex items-center gap-2">
            <div class="progress-bar w-16">
                <div class="{{ $r[5]>=70?'bg-red-500':($r[5]>=40?'bg-amber-500':'bg-green-500') }} h-full rounded-full" style="width:{{ $r[5] }}%"></div>
            </div>
            <span class="text-[12.5px] font-bold {{ $r[5]>=70?'text-red-600':($r[5]>=40?'text-amber-600':'text-green-600') }}">{{ $r[5] }}%</span>
        </div>
    </td>
    <td>
        <div class="flex gap-0.5 items-end">
            @for($s=1;$s<=5;$s++)
            <div class="w-2 h-5 rounded-sm {{ $s<=round($r[6]/20) ? ($r[6]>=80?'bg-red-500':($r[6]>=60?'bg-orange-500':($r[6]>=40?'bg-amber-400':'bg-green-500'))) : 'bg-gray-200' }}"></div>
            @endfor
            <span class="text-[11px] font-bold ml-1.5 {{ $r[6]>=80?'text-red-600':($r[6]>=60?'text-orange-600':'text-green-600') }}">{{ $r[6] }}</span>
        </div>
    </td>
    <td><span class="badge {{ $statusColors[$r[7]]??'badge-gray' }} text-[11px]">{{ $r[7] }}</span></td>
    <td class="text-[12px] text-gray-500 max-w-36">{{ $r[8] }}</td>
</tr>
@endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
const gc = 'rgba(124,58,237,.06)';
new Chart(document.getElementById('spoilageBarChart'), {
    type: 'bar',
    data: {
        labels: ['Mango-002','Durian-112','Mango-001','Lanzones','Mangosteen','Pomelo','Pineapple','Banana'],
        datasets: [{ label: 'Spoilage %', data: [78,65,45,38,22,18,12,8], backgroundColor: ['#EF4444','#F97316','#F97316','#F59E0B','#F59E0B','#84CC16','#22C55E','#10B981'], borderRadius: 6 }]
    },
    options: { indexAxis: 'y', responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { color: gc }, ticks: { font: { size: 10 }, color: '#9CA3AF', callback: v => v + '%' }, max: 100 }, y: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#6B7280' } } } }
});
new Chart(document.getElementById('riskTimelineChart'), {
    type: 'line',
    data: {
        labels: ['Today','Day 2','Day 3','Day 4','Day 5','Day 6','Day 7'],
        datasets: [
            { label: 'Mango',    data: [78,88,95,100,100,100,100], borderColor: '#EF4444', tension: .3, fill: false, borderWidth: 2, pointRadius: 3 },
            { label: 'Durian',   data: [65,72,80,88,93,97,100],    borderColor: '#F97316', tension: .3, fill: false, borderWidth: 2, pointRadius: 3 },
            { label: 'Lanzones', data: [38,45,52,60,68,76,84],     borderColor: '#F59E0B', tension: .3, fill: false, borderWidth: 2, pointRadius: 3 },
            { label: 'Banana',   data: [8,10,13,17,22,28,35],      borderColor: '#10B981', tension: .3, fill: false, borderWidth: 2, pointRadius: 3 },
        ]
    },
    options: { responsive: true, plugins: { legend: { position: 'top', labels: { font: { size: 10 } } } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF' } }, y: { grid: { color: gc }, border: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF', callback: v => v + '%' }, max: 100 } } }
});
new Chart(document.getElementById('priorityDonut'), {
    type: 'doughnut',
    data: { labels: ['High Risk','Moderate','Low Risk','Fresh'], datasets: [{ data: [3,5,8,10], backgroundColor: ['#EF4444','#F97316','#FACC15','#10B981'], borderWidth: 0, hoverOffset: 10 }] },
    options: { responsive: true, cutout: '68%', plugins: { legend: { display: false } } }
});
</script>
@endpush
</x-app-layout>
