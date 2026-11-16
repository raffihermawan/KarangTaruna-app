CREATE TABLE users (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    nama        VARCHAR(100) NOT NULL,
    username    VARCHAR(50) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    role        ENUM('bendahara','ketua','anggota') NOT NULL,
    foto        VARCHAR(255) DEFAULT NULL,
    telegram_chat_id VARCHAR(100) DEFAULT NULL,
    otp_code    VARCHAR(255) DEFAULT NULL,
    otp_expiry  DATETIME DEFAULT NULL,
    status      ENUM('aktif','nonaktif') DEFAULT 'aktif',
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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

CREATE TABLE kategori_transaksi (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    nama        VARCHAR(100) NOT NULL,
    jenis       ENUM('pemasukan','pengeluaran') NOT NULL,
    divisi      ENUM('sosial_pendidikan','ekonomi_kreatif','keagamaan','pemuda_olahraga','umum') DEFAULT 'umum',
    deskripsi   TEXT DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE kegiatan (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    nama            VARCHAR(150) NOT NULL,
    divisi          ENUM('sosial_pendidikan','ekonomi_kreatif','keagamaan','pemuda_olahraga','umum') NOT NULL,
    tanggal_mulai   DATE NOT NULL,
    tanggal_selesai DATE DEFAULT NULL,
    anggaran        DECIMAL(15,2) DEFAULT 0.00,
    deskripsi       TEXT DEFAULT NULL,
    status          ENUM('aktif','selesai','dibatalkan') DEFAULT 'aktif',
    created_by      INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE transaksi (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    jenis           ENUM('pemasukan','pengeluaran') NOT NULL,
    jumlah          DECIMAL(15,2) NOT NULL,
    tanggal         DATE NOT NULL,
    kategori_id     INT NOT NULL,
    kegiatan_id     INT DEFAULT NULL,
    keterangan      TEXT NOT NULL,
    pic             VARCHAR(100) NOT NULL,
    created_by      INT NOT NULL,
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    approved_by     INT NULL,
    approved_at     DATETIME NULL,
    catatan_penolakan TEXT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (kategori_id)  REFERENCES kategori_transaksi(id),
    FOREIGN KEY (kegiatan_id)  REFERENCES kegiatan(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by)   REFERENCES users(id),
    FOREIGN KEY (approved_by)  REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE bukti_transaksi (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    transaksi_id    INT NOT NULL,
    nama_file       VARCHAR(255) NOT NULL,
    path_file       VARCHAR(255) NOT NULL,
    tipe_file       VARCHAR(50) NOT NULL,
    ukuran_file     INT NOT NULL,
    created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (transaksi_id) REFERENCES transaksi(id) ON DELETE CASCADE
);

CREATE TABLE saldo (
    id          INT PRIMARY KEY AUTO_INCREMENT,
    saldo       DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE pengaturan (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    nama_organisasi VARCHAR(150) DEFAULT 'Karang Taruna 001 Lebak Bulus',
    alamat          TEXT DEFAULT NULL,
    email           VARCHAR(150) DEFAULT NULL,
    logo            VARCHAR(255) DEFAULT NULL,
    no_rekening     VARCHAR(50) DEFAULT NULL,
    nama_bank       VARCHAR(100) DEFAULT NULL,
    tahun_aktif     YEAR DEFAULT NULL,
    updated_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO saldo (saldo) VALUES (0.00);
INSERT INTO pengaturan (nama_organisasi) VALUES ('Karang Taruna 001 Lebak Bulus');

INSERT INTO kategori_transaksi (nama, jenis, divisi) VALUES
('Iuran Anggota', 'pemasukan', 'umum'),
('Dana Stimulan Pemerintah / Kelurahan', 'pemasukan', 'umum'),
('Donasi / Sumbangan', 'pemasukan', 'umum'),
('Hasil Kegiatan / Usaha', 'pemasukan', 'umum'),
('Hibah', 'pemasukan', 'umum'),
('Lain-lain Pemasukan', 'pemasukan', 'umum'),
('Konsumsi Kegiatan', 'pengeluaran', 'umum'),
('Pembelian Perlengkapan/Peralatan', 'pengeluaran', 'umum'),
('Operasional Sekretariat', 'pengeluaran', 'umum'),
('Transportasi', 'pengeluaran', 'umum'),
('Honorarium / Insentif', 'pengeluaran', 'umum'),
('Dokumentasi & Percetakan', 'pengeluaran', 'umum'),
('Kebersihan & Lingkungan', 'pengeluaran', 'umum'),
('Lain-lain Pengeluaran', 'pengeluaran', 'umum');

CREATE TABLE saran (
    id_saran           INT PRIMARY KEY AUTO_INCREMENT,
    id_anggota         INT NOT NULL,
    perihal            VARCHAR(255) NOT NULL,
    isi_saran          TEXT NOT NULL,
    tgl_kirim          TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status_baca        TINYINT DEFAULT 0,
    draf_balasan_ketua TEXT DEFAULT NULL,
    FOREIGN KEY (id_anggota) REFERENCES anggota(id) ON DELETE CASCADE
);

CREATE TABLE dokumen (
    id              INT PRIMARY KEY AUTO_INCREMENT,
    nama_dokumen    VARCHAR(255) NOT NULL,
    jenis_dokumen   VARCHAR(100) NOT NULL,
    nama_file       VARCHAR(255) NOT NULL,
    uploaded_by     INT NOT NULL,
    tgl_upload      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE CASCADE
);
