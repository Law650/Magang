import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/services/gps_service.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/gps_status_widget.dart';
import '../../../core/widgets/ui_components.dart';
import '../../../core/utils/geo_utils.dart';
import '../../log_tekanan/data/lokasi_repository.dart';
import '../data/aset_repository.dart';

class TambahAsetPage extends ConsumerStatefulWidget {
  const TambahAsetPage({super.key});

  @override
  ConsumerState<TambahAsetPage> createState() => _TambahAsetPageState();
}

class _TambahAsetPageState extends ConsumerState<TambahAsetPage> {
  final _formKey = GlobalKey<FormState>();
  final _jalurController = TextEditingController();
  final _jenisPipaController = TextEditingController();
  final _latController = TextEditingController();
  final _lngController = TextEditingController();
  double _kapasitasFull = 0.0;
  String _kondisiAwal = 'Full Bukaan';
  double _customTutupan = 0.0;
  bool _isLoading = false;

  @override
  void dispose() {
    _jalurController.dispose();
    _jenisPipaController.dispose();
    _latController.dispose();
    _lngController.dispose();
    super.dispose();
  }

  void _simpanData() async {
    if (!_formKey.currentState!.validate()) return;
    
    if (_kapasitasFull <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kapasitas full bukaan harus lebih besar dari 0'),
          backgroundColor: AppColors.statusKritis,
        ),
      );
      return;
    }

    if (_kondisiAwal == 'Sebagian') {
      if (_customTutupan > _kapasitasFull) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Putaran tertutup tidak boleh melebihi kapasitas full bukaan!'),
            backgroundColor: AppColors.statusKritis,
          ),
        );
        return;
      }
    }

    double? lat;
    if (_latController.text.trim().isNotEmpty) {
      lat = double.tryParse(_latController.text.trim());
      if (lat == null) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Format Latitude tidak valid.')));
        return;
      }
      if (!GeoUtils.isValidLatitude(lat)) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Latitude harus berada antara -90.0 hingga 90.0')));
        return;
      }
    }

    double? lng;
    if (_lngController.text.trim().isNotEmpty) {
      lng = double.tryParse(_lngController.text.trim());
      if (lng == null) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Format Longitude tidak valid.')));
        return;
      }
      if (!GeoUtils.isValidLongitude(lng)) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Longitude harus berada antara -180.0 hingga 180.0')));
        return;
      }
    }

    setState(() => _isLoading = true);

    try {
      final repository = ref.read(asetRepositoryProvider);
      await repository.tambahAsetValve({
        'jalur': _jalurController.text.trim(),
        'jenis_pipa': _jenisPipaController.text.trim(),
        'kapasitas_full': _kapasitasFull,
        'kondisi_awal': _kondisiAwal,
        'custom_tutupan': _customTutupan,
        'latitude': lat,
        'longitude': lng,
      });

      // Refresh data
      ref.invalidate(lokasiListProvider);
      ref.invalidate(asetValveListProvider);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Jalur & Pipa GV berhasil ditambahkan!'),
            backgroundColor: AppColors.statusNormal,
          ),
        );
        Navigator.pop(context); // Kembali ke halaman sebelumnya
      }
    } catch (e) {
      if (mounted) {
        String errMsg = e.toString();
        if (errMsg.contains('Duplicate entry') || errMsg.contains('Integrity constraint violation')) {
          errMsg = 'Jalur atau aset dengan nama ini sudah ada. Silakan gunakan nama lain.';
        } else if (errMsg.startsWith('Exception: ')) {
          errMsg = errMsg.substring(11);
        }

        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errMsg),
            backgroundColor: AppColors.statusKritis,
          ),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Widget _buildSectionLabel(String label) {
    return Text(
      label,
      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: AppColors.textPrimary),
    );
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final gpsState = ref.watch(gpsServiceProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Tambah Jalur / Pipa GV'),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Breadcrumb(title: 'Tambah Master Data'),
            const SizedBox(height: 16),
            const FormHeaderCard(
              title: 'Registrasi Aset Baru',
              subtitle: 'Pastikan Anda berada di titik lokasi yang benar karena aplikasi akan merekam koordinat GPS saat ini secara otomatis.',
              icon: Icons.add_location_alt,
            ),
            const SizedBox(height: 20),

            // GPS Tracker
            GpsStatusWidget(
              gpsState: gpsState,
              onRetry: () => ref.read(gpsServiceProvider.notifier).captureLocation(context),
            ),
            const SizedBox(height: 24),

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
                    _buildSectionLabel('Nama Jalur (Lokasi) *'),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _jalurController,
                      decoration: const InputDecoration(
                        hintText: 'Cth: Jalur Mawar / Lokasi 01',
                        helperText: 'Jika Jalur sudah ada, aplikasi akan otomatis menggabungkannya.',
                      ),
                      validator: (val) => (val == null || val.trim().isEmpty) ? 'Wajib diisi' : null,
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Jenis Pipa GV (Aset) *'),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _jenisPipaController,
                      decoration: const InputDecoration(
                        hintText: 'Cth: GV-99',
                      ),
                      validator: (val) => (val == null || val.trim().isEmpty) ? 'Wajib diisi' : null,
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Kondisi GV Saat Ini *'),
                    const SizedBox(height: 8),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppColors.cardBorder),
                        borderRadius: BorderRadius.circular(12),
                        color: AppColors.background,
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          isExpanded: true,
                          value: _kondisiAwal,
                          items: const [
                            DropdownMenuItem(value: 'Full Bukaan', child: Text('Full Bukaan (100% Terbuka)')),
                            DropdownMenuItem(value: 'Full Tutupan', child: Text('Full Tutupan (100% Tertutup)')),
                            DropdownMenuItem(value: 'Sebagian', child: Text('Sebagian (Ditutup Sebagian)')),
                          ],
                          onChanged: (val) {
                            if (val != null) setState(() => _kondisiAwal = val);
                          },
                        ),
                      ),
                    ),
                    const SizedBox(height: 20),

                    if (_kondisiAwal == 'Sebagian') ...[
                      _buildSectionLabel('Berapa putaran yang tertutup? *'),
                      const SizedBox(height: 8),
                      FractionalCounterInput(
                        value: _customTutupan,
                        onChanged: (val) {
                          setState(() {
                            _customTutupan = val;
                          });
                        },
                      ),
                      const SizedBox(height: 20),
                    ],

                    _buildSectionLabel('Kapasitas Full Bukaan (Putaran) *'),
                    const SizedBox(height: 8),
                    FractionalCounterInput(
                      value: _kapasitasFull,
                      onChanged: (val) {
                        setState(() {
                          _kapasitasFull = val;
                        });
                      },
                    ),
                    const SizedBox(height: 20),

                    _buildSectionLabel('Titik Koordinat (Latitude & Longitude)'),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: _latController,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                            decoration: const InputDecoration(hintText: 'Cth: -6.9090'),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: TextFormField(
                            controller: _lngController,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                            decoration: const InputDecoration(hintText: 'Cth: 109.3816'),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: OutlinedButton.icon(
                        onPressed: () {
                          if (gpsState is GpsSuccess) {
                            _latController.text = gpsState.latitude.toString();
                            _lngController.text = gpsState.longitude.toString();
                          } else {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(content: Text('Sinyal GPS belum stabil atau aktif.')),
                            );
                            ref.read(gpsServiceProvider.notifier).captureLocation(context);
                          }
                        },
                        icon: const Icon(Icons.my_location),
                        label: const Text('Gunakan Lokasi Saat Ini'),
                      ),
                    ),
                    const SizedBox(height: 32),

                    SizedBox(
                      width: double.infinity,
                      height: 56,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : () => _simpanData(),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppColors.accentGreen,
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                          elevation: 0,
                        ),
                        child: _isLoading
                            ? const CircularProgressIndicator(color: Colors.white)
                            : const Text('Simpan Data Aset', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
                      ),
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
}
