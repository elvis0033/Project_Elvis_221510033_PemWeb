<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Jika pengguna sudah login, alihkan ke halaman utama
if (isset($_SESSION['user_id'])) {
    header("Location: home.html");
    exit();
}

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "expense_tracker";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];

    // --- Validasi ---
    if ($password !== $password_confirm) {
        $message = "Password dan konfirmasi password tidak cocok.";
    } elseif (strlen($password) < 8) {
        $message = "Password minimal harus 8 karakter.";
    } else {
        // Cek apakah username atau email sudah ada
        $sql_check = "SELECT * FROM users WHERE username = '$username' OR email = '$email'";
        $result_check = $conn->query($sql_check);

        if ($result_check->num_rows > 0) {
            $message = "Username atau email sudah terdaftar.";
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user baru
            $sql_insert = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password_hash')";
            
            if ($conn->query($sql_insert)) {
                $message = "Pendaftaran berhasil! Silakan <a href='login.php'>login</a>.";
            } else {
                $message = "Pendaftaran gagal. Silakan coba lagi.";
            }
        }
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #005c97; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .signup-box { background: transparent; width: 100%; max-width: 450px; padding: 40px; text-align: center; color: white; }
        .money-icon { font-size: 48px; color: #fff; margin-bottom: 15px; }
        .form-control { background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.3); color: white; padding: 12px 16px 12px 50px; border-radius: 10px; }
        .form-control:focus { background: rgba(255, 255, 255, 0.1); border-color: #36a3d9; box-shadow: none; color: white; }
        .form-control::placeholder { color: white; opacity: 0.7; }
        .btn-signup { background: transparent; border: 2px solid #36a3d9; color: #36a3d9; padding: 14px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; }
        .btn-signup:hover { background: #36a3d9; color: white; }
        .auth-links a { color: #e9ecef; text-decoration: none; font-weight: 500; }
        .auth-links a:hover { color: white; text-decoration: underline; }
        .input-group { position: relative; }
        .input-group .fa { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: white; }
        .message-box { background: #fee2e2; border: 1px solid #fecaca; color: #ef4444; padding: 12px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; }
        .message-box.success { background: #dcfce7; border: 1px solid #86efac; color: #166534; }
    </style>
</head>
<body>
    <div class="signup-box">
        <div class="text-center mb-5">
            <i class="fas fa-coins money-icon"></i>
            <h2 class="h4 text-white fw-bold mb-4">Create Your Account</h2>
        </div>
        
        <form id="signupForm" method="POST" action="signup.php">
            <?php if (!empty($message)): ?>
                <div class="message-box <?= (strpos($message, 'berhasil') !== false) ? 'success' : '' ?>">
                    <?= $message ?>
                </div>
            <?php endif; ?>

            <div class="mb-4 input-group">
                <i class="fa fa-user fa-lg"></i>
                <input type="text" class="form-control" name="username" placeholder="Username" required>
            </div>

            <div class="mb-4 input-group">
                <i class="fa fa-envelope fa-lg"></i>
                <input type="email" class="form-control" name="email" placeholder="E-mail" required>
            </div>

            <div class="mb-4 input-group">
                <i class="fa fa-key fa-lg"></i>
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>

            <div class="mb-4 input-group">
                <i class="fa fa-key fa-lg"></i>
                <input type="password" class="form-control" name="password_confirm" placeholder="Confirm Password" required>
            </div>

            <button type="submit" class="btn btn-signup w-100 mt-4">Sign Up</button>

            <div class="auth-links text-center mt-4">
                <span>Already have an account?</span>
                <a href="login.php" class="ms-2">Login</a>
            </div>
        </form>
    </div>
</body>
</html> 