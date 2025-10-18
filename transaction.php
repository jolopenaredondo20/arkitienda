<?php
session_start();

// Check if tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    die("Access denied. Please log in.");
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$tenant_id = $_SESSION['tenant_id']; // tenant ID from login session

// Fetch transactions only for this tenant
$sql = "SELECT reference_no, payment_amount, monthly_balance, payment_date, payment_method 
        FROM payment 
        WHERE tenant_id = ? 
        ORDER BY payment_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

// Calculate total paid
$total_sql = "SELECT SUM(payment_amount) AS total_paid FROM payment WHERE tenant_id = ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param("i", $tenant_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_paid = $total_row['total_paid'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>My Transactions</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', Arial, sans-serif;
      background: linear-gradient(135deg, #f5f7fa 0%, #e4edf9 100%);
      color: #123458;
      min-height: 100vh;
      padding: 16px;
    }

    .back-button {
      display: inline-flex;
      align-items: center;
      margin: 16px 0 24px;
      font-weight: 600;
      font-size: 16px;
      padding: 12px 20px;
      border-radius: 16px;
      background: white;
      color: #123458;
      border: 2px solid #e4e9f0;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(18, 52, 88, 0.1);
      text-decoration: none;
      width: fit-content;
    }

    .back-button i { 
      margin-right: 10px; 
      font-size: 18px;
    }

    .back-button:hover { 
      background: #123458; 
      color: white;
      border-color: #123458;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(18, 52, 88, 0.2);
    }

    .container {
      background: white;
      padding: 24px 16px;
      border-radius: 24px;
      box-shadow: 0 8px 32px rgba(18, 52, 88, 0.15);
      width: 100%;
      max-width: 100%;
      margin: 0 auto;
    }

    h2 {
      text-align: center;
      font-weight: 700;
      font-size: 22px;
      margin-bottom: 24px;
      color: #123458;
      padding: 0 8px;
    }

    /* Mobile-first table styling */
    .transactions-container {
      overflow-x: auto;
      width: 100%;
      padding: 0 4px;
    }

    table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 12px;
      font-size: 14px;
      min-width: 600px;
    }

    thead th {
      background: linear-gradient(135deg, #123458 0%, #2d3748 100%);
      color: white;
      padding: 14px 12px;
      text-align: center;
      font-weight: 600;
      font-size: 13px;
      position: sticky;
      top: 0;
    }

    tbody tr {
      background: white;
      box-shadow: 0 4px 12px rgba(18, 52, 88, 0.08);
      border-radius: 16px;
      transition: all 0.3s ease;
      border: 1px solid #f0f4f8;
    }

    tbody tr:hover {
      background: #f8fafc;
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(18, 52, 88, 0.12);
    }

    tbody td {
      padding: 16px 12px;
      text-align: center;
      color: #123458;
      font-weight: 500;
    }

    .amount-paid {
      color: #059669;
      font-weight: 600;
    }

    .balance-amount {
      color: #dc2626;
      font-weight: 600;
    }

    .text-muted { 
      color: #94a3b8; 
      font-style: normal;
      font-weight: 400;
    }

    .summary-box {
      margin-top: 24px;
      padding: 18px;
      background: linear-gradient(135deg, #f8fafc 0%, #eef4ff 100%);
      border-radius: 18px;
      text-align: center;
      font-size: 16px;
      font-weight: 600;
      color: #123458;
      box-shadow: 0 4px 12px rgba(18, 52, 88, 0.08);
      border: 1px solid #e2e8f0;
    }

    .summary-box strong {
      color: #123458;
      font-size: 18px;
      display: block;
      margin-top: 4px;
    }

    .summary-label {
      font-weight: 500;
      color: #475569;
      font-size: 15px;
    }

    .no-transactions {
      text-align: center;
      padding: 40px 20px;
      color: #64748b;
      font-size: 16px;
      font-weight: 500;
    }

    .no-transactions i {
      font-size: 48px;
      margin-bottom: 16px;
      color: #cbd5e1;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      body {
        padding: 12px;
      }
      
      .back-button {
        margin: 12px 0 20px;
        padding: 10px 16px;
        font-size: 15px;
      }
      
      .container {
        padding: 20px 12px;
        border-radius: 20px;
      }
      
      h2 {
        font-size: 20px;
        margin-bottom: 20px;
      }
      
      table {
        font-size: 13px;
        min-width: 550px;
      }
      
      thead th {
        padding: 12px 8px;
        font-size: 12px;
      }
      
      tbody td {
        padding: 14px 8px;
        font-size: 13px;
      }
      
      .summary-box {
        padding: 16px;
        font-size: 15px;
      }
    }

    @media (max-width: 480px) {
      body {
        padding: 8px;
      }
      
      .back-button {
        margin: 8px 0 16px;
        padding: 8px 12px;
        font-size: 14px;
      }
      
      .container {
        padding: 16px 8px;
        border-radius: 16px;
      }
      
      h2 {
        font-size: 18px;
        margin-bottom: 16px;
      }
      
      table {
        font-size: 12px;
        min-width: 500px;
      }
      
      thead th {
        padding: 10px 6px;
        font-size: 11px;
      }
      
      tbody td {
        padding: 12px 6px;
        font-size: 12px;
      }
      
      .summary-box {
        padding: 14px;
        font-size: 14px;
      }
      
      .summary-box strong {
        font-size: 16px;
      }
    }

    /* Animation for loading */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    .container {
      animation: fadeIn 0.5s ease;
    }
  </style>
</head>
<body>

<a href="tenantUI.php" class="back-button">
  <i class="fas fa-arrow-left"></i> Back
</a>

<div class="container">
  <h2>My Transactions</h2>

  <?php if ($result->num_rows > 0): ?>
    <div class="transactions-container">
      <table>
        <thead>
          <tr>
            <th>Date</th>
            <th>Amount Paid</th>
            <th>Payment Method</th>
            <th>Reference No.</th>
            <th>Outstanding Balance</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= date("M d, Y", strtotime($row["payment_date"])) ?></td>
              <td class="amount-paid">₱<?= number_format($row["payment_amount"], 2) ?></td>
              <td><?= htmlspecialchars($row["payment_method"]) ?></td>
              <td><?= !empty($row["reference_no"]) ? htmlspecialchars($row["reference_no"]) : "<span class='text-muted'>N/A</span>" ?></td>
              <td class="balance-amount">₱<?= number_format($row["monthly_balance"], 2) ?></td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <div class="summary-box">
      <span class="summary-label">Total Paid</span>
      <strong>₱<?= number_format($total_paid, 2) ?></strong>
    </div>

  <?php else: ?>
    <div class="no-transactions">
      <i class="fas fa-receipt"></i>
      <p>No transactions found.</p>
    </div>
  <?php endif; ?>

</div>

</body>
</html>
<?php
$stmt->close();
$total_stmt->close();
$conn->close();
?>