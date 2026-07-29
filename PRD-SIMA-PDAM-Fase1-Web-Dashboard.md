# PRODUCT REQUIREMENT DOCUMENT (PRD)
## Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM
### Fase 1 — Web Dashboard & Core Backend

---

### Dokumen Kontrol

| Item | Keterangan |
|---|---|
| **Nama Dokumen** | PRD Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM |
| **Fase** | Fase 1 — Web Dashboard & Core Backend |
| **Arsitektur** | TALL Stack (Tailwind CSS, Alpine.js, Livewire, Laravel) |
| **Disusun oleh** | Senior Product Manager & System Analyst Enterprise |
| **Status** | Draft untuk Review Tim Development |
| **Versi** | 1.0 |
| **Tanggal** | 6 Juli 2026 |

---

## DAFTAR ISI

1. Project Information & Objectives
2. System Architecture & Tech Stack
3. Database & Relational Model Scope
4. Functional Requirements (Per Modul)
5. User Interface (UI) Layout Guidelines
6. Non-Functional Requirements
7. Lampiran (Glossary & Definisi Status)

---

## 1. PROJECT INFORMATION & OBJECTIVES

### 1.1 Latar Belakang

PDAM sebagai penyedia layanan distribusi air bersih memerlukan alat kendali terpusat untuk memantau kondisi infrastruktur jaringan pipa yang tersebar secara geografis, khususnya **Gate Valve (GV)** dan **titik tekanan air**. Selama ini pemantauan dilakukan secara manual dan terfragmentasi, sehingga menyulitkan deteksi dini gangguan distribusi (tekanan kritis, valve tertutup tidak semestinya) serta audit histori tindakan lapangan (siapa yang memutar valve, kapan, dan berapa besar bukaan tersisa).

**Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM** dibangun untuk menjawab kebutuhan tersebut, dimulai dari **Fase 1** yang berfokus pada:
- Web Dashboard sebagai *control room* terpusat.
- Core Backend & struktur database yang solid dan *future-proof*, sebagai fondasi bagi aplikasi mobile lapangan pada fase berikutnya.

### 1.2 Tujuan Utama (Goals)

| No | Goal | Deskripsi | Indikator Keberhasilan |
|---|---|---|---|
| 1 | **Real-time Executive Overview & GIS Mapping** | Menyajikan ringkasan kondisi operasional dan visualisasi peta wilayah distribusi secara langsung di halaman utama | Data metrik & peta ter-update otomatis maksimal setiap 30 detik tanpa reload halaman |
| 2 | **Kontrol Presisi Aset** | Melacak fisik sisa bukaan Gate Valve secara otomatis berdasarkan akumulasi input riwayat putaran | Sisa bukaan valve terhitung otomatis dan akurat dari log historis |
| 3 | **Early Warning System** | Memantau tren dan log tekanan air untuk mendeteksi status Normal/Rendah/Kritis | Sistem mampu menandai wilayah kritis (tekanan 0 Bar) secara visual real-time |
| 4 | **Future-Proof Database** | Membangun fondasi database yang siap menampung data spasial dan media foto eviden | Struktur tabel sudah mendukung koordinat presisi & penyimpanan file gambar sejak Fase 1 |

### 1.3 Ruang Lingkup (Scope)

**Termasuk dalam Fase 1 (In-Scope):**
- Web Dashboard berbasis TALL Stack.
- Modul Executive Dashboard (peta interaktif, chart, kartu metrik).
- Modul Manajemen Master Aset (Gate Valve & Lokasi).
- Modul Log Gate Valve & Log Tekanan Air (tampilan, filter, pencarian).
- Modul Export Laporan (.xlsx).
- Struktur database backend yang siap menerima input dari aplikasi mobile di fase berikutnya.

**Tidak termasuk dalam Fase 1 (Out-of-Scope):**
- Aplikasi mobile untuk teknisi lapangan (direncanakan Fase 2).
- Input data log dari perangkat IoT/sensor otomatis (real-time hardware integration).
- Modul notifikasi push/SMS/WhatsApp Gateway.
- Manajemen pelanggan/billing PDAM.

