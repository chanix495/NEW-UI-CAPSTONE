<x-app-layout>
<x-slot name="title">Notifications — FreshTrack</x-slot>

<div x-data="notifCenter()" x-init="init()">

{{-- CLEAN MINIMAL HEADER --}}
<div class="mb-8 fade-up">
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-[32px] font-black text-gray-900">Notification Center</h1>
        <div class="flex items-center gap-2">
            <button @click="markAllRead()" class="px-4 py-2 text-[13px] font-semibold text-gray-600 hover:text-violet-600 transition-colors">
                Mark All Read
            </button>
            <button @click="clearAll()" class="px-4 py-2 text-[13px] font-semibold text-red-500 hover:text-red-600 transition-colors">
                Clear All
            </button>
        </div>
    </div>
    <p class="text-[14px] text-gray-500">AI-powered alerts from SARIMAX, XGBoost, Weather API, and system monitoring</p>
</div>

{{-- CLEAN STATS GRID --}}
<div class="grid grid-cols-5 gap-4 mb-8 fade-up">
    @php
    $stats = [
        ['16', 'Total', 'bg-violet-50 text-violet-600 border-violet-200'],
        ['5',  'Critical', 'bg-red-50 text-red-600 border-red-200'],
        ['4',  'High', 'bg-orange-50 text-orange-600 border-orange-200'],
        ['7',  'Unread', 'bg-violet-50 text-violet-600 border-violet-200'],
        ['10', 'Today', 'bg-green-50 text-green-600 border-green-200'],
    ];
    @endphp
    @foreach($stats as [$count, $label, $classes])
    <div class="bg-white border-2 {{ $classes }} rounded-2xl p-5 hover:shadow-md transition-shadow">
        <p class="text-[32px] font-black leading-none mb-1">{{ $count }}</p>
        <p class="text-[12px] font-semibold opacity-75">{{ $label }}</p>
    </div>
    @endforeach
</div>

{{-- MINIMAL TAB FILTERS --}}
<div class="flex items-center gap-2 mb-6 pb-4 border-b-2 border-gray-100 fade-up">
    @php
    $tabs = [
        ['all', 'All', '16'],
        ['forecast', 'Forecast', '4'],
        ['spoilage', 'Spoilage', '3'],
        ['weather', 'Weather', '2'],
        ['inventory', 'Inventory', '4'],
        ['sales', 'Sales', '3'],
    ];
    @endphp
    @foreach($tabs as [$key, $label, $count])
    <button @click="activeFilter = '{{ $key }}'"
            :class="activeFilter === '{{ $key }}' ? 'bg-violet-600 text-white border-violet-600' : 'bg-white text-gray-600 hover:bg-violet-50 hover:border-violet-300 border-gray-200'"
            class="px-4 py-2 rounded-full text-[13px] font-bold transition-all border-2">
        {{ $label }} <span class="opacity-60">({{ $count }})</span>
    </button>
    @endforeach
</div>

{{-- CLEAN NOTIFICATION CARDS --}}
<div class="space-y-4 fade-up">
@php
$notifications = [
    ['forecast', 'critical', '2 min', false, 'Demand Exceeds Available Stock', 'Forecast Alert', 'Mango demand forecasted at 145 kg/day for the weekend, but current stock is only 120 kg. Predicted stockout in 20 hours.', 'Restock Now', 'inventory', 'border-red-200'],
    ['forecast', 'info', '15 min', false, 'Forecast Updated Successfully', 'Weekly Forecast', 'New 7-day sales forecast generated. Mango demand expected to rise 38% this weekend. Pineapple demand 20% below average.', 'View Forecast', 'forecast', 'border-blue-200'],
    ['spoilage', 'critical', '8 min', false, 'Critical Spoilage Warning', 'XGBoost Prediction', 'Mango batch MNG-002: 78% spoilage probability within 24 hours. 155 kg at risk. Potential loss: ₱18,290.', 'View Details', 'spoilage', 'border-red-200'],
    ['spoilage', 'high', '32 min', false, 'High Spoilage Probability', 'Durian Batch Alert', 'Durian batch DUR-112 shows 65% spoilage risk. Environmental sensors detect temp 28°C, humidity 75%. 92 kg affected.', 'Monitor', 'spoilage', 'border-orange-200'],
    ['weather', 'high', '45 min', false, 'High Humidity Warning', 'Weather Alert', 'Philippine Atmospheric Agency reports 78% humidity for next 48 hours. Enhanced monitoring recommended for Durian and Mangosteen.', 'View Weather', 'dashboard', 'border-orange-200'],
    ['inventory', 'critical', '5 min', false, 'Critical Low Stock', 'Inventory Alert', 'Pomelo stock critically low: 8 kg remaining. Daily demand: 20 kg. Stock depletes in 12 hours. Order 50 kg immediately.', 'Restock', 'inventory', 'border-red-200'],
    ['inventory', 'medium', '1.5 hrs', true, 'Low Stock Warning', 'Replenishment Needed', 'Mangosteen inventory at 14 kg (35% of required level). Reorder threshold reached. Place order for 40 kg.', 'View Inventory', 'inventory', 'border-amber-200'],
    ['sales', 'info', '3.5 hrs', true, 'Sales Milestone Achieved', 'Daily Target', "Today's sales reached ₱18,000 target at 2:30 PM — 3.5 hours ahead of schedule. Excellent performance!", 'View Sales', 'sales', 'border-green-200'],
];
@endphp

