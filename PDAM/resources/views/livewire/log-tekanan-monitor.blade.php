<div wire:poll.30s>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Monitoring Tekanan Air</h1>
        <p class="text-sm text-slate-400 mt-1">Pantau tekanan air per wilayah distribusi dan deteksi dini potensi kebocoran</p>
    </div>

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        {{-- Total Pengecekan --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/10">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Total Pengecekan</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($totalPengecekan) }}</p>
                </div>
            </div>
        </div>

        {{-- Rata-rata Tekanan --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-violet-500/10">
                    <svg class="w-6 h-6 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Rata-rata Tekanan</p>
                    <p class="text-2xl font-bold text-white">{{ $rataTekanan }} <span class="text-sm font-normal text-slate-400">Bar</span></p>
                </div>
            </div>
        </div>

        {{-- Kondisi Normal --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-emerald-500/10">
                    <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Kondisi Normal</p>
                    <p class="text-2xl font-bold text-emerald-400">{{ number_format($kondisiNormal) }}</p>
                </div>
            </div>
        </div>

        {{-- Peringatan Kritis --}}
        <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 hover:border-slate-700/50 transition-colors duration-300 {{ $peringatanKritis > 0 ? 'border-red-500/30 animate-pulse' : '' }}">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-red-500/10">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs font-medium text-slate-400 uppercase tracking-wider">Peringatan Kritis</p>
                    <p class="text-2xl font-bold text-red-400">{{ number_format($peringatanKritis) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js Bar Chart --}}
    <div class="bg-slate-900/50 backdrop-blur rounded-xl border border-slate-800/50 p-5 mb-6" wire:ignore>
        <h3 class="text-sm font-semibold text-slate-300 mb-4">Tekanan Terkini per Daerah</h3>
        <div class="relative h-72">
            <canvas id="tekananChart"></canvas>
        </div>
    </div>

    {{-- Chart initialization script --}}
    <script>
        document.addEventListener('livewire:navigated', () => {
            const ctx = document.getElementById('tekananChart');
            if (!ctx) return;

            const chartData = @json($chartData);

            if (window.tekananChartInstance) {
                window.tekananChartInstance.destroy();
            }

            window.tekananChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Tekanan (Bar)',
                        data: chartData.values,
                        backgroundColor: chartData.colors,
                        borderColor: chartData.colors.map(c => c.replace('0.8', '1')),
                        borderWidth: 1,
                        borderRadius: 6,
                        borderSkipped: false,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
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
                                label: function(context) {
                                    const val = context.parsed.y;
                                    let status = val >= 1.0 ? '🟢 Normal' : (val >= 0.5 ? '🟡 Rendah' : '🔴 Kritis');
                                    return `${val} Bar — ${status}`;
                                }
                            }
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: 'rgba(51, 65, 85, 0.3)' },
                            ticks: { color: '#64748b', font: { size: 11 } },
                            title: { display: true, text: 'Tekanan (Bar)', color: '#94a3b8', font: { size: 12 } }
                        },
                        x: {
                            grid: { display: false },
                            ticks: { color: '#64748b', font: { size: 11 }, maxRotation: 45 }
                        }
                    },
                    // Threshold line at y=1.0
                    animation: {
                        onComplete: function() {
                            const chart = this;
                            const yScale = chart.scales.y;
                            const ctx = chart.ctx;
                            const y = yScale.getPixelForValue(1.0);

                            ctx.save();
                            ctx.beginPath();
                            ctx.setLineDash([6, 4]);
                            ctx.strokeStyle = 'rgba(34, 197, 94, 0.6)';
                            ctx.lineWidth = 2;
                            ctx.moveTo(chart.chartArea.left, y);
                            ctx.lineTo(chart.chartArea.right, y);
                            ctx.stroke();

                            ctx.fillStyle = 'rgba(34, 197, 94, 0.8)';
                            ctx.font = '11px Inter, sans-serif';
                            ctx.fillText('Batas Normal (1.0 Bar)', chart.chartArea.left + 5, y - 6);
                            ctx.restore();
                        }
                    }
                }
            });

            // Listen for chart data updates from Livewire
            window.addEventListener('chart-data-updated', (event) => {
                if (window.tekananChartInstance) {
                    const data = event.detail.chartData;
                    window.tekananChartInstance.data.labels = data.labels;
                    window.tekananChartInstance.data.datasets[0].data = data.values;
                    window.tekananChartInstance.data.datasets[0].backgroundColor = data.colors;
                    window.tekananChartInstance.data.datasets[0].borderColor = data.colors.map(c => c.replace('0.8', '1'));
                    window.tekananChartInstance.update();
                }
            });
        });
    </script>

    {{-- Search & Filter --}}
    <div class="flex flex-col sm:flex-row gap-3 mb-6">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Cari lokasi..."
                class="w-full pl-10 pr-4 py-2.5 bg-slate-900/50 border border-slate-700/50 rounded-xl text-sm text-slate-200 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-cyan-500/40 focus:border-cyan-500/40 transition"
            >
        </div>

        {{-- Pills Filter --}}
        <div class="flex items-center gap-1 p-1 bg-slate-900/50 border border-slate-700/50 rounded-xl">
            <button wire:click="setFilter('')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterStatus === '' ? 'bg-cyan-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Semua</button>
            <button wire:click="setFilter('normal')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterStatus === 'normal' ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Normal</button>
            <button wire:click="setFilter('rendah')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterStatus === 'rendah' ? 'bg-amber-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Rendah</button>
            <button wire:click="setFilter('kritis')" class="px-4 py-2 text-xs font-semibold rounded-lg transition-all duration-200 {{ $filterStatus === 'kritis' ? 'bg-red-500 text-white shadow-sm' : 'text-slate-400 hover:text-white' }}">Kritis</button>
        </div>

        {{-- Export Button --}}
        <a href="{{ route('export.log-tekanan', ['search' => $search, 'filter' => $filterStatus]) }}"
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
                        <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Tekanan (Bar)</th>
                        <th class="px-5 py-3.5 text-center text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/30">
                    @forelse ($logs as $log)
                        <tr wire:key="tekanan-{{ $log->id }}" class="hover:bg-slate-800/30 transition-colors duration-150">
                            <td class="px-5 py-4 text-xs text-slate-400 whitespace-nowrap">
                                {{ $log->waktu_pengecekan->format('d/m/Y') }}
                                <br>
                                <span class="text-slate-500">{{ $log->waktu_pengecekan->format('H:i') }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="font-medium text-white">{{ $log->lokasi->nama_lokasi ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-4 text-center font-mono text-slate-300 font-semibold">{{ $log->nilai_tekanan }}</td>
                            <td class="px-5 py-4 text-center">
                                @switch($log->status)
                                    @case('normal')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-xs font-semibold text-emerald-400 border border-emerald-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            Normal
                                        </span>
                                        @break
                                    @case('rendah')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/10 text-xs font-semibold text-amber-400 border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            Rendah
                                        </span>
                                        @break
                                    @case('kritis')
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-500/10 text-xs font-semibold text-red-400 border border-red-500/20">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                                            Kritis
                                        </span>
                                        @break
                                @endswitch
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-5 py-12 text-center">
                                <svg class="w-12 h-12 text-slate-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                </svg>
                                <p class="text-slate-500 text-sm">Belum ada data tekanan</p>
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