### 1.4 Target Pengguna

| Role | Kebutuhan Utama |
|---|---|
| **Admin/Operator Control Room** | Memantau dashboard, mengelola master data aset, melakukan export laporan |
| **Manajemen/Eksekutif** | Melihat ringkasan kondisi jaringan secara cepat dan visual |
| **Tim Teknis IT** | Mengelola integritas data, backend, dan integrasi masa depan |

---

## 2. SYSTEM ARCHITECTURE & TECH STACK

### 2.1 Arsitektur Umum

Sistem menggunakan pendekatan **Server-Driven UI dengan Reaktivitas Hybrid**, di mana logika utama dan rendering tetap berada di sisi server (Laravel + Livewire), sementara interaktivitas ringan di sisi klien (modal, peta, lightbox) ditangani oleh Alpine.js tanpa membangun API terpisah (SPA-less architecture).

```
[ Browser Client ]
      │
      ▼
[ Tailwind CSS — Presentation Layer ]
      │
      ▼
[ Alpine.js — Client-side Interactivity: Modal, Dropdown, Inisialisasi Peta ]
      │
      ▼
[ Livewire — Server-Driven Reactivity: wire:poll, wire:model, wire:ignore ]
      │
      ▼
[ Laravel — Core MVC, Eloquent ORM, Business Logic ]
      │
      ▼
[ Database (MySQL/PostgreSQL) ]
```

### 2.2 Rincian TALL Stack

| Layer | Teknologi | Fungsi dalam Sistem |
|---|---|---|
| **Backend & Data Engine** | Laravel | Core MVC, Eloquent ORM, validasi input, manajemen relasi database, otentikasi & otorisasi |
| **Reactivity Layer** | Livewire | Pencarian real-time (`wire:model`), filter tab tanpa reload, auto-refresh metrik (`wire:poll`), state management komponen |
| **Client Interactivity** | Alpine.js | Modal pop-up form & gambar, dropdown menu, inisialisasi & kontrol peta interaktif (Leaflet.js/Google Maps API) |
| **Styling** | Tailwind CSS | Utility-first styling, membangun tampilan dashboard yang bersih, konsisten, dan profesional |
| **Visualisasi Data** | Chart.js | Grafik analitik interaktif (Doughnut Chart, Line Chart, Bar Chart) |
| **Pelaporan** | Laravel Excel (Maatwebsite) + PHP GD Extension | Export laporan .xlsx dengan pewarnaan sel berstandar enterprise |
| **Pemetaan** | Leaflet.js / Google Maps API | Visualisasi GIS, marker aset, popup interaktif |

### 2.3 Prinsip Interaksi Antar Layer

- **Livewire ↔ Alpine.js:** Elemen yang dikelola penuh oleh JavaScript pihak ketiga (peta, chart) **wajib** dibungkus `wire:ignore` agar DOM tidak di-*diff ulang* oleh Livewire saat polling, sehingga state peta/chart tidak reset atau berkedip.
- **Livewire Polling:** Komponen dashboard utama menggunakan `wire:poll.30s` untuk menjaga data tetap segar tanpa interaksi pengguna maupun reload penuh.
- **Alpine.js State:** Digunakan untuk state UI sementara yang tidak perlu disimpan di server (status buka/tutup modal, tab aktif secara visual, lightbox terbuka/tertutup).
- **Data Flow ke Alpine:** Data peta/koordinat dikirim dari Livewire ke Alpine melalui `x-data` yang di-*hydrate* dari properti Livewire saat komponen pertama kali dimuat (bukan setiap polling), untuk menghindari re-inisialisasi peta.

### 2.4 Library Pendukung

| Library | Kebutuhan |
|---|---|
| Leaflet.js atau Google Maps JavaScript API | Rendering peta interaktif dan marker |
| Chart.js | Doughnut Chart, Line Chart, Bar Chart dengan garis ambang batas |
| Laravel Excel (Maatwebsite/Excel) | Generate file .xlsx dari data terfilter |
| PHP GD Extension | Diperlukan oleh Laravel Excel untuk pewarnaan sel & styling laporan |
| Intervention Image (rekomendasi) | Optimasi ukuran file `foto_eviden` sebelum disimpan |

