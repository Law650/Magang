# PRODUCT REQUIREMENT DOCUMENT (PRD)
## SISTEM INFORMASI MANAJEMEN ASET & MONITORING DISTRIBUSI AIR PDAM
### Fase 1 — Web Dashboard & Core Backend

| | |
|---|---|
| **Nama Proyek** | SIM Aset & Monitoring Distribusi Air PDAM |
| **Fase** | Fase 1 — Web Dashboard & Core Backend |
| **Tech Stack** | TALL Stack (Tailwind CSS, Alpine.js, Livewire, Laravel) |
| **Versi Dokumen** | 1.0 |
| **Tanggal Terbit** | 07 Juli 2026 |
| **Status** | Draft untuk Review — Siap Dikembangkan |
| **Klasifikasi** | Internal / Confidential |

---

## Document Control

### Riwayat Revisi

| Versi | Tanggal | Deskripsi Perubahan | Disusun/Direvisi Oleh |
|---|---|---|---|
| 0.1 | 01 Jul 2026 | Draft awal kerangka PRD berdasarkan hasil elisitasi kebutuhan. | Product Management Team |
| 1.0 | 07 Jul 2026 | Finalisasi cakupan Fase 1: Autentikasi, Executive Dashboard & GIS Map, Master Aset, Log GV & Tekanan, Export Engine. | Product Management Team |

### Daftar Distribusi

| Peran | Tanggung Jawab Terhadap Dokumen | Tindakan |
|---|---|---|
| Product Manager | Pemilik dokumen, memastikan kelengkapan requirement | Approve |
| Tech Lead / System Analyst | Validasi kelayakan teknis arsitektur TALL Stack | Review |
| Backend Developer (Laravel) | Implementasi Eloquent Model, Migration, Controller | Reference |
| Frontend Developer (Livewire/Alpine) | Implementasi komponen reaktif & interaktif | Reference |
| QA Engineer | Menyusun test case berbasis functional requirement | Reference |

> **Catatan:** Dokumen ini bersifat *living document*. Setiap perubahan cakupan (scope) pada Fase 1 wajib melalui proses *change request* dan disetujui oleh Product Manager sebelum dieksekusi oleh tim pengembang.

---

## Daftar Isi

