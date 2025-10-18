<?php
session_start();

// Check if the user is logged in and is a tenant
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'tenant') {
    // Redirect to login page if not a tenant
    header("Location: login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the selected stall section from the filter
$stall_section = isset($_GET['stall_section']) ? $_GET['stall_section'] : '';

// Construct the SQL query based on the selected section
$sql = "SELECT stall_no, availability, stall_section FROM stall WHERE availability = 'available'";

if ($stall_section) {
    $sql .= " AND stall_section = '$stall_section'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Stalls</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Available Stalls</h2>

        <!-- Filter Dropdown -->
        <form method="get" class="mb-4">
            <div class="form-group">
                <label for="stall_section">Select Stall Section</label>
                <select id="stall_section" name="stall_section" class="form-control" onchange="this.form.submit()">
                    <option value="">All Sections</option>
                    <option value="Main stall" <?php echo $stall_section == 'Main stall' ? 'selected' : ''; ?>>Corner Stall</option>
                    <option value="Center stall" <?php echo $stall_section == 'Center stall' ? 'selected' : ''; ?>>Inside Stall</option>
                </select>
            </div>
        </form>

        <!-- Stall Table -->
        <table class="table table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>Stall No</th>
                    <th>Availability</th>
                    <th>Stall Section</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$row['stall_no']}</td>
                                <td>{$row['availability']}</td>
                                <td>{$row['stall_section']}</td>
                              </tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' class='text-center'>No stalls available</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
