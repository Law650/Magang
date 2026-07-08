/// Mock data Aset Valve untuk dropdown pencarian sementara.
///
/// Struktur mengikuti response `GET /api/aset-valve` pada PRD §3.1,
/// mencakup `id`, `nama_aset`, dan `nama_lokasi` agar teknisi
/// tidak salah pilih aset dengan nama mirip (PRD §4.3).
class MockAsetValve {
  final int id;
  final String namaAset;
  final String namaLokasi;
  final double latitude;
  final double longitude;

  const MockAsetValve({
    required this.id,
    required this.namaAset,
    required this.namaLokasi,
    this.latitude = -6.200000,
    this.longitude = 106.816666,
  });

  /// Label yang ditampilkan di dropdown: "NamaAset — NamaLokasi"
  String get displayLabel => '$namaAset — $namaLokasi';
}

/// Daftar mock data aset valve sementara.
/// Akan diganti dengan data dari Hive cache (Modul B) di fase selanjutnya.
const List<MockAsetValve> mockAsetValveList = [
  MockAsetValve(id: 1, namaAset: 'GV-01 Pipa Utama', namaLokasi: 'Jl. Sudirman'),
  MockAsetValve(id: 2, namaAset: 'GV-02 Cabang A', namaLokasi: 'Jl. Gatot Subroto'),
  MockAsetValve(id: 3, namaAset: 'GV-03 Distribusi B', namaLokasi: 'Jl. Diponegoro'),
  MockAsetValve(id: 4, namaAset: 'GV-04 Ring Utara', namaLokasi: 'Jl. Ahmad Yani'),
  MockAsetValve(id: 5, namaAset: 'GV-05 Zona Industri', namaLokasi: 'Kawasan Industri Timur'),
  MockAsetValve(id: 6, namaAset: 'GV-06 Reservoir', namaLokasi: 'Jl. Veteran'),
  MockAsetValve(id: 7, namaAset: 'GV-07 Cabang C', namaLokasi: 'Jl. Pahlawan'),
  MockAsetValve(id: 8, namaAset: 'GV-08 Pipa Sekunder', namaLokasi: 'Jl. Merdeka'),
];
