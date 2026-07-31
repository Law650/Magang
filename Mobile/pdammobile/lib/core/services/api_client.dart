import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiClient {
  // Server staging/production
  static const String baseUrl = 'http://103.217.210.179:3430/api';
  
  // Server lokal (disesuaikan dengan IPv4 jaringan PC Anda saat ini)
  //static const String baseUrl = 'https://untouched-creed-manly.ngrok-free.dev/api';
  // Jika menggunakan Android Emulator dan tidak bisa terhubung, bisa gunakan 'http://10.0.2.2:8000/api'

  late Dio _dio;

  ApiClient(SharedPreferences prefs) {
    _dio = Dio(
      BaseOptions(
        baseUrl: baseUrl,
        connectTimeout: const Duration(seconds: 15),
        receiveTimeout: const Duration(seconds: 15),
        headers: {
          'Accept': 'application/json',
        },
      ),
    );

    _dio.interceptors.add(InterceptorsWrapper(
      onRequest: (options, handler) {
        final token = prefs.getString('auth_token');
        if (token != null && token.isNotEmpty) {
          options.headers['Authorization'] = 'Bearer $token';
        }
        return handler.next(options);
      },
      onError: (DioException error, handler) async {
        if (error.response?.statusCode == 401) {
          // Token expired or invalid, auto logout logic can be added here
          await prefs.remove('auth_token');
        }
        return handler.next(error);
      },
    ));
  }

  Dio get dio => _dio;
}
