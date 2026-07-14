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
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart';
import '../../../core/models/queued_log.dart';

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
    
    final recentLogs = ref.read(syncControllerProvider.notifier).getAllLogs().reversed.take(3).toList();

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

              
              const Breadcrumb(title: 'Dashboard Utama'),
              const SizedBox(height: 16),

              // Status Sinkronisasi & Upload Card
              Text('Status Sinkronisasi', style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
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
                      mainAxisAlignment: MainAxisAlignment.spaceAround,
                      children: [
                        _buildUploadStat('Pending', syncState.pendingCount.toString(), Colors.orange),
                        _buildUploadStat('Gagal', syncState.failedCount.toString(), AppColors.statusKritis),
                        _buildUploadStat('Sukses', syncState.successCount.toString(), AppColors.statusNormal),
                        _buildUploadStat('Total', totalLog.toString(), AppColors.primary),
                      ],
                    ),
                    const SizedBox(height: 20),
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
                          minimumSize: const Size.fromHeight(48),
                        ),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Text(
                      syncState.lastSyncTime != null
                          ? 'Terakhir sync: ${dateFormat.format(syncState.lastSyncTime!)}'
                          : 'Belum ada data disinkronkan',
                      style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 24),
              
              // Mini Map
              Consumer(
                builder: (context, ref, child) {
                  final asetAsync = ref.watch(asetValveListProvider);
                  return _buildMiniMap(gpsState, asetAsync);
                },
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

              // Aktivitas Terakhir
              Text('Aktivitas Terakhir', style: theme.textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold)),
              const SizedBox(height: 12),
              
              if (recentLogs.isEmpty)
                const Center(child: Text('Belum ada aktivitas', style: TextStyle(color: AppColors.textHint)))
              else
                ...recentLogs.map((log) {
                  final isValve = log.endpoint.contains('/log-valve');
                  final title = isValve 
                      ? 'Cek Valve ${log.payloadFields['nama_lokasi'] ?? ''}'
                      : 'Ukur Tekanan ${log.payloadFields['nama_lokasi'] ?? ''}';
                  
                  String subtitle = '';
                  Color iconColor = AppColors.primary;
                  if (isValve) {
                    final aksi = log.payloadFields['aksi_kerja']?.toString() ?? 'Buka';
                    final putaran = log.payloadFields['jumlah_putaran']?.toString() ?? '0';
                    subtitle = '$aksi ($putaran putaran)';
                    iconColor = aksi.toLowerCase() == 'tutup' ? AppColors.statusKritis : AppColors.primary;
                  } else {
                    final tekanan = log.payloadFields['nilai_tekanan']?.toString() ?? '0';
                    final status = log.payloadFields['status']?.toString() ?? '';
                    subtitle = '$tekanan Bar ($status)';
                    iconColor = AppColors.accentGreen;
                  }
                  
                  // Extract time
                  String time = '';
                  if (log.payloadFields['waktu_pengecekan'] != null) {
                    try {
                      final dt = DateTime.parse(log.payloadFields['waktu_pengecekan']).toLocal();
                      time = DateFormat('HH:mm').format(dt);
                    } catch (_) {}
                  } else if (log.idempotencyKey.isNotEmpty) {
                      final parts = log.idempotencyKey.split('_');
                      if (parts.length > 1) {
                         try {
                           final dt = DateTime.fromMillisecondsSinceEpoch(int.parse(parts.last));
                           time = DateFormat('HH:mm').format(dt);
                         } catch(_) {}
                      }
                  }
                  
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8.0),
                    child: _buildActivityItem(title, subtitle, time, iconColor),
                  );
                }),
              
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
  Widget _buildUploadStat(String label, String value, Color color) {
    return Column(
      children: [
        Text(value, style: TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: color)),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
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

  Widget _buildMiniMap(GpsState gpsState, AsyncValue<List<AsetValve>> asetAsync) {
    LatLng center = const LatLng(-6.8909, 109.4390); // default
    if (gpsState is GpsSuccess) {
      center = LatLng(gpsState.latitude, gpsState.longitude);
    }
    
    CameraFit? cameraFit;
    final points = <LatLng>[];
    if (gpsState is GpsSuccess) {
      points.add(center);
    }
    
    final list = asetAsync.value;
    if (list != null) {
      for (final aset in list) {
        if (aset.latitude != null && aset.longitude != null) {
          points.add(LatLng(aset.latitude!, aset.longitude!));
        }
      }
    }

    if (points.isNotEmpty) {
      cameraFit = CameraFit.bounds(
        bounds: LatLngBounds.fromPoints(points),
        padding: const EdgeInsets.all(48.0),
      );
    }

    return Container(
      height: 320,
      width: double.infinity,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: AppColors.cardBorder),
        color: AppColors.surface,
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(16),
        child: Stack(
          children: [
            FlutterMap(
              key: ValueKey('map_${points.length}'), // Paksa rebuild saat jumlah poin berubah agar auto-fit jalan
              options: MapOptions(
                initialCenter: center,
                initialZoom: 14.0,
                initialCameraFit: cameraFit,
                interactionOptions: const InteractionOptions(
                  flags: InteractiveFlag.all & ~InteractiveFlag.rotate,
                ),
              ),
              children: [
                TileLayer(
                  urlTemplate: 'https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}',
                  userAgentPackageName: 'com.pdam.mobile',
                ),
                MarkerLayer(
                  markers: [
                    // Marker untuk lokasi user (GPS)
                    if (gpsState is GpsSuccess)
                      Marker(
                        point: center,
                        width: 40,
                        height: 40,
                        child: const Icon(Icons.my_location, color: Colors.blue, size: 30),
                      ),
                    
                    // Marker dari Aset Valve
                    ...asetAsync.maybeWhen(
                      data: (list) => list.where((a) => a.latitude != null && a.longitude != null).map(
                        (aset) => Marker(
                          point: LatLng(aset.latitude!, aset.longitude!),
                          width: 40,
                          height: 40,
                          child: const Icon(Icons.location_on, color: AppColors.accentGreen, size: 30),
                        ),
                      ).toList(),
                      orElse: () => [],
                    ),
                  ],
                ),
              ],
            ),
            
            // Overlay untuk memberi tahu bahwa ini Mini Map
            Positioned(
              top: 12,
              left: 12,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.black.withValues(alpha: 0.7),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.map, color: Colors.white, size: 14),
                    SizedBox(width: 6),
                    Text('Peta Lokasi Aset', style: TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.bold)),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
