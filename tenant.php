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

// Delete tenant logic
if (isset($_GET['id']) && isset($_GET['action']) && $_GET['action'] === 'delete') {
    $tenant_id = $_GET['id'];

    // Get the stall number rented by the tenant
    $get_stall_sql = "SELECT stall_no_rented FROM tenant WHERE id = ?";
    $stmt = $conn->prepare($get_stall_sql);
    $stmt->bind_param("i", $tenant_id);
    $stmt->execute();
    $stmt->bind_result($stall_no_rented);
    $stmt->fetch();
    $stmt->close();

    if (!empty($stall_no_rented)) {
        // Delete tenant
        $delete_sql = "DELETE FROM tenant WHERE id = ?";
        $stmt = $conn->prepare($delete_sql);
        $stmt->bind_param("i", $tenant_id);
        if ($stmt->execute()) {
            // Mark stall as available
            $update_stall_sql = "UPDATE stall SET availability = 'available' WHERE stall_no = ?";
            $update_stmt = $conn->prepare($update_stall_sql);
            $update_stmt->bind_param("i", $stall_no_rented);
            $update_stmt->execute();
            $update_stmt->close();

            echo "<script>alert('Tenant deleted successfully!'); window.location.href='tenant.php';</script>";
        } else {
            echo "<script>alert('Error deleting tenant!'); window.location.href='tenant.php';</script>";
        }
        $stmt->close();
    } else {
        echo "<script>alert('Tenant not found!'); window.location.href='tenant.php';</script>";
    }
}

// Fetch tenant data including start_date
$sql = "SELECT tenant.id, tenant.name, tenant.gender, tenant.contact_NO, tenant.address, tenant.stall_no_rented, tenant.start_date, stall.stall_section, stall.yearly_price 
        FROM tenant 
        LEFT JOIN stall ON tenant.stall_no_rented = stall.stall_no";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #eef1f5; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 30px auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08); }
        .header { background-color: #1e3d59; color: white; padding: 25px; text-align: center; border-radius: 10px 10px 0 0; }
        .header h1 { margin: 0; font-size: 26px; }

        /* Actions */
        .actions { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; margin-top: 25px; gap: 10px; }

        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border-radius: 6px; font-size: 14px; text-decoration: none; color: white; border: none; cursor: pointer; transition: all 0.25s ease-in-out; }
        .btn:hover { transform: scale(1.03); opacity: 0.9; }
        .add-btn { background-color: #2ecc71; }
        .edit-btn { background-color: #3498db; }
        .delete-btn { background-color: #e74c3c; }

        .search-container { position: relative; max-width: 260px; width: 100%; }
        .search-box { width: 100%; padding: 10px 40px 10px 14px; font-size: 14px; border: 1px solid #ccc; border-radius: 6px; background-color: #fafafa; box-sizing: border-box; }
        .search-box:focus { border-color: #2ecc71; outline: none; }
        .search-icon { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #aaa; font-size: 18px; pointer-events: none; }

        table { width: 100%; border-collapse: collapse; margin-top: 30px; font-size: 14px; background-color: #fff; border-radius: 8px; overflow: hidden; }
        th, td { padding: 14px 12px; text-align: center; border-bottom: 1px solid #f0f0f0; }
        th { background-color: #1e3d59; color: white; font-weight: 500; }
        tr:hover { background-color: #f9f9f9; }
        .action-btns { display: flex; justify-content: center; gap: 10px; }
        .empty-message { text-align: center; color: #888; padding: 20px; font-style: italic; }

        @media screen and (max-width: 768px) {
            .actions { flex-direction: column; align-items: flex-start; }
            table { font-size: 12px; }
            .search-container { max-width: 100%; width: 100%; margin-top: 10px; }
        }
    </style>
    <script>
        function searchTenant() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll("tbody tr");
            rows.forEach(row => {
                let name = row.cells[1].textContent.toLowerCase();
                row.style.display = name.includes(input) ? "" : "none";
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Tenant Management</h1>
        </div>

        <div class="actions">
            <button onclick="location.href='addtenant.php'" class="btn add-btn">
                <i class="fas fa-user-plus"></i> Add Tenant
            </button>
            <div class="search-container">
                <input type="text" id="searchInput" class="search-box" placeholder="Search Tenant..." onkeyup="searchTenant()">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Stall No.</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Contact No.</th>
                    <th>Address</th>
                    <th>Date Started</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                            <td>" . ($row["stall_no_rented"] ?? 'N/A') . "</td>
                            <td>{$row["name"]}</td>
                            <td>{$row["gender"]}</td>
                            <td>{$row["contact_NO"]}</td>
                            <td>{$row["address"]}</td>
                            <td>" . (!empty($row["start_date"]) ? date("M d, Y", strtotime($row["start_date"])) : 'N/A') . "</td>
                            <td class='action-btns'>
                                <a href='edittenant.php?id={$row["id"]}' class='btn edit-btn'><i class='fas fa-edit'></i></a>
                                <a href='?id={$row["id"]}&action=delete' onclick='return confirm(\"Are you sure you want to delete this tenant?\")' class='btn delete-btn'><i class='fas fa-trash'></i></a>
                            </td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='7' class='empty-message'>No tenants found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
