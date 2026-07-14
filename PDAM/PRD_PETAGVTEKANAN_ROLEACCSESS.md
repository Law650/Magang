# PRODUCT REQUIREMENT DOCUMENT (PRD)

## SISTEM INFORMASI MANAJEMEN ASET & MONITORING DISTRIBUSI AIR PDAM
### Fase 1 : Web Dashboard & Core Backend

| Field | Keterangan |
|---|---|
| **Nama Proyek** | Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM |
| **Fase Pengembangan** | Fase 1 — Web Dashboard & Core Backend |
| **Arsitektur** | TALL Stack (Tailwind CSS, Alpine.js, Livewire, Laravel) |
| **Nomor Dokumen** | PRD-PDAM-WD-001 |
| **Versi Dokumen** | 1.0 |
| **Tanggal Penyusunan** | 14 Juli 2026 |
| **Status Dokumen** | Draft — Menunggu Persetujuan (Pending Approval) |
| **Klasifikasi** | Internal — Confidential |

*Disusun oleh Tim Product Management & System Analyst Enterprise*

---

## Riwayat Perubahan Dokumen (Document Control)

| Versi | Tanggal | Deskripsi Perubahan | Disusun Oleh | Disetujui Oleh |
|---|---|---|---|---|
| 1.0 | 14 Jul 2026 | Penyusunan awal PRD Fase 1 — Web Dashboard & Core Backend (RBAC, Peta Distribusi, Master Aset, Log, Reporting) | Product Manager / System Analyst | Menunggu Persetujuan |

### Daftar Pemangku Kepentingan (Stakeholders)

| Peran | Tanggung Jawab Terhadap Dokumen |
|---|---|
| Project Sponsor / Direksi PDAM | Menyetujui ruang lingkup, anggaran, dan prioritas pengembangan Fase 1 |
| Product Manager | Memastikan requirement sesuai kebutuhan bisnis dan operasional lapangan |
| Tech Lead / System Analyst | Menerjemahkan requirement menjadi spesifikasi teknis TALL Stack |
| Tim Developer (Backend & Frontend) | Mengimplementasikan seluruh modul sesuai PRD ini |
| QA / Tester | Menyusun skenario pengujian berdasarkan acceptance criteria |
| Admin PDAM (End User) | Pengguna akhir dengan hak akses penuh (Full CRUD) |
| Pekerja / Teknisi Lapangan (End User) | Pengguna akhir dengan hak akses monitoring (Read-Only) pada Web |

---

## Daftar Isi

