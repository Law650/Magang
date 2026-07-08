import 'package:flutter/foundation.dart';
import 'package:hive_ce_flutter/hive_flutter.dart';
import 'package:workmanager/workmanager.dart';

import 'package:shared_preferences/shared_preferences.dart';

import '../models/queued_log.dart';
import 'api_client.dart';
import 'sync_controller.dart';
import 'sync_repository.dart';

/// Nama task unik untuk Workmanager.
const String kBackgroundSyncTaskName = 'com.pdam.mobile.backgroundSync';

/// Nama task unik untuk periodic task.
const String kBackgroundSyncTaskTag = 'backgroundSyncPeriodic';

/// Callback dispatcher untuk Workmanager — WAJIB top-level function.
///
/// Dipanggil oleh OS saat background task di-trigger (setiap ~15 menit).
/// Membuka Hive box secara independen (karena berjalan di isolate terpisah),
/// lalu menjalankan sync FIFO untuk semua entry yang retryable.
@pragma('vm:entry-point')
void callbackDispatcher() {
  Workmanager().executeTask((taskName, inputData) async {
    debugPrint('[BackgroundSync] Task dimulai: $taskName');

    try {
      // Inisialisasi Hive di isolate background
      await Hive.initFlutter();

      // Register adapter jika belum (di isolate baru, adapter belum terdaftar)
      if (!Hive.isAdapterRegistered(0)) {
        Hive.registerAdapter(QueuedLogAdapter());
      }

      // Buka box
      final box = await Hive.openBox<QueuedLog>(kQueuedLogsBoxName);

      // Ambil entry yang retryable secara FIFO
      final retryableEntries = box.values
          .where((entry) => entry.isRetryable)
          .toList()
        ..sort((a, b) => a.createdAt.compareTo(b.createdAt));

      if (retryableEntries.isEmpty) {
        debugPrint('[BackgroundSync] Tidak ada entry untuk di-sync.');
        await box.close();
        return Future.value(true);
      }

      debugPrint(
        '[BackgroundSync] Memproses ${retryableEntries.length} entry...',
      );

      final prefs = await SharedPreferences.getInstance();
      final apiClient = ApiClient(prefs);
      final repository = SyncRepository(apiClient);
      for (final entry in retryableEntries) {
        await repository.syncOneEntry(entry);
      }

      await box.close();
      debugPrint('[BackgroundSync] Task selesai.');
      return Future.value(true);
    } catch (e) {
      debugPrint('[BackgroundSync] Error: $e');
      return Future.value(false); // Workmanager akan retry
    }
  });
}

/// Inisialisasi dan registrasi Workmanager untuk background sync.
///
/// Dipanggil sekali di `main.dart` setelah Hive diinisialisasi.
///
/// Setup:
/// - Periodic task setiap 15 menit (minimum yang diizinkan Android)
/// - Constraint: memerlukan koneksi jaringan
/// - Jika task gagal, Workmanager otomatis me-retry dengan backoff
///
/// **Catatan iOS**: Background fetch di iOS tidak menjamin interval tepat.
/// Apple mengatur frekuensi berdasarkan pola penggunaan user.
Future<void> initBackgroundSync() async {
  await Workmanager().initialize(
    callbackDispatcher,
  );

  await Workmanager().registerPeriodicTask(
    kBackgroundSyncTaskTag,
    kBackgroundSyncTaskName,
    frequency: const Duration(minutes: 15),
    constraints: Constraints(
      networkType: NetworkType.connected, // Hanya sync saat ada jaringan
    ),
    existingWorkPolicy: ExistingPeriodicWorkPolicy.keep, // Jangan duplikasi task
    backoffPolicy: BackoffPolicy.exponential,
    backoffPolicyDelay: const Duration(minutes: 1),
  );

  debugPrint('[BackgroundSync] Periodic task terdaftar (15 menit).');
}
