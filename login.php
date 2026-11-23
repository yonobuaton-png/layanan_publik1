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
            overflow-x: hidden;
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
            margin-bottom: 0.45rem;
        }

        .form-control {
            border-color: var(--border-color);
            border-radius: 10px;
            padding: 0.65rem 0.9rem;
            color: var(--text-main);
            font-size: 0.95rem;
            transition: var(--transition-smooth);
        }

        .form-control::placeholder {
            color: #94A3B8;
        }

        .form-control:focus {
            border-color: var(--secondary-blue);
            box-shadow: 0 0 0 3px rgba(40, 120, 184, 0.14);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper .form-control {
            padding-right: 2.75rem;
        }

        .btn-toggle-password {
            position: absolute;
            top: 50%;
            right: 0.5rem;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: var(--text-secondary);
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition-smooth);
            z-index: 3;
        }

        .btn-toggle-password:hover,
        .btn-toggle-password:focus-visible {
            color: var(--secondary-blue);
            background-color: var(--light-blue);
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
            <div class="col-md-5 col-lg-4">
                
                <div class="text-center mb-4">
                    <a href="index.php" class="d-inline-flex align-items-center justify-content-center brand-logo-box mb-3 text-decoration-none">
                        <i class="bi bi-building-check fs-4"></i>
                    </a>
                    <h1 class="h4 fw-heading mb-1" style="color: var(--primary-navy);">Masuk ke Sistem</h1>
                    <p class="small mb-0" style="color: var(--text-secondary);">Portal Layanan Publik dan Antrean Digital</p>
                </div>

                <div class="card-auth p-4 p-sm-4">
                    <?php if (!empty($pesan_error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show small py-2.5 px-3 rounded-3" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> <?= $pesan_error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Alamat Email</label>
                            <input type="email" name="email" class="form-control" placeholder="nama@email.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Kata Sandi (Password)</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="loginPassword" class="form-control" placeholder="Masukkan password" required>
                                <button type="button" class="btn-toggle-password" id="togglePassword" aria-label="Tampilkan atau sembunyikan kata sandi">
                                    <i class="bi bi-eye" id="iconPassword"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-blue w-100 mt-2">Masuk Sistem</button>
                    </form>
                </div>

                <div class="text-center mt-4 small" style="color: var(--text-secondary);">
                    Belum punya akun warga? <a href="register.php" class="text-decoration-none auth-link">Daftar di sini</a><br>
                    <a href="index.php" class="text-decoration-none small mt-2 d-inline-block" style="color: var(--text-secondary);">&larr; Kembali ke Beranda Utama</a>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.getElementById('togglePassword');
        const loginPassword = document.getElementById('loginPassword');
        const iconPassword = document.getElementById('iconPassword');

        if (togglePassword && loginPassword && iconPassword) {
            togglePassword.addEventListener('click', function () {
                const isHidden = loginPassword.getAttribute('type') === 'password';
                loginPassword.setAttribute('type', isHidden ? 'text' : 'password');
                iconPassword.classList.toggle('bi-eye', !isHidden);
                iconPassword.classList.toggle('bi-eye-slash', isHidden);
            });
        }
    </script>
</body>
</html>
