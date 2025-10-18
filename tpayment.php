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
               stall.monthly_price
        FROM tenant 
        JOIN stall ON tenant.stall_no_rented = stall.stall_no
        WHERE tenant.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$tenant = $result->fetch_assoc();
$stmt->close();

if (!$tenant) {
    die("Tenant not found.");
}

// Fetch total payments made this month
$current_month = date('Y-m');
$sql_monthly = "SELECT IFNULL(SUM(payment_amount), 0) AS monthly_paid 
                FROM payment 
                WHERE tenant_id = ? AND DATE_FORMAT(payment_date, '%Y-%m') = ?";
$stmt = $conn->prepare($sql_monthly);
$stmt->bind_param("is", $tenant_id, $current_month);
$stmt->execute();
$result = $stmt->get_result();
$monthly_payment = $result->fetch_assoc();
$monthly_paid = $monthly_payment['monthly_paid'];
$stmt->close();

// === DYNAMIC PENALTY LOGIC (based on tenant's start date) ===
$penalty = 0;
$penalty_message = "No penalty";

// Get tenant's start day of the month
$startDay = (int)date('d', strtotime($tenant['start_date']));
$currentYearMonth = date('Y-m');
$lastDayOfMonth = (int)date('t', strtotime("{$currentYearMonth}-01"));
$dueDay = min($startDay, $lastDayOfMonth); // Avoid invalid dates (e.g., Feb 30)
$monthlyDueDate = "{$currentYearMonth}-" . str_pad($dueDay, 2, '0', STR_PAD_LEFT);
$overdueDate = date('Y-m-d', strtotime($monthlyDueDate . ' +3 days')); // Overdue = due + 3 days

$today = date('Y-m-d');

// Calculate unpaid rent
$unpaid_rent = max(0, $tenant['monthly_price'] - $monthly_paid);

// Apply penalty only if: unpaid AND today is past overdue date
if ($unpaid_rent > 0 && $today > $overdueDate) {
    $penalty = 0.25 * $tenant['monthly_price']; // 25% penalty

    // Check if penalty has already been paid
    $total_required = $tenant['monthly_price'] + $penalty;
    if ($monthly_paid >= $total_required) {
        $penalty = 0;
        $penalty_message = "Penalty: Paid ✅";
        $unpaid_rent = 0;
    } else {
        $penalty_message = "Penalty: PHP " . number_format($penalty, 2);
    }
} else {
    $penalty_message = "No penalty";
}

// Final values for display
$monthly_balance = $unpaid_rent;
$monthly_status = ($monthly_balance <= 0) ? "Paid for this month" : "PHP " . number_format($monthly_balance, 2);
$disable_payment = ($monthly_balance <= 0 && $penalty == 0) ? 'disabled' : '';
$current_month_text = date("F Y");

