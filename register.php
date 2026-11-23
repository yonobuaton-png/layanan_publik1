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

        h1, .fw-heading {
            font-weight: 700;
            letter-spacing: -0.02em;
        }

        .brand-logo-box {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background-color: var(--light-blue);
            color: var(--secondary-blue);
            border: 1px solid rgba(40, 120, 184, 0.22);
            transition: var(--transition-smooth);
        }

        .brand-logo-box:hover {
            background-color: var(--secondary-blue);
            color: var(--white);
            transform: translateY(-2px);
        }

        .card-auth {
            background-color: var(--white);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-card);
            box-shadow: 0 8px 28px rgba(21, 52, 91, 0.06);
        }

        .form-label {
            color: var(--text-main);
            font-weight: 600;
            font-size: 0.85rem;
            margin-bottom: 0.4rem;
        }

        .form-control {
            border-color: var(--border-color);
            border-radius: 10px;
            padding: 0.62rem 0.9rem;
            color: var(--text-main);
            font-size: 0.92rem;
            transition: var(--transition-smooth);
        }

        .form-control::placeholder {
            color: #94A3B8;
        }

        .form-control:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(40, 120, 184, 0.14);
        }

        .btn-primary-blue {
            background-color: var(--secondary-blue);
            border: 1.5px solid var(--secondary-blue);
            color: var(--white);
            font-weight: 600;
            padding: 0.7rem 1.25rem;
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

        .btn-primary-blue:active {
            transform: scale(0.98);
        }

        .alert-success-custom {
            background-color: rgba(32, 167, 121, 0.1);
            border: 1px solid rgba(32, 167, 121, 0.28);
            color: #157353;
            border-radius: 12px;
        }

        .auth-link {
            color: var(--secondary-blue);
            font-weight: 600;
            transition: var(--transition-smooth);
        }

        .auth-link:hover {
            color: var(--primary-navy);
        }
    </style>
</head>
<body class="d-flex flex-column min-vh-100 justify-content-center py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="text-center mb-4">
                    <a href="index.php" class="d-inline-flex align-items-center justify-content-center brand-logo-box mb-3 text-decoration-none">
                        <i class="bi bi-building-check fs-4"></i>
                    </a>
                    <h1 class="h4 fw-heading mb-1" style="color: var(--primary-navy);">Pendaftaran Akun Warga</h1>
                    <p class="small mb-0" style="color: var(--text-secondary);">Buat akun untuk mengajukan surat dan mengambil antrean</p>
                </div>

                <div class="card-auth p-4 p-md-4">
                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2.5 px-3 rounded-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $pesan_error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($pesan_sukses)): ?>
                        <div class="alert alert-success-custom alert-dismissible fade show small py-3 px-3" role="alert">
                            <i class="bi bi-check-circle-fill me-1" style="color: var(--success-green);"></i> <?= $pesan_sukses ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                        <div class="text-center mt-3">
                            <a href="login.php" class="btn btn-primary-blue w-100">Masuk ke Akun Sekarang</a>
                        </div>
                    <?php else: ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Nomor Induk Kependudukan (NIK)</label>
                            <input type="text" name="nik" class="form-control" placeholder="16 digit NIK KTP" maxlength="16" required>
                            <div class="form-text mt-1" style="font-size: 0.75rem; color: var(--text-secondary);">Harus tepat 16 digit angka sesuai KTP.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" class="form-control" placeholder="Nama lengkap sesuai KTP" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat Email Aktif</label>
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor WhatsApp / HP</label>
                            <input type="text" name="no_hp" class="form-control" placeholder="08xxxxxxxxxx" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kata Sandi (Password)</label>
                            <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" minlength="6" required>
                        </div>

                        <button type="submit" class="btn btn-primary-blue w-100 mt-2">Daftar Akun Baru</button>
                    </form>
                    <?php endif; ?>
                </div>

                <div class="text-center mt-4 small" style="color: var(--text-secondary);">
                    Sudah memiliki akun? <a href="login.php" class="text-decoration-none auth-link">Masuk di sini</a><br>
                    <a href="index.php" class="text-decoration-none small mt-2 d-inline-block" style="color: var(--text-secondary);">&larr; Kembali ke Beranda Utama</a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
