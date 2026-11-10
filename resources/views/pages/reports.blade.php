<x-app-layout>
<x-slot name="title">Reports — FreshTrack</x-slot>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7 fade-up">
    <div>
        <h1 class="text-[26px] font-black text-gray-900">Reports</h1>
        <p class="text-[13.5px] text-gray-500 mt-0.5">Comprehensive business performance reports</p>
    </div>
    <div class="flex items-center gap-2.5" id="report-buttons">
        <button onclick="window.reportController.exportPDF()" class="btn btn-outline btn-md text-[12.5px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            PDF
        </button>
        <button onclick="window.reportController.printReport()" class="btn btn-outline btn-md text-[12.5px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Print
        </button>
        <button onclick="window.reportController.exportExcel()" class="btn btn-outline btn-md text-[12.5px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            Excel
        </button>
        <button onclick="window.reportController.downloadCSV()" class="btn btn-violet btn-md text-[12.5px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Download CSV
        </button>
    </div>
</div>

<div class="card overflow-hidden fade-up delay-1" x-data="reportsDataController()">
    {{-- Tabs --}}
    <div class="flex gap-1 p-2 bg-gray-50 border-b border-gray-100">
    @foreach([['sales','money','Sales'],['forecast','chart','Forecast'],['inventory','box','Inventory'],['spoilage','warn','Spoilage'],['profit','gem','Profit']] as [$t,$icon,$l])
        <button @click="switchTab('{{ $t }}')"
                :class="tab==='{{ $t }}' ? 'g-violet text-white shadow-md' : 'text-gray-500 hover:bg-white hover:shadow-sm'"
                class="flex-1 py-2.5 px-3 rounded-xl text-[12.5px] font-semibold transition-all flex items-center justify-center gap-1.5">
            @if($icon==='money')<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($icon==='chart')<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            @elseif($icon==='box')<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            @elseif($icon==='warn')<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            @elseif($icon==='gem')<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            @endif
            {{ $l }}
        </button>
        @endforeach
    </div>

    {{-- SALES --}}
    <div x-show="tab==='sales'" class="p-6">
        {{-- Loading Indicator --}}
        <div x-show="loading && tab==='sales'" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-violet-600"></div>
            <span class="ml-3 text-gray-600">Loading sales data...</span>
        </div>

        <div x-show="!loading || tab!=='sales'">
        <div class="flex items-center justify-between mb-6">
            <div><h3 class="font-bold text-gray-900 text-[16px]">Sales Performance Report</h3><p class="text-[12px] text-gray-400 mt-0.5" x-text="periodLabel('sales')"></p></div>
            @include('pages.partials.reports-period')
        </div>
        <p x-show="errors.sales" x-text="errors.sales" class="text-[13px] text-red-500 mb-4"></p>
        <div class="grid grid-cols-4 gap-4 mb-6">
            <template x-for="card in salesCards" :key="card.label">
            <div class="bg-[#F5F3FF] rounded-2xl p-4 border border-violet-100 text-center">
                <p class="text-[22px] font-black text-gray-900" x-text="card.value"></p>
                <p class="text-[12px] text-gray-500 mt-0.5" x-text="card.label"></p>
                <span class="badge badge-violet text-[10px] mt-2" x-text="card.badge"></span>
            </div>
            </template>
        </div>
        <canvas id="salesReportChart" height="80" class="mb-6"></canvas>
        <table class="tbl w-full">
            <thead><tr><th class="text-left">Fruit</th><th class="text-left">Units Sold</th><th class="text-left">Revenue</th><th class="text-left">% of Total</th><th class="text-left">Trend</th></tr></thead>
            <tbody>
            <template x-for="r in (sales?.top_products || [])" :key="r.product_name">
            <tr><td class="font-medium text-gray-800" x-text="r.product_name"></td><td class="text-gray-500 text-[13px]" x-text="num(r.quantity_sold) + ' ' + (r.unit || '')"></td><td class="font-bold text-gray-900" x-text="peso(r.total_sales)"></td><td class="text-gray-500" x-text="pct(r.share_percent)"></td><td class="font-black text-[16px]" :class="r.trend==='up' ? 'text-green-600' : (r.trend==='down' ? 'text-red-500' : 'text-blue-600')" x-text="r.trend==='up' ? '↑' : (r.trend==='down' ? '↓' : '→')"></td></tr>
            </template>
            <tr x-show="sales && !sales.error && !(sales.top_products || []).length"><td colspan="5" class="text-center text-gray-400 text-[13px] py-8">No sales recorded for this period.</td></tr>
            </tbody>
        </table>
        </div>
    </div>

    {{-- FORECAST --}}
    <div x-show="tab==='forecast'" class="p-6">
        <div x-show="loading && tab==='forecast'" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-violet-600"></div>
            <span class="ml-3 text-gray-600">Loading forecast data...</span>
        </div>

        <div x-show="!loading || tab!=='forecast'">
        <div class="flex items-center justify-between mb-5">
            <div><h3 class="font-bold text-gray-900 text-[16px]">Forecast Accuracy Report</h3><p class="text-[12px] text-gray-400 mt-0.5" x-text="periodLabel('forecast')"></p></div>
            @include('pages.partials.reports-period')
        </div>
        <p x-show="errors.forecast" x-text="errors.forecast" class="text-[13px] text-red-500 mb-4"></p>
        <div class="grid grid-cols-4 gap-4 mb-6">
            <template x-for="card in forecastCards" :key="card.label">
            <div class="bg-[#F5F3FF] rounded-2xl p-4 border border-violet-100 text-center"><p class="text-[22px] font-black text-gray-900" x-text="card.value"></p><p class="text-[12px] text-gray-500 mt-0.5" x-text="card.label"></p><span class="badge badge-violet text-[10px] mt-2" x-text="card.badge"></span></div>
            </template>
        </div>
        <canvas id="forecastAccReport" height="100" class="mb-6"></canvas>
        <table class="tbl w-full">
            <thead><tr><th class="text-left">Fruit</th><th class="text-left">Forecast</th><th class="text-left">Actual</th><th class="text-left">Error</th><th class="text-left">Accuracy</th></tr></thead>
            <tbody>
            <template x-for="r in (forecast?.fruits || [])" :key="r.fruit">
            <tr><td class="font-medium text-gray-800" x-text="r.fruit"></td><td class="text-gray-500" x-text="peso(r.forecast)"></td><td class="text-gray-500" x-text="peso(r.actual)"></td><td class="font-semibold" :class="r.error_percent !== null && Math.abs(r.error_percent) <= 5 ? 'text-green-600' : 'text-amber-600'" x-text="r.error_percent === null ? '—' : (r.error_percent > 0 ? '+' : '') + num(r.error_percent, 1) + '%'"></td><td><span class="badge badge-green text-[11px]" x-text="pct(r.accuracy)"></span></td></tr>
            </template>
            <tr x-show="forecast && !forecast.error && !(forecast.fruits || []).length"><td colspan="5" class="text-center text-gray-400 text-[13px] py-8">No finished forecast days in this period yet. Forecasts are scored against actual sales once the day has passed.</td></tr>
            </tbody>
        </table>
        </div>
    </div>

    {{-- INVENTORY --}}
    <div x-show="tab==='inventory'" class="p-6">
        {{-- Loading Indicator --}}
        <div x-show="loading && tab==='inventory'" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-violet-600"></div>
            <span class="ml-3 text-gray-600">Loading inventory data...</span>
        </div>

        <div x-show="!loading || tab!=='inventory'">
        <div class="flex items-center justify-between mb-5">
            <div><h3 class="font-bold text-gray-900 text-[16px]">Inventory Turnover Report</h3><p class="text-[12px] text-gray-400 mt-0.5" x-text="periodLabel('inventory')"></p></div>
            @include('pages.partials.reports-period')
        </div>
        <p x-show="errors.inventory" x-text="errors.inventory" class="text-[13px] text-red-500 mb-4"></p>
        <canvas id="invTurnChart" height="100" class="mb-6"></canvas>
        <table class="tbl w-full">
            <thead><tr><th class="text-left">Fruit</th><th class="text-left">Opening Stock</th><th class="text-left">Received</th><th class="text-left">Sold</th><th class="text-left">Closing</th><th class="text-left">Turnover</th></tr></thead>
            <tbody>
            <template x-for="r in (inventory?.items || [])" :key="r.product_name">
            <tr><td class="font-medium text-gray-800" x-text="r.product_name"></td><td class="text-gray-500" x-text="num(r.opening_stock) + ' ' + (r.unit || '')"></td><td class="text-gray-500" x-text="num(r.received) + ' ' + (r.unit || '')"></td><td class="font-semibold text-green-600" x-text="num(r.sold) + ' ' + (r.unit || '')"></td><td class="text-gray-500" x-text="num(r.closing_stock) + ' ' + (r.unit || '')"></td><td class="font-black text-violet-700" x-text="r.turnover === null ? '—' : num(r.turnover, 1) + 'x'"></td></tr>
            </template>
            <tr x-show="inventory && !inventory.error && !(inventory.items || []).length"><td colspan="6" class="text-center text-gray-400 text-[13px] py-8">No stock or stock movement recorded for this period.</td></tr>
            </tbody>
        </table>
        </div>
    </div>

    {{-- SPOILAGE --}}
    <div x-show="tab==='spoilage'" class="p-6">
        {{-- Loading Indicator --}}
        <div x-show="loading && tab==='spoilage'" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-violet-600"></div>
            <span class="ml-3 text-gray-600">Loading spoilage data...</span>
        </div>

        <div x-show="!loading || tab!=='spoilage'">
        <div class="flex items-center justify-between mb-5">
            <div><h3 class="font-bold text-gray-900 text-[16px]">Spoilage Loss Report</h3><p class="text-[12px] text-gray-400 mt-0.5" x-text="periodLabel('spoilage')"></p></div>
            @include('pages.partials.reports-period')
        </div>
        <p x-show="errors.spoilage" x-text="errors.spoilage" class="text-[13px] text-red-500 mb-4"></p>
        <div class="grid grid-cols-4 gap-4 mb-6">
            <template x-for="card in spoilageCards" :key="card.label">
            <div class="bg-[#F5F3FF] rounded-2xl p-4 border border-violet-100 text-center"><p class="text-[22px] font-black text-gray-900" x-text="card.value"></p><p class="text-[12px] text-gray-500 mt-0.5" x-text="card.label"></p></div>
            </template>
        </div>
        <table class="tbl w-full">
            <thead><tr><th class="text-left">Fruit</th><th class="text-left">Spoilage Qty</th><th class="text-left">Loss Value</th><th class="text-left">Spoilage %</th><th class="text-left">Main Cause</th><th class="text-left">Status</th></tr></thead>
            <tbody>
            <template x-for="r in (spoilage?.fruits || [])" :key="r.fruit">
            <tr><td class="font-medium text-gray-800" x-text="r.fruit"></td><td class="text-red-600 font-semibold" x-text="num(r.quantity) + ' ' + (r.unit || '')"></td><td class="font-bold text-gray-900" x-text="peso(r.loss_value)"></td><td>
                <div class="flex items-center gap-2"><div class="progress-bar w-12"><div :class="r.spoilage_percent>30 ? 'bg-red-500' : (r.spoilage_percent>15 ? 'bg-amber-500' : 'bg-green-500')" class="h-full rounded-full" :style="'width:' + Math.min(r.spoilage_percent, 100) + '%'"></div></div><span class="text-[12.5px] font-bold text-gray-800" x-text="pct(r.spoilage_percent)"></span></div>
            </td><td class="text-gray-500 text-[12.5px]" x-text="r.cause"></td><td><span :class="r.spoilage_percent>30 ? 'badge-red' : (r.spoilage_percent>10 ? 'badge-amber' : 'badge-green')" class="badge text-[11px]" x-text="r.spoilage_percent>30 ? 'Critical' : (r.spoilage_percent>10 ? 'Moderate' : 'Low Risk')"></span></td></tr>
            </template>
            <tr x-show="spoilage && !spoilage.error && !(spoilage.fruits || []).length"><td colspan="6" class="text-center text-gray-400 text-[13px] py-8">No spoilage or wastage recorded for this period.</td></tr>
            </tbody>
        </table>
        </div>
    </div>

    {{-- PROFIT --}}
    <div x-show="tab==='profit'" class="p-6">
        {{-- Loading Indicator --}}
        <div x-show="loading && tab==='profit'" class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-violet-600"></div>
            <span class="ml-3 text-gray-600">Loading profit data...</span>
        </div>

        <div x-show="!loading || tab!=='profit'">
        <div class="flex items-center justify-between mb-5">
            <div><h3 class="font-bold text-gray-900 text-[16px]">Profit &amp; Loss Report <span x-text="profit?.period ? '· ' + profit.period.label : ''"></span></h3></div>
            @include('pages.partials.reports-period')
        </div>
        <p x-show="errors.profit" x-text="errors.profit" class="text-[13px] text-red-500 mb-4"></p>
        <div class="grid grid-cols-4 gap-4 mb-6">
            <template x-for="card in profitCards" :key="card.label">
            <div class="bg-[#F5F3FF] rounded-2xl p-4 border border-violet-100 text-center"><p class="text-[22px] font-black" :class="card.color" x-text="card.value"></p><p class="text-[12px] text-gray-500 mt-0.5" x-text="card.label"></p></div>
            </template>
        </div>
        <p x-show="profit?.summary && profit.summary.cost_coverage !== null && profit.summary.cost_coverage < 100" class="text-[12px] text-gray-400 mb-4" x-text="'Cost is known for ' + num(profit?.summary?.cost_coverage, 1) + '% of this period\'s sales (sales without a linked stock batch have no cost recorded), so profit may be overstated.'"></p>
        <canvas id="profitTrendChart" height="100"></canvas>
        </div>
    </div>
