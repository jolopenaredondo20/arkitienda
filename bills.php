<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

// Check if tenant_id is set
if (!isset($_GET['tenant_id']) || !is_numeric($_GET['tenant_id'])) die("Invalid tenant ID.");
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

if (!$tenant) die("Tenant not found.");

// Monthly price
$monthly_price = $tenant['yearly_price'] / 12;

// Due dates
$admissionDay = date('d', strtotime($tenant['start_date']));
$currentMonthDue = date('Y-m-' . str_pad($admissionDay, 2, '0', STR_PAD_LEFT));
$lastDayOfMonth = date('Y-m-t');
if ($currentMonthDue > $lastDayOfMonth) $currentMonthDue = $lastDayOfMonth;
$overdue_date = date('Y-m-d', strtotime($currentMonthDue . ' +3 days'));

// Penalty
$penalty = (date('Y-m-d') > $overdue_date) ? $monthly_price * 0.25 : 0;

// Total paid this month
$current_month = date('Y-m');
$payment_query = "SELECT SUM(payment_amount) AS total_paid 
                  FROM payment 
                  WHERE tenant_id = ? 
                  AND DATE_FORMAT(payment_date, '%Y-%m') = ?";
$payment_stmt = $conn->prepare($payment_query);
$payment_stmt->bind_param("is", $tenant_id, $current_month);
$payment_stmt->execute();
$payment_result = $payment_stmt->get_result();
$payment_data = $payment_result->fetch_assoc();
$total_paid = $payment_data['total_paid'] ?? 0;

// Remove penalty if already paid
if ($total_paid >= ($monthly_price + $penalty)) $penalty = 0;

// Remaining balance
$remaining_balance = max(($monthly_price + $penalty) - $total_paid, 0);

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_amount = isset($_POST['payment_amount']) ? floatval($_POST['payment_amount']) : 0;
    $payment_method = isset($_POST['payment_method']) ? $_POST['payment_method'] : '';
    $reference_no = isset($_POST['reference_no']) ? trim($_POST['reference_no']) : null;

    if ($payment_amount > 0 && !empty($payment_method)) {
        if (in_array($payment_method, ["G-Cash", "PayMaya", "Palawan Pay"]) && empty($reference_no)) {
            echo "<script>alert('Please enter Reference Number for $payment_method.');</script>";
        } elseif ($payment_amount > $remaining_balance) {
            echo "<script>alert('Payment amount cannot exceed the remaining balance.');</script>";
        } else {
            $payment_date = date('Y-m-d');
            $insert_query = "INSERT INTO payment (tenant_id, payment_amount, payment_method, reference_no, payment_date) 
                             VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("idsss", $tenant_id, $payment_amount, $payment_method, $reference_no, $payment_date);
            if ($stmt->execute()) {
                echo "<script>alert('Payment submitted successfully!'); window.location.href=window.location.href;</script>";
            }
            $stmt->close();
            $remaining_balance -= $payment_amount;
        }
    } else {
        echo "<script>alert('Please enter a valid payment amount and select a payment method.');</script>";
    }
}

// Fetch history
$history_query = "SELECT payment_amount, payment_method, reference_no, payment_date 
                  FROM payment 
                  WHERE tenant_id = ? 
                  ORDER BY payment_date DESC";
