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

// Fetch contact number from URL
$contact_no = isset($_GET['contact_no']) ? $_GET['contact_no'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $contact_no = $_POST['contact_no'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!');</script>";
    } else {
        // Check if the contact number already exists in the tenant table
        $check_sql = "SELECT id FROM tenant WHERE contact_NO = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $contact_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo "<script>alert('Tenant does not exist!');</script>";
        } else {
            $row = $result->fetch_assoc();
            $tenant_id = $row['id'];

            // Check if an account is already created
            $account_check = "SELECT * FROM accounts WHERE tenant_id = ?";
            $stmt = $conn->prepare($account_check);
            $stmt->bind_param("i", $tenant_id);
            $stmt->execute();
            $account_result = $stmt->get_result();

            if ($account_result->num_rows > 0) {
                echo "<script>alert('Account already exists for this tenant!'); window.location.href='tenantmanagement.php';</script>";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert new account linked to tenant
                $insert_sql = "INSERT INTO accounts (tenant_id, username, password) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("iss", $tenant_id, $contact_no, $hashed_password);

                if ($stmt->execute()) {
                    echo "<script>alert('Account created successfully!'); window.location.href='manageaccount.php';</script>";
                } else {
                    echo "<script>alert('Error creating account!');</script>";
                }
            }
        }
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-light: #dbeafe;
            --border: #e5e7eb;
            --text: #1f2937;
            --text-light: #6b7280;
            --bg: #f9fafb;
            --card-bg: #ffffff;
            --success: #10b981;
            --error: #ef4444;
            --transition: all 0.2s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 420px;
        }

        .header {
            text-align: center;
            margin-bottom: 32px;
        }

        .header h1 {
            font-weight: 600;
            font-size: 24px;
            margin-bottom: 8px;
            color: var(--text);
        }

        .header p {
            color: var(--text-light);
            font-size: 15px;
            line-height: 1.5;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--border);
        }

        .form-title {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 24px;
            text-align: center;
            color: var(--text);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
            color: var(--text);
        }

        .input-container {
            position: relative;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 15px;
            background-color: var(--card-bg);
            transition: var(--transition);
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        input:read-only {
            background-color: #f9fafb;
            cursor: not-allowed;
            color: var(--text-light);
        }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-light);
            cursor: pointer;
            font-size: 14px;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
        }

        .btn:hover {
            background-color: #1d4ed8;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 24px;
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            font-size: 15px;
            transition: var(--transition);
        }

        .back-link:hover {
            color: #1d4ed8;
            text-decoration: underline;
            text-decoration-thickness: 1px;
        }

        .info {
            margin-top: 24px;
            padding: 16px;
            background-color: var(--primary-light);
            border-radius: 8px;
            font-size: 14px;
            color: var(--text);
        }

        .info i {
            color: var(--primary);
            margin-right: 8px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .card {
                padding: 24px;
            }
            
            .header h1 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Create Tenant Account</h1>
            <p>Set up login credentials for your tenant</p>
        </div>
        
        <div class="card">
            <h2 class="form-title">Account Details</h2>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="contact_no">Contact Number</label>
                    <div class="input-container">
                        <input type="text" id="contact_no" name="contact_no" value="<?= htmlspecialchars($contact_no) ?>" readonly required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-container">
                        <input type="password" id="password" name="password" required>
                        <span class="password-toggle" onclick="togglePassword('password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <div class="input-container">
                        <input type="password" id="confirm_password" name="confirm_password" required>
                        <span class="password-toggle" onclick="togglePassword('confirm_password')">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                </div>
                
                <button type="submit" class="btn">Create Account</button>
            </form>
            
            <a href="manageaccount.php" class="back-link">← Back to Tenant Management</a>
            
            <div class="info">
                <i class="fas fa-info-circle"></i> Password must be at least 8 characters. The contact number must already exist in the tenant database.
            </div>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === "password") {
                field.type = "text";
                icon.className = "fas fa-eye-slash";
            } else {
                field.type = "password";
                icon.className = "fas fa-eye";
            }
        }
    </script>
</body>
</html>