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

// Fetch only stalls that have renters and use tenant.start_date as date_started
$sql = "SELECT 
            stall.stall_no, 
            stall.stall_name, 
            stall.category, 
            stall.stall_section, 
            stall.monthly_price, 
            stall.stall_image,
            tenant.name AS renter_name,
            tenant.start_date AS date_started
        FROM stall
        INNER JOIN tenant 
            ON stall.stall_no = tenant.stall_no_rented
        WHERE tenant.name IS NOT NULL 
        AND tenant.name != ''";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rental List</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Roboto', sans-serif; background-color: #eef1f5; margin: 0; padding: 0; }
        .container { max-width: 1200px; margin: 30px auto; background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08); }
        .header { background-color: #1e3d59; color: white; padding: 25px; text-align: center; border-radius: 10px 10px 0 0; }
        .header h1 { margin: 0; font-size: 26px; }

        /* Search container aligned right */
        .search-container {
            margin-top: 20px;
            text-align: right;
            position: relative;
            max-width: 300px;
            margin-left: auto;
        }

        /* Search input with icon */
        .search-box {
            width: 100%;
            padding: 10px 40px 10px 14px; /* leave space for icon on the right */
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            background-color: #fafafa;
            box-sizing: border-box;
        }
        .search-box:focus {
            border-color: #2ecc71;
            outline: none;
        }
        /* Search icon */
        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 18px;
            pointer-events: none;
        }

        table { width: 100%; border-collapse: collapse; margin-top: 30px; font-size: 14px; background-color: #fff; border-radius: 8px; overflow: hidden; }
        th, td { padding: 14px 12px; text-align: center; border-bottom: 1px solid #f0f0f0; }
        th { background-color: #1e3d59; color: white; font-weight: 500; }
        tr:hover { background-color: #f9f9f9; }
        img { width: 50px; height: 50px; object-fit: cover; border-radius: 5px; cursor: pointer; }
        .empty-message { text-align: center; color: #888; padding: 20px; font-style: italic; }
        @media screen and (max-width: 768px) {
            table { font-size: 12px; }
            .search-container { max-width: 100%; text-align: left; margin-bottom: 15px; }
            .search-box { width: 100%; }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            padding-top: 60px;
            left: 0; top: 0;
            width: 100%; height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.75);
        }
        .modal-content {
            margin: auto;
            padding: 0;
            max-width: 90vw;
            max-height: 90vh;
            position: relative;
        }
        .modal-content img {
            max-width: 90vw;
            max-height: 90vh;
            width: auto;
            height: auto;
            display: block;
            border-radius: 8px;
            margin: 0 auto;
        }
        .close {
            position: absolute;
            top: 10px; right: 15px;
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            user-select: none;
            transition: color 0.3s ease;
            z-index: 1010;
        }
        .close:hover {
            color: #e74c3c;
        }
    </style>
    <script>
        function searchStall() {
            let input = document.getElementById("searchInput").value.toLowerCase();
            let rows = document.querySelectorAll("tbody tr");
            rows.forEach(row => {
                let stallNo = row.cells[0].textContent.toLowerCase();
                let renter = row.cells[2].textContent.toLowerCase();
                row.style.display = stallNo.includes(input) || renter.includes(input) ? "" : "none";
            });
        }

        // Modal logic
        function openModal(src, alt) {
            const modal = document.getElementById("imgModal");
            const modalImg = document.getElementById("modalImg");
            const captionText = document.getElementById("caption");
            modal.style.display = "block";
            modalImg.src = src;
            modalImg.alt = alt;
            captionText.textContent = alt;
        }

        function closeModal() {
            document.getElementById("imgModal").style.display = "none";
        }

        window.onclick = function(event) {
            const modal = document.getElementById("imgModal");
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Rental</h1>
        </div>

        <div class="search-container">
            <input type="text" id="searchInput" class="search-box" placeholder="Search Stall or Renter..." onkeyup="searchStall()">
            <i class="fas fa-search search-icon"></i>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Stall No.</th>
                    <th>Image</th>
                    <th>Renter</th>
                    <th>Stall Name</th>
                    <th>Section</th>
                    <th>Category</th>
                    <th>Monthly Fee</th>
                    <th>Date Started</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $image = !empty($row['stall_image']) ? "uploads/" . $row['stall_image'] : "uploads/default.png";
                        $dateStarted = !empty($row['date_started']) ? date("F d, Y", strtotime($row['date_started'])) : "N/A";
                        echo "<tr>
                            <td>{$row['stall_no']}</td>
                            <td><img src='{$image}' alt='Stall Image' onclick=\"openModal('{$image}', 'Stall {$row['stall_no']} Image')\"></td>
                            <td>{$row['renter_name']}</td>
                            <td>{$row['stall_name']}</td>
                            <td>{$row['stall_section']}</td>
                            <td>{$row['category']}</td>
                            <td>₱" . number_format($row['monthly_price'], 2) . "</td>
                            <td>{$dateStarted}</td>
                        </tr>";
                    }
                } else {
                    echo "<tr><td colspan='8' class='empty-message'>No rental records found</td></tr>";
                }
                $conn->close();
                ?>
            </tbody>
        </table>
    </div>

    <!-- Modal HTML -->
    <div id="imgModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <div class="modal-content">
            <img id="modalImg" src="" alt="">
            <div id="caption" style="color:white; text-align:center; margin-top:10px;"></div>
        </div>
    </div>
</body>
</html>
