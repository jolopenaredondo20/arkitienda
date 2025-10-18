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
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];

    // Concatenate full name
    $name = trim($first_name . ' ' . ($middle_name ? $middle_name . ' ' : '') . $last_name);

    $gender = $_POST['gender'];
    $contact_no = $_POST['contact_no'];
    $address = $_POST['address'];
    $start_date = $_POST['start_date'];

    $sql = "INSERT INTO tenant (name, gender, contact_NO, address, start_date) VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("sssss", $name, $gender, $contact_no, $address, $start_date);
        if ($stmt->execute()) {
            echo "<script>alert('Tenant added successfully!'); window.location.href='tenant.php';</script>";
        } else {
            echo "Error adding tenant: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Add Tenant</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
<style>
    /* Reset and base */
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        margin: 0;
        font-family: 'Inter', sans-serif;
        background: linear-gradient(135deg, #f5f7fa 0%, #e4edf9 100%);
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 100vh;
        padding: 20px;
        color: #1f2937;
    }

    .form-wrapper {
        background: white;
        padding: 40px;
        border-radius: 20px;
        max-width: 800px;
        width: 100%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .form-wrapper:hover {
        transform: translateY(-5px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }

    h2 {
        font-weight: 700;
        font-size: 28px;
        margin-bottom: 32px;
        text-align: center;
        color: #1e40af;
        position: relative;
    }

    h2::after {
        content: '';
        position: absolute;
        bottom: -12px;
        left: 50%;
        transform: translateX(-50%);
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #4f46e5, #7c3aed);
        border-radius: 2px;
    }

    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    label {
        display: block;
        font-size: 14px;
        margin-bottom: 10px;
        font-weight: 600;
        color: #374151;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    label::before {
        content: '';
        display: inline-block;
        width: 4px;
        height: 16px;
        background: linear-gradient(to bottom, #4f46e5, #7c3aed);
        border-radius: 2px;
    }

    input[type="text"],
    input[type="date"] {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 15px;
        color: #111827;
        transition: all 0.3s ease;
        background-color: #fafafa;
    }

    input[type="text"]:focus,
    input[type="date"]:focus {
        outline: none;
        border-color: #4f46e5;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
    }

    .radio-group {
        display: flex;
        gap: 28px;
        margin-top: 8px;
    }

    .radio-option {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        transition: all 0.3s ease;
        background-color: #fafafa;
    }

    .radio-option:hover {
        border-color: #4f46e5;
        background-color: #f9fafb;
    }

    .radio-option input[type="radio"] {
        cursor: pointer;
        width: 20px;
        height: 20px;
        accent-color: #4f46e5;
    }

    .radio-option label {
        margin: 0;
        font-weight: 500;
        color: #4b5563;
        cursor: pointer;
    }

    .radio-option label::before {
        display: none;
    }

    .submit-btn {
        width: 100%;
        padding: 16px 0;
        background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        border: none;
        border-radius: 14px;
        font-weight: 700;
        font-size: 17px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(79, 70, 229, 0.4);
        margin-top: 16px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 30px rgba(79, 70, 229, 0.6);
        background: linear-gradient(135deg, #4338ca 0%, #6d28d9 100%);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid {
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .form-wrapper {
            padding: 30px 25px;
        }
        
        h2 {
            font-size: 24px;
        }
        
        .radio-group {
            flex-direction: column;
            gap: 12px;
        }
    }

    @media (max-width: 480px) {
        .form-wrapper {
            padding: 25px 20px;
            margin: 10px;
        }
        
        h2 {
            font-size: 22px;
        }
        
        input[type="text"],
        input[type="date"] {
            padding: 12px 14px;
            font-size: 14px;
        }
    }
</style>
</head>
<body>

<div class="form-wrapper">
    <h2>Add Tenant</h2>
    <form method="POST" action="">
        <div class="form-grid">
            <!-- Column 1 -->
            <div>
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" name="first_name" id="first_name" required>
                </div>
                
                <div class="form-group">
                    <label for="middle_name">Middle Name</label>
                    <input type="text" name="middle_name" id="middle_name">
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" name="last_name" id="last_name" required>
                </div>
                
                <div class="form-group">
                    <label for="contact_no">Contact Number</label>
                    <input type="text" name="contact_no" id="contact_no" required>
                </div>
            </div>
            
            <!-- Column 2 -->
            <div>
                <div class="form-group">
                    <label>Gender</label>
                    <div class="radio-group">
                        <div class="radio-option">
                            <input type="radio" name="gender" value="Male" id="male" required>
                            <label for="male">Male</label>
                        </div>
                        <div class="radio-option">
                            <input type="radio" name="gender" value="Female" id="female" required>
                            <label for="female">Female</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="address">Address</label>
                    <input type="text" name="address" id="address" required>
                </div>
                
                <div class="form-group">
                    <label for="start_date">Start Date</label>
                    <input type="date" name="start_date" id="start_date" required>
                </div>
            </div>
        </div>
        
        <button type="submit" class="submit-btn">Submit</button>
    </form>
</div>

</body>
</html>