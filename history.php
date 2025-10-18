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

// Check if the delete action is requested
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    
    // Retrieve the stall number associated with the tenant to be deleted
    $sql_stall_no = "SELECT stall_no_rented FROM tenant WHERE id = ?";
    $stmt_stall_no = $conn->prepare($sql_stall_no);
    $stmt_stall_no->bind_param("i", $id);
    $stmt_stall_no->execute();
    $stmt_stall_no->bind_result($stall_no);
    $stmt_stall_no->fetch();
    $stmt_stall_no->close();

    // Prepare a delete statement
    $sql_delete = "DELETE FROM tenant WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id);
    
    // Execute the delete statement
    if ($stmt_delete->execute()) {
        // Update the stall availability to "available"
        $sql_update_stall = "UPDATE stall SET availability = 'available' WHERE stall_no = ?";
        $stmt_update_stall = $conn->prepare($sql_update_stall);
        $stmt_update_stall->bind_param("i", $stall_no);
        $stmt_update_stall->execute();
        
        echo "<script>alert('Tenant deleted successfully!');</script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }

    $stmt_delete->close();
    $stmt_update_stall->close();
}

// Fetch data from database for inactive tenants (contracts ended or deleted)
$sql_inactive_tenants = "SELECT * FROM tenant WHERE end_date < CURDATE() OR status = 'inactive'";
$result_inactive_tenants = $conn->query($sql_inactive_tenants);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant History</title>
    <style>
        /* Your CSS styles here */
    </style>
</head>
<body>
    <h1>Tenant History</h1>
    <table>
        <thead>
            <tr>
                <th>RENTED STALL NO.</th>
                <th>NAME</th>
                <th>ADDRESS</th>
                <th>CONTACT NO.</th>
                <th>START DATE</th>
                <th>END DATE</th>
                <th>OUTSTANDING BALANCE</th>
                <th>MONTHLY RENT</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result_inactive_tenants->num_rows > 0) {
                // Output data of each row for inactive tenants
                while ($row = $result_inactive_tenants->fetch_assoc()) {
                    // Format the start_date and end_date fields
                    $start_date = strtotime($row["start_date"]);
                    $end_date = strtotime($row["end_date"]);
                    
                    // Calculate the number of months between start and end dates
                    $months_diff = round(($end_date - $start_date) / (31 * 24 * 60 * 60));
                    
                    // Fetch the stall price based on the rented stall number
                    $stall_no_rented = $row["stall_no_rented"];
                    $stall_price_sql = "SELECT price FROM stall WHERE stall_no = '$stall_no_rented'";
                    $stall_price_result = $conn->query($stall_price_sql);
                    $stall_price_row = $stall_price_result->fetch_assoc();
                    $monthly_rent = $stall_price_row['price'];
                    
                    // Calculate outstanding balance
                    $outstanding_balance = $months_diff * $monthly_rent;    

                    // Fetch the current fee taken
                    $fee_taken = $row["fee_taken"];

                    // Subtract the fee from outstanding balance
                    $outstanding_balance -= $fee_taken;

                    echo "<tr>
                            <td>".$row["stall_no_rented"]."</td>
                            <td>".$row["name"]."</td>
                            <td>".$row["address"]."</td>
                            <td>".$row["contact_NO"]."</td>
                            <td>".date('Y-m-d', $start_date)."</td>
                            <td>".date('Y-m-d', $end_date)."</td>
                            <td>".number_format($outstanding_balance, 2)."</td>
                            <td>".number_format($monthly_rent, 2)."</td>
                            <td>".$row["status"]."</td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='9'>No inactive tenants found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