---

## 3. DATABASE & RELATIONAL MODEL SCOPE

### 3.1 Entity Overview

| Entity | Deskripsi |
|---|---|
| `master_lokasi` | Data wilayah/daerah distribusi |
| `master_valve` | Data master Gate Valve (aset fisik) |
| `log_valve` | Riwayat aktivitas buka/tutup Gate Valve |
| `log_tekanan` | Riwayat pembacaan tekanan air di suatu titik |
| `users` | Data pengguna sistem (operator/teknisi/admin) |

**Relasi:**
`master_lokasi (1) — (N) master_valve`
`master_valve (1) — (N) log_valve`
`master_valve (1) — (N) log_tekanan`
`users (1) — (N) log_valve` dan `users (1) — (N) log_tekanan` (mencatat teknisi pelaksana)

### 3.2 Struktur Tabel Detail

#### Tabel: `master_lokasi`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | `BIGINT UNSIGNED, PK, AUTO_INCREMENT` | Primary key |
| `nama_daerah` | `VARCHAR(150)` | Nama wilayah/daerah distribusi |
| `deskripsi` | `TEXT NULLABLE` | Keterangan tambahan wilayah |
| `created_at`, `updated_at` | `TIMESTAMP` | Standar Eloquent timestamps |

#### Tabel: `master_valve`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | `BIGINT UNSIGNED, PK, AUTO_INCREMENT` | Primary key |
| `master_lokasi_id` | `BIGINT UNSIGNED, FK → master_lokasi.id` | Relasi ke lokasi |
| `nama_aset` | `VARCHAR(100)` | Nama aset. **Wajib disimpan menggunakan output ter-escape (htmlspecialchars/Blade `{{ }}`)** agar karakter tanda kutip seperti `GV 6"` tersimpan dan tertampil aman tanpa merusak markup HTML |
| `kapasitas_full_putaran` | `FLOAT(8,2)` | Kapasitas penuh putaran valve. Menggunakan tipe **Float/Decimal** untuk mendukung nilai pecahan (misal 12.5 putaran) |
| `latitude` | `FLOAT(10,6)` | **Wajib.** Titik koordinat lintang presisi tinggi aset |
| `longitude` | `FLOAT(10,6)` | **Wajib.** Titik koordinat bujur presisi tinggi aset |
| `status_terkini` | `ENUM('normal','rendah','kritis')` | Status agregat terakhir, di-*update* dari log terbaru untuk efisiensi query marker peta |
| `created_at`, `updated_at` | `TIMESTAMP` | Standar Eloquent timestamps |

> **Catatan Teknis:** Penggunaan `FLOAT(10,6)` dipilih agar mampu menampung presisi koordinat GPS hingga 6 digit desimal (± 0.11 meter akurasi), yang krusial untuk pemetaan aset infrastruktur skala kota.

#### Tabel: `log_valve`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | `BIGINT UNSIGNED, PK, AUTO_INCREMENT` | Primary key |
| `master_valve_id` | `BIGINT UNSIGNED, FK → master_valve.id` | Relasi ke aset valve |
| `user_id` | `BIGINT UNSIGNED, FK → users.id` | Teknisi pelaksana |
| `jumlah_putaran` | `FLOAT(8,2)` | Jumlah putaran buka/tutup pada aktivitas ini (mendukung nilai pecahan) |
| `jenis_aktivitas` | `ENUM('buka','tutup')` | Arah aktivitas putaran |
| `sisa_bukaan_saat_ini` | `FLOAT(8,2)` | **Kalkulasi otomatis** hasil akumulasi historis vs `kapasitas_full_putaran` |
| `latitude` | `FLOAT(10,6)` | Koordinat aktual saat log dicatat (dapat berbeda tipis dari master jika direkam via GPS lapangan) |
| `longitude` | `FLOAT(10,6)` | Koordinat aktual saat log dicatat |
| `foto_eviden` | `VARCHAR(255) NULLABLE` | **Menyimpan path/nama file** hasil upload (bukan BLOB), contoh: `log-valve/2026/07/uuid_namafile.jpg` |
| `catatan` | `TEXT NULLABLE` | Catatan tambahan teknisi |
| `created_at`, `updated_at` | `TIMESTAMP` | Standar Eloquent timestamps |

