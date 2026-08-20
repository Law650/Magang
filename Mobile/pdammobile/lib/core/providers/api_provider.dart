import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../services/api_client.dart';
import 'technician_provider.dart';
import '../../main.dart';

bool _isShowing401Alert = false;

final Provider<ApiClient> apiClientProvider = Provider<ApiClient>((ProviderRef<ApiClient> ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return ApiClient(
    prefs,
    onUnauthorized: () {
      // Panggil force logout lokal saat token tidak valid / expired
      ref.read(technicianNameProvider.notifier).forceLogoutLocal();
      
      final context = navigatorKey.currentContext;
      if (context != null && !_isShowing401Alert) {
        _isShowing401Alert = true;
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            title: const Text('Sesi Berakhir (401)'),
            content: const Text('Akun Anda telah tertabrak, login di perangkat lain, atau sesi telah berakhir. Silakan login kembali.'),
            actions: [
              TextButton(
                onPressed: () {
                  _isShowing401Alert = false;
                  Navigator.pop(context);
                },
                child: const Text('OK'),
              ),
            ],
          ),
        );
      }
    },
  );
});
