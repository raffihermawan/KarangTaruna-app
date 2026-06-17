# CLAUDE.md — Product Requirements Document (PRD)
# Sistem Aplikasi Keuangan Karang Taruna 001 Lebak Bulus

---

## 1. IDENTITAS PROYEK

| Atribut | Detail |
|---|---|
| **Nama Sistem** | Sistem Aplikasi Keuangan Organisasi Karang Taruna Berbasis Web |
| **Judul Skripsi** | Rancang Bangun Sistem Aplikasi Keuangan Organisasi Karang Taruna Berbasis Web dengan Visualisasi Data Real-Time untuk Transparansi dan Akuntabilitas Menggunakan Metode Extreme Programming (XP) pada Karang Taruna 001 Lebak Bulus |
| **Organisasi** | Karang Taruna 001, Kelurahan Lebak Bulus, Kecamatan Cilandak, Kota Jakarta Selatan |
| **Metode Pengembangan** | Extreme Programming (XP) |
| **Tech Stack** | PHP Native + MySQL |
| **Tipe Aplikasi** | Web Application (Responsive) |

---

## 2. LATAR BELAKANG & PERMASALAHAN

### 2.1 Konteks Organisasi
Karang Taruna 001 Lebak Bulus adalah organisasi sosial kepemudaan yang mencakup kegiatan di 4 divisi utama:
- Sosial & Pendidikan
- Ekonomi Kreatif
- Keagamaan
- Pemuda & Olahraga

### 2.2 Masalah yang Ditemukan
1. **Tidak ada transparansi** — Dana organisasi (termasuk dana stimulan pemerintah) dikelola melalui rekening operasional yang hanya bisa diakses oleh satu pengurus
2. **Pencatatan manual** — Semua riwayat transaksi hanya dari rekening bank (hanya tampil penarikan/penyetoran tanpa keterangan detail)
3. **Tidak ada detail penggunaan dana** — Anggota tidak tahu kegiatan apa yang dibiayai, siapa yang bertanggung jawab, dan berapa sisa saldo
4. **Risiko kehilangan data** — Pencatatan fisik (buku kas) berisiko hilang, rusak, atau tidak terdokumentasi dengan baik
5. **Pengawasan internal tidak optimal** — Tidak ada sistem digital yang terstruktur untuk pengawasan keuangan
6. **Potensi kesalahpahaman antar anggota** — Ketidakjelasan informasi keuangan mengurangi kepercayaan anggota

### 2.3 Tujuan Sistem
Membangun sistem aplikasi keuangan berbasis web yang mampu:
- Mencatat semua transaksi (pemasukan & pengeluaran) secara detail, terstruktur, dan sistematis
- Menampilkan visualisasi data keuangan real-time melalui dashboard interaktif
- Meningkatkan transparansi dan akuntabilitas pengelolaan dana organisasi
- Menyediakan bukti digital setiap transaksi sebagai bentuk pertanggungjawaban
- Menghasilkan laporan keuangan yang dapat diekspor dalam format PDF/Excel

---

## 3. USER ROLES & PERMISSIONS

Sistem memiliki 3 role dengan hak akses berbeda:

### 3.1 Bendahara (Admin)
> Role utama dengan full access ke seluruh sistem

| Fitur | Akses |
|---|---|
| Dashboard & visualisasi | ✅ Baca |
| Input transaksi pemasukan | ✅ Full CRUD |
| Input transaksi pengeluaran | ✅ Full CRUD |
| Upload bukti transaksi | ✅ Full CRUD |
| Kelola kategori transaksi | ✅ Full CRUD |
| Kelola data kegiatan | ✅ Full CRUD |
| Kelola data anggota | ✅ Full CRUD |
| Kelola user & role | ✅ Full CRUD |
| Lihat laporan keuangan | ✅ Baca |
| Export laporan PDF/Excel | ✅ Ya |
| Setting sistem | ✅ Full akses |

### 3.2 Ketua
> Role monitoring & oversight — hanya baca dan export

| Fitur | Akses |
|---|---|
| Dashboard & visualisasi | ✅ Baca |
| Lihat semua transaksi | ✅ Baca |
| Lihat detail transaksi & bukti | ✅ Baca |
| Lihat laporan keuangan | ✅ Baca |
| Export laporan PDF/Excel | ✅ Ya |
| Input/edit transaksi | ❌ Tidak |
| Kelola user & setting | ❌ Tidak |

