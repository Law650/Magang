import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:uuid/uuid.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/providers/technician_provider.dart';
import '../../../core/services/gps_service.dart';
import '../../../core/services/sync_controller.dart';
import '../../../core/models/queued_log.dart';
import '../../../core/widgets/gps_status_widget.dart';
import '../../../core/widgets/ui_components.dart';
import '../../../core/widgets/searchable_bottom_sheet.dart';
import '../../../core/utils/watermark_utils.dart';
import '../../camera/presentation/strict_camera_page.dart';
import '../../camera/presentation/preview_watermark_page.dart';
import '../../log_tekanan/data/lokasi_repository.dart';
import '../data/aset_repository.dart';
import 'tambah_aset_page.dart';

class LogValveFormPage extends ConsumerStatefulWidget {
  const LogValveFormPage({super.key});

  @override
  ConsumerState<LogValveFormPage> createState() => _LogValveFormPageState();
}

class _LogValveFormPageState extends ConsumerState<LogValveFormPage> {
  final _formKey = GlobalKey<FormState>();
  final _namaTeknisiController = TextEditingController();
  final _keteranganController = TextEditingController();

  String? _selectedNamaLokasi;
  AsetValve? _selectedAset;
  double _kapasitasFull = 0.0;
  double _bukaanSaatIni = 0.0;
  int _aksiKerjaIndex = 0; // 0: Buka, 1: Tutup
  DateTime _waktuKegiatan = DateTime.now();
  String? _fotoPath;
  bool _isSubmitting = false;

