<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Manajemen Master Aset</h1>
        <p class="text-sm text-slate-400 mt-1">Kelola data master Gate Valve dan lokasi distribusi</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Aset --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/10">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Aset</p>
                    <p class="text-2xl font-bold text-white">{{ $totalAset }}</p>
                </div>
            </div>
        </div>

        {{-- Total Lokasi --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-violet-500/10">
                    <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Lokasi</p>
                    <p class="text-2xl font-bold text-white">{{ $totalLokasi }}</p>
                </div>
            </div>
        </div>

        {{-- Rata-rata Bukaan --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/10">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Rata-rata Bukaan</p>
                    <p class="text-2xl font-bold text-white">{{ $rataPersentase }}%</p>
                </div>
            </div>
        </div>

        {{-- Aset Kritis --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-red-500/10">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Aset Kritis</p>
                    <p class="text-2xl font-bold text-red-400">{{ $asetKritis }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Actions --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari aset atau lokasi..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
            >
        </div>
        <button
            wire:click="create"
            class="flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/25 transition-all duration-300 hover:shadow-cyan-500/40 hover:-translate-y-0.5 active:translate-y-0"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Tambah Aset
        </button>
    </div>

    {{-- Table --}}
    <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800/50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Aset</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Kapasitas Full</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tutupan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider min-w-48">Sisa Bukaan</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @forelse ($asets as $aset)
                        <tr wire:key="aset-{{ $aset->id }}" class="hover:bg-slate-800/30 transition-colors duration-150">
                            <td class="px-5 py-4">
                                <span class="font-semibold text-white">{{ $aset->nama_aset }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-800/50 text-xs font-medium text-slate-300">
                                    {{ $aset->lokasi->nama_lokasi }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ $aset->kapasitas_full_putaran }}</td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ $aset->total_tutupan_saat_ini }}</td>
                            <td class="px-5 py-4">
                                @php
                                    $persen = $aset->persentase_bukaan;
                                    $colorClass = $persen >= 60 ? 'bg-emerald-500' : ($persen >= 25 ? 'bg-amber-500' : 'bg-red-500');
                                    $glowClass = $persen >= 60 ? 'shadow-emerald-500/30' : ($persen >= 25 ? 'shadow-amber-500/30' : 'shadow-red-500/30');
                                @endphp
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2.5 bg-slate-800 rounded-full overflow-hidden">
                                        <div class="{{ $colorClass }} h-full rounded-full transition-all duration-500 shadow-sm {{ $glowClass }}" style="width: {{ min($persen, 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-slate-300 w-14 text-right">{{ $aset->sisa_bukaan }} ({{ $persen }}%)</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        wire:click="edit({{ $aset->id }})"
                                        class="p-2 rounded-lg text-slate-400 hover:text-cyan-400 hover:bg-cyan-500/10 transition"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        wire:click="delete({{ $aset->id }})"
                                        wire:confirm="Yakin ingin menghapus aset ini?"
                                        class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 text-slate-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-slate-500 text-sm">Belum ada data aset</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($asets->hasPages())
            <div class="px-5 py-4 border-t border-slate-800/50">
                {{ $asets->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Tambah/Edit (Alpine.js) --}}
    <div
        x-data="{ open: false }"
        @open-modal.window="open = true"
        @close-modal.window="open = false"
        @keydown.escape.window="open = false"
    >
        <template x-teleport="body">
            <div
                x-show="open"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[60] flex items-center justify-center p-4"
                style="display: none;"
            >
                {{-- Backdrop --}}
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="open = false"></div>

                {{-- Modal Content --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-lg bg-slate-900 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50"
                >
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/50">
                        <h3 class="text-lg font-bold text-white">
                            {{ $editingId ? 'Edit Aset' : 'Tambah Aset Baru' }}
                        </h3>
                        <button @click="open = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Form --}}
                    <form wire:submit.prevent="save" class="p-6 space-y-5">
                        {{-- Nama Aset --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Nama Aset</label>
                            <input
                                type="text"
                                wire:model="nama_aset"
                                placeholder="Contoh: GV 6&quot;"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            @error('nama_aset')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama Lokasi --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Lokasi</label>
                            <input
                                type="text"
                                wire:model="nama_lokasi"
                                list="lokasi-list"
                                placeholder="Nama lokasi (autocomplete atau buat baru)"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            <datalist id="lokasi-list">
                                @foreach ($this->lokasiList as $lok)
                                    <option value="{{ $lok }}">
                                @endforeach
                            </datalist>
                            @error('nama_lokasi')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kapasitas Full Putaran --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Kapasitas Full Putaran</label>
                            <input
                                type="number"
                                wire:model="kapasitas_full_putaran"
                                step="0.01"
                                min="0.01"
                                placeholder="Contoh: 58.50"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            @error('kapasitas_full_putaran')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" @click="open = false" class="px-5 py-2.5 text-sm font-medium text-slate-400 hover:text-white bg-slate-800/50 hover:bg-slate-800 rounded-xl transition">
                                Batal
                            </button>
                            <button type="submit" class="px-5 py-2.5 text-sm font-semibold text-white bg-gradient-to-r from-cyan-500 to-blue-600 rounded-xl hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/25 transition-all duration-300">
                                <span wire:loading.remove wire:target="save">{{ $editingId ? 'Simpan Perubahan' : 'Tambah Aset' }}</span>
                                <span wire:loading wire:target="save" class="flex items-center gap-2">
                                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" fill="none"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                    Menyimpan...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>
</div>
