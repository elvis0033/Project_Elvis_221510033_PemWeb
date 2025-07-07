<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
$conn = new mysqli("localhost", "root", "", "expense_tracker");
if ($conn->connect_error) { die("Koneksi gagal: " . $conn->connect_error); }
$user = $conn->query("SELECT username, email FROM users WHERE id = $user_id")->fetch_assoc();

// Query data harian
$daily = [];
$res = $conn->query("SELECT DATE(expense_date) as label, SUM(amount) as total FROM expenses WHERE user_id = $user_id GROUP BY label ORDER BY label DESC LIMIT 7");
while ($row = $res->fetch_assoc()) {
    $daily[] = $row;
}
$daily = array_reverse($daily);

// Query data bulanan
$monthly = [];
$res = $conn->query("SELECT DATE_FORMAT(expense_date, '%Y-%m') as label, SUM(amount) as total FROM expenses WHERE user_id = $user_id GROUP BY label ORDER BY label DESC LIMIT 12");
while ($row = $res->fetch_assoc()) {
    $monthly[] = $row;
}
$monthly = array_reverse($monthly);

// Query data tahunan
$yearly = [];
$res = $conn->query("SELECT YEAR(expense_date) as label, SUM(amount) as total FROM expenses WHERE user_id = $user_id GROUP BY label ORDER BY label DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    $yearly[] = $row;
}
$yearly = array_reverse($yearly);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - Expense Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { font-family: 'Poppins', sans-serif; }
        body { background-color: #005c97; color: white !important; font-size: 0.875rem; }
        .offcanvas { background-color: #004772; }
        .nav-link { font-weight: 500; color: #e9ecef !important; }
        .nav-link:hover, .nav-link.active { color: #fff !important; }
        .nav-link .fa-icon { margin-right: 8px; }
        .main-content { padding: 20px; }
        .profile-img-sidebar { width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #36a3d9; }
        .offcanvas-header .offcanvas-title { color: #ffffff; }
        .navbar-toggle-btn { color: #fff; background: transparent; border: 1px solid rgba(255,255,255,0.5); border-radius: 8px; transition: background 0.2s, color 0.2s; }
        .navbar-toggle-btn:hover { background: rgba(255,255,255,0.1); color: #fff; }
        .chart-container { background: #005c97; border-radius: 15px; padding: 24px; margin-bottom: 32px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); }
        .report-flex { display: flex; flex-wrap: wrap; gap: 32px; }
        .report-chart { flex: 2 1 350px; }
        .report-analisis { flex: 1 1 250px; background: #004772; border-radius: 15px; padding: 24px; min-width: 220px; }
        .analisis-title { font-size: 1.1rem; font-weight: 600; margin-bottom: 12px; color: #fff; }
        .analisis-list { list-style: none; padding: 0; margin: 0; }
        .analisis-list li { margin-bottom: 8px; color: #fff; }
        @media (max-width: 900px) { .report-flex { flex-direction: column; } }
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
                        <a class="nav-link active" href="report.php">
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
        <div class="container">
            <h2 class="mb-4">Expense Reports</h2>
            <div class="mb-4">
                <label for="chartType" class="form-label">Pilih Periode:</label>
                <select id="chartType" class="form-select w-auto d-inline-block ms-2">
                    <option value="daily">Harian</option>
                    <option value="monthly">Bulanan</option>
                    <option value="yearly">Tahunan</option>
                </select>
            </div>
            <div class="report-flex">
                <div class="report-chart">
                    <div class="chart-container">
                        <canvas id="mainChart"></canvas>
                    </div>
                </div>
                <div class="report-analisis">
                    <div class="analisis-title">Analisis Data</div>
                    <ul class="analisis-list" id="analisisList">
                        <!-- Analisis data akan diisi oleh JS -->
                    </ul>
                </div>
            </div>
        </div>
    </main>
    <script>
        // Data dari PHP ke JS
        const chartData = {
            daily: {
                labels: <?= json_encode(array_column($daily, 'label')) ?>,
                data: <?= json_encode(array_map('floatval', array_column($daily, 'total'))) ?>
            },
            monthly: {
                labels: <?= json_encode(array_column($monthly, 'label')) ?>,
                data: <?= json_encode(array_map('floatval', array_column($monthly, 'total'))) ?>
            },
            yearly: {
                labels: <?= json_encode(array_column($yearly, 'label')) ?>,
                data: <?= json_encode(array_map('floatval', array_column($yearly, 'total'))) ?>
            }
        };
        let currentType = 'daily';
        const ctx = document.getElementById('mainChart').getContext('2d');
        let mainChart;

        function getChartConfig(type) {
            let chartType = (type === 'monthly') ? 'line' : 'bar';
            let color, borderColor, bgArea;
            if (type === 'daily') {
                color = '#0056b3'; // biru tua
                borderColor = '#003366';
                bgArea = 'rgba(0,86,179,0.15)';
            } else if (type === 'monthly') {
                color = '#1e7e34'; // hijau tua
                borderColor = '#145a24';
                bgArea = 'rgba(30,126,52,0.15)';
            } else {
                color = '#6f42c1'; // ungu tua
                borderColor = '#4b286d';
                bgArea = 'rgba(111,66,193,0.15)';
            }
            return {
                type: chartType,
                data: {
                    labels: chartData[type].labels,
                    datasets: [{
                        label: 'Total Pengeluaran',
                        data: chartData[type].data,
                        backgroundColor: chartType === 'line' ? bgArea : color,
                        borderColor: borderColor,
                        fill: chartType === 'line',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    animation: { duration: 1000 },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            enabled: true,
                            callbacks: {
                                label: function(context) {
                                    return 'Total: ' + context.parsed.y.toLocaleString('id-ID', {style:'currency', currency:'IDR'});
                                }
                            }
                        },
                        title: { color: '#fff' }
                    },
                    scales: {
                        x: {
                            ticks: { color: '#fff' },
                            grid: { color: 'rgba(255,255,255,0.2)' }
                        },
                        y: {
                            ticks: { color: '#fff' },
                            grid: { color: 'rgba(255,255,255,0.2)' }
                        }
                    }
                }
            };
        }

        function updateAnalisis(type) {
            const data = chartData[type].data;
            const labels = chartData[type].labels;
            if (!data.length) {
                document.getElementById('analisisList').innerHTML = '<li>Tidak ada data.</li>';
                return;
            }
            const total = data.reduce((a,b) => a+b, 0);
            const avg = total / data.length;
            const max = Math.max(...data);
            const min = Math.min(...data);
            const maxIdx = data.indexOf(max);
            const minIdx = data.indexOf(min);
            let prevTotal = 0;
            if (data.length > 1) prevTotal = data.slice(0, -1).reduce((a,b) => a+b, 0);
            let growth = '';
            if (prevTotal > 0) {
                const diff = total - prevTotal;
                const percent = (diff / prevTotal) * 100;
                growth = (diff >= 0 ? '+' : '') + percent.toFixed(1) + '% dibanding periode sebelumnya';
            }
            document.getElementById('analisisList').innerHTML = `
                <li><b>Total:</b> Rp ${total.toLocaleString('id-ID')}</li>
                <li><b>Rata-rata:</b> Rp ${avg.toLocaleString('id-ID')}</li>
                <li><b>Tertinggi:</b> Rp ${max.toLocaleString('id-ID')} (${labels[maxIdx]})</li>
                <li><b>Terendah:</b> Rp ${min.toLocaleString('id-ID')} (${labels[minIdx]})</li>
                ${growth ? `<li><b>Pertumbuhan:</b> ${growth}</li>` : ''}
            `;
        }

        function renderChart(type) {
            if (mainChart) mainChart.destroy();
            mainChart = new Chart(ctx, getChartConfig(type));
            updateAnalisis(type);
        }

        document.getElementById('chartType').addEventListener('change', function() {
            currentType = this.value;
            renderChart(currentType);
        });

        // Inisialisasi chart pertama kali
        renderChart(currentType);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 