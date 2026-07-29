{{-- Login Page — PDAM Monitor --}}
{{-- PRD §5.1: Centered card, branding, focus ring biru, dark theme --}}

<div class="min-h-screen flex items-center justify-center px-4 py-12 relative overflow-hidden">

    {{-- Background decorative elements --}}
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        {{-- Gradient orbs --}}
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-cyan-500/10 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-blue-600/10 rounded-full blur-3xl"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-slate-800/20 rounded-full blur-3xl"></div>

        {{-- Subtle grid pattern --}}
        <div class="absolute inset-0 opacity-[0.02]"
             style="background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.3) 1px, transparent 0); background-size: 40px 40px;">
        </div>
    </div>

    {{-- Login Card --}}
    <div class="relative w-full max-w-md">

        {{-- Glowing border effect --}}
        <div class="absolute -inset-0.5 bg-gradient-to-r from-cyan-500/20 via-blue-600/20 to-cyan-500/20 rounded-2xl blur-sm opacity-75"></div>

        <div class="relative bg-slate-900/90 backdrop-blur-xl border border-slate-800/60 rounded-2xl shadow-2xl shadow-black/40 p-8">

            {{-- Logo & Branding --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center mb-4">
                    <img src="/images/TIRTAFLOWBGPUTIH.png" alt="Tirta Flow" style="width: 80px; height: 80px;" class="rounded-full border-4 border-slate-800 object-cover shadow-xl shadow-black/50">
                </div>
                <h1 class="text-2xl font-bold text-white tracking-tight">Tirta Flow</h1>
                <p class="text-sm text-slate-400 mt-1">Sistem Informasi Setting Pipa Gate Valve dan Tekanan Air</p>
            </div>

            {{-- Divider --}}
            <div class="flex items-center gap-3 mb-6">
                <div class="flex-1 h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
                <span class="text-xs text-slate-500 font-medium uppercase tracking-wider">Masuk ke Akun</span>
                <div class="flex-1 h-px bg-gradient-to-r from-transparent via-slate-700 to-transparent"></div>
            </div>

            {{-- Login Form --}}
            <form wire:submit="authenticate" class="space-y-5">

                {{-- Global Error Alert --}}
                @if ($errors->has('login') && !$errors->has('password'))
                    <div class="flex items-start gap-3 p-3.5 rounded-xl bg-red-500/10 border border-red-500/20">
                        <svg class="w-5 h-5 text-red-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <p class="text-sm text-red-300">{{ $errors->first('login') }}</p>
                    </div>
                @endif

                {{-- Login Input --}}
                <div>
                    <label for="login" class="block text-sm font-medium text-slate-300 mb-2">
                        Email / Username
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input
                            wire:model="login"
                            type="text"
                            id="login"
                            placeholder="nama@pdam.go.id atau username"
                            autocomplete="username"
                            autofocus
                            class="w-full pl-11 pr-4 py-3 bg-slate-800/60 border border-slate-700/60 rounded-xl text-white placeholder-slate-500 text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 hover:border-slate-600"
                        >
                    </div>
                    @error('login')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Password Input --}}
                <div x-data="{ showPassword: false }">
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            wire:model="password"
                            :type="showPassword ? 'text' : 'password'"
                            id="password"
                            placeholder="Masukkan password"
                            autocomplete="current-password"
                            class="w-full pl-11 pr-12 py-3 bg-slate-800/60 border border-slate-700/60 rounded-xl text-white placeholder-slate-500 text-sm transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:border-blue-500/50 hover:border-slate-600"
                        >
                        {{-- Toggle password visibility --}}
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300 transition-colors">
                            <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            wire:model="remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-slate-600 bg-slate-800 text-cyan-500 focus:ring-cyan-500/30 focus:ring-offset-0 transition-colors"
                        >
                        <span class="text-sm text-slate-400 group-hover:text-slate-300 transition-colors">Ingat saya</span>
                    </label>
                </div>

                {{-- Submit Button --}}
                <button
                    type="submit"
                    class="relative w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-semibold text-sm rounded-xl shadow-lg shadow-blue-700/25 hover:shadow-blue-600/30 transition-all duration-300 focus:outline-none focus:ring-2 focus:ring-blue-500/50 focus:ring-offset-2 focus:ring-offset-slate-900 disabled:opacity-50 disabled:cursor-not-allowed"
                    wire:loading.attr="disabled"
                >
                    {{-- Normal State --}}
                    <span wire:loading.remove wire:target="authenticate" class="flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Masuk
                    </span>

                    {{-- Loading State --}}
                    <span wire:loading wire:target="authenticate" class="flex items-center justify-center gap-2">
                        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Memproses...
                    </span>
                </button>
            </form>

            {{-- Footer --}}
            <div class="mt-8 pt-6 border-t border-slate-800/60 text-center">
                <p class="text-xs text-slate-500">
                    PDAM Monitor v1.0 — Sistem Informasi Manajemen Aset
                </p>
                <p class="text-xs text-slate-600 mt-1">
                    &copy; {{ date('Y') }} PDAM Kabupaten Pemalang
                </p>
            </div>
        </div>
    </div>
</div>
