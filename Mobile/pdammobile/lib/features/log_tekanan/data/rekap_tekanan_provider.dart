import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/database_helper.dart';
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
  final String? noSr;
  final String? namaPelanggan;
  final String? alamat;
  final String? desa;
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
    this.noSr,
    this.namaPelanggan,
    this.alamat,
    this.desa,
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
      noSr: json['no_sr']?.toString() ?? latest?['no_sr'],
      namaPelanggan: json['nama_pelanggan']?.toString(),
      alamat: json['alamat']?.toString(),
      desa: json['desa']?.toString(),
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
      'no_sr': noSr,
      'nama_pelanggan': namaPelanggan,
      'alamat': alamat,
      'desa': desa,
      'latest_log': {
        'nilai_tekanan': nilaiTekanan,
        'status': status,
        'status_aliran': statusAliran,
        'kekeruhan': kekeruhan,
        'keterangan': keterangan,
        'no_sr': noSr, // Dipertahankan di dalam latest_log untuk kompatibilitas mundur
        'waktu_pengecekan': waktuPengecekan,
        'nama_teknisi': namaTeknisi,
        'foto_eviden': fotoEviden,
      }
    };
  }

  Map<String, dynamic> toSqlite() {
    return {
      'id': id,
      'nama_lokasi': namaLokasi,
      'latitude': latitude,
      'longitude': longitude,
      'nilai_tekanan': nilaiTekanan,
      'status': status,
      'status_aliran': statusAliran,
      'kekeruhan': kekeruhan,
      'keterangan': keterangan,
      'no_sr': noSr,
      'nama_pelanggan': namaPelanggan,
      'alamat': alamat,
      'desa': desa,
      'waktu_pengecekan': waktuPengecekan,
      'nama_teknisi': namaTeknisi,
      'foto_eviden': fotoEviden,
    };
  }

  factory RekapTekanan.fromSqlite(Map<String, dynamic> map) {
    return RekapTekanan(
      id: map['id'],
      namaLokasi: map['nama_lokasi'],
      latitude: _parseDouble(map['latitude']) ?? 0.0,
      longitude: _parseDouble(map['longitude']) ?? 0.0,
      nilaiTekanan: _parseDouble(map['nilai_tekanan']),
      status: map['status'],
      statusAliran: map['status_aliran'],
      kekeruhan: map['kekeruhan'],
      keterangan: map['keterangan'],
      noSr: map['no_sr']?.toString(),
      namaPelanggan: map['nama_pelanggan']?.toString(),
      alamat: map['alamat']?.toString(),
      desa: map['desa']?.toString(),
      waktuPengecekan: map['waktu_pengecekan'],
      namaTeknisi: map['nama_teknisi'],
      fotoEviden: map['foto_eviden'],
    );
  }
}

final rekapTekananProvider = FutureProvider.autoDispose<List<RekapTekanan>>((ref) async {
  final apiClient = ref.watch(apiClientProvider);
  
  try {
    final response = await apiClient.dio.get('/log-tekanan/rekap');
    
    if (response.statusCode == 200 && response.data['success'] == true) {
      final List data = response.data['data'];
      final list = data.map((json) => RekapTekanan.fromJson(json)).toList();
      
      final sqliteList = list.map((e) => e.toSqlite()).toList();
      await DatabaseHelper.instance.insertRekapTekananBatch(sqliteList);
      
      return list;
    }
    return await _loadRekapFromCache();
  } on DioException catch (e) {
    debugPrint('[RekapTekanan] API Error (Offline): ${e.message}');
    return await _loadRekapFromCache();
  } catch (e) {
    debugPrint('[RekapTekanan] Error: $e');
    return await _loadRekapFromCache();
  }
});

Future<List<RekapTekanan>> _loadRekapFromCache() async {
  try {
    final data = await DatabaseHelper.instance.getRekapTekananList();
    if (data.isNotEmpty) {
      return data.map((map) => RekapTekanan.fromSqlite(map)).toList();
    }
  } catch (e) {
    debugPrint('[RekapTekanan] Cache Parse Error: $e');
  }
  return [];
}
