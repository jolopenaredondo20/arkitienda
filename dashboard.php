<?php  
// dashboard.php

// Database connection
$conn = new mysqli('localhost', 'root', '', 'rental');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch total tenants
$tenant_result = $conn->query("SELECT COUNT(*) AS total_tenants FROM tenant");
$total_tenants = $tenant_result->fetch_assoc()['total_tenants'];

// Fetch total stalls
$total_stalls_result = $conn->query("SELECT COUNT(*) AS total_stalls FROM stall");
$total_stalls = $total_stalls_result->fetch_assoc()['total_stalls'];

// Fetch available stalls
$available_stalls_result = $conn->query("SELECT COUNT(*) AS available_stalls FROM stall WHERE availability = 'available'");
$available_stalls = $available_stalls_result->fetch_assoc()['available_stalls'];

// Get the selected year or set default
$selected_year = isset($_GET['year']) ? $_GET['year'] : date('Y');

// Fetch monthly income data for the selected year
$income_result = $conn->query("SELECT MONTH(payment_date) AS month, SUM(payment_amount) AS total_income 
                               FROM payment 
                               WHERE YEAR(payment_date) = $selected_year 
                               GROUP BY MONTH(payment_date) ORDER BY MONTH(payment_date)");

$income_data = [];
while ($row = $income_result->fetch_assoc()) {
    $income_data[$row['month']] = $row['total_income'];
}

// Prepare income data for the chart (months and income)
$months = [];
$income_values = [];
for ($i = 1; $i <= 12; $i++) {
    $months[] = date('F', mktime(0, 0, 0, $i, 10)); // Convert month number to month name
    $income_values[] = isset($income_data[$i]) ? $income_data[$i] : 0; // Use 0 if no income data for that month
}

// Fetch total income for the selected year
$total_income_result = $conn->query("SELECT SUM(payment_amount) AS total_income 
                                    FROM payment 
                                    WHERE YEAR(payment_date) = $selected_year");
$total_income = $total_income_result->fetch_assoc()['total_income'];

// Fetch available years from the payment table
$yearsQuery = $conn->query("SELECT DISTINCT YEAR(payment_date) AS year FROM payment ORDER BY year DESC");

// Fetch yearly income data
$yearly_income_result = $conn->query("SELECT YEAR(payment_date) AS year, SUM(payment_amount) AS total_income 
                                      FROM payment 
                                      GROUP BY YEAR(payment_date) 
                                      ORDER BY YEAR(payment_date)");

$yearly_income_data = [];
while ($row = $yearly_income_result->fetch_assoc()) {
    $yearly_income_data[$row['year']] = $row['total_income'];
}

// Prepare the data for the yearly income chart
$years = array_keys($yearly_income_data); // Get all unique years
$yearly_income_values = array_values($yearly_income_data); // Corresponding income for each year
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ARKI.TIENDA</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #f7f9fc;
            color: #1a1a1a;
            padding: 20px;
        }

        .content {
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }

        .stat-box {
            flex: 1 1 200px;
            background: #ffffff;
            border: 1px solid #e0e6ed;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .stat-box h3 {
            font-size: 16px;
            font-weight: 600;
            color: #333;
            margin-bottom: 10px;
        }

        .stat-box p {
            font-size: 20px;
            font-weight: bold;
            color: #006400;
        }

        .dual-chart {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .chart-box {
            flex: 1 1 45%;
            background-color: #fff;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            min-width: 300px;
        }

        .chart-box h3 {
            text-align: center;
            margin-bottom: 20px;
        }

        canvas {
            width: 100% !important;
            height: 350px !important;
        }

        footer {
            text-align: center;
            margin-top: 40px;
            color: #666;
            font-size: 14px;
        }

        select, button {
            padding: 10px 15px;
            font-size: 16px;
            margin: 0 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        select {
            background-color: #f1f3f5;
            color: #333;
        }

        button {
            background-color: #0047AB;
            color: #fff;
            cursor: pointer;
            border: none;
        }

        button:hover {
            background-color: #003080;
        }

        .date-time {
            font-size: 18px;
            color: #333;
            text-align: center;
            margin-top: 20px;
        }

    </style>
</head>

<body>
    <div class="content">

        <div class="dashboard-stats">
            <div class="stat-box">
                <h3>🧑‍💼 Total Tenants</h3>
                <p><?php echo $total_tenants; ?></p>
            </div>
            <div class="stat-box">
                <h3>🏠 Total Stalls</h3>
                <p><?php echo $total_stalls; ?></p>
            </div>
            <div class="stat-box">
                <h3>🏠 Available Stalls</h3>
                <p><?php echo $available_stalls; ?></p>
            </div>
            <div class="stat-box">
                <h3>💰 Monthly Income</h3>
                <p>₱<?php echo number_format($total_income, 2); ?></p>
            </div>
        </div>

        <!-- Dual chart section -->
        <div class="dual-chart">
            <div class="chart-box">
                <h3>📊 Monthly Income (<?= $selected_year ?>)</h3>
                <canvas id="incomeChart"></canvas>
            </div>
            <div class="chart-box">
                <h3>📊 Yearly Income Overview</h3>
                <canvas id="yearlyIncomeChart"></canvas>
            </div>
        </div>

        <!-- Date and Time Display -->
        <div class="date-time">
            <p id="currentDateTime"></p>
        </div>

    </div>

    <script>
        // Function to update the date and time
        function updateDateTime() {
            const now = new Date();
            const dateTimeString = now.toLocaleString();
            document.getElementById('currentDateTime').textContent = dateTimeString;
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();

        // Monthly income line chart using Chart.js
        var ctx1 = document.getElementById('incomeChart').getContext('2d');
        var incomeChart = new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($months); ?>,
                datasets: [{
                    label: 'Monthly Income',
                    data: <?php echo json_encode($income_values); ?>,
                    borderColor: 'rgb(76, 175, 80)',
                    backgroundColor: 'rgba(76, 175, 80, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return '₱' + tooltipItem.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Yearly income bar chart using Chart.js
        var ctx2 = document.getElementById('yearlyIncomeChart').getContext('2d');
        var yearlyIncomeChart = new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($years); ?>, // Use years as labels
                datasets: [{
                    label: 'Yearly Income',
                    data: <?php echo json_encode($yearly_income_values); ?>, // Yearly income values
                    backgroundColor: 'rgba(33, 150, 243, 0.6)',
                    borderColor: 'rgb(33, 150, 243)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return '₱' + tooltipItem.raw.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
<?php
$conn->close();
?>
