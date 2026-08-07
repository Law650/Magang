import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/services/database_helper.dart';
import '../../../core/providers/api_provider.dart';
import '../../../core/providers/technician_provider.dart';
import '../../../core/services/api_client.dart';

class AsetValve {
  final int id;
  final String namaAset;
  final String namaLokasi;
  final double kapasitasFullPutaran;
  final double? sisaBukaan;
  final double? persentaseBukaan;
  final double? latitude;
  final double? longitude;
  final String? namaTeknisi;
  final String? keterangan;
  final String? fotoEviden;
  final String? fotoEviden2;

  const AsetValve({
    required this.id,
    required this.namaAset,
    required this.namaLokasi,
    required this.kapasitasFullPutaran,
    this.sisaBukaan,
    this.persentaseBukaan,
    this.latitude,
    this.longitude,
    this.namaTeknisi,
    this.keterangan,
    this.fotoEviden,
    this.fotoEviden2,
  });

  static double? _parseDouble(dynamic value) {
    if (value == null) return null;
    if (value is num) return value.toDouble();
    if (value is String) return double.tryParse(value);
    return null;
  }

  factory AsetValve.fromJson(Map<String, dynamic> json) {
    return AsetValve(
      id: json['id'],
      namaAset: json['nama_aset'],
      namaLokasi: json['nama_lokasi'],
      kapasitasFullPutaran: _parseDouble(json['kapasitas_full_putaran']) ?? 0.0,
      sisaBukaan: _parseDouble(json['sisa_bukaan']),
      persentaseBukaan: _parseDouble(json['persentase_bukaan']),
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      namaTeknisi: json['nama_teknisi'] as String?,
      keterangan: json['keterangan'] as String?,
      fotoEviden: json['foto_eviden'] as String?,
      fotoEviden2: json['foto_eviden_2'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nama_aset': namaAset,
      'nama_lokasi': namaLokasi,
      'kapasitas_full_putaran': kapasitasFullPutaran,
      'sisa_bukaan': sisaBukaan,
      'persentase_bukaan': persentaseBukaan,
      'latitude': latitude,
      'longitude': longitude,
      'nama_teknisi': namaTeknisi,
      'keterangan': keterangan,
      'foto_eviden': fotoEviden,
      'foto_eviden_2': fotoEviden2,
    };
  }

  String get displayLabel => '$namaAset — $namaLokasi';

  @override
  bool operator ==(Object other) {
    if (identical(this, other)) return true;
    return other is AsetValve && other.id == id;
  }

  @override
  int get hashCode => id.hashCode;
}

final asetRepositoryProvider = Provider<AsetRepository>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return AsetRepository(apiClient);
});

final asetValveListProvider = FutureProvider<List<AsetValve>>((ref) async {
  final repository = ref.watch(asetRepositoryProvider);
  return repository.fetchAsetValves();
});

class AsetRepository {
  final ApiClient _apiClient;

  AsetRepository(this._apiClient);

  Future<List<AsetValve>> fetchAsetValves() async {
    try {
      final response = await _apiClient.dio.get('/aset-valve');
      if (response.data['success'] == true) {
        final List data = response.data['data'];
        final list = data.map((json) => AsetValve.fromJson(json)).toList();
        
        // Simpan ke cache lokal
        final jsonList = list.map((e) => e.toJson()).toList();
        await DatabaseHelper.instance.insertAsetValveBatch(jsonList);
        
        return list;
      }
      return await _loadFromCache();
    } on DioException catch (e) {
      debugPrint('[AsetRepository] API Error (Offline): ${e.message}');
      return await _loadFromCache();
    } catch (e) {
      debugPrint('[AsetRepository] Parse Error: $e');
      return await _loadFromCache();
    }
  }

  Future<List<AsetValve>> _loadFromCache() async {
    try {
      final data = await DatabaseHelper.instance.getAsetValveList();
      if (data.isNotEmpty) {
        return data.map((json) => AsetValve.fromJson(json)).toList();
      }
    } catch (e) {
      debugPrint('[AsetRepository] Cache Parse Error: $e');
    }
    return [];
  }

  Future<void> tambahAsetValve(Map<String, dynamic> data) async {
    try {
      final response = await _apiClient.dio.post('/aset-valve', data: data);
      if (response.data['success'] != true) {
        throw Exception(response.data['message'] ?? 'Gagal menambahkan aset');
      }
    } on DioException catch (e) {
      if (e.response != null && e.response!.data != null && e.response!.data['message'] != null) {
        throw Exception(e.response!.data['message']);
      }
      throw Exception('Koneksi terputus: ${e.message}');
    }
  }
}
