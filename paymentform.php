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

// Check if tenant_id is set and is valid
if (isset($_GET['tenant_id']) && is_numeric($_GET['tenant_id'])) {
    $tenant_id = intval($_GET['tenant_id']);

    // Fetch tenant details
    $query = "SELECT tenant.id, tenant.name, tenant.contact_NO, tenant.start_date, tenant.address, tenant.stall_no_rented, 
                     stall.stall_section, stall.yearly_price
              FROM tenant
              JOIN stall ON tenant.stall_no_rented = stall.stall_no
              WHERE tenant.id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $tenant = $result->fetch_assoc();
    $stmt->close();

    if (!$tenant) {
        die("Tenant not found.");
    }

    // Calculate the monthly balance
    $monthly_price = $tenant['yearly_price'] / 12;
    $current_date = new DateTime();
    $start_date = new DateTime($tenant['start_date']);
    $interval = $current_date->diff($start_date);

    // Check for penalty (if overdue more than 1 month)
    $penalty = 0;
    if ($interval->m >= 1) {
        $penalty = $monthly_price * 0.25; // 25% of monthly price
    }

    // Fetch total payments made for the current month
    $current_month = date('Y-m');
    $payment_query = "SELECT SUM(payment_amount) AS total_paid FROM payment WHERE tenant_id = ? AND DATE_FORMAT(payment_date, '%Y-%m') = ?";
    $payment_stmt = $conn->prepare($payment_query);
    $payment_stmt->bind_param("is", $tenant_id, $current_month);
    $payment_stmt->execute();
    $payment_result = $payment_stmt->get_result();
    $payment_data = $payment_result->fetch_assoc();
    $total_paid = $payment_data['total_paid'] ?? 0;

    $remaining_balance = max($monthly_price - $total_paid, 0);
    $remaining_balance += $penalty; // Add penalty if applicable
} else {
    die("Invalid tenant ID.");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Tenant Payment</title>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 20px; border-radius: 5px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; }
        .btn { padding: 10px 15px; color: white; background-color: #007bff; border-radius: 5px; cursor: pointer; }
        .btn:hover { background-color: #0056b3; }
        label { display: block; margin: 10px 0 5px; }
        input, select { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #007bff; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h1>Tenant Payment Approval</h1>

    <form method="POST" action="approve_payment.php" enctype="multipart/form-data">
        <input type="hidden" name="tenant_id" value="<?php echo $tenant['id']; ?>">

        <label for="tenant_name">Tenant Name:</label>
        <input type="text" name="tenant_name" value="<?php echo htmlspecialchars($tenant['name']); ?>" disabled>

        <label for="tenant_address">Tenant Address:</label>
        <input type="text" name="tenant_address" value="<?php echo htmlspecialchars($tenant['address']); ?>" disabled>

        <label for="contact_no">Contact No.:</label>
        <input type="text" name="contact_no" value="<?php echo htmlspecialchars($tenant['contact_NO']); ?>" disabled>

        <label for="stall_section">Stall Section Rented:</label>
        <input type="text" name="stall_section" value="<?php echo htmlspecialchars($tenant['stall_section']); ?>" disabled>

        <label for="payment_date">Date of Payment:</label>
        <input type="date" name="payment_date" value="<?php echo date('Y-m-d'); ?>" required>

        <label for="payment_amount">Payment Amount:</label>
        <input type="number" name="payment_amount" value="<?php echo number_format($remaining_balance, 2); ?>" step="0.01" required>

        <label for="payment_method">Payment Method:</label>
        <select name="payment_method" required>
            <option value="G-Cash">G-Cash</option>
            <option value="PayMaya">PayMaya</option>
            <option value="Palawan Pay">Palawan Pay</option>
        </select>

        <label for="payment_screenshot">Upload Payment Screenshot:</label>
        <input type="file" name="payment_screenshot" accept="image/*" required>

        <button type="submit" class="btn">Approve Payment</button>
    </form>
</div>

</body>
</html>
