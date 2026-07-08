# PRODUCT REQUIREMENT DOCUMENT (PRD)
## Aplikasi Mobile Teknisi Lapangan — Monitoring Aset & Distribusi Air PDAM
### (Mobile Companion App — Flutter, Terintegrasi dengan Backend Laravel PRD Web TALL Stack)

| Informasi Dokumen | Detail |
|---|---|
| **Nama Proyek** | Aplikasi Mobile Teknisi Lapangan — Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM |
| **Jenis Dokumen** | Product Requirement Document (PRD) — Mobile Companion App |
| **Platform** | Android & iOS (Flutter/Dart) |
| **Dokumen Acuan** | PRD Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM (Web Dashboard Analitik — TALL Stack) v1.0 |
| **Integrasi Backend** | RESTful API Laravel (`routes/api.php`) — **tanpa autentikasi token** (lihat catatan revisi), request `log-valve` & `log-tekanan` kini berformat `multipart/form-data` (lihat v1.2) |
| **Versi Dokumen** | 1.2 |
| **Status** | Draft untuk Review Tim Pengembang |
| **Ditujukan Untuk** | Tim Mobile Developer (Flutter), Tim Backend, QA, dan Project Manager |

### Riwayat Revisi

| Versi | Perubahan |
|---|---|
| 1.0 | Draf awal — autentikasi menggunakan Laravel Sanctum (login + Bearer Token) |
| 1.1 | **Keputusan produk:** modul login dihapus. Identifikasi teknisi diganti menjadi input nama secara manual (free text), tanpa proteksi tambahan (tanpa token/API key). Endpoint `routes/api.php` yang dikonsumsi mobile menjadi publik/terbuka. Lihat catatan risiko di Bagian 3.1 dan Bagian 6 |
| **1.2** | **Penambahan 3 fitur baru:** (1) Penangkapan koordinat GPS otomatis pada `log_valves` & `log_tekanans` beserta tautan Google Maps yang dapat diklik; (2) Foto bukti lapangan wajib dari kamera *in-app* (galeri dinonaktifkan total) dengan watermark otomatis (nama teknisi, koordinat, waktu, lokasi aset); (3) Modul sinkronisasi lanjutan — auto-sync berkala (60 detik saat aplikasi aktif, 15 menit di background) dan tombol sinkronisasi manual. Kontrak API `POST /api/log-valve` & `POST /api/log-tekanan` berubah dari JSON polos menjadi `multipart/form-data`. Rincian teknis (migration, kontrak API, alur logika Flutter) didokumentasikan pada dokumen pendamping *Spesifikasi Teknis Fitur Tambahan v1.2* |

---

