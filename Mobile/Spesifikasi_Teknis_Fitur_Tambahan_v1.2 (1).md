# SPESIFIKASI TEKNIS FITUR TAMBAHAN v1.2
## Dokumen Pendamping — PRD Mobile Teknisi Lapangan PDAM (Flutter & Laravel TALL Stack)

| Informasi Dokumen | Detail |
|---|---|
| **Dokumen Acuan** | PRD Mobile Teknisi Lapangan PDAM v1.2 |
| **Cakupan** | Rancangan migration Laravel, kontrak API `multipart/form-data`, dan panduan logika Flutter untuk 3 fitur: (1) Koordinat GPS & Tautan Google Maps, (2) Foto Bukti Strict Camera & Watermark, (3) Sinkronisasi Lanjutan |
| **Ditujukan Untuk** | Tim Backend (Laravel), Tim Mobile Developer (Flutter) |
| **Sifat Dokumen** | Spesifikasi implementasi teknis — bukan pengganti PRD, melainkan pendetailan atas Modul E, F, G pada PRD v1.2 Bagian 4.5–4.7 |

> **Catatan asumsi:** PRD tidak secara eksplisit menyebutkan kolom database untuk menyimpan referensi file foto. Dokumen ini menambahkan kolom `foto_bukti` (path relatif di storage) pada kedua tabel sebagai konsekuensi teknis wajib dari requirement "Foto Bukti Lapangan" — silakan konfirmasi ke Product Manager jika penamaan/pendekatan penyimpanan ini perlu disesuaikan.

---

## DAFTAR ISI
1. [Rancangan Migration Database (Laravel)](#1-rancangan-migration-database-laravel)
2. [Pemutakhiran Kontrak API](#2-pemutakhiran-kontrak-api)
3. [Panduan Alur Logika Flutter](#3-panduan-alur-logika-flutter)
4. [Checklist Implementasi & Risiko Teknis](#4-checklist-implementasi--risiko-teknis)

---

## 1. RANCANGAN MIGRATION DATABASE (LARAVEL)

### 1.1 Ringkasan Perubahan Skema

| Tabel | Kolom Baru | Tipe | Nullable | Catatan |
|---|---|---|---|---|
| `log_valves` | `latitude` | `FLOAT(10,6)` | Ya* | *Nullable di level DB untuk resiliensi data lama/edge-case; **wajib** diisi di level validasi `Form Request` (Bagian 2.4) |
| `log_valves` | `longitude` | `FLOAT(10,6)` | Ya* | Sama seperti di atas |
| `log_valves` | `foto_bukti` | `VARCHAR(255)` | Ya* | Path relatif hasil `Storage::putFile()`, mis. `foto-bukti/log-valve/xxxx.jpg` |
| `log_tekanans` | `latitude` | `FLOAT(10,6)` | Ya* | Sama seperti di atas |
| `log_tekanans` | `longitude` | `FLOAT(10,6)` | Ya* | Sama seperti di atas |
| `log_tekanans` | `foto_bukti` | `VARCHAR(255)` | Ya* | Path relatif, mis. `foto-bukti/log-tekanan/xxxx.jpg` |

> **Catatan teknis — tipe `FLOAT(10,6)` di MySQL:** Sesuai requirement, migration di bawah menggunakan `FLOAT(10,6)`. Perlu diketahui bahwa sejak MySQL 8.0.17, sintaks presisi `(M,D)` pada tipe `FLOAT`/`DOUBLE` **sudah deprecated** (masih berfungsi dan belum dihapus per versi MySQL 8.4, namun berpotensi dihapus di versi mendatang, dan akan memunculkan warning saat migration dijalankan). Jika suatu saat perlu migrasi ke arah yang lebih *future-proof*, alternatif yang lebih portable adalah `DECIMAL(10,6)` (presisi eksak, tanpa deprecation warning) — namun implementasi di bawah tetap mengikuti requirement `FLOAT(10,6)` sesuai permintaan.

### 1.2 Migration: `log_valves`

Perintah pembuatan file migration:

```bash
php artisan make:migration add_location_and_foto_bukti_columns_to_log_valves_table --table=log_valves
```

Isi file migration:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_valves', function (Blueprint $table) {
            $table->float('latitude', 10, 6)->nullable()->after('jumlah_putaran');
            $table->float('longitude', 10, 6)->nullable()->after('latitude');
            $table->string('foto_bukti', 255)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('log_valves', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'foto_bukti']);
        });
    }
};
```

### 1.3 Migration: `log_tekanans`

```bash
php artisan make:migration add_location_and_foto_bukti_columns_to_log_tekanans_table --table=log_tekanans
```

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('log_tekanans', function (Blueprint $table) {
            $table->float('latitude', 10, 6)->nullable()->after('nilai_tekanan');
            $table->float('longitude', 10, 6)->nullable()->after('latitude');
            $table->string('foto_bukti', 255)->nullable()->after('longitude');
        });
    }

    public function down(): void
    {
        Schema::table('log_tekanans', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude', 'foto_bukti']);
        });
    }
};
```

