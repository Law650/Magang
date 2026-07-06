<div wire:poll.30s class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Peta Distribusi Aset</h1>
            <p class="text-sm text-slate-400 mt-1">Monitoring lokasi Gate Valve dan status tekanan per kecamatan — Kabupaten Pemalang</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/60 border border-slate-700/50">
            <svg class="w-4 h-4 text-cyan-400 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="text-xs text-slate-400">Auto-refresh 30 detik</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        {{-- Total Lokasi --}}
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Total Lokasi</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $stats['total'] }}</p>
        </div>

        {{-- Normal --}}
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Normal</span>
            </div>
            <p class="text-2xl font-bold text-emerald-400">{{ $stats['normal'] }}</p>
        </div>

        {{-- Rendah --}}
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Rendah</span>
            </div>
            <p class="text-2xl font-bold text-amber-400">{{ $stats['rendah'] }}</p>
        </div>

        {{-- Kritis --}}
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-500/10 text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Kritis</span>
            </div>
            <p class="text-2xl font-bold text-red-400">{{ $stats['kritis'] }}</p>
        </div>
    </div>

    {{-- Map Container --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-white">Peta Lokasi Aset</h3>
                <p class="text-xs text-slate-400 mt-0.5">Klik marker untuk melihat detail lokasi</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-emerald-500"></span> Normal
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span> Rendah
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span> Kritis
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-slate-500"></span> Belum ada data
                </div>
            </div>
        </div>

        {{-- Leaflet Map - wrapped in wire:ignore so Livewire won't re-render the canvas --}}
        <div wire:ignore>
            <div
                x-data="{
                    map: null,
                    markerLayer: null,
                    markers: @js($markers),

                    init() {
                        this.initMap();
                        this.renderMarkers();
                    },

                    initMap() {
                        // Center on Kabupaten Pemalang
                        this.map = L.map(this.$refs.mapContainer, {
                            zoomControl: false
                        }).setView([-7.01, 109.40], 11);

                        // Dark-themed tile layer
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; <a href=&quot;https://www.openstreetmap.org/copyright&quot;>OSM</a> &copy; <a href=&quot;https://carto.com/&quot;>CARTO</a>',
                            subdomains: 'abcd',
                            maxZoom: 19
                        }).addTo(this.map);

                        // Zoom control top-right
                        L.control.zoom({ position: 'topright' }).addTo(this.map);
                    },

                    getMarkerColor(status) {
                        const colors = {
                            normal: '#10b981',
                            rendah: '#f59e0b',
                            kritis: '#ef4444',
                            unknown: '#64748b'
                        };
                        return colors[status] || colors.unknown;
                    },

                    getStatusLabel(status) {
                        const labels = {
                            normal: 'Normal',
                            rendah: 'Rendah',
                            kritis: 'Kritis',
                            unknown: 'Belum Ada Data'
                        };
                        return labels[status] || labels.unknown;
                    },

                    createCircleIcon(color) {
                        return L.divIcon({
                            html: `<div style='
                                width: 28px;
                                height: 28px;
                                background: ${color};
                                border: 3px solid rgba(15, 23, 42, 0.9);
                                border-radius: 50%;
                                box-shadow: 0 0 12px ${color}88, 0 2px 8px rgba(0,0,0,0.4);
                                position: relative;
                            '><div style='
                                position: absolute;
                                inset: 4px;
                                border-radius: 50%;
                                background: radial-gradient(circle at 35% 35%, rgba(255,255,255,0.4), transparent);
                            '></div></div>`,
                            className: '',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                            popupAnchor: [0, -16]
                        });
                    },

                    renderMarkers() {
                        if (this.markerLayer) {
                            this.map.removeLayer(this.markerLayer);
                        }

                        this.markerLayer = L.layerGroup();

                        this.markers.forEach(m => {
                            const color = this.getMarkerColor(m.status);
                            const statusLabel = this.getStatusLabel(m.status);
                            const tekananText = m.tekanan !== null ? m.tekanan.toFixed(2) + ' Bar' : '-';

                            const statusBadgeColor = {
                                normal: 'background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)',
                                rendah: 'background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)',
                                kritis: 'background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)',
                                unknown: 'background:rgba(100,116,139,0.15);color:#94a3b8;border:1px solid rgba(100,116,139,0.3)'
                            };

                            let valvesHtml = '';
                            if (m.valves && m.valves.length > 0) {
                                valvesHtml = `<div style='margin-top:12px;border-top:1px solid rgba(51,65,85,0.5);padding-top:10px;'>
                                    <div style='font-size:10px;font-weight:700;color:#94a3b8;margin-bottom:6px;letter-spacing:0.5px;'>DAFTAR GATE VALVE</div>
                                    <div style='display:flex;flex-direction:column;gap:6px;'>
                                `;
                                m.valves.forEach(v => {
                                    valvesHtml += `
                                        <div style='background:rgba(15,23,42,0.6);border:1px solid rgba(51,65,85,0.4);border-radius:6px;padding:8px;'>
                                            <div style='display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;'>
                                                <span style='font-size:12px;font-weight:600;color:#e2e8f0;'>${v.nama}</span>
                                                <span style='font-size:11px;font-weight:600;color:#38bdf8;'>${v.sisaBukaan} Sisa</span>
                                            </div>
                                            <div style='width:100%;height:4px;background:rgba(51,65,85,0.5);border-radius:2px;overflow:hidden;'>
                                                <div style='height:100%;width:${v.persentase}%;background:${v.persentase < 30 ? '#ef4444' : (v.persentase < 70 ? '#f59e0b' : '#10b981')}'></div>
                                            </div>
                                        </div>
                                    `;
                                });
                                valvesHtml += `</div></div>`;
                            }

                            const popup = `
                                <div style='font-family:Inter,sans-serif;min-width:240px;padding:4px 0;'>
                                    <div style='font-size:14px;font-weight:700;color:#f1f5f9;margin-bottom:4px;'>${m.nama}</div>
                                    <div style='font-size:10px;font-family:monospace;color:#64748b;margin-bottom:10px;display:flex;align-items:center;gap:4px;'>
                                        <svg style='width:12px;height:12px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'></path><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M15 11a3 3 0 11-6 0 3 3 0 016 0z'></path></svg>
                                        ${m.lat.toFixed(5)}, ${m.lng.toFixed(5)}
                                    </div>
                                    <div style='display:flex;align-items:center;gap:6px;margin-bottom:12px;'>
                                        <span style='${statusBadgeColor[m.status] || statusBadgeColor.unknown};padding:2px 10px;border-radius:6px;font-size:11px;font-weight:600;'>${statusLabel}</span>
                                    </div>
                                    <div style='display:grid;grid-template-columns:1fr 1fr;gap:8px;'>
                                        <div style='background:rgba(30,41,59,0.6);padding:8px 10px;border-radius:8px;border:1px solid rgba(51,65,85,0.4);'>
                                            <div style='font-size:10px;color:#64748b;margin-bottom:2px;'>Jumlah Aset</div>
                                            <div style='font-size:16px;font-weight:700;color:#e2e8f0;'>${m.jumlahAset}</div>
                                        </div>
                                        <div style='background:rgba(30,41,59,0.6);padding:8px 10px;border-radius:8px;border:1px solid rgba(51,65,85,0.4);'>
                                            <div style='font-size:10px;color:#64748b;margin-bottom:2px;'>Tekanan</div>
                                            <div style='font-size:16px;font-weight:700;color:${color};'>${tekananText}</div>
                                        </div>
                                    </div>
                                    ${valvesHtml}
                                </div>
                            `;

                            const marker = L.marker([m.lat, m.lng], {
                                icon: this.createCircleIcon(color)
                            }).bindPopup(popup, {
                                className: 'dark-popup',
                                maxWidth: 280
                            });

                            this.markerLayer.addLayer(marker);
                        });

                        this.markerLayer.addTo(this.map);
                    }
                }"
                x-ref="mapContainer"
                class="w-full"
                style="height: 550px;"
            ></div>
        </div>
    </div>

    {{-- Location Detail Table --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40">
            <h3 class="text-base font-semibold text-white">Detail Lokasi</h3>
            <p class="text-xs text-slate-400 mt-0.5">Ringkasan aset dan tekanan per kecamatan</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/40">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Koordinat</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Jumlah Aset</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tekanan Terakhir</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @foreach ($markers as $marker)
                        @php
                            $statusStyles = match($marker['status']) {
                                'normal' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                                'rendah' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                                'kritis' => 'bg-red-500/15 text-red-400 border-red-500/20',
                                default => 'bg-slate-500/15 text-slate-400 border-slate-500/20',
                            };
                            $statusLabel = match($marker['status']) {
                                'normal' => 'Normal',
                                'rendah' => 'Rendah',
                                'kritis' => 'Kritis',
                                default => 'N/A',
                            };
                        @endphp
                        <tr class="hover:bg-slate-700/20 transition-colors duration-150">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="w-2.5 h-2.5 rounded-full {{ match($marker['status']) {
                                        'normal' => 'bg-emerald-500',
                                        'rendah' => 'bg-amber-500',
                                        'kritis' => 'bg-red-500',
                                        default => 'bg-slate-500',
                                    } }}"></span>
                                    <span class="font-medium text-white">{{ $marker['nama'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="text-slate-400 font-mono text-xs">{{ number_format($marker['lat'], 4) }}, {{ number_format($marker['lng'], 4) }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="text-white font-semibold">{{ $marker['jumlahAset'] }}</span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="font-mono {{ match($marker['status']) {
                                    'normal' => 'text-emerald-400',
                                    'rendah' => 'text-amber-400',
                                    'kritis' => 'text-red-400',
                                    default => 'text-slate-400',
                                } }}">
                                    {{ $marker['tekanan'] !== null ? number_format($marker['tekanan'], 2) . ' Bar' : '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $statusStyles }}">
                                    {{ $statusLabel }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
