import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/database_helper.dart';
import '../../../core/providers/api_provider.dart';
import '../../../core/providers/technician_provider.dart';
import '../../../core/services/api_client.dart';

class Lokasi {
  final int id;
  final String namaLokasi;
  final String? noSr;
  final String? namaPelanggan;
  final String? alamat;
  final String? desa;
  final double? latitude;
  final double? longitude;

  const Lokasi({
    required this.id,
    required this.namaLokasi,
    this.noSr,
    this.namaPelanggan,
    this.alamat,
    this.desa,
    this.latitude,
    this.longitude,
  });

  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  factory Lokasi.fromJson(Map<String, dynamic> json) {
    return Lokasi(
      id: json['id'],
      namaLokasi: json['nama_lokasi'],
      noSr: json['no_sr']?.toString(),
      namaPelanggan: json['nama_pelanggan']?.toString(),
      alamat: json['alamat']?.toString(),
      desa: json['desa']?.toString(),
      latitude: _parseDouble(json['latitude']),
      longitude: _parseDouble(json['longitude']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nama_lokasi': namaLokasi,
      'no_sr': noSr,
      'nama_pelanggan': namaPelanggan,
      'alamat': alamat,
      'desa': desa,
      'latitude': latitude,
      'longitude': longitude,
    };
  }

  String get displayLabel => namaLokasi;

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is Lokasi && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;
}

final lokasiRepositoryProvider = Provider<LokasiRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return LokasiRepository(apiClient);
});

final lokasiListProvider = FutureProvider.autoDispose<List<Lokasi>>((ref) async {
  final repository = ref.watch(lokasiRepositoryProvider);
  return repository.fetchLokasi();
});

class LokasiRepository {
  final ApiClient _apiClient;

  LokasiRepository(this._apiClient);

  Future<List<Lokasi>> fetchLokasi() async {
    try {
      final response = await _apiClient.dio.get('/lokasi');
      if (response.data['success'] == true) {
        final List data = response.data['data'];
        final list = data.map((json) => Lokasi.fromJson(json)).toList();
        
        final jsonList = list.map((e) => e.toJson()).toList();
        await DatabaseHelper.instance.insertLokasiBatch(jsonList);
        
        return list;
      }
      return await _loadFromCache();
    } on DioException catch (e) {
      debugPrint('[LokasiRepository] API Error (Offline): ${e.message}');
      return await _loadFromCache();
    } catch (e) {
      debugPrint('[LokasiRepository] Parse Error: $e');
      return await _loadFromCache();
    }
  }

  Future<List<Lokasi>> _loadFromCache() async {
    try {
      final data = await DatabaseHelper.instance.getLokasiList();
      if (data.isNotEmpty) {
        return data.map((json) => Lokasi.fromJson(json)).toList();
      }
    } catch (e) {
      debugPrint('[LokasiRepository] Cache Parse Error: $e');
    }
    // Jika tidak ada cache dan offline, baru kita lempar exception
    throw Exception('Koneksi terputus dan tidak ada data offline');
  }

  Future<void> tambahDaerahTekanan(Map<String, dynamic> data) async {
    try {
      final response = await _apiClient.dio.post('/lokasi-tekanan', data: data);
      if (response.data['success'] != true) {
        throw Exception(response.data['message'] ?? 'Gagal menambah daerah tekanan');
      }
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? e.message;
      throw Exception(msg);
    }
  }
}