> Sesuaikan posisi `->after('kolom_sebelumnya')` dengan urutan kolom aktual pada skema `log_valves`/`log_tekanans` di database masing-masing tim; posisi kolom tidak memengaruhi fungsi, hanya urutan tampilan `DESCRIBE TABLE`.

### 1.4 Pembaruan Model (Eloquent)

Tambahkan kolom baru ke `$fillable` dan buat *accessor* URL foto agar mobile & dashboard admin tidak perlu membangun ulang URL storage secara manual:

```php
// app/Models/LogValve.php

class LogValve extends Model
{
    protected $fillable = [
        // ...kolom yang sudah ada,
        'latitude',
        'longitude',
        'foto_bukti',
    ];

    protected $appends = ['foto_bukti_url'];

    public function getFotoBuktiUrlAttribute(): ?string
    {
        return $this->foto_bukti
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->foto_bukti)
            : null;
    }
}
```

Lakukan hal yang sama untuk `app/Models/LogTekanan.php`.

### 1.5 Konfigurasi Storage

```bash
# Pastikan symlink storage sudah dibuat agar foto dapat diakses via URL publik
php artisan storage:link
```

Struktur folder penyimpanan yang direkomendasikan (di dalam `storage/app/public/`):

```
storage/app/public/
└── foto-bukti/
    ├── log-valve/
    │   └── {uuid}.jpg
    └── log-tekanan/
        └── {uuid}.jpg
```

---

## 2. PEMUTAKHIRAN KONTRAK API

### 2.1 Ringkasan Perubahan

| Aspek | Sebelumnya (v1.1) | Sekarang (v1.2) |
|---|---|---|
| `Content-Type` request | `application/json` | `multipart/form-data` |
| Jumlah field wajib | Sesuai PRD Web 3.2 | + `latitude`, `longitude`, `foto_bukti` (file) |
| Response `foto_bukti` | — | `foto_bukti` (path) & `foto_bukti_url` (URL publik siap pakai) ditambahkan ke response |

### 2.2 `POST /api/log-valve`

**Request — `multipart/form-data`**

| Field | Tipe Multipart | Contoh Nilai | Wajib |
|---|---|---|---|
| `aset_id` | text | `12` | Ya |
| `nama_teknisi` | text | `Budi Santoso` | Ya |
| `waktu_kegiatan` | text | `2026-07-06 09:15:00` | Ya |
| `aksi_kerja` | text | `buka` | Ya |
| `jumlah_putaran` | text | `12.25` | Ya |
| `keterangan` | text | `Kondisi valve baik` | Tidak |
| `latitude` | text | `-6.966667` | Ya |
| `longitude` | text | `109.632222` | Ya |
| `foto_bukti` | file | `bukti_lv_a1b2c3.jpg` | Ya |

> Catatan: pada `multipart/form-data`, seluruh field non-file secara teknis dikirim sebagai string — backend tetap perlu melakukan *casting*/validasi tipe numerik (lihat aturan validasi 2.4).

**Response — `201 Created`**

