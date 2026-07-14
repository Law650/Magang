<div wire:poll.10s class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Peta Distribusi</h1>
            <p class="text-sm text-slate-400 mt-1">Monitoring lokasi Gate Valve dan status tekanan air</p>
        </div>
        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-800/60 border border-slate-700/50">
            <svg class="w-4 h-4 text-cyan-400 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            <span class="text-xs text-slate-400">Auto-refresh 30 detik</span>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="flex items-center gap-1 p-1 rounded-xl bg-slate-800/60 border border-slate-700/40 w-fit">
        <button wire:click="setTab('gv')"
                class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200
                {{ $activeTab === 'gv' ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Peta Gate Valve
        </button>
        <button wire:click="setTab('tekanan')"
                class="flex items-center gap-2 px-5 py-2.5 rounded-lg text-sm font-semibold transition-all duration-200
                {{ $activeTab === 'tekanan' ? 'bg-gradient-to-r from-cyan-500 to-blue-600 text-white shadow-lg shadow-cyan-500/25' : 'text-slate-400 hover:text-white hover:bg-slate-700/50' }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Peta Tekanan Air
        </button>
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB: PETA GATE VALVE                                      --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @if($activeTab === 'gv')

    {{-- GV Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Total GV</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $gvStats['total'] }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Bukaan Penuh</span>
            </div>
            <p class="text-2xl font-bold text-emerald-400">{{ $gvStats['penuh'] }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Sebagian</span>
            </div>
            <p class="text-2xl font-bold text-amber-400">{{ $gvStats['sebagian'] }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-500/10 text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Tertutup</span>
            </div>
            <p class="text-2xl font-bold text-red-400">{{ $gvStats['tertutup'] }}</p>
        </div>
    </div>

    {{-- GV Map --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-white">Peta Gate Valve</h3>
                <p class="text-xs text-slate-400 mt-0.5">Klik marker untuk melihat detail aset</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Penuh</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Sebagian</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500"></span> Tertutup</div>
            </div>
        </div>
        <div wire:ignore>
            <div
                x-data="{
                    map: null, markerLayer: null, markers: @js($gvMarkers), markerObjects: {},
                    init() {
                        this.initMap();
                        this.renderMarkers();
                        window.addEventListener('gv-markers-updated', (e) => { this.markers = e.detail.markers; this.renderMarkers(); });
                        window.addEventListener('focus-gv-marker', (e) => {
                            if (this.map) { this.map.setView([e.detail.lat, e.detail.lng], 16, { animate: true });
                                if (this.markerObjects[e.detail.id]) setTimeout(() => this.markerObjects[e.detail.id].openPopup(), 250);
                            }
                        });
                    },
                    initMap() {
                        this.map = L.map(this.$refs.gvMap, { zoomControl: false }).setView([-7.01, 109.40], 11);
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; OSM &copy; CARTO', subdomains: 'abcd', maxZoom: 19 }).addTo(this.map);
                        L.control.zoom({ position: 'topright' }).addTo(this.map);
                    },
                    getColor(s) { return { penuh: '#10b981', sebagian: '#f59e0b', tertutup: '#ef4444' }[s] || '#64748b'; },
                    getLabel(s) { return { penuh: 'Bukaan Penuh', sebagian: 'Sebagian', tertutup: 'Tertutup' }[s] || 'Unknown'; },
                    icon(color) {
                        return L.divIcon({ html: `<div style='width:28px;height:28px;background:${color};border:3px solid rgba(15,23,42,0.9);border-radius:50%;box-shadow:0 0 12px ${color}88,0 2px 8px rgba(0,0,0,0.4);position:relative;'><div style='position:absolute;inset:4px;border-radius:50%;background:radial-gradient(circle at 35% 35%,rgba(255,255,255,0.4),transparent);'></div></div>`, className:'', iconSize:[28,28], iconAnchor:[14,14], popupAnchor:[0,-16] });
                    },
                    renderMarkers() {
                        if (this.markerLayer) this.map.removeLayer(this.markerLayer);
                        this.markerLayer = L.layerGroup(); this.markerObjects = {};
                        this.markers.forEach(m => {
                            const c = this.getColor(m.status);
                            const badge = { penuh:'background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)', sebagian:'background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)', tertutup:'background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)' };
                            const popup = `<div style='font-family:Inter,sans-serif;min-width:220px;padding:4px 0;'>
                                <div style='font-size:14px;font-weight:700;color:#f1f5f9;margin-bottom:4px;'>${m.nama}</div>
                                <div style='font-size:11px;color:#94a3b8;margin-bottom:10px;'>${m.lokasi}</div>
                                <div style='margin-bottom:10px;'><span style='${badge[m.status]||badge.penuh};padding:2px 10px;border-radius:6px;font-size:11px;font-weight:600;'>${this.getLabel(m.status)}</span></div>
                                <div style='display:grid;grid-template-columns:1fr 1fr;gap:8px;'>
                                    <div style='background:rgba(30,41,59,0.6);padding:8px 10px;border-radius:8px;border:1px solid rgba(51,65,85,0.4);'><div style='font-size:10px;color:#64748b;margin-bottom:2px;'>Kapasitas</div><div style='font-size:14px;font-weight:700;color:#e2e8f0;'>${m.kapasitas} Put</div></div>
                                    <div style='background:rgba(30,41,59,0.6);padding:8px 10px;border-radius:8px;border:1px solid rgba(51,65,85,0.4);'><div style='font-size:10px;color:#64748b;margin-bottom:2px;'>Sisa Bukaan</div><div style='font-size:14px;font-weight:700;color:${c};'>${m.sisaBukaan} Put</div></div>
                                </div>
                                <div style='margin-top:10px;background:rgba(30,41,59,0.6);padding:8px 10px;border-radius:8px;border:1px solid rgba(51,65,85,0.4);'><div style='font-size:10px;color:#64748b;margin-bottom:2px;'>Petugas Terakhir</div><div style='font-size:12px;font-weight:600;color:#e2e8f0;'>${m.teknisiTerakhir}</div></div>
                                <div style='margin-top:8px;'><div style='width:100%;height:6px;background:rgba(51,65,85,0.5);border-radius:3px;overflow:hidden;'><div style='height:100%;width:${m.persentase}%;background:${c};border-radius:3px;'></div></div><div style='font-size:10px;color:#94a3b8;margin-top:4px;text-align:right;'>${m.persentase}% terbuka</div></div>
                            </div>`;
                            const marker = L.marker([m.lat, m.lng], { icon: this.icon(c) }).bindPopup(popup, { className:'dark-popup', maxWidth:280 });
                            this.markerObjects[m.id] = marker;
                            this.markerLayer.addLayer(marker);
                        });
                        this.markerLayer.addTo(this.map);
                    }
                }"
                x-ref="gvMap" class="w-full" style="height: 550px;"></div>
        </div>
    </div>

    {{-- GV Detail Table --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40">
            <h3 class="text-base font-semibold text-white">Detail Gate Valve</h3>
            <p class="text-xs text-slate-400 mt-0.5">Daftar aset valve beserta status bukaan</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/40">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Nama Aset</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Kapasitas</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Sisa Bukaan</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @foreach ($gvMarkers as $m)
                        @php
                            $stStyle = match($m['status']) {
                                'penuh' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                                'sebagian' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                                'tertutup' => 'bg-red-500/15 text-red-400 border-red-500/20',
                                default => 'bg-slate-500/15 text-slate-400 border-slate-500/20',
                            };
                            $stLabel = match($m['status']) {
                                'penuh' => 'Penuh', 'sebagian' => 'Sebagian', 'tertutup' => 'Tertutup', default => 'N/A',
                            };
                            $dotColor = match($m['status']) {
                                'penuh' => 'bg-emerald-500', 'sebagian' => 'bg-amber-500', 'tertutup' => 'bg-red-500', default => 'bg-slate-500',
                            };
                        @endphp
                        <tr class="hover:bg-slate-700/20 transition-colors duration-150 cursor-pointer" x-on:click="$dispatch('focus-gv-marker', { lat: {{ $m['lat'] }}, lng: {{ $m['lng'] }}, id: {{ $m['id'] }} })">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $dotColor }}"></span>
                                    <span class="font-medium text-white">{{ $m['nama'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-slate-400">{{ $m['lokasi'] }}</td>
                            <td class="px-6 py-3.5 text-center text-slate-300 font-mono text-xs">{{ $m['kapasitas'] }} Put</td>
                            <td class="px-6 py-3.5 text-center font-mono text-xs {{ match($m['status']) { 'penuh' => 'text-emerald-400', 'sebagian' => 'text-amber-400', 'tertutup' => 'text-red-400', default => 'text-slate-400' } }}">{{ $m['sisaBukaan'] }} Put</td>
                            <td class="px-6 py-3.5 text-center">
                                <span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $stStyle }}">{{ $stLabel }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @else
    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- TAB: PETA TEKANAN AIR                                     --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}

    {{-- Tekanan Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-cyan-500/10 text-cyan-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Total Lokasi</span>
            </div>
            <p class="text-2xl font-bold text-white">{{ $tekananStats['total'] }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Normal</span>
            </div>
            <p class="text-2xl font-bold text-emerald-400">{{ $tekananStats['normal'] }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-amber-500/10 text-amber-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Rendah</span>
            </div>
            <p class="text-2xl font-bold text-amber-400">{{ $tekananStats['rendah'] }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-red-500/10 text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs font-medium text-slate-400">Kritis</span>
            </div>
            <p class="text-2xl font-bold text-red-400">{{ $tekananStats['kritis'] }}</p>
        </div>
    </div>

    {{-- Tekanan Map --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40 flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-white">Peta Tekanan Air</h3>
                <p class="text-xs text-slate-400 mt-0.5">Klik marker untuk melihat detail tekanan</p>
            </div>
            <div class="flex items-center gap-4 text-xs text-slate-400">
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500"></span> Normal (Mengalir)</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-amber-500"></span> Rendah (Mengalir)</div>
                <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-500"></span> Kritis (Tidak Mengalir)</div>
            </div>
        </div>
        <div wire:ignore>
            <div
                x-data="{
                    map: null, markerLayer: null, markers: @js($tekananMarkers), markerObjects: {},
                    init() {
                        this.initMap();
                        this.renderMarkers();
                        window.addEventListener('tekanan-markers-updated', (e) => { this.markers = e.detail.markers; this.renderMarkers(); });
                        window.addEventListener('focus-tekanan-marker', (e) => {
                            if (this.map) { this.map.setView([e.detail.lat, e.detail.lng], 16, { animate: true });
                                if (this.markerObjects[e.detail.id]) setTimeout(() => this.markerObjects[e.detail.id].openPopup(), 250);
                            }
                        });
                    },
                    initMap() {
                        this.map = L.map(this.$refs.tekananMap, { zoomControl: false }).setView([-7.01, 109.40], 11);
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png', { attribution: '&copy; OSM &copy; CARTO', subdomains: 'abcd', maxZoom: 19 }).addTo(this.map);
                        L.control.zoom({ position: 'topright' }).addTo(this.map);
                    },
                    getColor(s) { return { normal:'#10b981', rendah:'#f59e0b', kritis:'#ef4444', unknown:'#64748b' }[s] || '#64748b'; },
                    getLabel(s) { return { normal:'Normal', rendah:'Rendah', kritis:'Kritis', unknown:'Belum Ada Data' }[s] || 'Unknown'; },
                    getAliranLabel(s) { return s === 'mengalir' ? 'Mengalir' : (s === 'tidak_mengalir' ? 'Tidak Mengalir' : '-'); },
                    getAliranColor(s) { return s === 'mengalir' ? '#06b6d4' : (s === 'tidak_mengalir' ? '#ef4444' : '#64748b'); },
                    icon(color) {
                        return L.divIcon({ html: `<div style='width:28px;height:28px;background:${color};border:3px solid rgba(15,23,42,0.9);border-radius:50%;box-shadow:0 0 12px ${color}88,0 2px 8px rgba(0,0,0,0.4);position:relative;'><div style='position:absolute;inset:4px;border-radius:50%;background:radial-gradient(circle at 35% 35%,rgba(255,255,255,0.4),transparent);'></div></div>`, className:'', iconSize:[28,28], iconAnchor:[14,14], popupAnchor:[0,-16] });
                    },
                    renderMarkers() {
                        if (this.markerLayer) this.map.removeLayer(this.markerLayer);
                        this.markerLayer = L.layerGroup(); this.markerObjects = {};
                        this.markers.forEach(m => {
                            const c = this.getColor(m.status);
                            const aliranC = this.getAliranColor(m.statusAliran);
                            const badge = { normal:'background:rgba(16,185,129,0.15);color:#10b981;border:1px solid rgba(16,185,129,0.3)', rendah:'background:rgba(245,158,11,0.15);color:#f59e0b;border:1px solid rgba(245,158,11,0.3)', kritis:'background:rgba(239,68,68,0.15);color:#ef4444;border:1px solid rgba(239,68,68,0.3)', unknown:'background:rgba(100,116,139,0.15);color:#94a3b8;border:1px solid rgba(100,116,139,0.3)' };
                            const tekananText = m.tekanan !== null ? m.tekanan.toFixed(2) + ' Bar' : '-';
                            const waktuText = m.waktu || '-';
                            const popup = `<div style='font-family:Inter,sans-serif;min-width:240px;padding:4px 0;'>
                                <div style='font-size:14px;font-weight:700;color:#f1f5f9;margin-bottom:4px;'>${m.nama}</div>
                                <div style='font-size:10px;font-family:monospace;color:#64748b;margin-bottom:10px;display:flex;align-items:center;gap:4px;'>
                                    <svg style='width:12px;height:12px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z'></path></svg>
                                    ${m.lat.toFixed(6)}, ${m.lng.toFixed(6)}
                                </div>
                                <div style='display:flex;align-items:center;gap:6px;margin-bottom:12px;'>
                                    <span style='${badge[m.status]||badge.unknown};padding:2px 10px;border-radius:6px;font-size:11px;font-weight:600;'>${this.getLabel(m.status)}</span>
                                    <span style='background:rgba(${aliranC === '#06b6d4' ? '6,182,212' : '239,68,68'},0.15);color:${aliranC};border:1px solid rgba(${aliranC === '#06b6d4' ? '6,182,212' : '239,68,68'},0.3);padding:2px 10px;border-radius:6px;font-size:11px;font-weight:600;'>${this.getAliranLabel(m.statusAliran)}</span>
                                </div>
                                <div style='display:grid;grid-template-columns:1fr 1fr;gap:8px;'>
                                    <div style='background:rgba(30,41,59,0.6);padding:8px 10px;border-radius:8px;border:1px solid rgba(51,65,85,0.4);'><div style='font-size:10px;color:#64748b;margin-bottom:2px;'>Tekanan</div><div style='font-size:16px;font-weight:700;color:${c};'>${tekananText}</div></div>
                                    <div style='background:rgba(30,41,59,0.6);padding:8px 10px;border-radius:8px;border:1px solid rgba(51,65,85,0.4);'><div style='font-size:10px;color:#64748b;margin-bottom:2px;'>Terakhir Dicek</div><div style='font-size:11px;font-weight:600;color:#e2e8f0;'>${waktuText}</div></div>
                                </div>
                            </div>`;
                            const marker = L.marker([m.lat, m.lng], { icon: this.icon(c) }).bindPopup(popup, { className:'dark-popup', maxWidth:300 });
                            this.markerObjects[m.id] = marker;
                            this.markerLayer.addLayer(marker);
                        });
                        this.markerLayer.addTo(this.map);
                    }
                }"
                x-ref="tekananMap" class="w-full" style="height: 550px;"></div>
        </div>
    </div>

    {{-- Tekanan Detail Table --}}
    <div class="rounded-2xl bg-gradient-to-br from-slate-800/80 to-slate-900/80 border border-slate-700/40 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-700/40">
            <h3 class="text-base font-semibold text-white">Detail Tekanan Air</h3>
            <p class="text-xs text-slate-400 mt-0.5">Ringkasan tekanan dan aliran per daerah</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-700/40">
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Koordinat</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Tekanan</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Status</th>
                        <th class="text-center px-6 py-3 text-xs font-semibold text-slate-400 uppercase tracking-wider">Aliran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-700/30">
                    @foreach ($tekananMarkers as $m)
                        @php
                            $stStyle = match($m['status']) {
                                'normal' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/20',
                                'rendah' => 'bg-amber-500/15 text-amber-400 border-amber-500/20',
                                'kritis' => 'bg-red-500/15 text-red-400 border-red-500/20',
                                default => 'bg-slate-500/15 text-slate-400 border-slate-500/20',
                            };
                            $stLabel = match($m['status']) {
                                'normal' => 'Normal', 'rendah' => 'Rendah', 'kritis' => 'Kritis', default => 'N/A',
                            };
                            $dotColor = match($m['status']) {
                                'normal' => 'bg-emerald-500', 'rendah' => 'bg-amber-500', 'kritis' => 'bg-red-500', default => 'bg-slate-500',
                            };
                            $aliranLabel = match($m['statusAliran'] ?? null) {
                                'mengalir' => 'Mengalir', 'tidak_mengalir' => 'Tidak Mengalir', default => '-',
                            };
                            $aliranStyle = match($m['statusAliran'] ?? null) {
                                'mengalir' => 'bg-cyan-500/15 text-cyan-400 border-cyan-500/20',
                                'tidak_mengalir' => 'bg-red-500/15 text-red-400 border-red-500/20',
                                default => 'bg-slate-500/15 text-slate-400 border-slate-500/20',
                            };
                        @endphp
                        <tr class="hover:bg-slate-700/20 transition-colors duration-150 cursor-pointer" x-on:click="$dispatch('focus-tekanan-marker', { lat: {{ $m['lat'] }}, lng: {{ $m['lng'] }}, id: {{ $m['id'] }} })">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="w-2.5 h-2.5 rounded-full {{ $dotColor }}"></span>
                                    <span class="font-medium text-white">{{ $m['nama'] }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3.5 text-center"><span class="text-slate-400 font-mono text-xs">{{ number_format($m['lat'], 4) }}, {{ number_format($m['lng'], 4) }}</span></td>
                            <td class="px-6 py-3.5 text-center font-mono {{ match($m['status']) { 'normal' => 'text-emerald-400', 'rendah' => 'text-amber-400', 'kritis' => 'text-red-400', default => 'text-slate-400' } }}">{{ $m['tekanan'] !== null ? number_format($m['tekanan'], 2) . ' Bar' : '-' }}</td>
                            <td class="px-6 py-3.5 text-center"><span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $stStyle }}">{{ $stLabel }}</span></td>
                            <td class="px-6 py-3.5 text-center"><span class="inline-flex px-2.5 py-0.5 rounded-md text-xs font-medium border {{ $aliranStyle }}">{{ $aliranLabel }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @endif

</div>
