<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle tenant addition
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_tenant'])) {
    $name = $_POST['name'];
    $contact_NO = $_POST['contact_NO'];
    $address = $_POST['address'];
    $stall_no_rented = $_POST['stall_no_rented'];
    $stall_section = $_POST['stall_section'];
    $yearly_price = floatval($_POST['yearly_price']);
    $start_date = date('Y-m-d');

    $sql = "INSERT INTO tenant (name, contact_NO, address, stall_no_rented, stall_section, yearly_price, start_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssids", $name, $contact_NO, $address, $stall_no_rented, $stall_section, $yearly_price, $start_date);
        if ($stmt->execute()) {
            echo "<script>alert('Tenant added successfully!'); window.location.href='billing.php';</script>";
        } else {
            echo "Error adding tenant: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Handle payment submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['tenant_id'])) {
    $tenant_id = intval($_POST['tenant_id']);
    $payment_amount = floatval($_POST['payment_amount']);
    $payment_method = $_POST['payment_method'];
    $payment_date = date('Y-m-d');

    $sql = "INSERT INTO payment (tenant_id, payment_amount, payment_date, payment_method) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("idss", $tenant_id, $payment_amount, $payment_date, $payment_method);
        if ($stmt->execute()) {
            echo "<script>alert('Payment received successfully!'); window.location.href='billing.php';</script>";
        } else {
            echo "Error processing payment: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Fetch tenant payment data
$sql = "
SELECT 
    tenant.id, 
    tenant.name, 
    tenant.contact_NO, 
    tenant.start_date, 
    tenant.address, 
    tenant.stall_no_rented, 
    stall.stall_section, 
    stall.yearly_price,
    (stall.yearly_price / 12) AS monthly_price,
    IFNULL(SUM(CASE 
        WHEN MONTH(payment.payment_date) = MONTH(CURDATE()) 
         AND YEAR(payment.payment_date) = YEAR(CURDATE()) 
        THEN payment.payment_amount ELSE 0 END), 0) AS total_paid_this_month,
    ((stall.yearly_price / 12) - IFNULL(SUM(CASE 
        WHEN MONTH(payment.payment_date) = MONTH(CURDATE()) 
         AND YEAR(payment.payment_date) = YEAR(CURDATE()) 
        THEN payment.payment_amount ELSE 0 END), 0)) AS monthly_balance
FROM tenant 
JOIN stall ON tenant.stall_no_rented = stall.stall_no
LEFT JOIN payment ON tenant.id = payment.tenant_id
GROUP BY tenant.id
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Billing</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #eef1f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        }

        .header {
            background-color: #1e3d59;
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 10px 10px 0 0;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            font-size: 14px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 12px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background-color: #1e3d59;
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .btn {
            padding: 8px 14px;
            color: white;
            background-color: #3498db;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            cursor: pointer;
            transition: background 0.3s;
        }

        .btn:hover {
            background-color: #2980b9;
        }

        .empty-message {
            text-align: center;
            color: #888;
            padding: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Tenant Billing</h1>
    </div>

    <table>
        <thead>
        <tr>
            <th>Stall No.</th>
            <th>Name</th>
            <th>Date Admitted</th>
            <th>Monthly Rent</th>
            <th>Paid This Month</th>
            <th>Monthly Balance</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                    <td>{$row["stall_no_rented"]}</td>
                    <td>{$row["name"]}</td>
                    <td>" . date('Y-m-d', strtotime($row["start_date"])) . "</td>
                    <td>₱" . number_format($row["monthly_price"], 2) . "</td>
                    <td>₱" . number_format($row["total_paid_this_month"], 2) . "</td>
                    <td>₱" . number_format($row["monthly_balance"], 2) . "</td>
                    <td>
                        <form method='GET' action='bills.php'>
                            <input type='hidden' name='tenant_id' value='{$row["id"]}'>
                            <button type='submit' class='btn'>View Bills</button>
                        </form>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='7' class='empty-message'>No tenants found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php $conn->close(); ?>
