import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../services/api_client.dart';
import 'technician_provider.dart';

final Provider<ApiClient> apiClientProvider = Provider<ApiClient>((ProviderRef<ApiClient> ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return ApiClient(
    prefs,
    onUnauthorized: () {
      // Panggil force logout lokal saat token tidak valid / expired
      ref.read(technicianNameProvider.notifier).forceLogoutLocal();
    },
  );
});
