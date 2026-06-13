<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item_name = $_POST['item_name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    $cost_price = $_POST['cost_price'];
    $selling_price = $_POST['selling_price'];
    $category = $_POST['category'];
    $supplier = $_POST['supplier'];

    if (empty($item_name) || empty($quantity)) {
        $error = "Item Name and Quantity are required.";
    } else {
        $sql = "INSERT INTO inventory (item_name, description, quantity, cost_price, selling_price, category, supplier) VALUES (?, ?, ?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssiddss", $item_name, $description, $quantity, $cost_price, $selling_price, $category, $supplier);
            if ($stmt->execute()) {
                $message = "Item added successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}
$page_title = "Add New Item";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Item - IMS</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome removed for offline access -->
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="form-container">
            <div style="margin-bottom:20px;">
                <a href="inventory.php" class="action-btn" style="background:#888;">&larr; Back to List</a>
            </div>
            
            <h2>Product Details</h2>
            
            <?php if($message): ?>
                <div style="padding:10px; background:#d5f5e3; color:#2ecc71; margin-bottom:20px; border-radius:4px;"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div style="padding:10px; background:#fadbd8; color:#e74c3c; margin-bottom:20px; border-radius:4px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Item Name</label>
                    <input type="text" name="item_name" class="form-control" placeholder="e.g. Office Chair" required>
                </div>
                
                <div class="form-row">
                    <div class="form-col form-group">
                        <label>Quantity</label>
                        <input type="number" name="quantity" class="form-control" value="0" required>
                    </div>
                    <div class="form-col form-group">
                        <label>Cost Price (Rs.)</label>
                        <input type="number" step="0.01" name="cost_price" class="form-control" value="0.00">
                    </div>
                </div>

                <div class="form-group">
                    <label>Selling Price (Rs.)</label>
                    <input type="number" step="0.01" name="selling_price" class="form-control" value="0.00">
                </div>

                <div class="form-row">
                    <div class="form-col form-group">
                        <label>Category</label>
                        <select name="category" class="form-control" required>
                            <option value="">Select Category</option>
                            <?php
                            $cat_sql = "SELECT * FROM categories ORDER BY name ASC";
                            $cat_res = $conn->query($cat_sql);
                            if($cat_res && $cat_res->num_rows > 0) {
                                while($cat = $cat_res->fetch_assoc()) {
                                    echo "<option value='".htmlspecialchars($cat['name'])."'>".htmlspecialchars($cat['name'])."</option>";
                                }
                            } else {
                                echo "<option value=''>Error: " . $conn->error . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="form-col form-group">
                        <label>Supplier</label>
                        <input type="text" name="supplier" class="form-control" placeholder="e.g. ABC Suppliers">
                    </div>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Enter item details..."></textarea>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn-save" value="Save Item">
                </div>
            </form>
        </div>
    </div>

</body>
</html>
