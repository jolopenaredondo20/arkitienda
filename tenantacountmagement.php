<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_SESSION['tenant_id'])) {
    header("Location: login.php");
    exit();
}

$tenant_id = $_SESSION['tenant_id'];

// Upload Profile Picture
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["profile_pic"])) {
    $target_dir = "uploads/";
    $file_name = basename($_FILES["profile_pic"]["name"]);
    $target_file = $target_dir . time() . "_" . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (in_array($imageFileType, $allowed_types)) {
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $target_file)) {
            $stmt = $conn->prepare("UPDATE tenant SET profile_pic = ? WHERE id = ?");
            $stmt->bind_param("si", $target_file, $tenant_id);
            if ($stmt->execute()) {
                echo "<script>alert('Profile picture uploaded successfully!'); window.location.href='';</script>";
            } else {
                echo "<script>alert('Failed to update profile picture in database.');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('Error uploading file.');</script>";
        }
    } else {
        echo "<script>alert('Only JPG, JPEG, PNG & GIF files are allowed.');</script>";
    }
}

// Fetch tenant profile
$sql = "SELECT name, contact_NO, address, profile_pic FROM tenant WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$tenant = $result->fetch_assoc();
$stmt->close();

// Fetch password
$sql = "SELECT password FROM accounts WHERE tenant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $tenant_id);
$stmt->execute();
$result = $stmt->get_result();
$accounts = $result->fetch_assoc();
$stmt->close();

$account_password = $accounts["password"] ?? "";

