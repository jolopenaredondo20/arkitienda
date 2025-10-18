<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home Page</title>
    <style>
        body {
            color: white;
            background-image: url('w.jpg'); /* Replace with your background image URL */
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            position: relative;
            font-family: Tahoma;
            margin: 0;
            padding: 0;
            height: 100vh; /* Ensure body takes up full viewport height */
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: black;
            opacity: 0.5;
            z-index: -1;
        }

        h1.white-text {
            color: white;
            display: inline-block;
            margin-left: 5px;
            text-shadow: 4px 4px 4px rgba(0, 0, 0, 1);
            font-size: 40px;
        }

        .header-container {
            text-align: center;
            position: relative;
            z-index: 1;
        }

        img {
            position: absolute;
            left: 0px;
            top: 0px;
            z-index: -1;
        }

        h2 {
            padding: 5px; 
            text-align: center; 
            color: black;
            width: 50%; 
            font-size: 40px;
        }

        .container1 {
            display: flex;
            justify-content: space-between;
            margin: 60px; /* Adjust this property based on your preferred spacing */
        }

        .container1 p {
            width: 250px; /* Set a width for the buttons */
            height: 100px;
            background-color: #7469B6;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 30px;
            cursor: pointer;
            text-align: center;
            padding: 7px; 
            border: 2px solid #04AA6D;
            font-family: "Lucida Console", "Courier New", monospace;
        }

        .button {
            display: block; /* Change to block-level element */
            width: 200px; /* Set a width for the buttons */
            margin: 30px auto; /* Center horizontally */
            height: 40px;
            padding: 10px 20px;
            background-color: #1E0342;
            color: #fff;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 30px;
            cursor: pointer;
            text-align: center;
            padding: 7px; 
            border: 2px solid #04AA6D;
            font-family: "Lucida Console", "Courier New", monospace;
        }

        .button:hover {
            background-color: #0056b3;
            box-shadow: 0 12px 16px 0 rgba(0,0,0,0.35), 0 17px 50px 0 rgba(0,0,0,0.30);
        }

        .button.logout {
            width: 150px; /* Adjusted width for log out button */
            height: 25px; /* Adjusted height for log out button */
            padding: 4px 8px; /* Adjusted padding for log out button */
            font-size: 14px; /* Adjusted font size for log out button */
        }
    </style>
</head>
<body>

<div class="header-container">
    <div class="logo-container">
        <img src="wvsu.png" alt="WVSU Logo" width="100" height="100">
    </div>
    <h1 class="white-text">FOOD COURT RENTAL MANAGEMENT SYSTEM</h1>
</div>
<hr>

<div class="container1">
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
    $sql = "SELECT COUNT(*) AS total_rented FROM tenant";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $total_rented = $row['total_rented'];

    // Fetch total number of stalls
    $total_stalls_sql = "SELECT COUNT(*) AS total_stalls FROM stall";
    $total_stalls_result = $conn->query($total_stalls_sql);
    $total_stalls_row = $total_stalls_result->fetch_assoc();
    $total_stalls = $total_stalls_row['total_stalls'];

    // Calculate available stalls
    $available_stalls = $total_stalls - $total_rented;

    echo "<p>Total Tenants Rented: " . $total_rented . "</p>";
    echo "<p>Total Stalls: " . $total_stalls . "</p>";
    echo "<p>Stalls Available: " . $available_stalls . "</p>";

    // Close the database connection
    $conn->close();
    ?>
</div>

<div>
    <a href="user_stall.php" class="button">STALL</a>
    <a href="login.php" class="button logout">LOG OUT</a>
</div>

</body>
</html>