1. [Project Information & Objectives](#1-project-information--objectives)
2. [System Architecture & Tech Stack](#2-system-architecture--tech-stack)
3. [Database & Relational Model Scope](#3-database--relational-model-scope)
4. [Functional Requirements (Per Modul)](#4-functional-requirements-per-modul)
5. [User Interface (UI) Layout Guidelines](#5-user-interface-ui-layout-guidelines)
6. [Non-Functional Requirements](#6-non-functional-requirements)
7. [Lembar Persetujuan (Sign-Off Sheet)](#lembar-persetujuan-sign-off-sheet)

---

## 1. Project Information & Objectives

### 1.1 Latar Belakang

PDAM sebagai penyedia layanan air bersih memiliki jaringan pipa distribusi yang tersebar luas secara geografis, mencakup ratusan titik Gate Valve (GV) dan stasiun pemantauan tekanan air. Selama ini, proses pencatatan bukaan katup, pemantauan tekanan, serta pelaporan kondisi lapangan masih dilakukan secara manual atau tersebar pada berkas-berkas terpisah, sehingga menyulitkan pengambilan keputusan yang cepat dan berbasis data (data-driven).

Untuk menjawab tantangan tersebut, dibangun **Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM** sebagai platform digital terpusat. Fase 1 dari proyek ini berfokus pada pembangunan Web Dashboard dan Core Backend menggunakan **TALL Stack** (Tailwind CSS, Alpine.js, Livewire, Laravel), yang berfungsi sebagai ruang kendali (*control room*) bagi tim internal PDAM untuk memantau, mengelola, dan mengaudit infrastruktur jaringan pipa secara real-time. Fondasi backend pada fase ini juga dirancang agar siap diintegrasikan dengan aplikasi mobile teknisi lapangan pada fase pengembangan berikutnya.

### 1.2 Tujuan Utama (Goals)

| No | Tujuan (Goal) | Deskripsi |
|---|---|---|
| G1 | **Role-Based Access Control (RBAC)** | Memisahkan hak akses secara ketat antara Admin (pengelola sistem penuh/Full CRUD) dan Pekerja/Teknisi (hanya dapat memantau dashboard di web, tanpa izin mengedit data). |
| G2 | **Centralized User Management** | Admin dapat membuat dan mengelola akun pekerja langsung melalui antarmuka web dashboard. Akun ini disiapkan agar dapat digunakan pekerja untuk login ke aplikasi mobile pada fase berikutnya. |
| G3 | **Geospatial Mapping Isolation** | Menyediakan menu "Peta Distribusi" yang terpecah menjadi 2 pemetaan fokus (Peta Gate Valve & Peta Tekanan Air) agar visualisasi titik GPS lebih bersih, tidak tumpang tindih, dan akurat. |
| G4 | **Water Flow Visibility** | Menambahkan metrik laju air (Mengalir / Tidak Mengalir) pada pemantauan titik tekanan, dihitung berdasarkan analisis data riil tekanan lapangan. |
| G5 | **Kontrol Presisi Aset & Early Warning System** | Melacak fisik sisa bukaan katup (GV) secara otomatis dan memantau anomali tekanan air (Normal, Rendah, Kritis) untuk mempercepat respons teknis. |

### 1.3 Ruang Lingkup (Scope)

#### 1.3.1 Dalam Ruang Lingkup (In-Scope) — Fase 1

- Autentikasi berbasis sesi Laravel dengan RBAC 2 peran: Admin dan Pekerja.
- Modul Manajemen Pengguna (khusus Admin) untuk membuat akun pekerja, termasuk kesiapan arsitektur API Token (Sanctum/Passport).
- Executive Dashboard dengan metrik real-time (auto-refresh via `wire:poll`), grafik analitik (Chart.js), dan tabel ringkasan bukaan GV.
- Menu Peta Distribusi dengan 2 sub-menu terpisah: Peta Gate Valve dan Peta Tekanan Air (Leaflet.js / Google Maps API).
- Modul Manajemen Master Aset (CRUD Gate Valve & Lokasi) khusus Admin.
- Modul Log Gate Valve dan Log Tekanan Air, lengkap dengan live search, filter, koordinat lokasi, bukti foto (lightbox), dan grafik ambang batas tekanan.
- Modul Sistem Pelaporan (Export Engine) ke format `.xlsx` menggunakan Laravel Excel, khusus Admin.

#### 1.3.2 Di Luar Ruang Lingkup (Out-of-Scope) — Fase 1

- Aplikasi mobile teknisi lapangan (direncanakan pada Fase 2, namun backend/API sudah disiapkan fondasinya).
- Notifikasi push/WhatsApp otomatis untuk Early Warning System (dicatat sebagai kebutuhan Fase berikutnya).
- Integrasi IoT/sensor otomatis (input data tekanan & bukaan valve pada Fase 1 masih bersifat input manual oleh Admin/petugas via web).
- Modul billing, tagihan pelanggan, atau customer-facing portal.

### 1.4 Definisi, Akronim & Istilah

| Istilah | Definisi |
|---|---|
| PRD | Product Requirement Document — dokumen spesifikasi kebutuhan produk. |
| TALL Stack | Tailwind CSS, Alpine.js, Livewire, Laravel — kombinasi teknologi untuk membangun aplikasi web reaktif berbasis server-driven UI. |
| RBAC | Role-Based Access Control — mekanisme pembatasan akses berdasarkan peran pengguna. |
| GV | Gate Valve — katup pengatur aliran air pada jaringan pipa distribusi. |
| Bukaan Valve | Ukuran fisik seberapa terbuka sebuah Gate Valve, dinyatakan dalam satuan putaran (float). |
| Laju Air | Status apakah air mengalir atau tidak pada suatu titik pemantauan, diturunkan dari nilai tekanan (bar). |
| Bar | Satuan pengukuran tekanan air pada jaringan pipa distribusi. |
| Foto Eviden | Berkas foto bukti lapangan yang diunggah petugas saat pencatatan log GV atau tekanan air. |
| `wire:poll` | Direktif Livewire untuk melakukan polling/refresh data komponen secara berkala tanpa reload halaman. |
| `wire:ignore` | Direktif Livewire agar Livewire tidak melakukan DOM-diffing pada elemen tertentu (mis. peta interaktif). |
| Sanctum/Passport | Paket autentikasi API resmi Laravel untuk penerbitan token, digunakan sebagai fondasi login aplikasi mobile. |

---

## 2. System Architecture & Tech Stack

### 2.1 Ikhtisar Arsitektur TALL Stack

Sistem dibangun di atas TALL Stack, yaitu kombinasi empat teknologi yang saling melengkapi untuk menghasilkan aplikasi web yang reaktif tanpa memerlukan arsitektur SPA (Single Page Application) terpisah seperti React/Vue. Server-Driven UI melalui Livewire memungkinkan tim developer menulis logika reaktif menggunakan PHP, sementara Alpine.js menangani interaktivitas ringan di sisi client.

| Layer | Teknologi | Fungsi Utama dalam Sistem |
|---|---|---|
| Backend & Data Engine | Laravel 10.x (PHP 8.2+) | Core MVC, Eloquent ORM, Authentication State, Middleware RBAC (Gate/Policy), routing, validasi, dan manajemen basis data. |
| Server-Driven Reactivity | Livewire 3.x | Pencarian real-time (live search), filter tab tanpa reload, polling data metrik (`wire:poll`), perpindahan tab peta tanpa refresh halaman. |
| Client-Side Interactivity | Alpine.js 3.x | Modal pop-up data/gambar (lightbox), dropdown menu, inisialisasi & kontrol peta interaktif (Leaflet.js / Google Maps API). |
| Styling / UI | Tailwind CSS 3.x | Utility-first styling untuk dashboard, halaman login, komponen kartu metrik, tabel, dan form modal. |
| Visualisasi & Reporting | Chart.js 4.x + Laravel Excel (`maatwebsite/excel`) | Grafik analitik interaktif (doughnut, line, bar chart) dan export laporan `.xlsx` berstandar enterprise. |
| Basis Data | MySQL 8.x / MariaDB 10.x | Penyimpanan relasional untuk data pengguna, aset, lokasi, dan log operasional. |
| Peta Interaktif | Leaflet.js (open-source) atau Google Maps API | Rendering marker GPS, popup info aset, dan pengelompokan visual berdasarkan status. |

### 2.2 Pola Interaksi Antar Layer

Alur permintaan data pada sistem mengikuti pola berikut, yang menjadi acuan wajib bagi tim developer saat mengimplementasikan setiap modul:

1. **Request Masuk** — Browser mengirim request ke Route Laravel, disaring oleh Middleware (`auth`, `throttle`, dan RBAC role-check) sebelum mencapai Controller/Livewire Component.
2. **Render Komponen Livewire** — Livewire Component memuat data melalui Eloquent Model, menerapkan Policy/Gate untuk memfilter aksi yang boleh ditampilkan sesuai role pengguna.
3. **Interaktivitas Client** — Alpine.js menangani state UI lokal (buka/tutup modal, tab aktif secara visual, inisialisasi instance peta) tanpa perlu round-trip ke server untuk hal-hal yang bersifat murni presentasional.
4. **Sinkronisasi Reaktif** — Perubahan data (search, filter, submit form) dikirim via AJAX internal Livewire (`wire:model`, `wire:click`) dan hanya me-render ulang bagian DOM yang berubah (DOM diffing), tanpa reload penuh halaman.
5. **Output ke Pengguna** — Hasil akhir dirender dengan styling Tailwind CSS, dan untuk elemen non-Livewire-native (peta, chart) dibungkus `wire:ignore` agar tidak di-diff ulang oleh Livewire.

### 2.3 Implementasi Middleware RBAC (Gate & Policy)

Laravel menyediakan dua mekanisme otorisasi yang digunakan secara berlapis pada sistem ini:

- **Middleware Route-Level** — Middleware kustom (mis. `CheckRole`) didaftarkan pada grup route Admin (contoh: `/admin/*`) untuk memblokir akses Pekerja secara langsung di level HTTP request, mengembalikan response `403 Forbidden` jika role tidak sesuai.
- **Gate & Policy Level** — Untuk otorisasi granular per-aksi (create, update, delete pada Model `MasterAset`, `LogGateValve`, dsb.), digunakan Laravel Policy yang di-resolve melalui `Gate::authorize()` atau directive `@can` pada Blade/Livewire view.
- **UI-Level Guard** — Tombol aksi (Tambah, Edit, Hapus, Export, Manajemen Akun) disembunyikan menggunakan `@role('admin')` / `@can(...)` pada Blade, namun proteksi utama tetap berada di backend agar tidak bisa dibypass melalui manipulasi client-side.

> **Prinsip Keamanan Wajib:** Setiap validasi hak akses pada UI (menyembunyikan tombol) HARUS selalu diduplikasi dengan validasi yang sama di level Controller/Livewire Component/Policy. UI-only protection tanpa backend enforcement dianggap sebagai celah keamanan (*security gap*) dan tidak lolos code review.

### 2.4 Integrasi Library Pemetaan (Leaflet.js / Google Maps API)

Komponen peta interaktif diinisialisasi sepenuhnya melalui Alpine.js dan dibungkus atribut `wire:ignore` pada elemen container agar Livewire tidak melakukan DOM-diffing yang dapat merusak instance peta yang sudah aktif (mis. menghapus event listener marker).

- Data koordinat (`latitude`, `longitude`, `status`) di-fetch dari Livewire Component sebagai properti public, kemudian di-passing ke Alpine.js melalui `x-data` saat inisialisasi awal.
- Perpindahan antar tab (Peta GV ↔ Peta Tekanan) dikendalikan oleh state Livewire (`wire:click` memicu perubahan properti `activeTab`), namun instance peta di masing-masing tab tetap independen dan hanya di-render ulang saat tab tersebut pertama kali aktif.
- Marker dan popup dirender secara dinamis oleh JavaScript (`Leaflet L.marker()` / `Google Maps google.maps.Marker()`) berdasarkan array data yang di-inject dari Blade/Livewire, bukan oleh Blade loop langsung, untuk menjaga performa saat data berjumlah besar.

### 2.5 Diagram Alur Arsitektur (High-Level)

| Tahap | Komponen | Keterangan |
|---|---|---|
| 1 | Browser (Client) | Tailwind CSS (tampilan) + Alpine.js (state lokal UI, peta, modal) |
| 2 | Livewire Layer | Menjembatani interaksi client ↔ server tanpa reload penuh (AJAX terselubung) |
| 3 | Laravel Middleware | Autentikasi sesi + RBAC role-check + throttling login |
| 4 | Controller / Livewire Component | Logika bisnis, validasi input, otorisasi via Gate/Policy |
| 5 | Eloquent ORM | Query & manipulasi data ke database relasional |
| 6 | MySQL Database | Penyimpanan data users, aset, lokasi, log GV, log tekanan |
| 7 | Storage (Filesystem) | Penyimpanan file `foto_eviden` (local disk / cloud-ready) |

---

## 3. Database & Relational Model Scope

### 3.1 Ikhtisar Entitas (Entity Overview)

Skema basis data Fase 1 terdiri dari 6 entitas utama yang saling berelasi. Seluruh kolom koordinat geografis **WAJIB** menggunakan tipe data `FLOAT(10,6)` untuk menjamin presisi hingga 6 digit desimal, yang setara dengan akurasi ±0.11 meter di lapangan — standar minimum untuk pemetaan aset infrastruktur.

| Entitas | Deskripsi Singkat |
|---|---|
| `users` | Menyimpan akun pengguna sistem (Admin & Pekerja) beserta role dan kredensial. |
| `master_lokasi` | Menyimpan data daerah/wilayah cakupan distribusi air. |
| `master_aset_valve` | Menyimpan data statis Gate Valve (nama, kapasitas, koordinat, lokasi terkait). |
| `log_gate_valve` | Riwayat pencatatan bukaan/tutupan Gate Valve oleh petugas di lapangan. |
| `log_tekanan_air` | Riwayat pencatatan tekanan air pada titik/daerah pemantauan. |
| `personal_access_tokens` | Tabel bawaan Laravel Sanctum untuk menyimpan API token (fondasi login aplikasi mobile Fase 2). |

### 3.2 Tabel: `users`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK, AUTO_INCREMENT | Primary key. |
| `nama_lengkap` | VARCHAR(150), NOT NULL | Nama lengkap pengguna. |
| `email` | VARCHAR(150), UNIQUE, NOT NULL | Digunakan sebagai username login. |
| `password` | VARCHAR(255), NOT NULL | Hash password menggunakan Bcrypt. |
| `no_whatsapp` | VARCHAR(20), NULLABLE | Nomor kontak pengguna, disiapkan untuk notifikasi Fase berikutnya. |
| `role` | ENUM('admin','pekerja'), NOT NULL, DEFAULT 'pekerja' | Menentukan hak akses RBAC pengguna. |
| `status_aktif` | BOOLEAN, DEFAULT true | Menandai apakah akun masih aktif/boleh login. |
| `email_verified_at` | TIMESTAMP, NULLABLE | Standar Laravel Authentication. |
| `remember_token` | VARCHAR(100), NULLABLE | Standar Laravel "remember me". |
| `created_at` / `updated_at` | TIMESTAMP | Standar Eloquent timestamps. |

> **Wajib:** kolom `role` menggunakan tipe ENUM pada level migrasi database (bukan hanya validasi di aplikasi) sebagai lapisan pertahanan tambahan terhadap data tidak konsisten.

### 3.3 Tabel: `master_lokasi`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK, AUTO_INCREMENT | Primary key. |
| `nama_daerah` | VARCHAR(150), NOT NULL | Nama wilayah/daerah cakupan distribusi. |
| `kode_wilayah` | VARCHAR(30), UNIQUE, NULLABLE | Kode internal wilayah (opsional, untuk pelaporan). |
| `keterangan` | TEXT, NULLABLE | Catatan tambahan mengenai wilayah. |
| `created_at` / `updated_at` | TIMESTAMP | Standar Eloquent timestamps. |

### 3.4 Tabel: `master_aset_valve`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK, AUTO_INCREMENT | Primary key. |
| `nama_aset` | VARCHAR(150), NOT NULL | Nama identifikasi Gate Valve, disimpan aman terhadap karakter tanda kutip (contoh: `GV 6"`) menggunakan parameter binding Eloquent, bukan concatenation string mentah. |
| `master_lokasi_id` | BIGINT UNSIGNED, FK → `master_lokasi.id` | Relasi ke daerah tempat aset berada. |
| `kapasitas_full_putaran` | FLOAT, NOT NULL | Kapasitas bukaan penuh valve dalam satuan putaran. |
| `latitude` | FLOAT(10,6), NOT NULL | Koordinat lintang statis lokasi aset. |
| `longitude` | FLOAT(10,6), NOT NULL | Koordinat bujur statis lokasi aset. |
| `sisa_bukaan_saat_ini` | FLOAT, DEFAULT 0 | Nilai turunan (derived/cached) hasil kalkulasi dari `log_gate_valve` terakhir, untuk mempercepat render dashboard tanpa query agregat berulang. |
| `status_bukaan` | ENUM('penuh','sebagian','tertutup'), DEFAULT 'penuh' | Ditentukan otomatis dari persentase `sisa_bukaan_saat_ini` terhadap `kapasitas_full_putaran`. |
| `created_at` / `updated_at` | TIMESTAMP | Standar Eloquent timestamps. |

### 3.5 Tabel: `log_gate_valve`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK, AUTO_INCREMENT | Primary key. |
| `master_aset_valve_id` | BIGINT UNSIGNED, FK → `master_aset_valve.id` | Aset GV yang dicatat. |
| `user_id` | BIGINT UNSIGNED, FK → `users.id` | Petugas/teknisi yang melakukan pencatatan. |
| `jumlah_tutupan` | FLOAT, NOT NULL | Jumlah putaran tutupan pada momen pencatatan ini. |
| `sisa_bukaan` | FLOAT, NOT NULL | Nilai sisa bukaan hasil pencatatan (kapasitas_full − akumulasi tutupan). |
| `latitude` | FLOAT(10,6), NOT NULL | Koordinat lintang saat log diambil di lapangan. |
| `longitude` | FLOAT(10,6), NOT NULL | Koordinat bujur saat log diambil di lapangan. |
| `foto_eviden` | VARCHAR(255), NULLABLE | Nama/direktori berkas foto bukti lapangan, disimpan di `storage/app/public/eviden/gv/`. |
| `catatan` | TEXT, NULLABLE | Catatan tambahan petugas. |
| `waktu_pencatatan` | TIMESTAMP, NOT NULL | Waktu aktual pencatatan di lapangan (dapat berbeda dari `created_at`). |
| `created_at` / `updated_at` | TIMESTAMP | Standar Eloquent timestamps. |

### 3.6 Tabel: `log_tekanan_air`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT UNSIGNED, PK, AUTO_INCREMENT | Primary key. |
| `master_lokasi_id` | BIGINT UNSIGNED, FK → `master_lokasi.id` | Daerah/stasiun pemantauan tekanan. |
| `user_id` | BIGINT UNSIGNED, FK → `users.id` | Petugas yang melakukan pencatatan. |
| `tekanan_bar` | FLOAT, NOT NULL | Nilai tekanan air terukur, dalam satuan Bar. |
| `status_tekanan` | ENUM('normal','rendah','kritis'), NOT NULL | Dihitung otomatis dari `tekanan_bar` (lihat 3.7). |
| `status_laju_air` | ENUM('mengalir','tidak_mengalir'), NOT NULL | Dihitung otomatis dari `tekanan_bar` (lihat 3.7). |
| `latitude` | FLOAT(10,6), NOT NULL | Koordinat lintang titik pemantauan. |
| `longitude` | FLOAT(10,6), NOT NULL | Koordinat bujur titik pemantauan. |
| `foto_eviden` | VARCHAR(255), NULLABLE | Nama/direktori berkas foto bukti, disimpan di `storage/app/public/eviden/tekanan/`. |
| `waktu_pengecekan` | TIMESTAMP, NOT NULL | Waktu aktual pengecekan di lapangan. |
| `created_at` / `updated_at` | TIMESTAMP | Standar Eloquent timestamps. |

### 3.7 Logika Kalkulasi Status Tekanan & Laju Air

Nilai `status_tekanan` dan `status_laju_air` pada tabel `log_tekanan_air` **TIDAK** diinput manual oleh pengguna, melainkan dihitung otomatis oleh backend (Model Observer / Accessor Laravel) berdasarkan nilai float `tekanan_bar` yang diinput, mengikuti aturan berikut:

| Rentang Tekanan (Bar) | Status Tekanan | Status Laju Air | Warna Indikator |
|---|---|---|---|
| ≥ 1.0 Bar | Normal | Mengalir | 🟢 Hijau |
| 0.5 – 0.99 Bar | Rendah | Mengalir | 🟡 Kuning |
| 0 Bar (= 0) | Kritis | Tidak Mengalir / Aliran Terhenti | 🔴 Merah |

> **Implementasi teknis:** gunakan Eloquent Observer (`LogTekananObserver::saving()`) atau Model Accessor/Mutator agar logika ambang batas ini konsisten di seluruh aplikasi (dashboard, peta, log, dan export) dan tidak diduplikasi secara manual di berbagai Controller/Component.

### 3.8 Relasi Antar Tabel (Entity Relationship)

| Relasi | Kardinalitas | Keterangan |
|---|---|---|
| `users` → `log_gate_valve` | 1 : N | Satu pengguna dapat membuat banyak log pencatatan Gate Valve. |
| `users` → `log_tekanan_air` | 1 : N | Satu pengguna dapat membuat banyak log pencatatan tekanan air. |
| `master_lokasi` → `master_aset_valve` | 1 : N | Satu daerah dapat memiliki banyak aset Gate Valve. |
| `master_lokasi` → `log_tekanan_air` | 1 : N | Satu daerah dapat memiliki banyak riwayat pencatatan tekanan. |
| `master_aset_valve` → `log_gate_valve` | 1 : N | Satu aset GV dapat memiliki banyak riwayat pencatatan bukaan/tutupan. |
| `users` → `personal_access_tokens` | 1 : N | Satu pengguna dapat memiliki banyak API token aktif (kesiapan login mobile). |

---

## 4. Functional Requirements (Per Modul)

Bab ini menjabarkan kebutuhan fungsional tiap modul secara rinci, mencakup deskripsi, aktor, alur proses, implementasi TALL Stack, dan kriteria penerimaan (*acceptance criteria*) yang menjadi acuan tim QA dalam pengujian.

### Modul 0 — Autentikasi & Role-Based Access Control (RBAC)

Modul fondasi yang mengatur mekanisme masuk ke sistem serta pembatasan hak akses berdasarkan peran pengguna.

#### 4.0.1 Fitur Halaman Login & Keamanan

- Form login (email & password) dengan styling Tailwind CSS yang bersih dan profesional.
- Autentikasi menggunakan Laravel Session Guard (session-based, bukan token, untuk akses web).
- **Login Throttling:** maksimum 5 kali percobaan gagal per email dalam 1 menit, mengacu pada Laravel `RateLimiter`/`ThrottlesLogins` bawaan.
- Pesan error generik ("Email atau password salah") untuk mencegah user enumeration.
- Redirect otomatis berdasarkan role setelah login berhasil: Admin → Executive Dashboard penuh; Pekerja → Executive Dashboard mode Read-Only.

#### 4.0.2 Matriks Hak Akses Peran (Role Access Matrix)

| Aksi / Menu | 👑 Admin | 👷 Pekerja |
|---|---|---|
| Melihat Executive Dashboard | ✅ Ya | ✅ Ya (Read-Only) |
| Melihat Peta Distribusi (GV & Tekanan) | ✅ Ya | ✅ Ya (Read-Only) |
| Melihat Log Gate Valve & Log Tekanan | ✅ Ya | ✅ Ya (Read-Only) |
| Tambah / Edit / Hapus Master Aset & Lokasi | ✅ Ya | ❌ Tidak — tombol disembunyikan & diblokir Middleware |
| Tambah Log Gate Valve / Log Tekanan Baru | ✅ Ya | ❌ Tidak (Fase 1 — input via Web khusus Admin/Operator; pencatatan lapangan penuh menyusul di app mobile Fase 2) |
| Export Laporan Excel | ✅ Ya | ❌ Tidak — tombol disembunyikan & diblokir Middleware |
| Manajemen Akun Pengguna | ✅ Ya | ❌ Tidak — menu tidak tampil di sidebar |

#### 4.0.3 Menu Manajemen Pengguna (Khusus Admin)

| Elemen | Spesifikasi |
|---|---|
| Form Tambah/Edit Akun | Nama Lengkap, Email (unique), Password (min. 8 karakter, di-hash Bcrypt), No. WhatsApp, Role (dropdown: admin/pekerja). |
| Validasi | Email harus unik & format valid; password dikonfirmasi 2x (`password_confirmation`) saat pembuatan akun baru. |
| Aksi Non-Aktifkan Akun | Admin dapat menonaktifkan (bukan menghapus permanen) akun pekerja via toggle `status_aktif`, agar riwayat log tetap terjaga integritas relasinya. |
| Kesiapan API Token | Setiap akun otomatis kompatibel dengan Laravel Sanctum; endpoint `/api/login` disiapkan untuk penerbitan `personal_access_token` yang akan dikonsumsi aplikasi mobile pada Fase 2. |

#### 4.0.4 Kriteria Penerimaan (Acceptance Criteria)

- Pengguna dengan role pekerja yang mencoba mengakses route khusus Admin (mis. `/admin/master-aset/create`) secara langsung via URL harus menerima response `403 Forbidden`.
- Setelah 5 kali percobaan login gagal, sistem mengunci percobaan login berikutnya selama periode throttle dan menampilkan pesan waktu tunggu.
- Tombol-tombol aksi yang tidak diizinkan tidak boleh muncul di DOM sama sekali untuk role pekerja (bukan sekadar disembunyikan via CSS `display:none`).

### Modul 1 — Executive Dashboard (Real-time Overview)

Layar pantau utama yang menyajikan ringkasan metrik krusial dan tren analitik secara real-time bagi seluruh pengguna sistem.

#### 4.1.1 User Story

| Sebagai | Saya ingin | Sehingga |
|---|---|---|
| Admin / Pekerja | melihat ringkasan kondisi jaringan pipa dalam satu layar tanpa perlu membuka banyak menu | saya dapat mengambil keputusan atau tindakan cepat terhadap kondisi darurat/kritis |

#### 4.1.2 Komponen & Logika Fungsional

| Komponen | Spesifikasi Fungsional |
|---|---|
| Auto-Refresh Metrics | Menggunakan `wire:poll.30s` pada Livewire Component untuk memperbarui seluruh kartu metrik & chart di latar belakang setiap 30 detik tanpa interupsi aktivitas pengguna. |
| Kartu Metrik: Total Aset Terdaftar | Menampilkan `COUNT()` total baris `master_aset_valve` aktif. |
| Kartu Metrik: Total Aktivitas Hari Ini | Menghitung gabungan jumlah baris `log_gate_valve` + `log_tekanan_air` dengan waktu pencatatan/pengecekan = hari ini. |
| Kartu Metrik: Peringatan Daerah Kritis | Menghitung jumlah `master_lokasi` dengan `log_tekanan_air` terbaru berstatus 'kritis' (0 Bar). Kartu diberi efek animasi "berdenyut" (Tailwind `animate-pulse`) berwarna merah jika nilai > 0. |
| Doughnut Chart | Proporsi `status_bukaan` seluruh Gate Valve (Penuh / Sebagian / Tertutup), dirender via Chart.js dan dibungkus `wire:ignore`. |
| Line Chart | Tren rata-rata `tekanan_bar` harian selama 7 hari terakhir, dihitung dari agregat `log_tekanan_air` (AVG per hari). |
| Tabel Ringkasan Bukaan Terkini | Mendaftar seluruh Gate Valve beserta `kapasitas_full_putaran`, total tutupan kumulatif, dan `sisa_bukaan_saat_ini`, divisualisasikan dengan Progress Bar Tailwind (lebar bar proporsional terhadap persentase sisa bukaan). |

#### 4.1.3 Kriteria Penerimaan

- Kartu metrik dan chart harus diperbarui otomatis maksimal 30 detik sekali tanpa memerlukan aksi manual refresh dari pengguna.
- Kartu Peringatan Daerah Kritis wajib menampilkan animasi visual (pulsing/berdenyut) hanya ketika terdapat minimal satu daerah berstatus kritis.
- Progress bar pada tabel ringkasan bukaan harus akurat merepresentasikan persentase `sisa_bukaan_saat_ini` terhadap `kapasitas_full_putaran`, dibulatkan 1 angka desimal.

### Modul 2 — Menu Peta Distribusi (2 Sub-Menu Terpisah)

Menu pemetaan geografis (*Geographic Information System*) yang dibagi menjadi 2 tab tampilan agar penandaan marker antara data Gate Valve dan data Tekanan Air tidak tumpang tindih dan tetap mudah dibaca.

> **Implementasi TALL Stack:** Perpindahan tab dikendalikan Livewire (properti public `$activeTab`) secara instan tanpa refresh halaman, sedangkan elemen `<div>` pembungkus peta wajib diberi atribut `wire:ignore` agar instance Leaflet.js/Google Maps tidak dihancurkan ulang oleh proses DOM-diffing Livewire setiap kali ada update state lain di komponen yang sama.

#### 4.2.1 Sub-Menu A — Peta Gate Valve (Map GV)

| Elemen | Spesifikasi |
|---|---|
| Sumber Data | `master_aset_valve` (kolom `latitude`, `longitude`, `status_bukaan`). |
| Marker Dinamis | 🟢 Hijau = Bukaan Penuh · 🟡 Kuning = Bukaan Sebagian · 🔴 Merah = Tutup Rapat. |
| Interactive Popup (klik marker) | Nama GV, Lokasi Daerah, Kapasitas Full, Sisa Bukaan Saat Ini, dan Nama Teknisi Terakhir yang mencatat (join ke `log_gate_valve` terbaru → `users.nama_lengkap`). |
| Clustering (opsional performa) | Untuk jumlah marker besar (>200 titik), gunakan marker clustering agar peta tetap responsif. |

#### 4.2.2 Sub-Menu B — Peta Tekanan Air (Map Tekanan)

| Elemen | Spesifikasi |
|---|---|
| Sumber Data | `master_lokasi` berelasi dengan `log_tekanan_air` terbaru per lokasi (`latitude`, `longitude`, `tekanan_bar`, `status_tekanan`, `status_laju_air`). |
| Marker Dinamis & Indikator Laju Air | 🟢 Normal (Mengalir): ≥ 1.0 Bar · 🟡 Rendah (Mengalir): 0.5–0.99 Bar · 🔴 Kritis (Tidak Mengalir/Aliran Terhenti): 0 Bar. |
| Interactive Popup (klik marker) | Nama Daerah, Titik Koordinat presisi (6 desimal), Detail Angka Tekanan Terkini (Bar), Status Tekanan (Normal/Rendah/Kritis), Status Laju Air (Mengalir/Tidak Mengalir), dan Waktu Pengecekan Terakhir. |

#### 4.2.3 Kriteria Penerimaan

- Perpindahan antara tab Peta GV dan Peta Tekanan tidak boleh menyebabkan reload halaman penuh (*full page reload*).
- Warna marker harus selalu konsisten dan otomatis mengikuti perubahan status data terbaru, tanpa perlu intervensi manual.
- Popup wajib menampilkan seluruh field yang dipersyaratkan pada tabel spesifikasi di atas, tanpa terkecuali.
- Peta tetap responsif (tidak freeze) saat berpindah tab berkali-kali secara berurutan, membuktikan `wire:ignore` bekerja dengan benar.

### Modul 3 — Manajemen Master Aset (Gate Valve & Lokasi) — Khusus Admin

Modul CRUD (Create, Read, Update, Delete) untuk data induk (*master data*) aset Gate Valve dan Lokasi/Daerah cakupan distribusi.

#### 4.3.1 Spesifikasi Form Input

| Field | Validasi & Catatan Teknis |
|---|---|
| Nama Aset | String, required. Wajib aman terhadap karakter tanda kutip ganda (`"`) — contoh input sah: `GV 6"`. Gunakan Eloquent parameter binding / `htmlspecialchars` pada output Blade untuk mencegah XSS maupun query injection. |
| Nama Daerah (relasi) | Dropdown/select2 dari tabel `master_lokasi`, required. |
| Kapasitas Full Putaran | FLOAT, required, min. 0.1. |
| Latitude | FLOAT(10,6), required, rentang valid −90 s.d. 90. |
| Longitude | FLOAT(10,6), required, rentang valid −180 s.d. 180. |

#### 4.3.2 Interaksi UI

- Form dikelola melalui Modal Pop-up Alpine.js (`x-show`/`x-transition`), memuat data dari Livewire Component saat tombol Tambah/Edit diklik, tanpa berpindah halaman.
- Live validation pesan error ditampilkan langsung di bawah masing-masing field menggunakan properti error bawaan Livewire (`wire:model.blur` + `@error`).
- Konfirmasi hapus data menggunakan modal konfirmasi terpisah (Alpine.js) untuk mencegah penghapusan tidak sengaja.

#### 4.3.3 Kriteria Penerimaan

- Input nama aset yang mengandung karakter tanda kutip ganda tersimpan dan tertampil dengan benar tanpa merusak layout maupun struktur HTML.
- Percobaan menghapus aset yang masih memiliki relasi `log_gate_valve` harus ditolak (atau menggunakan soft-delete) untuk menjaga integritas riwayat data historis.
- Seluruh endpoint CRUD modul ini menolak akses role pekerja dengan response `403`, baik dari UI maupun akses langsung ke route.

### Modul 4 & 5 — Log Gate Valve dan Log Tekanan Air

Menampilkan tabel riwayat operasional lapangan dengan kemampuan pencarian dan filter secara real-time, dilengkapi bukti visual (foto) dan grafik analitik.

#### 4.4.1 Tabel Riwayat & Live Search/Filter

| Elemen | Spesifikasi |
|---|---|
| Live Search | Input pencarian terhubung `wire:model.live.debounce.300ms` ke properti Livewire, memfilter data (nama aset/daerah, nama petugas) tanpa reload dan tanpa membanjiri query database (debounce 300ms). |
| Filter Tab/Status | Tab filter (mis. Semua / Normal / Rendah / Kritis untuk Log Tekanan; Penuh / Sebagian / Tertutup untuk Log GV) menggunakan `wire:model` pada state aktif, hasil ter-update instan. |
| Kolom Koordinat | Setiap baris log menampilkan latitude & longitude presisi tempat data diambil di lapangan (bukan koordinat statis aset, melainkan titik aktual saat pencatatan). |
| Kolom Bukti Foto | Thumbnail kecil (lazy-loaded) yang ketika diklik memunculkan Lightbox Modal Alpine.js menampilkan `foto_eviden` dalam resolusi penuh dengan overlay gelap dan tombol tutup. |
| Paginasi | Menggunakan paginasi Livewire bawaan (`WithPagination` trait), 15–25 baris per halaman untuk menjaga performa rendering tabel. |

#### 4.4.2 Modul Log Tekanan — Grafik Ambang Batas

- Bar Chart interaktif (Chart.js) menampilkan riwayat `tekanan_bar` per waktu pengecekan untuk lokasi terpilih.
- Garis Ambang Batas Minimum digambarkan sebagai garis putus-putus (*dashed line*) berwarna hijau pada angka 1.0 Bar, menandai batas bawah status Normal.
- Elemen `<canvas>` Chart.js dibungkus `wire:ignore` agar instance grafik tidak di-reinisialisasi ulang secara tidak perlu saat komponen Livewire lain di halaman yang sama melakukan update.

#### 4.4.3 Kriteria Penerimaan

- Hasil pencarian/filter tampil dalam waktu kurang dari 1 detik setelah pengguna berhenti mengetik (setelah periode debounce).
- Lightbox foto dapat ditutup melalui tombol close, klik area luar gambar (backdrop), maupun tombol Escape pada keyboard.
- Grafik Bar Chart pada Log Tekanan wajib menampilkan garis ambang batas 1.0 Bar pada setiap rentang data yang ditampilkan.
- Role pekerja dapat melihat seluruh data pada modul ini namun tidak menemukan tombol tambah/edit/hapus data log dalam bentuk apa pun.

### Modul 6 — Sistem Pelaporan Perusahaan (Export Engine) — Khusus Admin

Fasilitas ekspor data operasional ke dalam format `.xlsx` yang siap digunakan untuk pelaporan manajemen maupun audit internal, dengan standar penyajian data yang konsisten dengan tampilan dashboard.

#### 4.6.1 Spesifikasi Fungsional

| Elemen | Spesifikasi |
|---|---|
| Engine | Laravel Excel (`maatwebsite/excel`) menggunakan class Export (`FromQuery` / `FromCollection`) per modul (Log Gate Valve, Log Tekanan Air). |
| Preservasi Filter Aktif | Parameter filter/search yang sedang aktif pada tampilan Livewire (kata kunci pencarian, rentang tanggal, status) diteruskan ke class Export sehingga file yang diunduh sesuai persis dengan data yang sedang dilihat Admin di layar — bukan seluruh data mentah tabel. |
| Kolom Data Export | Wajib menyertakan: Nama Aset/Lokasi, Tanggal & Waktu, Petugas Pencatat, Nilai (Bukaan/Tekanan), Latitude, Longitude, Status, dan khusus Log Tekanan turut menyertakan Status Laju Air (Mengalir/Tidak Mengalir). |
| Format File | Nama file mengikuti pola `laporan_{modul}_{YYYYMMDD_HHmm}.xlsx`, dengan header kolom di-bold dan lebar kolom auto-fit (agar sesuai standar enterprise/tidak terpotong). |
| Batasan Akses | Endpoint export dilindungi Middleware `role:admin`; percobaan akses langsung oleh role pekerja mengembalikan `403 Forbidden`. |

#### 4.6.2 Kriteria Penerimaan

- File `.xlsx` yang diunduh berisi data yang identik dengan hasil filter/pencarian yang sedang aktif di layar pada saat tombol Export ditekan.
- Kolom koordinat lokasi dan status laju air wajib selalu tersedia pada file export Log Tekanan Air, tidak boleh kosong/terlewat.
- Proses generate file tidak memblokir (non-blocking) antarmuka Livewire — untuk data besar, pertimbangkan Laravel Queue agar proses export berjalan di background.

---

## 5. User Interface (UI) Layout Guidelines

Bab ini menjadi panduan bagi tim front-end developer dalam menyusun tata letak antarmuka agar konsisten, profesional, dan selaras dengan prinsip pemisahan hak akses (RBAC).

### 5.1 Navigasi Sidebar Berdasarkan Role

| Menu Sidebar | 👑 Tampil untuk Admin | 👷 Tampil untuk Pekerja |
|---|---|---|
| Executive Dashboard | ✅ | ✅ |
| Peta Distribusi ▸ Peta Gate Valve | ✅ | ✅ |
| Peta Distribusi ▸ Peta Tekanan Air | ✅ | ✅ |
| Master Aset & Lokasi | ✅ | ❌ (menu tidak dirender) |
| Log Gate Valve | ✅ (+ aksi CRUD) | ✅ (Read-Only, tanpa tombol aksi) |
| Log Tekanan Air | ✅ (+ aksi CRUD) | ✅ (Read-Only, tanpa tombol aksi) |
| Laporan / Export | ✅ | ❌ (menu tidak dirender) |
| Manajemen Pengguna | ✅ | ❌ (menu tidak dirender) |

> **Panduan implementasi:** gunakan komponen Blade `@role('admin') ... @endrole` atau helper `auth()->user()->role === 'admin'` langsung pada partial `sidebar-menu.blade.php`. Menu yang tidak diizinkan harus benar-benar tidak dirender ke HTML (bukan disembunyikan lewat CSS), agar tidak terlihat pada inspect element browser.

### 5.2 Struktur Layout Umum Dashboard

- **Top Bar** — Berisi logo/nama sistem, nama pengguna & role aktif (badge berwarna), serta tombol logout.
- **Sidebar Kiri (collapsible)** — Navigasi utama sesuai matriks pada 5.1, dengan indikator menu aktif (*active state*) menggunakan warna aksen Tailwind.
- **Area Konten Utama** — Container responsif dengan max-width dan padding konsisten (mis. `p-6`, `max-w-screen-2xl`), menampung kartu metrik, tabel, atau peta sesuai halaman aktif.
- **Breadcrumb** — Menunjukkan posisi halaman (mis. Dashboard / Peta Distribusi / Peta Tekanan Air) untuk orientasi pengguna.

### 5.3 Layout Menu Peta Distribusi

- Kedua sub-menu (Peta GV & Peta Tekanan) ditampilkan sebagai Tab Navigation horizontal di bagian atas area konten, bukan sebagai 2 menu sidebar terpisah, untuk menegaskan bahwa keduanya adalah satu kesatuan modul "Peta Distribusi".
- Peta ditampilkan full-width dengan tinggi minimum `70vh` agar area eksplorasi peta cukup luas.
- Legenda warna marker (🟢🟡🔴) ditampilkan sebagai floating panel di sudut kanan-bawah atau kiri-atas peta, selalu terlihat tanpa menghalangi interaksi peta.
- Popup info marker menggunakan card ringkas dengan tipografi Tailwind yang jelas (judul bold, label abu-abu, nilai tegas), maksimal lebar 280px agar tidak menutupi area peta terlalu banyak.

### 5.4 Panduan Lightbox Foto

- Thumbnail pada tabel log berukuran kecil (mis. 48×48px, `object-cover`, `rounded`) dengan efek hover (scale/opacity) menandakan elemen dapat diklik.
- Modal lightbox menampilkan foto dalam ukuran maksimal (`contain`) dengan latar belakang overlay gelap semi-transparan (`bg-black/80`), serta metadata singkat (nama aset/lokasi, tanggal, petugas) di bawah foto.
- Transisi buka/tutup modal menggunakan `x-transition` Alpine.js agar terasa halus, bukan muncul/hilang secara instan.

### 5.5 Panduan Struktur Chart

| Chart | Lokasi | Panduan Visual |
|---|---|---|
| Doughnut Chart (Status Valve) | Executive Dashboard | 3 warna konsisten dengan status marker peta (hijau/kuning/merah); legend ditampilkan di sisi kanan chart. |
| Line Chart (Tren Tekanan 7 Hari) | Executive Dashboard | Sumbu-Y dimulai dari 0 Bar; gunakan warna aksen biru dengan area gradient tipis di bawah garis. |
| Bar Chart (Riwayat Tekanan + Ambang Batas) | Log Tekanan Air | Garis ambang batas 1.0 Bar berwarna hijau putus-putus (dashed) melintasi seluruh lebar chart sebagai referensi visual cepat. |

### 5.6 Prinsip Desain Visual (Tailwind Design Tokens)

| Elemen | Token / Pedoman |
|---|---|
| Warna Primer (Aksi Utama) | Biru navy (mis. `blue-800`/`900`) — tombol utama, header, elemen navigasi aktif. |
| Warna Status Normal/Sukses | Hijau (`green-500`/`600`) — status Normal, Mengalir, Bukaan Penuh. |
| Warna Status Rendah/Peringatan | Kuning/Amber (`amber-500`) — status Rendah, Bukaan Sebagian. |
| Warna Status Kritis/Bahaya | Merah (`red-600`) — status Kritis, Tidak Mengalir, Tutup Rapat. |
| Tipografi | Font sans-serif standar (Inter/system-ui), ukuran dasar 14–16px untuk body, bold untuk label metrik utama. |
| Responsiveness | Breakpoint Tailwind standar (`sm`/`md`/`lg`/`xl`); sidebar otomatis collapse menjadi off-canvas pada layar < `md`. |

---

## 6. Non-Functional Requirements

### 6.1 Performa (Performance)

| Aspek | Target / Ketentuan |
|---|---|
| DOM Diffing Peta | Elemen peta wajib dibungkus `wire:ignore`; tanpa pengecualian, guna mencegah re-render penuh yang menyebabkan flicker atau hilangnya interaksi marker. |
| DOM Diffing Tabel Besar | Gunakan `wire:key` unik pada setiap baris hasil `@foreach` agar Livewire hanya memperbarui baris yang benar-benar berubah, bukan seluruh tabel. |
| Waktu Muat Halaman (Load Time) | Target Time-to-Interactive ≤ 2.5 detik pada koneksi 4G standar untuk halaman Dashboard dan Peta. |
| Live Search Debounce | Wajib menggunakan debounce minimal 300ms (`wire:model.live.debounce.300ms`) untuk mencegah query database berlebihan saat pengguna mengetik. |
| Paginasi | Seluruh tabel log wajib menggunakan paginasi server-side (bukan memuat seluruh data sekaligus ke browser). |
| Query Database | Gunakan eager loading (`with()`) pada relasi Eloquent untuk mencegah N+1 query problem, khususnya pada tabel Log dan Peta yang menampilkan data relasi (nama petugas, nama lokasi). |

### 6.2 Keamanan (Security)

| Aspek | Target / Ketentuan |
|---|---|
| Enkripsi Password | Seluruh password pengguna WAJIB di-hash menggunakan algoritma Bcrypt (default Laravel `Hash` facade), tidak boleh disimpan dalam bentuk plain-text maupun enkripsi reversible. |
| Proteksi Middleware | Setiap route yang membutuhkan hak akses Admin WAJIB melewati Middleware role-check di level route/group, tidak cukup hanya proteksi UI. |
| Cross-Site Scripting (XSS) | Seluruh output data ke Blade menggunakan `{{ }}` (auto-escaping), tidak menggunakan `{!! !!}` kecuali untuk konten yang telah melalui sanitasi eksplisit. |
| Cross-Site Request Forgery (CSRF) | Seluruh form non-Livewire menyertakan `@csrf`; Livewire menangani proteksi CSRF secara otomatis pada request internalnya. |
| SQL Injection | Seluruh query menggunakan Eloquent ORM / Query Builder dengan parameter binding, tidak ada raw query dengan string concatenation langsung dari input pengguna. |
| Login Throttling | Maksimum 5 percobaan login gagal per menit per akun, sesuai spesifikasi Modul 0. |
| Validasi Upload Foto | File `foto_eviden` divalidasi tipe MIME (jpg, jpeg, png), ukuran maksimum 5MB per file, dan disimpan dengan nama file ter-generate acak (bukan nama asli unggahan) untuk mencegah path traversal. |
| Kesiapan API Token | Endpoint API (Sanctum) yang disiapkan untuk Fase 2 wajib menggunakan token dengan scope/ability terbatas serta masa berlaku (expiry) yang dapat dikonfigurasi. |

### 6.3 Reliabilitas & Ketersediaan (Reliability)

- Sistem menargetkan ketersediaan (*uptime*) operasional ≥ 99% pada jam kerja PDAM.
- Setiap kegagalan simpan data (mis. koneksi database terputus) wajib menampilkan notifikasi error yang informatif kepada pengguna, bukan halaman error mentah (blank/500 page).
- Backup basis data dijadwalkan otomatis minimal 1 kali per hari (mis. via Laravel Scheduler + `mysqldump`).

### 6.4 Manajemen Penyimpanan Media (Media Storage)

| Aspek | Ketentuan |
|---|---|
| Struktur Direktori | `storage/app/public/eviden/gv/` untuk foto Log Gate Valve; `storage/app/public/eviden/tekanan/` untuk foto Log Tekanan Air. |
| Symbolic Link | Wajib menjalankan `php artisan storage:link` agar direktori `storage/app/public` dapat diakses publik melalui `public/storage`. |
| Kompresi Gambar | Foto yang diunggah di-resize/dikompresi otomatis (mis. via Intervention Image) ke resolusi maksimum 1600px pada sisi terpanjang untuk mengefisienkan ruang penyimpanan tanpa mengorbankan kejelasan bukti visual. |
| Retensi Data | Foto eviden disimpan mengikuti masa retensi data log terkait; penghapusan log (jika diizinkan) turut menghapus berkas foto terkait dari storage. |

### 6.5 Skalabilitas & Pemeliharaan (Scalability & Maintainability)

- Struktur kode mengikuti konvensi Laravel standar (Repository/Service pattern opsional untuk logika bisnis kompleks seperti kalkulasi status tekanan) agar mudah dipelihara tim developer baru.
- Penamaan Livewire Component mengikuti konvensi domain modul (mis. `Dashboard\ExecutiveOverview`, `Peta\MapGateValve`, `Peta\MapTekananAir`, `Aset\MasterAsetCrud`).
- Seluruh konstanta ambang batas (1.0 Bar, 0.5 Bar) disimpan dalam config file (`config/water-monitoring.php`), bukan hardcoded di berbagai tempat, agar mudah disesuaikan tanpa mengubah banyak file.

### 6.6 Kompatibilitas (Compatibility)

| Aspek | Ketentuan |
|---|---|
| Browser Support | 2 versi terbaru Google Chrome, Mozilla Firefox, Microsoft Edge, dan Safari. |
| Resolusi Layar | Optimal pada resolusi desktop ≥ 1366×768; tampilan tetap dapat digunakan (usable, meski tidak menjadi prioritas utama Fase 1) pada tablet landscape. |
| Kesiapan Integrasi Mobile | Seluruh endpoint data (khususnya autentikasi via Sanctum) dirancang stateless-ready agar dapat langsung dikonsumsi aplikasi mobile teknisi pada Fase 2 tanpa perombakan struktur backend. |

---

## Lembar Persetujuan (Sign-Off Sheet)

Dokumen Product Requirement Document (PRD) ini menjadi acuan resmi pengembangan Fase 1 — Web Dashboard & Core Backend. Persetujuan dari seluruh pemangku kepentingan berikut diperlukan sebelum proses pengembangan (*sprint planning*) dimulai.

| Peran | Nama | Tanda Tangan | Tanggal |
|---|---|---|---|
| Project Sponsor | | | |
| Product Manager | | | |
| Tech Lead / System Analyst | | | |
| QA Lead | | | |

---

*Dokumen Internal — Confidential · Versi 1.0 · PRD-PDAM-WD-001*
