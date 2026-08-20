import 'dart:typed_data';
import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'package:image/image.dart' as img;
import '../../../core/theme/app_colors.dart';

/// Fungsi global (isolate) untuk kompresi gambar agar UI tidak freeze
Future<Uint8List> _compressImage(Uint8List rawBytes) async {
  final img.Image? decoded = img.decodeImage(rawBytes);
  if (decoded == null) return rawBytes;
  
  img.Image resized = decoded;
  if (decoded.width > 1200 || decoded.height > 1200) {
    resized = img.copyResize(decoded, width: 1200);
  }
  return Uint8List.fromList(img.encodeJpg(resized, quality: 50));
}

/// Layar Kamera Kustom Strict — Modul F (PRD §4.6).
///
/// Prinsip utama:
/// - Full-screen CameraPreview
/// - TIDAK ADA akses/tombol ke galeri perangkat (strict camera-only)
/// - Overlay kotak semi-transparan di bagian bawah sebagai panduan area watermark
/// - Tombol shutter bulat besar (touch target ≥ 56dp)
/// - Controller di-dispose dengan benar untuk mencegah memory leak
///
/// Returns `Uint8List` bytes foto mentah melalui `Navigator.pop()`.
class StrictCameraPage extends StatefulWidget {
  const StrictCameraPage({super.key});

  @override
  State<StrictCameraPage> createState() => _StrictCameraPageState();
}

