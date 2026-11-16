# Use Case Sistem Aplikasi Keuangan Karang Taruna 001 Lebak Bulus

## 1. Nama Use Case
**Mengelola dan Memantau Keuangan Organisasi Karang Taruna 001 Lebak Bulus**

Use case ini menggabungkan seluruh peran pengguna dalam satu alur utama. Setiap role memiliki hak akses berbeda, tetapi semuanya berinteraksi dengan sistem keuangan yang sama.

## 2. Aktor
- **Bendahara**
- **Ketua**
- **Anggota**

## 3. Tujuan
Menyediakan sistem keuangan organisasi yang transparan, akuntabel, dan mudah dipantau melalui pencatatan transaksi, pengelolaan data pendukung, visualisasi dashboard, dan laporan keuangan.

## 4. Deskripsi Singkat
Setelah login, sistem mengenali role pengguna dan menampilkan fitur sesuai hak aksesnya. Bendahara mengelola seluruh data operasional keuangan. Ketua memantau dan meninjau laporan. Anggota hanya melihat ringkasan dan informasi publik keuangan organisasi.

## 5. Pra-Kondisi
- Pengguna sudah memiliki akun yang aktif.
- Data master awal seperti kategori, kegiatan, dan pengaturan sistem tersedia.
- Pengguna berhasil login ke sistem.

## 6. Pasca-Kondisi
- Data transaksi, kegiatan, anggota, atau user tersimpan sesuai hak akses.
- Dashboard dan laporan menampilkan data terbaru.
- Saldo organisasi terbarui secara otomatis setelah transaksi diproses.

## 7. Alur Utama
1. Pengguna membuka sistem dan melakukan login.
2. Sistem memverifikasi username, password, dan status akun.
3. Sistem membaca role pengguna: Bendahara, Ketua, atau Anggota.
4. Sistem menampilkan menu dan fitur sesuai hak akses role tersebut.
5. Pengguna menjalankan fungsi yang diizinkan oleh role-nya.
6. Sistem memproses data, menyimpan perubahan, dan memperbarui informasi keuangan.
7. Sistem menampilkan hasil, notifikasi, dashboard, atau laporan sesuai kebutuhan pengguna.

## 8. Hak Akses Berdasarkan Role

| Fitur / Modul | Bendahara | Ketua | Anggota |
|---|---:|---:|---:|









\\\\



















| Login & logout | Ya | Ya | Ya |
| Lihat dashboard | Ya | Ya | Ya |
| Lihat saldo dan grafik | Ya | Ya | Ya |
| Input pemasukan | Ya | Tidak | Tidak |
| Input pengeluaran | Ya | Tidak | Tidak |
| Edit / hapus transaksi | Ya | Tidak | Tidak |
| Upload bukti transaksi | Ya | Tidak | Tidak |
| Kelola kategori transaksi | Ya | Tidak | Tidak    |
| Kelola kegiatan | Ya | Tidak | Tidak |
| Kelola anggota | Ya | Tidak | Tidak |
| Kelola user & role | Ya | Tidak | Tidak |
| Lihat daftar transaksi | Ya | Ya | Ya |
| Lihat detail transaksi | Ya | Ya | Tidak |
| Lihat laporan keuangan | Ya | Ya | Ya, versi publik |
| Export PDF / Excel | Ya | Ya | Tidak |
| Pengaturan sistem | Ya | Tidak | Tidak |

## 9. Rincian Perilaku per Role

### 9.1 Bendahara
Bendahara adalah role utama dengan akses penuh. Role ini dapat menambah, mengubah, dan menghapus data transaksi, kegiatan, anggota, user, kategori, serta pengaturan sistem. Bendahara juga dapat mengunggah bukti transaksi dan mengekspor laporan.

### 9.2 Ketua
Ketua berfungsi sebagai role monitoring. Ketua hanya dapat melihat dashboard, transaksi, detail transaksi, dan laporan keuangan. Ketua dapat mengekspor laporan, tetapi tidak dapat mengubah data operasional.

### 9.3 Anggota
Anggota hanya memiliki akses baca untuk melihat kondisi keuangan organisasi secara umum. Anggota dapat melihat dashboard ringkasan, daftar transaksi, dan laporan publik, tetapi tidak dapat mengubah data apa pun.

## 10. Alur Alternatif
### A1. Login Gagal
- Jika username atau password salah, sistem menampilkan pesan gagal login.
- Pengguna diminta mencoba kembali.

