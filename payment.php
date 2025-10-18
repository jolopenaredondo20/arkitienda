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

// --- FILTER HANDLING ---
$searchWallet = isset($_GET['searchWallet']) ? trim($_GET['searchWallet']) : '';
$statusFilter = isset($_GET['statusFilter']) ? trim($_GET['statusFilter']) : '';

// Get current month and year
$currentMonth = date('m');
$currentYear = date('Y');

// Step 1: Ensure every tenant has a payment record for current month/year
$ensurePayments = "
    INSERT INTO payment (tenant_id, payment_date, payment_amount, monthly_balance, penalty, payment_status)
    SELECT 
        t.id,
        CONCAT(?, '-', ?, '-01') AS first_day,
        0,
        (s.yearly_price / 12),
        0,
        'unpaid'
    FROM tenant t
    JOIN stall s ON t.stall_no_rented = s.stall_no
    WHERE NOT EXISTS (
        SELECT 1 FROM payment p 
        WHERE p.tenant_id = t.id 
        AND MONTH(p.payment_date) = ? 
        AND YEAR(p.payment_date) = ?
    )
";
$stmt = $conn->prepare($ensurePayments);
$stmt->bind_param("ssii", $currentYear, $currentMonth, $currentMonth, $currentYear);
$stmt->execute();
$stmt->close();

// Step 2: Fetch tenant payment data
$sql = "SELECT 
            tenant.id, 
            tenant.name, 
            tenant.contact_NO, 
            tenant.start_date, 
            tenant.address, 
            tenant.stall_no_rented, 
            stall.stall_section, 
            stall.yearly_price, 
            (stall.yearly_price / 12) AS monthly_price,
            IFNULL(SUM(CASE 
                WHEN MONTH(payment.payment_date) = ? AND YEAR(payment.payment_date) = ? 
                THEN payment.payment_amount ELSE 0 END), 0) AS total_paid_this_month,
            MAX(payment.monthly_balance) AS monthly_balance,
            MAX(payment.penalty) AS penalty,
            MAX(payment.payment_status) AS payment_status
        FROM tenant 
        JOIN stall ON tenant.stall_no_rented = stall.stall_no
        LEFT JOIN payment ON tenant.id = payment.tenant_id
        WHERE 1=1";

$params = [$currentMonth, $currentYear];
$types = "ii";

if (!empty($searchWallet)) {
    $sql .= " AND tenant.contact_NO LIKE ?";
    $searchParam = '%' . $conn->real_escape_string($searchWallet) . '%';
    $params[] = $searchParam;
    $types .= "s";
}

$sql .= " GROUP BY tenant.id";

