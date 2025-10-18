<?php
session_start();

// Replace with your actual login session logic
if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}
$tenant_id = $_SESSION['tenant_id'];

// DB Connection
$conn = new mysqli("localhost", "root", "", "rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if tenant already has a confirmed stall
$hasConfirmedStall = false;
$checkConfirmed = $conn->prepare("SELECT stall_no FROM stall WHERE tenant_id = ? AND availability = 'unavailable'");
$checkConfirmed->bind_param("i", $tenant_id);
$checkConfirmed->execute();
$checkConfirmed->store_result();
if ($checkConfirmed->num_rows > 0) {
    $hasConfirmedStall = true;
}
$checkConfirmed->close();

// Fetch only available stalls
$sql = "SELECT * FROM stall WHERE availability = 'available'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
  <title>Select Stall</title>
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
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    a {
      color: #123458;
      text-decoration: none;
      transition: color 0.25s ease;
    }

    a:hover, a:focus {
      color: #0d223f;
      outline: none;
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
    .stalls-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 20px;
      width: 100%;
    }

    .stall-card {
      background: white;
      border-radius: 20px;
      overflow: hidden;
      box-shadow: 0 6px 16px rgba(18, 52, 88, 0.12);
      transition: all 0.3s ease;
      border: 1px solid #f0f4f8;
    }

    .stall-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 12px 24px rgba(18, 52, 88, 0.18);
    }

    .stall-image {
      width: 100%;
      height: 160px;
      object-fit: cover;
      cursor: pointer;
      transition: transform 0.3s ease;
    }

    .stall-image:hover {
      transform: scale(1.05);
    }

    .stall-info {
      padding: 16px;
    }

    .stall-header {
      margin-bottom: 12px;
    }

    .stall-number {
      font-size: 18px;
      font-weight: 700;
      color: #123458;
      margin-bottom: 4px;
    }

    .stall-name {
      font-size: 14px;
      color: #475569;
      font-weight: 500;
    }

    .stall-details {
      margin-bottom: 16px;
    }

    .detail-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 8px;
      font-size: 13px;
    }

    .detail-label {
      color: #64748b;
      font-weight: 500;
    }

    .detail-value {
      color: #123458;
      font-weight: 600;
    }

    .price-item {
      display: flex;
      justify-content: space-between;
      margin-bottom: 12px;
      padding: 8px 0;
      border-top: 1px solid #f1f5f9;
      border-bottom: 1px solid #f1f5f9;
    }

    .monthly-price {
      color: #059669;
      font-weight: 700;
      font-size: 16px;
    }

    .yearly-price {
      color: #dc2626;
      font-weight: 700;
      font-size: 16px;
    }

    .btn-request {
      width: 100%;
      background: linear-gradient(135deg, #123458 0%, #2d3748 100%);
      color: white;
      border: none;
      padding: 12px;
      border-radius: 14px;
      font-size: 15px;
      font-weight: 600;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(18, 52, 88, 0.2);
    }

    .btn-request:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(18, 52, 88, 0.3);
    }

    .btn-disabled {
      background: linear-gradient(135deg, #cbd5e1 0%, #94a3b8 100%);
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }

    .btn-disabled:hover {
      transform: none;
      box-shadow: 0 4px 12px rgba(18, 52, 88, 0.2);
    }

    .text-muted {
      color: #94a3b8;
      font-style: normal;
      font-weight: 500;
      text-align: center;
      padding: 20px;
      font-size: 16px;
    }

    .text-muted i {
      font-size: 48px;
      margin-bottom: 12px;
      color: #cbd5e1;
    }

    /* Modal Styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      padding-top: 60px;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0, 0, 0, 0.8);
    }

    .modal-content {
      margin: auto;
      display: block;
      max-width: 90%;
      max-height: 80vh;
      border-radius: 16px;
      box-shadow: 0 8px 24px rgba(18, 52, 88, 0.3);
    }

    .modal-close {
      position: absolute;
      top: 20px;
      right: 20px;
      color: white;
      font-size: 2.5rem;
      font-weight: bold;
      cursor: pointer;
      user-select: none;
      transition: color 0.3s ease;
      background: rgba(0, 0, 0, 0.5);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .modal-close:hover {
      color: #ddd;
      background: rgba(0, 0, 0, 0.7);
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

      .stalls-grid {
        grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
        gap: 16px;
      }

      .stall-image {
        height: 140px;
      }

      .stall-info {
        padding: 14px;
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

      .stalls-grid {
        grid-template-columns: 1fr;
        gap: 12px;
      }

      .stall-image {
        height: 120px;
      }

      .stall-info {
        padding: 12px;
      }

      .stall-number {
        font-size: 16px;
      }

      .btn-request {
        padding: 10px;
        font-size: 14px;
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

<a href="tenantUI.php" class="back-button" aria-label="Back to Dashboard">
  <i class="fas fa-arrow-left" aria-hidden="true"></i> Back
</a>

<div class="container" role="main">
  <h2>Available Stalls</h2>

  <?php if ($result->num_rows > 0): ?>
    <div class="stalls-grid">
      <?php while($row = $result->fetch_assoc()): ?>
        <?php $img = !empty($row['stall_image']) ? "uploads/" . $row['stall_image'] : "https://placehold.co/300x200/e2e8f0/64748b?text=No+Image"; ?>
        <div class="stall-card">
          <img src="<?= htmlspecialchars($img) ?>" alt="Stall <?= htmlspecialchars($row['stall_no']) ?>" class="stall-image" tabindex="0" onclick="openModal(this.src)" />
          
          <div class="stall-info">
            <div class="stall-header">
              <div class="stall-number">Stall #<?= htmlspecialchars($row['stall_no']) ?></div>
              <div class="stall-name"><?= htmlspecialchars($row['stall_name']) ?></div>
            </div>
            
            <div class="stall-details">
              <div class="detail-item">
                <span class="detail-label">Section:</span>
                <span class="detail-value"><?= htmlspecialchars($row['stall_section']) ?></span>
              </div>
              <div class="detail-item">
                <span class="detail-label">Category:</span>
                <span class="detail-value"><?= htmlspecialchars($row['category']) ?></span>
              </div>
            </div>
            
            <div class="price-item">
              <span class="detail-label">Monthly:</span>
              <span class="monthly-price">₱<?= number_format($row['monthly_price'], 2) ?></span>
            </div>
            
            <div class="price-item">
              <span class="detail-label">Yearly:</span>
              <span class="yearly-price">₱<?= number_format($row['yearly_price'], 2) ?></span>
            </div>
            
            <div>
              <?php if (!$hasConfirmedStall): ?>
                <a href="request_stall.php?tenant_id=<?= $tenant_id ?>&stall_no=<?= urlencode($row['stall_no']) ?>" 
                   class="btn-request" 
                   role="button" 
                   aria-label="Request stall <?= htmlspecialchars($row['stall_no']) ?>">
                  Request Stall
                </a>
              <?php else: ?>
                <button class="btn-request btn-disabled" disabled>
                  Already Rented
                </button>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <div class="text-muted">
      <i class="fas fa-store"></i>
      <p>No available stalls at the moment.</p>
    </div>
  <?php endif; ?>
</div>

<!-- Modal -->
<div id="imgModal" class="modal" aria-hidden="true" role="dialog" aria-label="Image preview">
  <span class="modal-close" aria-label="Close" onclick="closeModal()">&times;</span>
  <img class="modal-content" id="modalImage" alt="Large stall image" />
</div>

<script>
  function openModal(src) {
    const modal = document.getElementById('imgModal');
    const modalImg = document.getElementById('modalImage');
    modal.style.display = 'block';
    modal.setAttribute('aria-hidden', 'false');
    modalImg.src = src;
  }

  function closeModal() {
    const modal = document.getElementById('imgModal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
  }

  // Close modal on click outside image
  document.getElementById('imgModal').addEventListener('click', function(e) {
    if (e.target === this) {
      closeModal();
    }
  });

  // Close modal on Escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
      closeModal();
    }
  });

  // Image click with keyboard support
  document.querySelectorAll('.stall-image').forEach(img => {
    img.addEventListener('keydown', function(e) {
      if (e.key === 'Enter' || e.key === ' ') {
        e.preventDefault();
        openModal(this.src);
      }
    });
  });
</script>

</body>
</html>
<?php $conn->close(); ?>