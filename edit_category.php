<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM categories WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $category = $result->fetch_assoc();
    
    if (!$category) {
        header("Location: categories.php");
        exit();
    }
} else if (!isset($_POST['id'])) {
    header("Location: categories.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);

    if (empty($name)) {
        $error = "Category Name is required.";
    } else {
        $sql = "UPDATE categories SET name=?, description=? WHERE id=?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $name, $description, $id);
            if ($stmt->execute()) {
                $message = "Category updated successfully!";
                // Refresh data
                $refresh_sql = "SELECT * FROM categories WHERE id = ?";
                $refresh_stmt = $conn->prepare($refresh_sql);
                $refresh_stmt->bind_param("i", $id);
                $refresh_stmt->execute();
                $category = $refresh_stmt->get_result()->fetch_assoc();
                $refresh_stmt->close();
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
$page_title = "Edit Category";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category - IMS</title>
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
            
            <h2>Edit Category</h2>
            
            <?php if($message): ?>
                <div style="padding:10px; background:#d5f5e3; color:#2ecc71; margin-bottom:20px; border-radius:4px;"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div style="padding:10px; background:#fadbd8; color:#e74c3c; margin-bottom:20px; border-radius:4px;"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if(isset($category)): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <input type="hidden" name="id" value="<?php echo $category['id']; ?>">
                <div class="form-group">
                    <label>Category Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($category['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <input type="submit" class="btn-save" value="Update Category">
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