</div>


@push('scripts')
<script>
// Reports Data Controller: every figure on this page comes from /api/reports/*
function reportsDataController() {
    const charts = {};   // Chart.js instances (kept out of Alpine's reactive state)
    const gc = 'rgba(124,58,237,.06)';
    const palette = ['#7C3AED', '#10B981', '#3B82F6', '#EF4444', '#EC4899', '#14B8A6', '#F59E0B', '#8B5CF6'];

    const peso = (v, d = 0) => '₱' + Number(v || 0).toLocaleString('en-PH', { minimumFractionDigits: d, maximumFractionDigits: d });
    const num = (v, d = 2) => Number(v || 0).toLocaleString('en-US', { maximumFractionDigits: d });
    const pct = (v, d = 1) => (v === null || v === undefined) ? '—' : num(v, d) + '%';
    const arrow = (c) => (c === null || c === undefined) ? '—' : (c >= 0 ? '▲ ' : '▼ ') + num(Math.abs(c), 1) + '%';
    const esc = (s) => String(s ?? '').replace(/[&<>"]/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[ch]));
    const kilo = (v) => '₱' + (v / 1000).toFixed(0) + 'k';

    const draw = (id, config) => {
        const el = document.getElementById(id);
        if (!el) return;
        if (charts[id]) charts[id].destroy();
        charts[id] = new Chart(el, config);
    };

    return {
        tab: 'sales',
        loading: false,
        period: @json($defaultPeriod),
        periods: @json($periods),
        sales: null, forecast: null, inventory: null, spoilage: null, profit: null,
        errors: { sales: '', forecast: '', inventory: '', spoilage: '', profit: '' },
        peso, num, pct,

        async init() {
            window.reportController = this;   // the header buttons call into this controller
            await this.load('sales');
        },

        periodLabel(tab) {
            return this[tab]?.period?.label || '';
        },

        async switchTab(tab) {
            this.tab = tab;
            if (!this[tab]) await this.load(tab);
            else this.$nextTick(() => this.render(tab));
        },

        // A new period invalidates everything that was loaded for the old one
        async changePeriod() {
            const current = this.tab;
            ['sales', 'forecast', 'inventory', 'spoilage', 'profit'].forEach((t) => { if (t !== current) this[t] = null; });
            await this.load(current);
        },

        async load(tab) {
            const path = { sales: 'sales', forecast: 'forecast', inventory: 'inventory', spoilage: 'spoilage', profit: 'profit-loss' }[tab];
            this.loading = true;
            this.errors[tab] = '';
            try {
                const res = await fetch(`/api/reports/${path}?period=${encodeURIComponent(this.period)}`, { headers: { 'Accept': 'application/json' } });
                const data = await res.json().catch(() => ({}));
                if (!res.ok) throw new Error(data.message || 'Could not load this report. Please try again.');
                this[tab] = data;
            } catch (e) {
                this.errors[tab] = e.message;
                this[tab] = { error: true, period: null };
            } finally {
                this.loading = false;
            }
            this.$nextTick(() => this.render(tab));
        },

        // ---- summary cards ---------------------------------------------------
        get salesCards() {
            const s = this.sales;
            if (!s || s.error) return ['Total Revenue', 'Transactions', 'Avg Daily Sales', 'Best Fruit'].map((label) => ({ label, value: '—', badge: '—' }));
            return [
                { label: 'Total Revenue', value: peso(s.summary.total_sales), badge: arrow(s.change.revenue) },
                { label: 'Transactions', value: num(s.summary.total_transactions, 0), badge: arrow(s.change.transactions) },
                { label: 'Avg Daily Sales', value: peso(s.summary.avg_daily_sales), badge: arrow(s.change.avg_daily) },
                { label: 'Best Fruit', value: s.summary.best_product ? s.summary.best_product.name : '—', badge: s.summary.best_product ? pct(s.summary.best_product.share_percent) + ' of sales' : '—' },
            ];
        },
        get forecastCards() {
            const f = this.forecast;
            const sm = f && !f.error ? f.summary : null;
            const verified = sm && sm.model_status === 'success';
            return [
                { label: 'Overall Accuracy', value: sm ? pct(sm.overall_accuracy ?? sm.model_accuracy) : '—', badge: sm && sm.overall_accuracy !== null ? 'vs actual sales' : (sm && sm.model_accuracy !== null ? 'Model validation' : 'No data yet') },
                { label: 'MAPE Score', value: sm ? pct(sm.mape) : '—', badge: sm && sm.mape !== null ? 'Model validation' : 'No data yet' },
                { label: 'Model Version', value: sm && sm.model ? sm.model : '—', badge: sm && sm.trained_at ? 'Trained ' + sm.trained_at : 'Not trained yet' },
                { label: 'Training Data', value: sm && sm.training_rows ? num(sm.training_rows, 0) + ' records' : '—', badge: verified ? 'Verified' : 'Unverified' },
            ];
        },
        get spoilageCards() {
            const s = this.spoilage;
            if (!s || s.error) return ['Total Spoilage', 'Spoilage Rate', 'Financial Loss', 'Prevented Loss'].map((label) => ({ label, value: '—' }));
            const m = s.summary;
            return [
                { label: 'Total Spoilage', value: num(m.total_spoilage_qty) + ' ' + m.unit },
                { label: 'Spoilage Rate', value: pct(m.spoilage_rate) },
                { label: 'Financial Loss', value: peso(m.financial_loss) },
                { label: 'Prevented Loss', value: peso(m.prevented_loss) },
            ];
        },
        get profitCards() {
            const p = this.profit;
            const m = p && !p.error ? p.summary : null;
            return [
                { label: 'Gross Revenue', value: m ? peso(m.total_revenue) : '—', color: 'text-violet-600' },
                { label: 'Total COGS', value: m ? peso(m.cost_of_goods_sold) : '—', color: 'text-red-600' },
                { label: 'Gross Profit', value: m ? peso(m.gross_profit) : '—', color: 'text-green-600' },
                { label: 'Net Margin', value: m ? pct(m.gross_margin_percentage) : '—', color: 'text-blue-600' },
            ];
        },

        // ---- charts ----------------------------------------------------------
        render(tab) {
            const d = this[tab];
            if (!d || d.error) return;
            if (tab === 'sales') {
                const rows = d.sales_by_date;
                draw('salesReportChart', { type: 'line', data: { labels: rows.map((r) => r.label), datasets: [{ label: 'Daily Revenue', data: rows.map((r) => r.total_sales), borderColor: '#7C3AED', backgroundColor: 'rgba(124,58,237,.07)', borderWidth: 2.5, fill: true, tension: .4, pointRadius: 0 }] },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false }, ticks: { font: { size: 9 }, color: '#9CA3AF', maxTicksLimit: 15 } }, y: { beginAtZero: true, grid: { color: gc }, border: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF', callback: kilo } } } } });
            } else if (tab === 'forecast') {
                const w = d.weekly;
                draw('forecastAccReport', { type: 'line', data: { labels: w.labels, datasets: w.series.map((s, i) => ({ label: s.fruit, data: s.data, borderColor: palette[i % palette.length], tension: .4, fill: false, borderWidth: 2, pointRadius: 4, spanGaps: true })) },
                    options: { responsive: true, plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } }, scales: { x: { grid: { display: false } }, y: { min: 0, max: 100, grid: { color: gc }, border: { display: false }, ticks: { callback: (v) => v + '%', font: { size: 10 }, color: '#9CA3AF' } } } } });
            } else if (tab === 'inventory') {
                const rows = d.items.filter((r) => r.turnover !== null);
                draw('invTurnChart', { type: 'bar', data: { labels: rows.map((r) => r.product_name), datasets: [{ label: 'Turnover Rate', data: rows.map((r) => r.turnover), backgroundColor: rows.map((_, i) => palette[i % palette.length]), borderRadius: 10 }] },
                    options: { responsive: true, plugins: { legend: { display: false } }, scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: gc }, border: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF', callback: (v) => v + 'x' } } } } });
            } else if (tab === 'profit') {
                const m = d.monthly;
                draw('profitTrendChart', { type: 'bar', data: { labels: m.map((r) => r.label), datasets: [{ label: 'Revenue', data: m.map((r) => r.revenue), backgroundColor: 'rgba(124,58,237,.65)', borderRadius: 8 }, { label: 'COGS', data: m.map((r) => r.cogs), backgroundColor: 'rgba(249,115,22,.65)', borderRadius: 8 }, { label: 'Profit', data: m.map((r) => r.profit), backgroundColor: 'rgba(16,185,129,.65)', borderRadius: 8 }] },
                    options: { responsive: true, plugins: { legend: { position: 'top', labels: { font: { size: 11 } } } }, scales: { x: { grid: { display: false } }, y: { grid: { color: gc }, border: { display: false }, ticks: { font: { size: 10 }, color: '#9CA3AF', callback: kilo } } } } });
            }
        },

        // ---- exports ---------------------------------------------------------
        // One table-shaped description of the current tab feeds CSV, Excel and the filename.
        exportModel() {
            const d = this[this.tab];
            if (!d || d.error) return null;
            const label = d.period ? d.period.label : '';
            switch (this.tab) {
                case 'sales': return {
                    title: 'Sales Report', file: 'sales_report', label,
                    summary: [['Total Revenue', peso(d.summary.total_sales)], ['Transactions', d.summary.total_transactions], ['Average Daily Sales', peso(d.summary.avg_daily_sales)], ['Average Transaction', peso(d.summary.average_transaction)], ['Best Fruit', d.summary.best_product ? d.summary.best_product.name : '—']],
                    headers: ['Fruit', 'Units Sold', 'Unit', 'Revenue', '% of Total', 'Trend'],
                    rows: d.top_products.map((r) => [r.product_name, r.quantity_sold, r.unit, r.total_sales, r.share_percent, r.trend]),
                };
                case 'forecast': return {
                    title: 'Forecast Accuracy Report', file: 'forecast_report', label,
                    summary: [['Overall Accuracy', pct(d.summary.overall_accuracy)], ['Model Accuracy (validation)', pct(d.summary.model_accuracy)], ['MAPE', pct(d.summary.mape)], ['Model', d.summary.model || '—'], ['Trained', d.summary.trained_at || '—']],
                    headers: ['Fruit', 'Forecast Revenue', 'Actual Revenue', 'Error %', 'Accuracy %'],
                    rows: d.fruits.map((r) => [r.fruit, r.forecast, r.actual, r.error_percent ?? '', r.accuracy ?? '']),
                };
                case 'inventory': return {
                    title: 'Inventory Turnover Report', file: 'inventory_report', label,
                    summary: [['Products', d.summary.total_products], ['Stock on hand', num(d.summary.total_stock_quantity)], ['Inventory value', peso(d.summary.total_inventory_value)], ['Low / out of stock', d.summary.low_stock_items], ['Average turnover', d.summary.average_turnover === null ? '—' : d.summary.average_turnover + 'x']],
                    headers: ['Fruit', 'Unit', 'Opening Stock', 'Received', 'Sold', 'Closing', 'Turnover (x)'],
                    rows: d.items.map((r) => [r.product_name, r.unit, r.opening_stock, r.received, r.sold, r.closing_stock, r.turnover ?? '']),
                };
                case 'spoilage': return {
                    title: 'Spoilage Loss Report', file: 'spoilage_report', label,
                    summary: [['Total Spoilage', num(d.summary.total_spoilage_qty) + ' ' + d.summary.unit], ['Spoilage Rate', pct(d.summary.spoilage_rate)], ['Financial Loss', peso(d.summary.financial_loss)], ['Prevented Loss', peso(d.summary.prevented_loss)]],
                    headers: ['Fruit', 'Spoilage Qty', 'Unit', 'Loss Value', 'Spoilage %', 'Main Cause'],
                    rows: d.fruits.map((r) => [r.fruit, r.quantity, r.unit, r.loss_value, r.spoilage_percent, r.cause]),
                };
                case 'profit': return {
                    title: 'Profit & Loss Report', file: 'profit_loss_report', label,
                    summary: [['Gross Revenue', peso(d.summary.total_revenue)], ['Total COGS', peso(d.summary.cost_of_goods_sold)], ['Gross Profit', peso(d.summary.gross_profit)], ['Net Margin', pct(d.summary.gross_margin_percentage)], ['Cost coverage', d.summary.cost_coverage === null ? '—' : d.summary.cost_coverage + '% of sales']],
                    headers: ['Fruit', 'Revenue', 'Cost', 'Profit', 'Margin %'],
                    rows: d.product_profits.map((r) => [r.product_name, r.revenue, r.cost, r.profit, r.margin_percentage]),
                };
            }
            return null;
        },

        download(content, mime, filename) {
            const url = URL.createObjectURL(new Blob([content], { type: mime }));
            const a = document.createElement('a');
            a.href = url; a.download = filename;
            document.body.appendChild(a); a.click(); document.body.removeChild(a);
            URL.revokeObjectURL(url);
        },

        stamp() {
            return this.period.replace('_', '-') + '_' + new Date().toISOString().split('T')[0];
        },

        downloadCSV() {
            const m = this.exportModel();
            if (!m) { alert('Nothing to export yet. Wait for the report to finish loading.'); return; }
            const cell = (v) => '"' + String(v ?? '').replace(/"/g, '""') + '"';
            const lines = [[m.title], [m.label], [], ...m.summary, [], m.headers, ...m.rows].map((r) => r.map(cell).join(','));
            this.download('﻿' + lines.join('\r\n'), 'text/csv;charset=utf-8;', `${m.file}_${this.stamp()}.csv`);
        },

        exportExcel() {
            const m = this.exportModel();
            if (!m) { alert('Nothing to export yet. Wait for the report to finish loading.'); return; }
            let html = '<html><head><meta charset="utf-8"></head><body>';
            html += `<h1>FreshTrack ${esc(m.title)}</h1><p>${esc(m.label)} · Generated: ${esc(new Date().toLocaleString())}</p><br>`;
            html += '<table border="1"><tr><th colspan="2" style="background:#7C3AED;color:white;">Summary</th></tr>';
            m.summary.forEach(([l, v]) => { html += `<tr><td><b>${esc(l)}</b></td><td>${esc(v)}</td></tr>`; });
            html += '</table><br><br><table border="1"><thead style="background:#7C3AED;color:white;"><tr>';
            m.headers.forEach((h) => { html += `<th>${esc(h)}</th>`; });
            html += '</tr></thead><tbody>';
            m.rows.forEach((r) => { html += '<tr>' + r.map((c) => `<td>${esc(c)}</td>`).join('') + '</tr>'; });
            html += '</tbody></table></body></html>';
            this.download(html, 'application/vnd.ms-excel;charset=utf-8;', `${m.file}_${this.stamp()}.xls`);
        },

        exportPDF() {
            if (this.tab === 'sales') {
                // Server-rendered sales report, opened for printing / saving as PDF
                const w = window.open(`/api/reports/sales/pdf?period=${encodeURIComponent(this.period)}`, '_blank');
                if (w) setTimeout(() => w.print(), 500);
                else alert('Please allow popups to generate PDF report');
            } else {
                this.printReport();
            }
        },

        printReport() {
            const printDate = new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' });
            document.body.setAttribute('data-print-date', printDate);
            document.body.classList.add('printing');
            setTimeout(() => {
                window.print();
                setTimeout(() => {
                    document.body.classList.remove('printing');
                    document.body.removeAttribute('data-print-date');
                }, 1000);
            }, 250);
        },
    };
}
</script>
@endpush
</x-app-layout>



{{-- PRINT STYLES - CLEAN REPORT OUTPUT --}}
<style>
@media print {
    /* ===== HIDE UI CHROME ===== */
    aside,
    nav,
    header,
    footer,
    button,
    .btn,
    select,
    input,
    .sidebar,
    [class*="sidebar"],
    [class*="breadcrumb"],
    .flex.items-center.gap-2\.5,
    .flex.gap-1.p-2.bg-gray-50,
    body > div > aside,
    body > div > div > nav,
    [class*="ai-chat"],
    [class*="chat-widget"],
    .fixed.bottom-6,
    .fixed.right-6,
    [x-data*="aiChat"],
    div[style*="position: fixed"] {
        display: none !important;
    }
    
    /* ===== PAGE LAYOUT ===== */
    html, body {
        width: 100% !important;
        height: auto !important;
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }
    
    body > div,
    body > div > div {
        display: block !important;
        width: 100% !important;
        margin: 0 !important;
    }
    
    main {
        width: 100% !important;
        padding: 15mm !important;
        margin: 0 !important;
        overflow: visible !important;
    }
    
    /* ===== HIDE PAGE TITLE & BUTTONS ===== */
    .flex.flex-col.sm\:flex-row.sm\:items-center.justify-between.gap-4.mb-7 {
        display: none !important;
    }
    
    /* ===== HIDE TABS ===== */
    .card > div.flex.gap-1.p-2,
    .card > .flex.gap-1 {
        display: none !important;
    }
    
    /* ===== CARD STYLING ===== */
    .card {
        border: none !important;
        box-shadow: none !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: visible !important;
    }
    
    /* ===== SHOW ACTIVE TAB CONTENT ===== */
    .card > div[x-show] {
        display: block !important;
    }
    
    .card > div[x-show] > div {
        display: block !important;
    }
    
    /* ===== REPORT HEADER IN CONTENT ===== */
    .card > div[x-show]::before {
        content: "";
        display: block !important;
        margin-bottom: 8mm !important;
    }
    
    /* ===== HEADINGS ===== */
    h1, h3 {
        color: #7C3AED !important;
        page-break-after: avoid !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    h1 {
        font-size: 22pt !important;
        text-align: center !important;
        margin-bottom: 8mm !important;
        padding-bottom: 4mm !important;
        border-bottom: 3pt solid #7C3AED !important;
    }
    
    h3 {
        font-size: 16pt !important;
        font-weight: bold !important;
        border-bottom: 2pt solid #7C3AED !important;
        padding-bottom: 2mm !important;
        margin: 8mm 0 4mm 0 !important;
    }
    
    /* ===== GRID/STATS BOXES ===== */
    .grid {
        display: grid !important;
        grid-template-columns: repeat(4, 1fr) !important;
        gap: 4mm !important;
        margin: 6mm 0 !important;
        page-break-inside: avoid !important;
    }
    
    .grid > div {
        border: 2px solid #7C3AED !important;
        border-radius: 3mm !important;
        padding: 5mm !important;
        background: #F5F3FF !important;
        page-break-inside: avoid !important;
        text-align: center !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .grid > div p:first-child {
        font-size: 18pt !important;
        font-weight: bold !important;
        color: #7C3AED !important;
        margin-bottom: 2mm !important;
    }
    
    .grid > div p:nth-child(2) {
        font-size: 10pt !important;
        color: #666 !important;
    }
    
    /* ===== CHARTS ===== */
    canvas {
        max-width: 100% !important;
        height: 200px !important;
        margin: 6mm 0 !important;
        page-break-inside: avoid !important;
        border: 1px solid #E5E7EB !important;
        padding: 3mm !important;
    }
    
    /* ===== TABLE STYLING ===== */
    table {
        width: 100% !important;
        border-collapse: collapse !important;
        margin: 6mm 0 !important;
        page-break-inside: auto;
        font-size: 10pt !important;
    }
    
    thead {
        display: table-header-group !important;
    }
    
    thead th {
        background: #7C3AED !important;
        color: white !important;
        padding: 8pt !important;
        border: 1px solid #7C3AED !important;
        text-align: left !important;
        font-weight: bold !important;
        font-size: 10pt !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    tbody td {
        border: 1px solid #ddd !important;
        padding: 6pt 8pt !important;
        text-align: left !important;
        font-size: 9pt !important;
    }
    
    tbody tr:nth-child(even) {
        background: #f9f9f9 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    tbody tr {
        page-break-inside: avoid !important;
    }
    
    /* ===== BADGES ===== */
    .badge {
        display: inline-block !important;
        border: 1px solid currentColor !important;
        padding: 1pt 4pt !important;
        font-size: 8pt !important;
        border-radius: 2mm !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .badge-green {
        background: #dcfce7 !important;
        color: #16a34a !important;
        border-color: #16a34a !important;
    }
    
    .badge-violet {
        background: #ede9fe !important;
        color: #7c3aed !important;
        border-color: #7c3aed !important;
    }
    
    .badge-blue {
        background: #dbeafe !important;
        color: #2563eb !important;
        border-color: #2563eb !important;
    }
    
    .badge-gray {
        background: #f3f4f6 !important;
        color: #6b7280 !important;
        border-color: #6b7280 !important;
    }
    
    .badge-red {
        background: #fee2e2 !important;
        color: #dc2626 !important;
        border-color: #dc2626 !important;
    }
    
    .badge-amber {
        background: #fef3c7 !important;
        color: #d97706 !important;
        border-color: #d97706 !important;
    }
    
    /* ===== REPORT HEADER (TOP OF PAGE) ===== */
    main::before {
        content: "🍎 FreshTrack Business Report";
        display: block !important;
        text-align: center !important;
        font-size: 26pt !important;
        font-weight: bold !important;
        color: #7C3AED !important;
        margin-bottom: 10mm !important;
        padding-bottom: 5mm !important;
        border-bottom: 4pt solid #7C3AED !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    /* ===== DATE/TIME INFO ===== */
    .flex.items-center.justify-between.mb-6 {
        margin-bottom: 5mm !important;
        page-break-after: avoid !important;
    }
    
    .flex.items-center.justify-between.mb-6 > div:last-child {
        display: none !important;
    }
    
    /* ===== PAGE SETUP ===== */
    @page {
        size: A4;
        margin: 15mm;
    }
    
    /* ===== ANIMATIONS OFF ===== */
    .fade-up,
    .delay-1,
    .delay-2,
    .delay-3,
    [class*="fade"],
    [class*="delay"] {
        opacity: 1 !important;
        transform: none !important;
        animation: none !important;
    }
    
    /* ===== HIDE LOADING ===== */
    [x-show*="loading"],
    .animate-spin {
        display: none !important;
    }
    
    /* ===== PROGRESS BARS ===== */
    .progress-bar {
        display: inline-block !important;
        height: 8pt !important;
        width: 40pt !important;
        background: #E5E7EB !important;
        border-radius: 2mm !important;
        overflow: hidden !important;
        vertical-align: middle !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    
    .progress-bar > div {
        height: 100% !important;
        border-radius: 2mm !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
