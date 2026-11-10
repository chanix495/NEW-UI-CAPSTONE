<x-app-layout>
<x-slot name="title">Settings — FreshTrack</x-slot>

@php
$init = [
    'isOwner' => $isOwner,
    'initials' => collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: 'U',
    'profile' => $profile,
    'notif' => $notif,
    'region' => $region,
    'appearance' => $appearance,
    'avatar' => $avatarUrl,
    'themes' => \App\Models\OwnerSetting::THEMES,
    'backup' => $backup,
];
@endphp

<div class="flex items-center justify-between mb-7 fade-up">
    <div><h1 class="text-[26px] font-black text-gray-900">Settings</h1><p class="text-[13.5px] text-gray-500 mt-0.5">Manage your account, system preferences, and notifications</p></div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-4 gap-6 fade-up delay-1"
     x-data="settingsPage(@js($init))">

    {{-- Sidebar nav --}}
    <div class="card p-3 h-fit lg:sticky lg:top-6">
    @foreach([['profile','user','Profile Settings'],['notifications','bell','Notifications'],['appearance','palette','Appearance'],['language','globe','Language & Region'],['backup','save','Data Backup'],['system','cog','System Info']] as [$t,$i,$l])
        @continue($t==='backup' && !$isOwner)
        <button @click="tab='{{ $t }}'"
                :class="tab==='{{ $t }}' ? 'g-violet text-white shadow-md' : 'text-gray-600 hover:bg-[#F5F3FF] hover:text-violet-700'"
                class="w-full flex items-center gap-3 text-left px-4 py-3 rounded-xl text-[13.5px] font-semibold mb-1 transition-all">
            @if($i==='user')<svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
            @elseif($i==='bell')<svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            @elseif($i==='palette')<svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
            @elseif($i==='globe')<svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            @elseif($i==='save')<svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
            @elseif($i==='cog')<svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            @endif
            {{ $l }}
        </button>
        @endforeach
    </div>

    <div class="lg:col-span-3 space-y-5">

        {{-- Profile --}}
        <div x-show="tab==='profile'" class="card overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100"><h3 class="font-bold text-gray-900 text-[16px]">Profile Settings</h3><p class="text-[12px] text-gray-400 mt-0.5">Update your personal information and account details</p></div>
            <div class="p-6">
                <div class="flex items-center gap-6 mb-8">
                    <div class="relative">
                        <div class="w-20 h-20 g-violet rounded-2xl flex items-center justify-center text-white text-2xl font-black shadow-xl shadow-violet-200 overflow-hidden">
                            <img x-show="avatar" :src="avatar" alt="Profile picture" class="w-full h-full object-cover">
                            <span x-show="!avatar" x-text="initials">{{ $init['initials'] }}</span>
                        </div>
                        <input type="file" x-ref="avatarInput" accept="image/png,image/jpeg,image/webp" class="hidden" @change="uploadAvatar($event)">
                        <button @click="$refs.avatarInput.click()" :disabled="busy.avatar" title="Change profile picture" class="absolute -bottom-2 -right-2 w-7 h-7 g-blue rounded-full flex items-center justify-center text-white shadow-md">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                    </div>
                    <div>
                        <h4 class="font-black text-gray-900 text-[18px]" x-text="(profile.first_name + ' ' + profile.last_name).trim()">{{ $user->name }}</h4>
                        <p class="text-gray-500 text-[13px] mt-0.5">{{ ucfirst($user->role) }} · <span x-text="profile.business_name">{{ $businessName }}</span></p>
                        <span class="badge badge-violet text-[11px] mt-2">{{ $isOwner ? 'Administrator' : ucfirst($user->role) }}</span>
                        <div class="mt-2 text-[12px]">
                            <button x-show="avatar" @click="removeAvatar()" :disabled="busy.avatar" class="text-gray-400 hover:text-red-500 font-semibold">Remove photo</button>
                            <span x-show="avatarMsg" x-text="avatarMsg" :class="avatarOk ? 'text-green-600' : 'text-red-500'" class="font-semibold"></span>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach([['First Name','text','first_name','',false],['Last Name','text','last_name','',false],['Email Address','email','email','md:col-span-2',false],['Phone Number','tel','phone','',false],['Business Name','text','business_name','',!$isOwner],['Location','text','location','',!$isOwner]] as [$l,$t,$k,$c,$ro])
                    <div class="{{ $c }}"><label class="inp-label">{{ $l }}</label><input type="{{ $t }}" x-model="profile.{{ $k }}" class="inp {{ $ro ? 'bg-gray-100 cursor-not-allowed' : '' }}" {{ $ro ? 'readonly' : '' }}></div>
                    @endforeach
                    @foreach([['Role','text',ucfirst($user->role) . ($isOwner ? ' · Administrator' : ''),''],['Account Created','text',$accountCreated,'md:col-span-2']] as [$l,$t,$v,$c])
                    <div class="{{ $c }}"><label class="inp-label">{{ $l }}</label><input type="{{ $t }}" value="{{ $v }}" class="inp bg-gray-100 cursor-not-allowed" readonly></div>
                    @endforeach
                </div>
                <div class="flex justify-end mt-6"><span x-show="profileMsg" x-text="profileMsg" :class="profileOk ? 'text-green-600' : 'text-red-500'" class="text-[12.5px] font-semibold mr-4 self-center"></span><button @click="saveProfile()" :disabled="busy.profile" class="btn btn-violet btn-md text-[13px]"><svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>Save Changes</button></div>
            </div>
        </div>

        {{-- Notifications --}}
        <div x-show="tab==='notifications'" class="card overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100"><h3 class="font-bold text-gray-900 text-[16px]">Notification Preferences</h3><p class="text-[12px] text-gray-400 mt-0.5">Choose how and when you receive alerts<span x-show="notifMsg" x-text="notifMsg" class="ml-2 text-green-600 font-semibold"></span></p></div>
            <div class="p-6 space-y-6">
                <div>
                    <h4 class="text-[12px] font-bold text-gray-500 uppercase tracking-wider mb-3">Notification Channels</h4>
                    <div class="space-y-3">
                        @foreach([['notifEmail','Email Notifications','Receive alerts and reports via email','notifEmail'],['notifSMS','SMS Notifications','Receive urgent alerts via SMS','notifSMS'],['notifPush','Push Notifications','In-app push notifications','notifPush']] as $n)
                        <div class="flex items-center justify-between p-4 bg-[#F9F8FF] rounded-2xl border border-violet-100">
                            <div><p class="text-[13.5px] font-semibold text-gray-800">{{ $n[1] }}</p><p class="text-[12px] text-gray-400 mt-0.5">{{ $n[2] }}</p></div>
                            <button @click="toggleNotif('{{ $n[3] }}')" :class="{{ $n[3] }} ? 'g-violet' : 'bg-gray-300'" class="relative w-12 h-6 rounded-full transition-all duration-200 flex-shrink-0 shadow-inner">
                                <span :style="{ transform: {{ $n[3] }} ? 'translateX(24px)' : 'translateX(0)' }" style="left:4px" class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-md transition-transform duration-200"></span>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <h4 class="text-[12px] font-bold text-gray-500 uppercase tracking-wider mb-3">Alert Types</h4>
                    <div class="space-y-3">
                        @foreach([['notifLow','Low Stock Alerts','Alert when fruit stock falls below minimum level','notifLow'],['notifSpoil','Spoilage Risk Alerts','Alert when spoilage risk exceeds threshold','notifSpoil'],['notifForecast','Sales Forecast Updates','Alert when new sales forecast is ready','notifForecast']] as $n)
                        <div class="flex items-center justify-between p-4 bg-[#F9F8FF] rounded-2xl border border-violet-100">
                            <div><p class="text-[13.5px] font-semibold text-gray-800">{{ $n[1] }}</p><p class="text-[12px] text-gray-400 mt-0.5">{{ $n[2] }}</p></div>
                            <button @click="toggleNotif('{{ $n[3] }}')" :class="{{ $n[3] }} ? 'g-violet' : 'bg-gray-300'" class="relative w-12 h-6 rounded-full transition-all duration-200 flex-shrink-0 shadow-inner">
                                <span :style="{ transform: {{ $n[3] }} ? 'translateX(24px)' : 'translateX(0)' }" style="left:4px" class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-md transition-transform duration-200"></span>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Appearance --}}
        <div x-show="tab==='appearance'" class="card overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100"><h3 class="font-bold text-gray-900 text-[16px]">Appearance</h3><p class="text-[12px] text-gray-400 mt-0.5">Customize the look and feel of FreshTrack</p></div>
            <div class="p-6 space-y-6">
                <div class="flex items-center justify-between p-4 bg-[#F9F8FF] rounded-2xl border border-violet-100">
                    <div class="flex items-center gap-3">
                        <svg class="w-6 h-6 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                        <div><p class="font-semibold text-gray-800 text-[13.5px]">Dark Mode</p><p class="text-[12px] text-gray-400">Switch to dark theme</p></div>
                    </div>
                    <button @click="setAppearance({ dark_mode: !darkMode })" :class="darkMode ? 'g-violet' : 'bg-gray-300'" class="relative w-12 h-6 rounded-full transition-all duration-200 shadow-inner"><span :style="{ transform: darkMode ? 'translateX(24px)' : 'translateX(0)' }" style="left:4px" class="absolute top-1 w-4 h-4 bg-white rounded-full shadow-md transition-transform duration-200"></span></button>
                </div>
                <div>
                    <p class="text-[13px] font-bold text-gray-700 mb-3">Color Theme</p>
                    <div class="grid grid-cols-5 gap-3">
                        @foreach($themes as $key => $t)
                        <button @click="setAppearance({ color_theme: '{{ $key }}' })" class="flex flex-col items-center gap-2 group">
                            <div style="background: {{ $t['swatch'] }} !important" :style="colorTheme === '{{ $key }}' ? 'box-shadow: 0 0 0 2px #fff, 0 0 0 4px {{ $t['swatch'] }}' : ''" class="w-12 h-12 rounded-2xl shadow-md group-hover:scale-110 transition-transform"></div>
                            <span class="text-[11.5px] text-gray-500 font-medium">{{ $t['label'] }}</span>
                        </button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-[13px] font-bold text-gray-700 mb-3">Font Size</p>
                    <div class="flex gap-3">
                        @foreach(['small' => 'Small', 'medium' => 'Medium', 'large' => 'Large'] as $key => $label)
                        <button @click="setAppearance({ font_size: '{{ $key }}' })" :class="fontSize === '{{ $key }}' ? 'g-violet text-white border-transparent shadow-md' : 'border-gray-200 text-gray-600 hover:bg-[#F5F3FF] hover:border-violet-200'" class="flex-1 py-2.5 rounded-xl border text-[13px] font-semibold transition-all">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
                <p x-show="appearanceMsg" x-text="appearanceMsg" :class="appearanceOk ? 'text-green-600' : 'text-red-500'" class="text-[12.5px] font-semibold"></p>
            </div>
        </div>

        {{-- Language --}}
        <div x-show="tab==='language'" class="card overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100"><h3 class="font-bold text-gray-900 text-[16px]">Language & Region</h3><p class="text-[12px] text-gray-400 mt-0.5">Set your preferred language and regional formats</p></div>
            <div class="p-6 space-y-4">
                @foreach([['System Language','language'],['Date Format','date_format'],['Currency','currency'],['Time Zone','timezone']] as [$l,$k])
                <div>
                    <label class="inp-label">{{ $l }}</label>
                    <select class="inp" x-model="region.{{ $k }}">
                        @foreach($regionOptions[$k] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                @endforeach
                <div class="flex justify-end pt-2"><span x-show="regionMsg" x-text="regionMsg" :class="regionOk ? 'text-green-600' : 'text-red-500'" class="text-[12.5px] font-semibold mr-4 self-center"></span><button @click="saveRegion()" :disabled="busy.region" class="btn btn-violet btn-md text-[13px]">Save Language Settings</button></div>
            </div>
        </div>

        {{-- Backup --}}
        <div x-show="tab==='backup'" class="card overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100"><h3 class="font-bold text-gray-900 text-[16px]">Data Backup</h3><p class="text-[12px] text-gray-400 mt-0.5">Manage data backup and restore operations</p></div>
            <div class="p-6 space-y-4">
                <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
                    <div class="flex items-start gap-4">
                        <div class="icon-ring flex-shrink-0">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        </div>
                        <div class="flex-1"><h4 class="font-bold text-gray-900">Last Backup</h4><p class="text-[13px] text-gray-600 mt-0.5" x-text="last ? last.when + ' · ' + last.type_label : 'No backup has been made yet'"></p><p class="text-[12px] font-semibold mt-1"
                           :class="!last ? 'text-gray-400' : (last.status === 'success' ? 'text-green-600' : 'text-red-500')"
                           x-text="!last ? 'Use Backup Now to create the first one' : (last.status === 'success' ? '✓ Backup successful · ' + last.size : '✗ Backup failed · ' + (last.message || 'unknown error'))"></p></div>
                        <button @click="download(last.id)" :disabled="!last || !last.available" :class="(!last || !last.available) ? 'opacity-50 cursor-not-allowed' : ''" class="btn btn-green btn-sm text-[12px]">Download</button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <button @click="backupNow()" :disabled="busy.backup" class="flex items-center justify-center gap-2.5 p-4 bg-[#F5F3FF] rounded-2xl border border-violet-100 hover:bg-violet-100 transition-all text-[13px] font-semibold text-violet-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span x-text="busy.backup ? 'Backing up…' : 'Backup Now'">Backup Now</span>
                    </button>
                    <button @click="$refs.restoreFile.click()" :disabled="busy.restore" class="flex items-center justify-center gap-2.5 p-4 bg-[#F5F3FF] rounded-2xl border border-violet-100 hover:bg-violet-100 transition-all text-[13px] font-semibold text-violet-700">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span x-text="busy.restore ? 'Restoring…' : 'Restore Backup'">Restore Backup</span>
                    </button>
                    <input type="file" x-ref="restoreFile" accept=".sql" class="hidden" @change="restoreFromFile($event)">
                </div>
                <p x-show="backupMsg" x-text="backupMsg" :class="backupOk ? 'text-green-600' : 'text-red-500'" class="text-[12.5px] font-semibold"></p>
                <div>
                    <p class="text-[12px] font-bold text-gray-500 uppercase tracking-wider mb-3">Auto Backup Schedule</p>
                    <div class="space-y-2">
                        @foreach([['daily','Daily at 12:00 AM'],['weekly','Weekly on Sunday'],['monthly','Monthly on 1st']] as [$k,$label])
                        <div class="flex items-center justify-between p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                            <span class="text-[13px] text-gray-700 font-medium">{{ $label }}</span>
                            <button type="button" @click="toggleSchedule('{{ $k }}')" :class="schedule.{{ $k }} ? 'badge-green' : 'badge-gray'" class="badge text-[11px] cursor-pointer" x-text="schedule.{{ $k }} ? 'Enabled' : 'Disabled'"></button>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-[12px] font-bold text-gray-500 uppercase tracking-wider mb-3">Backup History</p>
                    <div class="space-y-2">
                        <template x-for="b in history" :key="b.id">
                            <div class="flex items-center justify-between gap-3 p-3.5 bg-gray-50 rounded-xl border border-gray-100">
                                <div class="min-w-0">
                                    <p class="text-[13px] text-gray-700 font-medium" x-text="b.when"></p>
                                    <p class="text-[11.5px] text-gray-400 mt-0.5" x-text="b.type_label + ' · ' + (b.status === 'success' ? b.size : 'Failed') + ' · ' + b.by"></p>
                                </div>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <span class="badge text-[11px]" :class="b.status === 'success' ? 'badge-green' : 'badge-red'" x-text="b.status === 'success' ? 'Success' : 'Failed'"></span>
                                    <button x-show="b.available" @click="download(b.id)" class="btn btn-green btn-sm text-[12px]">Download</button>
                                    <button x-show="b.available" @click="restoreBackup(b)" :disabled="busy.restore" class="btn btn-violet btn-sm text-[12px]">Restore</button>
                                </div>
                            </div>
                        </template>
                        <p x-show="!history.length" class="text-[12.5px] text-gray-400 p-3.5">No backups yet.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- System Info --}}
        <div x-show="tab==='system'" class="card overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-100"><h3 class="font-bold text-gray-900 text-[16px]">System Information</h3><p class="text-[12px] text-gray-400 mt-0.5">Technical details about your FreshTrack installation</p></div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    @foreach([['System Name','FreshTrack — Fruit Inventory System'],['Version','v2.0.0-beta (Build 2026.06.23)'],['Framework','Laravel ' . $system['laravel']],['PHP Version',$system['php']],['Database',$system['database']],['AI Model Version','FreshTrack-ML v2.4.1'],['Frontend','Tailwind CSS v4 + Alpine.js v3'],['License','Academic / Capstone Project']] as [$l,$v])
                    <div class="bg-[#F5F3FF] rounded-2xl p-4 border border-violet-100">
                        <p class="text-[11px] font-bold text-gray-400 uppercase tracking-wider">{{ $l }}</p>
                        <p class="text-[13.5px] font-bold text-gray-900 mt-1">{{ $v }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="bg-green-50 border border-green-200 rounded-2xl p-5">
                    <div class="flex items-center gap-3 mb-4"><span class="w-3 h-3 bg-green-500 rounded-full pulse-dot"></span><h4 class="font-bold text-gray-900 text-[15px]">System Health: Excellent</h4></div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        @foreach([['CPU Usage','12%','g-green'],['Memory','34%','g-blue'],['Storage','28%','g-violet'],['Uptime','99.9%','g-green']] as [$l,$v,$g])
                        <div class="text-center bg-white rounded-2xl p-3.5 border border-green-100">
                            <p class="text-[22px] font-black text-green-600">{{ $v }}</p>
                            <p class="text-[11.5px] text-gray-500 mt-0.5">{{ $l }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function settingsPage(init) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const request = async (url, method, body) => {
        const isForm = body instanceof FormData;
        const res = await fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', ...(body && !isForm ? { 'Content-Type': 'application/json' } : {}) },
            body: body ? (isForm ? body : JSON.stringify(body)) : undefined,
        });
        let data = {};
        try { data = await res.json(); } catch (e) {}
        if (!res.ok) {
            throw new Error(data.message || (data.errors && Object.values(data.errors).flat()[0]) || 'Something went wrong. Please try again.');
        }
        return data;
    };
    const notifColumns = {
        notifEmail: 'email_notifications', notifSMS: 'sms_notifications', notifPush: 'push_notifications',
        notifLow: 'low_stock_alerts', notifSpoil: 'spoilage_alerts', notifForecast: 'forecast_alerts',
    };
    const flash = (self, key, text, ok = true, ms = 3500) => {
        self[key + 'Msg'] = text;
        if (key !== 'notif') self[key + 'Ok'] = ok;
        clearTimeout(self['_t_' + key]);
        self['_t_' + key] = setTimeout(() => self[key + 'Msg'] = '', ms);
    };

    return {
        tab: 'profile',
        darkMode: init.appearance.dark,
        colorTheme: init.appearance.theme,
        fontSize: init.appearance.font,
        appearanceMsg: '', appearanceOk: true,
        initials: init.initials,
        avatar: init.avatar,
        avatarMsg: '', avatarOk: true,
        profile: init.profile,
        region: init.region,
        notifEmail: init.notif.email_notifications, notifSMS: init.notif.sms_notifications, notifPush: init.notif.push_notifications,
        notifLow: init.notif.low_stock_alerts, notifSpoil: init.notif.spoilage_alerts, notifForecast: init.notif.forecast_alerts,
        last: init.backup?.last ?? null,
        history: init.backup?.history ?? [],
        schedule: init.backup?.schedule ?? { daily: true, weekly: true, monthly: false },
        busy: { avatar: false, profile: false, region: false, backup: false, restore: false },
        profileMsg: '', profileOk: true, notifMsg: '', regionMsg: '', regionOk: true, backupMsg: '', backupOk: true,

        async uploadAvatar(event) {
            const file = event.target.files[0];
            event.target.value = '';
            if (!file) return;
            if (file.size > 2 * 1024 * 1024) { flash(this, 'avatar', 'The picture must be 2 MB or smaller.', false, 6000); return; }
            this.busy.avatar = true;
            try {
                const form = new FormData();
                form.append('avatar', file);
                const res = await request('/api/settings/avatar', 'POST', form);
                this.avatar = res.url;
                flash(this, 'avatar', res.message);
                setTimeout(() => location.reload(), 800);   // show the new picture in the navbar and sidebar
            } catch (e) {
                flash(this, 'avatar', e.message, false, 6000);
            } finally {
                this.busy.avatar = false;
            }
        },

        async removeAvatar() {
            if (!confirm('Remove your profile picture?')) return;
            this.busy.avatar = true;
            try {
                const res = await request('/api/settings/avatar', 'DELETE');
                this.avatar = null;
                flash(this, 'avatar', res.message);
                setTimeout(() => location.reload(), 800);
            } catch (e) {
                flash(this, 'avatar', e.message, false, 6000);
            } finally {
                this.busy.avatar = false;
            }
        },

        async saveProfile() {
            this.busy.profile = true;
            try {
                const res = await request('/api/settings/profile', 'PUT', this.profile);
                this.initials = res.initials || this.initials;
                flash(this, 'profile', 'Profile updated');
                setTimeout(() => location.reload(), 900);   // refresh the name shown in the navbar and sidebar
            } catch (e) {
                flash(this, 'profile', e.message, false, 6000);
            } finally {
                this.busy.profile = false;
            }
        },

        // Dark mode, colour theme and font size apply immediately and are saved per user
        async setAppearance(changes) {
            const before = { dark_mode: this.darkMode, color_theme: this.colorTheme, font_size: this.fontSize };
            if ('dark_mode' in changes) this.darkMode = changes.dark_mode;
            if ('color_theme' in changes) this.colorTheme = changes.color_theme;
            if ('font_size' in changes) this.fontSize = changes.font_size;
            if ('dark_mode' in changes) Alpine.$data(document.documentElement).darkMode = changes.dark_mode;
            try {
                const a = await window.ftSaveAppearance(changes);
                window.ftApplyAppearance(a);
                flash(this, 'appearance', 'Appearance saved');
            } catch (e) {
                this.darkMode = before.dark_mode; this.colorTheme = before.color_theme; this.fontSize = before.font_size;
                Alpine.$data(document.documentElement).darkMode = before.dark_mode;
                flash(this, 'appearance', e.message, false, 6000);
            }
        },

        async toggleNotif(name) {
            const next = !this[name];
            this[name] = next;
            try {
                await request('/api/settings/notifications', 'PUT', { key: notifColumns[name], value: next });
                flash(this, 'notif', '· Saved');
            } catch (e) {
                this[name] = !next;
                alert(e.message);
            }
        },

        async saveRegion() {
            this.busy.region = true;
            try {
                const res = await request('/api/settings/region', 'PUT', this.region);
                flash(this, 'region', res.message);
                setTimeout(() => location.reload(), 900);   // date and time formats are applied on page load
            } catch (e) {
                flash(this, 'region', e.message, false, 6000);
            } finally {
                this.busy.region = false;
            }
        },

        applyBackup(data) {
            this.last = data.last ?? null;
            this.history = data.history ?? [];
            this.schedule = data.schedule ?? this.schedule;
        },

        async backupNow() {
            this.busy.backup = true;
            try {
                const res = await request('/api/backups', 'POST');
                this.applyBackup(res);
                flash(this, 'backup', res.message);
            } catch (e) {
                flash(this, 'backup', e.message, false, 8000);
                try { this.applyBackup(await request('/api/backups', 'GET')); } catch (_) {}
            } finally {
                this.busy.backup = false;
            }
        },

        download(id) {
            if (id) window.location.href = '/api/backups/' + id + '/download';
        },

        async restoreBackup(b) {
            if (!confirm('Restore the backup from ' + b.when + '?\n\nThis REPLACES all current data (inventory, sales, users, ...) with the data in that backup. A safety copy of the current data is saved first.')) return;
            await this.runRestore(() => request('/api/backups/' + b.id + '/restore', 'POST'));
        },

        async restoreFromFile(event) {
            const file = event.target.files[0];
            event.target.value = '';
            if (!file) return;
            if (!confirm('Restore from "' + file.name + '"?\n\nThis REPLACES all current data (inventory, sales, users, ...) with the data in the file. A safety copy of the current data is saved first.')) return;
            const form = new FormData();
            form.append('backup', file);
            await this.runRestore(() => request('/api/backups/restore-upload', 'POST', form));
        },

        async runRestore(call) {
            this.busy.restore = true;
            try {
                const res = await call();
                flash(this, 'backup', res.message, true, 10000);
                setTimeout(() => location.reload(), 1500);
            } catch (e) {
                flash(this, 'backup', e.message, false, 10000);
                this.busy.restore = false;
            }
        },

        async toggleSchedule(name) {
            const next = !this.schedule[name];
            this.schedule[name] = next;
            try {
                await request('/api/backups/schedule', 'PUT', { name, enabled: next });
            } catch (e) {
                this.schedule[name] = !next;
                alert(e.message);
            }
        },
    };
}
</script>
@endpush
</x-app-layout>
