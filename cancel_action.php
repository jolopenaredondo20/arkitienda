<?php
session_start();

// DB connection
$conn = new mysqli("localhost", "root", "", "rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get stall_no from GET parameter
$stall_no = $_GET['stall_no'];

// Update the stall's status to 'available' and remove the tenant_id
$sql = "UPDATE stall SET tenant_id = NULL, status = 'available' WHERE stall_no = '$stall_no' AND status = 'requested'";

if ($conn->query($sql) === TRUE) {
    // Stall request was canceled successfully
    echo "<script>alert('Stall request canceled successfully.'); window.location.href = 'confirm.php';</script>";
} else {
    echo "Error: " . $conn->error;
}

$conn->close();
?>