```json
{
  "message": "Log valve berhasil disimpan",
  "data": {
    "id": 451,
    "aset_id": 12,
    "nama_teknisi": "Budi Santoso",
    "waktu_kegiatan": "2026-07-06 09:15:00",
    "aksi_kerja": "buka",
    "jumlah_putaran": "12.25",
    "keterangan": "Kondisi valve baik",
    "latitude": -6.966667,
    "longitude": 109.632222,
    "foto_bukti": "foto-bukti/log-valve/a1b2c3.jpg",
    "foto_bukti_url": "https://domain-pdam.example/storage/foto-bukti/log-valve/a1b2c3.jpg",
    "created_at": "2026-07-06T02:15:03.000000Z"
  }
}
```

**Response — `422 Unprocessable Entity`** (contoh validasi gagal)

```json
{
  "message": "Data tidak valid",
  "errors": {
    "foto_bukti": ["Foto bukti wajib diunggah."],
    "latitude": ["Koordinat latitude wajib diisi."]
  }
}
```

### 2.3 `POST /api/log-tekanan`

**Request — `multipart/form-data`**

| Field | Tipe Multipart | Contoh Nilai | Wajib |
|---|---|---|---|
| `lokasi_id` | text | `7` | Ya |
| `nilai_tekanan` | text | `0.85` | Ya |
| `waktu_pengecekan` | text | `2026-07-06 09:20:00` | Ya |
| `latitude` | text | `-6.966701` | Ya |
| `longitude` | text | `109.632310` | Ya |
| `foto_bukti` | file | `bukti_lt_d4e5f6.jpg` | Ya |

**Response — `201 Created`**

```json
{
  "message": "Log tekanan berhasil disimpan",
  "data": {
    "id": 892,
    "lokasi_id": 7,
    "nilai_tekanan": "0.85",
    "status": "rendah",
    "waktu_pengecekan": "2026-07-06 09:20:00",
    "latitude": -6.966701,
    "longitude": 109.632310,
    "foto_bukti": "foto-bukti/log-tekanan/d4e5f6.jpg",
    "foto_bukti_url": "https://domain-pdam.example/storage/foto-bukti/log-tekanan/d4e5f6.jpg",
    "created_at": "2026-07-06T02:20:04.000000Z"
  }
}
```

### 2.4 Form Request Validation (Laravel)

```php
// app/Http/Requests/StoreLogValveRequest.php

public function rules(): array
{
    return [
        'aset_id' => ['required', 'integer', 'exists:aset_valves,id'],
        'nama_teknisi' => ['required', 'string', 'max:255'],
        'waktu_kegiatan' => ['required', 'date_format:Y-m-d H:i:s'],
        'aksi_kerja' => ['required', 'in:buka,tutup'],
        'jumlah_putaran' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
        'keterangan' => ['nullable', 'string'],
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
        'foto_bukti' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'], // maks 5MB
    ];
}
```

```php
// app/Http/Requests/StoreLogTekananRequest.php

public function rules(): array
{
    return [
        'lokasi_id' => ['required', 'integer', 'exists:lokasis,id'],
        'nilai_tekanan' => ['required', 'regex:/^\d+(\.\d{1,2})?$/'],
        'waktu_pengecekan' => ['required', 'date_format:Y-m-d H:i:s'],
        'latitude' => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
        'foto_bukti' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
    ];
}
```

Contoh penyimpanan file di dalam Controller:

```php
$path = $request->file('foto_bukti')->store('foto-bukti/log-valve', 'public');

$logValve = LogValve::create([
    ...$request->validated(),
    'foto_bukti' => $path,
]);

return response()->json([
    'message' => 'Log valve berhasil disimpan',
    'data' => $logValve,
], 201);
```

### 2.5 Dampak ke Endpoint `GET` (Riwayat/Dashboard)

Endpoint yang menampilkan data log (baik untuk kebutuhan Riwayat mobile maupun dashboard admin Web) wajib turut mengembalikan `latitude`, `longitude`, `foto_bukti`, dan `foto_bukti_url` — otomatis terpenuhi jika model sudah dikonfigurasi seperti Bagian 1.4 (`$appends`), tanpa perlu perubahan tambahan di controller `index()`/`show()`.

