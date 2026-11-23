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
            --primary-navy: #15345B;
            --secondary-blue: #2878B8;
            --light-blue: #EAF3FA;
            --bg-page: #F8FAFC;
            --white: #FFFFFF;
            --success-green: #20A779;
            --warning-amber: #F3B64B;
            --text-main: #263445;
            --text-secondary: #64748B;
            --border-color: #E2E8F0;
            --radius-card: 14px;
            --transition-smooth: all 0.25s ease;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 400;
            background-color: var(--bg-page);
            color: var(--text-main);
            line-height: 1.65;
        }

        h1, h2, h3, .fw-heading {
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        /* Header */
        .navbar-instansi {
            background-color: var(--white);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(21, 52, 91, 0.04);
        }

        .brand-badge-icon {
            width: 40px;
            height: 40px;
            background-color: var(--light-blue);
            color: var(--secondary-blue);
            border: 1px solid rgba(40, 120, 184, 0.2);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            transition: var(--transition-smooth);
        }

        .navbar-brand:hover .brand-badge-icon {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .brand-title {
            font-weight: 700;
            color: var(--primary-navy);
            font-size: 1.08rem;
            line-height: 1.2;
        }

        .brand-subtitle {
            color: var(--text-secondary);
            font-size: 0.76rem;
            font-weight: 500;
        }

        .btn-outline-secondary-blue {
            border: 1.5px solid var(--secondary-blue);
            color: var(--secondary-blue);
            background-color: transparent;
            font-weight: 600;
            border-radius: 10px;
            transition: var(--transition-smooth);
        }

        .btn-outline-secondary-blue:hover,
        .btn-outline-secondary-blue:focus-visible {
            background-color: var(--light-blue);
            color: var(--primary-navy);
            border-color: var(--secondary-blue);
            transform: translateY(-1px);
        }

        .btn-primary-blue {
            background-color: var(--secondary-blue);
            border: 1.5px solid var(--secondary-blue);
            color: var(--white);
            font-weight: 600;
            border-radius: 10px;
            transition: var(--transition-smooth);
        }

        .btn-primary-blue:hover,
        .btn-primary-blue:focus-visible {
            background-color: var(--primary-navy);
            border-color: var(--primary-navy);
            color: var(--white);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(21, 52, 91, 0.16);
        }

        /* Hero Section */
        .hero-section {
            background: linear-gradient(125deg, var(--primary-navy) 0%, #1C477A 62%, var(--secondary-blue) 100%);
            color: var(--white);
            position: relative;
            overflow: hidden;
        }

        .hero-pill {
            background-color: rgba(234, 243, 250, 0.14);
            color: #EAF3FA;
            border: 1px solid rgba(255, 255, 255, 0.22);
            font-weight: 500;
            font-size: 0.8rem;
        }

        .hero-desc {
            color: #E3EFFA;
            font-size: 1rem;
            line-height: 1.7;
            max-width: 580px;
        }

        .btn-hero-cta {
            background-color: var(--white);
            color: var(--primary-navy);
            border: 1.5px solid var(--white);
            font-weight: 700;
            border-radius: 10px;
            transition: var(--transition-smooth);
        }

        .btn-hero-cta:hover,
        .btn-hero-cta:focus-visible {
            background-color: var(--light-blue);
            border-color: var(--light-blue);
            color: var(--primary-navy);
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(10, 25, 47, 0.2);
        }

        .btn-hero-secondary {
            color: var(--white);
            border: 1.5px solid rgba(255, 255, 255, 0.45);
            background-color: rgba(255, 255, 255, 0.06);
            font-weight: 600;
            border-radius: 10px;
            transition: var(--transition-smooth);
        }

        .btn-hero-secondary:hover,
        .btn-hero-secondary:focus-visible {
            background-color: rgba(255, 255, 255, 0.16);
            border-color: var(--white);
            color: var(--white);
            transform: translateY(-1px);
        }

        .hero-highlights {
            border-top: 1px solid rgba(255, 255, 255, 0.16);
            padding-top: 1.15rem;
            margin-top: 1.5rem;
        }

        .hero-highlight-item {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #EAF3FA;
            font-weight: 500;
        }

        .hero-highlight-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            background-color: rgba(255, 255, 255, 0.14);
            color: #FFFFFF;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }

        /* Kartu & Monitor Antrean */
        .card-clean {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            box-shadow: 0 6px 24px rgba(21, 52, 91, 0.06);
            transition: var(--transition-smooth);
        }

        .monitor-display-box {
            background-color: var(--bg-page);
            border: 1px solid var(--border-color);
            border-radius: 12px;
        }

        .nomor-loket {
            font-family: 'JetBrains Mono', monospace;
            font-size: clamp(2.85rem, 6vw, 4.1rem);
            font-weight: 700;
            color: var(--primary-navy);
            letter-spacing: 2px;
            line-height: 1.15;
        }

        .badge-live-green {
            background-color: rgba(32, 167, 121, 0.12);
            color: var(--success-green);
            border: 1px solid rgba(32, 167, 121, 0.28);
            font-weight: 600;
            border-radius: 999px;
        }

        .badge-menunggu-amber {
            background-color: rgba(243, 182, 75, 0.18);
            color: #9A6700;
            border: 1px solid rgba(243, 182, 75, 0.45);
            font-weight: 600;
            border-radius: 999px;
        }

        .stat-mini-box {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            transition: var(--transition-smooth);
        }

        /* Bagian Jenis Layanan */
        .service-card {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            box-shadow: 0 4px 16px rgba(21, 52, 91, 0.04);
            transition: var(--transition-smooth);
        }

        .service-card:hover {
            transform: translateY(-4px);
            border-color: rgba(40, 120, 184, 0.35);
            box-shadow: 0 12px 28px rgba(21, 52, 91, 0.09);
        }

        .service-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background-color: var(--light-blue);
            color: var(--secondary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .badge-kode {
            background-color: var(--light-blue);
            color: var(--secondary-blue);
            font-weight: 700;
            font-size: 0.78rem;
            padding: 0.38rem 0.75rem;
            border-radius: 8px;
            letter-spacing: 0.02em;
        }

        .btn-layanan-action {
            border: 1.5px solid var(--border-color);
            color: var(--primary-navy);
            background-color: var(--white);
            font-weight: 600;
            border-radius: 10px;
            transition: var(--transition-smooth);
        }

        .btn-layanan-action:hover,
        .btn-layanan-action:focus-visible {
            background-color: var(--secondary-blue);
            border-color: var(--secondary-blue);
            color: var(--white);
        }

        .btn:active {
            transform: scale(0.98);
        }

        footer {
            background-color: var(--white);
            border-top: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navigasi Atas -->
    <nav class="navbar navbar-expand-lg navbar-instansi sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <div class="brand-badge-icon">
                    <i class="bi bi-building-check"></i>
                </div>
                <div>
                    <span class="brand-title d-block">Layanan Publik Digital</span>
                    <span class="brand-subtitle d-block">Sistem Administrasi &amp; Antrean Terpadu</span>
                </div>
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu" aria-controls="navMenu" aria-expanded="false" aria-label="Buka navigasi">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse justify-content-end mt-3 mt-lg-0" id="navMenu">
                <div class="d-flex flex-column flex-lg-row gap-2">
                    <?php if (isset($_SESSION['id_user'])): ?>
                        <?php $link_dash = ($_SESSION['role'] === 'admin') ? 'dashboard_admin.php' : 'dashboard_warga.php'; ?>
                        <a href="<?= $link_dash ?>" class="btn btn-primary-blue btn-sm px-3 py-2">
                            <i class="bi bi-grid-1x2 me-1"></i> Ke Dashboard Saya
                        </a>
                        <a href="logout.php" class="btn btn-outline-danger btn-sm px-3 py-2 rounded-3 fw-semibold">Keluar</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline-secondary-blue btn-sm px-3 py-2">Masuk Akun</a>
                        <a href="register.php" class="btn btn-primary-blue btn-sm px-3 py-2">Daftar Warga</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Header Informasi -->
    <header class="hero-section py-5">
        <div class="container py-lg-2">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="col-lg-7">
                    <span class="badge hero-pill mb-3 px-3 py-2 rounded-pill">
                        <i class="bi bi-clock me-1" style="color: #6EE7B7;"></i> Layanan Daring 24 Jam &bull; Loket Buka 08.00 – 15.00 WIB
                    </span>
                    <h1 class="fw-heading mb-3" style="font-size: clamp(1.65rem, 3.6vw, 2.4rem);">
                        Pengajuan Surat Keterangan &amp; Nomor Antrean Loket Tanpa Menunggu Lama
                    </h1>
                    <p class="hero-desc mb-4">
                        Ajukan permohonan surat keterangan dari rumah, unggah berkas persyaratan secara digital, dan pantau giliran antrean loket secara langsung melalui perangkat Anda.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="login.php" class="btn btn-hero-cta px-4 py-2.5">
                            Ajukan Surat Sekarang <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                        <a href="#daftar-layanan" class="btn btn-hero-secondary px-4 py-2.5">
                            Lihat Persyaratan
                        </a>
                    </div>

                    <div class="hero-highlights d-flex flex-wrap gap-3 gap-md-4">
                        <div class="hero-highlight-item">
                            <span class="hero-highlight-icon"><i class="bi bi-file-earmark-check"></i></span>
                            <span>Unggah Berkas Digital</span>
                        </div>
                        <div class="hero-highlight-item">
                            <span class="hero-highlight-icon"><i class="bi bi-ticket-perforated"></i></span>
                            <span>Antrean Loket Terjadwal</span>
                        </div>
                        <div class="hero-highlight-item">
                            <span class="hero-highlight-icon"><i class="bi bi-shield-check"></i></span>
                            <span>Proses Transparan</span>
                        </div>
                    </div>
                </div>

                <!-- Kartu Monitor Antrean Real-Time -->
                <div class="col-lg-5">
                    <div class="card-clean p-4" style="color: var(--text-main);">
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3" style="border-color: var(--border-color) !important;">
                            <div>
                                <span class="text-uppercase fw-bold d-block" style="font-size: 0.72rem; letter-spacing: 0.08em; color: var(--text-secondary);">Monitor Antrean Hari Ini</span>
                                <div class="fw-semibold small" style="color: var(--text-main);"><?= date('d M Y') ?></div>
                            </div>
                            <span class="badge badge-live-green px-3 py-1.5">
                                <i class="bi bi-broadcast me-1"></i> Live
                            </span>
                        </div>

                        <div class="monitor-display-box text-center py-4 px-3">
                            <small class="d-block mb-1 fw-medium" style="color: var(--text-secondary);">Nomor Antrean Sedang Dipanggil</small>
                            <div class="nomor-loket my-1"><?= htmlspecialchars($nomor_aktif) ?></div>
                            <small class="fw-medium d-block" style="color: var(--secondary-blue);"><?= htmlspecialchars($keperluan_aktif) ?></small>
                        </div>

                        <div class="row g-2 mt-3 text-center">
                            <div class="col-6">
                                <div class="stat-mini-box p-3 h-100 d-flex flex-column justify-content-center align-items-center">
                                    <small class="d-block mb-1" style="font-size: 0.75rem; color: var(--text-secondary);">Sisa Menunggu</small>
                                    <div class="d-flex align-items-center justify-content-center gap-1.5 flex-wrap">
                                        <span class="fw-bold fs-5" style="color: var(--primary-navy);"><?= $sisa_antrean ?></span>
                                        <span class="badge badge-menunggu-amber px-2 py-0.5" style="font-size: 0.72rem;">orang</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="stat-mini-box p-3 h-100 d-flex flex-column justify-content-center align-items-center">
                                    <small class="d-block mb-1" style="font-size: 0.75rem; color: var(--text-secondary);">Status Loket</small>
                                    <span class="badge badge-live-green px-2.5 py-1.5" style="font-size: 0.8rem;">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aktif Melayani
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Bagian Jenis Layanan Surat -->
    <main class="container my-5 py-2 flex-grow-1" id="daftar-layanan">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 pb-1">
            <div>
                <h2 class="h4 fw-heading mb-1" style="color: var(--primary-navy);">Jenis Layanan Administrasi Surat</h2>
                <p class="small mb-0" style="color: var(--text-secondary);">Siapkan berkas pindaian (PDF, JPG, atau PNG) sebelum mengisi formulir pengajuan.</p>
            </div>
        </div>

        <div class="row g-3 g-md-4">
            <?php while ($surat = mysqli_fetch_assoc($q_surat)): ?>
            <div class="col-md-4">
                <div class="service-card h-100 p-4 d-flex flex-column justify-content-between">
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge-kode"><?= htmlspecialchars($surat['kode_surat']) ?></span>
                            <div class="service-icon-box">
                                <i class="bi bi-file-earmark-text"></i>
                            </div>
                        </div>
                        <h3 class="h6 fw-heading mb-2" style="color: var(--text-main);"><?= htmlspecialchars($surat['nama_surat']) ?></h3>
                        <p class="small mb-4" style="color: var(--text-secondary); line-height: 1.65;">
                            <strong style="color: var(--text-main); font-weight: 600;">Persyaratan:</strong><br>
                            <?= htmlspecialchars($surat['persyaratan']) ?>
                        </p>
                    </div>
                    <a href="login.php" class="btn btn-layanan-action btn-sm w-100 py-2">
                        Ajukan Layanan Ini
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-4 mt-auto">
        <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small">
            <span>&copy; 2026 Sistem Informasi Layanan Publik Digital.</span>
            <span>Dikembangkan untuk Tugas Proyek Manajemen Proyek TI.</span>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
