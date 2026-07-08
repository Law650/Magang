import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:hive_ce_flutter/hive_flutter.dart';
import 'package:intl/date_symbol_data_local.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'core/models/queued_log.dart';
import 'core/theme/app_theme.dart';
import 'core/providers/technician_provider.dart';
import 'core/services/sync_controller.dart';
import 'core/services/sync_background.dart';
import 'features/identity/presentation/login_page.dart';
import 'features/main/presentation/main_layout.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Inisialisasi locale Indonesia untuk format tanggal (intl)
  await initializeDateFormatting('id', null);

  // Inisialisasi SharedPreferences sebelum app dimulai
  final prefs = await SharedPreferences.getInstance();

  // ── Hive Initialization (Modul G — Offline Queue) ─────────────
  await Hive.initFlutter();

  // Register TypeAdapter untuk QueuedLog
  Hive.registerAdapter(QueuedLogAdapter());

  // Buka box antrian log
  final queuedLogsBox = await Hive.openBox<QueuedLog>(kQueuedLogsBoxName);

  // MOCK DATA INJECTION
  final hasMockData = queuedLogsBox.values.any((log) => log.idempotencyKey == 'mock-uuid-gv02-decimal');
  if (!hasMockData) {
    final queuedLog = QueuedLog(
      idempotencyKey: 'mock-uuid-gv02-decimal',
      payloadFields: {
        'aset_id': 2,
        'nama_aset': 'GV-02 Cabang A',
        'nama_lokasi': 'Jl. Gatot Subroto',
        'nama_teknisi': prefs.getString('technicianName') ?? 'Teknisi Dummy',
        'waktu_kegiatan': DateTime.now().toIso8601String(),
        'kapasitas_full': 10.0,
        'aksi_kerja': 'tutup',
        'jumlah_putaran': 7.25,
        'keterangan': 'Pengecekan desimal',
        'latitude': -7.983908,
        'longitude': 112.621391,
      },
      fotoPath: 'dummy_path.jpg',
      endpoint: '/log-valve',
    );
    queuedLog.status = QueueStatus.success; // Mock status success
    await queuedLogsBox.add(queuedLog);
  }

  // ── Workmanager Background Sync (Modul G) ─────────────────────
  await initBackgroundSync();

  runApp(
    ProviderScope(
      overrides: [
        // Override provider dengan instance SharedPreferences yang sudah siap
        sharedPreferencesProvider.overrideWithValue(prefs),

        // Override provider dengan Hive box yang sudah terbuka
        queuedLogsBoxProvider.overrideWithValue(queuedLogsBox),
      ],
      child: const PdamMobileApp(),
    ),
  );
}

/// Root widget aplikasi PDAM Mobile.
///
/// Menentukan halaman awal berdasarkan status nama teknisi:
/// - Belum ada nama → IdentityPage (PRD §4.1)
/// - Sudah ada nama → MainLayout (PRD §5.1)
class PdamMobileApp extends ConsumerWidget {
  const PdamMobileApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final technicianName = ref.watch(technicianNameProvider);
    final hasName = technicianName != null && technicianName.isNotEmpty;

    return MaterialApp(
      title: 'PDAM Mobile — Teknisi Lapangan',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.lightTheme,
      home: hasName ? const MainLayout() : const LoginPage(),
    );
  }
}
