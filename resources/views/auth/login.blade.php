<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — FreshTrack</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        *,*::before,*::after{box-sizing:border-box}
        body{font-family:'Inter',sans-serif;-webkit-font-smoothing:antialiased}
        h1,h2,h3{font-family:'Poppins',sans-serif}
        [x-cloak]{display:none!important}
        .g-violet{background:linear-gradient(135deg,#7C3AED,#6D28D9)}
        .g-hero{background:linear-gradient(160deg,#7C3AED 0%,#6D28D9 50%,#059669 100%)}
        .float-a{animation:fa 7s ease-in-out infinite}
        .float-b{animation:fb 9s ease-in-out infinite}
        .float-c{animation:fc 8s ease-in-out infinite}
        @keyframes fa{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-22px) rotate(6deg)}}
        @keyframes fb{0%,100%{transform:translateY(0)}50%{transform:translateY(-16px) rotate(-5deg)}}
        @keyframes fc{0%,100%{transform:translateY(0)}50%{transform:translateY(-28px) rotate(4deg)}}
        .card-enter{animation:cardIn .5s cubic-bezier(.175,.885,.32,1.275) both}
        @keyframes cardIn{from{opacity:0;transform:translateX(32px) scale(.97)}to{opacity:1;transform:translateX(0) scale(1)}}
        .inp{width:100%;padding:12px 16px 12px 44px;border-radius:14px;border:1.5px solid #E5E7EB;background:#FAFAFA;font-size:14px;color:#1F2937;transition:all .2s ease;outline:none}
        .inp:focus{border-color:#7C3AED;background:#fff;box-shadow:0 0 0 3px rgba(124,58,237,.12)}
        .btn-login{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px;border-radius:14px;font-weight:700;font-size:15px;color:#fff;cursor:pointer;border:none;transition:all .2s ease;background:linear-gradient(135deg,#7C3AED,#6D28D9);box-shadow:0 6px 20px rgba(124,58,237,.35);font-family:'Poppins',sans-serif}
        .btn-login:hover{box-shadow:0 8px 28px rgba(124,58,237,.45);transform:translateY(-1px)}
        .btn-login:active{transform:scale(.97)}
        .pulse-dot{animation:pd 2.2s infinite}
        @keyframes pd{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(1.3)}}
    </style>
</head>
<body class="h-full">
<div class="min-h-screen flex">

    {{-- LEFT: Illustration panel --}}
    <div class="hidden lg:flex lg:w-1/2 g-hero relative overflow-hidden flex-col justify-between p-12">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle,rgba(255,255,255,.7) 1px,transparent 1px);background-size:36px 36px"></div>

        {{-- floating fruits --}}






        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm border border-white/20 shadow-xl"><svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                <div>
                    <p class="text-white font-black text-xl" style="font-family:Poppins,sans-serif">FreshTrack</p>
                    <p class="text-white/60 text-[11px] font-medium">AI-Powered Platform</p>
                </div>
            </div>
        </div>

        <div class="relative z-10">
            <h1 class="text-4xl font-black text-white leading-tight mb-4">
                The Smartest Way<br>to Manage Your<br><span class="text-green-300">Fruit Business</span>
            </h1>
            <p class="text-white/70 text-[15px] leading-relaxed mb-8 max-w-sm">AI-powered sales forecasting, real-time spoilage prediction, and intelligent inventory management — built for Davao City fruit vendors.</p>

            <div class="grid grid-cols-3 gap-4">
                @foreach([['96%', 'Forecast Accuracy'], ['↓62%', 'Less Spoilage'], ['₱342K', 'Revenue/Month']] as [$v, $l])
                <div class="bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl p-4 text-center">
                    <p class="text-green-300 font-black text-xl">{{ $v }}</p>
                    <p class="text-white/60 text-[10.5px] font-medium mt-0.5">{{ $l }}</p>
                </div>
                @endforeach
            </div>
        </div>

        <div class="relative z-10">
            <div class="flex items-center gap-2">
                <span class="w-2 h-2 bg-green-300 rounded-full pulse-dot"></span>
                <span class="text-white/60 text-[12px] font-medium">System Online · Davao City · 2026</span>
            </div>
        </div>
    </div>

    {{-- RIGHT: Login form --}}
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white">
        <div class="w-full max-w-md card-enter" x-data="{ showPass: false, loading: false }">

            {{-- Mobile logo --}}
            <div class="flex lg:hidden items-center gap-3 mb-8">
                <div class="w-10 h-10 g-violet rounded-xl flex items-center justify-center shadow-lg shadow-violet-300/40"><svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg></div>
                <div>
                    <p class="font-black text-[15px] text-gray-900" style="font-family:Poppins,sans-serif">FreshTrack</p>
                    <p class="text-violet-500 text-[10px] font-semibold">AI-Powered Platform</p>
                </div>
            </div>

            <div class="mb-8">
                <h2 class="text-3xl font-black text-gray-900 mb-2">Welcome back</h2>
                <p class="text-gray-500 text-[14.5px]">Sign in to your FreshTrack dashboard</p>
            </div>

            {{-- Role selector --}}
            <div class="grid grid-cols-3 gap-3 mb-7 p-1 bg-gray-100 rounded-2xl">
                @foreach([['Owner'], ['Manager'], ['Cashier']] as $i => $role)
                <button x-data :class="{{ $i }}===0 ? 'bg-white shadow-md text-violet-700 font-bold' : 'text-gray-500 hover:text-gray-700'"
                        class="flex flex-col items-center gap-1 py-3 rounded-xl text-[12px] font-semibold transition-all">
                    {{ $role[0] }}
                </button>
                @endforeach
            </div>

            {{-- Email --}}
            <div class="mb-4">
                <label class="block text-[11.5px] font-bold text-gray-500 uppercase tracking-wider mb-2">Email Address</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg class="w-4.5 h-4.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" style="width:18px;height:18px"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <input type="email" value="owner@FreshTrack.ph" placeholder="your@email.com" class="inp">
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-5">
                <label class="block text-[11.5px] font-bold text-gray-500 uppercase tracking-wider mb-2">Password</label>
                <div class="relative">
                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                        <svg style="width:18px;height:18px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    </span>
                    <input :type="showPass ? 'text' : 'password'" value="password" class="inp" style="padding-right:48px">
                    <button type="button" @click="showPass=!showPass" class="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-violet-600 transition-colors p-0.5 rounded">
                        <svg x-show="!showPass" style="width:18px;height:18px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="showPass" style="width:18px;height:18px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between mb-7">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" checked class="w-4 h-4 rounded border-gray-300 accent-violet-600">
                    <span class="text-[13px] text-gray-600 font-medium">Keep me signed in</span>
                </label>
                <a href="#" class="text-[13px] text-violet-600 font-semibold hover:text-violet-700 transition-colors hover:underline">Forgot password?</a>
            </div>

            <a href="{{ route('dashboard') }}" @click="loading=true" class="btn-login mb-5">
                <span x-show="!loading" class="flex items-center gap-2">
                    Sign In to Dashboard
                    <svg style="width:18px;height:18px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </span>
                <span x-show="loading" class="flex items-center gap-2">
                    <svg class="animate-spin" style="width:18px;height:18px" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                    Authenticating…
                </span>
            </a>

            <div class="bg-gradient-to-br from-violet-50 to-green-50 rounded-2xl p-4 border border-violet-100">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    <p class="text-[12px] font-bold text-gray-700">Demo Credentials</p>
                    <span class="ml-auto text-[10px] bg-violet-100 text-violet-700 font-bold px-2 py-0.5 rounded-full">Prototype</span>
                </div>
                <div class="grid grid-cols-3 gap-2 text-[11.5px]">
                    @foreach([['👑 Owner', 'owner@FreshTrack.ph', 'Full access'], ['📋 Manager', 'manager@FreshTrack.ph', 'Limited'], ['💰 Cashier', 'cashier@FreshTrack.ph', 'Sales only']] as [$r, $e, $a])
                    <div class="bg-white rounded-xl p-2.5 border border-violet-100 text-center">
                        <p class="font-bold text-gray-700 text-[11px]">{{ $r }}</p>
                        <p class="text-violet-600 font-mono text-[9.5px] mt-1">{{ $e }}</p>
                        <p class="text-gray-400 text-[9px] mt-0.5">{{ $a }}</p>
                    </div>
                    @endforeach
                </div>
                <p class="text-center text-[11px] text-gray-400 mt-2.5 font-medium">All accounts use password: <span class="font-mono font-bold text-gray-600">password</span></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
