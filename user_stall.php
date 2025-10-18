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
    
    // Prepare a delete statement
    $sql = "DELETE FROM stall WHERE id = ?";
    
    // Prepare and bind the parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    // Execute the statement
    if ($stmt->execute()) {
        echo "<script>alert('Stall deleted successfully!');</script>";
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Fetch data from database
$sql = "SELECT * FROM stall";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stalls</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            align-items: center;
            text-align: center;

        }
        th, td {
            border: 1px solid black;
            text-align: cennter;
            padding: 8px;
            height: 30px;
            font-size: 20px;
             width: calc(100% / 4);

        }
        th {
            background-color: #9AC8CD;


        }
        .action {
            width: 100px;
            text-align: center;
        }
        .header {
      background-color: #1E0342;
      color: white;
      padding: 1px;
      text-align: center;
      height: 30px;
    }
     .header1 {
      background-color: #1E0342;
      color: white;
      padding: 0px;
      text-align: right;
      height: 1px;

    }
     .header2 {
      background-color: #1E0342;
      color: white;
      padding: 0px;
      text-align: left;
    }
    .edit-btn, .delete-btn {
            display: inline-block;
            padding: 6px 12px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
            border: 2px solid black;

        }

        .delete-btn {
            background-color: #f44336;
            color: white;
            border: 2px solid black;
        }
        .add-btn {
            display: inline-block;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            background-color: blue;
            color: white;
            border: 2px solid black;
            letter-spacing: 5px;
            word-spacing: 3px;
        }
        .back-btn {
            display: inline-block;
            padding: 8px 16px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            background-color: blue;
            color: white;
            border: 2px solid black;
            letter-spacing: 7px;
        }
        h1{
            letter-spacing: 7px;
            word-spacing: 5px;

        }

    </style>
</head>
<body>
    <div class="header">
    <h1>STALL LIST</h1>
</div>

<div class="header2">
       <button onclick="location.href='user_index.php'" class='back-btn'>BACK</button></div>

    <table>
        <thead>
            <tr>
                <th>STALL NO.</th>
                <th>STALL NAME</th>
                <th>AVAILABILITY</th>
                <th>PRICE A MONTH</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                // Output data of each row
                while ($row = $result->fetch_assoc()) {
                    // Replace "available" with "Available" and "unavailable" with "Unavailable"
                    $availability = ($row["availability"] == "available") ? "Available" : "Unavailable";
                    echo "<tr>
                            <td>".$row["stall_no"]."</td>
                            <td>".$row["stall_name"]."</td>
                            <td>".$availability."</td>
                            <td>".number_format($row["price"], 2)."</td>

                            </td>
                        </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>0 results</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>
