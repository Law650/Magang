<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Manajemen Peran (Role)</h1>
            <p class="text-sm text-slate-400 mt-1">Konfigurasi hak akses (fungsi) standar untuk setiap peran</p>
        </div>
        <button wire:click="save"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 transition-all duration-300 hover:scale-[1.02]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Simpan Konfigurasi
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('message'))
        <div class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 p-4 text-sm text-emerald-400">
            {{ session('message') }}
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4 text-sm text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Role Configuration Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Super Admin Info (Locked) --}}
        <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden relative">
            <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm z-10 flex flex-col items-center justify-center">
                <svg class="w-10 h-10 text-slate-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <p class="text-sm font-semibold text-slate-400">Super Admin selalu memiliki akses penuh</p>
            </div>
            <div class="px-6 py-4 border-b border-slate-700/40 flex items-center justify-between opacity-50">
                <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                    <div class="w-8 h-8 rounded bg-rose-500/20 text-rose-400 flex items-center justify-center">S</div>
                    Super Admin
                </h3>
            </div>
            <div class="p-6 opacity-50">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($permissions as $perm)
                        <label class="flex items-center gap-2 p-2 rounded border border-slate-700/40 bg-slate-800/40 opacity-70">
                            <input type="checkbox" checked disabled class="w-4 h-4 rounded bg-slate-900 border-slate-600 text-rose-500">
                            <span class="text-sm text-slate-300 font-medium">{{ str_replace('_', ' ', Str::title($perm->name)) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Configurable Roles --}}
        @foreach($roles as $role)
            <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden transition-all duration-300 hover:border-cyan-500/30">
                <div class="px-6 py-4 border-b border-slate-700/40 flex items-center justify-between bg-slate-800/50">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <div class="w-8 h-8 rounded {{ $role->name == 'admin' ? 'bg-violet-500/20 text-violet-400' : 'bg-blue-500/20 text-blue-400' }} flex items-center justify-center font-bold uppercase text-xs">
                            {{ substr($role->name, 0, 1) }}
                        </div>
                        {{ Str::title($role->name) }}
                    </h3>
                </div>
                <div class="p-6">
                    <p class="text-xs text-slate-400 mb-4">Centang fungsi / hak akses bawaan untuk peran {{ Str::title($role->name) }} di bawah ini:</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach($permissions as $perm)
                            <label class="flex items-center gap-2 p-2.5 rounded-lg border border-slate-700/60 bg-slate-800/60 cursor-pointer hover:bg-slate-700/80 hover:border-cyan-500/30 transition-all duration-200">
                                <input type="checkbox" wire:model.defer="rolePermissions.{{ $role->name }}" value="{{ $perm->name }}"
                                       class="w-4 h-4 rounded bg-slate-900 border-slate-600 text-cyan-500 focus:ring-cyan-500 focus:ring-offset-slate-900 cursor-pointer">
                                <span class="text-sm text-slate-200 font-medium select-none">{{ str_replace('_', ' ', Str::title($perm->name)) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        @endforeach

    </div>
</div>
