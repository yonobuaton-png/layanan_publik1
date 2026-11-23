<?php
require 'koneksi.php';

// Pastikan yang login adalah admin
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$pesan_sukses = "";
$hari_ini = date('Y-m-d');

// 1. PROSES UPDATE STATUS SURAT
if (isset($_POST['update_surat'])) {
    $id_pengajuan  = bersihkan($_POST['id_pengajuan']);
    $status_baru   = bersihkan($_POST['status_baru']);
    $catatan_admin = bersihkan($_POST['catatan_admin']);

    $q_up = mysqli_query($conn, "UPDATE pengajuan_surat SET status = '$status_baru', catatan_admin = '$catatan_admin' WHERE id_pengajuan = '$id_pengajuan'");
    if ($q_up) {
        $pesan_sukses = "Status pengajuan surat berhasil diperbarui.";
    }
}

// 2. PROSES UPDATE STATUS ANTREAN
if (isset($_GET['aksi_antrean']) && isset($_GET['id'])) {
    $id_antrean = bersihkan($_GET['id']);
    $aksi = bersihkan($_GET['aksi_antrean']);
    
    if ($aksi === 'panggil') {
        // Ubah semua antrean 'Dipanggil' hari ini menjadi 'Selesai' atau biarkan, lalu set target jadi 'Dipanggil'
        mysqli_query($conn, "UPDATE antrean SET status_antrean = 'Selesai' WHERE tanggal_antrean = '$hari_ini' AND status_antrean = 'Dipanggil'");
        mysqli_query($conn, "UPDATE antrean SET status_antrean = 'Dipanggil' WHERE id_antrean = '$id_antrean'");
    } elseif ($aksi === 'selesai') {
        mysqli_query($conn, "UPDATE antrean SET status_antrean = 'Selesai' WHERE id_antrean = '$id_antrean'");
    } elseif ($aksi === 'lewat') {
        mysqli_query($conn, "UPDATE antrean SET status_antrean = 'Lewat' WHERE id_antrean = '$id_antrean'");
    }
    header("Location: dashboard_admin.php");
    exit;
}

