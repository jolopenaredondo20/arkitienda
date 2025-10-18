<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <style>
    /* Basic CSS for styling the dashboard */
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
    }
    .sidebar {
      width: 250px;
      background-color: #333;
      color: #fff;
      height: 100vh;
      float: left;
    }
    .content {
      margin-left: 250px;
      padding: 20px;
    }
    .menu-item {
      padding: 10px;
    }
    .menu-item a {
      color: #fff;
      text-decoration: none;
    }
    .menu-item:hover {
      background-color: #555;
    }
    .menu-item:hover a {
      color: #fff;
    }
    .header {
      background-color: #333;
      color: white;
      padding: 15px;
    }
  </style>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            text-align: center;
            padding: 8px;
            border-bottom: 1px solid black;
            border-right: 1px solid black; 
            position: relative; 
            border: 1px solid black; 
        }

        th {
            background-color: #f2f2f2;
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
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }
        .create-btn {
            display: inline-block;
            padding: 6px 12px;
            text-align: center;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
            background-color: blue;
            color: white;

        }

    </style>
</head>
<body>
  <div class="header">
    <h1>FOOD COURT RENTAL MANAGEMENT</h1>
  </div>
  <div class="sidebar">
    <h2>Dashboard</h2>
    <div class="menu">
      <div class="menu-item"><a href="http://localhost/System/stall.php">STAL</a></div>
      <div class="menu-item"><a href="#">TENANT</a></div>
      <div class="menu-item"><a href="#">PAYMENT</a></div>
      <div class="menu-item"><a href="#">REPORT</a></div>
    </div>
  </div>
  <div class="content">
    <?php include('db.php');

// Retrieve data from the database
$sql = "SELECT * FROM employee";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {

    echo "<a href='employee.php'class='create-btn'>CREATE EMPLOYEE</a>";
    echo "<table border='1' width='50%'>
            <tr>
                <th>STALL NO</th>   
                <th>STATUS</th>
                <th>POSITION</th>
                <th>ADDRESS</th>
                <th colspan='2'>ACTION</th>
            </tr>"; 
        while($row = mysqli_fetch_assoc($result)) {
                $id =  $row['id'];

            echo "<tr>  
          <td>" . $row["name"]. "</td> 
         <td>" . $row["status"]. "</td> 
         <td> " . $row["position"]. "</td> 
         <td>" . $row["address"]. "</td> 
         <td><a href='update.php?user_id=$id' class='edit-btn'>EDIT</a></td>
         <td><a href='delete.php?user_id=$id' class='delete-btn'>DELETE</a></td>
         </tr>";
}
echo "</table>"; // Close the table
}
?>

  

  </div>
</body>
</html>
