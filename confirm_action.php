<?php
// DB connection
$conn = new mysqli("localhost", "root", "", "rental");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['stall_no'])) {
    $stall_no = $_GET['stall_no'];

    // Get tenant_id from stall table
    $getTenantId = $conn->prepare("SELECT tenant_id FROM stall WHERE stall_no = ?");
    $getTenantId->bind_param("i", $stall_no);
    $getTenantId->execute();
    $getTenantId->bind_result($tenant_id);
    $getTenantId->fetch();
    $getTenantId->close();

    if ($tenant_id) {
        // Update tenant table to set stall_no_rented and start_date
        $updateTenant = $conn->prepare("UPDATE tenant SET stall_no_rented = ?, start_date = NOW() WHERE id = ?");
        $updateTenant->bind_param("ii", $stall_no, $tenant_id);
        $updateTenant->execute();
        $updateTenant->close();

        // Set availability to unavailable (mark as confirmed)
        $updateStall = $conn->prepare("UPDATE stall SET availability = 'unavailable' WHERE stall_no = ?");
        $updateStall->bind_param("i", $stall_no);
        $updateStall->execute();
        $updateStall->close();

        echo "<script>alert('Tenant confirmed!'); window.location.href='confirm.php';</script>";
    } else {
        echo "<script>alert('No tenant assigned to this stall.'); window.location.href='confirm.php';</script>";
    }
}
?>
