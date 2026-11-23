<?php
require 'koneksi.php';

// Pastikan yang login adalah warga
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'warga') {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$nama_user = $_SESSION['nama_lengkap'];
$pesan_sukses = "";
$pesan_error = "";

$hari_ini = date('Y-m-d');

// 1. PROSES AMBIL ANTREAN
if (isset($_POST['ambil_antrean'])) {
    $keperluan = bersihkan($_POST['keperluan_loket']);
    
    // Cek apakah warga sudah mengambil antrean aktif hari ini
    $cek_antrean = mysqli_query($conn, "SELECT id_antrean FROM antrean WHERE id_user = '$id_user' AND tanggal_antrean = '$hari_ini' AND status_antrean = 'Menunggu'");
    if (mysqli_num_rows($cek_antrean) > 0) {
        $pesan_error = "Anda sudah memiliki nomor antrean yang aktif hari ini.";
    } else {
        // Cari nomor urut terakhir hari ini
        $q_max = mysqli_query($conn, "SELECT MAX(id_antrean) as max_id FROM antrean");
        $d_max = mysqli_fetch_assoc($q_max);
        $nomor_urut = $d_max['max_id'] ? $d_max['max_id'] + 1 : 1;
        $nomor_antrean = "A-" . str_pad($nomor_urut, 3, "0", STR_PAD_LEFT);

        $query_insert_antrean = "INSERT INTO antrean (id_user, nomor_antrean, tanggal_antrean, keperluan_loket, status_antrean) 
                                 VALUES ('$id_user', '$nomor_antrean', '$hari_ini', '$keperluan', 'Menunggu')";
        if (mysqli_query($conn, $query_insert_antrean)) {
            $pesan_sukses = "Berhasil mengambil nomor antrean: <strong>$nomor_antrean</strong>";
        } else {
            $pesan_error = "Gagal mengambil antrean.";
        }
    }
}

// 2. PROSES PENGAJUAN SURAT
if (isset($_POST['ajukan_surat'])) {
    $id_jenis  = bersihkan($_POST['id_jenis']);
    $keperluan = bersihkan($_POST['keperluan']);
    
    // Validasi file unggahan
    $nama_file   = $_FILES['file_syarat']['name'];
    $ukuran_file = $_FILES['file_syarat']['size'];
    $tmp_file    = $_FILES['file_syarat']['tmp_name'];
    $ekstensi    = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
    
    $ekstensi_diizinkan = ['pdf', 'jpg', 'jpeg', 'png'];
    
    if (!in_array($ekstensi, $ekstensi_diizinkan)) {
        $pesan_error = "Format file syarat harus PDF, JPG, JPEG, atau PNG.";
    } elseif ($ukuran_file > 2097152) { // Maksimal 2MB
        $pesan_error = "Ukuran file terlalu besar. Maksimal 2 MB.";
    } else {
        // Buat nama file unik
        nama_baru:
        $nama_file_baru = uniqid('syarat_') . '.' . $ekstensi;
        $tujuan = "uploads/" . $nama_file_baru;
        
        if (move_uploaded_file($tmp_file, $tujuan)) {
            $query_surat = "INSERT INTO pengajuan_surat (id_user, id_jenis, keperluan, file_syarat, status) 
                            VALUES ('$id_user', '$id_jenis', '$keperluan', '$nama_file_baru', 'Menunggu Verifikasi')";
            if (mysqli_query($conn, $query_surat)) {
                $pesan_sukses = "Pengajuan surat berhasil dikirim dan sedang menunggu verifikasi petugas.";
            } else {
                $pesan_error = "Gagal menyimpan data pengajuan ke database.";
            }
        } else {
            $pesan_error = "Gagal mengunggah file persyaratan ke folder server.";
        }
    }
}

// Ambil data jenis surat untuk dropdown form
$q_jenis = mysqli_query($conn, "SELECT * FROM jenis_surat ORDER BY id_jenis ASC");

// Ambil antrean aktif milik warga hari ini
$q_my_antrean = mysqli_query($conn, "SELECT * FROM antrean WHERE id_user = '$id_user' AND tanggal_antrean = '$hari_ini' LIMIT 1");
$data_antrean = mysqli_fetch_assoc($q_my_antrean);

// Ambil riwayat pengajuan surat warga
$q_riwayat = mysqli_query($conn, "SELECT p.*, j.nama_surat, j.kode_surat FROM pengajuan_surat p 
                                  JOIN jenis_surat j ON p.id_jenis = j.id_jenis 
                                  WHERE p.id_user = '$id_user' ORDER BY p.id_pengajuan DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Warga - Layanan Publik Digital</title>
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

        .antrean-box {
            background-color: var(--bg-page);
            border: 1px solid var(--border-color);
            border-radius: 12px;
        }

        .nomor-antrean-warga {
            font-family: 'JetBrains Mono', monospace;
            font-size: 2.35rem;
            font-weight: 700;
            color: var(--primary-navy);
            letter-spacing: 2px;
            line-height: 1.15;
        }

        .form-label {
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }

        .form-control,
        .form-select {
            border-color: var(--border-color);
            border-radius: 10px;
            color: var(--text-main);
            font-size: 0.92rem;
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

        .btn-outline-secondary-blue {
            border: 1.5px solid var(--secondary-blue);
            color: var(--secondary-blue);
            background-color: var(--white);
            font-weight: 600;
            border-radius: 10px;
            transition: var(--transition-smooth);
        }

        .btn-outline-secondary-blue:hover,
        .btn-outline-secondary-blue:focus-visible {
            background-color: var(--light-blue);
            color: var(--primary-navy);
            border-color: var(--secondary-blue);
        }

        .btn:active {
            transform: scale(0.98);
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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-instansi sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2.5" href="index.php">
                <div class="brand-badge-icon">
                    <i class="bi bi-building-check"></i>
                </div>
                <div>
                    <span class="fw-heading d-block lh-1" style="color: var(--primary-navy); font-size: 1.06rem;">Dashboard Warga</span>
                    <small class="d-block mt-0.5" style="font-size: 0.76rem; color: var(--text-secondary); font-weight: 500;"><?= htmlspecialchars($nama_user) ?></small>
                </div>
            </a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary-blue btn-sm px-3 py-2"><i class="bi bi-house me-1"></i> Beranda</a>
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

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show small py-2.5 px-3 rounded-3 mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $pesan_error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Kolom Kiri: Ambil Antrean & Ajukan Surat -->
            <div class="col-lg-5">
                
                <!-- Kartu Antrean Digital -->
                <div class="card-clean p-4 mb-4">
                    <div class="d-flex align-items-center gap-2.5 mb-3">
                        <span class="section-icon-pill"><i class="bi bi-ticket-perforated"></i></span>
                        <h2 class="h6 fw-heading mb-0" style="color: var(--primary-navy);">Antrean Loket Digital</h2>
                    </div>

                    <?php if ($data_antrean): ?>
                        <div class="antrean-box p-4 text-center">
                            <small class="d-block fw-medium" style="color: var(--text-secondary);">Nomor Antrean Anda Hari Ini</small>
                            <div class="nomor-antrean-warga my-2"><?= $data_antrean['nomor_antrean'] ?></div>
                            <span class="badge badge-status-amber px-3 py-1.5"><?= $data_antrean['status_antrean'] ?></span>
                            <div class="small mt-3" style="color: var(--text-secondary);">Keperluan: <strong style="color: var(--text-main); font-weight: 600;"><?= htmlspecialchars($data_antrean['keperluan_loket']) ?></strong></div>
                        </div>
                    <?php else: ?>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Keperluan Datang ke Loket</label>
                                <input type="text" name="keperluan_loket" class="form-control py-2" placeholder="Contoh: Pengambilan fisik surat SKD" required>
                            </div>
                            <button type="submit" name="ambil_antrean" class="btn btn-primary-blue btn-sm w-100 py-2.5">
                                <i class="bi bi-plus-circle me-1"></i> Ambil Nomor Antrean Hari Ini
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Kartu Pengajuan Surat -->
                <div class="card-clean p-4">
                    <div class="d-flex align-items-center gap-2.5 mb-3">
                        <span class="section-icon-pill"><i class="bi bi-file-earmark-plus"></i></span>
                        <h2 class="h6 fw-heading mb-0" style="color: var(--primary-navy);">Form Pengajuan Surat</h2>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label">Pilih Jenis Surat</label>
                            <select name="id_jenis" class="form-select py-2" required>
                                <option value="">-- Pilih Layanan Surat --</option>
                                <?php while ($js = mysqli_fetch_assoc($q_jenis)): ?>
                                    <option value="<?= $js['id_jenis'] ?>"><?= htmlspecialchars($js['nama_surat']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan / Keperluan Pembuatan</label>
                            <textarea name="keperluan" class="form-control" rows="3" placeholder="Tuliskan keperluan secara ringkas" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Unggah Berkas Syarat (PDF/JPG/PNG, Max 2MB)</label>
                            <input type="file" name="file_syarat" class="form-control" required>
                        </div>

                        <button type="submit" name="ajukan_surat" class="btn btn-primary-blue btn-sm w-100 py-2.5">
                            <i class="bi bi-send me-1"></i> Kirim Pengajuan Surat
                        </button>
                    </form>
                </div>

            </div>

            <!-- Kolom Kanan: Riwayat Pengajuan Surat -->
            <div class="col-lg-7">
                <div class="card-clean p-4 h-100">
                    <div class="d-flex align-items-center gap-2.5 mb-3">
                        <span class="section-icon-pill"><i class="bi bi-clock-history"></i></span>
                        <h2 class="h6 fw-heading mb-0" style="color: var(--primary-navy);">Riwayat Pengajuan Surat Anda</h2>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-custom align-middle small mb-0">
                            <thead>
                                <tr>
                                    <th>Jenis Surat</th>
                                    <th>Tanggal</th>
                                    <th>Berkas</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($q_riwayat) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($q_riwayat)): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold" style="color: var(--text-main);"><?= htmlspecialchars($row['nama_surat']) ?></div>
                                                <small style="color: var(--text-secondary);"><?= htmlspecialchars($row['keperluan']) ?></small>
                                            </td>
                                            <td class="text-nowrap" style="color: var(--text-secondary);"><?= date('d/m/Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                            <td>
                                                <a href="uploads/<?= htmlspecialchars($row['file_syarat']) ?>" target="_blank" class="btn btn-outline-secondary-blue btn-sm py-1 px-2.5" style="font-size: 0.76rem;">
                                                    <i class="bi bi-eye me-1"></i> Lihat
                                                </a>
                                            </td>
                                            <td>
                                                <?php 
                                                    $st = $row['status'];
                                                    $badge_bg = 'bg-secondary text-white';
                                                    if ($st === 'Menunggu Verifikasi') $badge_bg = 'badge-status-amber';
                                                    elseif ($st === 'Diproses') $badge_bg = 'badge-status-blue';
                                                    elseif ($st === 'Selesai') $badge_bg = 'badge-status-green';
                                                    elseif ($st === 'Ditolak') $badge_bg = 'bg-danger text-white';
                                                ?>
                                                <span class="badge <?= $badge_bg ?> px-2.5 py-1"><?= $st ?></span>
                                                <?php if (!empty($row['catatan_admin'])): ?>
                                                    <div class="mt-1.5" style="font-size: 0.74rem; color: var(--text-secondary);">Catatan: <?= htmlspecialchars($row['catatan_admin']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-5" style="color: var(--text-secondary);">Belum ada riwayat pengajuan surat.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <footer class="py-3 mt-auto text-center small">
        &copy; 2026 Layanan Publik Digital &bull; Dashboard Warga
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