// Apply status filter after grouping (using HAVING)
if (!empty($statusFilter)) {
    if ($statusFilter === 'paid') {
        $sql .= " HAVING total_paid_this_month >= monthly_price";
    } elseif ($statusFilter === 'unpaid') {
        $sql .= " HAVING total_paid_this_month < monthly_price";
    }
}

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Payment Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #eef1f5; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 30px auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 25px rgba(0,0,0,0.08); }
        .header { background-color: #1e3d59; color:white; padding:20px; text-align:center; border-radius: 10px 10px 0 0; }
        .header h1 { margin:0; font-size:24px; }
        .filter-bar { display: flex; gap: 8px; align-items: center; margin-top: 20px; padding: 8px 0; }
        .filter-group { display: flex; gap: 6px; align-items: center; }
        .filter-btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border-radius: 4px; font-size: 13px; font-weight: 400; border: 1px solid #ddd; background: transparent; color: #666; cursor: pointer; transition: all 0.2s ease; }
        .filter-btn:hover { color: #333; border-color: #999; }
        .filter-btn.active { border-color: #1e3d59; background: #1e3d59; color: white; }
        .filter-btn.paid-active { border-color: #2ecc71; background: #2ecc71; color: white; }
        .filter-btn.unpaid-active { border-color: #e74c3c; background: #e74c3c; color: white; }
        table { width: 100%; border-collapse: collapse; margin-top: 30px; font-size: 14px; }
        th, td { padding: 12px; text-align: center; border-bottom: 1px solid #ddd; }
        th { background: #1e3d59; color: white; }
        .paid { color: green; font-weight: bold; }
        .unpaid { color: red; font-weight: bold; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 6px; font-size: 13px; text-decoration: none; color: white; border: none; cursor: pointer; transition: all 0.25s ease-in-out; }
        .btn:hover { transform: scale(1.03); opacity: 0.9; }
        .pay-btn { background-color: #2ecc71; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Payment Management</h1>
    </div>

    <div class="filter-bar">
        <div class="filter-group">
            <label style="font-weight: 500; color: #555;">Filter by Status:</label>
            <button class="filter-btn <?php echo empty($statusFilter) ? 'active' : ''; ?>" onclick="filterStatus('')">
                <i class="fas fa-list"></i> All
            </button>
            <button class="filter-btn <?php echo $statusFilter === 'paid' ? 'paid-active' : ''; ?>" onclick="filterStatus('paid')">
                <i class="fas fa-check-circle"></i> Paid
            </button>
            <button class="filter-btn <?php echo $statusFilter === 'unpaid' ? 'unpaid-active' : ''; ?>" onclick="filterStatus('unpaid')">
                <i class="fas fa-times-circle"></i> Unpaid
            </button>
        </div>
    </div>

    <script>
        function filterStatus(status) {
            const urlParams = new URLSearchParams(window.location.search);
            if (status) {
                urlParams.set('statusFilter', status);
            } else {
                urlParams.delete('statusFilter');
            }
            window.location.search = urlParams.toString();
        }
    </script>

    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Total Monthly Balance</th>
                <th>Penalty</th>
                <th>Start Date</th>
                <th>Monthly Due Date</th>
                <th>Overdue Date</th>
                <th>Contact No.</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

                // Compute due and overdue dates
                $startDay = date('d', strtotime($row["start_date"]));
                $currentMonthStr = date('Y-m');
                $lastDayOfMonth = date('t', strtotime($currentMonthStr . '-01')); 
                $dueDay = ($startDay > $lastDayOfMonth) ? $lastDayOfMonth : $startDay;
                $monthlyDueDate = $currentMonthStr . '-' . str_pad($dueDay, 2, '0', STR_PAD_LEFT);
                $overdueDate = date('Y-m-d', strtotime($monthlyDueDate . ' +3 days'));

                // Monthly fee & total paid
                $monthlyFee = $row["monthly_price"];
                $totalPaid = $row["total_paid_this_month"];
                $calculatedBalance = max(0, $monthlyFee - $totalPaid);

                // Penalty if overdue
                $penalty = 0;
                if ($calculatedBalance > 0 && date('Y-m-d') > $overdueDate) {
                    $penalty = $monthlyFee * 0.25; // 25%
                }

                // ✅ Add penalty to total monthly balance
                $totalBalance = $calculatedBalance + $penalty;

                $status = ($totalPaid >= $monthlyFee) ? 'paid' : 'unpaid';

                // ✅ Update DB with total (including penalty)
                $update = "UPDATE payment 
                           SET monthly_balance = ?, penalty = ?, payment_status = ?, monthly_due_date = ?, overdue_date = ?
                           WHERE tenant_id = ? 
                           AND MONTH(payment_date) = ? 
                           AND YEAR(payment_date) = ?";
                $stmtUpdate = $conn->prepare($update);
                $stmtUpdate->bind_param("ddsssiii", $totalBalance, $penalty, $status, $monthlyDueDate, $overdueDate, $row["id"], $currentMonth, $currentYear);
                $stmtUpdate->execute();
                $stmtUpdate->close();

                echo "<tr>
                    <td>" . htmlspecialchars($row["name"]) . "</td>
                    <td>₱" . number_format($totalBalance, 2) . "</td>
                    <td>₱" . number_format($penalty, 2) . "</td>
                    <td>" . date('M d, Y', strtotime($row["start_date"])) . "</td>
                    <td>" . date('M d, Y', strtotime($monthlyDueDate)) . "</td>
                    <td>" . date('M d, Y', strtotime($overdueDate)) . "</td>
                    <td>" . htmlspecialchars($row["contact_NO"]) . "</td>
                    <td class='$status'>" . ucfirst($status) . "</td>
                    <td>
                        <form method='GET' action='bills.php'>
                            <input type='hidden' name='tenant_id' value='" . $row["id"] . "'>
                            <button type='submit' class='btn pay-btn'>
                                <i class='fas fa-wallet'></i> Pay
                            </button>
                        </form>
                    </td>
                </tr>";
            }
        } else {
            echo "<tr><td colspan='9'>No tenants found.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php $conn->close(); ?>