### 2.6 Rekomendasi: `idempotency_key` sebagai Field Resmi *(opsional, lihat PRD Bagian 3.2a)*

Jika disetujui Tim Backend & Product Manager, tambahkan field `idempotency_key` (string UUID) ke request di atas beserta kolom unik pada migration:

```php
$table->uuid('idempotency_key')->unique()->nullable()->after('foto_bukti');
```

Lalu di controller, gunakan `firstOrCreate(['idempotency_key' => $request->idempotency_key], [...])` alih-alih `create()` langsung, sehingga retry request yang identik tidak menghasilkan entri maupun foto duplikat di server.

---

## 3. PANDUAN ALUR LOGIKA FLUTTER

### 3.1 Modul E — Location Capture

**Alur:**

1. Saat `LogValveFormPage`/`LogTekananFormPage` di-*build* pertama kali (`initState`), panggil provider lokasi.
2. Cek status layanan lokasi & izin via `permission_handler` sebelum memanggil `geolocator`:

```dart
Future<Position?> captureLocation() async {
  final serviceEnabled = await Geolocator.isLocationServiceEnabled();
  if (!serviceEnabled) {
    // tampilkan dialog: arahkan ke pengaturan GPS perangkat
    return null;
  }

  LocationPermission permission = await Geolocator.checkPermission();
  if (permission == LocationPermission.denied) {
    permission = await Geolocator.requestPermission();
    if (permission == LocationPermission.denied) return null;
  }
  if (permission == LocationPermission.deniedForever) {
    // arahkan ke App Settings via permission_handler: openAppSettings()
    return null;
  }

  return Geolocator.getCurrentPosition(
    desiredAccuracy: LocationAccuracy.high,
  );
}
```

3. Simpan hasil (`latitude`, `longitude`) ke state form (Riverpod `Notifier`). Tombol "Simpan" **wajib nonaktif** selagi koordinat belum tersedia (`null`), dengan indikator "Mencari lokasi…" dan tombol "Coba Lagi" yang memanggil ulang `captureLocation()`.
4. Koordinat ini **dipakai ulang** — tidak ditangkap dua kali — sebagai input watermark pada Modul F, untuk menjamin angka yang tersimpan di kolom `latitude`/`longitude` identik dengan yang tercetak di foto.

**Tautan Google Maps di halaman Riwayat:**

```dart
Future<void> openInGoogleMaps(double lat, double lng) async {
  final uri = Uri.parse('https://maps.google.com/?q=$lat,$lng');
  if (await canLaunchUrl(uri)) {
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }
}
```

Bungkus teks koordinat dengan `InkWell(onTap: () => openInGoogleMaps(lat, lng), child: Text('$lat, $lng', style: linkStyle))`.

### 3.2 Modul F — Strict Camera & Watermark

#### 3.2.1 Mengapa Bukan `image_picker`

`image_picker` dengan `ImageSource.camera` **tetap memanggil aplikasi kamera sistem/pihak ketiga**, yang pada sejumlah perangkat Android menyediakan tombol pintasan menuju thumbnail galeri terakhir di dalam UI kamera bawaan itu sendiri — sehingga larangan "akses galeri dilarang sepenuhnya" berisiko bocor secara tidak konsisten antar merk perangkat. Pendekatan yang lebih *strict* dan konsisten lintas perangkat adalah **membangun layar kamera kustom di dalam aplikasi** menggunakan package `camera`, di mana UI yang ditampilkan sepenuhnya dikontrol aplikasi (hanya tombol rana, tanpa elemen apa pun menuju galeri).

