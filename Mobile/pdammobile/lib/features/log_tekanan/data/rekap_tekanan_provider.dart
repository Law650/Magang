import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../../../core/providers/api_provider.dart';
import '../../../core/providers/technician_provider.dart';

class RekapTekanan {
  final int id;
  final String namaLokasi;
  final double latitude;
  final double longitude;
  final double? nilaiTekanan;
  final String? status;
  final String? statusAliran;
  final String? kekeruhan;
  final String? keterangan;
  final String? waktuPengecekan;
  final String? namaTeknisi;
  final String? fotoEviden;

  RekapTekanan({
    required this.id,
    required this.namaLokasi,
    required this.latitude,
    required this.longitude,
    this.nilaiTekanan,
    this.status,
    this.statusAliran,
    this.kekeruhan,
    this.keterangan,
    this.waktuPengecekan,
    this.namaTeknisi,
    this.fotoEviden,
  });

  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  factory RekapTekanan.fromJson(Map<String, dynamic> json) {
    final latest = json['latest_log'];
    return RekapTekanan(
      id: json['id'],
      namaLokasi: json['nama_lokasi'],
      latitude: _parseDouble(json['latitude']) ?? 0.0,
      longitude: _parseDouble(json['longitude']) ?? 0.0,
      nilaiTekanan: latest != null ? _parseDouble(latest['nilai_tekanan']) : null,
      status: latest?['status'],
      statusAliran: latest?['status_aliran'],
      kekeruhan: latest?['kekeruhan'],
      keterangan: latest?['keterangan'],
      waktuPengecekan: latest?['waktu_pengecekan'],
      namaTeknisi: latest?['nama_teknisi'],
      fotoEviden: latest?['foto_eviden'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nama_lokasi': namaLokasi,
      'latitude': latitude,
      'longitude': longitude,
      'latest_log': {
        'nilai_tekanan': nilaiTekanan,
        'status': status,
        'status_aliran': statusAliran,
        'kekeruhan': kekeruhan,
        'keterangan': keterangan,
        'waktu_pengecekan': waktuPengecekan,
        'nama_teknisi': namaTeknisi,
        'foto_eviden': fotoEviden,
      }
    };
  }
}

final rekapTekananProvider = FutureProvider.autoDispose<List<RekapTekanan>>((ref) async {
  final apiClient = ref.watch(apiClientProvider);
  final prefs = ref.watch(sharedPreferencesProvider);
  
  try {
    final response = await apiClient.dio.get('/log-tekanan/rekap');
    
    if (response.statusCode == 200 && response.data['success'] == true) {
      final List data = response.data['data'];
      final list = data.map((json) => RekapTekanan.fromJson(json)).toList();
      
      final jsonList = list.map((e) => e.toJson()).toList();
      await prefs.setString('cache_rekap_tekanan', jsonEncode(jsonList));
      
      return list;
    }
    return _loadRekapFromCache(prefs);
  } on DioException catch (e) {
    debugPrint('[RekapTekanan] API Error (Offline): ${e.message}');
    return _loadRekapFromCache(prefs);
  } catch (e) {
    debugPrint('[RekapTekanan] Error: $e');
    return _loadRekapFromCache(prefs);
  }
});

List<RekapTekanan> _loadRekapFromCache(SharedPreferences prefs) {
  final cached = prefs.getString('cache_rekap_tekanan');
  if (cached != null) {
    try {
      final List data = jsonDecode(cached);
      return data.map((json) => RekapTekanan.fromJson(json)).toList();
    } catch (e) {
      debugPrint('[RekapTekanan] Cache Parse Error: $e');
    }
  }
  return [];
}
