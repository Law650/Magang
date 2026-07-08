import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:hive_ce/hive.dart';

import '../models/queued_log.dart';
import '../providers/api_provider.dart';
import 'sync_repository.dart';

/// Nama Hive box untuk antrian log.
const String kQueuedLogsBoxName = 'queuedLogs';

/// State sinkronisasi yang di-expose ke UI.
class SyncState {
  final bool isSyncing;
  final int pendingCount;
  final int successCount;
  final int failedCount;
  final DateTime? lastSyncTime;
  final String? lastError;

  const SyncState({
    this.isSyncing = false,
    this.pendingCount = 0,
    this.successCount = 0,
    this.failedCount = 0,
    this.lastSyncTime,
    this.lastError,
  });

  SyncState copyWith({
    bool? isSyncing,
    int? pendingCount,
    int? successCount,
    int? failedCount,
    DateTime? lastSyncTime,
    String? lastError,
  }) {
    return SyncState(
      isSyncing: isSyncing ?? this.isSyncing,
      pendingCount: pendingCount ?? this.pendingCount,
      successCount: successCount ?? this.successCount,
      failedCount: failedCount ?? this.failedCount,
      lastSyncTime: lastSyncTime ?? this.lastSyncTime,
      lastError: lastError ?? this.lastError,
    );
  }
}

/// Controller utama sinkronisasi — Modul G (PRD §4.7).
///
/// Bertanggung jawab untuk:
/// - **Manual sync**: `forceSyncNow()` — dipanggil tombol "Sinkronkan Data"
/// - **Auto sync foreground**: `Timer.periodic` 60 detik (hanya saat app resumed)
/// - **Menghitung** jumlah pending/success/failed dari Hive box
///
/// Timer lifecycle:
/// - `startAutoSync()` dipanggil saat `AppLifecycleState.resumed`
/// - `stopAutoSync()` dipanggil saat `paused` / `inactive`
/// - Ini ditangani oleh [MainLayout] via `WidgetsBindingObserver`
class SyncController extends StateNotifier<SyncState> {
  final Box<QueuedLog> _box;
  final SyncRepository _repository;
  Timer? _autoSyncTimer;
  bool _isSyncRunning = false; // Guard terhadap concurrent sync

  SyncController({
    required Box<QueuedLog> box,
    required SyncRepository repository,
  })  : _box = box,
        _repository = repository,
        super(const SyncState()) {
    // Hitung state awal dari Hive box
    _refreshCounts();
  }

  /// Sinkronisasi manual — kirim semua entry FIFO yang berstatus
  /// `pending` atau `failed` (dengan retryCount < max).
  ///
  /// Dipanggil oleh:
  /// - Tombol "Sinkronkan Data" di Home
  /// - Timer auto-sync foreground
  /// - Form submit (setelah menyimpan entry baru)
  Future<void> forceSyncNow() async {
    // Guard: jangan jalankan sync bersamaan
    if (_isSyncRunning) {
      debugPrint('[SyncController] Sync sudah berjalan, skip.');
      return;
    }

    // Cek konektivitas terlebih dahulu
    final connectivityResult = await Connectivity().checkConnectivity();
    if (connectivityResult.contains(ConnectivityResult.none)) {
      debugPrint('[SyncController] Tidak ada koneksi, skip sync.');
      state = state.copyWith(lastError: 'Tidak ada koneksi internet');
      return;
    }

    _isSyncRunning = true;
    state = state.copyWith(isSyncing: true, lastError: null);

    try {
      // Ambil semua entry yang layak di-sync, urut FIFO (createdAt ascending)
      final retryableEntries = _box.values
          .where((entry) => entry.isRetryable)
          .toList()
        ..sort((a, b) => a.createdAt.compareTo(b.createdAt));

      if (retryableEntries.isEmpty) {
        debugPrint('[SyncController] Tidak ada entry untuk di-sync.');
        state = state.copyWith(isSyncing: false);
        _isSyncRunning = false;
        return;
      }

      debugPrint(
        '[SyncController] Memulai sync ${retryableEntries.length} entry...',
      );

      String? lastError;

      for (final entry in retryableEntries) {
        final success = await _repository.syncOneEntry(entry);
        if (!success) {
          lastError = 'Gagal mengirim: ${entry.idempotencyKey.substring(0, 8)}...';
        }
      }

      state = state.copyWith(
        lastSyncTime: DateTime.now(),
        lastError: lastError,
      );

      debugPrint('[SyncController] Sync selesai.');
    } catch (e) {
      debugPrint('[SyncController] Error tak terduga saat sync: $e');
      state = state.copyWith(lastError: 'Error: $e');
    } finally {
      _isSyncRunning = false;
      state = state.copyWith(isSyncing: false);
      _refreshCounts();
    }
  }

  /// Mulai auto-sync foreground dengan interval 60 detik.
  ///
  /// HANYA boleh dipanggil saat `AppLifecycleState.resumed`.
  void startAutoSync() {
    if (_autoSyncTimer?.isActive ?? false) return; // Sudah jalan

    debugPrint('[SyncController] Auto-sync timer dimulai (60 detik).');
    _autoSyncTimer = Timer.periodic(
      const Duration(seconds: 60),
      (_) => forceSyncNow(),
    );
  }

  /// Hentikan auto-sync timer.
  ///
  /// Dipanggil saat app masuk background (`paused`/`inactive`).
  void stopAutoSync() {
    _autoSyncTimer?.cancel();
    _autoSyncTimer = null;
    debugPrint('[SyncController] Auto-sync timer dihentikan.');
  }

  /// Tambahkan entry baru ke antrian Hive. Caller bertanggung jawab
  /// memanggil forceSyncNow() jika ingin sinkronisasi instan.
  Future<void> enqueue(QueuedLog entry) async {
    await _box.add(entry);
    _refreshCounts();
    debugPrint('[SyncController] Entry baru ditambahkan: ${entry.idempotencyKey}');
  }

  /// Hitung ulang statistik dari Hive box dan update state.
  void _refreshCounts() {
    final all = _box.values.toList();
    final pending = all.where(
      (e) => e.status == QueueStatus.pending || e.status == QueueStatus.syncing,
    ).length;
    final success = all.where(
      (e) => e.status == QueueStatus.success,
    ).length;
    final failed = all.where(
      (e) => e.status == QueueStatus.failed,
    ).length;

    state = state.copyWith(
      pendingCount: pending,
      successCount: success,
      failedCount: failed,
    );
  }

  /// Mendapatkan semua log dari Hive (untuk halaman Riwayat).
  List<QueuedLog> getAllLogs() {
    return _box.values.toList()
      ..sort((a, b) => b.createdAt.compareTo(a.createdAt)); // Terbaru dulu
  }

  @override
  void dispose() {
    stopAutoSync();
    super.dispose();
  }
}

/// Riverpod provider untuk Hive [Box] of [QueuedLog].
///
/// Di-override di `main.dart` setelah Hive box dibuka.
final queuedLogsBoxProvider = Provider<Box<QueuedLog>>((ref) {
  throw UnimplementedError(
    'queuedLogsBoxProvider harus di-override di ProviderScope',
  );
});

/// Riverpod provider untuk SyncController.
///
/// Otomatis terikat ke Hive box via [queuedLogsBoxProvider].
final syncControllerProvider =
    StateNotifierProvider<SyncController, SyncState>((ref) {
  final box = ref.watch(queuedLogsBoxProvider);
  final apiClient = ref.watch(apiClientProvider);
  return SyncController(
    box: box,
    repository: SyncRepository(apiClient),
  );
});
