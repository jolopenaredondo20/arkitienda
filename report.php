<?php
// Database connection
$conn = new mysqli('localhost', 'root', '', 'rental');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch stats
$tenant_result = $conn->query("SELECT COUNT(*) AS total_tenants FROM tenant");
$total_tenants = $tenant_result->fetch_assoc()['total_tenants'] ?? 0;

$total_stalls_result = $conn->query("SELECT COUNT(*) AS total_stalls FROM stall");
$total_stalls = $total_stalls_result->fetch_assoc()['total_stalls'] ?? 0;

$available_stalls_result = $conn->query("SELECT COUNT(*) AS available_stalls FROM stall WHERE availability = 'available'");
$available_stalls = $available_stalls_result->fetch_assoc()['available_stalls'] ?? 0;

$income_result = $conn->query("SELECT SUM(payment_amount) AS total_income FROM payment WHERE MONTH(payment_date) = MONTH(CURRENT_DATE()) AND YEAR(payment_date) = YEAR(CURRENT_DATE())");
$total_income = $income_result->fetch_assoc()['total_income'] ?? 0;

// Distinct years only
$yearsQuery = $conn->query("SELECT DISTINCT YEAR(payment_date) AS year FROM payment ORDER BY year DESC");

// Monthly income
$monthlyIncomeQuery = "SELECT SUM(payment_amount) AS total_income, MONTH(payment_date) AS month, YEAR(payment_date) AS year FROM payment";
$conditions = [];

$selected_month = $_GET['month'] ?? '';
$selected_year = $_GET['year'] ?? '';

if (!empty($selected_month)) {
    $conditions[] = "MONTH(payment_date) = " . intval($selected_month);
}
if (!empty($selected_year)) {
    $conditions[] = "YEAR(payment_date) = " . intval($selected_year);
}
if ($conditions) {
    $monthlyIncomeQuery .= " WHERE " . implode(" AND ", $conditions);
}
$monthlyIncomeQuery .= " GROUP BY YEAR(payment_date), MONTH(payment_date) ORDER BY YEAR(payment_date) DESC, MONTH(payment_date) DESC";
$monthlyIncome = $conn->query($monthlyIncomeQuery);

