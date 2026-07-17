<x-app-layout>
<x-slot name="title">User Management — FreshTrack</x-slot>
<div x-data="{ editModal:false, deleteModal:false, addModal:false, selected:null }">

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-7 fade-up">
    <div>
        <h1 class="text-[26px] font-black text-gray-900">User Management</h1>
        <p class="text-[13.5px] text-gray-500 mt-0.5">Manage system users, roles, and access permissions</p>
    </div>
    <button @click="addModal=true" class="btn btn-violet btn-md text-[13px]">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
        Add New User
    </button>
</div>

{{-- Role Summary --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-7 fade-up delay-1">
    @foreach([
        ['Owner','1 user','owner','g-violet','Full access to all features including AI, finances, and user management.','badge-violet'],
        ['Manager','2 users','manager','g-blue','Inventory, sales, forecast. Limited settings and no user admin.','badge-blue'],
        ['Cashier','3 users','cashier','g-green','Record transactions and view basic inventory. No admin access.','badge-green'],
    ] as [$r,$c,$e,$g,$d,$b])
    <div class="card p-5">
        <div class="flex items-center gap-3 mb-3">
            <div class="icon-ring">
                @if($e==='owner')<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                @elseif($e==='manager')<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                @elseif($e==='cashier')<svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @endif
            </div>
            <div><p class="font-bold text-gray-900 text-[16px]">{{ $r }}</p><span class="badge {{ $b }} text-[11px]">{{ $c }}</span></div>
        </div>
        <p class="text-[12.5px] text-gray-500 leading-relaxed">{{ $d }}</p>
    </div>
    @endforeach
</div>

{{-- User Cards --}}
@php
$users=[
    ['JD','Criss Banawa',  'juan@freshtrack.ph',    'Owner',   'Active',  '2 hours ago', 'owner',   ['Full system access','Financial reports','User management','All AI features'],  'badge-violet'],
    ['MR','Maria Santos Reyes','maria@freshtrack.ph', 'Manager', 'Active',  '30 min ago',  'manager', ['Inventory management','Sales reports','Forecast view','Limited settings'],     'badge-blue'],
    ['PR','Pedro Garcia Reyes','pedro@freshtrack.ph', 'Cashier', 'Active',  '5 min ago',   'cashier', ['Record sales','View inventory','Basic dashboard','No admin access'],           'badge-amber'],
    ['AG','Ana Lopez Gomez', 'ana@freshtrack.ph',     'Cashier', 'Active',  '1 hour ago',  'cashier', ['Record sales','View inventory','Basic dashboard','No admin access'],           'badge-amber'],
    ['RM','Roberto Mendoza', 'roberto@freshtrack.ph', 'Manager', 'Inactive','3 days ago',  'manager', ['Inventory management','Sales reports','Forecast view','Limited settings'],     'badge-blue'],
    ['LC','Lina Cruz',       'lina@freshtrack.ph',    'Cashier', 'Inactive','1 week ago',  'cashier', ['Record sales','View inventory','Basic dashboard','No admin access'],           'badge-amber'],
];
@endphp

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-7 fade-up delay-2">
@foreach($users as $u)
<div class="card overflow-hidden card-lift">
    {{-- Neutral card header --}}
    <div class="bg-gray-50 border-b border-gray-100 px-5 pt-5 pb-4 relative">
        <div class="absolute top-4 right-4">
            <span class="flex items-center gap-1.5 text-[11px] font-semibold px-2.5 py-1 rounded-full {{ $u[4]==='Active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400' }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $u[4]==='Active' ? 'bg-green-500 pulse-dot' : 'bg-gray-400' }}"></span>
                {{ $u[4] }}
            </span>
        </div>
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 g-violet rounded-2xl flex items-center justify-center text-white text-xl font-black shadow-md shadow-violet-200 flex-shrink-0">{{ $u[0] }}</div>
            <div>
                <p class="font-black text-gray-900 text-[16px] leading-tight">{{ $u[1] }}</p>
                <p class="text-gray-400 text-[12.5px] mt-0.5">{{ $u[2] }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2 mt-3">
            @if($u[5]==='owner')<svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            @elseif($u[5]==='manager')<svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            @elseif($u[5]==='cashier')<svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @endif
            <span class="text-[13px] font-semibold text-gray-600">{{ $u[3] }}</span>
            <span class="badge {{ $u[8] }} text-[10px] ml-1">{{ $u[3] }}</span>
        </div>
    </div>
    <div class="p-5">
        <div class="mb-4">
            <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider mb-2">Permissions</p>
            <div class="space-y-1.5">
                @foreach($u[7] as $p)
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-[12.5px] text-gray-600">{{ $p }}</span>
                </div>
                @endforeach
            </div>
        </div>
        <div class="flex items-center justify-between text-[12px] text-gray-400 mb-4">
            <span>Last login</span>
            <span class="font-semibold text-gray-600">{{ $u[4] }}</span>
        </div>
        <div class="flex gap-2">
            <button @click="editModal=true; selected='{{ $u[1] }}'"
                    class="btn btn-outline btn-md flex-1 text-[12.5px]">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </button>
            <button @click="deleteModal=true; selected='{{ $u[1] }}'"
                    class="btn btn-outline btn-sm px-3.5 text-[12px] hover:border-red-300 hover:text-red-600 hover:bg-red-50">
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    </div>
</div>
@endforeach
</div>

{{-- Modals --}}
<div x-show="editModal||addModal" @click.self="editModal=false;addModal=false" class="modal-bg fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" x-cloak x-transition>
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md border border-violet-100" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
            <h3 class="font-bold text-gray-900 text-[15px]" x-text="addModal ? 'Add New User' : 'Edit User — '+selected"></h3>
            <button @click="editModal=false;addModal=false" class="p-2 hover:bg-gray-100 rounded-xl text-gray-400"><svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-3">
                <div><label class="inp-label">First Name</label><input type="text" :value="editModal?'Criss':''" placeholder="First name" class="inp"></div>
                <div><label class="inp-label">Last Name</label><input type="text" :value="editModal?'Banawa':''" placeholder="Last name" class="inp"></div>
            </div>
            <div><label class="inp-label">Email Address</label><input type="email" :value="editModal?'juan@FreshTrack.ph':''" placeholder="user@FreshTrack.ph" class="inp"></div>
            <div><label class="inp-label">Phone Number</label><input type="tel" :value="editModal?'+63 912 345 6789':''" placeholder="+63 9XX XXX XXXX" class="inp"></div>
            <div class="grid grid-cols-2 gap-3">
                <div><label class="inp-label">Role</label><select class="inp"><option>Owner</option><option>Manager</option><option>Cashier</option></select></div>
                <div><label class="inp-label">Status</label><select class="inp"><option>Active</option><option>Inactive</option></select></div>
            </div>
            <div x-show="addModal"><label class="inp-label">Temporary Password</label><input type="password" placeholder="Set temporary password" class="inp"></div>
        </div>
        <div class="flex gap-3 px-6 pb-6">
            <button @click="editModal=false;addModal=false" class="btn btn-outline btn-md flex-1">Cancel</button>
            <button @click="editModal=false;addModal=false" class="btn btn-violet btn-md flex-1">Save User</button>
        </div>
    </div>
</div>

<div x-show="deleteModal" @click.self="deleteModal=false" class="modal-bg fixed inset-0 bg-black/40 flex items-center justify-center z-50 p-4" x-cloak x-transition>
    <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-sm text-center border border-red-100" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
        <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-5"><svg class="w-8 h-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>
        <h3 class="text-[18px] font-bold text-gray-900 mb-2">Remove User?</h3>
        <p class="text-[13.5px] text-gray-500 mb-6"><span class="font-bold text-gray-800" x-text="selected"></span> will lose all system access.</p>
        <div class="flex gap-3"><button @click="deleteModal=false" class="btn btn-outline btn-md flex-1">Cancel</button><button @click="deleteModal=false" class="btn bg-red-600 hover:bg-red-700 text-white btn-md flex-1">Remove User</button></div>
    </div>
</div>

</div>
</x-app-layout>