// Ambil daftar seluruh pengajuan surat warga
$q_semua_surat = mysqli_query($conn, "SELECT p.*, u.nama_lengkap, u.nik, j.nama_surat FROM pengajuan_surat p 
                                      JOIN users u ON p.id_user = u.id_user 
                                      JOIN jenis_surat j ON p.id_jenis = j.id_jenis 
                                      ORDER BY p.id_pengajuan DESC");

// Ambil daftar antrean hari ini
$q_daftar_antrean = mysqli_query($conn, "SELECT a.*, u.nama_lengkap FROM antrean a 
                                         JOIN users u ON a.id_user = u.id_user 
                                         WHERE a.tanggal_antrean = '$hari_ini' ORDER BY a.id_antrean ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - Layanan Publik Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
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

        .navbar-instansi {
            background-color: var(--white);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 10px rgba(21, 52, 91, 0.04);
        }

        .brand-badge-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background-color: var(--light-blue);
            color: var(--secondary-blue);
            border: 1px solid rgba(40, 120, 184, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            transition: var(--transition-smooth);
        }

        .navbar-brand:hover .brand-badge-icon {
            background-color: var(--secondary-blue);
            color: var(--white);
        }

        .card-clean {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            box-shadow: 0 6px 22px rgba(21, 52, 91, 0.05);
            transition: var(--transition-smooth);
        }

        .section-icon-pill {
            width: 34px;
            height: 34px;
            border-radius: 9px;
            background-color: var(--light-blue);
            color: var(--secondary-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .nomor-antrean-admin {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1rem;
            font-weight: 700;
            color: var(--primary-navy);
            background-color: var(--light-blue);
            padding: 0.28rem 0.65rem;
            border-radius: 8px;
            display: inline-block;
        }

        .form-control,
        .form-select {
            border-color: var(--border-color);
            border-radius: 9px;
            color: var(--text-main);
            transition: var(--transition-smooth);
        }

        .form-control::placeholder {
            color: #94A3B8;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(40, 120, 184, 0.14);
        }

        .btn-primary-blue {
            background-color: var(--secondary-blue);
            border: 1.5px solid var(--secondary-blue);
            color: var(--white);
            font-weight: 600;
            border-radius: 9px;
            transition: var(--transition-smooth);
        }

        .btn-primary-blue:hover,
        .btn-primary-blue:focus-visible {
            background-color: var(--primary-navy);
            border-color: var(--primary-navy);
            color: var(--white);
            transform: translateY(-1px);
            box-shadow: 0 6px 14px rgba(21, 52, 91, 0.14);
        }

        .btn-outline-secondary-blue {
            border: 1.5px solid var(--secondary-blue);
            color: var(--secondary-blue);
            background-color: var(--white);
            font-weight: 600;
            border-radius: 9px;
            transition: var(--transition-smooth);
        }

        .btn-outline-secondary-blue:hover,
        .btn-outline-secondary-blue:focus-visible {
            background-color: var(--light-blue);
            color: var(--primary-navy);
            border-color: var(--secondary-blue);
        }

        .btn-success-soft {
            background-color: var(--success-green);
            border: 1.5px solid var(--success-green);
            color: var(--white);
            font-weight: 600;
            border-radius: 9px;
            transition: var(--transition-smooth);
        }

        .btn-success-soft:hover,
        .btn-success-soft:focus-visible {
            background-color: #188A63;
            border-color: #188A63;
            color: var(--white);
            transform: translateY(-1px);
        }

        .btn:active {
            transform: scale(0.98);
        }

        .badge-status-neutral {
            background-color: var(--bg-page);
            color: var(--text-secondary);
            border: 1px solid var(--border-color);
            font-weight: 600;
            border-radius: 999px;
        }

        .badge-status-amber {
            background-color: rgba(243, 182, 75, 0.2);
            color: #9A6700;
            border: 1px solid rgba(243, 182, 75, 0.45);
            font-weight: 600;
            border-radius: 999px;
        }

        .badge-status-blue {
            background-color: var(--light-blue);
            color: var(--secondary-blue);
            border: 1px solid rgba(40, 120, 184, 0.28);
            font-weight: 600;
            border-radius: 999px;
        }

        .badge-status-green {
            background-color: rgba(32, 167, 121, 0.14);
            color: #167A58;
            border: 1px solid rgba(32, 167, 121, 0.32);
            font-weight: 600;
            border-radius: 999px;
        }

        .table-custom thead th {
            background-color: var(--bg-page);
            color: var(--text-secondary);
            font-weight: 600;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            border-bottom: 1px solid var(--border-color);
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }

        .table-custom tbody td {
            border-color: var(--border-color);
            padding-top: 0.9rem;
            padding-bottom: 0.9rem;
            transition: var(--transition-smooth);
        }

        .table-custom tbody tr:hover td {
            background-color: rgba(234, 243, 250, 0.45);
        }

        footer {
            background-color: var(--white);
            border-top: 1px solid var(--border-color);
            color: var(--text-secondary);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-instansi sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2.5" href="index.php">
                <div class="brand-badge-icon">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div>
                    <span class="fw-heading d-block lh-1" style="color: var(--primary-navy); font-size: 1.06rem;">Panel Admin &amp; Petugas</span>
                    <small class="d-block mt-0.5" style="font-size: 0.76rem; color: var(--text-secondary); font-weight: 500;">Kelola Verifikasi &amp; Antrean Loket</small>
                </div>
            </a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary-blue btn-sm px-3 py-2"><i class="bi bi-house me-1"></i> Beranda &amp; Monitor</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-3 py-2 rounded-3 fw-semibold"><i class="bi bi-box-arrow-right me-1"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <main class="container my-4 my-md-5 flex-grow-1">

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success alert-dismissible fade show small py-2.5 px-3 rounded-3 mb-4" role="alert" style="background-color: rgba(32, 167, 121, 0.12); border: 1px solid rgba(32, 167, 121, 0.3); color: #157353;">
                <i class="bi bi-check-circle-fill me-1" style="color: var(--success-green);"></i> <?= $pesan_sukses ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Bagian 1: Pengelolaan Antrean Loket -->
        <div class="card-clean p-4 mb-4">
            <div class="d-flex align-items-center gap-2.5 mb-3">
                <span class="section-icon-pill"><i class="bi bi-people"></i></span>
                <h2 class="h6 fw-heading mb-0" style="color: var(--primary-navy);">Pengelolaan Antrean Loket Hari Ini (<?= date('d M Y') ?>)</h2>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle small mb-0">
                    <thead>
                        <tr>
                            <th>Nomor</th>
                            <th>Nama Warga</th>
                            <th>Keperluan</th>
                            <th>Status Antrean</th>
                            <th>Aksi Petugas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($q_daftar_antrean) > 0): ?>
                            <?php while ($ant = mysqli_fetch_assoc($q_daftar_antrean)): ?>
                                <tr>
                                    <td><span class="nomor-antrean-admin"><?= $ant['nomor_antrean'] ?></span></td>
                                    <td class="fw-medium" style="color: var(--text-main);"><?= htmlspecialchars($ant['nama_lengkap']) ?></td>
                                    <td style="color: var(--text-secondary);"><?= htmlspecialchars($ant['keperluan_loket']) ?></td>
                                    <td>
                                        <?php 
                                            $st_a = $ant['status_antrean'];
                                            $badge_a = 'badge-status-neutral';
                                            if ($st_a === 'Menunggu') $badge_a = 'badge-status-amber';
                                            elseif ($st_a === 'Dipanggil') $badge_a = 'badge-status-blue';
                                            elseif ($st_a === 'Selesai') $badge_a = 'badge-status-green';
                                            elseif ($st_a === 'Lewat') $badge_a = 'bg-danger text-white rounded-pill';
                                        ?>
                                        <span class="badge <?= $badge_a ?> px-2.5 py-1"><?= $st_a ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1.5">
                                            <a href="dashboard_admin.php?aksi_antrean=panggil&id=<?= $ant['id_antrean'] ?>" class="btn btn-primary-blue btn-sm py-1 px-2.5" style="font-size: 0.76rem;">Panggil</a>
                                            <a href="dashboard_admin.php?aksi_antrean=selesai&id=<?= $ant['id_antrean'] ?>" class="btn btn-success-soft btn-sm py-1 px-2.5" style="font-size: 0.76rem;">Selesai</a>
                                            <a href="dashboard_admin.php?aksi_antrean=lewat&id=<?= $ant['id_antrean'] ?>" class="btn btn-outline-danger btn-sm py-1 px-2.5 rounded-3 fw-semibold" style="font-size: 0.76rem;">Lewat</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">Belum ada warga yang mengambil nomor antrean hari ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bagian 2: Verifikasi Pengajuan Surat Warga -->
        <div class="card-clean p-4">
            <div class="d-flex align-items-center gap-2.5 mb-3">
                <span class="section-icon-pill"><i class="bi bi-file-earmark-check"></i></span>
                <h2 class="h6 fw-heading mb-0" style="color: var(--primary-navy);">Verifikasi Pengajuan Surat Masuk</h2>
            </div>

            <div class="table-responsive">
                <table class="table table-custom align-middle small mb-0">
                    <thead>
                        <tr>
                            <th>Warga</th>
                            <th>Jenis Surat &amp; Keperluan</th>
                            <th>Berkas Syarat</th>
                            <th>Status &amp; Catatan</th>
                            <th>Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($q_semua_surat) > 0): ?>
                            <?php while ($surat = mysqli_fetch_assoc($q_semua_surat)): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold" style="color: var(--text-main);"><?= htmlspecialchars($surat['nama_lengkap']) ?></div>
                                        <small style="color: var(--text-secondary);">NIK: <?= htmlspecialchars($surat['nik']) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold" style="color: var(--secondary-blue);"><?= htmlspecialchars($surat['nama_surat']) ?></div>
                                        <small style="color: var(--text-secondary);"><?= htmlspecialchars($surat['keperluan']) ?></small>
                                    </td>
                                    <td>
                                        <a href="uploads/<?= htmlspecialchars($surat['file_syarat']) ?>" target="_blank" class="btn btn-outline-secondary-blue btn-sm py-1 px-2.5" style="font-size: 0.76rem;">
                                            <i class="bi bi-eye me-1"></i> Cek Berkas
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                            $st_s = $surat['status'];
                                            $badge_s = 'badge-status-blue';
                                            if ($st_s === 'Menunggu Verifikasi') $badge_s = 'badge-status-amber';
                                            elseif ($st_s === 'Diproses') $badge_s = 'badge-status-blue';
                                            elseif ($st_s === 'Selesai') $badge_s = 'badge-status-green';
                                            elseif ($st_s === 'Ditolak') $badge_s = 'bg-danger text-white rounded-pill';
                                        ?>
                                        <span class="badge <?= $badge_s ?> px-2.5 py-1"><?= $st_s ?></span>
                                        <?php if (!empty($surat['catatan_admin'])): ?>
                                            <div class="mt-1.5" style="font-size: 0.74rem; color: var(--text-secondary);">Catatan: <?= htmlspecialchars($surat['catatan_admin']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="min-width: 210px;">
                                        <form action="" method="POST" class="d-flex flex-column gap-1.5">
                                            <input type="hidden" name="id_pengajuan" value="<?= $surat['id_pengajuan'] ?>">
                                            <select name="status_baru" class="form-select form-select-sm py-1.5" style="font-size: 0.78rem;" required>
                                                <option value="Menunggu Verifikasi" <?= $surat['status'] == 'Menunggu Verifikasi' ? 'selected' : '' ?>>Menunggu</option>
                                                <option value="Diproses" <?= $surat['status'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="Selesai" <?= $surat['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                                <option value="Ditolak" <?= $surat['status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                            </select>
                                            <input type="text" name="catatan_admin" class="form-control form-control-sm py-1.5" style="font-size: 0.78rem;" placeholder="Catatan (opsional)" value="<?= htmlspecialchars($surat['catatan_admin']) ?>">
                                            <button type="submit" name="update_surat" class="btn btn-primary-blue btn-sm py-1.5" style="font-size: 0.78rem;">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4" style="color: var(--text-secondary);">Belum ada pengajuan surat masuk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="py-3 mt-auto text-center small">
        &copy; 2026 Layanan Publik Digital &bull; Panel Administrator
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