// Yearly income
$yearlyIncomeQuery = "SELECT SUM(payment_amount) AS total_income, YEAR(payment_date) AS year FROM payment";
if (!empty($selected_year)) {
    $yearlyIncomeQuery .= " WHERE YEAR(payment_date) = " . intval($selected_year);
}
$yearlyIncomeQuery .= " GROUP BY YEAR(payment_date) ORDER BY year DESC";
$yearlyIncome = $conn->query($yearlyIncomeQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Report</title>
    <!-- Fixed CDN URLs -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #eef2f7;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 95%;
            max-width: 1300px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
        }

        h2 {
            text-align: center;
            color: #2c3e50;
            font-size: 2rem;
            margin-bottom: 10px;
        }

        h3 {
            color: #34495e;
            border-bottom: 2px solid #eee;
            padding-bottom: 8px;
            margin-top: 40px;
            font-size: 1.4rem;
        }

        .filter-container {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin: 25px 0;
            align-items: center;
        }

        .filter-container form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
        }

        .filter-container select,
        .filter-container button {
            padding: 10px 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
            background: #fff;
            transition: 0.2s ease;
            min-width: 150px;
        }

        .filter-container button {
            background: #3498db;
            color: #fff;
            border: none;
            cursor: pointer;
        }

        .filter-container button:hover {
            background: #2980b9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 1rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #2c3e50;
            color: #fff;
            font-weight: 600;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover {
            background-color: #f9fbfd;
        }

        .export-btns {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            margin: 30px 0;
        }

        .btn {
            padding: 12px 20px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 6px rgba(39, 174, 96, 0.3);
        }

        .btn:hover {
            background-color: #1e8449;
            transform: translateY(-2px);
        }

        .metric-boxes {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 20px;
            margin-top: 30px;
        }

        .metric {
            flex: 1 1 220px;
            background: #f9fafc;
            border-left: 5px solid #3498db;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.06);
            text-align: center;
            transition: 0.3s ease;
        }

        .metric:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.1);
        }

        .metric h4 {
            margin: 0;
            font-size: 1.1rem;
            color: #555;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }

        .metric p {
            font-size: 1.8rem;
            font-weight: bold;
            margin-top: 10px;
            color: #2c3e50;
        }

        .chart-container {
            margin-top: 50px;
            background: #fff;
            padding: 20px;
            border-radius: 14px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
            display: none; /* Hide chart for print */
        }

        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .container {
                box-shadow: none;
                border-radius: 0;
                padding: 10px;
                max-width: 100%;
                margin: 0;
            }
            .filter-container,
            .export-btns,
            .chart-container {
                display: none !important;
            }
            table {
                box-shadow: none;
            }
            .metric-boxes {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>📊 Income Report</h2>

    <!-- Filters -->
    <div class="filter-container">
        <form method="GET" action="">
            <select name="month">
                <option value="">Select Month</option>
                <?php for ($m = 1; $m <= 12; $m++): ?>
                    <option value="<?= $m ?>" <?= ($selected_month == $m) ? 'selected' : '' ?>>
                        <?= date('F', mktime(0, 0, 0, $m, 10)) ?>
                    </option>
                <?php endfor; ?>
            </select>
            <select name="year">
                <option value="">Select Year</option>
                <?php while ($row = $yearsQuery->fetch_assoc()): ?>
                    <option value="<?= $row['year'] ?>" <?= ($selected_year == $row['year']) ? 'selected' : '' ?>>
                        <?= $row['year'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Generate Report</button>
        </form>
    </div>

    <!-- Monthly Income Table -->
    <h3>📅 Monthly Income</h3>
    <table>
        <thead>
            <tr><th>Month</th><th>Year</th><th>Total Income</th></tr>
        </thead>
        <tbody>
            <?php if ($monthlyIncome && $monthlyIncome->num_rows > 0): ?>
                <?php while ($row = $monthlyIncome->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars(date('F', mktime(0, 0, 0, $row['month'], 10))) ?></td>
                        <td><?= htmlspecialchars($row['year']) ?></td>
                        <td>₱<?= number_format($row['total_income'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="3">No data available for the selected filter.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Yearly Income Table -->
    <h3>📆 Yearly Income</h3>
    <table>
        <thead>
            <tr><th>Year</th><th>Total Income</th></tr>
        </thead>
        <tbody>
            <?php if ($yearlyIncome && $yearlyIncome->num_rows > 0): ?>
                <?php while ($row = $yearlyIncome->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['year']) ?></td>
                        <td>₱<?= number_format($row['total_income'], 2) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="2">No yearly data found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Print Button Only -->
    <div class="export-btns">
        <button onclick="window.print()" class="btn"><i class="fas fa-print"></i> Print Report</button>
    </div>

    <!-- Metric Summary -->
    <div class="metric-boxes">
        <div class="metric"><h4><i class="fas fa-users"></i> Total Tenants</h4><p><?= $total_tenants ?></p></div>
        <div class="metric"><h4><i class="fas fa-store"></i> Total Stalls</h4><p><?= $total_stalls ?></p></div>
        <div class="metric"><h4><i class="fas fa-check-circle"></i> Available Stalls</h4><p><?= $available_stalls ?></p></div>
        <div class="metric"><h4><i class="fas fa-wallet"></i> Current Month Income</h4><p>₱<?= number_format($total_income, 2) ?></p></div>
    </div>

    <!-- Chart (hidden on print) -->
    <div class="chart-container">
        <canvas id="monthlyIncomeChart" height="100"></canvas>
    </div>
</div>

<?php
$chartLabels = [];
$chartData = [];
if ($monthlyIncome) {
    $monthlyIncome->data_seek(0);
    while ($row = $monthlyIncome->fetch_assoc()) {
        $chartLabels[] = date('F Y', mktime(0, 0, 0, $row['month'], 10));
        $chartData[] = (float)$row['total_income'];
    }
}
?>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('monthlyIncomeChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
             {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Monthly Income (₱)',
                    data: <?= json_encode($chartData) ?>,
                    backgroundColor: '#3498db',
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>

<?php $conn->close(); ?>