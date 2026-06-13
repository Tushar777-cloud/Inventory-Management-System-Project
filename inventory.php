<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Inventory List";
$search = isset($_GET['search']) ? $_GET['search'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - IMS</title>
    <link rel="stylesheet" href="css/style.css">    <!-- FontAwesome removed for offline access -->
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>

        <div class="table-container">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <p>Manage your product inventory and stock levels.</p>
                <a href="add_item.php" class="btn-save" style="text-decoration:none; padding:10px 20px; font-size:14px;">+ Add New Item</a>
            </div>

            <form action="" method="GET" style="margin-bottom:20px; display:flex;">
                <input type="text" name="search" class="form-control" placeholder="Search by name or category..." value="<?php echo htmlspecialchars($search); ?>" style="max-width:300px; margin-right:10px;">
                <button type="submit" class="btn-save" style="padding:10px;">Search</button>
            </form>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM inventory";
                    if ($search) {
                        $search_term = "%$search%";
                        $sql .= " WHERE item_name LIKE '$search_term' OR category LIKE '$search_term'";
                    }
                    $sql .= " ORDER BY id DESC"; // Default sort
                    
                    $result = $conn->query($sql);

                    if ($result->num_rows > 0) {
                        while($row = $result->fetch_assoc()) {
                            if ($row['quantity'] <= 0) {
                                $status_class = 'status-out-of-stock';
                                $status_text = 'OUT OF STOCK';
                            } elseif ($row['quantity'] < 5) {
                                $status_class = 'status-low-stock';
                                $status_text = 'LOW STOCK';
                            } else {
                                $status_class = 'status-in-stock';
                                $status_text = 'IN STOCK';
                            }
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                            echo "<td>" . $row['quantity'] . "</td>";
                            echo "<td>Rs. " . number_format($row['selling_price'], 2) . "</td>";
                            echo "<td><span class='status-badge $status_class'>$status_text</span></td>";
                            echo "<td>";
                            echo "<a href='sell_item.php?id=" . $row['id'] . "' class='action-btn' style='background-color:#9b59b6;'> Sell</a>";
                            echo "<a href='edit_item.php?id=" . $row['id'] . "' class='action-btn btn-edit'>Edit</a>";
                            echo "<a href='delete_item.php?id=" . $row['id'] . "' class='action-btn btn-delete' onclick='return confirm(\"Are you sure?\")'>Del</a>";
                            echo "</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center;'>No items found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