class _StrictCameraPageState extends State<StrictCameraPage>
    with WidgetsBindingObserver {
  CameraController? _controller;
  bool _isInitialized = false;
  bool _isCapturing = false;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    _initCamera();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _controller?.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final controller = _controller;
    if (controller == null || !controller.value.isInitialized) return;

    if (state == AppLifecycleState.inactive) {
      controller.dispose();
      _controller = null;
    } else if (state == AppLifecycleState.resumed) {
      _initCamera();
    }
  }

  Future<void> _initCamera() async {
    try {
      final cameras = await availableCameras();
      if (cameras.isEmpty) {
        setState(() {
          _errorMessage = 'Tidak ada kamera yang tersedia di perangkat ini.';
        });
        return;
      }

      // Pilih kamera belakang (PRD — foto lapangan)
      final backCamera = cameras.firstWhere(
        (c) => c.lensDirection == CameraLensDirection.back,
        orElse: () => cameras.first,
      );

      final controller = CameraController(
        backCamera,
        ResolutionPreset.high,
        enableAudio: false, // Tidak perlu audio untuk foto
        imageFormatGroup: ImageFormatGroup.jpeg,
      );

      _controller = controller;

      await controller.initialize();

      if (!mounted) return;

      setState(() {
        _isInitialized = true;
        _errorMessage = null;
      });
    } catch (e) {
      setState(() {
        _errorMessage = 'Gagal menginisialisasi kamera: ${e.toString()}';
      });
    }
  }

  Future<void> _onShutterPressed() async {
    final controller = _controller;
    if (controller == null || !controller.value.isInitialized || _isCapturing) {
      return;
    }

    setState(() => _isCapturing = true);

    try {
      final XFile rawFile = await controller.takePicture();
      final Uint8List rawBytes = await rawFile.readAsBytes();

      if (!mounted) return;
      
      // Kompres foto menggunakan isolate agar UI tidak tersendat
      final Uint8List compressedBytes = await compute(_compressImage, rawBytes);

      if (!mounted) return;

      // Return bytes terkompresi ke pemanggil
      Navigator.of(context).pop(compressedBytes);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal mengambil foto: ${e.toString()}'),
          backgroundColor: AppColors.statusKritis,
          behavior: SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      );
      setState(() => _isCapturing = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      body: _errorMessage != null
          ? _buildErrorView()
          : !_isInitialized
              ? _buildLoadingView()
              : _buildCameraView(),
    );
  }

  Widget _buildLoadingView() {
    return const Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          CircularProgressIndicator(color: Colors.white),
          SizedBox(height: 16),
          Text(
            'Mempersiapkan kamera...',
            style: TextStyle(color: Colors.white70, fontSize: 14),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorView() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.camera_alt_outlined, color: Colors.white38, size: 64),
            const SizedBox(height: 20),
            Text(
              _errorMessage!,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.white70, fontSize: 15),
            ),
            const SizedBox(height: 24),
            OutlinedButton.icon(
              onPressed: _initCamera,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba Lagi'),
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white,
                side: const BorderSide(color: Colors.white54),
              ),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: () => Navigator.of(context).pop(),
              child: const Text(
                'Kembali',
                style: TextStyle(color: Colors.white54),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCameraView() {
    final controller = _controller!;
    final previewSize = controller.value.previewSize;

    return Stack(
      fit: StackFit.expand,
      children: [
        // ── Camera Preview (full screen) ────────────────────────
        Center(
          child: AspectRatio(
            aspectRatio: previewSize != null
                ? previewSize.height / previewSize.width
                : 3 / 4,
            child: CameraPreview(controller),
          ),
        ),

        // ── Overlay panduan area watermark (PRD §4.6) ───────────
        // Kotak semi-transparan hitam di bagian bawah
        Positioned(
          left: 0,
          right: 0,
          bottom: 0,
          child: Container(
            height: MediaQuery.of(context).size.height * 0.18,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topCenter,
                end: Alignment.bottomCenter,
                colors: [
                  Colors.transparent,
                  Colors.black.withValues(alpha: 0.4),
                  Colors.black.withValues(alpha: 0.6),
                ],
                stops: const [0.0, 0.3, 1.0],
              ),
            ),
            child: const Padding(
              padding: EdgeInsets.only(left: 16, bottom: 90, top: 12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    '↓ Area Watermark',
                    style: TextStyle(
                      color: Colors.white54,
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),

        // ── Tombol Close (kiri atas) ────────────────────────────
        Positioned(
          top: MediaQuery.of(context).padding.top + 12,
          left: 12,
          child: Material(
            color: Colors.black38,
            borderRadius: BorderRadius.circular(24),
            child: InkWell(
              borderRadius: BorderRadius.circular(24),
              onTap: () => Navigator.of(context).pop(),
              child: const Padding(
                padding: EdgeInsets.all(10),
                child: Icon(Icons.close_rounded, color: Colors.white, size: 24),
              ),
            ),
          ),
        ),

        // ── Tombol Shutter (tengah bawah, ≥ 56dp) ──────────────
        Positioned(
          bottom: MediaQuery.of(context).padding.bottom + 24,
          left: 0,
          right: 0,
          child: Center(
            child: GestureDetector(
              onTap: _isCapturing ? null : _onShutterPressed,
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 150),
                width: 76,
                height: 76,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: Colors.white,
                    width: 4,
                  ),
                  color: _isCapturing
                      ? Colors.white38
                      : Colors.white.withValues(alpha: 0.2),
                ),
                child: _isCapturing
                    ? const Center(
                        child: SizedBox(
                          width: 28,
                          height: 28,
                          child: CircularProgressIndicator(
                            strokeWidth: 2.5,
                            color: Colors.white,
                          ),
                        ),
                      )
                    : Center(
                        child: Container(
                          width: 60,
                          height: 60,
                          decoration: const BoxDecoration(
                            shape: BoxShape.circle,
                            color: Colors.white,
                          ),
                        ),
                      ),
              ),
            ),
          ),
        ),

        // ── Label TIDAK ADA galeri — visual reminder ────────────
        Positioned(
          bottom: MediaQuery.of(context).padding.bottom + 86,
          left: 0,
          right: 0,
          child: const Center(
            child: Text(
              'Ambil foto langsung dari kamera',
              style: TextStyle(
                color: Colors.white54,
                fontSize: 12,
                fontWeight: FontWeight.w400,
              ),
            ),
          ),
        ),
      ],
    );
  }
}