#### Tabel: `log_tekanan`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | `BIGINT UNSIGNED, PK, AUTO_INCREMENT` | Primary key |
| `master_valve_id` | `BIGINT UNSIGNED, FK → master_valve.id` | Titik pemantauan tekanan terasosiasi aset |
| `user_id` | `BIGINT UNSIGNED, FK → users.id` | Teknisi pencatat |
| `nilai_tekanan` | `FLOAT(6,2)` | Nilai tekanan air dalam satuan Bar (mendukung desimal, misal 1.25) |
| `status` | `ENUM('normal','rendah','kritis')` | Diturunkan otomatis dari `nilai_tekanan` (misal: kritis jika = 0) |
| `latitude` | `FLOAT(10,6)` | Koordinat aktual titik pencatatan tekanan |
| `longitude` | `FLOAT(10,6)` | Koordinat aktual titik pencatatan tekanan |
| `foto_eviden` | `VARCHAR(255) NULLABLE` | Path/nama file bukti foto pembacaan tekanan |
| `created_at`, `updated_at` | `TIMESTAMP` | Standar Eloquent timestamps |

#### Tabel: `users`

| Kolom | Tipe Data | Keterangan |
|---|---|---|
| `id` | `BIGINT UNSIGNED, PK, AUTO_INCREMENT` | Primary key |
| `name` | `VARCHAR(100)` | Nama pengguna/teknisi |
| `email` | `VARCHAR(150) UNIQUE` | Email login |
| `password` | `VARCHAR(255)` | Password ter-hash (bcrypt) |
| `role` | `ENUM('admin','operator','teknisi')` | Level akses |
| `created_at`, `updated_at` | `TIMESTAMP` | Standar Eloquent timestamps |

### 3.3 Penanganan Tipe Data Float

- Seluruh kolom kuantitatif yang berpotensi memiliki nilai pecahan (`kapasitas_full_putaran`, `jumlah_putaran`, `sisa_bukaan_saat_ini`, `nilai_tekanan`) **wajib** menggunakan tipe `FLOAT` dengan presisi eksplisit, bukan `INTEGER`, untuk mengakomodasi satuan lapangan yang tidak selalu bulat (contoh: putaran ¼, ½ valve; tekanan 1.25 Bar).
- Validasi backend (Laravel Form Request) menerapkan aturan `numeric` dan `min:0` pada seluruh kolom Float untuk mencegah nilai negatif yang tidak logis secara fisik.
- Perhitungan `sisa_bukaan_saat_ini` dilakukan di **Service/Model Layer** Laravel (bukan di Blade/View) agar konsisten dan dapat diuji (unit-testable).

### 3.4 Penyimpanan Media (`foto_eviden`)

- Kolom `foto_eviden` pada `log_valve` dan `log_tekanan` menyimpan **string path relatif**, bukan data biner.
- File fisik disimpan menggunakan Laravel Filesystem (`storage/app/public/...`) dan diakses melalui symbolic link `storage:link`.
- Struktur folder disarankan: `log-valve/{tahun}/{bulan}/` dan `log-tekanan/{tahun}/{bulan}/` untuk mempermudah manajemen dan arsip jangka panjang.

---

## 4. FUNCTIONAL REQUIREMENTS (PER MODUL)

### 4.1 Modul Utama: Executive Dashboard (Real-time Overview & Map)

| Aspek | Detail |
|---|---|
| **Aktor** | Admin, Operator, Manajemen |
| **Tujuan** | Memberikan gambaran kondisi jaringan secara instan dan visual |

**Alur Fungsional:**

