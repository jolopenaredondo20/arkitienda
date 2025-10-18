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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);
    $gender = trim($_POST['gender']);
    $contact_NO = trim($_POST['contact_NO']);
    $address = trim($_POST['address']);
    $stall_no_rented = trim($_POST['stall_no_rented']);
    $start_date = date('Y-m-d');
    
    // Check if contact number is provided
    if (empty($contact_NO)) {
        echo "<script>alert('Contact number is required!'); window.location.href='registerTenant.php';</script>";
        exit();
    }
    
    // Insert tenant data into database
    $sql = "INSERT INTO tenant (name, username, password, gender, contact_NO, address, stall_no_rented, start_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssis", $name, $username, $password, $gender, $contact_NO, $address, $stall_no_rented, $start_date);
    
    if ($stmt->execute()) {
        // Update stall availability
        $update_stall_sql = "UPDATE stall SET availability = 'occupied' WHERE stall_no = ?";
        $update_stmt = $conn->prepare($update_stall_sql);
        $update_stmt->bind_param("i", $stall_no_rented);
        $update_stmt->execute();
        $update_stmt->close();

        echo "<script>alert('Tenant registered successfully!'); window.location.href='tenantUI.php';</script>";
    } else {
        echo "<script>alert('Error registering tenant!'); window.location.href='registerTenant.php';</script>";
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Tenant</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; text-align: center; }
        .container { width: 40%; margin: 50px auto; background: white; padding: 20px; border-radius: 5px; box-shadow: 0px 0px 10px 0px #ccc; }
        input, select { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { background: #28a745; color: white; padding: 10px; border: none; cursor: pointer; border-radius: 5px; }
        button:hover { background: #218838; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register New Tenant</h2>
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <select name="gender" required>
                <option value="">Select Gender</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
            </select>
            <input type="text" name="contact_NO" placeholder="Contact Number" required>
            <input type="text" name="address" placeholder="Address" required>
            <input type="number" name="stall_no_rented" placeholder="Stall No. Rented" required>
            <button type="submit">Register</button>
        </form>
    </div>
</body>
</html>