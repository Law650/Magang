import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../../core/models/queued_log.dart';
import '../../../core/services/sync_controller.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/ui_components.dart';
import '../../../core/widgets/searchable_bottom_sheet.dart';
import '../../log_tekanan/data/rekap_tekanan_provider.dart';
import '../../log_tekanan/data/lokasi_repository.dart';
import '../../log_valve/data/aset_repository.dart';
class RiwayatPage extends ConsumerStatefulWidget {
  const RiwayatPage({super.key});

  @override
  ConsumerState<RiwayatPage> createState() => _RiwayatPageState();
}

class _RiwayatPageState extends ConsumerState<RiwayatPage> {
  Lokasi? _selectedLokasi;
  AsetValve? _selectedAset;

  @override
  Widget build(BuildContext context) {
    final syncState = ref.watch(syncControllerProvider);
    final syncController = ref.read(syncControllerProvider.notifier);
    final asetAsync = ref.watch(asetValveListProvider);
    final lokasiAsync = ref.watch(lokasiListProvider);
    final rekapTekananAsync = ref.watch(rekapTekananProvider);
    
    // Semua log (valve dan tekanan)
    final allLogs = syncController.getAllLogs();
    
    // Filter berdasarkan lokasi jika dipilih
    final filteredByLokasi = _selectedLokasi == null 
        ? allLogs 
        : allLogs.where((log) {
            final pid = log.payloadFields['lokasi_id'];
            if (pid != null && pid == _selectedLokasi!.id) return true;
            
            final pNama = log.payloadFields['nama_lokasi'];
            if (pNama != null && pNama == _selectedLokasi!.namaLokasi) return true;
            
            return false;
          }).toList();

    // Filter berdasarkan aset jika dipilih (untuk list tabel riwayat)
    final filteredLogs = _selectedAset == null 
        ? filteredByLokasi 
        : filteredByLokasi.where((log) => log.payloadFields['aset_id'] == _selectedAset!.id).toList();

    // Data untuk kartu status langsung (HANYA log valve dari antrean offline)
    final valveLogs = filteredLogs.where((log) => log.endpoint.contains('/log-valve')).toList();
    QueuedLog? latestLog = valveLogs.isNotEmpty ? valveLogs.first : null;
    
    // Data rekap tekanan dari server untuk lokasi yang dipilih
    RekapTekanan? selectedRekap;
    if (_selectedLokasi != null && _selectedAset == null) {
      final rekapList = rekapTekananAsync.valueOrNull ?? [];
      selectedRekap = rekapList.where((r) => r.id == _selectedLokasi!.id).firstOrNull;
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
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
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
                    Text('Lokasi', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textSecondary, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppColors.cardBorder),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: lokasiAsync.when(
                        data: (lokasiList) => InkWell(
                          onTap: () async {
                            final selected = await SearchableBottomSheet.show<Lokasi>(
                              context: context,
                              title: 'Filter Lokasi',
                              items: lokasiList,
                              itemAsString: (l) => l.namaLokasi,
                            );
                            if (selected != null) {
                              setState(() {
                                _selectedLokasi = selected;
                                // Reset aset jika lokasi berubah
                                _selectedAset = null;
                              });
                            }
                          },
                          child: Container(
                            height: 48,
                            alignment: Alignment.centerLeft,
                            child: Row(
                              children: [
                                Expanded(
                                  child: Text(
                                    _selectedLokasi?.namaLokasi ?? 'Semua Lokasi',
                                    style: theme.textTheme.bodyLarge?.copyWith(
                                      color: _selectedLokasi != null ? AppColors.textPrimary : AppColors.textHint,
                                    ),
                                  ),
                                ),
                                const Icon(Icons.arrow_drop_down, color: AppColors.textHint),
                              ],
                            ),
                          ),
                        ),
                        loading: () => const Center(child: CircularProgressIndicator()),
                        error: (_, __) => const Text('Gagal memuat'),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text('Aset / Valve', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textSecondary, fontWeight: FontWeight.w600)),
                    const SizedBox(height: 4),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppColors.cardBorder),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: asetAsync.when(
                        data: (asetList) {
                          final filteredList = asetList
                              .where((aset) => _selectedLokasi == null || aset.namaLokasi == _selectedLokasi!.namaLokasi)
                              .toList();
                          return InkWell(
                            onTap: () async {
                              final selected = await SearchableBottomSheet.show<AsetValve>(
                                context: context,
                                title: 'Filter Valve',
                                items: filteredList,
                                itemAsString: (a) => a.namaAset,
                              );
                              if (selected != null) {
                                setState(() => _selectedAset = selected);
                              }
                            },
                            child: Container(
                              height: 48,
                              alignment: Alignment.centerLeft,
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      _selectedAset?.namaAset ?? 'Semua Valve',
                                      style: theme.textTheme.bodyLarge?.copyWith(
                                        color: _selectedAset != null ? AppColors.textPrimary : AppColors.textHint,
                                      ),
                                    ),
                                  ),
                                  const Icon(Icons.arrow_drop_down, color: AppColors.textHint),
                                ],
                              ),
                            ),
                          );
                        },
                        loading: () => const Center(child: CircularProgressIndicator()),
                        error: (_, __) => const Text('Gagal memuat'),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),

              // Kartu Status Langsung
              if (_selectedAset != null && _selectedAset!.sisaBukaan != null)
                _buildKartuStatusLangsungAset(context, _selectedAset!, theme)
              else if (_selectedLokasi != null && selectedRekap != null)
                _buildKartuStatusLangsungTekanan(context, selectedRekap, theme)
              else if (latestLog != null)
                _buildKartuStatusLangsung(context, latestLog, theme)
              else
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: AppColors.primaryDark,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: const Center(
                    child: Text('Tidak ada data yang sesuai filter.', style: TextStyle(color: Colors.white70)),
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

              // List of logs
              Container(
                decoration: const BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.vertical(bottom: Radius.circular(16)),
                  border: Border(
                    left: BorderSide(color: AppColors.cardBorder),
                    right: BorderSide(color: AppColors.cardBorder),
                    bottom: BorderSide(color: AppColors.cardBorder),
                  ),
                ),
                child: filteredLogs.isEmpty
                    ? Padding(
                        padding: const EdgeInsets.all(32),
                        child: Center(
                          child: Text('Belum ada riwayat', style: TextStyle(color: AppColors.textHint)),
                        ),
                      )
                    : ListView.separated(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: filteredLogs.length,
                        separatorBuilder: (context, index) => const Divider(height: 1),
                        itemBuilder: (context, index) => _buildHistoryItem(context, filteredLogs[index], filteredLogs.length - index),
                      ),
              ),
            ],
          ),
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
    
    final percentBuka = aset.persentaseBukaan ?? (kapasitasFull > 0 ? (sisaBukaan / kapasitasFull) : 0.0);
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
            'latitude': aset.latitude ?? 0.0,
            'longitude': aset.longitude ?? 0.0,
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
                Text('Kartu Status Langsung (DB)', style: theme.textTheme.titleMedium?.copyWith(color: Colors.white)),
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
          
          const SizedBox(height: 20),
          Row(
            children: [
              Expanded(child: _buildValueCard('Kapasitas Full', kapasitasFull, const Color(0xFF2B5C6C))),
              const SizedBox(width: 8),
              Expanded(child: _buildValueCard('Total Tutupan', totalTutupan, const Color(0xFF5D3E4B))),
              const SizedBox(width: 8),
              Expanded(child: _buildValueCard('Sisa Bukaan', sisaBukaan, const Color(0xFF1B6A5C))),
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
            'nama_teknisi': rekap.namaTeknisi ?? 'Sistem',
          },
        );
        // Kita bisa menggunakan _showDetailBottomSheet karena itu umum
        _showDetailBottomSheet(context, dummyLog);
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
                Text('Status Tekanan Daerah', style: theme.textTheme.titleMedium?.copyWith(color: Colors.white)),
                const Spacer(),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(color: statusColor.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
                  child: Row(
                    children: [
                      Icon(statusIcon, color: statusColor, size: 10),
                      const SizedBox(width: 4),
                      Text(rekap.status?.toUpperCase() ?? 'BELUM ADA', style: TextStyle(color: statusColor, fontSize: 10, fontWeight: FontWeight.bold)),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),
          Text('Nama Lokasi / Jalur', style: theme.textTheme.bodySmall?.copyWith(color: Colors.white70)),
          Text(rekap.namaLokasi, style: theme.textTheme.titleLarge?.copyWith(color: Colors.white, fontWeight: FontWeight.bold)),
          
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
                Text('Teknisi: ${rekap.namaTeknisi}', style: const TextStyle(color: Colors.white70, fontSize: 12)),
              ],
            ),
          ],
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
            
            const SizedBox(height: 20),
            Row(
              children: [
                Expanded(child: _buildValueCard('Kapasitas Full', kapasitasFull, const Color(0xFF2B5C6C))),
                const SizedBox(width: 8),
                Expanded(child: _buildValueCard('Total Tutupan', totalTutupan, const Color(0xFF5D3E4B))),
                const SizedBox(width: 8),
                Expanded(child: _buildValueCard('Sisa Bukaan', sisaBukaan, const Color(0xFF1B6A5C))),
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
        children: [
          Text(title, style: const TextStyle(fontSize: 10, color: Colors.white70)),
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

  Widget _buildHistoryItem(BuildContext context, QueuedLog log, int indexNumber) {
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
    final aksiKerja = aksiKerjaRaw == 'tutup' ? 'Tutup' : 'Buka';
    final jumlahPutaran = (map['jumlah_putaran'] as num?)?.toDouble() ?? 0.0;
    final kapasitasFull = (map['kapasitas_full'] as num?)?.toDouble() ?? 58.0;
    final bukaanAwal = (map['bukaan_saat_ini'] as num?)?.toDouble() ?? kapasitasFull;
    
    double sisa = aksiKerja == 'Tutup' 
        ? (bukaanAwal - jumlahPutaran) 
        : (bukaanAwal + jumlahPutaran);
    sisa = sisa.clamp(0.0, kapasitasFull);
    
    // Detail untuk Tekanan
    final tekanan = (map['tekanan'] as num?)?.toDouble() ?? 0.0;
    final aliran = map['aliran_air'] ?? 'Normal';

    return InkWell(
      onTap: () => _showDetailBottomSheet(context, log),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('#$indexNumber', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint)),
            const SizedBox(width: 12),
            // Avatar
            CircleAvatar(
              backgroundColor: isValve ? (aksiKerja == 'Tutup' ? AppColors.statusKritis : Colors.blue) : AppColors.accentGreen,
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
                  Text(dateFormat.format(log.createdAt), style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint)),
                  const SizedBox(height: 8),
                  if (isValve)
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: (aksiKerja == 'Tutup' ? AppColors.statusKritis : AppColors.statusNormal).withValues(alpha: 0.1),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            '+$aksiKerja',
                            style: TextStyle(
                              color: aksiKerja == 'Tutup' ? AppColors.statusKritis : AppColors.statusNormal, 
                              fontWeight: FontWeight.bold, fontSize: 12
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        Text('+${_formatNumber(jumlahPutaran)} Put.', style: theme.textTheme.bodyMedium?.copyWith(fontSize: 13, color: AppColors.textSecondary)),
                        const SizedBox(width: 12),
                        Text('Sisa: ${_formatNumber(sisa)} Put.', style: theme.textTheme.bodyMedium?.copyWith(fontSize: 13, fontWeight: FontWeight.bold)),
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
                      _buildDetailRow('Lokasi', namaLokasi),
                      _buildDetailRow('Kapasitas Full', '${_formatNumber(kapasitasFull)} Putaran'),
                      _buildDetailRow('Jenis Valve', 'Gate Valve (Besi Cor)'),
                      _buildDetailRow('Diameter', '150 mm'),
                      _buildDetailRow('Tahun Pasang', '2015'),
                      _buildDetailRow('Kondisi Fisik', 'Beroperasi Normal'),
                    ],
                  ),
                ),
                
                const SizedBox(height: 24),
                
                if (log.fotoPath.isNotEmpty && File(log.fotoPath).existsSync()) ...[
                  const Text('Foto Bukti', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                  const SizedBox(height: 12),
                  ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      width: double.infinity,
                      constraints: const BoxConstraints(maxHeight: 350),
                      child: Image.file(
                        File(log.fotoPath),
                        fit: BoxFit.contain,
                      ),
                    ),
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
                  child: Container(
                    height: 120,
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: Colors.blue.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.blue.withValues(alpha: 0.3)),
                      image: DecorationImage(
                        image: NetworkImage(
                          lat != 0.0 && lng != 0.0 
                          ? 'https://maps.googleapis.com/maps/api/staticmap?center=$lat,$lng&zoom=15&size=400x200&maptype=roadmap&markers=color:red%7C$lat,$lng'
                          : 'https://maps.googleapis.com/maps/api/staticmap?center=-7.983908,112.621391&zoom=15&size=400x200&maptype=roadmap',
                        ),
                        fit: BoxFit.cover,
                        opacity: 0.5,
                      ),
                    ),
                    child: Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.location_on, color: Colors.red, size: 40),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4)],
                            ),
                            child: Text('$lat, $lng', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                          ),
                        ],
                      ),
                    ),
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
                const SizedBox(height: 16),
              ],
            ),
          ),
        );
      },
    );
  }

  void _showDetailBottomSheet(BuildContext context, QueuedLog log) {
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
                    const Text('Detail Laporan', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.primaryDark)),
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
                      _buildDetailRow(isValve ? 'Aset / Valve' : 'Lokasi Daerah', map[isValve ? 'nama_aset' : 'nama_lokasi'] ?? '-'),
                      if (isValve) _buildDetailRow('Lokasi', map['nama_lokasi'] ?? '-'),
                      _buildDetailRow('Teknisi', map['nama_teknisi'] ?? '-'),
                      
                      if (isValve) ...[
                        _buildDetailRow('Aksi', map['aksi_kerja']?.toString().toUpperCase() ?? '-'),
                        _buildDetailRow('Putaran', '${_formatNumber((map['jumlah_putaran'] as num?)?.toDouble() ?? 0.0)} Putaran'),
                      ] else ...[
                        _buildDetailRow('Tekanan', '${map['tekanan']} Bar'),
                        _buildDetailRow('Aliran', map['aliran_air'] ?? '-'),
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
                  ClipRRect(
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      width: double.infinity,
                      constraints: const BoxConstraints(maxHeight: 350),
                      child: Image.file(
                        File(log.fotoPath),
                        fit: BoxFit.contain,
                      ),
                    ),
                  ),
                  const SizedBox(height: 24),
                ],
                
                const Text('Lokasi Koordinat', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                const SizedBox(height: 12),
                
                // Minimap Placeholder
                InkWell(
                  onTap: () {
                    Navigator.pop(context);
                    _openMaps(context, lat, lng);
                  },
                  borderRadius: BorderRadius.circular(16),
                  child: Container(
                    height: 120,
                    width: double.infinity,
                    decoration: BoxDecoration(
                      color: Colors.blue.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: Colors.blue.withValues(alpha: 0.3)),
                      image: DecorationImage(
                        image: NetworkImage(
                          lat != 0.0 && lng != 0.0 
                          ? 'https://maps.googleapis.com/maps/api/staticmap?center=$lat,$lng&zoom=15&size=400x200&maptype=roadmap&markers=color:red%7C$lat,$lng'
                          : 'https://maps.googleapis.com/maps/api/staticmap?center=-7.983908,112.621391&zoom=15&size=400x200&maptype=roadmap',
                        ),
                        fit: BoxFit.cover,
                        opacity: 0.5,
                      ),
                    ),
                    child: Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(Icons.location_on, color: Colors.red, size: 40),
                          const SizedBox(height: 4),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4)],
                            ),
                            child: Text('$lat, $lng', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                          ),
                        ],
                      ),
                    ),
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
                const SizedBox(height: 16), // Extra bottom padding for safe area
              ],
            ),
          ),
        );
      },
    );
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
    final url = Uri.parse('https://maps.google.com/?q=$lat,$lng');
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
    return value == value.toInt() ? value.toInt().toString() : value.toString();
  }
}
