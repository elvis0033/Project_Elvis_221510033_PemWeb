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

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "expense_tracker";
    $conn = new mysqli($host, $user, $pass, $db);

    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    $username_or_email = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Cek pengguna berdasarkan username atau email
    $sql = "SELECT * FROM users WHERE username = '$username_or_email' OR email = '$username_or_email'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user_data = $result->fetch_assoc();
        
        // Verifikasi password yang di-hash
        if (password_verify($password, $user_data['password'])) {
            // Login sukses, simpan data sesi
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            header("Location: home.php"); // Alihkan ke halaman home
            exit();
        } else {
            $message = "Username atau password salah.";
        }
    } else {
        $message = "Username atau password salah.";
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #005c97; min-height: 100vh; display: flex; justify-content: center; align-items: center; }
        .login-box { background: transparent; width: 100%; max-width: 450px; padding: 40px; text-align: center; color: white; }
        .money-icon { font-size: 48px; color: #fff; margin-bottom: 15px; }
        .form-control { background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.3); color: white; padding: 12px 16px 12px 50px; border-radius: 10px; }
        .form-control:focus { background: rgba(255, 255, 255, 0.1); border-color: #36a3d9; box-shadow: none; color: white; }
        .form-control::placeholder { color: white; opacity: 0.7; }
        .btn-login { background: transparent; border: 2px solid #36a3d9; color: #36a3d9; padding: 14px; border-radius: 10px; font-weight: 600; transition: all 0.3s ease; }
        .btn-login:hover { background: #36a3d9; color: white; }
        .auth-links a { color: #e9ecef; text-decoration: none; font-weight: 500; }
        .auth-links a:hover { color: white; text-decoration: underline; }
        .input-group { position: relative; }
        .input-group .fa { position: absolute; left: 20px; top: 50%; transform: translateY(-50%); color: white; }
        .error-message { background: #fee2e2; border: 1px solid #fecaca; color: #ef4444; padding: 12px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="text-center mb-5">
            <i class="fas fa-coins money-icon"></i>
            <h2 class="h4 text-white fw-bold mb-4">Expense Tracker</h2>
        </div>
        
        <form id="loginForm" method="POST" action="login.php">
            <?php if (!empty($message)): ?>
                <div class="error-message text-center"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <div class="mb-4 input-group">
                <i class="fa fa-user fa-lg"></i>
                <input type="text" class="form-control" id="username" name="username" 
                       placeholder="Username or E-mail" required>
            </div>

            <div class="mb-4 input-group">
                <i class="fa fa-key fa-lg"></i>
                <input type="password" class="form-control" id="password" name="password"
                       placeholder="Password" required>
            </div>

            <button type="submit" class="btn btn-login w-100 mt-4">Login</button>

            <div class="auth-links d-flex justify-content-between mt-4">
                <a href="signup.php">Sign Up</a>
            </div>
     </form>
    </div>
</body>
</html> 