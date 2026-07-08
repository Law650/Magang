import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../services/api_client.dart';
import 'technician_provider.dart';

final apiClientProvider = Provider<ApiClient>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return ApiClient(prefs);
});
