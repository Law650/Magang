<div wire:poll.10s x-data="{ showDeleteModal: false, deleteType: 'single', deleteId: null, deleteMessage: 'Yakin ingin menghapus daerah ini?' }">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Manajemen Data Pelanggan & Tekanan</h1>
        <p class="text-sm text-slate-400 mt-1">Kelola data pelanggan dan titik koordinat untuk monitoring tekanan air</p>
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
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Pelanggan</p>
                    <p class="text-2xl font-bold text-white">{{ $totalLokasi }}</p>
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
                placeholder="Cari No SR, Nama, atau Desa..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
            >
        </div>
        @if(auth()->user()->isAdmin())
        @if(count($selectedRows) > 0)
            <button
                @click="deleteType = 'multiple'; deleteMessage = 'Yakin ingin menghapus {{ count($selectedRows) }} daerah terpilih?'; showDeleteModal = true;"
                class="flex items-center justify-center gap-2 px-5 py-2.5 bg-red-500 text-white text-sm font-semibold rounded-xl hover:bg-red-600 shadow-lg shadow-red-500/25 transition-all duration-300 hover:shadow-red-500/40"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus Terpilih ({{ count($selectedRows) }})
            </button>
        @endif
        <button
            @click="$dispatch('open-import-modal')"
            class="flex items-center justify-center gap-2 px-5 py-2.5 bg-emerald-600/20 text-emerald-400 border border-emerald-500/30 text-sm font-semibold rounded-xl hover:bg-emerald-600/30 transition-all duration-300"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Import Excel
        </button>
        <button
            wire:click="create"
            class="flex items-center justify-center gap-2 px-5 py-2.5 bg-gradient-to-r from-cyan-500 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-cyan-400 hover:to-blue-500 shadow-lg shadow-cyan-500/25 transition-all duration-300 hover:shadow-cyan-500/40 hover:-translate-y-0.5 active:translate-y-0"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Tambah Data
        </button>
        @endif
    </div>

    {{-- Table --}}
    <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                        @if(auth()->user()->isAdmin())
                        <th class="px-5 py-3.5 w-12 text-left">
                            <input type="checkbox" wire:model.live="selectAll" class="w-4 h-4 rounded border-slate-700 bg-slate-900/50 text-cyan-500 focus:ring-cyan-500/30 focus:ring-offset-slate-900">
                        </th>
                        @endif
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">No. SR</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Pelanggan</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Alamat</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Desa</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Koordinat</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @forelse ($lokasis as $lokasi)
                        <tr wire:key="lokasi-{{ $lokasi->id }}" class="hover:bg-slate-800/30 transition-colors duration-150 {{ in_array($lokasi->id, $selectedRows) ? 'bg-cyan-500/5' : '' }}">
                            @if(auth()->user()->isAdmin())
                            <td class="px-5 py-4">
                                <input type="checkbox" wire:model.live="selectedRows" value="{{ $lokasi->id }}" class="w-4 h-4 rounded border-slate-700 bg-slate-900/50 text-cyan-500 focus:ring-cyan-500/30 focus:ring-offset-slate-900">
                            </td>
                            @endif
                            <td class="px-5 py-4">
                                <span class="font-semibold text-cyan-400">{{ $lokasi->no_sr ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-semibold text-white block">{{ $lokasi->nama_pelanggan ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="text-sm text-slate-300 block truncate max-w-[200px]" title="{{ $lokasi->alamat }}">{{ $lokasi->alamat ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-300">
                                {{ $lokasi->desa ?? $lokasi->nama_lokasi ?? '-' }}
                            </td>
                            <td class="px-5 py-4 text-slate-300 text-xs">
                                @if($lokasi->latitude && $lokasi->longitude)
                                    <div>{{ $lokasi->latitude }} , {{ $lokasi->longitude }}</div>
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $lokasi->latitude }},{{ $lokasi->longitude }}" target="_blank"
                                       class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 transition-colors">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Maps
                                    </a>
                                @else
                                    <span class="text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if(auth()->user()->isAdmin())
                                <div class="flex items-center justify-center gap-1">
                                    <button
                                        wire:click="edit({{ $lokasi->id }})"
                                        class="p-2 rounded-lg text-slate-400 hover:text-cyan-400 hover:bg-cyan-500/10 transition"
                                        title="Edit"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button
                                        @click="deleteType = 'single'; deleteId = {{ $lokasi->id }}; deleteMessage = 'Yakin ingin menghapus daerah ini?'; showDeleteModal = true;"
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
                            <td colspan="6" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 text-slate-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-slate-500 text-sm">Belum ada data pelanggan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($lokasis->hasPages())
            <div class="px-5 py-4 border-t border-slate-800/50">
                {{ $lokasis->links() }}
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
                            {{ $editingId ? 'Edit Data Pelanggan' : 'Tambah Data Pelanggan' }}
                        </h3>
                        <button @click="open = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Form --}}
                    <form wire:submit.prevent="save" class="p-6 space-y-5">
                        {{-- No SR --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">No. Sambung (SR) *</label>
                            <input
                                type="text"
                                wire:model="no_sr"
                                placeholder="Contoh: 12070225"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            @error('no_sr')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Nama Pelanggan --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Nama Pelanggan *</label>
                            <input
                                type="text"
                                wire:model="nama_pelanggan"
                                placeholder="Contoh: Nur Sugiarti"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            @error('nama_pelanggan')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Alamat --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Alamat</label>
                            <input
                                type="text"
                                wire:model="alamat"
                                placeholder="Contoh: Jl. Pucuk Barata"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            @error('alamat')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Desa --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Desa *</label>
                            <input
                                type="text"
                                wire:model="desa"
                                placeholder="Contoh: Banyumudal"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            @error('desa')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        {{-- Latitude --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Latitude (Opsional)</label>
                            <input
                                type="text"
                                wire:model="latitude"
                                placeholder="Contoh: -6.9090"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            @error('latitude')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Longitude --}}
                        <div>
                            <label class="block text-sm font-medium text-slate-300 mb-1.5">Longitude (Opsional)</label>
                            <input
                                type="text"
                                wire:model="longitude"
                                placeholder="Contoh: 109.3816"
                                class="w-full px-4 py-2.5 bg-slate-800/50 border border-slate-700/50 rounded-xl text-sm text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                            >
                            @error('longitude')
                                <p class="mt-1 text-xs text-red-400">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Submit Button --}}
                        <div class="pt-2 flex justify-end gap-3">
                            <button
                                type="button"
                                @click="open = false"
                                class="px-5 py-2.5 text-sm font-medium text-slate-300 hover:text-white transition"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                class="px-5 py-2.5 bg-cyan-500 text-white text-sm font-semibold rounded-xl hover:bg-cyan-400 shadow-lg shadow-cyan-500/25 transition-all duration-300 hover:shadow-cyan-500/40"
                            >
                                Simpan Data
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>
    </div>

    {{-- Modal Import Excel (Alpine.js) --}}
    <div
        x-data="{ importOpen: false }"
        @open-import-modal.window="importOpen = true"
        @close-import-modal.window="importOpen = false"
        @keydown.escape.window="importOpen = false"
    >
        <template x-teleport="body">
            <div
                x-show="importOpen"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 z-[60] flex items-center justify-center p-4"
                style="display: none;"
            >
                <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="importOpen = false"></div>

                <div
                    x-show="importOpen"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-md bg-slate-900 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50 overflow-hidden"
                >
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-800/50">
                        <h3 class="text-lg font-bold text-white">Import Data Pelanggan</h3>
                        <button @click="importOpen = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form wire:submit.prevent="importExcel" class="p-6 relative">
                        {{-- Loading Overlay --}}
                        <div wire:loading wire:target="importExcel, excelFile" class="absolute inset-0 z-10 bg-slate-900/80 backdrop-blur-sm flex flex-col items-center justify-center rounded-b-2xl">
                            <svg class="animate-spin h-10 w-10 text-emerald-500 mb-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <p class="text-sm font-semibold text-emerald-400" wire:loading wire:target="importExcel">Sedang memproses data...</p>
                            <p class="text-sm font-semibold text-emerald-400" wire:loading wire:target="excelFile">Sedang mengunggah file...</p>
                            <p class="text-xs text-slate-400 mt-1">Mohon tunggu sebentar</p>
                        </div>

                        <div class="mb-4" x-data="{ isDropping: false }">
                            <p class="text-sm text-slate-400 mb-4">Upload file Excel (.xlsx atau .xls) dengan format kolom: <br><strong class="text-white">No | No. Sambung | Nama Pelanggan | Alamat | Desa | Latitude | Longitude</strong></p>
                            
                            <label 
                                @dragover.prevent="isDropping = true"
                                @dragleave.prevent="isDropping = false"
                                @drop.prevent="isDropping = false; $refs.fileInput.files = $event.dataTransfer.files; $refs.fileInput.dispatchEvent(new Event('change', { bubbles: true }));"
                                :class="{ 'border-cyan-500 bg-cyan-500/10 scale-[1.02] shadow-lg shadow-cyan-500/10': isDropping, 'border-slate-700/50 bg-slate-800/20 hover:bg-slate-800/50': !isDropping }"
                                class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed rounded-xl cursor-pointer transition-all duration-300">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6 pointer-events-none">
                                    <svg :class="{'text-cyan-400': isDropping, 'text-slate-400': !isDropping}" class="w-10 h-10 mb-3 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    <p class="mb-2 text-sm text-slate-300"><span class="font-semibold text-cyan-400">Klik untuk upload</span> atau Drag & Drop file ke sini</p>
                                    <p class="text-xs text-slate-500">XLSX, XLS (Max 10MB)</p>
                                </div>
                                <input x-ref="fileInput" type="file" wire:model="excelFile" accept=".xlsx,.xls,.csv" class="hidden" />
                            </label>
                            
                            @if ($excelFile)
                                <div class="mt-3 px-3 py-2 bg-emerald-500/10 border border-emerald-500/20 rounded-lg flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <p class="text-sm font-medium text-emerald-400 truncate max-w-[200px] sm:max-w-[300px]" title="{{ $excelFile->getClientOriginalName() }}">
                                            {{ $excelFile->getClientOriginalName() }}
                                        </p>
                                    </div>
                                    <button type="button" wire:click="$set('excelFile', null)" class="text-slate-400 hover:text-red-400 transition" title="Hapus File">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </div>
                            @endif
                            @error('excelFile')
                                <p class="mt-2 text-xs text-red-400 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div class="flex justify-end gap-3 mt-6">
                            <button type="button" @click="importOpen = false" class="px-4 py-2 text-sm font-medium text-slate-300 hover:text-white transition">Batal</button>
                            <button type="submit" wire:loading.attr="disabled" class="px-4 py-2 bg-emerald-600 text-white text-sm font-semibold rounded-xl hover:bg-emerald-500 transition-colors flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <span wire:loading.remove wire:target="importExcel">Import Data</span>
                                <span wire:loading wire:target="importExcel">Mengimport...</span>
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
                <button @click="if(deleteType === 'single') { $wire.deleteSingle(deleteId) } else { $wire.deleteSelected() }; showDeleteModal = false" class="px-3 py-1.5 rounded-lg text-xs font-medium bg-red-500 text-white hover:bg-red-600 transition-colors shadow-lg shadow-red-500/20">Ya, Hapus</button>
            </div>
        </div>
    </div>
</div>