1. [Project Information & Objectives](#1-project-information--objectives)
2. [System Architecture & Tech Stack](#2-system-architecture--tech-stack)
3. [Database & Relational Model Scope](#3-database--relational-model-scope)
4. [Functional Requirements (Per Modul)](#4-functional-requirements-per-modul)
5. [User Interface (UI) Layout Guidelines](#5-user-interface-ui-layout-guidelines)
6. [Non-Functional Requirements](#6-non-functional-requirements)

---

## 1. Project Information & Objectives

### 1.1 Latar Belakang

Operasional distribusi air bersih PDAM saat ini menghadapi tantangan signifikan dalam hal visibilitas kondisi infrastruktur jaringan pipa secara real-time. Pemantauan bukaan Gate Valve (GV) dan tekanan air pada titik-titik distribusi masih dilakukan secara manual dan terfragmentasi, sehingga menyulitkan proses pengambilan keputusan operasional yang cepat dan berbasis data.

Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM dikembangkan sebagai solusi terpusat yang berfungsi sebagai *control room* digital. Pada Fase 1, pembangunan difokuskan pada Web Dashboard yang menjadi fondasi arsitektur bagi seluruh ekosistem sistem — termasuk kesiapan backend untuk mendukung aplikasi mobile bagi teknisi lapangan pada fase berikutnya.

> **Batasan Ruang Lingkup (Scope Boundary):** Fase 1 TIDAK mencakup pembangunan aplikasi mobile. Seluruh input data lapangan (log GV, log tekanan, foto eviden) pada fase ini dimasukkan melalui Web Dashboard oleh Admin/Teknisi yang login lewat web, namun struktur database dan sistem autentikasi WAJIB dirancang agar API-ready untuk Fase 2 (Mobile App).

### 1.2 Tujuan Utama (Goals)

Proyek Fase 1 ini disusun untuk mencapai lima tujuan strategis berikut:

#### 1.2.1 Centralized User Authentication
Mengamankan seluruh akses sistem melalui satu pintu masuk (*single point of authentication*) berbasis web. Seluruh akun pengguna — termasuk akun teknisi lapangan — dibuat dan dikelola sepenuhnya oleh Administrator melalui Web Dashboard. Pendekatan ini memastikan bahwa ketika aplikasi mobile dikembangkan pada fase mendatang, teknisi cukup menggunakan kredensial yang sama tanpa perlu proses pendaftaran ulang.

#### 1.2.2 Real-time Executive Overview & GIS Mapping
Menyediakan halaman utama (dashboard) yang menyajikan ringkasan kondisi operasional secara langsung (*near real-time*), dilengkapi visualisasi peta interaktif (GIS) yang menampilkan sebaran aset dan status kondisinya di seluruh wilayah distribusi.

#### 1.2.3 Kontrol Presisi Aset
Melacak sisa bukaan fisik Gate Valve secara otomatis dan presisi, dihitung dari akumulasi riwayat putaran (buka/tutup) yang dicatat oleh petugas, sehingga kapasitas aktual valve selalu termonitor tanpa perlu pengecekan manual berulang ke lapangan.

#### 1.2.4 Early Warning System
Memantau tren historis dan log tekanan air pada setiap titik pantau untuk mendeteksi dan mengklasifikasikan status tekanan (Normal, Rendah, Kritis) secara otomatis, sehingga tim operasional dapat merespons potensi gangguan distribusi sebelum berdampak luas ke pelanggan.

#### 1.2.5 Future-Proof Database
Membangun fondasi skema basis data yang sejak awal telah mengakomodasi data spasial (koordinat presisi latitude/longitude) dan data media (foto eviden lapangan), sebagai kesiapan integrasi penuh dengan aplikasi mobile pada fase pengembangan selanjutnya.

### 1.3 Sasaran Pengguna (Target Users)

| Role | Akses Utama | Kebutuhan Fungsional |
|---|---|---|
| **Admin** | Penuh (Full Access) | Manajemen akun pengguna, manajemen master aset, akses seluruh modul & laporan |
| **Manajemen** | Read-only + Export | Melihat dashboard eksekutif, tren analitik, dan mengekspor laporan untuk pengambilan keputusan |
| **Teknisi** | Input & Lihat Terbatas | Mencatat log Gate Valve dan log tekanan air, mengunggah foto eviden lapangan |

### 1.4 Ruang Lingkup Fase 1 (In-Scope)

- Autentikasi terpusat berbasis web (session-based) dengan proteksi throttling.
- Manajemen pengguna (CRUD akun) beserta penetapan Role: Admin, Manajemen, Teknisi.
- Executive Dashboard dengan auto-refresh metrik, peta interaktif GIS, dan visualisasi chart.
- Manajemen Master Data Aset (Gate Valve & Lokasi).
- Manajemen Log operasional Gate Valve dan Log Tekanan Air, termasuk unggah foto eviden.
- Sistem pelaporan (export .xlsx) yang mempertahankan parameter filter aktif.
- Fondasi backend (struktur tabel & autentikasi) yang API-ready untuk aplikasi mobile Fase 2.

**Di Luar Ruang Lingkup (Out of Scope) Fase 1:**

- Aplikasi mobile native/hybrid untuk teknisi lapangan (direncanakan Fase 2).
- Endpoint API publik (Sanctum/Passport token issuance) — pada Fase 1 hanya disiapkan strukturnya, belum diaktifkan sepenuhnya untuk konsumsi eksternal.
- Notifikasi push/SMS/WhatsApp otomatis ke pelanggan akhir.
- Modul billing/tagihan pelanggan.

### 1.5 Metrik Keberhasilan (Success Metrics)

| Indikator | Target Fase 1 |
|---|---|
| Waktu refresh data dashboard | Update otomatis setiap 30 detik tanpa reload halaman (via `wire:poll`) |
| Akurasi status marker peta | 100% marker mencerminkan status data terakhir di database |
| Keamanan login | Percobaan login gagal dibatasi (throttled) sesuai standar Laravel |
| Kelengkapan data spasial | Seluruh entitas Aset & Log wajib memiliki koordinat latitude/longitude |
| Kesiapan integrasi mobile | Struktur tabel `users` & data pendukung tervalidasi API-ready (Sanctum-compatible) |

---

## 2. System Architecture & Tech Stack

### 2.1 Gambaran Umum Arsitektur

Aplikasi Fase 1 dibangun sepenuhnya di atas TALL Stack — akronim dari Tailwind CSS, Alpine.js, Livewire, dan Laravel. Pendekatan ini dipilih karena memungkinkan pengembangan antarmuka yang reaktif secara server-side (*server-driven UI*) tanpa kompleksitas membangun API terpisah untuk setiap interaksi kecil, sekaligus tetap menyisakan ruang bagi Alpine.js untuk menangani interaktivitas murni sisi klien (peta, modal, lightbox).

Prinsip pembagian tanggung jawab antar layer adalah sebagai berikut: Laravel menangani seluruh logika bisnis, validasi, dan akses data; Livewire menjembatani state server dengan render ulang komponen UI secara parsial (tanpa reload penuh); Alpine.js menangani perilaku UI yang bersifat lokal dan tidak memerlukan *round-trip* ke server; serta Tailwind CSS menyediakan sistem desain yang konsisten di seluruh halaman.

#### 2.1.1 Diagram Interaksi Layer

```
[ Browser ]
   │
   ├── Tailwind CSS  → Utility classes untuk seluruh styling UI
   │
   ├── Alpine.js     → x-data, x-show, x-on (modal, dropdown, init peta Leaflet)
   │        │
   │        └── wire:ignore  → melindungi elemen dari DOM-diffing Livewire
   │
   ├── Livewire      → wire:poll, wire:model, wire:click (reactivity server-driven)
   │        │
   │        └── AJAX request (tanpa reload) ke Livewire Component (PHP)
   │
   └── Laravel (Backend & Data Engine)
            ├── Eloquent ORM  → Model: User, Aset, LogGateValve, LogTekananAir
            ├── Auth (Session/Cookie) + Throttle Middleware
            ├── Laravel Excel → Export Engine (.xlsx)
            └── MySQL/PostgreSQL Database
```

### 2.2 Peran Tiap Komponen Tech Stack

| Komponen | Kategori | Peran dalam Sistem |
|---|---|---|
| **Laravel** | Backend & Data Engine | Core MVC, routing, Eloquent ORM, autentikasi (session & cookie), middleware keamanan, dan menjadi satu-satunya sumber kebenaran (*single source of truth*) untuk seluruh data. |
| **Livewire** | Server-Driven Reactivity | Pencarian real-time pada tabel log, filter tab tanpa reload, polling metrik dashboard (`wire:poll`), serta validasi form pembuatan akun secara real-time tanpa refresh halaman. |
| **Alpine.js** | Client-Side Interactivity | Modal pop-up (form aset, lightbox foto), dropdown menu navigasi, serta inisialisasi dan kontrol peta interaktif (Leaflet.js/Google Maps API) yang berjalan murni di browser. |
| **Tailwind CSS** | Design System | Utility-first styling untuk seluruh tampilan, termasuk halaman login, grid dashboard, progress bar bukaan valve, dan badge status. |
| **Chart.js** | Data Visualization | Doughnut chart proporsi status valve dan line/bar chart tren tekanan air, dirender di dalam container ber-atribut `wire:ignore`. |
| **Laravel Excel** | Reporting Engine | Menghasilkan file .xlsx dari data log/aset dengan tetap mempertahankan parameter filter dan pencarian yang sedang aktif di Livewire. |

### 2.3 Arsitektur Keamanan Login (Authentication Security)

Sistem autentikasi menggunakan mekanisme bawaan Laravel berbasis Session & Cookie (bukan token API pada Fase 1, karena konsumen utama adalah Web Dashboard). Rancangan ini tetap disiapkan agar kompatibel dengan penerbitan token Sanctum di masa depan tanpa perlu migrasi ulang struktur tabel `users`.

- **Hashing Password:** Password di-hash menggunakan algoritma Bcrypt (default hashing Laravel) — tidak pernah disimpan atau ditampilkan dalam bentuk plain text.
- **Rate Limiting:** Login Throttling diterapkan menggunakan middleware `throttle` bawaan Laravel untuk membatasi jumlah percobaan login yang gagal dalam rentang waktu tertentu (mitigasi *brute-force attack*).
- **Middleware Guard:** Setiap request ke route dashboard dan modul internal wajib melewati middleware `auth`, dengan pengecekan tambahan berbasis role (Admin, Manajemen, Teknisi) menggunakan Gate/Policy Laravel.
- **Session Management:** Session disimpan di server (database/redis driver direkomendasikan untuk skala enterprise) dengan regenerasi session ID setiap kali login berhasil untuk mencegah *session fixation*.

### 2.4 Arsitektur Pemetaan (GIS Mapping Layer)

Peta interaktif diimplementasikan menggunakan Leaflet.js atau Google Maps API, diinisialisasi melalui Alpine.js pada saat komponen dimuat (`x-init`). Karena peta memiliki DOM kompleks yang dikelola oleh library JavaScript eksternal, elemen pembungkusnya WAJIB diberi atribut `wire:ignore` agar Livewire tidak melakukan DOM-diffing terhadap area tersebut — yang jika terjadi akan merusak instance peta yang sedang berjalan.

> **Catatan Teknis Kritis:** Update data marker pada peta (misalnya perubahan status tekanan) dilakukan dengan mengirim data terbaru dari Livewire ke Alpine.js melalui event dispatch (`Livewire.on` / `$wire.on`), BUKAN dengan membiarkan Livewire me-render ulang elemen peta secara langsung.

### 2.5 Alur Data End-to-End (Fase 1)

```
1. Teknisi/Admin login  →  Laravel Auth (session) memvalidasi kredensial
2. Input Log GV/Tekanan  →  Livewire Component memvalidasi & menyimpan via Eloquent
3. Data tersimpan di MySQL  →  memicu perhitungan ulang sisa bukaan / status tekanan
4. Executive Dashboard (wire:poll 30s)  →  menarik data agregat terbaru
5. Alpine.js menerima event  →  memperbarui marker & warna pada peta (wire:ignore)
6. Manajemen mengekspor laporan  →  Laravel Excel menghasilkan file .xlsx sesuai filter aktif
```

---

## 3. Database & Relational Model Scope

### 3.1 Entity Relationship Overview

Skema basis data Fase 1 dirancang di sekitar empat entitas inti: `users`, `aset_valve`, `log_gate_valve`, dan `log_tekanan_air`, ditambah tabel pendukung `lokasi`. Seluruh entitas yang berhubungan dengan titik geografis WAJIB memiliki kolom `latitude` dan `longitude` bertipe `FLOAT(10,6)` agar presisi koordinat cukup untuk kebutuhan pemetaan skala jaringan pipa (akurasi hingga ±0.11 meter).

```
users (1) ──────< (N) log_gate_valve
  │  role: admin/manajemen/teknisi        │
  │                                        │
  │                                        ▼
  │                                  aset_valve (N) >───── (1) lokasi
  │                                        │
  │                                        ▼
  └───────────────────────────< (N) log_tekanan_air

Legenda: (1) satu, (N) banyak — relasi one-to-many pada seluruh tabel log
```

### 3.2 Tabel: `users`

Tabel `users` memperluas struktur standar Laravel (migration default) dengan penambahan kolom `role` dan `phone`. Struktur ini menjadi fondasi utama untuk kesiapan login lintas platform (web & mobile) pada fase mendatang.

| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | Primary key standar Laravel |
| `name` | VARCHAR(255) | NOT NULL | Nama lengkap pengguna |
| `email` | VARCHAR(255) | UNIQUE, NOT NULL | Digunakan sebagai username login |
| `phone` | VARCHAR(20) | NULLABLE | Nomor telepon/WhatsApp untuk kontak & future notification |
| `password` | VARCHAR(255) | NOT NULL | Di-hash dengan Bcrypt, tidak pernah ditampilkan |
| `role` | ENUM('admin','manajemen','teknisi') | NOT NULL, DEFAULT 'teknisi' | Menentukan hak akses & tampilan menu |
| `is_active` | BOOLEAN | DEFAULT true | Menonaktifkan akun tanpa menghapus data (soft-disable) |
| `email_verified_at` | TIMESTAMP | NULLABLE | Bawaan Laravel, disiapkan untuk verifikasi akun |
| `remember_token` | VARCHAR(100) | NULLABLE | Bawaan Laravel untuk fitur 'remember me' |
| `created_at` / `updated_at` | TIMESTAMP | NOT NULL | Bawaan Laravel (timestamps) |

> **Catatan Teknis:** Kolom `role` dirancang sebagai ENUM pada level migration Laravel, namun pada level aplikasi tetap divalidasi menggunakan Laravel Validation `Rule::in(['admin','manajemen','teknisi'])` agar mudah diperluas tanpa migration schema-breaking di kemudian hari.

#### 3.2.1 Kesiapan API (Mobile-Readiness)

- **Sanctum Table:** Tabel `personal_access_tokens` (bawaan Laravel Sanctum) disiapkan strukturnya sejak Fase 1, meski penerbitan token belum diaktifkan untuk konsumsi eksternal.
- **Trait HasApiTokens:** Model `User` akan meng-implement trait `HasApiTokens` agar tinggal diaktifkan saat Fase 2 (Mobile App) dikembangkan, tanpa perlu migrasi ulang data pengguna.

### 3.3 Tabel: `lokasi`

| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | Primary key |
| `nama_daerah` | VARCHAR(150) | NOT NULL | Nama wilayah/kelurahan cakupan distribusi |
| `keterangan` | TEXT | NULLABLE | Deskripsi tambahan wilayah |
| `created_at` / `updated_at` | TIMESTAMP | NOT NULL | Bawaan Laravel |

### 3.4 Tabel: `aset_valve` (Master Aset Gate Valve)

| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | Primary key |
| `lokasi_id` | BIGINT | FK → lokasi.id | Relasi ke wilayah distribusi |
| `nama_aset` | VARCHAR(150) | NOT NULL | Contoh: `GV 6"` — wajib di-escape dari karakter tanda kutip ganda sebelum disimpan |
| `kapasitas_full_putaran` | FLOAT(8,2) | NOT NULL | Jumlah putaran penuh valve dari kondisi terbuka penuh |
| `latitude` | FLOAT(10,6) | NOT NULL | Koordinat lintang statis lokasi aset terpasang |
| `longitude` | FLOAT(10,6) | NOT NULL | Koordinat bujur statis lokasi aset terpasang |
| `status_kondisi` | ENUM('normal','rendah','kritis') | DEFAULT 'normal' | Status kondisi terkini, di-update otomatis dari log tekanan terkait |
| `created_at` / `updated_at` | TIMESTAMP | NOT NULL | Bawaan Laravel |

> **Peringatan Keamanan Input:** Input `nama_aset` harus melalui proses sanitasi (misalnya menggunakan `htmlspecialchars` atau binding parameter Eloquent yang secara default sudah aman dari SQL Injection) agar karakter tanda kutip ganda pada penamaan seperti `GV 6"` tidak merusak query atau rendering HTML di Blade/Livewire.

### 3.5 Tabel: `log_gate_valve`

Tabel ini mencatat setiap riwayat aktivitas buka/tutup Gate Valve yang dilakukan oleh teknisi di lapangan, lengkap dengan koordinat pengambilan data dan bukti foto.

| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | Primary key |
| `aset_valve_id` | BIGINT | FK → aset_valve.id | Valve yang diaktivitaskan |
| `user_id` | BIGINT | FK → users.id | Teknisi pencatat log |
| `jenis_aktivitas` | ENUM('buka','tutup') | NOT NULL | Arah putaran valve |
| `jumlah_putaran` | FLOAT(8,2) | NOT NULL | Jumlah putaran pada aktivitas ini (mendukung nilai desimal, misal 2.5 putaran) |
| `sisa_bukaan_setelah` | FLOAT(8,2) | NOT NULL, computed | Hasil kalkulasi otomatis: kapasitas_full − akumulasi tutupan |
| `latitude` | FLOAT(10,6) | NOT NULL | Koordinat saat log diambil di lapangan |
| `longitude` | FLOAT(10,6) | NOT NULL | Koordinat saat log diambil di lapangan |
| `foto_eviden` | VARCHAR(255) | NULLABLE | Path/nama file penyimpanan bukti foto (Laravel Storage disk) |
| `catatan` | TEXT | NULLABLE | Catatan tambahan teknisi |
| `created_at` / `updated_at` | TIMESTAMP | NOT NULL | Bawaan Laravel |

### 3.6 Tabel: `log_tekanan_air`

| Kolom | Tipe Data | Constraint | Keterangan |
|---|---|---|---|
| `id` | BIGINT | PK, Auto Increment | Primary key |
| `aset_valve_id` | BIGINT | FK → aset_valve.id | Titik pantau tekanan terkait aset |
| `user_id` | BIGINT | FK → users.id | Teknisi pencatat log |
| `nilai_tekanan` | FLOAT(6,2) | NOT NULL | Nilai tekanan air dalam satuan Bar |
| `status` | ENUM('normal','rendah','kritis') | NOT NULL, computed | Diturunkan otomatis dari `nilai_tekanan` terhadap ambang batas |
| `latitude` | FLOAT(10,6) | NOT NULL | Koordinat titik pengukuran |
| `longitude` | FLOAT(10,6) | NOT NULL | Koordinat titik pengukuran |
| `foto_eviden` | VARCHAR(255) | NULLABLE | Path/nama file bukti foto pengukuran |
| `created_at` / `updated_at` | TIMESTAMP | NOT NULL | Bawaan Laravel |

### 3.7 Penanganan Tipe Data Float (Putaran & Tekanan)

- **Presisi Terkendali:** Seluruh kolom numerik yang merepresentasikan putaran valve dan nilai tekanan menggunakan tipe FLOAT dengan presisi tetap (contoh: `FLOAT(8,2)`) untuk mendukung nilai desimal seperti 2.5 putaran atau 0.8 Bar.
- **Validasi Backend Wajib:** Setiap input dari form Livewire divalidasi menggunakan rule `numeric` dan `min:0` pada level backend, tidak hanya validasi di sisi klien, untuk mencegah data korup akibat manipulasi request.
- **Kalkulasi Terpusat:** Perhitungan `sisa_bukaan_setelah` dilakukan di dalam Model Event (Eloquent Observer) atau Service Class, bukan di sisi Livewire Component, agar logika bisnis tetap konsisten meskipun diakses dari kanal lain (misal API mobile di Fase 2).
- **Threshold Terkonfigurasi:** Ambang batas status tekanan (Normal ≥ 1.0 Bar, Rendah 0.1–0.99 Bar, Kritis ≤ 0 Bar) disimpan sebagai konstanta konfigurasi (`config/thresholds.php`), bukan hardcode, agar mudah disesuaikan tanpa deploy ulang kode.

### 3.8 Ringkasan Kolom Spasial & Media

| Tabel | Kolom Spasial/Media | Tipe Data |
|---|---|---|
| `aset_valve` | latitude, longitude | FLOAT(10,6), FLOAT(10,6) |
| `log_gate_valve` | latitude, longitude, foto_eviden | FLOAT(10,6), FLOAT(10,6), VARCHAR(255) |
| `log_tekanan_air` | latitude, longitude, foto_eviden | FLOAT(10,6), FLOAT(10,6), VARCHAR(255) |

---

## 4. Functional Requirements (Per Modul)

Bagian ini menjabarkan kebutuhan fungsional setiap modul, lengkap dengan alur proses teknis implementasi TALL Stack yang wajib diikuti oleh tim developer.

### 4.1 Modul 0 — Autentikasi & Manajemen Pengguna

#### 4.1.1 Fitur Halaman Login (Web)

| ID | Deskripsi Requirement |
|---|---|
| FR-001 | Sistem menyediakan halaman login responsif menggunakan Tailwind CSS, dapat diakses melalui route `/login`. |
| FR-002 | Autentikasi menggunakan sistem bawaan Laravel (`Auth::attempt`) dengan session & cookie, tanpa mekanisme token pada Fase 1. |
| FR-003 | Sistem menerapkan Throttling — maksimal percobaan login gagal dibatasi dalam jangka waktu tertentu menggunakan middleware `throttle:login` sebelum akun dikunci sementara. |
| FR-004 | Pesan error validasi (email/password salah, akun nonaktif) ditampilkan secara jelas tanpa membocorkan informasi sensitif (generic error message). |
| FR-005 | Setelah login berhasil, sistem mengarahkan pengguna ke halaman dashboard sesuai role masing-masing. |

> **Catatan Teknis:** Session ID wajib di-regenerate setiap login berhasil (`session()->regenerate()`) untuk mencegah *session fixation attack*.

#### 4.1.2 Fitur CRUD Manajemen Pengguna

| ID | Deskripsi Requirement |
|---|---|
| FR-006 | Halaman khusus dapat diakses hanya oleh role Admin (dilindungi Policy/Gate) untuk membuat, melihat, mengubah, dan menonaktifkan akun karyawan. |
| FR-007 | Form pembuatan akun terdiri dari: Nama Lengkap, Email, Password, Nomor Telepon/WhatsApp, dan Role (dropdown: Admin, Manajemen, Teknisi). |
| FR-008 | Validasi form dilakukan secara real-time menggunakan Livewire (`wire:model.live`) — contoh: pengecekan keunikan email langsung saat pengguna mengetik, tanpa perlu submit atau refresh. |
| FR-009 | Menonaktifkan akun menggunakan pendekatan soft-disable (kolom `is_active = false`), BUKAN penghapusan permanen (hard delete), untuk menjaga integritas riwayat log historis milik akun tersebut. |
| FR-010 | Password yang diinput di-hash otomatis menggunakan Bcrypt sebelum disimpan ke database (memanfaatkan cast `'hashed'` pada Model User Laravel 10+). |

#### 4.1.3 Logika Kesiapan Mobile (Mobile Readiness)

- Backend Laravel dipersiapkan agar tabel `users` dapat diakses melalui API menggunakan Laravel Sanctum/Passport, sehingga teknisi lapangan dapat login ke aplikasi mobile Fase 2 menggunakan akun yang sama tanpa registrasi ulang.
- Struktur response API (Resource Class) untuk data user dirancang sejak Fase 1, meskipun endpoint publik belum diaktifkan sepenuhnya.

### 4.2 Modul Utama — Executive Dashboard (Real-time Overview & Map)

#### 4.2.1 Auto-Refresh Metrics

| ID | Deskripsi Requirement |
|---|---|
| FR-011 | Dashboard menggunakan directive `wire:poll.30s` pada Livewire Component untuk memperbarui data statistik, peta, dan tabel secara otomatis setiap 30 detik tanpa interaksi pengguna. |
| FR-012 | Polling tidak boleh menyebabkan flicker/reset pada state peta (harus tetap dilindungi `wire:ignore`) maupun scroll position tabel. |

#### 4.2.2 Kartu Metrik Utama

| ID | Deskripsi Requirement |
|---|---|
| FR-013 | Kartu 'Total Aset Terdaftar' menampilkan jumlah keseluruhan record `aset_valve` aktif. |
| FR-014 | Kartu 'Total Aktivitas Hari Ini' menghitung akumulasi record `log_gate_valve` dan `log_tekanan_air` dengan `created_at` = tanggal hari ini. |
| FR-015 | Kartu 'Peringatan Daerah Kritis' menampilkan efek animasi berdenyut (pulse, menggunakan Tailwind `animate-pulse`) berwarna merah apabila terdapat minimal satu titik dengan status tekanan 0 Bar (kritis). |

#### 4.2.3 Peta Interaktif (Interactive GIS Map) — Fitur Utama

| ID | Deskripsi Requirement |
|---|---|
| FR-016 | Peta interaktif skala besar dirender menggunakan Leaflet.js atau Google Maps API, diinisialisasi melalui Alpine.js (`x-init`) saat komponen dimuat. |
| FR-017 | Elemen container peta WAJIB dibungkus atribut `wire:ignore` agar tidak terganggu proses DOM-diffing Livewire saat `wire:poll` berjalan. |
| FR-018 | Seluruh Gate Valve dan Titik Tekanan Air ditampilkan sebagai marker/pinpoint sesuai koordinat latitude & longitude masing-masing. |
| FR-019 | Warna marker berubah otomatis mengikuti kondisi riil: Hijau (🟢 Normal), Kuning (🟡 Rendah), Merah (🔴 Kritis/Tertutup). |
| FR-020 | Klik pada marker memunculkan Interactive Popup berisi nama aset, lokasi, nilai tekanan/bukaan terkini, dan waktu update terakhir. |
| FR-021 | Update status marker terjadi via event dispatch dari Livewire ke Alpine (`$wire.on` / `Livewire.on`), memperbarui layer peta tanpa me-render ulang seluruh instance peta. |

#### 4.2.4 Visualisasi Chart

| ID | Deskripsi Requirement |
|---|---|
| FR-022 | Doughnut Chart menampilkan proporsi jumlah valve per status (Normal/Rendah/Kritis) menggunakan Chart.js, dibungkus `wire:ignore`. |
| FR-023 | Line Chart menampilkan tren rata-rata tekanan air 7 hari terakhir, dengan sumbu X berupa tanggal dan sumbu Y nilai Bar. |
| FR-024 | Data chart diperbarui mengikuti siklus `wire:poll` dashboard, dikirim ke Chart.js melalui event listener JavaScript, bukan re-inisialisasi ulang objek chart. |

#### 4.2.5 Tabel Ringkasan Bukaan Terkini

| ID | Deskripsi Requirement |
|---|---|
| FR-025 | Tabel menampilkan seluruh Gate Valve beserta kolom: Nama Aset, Kapasitas Full, Total Tutupan, dan Sisa Bukaan Saat Ini. |
| FR-026 | Sisa Bukaan Saat Ini divisualisasikan menggunakan Progress Bar berbasis Tailwind CSS (lebar bar proporsional terhadap persentase sisa bukaan terhadap kapasitas full). |

### 4.3 Modul A — Manajemen Master Aset (Gate Valve & Lokasi)

| ID | Deskripsi Requirement |
|---|---|
| FR-027 | Sistem menyediakan CRUD penuh (Create, Read, Update, Delete/nonaktifkan) untuk data `aset_valve` dan `lokasi`. |
| FR-028 | Form input terdiri dari: Nama Aset, Nama Daerah (relasi ke tabel lokasi), Kapasitas Full Putaran (Float), Latitude, dan Longitude. |
| FR-029 | Input Nama Aset mendukung karakter tanda kutip ganda secara aman (contoh: `GV 6"`) — nilai wajib di-escape/sanitasi sebelum disimpan dan ditampilkan agar tidak merusak rendering HTML maupun query database. |
| FR-030 | Form dikelola melalui Modal Pop-up berbasis Alpine.js (`x-show`/`x-transition`), bukan navigasi ke halaman terpisah, guna menjaga konteks pengguna tetap berada di halaman listing. |
| FR-031 | Validasi Latitude (-90 hingga 90) dan Longitude (-180 hingga 180) dilakukan di sisi backend menggunakan Laravel Validation Rule sebelum data disimpan. |

### 4.4 Modul B & C — Log Gate Valve & Log Tekanan Air

#### 4.4.1 Tampilan & Pencarian Data

| ID | Deskripsi Requirement |
|---|---|
| FR-032 | Tabel riwayat operasional lapangan menampilkan data `log_gate_valve` dan `log_tekanan_air` dengan pagination Livewire. |
| FR-033 | Pencarian teks global bersifat dinamis (live search) menggunakan `wire:model.live.debounce.300ms` yang menyaring data tanpa reload halaman. |
| FR-034 | Filter tab (misal: per status, per lokasi, per rentang tanggal) menggunakan binding `wire:model` milik Livewire, memperbarui query Eloquent secara reaktif. |

#### 4.4.2 Tampilan Media & Spasial

| ID | Deskripsi Requirement |
|---|---|
| FR-035 | Setiap baris tabel menampilkan kolom Latitude & Longitude lokasi pengambilan log (berbeda dari koordinat statis aset, karena mencerminkan posisi teknisi saat itu). |
| FR-036 | Kolom 'Bukti Foto' menampilkan thumbnail; saat diklik, memunculkan Lightbox Modal berbasis Alpine.js yang menampilkan foto dalam ukuran penuh. |
| FR-037 | Apabila `foto_eviden` kosong (NULL), sistem menampilkan placeholder ikon 'Tidak ada foto', bukan thumbnail rusak (broken image). |

#### 4.4.3 Visualisasi Bar Chart Log Tekanan

| ID | Deskripsi Requirement |
|---|---|
| FR-038 | Modul Log Tekanan menampilkan Bar Chart interaktif (Chart.js) dari nilai tekanan per titik/waktu, dibungkus `wire:ignore`. |
| FR-039 | Chart menampilkan Garis Ambang Batas Minimum berupa garis putus-putus berwarna hijau pada posisi 1.0 Bar sebagai referensi visual batas aman. |

### 4.5 Modul D — Sistem Pelaporan Perusahaan (Export Engine)

| ID | Deskripsi Requirement |
|---|---|
| FR-040 | Sistem menyediakan tombol ekspor pada setiap modul tabel (Log GV, Log Tekanan, Master Aset) yang menghasilkan file .xlsx menggunakan Laravel Excel. |
| FR-041 | Proses ekspor WAJIB mempertahankan seluruh parameter filter/search yang sedang aktif di komponen Livewire pada saat tombol ekspor ditekan. |
| FR-042 | File hasil ekspor menyertakan kolom data koordinat (Latitude & Longitude) lokasi, selain kolom data operasional standar. |
| FR-043 | Nama file ekspor mengikuti format standar: `[nama-modul]_[YYYYMMDD_HHmmss].xlsx` untuk memudahkan penelusuran arsip laporan. |

---

## 5. User Interface (UI) Layout Guidelines

Panduan ini menjadi acuan visual dan struktural bagi tim frontend agar konsisten dalam mengimplementasikan tampilan menggunakan Tailwind CSS di seluruh halaman.

### 5.1 Halaman Login

- **Struktur:** Layout terpusat (centered card) di tengah viewport, dengan lebar maksimum sekitar 400px pada desktop dan penuh (full-width dengan padding) pada mobile.
- **Branding:** Logo/nama instansi PDAM ditampilkan di bagian atas kartu login sebagai identitas visual utama.
- **Form:** Input email & password menggunakan komponen Tailwind dengan focus ring berwarna biru (misal `ring-blue-500`) dan pesan error berwarna merah di bawah masing-masing field.
- **Tombol Login:** Menggunakan warna solid (`bg-blue-700`) dengan status loading (spinner) saat proses autentikasi berjalan, memanfaatkan `wire:loading` dari Livewire.

### 5.2 Grid Dashboard Utama (Executive Dashboard)

```
┌─────────────────────────────────────────────────────────┐
│  TOP BAR: Logo | Nama Aplikasi         [User ▾] [Logout] │
├─────────────────────────────────────────────────────────┤
│ [Kartu: Total Aset] [Kartu: Aktivitas Hari Ini] [Kartu:  │
│                                        Peringatan Kritis] │
├───────────────────────────────────┬───────────────────────┤
│                                   │  Doughnut Chart       │
│      PETA INTERAKTIF (GIS)       │  (Proporsi Status)    │
│      wire:ignore                 ├───────────────────────┤
│      col-span-2 pada desktop     │  Line Chart           │
│                                   │  (Tren Tekanan 7 Hari)│
├───────────────────────────────────┴───────────────────────┤
│           Tabel Ringkasan Bukaan Terkini (full width)     │
│           + Progress Bar per baris                        │
└─────────────────────────────────────────────────────────┘
```

- **Struktur Grid:** Grid menggunakan sistem Tailwind `grid grid-cols-1 lg:grid-cols-3` — peta menempati 2 kolom (`lg:col-span-2`), sisi kanan berisi dua chart bertumpuk.
- **Dimensi Peta:** Peta memiliki tinggi tetap (contoh: `h-[500px]`) agar tidak menyebabkan reflow tata letak saat data di-poll ulang setiap 30 detik.
- **Kartu Metrik:** Menggunakan `shadow-sm`, `rounded-xl`, dan warna aksen border kiri (`border-l-4`) sesuai kategori (biru untuk normal, merah untuk peringatan kritis).

### 5.3 Viewing Lightbox Foto Eviden

- **Thumbnail:** Foto pada tabel log ditampilkan berukuran kecil (contoh: `w-12 h-12 rounded object-cover`) dengan `cursor-pointer`.
- **Modal Lightbox:** Saat diklik, Alpine.js (`x-data` dengan state boolean `showLightbox`) menampilkan overlay gelap (`bg-black/75`) penuh layar dengan gambar berukuran penuh di tengah, serta tombol tutup (✕) di pojok kanan atas.
- **Transisi:** Transisi buka/tutup modal menggunakan `x-transition` Alpine.js agar terasa halus (fade + scale).

### 5.4 Struktur Chart

| Jenis Chart | Digunakan Pada | Ketentuan Visual |
|---|---|---|
| Doughnut Chart | Proporsi Status Valve (Dashboard) | Warna segmen: hijau (normal), kuning (rendah), merah (kritis); legend di bawah chart |
| Line Chart | Tren Rata-rata Tekanan 7 Hari (Dashboard) | Sumbu X: tanggal (7 titik terakhir); sumbu Y: nilai Bar; garis warna biru dengan area fill tipis |
| Bar Chart | Log Tekanan Air (Modul C) | Garis ambang batas 1.0 Bar berupa garis putus-putus hijau (annotation plugin Chart.js) |

### 5.5 Konsistensi Palet Warna Status

| Status | Warna | Penggunaan |
|---|---|---|
| Normal | Hijau (`#16A34A` / green-600) | Marker peta, badge status, segmen chart |
| Rendah | Kuning (`#CA8A04` / yellow-600) | Marker peta, badge status, segmen chart |
| Kritis | Merah (`#DC2626` / red-600) | Marker peta, badge status, kartu berdenyut (`animate-pulse`) |

---

## 6. Non-Functional Requirements

### 6.1 Performa (Performance)

| ID | Deskripsi Requirement |
|---|---|
| NFR-001 | Peta interaktif dan tabel data harus dilindungi atribut `wire:ignore` untuk mencegah proses DOM-diffing Livewire mengganggu instance JavaScript pihak ketiga (Leaflet.js/Chart.js) setiap siklus `wire:poll`. |
| NFR-002 | Interval `wire:poll` pada Executive Dashboard ditetapkan 30 detik — tidak boleh diperpendek tanpa evaluasi beban server, karena polling terlalu sering berpotensi membebani query agregat metrik. |
| NFR-003 | Query pengambilan data untuk peta dan tabel ringkasan wajib menggunakan eager loading (`with()`) pada relasi Eloquent untuk menghindari N+1 query problem. |
| NFR-004 | Pencarian live (live search) pada tabel log menggunakan debounce minimal 300ms untuk mengurangi jumlah request ke server saat pengguna mengetik. |
| NFR-005 | Waktu respons rata-rata untuk render awal halaman dashboard ditargetkan di bawah 2 detik pada kondisi jaringan standar kantor. |

### 6.2 Keamanan (Security)

| ID | Deskripsi Requirement |
|---|---|
| NFR-006 | Seluruh password pengguna disimpan dalam bentuk hash menggunakan algoritma Bcrypt (cost factor default Laravel), tidak pernah dalam bentuk plain text di database maupun log aplikasi. |
| NFR-007 | Login dilindungi mekanisme Throttling untuk membatasi percobaan login gagal berulang dalam rentang waktu tertentu (mitigasi brute-force). |
| NFR-008 | Setiap input pengguna (khususnya nama aset yang dapat memuat karakter tanda kutip) wajib melalui parameter binding Eloquent/Blade escaping bawaan Laravel untuk mencegah SQL Injection dan Cross-Site Scripting (XSS). |
| NFR-009 | Akses ke setiap modul dibatasi berdasarkan role (Admin, Manajemen, Teknisi) menggunakan Laravel Policy/Gate, dievaluasi di sisi server pada setiap request — bukan hanya disembunyikan di sisi UI. |
| NFR-010 | Session pengguna diregenerasi setiap login berhasil dan memiliki masa kedaluwarsa (session timeout) sesuai kebijakan keamanan internal perusahaan. |

### 6.3 Manajemen Penyimpanan Media (File Storage)

| ID | Deskripsi Requirement |
|---|---|
| NFR-011 | Foto eviden disimpan menggunakan Laravel Filesystem (disk 'public' atau cloud storage seperti S3 untuk skala enterprise), bukan disimpan sebagai BLOB di database. |
| NFR-012 | Nama file yang diunggah wajib di-generate ulang (hash/UUID) oleh sistem, bukan menggunakan nama file asli dari perangkat pengguna, untuk mencegah collision dan potensi path traversal. |
| NFR-013 | Ukuran maksimum file foto yang dapat diunggah dibatasi (contoh: 5MB per file) dan divalidasi tipe MIME-nya (hanya `image/jpeg`, `image/png`) di sisi backend. |
| NFR-014 | Sistem menyediakan mekanisme pembersihan berkala (scheduled job) untuk foto yang sudah tidak terelasi dengan record log manapun (orphaned files), guna efisiensi penyimpanan jangka panjang. |

### 6.4 Skalabilitas & Ketersediaan

- **Desain Skema:** Struktur database dirancang normalisasi minimal 3NF untuk entitas inti, namun tetap mempertimbangkan indexing pada kolom yang sering difilter (`role`, `status`, `created_at`, `lokasi_id`) demi performa query jangka panjang.
- **Ekstensibilitas:** Arsitektur backend Laravel dipisahkan secara logis (Service Layer, Repository, atau Action Class) agar mudah diperluas untuk mendukung endpoint API Fase 2 tanpa merombak logika bisnis inti.

### 6.5 Usability & Aksesibilitas

- **Konsistensi Feedback:** Seluruh notifikasi aksi (berhasil simpan, gagal validasi, akun dinonaktifkan) menggunakan toast/alert Tailwind yang konsisten di seluruh modul.
- **Kontras Warna:** Kontras warna teks dan latar (khususnya pada badge status merah/kuning/hijau) mengikuti standar aksesibilitas WCAG AA agar tetap terbaca oleh pengguna dengan gangguan penglihatan warna ringan.

### 6.6 Maintainability

- **Konvensi Penamaan:** Seluruh komponen Livewire dan Alpine.js diberi penamaan yang deskriptif dan konsisten (contoh: `dashboard-map.blade.php`, `log-tekanan-table.blade.php`) untuk memudahkan pemeliharaan jangka panjang oleh tim developer.
- **Dokumentasi Kode:** Dokumentasi inline (PHPDoc) wajib disertakan pada Model, Service Class, dan Livewire Component yang menangani logika kalkulasi kritis (sisa bukaan valve, status tekanan).

---

*Dokumen ini bersifat Internal / Confidential — dipersiapkan untuk keperluan pengembangan Fase 1 Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM.*
