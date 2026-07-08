import 'dart:io';

import 'api_client.dart';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';

import '../models/queued_log.dart';

/// Repository untuk mengirim data antrian ke server — Modul G (PRD §4.7).
///
/// Menggunakan [Dio] dengan format `multipart/form-data` untuk mengunggah
/// payload fields beserta file foto bukti.
///
/// Prinsip desain:
/// - Satu entry per panggilan (`syncOneEntry`) agar mudah di-track status-nya
/// - Header `X-Idempotency-Key` untuk mencegah duplikasi di sisi server
/// - Timeout 15 detik untuk connect & receive (jaringan lapangan sering lambat)
/// - Error handling aman: network error → retry, bukan crash
class SyncRepository {
  final ApiClient _apiClient;

  SyncRepository(this._apiClient);

  /// Mengirim satu entry antrian ke server.
  ///
  /// Flow:
  /// 1. Set status → `syncing` dan simpan ke Hive
  /// 2. Cek apakah file foto masih ada di device
  /// 3. Build `FormData` dari payloadFields + file foto
  /// 4. POST ke endpoint dengan header `X-Idempotency-Key`
  /// 5. Jika sukses (2xx) → status `success`
  /// 6. Jika gagal → `retryCount++`, status `failed`
  ///
  /// Returns `true` jika berhasil, `false` jika gagal.
  Future<bool> syncOneEntry(QueuedLog entry) async {
    // 1. Tandai sedang syncing
    entry.status = QueueStatus.syncing;
    await entry.save();

    try {
      // 2. Siapkan FormData dari payload fields
      final Map<String, dynamic> formMap = Map<String, dynamic>.from(
        entry.payloadFields,
      );

      // 3. Cek dan tambahkan file foto jika path valid
      if (entry.fotoPath.isNotEmpty) {
        final fotoFile = File(entry.fotoPath);
        if (fotoFile.existsSync()) {
          formMap['foto'] = await MultipartFile.fromFile(
            entry.fotoPath,
            filename: '${entry.idempotencyKey}.jpg',
            contentType: DioMediaType('image', 'jpeg'),
          );
        } else {
          debugPrint(
            '[SyncRepository] File foto tidak ditemukan: ${entry.fotoPath}',
          );
          // Tetap kirim data tanpa foto — lebih baik data sampai
          // daripada gagal total karena foto hilang
        }
      }

      final formData = FormData.fromMap(formMap);

      // 4. Kirim ke server dengan idempotency key
      final response = await _apiClient.dio.post(
        entry.endpoint,
        data: formData,
        options: Options(
          headers: {
            'X-Idempotency-Key': entry.idempotencyKey,
          },
        ),
      );

      // 5. Cek response sukses (2xx)
      if (response.statusCode != null &&
          response.statusCode! >= 200 &&
          response.statusCode! < 300) {
        entry.status = QueueStatus.success;
        await entry.save();
        debugPrint(
          '[SyncRepository] ✓ Berhasil sync: ${entry.idempotencyKey}',
        );
        return true;
      }

      // Response non-2xx yang bukan exception
      _markAsFailed(entry);
      debugPrint(
        '[SyncRepository] ✗ Response ${response.statusCode}: ${entry.idempotencyKey}',
      );
      return false;
    } on DioException catch (e) {
      _markAsFailed(entry);
      _logDioError(e, entry);
      return false;
    } catch (e) {
      _markAsFailed(entry);
      debugPrint(
        '[SyncRepository] ✗ Unexpected error: $e (${entry.idempotencyKey})',
      );
      return false;
    }
  }

  /// Tandai entry sebagai failed dan tambah retry count.
  void _markAsFailed(QueuedLog entry) {
    entry.retryCount += 1;
    entry.status = QueueStatus.failed;
    entry.save(); // fire-and-forget, tidak perlu await
  }

  /// Log detail error Dio untuk debugging.
  void _logDioError(DioException e, QueuedLog entry) {
    final key = entry.idempotencyKey;
    switch (e.type) {
      case DioExceptionType.connectionTimeout:
        debugPrint('[SyncRepository] ✗ Connection timeout: $key');
        break;
      case DioExceptionType.sendTimeout:
        debugPrint('[SyncRepository] ✗ Send timeout: $key');
        break;
      case DioExceptionType.receiveTimeout:
        debugPrint('[SyncRepository] ✗ Receive timeout: $key');
        break;
      case DioExceptionType.connectionError:
        debugPrint('[SyncRepository] ✗ No connection: $key');
        break;
      case DioExceptionType.badResponse:
        debugPrint(
          '[SyncRepository] ✗ Bad response ${e.response?.statusCode}: $key',
        );
        break;
      default:
        debugPrint('[SyncRepository] ✗ DioError (${e.type}): $key');
    }
  }
}