$history_stmt = $conn->prepare($history_query);
$history_stmt->bind_param("i", $tenant_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tenant Billing</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    :root {
      --primary: #4361ee;
      --primary-dark: #3a56d4;
      --secondary: #7209b7;
      --success: #4cc9f0;
      --light: #f8f9fa;
      --dark: #212529;
      --gray: #6c757d;
      --light-gray: #e9ecef;
      --border: #dee2e6;
      --shadow: 0 4px 20px rgba(0,0,0,0.08);
      --radius: 12px;
    }
    
    * { 
      box-sizing: border-box; 
      margin: 0;
      padding: 0;
    }
    
    body { 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
      background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
      color: var(--dark);
      line-height: 1.6;
      padding: 20px;
      min-height: 100vh;
    }
    
    .container { 
      max-width: 800px; 
      margin: 0 auto; 
      background: white; 
      padding: 30px; 
      border-radius: var(--radius); 
      box-shadow: var(--shadow);
    }
    
    header {
      text-align: center;
      margin-bottom: 25px;
      padding-bottom: 20px;
      border-bottom: 1px solid var(--border);
    }
    
    h1 {
      color: var(--primary);
      font-size: 2.2rem;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
    }
    
    h1 i {
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
    
    .tenant-info {
      background: var(--light);
      border-radius: var(--radius);
      padding: 20px;
      margin-bottom: 25px;
      border: 1px solid var(--border);
    }
    
    .info-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      gap: 15px;
    }
    
    .info-item {
      display: flex;
      flex-direction: column;
    }
    
    .info-label {
      font-weight: 600;
      color: var(--gray);
      font-size: 0.9rem;
      margin-bottom: 4px;
    }
    
    .info-value {
      font-size: 1.1rem;
      color: var(--dark);
    }
    
    .penalty {
      color: #e63946;
      font-weight: 600;
    }
    
    .no-penalty {
      color: #2a9d8f;
    }
    
    .payment-section {
      background: white;
      border-radius: var(--radius);
      padding: 25px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      margin-bottom: 30px;
      border: 1px solid var(--border);
    }
    
    .payment-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--border);
    }
    
    .balance-amount {
      font-size: 1.8rem;
      font-weight: 700;
      color: var(--primary);
    }
    
    .form-group {
      margin-bottom: 20px;
    }
    
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 600;
      color: var(--dark);
    }
    
    input, select {
      width: 100%;
      padding: 14px;
      border: 1px solid var(--border);
      border-radius: 8px;
      font-size: 1rem;
      transition: all 0.3s ease;
    }
    
    input:focus, select:focus {
      outline: none;
      border-color: var(--primary);
      box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    .reference-field {
      display: none;
      margin-top: 15px;
    }
    
    .btn {
      display: inline-block;
      padding: 12px 28px;
      background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      font-weight: 600;
      text-align: center;
      transition: all 0.3s ease;
      width: 100%;
      box-shadow: 0 4px 6px rgba(67, 97, 238, 0.3);
    }
    
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(67, 97, 238, 0.4);
    }
    
    .btn:active {
      transform: translateY(0);
    }
    
    .btn-receipt {
      background: #28a745;
      padding: 8px 16px;
      font-size: 0.9rem;
      width: auto;
      box-shadow: 0 2px 4px rgba(40, 167, 69, 0.3);
    }
    
    .btn-receipt:hover {
      background: #218838;
      box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
    }
    
    .history-section {
      margin-top: 20px;
    }
    
    h2 {
      color: var(--primary);
      margin: 25px 0 20px;
      padding-bottom: 10px;
      border-bottom: 2px solid var(--light-gray);
      font-size: 1.5rem;
    }
    
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 15px;
      background: white;
      border-radius: var(--radius);
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
    
    th {
      background: var(--primary);
      color: white;
      padding: 16px 12px;
      text-align: left;
      font-weight: 600;
    }
    
    td {
      padding: 14px 12px;
      border-bottom: 1px solid var(--border);
    }
    
    tr:last-child td {
      border-bottom: none;
    }
    
    tr:hover {
      background-color: rgba(67, 97, 238, 0.03);
    }
    
    .reference-cell {
      font-family: monospace;
      font-size: 0.9rem;
    }
    
    .action-cell {
      text-align: center;
    }
    
    .empty-history {
      text-align: center;
      padding: 20px;
      color: var(--gray);
      font-style: italic;
    }
    
    @media (max-width: 600px) {
      .container {
        padding: 20px 15px;
      }
      
      h1 {
        font-size: 1.8rem;
      }
      
      .info-grid {
        grid-template-columns: 1fr;
      }
      
      .payment-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
      
      .balance-amount {
        font-size: 1.5rem;
      }
      
      th, td {
        padding: 12px 8px;
        font-size: 0.9rem;
      }
    }
  </style>
  <script>
    function toggleReferenceField() {
      const method = document.getElementById("payment_method").value;
      const refField = document.getElementById("reference_no_field");
      
      if (["G-Cash", "PayMaya", "Palawan Pay"].includes(method)) {
        refField.style.display = "block";
      } else {
        refField.style.display = "none";
      }
    }
    
    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
      toggleReferenceField();
    });
  </script>
