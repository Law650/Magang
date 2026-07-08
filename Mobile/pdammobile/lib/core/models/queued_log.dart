import 'package:hive_ce/hive.dart';

/// Status yang mungkin dimiliki oleh entri antrian sync.
///
/// Digunakan sebagai String di field `status` pada [QueuedLog]:
/// - `pending`  → Baru dibuat, belum pernah dicoba kirim
/// - `syncing`  → Sedang dalam proses pengiriman
/// - `success`  → Berhasil dikirim ke server
/// - `failed`   → Gagal dikirim (akan di-retry jika retryCount < max)
abstract class QueueStatus {
  static const String pending = 'pending';
  static const String syncing = 'syncing';
  static const String success = 'success';
  static const String failed = 'failed';
}

/// Model antrian lokal untuk offline-first sync — Modul G (PRD §4.7).
///
/// Setiap kali form (Log Valve / Log Tekanan) di-submit, sebuah instance
/// [QueuedLog] dibuat dan disimpan ke Hive box `queuedLogs`.
/// [SyncController] kemudian mencoba mengirimnya ke server secara FIFO.
///
/// Field `idempotencyKey` (UUID v4) memastikan server bisa mendeteksi
/// duplikasi jika entry yang sama terkirim lebih dari sekali.
class QueuedLog extends HiveObject {
  /// UUID v4 unik per entry — digunakan sebagai header
  /// `X-Idempotency-Key` saat pengiriman ke server.
  final String idempotencyKey;

  /// Data form yang dikumpulkan dari UI.
  final Map<String, dynamic> payloadFields;

  /// Path absolut file foto bukti yang sudah di-watermark.
  /// Bisa kosong jika foto tidak wajib pada form tertentu.
  final String fotoPath;

  /// Status sinkronisasi entry ini.
  /// Lihat [QueueStatus] untuk nilai yang valid.
  String status;

  /// Jumlah percobaan pengiriman yang sudah dilakukan.
  /// Ditambah +1 setiap kali gagal. Sync berhenti jika >= [maxRetryCount].
  int retryCount;

  /// Target endpoint API relatif (e.g. `/log-valve`, `/log-tekanan`).
  final String endpoint;

  /// Waktu pembuatan entry — digunakan untuk FIFO ordering.
  final DateTime createdAt;

  QueuedLog({
    required this.idempotencyKey,
    required this.payloadFields,
    required this.fotoPath,
    required this.endpoint,
    this.status = QueueStatus.pending,
    this.retryCount = 0,
    DateTime? createdAt,
  }) : createdAt = createdAt ?? DateTime.now();

  /// Batas maksimum retry sebelum entry diabaikan oleh auto-sync.
  static const int maxRetryCount = 5;

  /// Apakah entry ini layak untuk dicoba kirim ulang.
  bool get isRetryable =>
      (status == QueueStatus.pending || status == QueueStatus.failed) &&
      retryCount < maxRetryCount;

  @override
  String toString() =>
      'QueuedLog(key=$idempotencyKey, status=$status, retry=$retryCount, endpoint=$endpoint)';
}

/// TypeAdapter manual untuk [QueuedLog] — Hive typeId: 0.
///
/// Ditulis manual untuk menghindari masalah kompatibilitas
/// dengan hive_ce_generator dan build_runner.
/// Field order (index) harus TETAP KONSISTEN selamanya.
class QueuedLogAdapter extends TypeAdapter<QueuedLog> {
  @override
  final int typeId = 0;

  @override
  QueuedLog read(BinaryReader reader) {
    final numOfFields = reader.readByte();
    final fields = <int, dynamic>{
      for (int i = 0; i < numOfFields; i++) reader.readByte(): reader.read(),
    };

    return QueuedLog(
      idempotencyKey: fields[0] as String,
      payloadFields: Map<String, dynamic>.from(fields[1] as Map),
      fotoPath: fields[2] as String,
      endpoint: fields[5] as String,
      status: fields[3] as String,
      retryCount: fields[4] as int,
      createdAt: fields[6] as DateTime,
    );
  }

  @override
  void write(BinaryWriter writer, QueuedLog obj) {
    writer
      ..writeByte(7) // Jumlah field
      ..writeByte(0)
      ..write(obj.idempotencyKey)
      ..writeByte(1)
      ..write(obj.payloadFields)
      ..writeByte(2)
      ..write(obj.fotoPath)
      ..writeByte(3)
      ..write(obj.status)
      ..writeByte(4)
      ..write(obj.retryCount)
      ..writeByte(5)
      ..write(obj.endpoint)
      ..writeByte(6)
      ..write(obj.createdAt);
  }
}