```dart
class StrictCameraPage extends StatefulWidget {
  // ...
}

class _StrictCameraPageState extends State<StrictCameraPage> {
  late CameraController _controller;

  @override
  void initState() {
    super.initState();
    _initCamera();
  }

  Future<void> _initCamera() async {
    final cameras = await availableCameras();
    final backCamera = cameras.firstWhere(
      (c) => c.lensDirection == CameraLensDirection.back,
    );
    _controller = CameraController(backCamera, ResolutionPreset.high, enableAudio: false);
    await _controller.initialize();
    setState(() {});
  }

  Future<void> _onShutterPressed() async {
    final XFile rawFile = await _controller.takePicture();
    final bytes = await rawFile.readAsBytes();
    // lanjut ke proses watermark (3.2.2)
  }

  // build(): tampilkan CameraPreview(_controller) + tombol rana SAJA
  // TIDAK ADA tombol/ikon menuju galeri di layar ini
}
```

#### 3.2.2 Proses Watermark

Direkomendasikan menggunakan package `image` (pemrosesan bitmap murni Dart, konsisten di Android & iOS) dibanding `image_editor` (dukungan platform tidak selalu konsisten dan pengembangannya kurang aktif). Karena decode-composite-encode JPEG cukup berat secara CPU, proses ini **wajib dijalankan di `compute()`/isolate terpisah** agar tidak menyebabkan *jank* pada UI thread — terutama pada perangkat kelas bawah yang menjadi target pengguna (RAM 2GB, sesuai NFR PRD Bagian 6).

```dart
class WatermarkInput {
  final Uint8List rawBytes;
  final String namaTeknisi;
  final double latitude;
  final double longitude;
  final DateTime waktu;
  final String namaLokasiAset;

  WatermarkInput({
    required this.rawBytes,
    required this.namaTeknisi,
    required this.latitude,
    required this.longitude,
    required this.waktu,
    required this.namaLokasiAset,
  });
}

Uint8List applyWatermark(WatermarkInput input) {
  final original = img.decodeImage(input.rawBytes)!;

  final lines = [
    'Teknisi: ${input.namaTeknisi}',
    'Lokasi Aset: ${input.namaLokasiAset}',
    'Koordinat: ${input.latitude.toStringAsFixed(6)}, ${input.longitude.toStringAsFixed(6)}',
    'Waktu: ${DateFormat('dd/MM/yyyy HH:mm:ss').format(input.waktu)}',
  ];

  // Kotak latar semi-transparan gelap di bagian bawah gambar agar teks
  // tetap terbaca di atas latar foto lapangan yang bervariasi (lihat PRD 5.3)
  final boxHeight = 28 * lines.length + 20;
  img.fillRect(
    original,
    x1: 0,
    y1: original.height - boxHeight,
    x2: original.width,
    y2: original.height,
    color: img.ColorRgba8(0, 0, 0, 140),
  );

  for (var i = 0; i < lines.length; i++) {
    img.drawString(
      original,
      lines[i],
      font: img.arial24,
      x: 16,
      y: original.height - boxHeight + 10 + (i * 28),
      color: img.ColorRgb8(255, 255, 255),
    );
  }

  return Uint8List.fromList(img.encodeJpg(original, quality: 85));
}

// Dipanggil dari isolate terpisah:
final watermarkedBytes = await compute(applyWatermark, watermarkInput);
```

#### 3.2.3 Penyimpanan File Lokal

```dart
Future<String> saveWatermarkedPhoto(Uint8List bytes, String formType, String entryUuid) async {
  final dir = await getApplicationDocumentsDirectory();
  final subDir = Directory('${dir.path}/foto_bukti/$formType'); // formType: 'log_valve' | 'log_tekanan'
  if (!await subDir.exists()) {
    await subDir.create(recursive: true);
  }
  final file = File('${subDir.path}/$entryUuid.jpg');
  await file.writeAsBytes(bytes);
  return file.path; // PATH inilah yang disimpan ke Hive, bukan bytes-nya
}
```

Setelah foto tersimpan, tampilkan pratinjau hasil akhir (gambar + watermark) kepada teknisi dengan opsi "Gunakan Foto Ini" atau "Ambil Ulang" (mengulang dari 3.2.1), sebelum tombol "Simpan" form diaktifkan — sesuai catatan UX pada PRD Bagian 5.3.

### 3.3 Modul G — Sinkronisasi Lanjutan (Antrian Bermedia)

#### 3.3.1 Skema Hive Queue (Diperluas)

