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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $contact_number = $_POST['contact_number'];

    // Check if contact number is empty
    if (empty($contact_number)) {
        echo "<script>alert('Contact number is required!');</script>";
    } else {
        // Hash the password for security
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert tenant data into the database
        $sql = "INSERT INTO tenants (username, password, contact_number) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sss", $username, $hashed_password, $contact_number);

        if ($stmt->execute()) {
            echo "<script>alert('Tenant registered successfully!'); window.location.href='tenantLogin.php';</script>";
        } else {
            echo "<script>alert('Error registering tenant.');</script>";
        }

        $stmt->close();
    }
}
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ARKI.TIENDA: Tenant Registration</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: url('jj.jpg') no-repeat center center fixed;
            background-size: cover;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            color: white;
            font-size: 32px;
            margin-top: 50px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
        }
        .container {
            width: 100%;
            max-width: 400px;
            margin: 100px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header img {
            width: 80px;
        }
        .header h2 {
            font-size: 28px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            font-weight: bold;
            color: #333;
            display: block;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 16px;
        }
        .btn {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 18px;
            width: 100%;
        }
        .btn:hover {
            background-color: #218838;
        }
        .error {
            color: red;
            font-size: 14px;
            text-align: center;
            margin-bottom: 15px;
        }
        @media (max-width: 600px) {
            .container {
                padding: 20px;
                width: 80%;
            }
            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <h1>ARKI.TIENDA: Tenant Registration</h1>
    <div class="container">
        <div class="header">
            <img src="www.jpg" alt="ARKI.TIENDA Logo">
            <h2>Register as Tenant</h2>
        </div>

        <!-- Display error if registration fails -->
        <?php if (isset($error)) { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>

        <!-- Registration Form -->
       <form action="tenant_register.php" method="POST">
    <div class="form-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
    </div>

    <div class="form-group">
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>

    <div class="form-group">
        <label for="contact_number">Contact Number</label>
        <input type="text" name="contact_number" id="contact_number" required>
    </div>

    <button type="submit" class="btn">Register</button>
</form>

    </div>
</body>
</html>
