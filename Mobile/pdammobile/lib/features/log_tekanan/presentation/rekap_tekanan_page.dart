import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../../core/theme/app_colors.dart';
import '../data/rekap_tekanan_provider.dart';

class RekapTekananPage extends ConsumerStatefulWidget {
  const RekapTekananPage({super.key});

  @override
  ConsumerState<RekapTekananPage> createState() => _RekapTekananPageState();
}

class _RekapTekananPageState extends ConsumerState<RekapTekananPage> {
  String _searchQuery = '';
  final TextEditingController _searchController = TextEditingController();

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _openMaps(BuildContext context, double lat, double lng) async {
    final url = Uri.parse('https://maps.google.com/?q=$lat,$lng');
    try {
      if (await canLaunchUrl(url)) {
        await launchUrl(url, mode: LaunchMode.externalApplication);
      } else {
        await launchUrl(url); 
      }
    } catch (e) {
      if (!context.mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak dapat membuka aplikasi Google Maps')),
      );
    }
  }

  void _showDetailBottomSheet(BuildContext context, RekapTekanan item) {
    final bool hasLog = item.nilaiTekanan != null;
    Color statusColor = AppColors.statusNetral;
    IconData statusIcon = Icons.help_outline;
    
    if (hasLog) {
      final s = item.status?.toLowerCase();
      if (s == 'normal') {
        statusColor = AppColors.statusNormal;
        statusIcon = Icons.check_circle;
      } else if (s == 'rendah') {
        statusColor = AppColors.statusRendah;
        statusIcon = Icons.warning;
      } else if (s == 'kritis') {
        statusColor = AppColors.statusKritis;
        statusIcon = Icons.error;
      }
    }

    String formatWaktu(String? waktu) {
      if (waktu == null) return 'Belum ada data';
      try {
        final dt = DateTime.parse(waktu).toLocal();
        return DateFormat('dd/MM/yyyy HH:mm:ss').format(dt);
      } catch (_) {
        return waktu;
      }
    }

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          padding: EdgeInsets.only(bottom: MediaQuery.of(context).padding.bottom),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: SingleChildScrollView(
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Center(
                    child: Container(
                      width: 48, height: 6,
                      decoration: BoxDecoration(color: Colors.grey[300], borderRadius: BorderRadius.circular(3)),
                    ),
                  ),
                  const SizedBox(height: 24),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      const Expanded(child: Text('Detail Daerah', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: AppColors.primaryDark))),
                      if (hasLog)
                        Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(
                                color: statusColor.withValues(alpha: 0.15),
                                borderRadius: BorderRadius.circular(20),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(statusIcon, size: 14, color: statusColor),
                                  const SizedBox(width: 4),
                                  Text(
                                    item.status!.toUpperCase(),
                                    style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: statusColor),
                                  ),
                                ],
                              ),
                            ),
                            if (item.statusAliran != null) ...[
                              const SizedBox(width: 8),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: (item.statusAliran == 'mengalir' ? Colors.cyan : Colors.red).withValues(alpha: 0.15),
                                  borderRadius: BorderRadius.circular(20),
                                ),
                                child: Text(
                                  item.statusAliran == 'mengalir' ? 'MENGALIR' : 'TIDAK MENGALIR',
                                  style: TextStyle(
                                    fontSize: 11, 
                                    fontWeight: FontWeight.bold, 
                                    color: item.statusAliran == 'mengalir' ? Colors.cyan : Colors.red
                                  ),
                                ),
                              ),
                            ],
                          ],
                        ),
                    ],
                  ),
                  const SizedBox(height: 24),
                  
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.background,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: AppColors.cardBorder),
                    ),
                    child: Column(
                      children: [
                        _buildDetailRow('Nama Daerah', item.namaLokasi),
                        if (hasLog) ...[
                          _buildDetailRow('Tekanan Terakhir', '${item.nilaiTekanan} Bar'),
                          if (item.statusAliran != null)
                            _buildDetailRow('Aliran', item.statusAliran!.toUpperCase()),
                          if (item.kekeruhan != null)
                            _buildDetailRow('Kekeruhan', item.kekeruhan!),
                          _buildDetailRow('Waktu Pengecekan', formatWaktu(item.waktuPengecekan)),
                          _buildDetailRow('Nama Petugas', item.namaTeknisi ?? '-'),
                          if (item.keterangan != null)
                            _buildDetailRow('Keterangan', item.keterangan!),
                        ] else ...[
                          _buildDetailRow('Status', 'Belum Pernah Dicek'),
                        ]
                      ],
                    ),
                  ),
                  
                  if (item.fotoEviden != null && item.fotoEviden!.isNotEmpty) ...[
                    const SizedBox(height: 24),
                    const Text('Bukti Foto', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                    const SizedBox(height: 12),
                    ClipRRect(
                      borderRadius: BorderRadius.circular(16),
                      child: Image.network(
                        item.fotoEviden!,
                        headers: const {'ngrok-skip-browser-warning': '69420'},
                        width: double.infinity,
                        height: 200,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => Container(
                          width: double.infinity,
                          height: 200,
                          color: Colors.grey.withValues(alpha: 0.1),
                          child: const Center(child: Icon(Icons.broken_image, color: Colors.grey, size: 40)),
                        ),
                      ),
                    ),
                  ],

                  const SizedBox(height: 24),
                  
                  const Text('Lokasi Koordinat', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
                  const SizedBox(height: 12),
                  
                  InkWell(
                    onTap: () {
                      Navigator.pop(context);
                      _openMaps(context, item.latitude, item.longitude);
                    },
                    borderRadius: BorderRadius.circular(16),
                    child: Container(
                      height: 120,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: Colors.blue.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: Colors.blue.withValues(alpha: 0.3)),
                        image: DecorationImage(
                          image: NetworkImage(
                            item.latitude != 0.0 && item.longitude != 0.0 
                            ? 'https://maps.googleapis.com/maps/api/staticmap?center=${item.latitude},${item.longitude}&zoom=15&size=400x200&maptype=roadmap&markers=color:red%7C${item.latitude},${item.longitude}'
                            : 'https://maps.googleapis.com/maps/api/staticmap?center=-7.983908,112.621391&zoom=15&size=400x200&maptype=roadmap',
                          ),
                          fit: BoxFit.cover,
                          opacity: 0.5,
                        ),
                      ),
                      child: Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(Icons.location_on, color: Colors.red, size: 40),
                            const SizedBox(height: 4),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(20),
                                boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 4)],
                              ),
                              child: Text('${item.latitude}, ${item.longitude}', style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 12)),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                  
                  const SizedBox(height: 24),
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        _openMaps(context, item.latitude, item.longitude);
                      },
                      icon: const Icon(Icons.map),
                      label: const Text('Buka di Google Maps'),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: Colors.white,
                        minimumSize: const Size(double.infinity, 56),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }

  Widget _buildDetailRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 2,
            child: Text(label, style: const TextStyle(color: AppColors.textSecondary, fontSize: 14)),
          ),
          const SizedBox(width: 8),
          Expanded(
            flex: 3,
            child: Text(value, style: const TextStyle(color: AppColors.textPrimary, fontSize: 14, fontWeight: FontWeight.bold)),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final rekapAsyncValue = ref.watch(rekapTekananProvider);

    return Scaffold(
      backgroundColor: AppColors.background,
      appBar: AppBar(
        title: const Text('Rekap Tekanan Daerah'),
        backgroundColor: AppColors.primaryDark,
        foregroundColor: Colors.white,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: () {
              ref.refresh(rekapTekananProvider);
              _searchController.clear();
              setState(() => _searchQuery = '');
            },
          ),
        ],
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(60),
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 12),
            child: TextField(
              controller: _searchController,
              onChanged: (val) {
                setState(() {
                  _searchQuery = val.toLowerCase();
                });
              },
              decoration: InputDecoration(
                hintText: 'Cari nama daerah...',
                fillColor: Colors.white,
                filled: true,
                prefixIcon: const Icon(Icons.search, color: AppColors.textSecondary),
                suffixIcon: _searchQuery.isNotEmpty 
                  ? IconButton(
                      icon: const Icon(Icons.clear, color: AppColors.textSecondary),
                      onPressed: () {
                        _searchController.clear();
                        setState(() => _searchQuery = '');
                      },
                    )
                  : null,
                contentPadding: const EdgeInsets.symmetric(vertical: 0, horizontal: 16),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
              ),
            ),
          ),
        ),
      ),
      body: rekapAsyncValue.when(
        data: (list) {
          if (list.isEmpty) {
            return const Center(child: Text('Belum ada data lokasi'));
          }
          
          final filteredList = list.where((item) {
            return item.namaLokasi.toLowerCase().contains(_searchQuery);
          }).toList();
          
          if (filteredList.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.search_off, size: 48, color: AppColors.textSecondary),
                  const SizedBox(height: 16),
                  Text('Tidak menemukan daerah\nuntuk pencarian "$_searchQuery"', textAlign: TextAlign.center, style: const TextStyle(color: AppColors.textSecondary)),
                ],
              ),
            );
          }
          
          return RefreshIndicator(
            onRefresh: () async {
              ref.refresh(rekapTekananProvider);
              await ref.read(rekapTekananProvider.future);
            },
            color: AppColors.accentGreen,
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: filteredList.length,
              itemBuilder: (context, index) {
                final item = filteredList[index];
                final bool hasLog = item.nilaiTekanan != null;
                
                Color statusColor = AppColors.statusNetral;
                IconData statusIcon = Icons.help_outline;
                
                if (hasLog) {
                  final s = item.status?.toLowerCase();
                  if (s == 'normal') {
                    statusColor = AppColors.statusNormal;
                    statusIcon = Icons.check_circle;
                  } else if (s == 'rendah') {
                    statusColor = AppColors.statusRendah;
                    statusIcon = Icons.warning;
                  } else if (s == 'kritis') {
                    statusColor = AppColors.statusKritis;
                    statusIcon = Icons.error;
                  }
                }

                String formatWaktu(String? waktu) {
                  if (waktu == null) return 'Belum ada data';
                  try {
                    final dt = DateTime.parse(waktu).toLocal();
                    return DateFormat('dd/MM/yyyy HH:mm').format(dt);
                  } catch (_) {
                    return waktu;
                  }
                }

                return Card(
                  elevation: 2,
                  margin: const EdgeInsets.only(bottom: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  clipBehavior: Clip.antiAlias,
                  child: InkWell(
                    onTap: () => _showDetailBottomSheet(context, item),
                    child: Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Colors.white, statusColor.withValues(alpha: 0.05)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        border: Border.all(color: statusColor.withValues(alpha: 0.2), width: 1),
                      ),
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Expanded(
                                child: Text(
                                  item.namaLokasi,
                                  style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.textPrimary),
                                ),
                              ),
                              Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                    decoration: BoxDecoration(
                                      color: statusColor.withValues(alpha: 0.1),
                                      borderRadius: BorderRadius.circular(20),
                                    ),
                                    child: Row(
                                      mainAxisSize: MainAxisSize.min,
                                      children: [
                                        Icon(statusIcon, size: 16, color: statusColor),
                                        const SizedBox(width: 4),
                                        Text(
                                          item.status?.toUpperCase() ?? 'BELUM ADA DATA',
                                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: statusColor),
                                        ),
                                      ],
                                    ),
                                  ),
                                  if (item.statusAliran != null) ...[
                                    const SizedBox(width: 6),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                      decoration: BoxDecoration(
                                        color: (item.statusAliran == 'mengalir' ? Colors.cyan : Colors.red).withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(20),
                                      ),
                                      child: Text(
                                        item.statusAliran == 'mengalir' ? 'MENGALIR' : 'TIDAK MENGALIR',
                                        style: TextStyle(
                                          fontSize: 10, 
                                          fontWeight: FontWeight.bold, 
                                          color: item.statusAliran == 'mengalir' ? Colors.cyan : Colors.red
                                        ),
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ],
                          ),
                          const SizedBox(height: 16),
                          Row(
                            children: [
                              _buildInfoItem(
                                icon: Icons.speed,
                                label: 'Tekanan',
                                value: hasLog ? '${item.nilaiTekanan} Bar' : '-',
                                color: statusColor,
                              ),
                              Container(width: 1, height: 30, color: Colors.grey[300], margin: const EdgeInsets.symmetric(horizontal: 16)),
                              _buildInfoItem(
                                icon: Icons.access_time,
                                label: 'Terakhir Dicek',
                                value: formatWaktu(item.waktuPengecekan),
                                color: AppColors.textSecondary,
                              ),
                            ],
                          ),
                          if (hasLog && item.namaTeknisi != null) ...[
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                const Icon(Icons.person_outline, size: 16, color: AppColors.textSecondary),
                                const SizedBox(width: 4),
                                Text('Petugas: ${item.namaTeknisi}', style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                              ],
                            ),
                          ]
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
          );
        },
        loading: () => const Center(child: CircularProgressIndicator(color: AppColors.accentGreen)),
        error: (err, stack) => Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.error_outline, size: 48, color: Colors.red),
              const SizedBox(height: 16),
              Text('Terjadi kesalahan:\n$err', textAlign: TextAlign.center),
              const SizedBox(height: 16),
              ElevatedButton(
                onPressed: () => ref.refresh(rekapTekananProvider),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoItem({required IconData icon, required String label, required String value, required Color color}) {
    return Expanded(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 20, color: color),
          const SizedBox(width: 8),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(fontSize: 12, color: AppColors.textSecondary)),
                Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: AppColors.textPrimary)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
