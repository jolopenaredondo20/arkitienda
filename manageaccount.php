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

// --- DELETE ACCOUNT HANDLER ---
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $sql_delete = "DELETE FROM accounts WHERE id = $delete_id";
    if ($conn->query($sql_delete) === TRUE) {
        echo "<script>alert('Account deleted successfully!'); window.location.href='".$_SERVER['PHP_SELF']."';</script>";
        exit;
    } else {
        echo "<script>alert('Error deleting account: " . $conn->error . "');</script>";
    }
}

// Fetch tenant data and check account status
$sql = "SELECT tenant.id, tenant.name, tenant.gender, tenant.contact_NO, tenant.address, tenant.start_date, 
               tenant.stall_no_rented, stall.stall_section, stall.yearly_price, 
               accounts.id AS acc_id,
               CASE WHEN accounts.id IS NOT NULL THEN 'Active' ELSE 'Inactive' END AS account_status
        FROM tenant 
        LEFT JOIN stall ON tenant.stall_no_rented = stall.stall_no
        LEFT JOIN accounts ON tenant.id = accounts.tenant_id";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Tenant Management</title>
<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

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
    }

    .header h1 {
        margin: 0;
        font-size: 26px;
    }

    .actions {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-end;
        margin-top: 25px;
        gap: 10px;
    }

    .search-box {
        padding: 10px 14px;
        font-size: 14px;
        border: 1px solid #ccc;
        border-radius: 6px;
        width: 260px;
        background-color: #fafafa;
    }

    .search-box:focus {
        border-color: #2ecc71;
        outline: none;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
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

    .btn-primary { background-color: #3498db; }
    .btn-primary:hover { background-color: #2980b9; }

    .btn-danger { background-color: #e74c3c; }
    .btn-danger:hover { background-color: #c0392b; }

    .badge {
        padding: 6px 12px;
        border-radius: 50px;
        font-weight: 500;
        font-size: 13px;
        display: inline-block;
    }

    .status-active { background-color: #2ecc71; color: white; }
    .status-inactive { background-color: #e74c3c; color: white; }

    .empty-message {
        text-align: center;
        color: #888;
        padding: 20px;
        font-style: italic;
    }

    @media screen and (max-width: 768px) {
        .actions {
            flex-direction: column;
            align-items: flex-start;
        }
        table {
            font-size: 12px;
        }
        .search-box {
            width: 100%;
        }
    }
</style>

<script>
    function searchTenant() {
        let input = document.getElementById("searchInput").value.toLowerCase();
        let rows = document.querySelectorAll("tbody tr");
        rows.forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(input) ? "" : "none";
        });
    }
    function confirmDelete(id) {
        if (confirm("Are you sure you want to delete this account?")) {
            window.location.href = "<?php echo $_SERVER['PHP_SELF']; ?>?delete_id=" + id;
        }
    }
</script>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Tenant Account Management</h1>
    </div>

    <div class="actions">
        <input type="text" id="searchInput" class="search-box" placeholder="Search tenants..." onkeyup="searchTenant()">
    </div>

    <table>
        <thead>
            <tr>
                <th>Stall No.</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Contact No.</th>
                <th>Address</th>
                <th>Date Admitted</th>
                <th>Account Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $start_date = date('Y-m-d', strtotime($row["start_date"]));
                    $status_class = ($row["account_status"] === "Active") ? "status-active" : "status-inactive";
                    echo "<tr>
                        <td>" . ($row["stall_no_rented"] ?? 'N/A') . "</td>
                        <td>{$row["name"]}</td>
                        <td>{$row["gender"]}</td>
                        <td>{$row["contact_NO"]}</td>
                        <td>{$row["address"]}</td>
                        <td>{$start_date}</td>
                        <td><span class='badge $status_class'>{$row["account_status"]}</span></td>
                        <td>";
                    if ($row["account_status"] === "Inactive") {
                        echo "<a href='createaccount.php?id={$row["id"]}&contact_no={$row["contact_NO"]}' class='btn btn-primary'><i class='fas fa-user-plus'></i> Create</a>";
                    } else {
                        echo "<button class='btn btn-danger' onclick='confirmDelete({$row["acc_id"]})'><i class='fas fa-user-minus'></i> Delete</button>";
                    }
                    echo "</td></tr>";
                }
            } else {
                echo "<tr><td colspan='8' class='empty-message'>No tenants found</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
