import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/services/gps_service.dart';
import '../../../core/theme/app_colors.dart';
import '../../../core/widgets/gps_status_widget.dart';
import '../../../core/widgets/ui_components.dart';
import '../../../core/utils/geo_utils.dart';
import '../data/lokasi_repository.dart';

class TambahDaerahPage extends ConsumerStatefulWidget {
  const TambahDaerahPage({super.key});

  @override
  ConsumerState<TambahDaerahPage> createState() => _TambahDaerahPageState();
}

class _TambahDaerahPageState extends ConsumerState<TambahDaerahPage> {
  final _formKey = GlobalKey<FormState>();
  final _noSrController = TextEditingController();
  final _namaPelangganController = TextEditingController();
  final _alamatController = TextEditingController();
  final _desaController = TextEditingController();
  final _latController = TextEditingController();
  final _lngController = TextEditingController();
  bool _isLoading = false;

  @override
  void dispose() {
    _noSrController.dispose();
    _namaPelangganController.dispose();
    _alamatController.dispose();
    _desaController.dispose();
    _latController.dispose();
    _lngController.dispose();
    super.dispose();
  }

  void _simpanData() async {
    if (!_formKey.currentState!.validate()) return;

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
      final repository = ref.read(lokasiRepositoryProvider);
      await repository.tambahDaerahTekanan({
        'no_sr': _noSrController.text.trim(),
        'nama_pelanggan': _namaPelangganController.text.trim(),
        'alamat': _alamatController.text.trim(),
        'desa': _desaController.text.trim(),
        'latitude': lat,
        'longitude': lng,
      });

      // Refresh data
      ref.invalidate(lokasiListProvider);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Daerah Tekanan berhasil ditambahkan!'),
            backgroundColor: AppColors.statusNormal,
          ),
        );
        Navigator.pop(context); // Kembali ke halaman sebelumnya
      }
    } catch (e) {
      if (mounted) {
        String errMsg = e.toString();
        if (errMsg.contains('Duplicate entry') || errMsg.contains('Integrity constraint violation') || errMsg.contains('already has')) {
          errMsg = 'Nomor SR ini sudah ada. Silakan gunakan No SR lain.';
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

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    final gpsState = ref.watch(gpsServiceProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Tambah Daerah Tekanan'),
        centerTitle: true,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: const EdgeInsets.all(24.0),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildSectionLabel('Informasi Pelanggan *'),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _noSrController,
                      decoration: const InputDecoration(
                        labelText: 'Nomor SR',
                        hintText: 'Contoh: 12345678',
                      ),
                      validator: (v) =>
                          v == null || v.trim().isEmpty ? 'Wajib diisi' : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _namaPelangganController,
                      decoration: const InputDecoration(
                        labelText: 'Nama Pelanggan',
                        hintText: 'Contoh: Budi Santoso',
                      ),
                      validator: (v) =>
                          v == null || v.trim().isEmpty ? 'Wajib diisi' : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _desaController,
                      decoration: const InputDecoration(
                        labelText: 'Desa',
                        hintText: 'Contoh: Kedungwuluh',
                      ),
                      validator: (v) =>
                          v == null || v.trim().isEmpty ? 'Wajib diisi' : null,
                    ),
                    const SizedBox(height: 16),
                    TextFormField(
                      controller: _alamatController,
                      decoration: const InputDecoration(
                        labelText: 'Alamat (Opsional)',
                        hintText: 'Detail alamat',
                      ),
                    ),
                    const SizedBox(height: 24),
                    _buildSectionLabel('Koordinat GPS (Opsional)'),
                    const SizedBox(height: 8),
                    GpsStatusWidget(
                      gpsState: gpsState,
                      onRetry: () => ref.read(gpsServiceProvider.notifier).captureLocation(context),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: _latController,
                            keyboardType: const TextInputType.numberWithOptions(
                                decimal: true, signed: true),
                            decoration: const InputDecoration(
                              labelText: 'Latitude',
                              hintText: '-7.xxx',
                            ),
                          ),
                        ),
                        const SizedBox(width: 16),
                        Expanded(
                          child: TextFormField(
                            controller: _lngController,
                            keyboardType: const TextInputType.numberWithOptions(
                                decimal: true, signed: true),
                            decoration: const InputDecoration(
                              labelText: 'Longitude',
                              hintText: '109.xxx',
                            ),
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
                            setState(() {
                              _latController.text =
                                  gpsState.latitude.toString();
                              _lngController.text =
                                  gpsState.longitude.toString();
                            });
                          } else {
                            ScaffoldMessenger.of(context).showSnackBar(
                              const SnackBar(
                                  content: Text(
                                      'GPS belum siap. Tunggu atau pastikan aktif.')),
                            );
                            ref.read(gpsServiceProvider.notifier).captureLocation(context);
                          }
                        },
                        icon: const Icon(Icons.my_location),
                        label: const Text('Gunakan Lokasi Saat Ini'),
                      ),
                    ),
                    const SizedBox(height: 48),
                  ],
                ),
              ),
            ),
      bottomNavigationBar: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: SizedBox(
            width: double.infinity,
            height: 56,
            child: ElevatedButton(
              onPressed: _isLoading ? null : _simpanData,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppColors.accentGreen,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                elevation: 0,
              ),
              child: _isLoading
                  ? const CircularProgressIndicator(color: Colors.white)
                  : const Text('Simpan Daerah', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Colors.white)),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSectionLabel(String text) {
    return Text(
      text,
      style: const TextStyle(
        fontSize: 16,
        fontWeight: FontWeight.bold,
        color: AppColors.textPrimary,
      ),
    );
  }
}
