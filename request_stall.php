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

// Initialize variables
$message = "";
$success = false;

// Validate session and input
if (!isset($_SESSION['tenant_id']) || !isset($_GET['stall_no'])) {
    $message = "Invalid request. Please log in or select a stall.";
} else {
    $tenant_id = intval($_SESSION['tenant_id']);
    $stall_no = $conn->real_escape_string($_GET['stall_no']);

    // Check if tenant already has a confirmed stall
    $stmt = $conn->prepare("SELECT stall_no FROM stall WHERE tenant_id = ? AND availability = 'unavailable'");
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $message = "You already have a confirmed stall. You cannot request another.";
    } else {
        // Get tenant name
        $getName = $conn->prepare("SELECT name FROM tenant WHERE id = ?");
        $getName->bind_param("i", $tenant_id);
        $getName->execute();
        $getName->bind_result($tenant_name);
        $getName->fetch();
        $getName->close();

        // Request the stall
        $update = $conn->prepare("UPDATE stall SET tenant_id = ?, status = 'requested' WHERE stall_no = ? AND availability = 'available'");
        $update->bind_param("is", $tenant_id, $stall_no);
        $update->execute();

        if ($update->affected_rows > 0) {
            $message = "Stall request submitted successfully for <strong>$tenant_name</strong>.";
            $success = true;
        } else {
            $message = "Failed to request stall. It may no longer be available.";
        }
        $update->close();
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stall Request Status</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 700px;
            margin: 60px auto;
            background: #fff;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            text-align: center;
        }

        .header {
            background-color: #123458;
            color: white;
            padding: 20px;
            font-size: 22px;
            border-radius: 8px 8px 0 0;
        }

        .alert {
            margin-top: 30px;
            padding: 20px;
            font-size: 16px;
            border-radius: 4px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 18px;
            border-radius: 4px;
            font-size: 14px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn:hover {
            opacity: 0.85;
        }

        .back-btn {
            background-color: #007bff;
        }

        .dashboard-btn {
            background-color: #28a745;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">Stall Request Status</div>

        <div class="alert <?php echo $success ? 'alert-success' : 'alert-error'; ?>">
            <?php echo $message; ?>
        </div>

        <a href="stallavailable.php" class="btn back-btn">Back to Available Stalls</a>
        <?php if ($success): ?>
            <a href="tenantUI.php" class="btn dashboard-btn">Go to Dashboard</a>
        <?php endif; ?>
    </div>
</body>
</html>
