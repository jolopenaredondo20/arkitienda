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

// Delete stall logic
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM stall WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo "<script>alert('Stall deleted successfully!'); window.location.href = 'stall.php';</script>";
        } else {
            echo "Error deleting record: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Fetch stall data — INCLUDE monthly_price
$sql = "SELECT stall.id, stall.stall_no, stall.stall_name, stall.category, stall.stall_section, stall.availability, stall.monthly_price, stall.stall_image
        FROM stall";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stall Management</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Roboto', sans-serif; 
            background-color: #eef1f5; 
            margin: 0; 
            padding: 0; 
        }
        .container { 
            max-width: 1200px; 
            margin: 30px auto; 
            background: #fff; 
            border-radius: 10px; 
            padding: 30px; 
            box-shadow: 0 4px 25px rgba(0,0,0,0.08); 
        }
        .header { 
            background-color: #1e3d59; 
            color: white; 
            padding: 25px; 
            text-align: center; 
            border-radius: 10px 10px 0 0; 
        }
        .header h1 { 
            margin: 0; 
            font-size: 26px; 
        }

        .actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-top: 25px;
            gap: 10px;
        }

        .btn { 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            padding: 10px 16px; 
            border-radius: 6px; 
            font-size: 14px; 
            text-decoration: none; 
            color: white; 
            border: none; 
            cursor: pointer; 
            transition: all 0.25s ease-in-out; 
        }
        .btn:hover { transform: scale(1.03); opacity: 0.9; }
        .add-btn { background-color: #2ecc71; }
        .edit-btn { background-color: #3498db; }
        .delete-btn { background-color: #e74c3c; }

        .search-container {
            position: relative;
            max-width: 260px;
            width: 100%;
            margin-left: auto;
        }

        .search-box {
            width: 100%;
            padding: 10px 40px 10px 14px;
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

        .search-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #aaa;
            font-size: 18px;
            pointer-events: none;
        }

        #availabilityFilter {
            padding: 8px 12px;
            font-size: 14px;
            border: none;
            border-bottom: 2px solid #ccc;
            background-color: transparent;
            cursor: pointer;
            font-weight: 500;
            color: #333;
            max-width: 180px;
            width: 100%;
            transition: border-color 0.3s ease, color 0.3s ease;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            position: relative;
        }

        #availabilityFilter::after {
            content: "\25BC"; 
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            font-size: 12px;
            color: #888;
        }

        #availabilityFilter:hover,
        #availabilityFilter:focus {
            border-bottom-color: #3498db;
            color: #3498db;
            outline: none;
        }

        .stalls-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }

        .stall-card {
            background: #fff;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.2s ease;
        }

        .stall-card:hover {
            border-color: #1e3d59;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .stall-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            align-items: flex-start;
        }

        .stall-no {
            font-size: 18px;
            font-weight: 700;
            color: #1e3d59;
        }

        .stall-name {
            font-size: 16px;
            font-weight: 600;
            margin: 8px 0;
            color: #333;
        }

        .stall-image {
            width: 100%;
            height: 140px;
            margin: 10px 0;
            overflow: hidden;
            border-radius: 6px;
            cursor: pointer;
            position: relative;
        }

        .stall-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .stall-card:hover .stall-image img {
            transform: scale(1.03);
        }

        .stall-details {
            margin: 15px 0;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 14px;
        }

        .detail-label {
            color: #666;
            font-weight: 500;
        }

        .detail-value {
            color: #333;
            font-weight: 500;
        }

        .stall-availability {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            text-align: center;
            margin-top: 8px;
        }

        .available { 
            background-color: #3498db; 
            color: white; 
        }
        .unavailable { 
            background-color: #e74c3c; 
            color: white; 
        }

        .stall-actions {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 15px;
        }

        .action-btns a {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 8px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 13px;
            transition: all 0.25s ease-in-out;
        }
        .action-btns a:hover {
            transform: scale(1.03);
            opacity: 0.9;
        }
        .edit-btn { background-color: #3498db; }
        .delete-btn { background-color: #e74c3c; }

        .empty-state {
            text-align: center;
            color: #888;
            padding: 40px 20px;
            font-style: italic;
            grid-column: 1 / -1;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.9);
        }
        .modal-content {
            margin: auto;
            display: block;
            width: 80%;
            max-width: 900px;
            margin-top: 5%;
            animation: zoom 0.3s;
        }
        @keyframes zoom {
            from {transform: scale(0)} 
            to {transform: scale(1)}
        }
        .close {
            position: absolute;
            top: 15px;
            right: 35px;
            color: #f1f1f1;
            font-size: 40px;
            font-weight: bold;
            transition: 0.3s;
            z-index: 1001;
        }
        .close:hover,
        .close:focus {
            color: #bbb;
            text-decoration: none;
            cursor: pointer;
        }
        .modal-caption {
            margin: 15px auto;
            text-align: center;
            color: #ccc;
            width: 80%;
            max-width: 900px;
            font-size: 18px;
        }

        @media screen and (max-width: 768px) {
            .actions { 
                flex-direction: column; 
                align-items: flex-start; 
            }
            .search-container, #availabilityFilter {
                max-width: 100%;
                width: 100%;
                margin-left: 0;
                margin-top: 10px;
            }
            .stalls-grid {
                grid-template-columns: 1fr;
            }
            .modal-content {
                width: 95%;
            }
            .modal-caption {
                width: 95%;
                font-size: 16px;
            }

            .stall-availability {
                padding: 3px 8px;
                font-size: 11px;
            }

            .detail-label, .detail-value {
                font-size: 13px;
            }
        }
    </style>
    <script>
        function searchStall() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.stall-card');
            
            cards.forEach(card => {
                const stallNo = card.querySelector('.stall-no').textContent.toLowerCase();
                const stallName = card.querySelector('.stall-name').textContent.toLowerCase();
                
                if (stallNo.includes(searchTerm) || stallName.includes(searchTerm)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function filterAvailability() {
            const filterValue = document.getElementById('availabilityFilter').value;
            const cards = document.querySelectorAll('.stall-card');
            
            cards.forEach(card => {
                const availability = card.dataset.availability; // 'available' or 'unavailable'
                
                if (filterValue === 'all') {
                    card.style.display = 'block';
                } else if (filterValue === availability) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function openModal(src, alt) {
            const modal = document.getElementById("imgModal");
            const modalImg = document.getElementById("modalImg");
            const captionText = document.getElementById("caption");
            modal.style.display = "block";
            modalImg.src = src;
            modalImg.alt = alt;
            captionText.innerHTML = alt;
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

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") {
                closeModal();
            }
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Stall Management</h1>
        </div>

        <div class="actions">
            <button onclick="location.href='addstall.php'" class="btn add-btn">
                <i class="fas fa-plus"></i> Add Stall
            </button>

            <select id="availabilityFilter" onchange="filterAvailability()">
                <option value="all">All Availability</option>
                <option value="available">Available</option>
                <option value="unavailable">Unavailable</option>
            </select>

            <div class="search-container">
                <input type="text" id="searchInput" class="search-box" placeholder="Search Stall..." onkeyup="searchStall()">
                <i class="fas fa-search search-icon"></i>
            </div>
        </div>

        <div class="stalls-grid">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $image = !empty($row['stall_image']) ? "uploads/" . $row['stall_image'] : "uploads/default.png";
                    // Normalize availability to lowercase for data attribute
                    $availabilityRaw = strtolower(trim($row['availability']));
                    // Fallback in case value is not standard
                    if ($availabilityRaw !== 'available' && $availabilityRaw !== 'unavailable') {
                        $availabilityRaw = 'unavailable'; // or 'available' based on your logic
                    }
                    $availabilityClass = $availabilityRaw === 'available' ? 'available' : 'unavailable';
                    $availabilityText = ucfirst($availabilityRaw);
                    $price = isset($row['monthly_price']) ? number_format($row['monthly_price'], 2) : '0.00';

                    echo "<div class='stall-card' data-availability='{$availabilityRaw}'>
                        <div class='stall-header'>
                            <div class='stall-no'>{$row['stall_no']}</div>
                            <span class='stall-availability {$availabilityClass}'>{$availabilityText}</span>
                        </div>
                        <div class='stall-name'>{$row['stall_name']}</div>
                        <div class='stall-image' onclick=\"openModal('{$image}', 'Stall {$row['stall_no']} - {$row['stall_name']}')\">
                            <img src='{$image}' alt='Stall Image'>
                        </div>
                        <div class='stall-details'>
                            <div class='detail-item'>
                                <span class='detail-label'>Section:</span>
                                <span class='detail-value'>{$row['stall_section']}</span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Category:</span>
                                <span class='detail-value'>{$row['category']}</span>
                            </div>
                            <div class='detail-item'>
                                <span class='detail-label'>Monthly Price:</span>
                                <span class='detail-value'>₱{$price}</span>
                            </div>
                        </div>
                        <div class='stall-actions action-btns'>
                            <a href='editstall.php?id={$row['id']}' class='edit-btn'><i class='fas fa-edit'></i></a>
                            <a href='?id={$row['id']}' onclick='return confirm(\"Are you sure you want to delete this stall?\")' class='delete-btn'><i class='fas fa-trash'></i></a>
                        </div>
                    </div>";
                }
            } else {
                echo "<div class='empty-state'>No stalls found</div>";
            }
            $conn->close();
            ?>
        </div>
    </div>

    <!-- Full Image Modal -->
    <div id="imgModal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img class="modal-content" id="modalImg">
        <div id="caption" class="modal-caption"></div>
    </div>
</body>
</html>