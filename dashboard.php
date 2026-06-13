<?php
include 'db.php';
session_start();
$page_title = "Dashboard";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IMS</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome removed for offline access -->
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>

        <div class="cards-grid">
            <?php
            // Total Products
            $sql = "SELECT COUNT(*) as total_products FROM inventory";
            $result = $conn->query($sql);
            $total_products = $result->fetch_assoc()['total_products'];

            // Total Units
            $sql = "SELECT SUM(quantity) as total_units FROM inventory";
            $result = $conn->query($sql);
            $total_units = $result->fetch_assoc()['total_units'];
            $total_units = $total_units ? $total_units : 0;

            // Stock Value (Cost Price * Quantity)
            $sql = "SELECT SUM(quantity * cost_price) as stock_value FROM inventory";
            $result = $conn->query($sql);
            $stock_value = $result->fetch_assoc()['stock_value'];
            $stock_value = $stock_value ? $stock_value : 0;

            // Low Stock Items (< 5)
            $sql = "SELECT COUNT(*) as low_stock FROM inventory WHERE quantity < 5";
            $result = $conn->query($sql);
            $low_stock = $result->fetch_assoc()['low_stock'];
            ?>
            
            <div class="card">
                <h3>Total Products</h3>
                <div class="value"><?php echo $total_products; ?></div>
            </div>
            
            <div class="card total-units">
                <h3>Total Units</h3>
                <div class="value"><?php echo $total_units; ?></div>
            </div>
            
            <div class="card" style="border-left-color: #3498db;">
                <h3>Stock Value</h3>
                <div class="value">Rs. <?php echo number_format($stock_value, 2); ?></div>
            </div>
            
            <div class="card low-stock">
                <h3>Low Stock</h3>
                <div class="value"><?php echo $low_stock; ?></div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="card" style="margin-bottom: 30px; border-left: none;">
            <h3>Sales Overview (Last 7 Days)</h3>
            <canvas id="salesChart" style="max-height: 300px;"></canvas>
        </div>

        <?php
        // Fetch last 7 days sales
        $dates = [];
        $sales = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $dates[] = date('M d', strtotime($date));
            
            // Query for that day
            $sql = "SELECT SUM(total_price) as total FROM sales WHERE DATE(sale_date) = '$date'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $sales[] = $row['total'] ? $row['total'] : 0;
        }
        ?>

        <div class="table-container">
            <h3>Recent Items</h3>
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
                    $sql = "SELECT * FROM inventory ORDER BY added_date DESC LIMIT 5";
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
                            echo "<a href='inventory.php?edit=" . $row['id'] . "' class='action-btn btn-edit'>Edit</a>";
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

    <!-- Chart.js and Custom JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Pass PHP data to JS
        window.salesChartData = {
            labels: <?php echo json_encode($dates); ?>,
            datasets: [{
                label: 'Sales (Rs.)',
                data: <?php echo json_encode($sales); ?>,
                backgroundColor: 'rgba(52, 152, 219, 0.2)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        };
    </script>
    <script src="js/main.js"></script>

</body>
</html>