### 3.3 Anggota
> Role transparansi — hanya bisa melihat kondisi keuangan secara umum

| Fitur | Akses |
|---|---|
| Dashboard ringkasan keuangan | ✅ Baca (read-only) |
| Lihat daftar transaksi | ✅ Baca (read-only) |
| Lihat laporan publik | ✅ Baca (read-only) |
| Input/edit data apapun | ❌ Tidak |
| Export laporan | ❌ Tidak |
| Lihat detail user lain | ❌ Tidak |

---

## 4. FITUR & MODUL SISTEM

### 4.1 Modul Autentikasi
- Login dengan username & password
- Session management (auto logout setelah idle)
- Halaman login dengan validasi input
- Proteksi halaman berdasarkan role
- Logout

### 4.2 Modul Dashboard (Real-Time)
- **Kartu Statistik Utama:**
  - Saldo saat ini (real-time)
  - Total pemasukan (bulan berjalan)
  - Total pengeluaran (bulan berjalan)
  - Jumlah kegiatan aktif
- **Grafik Arus Kas Bulanan** — Bar chart pemasukan vs pengeluaran per bulan (1 tahun)
- **Grafik Distribusi Pengeluaran per Divisi** — Donut/pie chart berdasarkan kategori
- **Daftar 5 Transaksi Terbaru** — Ringkasan cepat aktivitas keuangan terkini
- **Indikator real-time** — Timestamp pembaruan data terkini

### 4.3 Modul Transaksi
- **Input Pemasukan:**
  - Jumlah dana
  - Tanggal transaksi
  - Kategori/sumber dana (iuran anggota, dana stimulan, donasi, dll)
  - Kegiatan terkait (opsional)
  - Keterangan/deskripsi detail
  - Nama penanggung jawab (PIC)
  - Upload bukti (foto/scan)
- **Input Pengeluaran:**
  - Jumlah dana
  - Tanggal transaksi
  - Kategori pengeluaran (berdasarkan divisi)
  - Kegiatan terkait
  - Keterangan/deskripsi detail
  - Nama penanggung jawab (PIC)
  - Upload bukti (struk/nota/kwitansi)
- **Daftar Transaksi:**
  - Filter berdasarkan: jenis (pemasukan/pengeluaran), kategori, periode tanggal, kegiatan
  - Search berdasarkan keterangan
  - Pagination
  - Lihat detail transaksi lengkap + bukti digital
- **Edit & Hapus Transaksi** — Hanya Bendahara (Admin)

### 4.4 Modul Kegiatan
- Input data kegiatan (nama, divisi, tanggal, deskripsi, anggaran)
- Mapping transaksi ke kegiatan
- Status kegiatan: Aktif / Selesai / Dibatalkan
- Rekapitulasi keuangan per kegiatan (total pemasukan & pengeluaran per kegiatan)
- Daftar dan detail kegiatan

### 4.5 Modul Laporan Keuangan
- **Laporan Arus Kas** — Ringkasan pemasukan & pengeluaran per periode
- **Laporan per Kegiatan** — Rincian keuangan berdasarkan kegiatan
- **Laporan per Divisi** — Rekapitulasi berdasarkan divisi organisasi
- **Laporan per Kategori** — Breakdown berdasarkan kategori transaksi
- Filter periode: harian, mingguan, bulanan, tahunan, custom range
- **Export ke PDF** — Format laporan resmi siap cetak
- **Export ke Excel (.xlsx)** — Data mentah untuk analisis lebih lanjut

### 4.6 Modul Manajemen Anggota
- Data anggota: nama, NIK, jabatan, divisi, no. HP, email, foto
- Status anggota: Aktif / Tidak Aktif
- Tambah, edit, hapus anggota
- Pencarian dan filter anggota

### 4.7 Modul Manajemen User
- Daftar user yang bisa login ke sistem
- Tambah user baru (assign ke role: Bendahara, Ketua, Anggota)
- Edit & nonaktifkan user
- Reset password user
- Hanya Bendahara (Admin) yang bisa mengakses modul ini

### 4.8 Modul Pengaturan Sistem
- Profil organisasi (nama, alamat, logo)
- Kelola kategori transaksi (tambah/edit/hapus)
- Periode tahun fiskal aktif
- Informasi rekening bank organisasi

---

## 5. DATABASE SCHEMA

