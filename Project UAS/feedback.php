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
    $subject = $conn->real_escape_string($_POST['subject']);
    $feedback_message = $conn->real_escape_string($_POST['message']);
    if (empty($subject) || empty($feedback_message)) {
        $message = '<div class="alert alert-danger">Subject dan message tidak boleh kosong.</div>';
    } else {
        $sql = "INSERT INTO feedback (user_id, subject, message) VALUES ($user_id, '$subject', '$feedback_message')";
        if ($conn->query($sql)) {
            $message = '<div class="alert alert-success">Feedback berhasil dikirim. Terima kasih!</div>';
        } else {
            $message = '<div class="alert alert-danger">Gagal mengirim feedback.</div>';
        }
    }
}
$user = $conn->query("SELECT username, email FROM users WHERE id = $user_id")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - Expense Tracker</title>
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
        .profile-img-sidebar { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #36a3d9; }
        .offcanvas-header .offcanvas-title { color: #ffffff; }
        .form-control, .form-select { background-color: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.2); color: white; border-radius: 10px; }
        .form-control:focus, .form-select:focus { background-color: rgba(255,255,255,0.05); border-color: #36a3d9; box-shadow: none; color: white; }
        .form-control::placeholder { color: #ced4da; opacity: 1; }
        .form-control option { background-color: #005c97; }
        .btn-custom { background: transparent; border: 2px solid #36a3d9; color: #36a3d9; font-weight: 600; transition: all 0.3s ease; }
        .btn-custom:hover { background: #36a3d9; color: white; }
        .navbar-toggle-btn { color: #fff; background: transparent; border: 1px solid rgba(255,255,255,0.5); border-radius: 8px; transition: background 0.2s, color 0.2s; }
        .navbar-toggle-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .alert { font-size: 14px; margin-bottom: 20px; }
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
                    <img src="profile.jpg" alt="Profile" class="profile-img-sidebar mb-2">
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
                        <a class="nav-link" href="profile.php">
                            <i class="fas fa-user fa-icon"></i>Profile
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="report.php">
                            <i class="fas fa-chart-bar fa-icon"></i>Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">
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
            <div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
                <div class="card p-4">
                    <h4 class="mb-4 text-center">Submit Feedback</h4>
                    <p class="text-center text-white-50 mb-4">We would love to hear your thoughts, suggestions, or concerns.</p>
                    <?php if (!empty($message)) echo $message; ?>
                    <form method="post" action="feedback.php">
                        <div class="mb-3">
                            <label for="feedbackSubject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="feedbackSubject" name="subject" placeholder="e.g., Feature Request" required>
                        </div>
                        <div class="mb-3">
                            <label for="feedbackMessage" class="form-label">Message</label>
                            <textarea class="form-control" id="feedbackMessage" name="message" rows="6" placeholder="Tell us more..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-custom w-100">Submit Feedback</button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 