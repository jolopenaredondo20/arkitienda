<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root"; // Default username for XAMPP
$password = ""; // Default password for XAMPP
$dbname = "rental"; // Your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form for changing password is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['change_password'])) {
    $username = $_POST['username'];
    $newPassword = $_POST['password'];

    // Update password in the database
    $sql = "UPDATE admin SET password='$newPassword' WHERE username='$username'";

    if ($conn->query($sql) === TRUE) {
        echo "Password updated successfully: $username";
    } else {
        echo "Error updating password: " . $conn->error;
    }
}

// Check if form for creating new admin is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_admin'])) {
    $newUsername = $_POST['new_username'];
    $newPassword = $_POST['new_password'];

    // Insert new admin into the database
    $sql = "INSERT INTO admin (username, password) VALUES ('$newUsername', '$newPassword')";

    if ($conn->query($sql) === TRUE) {
        echo "New admin created successfully: $newUsername";
    } else {
        echo "Error creating new admin: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Interface</title>
</head>
<body>
    <div class="header">
        <h1> ADMIN</h1>
    </div>
    <center>
    <h2>Change Password</h2>
    <form method="post">
        <label for="username">Username:</label>
        <select name="username" id="username">
            <?php
            // Fetch usernames from the database
            $sql = "SELECT username FROM admin";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<option value='" . $row['username'] . "'>" . $row['username'] . "</option>";
                }
            }
            ?>
        </select><br>
        <label for="password">New Password:</label>
        <input type="password" id="password" name="password"><br>
        <div style="position: relative;">
            <input type="checkbox" onclick="showPassword()"> Show Password
        </div>
         <br>
        <input type="submit" name="change_password" value="Change Password">
        <br><br>
         <a href="tenant_log.php" class="button">Tenant Log</a>
    </form>

        <a href="index.php" class="back-btn">Back</a>
    </form>
</center>
    <style>
        body {
            background-color: #79D7BE;
            font-family: Arial, sans-serif;
            margin: auto;
            padding: 5px;
        }

        h1, h2 {
            margin-top: 20px;
        }

        form {
            margin-bottom: 20px;
        }

        label {
            display: inline-block;
            width: 150px;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 250px;
            padding: 8px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        input[type="submit"],
        button {
            background-color: blue;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px; /* Added margin to separate buttons */
        }

        input[type="submit"]:hover,
        button:hover {
            background-color: skyblue;
        
        }

        .error {
            color: red;
            margin-top: 10px;
        }

        .header {
            background-color: #1E0342;
            color: white;
            padding: 1px;
            text-align: center;
            height: 70px;
        }
        a.back-btn {
    width: 5%;
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

a.back-btn:hover {
    background-color: skyblue;
}
footer {
            text-align: center;
            background-color: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px 0;
            margin-top: 20px;
            position: absolute;
            bottom: 0;
            width: 100%;
        }

        footer p {
            margin: 0;
            font-size: 14px;
        }

    </style>

    <script>
        function showPassword() {
            var passwordField = document.getElementById("password");
            if (passwordField.type === "password") {
                passwordField.type = "text";
            } else {
                passwordField.type = "password";
            }
        }
        function showNewPassword() {
            var newPasswordField = document.getElementById("new_password");
            if (newPasswordField.type === "password") {
                newPasswordField.type = "text";
            } else {
                newPasswordField.type = "password";
            }
        }
    </script>
        <!-- Footer Section -->
</body>
</html>

<?php
// Close database connection
$conn->close();
?>
