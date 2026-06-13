<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Categories";
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - IMS</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome removed for offline access -->
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>

        <div class="table-container">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <p>Manage product categories.</p>
                <a href="add_category.php" class="btn-save" style="text-decoration:none; padding:10px 20px; font-size:14px;">+ Add New Category</a>
            </div>

            <form action="" method="GET" style="margin-bottom:20px; display:flex;">
                <input type="text" name="search" class="form-control" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>" style="max-width:300px; margin-right:10px;">
                <button type="submit" class="btn-save" style="padding:10px;">Search</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM categories";
                    if ($search) {
                        $search_term = "%$search%";
                        $sql .= " WHERE name LIKE '$search_term' OR description LIKE '$search_term'";
                    }
                    $sql .= " ORDER BY id DESC";
                    
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td>";
                            echo "<a href='edit_category.php?id=" . $row['id'] . "' class='action-btn btn-edit'>Edit</a>";
                            echo "<a href='delete_category.php?id=" . $row['id'] . "' class='action-btn btn-delete' onclick='return confirm(\"Are you sure?\")'>Del</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center;'>No categories found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
