<?php
session_start();
$conn = new mysqli("localhost", "root", "", "expense_tracker");

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$username = $_POST['username'];
$password = $_POST['password'];

// Cek apakah username/email ada di database
$sql = "SELECT * FROM users WHERE username=? OR email=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Verifikasi password (asumsi password di-hash)
    if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        header("Location: home.php");
        exit();
    } else {
        echo "<script>alert('Password salah!');window.location.href='login.html';</script>";
    }
} else {
    echo "<script>alert('Akun tidak ditemukan!');window.location.href='login.html';</script>";
}
?> 