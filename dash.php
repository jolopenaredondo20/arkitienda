<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Side Bar Navigation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex; /* Use flexbox for layout */
        }

        .sidebar {
            height: 100%;
            width: 250px;
            background-color: #111;
            padding-top: 20px;
            flex: 0 0 250px; /* Sidebar width */
        }

        .sidebar h2 {
            color: white;
            text-align: center;
        }

        .sidebar ul {
            list-style-type: none;
            padding: 0;
        }

        .sidebar ul li {
            padding: 10px;
        }

        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
        }

        .sidebar ul li a:hover {
            background-color: #555;
        }

        .content {
            flex: 1; /* Take remaining space */
            padding: 20px;
        }
    </style>
</head>
<body>
    
    <div class="sidebar">
        <h2>Navigation</h2>
        <ul>
            <?php
                $sidebar_items = array(
                    "Home" => "index.php",
                    "About" => "stall.php",
                    "Services" => "services.php",
                    "Contact" => "contact.php"
                );

                foreach ($sidebar_items as $title => $link) {
                    echo "<li><a href='$link'>$title</a></li>";
                }
            ?>
        </ul>
    </div>
    <div class="content">
        <h2>Main Content</h2>
        <p>Welcome to my website!</p>
    </div>
</body>
</html>
