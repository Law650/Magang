import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/api_client.dart';
import 'api_provider.dart';

const String _kTechnicianNameKey = 'technician_name';
const String _kAuthTokenKey = 'auth_token';
const String _kTechnicianIdKey = 'technician_id';

final sharedPreferencesProvider = Provider<SharedPreferences>((ref) {
  throw UnimplementedError(
    'sharedPreferencesProvider harus di-override di ProviderScope',
  );
});

final technicianNameProvider = StateNotifierProvider<TechnicianNameNotifier, String?>(
  (ref) {
    final prefs = ref.watch(sharedPreferencesProvider);
    final apiClient = ref.watch(apiClientProvider);
    return TechnicianNameNotifier(prefs, apiClient);
  },
);

class TechnicianNameNotifier extends StateNotifier<String?> {
  final SharedPreferences _prefs;
  final ApiClient _apiClient;

  TechnicianNameNotifier(this._prefs, this._apiClient)
      : super(_prefs.getString(_kTechnicianNameKey));

  /// Login menggunakan endpoint API sungguhan.
  /// Mengembalikan [null] jika berhasil, atau [String] pesan error jika gagal.
  Future<String?> login(String usernameOrEmail, String password) async {
    final trimmedLogin = usernameOrEmail.trim();
    if (trimmedLogin.isEmpty || password.isEmpty) {
      return 'Username/Email dan password tidak boleh kosong';
    }
    
    try {
      final response = await _apiClient.dio.post('/auth/login', data: {
        'login': trimmedLogin,
        'password': password,
      });

      if (response.data['success'] == true) {
        final token = response.data['data']['token'];
        final user = response.data['data']['user'];
        final technicianName = user['name'];
        final technicianId = user['id'];

        // Simpan ke SharedPreferences
        await _prefs.setString(_kAuthTokenKey, token);
        await _prefs.setString(_kTechnicianNameKey, technicianName);
        await _prefs.setInt(_kTechnicianIdKey, technicianId);
        
        state = technicianName;
        return null;
      }
      return response.data['message'] ?? 'Login gagal.';
    } on DioException catch (e) {
      debugPrint('[Login Error] DioError: ${e.response?.data}');
      return e.response?.data['message'] ?? 'Gagal terhubung ke server.';
    } catch (e) {
      debugPrint('[Login Error] Unexpected: $e');
      return 'Terjadi kesalahan sistem.';
    }
  }

  /// Logout: hapus token dari backend dan bersihkan lokal.
  Future<void> logout() async {
    try {
      await _apiClient.dio.post('/auth/logout');
    } catch (e) {
      // Ignore error if already unauthenticated or offline
      debugPrint('[Logout Error] $e');
    }

    await _prefs.remove(_kAuthTokenKey);
    await _prefs.remove(_kTechnicianNameKey);
    await _prefs.remove(_kTechnicianIdKey);
    state = null;
  }

  bool get hasName => state != null && state!.isNotEmpty;
}