// Handle payment submission (placeholder)
if (isset($_POST['pay_now'])) {
    // In a real app, you'd process payment and insert into `payment` table here
    // For now, we'll just reload or redirect
    // Example: 
    // $total_to_pay = $monthly_balance + $penalty;
    // Insert into payment (tenant_id, payment_amount, payment_date, ...)
    // Then redirect to success page
    // header("Location: payment_success.php");
    // exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <title>Tenant Payment | ARKI.TIENDA</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(135deg, white 0%, #764ba2 100%);
      min-height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 20px;
    }

    .container {
      background: white;
      padding: 32px 24px;
      border-radius: 24px;
      box-shadow: 0 12px 36px rgba(0, 0, 0, 0.15);
      width: 100%;
      max-width: 450px;
      position: relative;
      overflow: hidden;
    }

    .container::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 6px;
      background: linear-gradient(90deg, #4361ee, #3a0ca3);
    }

    .header {
      text-align: center;
      margin-bottom: 28px;
    }

    .header h2 {
      font-weight: 700;
      font-size: 24px;
      color: #2d3748;
      margin-bottom: 8px;
    }

    .header p {
      color: #718096;
      font-size: 14px;
    }

    .balance-info {
      background: linear-gradient(135deg, #f8f9ff 0%, #edf2ff 100%);
      padding: 24px;
      border-radius: 20px;
      margin-bottom: 24px;
      border: 1px solid #e2e8f0;
    }

    .balance-info h3 {
      font-size: 16px;
      font-weight: 600;
      color: #4a5568;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .balance-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      padding-bottom: 12px;
      border-bottom: 1px solid #e2e8f0;
    }

    .balance-item:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .balance-label {
      color: #4a5568;
      font-weight: 500;
    }

    .balance-value {
      font-weight: 600;
      color: #2d3748;
    }

    .balance-paid {
      color: #48bb78 !important;
    }

    .penalty-active {
      color: #e53e3e !important;
    }

    .copy-container {
      margin-top: 16px;
      text-align: center;
    }

    .copy-box {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      background: #4361ee;
      padding: 14px 20px;
      border-radius: 16px;
      font-size: 18px;
      font-weight: 600;
      color: white;
      box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .copy-box i {
      font-size: 20px;
      cursor: pointer;
      transition: transform 0.2s ease;
    }

    .copy-box i:hover {
      transform: scale(1.2);
    }

    #copyMessage {
      text-align: center;
      color: #48bb78;
      font-size: 14px;
      margin-top: 12px;
      font-weight: 500;
      display: none;
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-5px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .payment-section {
      margin-top: 24px;
    }

    .payment-title {
      font-size: 16px;
      font-weight: 600;
      color: #2d3748;
      margin-bottom: 16px;
      text-align: center;
    }

    .payment-icons {
      display: flex;
      justify-content: center;
      gap: 24px;
      margin-bottom: 24px;
    }

    .payment-icon {
      width: 60px;
      height: 60px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: white;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .payment-icon:hover {
      transform: translateY(-4px);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
    }

    .payment-icon img {
      width: 36px;
      height: 36px;
      object-fit: contain;
    }

    .btn {
      width: 100%;
      padding: 16px;
      background: linear-gradient(135deg, #4361ee, #3a0ca3);
      color: white;
      border: none;
      border-radius: 16px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
    }

    .btn:hover:not(:disabled) {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(67, 97, 238, 0.4);
    }

    .btn:disabled {
      background: linear-gradient(135deg, #cbd5e0, #a0aec0);
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .back-link {
      display: block;
      text-align: center;
      margin-top: 20px;
      text-decoration: none;
      color: #4361ee;
      font-weight: 600;
      font-size: 15px;
      transition: color 0.2s ease;
    }

    .back-link:hover {
      color: #3a0ca3;
    }

    /* Mobile optimizations */
    @media (max-width: 480px) {
      .container {
        padding: 28px 20px;
        margin: 10px;
      }
      
      .header h2 {
        font-size: 22px;
      }
      
      .balance-info {
        padding: 20px;
      }
      
      .copy-box {
        padding: 12px 16px;
        font-size: 16px;
      }
      
      .payment-icons {
        gap: 16px;
      }
      
      .payment-icon {
        width: 52px;
        height: 52px;
      }
      
      .btn {
        padding: 14px;
        font-size: 15px;
      }
    }

    @media (max-width: 360px) {
      .payment-icons {
        gap: 12px;
      }
      
      .payment-icon {
        width: 48px;
        height: 48px;
      }
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="header">
      <h2>Tenant Payment</h2>
      <p>Manage your stall rental payments</p>
    </div>

    <div class="balance-info">
      <h3><i class="fas fa-user-circle"></i> Hello, <?= htmlspecialchars($tenant['name']) ?>!</h3>
      
      <div class="balance-item">
        <span class="balance-label">Current Period:</span>
        <span class="balance-value"><?= htmlspecialchars($current_month_text) ?></span>
      </div>
      
      <div class="balance-item">
        <span class="balance-label">Due Date:</span>
        <span class="balance-value"><?= date('F d, Y', strtotime($monthlyDueDate)) ?></span>
      </div>
      
      <div class="balance-item">
        <span class="balance-label">Overdue Date:</span>
        <span class="balance-value"><?= date('F d, Y', strtotime($overdueDate)) ?></span>
      </div>
      
      <div class="balance-item">
        <span class="balance-label">Monthly Balance:</span>
        <span class="balance-value <?= ($monthly_balance <= 0) ? 'balance-paid' : '' ?>">
          <?= htmlspecialchars($monthly_status) ?>
        </span>
      </div>
      
      <div class="balance-item">
        <span class="balance-label">Penalty:</span>
        <span class="balance-value <?= ($penalty > 0) ? 'penalty-active' : 'balance-paid' ?>">
          <?= htmlspecialchars($penalty_message) ?>
        </span>
      </div>

      <?php if ($penalty > 0): ?>
        <div class="balance-item">
          <span class="balance-label">Total Payment Balance:</span>
          <span class="balance-value penalty-active">
            PHP <?= number_format($monthly_balance + $penalty, 2) ?>
          </span>
        </div>
      <?php endif; ?>
      
      <div class="copy-container">
        <div class="copy-box">
          <span id="contactNumber">09487975693</span>
          <i class="fas fa-copy" id="copyBtn" onclick="copyNumber()" title="Copy Number"></i>
        </div>
      </div>
      <p id="copyMessage">✅ Copied successfully!</p>
    </div>

    <div class="payment-section">
      <h3 class="payment-title">Quick Pay with E-Wallet</h3>
      
      <div class="payment-icons">
        <a href="gcash://pay?to=09487975693&amount=<?= urlencode(number_format($monthly_balance + $penalty, 2)) ?>" target="_blank" class="payment-icon">
          <img src="gc.png" alt="GCash">
        </a>
        <a href="paymaya://send?to=09487975693&amount=<?= urlencode(number_format($monthly_balance + $penalty, 2)) ?>" target="_blank" class="payment-icon">
          <img src="paymaya_logo.png" alt="PayMaya">
        </a>
        <a href="palawan://send?to=09487975693&amount=<?= urlencode(number_format($monthly_balance + $penalty, 2)) ?>" target="_blank" class="payment-icon">
          <img src="palawan.png" alt="PalawanPay">
        </a>
      </div>

    </div>

    <a href="tenantUI.php" class="back-link">← Back to Dashboard</a>
  </div>

  <script>
    function copyNumber() {
      const number = document.getElementById('contactNumber').textContent;
      const copyBtn = document.getElementById('copyBtn');
      const message = document.getElementById('copyMessage');

      navigator.clipboard.writeText(number).then(() => {
        message.style.display = "block";
        copyBtn.className = "fas fa-check";
        copyBtn.style.color = "#48bb78";
        
        setTimeout(() => {
          message.style.display = "none";
          copyBtn.className = "fas fa-copy";
          copyBtn.style.color = "";
        }, 2000);
      }).catch(err => {
        console.error("Failed to copy number:", err);
      });
    }

    // Mobile deep linking fallback
    document.querySelectorAll('.payment-icons a').forEach(link => {
      link.addEventListener('click', function(e) {
        const appScheme = this.getAttribute('href').split(':')[0];
        setTimeout(() => {
          if (appScheme === 'gcash') {
            window.open('https://play.google.com/store/apps/details?id=com.globe.gcash.android', '_blank');
          } else if (appScheme === 'paymaya') {
            window.open('https://play.google.com/store/apps/details?id=com.paymaya.mobile', '_blank');
          } else if (appScheme === 'palawan') {
            window.open('https://play.google.com/store/apps/details?id=com.palawanpay', '_blank');
          }
        }, 1000);
      });
    });
  </script>
</body>
</html>