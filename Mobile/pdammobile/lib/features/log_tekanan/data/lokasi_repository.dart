import 'dart:convert';
import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../../../core/providers/api_provider.dart';
import '../../../core/providers/technician_provider.dart';
import '../../../core/services/api_client.dart';

class Lokasi {
  final int id;
  final String namaLokasi;
  final double? latitude;
  final double? longitude;

  const Lokasi({
    required this.id,
    required this.namaLokasi,
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
      latitude: _parseDouble(json['latitude']),
      longitude: _parseDouble(json['longitude']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nama_lokasi': namaLokasi,
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
  final prefs = ref.watch(sharedPreferencesProvider);
  return LokasiRepository(apiClient, prefs);
});

final lokasiListProvider = FutureProvider.autoDispose<List<Lokasi>>((ref) async {
  final repository = ref.watch(lokasiRepositoryProvider);
  return repository.fetchLokasi();
});

class LokasiRepository {
  final ApiClient _apiClient;
  final SharedPreferences _prefs;

  LokasiRepository(this._apiClient, this._prefs);

  Future<List<Lokasi>> fetchLokasi() async {
    try {
      final response = await _apiClient.dio.get('/lokasi');
      if (response.data['success'] == true) {
        final List data = response.data['data'];
        final list = data.map((json) => Lokasi.fromJson(json)).toList();
        
        final jsonList = list.map((e) => e.toJson()).toList();
        await _prefs.setString('cache_lokasi', jsonEncode(jsonList));
        
        return list;
      }
      return _loadFromCache();
    } on DioException catch (e) {
      debugPrint('[LokasiRepository] API Error (Offline): ${e.message}');
      return _loadFromCache();
    } catch (e) {
      debugPrint('[LokasiRepository] Parse Error: $e');
      return _loadFromCache();
    }
  }

  List<Lokasi> _loadFromCache() {
    final cached = _prefs.getString('cache_lokasi');
    if (cached != null) {
      try {
        final List data = jsonDecode(cached);
        return data.map((json) => Lokasi.fromJson(json)).toList();
      } catch (e) {
        debugPrint('[LokasiRepository] Cache Parse Error: $e');
      }
    }
    // Jika tidak ada cache dan offline, baru kita lempar exception
    throw Exception('Koneksi terputus dan tidak ada data offline');
  }
}