</head>
<body>
  <div class="container">
    <header>
      <h1><i class="fas fa-coins"></i> Tenant Payment Portal</h1>
    </header>
    
    <div class="tenant-info">
      <div class="info-grid">
        <div class="info-item">
          <span class="info-label">TENANT NAME</span>
          <span class="info-value"><?= htmlspecialchars($tenant['name']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">STALL SECTION</span>
          <span class="info-value"><?= htmlspecialchars($tenant['stall_section']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">STALL NO.</span>
          <span class="info-value"><?= htmlspecialchars($tenant['stall_no_rented']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">CONTACT NO.</span>
          <span class="info-value"><?= htmlspecialchars($tenant['contact_NO']); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">MONTHLY DUE DATE</span>
          <span class="info-value"><?= $currentMonthDue; ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">OVERDUE DATE</span>
          <span class="info-value"><?= $overdue_date; ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">MONTHLY RENT</span>
          <span class="info-value">₱<?= number_format($monthly_price, 2); ?></span>
        </div>
        <div class="info-item">
          <span class="info-label">PENALTY</span>
          <span class="info-value <?= $penalty > 0 ? 'penalty' : 'no-penalty' ?>">
            <?= $penalty > 0 ? "₱" . number_format($penalty, 2) . " (25%)" : "None" ?>
          </span>
        </div>
        <div class="info-item">
          <span class="info-label">TOTAL BALANCE</span>
          <span class="info-value balance-amount">₱<?= number_format($remaining_balance, 2); ?></span>
        </div>
      </div>
    </div>

    <?php if ($remaining_balance > 0): ?>
    <div class="payment-section">
      <div class="payment-header">
        <h2>Make Payment</h2>
        <div class="balance-amount">₱<?= number_format($remaining_balance, 2); ?></div>
      </div>
      
      <form method="POST">
        <div class="form-group">
          <label for="payment_amount">Payment Amount</label>
          <input type="number" 
                 name="payment_amount" 
                 id="payment_amount"
                 step="0.01" 
                 min="0.01"
                 max="<?= $remaining_balance; ?>" 
                 required
                 placeholder="Enter amount to pay">
        </div>

        <div class="form-group">
          <label for="payment_method">Payment Method</label>
          <select name="payment_method" 
                  id="payment_method" 
                  onchange="toggleReferenceField()" 
                  required>
            <option value="">-- Select Payment Method --</option>
            <option value="G-Cash">G-Cash</option>
            <option value="PayMaya">PayMaya</option>
            <option value="Palawan Pay">Palawan Pay</option>
            <option value="Over The Counter">Over The Counter</option>
          </select>
        </div>

        <div id="reference_no_field" class="reference-field">
          <label for="reference_no">Reference Number</label>
          <input type="text" 
                 name="reference_no" 
                 id="reference_no"
                 placeholder="Enter transaction reference number"
                 maxlength="50">
        </div>

        <button type="submit" class="btn">
          <i class="fas fa-paper-plane"></i> Submit Payment
        </button>
      </form>
    </div>
    <?php endif; ?>

    <div class="history-section">
      <h2><i class="fas fa-history"></i> Transaction History</h2>
      
      <?php if ($history_result->num_rows > 0): ?>
        <table>
          <thead>
            <tr>
              <th>Amount</th>
              <th>Method</th>
              <th>Reference No.</th>
              <th>Date</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $history_result->fetch_assoc()): ?>
              <tr>
                <td>₱<?= number_format($row['payment_amount'], 2); ?></td>
                <td><?= htmlspecialchars($row['payment_method']); ?></td>
                <td class="reference-cell"><?= htmlspecialchars($row['reference_no'] ?? 'N/A'); ?></td>
                <td><?= $row['payment_date']; ?></td>
                <td class="action-cell">
                  <form method="GET" action="receipt.php" target="_blank">
                    <input type="hidden" name="tenant_id" value="<?= $tenant_id; ?>">
                    <input type="hidden" name="payment_date" value="<?= $row['payment_date']; ?>">
                    <button type="submit" class="btn btn-receipt">
                      <i class="fas fa-receipt"></i> Receipt
                    </button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      <?php else: ?>
        <div class="empty-history">
          <i class="fas fa-inbox fa-2x" style="margin-bottom: 10px; color: var(--gray);"></i>
          <p>No payment history found</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</body>
</html>
<?php $conn->close(); ?>