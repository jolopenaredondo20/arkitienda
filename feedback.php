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

// Handle feedback deletion by admin
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $feedback_id = intval($_GET['id']);
    $sql = "DELETE FROM feedback WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $feedback_id);
        if ($stmt->execute()) {
            echo "<script>alert('Feedback deleted successfully!'); window.location.href='feedback.php';</script>";
        } else {
            echo "Error deleting feedback: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Fetch all feedback from tenants
$feedbackQuery = "SELECT feedback.id, tenant.name, feedback.feedback, feedback.feedback_date 
                  FROM feedback 
                  JOIN tenant ON feedback.tenant_id = tenant.id 
                  ORDER BY feedback.feedback_date DESC";
$feedbackResult = $conn->query($feedbackQuery);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Tenant Feedback</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #79D7BE, #9AC8CD);
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
        }
        .header {
            background-color: #1E0342;
            color: white;
            text-align: center;
            padding: 20px 0;
            border-radius: 8px 8px 0 0;
        }
        .actions {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .btn {
            padding: 10px 20px;
            color: white;
            background-color: #28a745;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #218838;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: white;
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background-color: #1E0342;
            color: white;
        }
        td {
            background-color: #f8f9fa;
        }
        tr:nth-child(even) td {
            background-color: #f1f3f5;
        }
        tr:hover td {
            background-color: #e9ecef;
        }
        footer {
            text-align: center;
            background-color: #1E0342;
            color: white;
            padding: 10px 0;
            margin-top: 20px;
            border-radius: 0 0 8px 8px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Admin - Tenant Feedback</h1>
        </div>

        <h3>Feedback List</h3>
        <table>
            <thead>
                <tr>
                    <th>Tenant Name</th>
                    <th>Feedback</th>
                    <th>Date Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($feedbackResult->num_rows > 0) {
                    while ($row = $feedbackResult->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row['name']}</td>
                            <td>{$row['feedback']}</td>
                            <td>{$row['feedback_date']}</td>
                            <td>
                                <a href='?action=delete&id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this feedback?\")' class='btn' style='background-color: #dc3545;'>Delete</a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>No feedback available.</td></tr>";
                }
                ?>
            </tbody>
        </table>

    </div>
</body>
</html>

<?php $conn->close(); ?>