```dart
@HiveType(typeId: 1)
class QueuedLog extends HiveObject {
  @HiveField(0)
  String idempotencyKey;

  @HiveField(1)
  Map<String, dynamic> payloadFields; // aset_id, nama_teknisi, waktu_kegiatan, dst (String)

  @HiveField(2)
  String fotoPath; // path file lokal, BUKAN bytes

  @HiveField(3)
  String status; // 'pending' | 'syncing' | 'success' | 'failed'

  @HiveField(4)
  int retryCount;

  @HiveField(5)
  String? lastError;

  @HiveField(6)
  String endpoint; // '/api/log-valve' | '/api/log-tekanan'
}
```

#### 3.3.2 Proses Upload Multipart via Dio

```dart
Future<void> syncOneEntry(QueuedLog entry, Dio dio) async {
  final file = File(entry.fotoPath);
  if (!await file.exists()) {
    entry.status = 'failed';
    entry.lastError = 'file_missing_local';
    await entry.save();
    return; // hindari kirim data tanpa foto — backend pasti menolak (Bagian 2.4)
  }

  entry.status = 'syncing';
  await entry.save();

  try {
    final formData = FormData.fromMap({
      ...entry.payloadFields,
      'foto_bukti': await MultipartFile.fromFile(
        entry.fotoPath,
        filename: p.basename(entry.fotoPath),
      ),
    });

    final response = await dio.post(entry.endpoint, data: formData);

    if (response.statusCode == 201) {
      entry.status = 'success';
      entry.lastError = null;
      await entry.save();
      // Opsional: jadwalkan pembersihan file lokal setelah retensi 7 hari (NFR Bagian 6)
    }
  } on DioException catch (e) {
    entry.retryCount += 1;
    entry.lastError = e.message;
    entry.status = entry.retryCount >= 5 ? 'failed' : 'pending';
    await entry.save();
    // Exponential backoff (5s → 15s → 1m → 5m → 15m) dijadwalkan oleh pemanggil,
    // konsisten dengan strategi retry non-media yang sudah ada (PRD Bagian 6)
  }
}
```

#### 3.3.3 Auto-Sync Foreground vs Background — Catatan Teknis Penting

> **Koreksi penting terhadap requirement awal:** Android **WorkManager** (dasar dari package `workmanager`) membatasi *periodic task* pada interval minimum **15 menit** oleh OS — nilai ini **tidak bisa** dikonfigurasi menjadi 60 detik, berapa pun nilai yang dimasukkan ke kode. Oleh karena itu, dua kebutuhan interval pada requirement harus dipisah menjadi dua mekanisme berbeda:

```dart
// Foreground — 60 detik, HANYA saat app aktif, via Timer biasa (bukan workmanager)
class ForegroundSyncController with WidgetsBindingObserver {
  Timer? _timer;

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _timer ??= Timer.periodic(const Duration(seconds: 60), (_) => runSyncQueue());
    } else {
      _timer?.cancel();
      _timer = null;
    }
  }
}
```

```dart
// Background — 15 menit, via workmanager (sesuai batas minimum OS Android)
void callbackDispatcher() {
  Workmanager().executeTask((task, inputData) async {
    await runSyncQueue();
    return Future.value(true);
  });
}

// Registrasi (di main.dart):
Workmanager().initialize(callbackDispatcher);
Workmanager().registerPeriodicTask(
  'sync-queue-background',
  'syncQueueTask',
  frequency: const Duration(minutes: 15), // minimum yang diizinkan Android
);
```

> **Catatan iOS:** `BGTaskScheduler` (dipakai `workmanager` di balik layar untuk iOS) bersifat *opportunistic* — OS yang memutuskan kapan tugas benar-benar dijalankan, tanpa jaminan interval pasti. Di iOS, auto-sync foreground (Timer 60 detik) dan tombol Manual Sync menjadi jaring pengaman utama; auto-sync background hanya diperlakukan sebagai *best-effort tambahan*, bukan mekanisme yang bisa diandalkan sepenuhnya.

