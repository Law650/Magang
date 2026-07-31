<div wire:poll.30s x-data="{ showDetailModal: false, selectedLog: null, showImageModal: false, selectedImage: null }">
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Riwayat Operasional Valve</h1>
        <p class="text-sm text-slate-400 mt-1">Riwayat aktivitas buka/tutup katup dari petugas lapangan</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Log --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/10">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Log</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($totalLog) }}</p>
                </div>
            </div>
        </div>

        {{-- Jumlah Buka --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/10">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Aksi Buka</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ number_format($jumlahBuka) }}</p>
                </div>
            </div>
        </div>

        {{-- Jumlah Tutup --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-amber-500/10">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Aksi Tutup</p>
                    <p class="text-2xl font-bold text-amber-400">{{ number_format($jumlahTutup) }}</p>
                </div>
            </div>
        </div>

        {{-- Rata-rata Putaran --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-violet-500/10">
                    <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Rata-rata Putaran</p>
                    <p class="text-2xl font-bold text-white">{{ $rataPutaran }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari petugas, aset, atau lokasi..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
            >
        </div>
        {{-- Date Filters --}}
        <div class="flex items-center gap-2">
            <input 
                type="date" 
                wire:model.live="startDate"
                class="px-3 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                title="Tanggal Mulai"
            >
            <span class="text-slate-500">-</span>
            <input 
                type="date" 
                wire:model.live="endDate"
                class="px-3 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-sm text-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
                title="Tanggal Akhir"
            >
        </div>

        {{-- Pills Filter --}}
        <div class="flex items-center gap-1 p-1 bg-slate-900/50 border border-slate-700/50 rounded-xl">
            <button wire:click="setFilter('')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterAksi === '' ? 'bg-cyan-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Semua</button>
            <button wire:click="setFilter('buka')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterAksi === 'buka' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Buka</button>
            <button wire:click="setFilter('tutup')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterAksi === 'tutup' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Tutup</button>
            <button wire:click="setFilter('cek')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterAksi === 'cek' ? 'bg-blue-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Cek</button>
        </div>

        {{-- Export Button --}}
        <a href="{{ route('export.log-valve', ['search' => $search, 'filter' => $filterAksi, 'start_date' => $startDate, 'end_date' => $endDate]) }}"
           target="_blank" download
           class="flex items-center justify-center gap-2 px-5 py-2.5 bg-slate-800/80 border border-slate-700/50 text-slate-300 text-sm font-medium rounded-xl hover:bg-slate-700/80 hover:text-white transition-all duration-200"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Export
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800/50">
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Waktu</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Aset</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Putaran</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Bukaan</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tutupan</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Koordinat</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @forelse ($logs as $log)
                        <tr wire:key="log-{{ $log->id }}" 
                            class="hover:bg-slate-800/30 transition-colors duration-150 cursor-pointer"
                            @click="selectedLog = {{ json_encode([
                                'waktu' => $log->waktu_kegiatan->format('Y-m-d H:i:s'),
                                'teknisi' => $log->nama_teknisi,
                                'aset' => $log->asetValve->nama_aset ?? '-',
                                'lokasi' => $log->asetValve->lokasi->nama_lokasi ?? '-',
                                'latitude' => $log->latitude,
                                'longitude' => $log->longitude,
                                'aksi' => ($log->aksi_kerja === 'buka' && (float)$log->jumlah_putaran == 0) ? 'cek' : $log->aksi_kerja,
                                'putaran' => \App\Helpers\FormatHelper::putaran($log->jumlah_putaran),
                                'sisa_bukaan' => \App\Helpers\FormatHelper::putaran($log->snapshot_sisa_bukaan),
                                'total_tutupan' => \App\Helpers\FormatHelper::putaran($log->snapshot_total_tutupan),
                                // 'status_radius' => $log->status_radius,
                                'foto_1' => $log->foto_eviden ? Storage::url($log->foto_eviden) : null,
                                'foto_2' => $log->foto_eviden_2 ? Storage::url($log->foto_eviden_2) : null,
                            ]) }}; showDetailModal = true;">
                            <td class="px-5 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ $log->waktu_kegiatan->format('d/m/Y') }}
                                <br>
                                <span class="text-slate-500">{{ $log->waktu_kegiatan->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-white">{{ $log->nama_teknisi }}</span>
                            </td>
                            <td class="px-5 py-4 text-slate-300">{{ $log->asetValve->nama_aset }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-800/50 text-xs font-medium text-slate-300">
                                    {{ $log->asetValve->lokasi->nama_lokasi ?? '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if ($log->aksi_kerja === 'buka' && (float)$log->jumlah_putaran == 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-blue-500/10 text-xs font-semibold text-blue-400 border border-blue-500/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        Cek
                                    </span>
                                @elseif ($log->aksi_kerja === 'buka')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-emerald-500/10 text-xs font-semibold text-emerald-400 border border-emerald-500/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                        Buka
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full bg-amber-500/10 text-xs font-semibold text-amber-400 border border-amber-500/20">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                        Tutup
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center font-mono text-slate-300">{{ \App\Helpers\FormatHelper::putaran($log->jumlah_putaran) }}</td>
                            <td class="px-5 py-4 text-center font-mono text-slate-300">{{ \App\Helpers\FormatHelper::putaran($log->snapshot_sisa_bukaan) }}</td>
                            <td class="px-5 py-4 text-center font-mono text-slate-300">{{ \App\Helpers\FormatHelper::putaran($log->snapshot_total_tutupan) }}</td>
                            <td class="px-5 py-4 text-center">
                                @if($log->latitude && $log->longitude)
                                    <div class="flex flex-col items-center gap-1.5">
                                        <span class="text-xs text-slate-400 font-mono">{{ $log->latitude }}, {{ $log->longitude }}</span>
                                        <div class="flex gap-1.5">
                                            <a href="https://www.google.com/maps/search/?api=1&query={{ $log->latitude }},{{ $log->longitude }}" target="_blank"
                                               @click.stop
                                               class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 text-xs font-medium transition-colors" title="Buka Maps">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Maps
                                            </a>
                                            {{-- <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium {{ $log->status_radius === 'Di Dalam Radius' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400' }}" title="Jarak Aktual: {{ $log->jarak_dari_master ? number_format($log->jarak_dari_master, 2) . ' m' : '-' }}">
                                                {{ $log->status_radius }}
                                            </span> --}}
                                        </div>
                                    </div>
                                @else
                                    <span class="text-xs text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                @if($log->foto_eviden || $log->foto_eviden_2)
                                    <span class="inline-flex items-center justify-center p-2 rounded-lg bg-slate-800 text-slate-400 hover:text-white transition-colors" title="Lihat Foto">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </span>
                                @else
                                    <span class="text-xs text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 text-slate-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                <p class="text-slate-500 text-sm">Belum ada data log valve</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-5 py-4 border-t border-slate-800/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    {{-- Detail Modal --}}
    <div wire:ignore>
        <template x-teleport="body">
            <div x-show="showDetailModal"
                 x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 flex items-center justify-center p-4 sm:p-6" style="display: none; z-index: 100000 !important;">
                <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="showDetailModal = false"></div>
                
                <div x-show="showDetailModal"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                     class="relative w-full max-h-[90vh] overflow-hidden bg-slate-900 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50 flex flex-col"
                     style="max-width: 800px;">
                    
                    {{-- Header --}}
                    <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-800/50 sticky top-0 bg-slate-900/95 backdrop-blur z-20">
                        <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Detail Log Valve
                        </h3>
                        <button @click="showDetailModal = false" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <div class="p-6 sm:p-8 space-y-6 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
                        
                        {{-- Info Dasar --}}
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-4 border-b border-slate-700/50 gap-4">
                            <div>
                                <p class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">PETUGAS / WAKTU</p>
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-cyan-500/10 flex items-center justify-center border border-cyan-500/20">
                                        <span class="text-cyan-400 font-bold text-sm" x-text="selectedLog?.teknisi.charAt(0)"></span>
                                    </div>
                                    <div>
                                        <p class="text-sm sm:text-base font-bold text-white" x-text="selectedLog?.teknisi"></p>
                                        <p class="text-xs sm:text-sm text-slate-400" x-text="selectedLog?.waktu"></p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-left sm:text-right">
                                <p class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-widest mb-1">ASET & LOKASI</p>
                                <p class="text-sm sm:text-base font-bold text-white" x-text="selectedLog?.aset"></p>
                                <p class="text-xs sm:text-sm text-slate-400" x-text="selectedLog?.lokasi"></p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Kiri: Detail Data --}}
                            <div class="space-y-4">
                                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl" style="padding: 1.25rem;">
                                    <p class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Data Operasional</p>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-xs text-slate-400 mb-1">Aksi Kerja</p>
                                            <span :class="selectedLog?.aksi === 'buka' ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20' : (selectedLog?.aksi === 'tutup' ? 'bg-amber-500/10 text-amber-400 border-amber-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20')" class="inline-flex px-2.5 py-1 rounded-md text-xs font-semibold border" x-text="selectedLog?.aksi === 'buka' ? 'Buka' : (selectedLog?.aksi === 'tutup' ? 'Tutup' : 'Cek')"></span>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-400 mb-1">Jumlah Putaran</p>
                                            <p class="text-white font-mono font-bold text-base" x-text="selectedLog?.putaran"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-400 mb-1">Sisa Bukaan</p>
                                            <p class="text-cyan-400 font-mono font-bold text-base" x-text="selectedLog?.sisa_bukaan"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-400 mb-1">Total Tutupan</p>
                                            <p class="text-amber-400 font-mono font-bold text-base" x-text="selectedLog?.total_tutupan"></p>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-slate-800/50 border border-slate-700/50 rounded-xl" style="padding: 1.25rem;">
                                    <p class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-widest mb-3">Lokasi & Radius</p>
                                    <div class="grid grid-cols-2 gap-4 mb-3">
                                        <div>
                                            <p class="text-xs text-slate-400 mb-1">Latitude</p>
                                            <p class="text-slate-300 font-mono text-sm" x-text="selectedLog?.latitude || '-'"></p>
                                        </div>
                                        <div>
                                            <p class="text-xs text-slate-400 mb-1">Longitude</p>
                                            <p class="text-slate-300 font-mono text-sm" x-text="selectedLog?.longitude || '-'"></p>
                                        </div>
                                    </div>
                                    {{-- <div class="flex justify-between items-center mt-2 pt-2 border-t border-slate-700/50">
                                        <p class="text-xs text-slate-400">Status Radius</p>
                                        <span :class="selectedLog?.status_radius === 'Di Dalam Radius' ? 'bg-emerald-500/20 text-emerald-400 border-emerald-500/30' : 'bg-red-500/20 text-red-400 border-red-500/30'" class="inline-flex px-2 py-1 rounded-md text-xs font-semibold border" x-text="selectedLog?.status_radius || 'Tidak Diketahui'"></span>
                                    </div> --}}
                                </div>
                            </div>

                            {{-- Kanan: Foto --}}
                            <div class="space-y-4">
                                <h4 class="text-[10px] sm:text-xs font-semibold text-slate-500 uppercase tracking-widest flex items-center gap-1.5">
                                    <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    Foto Bukti
                                </h4>
                                <div class="grid grid-cols-2 gap-3">
                                    <template x-if="selectedLog?.foto_1">
                                        <div class="group relative overflow-hidden rounded-lg border border-slate-700/50 bg-black/40 shadow-md">
                                            <img :src="selectedLog.foto_1" alt="Foto 1" @click="selectedImage = selectedLog.foto_1; showImageModal = true;" class="w-full h-32 object-cover cursor-pointer hover:opacity-75 transition">
                                        </div>
                                    </template>
                                    <template x-if="!selectedLog?.foto_1">
                                        <div class="flex flex-col items-center justify-center w-full h-32 rounded-lg border border-dashed border-slate-700/50 bg-slate-800/30">
                                            <span class="text-xs text-slate-500">Tidak ada Foto 1</span>
                                        </div>
                                    </template>

                                    <template x-if="selectedLog?.foto_2">
                                        <div class="group relative overflow-hidden rounded-lg border border-slate-700/50 bg-black/40 shadow-md">
                                            <img :src="selectedLog.foto_2" alt="Foto 2" @click="selectedImage = selectedLog.foto_2; showImageModal = true;" class="w-full h-32 object-cover cursor-pointer hover:opacity-75 transition">
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </template>
        
        {{-- Lightbox Image Modal --}}
        <template x-teleport="body">
            <div x-show="showImageModal"
                 class="fixed inset-0 flex items-center justify-center p-4" style="display: none; z-index: 999999 !important;">
                <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="showImageModal = false"></div>
                <div x-show="showImageModal"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
                     class="relative z-10 flex flex-col items-center justify-center" style="max-width: 95vw; max-height: 95vh;">
                    <button @click="showImageModal = false" class="absolute -top-12 right-0 p-2 text-white/70 hover:text-white transition-colors bg-slate-800/50 hover:bg-slate-700/50 rounded-full z-50">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    <img :src="selectedImage" class="object-contain rounded-xl shadow-2xl border border-slate-700/50" style="max-width: 90vw; max-height: 80vh;">
                </div>
            </div>
        </template>
    </div>
</div>
