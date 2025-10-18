<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Rental Management Dashboard</title>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      display: flex;
      min-height: 100vh;
      background: #f4f6f8;
      color: #333;
    }

    /* Sidebar */
    .sidebar {
      width: 240px;
      background: #1e2a38;
      color: #fff;
      display: flex;
      flex-direction: column;
      padding: 24px 16px;
      position: fixed;
      height: 100vh;
    }

    .sidebar h2 {
      text-align: center;
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 48px;
      letter-spacing: 0.5px;
    }

    .sidebar h2 img {
      width: 28px;
      vertical-align: middle;
      margin-right: 6px;
      border-radius: 6px;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      color: #fff;
      padding: 10px 14px;
      margin: 6px 0;
      text-decoration: none;
      border-radius: 6px;
      font-size: 15px;
      transition: background 0.2s ease, transform 0.1s ease;
    }

    .sidebar a i {
      margin-right: 10px;
      font-size: 16px;
      opacity: 0.9;
    }

    .sidebar a:hover {
      background: #2d3e50;
      transform: translateX(2px);
    }

    .sidebar a:last-child {
      margin-top: auto;
      background: #dc3545;
      text-align: center;
      justify-content: center;
    }

    .sidebar a:last-child:hover {
      background: #c82333;
    }

    /* Main content */
    .content {
      margin-left: 240px;
      padding: 40px 40px 20px;
      width: calc(100% - 240px);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    .header {
      margin-bottom: 24px;
      text-align: center;
    }

    .header h1 {
      font-size: 22px;
      color: #1e2a38;
      font-weight: 600;
    }

    iframe {
      flex-grow: 1;
      width: 100%;
      height: 70vh;
      border: none;
      border-radius: 10px;
      background: #fff;
    }

    footer {
      text-align: center;
      margin-top: 30px;
      font-size: 13px;
      color: #777;
      padding-bottom: 10px;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .sidebar {
        width: 200px;
      }
      .content {
        margin-left: 200px;
        width: calc(100% - 200px);
      }
    }

    @media (max-width: 600px) {
      .sidebar {
        position: absolute;
        z-index: 1000;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }

      .sidebar.open {
        transform: translateX(0);
      }

      .content {
        margin-left: 0;
        width: 100%;
        padding: 24px;
      }
    }
  </style>
</head>
<body>
  <div class="sidebar" id="sidebar">
    <h2><img src="www.jpg" alt="Logo" />ARKI.TIENDA</h2>
    <a onclick="loadPage('dashboard.php')"><i class="fas fa-tachometer-alt"></i>Dashboard</a>
    <a onclick="loadPage('stall.php')"><i class="fas fa-store"></i>Stall</a>
    <a onclick="loadPage('tenant.php')"><i class="fas fa-user"></i>Tenant</a>
    <a onclick="loadPage('rental.php')"><i class="fas fa-home"></i>Rental</a>
    <a onclick="loadPage('billing.php')"><i class="fas fa-file-invoice-dollar"></i>Billing</a>
    <a onclick="loadPage('payment.php')"><i class="fas fa-money-bill-wave"></i>Payment</a>
    <a onclick="loadPage('report.php')"><i class="fas fa-file-alt"></i>Report</a>
    <a onclick="loadPage('Exectools.php')"><i class="fas fa-comment-dots"></i>SMS</a>
    <a onclick="loadPage('confirm.php')"><i class="fas fa-plus-circle"></i>Stall Request</a> 
    <a onclick="loadPage('manageaccount.php')"><i class="fas fa-users-cog"></i>User Account</a>
    <a href="login.php"><i class="fas fa-sign-out-alt"></i>Log Out</a>
  </div>

  <div class="content">
    <div class="header">
      <h1>ARKI.TIENDA: Your Digital Stall Rental in Janiuay</h1>
    </div>

    <iframe id="mainFrame" src="dashboard.php"></iframe>

    <footer>
      &copy; 2025 ARKI.TIENDA - All Rights Reserved.
    </footer>
  </div>

  <script>
    function loadPage(page) {
      document.getElementById('mainFrame').src = page;
    }
  </script>
</body>
</html>
