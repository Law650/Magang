import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:uuid/uuid.dart';
import 'package:image_picker/image_picker.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/providers/technician_provider.dart';
import '../../../core/services/gps_service.dart';
import '../../../core/services/sync_controller.dart';
import '../../../core/models/queued_log.dart';
import '../../../core/widgets/gps_status_widget.dart';
import '../../../core/widgets/ui_components.dart';
import '../../../core/utils/watermark_utils.dart';
import '../../../core/widgets/searchable_bottom_sheet.dart';
import '../../camera/presentation/strict_camera_page.dart';
import '../../camera/presentation/preview_watermark_page.dart';
import '../data/lokasi_repository.dart';
import '../data/rekap_tekanan_provider.dart';
import 'rekap_tekanan_page.dart';
import 'tambah_daerah_page.dart';

class LogTekananFormPage extends ConsumerStatefulWidget {
  const LogTekananFormPage({super.key});

  @override
  ConsumerState<LogTekananFormPage> createState() => _LogTekananFormPageState();
}

class _LogTekananFormPageState extends ConsumerState<LogTekananFormPage> {
  final _formKey = GlobalKey<FormState>();
  final _namaTeknisiController = TextEditingController();

  Lokasi? _selectedLokasi;
  DateTime _waktuPengecekan = DateTime.now();
  String? _fotoPath;
  bool _isSubmitting = false;

