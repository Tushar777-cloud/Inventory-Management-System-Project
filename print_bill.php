<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: sell_item.php");
    exit();
}

$invoice_id = (int)$_GET['id'];

// Fetch invoice
$sql = "SELECT * FROM invoices WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();

if(!$invoice) {
    die("Invoice not found!");
}

// Fetch items
$items_sql = "SELECT s.*, i.item_name FROM sales s LEFT JOIN inventory i ON s.inventory_id = i.id WHERE s.invoice_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $invoice_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

$page_title = "Print Bill";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $invoice_id; ?> - IMS</title>
    <!-- FontAwesome removed for offline access -->
    <style>
        body { font-family: monospace; background: #fff; margin: 20px; padding: 0; color: #000; }
        .bill-container { width: 100%; max-width: 600px; }
        h2, h3 { margin: 5px 0; }
        .info-table, .items-table, .totals-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .items-table th, .items-table td { border: 1px solid #000; padding: 5px; text-align: left; }
        .totals-table td { padding: 5px; border: 1px solid #000; }
        .totals-table td:last-child { text-align: right; font-weight: bold; }
        .no-print { margin-bottom: 20px; }
        .btn { padding: 5px 10px; cursor: pointer; border: 1px solid #000; background: #eee; text-decoration: none; color: #000; }
        
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn">Print Bill</button>
        <a href="sell_item.php" class="btn">New Sale</a>
    </div>

    <div class="bill-container">
        <h2>INVENTORY MANAGEMENT SYSTEM</h2>
        <hr>
        <h3>Sales Invoice</h3>
        <p>Invoice #: <?php echo $invoice_id; ?></p>
        <p>Date: <?php echo date('Y-m-d H:i', strtotime($invoice['created_at'])); ?></p>
        <hr>
        
        <p><strong>Customer Details:</strong></p>
        <p>Name: <?php echo htmlspecialchars($invoice['customer_name']); ?></p>
        <p>Phone: <?php echo htmlspecialchars($invoice['customer_phone']); ?></p>
        <br>

        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Qty</th>
                    <th>Unit Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                    <td><?php echo $item['quantity_sold']; ?></td>
                    <?php 
                        $unit_price = $item['quantity_sold'] > 0 ? ($item['total_price'] / $item['quantity_sold']) : 0; 
                    ?>
                    <td><?php echo number_format($unit_price, 2); ?></td>
                    <td><?php echo number_format($item['total_price'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <table class="totals-table">
            <tr>
                <td>Subtotal</td>
                <td>Rs. <?php echo number_format($invoice['total_amount'], 2); ?></td>
            </tr>
            <tr>
                <td>Discount</td>
                <td>- Rs. <?php echo number_format($invoice['discount'], 2); ?></td>
            </tr>
            <tr>
                <td><strong>Grand Total</strong></td>
                <td><strong>Rs. <?php echo number_format($invoice['net_amount'], 2); ?></strong></td>
            </tr>
        </table>

        <p style="text-align:center; margin-top:30px;">--- End of Bill ---</p>
    </div>

</body>
</html>
