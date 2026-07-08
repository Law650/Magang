/// Mock data Lokasi untuk dropdown pencarian sementara.
///
/// Struktur mengikuti response `GET /api/lokasi` pada PRD §3.1.
/// Akan diganti dengan data dari Hive cache (Modul B) di fase selanjutnya.
class MockLokasi {
  final int id;
  final String namaLokasi;

  const MockLokasi({
    required this.id,
    required this.namaLokasi,
  });
}

/// Daftar mock data lokasi sementara.
const List<MockLokasi> mockLokasiList = [
  MockLokasi(id: 1, namaLokasi: 'Zona Distribusi Utara'),
  MockLokasi(id: 2, namaLokasi: 'Zona Distribusi Selatan'),
  MockLokasi(id: 3, namaLokasi: 'Zona Distribusi Timur'),
  MockLokasi(id: 4, namaLokasi: 'Zona Distribusi Barat'),
  MockLokasi(id: 5, namaLokasi: 'Kawasan Industri A'),
  MockLokasi(id: 6, namaLokasi: 'Perumahan Griya Indah'),
  MockLokasi(id: 7, namaLokasi: 'Pusat Kota'),
  MockLokasi(id: 8, namaLokasi: 'Reservoir Utama'),
];
