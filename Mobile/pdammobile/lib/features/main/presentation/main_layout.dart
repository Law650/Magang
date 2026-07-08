import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/sync_controller.dart';
import '../../home/presentation/home_page.dart';
import '../../log_valve/presentation/log_valve_form_page.dart';
import '../../log_tekanan/presentation/log_tekanan_form_page.dart';
import '../../riwayat/presentation/riwayat_page.dart';

/// Main Layout dengan BottomNavigationBar 4 tab.
///
/// Sesuai PRD §5.1 (disesuaikan: 4 tab tanpa Profil per instruksi user):
/// 1. Home — Dashboard ringkasan
/// 2. Log Valve — Form input Modul C
/// 3. Log Tekanan — Form input Modul D
/// 4. Riwayat — Daftar log (placeholder)
///
/// Modul G: Mengelola lifecycle auto-sync timer via WidgetsBindingObserver.
/// Timer hanya aktif saat app di foreground (AppLifecycleState.resumed).
class MainLayout extends ConsumerStatefulWidget {
  const MainLayout({super.key});

  @override
  ConsumerState<MainLayout> createState() => _MainLayoutState();
}

class _MainLayoutState extends ConsumerState<MainLayout>
    with WidgetsBindingObserver {
  int _currentIndex = 0;

  /// Menggunakan IndexedStack agar state tiap tab tetap terjaga
  /// saat berpindah tab (form tidak ter-reset).
  final List<Widget> _pages = const [
    HomePage(),
    LogValveFormPage(),
    LogTekananFormPage(),
    RiwayatPage(),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    // Mulai auto-sync saat app pertama kali dibuka (sudah resumed)
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(syncControllerProvider.notifier).startAutoSync();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  /// Kelola auto-sync berdasarkan lifecycle app.
  ///
  /// - `resumed` → Mulai timer (60 detik)
  /// - `paused` / `inactive` / `detached` → Hentikan timer
  ///
  /// Ini memastikan Timer.periodic tidak membuang baterai saat app
  /// di background (background sync ditangani oleh Workmanager).
  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final syncController = ref.read(syncControllerProvider.notifier);

    if (state == AppLifecycleState.resumed) {
      syncController.startAutoSync();
      // Juga langsung coba sync sekali saat app kembali ke foreground
      syncController.forceSyncNow();
    } else {
      syncController.stopAutoSync();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _currentIndex,
        children: _pages,
      ),
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (index) => setState(() => _currentIndex = index),
        items: const [
          BottomNavigationBarItem(
            icon: Icon(Icons.home_rounded),
            activeIcon: Icon(Icons.home_rounded),
            label: 'Home',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.settings_input_component_outlined),
            activeIcon: Icon(Icons.settings_input_component),
            label: 'Log Valve',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.speed_outlined),
            activeIcon: Icon(Icons.speed),
            label: 'Log Tekanan',
          ),
          BottomNavigationBarItem(
            icon: Icon(Icons.history_rounded),
            activeIcon: Icon(Icons.history_rounded),
            label: 'Riwayat',
          ),
        ],
      ),
    );
  }
}
