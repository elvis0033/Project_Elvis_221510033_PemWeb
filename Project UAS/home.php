<?php
session_start();

// Jika pengguna belum login, alihkan ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ambil data pengguna dari sesi untuk ditampilkan
$username = $_SESSION['username'] ?? 'User';
// Anda bisa mengambil data lain dari database jika perlu

$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// Ambil data user
$user = $conn->query("SELECT username, email, savings_goal FROM users WHERE id = $user_id")->fetch_assoc();
// Ambil kategori
$categories = $conn->query("SELECT * FROM categories");
// Proses input expense/income
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'add') {
    $desc = $_POST['description'];
    $amount = $_POST['amount'];
    $date = $_POST['expense_date'];
    $category_id = $_POST['category_id'] ?: null;
    $type = $_POST['type'];
    $stmt = $conn->prepare("INSERT INTO expenses (user_id, description, amount, expense_date, category_id, type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdiss", $user_id, $desc, $amount, $date, $category_id, $type);
    $stmt->execute();
}
// Proses edit transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'edit') {
    $id = $_POST['edit_id'];
    $desc = $_POST['edit_description'];
    $amount = $_POST['edit_amount'];
    $date = $_POST['edit_expense_date'];
    $category_id = $_POST['edit_category_id'] ?: null;
    $type = $_POST['edit_type'];
    $stmt = $conn->prepare("UPDATE expenses SET description=?, amount=?, expense_date=?, category_id=?, type=? WHERE id=? AND user_id=?");
    $stmt->bind_param("sdssisi", $desc, $amount, $date, $category_id, $type, $id, $user_id);
    $stmt->execute();
}
// Proses hapus transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['form_type']) && $_POST['form_type'] === 'delete') {
    $id = $_POST['delete_id'];
    $stmt = $conn->prepare("DELETE FROM expenses WHERE id=? AND user_id=?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
}
// Ambil data expense user (join kategori)
$result = $conn->query("SELECT e.*, c.name as category_name FROM expenses e LEFT JOIN categories c ON e.category_id = c.id WHERE e.user_id = $user_id ORDER BY e.expense_date DESC, e.id DESC");
// Hitung summary cards
$total_income = 0;
$total_expense = 0;
$monthly_income = 0;
$monthly_expense = 0;
$year = date('Y');
$month = date('m');
$q = $conn->query("SELECT amount, expense_date, type FROM expenses WHERE user_id = $user_id");
while($row = $q->fetch_assoc()) {
    if ($row['type'] === 'income') {
        $total_income += $row['amount'];
        if (date('Y-m', strtotime($row['expense_date'])) == "$year-$month") {
            $monthly_income += $row['amount'];
        }
    } else {
        $total_expense += $row['amount'];
        if (date('Y-m', strtotime($row['expense_date'])) == "$year-$month") {
            $monthly_expense += $row['amount'];
        }
    }
}
$savings = $total_income - $total_expense;
$savings_goal = $user['savings_goal'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Expense Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #005c97;
            color: white;
            font-size: 0.875rem;
        }

        .offcanvas {
            background-color: #004772;
        }

        .nav-link {
            font-weight: 500;
            color: #e9ecef;
        }

        .nav-link:hover, .nav-link.active {
            color: white;
        }

        .nav-link .fa-icon {
            margin-right: 8px;
        }
        
        .main-content {
            padding: 20px;
        }
        
        .card {
            background-color: #005c97;
            border: none;
            border-radius: 15px;
            color: #fff;
            box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        }

        .card .text-muted {
            color: #6c757d !important;
        }

        .profile-img-sidebar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #36a3d9;
        }

        .offcanvas-header .offcanvas-title {
            color: #ffffff;
        }

        .form-control, .form-select {
            background-color: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: white;
            border-radius: 10px;
        }

        .form-control:focus, .form-select:focus {
            background-color: rgba(255, 255, 255, 0.05);
            border-color: #36a3d9;
            box-shadow: none;
            color: white;
        }

        .form-control::placeholder {
            color: #ced4da;
            opacity: 1;
        }
        
        .form-control option, .form-select option {
            background-color: #005c97;
        }

        .btn-add-expense {
            background-color: #36a3d9;
            border: none;
            color: white;
            font-weight: 600;
        }

        .btn-add-expense:hover {
            background-color: #2b8cbd;
        }

        .summary-icon {
            color: #fff;
        }

        .transactions-table {
            background: #005c97;
            color: #fff;
            border-radius: 10px;
            overflow: hidden;
        }
        .transactions-table th {
            background: #003f6b;
            color: #fff;
            font-weight: 600;
            border-bottom: 2px solid #004772;
        }
        .transactions-table td {
            border-bottom: 1px solid #004772;
        }
        .transactions-table tr:last-child td {
            border-bottom: none;
        }
        .amount-positive {
            color: #28a745; /* Vibrant Green */
            font-weight: 600;
            background: none;
            border: none;
            border-radius: 0;
            padding: 0;
            font-size: inherit;
            display: inline;
        }
        .amount-negative {
            color: #dc3545; /* Vibrant Red */
            font-weight: 600;
            background: none;
            border: none;
            border-radius: 0;
            padding: 0;
            font-size: inherit;
            display: inline;
        }
        /* Tambahan: warna amount di tabel transaksi */
        .amount-cell.income {
            color: #28a745 !important;
            font-weight: 700;
        }
        .amount-cell.expense {
            color: #dc3545 !important;
            font-weight: 700;
        }
        .navbar-toggle-btn {
            color: #fff;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 8px;
            transition: background 0.2s, color 0.2s;
        }
        .navbar-toggle-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
        }
        .card-label {
            color: #111 !important;
            font-weight: 600;
        }
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
                        <a class="nav-link active" href="#">
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
            <div class="col-12">
                <h2 class="mb-4">Expenses</h2>
                <div class="row">
                    <!-- Summary Cards Dinamis -->
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100 p-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-wallet fa-2x summary-icon" style="background: linear-gradient(135deg, #43e97b, #38f9d7, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                </div>
                                <div>
                                    <div class="card-label">Total Balance</div>
                                    <div class="fw-bold fs-5">$<?= number_format($total_income - $total_expense, 2) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100 p-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-arrow-down fa-2x summary-icon" style="background: linear-gradient(135deg, #43cea2, #185a9d); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                </div>
                                <div>
                                    <div class="card-label">Monthly Income</div>
                                    <div class="fw-bold fs-5">$<?= number_format($monthly_income, 2) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100 p-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-arrow-up fa-2x summary-icon" style="background: linear-gradient(135deg, #ff5858, #f09819); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                </div>
                                <div>
                                    <div class="card-label">Monthly Expenses</div>
                                    <div class="fw-bold fs-5">$<?= number_format($monthly_expense, 2) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100 p-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-piggy-bank fa-2x summary-icon" style="background: linear-gradient(135deg, #ffb347, #ff5e62, #36a3d9, #00b428); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                </div>
                                <div>
                                    <div class="card-label">Savings</div>
                                    <div class="fw-bold fs-5">$<?= number_format($savings, 2) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card h-100 p-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-bullseye fa-2x summary-icon" style="background: linear-gradient(135deg, #36a3d9, #00b428, #ffb347, #ff5e62); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;"></i>
                                </div>
                                <div>
                                    <div class="card-label">Savings Goal</div>
                                    <div class="fw-bold fs-5">$<?= number_format($savings, 2) ?> / $<?= number_format($savings_goal, 2) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Add Transaction Form (TYPE DI ATAS) -->
                <div class="card p-4 mb-4">
                    <h4 class="mb-4">Add New Transaction</h4>
                    <form method="POST">
                        <input type="hidden" name="form_type" value="add">
                        <div class="row g-3">
                            <div class="col-lg-3">
                                <label for="type" class="form-label">Type</label>
                                <select class="form-select" id="type" name="type" required>
                                    <option value="income">Income</option>
                                    <option value="expense">Expense</option>
                                </select>
                            </div>
                            <div class="col-lg-6">
                                <label for="expenseDescription" class="form-label">Description</label>
                                <input type="text" class="form-control" id="expenseDescription" name="description" placeholder="e.g., Coffee with friends" required>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label for="expenseAmount" class="form-label">Amount</label>
                                <input type="number" class="form-control" id="expenseAmount" name="amount" placeholder="0.00" required>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <label for="expenseDate" class="form-label">Date</label>
                                <input type="date" class="form-control" id="expenseDate" name="expense_date" required>
                            </div>
                            <div class="col-lg-9">
                                <label for="expenseCategory" class="form-label">Category</label>
                                <select class="form-select" id="expenseCategory" name="category_id">
                                    <option value="">Choose...</option>
                                    <?php $categories->data_seek(0); while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-lg-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-add-expense w-100">Add</button>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- END Add Transaction Form -->
                <!-- Recent Transactions -->
                <div class="card p-4">
                    <h4 class="mb-4">Recent Transactions</h4>
                    <div class="table-responsive">
                        <table class="table transactions-table">
                            <thead>
                                <tr>
                                    <th scope="col">Date</th>
                                    <th scope="col">Description</th>
                                    <th scope="col">Category</th>
                                    <th scope="col">Type</th>
                                    <th scope="col" class="text-end">Amount</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['expense_date']) ?></td>
                                    <td><?= htmlspecialchars($row['description']) ?></td>
                                    <td><?= htmlspecialchars($row['category_name'] ?? 'Uncategorized') ?></td>
                                    <td><?= htmlspecialchars(ucfirst($row['type'])) ?></td>
                                    <td class="text-end amount-cell <?= $row['type'] ?>">
                                        <?= $row['type'] === 'income' ? '+' : '-' ?>$<?= htmlspecialchars($row['amount']) ?>
                                    </td>
                                    <td>
                                        <!-- Tombol Delete -->
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <!-- Modal Delete -->
                                <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?= $row['id'] ?>" aria-hidden="true">
                                  <div class="modal-dialog">
                                    <div class="modal-content">
                                      <form method="POST">
                                        <input type="hidden" name="form_type" value="delete">
                                        <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                        <div class="modal-header">
                                          <h5 class="modal-title" id="deleteModalLabel<?= $row['id'] ?>">Konfirmasi Hapus</h5>
                                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body">
                                          Apakah Anda yakin ingin menghapus transaksi ini?
                                        </div>
                                        <div class="modal-footer">
                                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                          <button type="submit" class="btn btn-danger">Hapus</button>
                                        </div>
                                      </form>
                                    </div>
                                  </div>
                                </div>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 