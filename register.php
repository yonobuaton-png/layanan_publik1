<?php
require 'koneksi.php';

$pesan_error = "";
$pesan_sukses = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik          = bersihkan($_POST['nik']);
    $nama_lengkap = bersihkan($_POST['nama_lengkap']);
    $email        = bersihkan($_POST['email']);
    $password     = $_POST['password'];
    $no_hp        = bersihkan($_POST['no_hp']);
    $role         = 'warga'; // Pendaftaran publik otomatis sebagai warga

    // Validasi NIK harus tepat 16 digit angka
    if (strlen($nik) !== 16 || !ctype_digit($nik)) {
        $pesan_error = "Nomor Induk Kependudukan (NIK) harus tepat 16 digit angka.";
    } else {
        // Cek duplikasi NIK atau Email
        $cek = mysqli_query($conn, "SELECT id_user FROM users WHERE nik = '$nik' OR email = '$email' LIMIT 1");
        if (mysqli_num_rows($cek) > 0) {
            $pesan_error = "NIK atau Alamat Email tersebut sudah terdaftar di sistem.";
        } else {
            // Enskripsi password aman
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            $query = "INSERT INTO users (nik, nama_lengkap, email, password, no_hp, role) 
                      VALUES ('$nik', '$nama_lengkap', '$email', '$password_hash', '$no_hp', '$role')";
            
            if (mysqli_query($conn, $query)) {
                $pesan_sukses = "Registrasi akun berhasil! Silakan masuk menggunakan akun Anda.";
            } else {
                $pesan_error = "Terjadi kesalahan sistem, silakan coba lagi.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun Warga - Layanan Publik Digital</title>
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
        .card-auth {
            background-color: #FFFFFF;
            border: 1px solid var(--garis-halus);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(15, 44, 89, 0.05);
        }
        .btn-navy {
            background-color: var(--navy-utama);
            color: #FFFFFF;
            font-weight: 600;
            padding: 0.65rem;
            border-radius: 8px;
        }
        .btn-navy:hover {
            background-color: var(--navy-gelap);
            color: #FFFFFF;
        }
        .form-control:focus {
            border-color: var(--navy-utama);
            box-shadow: 0 0 0 0.2rem rgba(15, 44, 89, 0.15);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 justify-content-center py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="text-center mb-4">
                    <a href="index.php" class="d-inline-flex align-items-center justify-content-center rounded mb-3 text-decoration-none" style="width: 48px; height: 48px; background-color: var(--navy-utama); color: #fff;">
                        <i class="bi bi-building-check fs-4"></i>
                    </a>
                    <h1 class="h4 fw-bold" style="color: var(--navy-utama);">Pendaftaran Akun Warga</h1>
                    <p class="text-muted small">Buat akun untuk mengajukan surat dan mengambil antrean</p>
                </div>

                <div class="card-auth p-4 p-md-4">
                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $pesan_error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pesan_sukses)): ?>
                        <div class="alert alert-success alert-dismissible fade show small py-2" role="alert">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= $pesan_sukses ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="login.php" class="btn btn-navy w-100 py-2">Masuk ke Akun Sekarang</a>
                        </div>
                    <?php else: ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nomor Induk Kependudukan (NIK)</label>
                            <input type="text" name="nik" class="form-control form-control-sm py-2" placeholder="16 digit NIK KTP" maxlength="16" required>
                            <div class="form-text" style="font-size: 0.72rem;">Harus tepat 16 digit angka sesuai KTP.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control form-control-sm py-2" placeholder="Nama lengkap sesuai KTP" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Alamat Email Aktif</label>
                            <input type="email" name="email" class="form-control form-control-sm py-2" placeholder="nama@email.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" class="form-control form-control-sm py-2" placeholder="08xxxxxxxxxx" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Kata Sandi (Password)</label>
                            <input type="password" name="password" class="form-control form-control-sm py-2" placeholder="Minimal 6 karakter" minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-navy w-100 mt-2 py-2">Daftar Akun Baru</button>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="text-center mt-4 small text-muted">
                    Sudah memiliki akun? <a href="login.php" class="fw-semibold text-decoration-none" style="color: var(--navy-utama);">Masuk di sini</a><br>
                    <a href="index.php" class="text-decoration-none text-muted small mt-2 d-inline-block">&larr; Kembali ke Beranda Utama</a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>