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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --navy-utama: #0F2C59;
            --navy-gelap: #0A1E3F;
            --latar-halaman: #F4F6F9;
            --garis-halus: #E2E8F0;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--latar-halaman);
            color: #1E293B;
        }
        .navbar-instansi {
            background-color: #FFFFFF;
            border-bottom: 1px solid var(--garis-halus);
        }
        .card-clean {
            background-color: #FFFFFF;
            border: 1px solid var(--garis-halus);
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(15, 44, 89, 0.04);
        }
        .btn-navy {
            background-color: var(--navy-utama);
            color: #FFFFFF;
            font-weight: 600;
            border-radius: 7px;
        }
        .btn-navy:hover {
            background-color: var(--navy-gelap);
            color: #FFFFFF;
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100">

    <!-- Navbar Admin -->
    <nav class="navbar navbar-expand-lg navbar-instansi sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 36px; height: 36px; background-color: var(--navy-utama); color: #fff;">
                    <i class="bi bi-shield-lock"></i>
                </div>
                <div>
                    <span class="fw-bold d-block lh-1" style="color: var(--navy-utama); font-size: 1.05rem;">Panel Admin & Petugas</span>
                    <small class="text-muted" style="font-size: 0.75rem;">Kelola Verifikasi & Antrean Loket</small>
                </div>
            </a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary btn-sm px-3 py-2"><i class="bi bi-house me-1"></i> Beranda & Monitor</a>
                <a href="logout.php" class="btn btn-outline-danger btn-sm px-3 py-2"><i class="bi bi-box-arrow-right"></i> Keluar</a>
            </div>
        </div>
    </nav>

    <main class="container my-4 flex-grow-1">

        <?php if (!empty($pesan_sukses)): ?>
            <div class="alert alert-success alert-dismissible fade show py-2 small" role="alert">
                <i class="bi bi-check-circle-fill me-1"></i> <?= $pesan_sukses ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Bagian 1: Pengelolaan Antrean Loket -->
        <div class="card-clean p-4 mb-4">
            <h2 class="h6 fw-bold mb-3 text-dark"><i class="bi bi-people me-1 text-primary"></i> Pengelolaan Antrean Loket Hari Ini (<?= date('d M Y') ?>)</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light text-muted">
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
                                    <td><span class="fw-bold fs-6 text-primary font-monospace"><?= $ant['nomor_antrean'] ?></span></td>
                                    <td><?= htmlspecialchars($ant['nama_lengkap']) ?></td>
                                    <td><?= htmlspecialchars($ant['keperluan_loket']) ?></td>
                                    <td>
                                        <?php 
                                            $st_a = $ant['status_antrean'];
                                            $badge_a = 'bg-secondary';
                                            if ($st_a === 'Menunggu') $badge_a = 'bg-secondary';
                                            elseif ($st_a === 'Dipanggil') $badge_a = 'bg-primary';
                                            elseif ($st_a === 'Selesai') $badge_a = 'bg-success';
                                            elseif ($st_a === 'Lewat') $badge_a = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $badge_a ?>"><?= $st_a ?></span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="dashboard_admin.php?aksi_antrean=panggil&id=<?= $ant['id_antrean'] ?>" class="btn btn-primary btn-sm py-0 px-2" style="font-size: 0.75rem;">Panggil</a>
                                            <a href="dashboard_admin.php?aksi_antrean=selesai&id=<?= $ant['id_antrean'] ?>" class="btn btn-success btn-sm py-0 px-2" style="font-size: 0.75rem;">Selesai</a>
                                            <a href="dashboard_admin.php?aksi_antrean=lewat&id=<?= $ant['id_antrean'] ?>" class="btn btn-outline-danger btn-sm py-0 px-2" style="font-size: 0.75rem;">Lewat</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-3 text-muted">Belum ada warga yang mengambil nomor antrean hari ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Bagian 2: Verifikasi Pengajuan Surat Warga -->
        <div class="card-clean p-4">
            <h2 class="h6 fw-bold mb-3 text-dark"><i class="bi bi-file-earmark-check me-1 text-primary"></i> Verifikasi Pengajuan Surat Masuk</h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle small">
                    <thead class="table-light text-muted">
                        <tr>
                            <th>Warga</th>
                            <th>Jenis Surat & Keperluan</th>
                            <th>Berkas Syarat</th>
                            <th>Status & Catatan</th>
                            <th>Aksi Verifikasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($q_semua_surat) > 0): ?>
                            <?php while ($surat = mysqli_fetch_assoc($q_semua_surat)): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?= htmlspecialchars($surat['nama_lengkap']) ?></div>
                                        <small class="text-muted">NIK: <?= htmlspecialchars($surat['nik']) ?></small>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary"><?= htmlspecialchars($surat['nama_surat']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($surat['keperluan']) ?></small>
                                    </td>
                                    <td>
                                        <a href="uploads/<?= htmlspecialchars($surat['file_syarat']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.75rem;">
                                            <i class="bi bi-eye"></i> Cek Berkas
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-info text-dark"><?= $surat['status'] ?></span>
                                        <?php if (!empty($surat['catatan_admin'])): ?>
                                            <div class="text-muted mt-1" style="font-size: 0.7rem;">Catatan: <?= htmlspecialchars($surat['catatan_admin']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <form action="" method="POST" class="d-flex flex-column gap-1">
                                            <input type="hidden" name="id_pengajuan" value="<?= $surat['id_pengajuan'] ?>">
                                            <select name="status_baru" class="form-select form-select-sm py-0" style="font-size: 0.75rem;" required>
                                                <option value="Menunggu Verifikasi" <?= $surat['status'] == 'Menunggu Verifikasi' ? 'selected' : '' ?>>Menunggu</option>
                                                <option value="Diproses" <?= $surat['status'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                                <option value="Selesai" <?= $surat['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                                <option value="Ditolak" <?= $surat['status'] == 'Ditolak' ? 'selected' : '' ?>>Ditolak</option>
                                            </select>
                                            <input type="text" name="catatan_admin" class="form-control form-control-sm py-0" style="font-size: 0.75rem;" placeholder="Catatan (opsional)" value="<?= htmlspecialchars($surat['catatan_admin']) ?>">
                                            <button type="submit" name="update_surat" class="btn btn-navy btn-sm py-0" style="font-size: 0.75rem;">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada pengajuan surat masuk.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <footer class="bg-white border-top py-3 mt-auto text-center small text-muted">
        &copy; 2026 Layanan Publik Digital &bull; Panel Administrator
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>