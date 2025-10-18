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

// Check if tenant_id is set (assuming tenant logs in and has an ID)
if (isset($_GET['tenant_id']) && is_numeric($_GET['tenant_id'])) {
    $tenant_id = intval($_GET['tenant_id']);

    // Fetch tenant details
    $query = "SELECT name FROM tenant WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();
    $stmt->close();

    if (!$tenant) {
        die("Tenant not found.");
    }

    // Fetch received receipts history
    $receipt_query = "SELECT payment_amount, payment_method, payment_date FROM payment WHERE tenant_id = ? ORDER BY payment_date DESC";
    $receipt_stmt = $conn->prepare($receipt_query);
    $receipt_stmt->bind_param("i", $tenant_id);
    $receipt_stmt->execute();
    $receipt_result = $receipt_stmt->get_result();
} else {
    die("Invalid tenant ID.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Received Receipts</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #007bff; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Received Receipts</h1>
        <p><strong>Tenant Name:</strong> <?php echo htmlspecialchars($tenant['name']); ?></p>

        <h2>Receipt History</h2>
        <table>
            <thead>
                <tr>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Payment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($receipt_result->num_rows > 0) {
                    while ($row = $receipt_result->fetch_assoc()) {
                        echo "<tr>
                                <td>" . number_format($row['payment_amount'], 2) . "</td>
                                <td>" . htmlspecialchars($row['payment_method']) . "</td>
                                <td>" . date('Y-m-d', strtotime($row['payment_date'])) . "</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>No receipts found.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>

<?php 
$receipt_stmt->close();
$conn->close();
?>
