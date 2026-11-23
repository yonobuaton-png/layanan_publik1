<?php
require 'koneksi.php';

$pesan_error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = bersihkan($_POST['email']);
    $password = $_POST['password'];

    $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
    
    if ($query && mysqli_num_rows($query) > 0) {
        $user = mysqli_fetch_assoc($query);
        
        // Verifikasi password yang terenkripsi
        if (password_verify($password, $user['password'])) {
            $_SESSION['id_user']      = $user['id_user'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['email']        = $user['email'];
            $_SESSION['role']         = $user['role'];

            // Arahkan otomatis berdasarkan peran (warga atau admin)
            if ($user['role'] === 'admin') {
                header("Location: dashboard_admin.php");
                exit;
            } else {
                header("Location: dashboard_warga.php");
                exit;
            }
        } else {
            $pesan_error = "Email atau kata sandi salah. Silakan periksa kembali.";
        }
    } else {
        $pesan_error = "Akun dengan email tersebut tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Layanan Publik Digital</title>
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
            <div class="col-md-5 col-lg-4">
                
                <div class="text-center mb-4">
                    <a href="index.php" class="d-inline-flex align-items-center justify-content-center rounded mb-3 text-decoration-none" style="width: 48px; height: 48px; background-color: var(--navy-utama); color: #fff;">
                        <i class="bi bi-building-check fs-4"></i>
                    </a>
                    <h1 class="h4 fw-bold" style="color: var(--navy-utama);">Masuk ke Sistem</h1>
                    <p class="text-muted small">Portal Layanan Publik dan Antrean Digital</p>
                </div>

                <div class="card-auth p-4">
                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $pesan_error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Alamat Email</label>
                            <input type="email" name="email" class="form-control py-2" placeholder="nama@email.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Kata Sandi (Password)</label>
                            <input type="password" name="password" class="form-control py-2" placeholder="Masukkan password" required>
                        </div>

                        <button type="submit" class="btn btn-navy w-100 mt-2 py-2">Masuk Sistem</button>
                    </form>

                    <hr class="my-4 text-muted">
                    
                    <div class="p-3 rounded bg-light border text-start" style="font-size: 0.78rem;">
                        <span class="fw-bold text-dark d-block mb-1"><i class="bi bi-info-circle me-1"></i> Akun Demo Petugas (Admin):</span>
                        <span class="text-muted">Email:</span> <code>admin@desa.go.id</code><br>
                        <span class="text-muted">Password:</span> <code>admin123</code>
                    </div>
                </div>

                <div class="text-center mt-4 small text-muted">
                    Belum punya akun warga? <a href="register.php" class="fw-semibold text-decoration-none" style="color: var(--navy-utama);">Daftar di sini</a><br>
                    <a href="index.php" class="text-decoration-none text-muted small mt-2 d-inline-block">&larr; Kembali ke Beranda Utama</a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>