### A2. Akses Tidak Sesuai Role
- Jika pengguna mencoba membuka fitur yang tidak sesuai role, sistem menolak akses.
- Sistem menampilkan pesan bahwa pengguna tidak memiliki hak akses.

### A3. Data Tidak Valid
- Jika form transaksi, kegiatan, atau anggota tidak valid, sistem menampilkan pesan validasi.
- Pengguna harus memperbaiki data sebelum disimpan.

## 11. Use Case Diagram Sederhana
```mermaid
flowchart LR
    B[Bendahara] --> S[Sistem Aplikasi Keuangan]
    K[Ketua] --> S
    A[Anggota] --> S

    S --> D[Dashboard dan Ringkasan Keuangan]
    S --> T[Transaksi]
    S --> L[Laporan Keuangan]
    S --> G[Kegiatan]
    S --> M[Manajemen Data Master]
    S --> U[Manajemen User]
    S --> P[Pengaturan Sistem]

    B --> T
    B --> G
    B --> M
    B --> U
    B --> P
    B --> L

    K --> D
    K --> T
    K --> L

    A --> D
    A --> T
    A --> L
```

## 12. Activity Diagram Per Role

> Diagram berikut disusun berdasarkan modul yang tersedia di sistem dan dibuat dengan format swimlane sederhana agar menyerupai contoh gambar yang kamu kirim.

### 12.1 Activity Diagram Bendahara

Aktivitas utama Bendahara:
1. Memantau Dashboard
2. Mengelola Transaksi Pemasukan
3. Mengelola Transaksi Pengeluaran
4. Mengelola Kategori Transaksi
5. Mengelola Kegiatan
6. Mengelola Anggota
7. Mengelola User
8. Mengelola Dokumen/Berkas
9. Mengelola Laporan Keuangan
10. Mengelola Pengaturan Sistem

```mermaid
flowchart LR
    subgraph Bendahara
        B1([Mulai]) --> B2[Login ke Sistem]
        B3[Pilih Menu Aktivitas]
        B4{Pilih Modul}
        B5[Lihat Dashboard]
        B6[Input Pemasukan]
        B7[Input Pengeluaran]
        B8[Kelola Kategori Transaksi]
        B9[Kelola Kegiatan]
        B10[Kelola Anggota]
        B11[Kelola User]
        B12[Kelola Dokumen/Berkas]
        B13[Lihat / Ekspor Laporan]
        B14[Kelola Pengaturan Sistem]
        B15[Logout]
        B16([Selesai])
    end

    subgraph Sistem
        S1[Validasi username, password, dan role]
        S2[Tampilkan menu sesuai hak akses Bendahara]
        S3[Validasi form dan simpan data]
        S4[Perbarui saldo, rekap, dan status data]
        S5[Tampilkan notifikasi berhasil]
    end

    B2 --> S1 --> S2 --> B3 --> B4
    B4 -->|Dashboard| B5 --> S5 --> B3
    B4 -->|Pemasukan| B6 --> S3 --> S4 --> S5 --> B3
    B4 -->|Pengeluaran| B7 --> S3
    B4 -->|Kategori| B8 --> S3
    B4 -->|Kegiatan| B9 --> S3
    B4 -->|Anggota| B10 --> S3
    B4 -->|User| B11 --> S3
    B4 -->|Dokumen| B12 --> S3
    B4 -->|Laporan| B13 --> S5 --> B3
    B4 -->|Pengaturan| B14 --> S3
    S3 --> S4 --> S5 --> B3
    B15 --> B16
```

### 12.2 Activity Diagram Ketua

Aktivitas utama Ketua:
1. Memantau Dashboard
2. Melihat Daftar Transaksi
3. Melihat Detail Transaksi
4. Memproses Persetujuan Transaksi
5. Melihat Dokumen/Berkas
6. Melihat Laporan Keuangan
7. Mengekspor Laporan PDF/Excel
8. Melihat Masukan & Saran
9. Logout

