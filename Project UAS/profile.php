<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $savings_goal = floatval($_POST['savings_goal']);
    $update_password = false;
    $password_sql = '';
    $profile_picture_sql = '';
    // Proses upload foto profil
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $max_size = 2 * 1024 * 1024; // 2MB
        $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed) && $_FILES['profile_picture']['size'] <= $max_size) {
            $new_name = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            $upload_path = $upload_dir . $new_name;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                // Hapus file lama jika ada
                $old = $conn->query("SELECT profile_picture FROM users WHERE id = $user_id")->fetch_assoc();
                if ($old && $old['profile_picture'] && file_exists($upload_dir . $old['profile_picture'])) {
                    @unlink($upload_dir . $old['profile_picture']);
                }
                $profile_picture_sql = ", profile_picture = '$new_name'";
            } else {
                $message = '<div class="alert alert-danger">Gagal upload foto.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">File harus gambar (jpg/png/webp/gif) dan max 2MB.</div>';
        }
    }
    // Validasi ganti password
    $is_change_password = !empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password']);
    if ($is_change_password) {
        if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
            $message = '<div class="alert alert-danger">Semua field password harus diisi untuk mengganti password.</div>';
        } else {
            $current_password = $_POST['current_password'];
            $row = $conn->query("SELECT password FROM users WHERE id = $user_id")->fetch_assoc();
            if (!$row || !password_verify($current_password, $row['password'])) {
                $message = '<div class="alert alert-danger">Password lama salah.</div>';
            } else if ($_POST['new_password'] === $_POST['confirm_password']) {
                if (strlen($_POST['new_password']) >= 8) {
                    $password_hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
                    $password_sql = ", password = '$password_hash'";
                    $update_password = true;
                } else {
                    $message = '<div class="alert alert-danger">Password minimal 8 karakter.</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Konfirmasi password tidak cocok.</div>';
            }
        }
    }
    if (empty($message)) {
        $sql = "UPDATE users SET username = '$username', email = '$email', savings_goal = $savings_goal $password_sql $profile_picture_sql WHERE id = $user_id";
        if ($conn->query($sql)) {
            $message = '<div class="alert alert-success">Profil berhasil diperbarui.</div>';
            $_SESSION['username'] = $username;
        } else {
            $message = '<div class="alert alert-danger">Gagal memperbarui profil.</div>';
        }
    }
}
$user = $conn->query("SELECT username, email, savings_goal, profile_picture FROM users WHERE id = $user_id")->fetch_assoc();
$profile_pic = (!empty($user['profile_picture']) && file_exists('uploads/' . $user['profile_picture'])) ? 'uploads/' . $user['profile_picture'] : 'profile.jpg';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Expense Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #005c97; color: white; font-size: 0.875rem; }
        .offcanvas { background-color: #004772; }
        .nav-link { font-weight: 500; color: #e9ecef; }
        .nav-link:hover, .nav-link.active { color: white; }
        .nav-link .fa-icon { margin-right: 8px; }
        .main-content { padding: 20px; }
        .card { background-color: #005c97; border: 1px solid rgba(255,255,255,0.1); border-radius: 15px; color: #fff; }
        .form-control, .form-select { background-color: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: 10px; }
        .form-control:focus, .form-select:focus { background-color: rgba(255,255,255,0.05); border-color: #36a3d9; box-shadow: none; color: white; }
        .form-control::placeholder { color: #ced4da; opacity: 1; }
        .form-control option { background-color: #005c97; }
        .btn-custom { background: transparent; border: 2px solid #36a3d9; color: #36a3d9; font-weight: 600; transition: all 0.3s ease; }
        .btn-custom:hover { background: #36a3d9; color: white; }
        .profile-picture { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; margin: 0 auto 20px; border: 4px solid #36a3d9; }
        .profile-img-sidebar { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #36a3d9; }
        .offcanvas-header .offcanvas-title { color: #ffffff; }
        .navbar-toggle-btn { color: #fff; background: transparent; border: 1px solid rgba(255,255,255,0.5); border-radius: 8px; transition: background 0.2s, color 0.2s; }
        .navbar-toggle-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .alert { font-size: 14px; margin-bottom: 20px; }
        .card.p-4 { padding: 20px !important; }
        .profile-picture { margin-bottom: 10px; }
        .card .mb-4 { margin-bottom: 16px !important; }
        .card .mb-3 { margin-bottom: 10px !important; }
        .btn-custom { margin-top: 10px; }
    </style>
</head>
<body>
    <main class="main-content">
        <div class="d-flex align-items-center mb-3">
            <button class="btn navbar-toggle-btn me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                <i class="fas fa-bars"></i>
            </button>
            <a href="home.php" class="d-flex align-items-center text-decoration-none" style="color:#fff;font-weight:600;font-size:1.3rem;">
                <i class="fas fa-coins money-icon me-2"></i>Expense Tracker
            </a>
        </div>
        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu" aria-labelledby="sidebarMenuLabel">
            <div class="offcanvas-header pb-0">
                <div class="text-center w-100">
                    <img src="<?= $profile_pic ?>" alt="Profile" class="profile-img-sidebar mb-2">
                    <h5 class="offcanvas-title d-block mx-auto" id="sidebarMenuLabel"><?= htmlspecialchars($user['username']) ?></h5>
                    <p class="small text-light"><?= htmlspecialchars($user['email']) ?></p>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close" style="position: absolute; top: 1rem; right: 1rem;"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column p-0">
                <hr class="text-white-50 my-2">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">
                            <i class="fas fa-tachometer-alt fa-icon"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="profile.php">
                            <i class="fas fa-user fa-icon"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="report.php">
                            <i class="fas fa-chart-bar fa-icon"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="feedback.php">
                            <i class="fas fa-comment-alt fa-icon"></i>Feedback
                        </a>
                    </li>
                </ul>
                <div class="mt-auto p-3">
                    <a href="logout.php" class="nav-link text-center">
                       <i class="fas fa-sign-out-alt me-2"></i>Logout
                   </a>
               </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-lg-6 col-xl-4 mx-auto">
                <div class="card p-4">
                    <h4 class="mb-4 text-center">Edit Profile</h4>
                    <?php if (!empty($message)) echo $message; ?>
                    <div class="text-center">
                        <img src="<?= $profile_pic ?>" alt="Profile Picture" class="profile-picture">
                    </div>
                    <form method="post" action="profile.php" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="profilePicture" class="form-label">Change Profile Picture</label>
                            <input class="form-control" type="file" id="profilePicture" name="profile_picture" accept="image/*">
                        </div>
                        <div class="mb-3">
                            <label for="username" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email address</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="savings_goal" class="form-label">Savings Goal</label>
                            <input type="number" step="0.01" class="form-control" id="savings_goal" name="savings_goal" value="<?= htmlspecialchars($user['savings_goal']) ?>">
                        </div>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" placeholder="Enter current password">
                        </div>
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Enter new password">
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 