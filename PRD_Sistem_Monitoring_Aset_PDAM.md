# PRODUCT REQUIREMENT DOCUMENT (PRD)
## Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM
### (Web Dashboard Analitik — Arsitektur TALL Stack)

| Informasi Dokumen | Detail |
|---|---|
| **Nama Proyek** | Sistem Informasi Manajemen Aset & Monitoring Distribusi Air PDAM |
| **Jenis Dokumen** | Product Requirement Document (PRD) |
| **Arsitektur** | TALL Stack (Tailwind CSS, Alpine.js, Livewire, Laravel) |
| **Versi Dokumen** | 1.0 |
| **Status** | Draft untuk Review Tim Pengembang |
| **Ditujukan Untuk** | Tim Backend, Tim Frontend, QA, dan Project Manager |

---

## DAFTAR ISI
1. [Project Information & Objectives](#1-project-information--objectives)
2. [System Architecture & Tech Stack](#2-system-architecture--tech-stack)
3. [Database & Relational Model Scope](#3-database--relational-model-scope)
4. [Functional Requirements (Per Modul)](#4-functional-requirements-per-modul)
5. [User Interface (UI) Layout Guidelines](#5-user-interface-ui-layout-guidelines)
6. [Non-Functional Requirements](#6-non-functional-requirements)

---

## 1. PROJECT INFORMATION & OBJECTIVES

### 1.1 Latar Belakang
Proses pemantauan aset katup distribusi air (**Gate Valve**) dan tekanan jaringan pipa PDAM saat ini masih bergantung pada pencatatan manual oleh teknisi lapangan yang kemudian direkap secara terpisah oleh staf administrasi. Pendekatan ini menimbulkan risiko keterlambatan data, human error dalam pencatatan angka putaran katup, serta ketiadaan mekanisme peringatan dini (*early warning*) terhadap potensi kebocoran pipa.

Proyek ini merupakan **refactor menyeluruh** terhadap sistem informasi internal yang sudah ada, dengan tujuan memindahkan seluruh proses pencatatan dan monitoring ke dalam satu **Web Dashboard Terpusat** yang reaktif, cepat, dan terintegrasi langsung dengan aplikasi mobile teknisi lapangan melalui RESTful API.

### 1.2 Ruang Lingkup (Scope)

| Kategori | Cakupan |
|---|---|
| **In-Scope** | Manajemen master aset valve & lokasi, pencatatan log aktivitas katup, monitoring & grafik tekanan air, export laporan Excel, integrasi API penerima data dari aplikasi mobile teknisi |
| **Out-of-Scope** | Pengembangan aplikasi mobile teknisi (dianggap sudah ada/pihak ketiga), sistem billing pelanggan, modul pengaduan pelanggan (customer complaint) |

### 1.3 Tujuan Utama (Goals)

| No | Goal | Deskripsi | Indikator Keberhasilan |
|---|---|---|---|
| 1 | **Digitalisasi Laporan** | Eliminasi pencatatan manual kertas ke basis data terpusat *real-time* | 100% input lapangan tercatat via API, 0% laporan kertas |
| 2 | **Kontrol Presisi Aset** | Melacak sisa bukaan/tutupan Gate Valve berbasis jumlah putaran mekanis | Nilai sisa bukaan valve akurat dan konsisten dengan log historis |
| 3 | **Early Warning System** | Deteksi dini kebocoran (*burst pipe*) atau drop tekanan | Notifikasi/visual status kritis muncul sebelum ada komplain pelanggan |
| 4 | **Akuntabilitas Lapangan** | Log aktivitas teknisi transparan (siapa, apa, kapan, di mana) | Setiap entri log memiliki jejak audit lengkap |
| 5 | **Enterprise Reporting** | Laporan performa operasional berformat Excel profesional | File `.xlsx` terunduh sesuai filter aktif, siap audit manajemen |

### 1.4 Stakeholders

| Peran | Tanggung Jawab |
|---|---|
| **Manajemen PDAM** | Pengguna akhir laporan Excel & dashboard analitik untuk pengambilan keputusan |
| **Staf Operasional / Admin Pusat** | Mengelola master data aset, memantau dashboard harian |
| **Teknisi Lapangan** | Sumber data via aplikasi mobile pihak ketiga (input log valve & tekanan) |
| **Tim Pengembang (Dev Team)** | Implementasi teknis sesuai PRD ini |

---

## 2. SYSTEM ARCHITECTURE & TECH STACK

### 2.1 Gambaran Umum Arsitektur
Sistem dibangun di atas filosofi **TALL Stack**, memungkinkan pengalaman *Single Page Application* (SPA) tanpa kompleksitas membangun API frontend terpisah (seperti pada arsitektur SPA berbasis Vue/React murni). Seluruh reaktivitas UI ditangani di sisi server oleh Livewire, sementara interaksi visual instan ditangani oleh Alpine.js di sisi klien.

**Alur data tingkat tinggi:**

```
[Aplikasi Mobile Teknisi (Pihak Ketiga)]
            │  (HTTP Request, JSON Payload)
            ▼
[routes/api.php — RESTful API Laravel]
            │  (Validasi, Auth Token, Sanctum/Passport)
            ▼
[Laravel Controller → Eloquent ORM]
            │
            ▼
[Database Terpusat (MySQL/PostgreSQL)]
            │
            ▼
[Livewire Component ← Query Eloquent]
            │  (wire:model, wire:poll / event broadcast)
            ▼
[Blade View + Alpine.js (UI Interaktif)]
            │
            ▼
[Dashboard Web — Manajemen & Tim Operasional]
```

### 2.2 Peran Masing-Masing Komponen

| Layer | Teknologi | Tanggung Jawab Utama |
|---|---|---|
| **Backend & Data Engine** | Laravel | Core MVC, Eloquent ORM (relasi antar tabel), keamanan routing, validasi server-side, RESTful API (`routes/api.php`) untuk aplikasi mobile teknisi |
| **Server-Driven Reactivity** | Livewire | Reaktivitas komponen UI utama: pencarian teks global, filter tab, pembaruan tabel data secara *real-time* via AJAX otomatis (**zero full-page reload**) |
| **Client-Side Interactivity** | Alpine.js | Interaksi lokal instan tanpa query database: buka/tutup modal tambah/edit aset, dropdown filter melayang, toggle state UI |
| **UI & Styling** | Tailwind CSS | Utility-first styling untuk tampilan dashboard yang bersih, modern, profesional, dengan hierarki visual jelas |
| **Visualisasi Data** | Chart.js | Rendering grafik batang analitik tekanan air di halaman web |
| **Rendering Laporan** | PHP GD Extension | Mendukung pewarnaan tabel pada dokumen laporan yang diekspor |
| **Export Engine** | Laravel Excel (Maatwebsite v3.1) | Konversi query database menjadi file `.xlsx` resmi dengan format tabel bergrid, tebal, dan auto-width kolom |

### 2.3 Prinsip Pembagian Tugas Livewire vs Alpine.js

> **Aturan emas arsitektur:** Jika aksi memerlukan data dari database atau harus konsisten di seluruh sesi (search, filter, submit form), gunakan **Livewire**. Jika aksi hanya bersifat visual sementara di browser dan tidak memerlukan permintaan ke server (toggle modal, dropdown, animasi), gunakan **Alpine.js**.

| Aksi | Ditangani Oleh | Alasan |
|---|---|---|
| Pencarian teks global pada tabel | Livewire (`wire:model.live`) | Membutuhkan query ke database |
| Filter status (Pills Button Group) | Livewire | Mengubah hasil query yang ditampilkan |
| Buka/tutup Modal Tambah/Edit Aset | Alpine.js (`x-data`, `x-show`) | Murni state UI, tidak butuh roundtrip server |
| Mengisi ulang data form Edit | Alpine.js (via objek JSON) dipicu event dari Livewire | Data di-passing sebagai JSON lalu di-bind ke `x-model` |
| Render grafik Chart.js | Alpine.js/Vanilla JS dibungkus `wire:ignore` | Mencegah Livewire merender ulang canvas saat DOM diffing |
| Submit form Tambah/Edit Aset | Livewire (`wire:submit`) | Validasi & penyimpanan ke database |

### 2.4 Library & Tools Pendukung

| Tools | Fungsi |
|---|---|
| `livewire/livewire` | Komponen reaktif server-driven |
| `maatwebsite/excel` (v3.1) | Export laporan `.xlsx` |
| `chart.js` (CDN/NPM) | Grafik batang tekanan air |
| PHP `ext-gd` | Rendering warna sel pada dokumen export |
| Laravel Sanctum / Passport | Autentikasi token untuk RESTful API aplikasi mobile |

---

## 3. DATABASE & RELATIONAL MODEL SCOPE

### 3.1 Entity Relationship Diagram (Deskriptif)

```
Lokasi (1) ────────< (N) AsetValve
AsetValve (1) ──────< (N) LogValve
Lokasi (1) ──────────< (N) LogTekanan
```

- Satu **Lokasi** memiliki banyak **AsetValve**.
- Satu **AsetValve** memiliki banyak riwayat **LogValve**.
- Satu **Lokasi** memiliki banyak riwayat **LogTekanan** (pengecekan tekanan per wilayah distribusi).

### 3.2 Detail Struktur Tabel

#### Tabel: `lokasis`
| Field | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK, Auto Increment | Primary Key |
| `nama_lokasi` | VARCHAR(150), Unique | Nama daerah/wilayah distribusi |
| `created_at`, `updated_at` | TIMESTAMP | Standar Eloquent |

> **Catatan Logika:** Field ini diisi otomatis via `firstOrCreate(['nama_lokasi' => $input])` ketika admin menambah aset baru dengan nama lokasi yang belum terdaftar — tidak ada input manual terpisah untuk tabel ini.

#### Tabel: `aset_valves`
| Field | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | Primary Key |
| `lokasi_id` | BIGINT, FK → `lokasis.id` | Relasi lokasi |
| `nama_aset` | VARCHAR(150) | Contoh: `GV 6"`. **Wajib** mendukung karakter tanda kutip dua (`"`) dengan aman — di-encode sebagai string JSON valid di sisi frontend sebelum dikirim via `wire:model`/request |
| `kapasitas_full_putaran` | **DECIMAL(8,2)** | Kapasitas total putaran valve (mis. `58.50`) — **wajib desimal**, bukan integer |
| `total_tutupan_saat_ini` | DECIMAL(8,2), default `0.00` | Akumulasi hasil perhitungan dari `LogValve` terakhir |
| `created_at`, `updated_at` | TIMESTAMP | Standar Eloquent |

#### Tabel: `log_valves`
| Field | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | Primary Key |
| `aset_valve_id` | BIGINT, FK → `aset_valves.id` | Relasi aset |
| `nama_teknisi` | VARCHAR(150) | Diterima dari payload API mobile |
| `waktu_kegiatan` | DATETIME | Timestamp aktivitas di lapangan (bukan `created_at` sistem) |
| `aksi_kerja` | ENUM(`'buka'`, `'tutup'`) | Jenis aksi mekanis |
| `jumlah_putaran` | **DECIMAL(6,2)** | Jumlah putaran katup pada aksi ini — desimal wajib |
| `keterangan` | TEXT, nullable | Catatan lapangan bebas |
| `snapshot_sisa_bukaan` | DECIMAL(8,2) | Nilai hasil kalkulasi otomatis pasca-kegiatan (disimpan sebagai histori, tidak dihitung ulang) |
| `snapshot_total_tutupan` | DECIMAL(8,2) | Nilai hasil kalkulasi otomatis pasca-kegiatan |
| `created_at`, `updated_at` | TIMESTAMP | Standar Eloquent |

#### Tabel: `log_tekanans`
| Field | Tipe Data | Keterangan |
|---|---|---|
| `id` | BIGINT, PK | Primary Key |
| `lokasi_id` | BIGINT, FK → `lokasis.id` | Relasi wilayah distribusi |
| `nilai_tekanan` | **DECIMAL(5,2)** | Satuan Bar, contoh `0.85` — wajib desimal untuk presisi ambang batas |
| `status` | ENUM(`'normal'`, `'rendah'`, `'kritis'`) | Dihitung otomatis via *accessor*/observer berdasarkan `nilai_tekanan` |
| `waktu_pengecekan` | DATETIME | Waktu pengecekan aktual di lapangan |
| `created_at`, `updated_at` | TIMESTAMP | Standar Eloquent |

### 3.3 Catatan Khusus Penanganan Tipe Data Float/Decimal
- Seluruh kolom numerik yang merepresentasikan **putaran katup** dan **tekanan air** **wajib** menggunakan tipe `DECIMAL`, bukan `FLOAT`, untuk menghindari *floating point rounding error* saat kalkulasi akumulatif (mis. `58.5 - 12.25 = 46.25`, bukan `46.24999999`).
- Validasi Laravel wajib menggunakan rule `numeric` dan `regex:/^\d+(\.\d{1,2})?$/` untuk membatasi input maksimal 2 digit desimal.
- Perhitungan **sisa bukaan** = `kapasitas_full_putaran − total_tutupan_saat_ini`, dilakukan di *Model Accessor* (`getSisaBukaanAttribute()`) agar konsisten di seluruh sistem (bukan dihardcode di Blade/Livewire).

---

## 4. FUNCTIONAL REQUIREMENTS (PER MODUL)

### 4.1 Modul A — Manajemen Master Aset (Gate Valve)

**User Story:** Sebagai admin operasional, saya ingin mengelola data master valve dan lokasi agar data acuan sistem selalu akurat.

**Input Form (Modal Tambah/Edit):**

| Field | Tipe UI | Wajib | Catatan |
|---|---|---|---|
| Nama Aset | Text Input | Ya | Mendukung karakter `"` dengan aman (di-escape via JSON.stringify di Alpine sebelum dikirim) |
| Nama Lokasi | Text Input dengan autocomplete/datalist | Ya | `firstOrCreate` otomatis jika lokasi baru |
| Kapasitas Full Putaran | Number Input (step 0.01) | Ya | Decimal, contoh `58.5` |

**Logika TALL Stack:**
| Aksi | Teknologi |
|---|---|
| Tombol "+ Tambah Aset" membuka modal kosong | Alpine.js (`x-data="{open:false}"`) |
| Tombol "Edit" pada baris tabel membuka modal terisi data lama | Alpine.js — data di-passing dari Livewire sebagai objek JSON (`x-data="{form: @js($aset)}"`), lalu di-*bind* ke `x-model` |
| Submit form (create/update) | Livewire (`wire:submit.prevent`) → validasi server-side → simpan via Eloquent |
| Refresh tabel setelah submit | Livewire otomatis re-render komponen (tanpa reload) |

**Output UI:**
- Tabel daftar aset dengan kolom: Nama Aset, Lokasi, Kapasitas Full, Total Tutupan, **Progress Bar** visual (persentase sisa bukaan = `sisa_bukaan / kapasitas_full × 100%`), Aksi (Edit/Hapus).
- Progress bar menggunakan gradasi warna Tailwind (hijau → kuning → merah) berdasarkan persentase sisa bukaan.

---

### 4.2 Modul B — Riwayat Aktivitas Katup (Log Gate Valve)

**User Story:** Sebagai staf operasional, saya ingin memantau riwayat aktivitas buka/tutup valve secara real-time untuk keperluan akuntabilitas.

**Sumber Data:** Diterima dari aplikasi mobile teknisi melalui `POST /api/log-valve` dengan payload: `aset_id`, `nama_teknisi`, `waktu_kegiatan`, `aksi_kerja`, `jumlah_putaran`, `keterangan`.

**Interaksi UI:**

| Fitur | Teknologi | Detail |
|---|---|---|
| Search global (nama teknisi/aset/lokasi dalam 1 kolom) | Livewire (`wire:model.live.debounce.300ms`) | Query `orWhere` lintas relasi |
| Filter cepat (Semua/Buka/Tutup) | Livewire — Pills Button Group | `wire:click` mengubah properti `$filterAksi` |

**Output UI:**
- **4 Kartu Statistik:** Total Log, Jumlah Aksi Buka, Jumlah Aksi Tutup, Rata-rata Putaran Kerja (dihitung real-time sesuai filter aktif).
- Tabel riwayat menampilkan kolom snapshot **Sisa Bukaan** dan **Total Tutupan** pasca kegiatan (nilai historis yang tersimpan, bukan hasil kalkulasi ulang saat ditampilkan — demi integritas data audit).

**Business Logic Backend (saat menerima data dari API):**
1. Validasi `aset_id` harus ada (exists rule).
2. Update `total_tutupan_saat_ini` pada `AsetValve` terkait (`+` jika aksi tutup, `−` jika aksi buka, dibatasi tidak boleh negatif atau melebihi kapasitas full — gunakan `clamp`).
3. Simpan snapshot hasil kalkulasi ke kolom `snapshot_sisa_bukaan` dan `snapshot_total_tutupan` pada baris log yang baru dibuat.
4. Seluruh proses dibungkus `DB::transaction()` untuk menjaga konsistensi data antara tabel `aset_valves` dan `log_valves`.

---

### 4.3 Modul C — Riwayat & Grafik Tekanan Air (Log Tekanan Air)

**User Story:** Sebagai manajemen, saya ingin melihat status tekanan air per wilayah secara visual agar dapat mendeteksi potensi kebocoran sedini mungkin.

**Klasifikasi Status Tekanan:**

| Status | Kondisi | Warna Indikator |
|---|---|---|
| 🟢 **Normal** | Tekanan ≥ 1.0 Bar | Hijau (`bg-green-500`) |
| 🟡 **Rendah** | 0.5 Bar ≤ Tekanan < 1.0 Bar | Kuning (`bg-yellow-500`) |
| 🔴 **Kritis** | Tekanan = 0 Bar (drop total) | Merah (`bg-red-500`) |

> Logika klasifikasi diimplementasikan sebagai *Model Accessor* atau *Observer* pada model `LogTekanan` agar konsisten digunakan baik oleh tabel, kartu statistik, maupun grafik.

**Grafik (Chart.js):**

| Spesifikasi | Detail |
|---|---|
| Jenis Grafik | Bar Chart — "Tekanan Terkini per Daerah" |
| Warna Batang | Dinamis mengikuti status (Hijau/Kuning/Merah) per data point |
| Threshold Line | Garis putus-putus (dashed) berwarna hijau tepat di `y = 1.0 Bar`, menggunakan plugin `chartjs-plugin-annotation` atau dataset tipe `line` overlay |
| Isolasi dari Livewire | Elemen `<canvas>` **wajib** dibungkus `wire:ignore` agar tidak ikut ter-*diffing*/re-render oleh Livewire saat filter tabel di bawahnya berubah — mencegah grafik "patah" atau ter-reset |
| Update data grafik | Menggunakan Livewire JS hook (`Livewire.hook` / `wire:key` + event listener `livewire:update`) untuk memanggil `chart.update()` secara manual tanpa merender ulang elemen canvas |

**Interaksi UI Tabel:**
| Fitur | Teknologi |
|---|---|
| Pills Button Group (Semua/Normal/Rendah/Kritis) | Livewire |
| Search teks | Livewire (`wire:model.live`), digabung dengan filter status pada query yang sama |

**Output UI:**
- **4 Kartu Statistik:** Total Pengecekan, Rata-rata Tekanan (Bar), Kondisi Normal, Peringatan Kritis.
- Tabel log detail dengan kolom status berwarna (badge).

---

### 4.4 Modul D — Sistem Pelaporan Perusahaan (Export Engine)

**User Story:** Sebagai manajemen, saya ingin mengunduh laporan performa operasional dalam format Excel profesional sesuai filter yang sedang aktif di dashboard.

**Alur Proses:**

```
[User klik tombol "Export Excel" di halaman Log Valve/Log Tekanan]
            │
            ▼
[Livewire menangkap parameter aktif: $search, $filterStatus/$filterAksi]
            │
            ▼
[Redirect ke route backend: GET /export/log-valve?search=...&filter=...]
            │
            ▼
[Controller memanggil Excel::download(new LogValveExport($search, $filter), 'nama_file.xlsx')]
            │
            ▼
[Class LogValveExport menerapkan query filter yang sama persis dengan tampilan tabel di layar]
            │
            ▼
[File .xlsx terunduh ke browser user]
```

**Ketentuan Teknis Export Class:**

| Requirement | Detail |
|---|---|
| Konsistensi Filter | Parameter `search` dan `status/aksi filter` yang aktif di UI **wajib** diteruskan sebagai constructor argument ke class `LogValveExport` / `LogTekananExport`, memastikan isi laporan = isi tabel yang sedang dilihat user |
| Header Laporan | Judul laporan center-aligned, mencantumkan metadata: nama laporan, rentang filter aktif, dan waktu cetak (`now()->format('d F Y H:i')`) |
| Style Header Tabel | Background gelap (Tailwind/Hex `#0F172A` — setara `Slate-900`), teks putih, bold — diimplementasikan via `WithStyles` interface dari Maatwebsite Excel |
| Format Kolom Angka | Kolom numerik (putaran, tekanan) rata kanan/tengah otomatis via `WithColumnFormatting` |
| Border | Seluruh sel data dikelilingi *thin border* abu-abu, menggunakan `PHPExcel/PhpSpreadsheet Style Border` |
| Lebar Kolom | Auto-width via `ShouldAutoSize` interface |
| Ekstensi GD | Digunakan untuk memastikan rendering warna sel konsisten saat proses generate file di server |

---

## 5. USER INTERFACE (UI) LAYOUT GUIDELINES

### 5.1 Struktur Layout Umum

```
┌─────────────────────────────────────────────┐
│  Topbar (Logo, Nama User, Notifikasi)        │
├───────────┬─────────────────────────────────┤
│           │  Breadcrumb / Judul Halaman      │
│  Sidebar  ├─────────────────────────────────┤
│  Navigasi │  4 Kartu Statistik (Grid 4 kolom)│
│  (Master  ├─────────────────────────────────┤
│  Aset,    │  Grafik (jika ada)               │
│  Log      ├─────────────────────────────────┤
│  Valve,   │  Search Bar + Pills Filter       │
│  Log      ├─────────────────────────────────┤
│  Tekanan) │  Tabel Data + Pagination         │
└───────────┴─────────────────────────────────┘
```

### 5.2 Skema Warna Status (Konsisten di Seluruh Modul)

| Status | Tailwind Class Referensi |
|---|---|
| Normal / Sukses | `text-green-600`, `bg-green-100`, `border-green-500` |
| Rendah / Peringatan | `text-yellow-600`, `bg-yellow-100`, `border-yellow-500` |
| Kritis / Bahaya | `text-red-600`, `bg-red-100`, `border-red-500` |
| Netral / Info | `text-slate-600`, `bg-slate-100` |

### 5.3 Struktur Komponen Kartu Metrik (Stat Card)
Setiap kartu statistik mengikuti struktur seragam: **Ikon** (kiri, dengan background lingkaran warna soft) → **Label** (teks kecil, abu-abu) → **Nilai Utama** (angka besar, bold). Grid menggunakan `grid grid-cols-1 md:grid-cols-4 gap-4` agar responsif pada layar mobile (stack vertikal) dan desktop (4 kolom sejajar).

### 5.4 Panduan Reaktivitas Grafik
- Elemen canvas Chart.js **wajib** berada dalam wrapper `wire:ignore` di level parent-nya.
- Inisialisasi chart dilakukan sekali melalui `document.addEventListener('livewire:navigated', ...)` atau `Alpine.js x-init`, bukan setiap kali Livewire re-render.
- Update data grafik (tanpa re-inisialisasi canvas) dilakukan via `Livewire.on('tekananUpdated', (data) => { chart.data = data; chart.update(); })`.

### 5.5 Panduan Modal & Popup (Alpine.js)
- Semua modal menggunakan pola standar: `x-data="{open:false}"`, `x-show="open"`, `x-transition` untuk animasi fade/scale halus.
- Modal ditutup otomatis via `@click.away` dan tombol `Escape` (`@keydown.escape.window`).
- Setelah submit sukses via Livewire, modal ditutup otomatis melalui event dispatch dari backend: `$this->dispatch('closeModal')` ditangkap oleh `x-on:closeModal.window="open=false"`.

---

## 6. NON-FUNCTIONAL REQUIREMENTS

| Aspek | Requirement |
|---|---|
| **Skalabilitas API** | Endpoint `routes/api.php` harus mampu menangani beban tinggi dari banyak perangkat mobile teknisi secara bersamaan; gunakan *queue* (Laravel Queue + Redis) untuk proses berat seperti kalkulasi ulang snapshot agar tidak memblokir response API |
| **Performa DOM Diffing Livewire** | Batasi jumlah baris tabel per halaman (pagination maksimal 15–25 baris) untuk menjaga payload AJAX Livewire tetap ringan; gunakan `wire:key` unik pada setiap baris `@foreach` untuk optimasi diffing DOM |
| **Keamanan — CSRF** | Seluruh form Livewire otomatis terlindungi token CSRF bawaan Laravel; pastikan middleware `VerifyCsrfToken` aktif |
| **Keamanan — SQL Injection** | Seluruh query wajib menggunakan Eloquent ORM / Query Builder dengan parameter binding, dilarang menggunakan raw query dengan concatenation string input user |
| **Keamanan — API Mobile** | Endpoint API wajib dilindungi autentikasi token (Laravel Sanctum), rate limiting (`throttle` middleware) untuk mencegah abuse |
| **Validasi Server-Side** | Setiap input, baik dari form web (Livewire) maupun payload API mobile, wajib melalui `Form Request` atau `$this->validate()` sebelum disimpan |
| **Exception Handling** | Jika `AsetValve` terkait sebuah `LogValve` telah dihapus (relasi terputus), sistem wajib menampilkan fallback aman (mis. label "Aset Tidak Ditemukan") pada tabel riwayat, bukan menyebabkan *fatal error* — gunakan *nullable relationship* (`optional($log->asetValve)->nama_aset`) atau *soft delete* pada tabel `aset_valves` agar histori data tidak hilang |
| **Konsistensi Data** | Operasi yang melibatkan update lebih dari satu tabel (mis. insert `LogValve` + update `AsetValve`) wajib dibungkus `DB::transaction()` untuk mencegah data tidak konsisten akibat kegagalan parsial |
| **Audit Trail** | Seluruh tabel utama menggunakan `created_at`/`updated_at`, dan disarankan menambahkan package audit log (mis. `spatie/laravel-activitylog`) untuk mencatat perubahan data master aset |
| **Kompatibilitas Browser** | Dashboard wajib teruji pada browser modern (Chrome, Edge, Firefox versi terbaru) mengingat ketergantungan pada Livewire/Alpine.js untuk reaktivitas |

---

## LAMPIRAN — GLOSARIUM ISTILAH

| Istilah | Definisi |
|---|---|
| **Gate Valve (GV)** | Katup jaringan pipa air yang dapat dibuka/tutup secara mekanis melalui putaran |
| **Sisa Bukaan** | Selisih antara kapasitas full putaran dengan total tutupan saat ini pada sebuah valve |
| **Burst Pipe** | Kondisi pipa pecah/bocor yang menyebabkan drop tekanan mendadak |
| **wire:ignore** | Direktif Livewire untuk mengecualikan elemen DOM tertentu dari proses re-render otomatis |
| **firstOrCreate** | Method Eloquent untuk mencari data yang sudah ada, atau membuat baru jika belum ditemukan |

---

*Dokumen ini bersifat living document dan dapat direvisi seiring proses diskusi teknis lebih lanjut antara Product Manager dan Tim Pengembang.*