```mermaid
flowchart LR
    subgraph Ketua
        K1([Mulai]) --> K2[Login ke Sistem]
        K3[Pilih Menu Aktivitas]
        K4{Pilih Modul}
        K5[Lihat Dashboard]
        K6[Lihat Daftar Transaksi]
        K7[Lihat Detail Transaksi]
        K8[Setujui / Tolak Transaksi Pending]
        K9[Lihat Dokumen/Berkas]
        K10[Lihat Laporan Keuangan]
        K11[Ekspor PDF / Excel]
        K12[Lihat Masukan & Saran]
        K13[Logout]
        K14([Selesai])
    end

    subgraph Sistem
        T1[Validasi username, password, dan role]
        T2[Tampilkan menu sesuai hak akses Ketua]
        T3[Ambil data transaksi, laporan, dan saran]
        T4[Validasi aksi persetujuan]
        T5[Simpan hasil approve / reject]
        T6[Generate file laporan]
        T7[Tampilkan notifikasi dan hasil proses]
    end

    K2 --> T1 --> T2 --> K3 --> K4
    K4 -->|Dashboard| K5 --> T7 --> K3
    K4 -->|Transaksi| K6 --> T3 --> K7 --> T7 --> K3
    K4 -->|Persetujuan| K8 --> T4 --> T5 --> T7 --> K3
    K4 -->|Dokumen| K9 --> T3 --> T7 --> K3
    K4 -->|Laporan| K10 --> T3 --> K11 --> T6 --> T7 --> K3
    K4 -->|Saran| K12 --> T3 --> T7 --> K3
    K13 --> K14
```

### 12.3 Activity Diagram Anggota

Aktivitas utama Anggota:
1. Memantau Dashboard Ringkasan
2. Melihat Daftar Transaksi
3. Melihat Laporan Publik
4. Mengirim Masukan & Saran
5. Melihat Notifikasi
6. Mengelola Profil Pribadi
7. Mengubah Kata Sandi
8. Logout

```mermaid
flowchart LR
    subgraph Anggota
        A1([Mulai]) --> A2[Login ke Sistem]
        A3[Pilih Menu Aktivitas]
        A4{Pilih Modul}
        A5[Lihat Dashboard Ringkasan]
        A6[Lihat Daftar Transaksi]
        A7[Lihat Laporan Publik]
        A8[Kirim Masukan & Saran]
        A9[Lihat Notifikasi]
        A10[Kelola Profil Pribadi]
        A11[Ubah Kata Sandi]
        A12[Logout]
        A13([Selesai])
    end

    subgraph Sistem
        U1[Validasi username, password, dan role]
        U2[Tampilkan menu sesuai hak akses Anggota]
        U3[Ambil data dashboard, transaksi, dan laporan publik]
        U4[Validasi dan simpan saran]
        U5[Tampilkan notifikasi dan hasil proses]
    end

    A2 --> U1 --> U2 --> A3 --> A4
    A4 -->|Dashboard| A5 --> U3 --> U5 --> A3
    A4 -->|Transaksi| A6 --> U3 --> U5 --> A3
    A4 -->|Laporan| A7 --> U3 --> U5 --> A3
    A4 -->|Saran| A8 --> U4 --> U5 --> A3
    A4 -->|Notifikasi| A9 --> U5 --> A3
    A4 -->|Profil| A10 --> U5 --> A3
    A4 -->|Kata Sandi| A11 --> U5 --> A3
    A12 --> A13
```

## 13. Activity Diagram Mengelola Data Transaksi Keuangan

Diagram ini menggambarkan alur umum pengelolaan data transaksi keuangan, baik pemasukan maupun pengeluaran, dari mulai input data sampai sistem memperbarui saldo dan menampilkan hasil.

```mermaid
flowchart LR
    subgraph Pengguna
        P1([Mulai]) --> P2[Login ke Sistem]
        P3[Pilih Menu Transaksi]
        P4{Pilih Jenis Transaksi}
        P5[Input Data Pemasukan]
        P6[Input Data Pengeluaran]
        P7[Unggah Bukti Transaksi]
        P8[Klik Simpan]
        P9[Lihat Hasil Proses]
        P10[Logout]
        P11([Selesai])
    end

    subgraph Sistem
        S1[Validasi username, password, dan role]
        S2[Tampilkan halaman transaksi]
        S3[Validasi isi form transaksi]
        S4[Validasi file bukti transaksi]
        S5[Simpan data transaksi ke database]
        S6[Simpan bukti transaksi]
        S7[Perbarui saldo dan rekap keuangan]
        S8[Tampilkan notifikasi berhasil atau gagal]
    end

    P2 --> S1 --> S2 --> P3 --> P4
    P4 -->|Pemasukan| P5 --> P7
    P4 -->|Pengeluaran| P6 --> P7
    P7 --> P8 --> S3 --> S4 --> S5 --> S6 --> S7 --> S8 --> P9 --> P10 --> P11
```

## 14. Kesimpulan
Satu use case utama ini menunjukkan bahwa sistem berfokus pada pengelolaan dan pemantauan keuangan organisasi, dengan tiga role yang bekerja dalam satu sistem tetapi memiliki hak akses yang berbeda sesuai tanggung jawabnya.
