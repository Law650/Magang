import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/models/queued_log.dart';
import '../../../core/services/sync_controller.dart';
import '../../../core/services/api_client.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/ui_components.dart';
import '../../../core/widgets/searchable_bottom_sheet.dart';
import '../../log_tekanan/data/rekap_tekanan_provider.dart';
import '../../log_tekanan/data/lokasi_repository.dart';
import '../../log_valve/data/aset_repository.dart';
import '../../log_valve/presentation/log_valve_form_page.dart';
import '../../log_tekanan/presentation/log_tekanan_form_page.dart';
import '../../../core/providers/technician_provider.dart';
import 'dart:async';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
class RiwayatPage extends ConsumerStatefulWidget {
  final AsetValve? initialAset;
  final RekapTekanan? initialRekap;
  final String? filterKategori;
  final String? filterLokasiName;
  final String? filterAsetName;
  final String? filterNoSr;

  const RiwayatPage({
    super.key, 
    this.initialAset, 
    this.initialRekap,
    this.filterKategori,
    this.filterLokasiName,
    this.filterAsetName,
    this.filterNoSr,
  });

  @override
  ConsumerState<RiwayatPage> createState() => _RiwayatPageState();
}

class _RiwayatPageState extends ConsumerState<RiwayatPage> {
  String? _selectedLokasiName;
  String? _selectedNoSr;
  String? _selectedAsetName;
  String _kategoriFilter = 'Tekanan';
  Timer? _refreshTimer;