Tambahan: pasang *listener* `connectivity_plus` agar begitu koneksi kembali online (dari kondisi offline), `runSyncQueue()` langsung dipicu tanpa menunggu interval Timer/WorkManager berikutnya:

```dart
Connectivity().onConnectivityChanged.listen((result) {
  if (result != ConnectivityResult.none) {
    runSyncQueue();
  }
});
```

#### 3.3.4 Manual Sync

```dart
Future<SyncSummary> forceSyncNow() async {
  if (isSyncing) return SyncSummary.alreadyRunning();
  isSyncing = true;

  final queue = hiveBox.values
      .where((e) => e.status == 'pending' || e.status == 'failed')
      .toList(); // FIFO — Hive mengembalikan sesuai urutan insert

  int success = 0, failed = 0;
  for (final entry in queue) {
    await syncOneEntry(entry, dio);
    entry.status == 'success' ? success++ : failed++;
  }

  isSyncing = false;
  return SyncSummary(success: success, failed: failed);
}
```

Tombol "Sinkronkan Data" pada UI memanggil `forceSyncNow()` secara langsung — **fungsi yang sama** dipakai oleh auto-sync foreground, auto-sync background, dan listener konektivitas, dijaga oleh flag `isSyncing` agar tidak terjadi proses ganda secara bersamaan (*race condition*) saat beberapa trigger aktif dalam waktu berdekatan.

### 3.4 Ringkasan Dependency Tambahan (`pubspec.yaml`)

```yaml
dependencies:
  geolocator: ^13.0.0
  camera: ^0.11.0
  image: ^4.2.0
  path_provider: ^2.1.0
  permission_handler: ^11.3.0
  url_launcher: ^6.3.0
  workmanager: ^0.5.2
  connectivity_plus: ^6.0.0
  dio: ^5.5.0
  intl: ^0.19.0
```

> Versi di atas adalah acuan umum yang kompatibel per pertengahan 2026 — Tim Mobile tetap perlu mengecek versi stabil terbaru masing-masing package di pub.dev sebelum implementasi, karena rilis baru dapat muncul kapan saja.

---

## 4. CHECKLIST IMPLEMENTASI & RISIKO TEKNIS

| # | Item | Status Perhatian |
|---|---|---|
| 1 | Migration `latitude`, `longitude`, `foto_bukti` pada `log_valves` & `log_tekanans` | Wajib |
| 2 | `Form Request` backend memvalidasi `foto_bukti` sebagai file image wajib, bukan opsional | Wajib |
| 3 | `storage:link` sudah dijalankan di server produksi, bukan hanya lokal | Wajib |
| 4 | Layar kamera kustom benar-benar tidak memiliki elemen navigasi ke galeri (audit manual UI) | Wajib |
| 5 | Watermark dijalankan di `compute()`/isolate agar tidak nge-*lag* di perangkat RAM 2GB | Wajib |
| 6 | Path foto (bukan bytes) yang disimpan ke Hive queue | Wajib |
| 7 | Auto-sync 60 detik memakai `Timer`, **bukan** `workmanager` (batas OS 15 menit) | Wajib — koreksi teknis penting |
| 8 | Penanganan file foto hilang sebelum retry upload (`File.existsSync()`) | Wajib |
| 9 | `idempotency_key` dikirim & di-`unique`-kan di backend untuk cegah duplikasi akibat retry unggah foto | Direkomendasikan (opsional) |
| 10 | Kebijakan retensi/pembersihan file foto lokal pasca-sync sukses | Direkomendasikan |
| 11 | Dialog rasional izin kamera & lokasi sebelum permintaan native | Wajib (UX & tingkat *grant rate* izin) |
| 12 | Uji kompatibilitas `workmanager`/`BGTaskScheduler` khusus di iOS (perilaku *opportunistic*) | Wajib diuji, bukan diasumsikan |

---

*Dokumen ini merupakan pendetailan teknis atas Modul E, F, dan G pada PRD Mobile Teknisi Lapangan PDAM v1.2, dan bersifat living document — dapat direvisi seiring diskusi lebih lanjut antara Tim Backend dan Tim Mobile Developer.*
