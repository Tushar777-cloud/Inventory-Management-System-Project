<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$page_title = "Reports";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - IMS</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome removed for offline access -->
    <style>
        .report-section { margin-bottom: 40px; }
        .report-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .btn-print { background: #95a5a6; color: white; padding: 5px 15px; text-decoration: none; border-radius: 4px; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>

        <div class="cards-grid">
            <?php
            // Items Sold (Transactions Count)
            $sql = "SELECT COUNT(*) as transactions_count FROM sales";
            $result = $conn->query($sql);
            $items_sold_count = $result->fetch_assoc()['transactions_count'];

            // Total Revenue
            $sql = "SELECT SUM(total_price) as total_revenue FROM sales";
            $result = $conn->query($sql);
            $total_revenue = $result->fetch_assoc()['total_revenue'];
            $total_revenue = $total_revenue ? $total_revenue : 0;

            // Total Profit
             $sql = "SELECT SUM(profit) as total_profit FROM sales";
            $result = $conn->query($sql);
            $total_profit = $result->fetch_assoc()['total_profit'];
            $total_profit = $total_profit ? $total_profit : 0;

            // Total Units Sold
            $sql = "SELECT SUM(quantity_sold) as total_units_sold FROM sales";
            $result = $conn->query($sql);
            $total_units_sold = $result->fetch_assoc()['total_units_sold'];
            $total_units_sold = $total_units_sold ? $total_units_sold : 0;
            ?>
            
            <div class="card">
                <h3>ITEMS SOLD (TRANSACTIONS)</h3>
                <div class="value"><?php echo $items_sold_count; ?></div>
                <div style="font-size:12px; color:#888;">Number of sale records</div>
            </div>
            
            <div class="card">
                <h3>TOTAL REVENUE</h3>
                <div class="value">Rs. <?php echo number_format($total_revenue, 2); ?></div>
                <div style="font-size:12px; color:#888;">Money received from sales</div>
            </div>
            
            <div class="card">
                <h3>TOTAL PROFIT</h3>
                <div class="value">Rs. <?php echo number_format($total_profit, 2); ?></div>
                <div style="font-size:12px; color:#888;">Revenue minus cost</div>
            </div>
            
            <div class="card">
                <h3>TOTAL UNITS SOLD</h3>
                <div class="value"><?php echo $total_units_sold; ?></div>
                <div style="font-size:12px; color:#888;">All sold units combined</div>
            </div>
        </div>

        <div class="report-section">
            <div class="report-header">
                <h3>Low Stock Report (Qty < 5)</h3>
                <a href="#" onclick="window.print()" class="btn-print">Print</a>
            </div>
             <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Qty</th>
                            <th>Supplier</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM inventory WHERE quantity < 5";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $badge_class = $row['quantity'] <= 0 ? 'status-out-of-stock' : 'status-low-stock';
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['item_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                                echo "<td><span class='status-badge $badge_class'>" . $row['quantity'] . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['supplier']) . "</td>";
                                echo "<td><a href='edit_item.php?id=".$row['id']."' class='action-btn btn-edit'>Restock</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5'>No low stock items.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-section">
            <div class="report-header">
                <h3>Sales by Category</h3>
            </div>
            <div class="card" style="margin-bottom: 30px; border-left: none;">
                <canvas id="categoryChart" style="max-height: 300px;"></canvas>
            </div>
        </div>

        <div class="report-section">
            <div class="report-header">
                <h3>Recent Sales Transactions</h3>
            </div>
             <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Item Name</th>
                            <th>Qty Sold</th>
                            <th>Total Price</th>
                            <th>Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT sales.*, inventory.item_name FROM sales LEFT JOIN inventory ON sales.inventory_id = inventory.id ORDER BY sales.sale_date DESC LIMIT 5";
                        $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $name = $row['item_name'] ? $row['item_name'] : 'Deleted Item';
                                echo "<tr>";
                                echo "<td>" . $row['sale_date'] . "</td>";
                                echo "<td>" . htmlspecialchars($name) . "</td>";
                                echo "<td>" . $row['quantity_sold'] . "</td>";
                                echo "<td>Rs. " . number_format($row['total_price'], 2) . "</td>";
                                echo "<td>Rs. " . number_format($row['profit'], 2) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                             echo "<tr><td colspan='5'>No sales records found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <?php
    // Fetch Sales grouped by category
    $cat_sales_sql = "
        SELECT 
            i.category, 
            SUM(s.quantity_sold) as total_qty, 
            SUM(s.total_price) as total_amount 
        FROM sales s 
        JOIN inventory i ON s.inventory_id = i.id 
        GROUP BY i.category 
        ORDER BY total_amount DESC
    ";
    $cat_sales_res = $conn->query($cat_sales_sql);
    
    $chart_labels = [];
    $chart_data = [];

    if($cat_sales_res && $cat_sales_res->num_rows > 0) {
        while($c_row = $cat_sales_res->fetch_assoc()) {
            $cat_name = $c_row['category'] ? $c_row['category'] : 'Uncategorized';
            $chart_labels[] = $cat_name;
            $chart_data[] = $c_row['total_amount'];
        }
    }
    ?>
    <script>
        const ctx = document.getElementById('categoryChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($chart_labels); ?>,
                    datasets: [{
                        label: 'Total Sales (Rs.)',
                        data: <?php echo json_encode($chart_data); ?>,
                        backgroundColor: [
                            'rgba(52, 152, 219, 0.6)',
                            'rgba(46, 204, 113, 0.6)',
                            'rgba(155, 89, 182, 0.6)',
                            'rgba(241, 196, 15, 0.6)',
                            'rgba(231, 76, 60, 0.6)',
                            'rgba(52, 73, 94, 0.6)'
                        ],
                        borderColor: [
                            'rgba(52, 152, 219, 1)',
                            'rgba(46, 204, 113, 1)',
                            'rgba(155, 89, 182, 1)',
                            'rgba(241, 196, 15, 1)',
                            'rgba(231, 76, 60, 1)',
                            'rgba(52, 73, 94, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>
