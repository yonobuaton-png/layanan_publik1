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

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-instansi sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="index.php">
                <div class="d-flex align-items-center justify-content-center rounded" style="width: 36px; height: 36px; background-color: var(--navy-utama); color: #fff;">
                    <i class="bi bi-building-check"></i>
                </div>
                <div>
                    <span class="fw-bold d-block lh-1" style="color: var(--navy-utama); font-size: 1.05rem;">Dashboard Warga</span>
                    <small class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($nama_user) ?></small>
                </div>
            </a>
            <div class="d-flex gap-2">
                <a href="index.php" class="btn btn-outline-secondary btn-sm px-3 py-2"><i class="bi bi-house me-1"></i> Beranda</a>
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

        <?php if (!empty($pesan_error)): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2 small" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $pesan_error ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Kolom Kiri: Ambil Antrean & Ajukan Surat -->
            <div class="col-lg-5">
                
                <!-- Kartu Antrean Digital -->
                <div class="card-clean p-4 mb-4">
                    <h2 class="h6 fw-bold mb-3 text-dark"><i class="bi bi-ticket-perforated me-1 text-primary"></i> Antrean Loket Digital</h2>
                    <?php if ($data_antrean): ?>
                        <div class="p-3 rounded text-center" style="background-color: #F8FAFC; border: 1px solid var(--garis-halus);">
                            <small class="text-muted d-block">Nomor Antrean Anda Hari Ini</small>
                            <div class="fs-2 fw-bold text-primary my-1" style="font-family: monospace;"><?= $data_antrean['nomor_antrean'] ?></div>
                            <span class="badge bg-warning-subtle text-warning border px-2 py-1"><?= $data_antrean['status_antrean'] ?></span>
                            <div class="small text-muted mt-2">Keperluan: <?= htmlspecialchars($data_antrean['keperluan_loket']) ?></div>
                        </div>
                    <?php else: ?>
                        <form action="" method="POST">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">Keperluan Datang ke Loket</label>
                                <input type="text" name="keperluan_loket" class="form-control form-control-sm py-2" placeholder="Contoh: Pengambilan fisik surat SKD" required>
                            </div>
                            <button type="submit" name="ambil_antrean" class="btn btn-navy btn-sm w-100 py-2">
                                <i class="bi bi-plus-circle me-1"></i> Ambil Nomor Antrean Hari Ini
                            </button>
                        </form>
                    <?php endif; ?>
                </div>

                <!-- Kartu Pengajuan Surat -->
                <div class="card-clean p-4">
                    <h2 class="h6 fw-bold mb-3 text-dark"><i class="bi bi-file-earmark-plus me-1 text-primary"></i> Form Pengajuan Surat</h2>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Pilih Jenis Surat</label>
                            <select name="id_jenis" class="form-select form-select-sm py-2" required>
                                <option value="">-- Pilih Layanan Surat --</option>
                                <?php while ($js = mysqli_fetch_assoc($q_jenis)): ?>
                                    <option value="<?= $js['id_jenis'] ?>"><?= htmlspecialchars($js['nama_surat']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Keterangan / Keperluan Pembuatan</label>
                            <textarea name="keperluan" class="form-control form-control-sm" rows="3" placeholder="Tuliskan keperluan secara ringkas" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Unggah Berkas Syarat (PDF/JPG/PNG, Max 2MB)</label>
                            <input type="file" name="file_syarat" class="form-control form-control-sm" required>
                        </div>

                        <button type="submit" name="ajukan_surat" class="btn btn-navy btn-sm w-100 py-2">
                            <i class="bi bi-send me-1"></i> Kirim Pengajuan Surat
                        </button>
                    </form>
                </div>

            </div>

            <!-- Kolom Kanan: Riwayat Pengajuan Surat -->
            <div class="col-lg-7">
                <div class="card-clean p-4 h-100">
                    <h2 class="h6 fw-bold mb-3 text-dark"><i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Pengajuan Surat Anda</h2>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small">
                            <thead class="table-light text-muted">
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
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_surat']) ?></div>
                                                <small class="text-muted"><?= htmlspecialchars($row['keperluan']) ?></small>
                                            </td>
                                            <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['tanggal_pengajuan'])) ?></td>
                                            <td>
                                                <a href="uploads/<?= htmlspecialchars($row['file_syarat']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2" style="font-size: 0.75rem;">
                                                    <i class="bi bi-eye"></i> Lihat
                                                </a>
                                            </td>
                                            <td>
                                                <?php 
                                                    $st = $row['status'];
                                                    $badge_bg = 'bg-secondary';
                                                    if ($st === 'Menunggu Verifikasi') $badge_bg = 'bg-warning text-dark';
                                                    elseif ($st === 'Diproses') $badge_bg = 'bg-info text-dark';
                                                    elseif ($st === 'Selesai') $badge_bg = 'bg-success';
                                                    elseif ($st === 'Ditolak') $badge_bg = 'bg-danger';
                                                ?>
                                                <span class="badge <?= $badge_bg ?> fw-semibold"><?= $st ?></span>
                                                <?php if (!empty($row['catatan_admin'])): ?>
                                                    <div class="text-muted mt-1" style="font-size: 0.7rem;">Catatan: <?= htmlspecialchars($row['catatan_admin']) ?></div>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Belum ada riwayat pengajuan surat.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <footer class="bg-white border-top py-3 mt-auto text-center small text-muted">
        &copy; 2026 Layanan Publik Digital &bull; Dashboard Warga
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>