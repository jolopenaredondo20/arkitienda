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

// Fetch notifications from the database
$query = "SELECT * FROM notifications ORDER BY date_created DESC";
$result = $conn->query($query);

// Handle notification as read
if (isset($_GET['mark_as_read'])) {
    $notification_id = $_GET['mark_as_read'];
    $update_query = "UPDATE notifications SET status='read' WHERE notification_id='$notification_id'";
    $conn->query($update_query);
    header("Location: notification.php");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications | ARKI.TIENDA</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #4D55CC, #7886C7);
            margin: 0;
            padding: 20px;
            color: white;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            color: black;
        }
        h2 {
            font-weight: 600;
            color: #003092;
            margin-bottom: 20px;
        }
        .notification-list {
            margin-top: 20px;
        }
        .notification {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .notification.read {
            background-color: #d3d3d3;
        }
        .notification h4 {
            margin: 0;
            font-size: 18px;
            color: #003092;
        }
        .notification p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }
        .notification .status {
            font-size: 12px;
            color: #aaa;
        }
        .notification .mark-read {
            background-color: #003092;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .notification .mark-read:hover {
            background-color: #001f5b;
        }
        .btn-back {
            display: inline-block;
            background-color: #003092;
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            text-decoration: none;
            margin-top: 20px;
        }
        .btn-back:hover {
            background-color: #001f5b;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>📢 Notifications</h2>

    <!-- Notification List -->
    <div class="notification-list">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<div class='notification " . ($row['status'] == 'read' ? 'read' : '') . "'>";
                echo "<h4>" . $row['tenant_name'] . "</h4>";
                echo "<p>" . $row['message'] . "</p>";
                echo "<p class='status'>Status: " . ucfirst($row['status']) . "</p>";
                echo "<a href='?mark_as_read=" . $row['notification_id'] . "' class='mark-read'>Mark as Read</a>";
                echo "</div>";
            }
        } else {
            echo "<p>No notifications found.</p>";
        }
        ?>
    </div>

    <!-- Back to Dashboard Button -->
    <a href="tenantUI.php" class="btn-back">🏠 Back to Dashboard</a>
</div>

</body>
</html>
