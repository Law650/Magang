<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM">
    <title>{{ $title ?? 'Dashboard' }} — PDAM Monitor</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles

    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>

    {{-- Leaflet.js CDN --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>

    {{-- Leaflet dark popup styling --}}
    <style>
        .dark-popup .leaflet-popup-content-wrapper {
            background: rgba(15, 23, 42, 0.95);
            color: #e2e8f0;
            border-radius: 12px;
            border: 1px solid rgba(51, 65, 85, 0.5);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(12px);
        }
        .dark-popup .leaflet-popup-tip {
            background: rgba(15, 23, 42, 0.95);
            border: 1px solid rgba(51, 65, 85, 0.5);
            border-top: none;
            border-right: none;
        }
        .dark-popup .leaflet-popup-close-button {
            color: #94a3b8 !important;
            font-size: 18px !important;
            padding: 6px 8px !important;
        }
        .dark-popup .leaflet-popup-close-button:hover {
            color: #f1f5f9 !important;
        }
    </style>

    {{-- Google Fonts: Inter --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body class="h-full bg-slate-950 text-slate-200 font-sans antialiased" style="font-family: 'Inter', system-ui, sans-serif;">

    {{-- Mobile menu overlay --}}
    <div x-data="{ sidebarOpen: false }" class="flex h-full">

        {{-- Sidebar --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900/95 backdrop-blur-xl border-r border-slate-800/50 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
        >
            {{-- Logo --}}
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-800/50">
                <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 shadow-lg shadow-cyan-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">PDAM Monitor</h1>
                    <p class="text-xs text-slate-400">Sistem Monitoring Aset</p>
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <p class="px-3 mb-2 text-xs font-semibold tracking-wider text-slate-500 uppercase">Menu Utama</p>

                <a href="{{ route('dashboard') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-cyan-500/10 text-cyan-400 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('manajemen-aset') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('manajemen-aset') ? 'bg-cyan-500/10 text-cyan-400 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Master Aset
                </a>

                <a href="{{ route('log-valve') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('log-valve') ? 'bg-cyan-500/10 text-cyan-400 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Log Valve
                </a>

                <a href="{{ route('log-tekanan') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('log-tekanan') ? 'bg-cyan-500/10 text-cyan-400 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    Log Tekanan
                </a>

                <a href="{{ route('peta-distribusi') }}" wire:navigate
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 {{ request()->routeIs('peta-distribusi') ? 'bg-cyan-500/10 text-cyan-400 shadow-sm' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    Peta Distribusi
                </a>
            </nav>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-slate-800/50">
                <p class="text-xs text-slate-500">PDAM Monitor v1.0</p>
                <p class="text-xs text-slate-600 mt-0.5">TALL Stack Dashboard</p>
            </div>
        </aside>

        {{-- Mobile sidebar backdrop --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/60 backdrop-blur-sm lg:hidden"
        ></div>

        {{-- Main content area --}}
        <div class="flex flex-col flex-1 min-h-0 overflow-hidden">

            {{-- Topbar --}}
            <header class="sticky top-0 z-30 flex items-center justify-between px-4 sm:px-6 h-16 bg-slate-900/80 backdrop-blur-xl border-b border-slate-800/50">
                {{-- Mobile menu button --}}
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>

                {{-- Page title --}}
                <div class="hidden lg:block">
                    <h2 class="text-lg font-semibold text-white">{{ $title ?? 'Dashboard' }}</h2>
                </div>

                {{-- Right side --}}
                <div class="flex items-center gap-3">
                    {{-- Status indicator --}}
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/20">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <span class="text-xs font-medium text-emerald-400">Sistem Aktif</span>
                    </div>

                    {{-- User avatar --}}
                    <div class="flex items-center justify-center w-9 h-9 rounded-full bg-gradient-to-br from-cyan-500 to-blue-600 text-white text-sm font-bold shadow-lg shadow-cyan-500/20">
                        AD
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 overflow-y-auto p-4 sm:p-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