1. Saat halaman dimuat, komponen Livewire `DashboardOverview` mengambil data agregat: total aset, total aktivitas hari ini, dan jumlah daerah kritis.
2. Komponen menerapkan `wire:poll.30s="refreshData"` pada wrapper utama sehingga kartu metrik, tabel ringkasan, dan sumber data peta diperbarui otomatis setiap 30 detik tanpa reload browser.
3. **Kartu Metrik Utama** ditampilkan dalam grid 3 kolom:
   - Total Aset Terdaftar.
   - Total Aktivitas Hari Ini (gabungan log valve + log tekanan).
   - Peringatan Daerah Kritis — kartu ini menggunakan animasi `animate-pulse` (Tailwind) berwarna merah apabila terdapat minimal satu titik dengan tekanan 0 Bar.
4. **Peta Interaktif (GIS Map):**
   - Peta diinisialisasi oleh Alpine.js (`x-data`, `x-init`) menggunakan Leaflet.js/Google Maps API, dan **wajib dibungkus `wire:ignore`** pada elemen kontainernya agar re-render Livewire tidak menghancurkan instance peta.
   - Data marker (posisi GV & titik tekanan) di-*passing* dari Livewire ke Alpine sebagai JSON pada inisialisasi awal komponen.
   - Warna marker mengikuti kondisi: 🟢 Normal, 🟡 Rendah, 🔴 Kritis/Tertutup.
   - Klik marker memicu popup Leaflet/Google Maps berisi: Nama Aset, Lokasi, Tekanan Terkini, Status Bukaan GV, dan Nama Teknisi Terakhir.
   - Pembaruan posisi/status marker saat polling dilakukan melalui pemanggilan method JavaScript Alpine (bukan re-render seluruh peta), dipicu event dari Livewire (`$dispatch`) ke listener Alpine.
5. **Visualisasi Chart:**
   - Doughnut Chart menampilkan proporsi status valve (Normal/Rendah/Kritis).
   - Line Chart menampilkan tren rata-rata tekanan 7 hari terakhir.
   - Kedua chart dibungkus `wire:ignore` dan diinisialisasi via Alpine/Chart.js, diperbarui melalui method `update()` Chart.js saat menerima event baru dari Livewire.
6. **Tabel Ringkasan Bukaan Terkini:** Menampilkan seluruh GV dengan kolom Kapasitas Full, Total Tutupan, dan Sisa Bukaan Saat Ini yang divisualisasikan sebagai *progress bar* Tailwind (lebar bar proporsional terhadap persentase sisa bukaan).

**Acceptance Criteria:**
- [ ] Data metrik, peta, dan tabel diperbarui otomatis tanpa interaksi pengguna dalam interval ≤ 30 detik.
- [ ] Peta tidak "berkedip" atau kehilangan posisi zoom/pan saat polling terjadi.
- [ ] Kartu peringatan kritis berdenyut visual saat ada tekanan 0 Bar.

---

### 4.2 Modul A: Manajemen Master Aset (Gate Valve & Lokasi)

| Aspek | Detail |
|---|---|
| **Aktor** | Admin, Operator |
| **Tujuan** | CRUD data master aset valve dan lokasi sebagai rujukan seluruh modul log |

**Alur Fungsional:**

1. Halaman menampilkan tabel daftar aset (Livewire component dengan paginasi).
2. Tombol "Tambah Aset" memicu Alpine.js membuka **Modal** (`x-show`, transisi Tailwind) berisi form.
3. **Form Input** mencakup:
   - Nama Aset — mendukung karakter tanda kutip (`"`) secara aman; backend melakukan sanitasi/escaping otomatis melalui Eloquent mass-assignment & Blade escaping, sehingga input seperti `GV 6"` tersimpan dan tampil dengan benar tanpa merusak layout.
   - Nama Daerah/Lokasi — dropdown relasi ke `master_lokasi`.
   - Kapasitas Full Putaran — input numerik (step desimal, misal `0.1`).
   - **Latitude** & **Longitude** — input wajib (required), divalidasi rentang nilai koordinat geografis yang valid.