  @override
  void initState() {
    super.initState();
    if (widget.initialAset != null) {
      _kategoriFilter = 'Valve';
      _selectedLokasiName = widget.initialAset!.namaLokasi;
      _selectedAsetName = widget.initialAset!.namaAset;
    } else if (widget.initialRekap != null) {
      _kategoriFilter = 'Tekanan';
      _selectedLokasiName = widget.initialRekap!.namaLokasi;
      _selectedNoSr = widget.initialRekap!.noSr;
    } else if (widget.filterKategori != null) {
      _kategoriFilter = widget.filterKategori!;
      _selectedLokasiName = widget.filterLokasiName;
      if (_kategoriFilter == 'Valve') {
        _selectedAsetName = widget.filterAsetName;
      } else {
        _selectedNoSr = widget.filterNoSr;
      }
    }
    _refreshTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      final syncState = ref.read(syncControllerProvider);
      if (!syncState.isSyncing) {
        // ref.read(syncControllerProvider.notifier).forceSyncNow(); // don't force sync every 5s, just refresh data
        ref.invalidate(asetValveListProvider);
        ref.invalidate(rekapTekananProvider);
        ref.invalidate(lokasiListProvider);
      }
    });
  }

  @override
  void dispose() {
    _refreshTimer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final syncState = ref.watch(syncControllerProvider);
    final syncController = ref.read(syncControllerProvider.notifier);
    final asetAsync = ref.watch(asetValveListProvider);
    final lokasiAsync = ref.watch(lokasiListProvider);
    final rekapTekananAsync = ref.watch(rekapTekananProvider);
    
    final currentUserName = ref.watch(technicianNameProvider);

    // Semua log difilter berdasarkan nama user dan kategori tab yang dipilih
    final allLogs = syncController.getAllLogs().where((log) {
      if (log.payloadFields['nama_teknisi'] != currentUserName && log.idempotencyKey != 'db_mock') {
        return false;
      }
      if (_kategoriFilter == 'Tekanan') {
        return log.endpoint.contains('/log-tekanan');
      } else {
        return log.endpoint.contains('/log-valve');
      }
    }).toList();
    
    // Filter berdasarkan lokasi jika dipilih
    final filteredByLokasi = _selectedLokasiName == null 
        ? allLogs 
        : allLogs.where((log) {
            final pNama = log.payloadFields['nama_lokasi'];
            return pNama == _selectedLokasiName;
          }).toList();

    // Filter berdasarkan aset jika dipilih (untuk list tabel riwayat)
    final filteredLogs = _selectedAsetName == null 
        ? filteredByLokasi 
        : filteredByLokasi.where((log) => log.payloadFields['nama_aset'] == _selectedAsetName).toList();

    // Data untuk kartu status langsung (HANYA log valve dari antrean offline)
    final valveLogs = filteredLogs.where((log) => log.endpoint.contains('/log-valve')).toList();
    QueuedLog? latestLog = valveLogs.isNotEmpty ? valveLogs.first : null;
    
    // Prepare latest logs map to restrict editing (hanya bisa edit laporan terbaru)
    final sortedAllLogs = List<QueuedLog>.from(allLogs)
      ..sort((a, b) {
        final tA = DateTime.tryParse(a.payloadFields['waktu_kegiatan']?.toString() ?? '') ?? a.createdAt;
        final tB = DateTime.tryParse(b.payloadFields['waktu_kegiatan']?.toString() ?? '') ?? b.createdAt;
        return tB.compareTo(tA);
      });
      
    final latestValveKeys = <int>{};
    final latestTekananKeys = <String>{};
    final allowedToEditKeys = <String>{};
    
    for (final log in sortedAllLogs) {
      if (log.idempotencyKey == 'db_mock') continue;
      final map = log.payloadFields;
      if (log.endpoint.contains('/log-valve')) {
        final asetId = map['aset_id'] as int?;
        if (asetId != null && !latestValveKeys.contains(asetId)) {
          latestValveKeys.add(asetId);
          allowedToEditKeys.add(log.idempotencyKey);
        }
      } else {
        final loc = map['nama_lokasi']?.toString();
        if (loc != null && !latestTekananKeys.contains(loc)) {
          latestTekananKeys.add(loc);
          allowedToEditKeys.add(log.idempotencyKey);
        }
      }
    }

    
    // Data rekap tekanan dari server untuk lokasi yang dipilih
    RekapTekanan? selectedRekap;
    if (_kategoriFilter == 'Tekanan' && _selectedLokasiName != null && _selectedAsetName == null) {
      final rekapList = rekapTekananAsync.valueOrNull ?? [];
      selectedRekap = rekapList.where((r) => r.namaLokasi == _selectedLokasiName).firstOrNull;
    }

    AsetValve? selectedAsetDb;
    if (_selectedAsetName != null) {
      final asetList = asetAsync.valueOrNull ?? [];
      for (final a in asetList) {
        bool match = a.namaAset == _selectedAsetName;
        if (_selectedLokasiName != null) {
          match = match && a.namaLokasi == _selectedLokasiName;
        }
        if (match) {
          selectedAsetDb = a;
          break;
        }
      }
    }

    final theme = Theme.of(context);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Histori & Akumulasi'),
        actions: [
          IconButton(
            onPressed: syncState.isSyncing ? null : () => syncController.forceSyncNow(),
            icon: syncState.isSyncing
                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                : const Icon(Icons.sync_rounded),
            tooltip: 'Sinkronkan Data',
          ),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => await syncController.forceSyncNow(),
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
              sliver: SliverToBoxAdapter(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Breadcrumb(title: 'Histori & Akumulasi'),
                    const SizedBox(height: 16),
                    const FormHeaderCard(
                      title: 'Histori & Akumulasi Status Valve',
                      subtitle: 'Pelacakan kronologis perubahan posisi valve jaringan.',
                      icon: Icons.access_time,
                    ),
                    const SizedBox(height: 16),

                    // Filter Data
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.cardBorder),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.filter_alt_outlined, color: AppColors.textSecondary, size: 20),
                              const SizedBox(width: 8),
                              Text('Filter Data', style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                            ],
                          ),
                          const SizedBox(height: 16),
                          // Kategori Toggle
                          Row(
                            children: [
                              Expanded(
                                child: InkWell(
                                  onTap: () {
                                    if (_kategoriFilter != 'Tekanan') {
                                      setState(() {
                                        _kategoriFilter = 'Tekanan';
                                        _selectedLokasiName = null;
                                        _selectedNoSr = null;
                                        _selectedAsetName = null;
                                      });
                                    }
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 8),
                                    decoration: BoxDecoration(
                                      color: _kategoriFilter == 'Tekanan' ? AppColors.primary : Colors.transparent,
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(color: _kategoriFilter == 'Tekanan' ? AppColors.primary : AppColors.cardBorder),
                                    ),
                                    alignment: Alignment.center,
                                    child: Text('Tekanan', style: TextStyle(color: _kategoriFilter == 'Tekanan' ? Colors.white : AppColors.textSecondary, fontWeight: FontWeight.bold)),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: InkWell(
                                  onTap: () {
                                    if (_kategoriFilter != 'Valve') {
                                      setState(() {
                                        _kategoriFilter = 'Valve';
                                        _selectedLokasiName = null;
                                        _selectedNoSr = null;
                                        _selectedAsetName = null;
                                      });
                                    }
                                  },
                                  child: Container(
                                    padding: const EdgeInsets.symmetric(vertical: 8),
                                    decoration: BoxDecoration(
                                      color: _kategoriFilter == 'Valve' ? AppColors.primary : Colors.transparent,
                                      borderRadius: BorderRadius.circular(8),
                                      border: Border.all(color: _kategoriFilter == 'Valve' ? AppColors.primary : AppColors.cardBorder),
                                    ),
                                    alignment: Alignment.center,
                                    child: Text('Valve', style: TextStyle(color: _kategoriFilter == 'Valve' ? Colors.white : AppColors.textSecondary, fontWeight: FontWeight.bold)),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          Text(_kategoriFilter == 'Tekanan' ? 'Lokasi Tekanan' : 'Jalur Valve', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textSecondary, fontWeight: FontWeight.w600)),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            decoration: BoxDecoration(
                              border: Border.all(color: AppColors.cardBorder),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: InkWell(
                              onTap: () async {
                                if (_kategoriFilter == 'Tekanan') {
                                  final rekapList = rekapTekananAsync.valueOrNull ?? [];
                                  final selected = await SearchableBottomSheet.show<RekapTekanan>(
                                    context: context,
                                    title: 'Filter No SR / Lokasi',
                                    items: rekapList,
                                    itemAsString: (r) => r.namaLokasi,
                                  );
                                  if (selected != null) {
                                    setState(() {
                                      _selectedLokasiName = selected.namaLokasi;
                                      _selectedNoSr = selected.noSr;
                                      _selectedAsetName = null;
                                    });
                                  }
                                } else {
                                  final unik = (asetAsync.valueOrNull ?? []).map((a) => a.namaLokasi).toSet().toList();
                                  unik.sort();
                                  
                                  final selected = await SearchableBottomSheet.show<String>(
                                    context: context,
                                    title: 'Filter Lokasi',
                                    items: unik,
                                    itemAsString: (l) => l,
                                  );
                                  if (selected != null) {
                                    setState(() {
                                      _selectedLokasiName = selected;
                                      _selectedNoSr = null;
                                      _selectedAsetName = null;
                                    });
                                  }
                                }
                              },
                              child: Container(
                                height: 48,
                                alignment: Alignment.centerLeft,
                                child: Row(
                                  children: [
                                    Expanded(
                                      child: Text(
                                        _selectedLokasiName != null 
                                          ? _selectedLokasiName!
                                          : 'Pilih Lokasi',
                                        style: theme.textTheme.bodyLarge?.copyWith(
                                          color: _selectedLokasiName != null ? AppColors.textPrimary : AppColors.textHint,
                                        ),
                                      ),
                                    ),
                                    const Icon(Icons.arrow_drop_down, color: AppColors.textHint),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          
                          if (_kategoriFilter == 'Valve' && _selectedLokasiName != null) ...[
                            const SizedBox(height: 16),
                            Text('Aset / Valve', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textSecondary, fontWeight: FontWeight.w600)),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 16),
                              decoration: BoxDecoration(
                                border: Border.all(color: AppColors.cardBorder),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: InkWell(
                                onTap: () async {
                                  final unik = (asetAsync.valueOrNull ?? [])
                                      .where((a) => _selectedLokasiName == null || a.namaLokasi == _selectedLokasiName)
                                      .map((a) => a.namaAset)
                                      .toSet()
                                      .toList();
                                  unik.sort();
                                  
                                  final selected = await SearchableBottomSheet.show<String>(
                                    context: context,
                                    title: 'Filter Valve',
                                    items: unik,
                                    itemAsString: (a) => a,
                                  );
                                  if (selected != null) {
                                    setState(() => _selectedAsetName = selected);
                                  }
                                },
                                child: Container(
                                  height: 48,
                                  alignment: Alignment.centerLeft,
                                  child: Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                        _selectedAsetName ?? 'Semua Valve',
                                        style: theme.textTheme.bodyLarge?.copyWith(
                                          color: _selectedAsetName != null ? AppColors.textPrimary : AppColors.textHint,
                                        ),
                                      ),
                                    ),
                                    const Icon(Icons.arrow_drop_down, color: AppColors.textHint),
                                  ],
                                ),
                              ),
                            ),
                          ),
                          ], // Closes the ...[ from _kategoriFilter == 'Valve'
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),

                    // Kartu Status Langsung
                    if (_selectedLokasiName == null && _selectedAsetName == null)
                      const SizedBox.shrink()
                    else if (selectedAsetDb != null && selectedAsetDb.sisaBukaan != null)
                      _buildKartuStatusLangsungAset(context, selectedAsetDb, theme)
                    else if (_selectedLokasiName != null && selectedRekap != null)
                      _buildKartuStatusLangsungTekanan(context, selectedRekap, theme)
                    else
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: AppColors.primaryDark,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: const Center(
                          child: Text('Tidak ada data status langsung untuk filter ini.', style: TextStyle(color: Colors.white70)),
                        ),
                      ),
                    
                    const SizedBox(height: 24),

                    // Tabel Riwayat Kronologis
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: const BorderRadius.vertical(top: Radius.circular(16)),
                        border: Border.all(color: AppColors.cardBorder),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.list, color: AppColors.textSecondary),
                          const SizedBox(width: 8),
                          Text('Tabel Riwayat Kronologis', style: theme.textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold)),
                          const Spacer(),
                          Text('${filteredLogs.length} entri', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint)),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
            
            // List of logs
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              sliver: filteredLogs.isEmpty
                  ? SliverToBoxAdapter(
                      child: Container(
                        decoration: const BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
                          border: Border(
                            left: BorderSide(color: AppColors.cardBorder),
                            right: BorderSide(color: AppColors.cardBorder),
                            bottom: BorderSide(color: AppColors.cardBorder),
                          ),
                        ),
                        padding: const EdgeInsets.all(32),
                        child: const Center(
                          child: Text('Belum ada riwayat', style: TextStyle(color: AppColors.textHint)),
                        ),
                      ),
                    )
                  : SliverList(
                      delegate: SliverChildBuilderDelegate(
                        (context, index) {
                          final log = filteredLogs[index];
                          final isLast = index == filteredLogs.length - 1;
                          
                          return Container(
                            decoration: BoxDecoration(
                              color: AppColors.surface,
                              borderRadius: isLast ? const BorderRadius.vertical(bottom: Radius.circular(16)) : BorderRadius.zero,
                              border: Border(
                                left: const BorderSide(color: AppColors.cardBorder),
                                right: const BorderSide(color: AppColors.cardBorder),
                                bottom: BorderSide(color: AppColors.cardBorder, width: isLast ? 1.0 : 0.0),
                              ),
                            ),
                            child: Column(
                              children: [
                                _buildHistoryItem(
                                  context, 
                                  log, 
                                  filteredLogs.length - index, 
                                  allowedToEditKeys.contains(log.idempotencyKey),
                                ),
                                if (!isLast) const Divider(height: 1),
                              ],
                            ),
                          );
                        },
                        childCount: filteredLogs.length,
                      ),
                    ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildKartuStatusLangsungAset(BuildContext context, AsetValve aset, ThemeData theme) {
    final namaAset = aset.namaAset;
    final namaLokasi = aset.namaLokasi;
    
    final kapasitasFull = aset.kapasitasFullPutaran;
    final sisaBukaan = aset.sisaBukaan ?? 0.0;
    final totalTutupan = kapasitasFull - sisaBukaan;
    
    double percentBuka = 0.0;
    if (aset.persentaseBukaan != null) {
      percentBuka = aset.persentaseBukaan! / 100.0;
    } else if (kapasitasFull > 0) {
      percentBuka = sisaBukaan / kapasitasFull;
    }
    percentBuka = percentBuka.clamp(0.0, 1.0);
    final percentTutup = 1.0 - percentBuka;

    return InkWell(
      onTap: () {
        final dummyLog = QueuedLog(
          idempotencyKey: 'db_mock',
          endpoint: '/log-valve',
          fotoPath: '',
          payloadFields: {
            'aset_id': aset.id,
            'nama_aset': namaAset,
            'nama_lokasi': namaLokasi,
            'kapasitas_full': kapasitasFull,
            'sisa_bukaan': sisaBukaan,
            'latitude': aset.latitude ?? 0.0,
            'longitude': aset.longitude ?? 0.0,
            'nama_teknisi': aset.namaTeknisi,
            'keterangan': aset.keterangan,
            'foto_eviden': aset.fotoEviden,
            'foto_eviden_2': aset.fotoEviden2,
          },
        );
        _showAsetDetailBottomSheet(context, dummyLog);
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: const Color(0xFF1B4958), // Mirip warna dark blue/teal di gambar
          borderRadius: BorderRadius.circular(16),
          boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 8, offset: Offset(0, 4))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.monitor, color: Colors.white70, size: 20),
                const SizedBox(width: 8),
                Text('Informasi Gate Valve', style: theme.textTheme.titleMedium?.copyWith(color: Colors.white)),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(color: AppColors.statusNormal.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
                  child: const Row(
                    children: [
                      Icon(Icons.circle, color: AppColors.statusNormal, size: 8),
                      SizedBox(width: 4),
                      Text('Auto Fill', style: TextStyle(color: AppColors.statusNormal, fontSize: 10, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
          Text('Nama Aset', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
          Text(namaAset, style: theme.textTheme.titleLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.bold)),
          Text(namaLokasi, style: theme.textTheme.bodyMedium?.copyWith(color: Colors.white70)),
          
          if (aset.keterangan != null && aset.keterangan!.isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.black26,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white12),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Keterangan:', style: TextStyle(color: Colors.white54, fontSize: 11)),
                  const SizedBox(height: 4),
                  Text(aset.keterangan!, style: const TextStyle(color: Colors.white, fontSize: 13, fontStyle: FontStyle.italic)),
                ],
              ),
            ),
          ],
          
          if (aset.fotoEviden != null && aset.fotoEviden!.isNotEmpty) ...[
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(12),
                    child: Image.network(
                      ApiClient.formatImageUrl(aset.fotoEviden)!,
                      height: 100,
                      fit: BoxFit.cover,
                      errorBuilder: (_, __, ___) => Container(height: 100, color: Colors.black26, child: const Icon(Icons.broken_image, color: Colors.white54)),
                    ),
                  ),
                ),
                if (aset.fotoEviden2 != null && aset.fotoEviden2!.isNotEmpty) ...[
                  const SizedBox(width: 8),
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Image.network(
                        ApiClient.formatImageUrl(aset.fotoEviden2)!,
                        height: 100,
                        fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(height: 100, color: Colors.black26, child: const Icon(Icons.broken_image, color: Colors.white54)),
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ],
          
          const SizedBox(height: 20),
          Row(
            children: [
              Expanded(child: _buildValueCard('Kapasitas Full', kapasitasFull, const Color(0xFF2B5C6C))),
              const SizedBox(width: 8),
              Expanded(child: _buildValueCard('Total Tutupan', totalTutupan, const Color(0xFF5D3E4B))),
              const SizedBox(width: 8),
              Expanded(child: _buildValueCard('Putaran Saat Ini\n(Terbuka)', sisaBukaan, const Color(0xFF1B6A5C))),
            ],
          ),

          const SizedBox(height: 24),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('0 (Tertutup)', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
              Text('${kapasitasFull.toInt()} (Full Buka)', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
            ],
          ),
          const SizedBox(height: 8),
          Container(
            height: 12,
            width: double.infinity,
            decoration: BoxDecoration(color: Colors.black26, borderRadius: BorderRadius.circular(6)),
            child: Row(
              children: [
                Expanded(
                  flex: (percentTutup * 100).toInt().clamp(0, 100),
                  child: Container(decoration: BoxDecoration(color: AppColors.statusKritis, borderRadius: BorderRadius.horizontal(left: const Radius.circular(6), right: Radius.circular(percentBuka == 0 ? 6 : 0)))),
                ),
                Expanded(
                  flex: (percentBuka * 100).toInt().clamp(0, 100),
                  child: Container(decoration: BoxDecoration(color: AppColors.statusNormal, borderRadius: BorderRadius.horizontal(right: const Radius.circular(6), left: Radius.circular(percentTutup == 0 ? 6 : 0)))),
                ),
              ],
            ),
          ),
          const SizedBox(height: 8),
          Center(
            child: Text(
              '${(percentBuka * 100).toStringAsFixed(1)}% Terbuka',
              style: const TextStyle(color: AppColors.statusNormal, fontWeight: FontWeight.bold),
            ),
          ),
          const SizedBox(height: 16),
          _buildMiniMapPlaceholder(aset.latitude ?? 0.0, aset.longitude ?? 0.0, height: 100),
        ],
      ),
    ),
  );
}

  Widget _buildKartuStatusLangsungTekanan(BuildContext context, RekapTekanan rekap, ThemeData theme) {
    Color statusColor = AppColors.statusNetral;
    IconData statusIcon = Icons.help_outline;
    
    final s = rekap.status?.toLowerCase();
    if (s == 'normal') {
      statusColor = AppColors.statusNormal;
      statusIcon = Icons.check_circle;
    } else if (s == 'rendah') {
      statusColor = AppColors.statusRendah;
      statusIcon = Icons.warning;
    } else if (s == 'kritis') {
      statusColor = AppColors.statusKritis;
      statusIcon = Icons.error;
    }

    String formatWaktu(String? waktu) {
      if (waktu == null) return 'Belum ada data';
      try {
        final dt = DateTime.parse(waktu).toLocal();
        return DateFormat('dd/MM/yyyy HH:mm:ss').format(dt);
      } catch (_) {
        return waktu;
      }
    }

    return InkWell(
      onTap: () {
        final dummyLog = QueuedLog(
          idempotencyKey: 'db_mock',
          endpoint: '/log-tekanan',
          fotoPath: '',
          payloadFields: {
            'nama_lokasi': rekap.namaLokasi,
            'latitude': rekap.latitude ?? 0.0,
            'longitude': rekap.longitude ?? 0.0,
            'nilai_tekanan': rekap.nilaiTekanan,
            'status': rekap.status,
            'status_aliran': rekap.statusAliran,
            'kekeruhan': rekap.kekeruhan,
            'keterangan': rekap.keterangan,
            'no_sr': rekap.noSr,
            'nama_pelanggan': rekap.namaPelanggan,
            'alamat': rekap.alamat,
            'desa': rekap.desa,
            'nama_teknisi': rekap.namaTeknisi ?? 'Sistem',
            'foto_eviden': rekap.fotoEviden,
          },
        );
        // Kita bisa menggunakan _showDetailBottomSheet karena itu umum
        _showDetailBottomSheet(context, dummyLog, false);
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: const Color(0xFF1B4958), // Dark blue/teal
          borderRadius: BorderRadius.circular(16),
          boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 8, offset: Offset(0, 4))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.speed, color: Colors.white70, size: 20),
                const SizedBox(width: 8),
                Text('Informasi Tekanan Daerah', style: theme.textTheme.titleMedium?.copyWith(color: Colors.white)),
                const Spacer(),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(color: statusColor.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(statusIcon, color: statusColor, size: 10),
                          const SizedBox(width: 4),
                          Text(rekap.status?.toUpperCase() ?? 'BELUM ADA', style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ),
                    if (rekap.statusAliran != null) ...[
                      const SizedBox(height: 4),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: (rekap.statusAliran == 'mengalir' ? Colors.cyan : Colors.red).withValues(alpha: 0.2), 
                          borderRadius: BorderRadius.circular(12)
                        ),
                        child: Text(
                          rekap.statusAliran == 'mengalir' ? 'MENGALIR' : 'TIDAK MENGALIR', 
                          style: TextStyle(
                            color: rekap.statusAliran == 'mengalir' ? Colors.cyan : Colors.red, 
                            fontSize: 9, 
                            fontWeight: FontWeight.bold
                          )
                        ),
                      ),
                    ],
                  ],
                ),
              ],
            ),
            const SizedBox(height: 20),
          if (rekap.noSr != null && rekap.noSr!.isNotEmpty) ...[
            Text('No. SR Pelanggan', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
            Text(rekap.noSr!, style: theme.textTheme.titleLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.bold)),
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.black26,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white12),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Nama Pelanggan:', style: TextStyle(color: Colors.white54, fontSize: 11)),
                  const SizedBox(height: 4),
                  Text(rekap.namaPelanggan ?? '-', style: const TextStyle(color: Colors.white, fontSize: 13, fontStyle: FontStyle.italic)),
                  
                  if (rekap.alamat != null && rekap.alamat!.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    const Text('Alamat:', style: TextStyle(color: Colors.white54, fontSize: 11)),
                    const SizedBox(height: 4),
                    Text(rekap.alamat!, style: const TextStyle(color: Colors.white, fontSize: 13, fontStyle: FontStyle.italic)),
                  ],
                  
                  if (rekap.desa != null && rekap.desa!.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    const Text('Desa:', style: TextStyle(color: Colors.white54, fontSize: 11)),
                    const SizedBox(height: 4),
                    Text(rekap.desa!, style: const TextStyle(color: Colors.white, fontSize: 13, fontStyle: FontStyle.italic)),
                  ],
                ],
              ),
            ),
          ] else ...[
            Text('Nama Lokasi / Jalur', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
            Text(rekap.namaLokasi, style: theme.textTheme.titleLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.bold)),
          ],
          
          if (rekap.keterangan != null && rekap.keterangan!.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.black26,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: Colors.white12),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Keterangan:', style: TextStyle(color: Colors.white54, fontSize: 11)),
                  const SizedBox(height: 4),
                  Text(rekap.keterangan!, style: const TextStyle(color: Colors.white, fontSize: 13, fontStyle: FontStyle.italic)),
                ],
              ),
            ),
          ],
          
          if (rekap.fotoEviden != null && rekap.fotoEviden!.isNotEmpty) ...[
            const SizedBox(height: 16),
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.network(
                ApiClient.formatImageUrl(rekap.fotoEviden)!,
                height: 120,
                width: double.infinity,
                fit: BoxFit.cover,
                errorBuilder: (_, __, ___) => Container(height: 120, color: Colors.black26, child: const Icon(Icons.broken_image, color: Colors.white54)),
              ),
            ),
          ],
          
          const SizedBox(height: 20),
          Row(
            children: [
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFF2B5C6C),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Tekanan Air', style: TextStyle(color: Colors.white70, fontSize: 12)),
                      const SizedBox(height: 4),
                      Text(rekap.nilaiTekanan != null ? '${rekap.nilaiTekanan} Bar' : '-', style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1B6A5C),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Terakhir Dicek', style: TextStyle(color: Colors.white70, fontSize: 12)),
                      const SizedBox(height: 4),
                      Text(
                        formatWaktu(rekap.waktuPengecekan), 
                        style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          if (rekap.namaTeknisi != null) ...[
            const SizedBox(height: 16),
            Row(
              children: [
                const Icon(Icons.person_outline, size: 14, color: Colors.white70),
                const SizedBox(width: 6),
                Text('Petugas: ${rekap.namaTeknisi}', style: const TextStyle(color: Colors.white70, fontSize: 12)),
              ],
            ),
          ],
          
          const SizedBox(height: 16),
          _buildMiniMapPlaceholder(rekap.latitude ?? 0.0, rekap.longitude ?? 0.0, height: 100),
        ],
      ),
    ),
  );
}

  Widget _buildKartuStatusLangsung(BuildContext context, QueuedLog log, ThemeData theme) {
    final map = log.payloadFields;
    final namaAset = map['nama_aset'] ?? 'Unknown Valve';
    final namaLokasi = map['nama_lokasi'] ?? 'Unknown Location';
    
    final kapasitasFull = (map['kapasitas_full'] as num?)?.toDouble() ?? 58.0;
    final aksiKerjaRaw = map['aksi_kerja']?.toString().toLowerCase() ?? 'buka';
    final aksi = aksiKerjaRaw == 'tutup' ? 'Tutup' : 'Buka';
    final jumlahPutaran = (map['jumlah_putaran'] as num?)?.toDouble() ?? 0.0;
    
    final bukaanAwal = (map['bukaan_saat_ini'] as num?)?.toDouble() ?? kapasitasFull;
    
    double sisaBukaan = aksi == 'Tutup' 
        ? (bukaanAwal - jumlahPutaran) 
        : (bukaanAwal + jumlahPutaran);
    sisaBukaan = sisaBukaan.clamp(0.0, kapasitasFull);
    
    double totalTutupan = kapasitasFull - sisaBukaan;
    
    double percentTutup = kapasitasFull > 0 ? (totalTutupan / kapasitasFull) : 0;
    double percentBuka = 1.0 - percentTutup;

    return InkWell(
      onTap: () => _showAsetDetailBottomSheet(context, log),
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(20),
        decoration: BoxDecoration(
          color: const Color(0xFF1B4958), // Mirip warna dark blue/teal di gambar
          borderRadius: BorderRadius.circular(16),
          boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 8, offset: Offset(0, 4))],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                const Icon(Icons.monitor, color: Colors.white70, size: 20),
                const SizedBox(width: 8),
                Text('Kartu Status Langsung', style: theme.textTheme.titleMedium?.copyWith(color: Colors.white)),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(color: AppColors.statusNormal.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
                  child: const Row(
                    children: [
                      Icon(Icons.circle, color: AppColors.statusNormal, size: 8),
                      SizedBox(width: 4),
                      Text('Live', style: TextStyle(color: AppColors.statusNormal, fontSize: 10, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
            Text('Nama Aset', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
            Text(namaAset, style: theme.textTheme.titleLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.bold)),
            Text(namaLokasi, style: theme.textTheme.bodyMedium?.copyWith(color: Colors.white70)),
            
            if (map['keterangan'] != null && map['keterangan'].toString().isNotEmpty) ...[
              const SizedBox(height: 12),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: Colors.black26,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.white12),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Keterangan:', style: TextStyle(color: Colors.white54, fontSize: 11)),
                    const SizedBox(height: 4),
                    Text(map['keterangan'].toString(), style: const TextStyle(color: Colors.white, fontSize: 13, fontStyle: FontStyle.italic)),
                  ],
                ),
              ),
            ],
            
            if (log.fotoPath.isNotEmpty && File(log.fotoPath).existsSync()) ...[
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Image.file(
                        File(log.fotoPath),
                        height: 100,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Container(height: 100, color: Colors.black26, child: const Icon(Icons.broken_image, color: Colors.white54)),
                      ),
                    ),
                  ),
                  if (log.fotoPath2 != null && log.fotoPath2!.isNotEmpty && File(log.fotoPath2!).existsSync()) ...[
                    const SizedBox(width: 8),
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.file(
                          File(log.fotoPath2!),
                          height: 100,
                          fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(height: 100, color: Colors.black26, child: const Icon(Icons.broken_image, color: Colors.white54)),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ] else if (map['foto_eviden'] != null && map['foto_eviden'].toString().isNotEmpty) ...[
              const SizedBox(height: 16),
              Row(
                children: [
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(12),
                      child: Image.network(
                        ApiClient.formatImageUrl(map['foto_eviden'].toString())!,
                        height: 100,
                        fit: BoxFit.cover,
                          errorBuilder: (_, __, ___) => Container(height: 100, color: Colors.black26, child: const Icon(Icons.broken_image, color: Colors.white54)),
                      ),
                    ),
                  ),
                  if (map['foto_eviden_2'] != null && map['foto_eviden_2'].toString().isNotEmpty) ...[
                    const SizedBox(width: 8),
                    Expanded(
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(12),
                        child: Image.network(
                          ApiClient.formatImageUrl(map['foto_eviden_2'].toString())!,
                          height: 100,
                          fit: BoxFit.cover,
                              errorBuilder: (_, __, ___) => Container(height: 100, color: Colors.black26, child: const Icon(Icons.broken_image, color: Colors.white54)),
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ],
            
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(child: _buildValueCard('Kapasitas Full', kapasitasFull, const Color(0xFF2B5C6C))),
                const SizedBox(width: 8),
                Expanded(child: _buildValueCard('Total Tutupan', totalTutupan, const Color(0xFF5D3E4B))),
                const SizedBox(width: 8),
                Expanded(child: _buildValueCard('Putaran Saat Ini\n(Terbuka)', sisaBukaan, const Color(0xFF1B6A5C))),
              ],
            ),

            const SizedBox(height: 24),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('0 (Tertutup)', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
                Text('${kapasitasFull.toInt()} (Full Buka)', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
              ],
            ),
            const SizedBox(height: 8),
            
            // Progress Bar
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: Row(
                children: [
                  Expanded(
                    flex: (percentTutup * 100).toInt() == 0 ? 1 : (percentTutup * 100).toInt(),
                    child: Container(
                      height: 8,
                      color: AppColors.statusKritis,
                    ),
                  ),
                  Expanded(
                    flex: (percentBuka * 100).toInt() == 0 ? 1 : (percentBuka * 100).toInt(),
                    child: Container(
                      height: 8,
                      color: Colors.white,
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('${(percentTutup * 100).toStringAsFixed(1)}% Tertutup', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.statusKritis, fontWeight: FontWeight.bold)),
                Text('${(percentBuka * 100).toStringAsFixed(1)}% Terbuka', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.statusNormal, fontWeight: FontWeight.bold)),
              ],
            ),
            
            const SizedBox(height: 16),
            _buildMiniMapPlaceholder(
              (map['latitude'] as num?)?.toDouble() ?? 0.0, 
              (map['longitude'] as num?)?.toDouble() ?? 0.0, 
              height: 100
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildValueCard(String title, double value, Color bgColor) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 12),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(12),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(title, style: const TextStyle(fontSize: 10, color: Colors.white70), textAlign: TextAlign.center),
          const SizedBox(height: 4),
          Text(
            _formatNumber(value), 
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white)
          ),
          const SizedBox(height: 2),
          const Text('Putaran', style: TextStyle(fontSize: 10, color: Colors.white70)),
        ],
      ),
    );
  }

  Widget _buildHistoryItem(BuildContext context, QueuedLog log, int indexNumber, bool isLatest) {
    final theme = Theme.of(context);
    final dateFormat = DateFormat('dd MMM yyyy - HH:mm', 'id');
    final map = log.payloadFields;
    
    final isValve = log.endpoint.contains('/log-valve');
    final namaTeknisi = map['nama_teknisi'] ?? 'Unknown';
    final initial = namaTeknisi.toString().isNotEmpty ? namaTeknisi.toString()[0].toUpperCase() : '?';
    
    final statusColor = log.status == 'success' ? AppColors.statusNormal 
                      : log.status == 'failed' ? AppColors.statusKritis 
                      : Colors.orange;
    final statusIcon = log.status == 'success' ? Icons.check_circle 
                     : log.status == 'failed' ? Icons.error 
                     : Icons.schedule;
    
    // Detail untuk Valve
    final aksiKerjaRaw = map['aksi_kerja']?.toString().toLowerCase() ?? 'buka';
    final jumlahPutaran = (map['jumlah_putaran'] as num?)?.toDouble() ?? 0.0;
    final isCek = (aksiKerjaRaw == 'buka' && jumlahPutaran == 0.0);
    final aksiKerja = isCek ? 'Cek' : (aksiKerjaRaw == 'tutup' ? 'Tutup' : 'Buka');
    
    final kapasitasFull = (map['kapasitas_full'] as num?)?.toDouble() ?? 58.0;
    final bukaanAwal = (map['bukaan_saat_ini'] as num?)?.toDouble() ?? kapasitasFull;
    
    double sisa = aksiKerja == 'Tutup' 
        ? (bukaanAwal - jumlahPutaran) 
        : (bukaanAwal + jumlahPutaran);
    sisa = sisa.clamp(0.0, kapasitasFull);
    
    // Detail untuk Tekanan
    final tekanan = (map['nilai_tekanan'] as num?)?.toDouble() ?? 0.0;
    final aliran = map['status_aliran'] ?? 'Normal';

    return InkWell(
      onTap: () => _showDetailBottomSheet(context, log, isLatest),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('#$indexNumber', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint)),
            const SizedBox(width: 12),
            // Avatar
            CircleAvatar(
              backgroundColor: isValve ? (aksiKerja == 'Tutup' ? AppColors.statusKritis : (aksiKerja == 'Cek' ? Colors.blue : AppColors.statusNormal)) : AppColors.accentGreen,
              child: Text(initial, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(child: Text(namaTeknisi, style: theme.textTheme.bodyLarge?.copyWith(fontWeight: FontWeight.bold), maxLines: 1, overflow: TextOverflow.ellipsis)),
                      const SizedBox(width: 8),
                      // Status Sync Indicator
                      Row(
                        children: [
                          Icon(statusIcon, size: 14, color: statusColor),
                          const SizedBox(width: 4),
                          Text(log.status.toUpperCase(), style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.bold)),
                        ],
                      ),
                    ],
                  ),
                  Row(
                    children: [
                      Text(dateFormat.format(log.createdAt), style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint)),
                      if (map['is_edited'] == true) ...[
                        const SizedBox(width: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          decoration: BoxDecoration(color: Colors.blue.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(4)),
                          child: const Text('Diedit', style: TextStyle(color: Colors.blue, fontSize: 9, fontStyle: FontStyle.italic, fontWeight: FontWeight.bold)),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 8),
                  if (isValve)
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: (aksiKerja == 'Tutup' ? AppColors.statusKritis : (aksiKerja == 'Cek' ? Colors.blue : AppColors.statusNormal)).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            aksiKerja == 'Cek' ? 'Cek' : '+$aksiKerja',
                            style: TextStyle(
                              color: aksiKerja == 'Tutup' ? AppColors.statusKritis : (aksiKerja == 'Cek' ? Colors.blue : AppColors.statusNormal), 
                              fontWeight: FontWeight.bold, fontSize: 12
                            ),
                          ),
                        ),
                        if (aksiKerja != 'Cek') ...[
                          const SizedBox(width: 8),
                          Text('+${_formatNumber(jumlahPutaran)} Put.', style: theme.textTheme.bodyMedium?.copyWith(fontSize: 13, color: AppColors.textSecondary)),
                        ],
                        const SizedBox(width: 12),
                        Text('Posisi: ${_formatNumber(sisa)} Put.', style: theme.textTheme.bodyMedium?.copyWith(fontSize: 13, fontWeight: FontWeight.bold)),
                      ],
                    )
                  else
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppColors.accentGreen.withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: const Text(
                            'Tekanan',
                            style: TextStyle(
                              color: AppColors.accentGreen, 
                              fontWeight: FontWeight.bold, fontSize: 12
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text('$tekanan Bar ($aliran)', style: theme.textTheme.bodyMedium?.copyWith(fontSize: 13, fontWeight: FontWeight.bold)),
                      ],
                    )
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showAsetDetailBottomSheet(BuildContext context, QueuedLog log) {
    final map = log.payloadFields;
    final lat = (map['latitude'] as num?)?.toDouble() ?? 0.0;
    final lng = (map['longitude'] as num?)?.toDouble() ?? 0.0;
    
    final namaAset = map['nama_aset'] ?? 'Unknown Valve';
    final namaLokasi = map['nama_lokasi'] ?? 'Unknown Location';
    final kapasitasFull = (map['kapasitas_full'] as num?)?.toDouble() ?? 58.0;
    final idAset = map['aset_id'] ?? 'N/A';
    
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          margin: const EdgeInsets.all(16),
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 20, offset: Offset(0, 10))],
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 48, height: 6,
                    decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(3)),
                  ),
                ),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.primary.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.settings_input_component, color: AppColors.primary, size: 28),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Informasi Aset GV', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w800, color: AppColors.primaryDark)),
                          Text('ID Aset: $idAset', style: TextStyle(color: AppColors.textSecondary, fontWeight: FontWeight.w500)),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 24),
                
                // Data Grid
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.background,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.cardBorder),
                  ),
                  child: Column(
                    children: [
                      _buildDetailRow('Nama Aset', namaAset),
                      _buildDetailRow('Jalur/Lokasi', namaLokasi),
                      if (map['keterangan'] != null && map['keterangan'].toString().isNotEmpty)
                        _buildDetailRow('Keterangan', map['keterangan'].toString()),
                      if (map['sisa_bukaan'] != null) ...[
                        _buildDetailRow('Putaran Saat Ini\n(Terbuka)', '${_formatNumber((map['sisa_bukaan'] as num).toDouble())} Putaran'),
                        _buildDetailRow('Sisa Putaran\n(yang bisa dibuka', '${_formatNumber(kapasitasFull - (map['sisa_bukaan'] as num).toDouble())} Putaran'),
                        _buildDetailRow('Sisa Putaran\n(yang bisa ditutup)', '${_formatNumber((map['sisa_bukaan'] as num).toDouble())} Putaran'),
                      ],
                      if (map['aksi_kerja'] != null)
                        _buildDetailRow('Aksi Kerja', map['aksi_kerja'].toString()),
                      if (map['jumlah_putaran'] != null)
                        _buildDetailRow('Jml Putaran', '${_formatNumber((map['jumlah_putaran'] as num).toDouble())} Putaran'),
                      if (map['nama_teknisi'] != null)
                        _buildDetailRow('Petugas Terakhir', map['nama_teknisi'].toString()),
                      _buildDetailRow('Kapasitas Full', '${_formatNumber(kapasitasFull)} Putaran'),
                      if (lat != 0.0 || lng != 0.0)
                        _buildDetailRow('Titik Koordinat', '${lat.toStringAsFixed(5)}, ${lng.toStringAsFixed(5)}'),
                    ],
                  ),
                ),
                
                const SizedBox(height: 24),
                
                if (log.fotoPath.isNotEmpty && File(log.fotoPath).existsSync()) ...[
                  const Text('Foto Bukti', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: GestureDetector(
                            onTap: () {
                              showDialog(
                                context: context,
                                builder: (context) => Dialog(
                                  backgroundColor: Colors.transparent,
                                  insetPadding: const EdgeInsets.all(16),
                                  child: InteractiveViewer(
                                    child: Image.file(File(log.fotoPath)),
                                  ),
                                ),
                              );
                            },
                            child: Container(
                              height: 200,
                              child: Image.file(
                                File(log.fotoPath),
                                fit: BoxFit.cover,
                              ),
                            ),
                          ),
                        ),
                      ),
                      if (log.fotoPath2 != null && log.fotoPath2!.isNotEmpty && File(log.fotoPath2!).existsSync()) ...[
                        const SizedBox(width: 8),
                        Expanded(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: GestureDetector(
                              onTap: () {
                                showDialog(
                                  context: context,
                                  builder: (context) => Dialog(
                                    backgroundColor: Colors.transparent,
                                    insetPadding: const EdgeInsets.all(16),
                                    child: InteractiveViewer(
                                      child: Image.file(File(log.fotoPath2!)),
                                    ),
                                  ),
                                );
                              },
                              child: Container(
                                height: 200,
                                child: Image.file(
                                  File(log.fotoPath2!),
                                  fit: BoxFit.cover,
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 24),
                ] else if (map['foto_eviden'] != null && map['foto_eviden'].toString().isNotEmpty) ...[
                  const Text('Foto Bukti', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: GestureDetector(
                            onTap: () {
                              showDialog(
                                context: context,
                                builder: (context) => Dialog(
                                  backgroundColor: Colors.transparent,
                                  insetPadding: const EdgeInsets.all(16),
                                  child: InteractiveViewer(
                                    child: Image.network(
                                      ApiClient.formatImageUrl(map['foto_eviden'].toString()) ?? '',
                                    ),
                                  ),
                                ),
                              );
                            },
                            child: Container(
                              height: 200,
                              child: Image.network(
                                ApiClient.formatImageUrl(map['foto_eviden'].toString()) ?? '',
                                          fit: BoxFit.cover,
                                errorBuilder: (_, __, ___) => Container(
                                  width: double.infinity,
                                  height: 200,
                                  color: Colors.grey[200],
                                  child: const Center(child: Icon(Icons.broken_image, color: Colors.grey, size: 40)),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ),
                      if (map['foto_eviden_2'] != null && map['foto_eviden_2'].toString().isNotEmpty) ...[
                        const SizedBox(width: 8),
                        Expanded(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: GestureDetector(
                              onTap: () {
                                showDialog(
                                  context: context,
                                  builder: (context) => Dialog(
                                    backgroundColor: Colors.transparent,
                                    insetPadding: const EdgeInsets.all(16),
                                    child: InteractiveViewer(
                                      child: Image.network(
                                        ApiClient.formatImageUrl(map['foto_eviden_2'].toString()) ?? '',
                                      ),
                                    ),
                                  ),
                                );
                              },
                              child: Container(
                                height: 200,
                                child: Image.network(
                                  ApiClient.formatImageUrl(map['foto_eviden_2'].toString()) ?? '',
                                              fit: BoxFit.cover,
                                  errorBuilder: (_, __, ___) => Container(
                                    width: double.infinity,
                                    height: 200,
                                    color: Colors.grey[200],
                                    child: const Center(child: Icon(Icons.broken_image, color: Colors.grey, size: 40)),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 24),
                ],
                
                const Text('Lokasi Titik Aset', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                const SizedBox(height: 12),
                
                // Minimap Placeholder
                InkWell(
                  onTap: () {
                    Navigator.pop(context);
                    _openMaps(context, lat, lng);
                  },
                  borderRadius: BorderRadius.circular(16),
                    child: _buildMiniMapPlaceholder(lat, lng),
                ),
                
                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      _openMaps(context, lat, lng);
                    },
                    icon: const Icon(Icons.map),
                    label: const Text('Buka di Aplikasi Google Maps'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      minimumSize: const Size(double.infinity, 56),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
              ],
            ),
          ),
        );
      },
    );
  }

  void _showDetailBottomSheet(BuildContext context, QueuedLog log, bool isLatest) {
    final map = log.payloadFields;
    final lat = (map['latitude'] as num?)?.toDouble() ?? 0.0;
    final lng = (map['longitude'] as num?)?.toDouble() ?? 0.0;
    final isValve = log.endpoint.contains('/log-valve');
    
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          margin: const EdgeInsets.all(16),
          padding: const EdgeInsets.all(24),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(24),
            boxShadow: const [BoxShadow(color: Colors.black26, blurRadius: 20, offset: Offset(0, 10))],
          ),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 48, height: 6,
                    decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(3)),
                  ),
                ),
                const SizedBox(height: 24),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(log.idempotencyKey == 'db_mock' ? 'Detail Tekanan Daerah' : 'Detail Laporan', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.primaryDark)),
                    if (log.idempotencyKey != 'db_mock')
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: (log.status == 'success' ? AppColors.statusNormal : (log.status == 'failed' ? AppColors.statusKritis : Colors.orange)).withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              log.status == 'success' ? Icons.check_circle : (log.status == 'failed' ? Icons.error : Icons.schedule),
                              size: 14,
                              color: (log.status == 'success' ? AppColors.statusNormal : (log.status == 'failed' ? AppColors.statusKritis : Colors.orange)),
                            ),
                            const SizedBox(width: 4),
                            Text(log.status.toUpperCase(), style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: (log.status == 'success' ? AppColors.statusNormal : (log.status == 'failed' ? AppColors.statusKritis : Colors.orange)))),
                          ],
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: 24),
                
                // Data Grid
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.background,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.cardBorder),
                  ),
                  child: Column(
                    children: [
                      if (!isValve && map['no_sr'] != null && map['no_sr'].toString().isNotEmpty)
                        _buildDetailRow('No. SR', map['no_sr'].toString()),
                      if (!isValve && map['nama_pelanggan'] != null && map['nama_pelanggan'].toString().isNotEmpty)
                        _buildDetailRow('Nama Pelanggan', map['nama_pelanggan'].toString()),
                      if (!isValve && map['alamat'] != null && map['alamat'].toString().isNotEmpty)
                        _buildDetailRow('Alamat', map['alamat'].toString()),
                      if (isValve)
                        _buildDetailRow('Aset / Valve', map['nama_aset'] ?? '-')
                      else if (map['no_sr'] == null || map['no_sr'].toString().isEmpty)
                        _buildDetailRow('Lokasi Daerah', map['nama_lokasi'] ?? '-'),
                      if (!isValve && map['desa'] != null && map['desa'].toString().isNotEmpty)
                        _buildDetailRow('Desa', map['desa'].toString()),
                      if (isValve) _buildDetailRow('Lokasi', map['nama_lokasi'] ?? '-'),
                      _buildDetailRow('Petugas', map['nama_teknisi'] ?? '-'),
                      
                      if (isValve) ...[
                        _buildDetailRow('Aksi', map['aksi_kerja']?.toString().toUpperCase() ?? '-'),
                        _buildDetailRow('Putaran', '${_formatNumber((map['jumlah_putaran'] as num?)?.toDouble() ?? 0.0)} Putaran'),
                      ] else ...[
                        _buildDetailRow('Tekanan', '${map['nilai_tekanan'] ?? 0.0} Bar'),
                        _buildDetailRow('Aliran', map['status_aliran']?.toString().toUpperCase() ?? '-'),
                        if (map['kekeruhan'] != null) _buildDetailRow('Kekeruhan', map['kekeruhan']!),
                      ],
                      
                      _buildDetailRow('Keterangan', map['keterangan'] ?? '-'),
                    ],
                  ),
                ),
                
                const SizedBox(height: 24),
                
                if (log.fotoPath.isNotEmpty && File(log.fotoPath).existsSync()) ...[
                  const Text('Foto Bukti', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: GestureDetector(
                            onTap: () {
                              showDialog(
                                context: context,
                                builder: (context) => Dialog(
                                  backgroundColor: Colors.transparent,
                                  insetPadding: const EdgeInsets.all(16),
                                  child: InteractiveViewer(
                                    child: Image.file(File(log.fotoPath)),
                                  ),
                                ),
                              );
                            },
                            child: Container(
                              constraints: const BoxConstraints(maxHeight: 350),
                              child: Image.file(
                                File(log.fotoPath),
                                fit: BoxFit.contain,
                              ),
                            ),
                          ),
                        ),
                      ),
                      if (log.fotoPath2 != null && log.fotoPath2!.isNotEmpty && File(log.fotoPath2!).existsSync()) ...[
                        const SizedBox(width: 8),
                        Expanded(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: GestureDetector(
                            onTap: () {
                              showDialog(
                                context: context,
                                builder: (context) => Dialog(
                                  backgroundColor: Colors.transparent,
                                  insetPadding: const EdgeInsets.all(16),
                                  child: InteractiveViewer(
                                    child: Image.file(File(log.fotoPath2!)),
                                  ),
                                ),
                              );
                            },
                            child: Container(
                              constraints: const BoxConstraints(maxHeight: 350),
                              child: Image.file(
                                File(log.fotoPath2!),
                                fit: BoxFit.contain,
                              ),
                            ),
                          ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 24),
                ] else if (map['foto_eviden'] != null && map['foto_eviden'].toString().isNotEmpty) ...[
                  const Text('Foto Bukti', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(
                        child: ClipRRect(
                          borderRadius: BorderRadius.circular(16),
                          child: GestureDetector(
                            onTap: () {
                              showDialog(
                                context: context,
                                builder: (context) => Dialog(
                                  backgroundColor: Colors.transparent,
                                  insetPadding: const EdgeInsets.all(16),
                                  child: InteractiveViewer(
                                    child: Image.network(ApiClient.formatImageUrl(map['foto_eviden'].toString()) ?? ''),
                                  ),
                                ),
                              );
                            },
                            child: Container(
                              constraints: const BoxConstraints(maxHeight: 350),
                              child: Image.network(
                                ApiClient.formatImageUrl(map['foto_eviden'].toString()) ?? '',
                                        fit: BoxFit.contain,
                                errorBuilder: (_, __, ___) => Container(
                                  width: double.infinity,
                                  height: 150,
                                  color: Colors.grey[200],
                                  child: const Center(child: Icon(Icons.broken_image, color: Colors.grey, size: 40)),
                                ),  
                              ),
                            ),
                          ),
                        ),
                      ),
                      if (map['foto_eviden_2'] != null && map['foto_eviden_2'].toString().isNotEmpty) ...[
                        const SizedBox(width: 8),
                        Expanded(
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(16),
                            child: GestureDetector(
                              onTap: () {
                                showDialog(
                                  context: context,
                                  builder: (context) => Dialog(
                                    backgroundColor: Colors.transparent,
                                    insetPadding: const EdgeInsets.all(16),
                                    child: InteractiveViewer(
                                      child: Image.network(ApiClient.formatImageUrl(map['foto_eviden_2'].toString()) ?? ''),
                                    ),
                                  ),
                                );
                              },
                              child: Container(
                                constraints: const BoxConstraints(maxHeight: 350),
                                child: Image.network(
                                  ApiClient.formatImageUrl(map['foto_eviden_2'].toString()) ?? '',
                                            fit: BoxFit.contain,
                                  errorBuilder: (_, __, ___) => Container(
                                    width: double.infinity,
                                    height: 150,
                                    color: Colors.grey[200],
                                    child: const Center(child: Icon(Icons.broken_image, color: Colors.grey, size: 40)),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 24),
                ],
                
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Informasi Laporan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                    if (map['is_edited'] == true)
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(color: Colors.blue.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(8)),
                        child: const Text('Telah Diedit', style: TextStyle(color: Colors.blue, fontSize: 12, fontWeight: FontWeight.bold, fontStyle: FontStyle.italic)),
                      ),
                  ],
                ),
                const SizedBox(height: 12),
                
                // Minimap Placeholder
                InkWell(
                  onTap: () {
                    Navigator.pop(context);
                    _openMaps(context, lat, lng);
                  },
                  borderRadius: BorderRadius.circular(16),
                  child: Container(
                    child: _buildMiniMapPlaceholder(lat, lng),
                    ),
                ),
                
                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: () {
                      Navigator.pop(context);
                      _openMaps(context, lat, lng);
                    },
                    icon: const Icon(Icons.map),
                    label: const Text('Buka di Aplikasi Google Maps'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.primary,
                      minimumSize: const Size(double.infinity, 56),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                    ),
                  ),
                ),
                if (isLatest && log.idempotencyKey != 'db_mock' && (log.status != 'success' || map['server_id'] != null)) ...[
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: OutlinedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        _navigateToEditForm(context, log);
                      },
                      icon: const Icon(Icons.edit, color: AppColors.primary),
                      label: const Text('Edit Laporan', style: TextStyle(color: AppColors.primary)),
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size(double.infinity, 56),
                        side: const BorderSide(color: AppColors.primary),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      ),
                    ),
                  ),
                ],
                const SizedBox(height: 16), // Extra bottom padding for safe area
              ],
            ),
          ),
        );
      },
    );
  }

  void _navigateToEditForm(BuildContext context, QueuedLog log) {
    // Check if it's valve or tekanan
    final isValve = log.endpoint.contains('/log-valve');
    
    if (isValve) {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => LogValveFormPage(editLog: log),
        ),
      );
    } else {
      Navigator.push(
        context,
        MaterialPageRoute(
          builder: (context) => LogTekananFormPage(editLog: log),
        ),
      );
    }
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 100, child: Text(label, style: const TextStyle(color: AppColors.textSecondary))),
          const Text(': '),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w500))),
        ],
      ),
    );
  }

  Future<void> _openMaps(BuildContext context, double lat, double lng) async {
    final url = Uri.parse('https://www.google.com/maps/search/?api=1&query=$lat,$lng');
    try {
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(url); // Try default launch
      }
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak dapat membuka aplikasi Google Maps')),
      );
    }
  }

  String _formatNumber(double value) {
    int intPart = value.floor();
    double fracPart = value - intPart;
    if (fracPart < 0.001) {
      return intPart.toString();
    }
    
    int eights = (fracPart * 8).round();
    if (eights == 8) return (intPart + 1).toString();
    if (eights == 0) return intPart.toString();
    
    int numerator = eights;
    int denominator = 8;
    while (numerator % 2 == 0 && denominator % 2 == 0) {
      numerator = numerator ~/ 2;
      denominator = denominator ~/ 2;
    }
    
    if (intPart == 0) return '$numerator/$denominator';
    return '$intPart $numerator/$denominator';
  }

  Widget _buildMiniMapPlaceholder(double lat, double lng, {double height = 120}) {
    if (lat == 0.0 && lng == 0.0) {
      return Container(
        height: height,
        width: double.infinity,
        decoration: BoxDecoration(
          color: Colors.blue.withValues(alpha: 0.1),
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: Colors.blue.withValues(alpha: 0.3)),
        ),
        child: const Center(child: Text('Koordinat tidak tersedia', style: TextStyle(color: Colors.blue))),
      );
    }
    
    final center = LatLng(lat, lng);
    return Container(
      height: height,
      width: double.infinity,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.blue.withValues(alpha: 0.3)),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: IgnorePointer(
          ignoring: true, // Prevent scroll conflicts
          child: FlutterMap(
            options: MapOptions(
              initialCenter: center,
              initialZoom: 15.0,
            ),
            children: [
              TileLayer(
                urlTemplate: 'https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
                userAgentPackageName: 'com.pdam.mobile',
              ),
              MarkerLayer(
                markers: [
                  Marker(
                    point: center,
                    width: 40,
                    height: 40,
                    child: const Icon(Icons.location_on, color: Colors.red, size: 30),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
