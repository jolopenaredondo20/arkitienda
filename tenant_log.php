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

// Handle search query
$search = "";
if(isset($_GET['search'])){
    $search = $_GET['search'];
}

// Fetch data from tenant_log table based on search query or fetch all records if search query is empty
if (!empty($search)) {
    $sql = "SELECT * FROM tenant_log WHERE name LIKE '%$search%' OR tenant_id LIKE '%$search%'";
} else {
    $sql = "SELECT * FROM tenant_log";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenant Log</title>
        <div class="header">
        <h1> TENANT LOG</h1>
    </div>
     <div class="header1">
        <div class="search-container">
    <form action="#" method="GET" id="searchForm">
        <input type="text" placeholder="Search tenant name.." name="search" id="searchInput" value="<?php echo $search; ?>">
        <button type="submit">Search</button>
    </form>
</div>
    </div>
    <div class="header2">
           <button onclick="location.href='admin.php'" class='back-btn'>BACK</button></div>
    <table>
        <thead>
    <tr>
       <style>
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
          height: 25px;

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
    <style>
        table {

            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        /* CSS for search bar */
        .search-container {
            float: right;
            margin-top: 20px;
        }
        .search-container input[type=text] {
            padding: 6px;
            margin-top: 8px;
            font-size: 17px;
            border: none;
        }
        .search-container button {
            float: right;
            padding: 6px 10px;
            margin-top: 8px;
            margin-right: 16px;
            background: #ddd;
            font-size: 17px;
            border: none;
            cursor: pointer;
        }
    </style>
</head>
<body>

<!-- Search bar -->

<table>
    <tr>
        <th>ID</th>
        <th>Log Date</th>
        <th>Rented Stall no.</th>
        <th>Name</th>
        <th>Address</th>
        <th>Contact No.</th>
        <th>Date Rented</th>
        <th>School Year Rented</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["id"] . "</td>";
            echo "<td>" . $row["log_date"] . "</td>";
            echo "<td>" . $row["stall_no_rented"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["address"] . "</td>";
            echo "<td>" . $row["contact_no"] . "</td>";
            echo "<td>" . $row["start_date"] . "</td>";
            echo "<td>" . $row["school_year"] . "</td>";

            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='8'>No records found</td></tr>";
    }
    ?>
</table>

<script>
    // Function to toggle clear button visibility based on input value
    function toggleClearButton() {
        var input = document.getElementById('searchInput');
        var clearButton = document.querySelector('.search-container button.clear');
        clearButton.style.display = input.value.length ? 'block' : 'none';
    }

    // Function to clear search input
    function clearSearch() {
        document.getElementById('searchInput').value = '';
        document.getElementById('searchForm').submit();
    }
</script>
</body>
</html>