@foreach($notifications as $i => $n)
<div x-show="activeFilter === 'all' || activeFilter === '{{ $n[0] }}'"
     x-transition
     class="bg-white border-2 {{ $n[9] }} rounded-2xl overflow-hidden hover:shadow-lg transition-all"
     :class="dismissed.includes({{ $i }}) ? 'hidden' : ''"
     style="border-left-width: 6px;">
    <div class="p-6">
        <div class="flex items-start justify-between mb-3">
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-black uppercase tracking-wider text-gray-400">{{ $n[1] }}</span>
                @if(!$n[3])
                <span class="w-1.5 h-1.5 bg-violet-500 rounded-full animate-pulse"></span>
                @endif
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[12px] text-gray-400 font-medium">{{ $n[2] }} ago</span>
                <button @click="dismissed.push({{ $i }})" class="text-gray-300 hover:text-gray-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <h3 class="text-[17px] font-black text-gray-900 mb-1">{{ $n[4] }}</h3>
        <p class="text-[12px] font-semibold text-gray-500 mb-3">{{ $n[5] }}</p>
        <p class="text-[14px] text-gray-600 leading-relaxed mb-4">{{ $n[6] }}</p>

        <div class="flex items-center gap-3">
            <a href="{{ route($n[8]) }}" class="px-4 py-2 bg-violet-600 text-white text-[13px] font-bold rounded-full hover:bg-violet-700 transition-colors">
                {{ $n[7] }}
            </a>
            @if(!$n[3])
            <button @click="markRead({{ $i }})" class="px-4 py-2 text-[13px] font-semibold text-gray-500 hover:text-violet-600 transition-colors">
                Mark as Read
            </button>
            @endif
            @if($n[3])
            <span class="flex items-center gap-1.5 text-[12px] text-green-600 font-semibold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
                Read
            </span>
            @endif
        </div>
    </div>
</div>
@endforeach

{{-- Empty State --}}
<div x-show="dismissed.length >= {{ count($notifications) }}" class="text-center py-20">
    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
        </svg>
    </div>
    <p class="text-[16px] font-bold text-gray-400">All caught up!</p>
    <p class="text-[13px] text-gray-300 mt-1">No notifications to display</p>
</div>

</div>

{{-- PAGINATION --}}
<div class="flex items-center justify-between mt-8 pt-6 border-t-2 border-gray-100">
    <p class="text-[13px] text-gray-500">Showing <span class="font-bold text-gray-900">1–8</span> of <span class="font-bold text-gray-900">16</span></p>
    <div class="flex items-center gap-2">
        <button class="w-9 h-9 flex items-center justify-center rounded-full border-2 border-gray-200 text-gray-400 hover:border-violet-600 hover:text-violet-600 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button class="w-9 h-9 flex items-center justify-center rounded-full bg-violet-600 text-white text-[13px] font-black">1</button>
        <button class="w-9 h-9 flex items-center justify-center rounded-full border-2 border-gray-200 text-gray-400 hover:border-violet-600 hover:text-violet-600 transition-all">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
            </svg>
        </button>
    </div>
</div>

</div>

@push('scripts')
<script>
function notifCenter() {
    return {
        activeFilter: 'all',
        dismissed: [],
        readItems: [],
        init() {},
        markAllRead() {
            this.readItems = Array.from({ length: 8 }, (_, i) => i);
            alert('All notifications marked as read');
        },
        clearAll() {
            if (confirm('Clear all notifications?')) {
                this.dismissed = Array.from({ length: 8 }, (_, i) => i);
            }
        },
        markRead(index) {
            if (!this.readItems.includes(index)) {
                this.readItems.push(index);
            }
        }
    }
}
</script>
@endpush
</x-app-layout>
