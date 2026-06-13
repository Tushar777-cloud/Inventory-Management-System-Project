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
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (empty($name)) {
        $error = "Category Name is required.";
    } else {
        $sql = "INSERT INTO categories (name, description) VALUES (?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $name, $description);
            if ($stmt->execute()) {
                $message = "Category added successfully!";
            } else {
                if ($conn->errno == 1062) {
                    $error = "Error: Category name already exists.";
                } else {
                    $error = "Error: " . $stmt->error;
                }
            }
            $stmt->close();
        }
    }
}
$page_title = "Add Category";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Category - IMS</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome removed for offline access -->
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div class="form-container">
            <div style="margin-bottom:20px;">
                <a href="categories.php" class="action-btn" style="background:#888;">&larr; Back to Categories</a>
            </div>
            
            <h2>Category Details</h2>
            
            <?php if($message): ?>
                <div style="padding:10px; background:#d5f5e3; color:#2ecc71; margin-bottom:20px; border-radius:4px;"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div style="padding:10px; background:#fadbd8; color:#e74c3c; margin-bottom:20px; border-radius:4px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Electronics" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Enter category details..."></textarea>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn-save" value="Save Category">
                </div>
            </form>
        </div>
    </div>

</body>
</html>