  double _tekananAir = 0.0;
  int _aliranIndex = 0; // 0: Mengalir, 1: Tidak Mengalir

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final name = ref.read(technicianNameProvider);
      if (name != null) {
        _namaTeknisiController.text = name;
      }
      ref.read(gpsServiceProvider.notifier).captureLocation(context);
    });
  }

  @override
  void dispose() {
    _namaTeknisiController.dispose();
    super.dispose();
  }

  Future<void> _pickDateTime() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _waktuPengecekan,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 1)),
    );
    if (date == null || !mounted) return;

    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_waktuPengecekan),
    );
    if (time == null || !mounted) return;

    setState(() {
      _waktuPengecekan = DateTime(
        date.year,
        date.month,
        date.day,
        time.hour,
        time.minute,
      );
    });
  }

  void _showLokasiSearchDialog() async {
    final lokasiList = ref.read(lokasiListProvider).valueOrNull ?? [];
    if (lokasiList.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak ada data lokasi.')),
      );
      return;
    }

    final selected = await SearchableBottomSheet.show<Lokasi>(
      context: context,
      title: 'Pilih Lokasi',
      items: lokasiList,
      itemAsString: (l) => l.namaLokasi,
    );

    if (selected != null && mounted) {
      setState(() => _selectedLokasi = selected);
      
      try {
        final rekapList = await ref.read(rekapTekananProvider.future);
        final latest = rekapList.where((r) => r.id == selected.id).firstOrNull;
        if (latest != null && latest.nilaiTekanan != null && mounted) {
          setState(() {
            _tekananAir = latest.nilaiTekanan!;
          });
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Tekanan air otomatis diisi dengan data terakhir: ${latest.nilaiTekanan} Bar'),
              duration: const Duration(seconds: 2),
            ),
          );
        }
      } catch (_) {}
    }
  }

  Future<void> _handleAmbilFoto(GpsSuccess gps) async {
    final source = await showModalBottomSheet<String>(
      context: context,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(20))),
      builder: (_) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.camera_alt, color: AppColors.primary),
              title: const Text('Kamera'),
              onTap: () => Navigator.pop(context, 'camera'),
            ),
            ListTile(
              leading: const Icon(Icons.photo_library, color: AppColors.primary),
              title: const Text('Galeri'),
              onTap: () => Navigator.pop(context, 'gallery'),
            ),
          ],
        ),
      ),
    );

    if (source == null) return;

    Uint8List? rawBytes;
    if (source == 'camera') {
      rawBytes = await Navigator.push(
        context,
        MaterialPageRoute(builder: (_) => const StrictCameraPage()),
      );
    } else if (source == 'gallery') {
      final picker = ImagePicker();
      final XFile? image = await picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 50,
        maxWidth: 1200,
        maxHeight: 1200,
      );
      if (image != null) {
        rawBytes = await image.readAsBytes();
      }
    }

    if (rawBytes == null || !mounted) return;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => const Center(child: CircularProgressIndicator()),
    );

    final input = WatermarkInput(
      rawBytes: rawBytes,
      namaTeknisi: _namaTeknisiController.text.trim(),
      latitude: gps.latitude,
      longitude: gps.longitude,
      waktu: DateTime.now(),
      namaLokasiAset: _selectedLokasi!.namaLokasi,
      sumberFoto: source == 'camera' ? 'Kamera Langsung' : 'Galeri HP',
    );

    final watermarkedBytes = await processWatermarkInIsolate(input);

    if (!mounted) return;
    Navigator.pop(context); 

    String? action = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => PreviewWatermarkPage(
          watermarkedBytes: watermarkedBytes,
          formType: 'log_tekanan',
        ),
      ),
    );

    if (action == 'retake') {
      if (!mounted) return;
      _handleAmbilFoto(gps);
    } else if (action != null) {
      setState(() => _fotoPath = action);
    }
  }

  Future<void> _handleSubmit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedLokasi == null) {
      _showError('Pilih Lokasi terlebih dahulu');
      return;
    }
    if (_fotoPath == null) {
      _showError('Foto bukti wajib diambil');
      return;
    }

    final gpsState = ref.read(gpsServiceProvider);
    if (gpsState is! GpsSuccess) return;

    setState(() => _isSubmitting = true);

    try {
      final idempotencyKey = const Uuid().v4();
      final payloadFields = <String, dynamic>{
        'lokasi_id': _selectedLokasi!.id,
        'nama_lokasi': _selectedLokasi!.namaLokasi,
        'nama_teknisi': _namaTeknisiController.text.trim(),
        'waktu_pengecekan': _waktuPengecekan.toIso8601String(),
        'nilai_tekanan': _tekananAir,
        'status_aliran': _aliranIndex == 0 ? 'mengalir' : 'tidak_mengalir',
        'latitude': gpsState.latitude,
        'longitude': gpsState.longitude,
      };

      final queuedLog = QueuedLog(
        idempotencyKey: idempotencyKey,
        payloadFields: payloadFields,
        fotoPath: _fotoPath ?? '',
        endpoint: '/log-tekanan',
      );

      await ref.read(syncControllerProvider.notifier).enqueue(queuedLog);
      // Tunggu proses sync selesai agar bisa tahu apakah langsung terkirim
      await ref.read(syncControllerProvider.notifier).forceSyncNow();

      if (!mounted) return;
      
      final isSuccess = queuedLog.status == QueueStatus.success;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(isSuccess ? 'Berhasil tersimpan ke server!' : 'Tersimpan ke antrian offline (menunggu sinyal)'),
          backgroundColor: isSuccess ? AppColors.statusNormal : Colors.orange,
        ),
      );

      if (isSuccess) {
        // Segarkan data cache lokasi agar data terbaru ditarik dari server
        ref.invalidate(lokasiListProvider);
      }

      setState(() {
        _selectedLokasi = null;
        _tekananAir = 0.0;
        _aliranIndex = 0;
        _waktuPengecekan = DateTime.now();
        _fotoPath = null;
        _isSubmitting = false;
      });
    } catch (e) {
      if (!mounted) return;
      _showError('Gagal menyimpan: $e');
      setState(() => _isSubmitting = false);
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: AppColors.statusKritis,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final dateFormat = DateFormat('dd MMM yyyy, HH:mm', 'id');
    final gpsState = ref.watch(gpsServiceProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Tekanan Air'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Breadcrumb(title: 'Tekanan Air'),
            const SizedBox(height: 16),
            
            // Rekap Banner
            InkWell(
              onTap: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(builder: (_) => const RekapTekananPage()),
                );
              },
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                decoration: BoxDecoration(
                  color: AppColors.accentGreen.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.accentGreen.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.bar_chart, color: AppColors.accentGreen),
                    const SizedBox(width: 12),
                    Text(
                      'Lihat Rekap Tekanan Seluruh Daerah',
                      style: theme.textTheme.bodyMedium?.copyWith(
                        color: AppColors.accentGreen,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                    const Spacer(),
                    const Icon(Icons.chevron_right, color: AppColors.accentGreen),
                  ],
                ),
              ),
            ),
            
            const SizedBox(height: 16),
            const FormHeaderCard(
              title: 'Formulir Pencatatan Tekanan Air',
              subtitle: 'Catat tekanan air di titik ujung jaringan.',
              icon: Icons.water_drop_outlined,
            ),
            const SizedBox(height: 24),
            GpsStatusWidget(
              gpsState: gpsState,
              onRetry: () => ref.read(gpsServiceProvider.notifier).captureLocation(context),
            ),
            const SizedBox(height: 24),

            // Form Card
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(20),
                boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 10, offset: Offset(0, 4))],
              ),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildSectionLabel('Nama Petugas *'),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _namaTeknisiController,
                      decoration: const InputDecoration(hintText: 'Nama lengkap'),
                      validator: (val) => (val == null || val.isEmpty) ? 'Wajib diisi' : null,
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Waktu Pengecekan *'),
                    const SizedBox(height: 8),
                    InkWell(
                      onTap: _pickDateTime,
                      child: Container(
                        height: 56,
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        decoration: BoxDecoration(
                          color: AppColors.surface,
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: AppColors.cardBorder),
                        ),
                        alignment: Alignment.centerLeft,
                        child: Text(
                          dateFormat.format(_waktuPengecekan),
                          style: theme.textTheme.bodyLarge,
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Cari / Tambah Lokasi Daerah *'),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: InkWell(
                            onTap: _showLokasiSearchDialog,
                            child: Container(
                              height: 56,
                              padding: const EdgeInsets.symmetric(horizontal: 16),
                              decoration: BoxDecoration(
                                color: AppColors.surface,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: AppColors.cardBorder),
                              ),
                              child: Row(
                                children: [
                                  const Icon(Icons.search, color: AppColors.textHint),
                                  const SizedBox(width: 8),
                                  Expanded(
                                    child: Text(
                                      _selectedLokasi?.namaLokasi ?? 'Cari lokasi...',
                                      style: theme.textTheme.bodyLarge?.copyWith(
                                        color: _selectedLokasi != null ? AppColors.textPrimary : AppColors.textHint,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        InkWell(
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(builder: (_) => const TambahDaerahPage()),
                            );
                          },
                          child: Container(
                            height: 56,
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            decoration: BoxDecoration(
                              color: AppColors.accentGreen,
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(Icons.add, color: Colors.white),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Tekanan Air (Bar) *'),
                    const SizedBox(height: 8),
                    CounterInput(
                      value: _tekananAir,
                      step: 0.1,
                      onChanged: (val) => setState(() => _tekananAir = val),
                    ),
                    const SizedBox(height: 16),

                    // Warning note
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.statusRendah.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: AppColors.statusRendah.withValues(alpha: 0.3)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.info_outline, color: AppColors.statusRendah, size: 18),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Catatan: Input tekanan hanya untuk rumah paling ujung/terakhir di daerah yang dipilih.',
                              style: theme.textTheme.bodyMedium?.copyWith(
                                color: AppColors.statusRendah,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Status Aliran Air *'),
                    const SizedBox(height: 8),
                    CustomToggleButton(
                      option1Text: 'Mengalir',
                      option1Icon: Icons.water_drop,
                      option1Color: AppColors.accentGreen,
                      option2Text: 'Tidak Mengalir',
                      option2Icon: Icons.do_not_disturb_alt,
                      option2Color: AppColors.statusKritis,
                      selectedIndex: _aliranIndex,
                      onChanged: (idx) => setState(() => _aliranIndex = idx),
                    ),
                    const SizedBox(height: 24),

                    // Button Ambil Foto (karena foto wajib, kita modif dari tombol simpan)
                    OutlinedButton.icon(
                      onPressed: (gpsState is GpsSuccess && _selectedLokasi != null)
                          ? () => _handleAmbilFoto(gpsState)
                          : () {
                              _showError('Pilih GPS dan Lokasi dulu.');
                            },
                      icon: Icon(_fotoPath != null ? Icons.check_circle : Icons.camera_alt),
                      label: Text(_fotoPath != null ? 'Foto Diambil' : 'Ambil Foto Bukti'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: _fotoPath != null ? AppColors.statusNormal : AppColors.accentGreen,
                        side: BorderSide(color: _fotoPath != null ? AppColors.statusNormal : AppColors.accentGreen),
                      ),
                    ),
                    const SizedBox(height: 24),

                    Row(
                      children: [
                        Expanded(
                          flex: 1,
                          child: OutlinedButton.icon(
                            onPressed: () {
                              setState(() {
                                _tekananAir = 0.0;
                                _selectedLokasi = null;
                                _fotoPath = null;
                              });
                            },
                            icon: const Icon(Icons.refresh, color: AppColors.textSecondary),
                            label: const Text('Reset', style: TextStyle(color: AppColors.textSecondary)),
                            style: OutlinedButton.styleFrom(side: const BorderSide(color: AppColors.cardBorder)),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          flex: 2,
                          child: ElevatedButton.icon(
                            onPressed: (_isSubmitting || gpsState is! GpsSuccess) ? null : _handleSubmit,
                            icon: _isSubmitting 
                                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                                : const Icon(Icons.save),
                            label: Text(_isSubmitting ? 'Loading...' : 'Simpan Tekanan'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.accentGreen, // Hijau sesuai mockup
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSectionLabel(String label) {
    return Text(
      label,
      style: Theme.of(context).textTheme.labelMedium?.copyWith(
            fontWeight: FontWeight.w700,
            color: AppColors.textPrimary,
          ),
    );
  }
}
