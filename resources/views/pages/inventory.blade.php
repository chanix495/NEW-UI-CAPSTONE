<x-app-layout>
<x-slot name="title">Inventory — FreshTrack</x-slot>
<div x-data="{ addModal:false, editModal:false, viewModal:false, deleteModal:false, selected:null }" @open-add.window="addModal=true">

{{-- Header --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7 fade-up">
    <div>
        <h1 class="text-[26px] font-black text-gray-900">Inventory Management</h1>
        <p class="text-[13.5px] text-gray-500 mt-0.5">Monitor stock levels, batches, and supplier data in real-time</p>
    </div>
    <div class="flex items-center gap-3">
        <button class="btn btn-outline btn-md text-[13px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export
        </button>
        <button @click="addModal=true" class="btn btn-violet btn-md text-[13px]">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Add Stock
        </button>
    </div>
</div>

{{-- Summary --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 fade-up delay-1">
@php
$summaryCards = [
    ['Total Stock',   '1,240 kg', 'g-violet', 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',                                   'All batches'],
    ['Available',     '1,102 kg', 'g-green',  'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                       'Ready to sell'],
    ['Critical Items','3 items',  'g-rose',   'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z','Needs restock'],
    ['Out of Stock',  '1 item',   'g-amber',  'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636',       'Depleted'],
];
@endphp
@foreach($summaryCards as [$l,$v,$g,$iconPath,$s])
<div class="card shimmer card-lift p-5">
    <div class="icon-ring mb-3">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}"/>
        </svg>
    </div>
    <p class="text-[22px] font-black text-gray-900">{{ $v }}</p>
    <p class="text-[12.5px] font-semibold text-gray-600">{{ $l }}</p>
    <p class="text-[11.5px] text-gray-400 mt-0.5">{{ $s }}</p>
</div>
@endforeach
</div>

{{-- Filters --}}
<div class="card p-4 mb-5 fade-up delay-2">
    <div class="flex flex-wrap gap-3">
        <div class="flex items-center gap-2.5 bg-gray-100 rounded-2xl px-4 py-2.5 flex-1 min-w-52 max-w-sm border-2 border-transparent focus-within:border-violet-400 focus-within:bg-white transition-all">
            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" placeholder="Search fruit, batch ID, supplier…" class="bg-transparent text-[13px] outline-none w-full placeholder-gray-400">
        </div>
        <select class="inp" style="width:auto;padding:10px 14px;border-radius:12px">
            <option>All Status</option><option>Available</option><option>Low Stock</option><option>Critical</option><option>Out of Stock</option>
        </select>
        <select class="inp" style="width:auto;padding:10px 14px;border-radius:12px">
            <option>All Suppliers</option><option>Davao Fresh Farms</option><option>Mt. Apo Growers</option><option>Sta. Cruz Orchards</option>
        </select>
        <select class="inp" style="width:auto;padding:10px 14px;border-radius:12px">
            <option>All Fruits</option><option>Mango</option><option>Durian</option><option>Pomelo</option><option>Mangosteen</option>
        </select>
    </div>
</div>

{{-- Grid Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mb-6 fade-up delay-3">
@php
$items = [
    ['Mango',      'MNG-001','285 kg','Jun 26','Davao Fresh Farms','₱120/kg','Available','badge-green', 92,'Fresh', 12],
    ['Durian',     'DUR-112','145 kg','Jun 25','Mt. Apo Growers',  '₱320/kg','Available','badge-green', 78,'Good', 11],
    ['Pomelo',     'POM-034','8 kg',  'Jul 2', 'Sta. Cruz Orchards','₱70/kg','Critical', 'badge-red',   16,'Critical', 18],
    ['Mangosteen', 'MGS-078','92 kg', 'Jun 28','Davao Fresh Farms','₱170/kg','Available','badge-green', 85,'Fresh', 14],
    ['Lanzones',   'LNZ-055','22 kg', 'Jun 27','Mt. Apo Growers',  '₱85/kg', 'Low Stock','badge-amber', 44,'Fair', 13],
    ['Pineapple',  'PNA-019','118 kg','Jun 30','Sta. Cruz Orchards','₱75/kg','Available','badge-green', 88,'Fresh', 16],
    ['Banana',     'BNA-041','210 kg','Jun 29','Davao Fresh Farms','₱42/kg', 'Available','badge-green', 95,'Excellent', 15],
    ['Mango',      'MNG-002','155 kg','Jun 24','Mt. Apo Growers',  '₱118/kg','Low Stock','badge-amber', 35,'Fair', 10],
    ['Durian',     'DUR-113','0 kg',  '—',     'Mt. Apo Growers',  '₱320/kg','Out of Stock','badge-gray', 0,'Depleted', 0],
];
$fruitIconPath = 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4';
@endphp
@foreach($items as $it)
<div class="card overflow-hidden card-lift fade-up delay-{{ min($loop->index+1,8) }}"
     x-on:click="viewModal=true; selected='{{ $it[1] }}'">
    {{-- Neutral header --}}
    <div class="bg-gray-50 border-b border-gray-100 px-5 pt-5 pb-4 relative">
        <div class="absolute top-3 right-3">
            <span class="badge {{ $it[7] }} text-[10px]">{{ $it[6] }}</span>
        </div>
        <div class="w-10 h-10 bg-white border border-gray-200 rounded-xl flex items-center justify-center mb-3 shadow-sm">
            <svg class="w-5 h-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $fruitIconPath }}"/>
            </svg>
        </div>
        <p class="font-black text-gray-900 text-[16px] leading-tight">{{ $it[0] }}</p>
        <p class="text-gray-400 text-[11px] font-medium mt-0.5 font-mono">{{ $it[1] }}</p>
    </div>
    {{-- Card body --}}
    <div class="p-4">
        <div class="flex items-center justify-between mb-3">
            <div>
                <p class="text-[22px] font-black text-gray-900">{{ $it[2] }}</p>
                <p class="text-[11.5px] text-gray-400">Stock quantity</p>
            </div>
            <div class="text-right">
                <p class="text-[14px] font-bold text-violet-700">{{ $it[5] }}</p>
                <p class="text-[11px] text-gray-400">Unit price</p>
            </div>
        </div>
        <div class="progress-bar mb-3">
            <div class="progress-fill {{ $it[8] < 20 ? 'bg-red-400' : ($it[8] < 50 ? 'bg-amber-400' : 'bg-violet-500') }}"
                 style="width:{{ $it[8] }}%"></div>
        </div>
        <div class="flex items-center justify-between text-[11.5px] text-gray-400 mb-3">
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                {{ $it[4] }}
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Exp: {{ $it[3] }}
            </span>
        </div>
        <div class="flex items-center justify-between text-[11.5px] text-gray-400">
            <span class="flex items-center gap-1">
                <svg class="w-3 h-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Shelf Life: <span class="{{ $it[10] <= 7 ? 'text-red-500' : ($it[10] <= 14 ? 'text-amber-500' : 'text-green-600') }} font-semibold">{{ $it[10] }} days</span>
            </span>
        </div>
        <div class="flex items-center justify-between">
            <span class="text-[12px] font-medium text-gray-500">
                Freshness: <span class="{{ $it[8] > 70 ? 'text-green-600' : ($it[8] > 40 ? 'text-amber-500' : 'text-red-500') }} font-semibold">{{ $it[9] }}</span>
            </span>
            <div class="flex gap-1.5">
                <button @click.stop="editModal=true; selected='{{ $it[0] }}'"
                        class="p-1.5 text-gray-300 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button @click.stop="deleteModal=true; selected='{{ $it[0] }}'"
                        class="p-1.5 text-gray-300 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endforeach
</div>

{{-- View Modal --}}
<div x-show="viewModal" @click.self="viewModal=false" class="modal-bg fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" x-cloak x-transition>
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-lg border border-violet-100" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="bg-gray-50 border-b border-gray-100 p-6 relative rounded-t-3xl">
            <div class="absolute top-4 right-4"><button @click="viewModal=false" class="text-gray-400 hover:text-gray-600 p-1.5 rounded-xl hover:bg-gray-200 transition-colors"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button></div>
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-white border border-gray-200 rounded-2xl flex items-center justify-center shadow-sm">
                    <svg class="w-7 h-7 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-gray-900">Mango</h3>
                    <p class="text-gray-500 text-[13px] font-mono" x-text="'Batch: '+selected"></p>
                    <span class="badge badge-green text-[10px] mt-1">Available</span>
                </div>
            </div>
        </div>
        <div class="p-6 grid grid-cols-2 gap-4">
            @foreach([['Current Stock','285 kg'],['Unit Price','₱120/kg'],['Supplier','Davao Fresh Farms'],['Expiration','Jun 26, 2026'],['Received Date','Jun 16, 2026'],['Shelf Life','12 days'],['Storage','Room A · Shelf 3'],['Freshness Score','92/100']] as [$l,$v])
            <div class="bg-gray-50 rounded-xl p-3.5 border border-gray-100">
                <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wide">{{ $l }}</p>
                <p class="text-[14px] font-bold text-gray-900 mt-1">{{ $v }}</p>
            </div>
            @endforeach
        </div>
        <div class="flex gap-3 px-6 pb-6">
            <button @click="viewModal=false; editModal=true" class="btn btn-violet btn-md flex-1">Edit Stock</button>
            <button @click="viewModal=false" class="btn btn-outline btn-md flex-1">Close</button>
        </div>
    </div>
</div>

{{-- Add/Edit Modal --}}
<div x-show="addModal||editModal" @click.self="addModal=false;editModal=false" class="modal-bg fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" x-cloak x-transition>
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md border border-violet-100" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-[15px]" x-text="editModal ? 'Edit Stock — '+selected : 'Add New Stock'"></h3>
            <button @click="addModal=false;editModal=false" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6 space-y-4">
            <div><label class="inp-label">Fruit Type</label><select class="inp"><option>Mango</option><option>Durian</option><option>Pomelo</option><option>Mangosteen</option><option>Lanzones</option><option>Banana</option><option>Pineapple</option></select></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="inp-label">Batch ID</label><input type="text" :value="editModal?'MNG-001':''" placeholder="MNG-003" class="inp"></div>
                <div><label class="inp-label">Quantity (kg)</label><input type="number" :value="editModal?'285':''" placeholder="0" class="inp"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="inp-label">Unit Price (₱)</label><input type="number" :value="editModal?'120':''" placeholder="0" class="inp"></div>
                <div><label class="inp-label">Expiration Date</label><input type="date" :value="editModal?'2026-06-26':''" class="inp"></div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="inp-label">Remaining Shelf Life (days)</label><input type="number" :value="editModal?'12':''" placeholder="0" class="inp"></div>
                <div><label class="inp-label">Supplier</label><select class="inp"><option>Davao Fresh Farms</option><option>Mt. Apo Growers</option><option>Sta. Cruz Orchards</option></select></div>
            </div>
        </div>
        <div class="flex gap-3 px-6 pb-6">
            <button @click="addModal=false;editModal=false" class="btn btn-outline btn-md flex-1">Cancel</button>
            <button @click="addModal=false;editModal=false" class="btn btn-violet btn-md flex-1">Save Changes</button>
        </div>
    </div>
</div>

{{-- Delete Modal --}}
<div x-show="deleteModal" @click.self="deleteModal=false" class="modal-bg fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" x-cloak x-transition>
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm text-center border border-red-100" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-5"><svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg></div>
        <h3 class="text-[18px] font-bold text-gray-900 mb-2">Remove Stock Item?</h3>
        <p class="text-[13.5px] text-gray-500 mb-6"><span class="font-bold text-gray-800" x-text="selected"></span> will be permanently removed from inventory.</p>
        <div class="flex gap-3">
            <button @click="deleteModal=false" class="btn btn-outline btn-md flex-1">Cancel</button>
            <button @click="deleteModal=false" class="btn bg-red-600 hover:bg-red-700 text-white btn-md flex-1">Remove</button>
        </div>
    </div>
</div>

</div>
</x-app-layout>
