<x-app-layout>
<x-slot name="title">Notifications — FreshTrack</x-slot>

<div x-data="notifCenter()" x-init="init()">

{{-- HEADER MATCHING DASHBOARD STYLE --}}
<div class="mb-6 fade-up">
    <div class="flex items-center justify-between mb-2">
        <h1 class="text-[32px] font-black text-gray-900">Notification Center</h1>
        <div class="flex items-center gap-3">
            <button @click="markAllRead()" 
                    class="btn btn-outline btn-sm hover:border-violet-600 hover:text-violet-600">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Mark All Read
            </button>
            <button @click="clearAll()" 
                    class="btn btn-sm"
                    style="background: #fff; border: 1.5px solid #E5E7EB; color: #EF4444;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Clear All
            </button>
        </div>
    </div>
    <p class="text-[14px] text-gray-500">AI-powered alerts from SARIMAX, XGBoost, Weather API, and system monitoring</p>
</div>

{{-- STATS CARDS MATCHING DASHBOARD KPI STYLE --}}
<div class="grid grid-cols-5 gap-4 mb-6 fade-up delay-1">
    @php
    $stats = [
        ['16', 'Total Notifications', 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ['5',  'Critical', 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
        ['4',  'High Priority', 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['7',  'Unread', 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['10', 'Today', 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
    ];
    @endphp
    @foreach($stats as [$count, $label, $icon])
    <div class="card shimmer card-lift p-5">
        <div class="flex items-start justify-between mb-3">
            <div class="icon-ring">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/>
                </svg>
            </div>
        </div>
        <p class="text-[26px] font-black text-gray-900 leading-none mb-1">{{ $count }}</p>
        <p class="text-[12.5px] font-semibold text-gray-600">{{ $label }}</p>
    </div>
    @endforeach
</div>

{{-- TAB FILTERS MATCHING DASHBOARD STYLE --}}
<div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-200 fade-up delay-2">
    <div class="flex items-center gap-2">
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
                :class="activeFilter === '{{ $key }}' ? 'btn-violet text-white' : 'btn-outline text-gray-600'"
                class="btn btn-sm px-4">
            {{ $label }} <span class="opacity-60">({{ $count }})</span>
        </button>
        @endforeach
    </div>
    
    <div class="flex items-center gap-3">
        <div class="relative">
            <input type="text" 
                   x-model="searchQuery"
                   @input="filterNotifications()"
                   placeholder="Search notifications..."
                   class="inp text-[13px] py-2 pl-10 pr-4 w-64">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <select x-model="sortBy" @change="sortNotifications()" 
                class="inp text-[13px] py-2 px-3 w-40">
            <option value="recent">Most Recent</option>
            <option value="priority">Priority</option>
            <option value="unread">Unread First</option>
        </select>
    </div>
</div>

{{-- NOTIFICATION CARDS MATCHING DASHBOARD CARD STYLE --}}
<div class="space-y-4 fade-up delay-3">
@php
$notifications = [
    ['forecast', 'CRITICAL', '2 min', false, 'Demand Exceeds Available Stock', 'SARIMAX Forecast Alert', 'Mango demand forecasted at 145 kg/day for the weekend, but current stock is only 120 kg. Predicted stockout in 20 hours.', 'Restock Now', 'inventory'],
    ['forecast', 'INFO', '15 min', false, 'Forecast Updated Successfully', 'Weekly SARIMAX Forecast', 'New 7-day sales forecast generated. Mango demand expected to rise 38% this weekend. Pineapple demand 20% below average.', 'View Forecast', 'forecast'],
    ['spoilage', 'CRITICAL', '8 min', false, 'Critical Spoilage Warning', 'XGBoost AI Prediction', 'Mango batch MNG-002: 78% spoilage probability within 24 hours. 155 kg at risk. Potential loss: ₱18,290.', 'View Details', 'spoilage'],
    ['spoilage', 'HIGH', '32 min', false, 'High Spoilage Probability', 'XGBoost Durian Alert', 'Durian batch DUR-112 shows 65% spoilage risk. Environmental sensors detect temp 28°C, humidity 75%. 92 kg affected.', 'Monitor Batch', 'spoilage'],
    ['weather', 'HIGH', '45 min', false, 'High Humidity Warning', 'Weather API Alert', 'Philippine Atmospheric Agency reports 78% humidity for next 48 hours. Enhanced monitoring recommended for Durian and Mangosteen.', 'View Weather', 'dashboard'],
    ['inventory', 'CRITICAL', '5 min', false, 'Critical Low Stock', 'Inventory System Alert', 'Pomelo stock critically low: 8 kg remaining. Daily demand: 20 kg. Stock depletes in 12 hours. Order 50 kg immediately.', 'Restock Now', 'inventory'],
    ['inventory', 'MEDIUM', '1.5 hrs', true, 'Low Stock Warning', 'Replenishment Needed', 'Mangosteen inventory at 14 kg (35% of required level). Reorder threshold reached. Place order for 40 kg.', 'View Inventory', 'inventory'],
    ['sales', 'INFO', '3.5 hrs', true, 'Sales Milestone Achieved', 'Daily Sales Target', "Today's sales reached ₱18,000 target at 2:30 PM — 3.5 hours ahead of schedule. Excellent performance!", 'View Sales', 'sales'],
];
@endphp

@foreach($notifications as $i => $n)
<div x-show="activeFilter === 'all' || activeFilter === '{{ $n[0] }}'"
     x-transition
     class="card shimmer card-lift"
     :class="dismissed.includes({{ $i }}) ? 'hidden' : ''">
    <div class="p-5">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <span class="badge badge-violet text-[10px] uppercase tracking-wide font-bold">
                    {{ $n[1] }}
                </span>
                @if(!$n[3])
                <span class="flex items-center gap-1.5">
                    <span class="w-2 h-2 bg-violet-600 rounded-full pulse-dot"></span>
                    <span class="text-[10px] font-bold text-violet-600">NEW</span>
                </span>
                @endif
                <span class="text-[11px] text-gray-400 font-medium">{{ $n[5] }}</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-[12px] text-gray-400 font-medium flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ $n[2] }} ago
                </span>
                <button @click.stop="dismissed.push({{ $i }})" 
                        class="text-gray-300 hover:text-red-500 transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <h3 class="text-[17px] font-black text-gray-900 mb-2">{{ $n[4] }}</h3>
        <p class="text-[14px] text-gray-600 leading-relaxed mb-4">{{ $n[6] }}</p>

        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route($n[8]) }}" class="btn btn-violet btn-sm">
                    {{ $n[7] }}
                </a>
                @if(!$n[3])
                <button @click.stop="markRead({{ $i }})" 
                        class="btn btn-outline btn-sm">
                    Mark as Read
                </button>
                @endif
            </div>
            
            @if($n[3])
            <span class="flex items-center gap-2 text-[12px] text-gray-400 font-semibold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Read
            </span>
            @endif
        </div>
    </div>
</div>
@endforeach

{{-- EMPTY STATE --}}
<div x-show="dismissed.length >= {{ count($notifications) }}" 
     x-transition
     class="card text-center py-16">
    <div class="w-16 h-16 bg-violet-50 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
    </div>
    <p class="text-[18px] font-bold text-gray-900 mb-1">All Caught Up!</p>
    <p class="text-[14px] text-gray-500 mb-5">No notifications to display</p>
    <button @click="dismissed = []" class="btn btn-violet btn-sm">
        Restore Notifications
    </button>
</div>

</div>

{{-- PAGINATION --}}
<div class="flex items-center justify-between mt-7 pt-5 border-t border-gray-200 fade-up delay-4">
    <p class="text-[13px] text-gray-500">
        Showing <span class="font-bold text-gray-900">1–8</span> of <span class="font-bold text-gray-900">16</span> notifications
    </p>
    <div class="flex items-center gap-2">
        <button class="btn btn-outline btn-sm w-9 h-9 p-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
        <button class="btn btn-violet btn-sm w-9 h-9 p-0">1</button>
        <button class="btn btn-outline btn-sm w-9 h-9 p-0">2</button>
        <button class="btn btn-outline btn-sm w-9 h-9 p-0">
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
        sortBy: 'recent',
        searchQuery: '',
        
        init() {},
        
        markAllRead() {
            this.readItems = Array.from({ length: 8 }, (_, i) => i);
            alert('✓ All notifications marked as read');
        },
        
        clearAll() {
            if (confirm('Clear all notifications? This action cannot be undone.')) {
                this.dismissed = Array.from({ length: 8 }, (_, i) => i);
            }
        },
        
        markRead(index) {
            if (!this.readItems.includes(index)) {
                this.readItems.push(index);
            }
        },
        
        sortNotifications() {},
        filterNotifications() {}
    }
}
</script>
@endpush
</x-app-layout>