// Password Change
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
    $old_password = $_POST["old_password"];
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    $is_hashed = password_get_info($account_password)['algo'] !== 0;
    $password_match = $is_hashed ? password_verify($old_password, $account_password) : $old_password === $account_password;

    if ($password_match) {
        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE accounts SET password = ? WHERE tenant_id = ?";
            $stmt = $conn->prepare($update_sql);
            $stmt->bind_param("si", $hashed_password, $tenant_id);
            if ($stmt->execute()) {
                echo "<script>alert('Password changed successfully!');</script>";
            } else {
                echo "<script>alert('Error updating password.');</script>";
            }
            $stmt->close();
        } else {
            echo "<script>alert('New passwords do not match!');</script>";
        }
    } else {
        echo "<script>alert('Incorrect old password!');</script>";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="theme-color" content="#4361ee">
    <title>Tenant Profile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf9 100%);
            color: #123458;
            min-height: 100vh;
            padding: 16px;
            -webkit-tap-highlight-color: transparent;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            margin: 16px 0 20px;
            font-weight: 600;
            font-size: 16px;
            padding: 12px 20px;
            border-radius: 16px;
            background: white;
            color: #123458;
            border: 2px solid #e4e9f0;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(18, 52, 88, 0.1);
            text-decoration: none;
            width: fit-content;
            touch-action: manipulation;
        }

        .back-button i {
            margin-right: 10px;
            font-size: 18px;
        }

        .back-button:active {
            transform: translateY(1px);
        }

        .back-button:hover {
            background: #123458;
            color: white;
            border-color: #123458;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(18, 52, 88, 0.2);
        }

        .container {
            background: white;
            padding: 24px 16px;
            border-radius: 24px;
            box-shadow: 0 8px 32px rgba(18, 52, 88, 0.15);
            width: 100%;
            max-width: 100%;
            margin: 0 auto;
        }

        h2 {
            text-align: center;
            font-weight: 700;
            font-size: 20px;
            margin-bottom: 20px;
            color: #123458;
            padding: 0 8px;
        }

        .profile-section,
        .password-section {
            margin-top: 20px;
        }

        .profile-pic {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .profile-pic img {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 50%;
            border: 4px solid #4361ee;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            touch-action: manipulation;
        }

        .profile-pic img:active {
            transform: scale(0.98);
        }

        .profile-pic img:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 16px rgba(67, 97, 238, 0.4);
        }

        .form-group {
            margin-bottom: 16px;
        }

        label {
            display: block;
            font-weight: 600;
            margin-bottom: 8px;
            color: #123458;
            font-size: 14px;
        }

        input[type="text"],
        input[type="password"],
        input[type="file"] {
            width: 100%;
            padding: 12px;
            font-size: 14px;
            border: 2px solid #e2e8f0;
            border-radius: 14px;
            background: #f8fafc;
            outline: none;
            transition: all 0.2s ease;
            font-family: 'Poppins', Arial, sans-serif;
        }

        input[type="text"]:focus,
        input[type="password"]:focus,
        input[type="file"]:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
        }

        input[disabled] {
            color: #64748b;
            background: #f1f5f9;
            cursor: not-allowed;
        }

        .btn {
            width: 100%;
            padding: 14px;
            font-size: 15px;
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            border: none;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
            font-family: 'Poppins', Arial, sans-serif;
            touch-action: manipulation;
        }

        .btn:active {
            transform: translateY(1px);
        }

        .btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(67, 97, 238, 0.4);
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
            margin: 24px 0;
            border: none;
        }

        /* Modal styles */
        .modal-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background-color: rgba(18, 52, 88, 0.85);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            touch-action: none;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            position: relative;
            max-width: 95vw;
            max-height: 90vh;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 40px rgba(18, 52, 88, 0.6);
        }

        .modal-content img {
            width: 100%;
            height: auto;
            display: block;
            max-height: 80vh;
            object-fit: contain;
        }

        .modal-close {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #4361ee;
            border: none;
            color: white;
            font-size: 20px;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 10px;
            cursor: pointer;
            opacity: 0.9;
            transition: all 0.2s ease;
            backdrop-filter: blur(4px);
            touch-action: manipulation;
        }

        .modal-close:active {
            transform: scale(0.95);
        }

        .modal-close:hover {
            opacity: 1;
            background: #3a0ca3;
        }

        /* Mobile-first responsive design */
        @media (max-width: 480px) {
            body {
                padding: 12px 8px;
            }

            .back-button {
                margin: 12px 0 16px;
                padding: 10px 16px;
                font-size: 14px;
            }

            .container {
                padding: 20px 12px;
                border-radius: 20px;
            }

            h2 {
                font-size: 18px;
                margin-bottom: 16px;
            }

            .profile-pic img {
                width: 90px;
                height: 90px;
            }

            .form-group {
                margin-bottom: 12px;
            }

            label {
                font-size: 13px;
                margin-bottom: 6px;
            }

            input[type="text"],
            input[type="password"],
            input[type="file"] {
                padding: 10px;
                font-size: 13px;
                border-radius: 12px;
            }

            .btn {
                padding: 12px;
                font-size: 14px;
                border-radius: 12px;
            }

            .divider {
                margin: 20px 0;
            }

            .modal-content {
                max-width: 98vw;
            }
        }

        /* Extra small devices */
        @media (max-width: 360px) {
            body {
                padding: 10px 6px;
            }

            .back-button {
                margin: 10px 0 14px;
                padding: 8px 14px;
                font-size: 13px;
            }

            .container {
                padding: 18px 10px;
                border-radius: 18px;
            }

            h2 {
                font-size: 17px;
            }

            .profile-pic img {
                width: 80px;
                height: 80px;
            }

            .btn {
                padding: 11px;
                font-size: 13px;
            }
        }

        /* Animation for loading */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(8px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container {
            animation: fadeIn 0.4s ease;
        }

        /* Prevent zoom on input focus */
        @media (max-width: 768px) {
            input[type="text"],
            input[type="password"] {
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <a href="tenantUI.php" class="back-button">
        <i class="fas fa-arrow-left"></i> Back
    </a>

    <div class="container">
        <h2>Tenant Profile</h2>

        <div class="profile-section">
            <div class="profile-pic">
                <img
                  src="<?php echo !empty($tenant['profile_pic']) ? htmlspecialchars($tenant['profile_pic']) : 'https://placehold.co/100x100/e2e8f0/64748b?text=No+Photo'; ?>"
                  alt="Profile Picture"
                  id="profileImage"
                  tabindex="0"
                  role="button"
                  aria-label="View profile picture"
                />
            </div>
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="profile_pic">Upload New Profile Picture</label>
                    <input type="file" name="profile_pic" id="profile_pic" accept="image/*" required />
                </div>
                <button type="submit" class="btn">Upload Picture</button>
            </form>

            <hr class="divider" />

            <div class="form-group">
                <label>Name:</label>
                <input type="text" value="<?php echo htmlspecialchars($tenant['name']); ?>" disabled />
            </div>
            <div class="form-group">
                <label>Contact No.:</label>
                <input type="text" value="<?php echo htmlspecialchars($tenant['contact_NO']); ?>" disabled />
            </div>
            <div class="form-group">
                <label>Address:</label>
                <input type="text" value="<?php echo htmlspecialchars($tenant['address']); ?>" disabled />
            </div>
        </div>

        <div class="password-section">
            <h2>Change Password</h2>
            <form action="" method="POST">
                <div class="form-group">
                    <label>Old Password</label>
                    <input type="password" name="old_password" required />
                </div>
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" name="new_password" required />
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" name="confirm_password" required />
                </div>
                <button type="submit" name="change_password" class="btn">Change Password</button>
            </form>
        </div>
    </div>

    <!-- Modal for image view -->
    <div class="modal-overlay" id="imageModal" role="dialog" aria-modal="true">
        <div class="modal-content">
            <button class="modal-close" id="modalClose" aria-label="Close image view">&times;</button>
            <img src="" alt="Profile Picture Enlarged" id="modalImage" />
        </div>
    </div>

    <script>
        const profileImage = document.getElementById('profileImage');
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        const modalClose = document.getElementById('modalClose');

        // Open modal on image click
        profileImage.addEventListener('click', () => {
            modalImage.src = profileImage.src;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        // Keyboard support for image
        profileImage.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                modalImage.src = profileImage.src;
                modal.classList.add('active');
                document.body.style.overflow = 'hidden';
            }
        });

        // Close modal
        function closeModal() {
            modal.classList.remove('active');
            document.body.style.overflow = '';
            modalImage.src = '';
        }

        modalClose.addEventListener('click', closeModal);
        
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.classList.contains('active')) {
                closeModal();
            }
        });

        // Prevent pull-to-refresh on mobile
        document.addEventListener('touchmove', function(e) {
            if (modal.classList.contains('active')) {
                e.preventDefault();
            }
        }, { passive: false });
    </script>
</body>
</html>