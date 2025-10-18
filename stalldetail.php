<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

// Check if tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    echo "<script>alert('Please log in first.'); window.location.href='login.php';</script>";
    exit;
}

$tenant_id = $_SESSION['tenant_id'];

// DB connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tenant and stall details (removed yearly_price)
$sql = "SELECT t.name AS tenant_name, t.contact_NO AS tenant_contact, t.stall_no_rented, t.start_date, 
               s.stall_no, s.stall_name, s.stall_section, s.category, s.monthly_price, s.stall_image
        FROM tenant t
        LEFT JOIN stall s ON t.stall_no_rented = s.stall_no
        WHERE t.id = ? AND t.stall_no_rented IS NOT NULL";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "<script>alert('No rented stall found.'); window.location.href='profile.php';</script>";
    exit;
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Stall Details - <?= htmlspecialchars($row['tenant_name']) ?></title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
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

    /* Mobile-first card layout */
    .stall-card {
      background: linear-gradient(135deg, #f8fafc 0%, #edf2ff 100%);
      border-radius: 20px;
      padding: 24px;
      margin-bottom: 24px;
      box-shadow: 0 6px 16px rgba(18, 52, 88, 0.08);
      border: 1px solid #e2e8f0;
    }

    .stall-header {
      text-align: center;
      margin-bottom: 20px;
      padding-bottom: 16px;
      border-bottom: 2px solid #f1f5f9;
    }

    .stall-title {
      font-size: 18px;
      font-weight: 700;
      color: #123458;
      margin-bottom: 8px;
    }

    .stall-number {
      font-size: 14px;
      color: #475569;
      font-weight: 500;
    }

    .stall-details {
      display: grid;
      gap: 16px;
    }

    .detail-item {
      display: flex;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #f1f5f9;
    }

    .detail-item:last-child {
      border-bottom: none;
    }

    .detail-label {
      color: #64748b;
      font-weight: 500;
      font-size: 14px;
    }

    .detail-value {
      color: #123458;
      font-weight: 600;
      font-size: 15px;
    }

    .monthly-price {
      color: #059669;
      font-size: 18px;
    }

    .contact-number {
      color: #059669;
      font-weight: 600;
    }

    .start-date {
      color: #dc2626;
      font-weight: 600;
    }

    .image-section {
      text-align: center;
      margin-top: 8px;
    }

    .image-section h4 {
      margin-bottom: 16px;
      color: #123458;
      font-weight: 600;
      font-size: 18px;
      text-align: center;
    }

    .stall-image {
      width: 100%;
      max-width: 320px;
      height: 200px;
      object-fit: cover;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(18, 52, 88, 0.2);
      cursor: pointer;
      transition: all 0.3s ease;
      margin: 0 auto;
    }

    .stall-image:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 28px rgba(18, 52, 88, 0.3);
    }

    .no-image {
      background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
      width: 100%;
      max-width: 320px;
      height: 200px;
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #94a3b8;
      font-size: 16px;
      font-weight: 500;
      margin: 0 auto;
      border: 2px dashed #cbd5e1;
    }

    .no-image i {
      font-size: 48px;
      margin-bottom: 12px;
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

      .stall-card {
        padding: 20px;
      }

      .stall-image {
        height: 180px;
      }

      .no-image {
        height: 180px;
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

      .stall-card {
        padding: 16px;
      }

      .stall-title {
        font-size: 16px;
      }

      .stall-image {
        height: 160px;
      }

      .no-image {
        height: 160px;
        font-size: 14px;
      }

      .detail-label,
      .detail-value {
        font-size: 13px;
      }

      .monthly-price {
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

<a href="TenantUI.php" class="back-button">
  <i class="fas fa-arrow-left"></i> Back
</a>

<div class="container">
  <h2>Stall Details for <?= htmlspecialchars($row['tenant_name']) ?></h2>

  <div class="stall-card">
    <div class="stall-header">
      <div class="stall-title"><?= htmlspecialchars($row['stall_name']) ?></div>
      <div class="stall-number">Stall #<?= htmlspecialchars($row['stall_no']) ?></div>
    </div>

    <div class="stall-details">
      <div class="detail-item">
        <span class="detail-label">Category:</span>
        <span class="detail-value"><?= htmlspecialchars($row['category']) ?></span>
      </div>
      
      <div class="detail-item">
        <span class="detail-label">Monthly Rent:</span>
        <span class="detail-value monthly-price">₱<?= number_format($row['monthly_price'], 2) ?></span>
      </div>
      
      <div class="detail-item">
        <span class="detail-label">Contact Number:</span>
        <span class="detail-value contact-number"><?= htmlspecialchars($row['tenant_contact']) ?></span>
      </div>
      
      <div class="detail-item">
        <span class="detail-label">Start Date:</span>
        <span class="detail-value start-date"><?= date("F j, Y", strtotime($row['start_date'])) ?></span>
      </div>
    </div>
  </div>

  <div class="image-section">
    <h4>Stall Image</h4>
    <?php if (!empty($row['stall_image'])): ?>
      <img src="uploads/<?= htmlspecialchars($row['stall_image']) ?>" alt="Stall Image" class="stall-image" />
    <?php else: ?>
      <div class="no-image">
        <div>
          <i class="fas fa-image"></i>
          <div>No image available</div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

</body>
</html>