4. Submit form memicu method Livewire (`store`/`update`) yang melakukan validasi server-side, menyimpan data, lalu menutup modal dan me-*refresh* tabel tanpa reload halaman.
5. Aksi Edit membuka modal yang sama dalam mode edit (data ter-*prefill*), aksi Hapus memicu konfirmasi (SweetAlert/Alpine confirm) sebelum eksekusi.

**Acceptance Criteria:**
- [ ] Nama aset dengan karakter `"` tersimpan dan tampil tanpa error rendering.
- [ ] Latitude & Longitude bersifat wajib diisi dan tervalidasi sebagai koordinat numerik.
- [ ] Modal tambah/edit berfungsi tanpa reload halaman penuh.

---

### 4.3 Modul B: Log Gate Valve (Tampilan & Manajemen Data)

| Aspek | Detail |
|---|---|
| **Aktor** | Admin, Operator |
| **Tujuan** | Menampilkan dan mengaudit riwayat aktivitas buka/tutup Gate Valve |

**Alur Fungsional:**

1. Tabel log ditampilkan dengan kolom: Nama Aset, Jenis Aktivitas, Jumlah Putaran, Sisa Bukaan, Latitude, Longitude, Foto Bukti, Teknisi, Tanggal.
2. **Live Search:** Input pencarian diikat via `wire:model.live` (debounce disarankan 300ms) untuk memfilter tabel secara instan berdasarkan nama aset/lokasi/teknisi.
3. **Tab Filter:** Tab (Semua/Buka/Tutup) diikat ke properti Livewire, mengubah query tabel tanpa reload.
4. **Kolom Bukti Foto:** Menampilkan thumbnail; klik thumbnail memicu Alpine.js membuka **Lightbox/Modal** gambar ukuran penuh (`x-show` + overlay gelap + tombol tutup).
5. Kolom Latitude & Longitude aktual dari log (bukan dari master) ditampilkan apa adanya untuk keperluan audit posisi riil saat pencatatan.

**Acceptance Criteria:**
- [ ] Pencarian dan filter tab menghasilkan update tabel instan tanpa reload browser.
- [ ] Klik thumbnail foto membuka lightbox gambar penuh.
- [ ] Kolom koordinat log tampil sesuai data pada baris terkait.

---

### 4.4 Modul C: Log Tekanan Air (Tampilan & Manajemen Data)

| Aspek | Detail |
|---|---|
| **Aktor** | Admin, Operator |
| **Tujuan** | Menampilkan dan mengaudit riwayat pembacaan tekanan air |

**Alur Fungsional:**

1. Tabel log tekanan dengan kolom serupa Modul B, ditambah kolom Nilai Tekanan (Bar) dan Status (Normal/Rendah/Kritis).
2. Live Search dan Tab Filter (Semua/Normal/Kritis) menggunakan mekanisme `wire:model` yang identik dengan Modul B.
3. **Bar Chart Interaktif (Chart.js):** Menampilkan tren nilai tekanan, dengan **garis ambang batas minimum** berupa garis putus-putus hijau pada level 1.0 Bar (menggunakan plugin anotasi Chart.js).
4. Chart dibungkus `wire:ignore` dan diperbarui via method Chart.js saat filter/pencarian berubah, dipicu melalui event Livewire ke Alpine.
5. Kolom Bukti Foto menggunakan mekanisme lightbox yang sama seperti Modul B.

**Acceptance Criteria:**
- [ ] Bar chart menampilkan garis ambang batas 1.0 Bar secara konsisten pada setiap render.
- [ ] Filter status memperbarui isi chart dan tabel secara sinkron.

---

### 4.5 Modul D: Sistem Pelaporan Perusahaan (Export Engine)

| Aspek | Detail |
|---|---|
| **Aktor** | Admin, Operator, Manajemen |
| **Tujuan** | Menghasilkan laporan .xlsx berstandar enterprise dari data yang sedang difilter |

**Alur Fungsional:**

