<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Log Aktivitas Valve</h1>
        <p class="text-sm text-slate-400 mt-1">Riwayat aktivitas buka/tutup katup dari teknisi lapangan</p>
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
                placeholder="Cari teknisi, aset, atau lokasi..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
            >
        </div>

        {{-- Pills Filter --}}
        <div class="flex items-center gap-1 p-1 bg-slate-900/50 border border-slate-700/50 rounded-xl">
            <button wire:click="setFilter('')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterAksi === '' ? 'bg-cyan-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Semua</button>
            <button wire:click="setFilter('buka')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterAksi === 'buka' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Buka</button>
            <button wire:click="setFilter('tutup')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterAksi === 'tutup' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Tutup</button>
        </div>

        {{-- Export Button --}}
        <a href="{{ route('export.log-valve', ['search' => $search, 'filter' => $filterAksi]) }}"
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
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Teknisi</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Aset</th>
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Aksi</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Putaran</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Bukaan</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tutupan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @forelse ($logs as $log)
                        <tr wire:key="log-{{ $log->id }}" class="hover:bg-slate-800/30 transition-colors duration-150">
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
                                @if ($log->aksi_kerja === 'buka')
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
                            <td class="px-5 py-4 text-center font-mono text-slate-300">{{ $log->jumlah_putaran }}</td>
                            <td class="px-5 py-4 text-center font-mono text-slate-300">{{ $log->snapshot_sisa_bukaan }}</td>
                            <td class="px-5 py-4 text-center font-mono text-slate-300">{{ $log->snapshot_total_tutupan }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-5 py-12 text-center">
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
</div>
