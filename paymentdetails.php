<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];

// Fetch tenant payment details
$sql = "SELECT tenant.stall_no_rented, tenant.name, stall.stall_section, tenant.start_date, 
               stall.yearly_price, IFNULL(SUM(payment.payment_amount), 0) AS total_paid
        FROM tenant 
        JOIN stall ON tenant.stall_no_rented = stall.stall_no
        LEFT JOIN payment ON tenant.id = payment.tenant_id
        WHERE tenant.id = ?
        GROUP BY tenant.id";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$tenant = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f4;
        }
        .back-button {
            display: inline-block;
            margin: 20px;
            text-decoration: none;
            color: #123458;
            font-weight: bold;
            font-size: 16px;
            background: #e4e9f0;
            padding: 10px 16px;
            border-radius: 6px;
            transition: background-color 0.3s, color 0.3s;
        }
        .back-button i {
            margin-right: 8px;
        }
        .back-button:hover {
            background-color: #123458;
            color: white;
        }
        .container {
            width: 80%;
            max-width: 800px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h1 {
            color: #123458;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        th {
            background-color: #123458;
            color: white;
        }
    </style>
</head>
<body>
    <a href="tenantUI.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <div class="container">
        <h1>Payment Details</h1>
        <table>
            <thead>
                <tr>
                    <th>Stall No. Rented</th>
                    <th>Name</th>
                    <th>Stall Section</th>
                    <th>Date Admitted</th>
                    <th>Yearly Balance</th>
                    <th>Total Paid</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?= htmlspecialchars($tenant['stall_no_rented']) ?></td>
                    <td><?= htmlspecialchars($tenant['name']) ?></td>
                    <td><?= htmlspecialchars($tenant['stall_section']) ?></td>
                    <td><?= date('Y-m-d', strtotime($tenant['start_date'])) ?></td>
                    <td><?= number_format($tenant['yearly_price'], 2) ?></td>
                    <td><?= number_format($tenant['total_paid'], 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</body>
</html>
