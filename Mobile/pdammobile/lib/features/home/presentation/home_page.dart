import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:connectivity_plus/connectivity_plus.dart';

import '../../../core/theme/app_colors.dart';
import '../../../core/providers/technician_provider.dart';
import '../../../core/services/sync_controller.dart';
import '../../../core/services/gps_service.dart';
import '../../../core/widgets/ui_components.dart';
import '../../identity/presentation/login_page.dart';
import '../../log_valve/data/aset_repository.dart';

final connectivityProvider = StreamProvider<List<ConnectivityResult>>((ref) {
  return Connectivity().onConnectivityChanged;
});

class HomePage extends ConsumerWidget {
  const HomePage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final technicianName = ref.watch(technicianNameProvider) ?? 'Teknisi';
    final syncState = ref.watch(syncControllerProvider);
    final gpsState = ref.watch(gpsServiceProvider);
    final connectivity = ref.watch(connectivityProvider);
    
    final isOnline = connectivity.value != null && 
        !connectivity.value!.contains(ConnectivityResult.none);
    
    final theme = Theme.of(context);
    final dateFormat = DateFormat('dd MMM yyyy, HH:mm', 'id');
    final totalLog = syncState.pendingCount + syncState.successCount + syncState.failedCount;
    final progress = totalLog == 0 ? 0.0 : syncState.successCount / totalLog;

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        elevation: 0,
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Halo, $technicianName',
              style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 20),
            ),
            Row(
              children: [
                Icon(
                  isOnline ? Icons.wifi : Icons.wifi_off,
                  color: isOnline ? Colors.white70 : AppColors.statusKritis,
                  size: 14,
                ),
                const SizedBox(width: 4),
                Text(
                  isOnline ? 'Terhubung (Mode Sinkronisasi)' : 'Offline (Mode Lokal)',
                  style: TextStyle(
                    color: isOnline ? Colors.white70 : AppColors.statusKritis,
                    fontSize: 12,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.notifications_none, color: Colors.white),
            onPressed: () {},
          ),
          IconButton(
            icon: const Icon(Icons.logout, color: Colors.white),
            onPressed: () async {
              await ref.read(technicianNameProvider.notifier).logout();
              if (context.mounted) {
                Navigator.of(context).pushAndRemoveUntil(
                  MaterialPageRoute(builder: (_) => const LoginPage()),
                  (route) => false,
                );
              }
            },
          ),
          const SizedBox(width: 8),
        ],
      ),
      body: RefreshIndicator(
        onRefresh: () async => await ref.read(syncControllerProvider.notifier).forceSyncNow(),
        child: SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Search Bar mock
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16),
                height: 48,
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: AppColors.cardBorder),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.search, color: AppColors.textHint),
                    SizedBox(width: 8),
                    Text('Cari Menu atau Aset...', style: TextStyle(color: AppColors.textHint)),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              
              // Quick Actions
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                children: [
                  _buildQuickAction(Icons.warning_amber_rounded, 'Lapor\nKebocoran', AppColors.statusKritis),
                  _buildQuickAction(Icons.build_rounded, 'Lapor\nRusak', AppColors.primary),
                  _buildQuickAction(Icons.qr_code_scanner_rounded, 'Pindai\nAset', AppColors.primaryDark),
                  _buildQuickAction(Icons.map_rounded, 'Peta\nJaringan', AppColors.statusNormal),
                ],
              ),
              
              const SizedBox(height: 24),
              
              const Breadcrumb(title: 'Dashboard Utama'),
              const SizedBox(height: 16),
              
              Text('Status Pekerjaan', style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),

              // Gauge Dashboard Card
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: AppColors.surface,
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 10, offset: Offset(0, 4))],
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceEvenly,
                      children: [
                        _buildGauge(
                          title: 'Target Harian',
                          progress: 0.65, // Mock
                          color: AppColors.primary,
                        ),
                        _buildGauge(
                          title: 'Efisiensi',
                          progress: 0.90, // Mock
                          color: AppColors.statusNormal,
                        ),
                        _buildGauge(
                          title: 'Sinkronisasi',
                          progress: totalLog == 0 ? 1.0 : progress,
                          color: AppColors.accentGreen,
                        ),
                      ],
                    ),
                    const SizedBox(height: 24),
                    const Divider(height: 1),
                    const SizedBox(height: 16),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _buildStatText('Pending', syncState.pendingCount.toString()),
                        _buildStatText('Gagal', syncState.failedCount.toString()),
                        _buildStatText('Sukses', syncState.successCount.toString()),
                        _buildStatText('Total', totalLog.toString()),
                      ],
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 24),

              // Sync Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  onPressed: syncState.isSyncing || !isOnline
                      ? null
                      : () => ref.read(syncControllerProvider.notifier).forceSyncNow(),
                  icon: syncState.isSyncing
                      ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Icon(Icons.sync_rounded),
                  label: Text(syncState.isSyncing 
                      ? 'Menyinkronkan...' 
                      : (isOnline ? 'Sinkronkan Data Sekarang' : 'Koneksi Terputus (Offline)')),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.primary,
                    minimumSize: const Size.fromHeight(56),
                  ),
                ),
              ),

              const SizedBox(height: 16),
              Center(
                child: Text(
                  syncState.lastSyncTime != null
                      ? 'Terakhir sync: ${dateFormat.format(syncState.lastSyncTime!)}'
                      : 'Belum ada data disinkronkan',
                  style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint),
                ),
              ),

              const SizedBox(height: 24),
              
              // Aset Terdekat
              Text('Aset Terdekat', style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 4),
              Text('Berdasarkan posisi GPS Anda saat ini', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textSecondary)),
              const SizedBox(height: 12),
              Consumer(
                builder: (context, ref, child) {
                  final asetAsync = ref.watch(asetValveListProvider);
                  return asetAsync.when(
                    data: (asetList) => Column(
                      children: _buildNearbyAssets(gpsState, asetList),
                    ),
                    loading: () => const Center(child: CircularProgressIndicator()),
                    error: (_, __) => const SizedBox(),
                  );
                },
              ),
              const SizedBox(height: 24),

              // Aktivitas Terakhir Mock
              Text('Aktivitas Terakhir', style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              
              _buildActivityItem('Cek Valve Jl. Merdeka', 'Tutup (1.5 putaran)', '12:30', AppColors.primary),
              const SizedBox(height: 8),
              _buildActivityItem('Ukur Tekanan Perum. Asri', '0.8 Bar (Mengalir)', '10:15', AppColors.accentGreen),
              const SizedBox(height: 8),
              _buildActivityItem('Cek Valve Jl. Sudirman', 'Buka (2.0 putaran)', '09:00', AppColors.statusKritis),
              
              const SizedBox(height: 32),
            ],
          ),
        ),
      ),
    );
  }
  
  Widget _buildQuickAction(IconData icon, String label, Color color) {
    return Column(
      children: [
        Container(
          width: 56,
          height: 56,
          decoration: BoxDecoration(
            color: color.withValues(alpha: 0.1),
            shape: BoxShape.circle,
          ),
          child: Icon(icon, color: color, size: 28),
        ),
        const SizedBox(height: 8),
        Text(
          label,
          textAlign: TextAlign.center,
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.textPrimary),
        ),
      ],
    );
  }

  List<Widget> _buildNearbyAssets(GpsState gpsState, List<AsetValve> asetList) {
    if (gpsState is! GpsSuccess) {
      return [
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.cardBorder),
          ),
          child: const Row(
            children: [
              Icon(Icons.location_off, color: AppColors.textHint),
              SizedBox(width: 12),
              Expanded(
                child: Text('Aktifkan GPS untuk melihat aset terdekat.', style: TextStyle(color: AppColors.textSecondary)),
              ),
            ],
          ),
        )
      ];
    }
    
    // Hitung jarak (mock haversine)
    final userLat = gpsState.latitude;
    final userLng = gpsState.longitude;
    
    final List<Map<String, dynamic>> assetsWithDistance = [];
    for (var aset in asetList) {
      if (aset.latitude == null || aset.longitude == null) continue;
      // Haversine formula sederhana
      const p = 0.017453292519943295;
      final a = 0.5 - math.cos((aset.latitude! - userLat) * p)/2 + 
                math.cos(userLat * p) * math.cos(aset.latitude! * p) * 
                (1 - math.cos((aset.longitude! - userLng) * p))/2;
      final distanceKm = 12742 * math.asin(math.sqrt(a)); // 2 * R; R = 6371 km
      assetsWithDistance.add({
        'aset': aset,
        'distanceKm': distanceKm,
      });
    }
    
    assetsWithDistance.sort((a, b) => (a['distanceKm'] as double).compareTo(b['distanceKm'] as double));
    
    // Ambil 2 terdekat
    final nearby = assetsWithDistance.take(2).toList();
    
    return nearby.map((data) {
      final aset = data['aset'] as AsetValve;
      final dist = data['distanceKm'] as double;
      final distStr = dist < 1.0 ? '${(dist * 1000).toInt()} m' : '${dist.toStringAsFixed(1)} km';
      
      return Padding(
        padding: const EdgeInsets.only(bottom: 8.0),
        child: Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: AppColors.cardBorder),
          ),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: AppColors.statusNormal.withValues(alpha: 0.1), shape: BoxShape.circle),
                child: const Icon(Icons.settings_input_component, color: AppColors.statusNormal, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(aset.namaAset, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                    Text(aset.namaLokasi, style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
                  ],
                ),
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  const Icon(Icons.place, size: 14, color: AppColors.primary),
                  const SizedBox(height: 2),
                  Text(distStr, style: const TextStyle(color: AppColors.primary, fontWeight: FontWeight.bold, fontSize: 12)),
                ],
              ),
            ],
          ),
        ),
      );
    }).toList();
  }

  Widget _buildGauge({required String title, required double progress, required Color color}) {
    return Column(
      children: [
        SizedBox(
          width: 80,
          height: 80,
          child: Stack(
            fit: StackFit.expand,
            children: [
              CircularProgressIndicator(
                value: 1.0,
                strokeWidth: 8,
                color: color.withValues(alpha: 0.15),
              ),
              CircularProgressIndicator(
                value: progress,
                strokeWidth: 8,
                color: color,
                strokeCap: StrokeCap.round,
              ),
              Center(
                child: Text(
                  '${(progress * 100).toInt()}%',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: color),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 12),
        Text(title, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppColors.textSecondary)),
      ],
    );
  }

  Widget _buildStatText(String label, String value) {
    return Column(
      children: [
        Text(value, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: AppColors.textPrimary)),
        Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textHint)),
      ],
    );
  }

  Widget _buildActivityItem(String title, String subtitle, String time, Color iconColor) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.cardBorder),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(color: iconColor.withValues(alpha: 0.1), shape: BoxShape.circle),
            child: Icon(Icons.assignment, color: iconColor, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 14)),
                Text(subtitle, style: const TextStyle(color: AppColors.textSecondary, fontSize: 12)),
              ],
            ),
          ),
          Text(time, style: const TextStyle(color: AppColors.textHint, fontSize: 12)),
        ],
      ),
    );
  }
}
