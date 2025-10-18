<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REPORT</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f2f2f2;
        }
        .receipt {
            width: 600px;
            margin: 20px auto;
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
        }
        .receipt-header h1 {
            margin: 0;
            padding: 10px 0;
            background-color: #1E0342;
            color: white;
            border-radius: 8px;
            font-size: 24px;
            line-height: 1.5;
        }
        .receipt-details {
            margin-bottom: 20px;
        }
        .detail-label {
            font-weight: bold;
            margin-right: 5px;
        }
        .detail-value {
            font-style: italic;
        }
        .footer-text {
            font-style: italic;
            color: #666;
            text-align: center;
        }
        a {
            text-decoration: none;
            color: black;
            margin-top: 10px;
            display: inline-block;
        }
        a:hover {
            text-decoration: underline;
        }
        a.back-btn {
            width: 10%;
            display: inline-block;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            background-color: blue; /* Blue background color */
            color: white; /* White text color */
            transition: background-color 0.3s ease;
            text-transform: uppercase;
            margin-bottom: 15px;
            font-size: 20px;
  
        }
        a.back-btn:hover{
            background-color: #0044cc; 
        }
         .print-btn {
            background-color: #4CAF50; /* Green */
            border: none;
            color: white;
            padding: 10px 20px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            border-radius: 8px;
        }

        /* Hover effect for the print button */
        .print-btn:hover {
            background-color: #45a049; /* Darker green */
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="receipt-header">
            <h1>REPORT</h1>
        </div>
        <div class="receipt-details">
 <?php
// Establish a database connection and fetch data
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch data from the tenant table
$sql = "SELECT * FROM tenant";
$result = $conn->query($sql);

// Initialize total rented and total paid amount variables
$total_rented = 0;
$total_paid = 0;

if ($result->num_rows > 0) {
    // Output data of each row
    while ($row = $result->fetch_assoc()) {
        echo "<div class='detail-box'>";
        echo "<p><span class='detail-label'>Rented Stall No.:</span> <span class='detail-value'>" . $row["stall_no_rented"] . "</span></p>";
        echo "<p><span class='detail-label'>Tenant Name:</span> <span class='detail-value'>" . $row["name"] . "</span></p>";
        echo "<p><span class='detail-label'>Start Date:</span> <span class='detail-value'>" . $row["start_date"] . "</span></p>";
        echo "<p><span class='detail-label'>School Year Rented:</span> <span class='detail-value'>" . $row["school_year"] . "</span></p>";

        // Calculate months rented


        // Fetch monthly rent
        $stall_no_rented = $row["stall_no_rented"];
        $stall_price_sql = "SELECT price FROM stall WHERE stall_no = '$stall_no_rented'";
        $stall_price_result = $conn->query($stall_price_sql);
        $stall_price_row = $stall_price_result->fetch_assoc();
        $monthly_rent = $stall_price_row['price'];
    
        echo "<p><span class='detail-label'>Paid:</span> <span class='detail-value'>" . number_format($row["fee_taken"], 2) . "</span></p>";
        echo "<p><span class='detail-label'>Unpaid:</span> <span class='detail-value'>". number_format($row["outstanding_balance"], 2) ."</span></p>";

        echo "</div>";
        echo "<hr>";

        // Update total rented and total paid amount
        $total_rented++;
        $total_paid += $row["fee_taken"];
    }
} else {
    echo "0 results";
}

// Fetch total number of stalls
$total_stalls_sql = "SELECT COUNT(*) AS total_stalls FROM stall";
$total_stalls_result = $conn->query($total_stalls_sql);
$total_stalls_row = $total_stalls_result->fetch_assoc();
$total_stalls = $total_stalls_row['total_stalls'];

// Calculate available stalls
$available_stalls = $total_stalls - $total_rented;

// Close the database connection
$conn->close();
?>

        </div>
        <div class="receipt-footer">
    <p><span class='detail-label'>Total Tenants Rented:</span> <span class='detail-value'><?php echo $total_rented; ?></span></p>
    <p><span class='detail-label'>Total Stalls:</span> <span class='detail-value'><?php echo $total_stalls; ?></span></p>
    <p><span class='detail-label'>Stalls Available:</span> <span class='detail-value'><?php echo $available_stalls; ?></span></p>
    <p><span class='detail-label'>Total Earn This School Year:</span> <span class='detail-value'><?php echo number_format($total_paid, 2); ?></span></p>
    <p class="footer-text">Generated on <?php echo date('Y-m-d H:i:s'); ?></p>
</div>

    </div>
     <center><button onclick="window.print()" class="print-btn">Print Report</button></center>
    <center><a href="report.php" class="back-btn">Back</a></center>
</body>
</html>