1. Tombol "Export ke Excel" tersedia pada halaman Log Valve dan Log Tekanan.
2. Saat diklik, Livewire mengirimkan parameter filter/search yang sedang aktif (bukan seluruh data mentah) ke backend melalui method export.
3. Backend memanggil **Laravel Excel (Maatwebsite\Excel)** dengan kelas Export khusus per modul, menyusun kolom termasuk **Latitude & Longitude**.
4. File .xlsx dirender dengan styling rapi: header tebal (bold), border sel, dan pewarnaan kondisional (misal baris status "Kritis" berwarna latar merah muda) menggunakan kapabilitas **PHP GD Extension**.
5. File diunduh langsung ke browser pengguna (`response()->download()` atau streamed download Livewire).

**Acceptance Criteria:**
- [ ] File yang diunduh hanya berisi data sesuai filter aktif saat tombol export ditekan.
- [ ] Header tabel pada file .xlsx tercetak tebal dan kolom koordinat lengkap.

---

## 5. USER INTERFACE (UI) LAYOUT GUIDELINES

### 5.1 Tata Letak Dashboard Utama (Grid System)

| Zona | Posisi | Konten |
|---|---|---|
| **Header/Topbar** | Atas, full-width | Logo, nama sistem, menu navigasi, profil pengguna |
| **Baris Kartu Metrik** | Grid 3 kolom (desktop) / 1 kolom (mobile) | Total Aset, Total Aktivitas Hari Ini, Peringatan Kritis |
| **Peta Interaktif** | Kolom besar (misal 8/12 grid), tinggi minimum 400px | Peta GIS dengan seluruh marker |
| **Panel Chart** | Kolom samping (misal 4/12 grid) | Doughnut Chart & Line Chart bertumpuk vertikal |
| **Tabel Ringkasan** | Full-width, di bawah peta | Tabel bukaan valve dengan progress bar |

Gunakan Tailwind CSS grid utility (`grid grid-cols-12 gap-4`) agar layout tetap konsisten dan mudah disesuaikan untuk breakpoint responsif (`md:`, `lg:`).

### 5.2 Panduan Integrasi Peta pada Grid Dashboard

- Kontainer peta harus memiliki tinggi tetap eksplisit (bukan `auto`) agar library pemetaan (Leaflet.js) dapat menghitung dimensi dengan benar saat inisialisasi.
- Elemen peta wajib berada dalam elemen bertanda `wire:ignore` sebagaimana dijelaskan pada bagian Functional Requirements.
- Legenda warna marker (🟢🟡🔴) ditampilkan sebagai overlay kecil di salah satu sudut peta.

### 5.3 Panduan Modal & Lightbox

- Seluruh modal (form tambah/edit, konfirmasi hapus, lightbox foto) menggunakan pola Alpine.js standar: `x-data="{ open: false }"`, transisi `x-transition`, dan overlay gelap semi-transparan (`bg-black/50`).
- Modal ditutup melalui tombol close, klik area overlay, atau tombol `Esc` (event listener `@keydown.escape.window`).
- Lightbox gambar menampilkan gambar dalam ukuran maksimal layar (`max-h-screen`, `object-contain`) dengan tombol close di pojok kanan atas.

### 5.4 Struktur & Penempatan Chart

- Setiap chart diberi judul singkat dan jelas di atasnya (misal "Tren Tekanan 7 Hari Terakhir").
- Bar Chart Log Tekanan wajib menampilkan garis ambang batas hijau putus-putus pada 1.0 Bar sebagai referensi visual cepat.
- Warna chart mengikuti konvensi status: Hijau (Normal), Kuning (Rendah), Merah (Kritis) agar konsisten dengan warna marker peta.

### 5.5 Konvensi Visual Status

| Status | Warna | Kondisi |
|---|---|---|
| Normal | 🟢 Hijau | Tekanan > 1.0 Bar / Valve beroperasi normal |
| Rendah | 🟡 Kuning | Tekanan antara 0 – 1.0 Bar |
| Kritis/Tertutup | 🔴 Merah | Tekanan = 0 Bar / Valve tertutup penuh |

---

## 6. NON-FUNCTIONAL REQUIREMENTS

### 6.1 Performa

