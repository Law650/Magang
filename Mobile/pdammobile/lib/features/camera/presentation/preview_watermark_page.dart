import 'dart:io';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:path_provider/path_provider.dart';
import 'package:uuid/uuid.dart';
import '../../../core/theme/app_colors.dart';

/// Halaman Pratinjau & Konfirmasi Foto yang Sudah Di-Watermark.
///
/// Sesuai PRD §4.6:
/// - Menampilkan pratinjau foto akhir (lengkap watermark)
/// - Tombol "Ambil Ulang" → kembali ke kamera
/// - Tombol "Gunakan Foto Ini" → simpan file fisik → return path
///
/// Menerima `watermarkedBytes` (Uint8List) sebagai parameter.
/// Mengembalikan `String?` file path lokal melalui `Navigator.pop()`.
class PreviewWatermarkPage extends StatefulWidget {
  final Uint8List watermarkedBytes;
  final String formType; // 'log_valve' atau 'log_tekanan'

  const PreviewWatermarkPage({
    super.key,
    required this.watermarkedBytes,
    required this.formType,
  });

  @override
  State<PreviewWatermarkPage> createState() => _PreviewWatermarkPageState();
}

class _PreviewWatermarkPageState extends State<PreviewWatermarkPage> {
  bool _isSaving = false;

  /// Simpan file JPEG ke direktori lokal perangkat.
  ///
  /// Sesuai Spesifikasi Teknis v1.2 §3.2.3:
  /// - Path: `{appDocDir}/foto_bukti/{formType}/{uuid}.jpg`
  /// - File fisik, BUKAN Base64 di Hive
  Future<void> _saveAndReturn() async {
    setState(() => _isSaving = true);

    try {
      final dir = await getApplicationDocumentsDirectory();
      final subDir = Directory('${dir.path}/foto_bukti/${widget.formType}');
      if (!await subDir.exists()) {
        await subDir.create(recursive: true);
      }

      final uuid = const Uuid().v4();
      final file = File('${subDir.path}/$uuid.jpg');
      await file.writeAsBytes(widget.watermarkedBytes);

      if (!mounted) return;

      // Return file path ke halaman form
      Navigator.of(context).pop(file.path);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal menyimpan foto: ${e.toString()}'),
          backgroundColor: AppColors.statusKritis,
          behavior: SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      );
      setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);

    return Scaffold(
      backgroundColor: Colors.black,
      appBar: AppBar(
        backgroundColor: Colors.black,
        foregroundColor: Colors.white,
        title: const Text('Pratinjau Foto'),
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.close_rounded),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: Column(
        children: [
          // ── Preview Foto ────────────────────────────────────────
          Expanded(
            child: InteractiveViewer(
              minScale: 0.5,
              maxScale: 3.0,
              child: Center(
                child: Image.memory(
                  widget.watermarkedBytes,
                  fit: BoxFit.contain,
                ),
              ),
            ),
          ),

          // ── Info ────────────────────────────────────────────────
          Container(
            width: double.infinity,
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
            color: Colors.black,
            child: Text(
              'Periksa keterbacaan watermark pada foto. '
              'Foto tidak dapat diedit setelah disimpan.',
              textAlign: TextAlign.center,
              style: theme.textTheme.bodyMedium?.copyWith(
                color: Colors.white54,
                fontSize: 12,
              ),
            ),
          ),

          // ── Action Buttons ─────────────────────────────────────
          Container(
            width: double.infinity,
            padding: EdgeInsets.fromLTRB(
              20,
              12,
              20,
              MediaQuery.of(context).padding.bottom + 16,
            ),
            color: Colors.black,
            child: Row(
              children: [
                // Tombol "Ambil Ulang" — outline merah
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _isSaving
                        ? null
                        : () {
                            // Kembali ke kamera tanpa return path (null)
                            // Form page akan memanggil kamera lagi
                            Navigator.of(context).pop('retake');
                          },
                    icon: const Icon(Icons.camera_alt_outlined, size: 20),
                    label: const Text('Ambil Ulang'),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: AppColors.statusKritis,
                      side: const BorderSide(color: AppColors.statusKritis),
                      minimumSize: const Size(0, 56),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                  ),
                ),

                const SizedBox(width: 12),

                // Tombol "Gunakan Foto Ini" — elevated hijau
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: _isSaving ? null : _saveAndReturn,
                    icon: _isSaving
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(Icons.check_rounded, size: 20),
                    label: Text(_isSaving ? 'Menyimpan...' : 'Gunakan Foto'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppColors.statusNormal,
                      foregroundColor: Colors.white,
                      minimumSize: const Size(0, 56),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
