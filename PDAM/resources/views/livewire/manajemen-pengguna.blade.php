<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Manajemen Pengguna</h1>
            <p class="text-sm text-slate-400 mt-1">Kelola akun pengguna sistem PDAM Monitor</p>
        </div>
        <button wire:click="create"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 transition-all duration-300 hover:scale-[1.02]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
            </svg>
            Tambah Akun
        </button>
    </div>

    {{-- Flash Messages --}}
    @if(session('error'))
        <div class="rounded-xl bg-red-500/10 border border-red-500/30 p-4 text-sm text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Total Pengguna</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $totalUsers }}</p>
        </div>

        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-violet-500/10 text-violet-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Admin</span>
            </div>
            <p class="text-2xl font-bold text-violet-400">{{ $totalAdmin }}</p>
        </div>

        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Petugas</span>
            </div>
            <p class="text-2xl font-bold text-blue-400">{{ $totalPetugas }}</p>
        </div>

        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Aktif</span>
            </div>
            <p class="text-2xl font-bold text-emerald-400">{{ $totalAktif }}</p>
        </div>
    </div>

    {{-- Search & Table --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-base font-semibold text-white">Daftar Pengguna</h3>
            <div class="relative w-full sm:w-72">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari nama, username, no telp..."
                       class="w-full pl-10 pr-4 py-2 rounded-lg bg-slate-900/60 border border-slate-700/50 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/40">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Pengguna</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Username</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Role</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">No. HP</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @forelse ($users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-slate-700/20 transition-colors duration-150">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex items-center justify-center w-9 h-9 rounded-full {{ $user->isAdmin() ? 'bg-gradient-to-br from-violet-500 to-purple-600' : 'bg-gradient-to-br from-cyan-500 to-blue-600' }} text-white text-xs font-bold shrink-0">
                                        {{ $user->initials() }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-white">{{ $user->name }}</p>

                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-slate-300 font-mono text-xs">{{ $user->username ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $user->isAdmin() ? 'bg-violet-500/15 text-violet-400 border-violet-500/20' : 'bg-cyan-500/15 text-cyan-400 border-cyan-500/20' }}">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="text-slate-400 text-xs">{{ $user->phone ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <button wire:click="toggleActive({{ $user->id }})"
                                        wire:confirm="{{ $user->is_active ? 'Nonaktifkan akun ' . $user->name . '?' : 'Aktifkan kembali akun ' . $user->name . '?' }}"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium transition-colors cursor-pointer
                                        {{ $user->is_active ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/25' : 'bg-red-500/15 text-red-400 border border-red-500/20 hover:bg-red-500/25' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </button>
                            </td>
                            <td class="px-6 py-3.5 text-center flex items-center justify-center gap-2">
                                <button wire:click="edit({{ $user->id }})"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-cyan-400 hover:bg-cyan-500/10 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    Edit
                                </button>
                                <button wire:click="delete({{ $user->id }})"
                                        wire:confirm="Yakin ingin menghapus akun {{ $user->name }} secara permanen?"
                                        class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-medium text-red-400 hover:bg-red-500/10 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    Hapus
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <svg class="w-12 h-12 mx-auto mb-3 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Tidak ada pengguna ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($users->hasPages())
            <div class="px-6 py-4 border-t border-slate-700/40">
                {{ $users->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Form --}}
    <div x-data="{ open: false }"
         x-on:open-user-modal.window="open = true"
         x-on:close-user-modal.window="open = false"
         x-cloak>

        {{-- Backdrop --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"></div>

        {{-- Modal --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0" x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="bg-slate-900 border border-slate-700/60 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.outside="open = false">

                <div class="px-6 py-4 border-b border-slate-700/40">
                    <h3 class="text-lg font-semibold text-white">{{ $editingId ? 'Edit Pengguna' : 'Tambah Pengguna Baru' }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $editingId ? 'Perbarui data akun pengguna' : 'Buat akun baru untuk sistem PDAM Monitor' }}</p>
                </div>

                <form wire:submit="save" class="px-6 py-4 space-y-4">
                    {{-- Nama --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Nama Lengkap</label>
                        <input wire:model="name" type="text"
                               class="w-full px-3 py-2.5 rounded-lg bg-slate-800/60 border border-slate-700/50 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40"
                               placeholder="Nama lengkap pengguna">
                        @error('name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Username --}}
                    <div>
                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Username</label>
                        <input wire:model="username" type="text"
                               class="w-full px-3 py-2.5 rounded-lg bg-slate-800/60 border border-slate-700/50 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40"
                               placeholder="Username untuk login">
                        @error('username') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>


                    {{-- Password --}}
                    <div class="grid grid-cols-2 gap-4" x-data="{ showPassword: false }">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Password {{ $editingId ? '(kosongkan jika tidak diubah)' : '' }}</label>
                            <div class="relative">
                                <input wire:model="password" :type="showPassword ? 'text' : 'password'"
                                       class="w-full pl-3 pr-10 py-2.5 rounded-lg bg-slate-800/60 border border-slate-700/50 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40"
                                       placeholder="Min. 8 karakter">
                                <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-200 focus:outline-none">
                                    <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                </button>
                            </div>
                            @error('password') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Konfirmasi Password</label>
                            <div class="relative">
                                <input wire:model="password_confirmation" :type="showPassword ? 'text' : 'password'"
                                       class="w-full pl-3 pr-10 py-2.5 rounded-lg bg-slate-800/60 border border-slate-700/50 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40"
                                       placeholder="Ulangi password">
                            </div>
                        </div>
                    </div>

                    {{-- Phone & Role --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">No. HP / WhatsApp</label>
                            <input wire:model="phone" type="text"
                                   class="w-full px-3 py-2.5 rounded-lg bg-slate-800/60 border border-slate-700/50 text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40"
                                   placeholder="08xxxxxxxxxx">
                            @error('phone') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-400 mb-1.5">Role</label>
                            <select wire:model="role"
                                    class="w-full px-3 py-2.5 rounded-lg bg-slate-800/60 border border-slate-700/50 text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40">
                                <option value="petugas">Petugas</option>
                                <option value="admin">Admin</option>
                            </select>
                            @error('role') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-700/40">
                        <button type="button" @click="open = false"
                                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-700/50 transition-colors">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2 rounded-lg bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold shadow-lg shadow-cyan-500/25 hover:shadow-cyan-500/40 transition-all">
                            {{ $editingId ? 'Simpan Perubahan' : 'Buat Akun' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
