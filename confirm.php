<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all requested stalls with tenant name and contact number (hide confirmed stalls)
$sql = "SELECT s.stall_no, s.stall_name, s.stall_section, s.category, s.monthly_price, s.yearly_price, 
               t.name AS tenant_name, t.contact_NO AS tenant_contact
        FROM stall s
        LEFT JOIN tenant t ON s.tenant_id = t.id
        WHERE s.status = 'requested' AND s.availability = 'available'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Confirm Tenant Stall Requests</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #eef1f5;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 30px auto;
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
        }

        .header {
            background-color: #1e3d59;
            color: white;
            padding: 25px;
            text-align: center;
            border-radius: 10px 10px 0 0;
            margin: -30px -30px 20px -30px;
        }

        .header h1 {
            margin: 0;
            font-size: 26px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            font-size: 14px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
        }

        th, td {
            padding: 14px 12px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background-color: #1e3d59;
            color: white;
            font-weight: 500;
        }

        tr:hover {
            background-color: #f9f9f9;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 13px;
            text-decoration: none;
            color: white;
            border: none;
            cursor: pointer;
            transition: all 0.25s ease-in-out;
        }

        .btn:hover {
            transform: scale(1.03);
            opacity: 0.9;
        }

        .confirm-btn { background-color: #2ecc71; }
        .cancel-btn { background-color: #e74c3c; }

        .empty-message {
            text-align: center;
            color: #888;
            padding: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-clipboard-list"></i> Tenant Stall Requests</h1>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Stall No</th>
                    <th>Name</th>
                    <th>Section</th>
                    <th>Category</th>
                    <th>Monthly</th>
                    <th>Yearly</th>
                    <th>Tenant</th>
                    <th>Contact</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['stall_no']) ?></td>
                        <td><?= htmlspecialchars($row['stall_name']) ?></td>
                        <td><?= htmlspecialchars($row['stall_section']) ?></td>
                        <td><?= htmlspecialchars($row['category']) ?></td>
                        <td>₱<?= number_format($row['monthly_price'], 2) ?></td>
                        <td>₱<?= number_format($row['yearly_price'], 2) ?></td>
                        <td><?= htmlspecialchars($row['tenant_name']) ?: '<span style="color:#e74c3c;">No tenant</span>' ?></td>
                        <td><?= htmlspecialchars($row['tenant_contact']) ?: '<span style="color:#e74c3c;">No contact</span>' ?></td>
                        <td>
                            <a href="confirm_action.php?stall_no=<?= urlencode($row['stall_no']) ?>" class="btn confirm-btn">
                                <i class="fas fa-check"></i> Confirm
                            </a>
                            <a href="cancel_action.php?stall_no=<?= urlencode($row['stall_no']) ?>" class="btn cancel-btn" 
                               onclick="return confirm('Are you sure you want to cancel this request?');">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="9" class="empty-message">No pending stall requests.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php 
$conn->close(); 
?>