| Aspek | Requirement |
|---|---|
| **DOM Diffing pada Peta & Chart** | Elemen yang dikelola JS pihak ketiga wajib `wire:ignore` untuk mencegah Livewire melakukan diff/replace DOM yang menyebabkan peta/chart reset saat polling |
| **Polling Interval** | Maksimal setiap 30 detik (`wire:poll.30s`) untuk menyeimbangkan kesegaran data dan beban server |
| **Live Search Debounce** | Gunakan `wire:model.live.debounce.300ms` untuk mencegah request berlebihan saat pengguna mengetik |
| **Paginasi** | Seluruh tabel data log wajib menerapkan paginasi server-side (Livewire `WithPagination`), bukan memuat seluruh data sekaligus |
| **Lazy Loading Gambar** | Thumbnail foto eviden menggunakan atribut `loading="lazy"` untuk mempercepat render tabel |

### 6.2 Keamanan & Proteksi Input

| Aspek | Requirement |
|---|---|
| **Escaping Karakter Khusus** | Seluruh input teks (termasuk nama aset dengan tanda kutip) wajib melalui Blade escaping default (`{{ }}`) untuk mencegah XSS/rendering rusak |
| **Validasi Server-Side** | Setiap form (Livewire component) menerapkan validasi eksplisit (`rules()`), termasuk tipe numerik untuk koordinat dan nilai Float |
| **CSRF Protection** | Mengikuti mekanisme bawaan Laravel/Livewire, tidak dinonaktifkan pada komponen apa pun |
| **Otorisasi Akses** | Middleware role-based (Admin/Operator/Teknisi) membatasi akses modul CRUD sesuai hak akses |
| **Validasi Upload File** | Foto eviden divalidasi tipe file (`jpg,jpeg,png`) dan ukuran maksimum (misal 2MB) sebelum disimpan |

### 6.3 Struktur Penyimpanan Media

- File `foto_eviden` disimpan pada disk `public` Laravel dengan struktur folder per modul dan periode (tahun/bulan) sebagaimana dijelaskan di Bagian 3.4.
- Nama file di-generate menggunakan UUID/hash untuk menghindari konflik nama file dan potensi eksploitasi path.
- Backend menyimpan hanya path relatif di database, bukan path absolut server, agar portabel antar-environment (staging/production).

### 6.4 Kompatibilitas & Skalabilitas

- Dashboard harus responsif dan tetap fungsional pada resolusi tablet (≥768px); dukungan penuh mobile phone menjadi prioritas Fase 2 (aplikasi mobile terpisah).
- Struktur database dirancang mendukung pertumbuhan jumlah aset dan volume log tanpa perubahan skema besar (indexing pada kolom `master_valve_id`, `created_at` untuk mempercepat query log dan chart tren).
- Kompatibel dengan browser modern (Chrome, Edge, Firefox versi 2 tahun terakhir).

---

## 7. LAMPIRAN

### 7.1 Glossary

| Istilah | Definisi |
|---|---|
| **Gate Valve (GV)** | Katup pengatur aliran air pada jaringan pipa distribusi |
| **Bukaan Valve** | Jumlah putaran valve yang menunjukkan seberapa besar katup terbuka |
| **Foto Eviden** | Foto bukti lapangan yang diambil teknisi saat mencatat aktivitas/log |
| **wire:poll** | Direktif Livewire untuk memperbarui komponen secara berkala otomatis |
| **wire:ignore** | Direktif Livewire untuk mengecualikan elemen dari proses DOM diffing |
| **wire:model** | Direktif Livewire untuk mengikat input form ke properti komponen secara reaktif |

### 7.2 Ringkasan Definisi Status Kritis

Sebuah wilayah/aset dianggap **Kritis** apabila:
- Nilai tekanan air pada titik terkait tercatat **0 Bar**, ATAU
- Status valve tercatat **Tertutup penuh** (sisa bukaan = 0) tanpa aktivitas pembukaan terbaru.

---

*Dokumen ini merupakan acuan resmi bagi tim developer dalam membangun Fase 1 sistem. Perubahan lingkup di luar dokumen ini memerlukan persetujuan ulang melalui proses Change Request.*
