
import 'package:flutter/foundation.dart';
import 'package:image/image.dart' as img;
import 'package:intl/intl.dart';

/// Data input untuk proses watermark.
///
/// Sesuai PRD §4.6: Isi watermark mencakup Nama Teknisi, Koordinat,
/// Waktu aktual saat rana ditekan, dan Nama Lokasi/Aset.
class WatermarkInput {
  final Uint8List rawBytes;
  final String namaTeknisi;
  final double latitude;
  final double longitude;
  final DateTime waktu;
  final String namaLokasiAset;

  const WatermarkInput({
    required this.rawBytes,
    required this.namaTeknisi,
    required this.latitude,
    required this.longitude,
    required this.waktu,
    required this.namaLokasiAset,
  });
}

/// Fungsi top-level untuk komposit watermark ke gambar.
///
/// WAJIB top-level (bukan method/closure) agar dapat digunakan
/// dengan `compute()` / isolate terpisah.
///
/// Sesuai PRD §4.6 & Spesifikasi Teknis v1.2 §3.2.2:
/// - Kotak latar semi-transparan gelap di bagian bawah gambar
/// - Teks putih di atas kotak gelap
/// - Encode JPEG 85% (PRD §6 — Kompresi Foto)
/// - Dijalankan di isolate terpisah agar tidak memblokir UI thread
Uint8List applyWatermark(WatermarkInput input) {
  final original = img.decodeImage(input.rawBytes);
  if (original == null) {
    // Fallback: kembalikan bytes asli jika decode gagal
    return input.rawBytes;
  }

  final dateFormat = DateFormat('dd/MM/yyyy HH:mm:ss');

  final lines = [
    'Teknisi: ${input.namaTeknisi}',
    'Lokasi Aset: ${input.namaLokasiAset}',
    'Koordinat: ${input.latitude.toStringAsFixed(6)}, ${input.longitude.toStringAsFixed(6)}',
    'Waktu: ${dateFormat.format(input.waktu)}',
  ];

  // Hitung ukuran font berdasarkan lebar gambar (responsif)
  final fontSize = (original.width * 0.028).clamp(14, 32).toInt();
  final lineHeight = (fontSize * 1.5).toInt();
  final boxPadding = (fontSize * 0.6).toInt();
  final boxHeight = lineHeight * lines.length + boxPadding * 2;

  // Kotak latar semi-transparan gelap di bagian bawah (PRD §5.3)
  // Opacity ~55% hitam agar watermark tetap terbaca di atas foto
  // lapangan dengan latar yang bervariasi
  img.fillRect(
    original,
    x1: 0,
    y1: original.height - boxHeight,
    x2: original.width,
    y2: original.height,
    color: img.ColorRgba8(0, 0, 0, 140),
  );

  // Gambar teks watermark putih di atas kotak gelap
  final font = _selectFont(fontSize);
  for (var i = 0; i < lines.length; i++) {
    img.drawString(
      original,
      lines[i],
      font: font,
      x: boxPadding,
      y: original.height - boxHeight + boxPadding + (i * lineHeight),
      color: img.ColorRgb8(255, 255, 255),
    );
  }

  // Encode JPEG dengan kualitas 85% (PRD §6 — Kompresi Foto)
  return Uint8List.fromList(img.encodeJpg(original, quality: 85));
}

/// Wrapper untuk memanggil `applyWatermark` di isolate terpisah.
///
/// Penggunaan:
/// ```dart
/// final watermarkedBytes = await processWatermarkInIsolate(input);
/// ```
Future<Uint8List> processWatermarkInIsolate(WatermarkInput input) {
  return compute(applyWatermark, input);
}

/// Memilih font terbaik berdasarkan ukuran target.
/// Package `image` memiliki font built-in terbatas,
/// kita pilih yang paling mendekati ukuran yang diinginkan.
img.BitmapFont _selectFont(int targetSize) {
  if (targetSize >= 24) return img.arial48;
  if (targetSize >= 16) return img.arial24;
  return img.arial14;
}
