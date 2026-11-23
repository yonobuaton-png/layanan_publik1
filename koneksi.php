<?php
// Memulai sesi login pengguna
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi koneksi database lokal XAMPP
$host = "localhost";
$user = "root";
$pass = "";
$db   = "layanan_publik1";

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Memastikan akun admin bawaan selalu siap digunakan dengan password: admin123
$email_admin = 'admin@desa.go.id';
$pass_admin  = password_hash("admin123", PASSWORD_DEFAULT);
$cek_admin   = mysqli_query($conn, "SELECT id_user, password, role FROM users WHERE email = '$email_admin' LIMIT 1");

if ($cek_admin) {
    if (mysqli_num_rows($cek_admin) == 0) {
        mysqli_query($conn, "INSERT INTO users (nik, nama_lengkap, email, password, no_hp, role) 
                             VALUES ('0000000000000001', 'Petugas Loket Utama', '$email_admin', '$pass_admin', '081234567890', 'admin')");
    } else {
        $row_admin = mysqli_fetch_assoc($cek_admin);
        if (!password_verify("admin123", $row_admin['password']) || $row_admin['role'] !== 'admin') {
            mysqli_query($conn, "UPDATE users SET password = '$pass_admin', role = 'admin' WHERE id_user = '{$row_admin['id_user']}'");
        }
    }
}

// Fungsi bantu untuk mengamankan input teks
function bersihkan($data) {
    global $conn;
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}
?>
