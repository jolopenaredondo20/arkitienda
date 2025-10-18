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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["take_fee"])) {
    $tenant_id = $_POST["tenant_id"];
    $fee_taken = $_POST["fee_taken"];

    // Fetch the current fee taken and outstanding balance for the tenant
    $fetch_fee_balance_sql = "SELECT fee_taken, outstanding_balance FROM tenant WHERE id = $tenant_id";
    $fetch_fee_balance_result = $conn->query($fetch_fee_balance_sql);
    $fetch_fee_balance_row = $fetch_fee_balance_result->fetch_assoc();
    $current_fee_taken = $fetch_fee_balance_row["fee_taken"];
    $outstanding_balance = $fetch_fee_balance_row["outstanding_balance"];

    // Subtract the fee from the outstanding balance
    $outstanding_balance = $fee_taken;

    // Update the fee taken and outstanding balance for the tenant
    $update_fee_balance_sql = "UPDATE tenant 
                               SET fee_taken = $current_fee_taken + $fee_taken, 
                                   outstanding_balance = $outstanding_balance
                               WHERE id = $tenant_id";
    if ($conn->query($update_fee_balance_sql) === TRUE) {
        echo "Fee subtracted successfully and outstanding balance updated.";
        // Redirect to payment.php or any other desired page
        echo "<script>document.location='payment.php';</script>";
    } else {
        echo "Error updating fee and outstanding balance: " . $conn->error;
    }
}

$sql = "SELECT t.*, s.price AS monthly_rent FROM tenant t JOIN stall s ON t.stall_no_rented = s.stall_no";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Fee</title>
    <style>
    body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #333;
        }

        form {
            width: 50%;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
        }

        select, input[type="number"], input[type="submit"] {
            width: 50%;
            padding: 10px;
            margin-top: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: blue;
            color: white;
            cursor: pointer;
            font-size: 15px;
        }
        a.back-btn {
    width: 20%;
    display: inline-block;
    padding: 8px 16px;
    text-align: center;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;
    cursor: pointer;
    background-color: blue;
    color: white;
    transition: background-color 0.3s ease;
    margin-bottom: 15px;
    font-size: 16px; /* Adjusted font size for better fit */
}


    
    </style>
</head>
<body>
    <h1>Take Rental Fee</h1>
    <form method="post">
        <label for="tenant">Select Tenant:</label>
        <select name="tenant_id" id="tenant">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row["id"] . "'>" . $row["name"] . "</option>";
                }
            } else {
                echo "<option value=''>No tenants found</option>";
            }
            ?>
        </select>
        <br><br>
        <label for="fee">Enter Rental Fee Amount:</label>
        <input type="number" name="fee_taken" id="fee" min="0" required>
        <br><br>
        <input type="submit" name="take_fee" value="Take Rental Fee"><br>
        <a href="payment.php" class="back-btn">Back</a>
    </form>
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                            <td>".$row["stall_no_rented"]."</td>
                            <td>".$row["name"]."</td>
                            <td>".$row["monthly_rent"]."</td>
                            <td>".$row["outstanding_balance"]."</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='4'>0 results</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>
