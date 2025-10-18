<?php
session_start();

// Check if tenant is logged in
if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch tenant profile picture
$sql = "SELECT profile_pic FROM tenant WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$tenant = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Set profile picture URL
$profilePic = !empty($tenant['profile_pic']) ? htmlspecialchars($tenant['profile_pic']) : 'https://placehold.co/36x36/e2e8f0/64748b?text=U';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
  <title>ARKI.TIENDA: Digital Stall Rental</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', Tahoma, Geneva, Verdana, sans-serif;
      min-height: 100vh;
      color: #fff;
      background: linear-gradient(to bottom, rgba(0, 0, 0, 0.7), rgba(20, 20, 20, 0.9)), 
                  url('https://placehold.co/1200x800/2c3e50/ffffff?text=ARKI.TIENDA') no-repeat center center / cover;
      display: flex;
      flex-direction: column;
      align-items: center;
      padding: 16px;
      position: relative;
    }

    .profile-avatar {
      position: absolute;
      top: 20px;
      right: 20px;
      width: 52px;
      height: 52px;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.15);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
      z-index: 10;
      overflow: hidden;
      border: 2px solid rgba(255, 255, 255, 0.2);
    }

    .profile-avatar:hover {
      transform: scale(1.12);
      background: rgba(255, 255, 255, 0.25);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
    }

    .profile-avatar img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .header-container {
      text-align: center;
      padding: 32px 16px 24px;
      width: 100%;
      max-width: 500px;
      margin-top: 20px;
    }

    .header-container img {
      width: 80px;
      height: 80px;
      border-radius: 20px;
      margin-bottom: 20px;
      background: rgba(255, 255, 255, 0.1);
      padding: 12px;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
      object-fit: contain;
      border: 2px solid rgba(255, 255, 255, 0.15);
    }

    .header-container h1 {
      font-size: 20px;
      font-weight: 700;
      line-height: 1.4;
      color: #f8f9fa;
      text-shadow: 0 2px 6px rgba(0, 0, 0, 0.4);
      letter-spacing: -0.3px;
    }

    .button-container {
      display: grid;
      grid-template-columns: 1fr;
      width: 100%;
      max-width: 500px;
      gap: 14px;
      padding: 16px 0 24px;
    }

    .button {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 14px;
      padding: 18px 20px;
      background: linear-gradient(135deg, #4361ee, #3a0ca3);
      color: #fff;
      text-decoration: none;
      font-size: 16px;
      font-weight: 600;
      border-radius: 18px;
      transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25);
      border: none;
      text-align: center;
      position: relative;
      overflow: hidden;
    }

    .button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.5s;
    }

    .button:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 22px rgba(0, 0, 0, 0.35);
    }

    .button:hover::before {
      left: 100%;
    }

    .button:active {
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    .logout {
      background: linear-gradient(135deg, #e63946, #d90429);
    }

    .logout:hover {
      background: linear-gradient(135deg, #d90429, #b5179e);
    }

    /* Minimalist Date & Time */
    .datetime-container {
      margin-top: 12px;
      text-align: center;
      font-size: 14px;
      color: rgba(255, 255, 255, 0.85);
      font-weight: 400;
      letter-spacing: 0.3px;
      padding: 0 16px;
      max-width: 500px;
      width: 100%;
    }

    .datetime-container span {
      display: block;
      margin-top: 4px;
      font-family: 'Courier New', monospace;
      font-size: 15px;
      color: #ffd166;
      font-weight: 500;
    }

    /* Loading animation */
    @keyframes pulse {
      0% { opacity: 0.6; }
      50% { opacity: 1; }
      100% { opacity: 0.6; }
    }

    #currentDateTime.loading {
      animation: pulse 1.5s infinite;
    }

    /* Mobile-first design */
    @media (min-width: 768px) {
      .button-container {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
      }
      
      .header-container h1 {
        font-size: 22px;
      }
      
      .button {
        padding: 20px 16px;
        font-size: 15px;
      }
      
      .profile-avatar {
        top: 24px;
        right: 24px;
        width: 56px;
        height: 56px;
      }
      
      .datetime-container {
        font-size: 15px;
      }
      
      .datetime-container span {
        font-size: 16px;
      }
    }

    @media (min-width: 1024px) {
      .header-container h1 {
        font-size: 24px;
      }
      
      .button {
        font-size: 16px;
        padding: 20px 20px;
      }
    }

    /* Small mobile optimizations */
    @media (max-width: 390px) {
      .header-container h1 {
        font-size: 18px;
      }
      
      .button {
        font-size: 15px;
        padding: 16px 18px;
        gap: 12px;
      }
      
      .profile-avatar {
        top: 16px;
        right: 16px;
        width: 48px;
        height: 48px;
      }
      
      .datetime-container {
        font-size: 13px;
        padding: 0 12px;
      }
      
      .datetime-container span {
        font-size: 14px;
      }
    }

    @media (max-width: 320px) {
      .profile-avatar {
        top: 12px;
        right: 12px;
        width: 44px;
        height: 44px;
      }
      
      .header-container h1 {
        font-size: 16px;
      }
      
      .button {
        font-size: 14px;
        padding: 14px 16px;
      }
      
      .datetime-container {
        font-size: 12px;
      }
      
      .datetime-container span {
        font-size: 13px;
      }
    }

    /* Safe area padding for iOS */
    @supports (padding-top: env(safe-area-inset-top)) {
      body {
        padding-top: env(safe-area-inset-top);
        padding-left: env(safe-area-inset-left);
        padding-right: env(safe-area-inset-right);
        padding-bottom: env(safe-area-inset-bottom);
      }
    }
  </style>
</head>
<body>
  <!-- Profile Avatar - Top Right Corner -->
  <a href="tenantacountmagement.php" class="profile-avatar" title="My Account">
    <img src="<?php echo $profilePic; ?>" alt="Profile Picture" onerror="this.src='https://placehold.co/36x36/e2e8f0/64748b?text=U'">
  </a>

  <div class="header-container">
    <img src="www.jpg" alt="ARKI.TIENDA Logo" onerror="this.style.display='none'" />
    <h1>ARKI.TIENDA: YOUR DIGITAL STALL RENTAL IN JANIUAY</h1>
  </div>

  <div class="button-container">
    <a href="tpayment.php" class="button"><i class="fas fa-money-bill-wave"></i> Payment</a>
    <a href="transaction.php" class="button"><i class="fas fa-receipt"></i> Transaction History</a>
    <a href="stallavailable.php" class="button"><i class="fas fa-store"></i> Select Your Stall</a>
    <a href="stalldetail.php" class="button"><i class="fas fa-box"></i> Stall Rented</a> 
    <a href="tenantacountmagement.php" class="button"><i class="fas fa-user-cog"></i> Account</a>
    <a href="tenantLogin.php" class="button logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
  </div>

  <!-- Minimalist Date & Time -->
  <div class="datetime-container">
    Current Time and Date:<br>
    <span id="currentDateTime" class="loading">Loading...</span>
  </div>

  <script>
    // Function to update current date and time
    function updateDateTime() {
      const now = new Date();
      
      const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false
      };
      
      const dateTimeString = now.toLocaleDateString('en-PH', options).replace(',', ' at');
      
      const dateTimeEl = document.getElementById('currentDateTime');
      dateTimeEl.textContent = dateTimeString;
      dateTimeEl.classList.remove('loading');
    }
    
    // Update immediately and then every second
    updateDateTime();
    setInterval(updateDateTime, 1000);

    // Add smooth loading for images
    document.querySelectorAll('img').forEach(img => {
      img.style.opacity = '0';
      img.onload = () => {
        img.style.transition = 'opacity 0.3s ease';
        img.style.opacity = '1';
      };
    });
  </script>
</body>
</html>