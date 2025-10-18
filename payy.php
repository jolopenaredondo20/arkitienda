<?php
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// SQL query to fetch data from the tenant_fee_taken table
$sql = "SELECT amount_paid, outstanding_balance AS ot_balance FROM tenant_fee_taken";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
  // Loop through each row of the result
  while ($row = $result->fetch_assoc()) {
    // Insert data into the payment table
    $amount_paid = $row['amount_paid'];
    $ot_balance = $row['ot_balance'];

    // SQL query to insert data into the payment table
    $insert_sql = "INSERT INTO payment (amount_paid, ot_balance) VALUES ('$amount_paid', '$ot_balance')";

    if ($conn->query($insert_sql) === TRUE) {
      echo "Data inserted successfully.";
    } else {
      echo "Error: " . $insert_sql . "<br>" . $conn->error;
    }
  }

  // Now fetch data from payment table and display 
  $payment_sql = "SELECT amount_paid, ot_balance FROM payment";
  $payment_result = $conn->query($payment_sql);

  if ($payment_result->num_rows > 0) {
    // Output data of each row
    echo "<h2>XAMPP Payment Table</h2>";
    echo "<table>
            <thead>
              <tr>
                <th>Amount Paid</th>
                <th>Outstanding Balance</th>
              </tr>
            </thead>
            <tbody>";
    while ($row = $payment_result->fetch_assoc()) {
      echo "<tr>
              <td>" . $row["amount_paid"] . "</td>
              <td>" . $row["ot_balance"] . "</td>
            </tr>";
    }
    echo "</tbody></table>";
  } else {
    echo "<p>No payments found.</p>";
  }
} else {
  echo "No data found in tenant_fee_taken table.";
}

// Close connection
$conn->close();
?>
