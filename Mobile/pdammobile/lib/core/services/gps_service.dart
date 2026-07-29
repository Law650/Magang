import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:geolocator/geolocator.dart';
import 'package:permission_handler/permission_handler.dart';

/// State GPS yang merepresentasikan 3 kondisi: loading, success, error.
@immutable
sealed class GpsState {
  const GpsState();
}

class GpsLoading extends GpsState {
  const GpsLoading();
}

class GpsSuccess extends GpsState {
  final double latitude;
  final double longitude;

  const GpsSuccess({required this.latitude, required this.longitude});
}

class GpsError extends GpsState {
  final String message;

  const GpsError(this.message);
}

/// Provider GPS per-form — menggunakan `autoDispose` agar GPS state
/// ter-reset saat halaman form ditutup, dan `.family` tidak diperlukan
/// karena setiap form tab sudah punya instance sendiri via `IndexedStack`.
///
/// Sesuai PRD §4.5:
/// - Koordinat ditangkap SATU KALI per sesi form
/// - Dipakai ulang untuk watermark (PRD §4.5 — tidak ditangkap dua kali)
/// - Tombol "Simpan" wajib nonaktif selagi koordinat belum tersedia
final gpsServiceProvider =
    StateNotifierProvider.autoDispose<GpsServiceNotifier, GpsState>(
  (ref) => GpsServiceNotifier(),
);

class GpsServiceNotifier extends StateNotifier<GpsState> {
  StreamSubscription<Position>? _positionStreamSubscription;

  GpsServiceNotifier() : super(const GpsLoading());

  @override
  void dispose() {
    _positionStreamSubscription?.cancel();
    super.dispose();
  }

  /// Memulai proses penangkapan koordinat GPS.
  ///
  /// Alur sesuai Spesifikasi Teknis v1.2 §3.1:
  /// 1. Cek apakah layanan lokasi aktif
  /// 2. Cek/minta izin lokasi via permission_handler
  /// 3. Ambil posisi presisi tinggi via geolocator
  Future<void> captureLocation(BuildContext context) async {
    state = const GpsLoading();

    try {
      // 1. Cek layanan lokasi
      final serviceEnabled = await Geolocator.isLocationServiceEnabled();
      if (!serviceEnabled) {
        state = const GpsError(
          'Layanan lokasi tidak aktif. Aktifkan GPS di pengaturan perangkat.',
        );
        return;
      }

      // 2. Cek izin lokasi
      var permissionStatus = await Permission.locationWhenInUse.status;

      if (permissionStatus.isDenied) {
        // Tampilkan dialog rasional sebelum permintaan native (PRD §5.3)
        if (context.mounted) {
          final shouldRequest = await _showRationaleDialog(context);
          if (!shouldRequest) {
            state = const GpsError(
              'Izin lokasi diperlukan untuk mencatat koordinat GPS.',
            );
            return;
          }
        }

        permissionStatus = await Permission.locationWhenInUse.request();
      }

      if (permissionStatus.isPermanentlyDenied) {
        state = const GpsError(
          'Izin lokasi ditolak permanen. Buka Pengaturan untuk mengizinkan.',
        );
        return;
      }

      if (!permissionStatus.isGranted) {
        state = const GpsError(
          'Izin lokasi tidak diberikan. Koordinat GPS tidak dapat diambil.',
        );
        return;
      }

      // 3. Ambil posisi dengan menyaring akurasi terbaik (cocok untuk HP spesifikasi rendah)
      _positionStreamSubscription?.cancel();
      
      Position? bestPosition;
      final completer = Completer<Position?>();
      
      _positionStreamSubscription = Geolocator.getPositionStream(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.best,
          distanceFilter: 0,
        ),
      ).listen((Position position) {
        if (bestPosition == null || position.accuracy < bestPosition!.accuracy) {
          bestPosition = position;
        }

        // Target akurasi yang ideal (di bawah 20 meter), bisa langsung selesai
        if (bestPosition!.accuracy <= 20.0) {
          if (!completer.isCompleted) {
            completer.complete(bestPosition);
          }
        }
      }, onError: (e) {
        if (!completer.isCompleted) {
          completer.completeError(e);
        }
      });

      try {
        // Tunggu maksimal 15 detik
        await completer.future.timeout(const Duration(seconds: 15));
      } catch (e) {
        if (e is TimeoutException) {
          // Timeout terjadi, biarkan berlanjut untuk menggunakan bestPosition yang berhasil dikumpulkan
        } else {
          rethrow;
        }
      } finally {
        _positionStreamSubscription?.cancel();
      }

      if (bestPosition == null) {
        throw Exception("Gagal mendapatkan sinyal GPS. Pastikan Anda berada di luar ruangan.");
      } else if (bestPosition!.accuracy > 100.0) {
        throw Exception("Akurasi terlalu lemah (${bestPosition!.accuracy.toStringAsFixed(0)}m). Nyalakan GPS Akurasi Tinggi & cari area terbuka.");
      }

      if (mounted) {
        state = GpsSuccess(
          latitude: bestPosition!.latitude,
          longitude: bestPosition!.longitude,
        );
      }
    } catch (e) {
      state = GpsError(
        'Gagal mendapatkan lokasi: ${e.toString().length > 80 ? '${e.toString().substring(0, 80)}...' : e.toString()}',
      );
    }
  }

  /// Dialog rasional sebelum permintaan izin native (PRD §5.3, checklist #11).
  Future<bool> _showRationaleDialog(BuildContext context) async {
    final result = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (context) => AlertDialog(
        title: const Row(
          children: [
            Icon(Icons.location_on, color: Color(0xFF1565C0)),
            SizedBox(width: 10),
            Text('Izin Lokasi'),
          ],
        ),
        content: const Text(
          'Aplikasi memerlukan akses lokasi untuk mencatat koordinat GPS secara otomatis pada setiap laporan.\n\n'
          'Koordinat ini akan ditampilkan pada watermark foto bukti sebagai bukti kehadiran di lokasi kerja.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Nanti'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Izinkan'),
          ),
        ],
      ),
    );
    return result ?? false;
  }

  /// Membuka pengaturan aplikasi (untuk kasus izin ditolak permanen).
  Future<void> openSettings() async {
    await openAppSettings();
  }
}
