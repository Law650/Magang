import 'package:path/path.dart';
import 'package:sqflite/sqflite.dart';

class DatabaseHelper {
  static const _databaseName = "pdam_cache.db";
  static const _databaseVersion = 2;

  static const tableLokasi = 'lokasi';
  static const tableAsetValve = 'aset_valve';
  static const tableRekapTekanan = 'rekap_tekanan';

  // Make this a singleton class
  DatabaseHelper._privateConstructor();
  static final DatabaseHelper instance = DatabaseHelper._privateConstructor();

  static Database? _database;
  Future<Database> get database async {
    if (_database != null) return _database!;
    _database = await _initDatabase();
    return _database!;
  }

  _initDatabase() async {
    String path = join(await getDatabasesPath(), _databaseName);
    return await openDatabase(
      path,
      version: _databaseVersion,
      onCreate: _onCreate,
      onUpgrade: _onUpgrade,
    );
  }

  Future _onUpgrade(Database db, int oldVersion, int newVersion) async {
    // Jika versi naik, hapus tabel lama dan buat ulang
    await db.execute('DROP TABLE IF EXISTS $tableLokasi');
    await db.execute('DROP TABLE IF EXISTS $tableAsetValve');
    await db.execute('DROP TABLE IF EXISTS $tableRekapTekanan');
    await _onCreate(db, newVersion);
  }

  Future _onCreate(Database db, int version) async {
    // Tabel Lokasi
    await db.execute('''
      CREATE TABLE $tableLokasi (
        id INTEGER PRIMARY KEY,
        nama_lokasi TEXT,
        no_sr TEXT,
        nama_pelanggan TEXT,
        alamat TEXT,
        desa TEXT,
        latitude REAL,
        longitude REAL
      )
    ''');

    // Tabel Aset Valve
    await db.execute('''
      CREATE TABLE $tableAsetValve (
        id INTEGER PRIMARY KEY,
        nama_aset TEXT,
        nama_lokasi TEXT,
        kapasitas_full_putaran REAL,
        sisa_bukaan REAL,
        persentase_bukaan REAL,
        latitude REAL,
        longitude REAL,
        nama_teknisi TEXT,
        keterangan TEXT,
        foto_eviden TEXT,
        foto_eviden_2 TEXT
      )
    ''');

    // Tabel Rekap Tekanan
    await db.execute('''
      CREATE TABLE $tableRekapTekanan (
        id INTEGER PRIMARY KEY,
        nama_lokasi TEXT,
        latitude REAL,
        longitude REAL,
        nilai_tekanan REAL,
        status TEXT,
        status_aliran TEXT,
        kekeruhan TEXT,
        keterangan TEXT,
        no_sr TEXT,
        nama_pelanggan TEXT,
        alamat TEXT,
        desa TEXT,
        waktu_pengecekan TEXT,
        nama_teknisi TEXT,
        foto_eviden TEXT
      )
    ''');
  }

  // Helper methods for bulk insert

  Future<void> insertLokasiBatch(List<Map<String, dynamic>> lokasiList) async {
    Database db = await instance.database;
    Batch batch = db.batch();
    // Clear old data first since this is a cache
    batch.delete(tableLokasi);
    for (var lokasi in lokasiList) {
      batch.insert(tableLokasi, lokasi, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getLokasiList() async {
    Database db = await instance.database;
    return await db.query(tableLokasi);
  }

  Future<void> insertAsetValveBatch(List<Map<String, dynamic>> asetList) async {
    Database db = await instance.database;
    Batch batch = db.batch();
    batch.delete(tableAsetValve);
    for (var aset in asetList) {
      batch.insert(tableAsetValve, aset, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getAsetValveList() async {
    Database db = await instance.database;
    return await db.query(tableAsetValve);
  }

  Future<void> insertRekapTekananBatch(List<Map<String, dynamic>> rekapList) async {
    Database db = await instance.database;
    Batch batch = db.batch();
    batch.delete(tableRekapTekanan);
    for (var rekap in rekapList) {
      batch.insert(tableRekapTekanan, rekap, conflictAlgorithm: ConflictAlgorithm.replace);
    }
    await batch.commit(noResult: true);
  }

  Future<List<Map<String, dynamic>>> getRekapTekananList() async {
    Database db = await instance.database;
    return await db.query(tableRekapTekanan);
  }
}