  double _jumlahPutaran = 0.0;

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
    _keteranganController.dispose();
    super.dispose();
  }

  void _validateActionToggle() {
    if (_aksiKerjaIndex == 0 && _bukaanSaatIni >= _kapasitasFull) {
      setState(() => _aksiKerjaIndex = 1);
    } else if (_aksiKerjaIndex == 1 && _bukaanSaatIni <= 0) {
      setState(() => _aksiKerjaIndex = 0);
    }
  }

  void _calculateBukaanSaatIni() {
    if (_selectedAset == null) {
      _bukaanSaatIni = 0.0;
      _validateActionToggle();
      return;
    }

    final logsBox = ref.read(queuedLogsBoxProvider);
    final valveLogs = logsBox.values.where((log) => 
      log.endpoint == '/log-valve' && 
      log.payloadFields['aset_id'] == _selectedAset!.id
    ).toList();
    
    if (valveLogs.isEmpty) {
      // Tidak ada histori lokal, ikuti kapasitas full
      _bukaanSaatIni = _kapasitasFull;
    } else {
      // Urutkan berdasarkan waktu_kegiatan (terbaru di atas)
      valveLogs.sort((a, b) {
        final timeA = DateTime.parse(a.payloadFields['waktu_kegiatan']);
        final timeB = DateTime.parse(b.payloadFields['waktu_kegiatan']);
        return timeB.compareTo(timeA);
      });
      
      final lastLog = valveLogs.first.payloadFields;
      // Gunakan perhitungan: Bukaan terakhir + jumlah buka ATAU - jumlah tutup
      final lastBukaan = (lastLog['bukaan_saat_ini'] as num?)?.toDouble() ?? 0.0;
      final lastAksi = lastLog['aksi_kerja'] as String? ?? 'Buka';
      final lastJumlah = (lastLog['jumlah_putaran'] as num?)?.toDouble() ?? 0.0;
      
      double newBukaan = lastAksi == 'Buka' 
          ? lastBukaan + lastJumlah 
          : lastBukaan - lastJumlah;
          
      _bukaanSaatIni = newBukaan.clamp(0.0, _kapasitasFull);
    }
    _validateActionToggle();
  }

  Future<void> _pickDateTime() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _waktuKegiatan,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 1)),
    );
    if (date == null || !mounted) return;

    final time = await showTimePicker(
      context: context,
      initialTime: TimeOfDay.fromDateTime(_waktuKegiatan),
    );
    if (time == null || !mounted) return;

    setState(() {
      _waktuKegiatan = DateTime(
        date.year,
        date.month,
        date.day,
        time.hour,
        time.minute,
      );
    });
  }

  Future<void> _handleAmbilFoto(GpsSuccess gps) async {
    final Uint8List? rawBytes = await Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => const StrictCameraPage()),
    );
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
      namaLokasiAset: _selectedAset!.namaLokasi,
      sumberFoto: 'Kamera Langsung',
    );

    final watermarkedBytes = await processWatermarkInIsolate(input);

    if (!mounted) return;
    Navigator.pop(context); 

    String? action = await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => PreviewWatermarkPage(
          watermarkedBytes: watermarkedBytes,
          formType: 'log_valve',
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
    if (_selectedAset == null) {
      _showError('Pilih Aset Valve terlebih dahulu');
      return;
    }
    if (_fotoPath == null) {
      _showError('Foto bukti wajib diambil');
      return;
    }
    if (_jumlahPutaran <= 0) {
      _showError('Jumlah putaran harus lebih dari 0');
      return;
    }

    final gpsState = ref.read(gpsServiceProvider);
    if (gpsState is! GpsSuccess) return;

    setState(() => _isSubmitting = true);

    try {
      final idempotencyKey = const Uuid().v4();
      final payloadFields = <String, dynamic>{
        'aset_id': _selectedAset!.id,
        'nama_aset': _selectedAset!.namaAset,
        'nama_lokasi': _selectedAset!.namaLokasi,
        'nama_teknisi': _namaTeknisiController.text.trim(),
        'waktu_kegiatan': _waktuKegiatan.toIso8601String(),
        'kapasitas_full': _kapasitasFull,
        'bukaan_saat_ini': _bukaanSaatIni,
        'aksi_kerja': _aksiKerjaIndex == 0 ? 'Buka' : 'Tutup',
        'jumlah_putaran': _jumlahPutaran,
        'keterangan': _keteranganController.text.trim(),
        'latitude': gpsState.latitude,
        'longitude': gpsState.longitude,
      };

      final queuedLog = QueuedLog(
        idempotencyKey: idempotencyKey,
        payloadFields: payloadFields,
        fotoPath: _fotoPath ?? '',
        endpoint: '/log-valve',
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
        // Segarkan data cache aset agar bukaan valve terbaru ditarik dari server
        ref.invalidate(asetValveListProvider);
      }

      setState(() {
        _selectedNamaLokasi = null;
        _selectedAset = null;
        _kapasitasFull = 0.0;
        _bukaanSaatIni = 0.0;
        _aksiKerjaIndex = 0;
        _jumlahPutaran = 0.0;
        _keteranganController.clear();
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
    final lokasiAsync = ref.watch(lokasiListProvider);
    final asetAsync = ref.watch(asetValveListProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Gate Valve'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Breadcrumb(title: 'Gate Valve'),
            const SizedBox(height: 16),
            const FormHeaderCard(
              title: 'Formulir Gate Valve',
              subtitle: 'Catat setiap perubahan posisi valve secara akurat.',
              icon: Icons.settings_input_component,
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
                    _buildSectionLabel('Nama Petugas Bertugas *'),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _namaTeknisiController,
                      decoration: const InputDecoration(hintText: 'Nama lengkap'),
                      validator: (val) => (val == null || val.isEmpty) ? 'Wajib diisi' : null,
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Waktu Kegiatan Lapangan *'),
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
                          dateFormat.format(_waktuKegiatan),
                          style: theme.textTheme.bodyLarge,
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    Row(
                      children: [
                        _buildSectionLabel('Pilih Jalur *'),
                        const Spacer(),
                        TextButton.icon(
                          onPressed: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(builder: (_) => const TambahAsetPage()),
                            );
                          },
                          icon: const Icon(Icons.add, size: 16),
                          label: const Text('Tambah Baru'),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.cardBorder),
                      ),
                      child: asetAsync.when(
                        data: (asetList) {
                          final uniqueJalur = asetList.map((a) => a.namaLokasi).toSet().toList();
                          return InkWell(
                            onTap: () async {
                              final selected = await SearchableBottomSheet.show<String>(
                                context: context,
                                title: 'Pilih Jalur',
                                items: uniqueJalur,
                                itemAsString: (l) => l,
                              );
                              if (selected != null) {
                                setState(() {
                                  _selectedNamaLokasi = selected;
                                  _selectedAset = null; // Reset aset jika lokasi berubah
                                });
                              }
                            },
                            child: Container(
                              height: 56,
                              alignment: Alignment.centerLeft,
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      _selectedNamaLokasi ?? 'Pilih Jalur',
                                      style: theme.textTheme.bodyLarge?.copyWith(
                                        color: _selectedNamaLokasi != null ? AppColors.textPrimary : AppColors.textHint,
                                      ),
                                    ),
                                  ),
                                  const Icon(Icons.arrow_drop_down, color: AppColors.textHint),
                                ],
                              ),
                            ),
                          );
                        },
                        loading: () => const Center(child: CircularProgressIndicator()),
                        error: (_, __) => const Text('Gagal memuat'),
                      ),
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Pilih Jenis Pipa GV *'),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 16),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.cardBorder),
                      ),
                      child: asetAsync.when(
                        data: (asetList) {
                          final availableAsets = _selectedNamaLokasi == null 
                              ? asetList 
                              : asetList.where((e) => e.namaLokasi == _selectedNamaLokasi).toList();
                          return InkWell(
                            onTap: () async {
                              final selected = await SearchableBottomSheet.show<AsetValve>(
                                context: context,
                                title: 'Pilih Jenis Pipa GV',
                                items: availableAsets,
                                itemAsString: (a) => a.namaAset,
                              );
                              if (selected != null) {
                                setState(() {
                                  _selectedAset = selected;
                                  _kapasitasFull = selected.kapasitasFullPutaran;
                                  _calculateBukaanSaatIni();
                                });
                              }
                            },
                            child: Container(
                              height: 56,
                              alignment: Alignment.centerLeft,
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      _selectedAset?.namaAset ?? 'Pilih Jenis Pipa GV',
                                      style: theme.textTheme.bodyLarge?.copyWith(
                                        color: _selectedAset != null ? AppColors.textPrimary : AppColors.textHint,
                                      ),
                                    ),
                                  ),
                                  const Icon(Icons.arrow_drop_down, color: AppColors.textHint),
                                ],
                              ),
                            ),
                          );
                        },
                        loading: () => const Center(child: CircularProgressIndicator()),
                        error: (_, __) => const Text('Gagal memuat'),
                      ),
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Kapasitas Full Bukaan (Putaran) *'),
                    const SizedBox(height: 8),
                    FractionalCounterInput(
                      value: _kapasitasFull,
                      readOnly: true,
                      onChanged: (val) {
                        setState(() {
                          _kapasitasFull = val;
                          _calculateBukaanSaatIni();
                        });
                      },
                    ),
                    const SizedBox(height: 4),
                    Center(
                      child: Text(
                        'Mendukung desimal · Auto-isi dari data valve (dapat diubah manual)',
                        style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint, fontSize: 11),
                      ),
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Bukaan Saat Ini (Putaran) *'),
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      height: 56,
                      decoration: BoxDecoration(
                        color: AppColors.disabled,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.cardBorder),
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        _bukaanSaatIni == _bukaanSaatIni.toInt() 
                            ? _bukaanSaatIni.toInt().toString() 
                            : _bukaanSaatIni.toString(),
                        style: const TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w600,
                          color: AppColors.textSecondary,
                        ),
                      ),
                    ),
                    const SizedBox(height: 4),
                    Center(
                      child: Text(
                        'Dihitung otomatis dari riwayat (Read-only)',
                        style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint, fontSize: 11),
                      ),
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Aksi Lapangan *'),
                    const SizedBox(height: 8),
                    CustomToggleButton(
                      option1Text: 'Buka',
                      option1Icon: Icons.arrow_upward,
                      option1Color: AppColors.accentGreen,
                      option2Text: 'Tutup',
                      option2Icon: Icons.arrow_downward,
                      option2Color: AppColors.statusKritis,
                      selectedIndex: _aksiKerjaIndex,
                      disableOption1: _bukaanSaatIni >= _kapasitasFull && _kapasitasFull > 0,
                      disableOption2: _bukaanSaatIni <= 0,
                      onChanged: (idx) => setState(() => _aksiKerjaIndex = idx),
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Jumlah Putaran Saat Ini *'),
                    const SizedBox(height: 8),
                    FractionalCounterInput(
                      value: _jumlahPutaran,
                      onChanged: (val) => setState(() => _jumlahPutaran = val),
                    ),
                    const SizedBox(height: 4),
                    Center(
                      child: Text(
                        'Mendukung desimal (Contoh: 1/4 putaran = 0.25, 1/2 putaran = 0.5, 3/4 putaran = 0.75. 1 putaran = 1.0)',
                        style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint),
                      ),
                    ),
                    const SizedBox(height: 20),

                    Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Expanded(child: _buildSectionLabel('Estimasi Total Akumulasi Putaran')),
                        Text(' (Otomatis dihitung)', style: theme.textTheme.bodySmall?.copyWith(color: AppColors.textHint, fontSize: 10)),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
                      decoration: BoxDecoration(
                        color: AppColors.background,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.cardBorder),
                      ),
                      alignment: Alignment.center,
                      child: Text(
                        _selectedAset != null && _jumlahPutaran > 0 
                            ? 'Sisa Bukaan: ${(_bukaanSaatIni + (_aksiKerjaIndex == 0 ? _jumlahPutaran : -_jumlahPutaran)).clamp(0.0, _kapasitasFull)} Putaran' 
                            : '— Pilih valve & masukkan putaran —',
                        style: theme.textTheme.bodyMedium?.copyWith(
                          color: _selectedAset != null && _jumlahPutaran > 0 ? AppColors.textPrimary : AppColors.textHint,
                          fontWeight: _selectedAset != null && _jumlahPutaran > 0 ? FontWeight.bold : FontWeight.normal,
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Keterangan / Catatan Kondisi (Opsional)'),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _keteranganController,
                      maxLines: 3,
                      decoration: const InputDecoration(
                        hintText: 'Contoh: Valve sedikit berkarat...',
                      ),
                    ),
                    const SizedBox(height: 24),

                    // Button Ambil Foto (karena foto wajib, kita modif dari tombol simpan)
                    OutlinedButton.icon(
                      onPressed: (gpsState is GpsSuccess && _selectedAset != null)
                          ? () => _handleAmbilFoto(gpsState)
                          : () {
                              _showError('Pilih GPS dan Aset Valve dulu.');
                            },
                      icon: Icon(_fotoPath != null ? Icons.check_circle : Icons.camera_alt),
                      label: Text(_fotoPath != null ? 'Foto Diambil' : 'Ambil Foto Bukti'),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: _fotoPath != null ? AppColors.statusNormal : AppColors.primary,
                        side: BorderSide(color: _fotoPath != null ? AppColors.statusNormal : AppColors.primary),
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
                                _kapasitasFull = 0.0;
                                _jumlahPutaran = 0.0;
                                _selectedAset = null;
                                _keteranganController.clear();
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
                            label: Text(_isSubmitting ? 'Loading...' : 'Simpan Log Valve'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: AppColors.primary,
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
