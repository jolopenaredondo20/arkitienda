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

// Pagination setup
$transactions_per_page = 10;  // Number of transactions per page
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$start_from = ($page - 1) * $transactions_per_page;

// Search functionality
$search_query = '';
if (isset($_POST['search'])) {
    $search_query = mysqli_real_escape_string($conn, $_POST['search_term']);
    $query = "SELECT * FROM payments WHERE tenant_name LIKE '%$search_query%' OR payment_method LIKE '%$search_query%' ORDER BY payment_date DESC LIMIT $start_from, $transactions_per_page";
} else {
    $query = "SELECT * FROM payments ORDER BY payment_date DESC LIMIT $start_from, $transactions_per_page";
}

$result = $conn->query($query);

// Count total records for pagination
$total_query = "SELECT COUNT(*) FROM payments";
$total_result = $conn->query($total_query);
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $transactions_per_page);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History | ARKI.TIENDA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f4f9;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            font-weight: 600;
            margin-bottom: 20px;
            color: #003092;
        }
        .search-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        input[type="text"] {
            padding: 10px;
            width: 70%;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        button[type="submit"] {
            padding: 10px 20px;
            background-color: #003092;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button[type="submit"]:hover {
            background-color: #001f5b;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table, .table th, .table td {
            border: 1px solid #ddd;
            text-align: center;
        }
        .table th, .table td {
            padding: 12px;
        }
        .table th {
            background-color: #003092;
            color: white;
        }
        .btn-details {
            padding: 6px 12px;
            background-color: #f39c12;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn-details:hover {
            background-color: #e67e22;
        }
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        .pagination a {
            padding: 8px 15px;
            margin: 0 5px;
            background-color: #003092;
            color: white;
            border-radius: 5px;
            text-decoration: none;
        }
        .pagination a:hover {
            background-color: #001f5b;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>💳 Payment Transaction History</h2>

    <!-- Search Form -->
    <div class="search-box">
        <form method="POST" action="">
            <input type="text" name="search_term" placeholder="Search by tenant name or payment method..." value="<?php echo $search_query; ?>">
            <button type="submit" name="search">🔍 Search</button>
        </form>
    </div>

    <!-- Transactions Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Transaction ID</th>
                <th>Tenant Name</th>
                <th>Amount (PHP)</th>
                <th>Payment Method</th>
                <th>Payment Date</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['transaction_id'] . "</td>";
                    echo "<td>" . $row['tenant_name'] . "</td>";
                    echo "<td>" . number_format($row['amount'], 2) . "</td>";
                    echo "<td>" . $row['payment_method'] . "</td>";
                    echo "<td>" . date("F j, Y, g:i a", strtotime($row['payment_date'])) . "</td>";
                    echo "<td><a href='transaction_details.php?id=" . $row['transaction_id'] . "' class='btn-details'>View Details</a></td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='6'>No transactions found.</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <div class="pagination">
        <?php
        for ($i = 1; $i <= $total_pages; $i++) {
            echo "<a href='transactionhistory.php?page=" . $i . "'>" . $i . "</a>";
        }
        ?>
    </div>

    <!-- Back to Dashboard Button -->
    <div style="text-align: center; margin-top: 20px;">
        <a href="tenantUI.php" class="btn" style="display: inline-block; background-color: #003092; color: white; padding: 12px 20px; border-radius: 8px; text-decoration: none;">🏠 Back to Dashboard</a>
    </div>
</div>


</body>
</html>
