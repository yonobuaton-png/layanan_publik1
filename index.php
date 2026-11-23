<?php
require 'koneksi.php';

$hari_ini = date('Y-m-d');

// Ambil nomor antrean yang sedang berstatus 'Dipanggil' hari ini
$q_dipanggil = mysqli_query($conn, "SELECT nomor_antrean, keperluan_loket FROM antrean 
                                   WHERE tanggal_antrean = '$hari_ini' AND status_antrean = 'Dipanggil' 
                                   ORDER BY id_antrean DESC LIMIT 1");
$data_dipanggil = mysqli_fetch_assoc($q_dipanggil);
$nomor_aktif    = $data_dipanggil ? $data_dipanggil['nomor_antrean'] : '-';
$keperluan_aktif = $data_dipanggil ? $data_dipanggil['keperluan_loket'] : 'Belum ada panggilan aktif';

// Hitung sisa antrean yang masih 'Menunggu' hari ini
$q_sisa = mysqli_query($conn, "SELECT COUNT(*) AS total FROM antrean 
                               WHERE tanggal_antrean = '$hari_ini' AND status_antrean = 'Menunggu'");
$sisa_antrean = mysqli_fetch_assoc($q_sisa)['total'];

// Ambil daftar jenis surat dari database
$q_surat = mysqli_query($conn, "SELECT * FROM jenis_surat ORDER BY id_jenis ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Layanan Publik Digital</title>
    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-utama: #0F2C59;
            --navy-gelap: #0A1E3F;
            --aksen-hijau: #0D7A5F;
            --latar-halaman: #F4F6F9;
            --garis-halus: #E2E8F0;
            --teks-utama: #1E293B;
            --teks-sekunder: #64748B;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--latar-halaman);
            color: var(--teks-utama);
        }
        .navbar-instansi {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--garis-halus);
        }
        .brand-title {
            font-weight: 700;
            color: var(--navy-utama);
            letter-spacing: -0.3px;
            font-size: 1.1rem;
        }
        .hero-section {
            background-color: var(--navy-utama);
            color: #FFFFFF;
            border-bottom: 4px solid var(--aksen-hijau);
        }
        .card-clean {
            background-color: #FFFFFF;
            border: 1px solid var(--garis-halus);
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(15, 44, 89, 0.04);
        }
        .nomor-loket {
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(2.75rem, 6vw, 4rem);
            font-weight: 700;
            color: var(--navy-utama);
            letter-spacing: 2px;
            line-height: 1.1;
        }
        .btn-navy {
            background-color: var(--navy-utama);
            color: #FFFFFF;
            border: none;
            font-weight: 600;
            padding: 0.6rem 1.25rem;
            border-radius: 7px;
        }
        .btn-navy:hover {
            background-color: var(--navy-gelap);
            color: #FFFFFF;
        }
        .btn-outline-navy {
            border: 1.5px solid var(--navy-utama);
            color: var(--navy-utama);
            font-weight: 600;
            padding: 0.6rem 1.25rem;
            border-radius: 7px;
        }
        .btn-outline-navy:hover {
            background-color: var(--navy-utama);
            color: #FFFFFF;
        }
        .badge-kode {
            background-color: #EEF2F6;
            color: var(--navy-utama);
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0.35rem 0.65rem;
            border-radius: 6px;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navigasi Atas -->
    <nav class="navbar navbar-expand-lg navbar-instansi sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 36px; height: 36px; background-color: var(--navy-utama); color: #fff;">
                    <i class="bi bi-building-check"></i>
                </div>
                <div>
                    <span class="brand-title d-block lh-1">Layanan Publik Digital</span>
                    <small class="text-muted" style="font-size: 0.75rem;">Sistem Administrasi & Antrean Terpadu</small>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end mt-3 mt-lg-0" id="navMenu">
                <div class="d-flex flex-column flex-lg-row gap-2">
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <?php $link_dash = ($_SESSION['role'] === 'admin') ? 'dashboard_admin.php' : 'dashboard_warga.php'; ?>
                        <a href="<?= $link_dash ?>" class="btn btn-navy btn-sm px-3 py-2">
                            <i class="bi bi-grid-1x2 me-1"></i> Ke Dashboard Saya
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm px-3 py-2">Keluar</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-navy btn-sm px-3 py-2">Masuk Akun</a>
                        <a href="register.php" class="btn btn-navy btn-sm px-3 py-2">Daftar Warga</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header Informasi -->
    <header class="hero-section py-4 py-md-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-7">
                    <span class="badge bg-white text-dark fw-semibold mb-2 px-3 py-2 rounded-pill" style="font-size: 0.78rem;">
                        <i class="bi bi-clock me-1 text-success"></i> Layanan Daring 24 Jam &bull; Loket Buka 08.00 – 15.00 WIB
                    </span>
                    <h1 class="fw-bold mb-3" style="font-size: clamp(1.5rem, 3.5vw, 2.25rem); letter-spacing: -0.5px;">
                        Pengajuan Surat Keterangan & Nomor Antrean Loket Tanpa Menunggu Lama
                    </h1>
                    <p class="mb-4 text-white-50" style="font-size: 0.98rem; max-width: 580px;">
                        Ajukan permohonan surat keterangan dari rumah, unggah berkas persyaratan secara digital, dan pantau giliran antrean loket secara langsung melalui perangkat Anda.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="login.php" class="btn btn-light fw-semibold px-4 py-2 text-dark">
                            Ajukan Surat Sekarang <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="#daftar-layanan" class="btn btn-outline-light px-4 py-2">
                            Lihat Persyaratan
                        </a>
                    </div>
                </div>

                <!-- Kartu Monitor Antrean Real-Time -->
                <div class="col-lg-5">
                    <div class="card-clean p-4 text-dark">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                            <div>
                                <span class="text-uppercase fw-bold text-muted" style="font-size: 0.72rem; letter-spacing: 0.8px;">Monitor Antrean Hari Ini</span>
                                <div class="fw-semibold small text-dark"><?= date('d M Y') ?></div>
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="bi bi-broadcast me-1"></i> Live
                            </span>
                        </div>

                        <div class="text-center py-3 rounded" style="background-color: #F8FAFC; border: 1px dashed #CBD5E1;">
                            <small class="text-muted d-block mb-1">Nomor Antrean Sedang Dipanggil</small>
                            <div class="nomor-loket my-1"><?= htmlspecialchars($nomor_aktif) ?></div>
                            <small class="text-secondary fw-medium"><?= htmlspecialchars($keperluan_aktif) ?></small>
                        </div>

                        <div class="row g-2 mt-3 text-center">
                            <div class="col-6">
                                <div class="p-2 rounded border bg-white">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Sisa Menunggu</small>
                                    <span class="fw-bold fs-5 text-dark"><?= $sisa_antrean ?></span>
                                    <small class="text-muted"> orang</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded border bg-white">
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Status Loket</small>
                                    <span class="fw-bold fs-6 text-success">Aktif Melayani</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Bagian Jenis Layanan Surat -->
    <main class="container my-5 flex-grow-1" id="daftar-layanan">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <h2 class="h4 fw-bold mb-1" style="color: var(--navy-utama);">Jenis Layanan Administrasi Surat</h2>
                <p class="text-muted small mb-0">Siapkan berkas pindaian (PDF, JPG, atau PNG) sebelum mengisi formulir pengajuan.</p>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <?php while ($surat = mysqli_fetch_assoc($q_surat)): ?>
            <div class="col-md-4">
                <div class="card-clean h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge-kode"><?= htmlspecialchars($surat['kode_surat']) ?></span>
                            <i class="bi bi-file-earmark-text text-muted fs-5"></i>
                        </div>
                        <h3 class="h6 fw-bold mb-2"><?= htmlspecialchars($surat['nama_surat']) ?></h3>
                        <p class="text-muted small mb-3">
                            <strong>Persyaratan:</strong><br>
                            <?= htmlspecialchars($surat['persyaratan']) ?>
                        </p>
                    </div>
                    <a href="login.php" class="btn btn-outline-navy btn-sm w-100 mt-2">
                        Ajukan Layanan Ini
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-top py-4 mt-auto">
        <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 text-muted small">
            <span>&copy; 2026 Sistem Informasi Layanan Publik Digital.</span>
            <span>Dikembangkan untuk Tugas Proyek Manajemen Proyek TI.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>