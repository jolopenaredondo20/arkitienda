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

// Check if tenant_id is set
if (!isset($_GET['tenant_id']) || !is_numeric($_GET['tenant_id'])) {
    die("Invalid request.");
}
$tenant_id = intval($_GET['tenant_id']);

// Fetch tenant details
$query = "SELECT tenant.id, tenant.name, tenant.stall_no_rented, tenant.start_date, stall.stall_section, stall.yearly_price
          FROM tenant
          JOIN stall ON tenant.stall_no_rented = stall.stall_no
          WHERE tenant.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$tenant = $result->fetch_assoc();
$stmt->close();

if (!$tenant) die("Tenant not found.");

// Calculate monthly price
$monthly_price = $tenant['yearly_price'] / 12;

// Fetch the latest payment amount
$payment_query = "SELECT payment_amount, payment_date, payment_method 
                  FROM payment 
                  WHERE tenant_id = ? 
                  ORDER BY payment_date DESC 
                  LIMIT 1";
$payment_stmt = $conn->prepare($payment_query);
$payment_stmt->bind_param("i", $tenant_id);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
$payment = $payment_result->fetch_assoc();
$payment_stmt->close();

if (!$payment) {
    $amount_paid = 0;
    $payment_date = '';
    $payment_method = 'N/A';
} else {
    $amount_paid = $payment['payment_amount'];
    $payment_date = $payment['payment_date'];
    $payment_method = $payment['payment_method'];
}

// Fetch total payments made by the tenant
$total_payment_query = "SELECT SUM(payment_amount) AS total_paid 
                        FROM payment 
                        WHERE tenant_id = ?";
$total_payment_stmt = $conn->prepare($total_payment_query);
$total_payment_stmt->bind_param("i", $tenant_id);
$total_payment_stmt->execute();
$total_payment_result = $total_payment_stmt->get_result();
$total_payment = $total_payment_result->fetch_assoc();
$total_paid = $total_payment['total_paid'] ? $total_payment['total_paid'] : 0;

// Determine due date based on start date
$admissionDay = date('d', strtotime($tenant['start_date']));
$currentMonthDue = date('Y-m-' . str_pad($admissionDay, 2, '0', STR_PAD_LEFT));
$lastDayOfMonth = date('Y-m-t');
if ($currentMonthDue > $lastDayOfMonth) {
    $currentMonthDue = $lastDayOfMonth;
}
$overdue_date = date('Y-m-d', strtotime($currentMonthDue . ' +3 days'));

// Calculate penalty only if current date is past overdue date
$today = date('Y-m-d');
$penalty = ($today > $overdue_date) ? $monthly_price * 0.25 : 0;

// Determine if penalty is applicable and paid
$has_penalty = ($penalty > 0);
$penalty_paid = false;

if ($has_penalty) {
    // Check if total paid covers monthly + penalty
    if ($total_paid >= ($monthly_price + $penalty)) {
        $penalty_paid = true;
        $penalty = 0; // Hide penalty amount if fully covered
    }
}

// Remaining balance
$remaining_balance = max(($monthly_price + $penalty) - $total_paid, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Receipt - ARKI.TIENDA</title>
<style>
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        background:#f8f8f8; 
        margin:0; 
        padding:20px; 
        color:#333; 
    }
    .receipt { 
        max-width: 380px; 
        margin: auto; 
        background: #fff; 
        padding: 25px 20px; 
        border-radius: 12px; 
        box-shadow: 0 3px 12px rgba(0,0,0,0.08); 
        font-size: 14px; 
    }
    .header { 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        margin-bottom: 20px; 
    }
    .header img { 
        height: 50px; 
        margin-right: 12px; 
    }
    .header h1 { 
        font-size: 22px; 
        font-weight: 600; 
        margin: 0; 
        letter-spacing: 0.5px; 
    }
    h2 { 
        text-align: center; 
        font-size: 16px; 
        font-weight: 400; 
        color: #555; 
        margin-bottom: 20px; 
    }
    .info p { 
        margin: 6px 0; 
        line-height: 1.5; 
    }
    .info strong { 
        color: #111; 
    }
    .signature { 
        margin-top: 25px; 
        text-align: center; 
    }
    .signature p { 
        margin: 4px 0; 
        font-size: 13px; 
    }
    .signature .line { 
        border-top: 1px solid #333; 
        width: 60%; 
        margin: 6px auto; 
    }
    .btn { 
        padding: 8px 20px; 
        background-color: #4a90e2; 
        color: #fff; 
        border: none; 
        border-radius: 6px; 
        cursor: pointer; 
        font-size: 13px; 
        margin-top: 20px; 
        display: block; 
        text-align: center; 
    }
    .btn:hover { 
        background-color: #357ab8; 
    }
    
    /* Penalty Status Styling */
    .penalty-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 13px;
        margin-left: 8px;
    }
    .penalty-no {
        background-color: #d4edda;
        color: #155724;
    }
    .penalty-yes-unpaid {
        background-color: #f8d7da;
        color: #721c24;
    }
    .penalty-yes-paid {
        background-color: #d1ecf1;
        color: #0c5460;
    }
    
    @media print {
        body { background: #fff; padding: 0; }
        .receipt { box-shadow: none; margin: 0 auto; width: 380px; }
        .btn { display: none; }
    }
</style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <img src="www.jpg" alt="Logo">
        <h1>ARKI.TIENDA</h1>
    </div>
    <h2>Payment Receipt</h2>

    <div class="info">
        <p><strong>Tenant Name:</strong> <?= htmlspecialchars($tenant['name']); ?></p>
        <p><strong>Stall Rented:</strong> <?= htmlspecialchars($tenant['stall_no_rented']) . " (" . htmlspecialchars($tenant['stall_section']) . ")"; ?></p>
        <p><strong>Monthly Fee:</strong> ₱<?= number_format($monthly_price, 2); ?></p>
        
        <!-- Penalty Status Display -->
        <p><strong>Penalty Status:</strong> 
            <?php if (!$has_penalty): ?>
                <span class="penalty-status penalty-no">No Penalty</span>
            <?php else: ?>
                <?php if ($penalty_paid): ?>
                    <span class="penalty-status penalty-yes-paid">Penalty Paid</span>
                <?php else: ?>
                    <span class="penalty-status penalty-yes-unpaid">Penalty Due</span>
                <?php endif; ?>
            <?php endif; ?>
        </p>
        
        <?php if ($has_penalty && !$penalty_paid): ?>
            <p><strong>Penalty Amount:</strong> ₱<?= number_format($monthly_price * 0.25, 2); ?> (25% of monthly fee)</p>
        <?php endif; ?>
        
        <p><strong>Total Balance:</strong> ₱<?= number_format($remaining_balance, 2); ?></p>
        <p><strong>Amount Paid:</strong> ₱<?= number_format($amount_paid, 2); ?> (<?= htmlspecialchars($payment_method); ?>)</p>
        <p><strong>Date & Time:</strong> <?= $payment_date ? date("F d, Y h:i A", strtotime($payment_date)) : 'N/A'; ?></p>
    </div>

    <div class="signature">
        <p>Admin: JOLO PEÑAREDONDO</p>
        <div class="line"></div>
        <p>Signature</p>
    </div>

    <button class="btn" onclick="window.print()">Print Receipt</button>
</div>
</body>
</html>

<?php $conn->close(); ?>