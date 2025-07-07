<?php
session_start();
$conn = new mysqli("localhost", "root", "", "expense_tracker");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$fullName = trim($_POST['fullName']);
$email = trim($_POST['email']);
$password = $_POST['password'];
$confirmPassword = $_POST['confirmPassword'];

// Validasi password sama
if ($password !== $confirmPassword) {
    echo "<script>alert('Password dan konfirmasi password tidak sama!');window.location.href='signup.html';</script>";
    exit();
}

// Cek apakah email atau username sudah terdaftar
$sql = "SELECT * FROM users WHERE username=? OR email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $fullName, $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    echo "<script>alert('Username atau email sudah terdaftar!');window.location.href='signup.html';</script>";
    exit();
}

// Hash password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Simpan user baru
$sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $fullName, $email, $hashedPassword);
if ($stmt->execute()) {
    echo "<script>alert('Registrasi berhasil! Silakan login.');window.location.href='login.html';</script>";
} else {
    echo "<script>alert('Terjadi kesalahan saat registrasi!');window.location.href='signup.html';</script>";
}
?> 