## DAFTAR ISI
1. [Project Information & Objectives](#1-project-information--objectives)
2. [System Architecture & Tech Stack (Mobile)](#2-system-architecture--tech-stack-mobile)
3. [Integrasi API & Kontrak Data (Data Contract)](#3-integrasi-api--kontrak-data-data-contract)
4. [Functional Requirements (Modul Aplikasi Mobile)](#4-functional-requirements-modul-aplikasi-mobile)
5. [User Interface (UI) & UX Guidelines (Mobile)](#5-user-interface-ui--ux-guidelines-mobile)
6. [Non-Functional Requirements](#6-non-functional-requirements)

> **Dokumen Terkait (v1.2):** Rancangan migration Laravel, kontrak API `multipart/form-data` secara lengkap, dan panduan alur logika Flutter (kamera *strict*, watermark, antrian sinkronisasi file gambar) didokumentasikan secara rinci pada berkas terpisah **`Spesifikasi_Teknis_Fitur_Tambahan_v1.2.md`**, agar PRD ini tetap fokus pada level kebutuhan produk sementara detail implementasi tidak membebani dokumen ini.

---

## 1. PROJECT INFORMATION & OBJECTIVES

### 1.1 Latar Belakang

Pada PRD Web sebelumnya (v1.0), pengembangan aplikasi mobile bagi teknisi lapangan secara eksplisit dinyatakan sebagai **Out-of-Scope** dan diasumsikan sebagai sistem pihak ketiga yang sudah tersedia. Dalam praktiknya, ketiadaan aplikasi resmi menyebabkan teknisi lapangan tetap bergantung pada pencatatan manual di kertas atau aplikasi pesan instan, sebelum data direkap ulang secara manual oleh staf admin ke dalam dashboard — sehingga tujuan *real-time data* dan *early warning system* pada PRD Web belum sepenuhnya tercapai di titik input data paling awal, yaitu di lapangan.

Dokumen ini mendefinisikan **Aplikasi Mobile Pendamping (Companion App)** berbasis **Flutter** yang kini berstatus **proyek internal In-Scope**, dirancang khusus untuk digunakan oleh Teknisi Lapangan dalam mencatat aktivitas Gate Valve dan tekanan jaringan pipa secara langsung dari titik lokasi kerja, lalu mengirimkan data tersebut ke backend Laravel yang sama persis dengan yang telah didefinisikan pada PRD Web (`routes/api.php`).

> **Keputusan Produk (v1.1):** Berbeda dari asumsi awal yang mengacu pada autentikasi **Laravel Sanctum** seperti pada PRD Web, tim produk memutuskan aplikasi mobile **tidak menggunakan proses login**. Identifikasi teknisi cukup dilakukan melalui input nama secara manual (free text) tanpa proteksi tambahan (tanpa token, tanpa API key). Keputusan ini diambil secara sadar demi kemudahan penggunaan di lapangan, dengan risiko yang diterima (*accepted risk*) berupa endpoint API yang terbuka tanpa lapisan autentikasi — dijelaskan lebih rinci pada Bagian 3.1 dan Bagian 6.

> **Catatan Perubahan Ruang Lingkup:** Sub-bab 1.2 pada PRD Web v1.0 mencantumkan "Pengembangan aplikasi mobile teknisi (dianggap sudah ada/pihak ketiga)" sebagai Out-of-Scope. Dokumen PRD Mobile ini secara resmi menggantikan asumsi tersebut dan menjadikan aplikasi mobile sebagai bagian dari ekosistem internal yang wajib dikembangkan selaras dengan kontrak API yang sudah didefinisikan di backend, tanpa memerlukan perubahan struktur database maupun endpoint baru.

### 1.2 Ruang Lingkup (Scope)

| Kategori | Cakupan |
|---|---|
| **In-Scope** | Input identitas teknisi via nama manual (tanpa login/akun), sinkronisasi master data (`Lokasi` & `AsetValve`) untuk kebutuhan dropdown, input & submit Log Valve, input & submit Log Tekanan, mekanisme antrian (*queue*) offline-first dengan sinkronisasi otomatis; **(v1.2)** penangkapan koordinat GPS otomatis + tautan Google Maps yang dapat diklik, foto bukti wajib dari kamera *in-app* dengan watermark otomatis, modul sinkronisasi lanjutan (auto-sync interval & tombol sinkronisasi manual) |
| **Out-of-Scope** | Dashboard analitik/grafik di sisi mobile (tetap menjadi domain Web TALL Stack), manajemen master data — *create/edit/delete* `Lokasi` & `AsetValve` dari mobile (hanya *read* untuk dropdown), export laporan Excel, modul billing/pengaduan pelanggan, push notification real-time (dapat menjadi fase pengembangan lanjutan), editor/anotasi foto pasca-pengambilan (foto bersifat final begitu watermark diterapkan, tidak dapat diedit ulang oleh teknisi), penyimpanan foto di cloud storage pihak ketiga (fase ini menyimpan foto di `storage/app/public` server Laravel) |

### 1.3 Tujuan Utama (Goals)

| No | Goal | Deskripsi | Indikator Keberhasilan |
|---|---|---|---|
| 1 | **Kemudahan Input Lapangan** | Menyediakan form input sederhana, cepat, dan minim kesalahan bagi teknisi yang bekerja di lokasi terbuka | Rata-rata waktu pengisian 1 log < 60 detik, error input < 2% |
| 2 | **Data Real-Time dari Sumber Pertama** | Menghilangkan jeda pencatatan manual kertas → rekap admin, dengan mengirim data langsung dari titik kejadian ke server pusat | 100% log baru berasal langsung dari aplikasi mobile, 0% rekap manual |
| 3 | **Operasional Tanpa Sinyal (Offline-First)** | Teknisi tetap dapat mencatat aktivitas meski berada di *blank spot* tanpa koneksi internet | 0% data hilang akibat tidak ada sinyal, sinkronisasi otomatis berhasil saat sinyal kembali |
| 4 | **Konsistensi dengan Backend Web** | Struktur data, validasi, dan kontrak API identik dengan yang telah didefinisikan pada PRD Web, tanpa duplikasi logic bisnis di sisi mobile | Payload API mobile diterima tanpa error validasi oleh `routes/api.php` yang sudah ada |
| 5 | **Minim Friksi Penggunaan (No-Login)** | Teknisi dapat langsung memakai aplikasi tanpa proses pendaftaran/login akun — cukup input nama sekali di awal | Waktu dari install ke pengisian log pertama < 2 menit, tanpa proses lupa password/reset akun |
| 6 | **Bukti Lapangan Terverifikasi Lokasi & Visual (v1.2)** | Menutup celah akuntabilitas yang timbul dari tidak adanya login (Bagian 3.1a), dengan mewajibkan foto asli kamera *in-app* + watermark (nama, koordinat, waktu, lokasi aset) dan koordinat GPS presisi pada setiap log, sebagai bukti kehadiran fisik teknisi di lokasi | 100% entri Log Valve & Log Tekanan memiliki `latitude`/`longitude` dan `foto_bukti` terisi, 0% foto berasal dari galeri/manipulasi |
| 7 | **Ketahanan Sinkronisasi pada Payload Bermedia (v1.2)** | Menjamin foto berukuran besar tidak menghambat ataupun gagal terkirim akibat sinyal tidak stabil, melalui kombinasi auto-sync berkala dan kontrol manual dari teknisi | Antrian `pending` tidak menumpuk > 24 jam pada kondisi sinyal normal; teknisi dapat memaksa sinkronisasi kapan pun via tombol manual |

### 1.4 Target Pengguna & Stakeholders

| Peran | Deskripsi | Kebutuhan Utama terhadap Aplikasi |
|---|---|---|
| **Teknisi Lapangan** *(primary user)* | Petugas yang secara fisik mengunjungi lokasi Gate Valve dan titik pengecekan tekanan | Aplikasi ringan, cepat dibuka, tombol besar, tetap bisa dipakai tanpa sinyal |
| **Admin Operasional (Web)** | Menerima dan memvalidasi data yang masuk melalui dashboard TALL Stack | Data yang masuk dari mobile konsisten formatnya dengan yang divalidasi backend |
| **Tim Backend (Laravel)** | Menyediakan dan menjaga kontrak `routes/api.php` agar tetap kompatibel dengan mobile | Payload dari mobile sesuai `Form Request` yang sudah ada, tanpa perlu endpoint baru |
| **Tim Mobile Developer (Flutter)** | Implementasi teknis aplikasi sesuai PRD ini | Spesifikasi state management, networking, dan local storage yang jelas dan konsisten |

---

## 2. SYSTEM ARCHITECTURE & TECH STACK (MOBILE)

### 2.1 Gambaran Umum Arsitektur

Aplikasi mobile dibangun dengan pendekatan **Clean Architecture ringan** (Presentation → State → Data/Repository), dengan prinsip **Offline-First**: setiap aksi input pengguna selalu ditulis lebih dahulu ke penyimpanan lokal (*local-first write*), baru kemudian disinkronkan ke server saat koneksi tersedia. Pendekatan ini memastikan teknisi tidak pernah kehilangan data akibat kondisi jaringan lapangan yang tidak stabil.

**Alur data tingkat tinggi:**

```
[Teknisi Lapangan membuka Form Log Valve/Log Tekanan]
            │
            ▼
[UI Layer — Flutter Widgets]
            │  (input decimal, dropdown lokasi/aset)
            ▼
[State Management — Riverpod Notifier]
            │  (validasi form sisi client)
            ▼
[Repository Layer — cek status konektivitas]
            │
     ┌──────┴──────┐
     ▼             ▼
 (Online)      (Offline/Blank Spot)
     │             │
     ▼             ▼
[Dio HTTP Client     [Hive — Queue Box
 (tanpa token,        status: 'pending']
 payload JSON polos)]      │
     │                    │ (saat konektivitas kembali,
     ▼                    │  terdeteksi connectivity_plus)
[routes/api.php —         │  Background Sync Worker
 endpoint publik]  ◄───────┘
     │
     ▼
[Database Terpusat MySQL/PostgreSQL]
     │
     ▼
[Dashboard Web TALL Stack — Livewire/Alpine.js]
```

### 2.2 Peran Masing-Masing Komponen

| Layer | Teknologi | Tanggung Jawab Utama |
|---|---|---|
| **UI Framework** | **Flutter (Dart)** | Rendering antarmuka lintas platform (Android & iOS) dari satu basis kode |
| **State Management** | **Riverpod** *(disarankan utama)* | Mengelola state form, status sinkronisasi, dan cache master data secara reaktif dan *testable* |
| **Networking & API Client** | **Dio** | HTTP client untuk komunikasi JSON dengan `routes/api.php`, mendukung timeout dan retry (tanpa interceptor token — endpoint diakses publik) |
| **Penyimpanan Lokal — Cache & Queue** | **Hive** | Menyimpan cache master data (`Lokasi`, `AsetValve`) untuk dropdown offline, serta antrian (*queue*) log yang belum tersinkron |
| **Penyimpanan Lokal — Identitas Teknisi** | **shared_preferences** | Menyimpan nama teknisi yang terakhir diinput, dipakai sebagai nilai default (prefill) pada form berikutnya — data tidak sensitif sehingga tidak memerlukan enkripsi khusus |
| **Deteksi Konektivitas** | **connectivity_plus** | Memantau status jaringan untuk memicu proses sinkronisasi otomatis |
| **Background Sync** | **workmanager** | Menjalankan proses sinkronisasi antrian secara periodik saat aplikasi tidak aktif di foreground (interval 15 menit — batas minimum OS, lihat Bagian 6) |
| **Foreground Auto-Sync *(baru — v1.2)*** | `Timer.periodic` (Dart, dikelola provider Riverpod, aktif hanya saat `AppLifecycleState.resumed`) | Memicu sinkronisasi setiap 60 detik selama aplikasi aktif di foreground — **bukan** via `workmanager`, karena OS membatasi interval periodic task minimum 15 menit (lihat catatan teknis Bagian 6) |
| **Geolocation *(baru — v1.2)*** | `geolocator` | Menangkap koordinat GPS (`latitude`, `longitude`) presisi tinggi secara otomatis saat form Log Valve/Log Tekanan dibuka |
| **Kamera *In-App* *(baru — v1.2)*** | `camera` | Membangun layar kamera kustom di dalam aplikasi agar akses ke galeri perangkat dapat dinonaktifkan sepenuhnya (strict camera-only capture) |
| **Watermark Gambar *(baru — v1.2)*** | `image` (pemrosesan gambar murni Dart) | Menempelkan teks watermark (nama teknisi, koordinat, waktu, lokasi aset) secara otomatis ke foto setelah diambil, dijalankan di `compute()`/isolate terpisah agar tidak memblokir UI thread |
| **Penyimpanan File Lokal *(baru — v1.2)*** | `path_provider` | Menyediakan direktori penyimpanan lokal perangkat untuk file foto bukti (fisik, bukan Base64 di Hive) |
| **Tautan Eksternal *(baru — v1.2)*** | `url_launcher` | Membuka aplikasi Google Maps dari koordinat yang ditampilkan di halaman Riwayat |
| **Izin Perangkat *(baru — v1.2)*** | `permission_handler` | Mengelola permintaan & status izin kamera dan lokasi dengan dialog rasional sebelum permintaan native |

### 2.3 Perbandingan & Keputusan State Management

> **Aturan emas arsitektur mobile:** Jika state bersifat lintas layar dan bergantung pada hasil panggilan API/data lokal (cache master data, antrian sinkronisasi, nama teknisi aktif), gunakan **provider global via Riverpod**. Jika state murni bersifat lokal pada satu widget (validasi field, status expand/collapse), cukup gunakan state lokal `StatefulWidget` tanpa provider terpisah.

| Opsi | Kelebihan | Pertimbangan | Rekomendasi |
|---|---|---|---|
| **Riverpod** | Compile-safe (tanpa lookup `BuildContext`), mudah di-*mock* untuk unit test repository/queue, cocok untuk async state (`AsyncNotifier`) menangani status sync | Kurva belajar sedikit lebih tinggi dibanding Provider klasik | ✅ **Direkomendasikan sebagai default** |
| **Provider** | Sederhana, banyak referensi, cukup untuk aplikasi skala kecil-menengah | Rawan *runtime error* jika `context` salah tempat, kurang ideal untuk state async kompleks (queue sync) | Alternatif jika tim sudah familiar dan ingin *learning curve* minimal |
| **BLoC** | Pemisahan Event → State sangat eksplisit, cocok untuk alur sinkronisasi kompleks dengan banyak status (idle/syncing/success/failed/retry) | Boilerplate lebih banyak (class Event & State per fitur) | Alternatif jika tim sudah menerapkan BLoC di proyek Flutter lain demi konsistensi standar internal |

### 2.4 Library & Tools Pendukung

| Package (pub.dev) | Fungsi |
|---|---|
| `flutter_riverpod` | State management utama |
| `dio` | HTTP client (timeout & retry) untuk konsumsi endpoint publik |
| `hive`, `hive_flutter` | Local NoSQL storage untuk cache master data & antrian log |
| `shared_preferences` | Menyimpan nama teknisi terakhir untuk prefill form berikutnya |
| `connectivity_plus` | Deteksi status online/offline |
| `workmanager` | Background task untuk sinkronisasi periodik |
| `uuid` | Generate *client-side idempotency key* per log sebelum dikirim (mencegah duplikasi saat retry) |
| `intl` | Format tanggal/waktu (`waktu_kegiatan`, `waktu_pengecekan`) sesuai standar `DATETIME` backend |
| `geolocator` *(v1.2)* | Menangkap koordinat GPS presisi tinggi (`latitude`, `longitude`) |
| `camera` *(v1.2)* | Membangun UI kamera kustom in-app (strict, tanpa akses galeri) |
| `image` *(v1.2)* | Kompositing watermark teks ke atas byte gambar hasil kamera |
| `path_provider` *(v1.2)* | Lokasi direktori penyimpanan lokal untuk file foto bukti |
| `permission_handler` *(v1.2)* | Permintaan & pengecekan status izin kamera serta lokasi |
| `url_launcher` *(v1.2)* | Membuka Google Maps dari koordinat tersimpan di halaman Riwayat |

---

## 3. INTEGRASI API & KONTRAK DATA (DATA CONTRACT)

### 3.1 Daftar Endpoint yang Dikonsumsi

Seluruh endpoint berikut mengacu langsung pada `routes/api.php` yang telah didefinisikan pada PRD Web. Tidak ada endpoint baru yang ditambahkan, namun **middleware autentikasi (`auth:sanctum`) tidak diterapkan** pada endpoint yang dikonsumsi mobile — seluruhnya bersifat publik.

| Method | Endpoint | Fungsi | Autentikasi |
|---|---|---|---|
| `GET` | `/api/lokasi` | Mengambil daftar master `Lokasi` untuk dropdown | Tidak ada (publik) |
| `GET` | `/api/aset-valve` | Mengambil daftar master `AsetValve` beserta relasi lokasinya untuk dropdown | Tidak ada (publik) |
| `POST` | `/api/log-valve` | Mengirim satu entri log aktivitas Gate Valve — **format `multipart/form-data` sejak v1.2** (menyertakan koordinat GPS & file foto bukti) | Tidak ada (publik) |
| `POST` | `/api/log-tekanan` | Mengirim satu entri log pengecekan tekanan air — **format `multipart/form-data` sejak v1.2** (menyertakan koordinat GPS & file foto bukti) | Tidak ada (publik) |

> **Perubahan Format Payload (v1.2):** Sejak penambahan fitur foto bukti lapangan, kedua endpoint di atas **tidak lagi menerima `Content-Type: application/json`**, melainkan wajib `multipart/form-data` agar dapat menyertakan berkas gambar dalam satu request yang sama dengan data teks. Rincian lengkap field, aturan validasi Laravel `Form Request`, dan contoh payload tersedia pada dokumen *Spesifikasi Teknis Fitur Tambahan v1.2*, Bagian 2.

> **Catatan Penting — Deviasi dari NFR Keamanan PRD Web:** PRD Web v1.0 (Bagian 6) mensyaratkan "Endpoint API wajib dilindungi autentikasi token (Laravel Sanctum), rate limiting untuk mencegah abuse". Sesuai keputusan produk pada revisi ini, syarat tersebut **tidak diberlakukan** untuk endpoint yang dikonsumsi aplikasi mobile teknisi. Ini adalah *accepted risk* yang disetujui secara sadar demi kemudahan penggunaan tanpa akun/login — Tim Backend perlu mengecualikan endpoint di atas dari middleware `auth:sanctum` pada `routes/api.php`. Konsekuensinya: siapa pun yang mengetahui URL endpoint dapat mengirim data (termasuk data palsu) tanpa hambatan apa pun.

### 3.1a Dampak terhadap Kolom `nama_teknisi`

Karena tidak ada sesi login, kolom `nama_teknisi` pada tabel `log_valves` sepenuhnya bergantung pada input manual dan **tidak dapat dijadikan bukti identitas terverifikasi**. Kebutuhan "Akuntabilitas Lapangan" (Goal Web PRD #4) pada konteks mobile ini hanya terpenuhi sebatas *pencatatan nama yang diklaim*, bukan *verifikasi identitas*.

### 3.2 Struktur Payload — Mengacu pada Model Backend

Struktur field berikut **wajib identik** dengan struktur tabel `log_valves` dan `log_tekanans` pada PRD Web (bagian 3.2), agar validasi `Form Request` di backend dapat diterima tanpa modifikasi.

**Request: `POST /api/log-valve`**

| Field | Tipe (Dart) | Format Dikirim | Wajib | Catatan |
|---|---|---|---|---|
| `aset_id` | `int` | Integer, hasil pilihan dropdown `AsetValve` | Ya | Divalidasi backend dengan rule `exists:aset_valves,id` |
| `nama_teknisi` | `String` | Input manual, otomatis ter-*prefill* dari nama tersimpan terakhir (`shared_preferences`) | Ya | Dapat diedit bebas oleh teknisi di setiap submit — tidak diverifikasi terhadap sumber data mana pun |
| `waktu_kegiatan` | `String` | ISO 8601 (`yyyy-MM-dd HH:mm:ss`) via `intl` | Ya | Timestamp aktual kejadian, bukan waktu kirim data |
| `aksi_kerja` | `String` (enum) | `"buka"` atau `"tutup"` | Ya | Direpresentasikan sebagai toggle 2 opsi di UI |
| `jumlah_putaran` | `String` | Angka desimal 2 digit, contoh `"12.25"` | Ya | **Dikirim sebagai `String` berformat titik**, bukan `double`, untuk menghindari *floating point rounding error* saat parsing ke kolom `DECIMAL(6,2)` di backend |
| `keterangan` | `String?` | Teks bebas | Tidak | Nullable, sesuai skema `log_valves.keterangan` |
| `latitude` *(v1.2)* | `String` | Desimal 6 digit, contoh `"-6.966667"` | Ya | Ditangkap otomatis via `geolocator` saat form dibuka; disimpan ke kolom `FLOAT(10,6)` |
| `longitude` *(v1.2)* | `String` | Desimal 6 digit, contoh `"109.632222"` | Ya | Sama seperti `latitude` |
| `foto_bukti` *(v1.2)* | `File` (binary) | JPEG hasil kamera *in-app* + watermark, dikirim sebagai bagian `multipart/form-data` | Ya | Diambil dari file fisik tersimpan lokal (bukan Base64); lihat Modul F (4.6) |

**Request: `POST /api/log-tekanan`**

| Field | Tipe (Dart) | Format Dikirim | Wajib | Catatan |
|---|---|---|---|---|
| `lokasi_id` | `int` | Integer, hasil pilihan dropdown `Lokasi` | Ya | Divalidasi backend dengan rule `exists:lokasis,id` |
| `nilai_tekanan` | `String` | Angka desimal 2 digit dalam satuan Bar, contoh `"0.85"` | Ya | Format string titik, konsisten dengan kolom `DECIMAL(5,2)` |
| `waktu_pengecekan` | `String` | ISO 8601 (`yyyy-MM-dd HH:mm:ss`) | Ya | Field `status` **tidak dikirim dari mobile** — dihitung otomatis oleh accessor/observer di backend |
| `latitude` *(v1.2)* | `String` | Desimal 6 digit | Ya | Ditangkap otomatis via `geolocator` saat form dibuka; disimpan ke kolom `FLOAT(10,6)` |
| `longitude` *(v1.2)* | `String` | Desimal 6 digit | Ya | Sama seperti `latitude` |
| `foto_bukti` *(v1.2)* | `File` (binary) | JPEG hasil kamera *in-app* + watermark | Ya | Diambil dari file fisik tersimpan lokal; lihat Modul F (4.6) |

> **Catatan Penanganan Desimal (selaras dengan PRD Web 3.3):** Input desimal di sisi Flutter wajib dibatasi maksimal 2 digit di belakang koma menggunakan `TextInputFormatter` kustom, serta dikonversi ke `String` — bukan `double` — sebelum dikirim melalui Dio, agar tidak terjadi presisi mengambang (mis. `0.85` berubah menjadi `0.8499999999999999`) saat serialisasi JSON.

### 3.2a Kontrak Response & Rekomendasi Tambahan (v1.2)

Agar halaman Riwayat mobile dan dashboard admin Web dapat menampilkan tautan Google Maps serta thumbnail foto, response `201 Created` dari kedua endpoint wajib menyertakan URL foto yang dapat diakses publik (`foto_bukti_url`) selain field yang sudah ada, dibangun backend menggunakan `Storage::url()`. Detail skema JSON lengkap tersedia pada *Spesifikasi Teknis Fitur Tambahan v1.2*, Bagian 2.

> **Rekomendasi Tambahan (bukan bagian dari requirement awal, disarankan mengingat kompleksitas retry unggahan file):** Karena unggahan foto memperbesar risiko *timeout* di jaringan lapangan yang tidak stabil dibanding payload JSON polos sebelumnya, disarankan agar `idempotency_key` (UUID yang sudah dibangkitkan sisi mobile untuk keperluan antrian lokal — lihat Modul C 4.3) turut **dikirim sebagai field pada payload `multipart/form-data`** dan dijadikan `unique` constraint pada tabel `log_valves`/`log_tekanans` di backend. Tanpa ini, retry otomatis atas request yang sebenarnya sudah sukses diterima server (namun response-nya gagal diterima mobile akibat koneksi terputus) berisiko menghasilkan foto & entri duplikat. Keputusan penerapan tetap berada di tangan Tim Backend & Product Manager.

### 3.3 Strategi Caching Master Data (Read-Only)

| Data | Sumber | Strategi Cache Lokal | Refresh Trigger |
|---|---|---|---|
| `Lokasi` | `GET /api/lokasi` | Disimpan di Hive Box `master_lokasi`, dipakai sebagai sumber dropdown utama | Saat aplikasi pertama kali dibuka, saat pull-to-refresh manual, dan otomatis tiap 24 jam jika online |
| `AsetValve` | `GET /api/aset-valve` | Disimpan di Hive Box `master_aset`, mencakup `nama_aset`, `lokasi_id`, `kapasitas_full_putaran` | Sama seperti di atas |

Jika aplikasi dibuka dalam kondisi offline dan cache lokal masih tersedia, dropdown tetap ditampilkan dari cache terakhir dengan indikator label kecil "*Data lokal — terakhir sync: [waktu]*".

---

## 4. FUNCTIONAL REQUIREMENTS (MODUL APLIKASI MOBILE)

### 4.1 Modul A — Input Identitas Teknisi (Tanpa Login)

**User Story:** Sebagai teknisi lapangan, saya ingin langsung memakai aplikasi dengan memasukkan nama saya sekali di awal, tanpa perlu membuat akun atau login, agar saya bisa segera mencatat aktivitas di lapangan tanpa hambatan administratif.

**Alur Proses:**

```
[Aplikasi dibuka pertama kali]
            │
            ▼
[Cek shared_preferences: apakah nama teknisi
 sudah pernah diisi sebelumnya?]
            │
     ┌──────┴──────┐
     ▼             ▼
 (Belum ada)    (Sudah ada)
     │             │
     ▼             ▼
[Tampilkan layar   [Langsung navigasi
 input nama —      ke Home, nama
 field wajib diisi] tersimpan dipakai
     │             sebagai default]
     ▼
[Simpan nama ke shared_preferences]
     │
     ▼
[Navigasi ke Home/Dashboard Ringkas Mobile]
```

**Detail Interaksi:**

| Aksi | Penanganan | Catatan |
|---|---|---|
| Input nama pertama kali | Layar sederhana satu field teks + tombol "Mulai" | Tidak ada password, tidak ada validasi ke server — murni penyimpanan lokal |
| Nama tersimpan dipakai di seluruh form | Diambil dari `shared_preferences` sebagai nilai default field `nama_teknisi` pada Modul C | Tetap dapat diedit langsung di form jika perangkat dipakai bergantian oleh teknisi lain |
| Ubah nama di kemudian hari | Tersedia di tab **Profil** — field teks yang dapat diubah kapan saja | Perubahan langsung tersimpan ke `shared_preferences`, tidak memengaruhi data yang sudah terkirim sebelumnya |
| Tidak ada sesi/expired | Karena tidak ada token, tidak ada konsep "logout paksa" atau sesi kedaluwarsa | Aplikasi selalu dapat langsung dipakai selama nama sudah pernah diisi |

> **Catatan Risiko (diterima sesuai keputusan produk):** Karena tidak ada verifikasi identitas apa pun, nama yang tercatat pada setiap log sepenuhnya bergantung pada kejujuran input teknisi di lapangan. Tidak ada mekanisme untuk mencegah satu perangkat dipakai atas nama teknisi lain, atau untuk memvalidasi bahwa nama yang diketik memang benar terdaftar sebagai pegawai PDAM.

---

### 4.2 Modul B — Sinkronisasi Master Data

**User Story:** Sebagai teknisi lapangan, saya ingin dropdown pilihan Lokasi dan Aset Valve tetap tersedia meskipun saya sedang tidak memiliki sinyal internet, agar saya tetap bisa mengisi form di lapangan terpencil.

**Alur Proses:**

```
[Aplikasi dibuka / pull-to-refresh manual]
            │
            ▼
[Cek konektivitas via connectivity_plus]
            │
     ┌──────┴──────┐
     ▼             ▼
 (Online)      (Offline)
     │             │
     ▼             ▼
[GET /api/lokasi     [Baca langsung dari
 GET /api/aset-valve]  Hive Box lokal]
     │             │
     ▼             │
[Timpa (overwrite)  │
 Hive Box dengan     │
 data terbaru]       │
     │◄──────────────┘
     ▼
[Dropdown Lokasi & Aset Valve siap dipakai
 di Form Log Valve / Log Tekanan]
```

**Detail Interaksi:**

| Fitur | Teknologi | Detail |
|---|---|---|
| Sync otomatis saat aplikasi dibuka | Riverpod `AsyncNotifier` dipanggil di splash/home screen | Tidak memblokir UI — dropdown tetap bisa dipakai dari cache lama sambil sync berjalan di belakang layar |
| Pull-to-refresh manual | `RefreshIndicator` (widget bawaan Flutter) | Memberi kontrol eksplisit kepada teknisi untuk memaksa data terbaru saat sinyal tersedia |
| Indikator status cache | Label kecil di bawah dropdown | Menampilkan `"Terakhir diperbarui: [timestamp]"` agar teknisi sadar data mungkin belum 100% terbaru |
| Pencarian pada dropdown | Local search/filter langsung di Hive Box (tanpa request server) | Karena `AsetValve` bisa berjumlah banyak, dropdown wajib mendukung pencarian teks nama aset |

---

### 4.3 Modul C — Input Log Valve

**User Story:** Sebagai teknisi lapangan, saya ingin mencatat aktivitas buka/tutup Gate Valve langsung dari lokasi kerja — termasuk saat tidak ada sinyal — agar histori aset selalu akurat tanpa saya perlu mengingat dan mencatat ulang nanti.

**Field Form:**

| Field | Tipe UI (Flutter Widget) | Wajib | Catatan |
|---|---|---|---|
| Aset Valve | Dropdown pencarian (dari cache `AsetValve`) | Ya | Menampilkan `nama_aset` + `nama_lokasi` agar teknisi tidak salah pilih aset dengan nama mirip |
| Nama Teknisi | `TextField` teks, ter-*prefill* otomatis | Ya | Terisi otomatis dari nama tersimpan terakhir (`shared_preferences`), namun **dapat diedit bebas** oleh pengguna di setiap submit |
| Waktu Kegiatan | Date & Time Picker bawaan Flutter | Ya | Default terisi waktu saat ini, dapat diubah manual jika input dilakukan setelah kejadian |
| Aksi Kerja | Toggle/Segmented button 2 opsi (**Buka** / **Tutup**) | Ya | Dibuat besar dan jelas karena merupakan input paling krusial, bukan dropdown |
| Jumlah Putaran | `TextField` numerik desimal | Ya | `keyboardType: TextInputType.numberWithOptions(decimal: true)`, dibatasi 2 digit desimal via formatter |
| Keterangan | `TextField` multiline | Tidak | Opsional, untuk catatan tambahan kondisi lapangan |

**Alur Proses (selaras dengan Business Logic Backend PRD Web 4.2):**

```
[Teknisi isi form & tekan tombol "Simpan"]
            │
            ▼
[Validasi client-side: field wajib terisi,
 format desimal jumlah_putaran valid]
            │
            ▼
[Generate idempotency_key (UUID) untuk entri ini]
            │
            ▼
[Cek konektivitas]
            │
     ┌──────┴──────┐
     ▼             ▼
 (Online)      (Offline/Blank Spot)
     │             │
     ▼             ▼
[POST /api/log-valve   [Simpan ke Hive Box
 langsung]              'queue_log_valve'
     │                  status: 'pending']
     ▼                     │
[Sukses → konfirmasi        │ (saat sinyal kembali)
 & form dikosongkan]         ▼
     │                  [Background Sync Worker
     ▼                   memproses antrian FIFO]
[Gagal (network error saat
 kirim online) → otomatis
 fallback masuk antrian
 lokal, bukan hilang]
```

**Business Logic Sisi Mobile:**
1. Setiap entri log yang disimpan lokal wajib memiliki `idempotency_key` unik (UUID v4) agar duplikasi dapat dideteksi jika proses retry terjadi lebih dari sekali.
2. Entri dalam antrian ditandai status: `pending` → `syncing` → `success` / `failed`. Status `failed` setelah percobaan maksimal (*exponential backoff* 5x) ditandai untuk **retry manual** oleh teknisi, bukan dihapus otomatis.
3. Riwayat log yang tersimpan lokal (sudah sync maupun masih pending) tetap dapat dilihat teknisi di halaman "Riwayat" sebagai bentuk transparansi.

---

### 4.4 Modul D — Input Log Tekanan

**User Story:** Sebagai teknisi lapangan, saya ingin mencatat hasil pengecekan tekanan air di suatu wilayah distribusi secara cepat, agar tim manajemen dapat mendeteksi potensi kebocoran sedini mungkin.

**Field Form:**

| Field | Tipe UI (Flutter Widget) | Wajib | Catatan |
|---|---|---|---|
| Lokasi | Dropdown pencarian (dari cache `Lokasi`) | Ya | Daftar wilayah distribusi hasil sinkronisasi Modul B |
| Nilai Tekanan (Bar) | `TextField` numerik desimal | Ya | Dibatasi 2 digit desimal, disertai preview badge warna status secara *live* saat mengetik |
| Waktu Pengecekan | Date & Time Picker | Ya | Default waktu saat ini |

**Preview Status Real-Time:**
Saat teknisi mengetik nilai tekanan, aplikasi menampilkan badge warna status secara instan di sisi klien (**tanpa** menunggu response server), menggunakan threshold yang identik dengan backend:

| Kondisi Nilai | Status | Warna Badge |
|---|---|---|
| ≥ 1.0 Bar | Normal | Hijau |
| 0.5 – < 1.0 Bar | Rendah | Kuning |
| = 0 Bar | Kritis | Merah |

> **Catatan:** Kalkulasi ini **hanya preview visual di sisi mobile** agar teknisi mendapat umpan balik instan. Status final yang tersimpan tetap sepenuhnya dihitung ulang oleh accessor/observer backend (sesuai PRD Web 4.3), demi menjaga *single source of truth*.

**Alur Proses:** Mengikuti pola offline-queue yang identik dengan Modul C (4.3) — submit → cek konektivitas → kirim langsung atau masuk antrian Hive `queue_log_tekanan` → background sync.

---

### 4.5 Modul E — Koordinat Lokasi & Tautan Google Maps *(baru — v1.2)*

**User Story:** Sebagai teknisi lapangan, saya ingin lokasi persis tempat saya mencatat aktivitas terekam otomatis, dan sebagai admin, saya ingin bisa langsung membuka lokasi tersebut di Google Maps hanya dengan satu ketukan, agar validasi lapangan tidak perlu bertanya ulang ke teknisi.

**Detail Interaksi:**

| Aksi | Penanganan | Catatan |
|---|---|---|
| Penangkapan koordinat | Otomatis saat form Log Valve/Log Tekanan dibuka, via `geolocator` (`LocationAccuracy.high`) | Tidak memerlukan input manual dari teknisi; berjalan di latar belakang begitu halaman form aktif |
| Izin lokasi belum diberikan | Tampilkan dialog rasional (`permission_handler`) lalu arahkan ke pengaturan sistem jika ditolak permanen | Form tetap dapat dibuka, namun tombol "Simpan" nonaktif hingga koordinat berhasil diambil, karena `latitude`/`longitude` wajib pada kontrak API |
| GPS lambat/tidak akurat (indoor, area tertutup) | Tampilkan indikator "Mencari lokasi…" dengan opsi "Coba Lagi" | Mencegah form tersubmit dengan koordinat kosong atau usang tanpa disadari teknisi |
| Tampilan koordinat di Riwayat | Teks koordinat (`latitude, longitude`) ditampilkan sebagai elemen yang dapat diketuk (`InkWell`) | Konsisten dengan gaya link, agar teknisi/admin sadar elemen ini interaktif |
| Aksi ketuk koordinat | Membuka Google Maps via `url_launcher` dengan skema `https://maps.google.com/?q=lat,lng` | Dipilih dibanding skema `google.navigation:q=` karena bekerja lintas platform (Android & iOS) tanpa bergantung pada aplikasi Google Maps native terpasang |

> **Ketergantungan terhadap Modul F:** Koordinat yang ditangkap pada modul ini adalah sumber data yang sama yang dibubuhkan ke watermark foto (Modul F) — proses tangkap GPS hanya dilakukan **satu kali** per sesi pengisian form dan dipakai ulang (bukan ditangkap dua kali) untuk menjaga konsistensi angka antara kolom `latitude`/`longitude` dan teks watermark pada gambar.

---

### 4.6 Modul F — Foto Bukti Lapangan: Strict Camera & Watermark Otomatis *(baru — v1.2)*

**User Story:** Sebagai admin operasional, saya ingin memastikan foto yang dilampirkan teknisi benar-benar diambil saat itu juga di lokasi kejadian — bukan foto lama dari galeri — agar bukti lapangan dapat dipercaya sebagai dasar validasi maupun audit.

**Prinsip Utama:**

| Aspek | Ketentuan |
|---|---|
| Sumber gambar | **Wajib** dari kamera *in-app* (custom camera screen). Akses ke galeri perangkat **dinonaktifkan sepenuhnya** — tidak tersedia tombol/opsi apa pun menuju galeri di alur pengambilan foto |
| Watermark | Ditempelkan otomatis & permanen ke piksel gambar (bukan overlay UI yang bisa dihilangkan), segera setelah kamera menangkap foto, sebelum foto disimpan ke penyimpanan lokal |
| Isi watermark | Nama Teknisi (dari `shared_preferences`), Koordinat (`latitude, longitude` presisi dari Modul E), Waktu (jam & tanggal aktual saat rana ditekan), Lokasi Aset (nama Gate Valve terpilih dari dropdown) |
| Penyimpanan | File JPEG fisik di direktori lokal aplikasi (`path_provider`) — **bukan** string Base64 di dalam Hive, untuk mencegah pembengkakan ukuran database lokal dan risiko *out-of-memory* |
| Editability | Foto final tidak dapat diedit/dihapus watermark-nya oleh teknisi setelah proses stamping selesai; opsi yang tersedia hanya "Ambil Ulang" (mengulang seluruh proses dari awal) |

**Alur Proses Ringkas:**

```
[Teknisi menekan tombol "Ambil Foto Bukti" di form]
            │
            ▼
[Buka layar kamera kustom in-app — TIDAK ADA
 tombol/akses menuju galeri perangkat]
            │
            ▼
[Teknisi menekan tombol rana]
            │
            ▼
[Ambil data konteks saat itu juga:
 nama teknisi (shared_preferences),
 latitude/longitude (Modul E),
 timestamp aktual, nama lokasi GV (dropdown)]
            │
            ▼
[Proses watermark: komposit teks ke atas
 byte gambar hasil kamera]
            │
            ▼
[Simpan file JPEG hasil komposit ke
 direktori lokal perangkat]
            │
            ▼
[Simpan PATH file (bukan bytes) ke state form
 → siap disertakan pada saat submit/antrian]
```

> **Konfirmasi visual:** Setelah proses watermark selesai, aplikasi menampilkan pratinjau foto akhir (lengkap dengan watermark yang sudah menempel) kepada teknisi sebelum form dapat disubmit, sehingga teknisi dapat memverifikasi keterbacaan watermark di lokasi dengan pencahayaan yang mungkin sulit.

> Rincian implementasi teknis (pemilihan package, pendekatan kompositing watermark, dan penanganan performa) didokumentasikan pada *Spesifikasi Teknis Fitur Tambahan v1.2*, Bagian 3.

---

### 4.7 Modul G — Sinkronisasi Lanjutan (Auto-Sync & Manual Sync) *(baru — v1.2)*

**User Story:** Sebagai teknisi lapangan, saya ingin antrian data (termasuk foto berukuran besar) terkirim otomatis begitu ada sinyal, namun saya juga ingin bisa memaksa pengiriman secara instan saat saya tahu sedang berada di titik dengan sinyal bagus, agar saya tidak perlu menunggu tanpa kepastian.

**Detail Interaksi:**

| Mekanisme | Trigger | Interval | Catatan |
|---|---|---|---|
| Auto-Sync (foreground) | Aplikasi berada di `AppLifecycleState.resumed` | Setiap 60 detik | Dikelola `Timer.periodic` di sisi Dart, **bukan** `workmanager` (lihat catatan teknis Bagian 6) |
| Auto-Sync (background) | Perubahan konektivitas terdeteksi (`connectivity_plus`) atau jadwal periodik OS | Setiap 15 menit | Via `workmanager`; 15 menit merupakan batas minimum interval *periodic task* yang diizinkan Android WorkManager |
| Manual Sync | Teknisi menekan tombol "Sinkronkan Data" | Instan, kapan pun ditekan | Tombol menonjol di tab Home/Riwayat; memaksa proses sinkronisasi FIFO atas seluruh antrian `pending`/`failed` tanpa menunggu interval berjalan |

**Alur Proses (Manual Sync):**

```
[Teknisi menekan "Sinkronkan Data"]
            │
            ▼
[Tampilkan loading indicator pada tombol]
            │
            ▼
[Proses seluruh antrian Hive berstatus
 'pending'/'failed' secara FIFO —
 abaikan status konektivitas yang terdeteksi,
 biarkan Dio timeout yang menentukan gagal/berhasil]
            │
            ▼
[Tampilkan ringkasan hasil: "X berhasil, Y gagal,
 Z masih dalam proses"]
```

**Business Logic Tambahan:**
1. Auto-sync dan manual sync memanggil **fungsi sinkronisasi yang sama** di *repository layer* — tidak ada logic ganda, untuk menghindari kondisi antrian diproses dua kali secara bersamaan (*race condition*), diamankan dengan flag `isSyncing` sederhana.
2. Manual sync tidak dibatasi *exponential backoff* — teknisi dapat menekannya berulang kali; namun entri yang sedang berstatus `syncing` tidak diproses ulang hingga selesai atau timeout.
3. Indikator jumlah antrian `pending` tetap ditampilkan sebagai badge di tab Home (mengacu pola yang sudah ada pada Bagian 5.1), diperbarui setiap kali proses sync (baik otomatis maupun manual) selesai.

---

## 5. USER INTERFACE (UI) & UX GUIDELINES (MOBILE)

### 5.1 Struktur Navigasi

```
┌───────────────────────────────────────────┐
│           App Bar (Judul Halaman)          │
├─────────────────────────────────────────────┤
│                                               │
│              Konten Halaman Aktif            │
│         (Form / Riwayat / Profil)            │
│                                               │
├─────────────────────────────────────────────┤
│ Home │ Log Valve │ Log Tekanan │ Riwayat │ Profil │
└───────────────────────────────────────────┘
              (Bottom Navigation Bar)
```

| Tab | Fungsi |
|---|---|
| **Home** | Ringkasan singkat: jumlah log hari ini, badge status antrian jika ada entri `pending`; **(v1.2)** tombol "Sinkronkan Data" yang menonjol untuk memicu manual sync (Modul G) |
| **Log Valve** | Akses langsung ke Modul C (form input); **(v1.2)** menyertakan penangkapan GPS otomatis (Modul E) dan tombol "Ambil Foto Bukti" (Modul F) |
| **Log Tekanan** | Akses langsung ke Modul D (form input); **(v1.2)** menyertakan penangkapan GPS otomatis (Modul E) dan tombol "Ambil Foto Bukti" (Modul F) |
| **Riwayat** | Daftar seluruh log yang pernah dikirim teknisi ini beserta status (`success`/`pending`/`failed`), mendukung retry manual; **(v1.2)** setiap kartu log menampilkan thumbnail foto bukti dan koordinat yang dapat diketuk untuk membuka Google Maps (Modul E), serta tombol "Sinkronkan Data" |
| **Profil** | Nama teknisi tersimpan (dapat diubah kapan saja), indikator versi aplikasi — tanpa akun/logout |

### 5.2 Skema Warna Status (Selaras dengan Web Dashboard)

Agar persepsi visual teknisi di lapangan konsisten dengan yang dilihat tim manajemen di web, palet warna mobile mengacu langsung pada kelas Tailwind yang sama pada PRD Web (5.2):

| Status | Referensi Tailwind (Web) | Hex Warna (Flutter `Color`) |
|---|---|---|
| Normal / Sukses | `green-600` | `#16A34A` |
| Rendah / Peringatan | `yellow-600` | `#CA8A04` |
| Kritis / Bahaya | `red-600` | `#DC2626` |
| Netral / Info / Pending Sync | `slate-600` | `#475569` |

### 5.3 UX Khusus Lapangan

| Aspek | Guideline | Alasan |
|---|---|---|
| **Ukuran Tombol** | Minimum *touch target* `56dp` untuk tombol aksi utama (Simpan, Buka/Tutup) | Memudahkan penggunaan dengan sarung tangan kerja atau kondisi tangan basah |
| **Kontras Warna** | Rasio kontras teks-background minimal 4.5:1 (WCAG AA), hindari warna pastel/transparan pada elemen penting | Layar tetap terbaca di bawah sinar matahari langsung |
| **Tema** | Mode terang (*light mode*) sebagai default | Dark mode cenderung lebih sulit dibaca di luar ruangan pada siang hari |
| **Keyboard Desimal** | `TextInputType.numberWithOptions(decimal: true)` dengan formatter yang memaksa pemisah desimal berupa titik (`.`), meski locale perangkat Indonesia default menggunakan koma | Menjaga konsistensi format data yang dikirim ke backend (`DECIMAL` format titik), menghindari kesalahan parsing |
| **Feedback Aksi** | Snackbar hijau untuk "terkirim", Snackbar abu-abu untuk "tersimpan offline, menunggu sync" | Teknisi perlu kepastian instan bahwa data mereka "aman", baik online maupun offline |
| **Ukuran Font** | Minimum 16sp untuk teks input, 14sp untuk label | Keterbacaan pada kondisi pencahayaan luar ruangan yang bervariasi |
| **Kontras Watermark *(v1.2)*** | Teks watermark dibubuhkan di atas kotak latar semi-transparan gelap, bukan langsung di atas foto polos | Watermark tetap terbaca di atas foto lapangan dengan latar belakang yang bervariasi (langit terang, aspal gelap, dsb.) |
| **Feedback Kamera *(v1.2)*** | Tampilkan pratinjau hasil watermark sebelum form dapat disubmit, dengan opsi "Ambil Ulang" | Memberi kesempatan teknisi memverifikasi keterbacaan watermark sebelum data terkirim, karena foto tidak dapat diedit setelah stamping |
| **Indikator Sinkronisasi *(v1.2)*** | Tombol "Sinkronkan Data" menampilkan status berbeda: idle, sedang berjalan (spinner), selesai (ringkasan hasil) | Memberi kepastian instan kepada teknisi bahwa penekanan tombol benar-benar memproses antrian, khususnya saat mengirim foto berukuran besar yang memakan waktu lebih lama dari payload teks |

---

## 6. NON-FUNCTIONAL REQUIREMENTS

| Aspek | Requirement |
|---|---|
| **Offline-First / Strategi Antrian** | Setiap submit form (Log Valve/Log Tekanan) wajib ditulis lebih dahulu ke Hive Box lokal dengan status `pending`; `connectivity_plus` memantau perubahan jaringan untuk memicu sinkronisasi otomatis via `workmanager`; setiap entri memiliki `idempotency_key` (UUID) untuk mencegah duplikasi data akibat retry |
| **Strategi Retry** | Retry otomatis dengan *exponential backoff* (5 detik → 15 detik → 1 menit → 5 menit → 15 menit), maksimal 5 percobaan sebelum entri ditandai `failed` dan wajib retry manual oleh teknisi dari halaman Riwayat |
| **Keamanan — Endpoint Publik (Risiko Diterima)** | Endpoint `/api/log-valve`, `/api/log-tekanan`, `/api/lokasi`, `/api/aset-valve` diakses tanpa autentikasi token, sesuai keputusan produk (v1.1). Tidak ada validasi identitas pengirim; data dapat dipalsukan oleh siapa pun yang mengetahui URL endpoint. Risiko ini diterima secara sadar demi kemudahan penggunaan tanpa akun |
| **Keamanan — Transmisi Data** | Seluruh komunikasi API tetap wajib melalui HTTPS (TLS) agar payload tidak dapat disadap dalam perjalanan, meskipun endpoint itu sendiri tidak memerlukan token |
| **Performa — Ukuran Aplikasi** | Target ukuran AAB/APK release **< 25 MB** melalui *code shrinking* (R8/ProGuard Android), *split per ABI*, dan menghindari dependency yang tidak esensial |
| **Performa — Perangkat Rendah** | Tetap responsif pada perangkat Android entry-level (RAM 2GB, Android 8+) yang umum digunakan teknisi lapangan; hindari widget rebuild berlebihan dengan `const` constructor dan `select` pada Riverpod |
| **Performa — Penggunaan Data** | Payload API dijaga minimal (hanya field yang diperlukan), ikon dikompresi, cache master data mengurangi frekuensi request berulang |
| **Konsistensi Validasi** | Aturan validasi desimal (`numeric`, maksimal 2 digit) di sisi Flutter wajib identik dengan rule Laravel `regex:/^\d+(\.\d{1,2})?$/` pada PRD Web 3.3, agar tidak ada data yang lolos validasi client namun ditolak server |
| **Kompatibilitas Platform** | Mendukung Android 8.0 (API 26) ke atas dan iOS 13 ke atas, mengikuti *baseline* dukungan Flutter stable channel terkini |
| **Audit & Transparansi Lokal** | Halaman Riwayat wajib menampilkan seluruh log yang pernah dibuat teknisi di perangkatnya (`success`, `pending`, `failed`) sebagai alat bantu troubleshooting mandiri |
| **Catatan Teknis — Batas Interval `workmanager` *(v1.2)*** | Android **WorkManager** membatasi *periodic task* pada interval minimum **15 menit** — tidak dapat dikonfigurasi lebih cepat, sekalipun diminta. Requirement "sinkronisasi tiap 60 detik saat aplikasi aktif" **tidak dapat** dipenuhi lewat `workmanager`, dan wajib diimplementasikan sebagai `Timer.periodic` di sisi Dart yang hanya aktif selama `AppLifecycleState.resumed` (lihat Modul G, 4.7). `workmanager` tetap dipakai khusus untuk siklus 15 menit di background, yang justru sudah sesuai batas minimum OS tersebut |
| **Catatan Teknis — Background Sync iOS *(v1.2)*** | `BGTaskScheduler` pada iOS bersifat *opportunistic* (dijadwalkan sepenuhnya oleh OS, tidak ada jaminan interval pasti seperti Android WorkManager). Auto-sync background di iOS harus diperlakukan sebagai *best-effort*, dengan auto-sync foreground (60 detik) dan tombol Manual Sync sebagai jaring pengaman utama untuk memastikan data tetap terkirim |
| **Penyimpanan Foto Bukti *(v1.2)*** | Foto disimpan sebagai file fisik JPEG di direktori lokal aplikasi (`path_provider`), **bukan** Base64 di Hive. Setelah entri berhasil tersinkron ke server, file lokal disarankan tetap disimpan selama periode retensi tertentu (mis. 7 hari) sebagai cadangan troubleshooting, sebelum dibersihkan otomatis untuk mencegah penyimpanan perangkat penuh |
| **Kompresi Foto *(v1.2)*** | Foto hasil kamera + watermark dikompresi (kualitas JPEG ±85%) sebelum disimpan/diunggah, menyeimbangkan keterbacaan watermark dengan ukuran file agar tidak membebani kuota data maupun waktu unggah di sinyal lemah |
| **Izin Perangkat (Permission) *(v1.2)*** | Aplikasi wajib menampilkan dialog rasional sebelum meminta izin native kamera dan lokasi (`permission_handler`); jika izin ditolak permanen, arahkan teknisi ke pengaturan sistem, dan nonaktifkan tombol submit hingga izin diberikan — karena `latitude`, `longitude`, dan `foto_bukti` bersifat wajib pada kontrak API |
| **Privasi Data Personal *(v1.2)*** | Watermark menempelkan nama teknisi, koordinat presisi, dan waktu secara permanen ke dalam foto — data ini tergolong data personal/lokasi. Direkomendasikan agar hal ini dicantumkan dalam kebijakan privasi internal PDAM terkait penggunaan perangkat kerja, meski di luar cakupan teknis PRD ini |
| **Ketahanan Antrian Bermedia *(v1.2)*** | Proses sinkronisasi wajib memverifikasi keberadaan file foto lokal (`File.existsSync()`) sebelum mengunggah; jika file hilang (mis. terhapus manual/OS cleanup), entri ditandai `failed` dengan alasan spesifik alih-alih mengirim data tanpa foto ke server (yang pasti ditolak validasi backend) |

---

## LAMPIRAN — GLOSARIUM ISTILAH (MOBILE)

| Istilah | Definisi |
|---|---|
| **Offline-First** | Pendekatan arsitektur di mana aplikasi menulis data ke penyimpanan lokal terlebih dahulu, tidak bergantung pada koneksi internet untuk berfungsi |
| **Blank Spot** | Area/lokasi geografis tanpa jangkauan sinyal seluler/internet |
| **Idempotency Key** | Kunci unik (UUID) yang dilekatkan pada setiap transaksi untuk memastikan proses retry tidak menghasilkan duplikasi data di server |
| **Exponential Backoff** | Strategi penundaan antar percobaan ulang (*retry*) yang meningkat secara eksponensial untuk menghindari pembebanan server berlebih |
| **Hive Box** | Unit penyimpanan data pada database lokal NoSQL Hive di Flutter |
| **Endpoint Publik** | Endpoint API yang dapat diakses tanpa autentikasi/token apa pun |
| **Accepted Risk** | Risiko yang telah diidentifikasi dan disetujui secara sadar oleh pemilik produk untuk tetap dijalankan, biasanya demi trade-off lain seperti kemudahan penggunaan |
| **Prefill** | Nilai default yang otomatis mengisi sebuah field form berdasarkan data tersimpan sebelumnya, namun tetap dapat diubah pengguna |
| **Watermark** *(v1.2)* | Teks/informasi yang ditempelkan secara permanen ke piksel gambar, tidak dapat dihilangkan tanpa mengedit ulang gambar aslinya |
| **Strict Camera** *(v1.2)* | Pendekatan pengambilan foto yang hanya mengizinkan sumber dari kamera langsung (real-time), tanpa opsi memilih gambar yang sudah ada di galeri perangkat |
| **Multipart/Form-Data** *(v1.2)* | Format `Content-Type` HTTP yang memungkinkan satu request membawa kombinasi data teks dan berkas biner (seperti gambar) sekaligus |
| **WorkManager Periodic Task** *(v1.2)* | Mekanisme Android untuk menjalankan tugas berulang di latar belakang; dibatasi OS pada interval minimum 15 menit |
| **BGTaskScheduler** *(v1.2)* | Mekanisme iOS untuk menjalankan tugas latar belakang secara *opportunistic*, dijadwalkan sepenuhnya oleh keputusan sistem operasi, bukan interval tetap |
| **Reverse/Google Maps Deep Link** *(v1.2)* | URI yang saat diketuk membuka lokasi tertentu langsung di aplikasi/situs Google Maps, dibangun dari pasangan koordinat `latitude,longitude` |

---

*Dokumen ini merupakan turunan langsung (derivative) dari PRD Web Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM v1.0, dan bersifat living document yang dapat direvisi seiring diskusi teknis lebih lanjut antara Product Manager, Tim Mobile, dan Tim Backend. Untuk rincian implementasi teknis fitur v1.2 (rancangan migration Laravel, kontrak API `multipart/form-data` lengkap, dan panduan alur logika Flutter), lihat dokumen pendamping* **`Spesifikasi_Teknis_Fitur_Tambahan_v1.2.md`**.
