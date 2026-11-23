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
$cek_admin = mysqli_query($conn, "SELECT id_user FROM users WHERE email = 'admin@desa.go.id' LIMIT 1");
if (mysqli_num_rows($cek_admin) == 0) {
    $pass_admin = password_hash("admin123", PASSWORD_DEFAULT);
    mysqli_query($conn, "INSERT INTO users (nik, nama_lengkap, email, password, no_hp, role) 
                         VALUES ('0000000000000001', 'Petugas Loket Utama', 'admin@desa.go.id', '$pass_admin', '081234567890', 'admin')");
}

// Fungsi bantu untuk mengamankan input teks
function bersihkan($data) {
    global $conn;
    return htmlspecialchars(mysqli_real_escape_string($conn, trim($data)));
}
?>