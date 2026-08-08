<div wire:poll.10s x-data="{ showDeleteModal: false, deleteType: 'single', deleteId: null, deleteMessage: 'Yakin ingin menghapus aset ini?' }">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Manajemen Master Aset</h1>
        <p class="text-sm text-slate-400 mt-1">Kelola data master Gate Valve dan lokasi distribusi</p>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('success'))
        <div class="mb-6 px-4 py-3 bg-emerald-500/10 border border-emerald-500/20 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="text-sm text-emerald-400">{{ session('success') }}</p>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-6 px-4 py-3 bg-red-500/10 border border-red-500/20 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <p class="text-sm text-red-400">{{ session('error') }}</p>
        </div>
    @endif

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
        @if(auth()->user()->isAdmin())
        @if(count($selectedRows) > 0)
            <button
                @click="deleteType = 'multiple'; deleteMessage = 'Yakin ingin menghapus {{ count($selectedRows) }} aset terpilih?'; showDeleteModal = true;"
                class="flex items-center justify-center gap-2 px-5 py-2.5 bg-red-500 text-white text-sm font-semibold rounded-xl hover:bg-red-600 shadow-lg shadow-red-500/25 transition-all duration-300 hover:shadow-red-500/40"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Terpilih ({{ count($selectedRows) }})
            </button>
        @endif
        <button
            wire:click="create"
            class="flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/25 transition-all duration-300 hover:shadow-cyan-500/40 hover:-translate-y-0.5 active:translate-y-0"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Tambah Aset
        </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800/50">
                        @if(auth()->user()->isAdmin())
                        <th class="px-5 py-3.5 w-12 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-700 bg-slate-900/50 text-cyan-500 focus:ring-cyan-500/30 focus:ring-offset-slate-900">
                        </th>
                        @endif
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Aset</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Koordinat</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Kapasitas Full</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tutupan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider min-w-48">Sisa Bukaan</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @forelse ($asets as $aset)
                        <tr wire:key="aset-{{ $aset->id }}" class="hover:bg-slate-800/30 transition-colors duration-150 {{ in_array($aset->id, $selectedRows) ? 'bg-cyan-500/5' : '' }}">
                            @if(auth()->user()->isAdmin())
                            <td class="px-5 py-4">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $aset->id }}" class="w-4 h-4 rounded border-slate-700 bg-slate-900/50 text-cyan-500 focus:ring-cyan-500/30 focus:ring-offset-slate-900">
                            </td>
                            @endif
                            <td class="px-5 py-4">
                                <span class="font-semibold text-white">{{ $aset->nama_aset }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-800/50 text-xs font-medium text-slate-300">
                                    {{ $aset->lokasi->nama_lokasi }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($aset->latitude && $aset->longitude)
                                    <div class="flex flex-col items-center gap-1">
                                        <span class="text-xs text-slate-400 font-mono">{{ $aset->latitude }}, {{ $aset->longitude }}</span>
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $aset->latitude }},{{ $aset->longitude }}" target="_blank"
                                           class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 text-xs font-medium transition-colors">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Maps
                                        </a>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ \App\Helpers\FormatHelper::putaran($aset->kapasitas_full_putaran) }}</td>
                            <td class="px-5 py-4 text-center text-slate-300">{{ \App\Helpers\FormatHelper::putaran($aset->total_tutupan_saat_ini) }}</td>
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
                                    <span class="text-xs font-semibold text-slate-300 w-14 text-right">{{ \App\Helpers\FormatHelper::putaran($aset->sisa_bukaan) }} ({{ $persen }}%)</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if(auth()->user()->isAdmin())
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
                                        @click="deleteType = 'single'; deleteId = {{ $aset->id }}; deleteMessage = 'Yakin ingin menghapus aset ini?'; showDeleteModal = true;"
                                        class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition"
                                        title="Hapus"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                                @else
                                <span class="text-xs text-slate-500">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
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
                    class="relative w-full max-w-lg flex flex-col max-h-[90vh] bg-slate-900 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50"
                >
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/50 shrink-0">
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
                    <form wire:submit.prevent="save" class="p-6 space-y-5 overflow-y-auto">
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

                        {{-- Titik Koordinat (Latitude & Longitude) --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Titik Koordinat
                                </span>
                            </label>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <input
                                        type="number"
                                        wire:model="latitude"
                                        step="0.0000001"
                                        min="-90"
                                        max="90"
                                        placeholder="Latitude (cth: -7.2575)"
                                        class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                                    >
                                    @error('latitude')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <input
                                        type="number"
                                        wire:model="longitude"
                                        step="0.0000001"
                                        min="-180"
                                        max="180"
                                        placeholder="Longitude (cth: 112.7521)"
                                        class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                                    >
                                    @error('longitude')
                                        <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-500">Opsional. Masukkan koordinat GPS untuk menandai lokasi di peta.</p>
                        </div>

                        {{-- Kapasitas Full Putaran --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Kapasitas Full Putaran</label>
                            
                            {{-- Input Angka Bulat --}}
                            <div class="mb-3">
                                <label class="block text-xs font-medium text-slate-400 mb-1">Angka Bulat</label>
                                <input
                                    type="number"
                                    wire:model.live="kapasitas_full_putaran"
                                    step="1"
                                    min="0"
                                    placeholder="Contoh: 58"
                                    class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                                >
                            </div>

                            {{-- Quick Buttons Pecahan --}}
                            <div class="mb-3">
                                <label class="block text-xs font-medium text-slate-400 mb-1.5">Tambahan Pecahan</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach([
                                        ['0', '0 (Bulat)'],
                                        ['0.125', '⅛'],
                                        ['0.25', '¼'],
                                        ['0.375', '⅜'],
                                        ['0.5', '½'],
                                        ['0.625', '⅝'],
                                        ['0.75', '¾'],
                                        ['0.875', '⅞'],
                                    ] as [$val, $label])
                                        <button
                                            type="button"
                                            wire:click="$set('kapasitas_full_pecahan', '{{ $val }}')"
                                            class="px-4 py-2 rounded-lg text-sm font-bold border transition-all duration-200
                                                {{ $kapasitas_full_pecahan === $val
                                                    ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50 shadow-sm shadow-cyan-500/10'
                                                    : 'bg-slate-800/50 text-slate-400 border-slate-700/50 hover:bg-slate-700/50 hover:text-white' }}"
                                        >
                                            {{ $label }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Preview Nilai Gabungan --}}
                            @if($kapasitas_full_putaran !== '')
                                @php
                                    $previewKapasitas = (float) $kapasitas_full_putaran + (float) $kapasitas_full_pecahan;
                                    $pecahanMapKap = ['0' => '', '0.125' => ' ⅛', '0.25' => ' ¼', '0.375' => ' ⅜', '0.5' => ' ½', '0.625' => ' ⅝', '0.75' => ' ¾', '0.875' => ' ⅞'];
                                    $pecahanLabelKap = $pecahanMapKap[$kapasitas_full_pecahan] ?? '';
                                    $displayKapasitas = $kapasitas_full_putaran . $pecahanLabelKap;
                                @endphp
                                <div class="flex items-center gap-3 p-3 rounded-xl bg-emerald-500/5 border border-emerald-500/20">
                                    <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <div>
                                        <p class="text-xs text-slate-400">Kapasitas Full Putaran:</p>
                                        <p class="text-sm font-bold text-emerald-300">{{ $displayKapasitas }} putaran <span class="text-slate-500 font-normal">(= {{ $previewKapasitas }})</span></p>
                                    </div>
                                </div>
                            @endif

                            @error('kapasitas_full_putaran')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Kondisi Awal (Hanya saat tambah) --}}
                        @if(!$editingId)
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-2">Kondisi Saat Ini (Posisi Valve)</label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-700/50 bg-slate-800/30 cursor-pointer hover:bg-slate-800 transition">
                                    <input type="radio" wire:model.live="kondisi_awal" value="buka_full" class="text-cyan-500 bg-slate-900 border-slate-700 focus:ring-cyan-500/40">
                                    <span class="text-sm text-slate-300">Buka Full</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-700/50 bg-slate-800/30 cursor-pointer hover:bg-slate-800 transition">
                                    <input type="radio" wire:model.live="kondisi_awal" value="tutup_full" class="text-cyan-500 bg-slate-900 border-slate-700 focus:ring-cyan-500/40">
                                    <span class="text-sm text-slate-300">Tutup Full</span>
                                </label>
                                <label class="flex items-center gap-2 p-3 rounded-xl border border-slate-700/50 bg-slate-800/30 cursor-pointer hover:bg-slate-800 transition">
                                    <input type="radio" wire:model.live="kondisi_awal" value="custom" class="text-cyan-500 bg-slate-900 border-slate-700 focus:ring-cyan-500/40">
                                    <span class="text-sm text-slate-300">Sebagian</span>
                                </label>
                            </div>
                            @if($kondisi_awal === 'custom')
                                <div class="mt-3 space-y-3">
                                    {{-- Input Angka Bulat --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-400 mb-1">Angka Bulat Putaran</label>
                                        <input
                                            type="number"
                                            wire:model.live="custom_tutupan_bulat"
                                            step="1"
                                            min="0"
                                            placeholder="Contoh: 23"
                                            class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                                        >
                                    </div>

                                    {{-- Quick Buttons Pecahan --}}
                                    <div>
                                        <label class="block text-xs font-medium text-slate-400 mb-1.5">Tambahan Pecahan</label>
                                        <div class="flex flex-wrap gap-2">
                                            @foreach([
                                                ['0', '0 (Bulat)'],
                                                ['0.125', '⅛'],
                                                ['0.25', '¼'],
                                                ['0.375', '⅜'],
                                                ['0.5', '½'],
                                                ['0.625', '⅝'],
                                                ['0.75', '¾'],
                                                ['0.875', '⅞'],
                                            ] as [$val, $label])
                                                <button
                                                    type="button"
                                                    wire:click="$set('custom_tutupan_pecahan', '{{ $val }}')"
                                                    class="px-4 py-2 rounded-lg text-sm font-bold border transition-all duration-200
                                                        {{ $custom_tutupan_pecahan === $val
                                                            ? 'bg-cyan-500/20 text-cyan-300 border-cyan-500/50 shadow-sm shadow-cyan-500/10'
                                                            : 'bg-slate-800/50 text-slate-400 border-slate-700/50 hover:bg-slate-700/50 hover:text-white' }}"
                                                >
                                                    {{ $label }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Preview Nilai Gabungan --}}
                                    @if($custom_tutupan_bulat !== '')
                                        @php
                                            $previewTotal = (float) $custom_tutupan_bulat + (float) $custom_tutupan_pecahan;
                                            // Format pecahan untuk tampilan
                                            $pecahanMap = ['0' => '', '0.125' => ' ⅛', '0.25' => ' ¼', '0.375' => ' ⅜', '0.5' => ' ½', '0.625' => ' ⅝', '0.75' => ' ¾', '0.875' => ' ⅞'];
                                            $pecahanLabel = $pecahanMap[$custom_tutupan_pecahan] ?? '';
                                            $displayFraksi = $custom_tutupan_bulat . $pecahanLabel;
                                        @endphp
                                        <div class="flex items-center gap-3 p-3 rounded-xl bg-cyan-500/5 border border-cyan-500/20">
                                            <svg class="w-5 h-5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            <div>
                                                <p class="text-xs text-slate-400">Total putaran tertutup:</p>
                                                <p class="text-sm font-bold text-cyan-300">{{ $displayFraksi }} putaran <span class="text-slate-500 font-normal">(= {{ $previewTotal }})</span></p>
                                            </div>
                                        </div>
                                    @endif

                                    <p class="text-xs text-slate-400">Masukkan nilai putaran <b>yang sedang tertutup</b> saat ini.</p>
                                </div>
                            @endif
                        </div>
                        @endif

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

    {{-- Delete Confirmation Modal --}}
    <div x-show="showDeleteModal" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" 
         style="display: none;">
        <div @click.outside="showDeleteModal = false" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-90"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-90"
             class="bg-slate-900 border border-slate-700/50 rounded-xl p-5 w-80 shadow-2xl relative overflow-hidden">
            <div class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-red-500 to-rose-500"></div>
            <div class="flex items-start gap-3 mb-4">
                <div class="flex-shrink-0 w-9 h-9 rounded-full bg-red-500/10 flex items-center justify-center mt-0.5">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Konfirmasi Hapus</h3>
                    <p class="text-xs text-slate-400 mt-0.5" x-text="deleteMessage"></p>
                </div>
            </div>
            <div class="flex justify-end gap-2">
                <button @click="showDeleteModal = false" class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-300 bg-slate-800 hover:bg-slate-700 transition-colors">Batal</button>
                <button @click="if(deleteType === 'single') { $wire.delete(deleteId) } else { $wire.deleteSelected() }; showDeleteModal = false" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-500 text-white hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