### 5.1 Tabel: `users`
```sql
CREATE TABLE users (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    nama        VARCHAR(100) NOT NULL,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,            -- bcrypt hash
    role        ENUM('bendahara','ketua','anggota') NOT NULL,
    foto        VARCHAR(255) DEFAULT NULL,
    status      ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 5.2 Tabel: `anggota`
```sql
CREATE TABLE anggota (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    nik         VARCHAR(20) DEFAULT NULL,
    nama        VARCHAR(100) NOT NULL,
    jabatan     VARCHAR(100) DEFAULT NULL,
    divisi      ENUM('sosial_pendidikan','ekonomi_kreatif','keagamaan','pemuda_olahraga','umum') DEFAULT 'umum',
    no_hp       VARCHAR(20) DEFAULT NULL,
    email       VARCHAR(100) DEFAULT NULL,
    alamat      TEXT DEFAULT NULL,
    foto        VARCHAR(255) DEFAULT NULL,
    status      ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### 5.3 Tabel: `kategori_transaksi`
```sql
CREATE TABLE kategori_transaksi (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    nama        VARCHAR(100) NOT NULL,
    jenis       ENUM('pemasukan','pengeluaran') NOT NULL,
    divisi      ENUM('sosial_pendidikan','ekonomi_kreatif','keagamaan','pemuda_olahraga','umum') DEFAULT 'umum',
    deskripsi   TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### 5.4 Tabel: `kegiatan`
```sql
CREATE TABLE kegiatan (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    nama            VARCHAR(150) NOT NULL,
    divisi          ENUM('sosial_pendidikan','ekonomi_kreatif','keagamaan','pemuda_olahraga','umum') NOT NULL,
    tanggal_mulai   DATE NOT NULL,
    tanggal_selesai DATE DEFAULT NULL,
    anggaran        DECIMAL(15,2) DEFAULT 0.00,
    deskripsi       TEXT DEFAULT NULL,
    status          ENUM('aktif','selesai','dibatalkan') DEFAULT 'aktif',
    created_by      INT NOT NULL,                -- FK ke users.id
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### 5.5 Tabel: `transaksi`
```sql
CREATE TABLE transaksi (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    jenis           ENUM('pemasukan','pengeluaran') NOT NULL,
    jumlah          DECIMAL(15,2) NOT NULL,
    tanggal         DATE NOT NULL,
    kategori_id     INT NOT NULL,                -- FK ke kategori_transaksi.id
    kegiatan_id     INT DEFAULT NULL,            -- FK ke kegiatan.id (opsional)
    keterangan      TEXT NOT NULL,
    pic             VARCHAR(100) NOT NULL,        -- Nama penanggung jawab
    created_by      INT NOT NULL,                -- FK ke users.id
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id)  REFERENCES kategori_transaksi(id),
    FOREIGN KEY (kegiatan_id)  REFERENCES kegiatan(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)   REFERENCES users(id)
);
```

### 5.6 Tabel: `bukti_transaksi`
```sql
CREATE TABLE bukti_transaksi (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    transaksi_id    INT NOT NULL,                -- FK ke transaksi.id
    nama_file       VARCHAR(255) NOT NULL,        -- Nama file asli
    path_file       VARCHAR(255) NOT NULL,        -- Path file di server
    tipe_file       VARCHAR(50) NOT NULL,         -- image/jpeg, image/png, application/pdf
    ukuran_file     INT NOT NULL,                 -- Ukuran dalam bytes
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE
);
```

### 5.7 Tabel: `saldo`
```sql
CREATE TABLE saldo (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    saldo       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
-- Hanya ada 1 baris, diupdate setiap ada transaksi
```

### 5.8 Tabel: `pengaturan`
```sql
CREATE TABLE pengaturan (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    nama_organisasi VARCHAR(150) DEFAULT 'Karang Taruna 001 Lebak Bulus',
    alamat          TEXT DEFAULT NULL,
    logo            VARCHAR(255) DEFAULT NULL,
    no_rekening     VARCHAR(50) DEFAULT NULL,
    nama_bank       VARCHAR(100) DEFAULT NULL,
    tahun_aktif     YEAR DEFAULT NULL,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

---

## 6. STRUKTUR FOLDER PROJECT (PHP NATIVE)

```
karang-taruna-keuangan/
│
├── index.php                          # Entry point — redirect ke dashboard/login
├── .htaccess                          # URL rewriting & keamanan
│
├── config/
│   ├── database.php                   # Konfigurasi koneksi MySQL (PDO)
│   ├── config.php                     # Konstanta global (BASE_URL, APP_NAME, dll)
│   └── session.php                    # Inisialisasi & pengecekan session
│
├── assets/
│   ├── css/
│   │   ├── style.css                  # CSS utama (custom + responsive)
│   │   └── print.css                  # CSS khusus cetak/PDF
│   ├── js/
│   │   ├── main.js                    # JS utama (inisialisasi, event global)
│   │   ├── chart.js                   # Logika Chart.js (dashboard)
│   │   └── validation.js              # Validasi form client-side
│   ├── img/
│   │   └── logo.png                   # Logo organisasi
│   └── fonts/
│       └── poppins/                   # Font Poppins (local, atau via Google CDN)
│
├── uploads/
│   └── bukti/                         # Folder upload bukti transaksi
│       └── .htaccess                  # Cegah eksekusi PHP di folder upload
│
├── includes/
│   ├── header.php                     # HTML head + navbar atas
│   ├── sidebar.php                    # Sidebar navigasi
│   ├── footer.php                     # Footer + script JS
│   └── alert.php                      # Komponen notifikasi/flash message
│
├── auth/
│   ├── login.php                      # Halaman & proses login
│   └── logout.php                     # Proses logout
│
├── pages/
│   ├── dashboard/
│   │   └── index.php                  # Halaman dashboard utama
│   │
│   ├── transaksi/
│   │   ├── index.php                  # Daftar semua transaksi
│   │   ├── tambah.php                 # Form tambah transaksi
│   │   ├── edit.php                   # Form edit transaksi
│   │   ├── detail.php                 # Detail transaksi + bukti
│   │   └── hapus.php                  # Proses hapus transaksi
│   │
│   ├── kegiatan/
│   │   ├── index.php                  # Daftar kegiatan
│   │   ├── tambah.php                 # Form tambah kegiatan
│   │   ├── edit.php                   # Form edit kegiatan
│   │   ├── detail.php                 # Detail kegiatan + transaksi terkait
│   │   └── hapus.php                  # Proses hapus kegiatan
│   │
│   ├── laporan/
│   │   ├── index.php                  # Halaman laporan keuangan
│   │   ├── export-pdf.php             # Generate & export laporan PDF
│   │   └── export-excel.php           # Generate & export laporan Excel
│   │
│   ├── anggota/
│   │   ├── index.php                  # Daftar anggota
│   │   ├── tambah.php                 # Form tambah anggota
│   │   ├── edit.php                   # Form edit anggota
│   │   └── hapus.php                  # Proses hapus anggota
│   │
│   ├── user/
│   │   ├── index.php                  # Daftar user sistem
│   │   ├── tambah.php                 # Form tambah user
│   │   ├── edit.php                   # Form edit user
│   │   └── hapus.php                  # Proses hapus/nonaktifkan user
│   │
│   └── pengaturan/
│       └── index.php                  # Pengaturan sistem & profil organisasi
│
├── api/
│   ├── dashboard-stats.php            # Endpoint JSON data statistik dashboard
│   ├── chart-arus-kas.php             # Endpoint JSON data grafik arus kas
│   └── chart-kategori.php             # Endpoint JSON data grafik distribusi
│
├── helpers/
│   ├── auth-helper.php                # Fungsi cek role & autentikasi
│   ├── format-helper.php              # Fungsi format rupiah, tanggal, dll
│   ├── upload-helper.php              # Fungsi upload file & validasi
│   └── query-helper.php              # Fungsi query database yang sering dipakai
│
└── vendor/                            # Library pihak ketiga
    ├── tcpdf/                         # Library export PDF
    └── phpspreadsheet/                # Library export Excel
```

---

## 7. DESAIN UI/UX

### 7.1 Prinsip Desain
- **Responsive** — Tampil optimal di desktop, tablet, dan mobile
- **Typography** — Font Poppins (weight: 300, 400, 500, 600, 700)
- **Estetik & profesional** — Clean, modern, dengan hierarki tipografi yang jelas

### 7.2 Palet Warna
| Nama | Hex | Penggunaan |
|---|---|---|
| Navy Primary | `#1a3a5c` | Sidebar, heading, elemen utama |
| Amber Accent | `#e8a020` | Aksen, tombol aktif, logo badge |
| Success Green | `#2d8a3e` | Pemasukan, status positif |
| Danger Red | `#c0392b` | Pengeluaran, status negatif |
| Info Blue | `#1a6fc4` | Informasi, badge kegiatan |
| Background | `#f0f4f8` | Background halaman utama |
| Card White | `#ffffff` | Permukaan kartu/komponen |
| Border | `#e8edf2` | Garis pembatas komponen |
| Text Muted | `#8a96a3` | Label, teks sekunder |

### 7.3 Komponen UI Utama
- **Sidebar navigasi** — Fixed, collapsible di mobile, dengan indikator role user
- **Stat cards** — 4 kartu statistik utama di atas dashboard
- **Bar chart** — Arus kas bulanan (pemasukan vs pengeluaran)
- **Donut chart** — Distribusi pengeluaran per divisi
- **Data table** — Tabel transaksi dengan filter, search, dan pagination
- **Form input** — Validasi client-side + server-side
- **Modal konfirmasi** — Untuk aksi hapus/destruktif
- **Flash message** — Notifikasi sukses/gagal setelah aksi
- **Badge status** — Pill berwarna untuk jenis transaksi dan status kegiatan
- **Live indicator** — Titik berkedip sebagai indikator data real-time

### 7.4 Breakpoint Responsif
| Breakpoint | Lebar | Perilaku |
|---|---|---|
| Mobile | < 768px | Sidebar hidden, tampil sebagai hamburger menu |
| Tablet | 768px – 1024px | Sidebar collapsed (hanya ikon) |
| Desktop | > 1024px | Sidebar full (ikon + label) |

---

## 8. KEAMANAN SISTEM

| Aspek | Implementasi |
|---|---|
| **Autentikasi** | Session PHP + validasi setiap request |
| **Password** | Hashing menggunakan `password_hash()` (bcrypt) |
| **Otorisasi** | Pengecekan role di setiap halaman/endpoint |
| **SQL Injection** | Prepared statements dengan PDO |
| **XSS** | `htmlspecialchars()` pada semua output |
| **CSRF** | CSRF token di setiap form POST |
| **Upload file** | Validasi ekstensi, MIME type, dan ukuran maksimal (5MB) |
| **Folder upload** | `.htaccess` mencegah eksekusi PHP di folder `uploads/` |
| **Session timeout** | Auto logout setelah 60 menit idle |
| **Direct access** | Semua halaman dicek session, redirect ke login jika belum login |

---

## 9. SPESIFIKASI TEKNIS

### 9.1 Backend
- **Bahasa** : PHP 8.x (Native, tanpa framework)
- **Database** : MySQL 8.x
- **Koneksi DB** : PDO (PHP Data Objects) dengan prepared statements
- **Session** : PHP native session
- **Export PDF** : Library TCPDF
- **Export Excel** : Library PhpSpreadsheet

### 9.2 Frontend
- **Markup** : HTML5 semantik
- **Styling** : CSS3 custom (tanpa framework CSS)
- **Font** : Google Fonts — Poppins (300, 400, 500, 600, 700)
- **Grafik** : Chart.js v4
- **JavaScript** : Vanilla JS (tanpa framework)
- **Responsif** : CSS Media Queries native

### 9.3 Server & Deployment
- **Web Server** : Apache (XAMPP/Laragon untuk development)
- **PHP Version** : 8.0 ke atas
- **MySQL Version** : 8.0 ke atas
- **Upload max size** : 5MB per file bukti transaksi
- **Allowed file types** : JPG, PNG, PDF

---

## 10. METODE PENGEMBANGAN — EXTREME PROGRAMMING (XP)

### Tahapan XP yang Diterapkan

#### Fase 1 — Planning (Perencanaan)
- Identifikasi kebutuhan sistem bersama stakeholder (Karang Taruna 001 Lebak Bulus)
- Penulisan user stories berdasarkan permasalahan nyata
- Penentuan iterasi (release plan)
- Estimasi waktu pengerjaan setiap fitur

#### Fase 2 — Design (Perancangan)
- Pembuatan ERD (Entity Relationship Diagram) database
- Pembuatan Use Case Diagram
- Pembuatan Activity Diagram per fitur
- Perancangan UI/UX mockup dashboard dan halaman utama
- Penentuan struktur folder dan arsitektur sistem

#### Fase 3 — Coding (Pengkodean)
- Implementasi database schema MySQL
- Pengembangan fitur iteratif berdasarkan prioritas
- Urutan pengembangan:
  1. Autentikasi & manajemen session
  2. Dashboard & visualisasi data
  3. Modul transaksi (CRUD + upload bukti)
  4. Modul kegiatan
  5. Modul laporan & export
  6. Modul anggota & user
  7. Modul pengaturan sistem

#### Fase 4 — Testing (Pengujian)
- **Black Box Testing** — Pengujian fungsionalitas setiap fitur
- **User Acceptance Testing (UAT)** — Pengujian langsung bersama pengguna (Bendahara, Ketua, Anggota Karang Taruna 001 Lebak Bulus)

---

## 11. USER STORIES

| ID | Sebagai | Saya ingin | Sehingga |
|---|---|---|---|
| US-01 | Bendahara | Login ke sistem dengan username & password | Bisa mengakses fitur pengelolaan keuangan |
| US-02 | Bendahara | Mencatat pemasukan dana dengan detail lengkap | Semua pemasukan terdokumentasi dengan jelas |
| US-03 | Bendahara | Mencatat pengeluaran dana beserta keterangan penggunaan | Setiap pengeluaran bisa dipertanggungjawabkan |
| US-04 | Bendahara | Upload foto/scan bukti transaksi | Ada bukti digital setiap transaksi |
| US-05 | Bendahara | Mengelola data kegiatan dan menghubungkannya ke transaksi | Laporan keuangan per kegiatan tersedia |
| US-06 | Ketua | Melihat dashboard kondisi keuangan secara real-time | Bisa memantau keuangan organisasi kapan saja |
| US-07 | Ketua | Mengekspor laporan keuangan ke PDF | Laporan bisa dibawa ke rapat atau diserahkan ke kelurahan |
| US-08 | Anggota | Melihat dashboard dan daftar transaksi | Bisa memantau transparansi keuangan organisasi |
| US-09 | Bendahara | Memfilter transaksi berdasarkan periode & kategori | Mudah mencari transaksi yang dibutuhkan |
| US-10 | Bendahara | Mengelola data anggota dan user sistem | Kontrol akses ke sistem tetap terjaga |

---

## 12. KATEGORISASI DATA MASTER

### 12.1 Divisi Organisasi
1. Sosial & Pendidikan
2. Ekonomi Kreatif
3. Keagamaan
4. Pemuda & Olahraga
5. Umum (Operasional Organisasi)

### 12.2 Kategori Pemasukan (Default)
- Iuran Anggota
- Dana Stimulan Pemerintah / Kelurahan
- Donasi / Sumbangan
- Hasil Kegiatan / Usaha
- Hibah
- Lain-lain Pemasukan

### 12.3 Kategori Pengeluaran (Default)
- Konsumsi Kegiatan
- Pembelian Perlengkapan/Peralatan
- Operasional Sekretariat
- Transportasi
- Honorarium / Insentif
- Dokumentasi & Percetakan
- Kebersihan & Lingkungan
- Lain-lain Pengeluaran

---

## 13. KETERBATASAN SISTEM (OUT OF SCOPE)

Hal-hal berikut **tidak** termasuk dalam lingkup pengembangan sistem ini:
- Integrasi langsung dengan sistem perbankan atau e-payment
- Fitur multi-cabang / multi-organisasi
- Fitur chat / komunikasi anggota
- Notifikasi email atau SMS otomatis
- Mobile app native (Android/iOS)
- Fitur absensi anggota
- Manajemen inventaris/aset

---

## 14. DEFINISI ISTILAH

| Istilah | Definisi |
|---|---|
| **Pemasukan** | Seluruh dana yang masuk ke kas organisasi |
| **Pengeluaran** | Seluruh dana yang keluar dari kas organisasi |
| **Saldo** | Selisih total pemasukan dikurangi total pengeluaran |
| **PIC** | Person In Charge — penanggung jawab suatu transaksi/kegiatan |
| **Bukti Digital** | File foto/scan struk, nota, kwitansi, atau dokumen pendukung transaksi |
| **Dana Stimulan** | Dana bantuan dari pemerintah (kelurahan/kecamatan) untuk kegiatan organisasi |
| **Divisi** | Pembagian bidang kerja dalam organisasi Karang Taruna |
| **Real-time** | Data yang ditampilkan selalu menggambarkan kondisi terkini (sesuai data di database) |

---

*Dokumen ini dibuat sebagai acuan pengembangan sistem dan bersifat living document — dapat diperbarui seiring perkembangan proyek.*

*Karang Taruna 001 Lebak Bulus · Kelurahan Lebak Bulus, Cilandak, Jakarta Selatan*
