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

// Filter by section if selected
$filter_section = isset($_GET['section']) ? $_GET['section'] : '';
if (!empty($filter_section)) {
    $stmt = $conn->prepare("SELECT stall_no, availability, stall_section FROM stall WHERE availability = 'available' AND stall_section = ?");
    $stmt->bind_param("s", $filter_section);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT stall_no, availability, stall_section FROM stall WHERE availability = 'available'";
    $result = $conn->query($sql);
}
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

        <form method="GET" class="mb-4 text-center">
            <label for="section" class="form-label">Filter by Section:</label>
            <select name="section" id="section" class="form-select d-inline-block w-auto" onchange="this.form.submit()">
                <option value="">All Sections</option>
                <option value="Corner Stall" <?= $filter_section == 'Corner Stall' ? 'selected' : '' ?>>Corner Stall</option>
                <option value="Inside Stall" <?= $filter_section == 'Inside Stall' ? 'selected' : '' ?>>Inside Stall</option>
            </select>
        </form>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Stall No</th>
                    <th>Availability</th>
                    <th>Stall Section</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['stall_no'] ?></td>
                            <td><?= $row['availability'] ?></td>
                            <td><?= $row['stall_section'] ?></td>
                            <td><a href="addtenant.php?stall_no=<?= $row['stall_no'] ?>" class="btn btn-primary">Select</a></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No available stalls found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
