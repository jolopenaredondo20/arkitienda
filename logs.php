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

// Fetch tenant logs
$sql = "SELECT * FROM tenant_logs ORDER BY deleted_at DESC";
$result = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Logs</title>
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
            width: 90%;
            margin: 20px auto;
        }
        .header {
            background-color: #1E0342;
            color: white;
            text-align: center;
            padding: 20px 0;
            border-radius: 8px 8px 0 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
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
            <h1>Tenant Deletion Logs</h1>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tenant Name</th>
                    <th>Contact No.</th>
                    <th>Address</th>
                    <th>Stall No. Rented</th>
                    <th>Stall Section</th>
                    <th>Deleted At</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>{$row["tenant_name"]}</td>
                            <td>{$row["contact_no"]}</td>
                            <td>{$row["address"]}</td>
                            <td>{$row["stall_no_rented"]}</td>
                            <td>{$row["stall_section"]}</td>
                            <td>{$row["deleted_at"]}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>No logs found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>

        <footer>
            <p>&copy; 2025 Tenant Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
