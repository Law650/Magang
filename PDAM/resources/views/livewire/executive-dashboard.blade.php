<div wire:poll.30s class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Executive Dashboard</h1>
            <p class="text-sm text-slate-400 mt-1">Ringkasan real-time sistem monitoring distribusi air PDAM</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/60 border border-slate-700/50">
            <svg class="w-4 h-4 text-cyan-400 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="text-xs text-slate-400">Auto-refresh 10 detik</span>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- Total Aset Terdaftar --}}
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-5 transition-all duration-300 hover:border-cyan-500/30 hover:shadow-lg hover:shadow-cyan-500/5">
            <div class="absolute top-0 right-0 w-24 h-24 bg-cyan-500/5 rounded-full -translate-y-6 translate-x-6 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-cyan-500/10 text-cyan-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-400">Total Aset Terdaftar</p>
                </div>
                <p class="text-3xl font-bold text-white">{{ $metrics['totalAset'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Valve aktif dalam sistem</p>
            </div>
        </div>

        {{-- Total Aktivitas Hari Ini --}}
        <div class="group relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-5 transition-all duration-300 hover:border-emerald-500/30 hover:shadow-lg hover:shadow-emerald-500/5">
            <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-500/5 rounded-full -translate-y-6 translate-x-6 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-slate-400">Aktivitas Hari Ini</p>
                </div>
                <p class="text-3xl font-bold text-white">{{ $metrics['totalAktivitasHariIni'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Kegiatan tercatat hari ini</p>
            </div>
        </div>

        {{-- Peringatan Daerah Kritis --}}
        <div class="group relative overflow-hidden rounded-2xl border p-5 transition-all duration-300
            {{ $metrics['hasDaerahKritis']
                ? 'bg-gradient-to-br from-red-950/60 to-red-900/30 border-red-500/40 animate-pulse-slow shadow-lg shadow-red-500/10'
                : 'bg-gradient-to-br from-slate-800/80 to-slate-900/80 border-slate-700/40 hover:border-amber-500/30 hover:shadow-lg hover:shadow-amber-500/5' }}">
            <div class="absolute top-0 right-0 w-24 h-24 {{ $metrics['hasDaerahKritis'] ? 'bg-red-500/10' : 'bg-amber-500/5' }} rounded-full -translate-y-6 translate-x-6 group-hover:scale-150 transition-transform duration-500"></div>
            <div class="relative">
                <div class="flex items-center gap-3 mb-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl {{ $metrics['hasDaerahKritis'] ? 'bg-red-500/20 text-red-400' : 'bg-amber-500/10 text-amber-400' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium {{ $metrics['hasDaerahKritis'] ? 'text-red-300' : 'text-slate-400' }}">Daerah Kritis</p>
                </div>
                <p class="text-3xl font-bold {{ $metrics['hasDaerahKritis'] ? 'text-red-400' : 'text-white' }}">{{ $metrics['daerahKritis'] }}</p>
                <p class="text-xs {{ $metrics['hasDaerahKritis'] ? 'text-red-400/70' : 'text-slate-500' }} mt-1">
                    {{ $metrics['hasDaerahKritis'] ? '⚠ Tekanan 0 Bar terdeteksi!' : 'Tidak ada daerah kritis' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Doughnut Chart: Status Valve --}}
        <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-white">Status Kesehatan Distribusi Air</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Kondisi tekanan terkini seluruh daerah</p>
                </div>
                <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-700/40 text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    Doughnut
                </div>
            </div>
            <div wire:ignore
                x-data="{
                    chart: null,
                    data: @js($doughnutData),
                    init() {
                        this.renderChart();
                    },
                    renderChart() {
                        const ctx = this.$refs.doughnutCanvas.getContext('2d');
                        if (this.chart) this.chart.destroy();
                        this.chart = new Chart(ctx, {
                            type: 'doughnut',
                            data: {
                                labels: this.data.labels,
                                datasets: [{
                                    data: this.data.values,
                                    backgroundColor: this.data.colors,
                                    borderColor: 'rgba(15, 23, 42, 0.8)',
                                    borderWidth: 3,
                                    hoverBorderColor: 'rgba(15, 23, 42, 1)',
                                    hoverOffset: 8,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                cutout: '65%',
                                plugins: {
                                    legend: {
                                        position: 'bottom',
                                        labels: {
                                            color: '#94a3b8',
                                            padding: 16,
                                            usePointStyle: true,
                                            pointStyleWidth: 10,
                                            font: { size: 12, family: 'Inter' }
                                        }
                                    },
                                    tooltip: {
                                        backgroundColor: 'rgba(15, 23, 42, 0.95)',
                                        titleColor: '#e2e8f0',
                                        bodyColor: '#94a3b8',
                                        borderColor: 'rgba(51, 65, 85, 0.5)',
                                        borderWidth: 1,
                                        cornerRadius: 8,
                                        padding: 12,
                                        callbacks: {
                                            label: function(ctx) {
                                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                                const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                                return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }"
                x-effect="data = @js($doughnutData); if(chart) renderChart();"
                class="relative h-64"
            >
                <canvas x-ref="doughnutCanvas"></canvas>
            </div>
        </div>

        {{-- Daerah Bermasalah Table --}}
    <div wire:ignore.self x-data="{
        showModal: false,
        selected: null,
        map: null,
        marker: null,
        initMap(item) {
            const mapEl = document.getElementById('daerahMapContainer');
            if (!mapEl) return;
            if (this.map) { this.map.remove(); this.map = null; }
            this.map = L.map(mapEl, { zoomControl: true }).setView([item.latitude, item.longitude], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);
            this.marker = L.marker([item.latitude, item.longitude]).addTo(this.map)
                .bindPopup('<b>' + item.nama_lokasi + '</b>').openPopup();
            
            // Force leaflet to recalculate size while modal is animating open
            let count = 0;
            let interval = setInterval(() => {
                if (this.map) this.map.invalidateSize();
                count++;
                if (count > 10) clearInterval(interval);
            }, 100);
        },
        openDetail(item) {
            this.selected = item;
            this.showModal = true;
            if (item.latitude && item.longitude) {
                this.$nextTick(() => { this.initMap(item); });
            }
        },
        closeModal() {
            this.showModal = false;
            setTimeout(() => {
                if (this.map) { this.map.remove(); this.map = null; }
            }, 300);
        }
    }" class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-semibold text-white">Daerah Bermasalah</h3>
                <p class="text-xs text-slate-400 mt-0.5">Daerah dengan tekanan rendah atau kritis — klik baris untuk detail</p>
            </div>
            <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-700/40 text-xs text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
                Real-time
            </div>
        </div>

        @if($daerahBermasalah->isEmpty())
            <div class="flex flex-col items-center justify-center h-52 text-center">
                <div class="flex items-center justify-center w-14 h-14 rounded-full bg-emerald-500/10 mb-3">
                    <svg class="w-7 h-7 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm font-medium text-emerald-400">Semua Daerah Normal</p>
                <p class="text-xs text-slate-500 mt-1">Tidak ada daerah dengan tekanan rendah atau kritis</p>
            </div>
        @else
            <div class="overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent pr-1" style="max-height: 290px;">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-slate-800/95 backdrop-blur-sm z-10">
                        <tr class="border-b border-slate-700/40">
                            <th class="text-left px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Daerah</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tekanan</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Koordinat</th>
                            <th class="text-center px-4 py-2 text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($daerahBermasalah as $daerah)
                            <tr class="border-b border-slate-700/20 hover:bg-slate-700/20 transition-colors cursor-pointer"
                                @click="openDetail(@js($daerah))">
                                <td class="px-4 py-2.5">
                                    <p class="text-white font-medium text-sm">{{ $daerah['nama_lokasi'] }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ \Carbon\Carbon::parse($daerah['waktu'])->diffForHumans() }}</p>
                                </td>
                                <td class="text-center px-4 py-2.5">
                                    @if($daerah['status'] === 'kritis')
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-red-500/15 text-red-400 border border-red-500/20">Kritis</span>
                                    @else
                                        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-amber-500/15 text-amber-400 border border-amber-500/20">Rendah</span>
                                    @endif
                                </td>
                                <td class="text-center px-4 py-2.5 font-mono text-sm {{ $daerah['status'] === 'kritis' ? 'text-red-400' : 'text-amber-400' }}">
                                    {{ number_format($daerah['nilai_tekanan'], 2) }} Bar
                                </td>
                                <td class="text-center px-4 py-2.5">
                                    @if($daerah['latitude'] && $daerah['longitude'])
                                        <span class="text-xs text-slate-400 font-mono">{{ $daerah['latitude'] }}, {{ $daerah['longitude'] }}</span>
                                    @else
                                        <span class="text-xs text-slate-600">-</span>
                                    @endif
                                </td>
                                <td class="text-center px-4 py-2.5">
                                    @if($daerah['latitude'] && $daerah['longitude'])
                                        <a href="https://www.google.com/maps/search/?api=1&query={{ $daerah['latitude'] }},{{ $daerah['longitude'] }}" target="_blank" @click.stop
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 text-xs font-medium transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Maps
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-600">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Modal Detail Daerah Bermasalah --}}
        <div wire:ignore>
            <template x-teleport="body">
                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 flex items-center justify-center p-4 sm:p-6" style="display: none; z-index: 100000 !important;">
                    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="closeModal()"></div>
                    <div x-show="showModal"
                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                         class="relative w-full max-h-[90vh] overflow-y-auto bg-slate-900 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50 flex flex-col"
                         style="max-width: 800px;">
                        
                        {{-- Header --}}
                        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-800/50 sticky top-0 bg-slate-900/95 backdrop-blur z-20">
                            <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Detail Daerah Bermasalah
                            </h3>
                            <button @click="closeModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 flex-1">
                            {{-- Left: Map --}}
                            <div class="bg-slate-800 relative z-10 border-b md:border-b-0 md:border-r border-slate-700/50" style="min-height: 320px;">
                                <div id="daerahMapContainer" class="w-full absolute inset-0 z-0"></div>
                            </div>
                        {{-- Right: Detail --}}
                        <div class="p-6 sm:p-8 space-y-6">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Nama Daerah</p>
                                <p class="text-white font-semibold text-lg sm:text-xl leading-tight" x-text="selected?.nama_lokasi"></p>
                            </div>
                            <div class="grid grid-cols-2" style="gap: 2rem;">
                                <div class="bg-slate-800/50 rounded-xl p-4 sm:p-5">
                                    <p class="text-xs text-slate-500 mb-2">Status</p>
                                    <span x-show="selected?.status === 'kritis'" class="inline-flex px-3 py-1.5 rounded-md text-xs font-semibold bg-red-500/15 text-red-400 border border-red-500/20">Kritis</span>
                                    <span x-show="selected?.status === 'rendah'" class="inline-flex px-3 py-1.5 rounded-md text-xs font-semibold bg-amber-500/15 text-amber-400 border border-amber-500/20">Rendah</span>
                                </div>
                                <div class="bg-slate-800/50 rounded-xl p-4 sm:p-5">
                                    <p class="text-xs text-slate-500 mb-2">Tekanan</p>
                                    <p class="text-white font-mono font-bold text-lg sm:text-xl" x-text="selected ? parseFloat(selected.nilai_tekanan).toFixed(2) + ' Bar' : '-'"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2" style="gap: 2rem;">
                                <div class="bg-slate-800/50 rounded-xl p-4 sm:p-5">
                                    <p class="text-xs text-slate-500 mb-2">Latitude</p>
                                    <p class="text-slate-300 font-mono text-sm sm:text-base" x-text="selected?.latitude ?? '-'"></p>
                                </div>
                                <div class="bg-slate-800/50 rounded-xl p-4 sm:p-5">
                                    <p class="text-xs text-slate-500 mb-2">Longitude</p>
                                    <p class="text-slate-300 font-mono text-sm sm:text-base" x-text="selected?.longitude ?? '-'"></p>
                                </div>
                            </div>
                            <div class="bg-slate-800/50 rounded-xl p-4 sm:p-5">
                                <p class="text-xs text-slate-500 mb-2">Teknisi</p>
                                <p class="text-slate-300 text-sm sm:text-base font-medium" x-text="selected?.nama_teknisi ?? '-'"></p>
                            </div>
                            <template x-if="selected?.latitude && selected?.longitude">
                                <a :href="'https://www.google.com/maps/search/?api=1&query=' + selected.latitude + ',' + selected.longitude" target="_blank"
                                   class="mt-4 inline-flex items-center gap-2 px-5 py-3.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-blue-400 hover:to-blue-500 shadow-lg shadow-blue-500/25 transition-all w-full justify-center group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Buka di Google Maps
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            </template>
        </div>
    </div>

    {{-- Valve Summary Table --}}
    <div wire:ignore.self x-data="{
        showGvModal: false,
        selectedGv: null,
        gvMap: null,
        initGvMap(item) {
            const mapEl = document.getElementById('gvMapContainer');
            if (!mapEl) return;
            if (this.gvMap) { this.gvMap.remove(); this.gvMap = null; }
            this.gvMap = L.map(mapEl, { zoomControl: true }).setView([item.latitude, item.longitude], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.gvMap);
            L.marker([item.latitude, item.longitude]).addTo(this.gvMap)
                .bindPopup('<b>' + item.nama_aset + '</b><br>' + item.lokasi).openPopup();
            
            let count = 0;
            let interval = setInterval(() => {
                if (this.gvMap) this.gvMap.invalidateSize();
                count++;
                if (count > 10) clearInterval(interval);
            }, 100);
        },
        openGvDetail(item) {
            this.selectedGv = item;
            this.showGvModal = true;
            if (item.latitude && item.longitude) {
                this.$nextTick(() => { this.initGvMap(item); });
            }
        },
        closeGvModal() {
            this.showGvModal = false;
            setTimeout(() => {
                if (this.gvMap) { this.gvMap.remove(); this.gvMap = null; }
            }, 300);
        }
    }" class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40">
            <h3 class="text-base font-semibold text-white">Ringkasan Bukaan Terkini Setiap GV</h3>
            <p class="text-xs text-slate-400 mt-0.5">Posisi aktual katup dan persentase laju aliran air — klik baris untuk detail</p>
        </div>
        <div class="overflow-x-auto overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent" style="max-height: 560px;">
            <table class="w-full text-sm">
                <thead class="sticky top-0 bg-slate-800/95 backdrop-blur-sm z-10">
                    <tr class="border-b border-slate-700/40">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama GV</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Koordinat</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Bukaan</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider min-w-[200px]">Aliran Terbuka</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @forelse ($valveSummary as $valve)
                        @php
                            $pct = $valve['persentase_bukaan'];
                            if ($pct >= 75) {
                                $barColor = 'bg-gradient-to-r from-emerald-500 to-emerald-400';
                                $textColor = 'text-emerald-400';
                                $bgColor = 'bg-emerald-500/10';
                            } elseif ($pct >= 40) {
                                $barColor = 'bg-gradient-to-r from-amber-500 to-yellow-400';
                                $textColor = 'text-amber-400';
                                $bgColor = 'bg-amber-500/10';
                            } else {
                                $barColor = 'bg-gradient-to-r from-red-500 to-rose-400';
                                $textColor = 'text-red-400';
                                $bgColor = 'bg-red-500/10';
                            }
                        @endphp
                        <tr class="hover:bg-slate-700/20 transition-colors duration-150 cursor-pointer"
                            @click="openGvDetail(@js($valve))">
                            <td class="px-6 py-3.5">
                                <span class="font-medium text-white">{{ $valve['nama_aset'] }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-slate-400">{{ $valve['lokasi'] }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                @if($valve['latitude'] && $valve['longitude'])
                                    <span class="text-xs text-slate-400 font-mono">{{ $valve['latitude'] }}, {{ $valve['longitude'] }}</span>
                                @else
                                    <span class="text-xs text-slate-600">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="{{ $textColor }} font-mono font-semibold">{{ number_format($valve['sisa_bukaan'], 2) }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-2.5 rounded-full bg-slate-700/60 overflow-hidden">
                                        <div
                                            class="{{ $barColor }} h-full rounded-full transition-all duration-700 ease-out"
                                            style="width: {{ $pct }}%"
                                        ></div>
                                    </div>
                                    <span class="text-xs font-semibold {{ $textColor }} w-12 text-right">{{ number_format($pct, 1) }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                @if($valve['latitude'] && $valve['longitude'])
                                    <a href="https://www.google.com/maps/search/?api=1&query={{ $valve['latitude'] }},{{ $valve['longitude'] }}" target="_blank" @click.stop
                                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-500/10 text-blue-400 hover:bg-blue-500/20 text-xs font-medium transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        Maps
                                    </a>
                                @else
                                    <span class="text-xs text-slate-600">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                Belum ada data aset valve.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal Detail GV --}}
        <div wire:ignore>
            <template x-teleport="body">
                <div x-show="showGvModal"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 flex items-center justify-center p-4 sm:p-6" style="display: none; z-index: 100000 !important;">
                    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" @click="closeGvModal()"></div>
                    <div x-show="showGvModal"
                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                         class="relative w-full max-h-[75vh] overflow-y-auto bg-slate-900 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50 flex flex-col"
                         style="max-width: 800px;">
                        
                        {{-- Header --}}
                        <div class="flex items-center justify-between px-5 sm:px-6 py-3 border-b border-slate-800/50 sticky top-0 bg-slate-900/95 backdrop-blur z-20">
                            <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                Detail Gate Valve
                            </h3>
                            <button @click="closeGvModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 flex-1">
                            {{-- Left: Map --}}
                            <div class="bg-slate-800 relative z-10 border-b md:border-b-0 md:border-r border-slate-700/50" style="min-height: 320px;">
                                <div id="gvMapContainer" class="w-full absolute inset-0 z-0"></div>
                            </div>
                        {{-- Right: Detail --}}
                        <div class="p-4 sm:p-5 space-y-5">
                            <div>
                                <p class="text-xs text-slate-500 uppercase tracking-wider mb-2">Nama GV</p>
                                <p class="text-white font-semibold text-lg sm:text-xl leading-tight" x-text="selectedGv?.nama_aset"></p>
                            </div>
                            <div class="bg-slate-800/50 rounded-xl p-4 sm:p-5">
                                <p class="text-xs text-slate-500 mb-1.5">Lokasi</p>
                                <p class="text-slate-300 font-medium sm:text-base" x-text="selectedGv?.lokasi"></p>
                            </div>
                            <div class="grid grid-cols-2" style="gap: 1.25rem;">
                                <div class="bg-slate-800/50 rounded-xl p-4 sm:p-4">
                                    <p class="text-xs text-slate-500 mb-1.5">Latitude</p>
                                    <p class="text-slate-300 font-mono text-sm" x-text="selectedGv?.latitude ?? '-'"></p>
                                </div>
                                <div class="bg-slate-800/50 rounded-xl p-4 sm:p-4">
                                    <p class="text-xs text-slate-500 mb-1.5">Longitude</p>
                                    <p class="text-slate-300 font-mono text-sm" x-text="selectedGv?.longitude ?? '-'"></p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3" style="gap: 1.25rem;">
                                <div class="bg-slate-800/60 rounded-xl p-3.5 sm:p-4 text-center">
                                    <p class="text-[10px] text-slate-500 mb-1.5">Kapasitas Full</p>
                                    <p class="text-white font-mono font-bold text-base sm:text-lg" x-text="selectedGv ? parseFloat(selectedGv.kapasitas_full).toFixed(2) : '-'"></p>
                                </div>
                                <div class="bg-slate-800/60 rounded-xl p-3.5 sm:p-4 text-center">
                                    <p class="text-[10px] text-slate-500 mb-1.5">Total Tutupan</p>
                                    <p class="text-amber-400 font-mono font-bold text-base sm:text-lg" x-text="selectedGv ? parseFloat(selectedGv.total_tutupan).toFixed(2) : '-'"></p>
                                </div>
                                <div class="bg-slate-800/60 rounded-xl p-3.5 sm:p-4 text-center">
                                    <p class="text-[10px] text-slate-500 mb-1.5">Sisa Bukaan</p>
                                    <p class="text-emerald-400 font-mono font-bold text-base sm:text-lg" x-text="selectedGv ? parseFloat(selectedGv.sisa_bukaan).toFixed(2) : '-'"></p>
                                </div>
                            </div>
                            <div>
                                <p class="text-xs text-slate-500 mb-3">Persentase Bukaan</p>
                                <div class="flex items-center gap-3">
                                    <div class="flex-1 h-3 rounded-full bg-slate-700/60 overflow-hidden">
                                        <div class="bg-gradient-to-r from-cyan-500 to-emerald-400 h-full rounded-full transition-all duration-700" :style="'width:' + (selectedGv?.persentase_bukaan ?? 0) + '%'"></div>
                                    </div>
                                    <span class="text-sm font-bold text-cyan-400" x-text="(selectedGv?.persentase_bukaan ?? 0).toFixed(1) + '%'"></span>
                                </div>
                            </div>
                            <template x-if="selectedGv?.latitude && selectedGv?.longitude">
                                <a :href="'https://www.google.com/maps/search/?api=1&query=' + selectedGv.latitude + ',' + selectedGv.longitude" target="_blank"
                                   class="mt-2 inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-blue-500 to-blue-600 text-white text-sm font-semibold rounded-xl hover:from-blue-400 hover:to-blue-500 shadow-lg shadow-blue-500/25 transition-all w-full justify-center group">
                                    <svg class="w-4 h-4 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Buka di Google Maps
                                </a>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            </template>
        </div>
    </div>

    {{-- Live Activity Feed --}}
    <div wire:ignore.self x-data="{
        showActivityModal: false,
        selectedActivity: null,
        showImageModal: false,
        selectedImage: null,
        closeActivityModal() {
            this.showActivityModal = false;
        },
        openImageModal(src) {
            if(!src) return;
            this.selectedImage = src;
            this.showImageModal = true;
        },
        closeImageModal() {
            this.showImageModal = false;
            setTimeout(() => this.selectedImage = null, 300);
        }
    }" class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-white">Live Activity Feed</h3>
                <p class="text-xs text-slate-400 mt-0.5">10 aktivitas terbaru dari petugas lapangan (Setting GV & Tekanan)</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                </span>
                <span class="text-xs text-cyan-400 font-medium">Live</span>
            </div>
        </div>
        <div class="divide-y divide-slate-700/30 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent" style="max-height: 700px;">
            @forelse ($recentActivities as $activity)
                <div class="px-6 py-4 hover:bg-slate-700/30 transition-colors duration-150 cursor-pointer group"
                     @click="selectedActivity = @js($activity); showActivityModal = true">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        @if($activity['type'] === 'valve')
                            <div class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-full
                                {{ $activity['aksi_kerja'] === 'buka' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-amber-500/15 text-amber-400' }}
                                text-xs font-bold">
                                {{ strtoupper(substr($activity['nama_teknisi'] ?? '-', 0, 2)) }}
                            </div>
                        @else
                            <div class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-full bg-cyan-500/15 text-cyan-400 text-xs font-bold">
                                {{ strtoupper(substr($activity['nama_teknisi'] ?? '-', 0, 2)) }}
                            </div>
                        @endif
                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-white text-sm">{{ $activity['nama_teknisi'] ?? '-' }}</span>
                                @if($activity['type'] === 'valve')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                                        {{ $activity['aksi_kerja'] === 'buka'
                                            ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20'
                                            : 'bg-amber-500/15 text-amber-400 border border-amber-500/20' }}">
                                        {{ $activity['aksi_kerja'] === 'buka' ? '↑ Buka' : '↓ Tutup' }}
                                    </span>
                                    <span class="text-xs text-slate-500">{{ $activity['jumlah_putaran'] }} putaran</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-cyan-500/15 text-cyan-400 border border-cyan-500/20">
                                        Cek Tekanan
                                    </span>
                                    <span class="text-xs text-slate-500">{{ $activity['nilai_tekanan'] }} Bar</span>
                                @endif
                            </div>
                            <p class="text-sm text-slate-400 mt-0.5">
                                @if($activity['type'] === 'valve')
                                    {{ $activity['nama_aset'] }} — {{ $activity['lokasi'] }}
                                @else
                                    {{ $activity['lokasi'] }}
                                @endif
                            </p>
                            @if ($activity['keterangan'])
                                <p class="text-xs text-slate-500 mt-1 italic">{{ $activity['keterangan'] }}</p>
                            @endif
                        </div>
                        {{-- Timestamp --}}
                        <div class="flex-shrink-0 text-right">
                            <p class="text-xs text-slate-500">{{ \Carbon\Carbon::parse($activity['waktu'])->diffForHumans() }}</p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ \Carbon\Carbon::parse($activity['waktu'])->format('H:i') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-500">
                    Belum ada aktivitas tercatat.
                </div>
            @endforelse
        </div>

        {{-- Modal Detail Live Activity --}}
        <div wire:ignore>
            <template x-teleport="body">
                <div x-show="showActivityModal"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="fixed inset-0 flex items-center justify-center p-4 sm:p-6" style="display: none; z-index: 100000 !important;">
                    <div class="absolute inset-0 bg-black/80 backdrop-blur-sm" @click="closeActivityModal()"></div>
                    
                    <div x-show="showActivityModal"
                         x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-95 translate-y-4" x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100 translate-y-0" x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                         class="relative w-full max-h-[90vh] overflow-hidden bg-slate-900 border border-slate-700/50 rounded-2xl shadow-2xl shadow-black/50 flex flex-col"
                         style="max-width: 700px;">
                        
                        {{-- Header --}}
                        <div class="flex items-center justify-between px-5 sm:px-6 py-4 border-b border-slate-800/50 sticky top-0 bg-slate-900/95 backdrop-blur z-20">
                            <h3 class="text-base sm:text-lg font-bold text-white flex items-center gap-2">
                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Detail Aktivitas Terkini
                            </h3>
                            <button @click="closeActivityModal()" class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="p-6 sm:p-8 space-y-6 overflow-y-auto scrollbar-thin scrollbar-thumb-slate-700 scrollbar-track-transparent">
                            {{-- Info Dasar (Global) --}}
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between pb-4 border-b border-slate-800 gap-4">
                                <div>
                                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Petugas / Waktu</p>
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-cyan-500/20 text-cyan-400 flex items-center justify-center font-bold text-sm" x-text="selectedActivity?.nama_teknisi?.substring(0,2).toUpperCase()"></div>
                                        <div>
                                            <p class="text-white font-semibold text-base" x-text="selectedActivity?.nama_teknisi"></p>
                                            <p class="text-sm text-slate-400" x-text="selectedActivity?.waktu"></p>
                                        </div>
                                    </div>
                                </div>
                                <div class="sm:text-right">
                                    <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">Lokasi</p>
                                    <p class="text-white font-medium text-base" x-text="selectedActivity?.lokasi"></p>
                                    <template x-if="selectedActivity?.type === 'valve'">
                                        <p class="text-sm text-slate-400 mt-0.5" x-text="'Aset: ' + selectedActivity?.nama_aset"></p>
                                    </template>
                                </div>
                            </div>

                            {{-- KONTEN VALVE --}}
                            <template x-if="selectedActivity?.type === 'valve'">
                                <div class="flex flex-row gap-5 sm:gap-8 overflow-x-auto sm:overflow-visible pb-2 sm:pb-0">
                                    {{-- Kiri: Detail & Validasi --}}
                                    <div class="flex-1 space-y-6 min-w-[300px]">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div class="bg-slate-800/50 rounded-xl p-5 border border-slate-700/30">
                                                <p class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-semibold">Aksi Kerja</p>
                                                <div class="flex items-center gap-3">
                                                    <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-bold shadow-sm"
                                                          :class="selectedActivity?.aksi_kerja === 'buka' ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/15 text-amber-400 border border-amber-500/20'"
                                                          x-text="selectedActivity?.aksi_kerja === 'buka' ? '↑ Buka' : '↓ Tutup'">
                                                    </span>
                                                    <span class="text-white font-mono text-lg font-medium" x-text="selectedActivity?.jumlah_putaran + ' Putaran'"></span>
                                                </div>
                                            </div>
                                            <div class="bg-slate-800/50 rounded-xl p-5 border border-slate-700/30">
                                                <p class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-semibold">Keterangan</p>
                                                <p class="text-sm text-slate-300 italic leading-relaxed" x-text="selectedActivity?.keterangan || '-'"></p>
                                            </div>
                                        </div>

                                        {{-- Validasi Koordinat --}}
                                        <div>
                                            <h4 class="text-sm font-semibold text-slate-300 mb-3 flex items-center gap-2">
                                                <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                Validasi Jarak Titik Koordinat
                                            </h4>
                                            <div class="bg-slate-800/40 rounded-xl border border-slate-700/50 p-5">
                                                <div class="grid grid-cols-2 gap-5 mb-5 pb-5 border-b border-slate-700/40">
                                                    <div>
                                                        <p class="text-xs text-slate-500 mb-1">Koordinat Master Aset</p>
                                                        <p class="text-slate-300 font-mono text-xs sm:text-sm" x-text="selectedActivity?.lat_master ? (selectedActivity.lat_master + ', ' + selectedActivity.lng_master) : 'Tidak Ada Data'"></p>
                                                    </div>
                                                    <div>
                                                        <p class="text-xs text-slate-500 mb-1">Koordinat Inputan Petugas</p>
                                                        <p class="text-slate-300 font-mono text-xs sm:text-sm" x-text="selectedActivity?.lat_input ? (selectedActivity.lat_input + ', ' + selectedActivity.lng_input) : 'Tidak Ada Data'"></p>
                                                    </div>
                                                </div>
                                                <div class="flex items-center justify-between p-4 rounded-xl bg-slate-900/60 shadow-inner">
                                                    <div>
                                                        <p class="text-xs text-slate-500 font-medium tracking-wider mb-0.5">Jarak Aktual</p>
                                                        <p class="text-white font-mono text-xl sm:text-2xl font-bold" x-text="selectedActivity?.jarak_meter !== null ? parseFloat(selectedActivity.jarak_meter).toFixed(2) + ' Meter' : '-'"></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Kanan: Foto Eviden --}}
                                    <div class="shrink-0 border-l border-slate-700/50 pl-4 sm:pl-5" style="width: 150px;">
                                        <h4 class="text-xs font-semibold text-slate-300 mb-3 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Foto Bukti
                                        </h4>
                                        <div class="space-y-3">
                                            <template x-if="selectedActivity?.foto_eviden">
                                                <div class="group relative overflow-hidden rounded-lg border border-slate-700/50 bg-black/40 shadow-md flex justify-center">
                                                    <img :src="selectedActivity.foto_eviden" alt="Foto Eviden 1" @click="openImageModal(selectedActivity.foto_eviden)" class="max-w-full object-contain transition-transform duration-300 group-hover:scale-105 cursor-pointer" style="max-height: 120px;">
                                                    <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-lg pointer-events-none"></div>
                                                </div>
                                            </template>
                                            <template x-if="selectedActivity?.foto_eviden_2">
                                                <div class="group relative overflow-hidden rounded-lg border border-slate-700/50 bg-black/40 shadow-md flex justify-center">
                                                    <img :src="selectedActivity.foto_eviden_2" alt="Foto Eviden 2" @click="openImageModal(selectedActivity.foto_eviden_2)" class="max-w-full object-contain transition-transform duration-300 group-hover:scale-105 cursor-pointer" style="max-height: 120px;">
                                                    <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-lg pointer-events-none"></div>
                                                </div>
                                            </template>
                                            <template x-if="!selectedActivity?.foto_eviden && !selectedActivity?.foto_eviden_2">
                                                <div class="flex flex-col items-center justify-center w-full rounded-lg border border-dashed border-slate-700/50 bg-slate-800/30" style="height: 120px;">
                                                    <svg class="w-6 h-6 text-slate-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <p class="text-[10px] text-slate-500 font-medium text-center">Tidak ada foto</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            {{-- KONTEN TEKANAN --}}
                            <template x-if="selectedActivity?.type === 'tekanan'">
                                <div class="flex flex-row gap-5 sm:gap-8 overflow-x-auto sm:overflow-visible pb-2 sm:pb-0">
                                    {{-- Kiri: Detail Tekanan --}}
                                    <div class="flex-1 space-y-6 min-w-[300px]">
                                        <div class="bg-slate-800/50 rounded-xl p-5 border border-slate-700/30">
                                            <p class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-semibold">Nilai Tekanan Aktual</p>
                                            <div class="flex items-center gap-4">
                                                <p class="text-white font-mono font-bold text-4xl" x-text="parseFloat(selectedActivity?.nilai_tekanan).toFixed(2) + ' Bar'"></p>
                                                <span class="inline-flex px-4 py-2 rounded-lg text-sm font-bold tracking-wide shadow-sm"
                                                      :class="{
                                                          'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20': selectedActivity?.status === 'normal',
                                                          'bg-amber-500/15 text-amber-400 border border-amber-500/20': selectedActivity?.status === 'rendah',
                                                          'bg-red-500/15 text-red-400 border border-red-500/20': selectedActivity?.status === 'kritis'
                                                      }"
                                                      x-text="selectedActivity?.status.charAt(0).toUpperCase() + selectedActivity?.status.slice(1)">
                                                </span>
                                            </div>
                                        </div>
                                        <div class="bg-slate-800/50 rounded-xl p-5 border border-slate-700/30">
                                            <p class="text-xs text-slate-500 mb-2 uppercase tracking-wider font-semibold">Keterangan Petugas</p>
                                            <p class="text-base text-slate-300 italic leading-relaxed" x-text="selectedActivity?.keterangan || 'Tidak ada keterangan tambahan.'"></p>
                                        </div>
                                    </div>

                                    {{-- Kanan: Foto Manometer --}}
                                    <div class="shrink-0 border-l border-slate-700/50 pl-4 sm:pl-5" style="width: 150px;">
                                        <h4 class="text-xs font-semibold text-slate-300 mb-3 flex items-center gap-1.5">
                                            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                            Foto Manometer
                                        </h4>
                                        <div>
                                            <template x-if="selectedActivity?.foto_eviden">
                                                <div class="group relative overflow-hidden rounded-lg border border-slate-700/50 bg-black/40 shadow-md flex justify-center">
                                                    <img :src="selectedActivity.foto_eviden" alt="Foto Manometer" @click="openImageModal(selectedActivity.foto_eviden)" class="max-w-full object-contain transition-transform duration-300 group-hover:scale-105 cursor-pointer" style="max-height: 120px;">
                                                    <div class="absolute inset-0 ring-1 ring-inset ring-white/10 rounded-lg pointer-events-none"></div>
                                                </div>
                                            </template>
                                            <template x-if="!selectedActivity?.foto_eviden">
                                                <div class="flex flex-col items-center justify-center w-full rounded-lg border border-dashed border-slate-700/50 bg-slate-800/30" style="height: 120px;">
                                                    <svg class="w-6 h-6 text-slate-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                    <p class="text-[10px] text-slate-500 font-medium text-center">Tidak ada foto</p>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </template>

                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Lightbox Modal untuk Foto (Zoom 5x) --}}
        <template x-teleport="body">
            <div x-show="showImageModal"
                 class="fixed inset-0 flex items-center justify-center p-4" style="display: none; z-index: 999999 !important;">
                <div class="absolute inset-0 bg-black/90 backdrop-blur-sm" @click="closeImageModal()"></div>
                
                <div x-show="showImageModal"
                     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90"
                     class="relative z-10 flex flex-col items-center justify-center" style="max-width: 95vw; max-height: 95vh;">
                    
                    {{-- Tombol Tutup --}}
                    <button @click="closeImageModal()" class="absolute -top-12 right-0 p-2 text-white/70 hover:text-white transition-colors bg-slate-800/50 hover:bg-slate-700/50 rounded-full z-50">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    
                    {{-- Gambar yang diperbesar --}}
                    <img :src="selectedImage" class="object-contain rounded-xl shadow-2xl border border-slate-700/50" style="max-width: 90vw; max-height: 80vh;">
                </div>
            </div>
        </template>
    </div>

</div>
