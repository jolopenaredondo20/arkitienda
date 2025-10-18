<?php  
include('db.php');

if (isset($_POST['submit'])) {
    $stall_no = $_POST['stall_no'];
    $stall_name = $_POST['stall_name'];
    $category = $_POST['category'];
    $availability = $_POST['availability'];
    $stall_section = $_POST['stall_section'];
    $monthly_price = $_POST['monthly_price'];
    $yearly_price = $monthly_price * 12;

    $image_name = $_FILES['stall_image']['name'];
    $image_tmp = $_FILES['stall_image']['tmp_name'];
    $image_folder = 'uploads/' . basename($image_name);

    // 🔍 Check if stall number already exists
    $check_sql = "SELECT * FROM stall WHERE stall_no = '$stall_no'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        echo "<script>alert('Error: Stall number already exists! Please use a unique stall number.'); window.history.back();</script>";
    } else {
        // ✅ Proceed if unique
        if (move_uploaded_file($image_tmp, $image_folder)) {
            $sql = "INSERT INTO stall (stall_no, stall_name, category, availability, stall_section, monthly_price, yearly_price, stall_image) 
                    VALUES ('$stall_no', '$stall_name', '$category', '$availability', '$stall_section', '$monthly_price', '$yearly_price', '$image_name')";

            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('Stall added successfully!'); window.location.href='stall.php';</script>";
            } else {
                echo "Error: " . mysqli_error($conn);
            }
        } else {
            echo "<script>alert('Image upload failed.');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Add Stall</title>
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
  input[type="number"],
  input[type="file"],
  select {
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
  input[type="number"]:focus,
  input[type="file"]:focus,
  select:focus {
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

  input[type="file"] {
    background: white;
    border: 2px dashed #d1d5db;
  }

  input[type="file"]:focus {
    border-color: #4f46e5;
    background: white;
  }

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
    input[type="number"],
    input[type="file"],
    select {
      padding: 12px 14px;
      font-size: 14px;
    }
  }
</style>
</head>
<body>

<div class="form-wrapper">
  <h2>Add New Stall</h2>
  <form method="POST" enctype="multipart/form-data" novalidate>
    <div class="form-grid">
      <!-- Column 1 -->
      <div>
        <div class="form-group">
          <label for="stall_no">Stall Number</label>
          <input type="text" name="stall_no" id="stall_no" required autocomplete="off" />
        </div>
        
        <div class="form-group">
          <label for="stall_name">Stall Name</label>
          <input type="text" name="stall_name" id="stall_name" required autocomplete="off" />
        </div>
        
        <div class="form-group">
          <label for="category">Category</label>
          <select name="category" id="category" required>
            <option value="" disabled selected>Select Category</option>
            <option value="Meat Section">Meat Section</option>
            <option value="Fish & Seafood Section">Fish & Seafood Section</option>
            <option value="Vegetables & Fruits">Vegetables & Fruits</option>
            <option value="Rice & Grains">Rice & Grains</option>
            <option value="Eggs & Poultry Products">Eggs & Poultry Products</option>
            <option value="Grocery Section">Grocery Section</option>
            <option value="Spices & Dried Goods">Spices & Dried Goods</option>
            <option value="Grains & Legumes">Grains & Legumes</option>
            <option value="Pharmacy & Health">Pharmacy & Health</option>
            <option value="Clothing & Footwear">Clothing & Footwear</option>
            <option value="School & Office Supply">School & Office Supply</option>
            <option value="Household Items">Household Items</option>
            <option value="Hardware & Construction">Hardware & Construction</option>
            <option value="Flower & Garden">Flower & Garden</option>
            <option value="Electronics & Repair">Electronics & Repair</option>
            <option value="Salon & Beauty">Salon & Beauty</option>
            <option value="Food Stalls & Carinderia">Food Stalls & Carinderia</option>
          </select>
        </div>
      </div>
      
      <!-- Column 2 -->
      <div>
        <div class="form-group">
          <label>Availability</label>
          <div class="radio-group">
            <div class="radio-option">
              <input type="radio" name="availability" value="available" id="available" checked />
              <label for="available">Available</label>
            </div>
            <div class="radio-option">
              <input type="radio" name="availability" value="unavailable" id="unavailable" />
              <label for="unavailable">Unavailable</label>
            </div>
          </div>
        </div>
        
        <div class="form-group">
          <label for="stall_section">Stall Section</label>
          <select name="stall_section" id="stall_section" required>
            <option value="" disabled selected>Select Section</option>
            <option value="Main stall">Main Stall</option>
            <option value="Inside stall">Inside Stall</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="monthly_price">Monthly Price (₱)</label>
          <input type="number" name="monthly_price" id="monthly_price" readonly required />
        </div>
        
        <div class="form-group">
          <label for="stall_image">Upload Image</label>
          <input type="file" name="stall_image" id="stall_image" accept="image/*" required />
        </div>
      </div>
    </div>
    
    <button type="submit" name="submit" class="submit-btn">Add Stall</button>
  </form>
</div>

<script>
  document.getElementById('stall_section').addEventListener('change', function () {
    const prices = {
      "Main stall": 7770,
      "Inside stall": 5170
    };
    document.getElementById('monthly_price').value = prices[this.value] || '';
  });
</script>

</body>
</html>
