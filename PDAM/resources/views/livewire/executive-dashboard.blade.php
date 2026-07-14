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
            <span class="text-xs text-slate-400">Auto-refresh 30 detik</span>
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
                    <h3 class="text-base font-semibold text-white">Status Valve</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Distribusi posisi katup saat ini</p>
                </div>
                <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-700/40 text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                    </svg>
                    Doughnut
                </div>
            </div>
            <div
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

        {{-- Line Chart: Tren Tekanan 7 Hari --}}
        <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-base font-semibold text-white">Tren Tekanan Air</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Rata-rata tekanan 7 hari terakhir (Bar)</p>
                </div>
                <div class="flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-700/40 text-xs text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                    7 Hari
                </div>
            </div>
            <div
                x-data="{
                    chart: null,
                    chartData: @js($lineChartData),
                    init() {
                        this.renderChart();
                    },
                    renderChart() {
                        const ctx = this.$refs.lineCanvas.getContext('2d');
                        if (this.chart) this.chart.destroy();

                        const gradient = ctx.createLinearGradient(0, 0, 0, 250);
                        gradient.addColorStop(0, 'rgba(6, 182, 212, 0.25)');
                        gradient.addColorStop(1, 'rgba(6, 182, 212, 0.01)');

                        this.chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: this.chartData.labels,
                                datasets: [{
                                    label: 'Rata-rata Tekanan (Bar)',
                                    data: this.chartData.values,
                                    borderColor: '#06b6d4',
                                    backgroundColor: gradient,
                                    borderWidth: 2.5,
                                    fill: true,
                                    tension: 0.4,
                                    pointBackgroundColor: '#06b6d4',
                                    pointBorderColor: '#0f172a',
                                    pointBorderWidth: 2,
                                    pointRadius: 5,
                                    pointHoverRadius: 7,
                                    pointHoverBackgroundColor: '#22d3ee',
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    x: {
                                        grid: { color: 'rgba(51, 65, 85, 0.3)', drawBorder: false },
                                        ticks: { color: '#64748b', font: { size: 11, family: 'Inter' } }
                                    },
                                    y: {
                                        beginAtZero: true,
                                        grid: { color: 'rgba(51, 65, 85, 0.3)', drawBorder: false },
                                        ticks: {
                                            color: '#64748b',
                                            font: { size: 11, family: 'Inter' },
                                            callback: function(value) { return value + ' Bar'; }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: { display: false },
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
                                                return ` Tekanan: ${ctx.raw} Bar`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                }"
                x-effect="chartData = @js($lineChartData); if(chart) renderChart();"
                class="relative h-64"
            >
                <canvas x-ref="lineCanvas"></canvas>
            </div>
        </div>
    </div>

    {{-- Valve Summary Table --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40">
            <h3 class="text-base font-semibold text-white">Ringkasan Bukaan Terkini Setiap GV</h3>
            <p class="text-xs text-slate-400 mt-0.5">Posisi aktual katup dan persentase laju aliran air</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/40">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama GV</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Kapasitas Full</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tutupan</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Bukaan</th>
                        <th class="px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider min-w-[200px]">Aliran Terbuka</th>
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
                        <tr class="hover:bg-slate-700/20 transition-colors duration-150">
                            <td class="px-6 py-3.5">
                                <span class="font-medium text-white">{{ $valve['nama_aset'] }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <span class="text-slate-400">{{ $valve['lokasi'] }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="text-slate-300 font-mono">{{ number_format($valve['kapasitas_full'], 2) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="text-slate-300 font-mono">{{ number_format($valve['total_tutupan'], 2) }}</span>
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
    </div>

    {{-- Live Activity Feed --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-white">Live Activity Feed</h3>
                <p class="text-xs text-slate-400 mt-0.5">5 aktivitas terbaru dari petugas lapangan</p>
            </div>
            <div class="flex items-center gap-1.5">
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-cyan-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-cyan-500"></span>
                </span>
                <span class="text-xs text-cyan-400 font-medium">Live</span>
            </div>
        </div>
        <div class="divide-y divide-slate-700/30 max-h-80 overflow-y-auto">
            @forelse ($recentActivities as $activity)
                <div class="px-6 py-4 hover:bg-slate-700/15 transition-colors duration-150">
                    <div class="flex items-start gap-4">
                        {{-- Avatar --}}
                        <div class="flex-shrink-0 flex items-center justify-center w-9 h-9 rounded-full
                            {{ $activity->aksi_kerja === 'buka' ? 'bg-emerald-500/15 text-emerald-400' : 'bg-amber-500/15 text-amber-400' }}
                            text-xs font-bold">
                            {{ strtoupper(substr($activity->nama_teknisi, 0, 2)) }}
                        </div>
                        {{-- Content --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="font-medium text-white text-sm">{{ $activity->nama_teknisi }}</span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium
                                    {{ $activity->aksi_kerja === 'buka'
                                        ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/20'
                                        : 'bg-amber-500/15 text-amber-400 border border-amber-500/20' }}">
                                    {{ $activity->aksi_kerja === 'buka' ? '↑ Buka' : '↓ Tutup' }}
                                </span>
                                <span class="text-xs text-slate-500">{{ $activity->jumlah_putaran }} putaran</span>
                            </div>
                            <p class="text-sm text-slate-400 mt-0.5">
                                {{ $activity->asetValve->nama_aset }}
                            </p>
                            @if ($activity->keterangan)
                                <p class="text-xs text-slate-500 mt-1 italic">{{ $activity->keterangan }}</p>
                            @endif
                        </div>
                        {{-- Timestamp --}}
                        <div class="flex-shrink-0 text-right">
                            <p class="text-xs text-slate-500">{{ $activity->waktu_kegiatan->diffForHumans() }}</p>
                            <p class="text-xs text-slate-600 mt-0.5">{{ $activity->waktu_kegiatan->format('H:i') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-slate-500">
                    Belum ada aktivitas tercatat.
                </div>
            @endforelse
        </div>
    </div>

</div>
