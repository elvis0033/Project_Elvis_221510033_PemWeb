<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body {
            background-color: #005c97;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .welcome-box {
            background: transparent;
            width: 100%;
            max-width: 450px;
            padding: 40px;
            text-align: center;
            color: white;
        }
        .money-icon { font-size: 48px; color: #fff; margin-bottom: 15px; }
        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            color: #fff;
            margin-bottom: 12px;
        }
        .welcome-desc {
            color: #e9ecef;
            font-size: 1.1rem;
            margin-bottom: 32px;
        }
        .btn-login {
            background: transparent;
            border: 2px solid #36a3d9;
            color: #36a3d9;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
            margin-bottom: 12px;
        }
        .btn-login:hover { background: #36a3d9; color: white; }
        .btn-signup {
            background: transparent;
            border: 2px solid #fff;
            color: #fff;
            padding: 14px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        .btn-signup:hover { background: #fff; color: #005c97; }
    </style>
</head>
<body>
    <div class="welcome-box">
        <div class="text-center mb-5">
            <i class="fas fa-coins money-icon"></i>
            <h2 class="welcome-title">Expense Tracker</h2>
        </div>
        <div class="welcome-desc mb-4">
            Take control of your finances with ease.
        </div>
        <a href="login.php" class="btn btn-login mb-2">Login</a>
        <a href="signup.php" class="btn btn-signup">Sign Up</a>
    </div>
</body>
</html> 