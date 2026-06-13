<?php
include 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Initialize Cart
if(!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add Item to Cart
if(isset($_POST['add_to_cart'])) {
    $item_id = $_POST['item_id'];
    $qty = (int)$_POST['qty'];
    
    // Check Stock
    $sql = "SELECT * FROM inventory WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $item_id);
    $stmt->execute();
    $item = $stmt->get_result()->fetch_assoc();
    
    if($item) {
        $current_cart_qty = isset($_SESSION['cart'][$item_id]) ? $_SESSION['cart'][$item_id]['qty'] : 0;
        if($item['quantity'] >= ($qty + $current_cart_qty)) {
            if(isset($_SESSION['cart'][$item_id])) {
                $_SESSION['cart'][$item_id]['qty'] += $qty;
            } else {
                $_SESSION['cart'][$item_id] = [
                    'name' => $item['item_name'],
                    'price' => $item['selling_price'],
                    'cost' => $item['cost_price'],
                    'qty' => $qty,
                    'stock' => $item['quantity']
                ];
            }
        } else {
            $error = "Not enough stock for " . htmlspecialchars($item['item_name']);
        }
    }
}

// Remove from cart
if(isset($_GET['remove_cart'])) {
    $id = $_GET['remove_cart'];
    if(isset($_SESSION['cart'][$id])) {
        unset($_SESSION['cart'][$id]);
    }
    header("Location: sell_item.php");
    exit();
}

// Checkout Process
if(isset($_POST['checkout'])) {
    if(empty($_SESSION['cart'])) {
        $error = "Cart is empty!";
    } else {
        $customer_name = $_POST['customer_name'] ?? 'Walk-in Customer';
        $customer_phone = $_POST['customer_phone'] ?? '';
        $discount = (float)$_POST['discount'] ?? 0;
        
        $total_amount = 0;
        foreach($_SESSION['cart'] as $id => $details) {
            $total_amount += ($details['price'] * $details['qty']);
        }
        $net_amount = $total_amount - $discount;
        
        // 1. Create Invoice
        $inv_sql = "INSERT INTO invoices (customer_name, customer_phone, total_amount, discount, net_amount) VALUES (?, ?, ?, ?, ?)";
        $inv_stmt = $conn->prepare($inv_sql);
        $inv_stmt->bind_param("ssddd", $customer_name, $customer_phone, $total_amount, $discount, $net_amount);
        
        if($inv_stmt->execute()) {
            $invoice_id = $conn->insert_id;
            
            // 2. Loop Cart, Insert Sales, Update Inventory
            foreach($_SESSION['cart'] as $id => $details) {
                $qty_sold = $details['qty'];
                $item_total = $details['price'] * $qty_sold;
                $item_cost = $details['cost'] * $qty_sold;
                $item_profit = $item_total - $item_cost; // Simple profit calc before discount
                
                // Insert Sale
                $sale_sql = "INSERT INTO sales (inventory_id, quantity_sold, total_price, profit, invoice_id) VALUES (?, ?, ?, ?, ?)";
                $sale_stmt = $conn->prepare($sale_sql);
                $sale_stmt->bind_param("iiddi", $id, $qty_sold, $item_total, $item_profit, $invoice_id);
                $sale_stmt->execute();
                
                // Update Inventory
                $update_sql = "UPDATE inventory SET quantity = quantity - ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("ii", $qty_sold, $id);
                $update_stmt->execute();
            }
            
            // Clear Cart and Redirect to Bill
            unset($_SESSION['cart']);
            header("Location: print_bill.php?id=" . $invoice_id);
            exit();
        } else {
            $error = "Checkout Failed: " . $conn->error;
        }
    }
}

$page_title = "Point of Sale (POS)";

// Fetch Available Items for POS
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$items_sql = "SELECT * FROM inventory WHERE quantity > 0";
if($search) {
    $items_sql .= " AND (item_name LIKE '%$search%' OR category LIKE '%$search%')";
}
$items_sql .= " ORDER BY item_name ASC";
$items_result = $conn->query($items_sql);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS - IMS</title>
    <link rel="stylesheet" href="css/style.css">
    <!-- FontAwesome removed for offline access -->
    <style>
        .pos-container { display: flex; gap: 20px; }
        .pos-products { flex: 2; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .pos-cart { flex: 1; background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 15px; margin-top:15px; }
        .product-card { border: 1px solid #eee; padding: 15px; border-radius: 8px; text-align: center; cursor: pointer; transition: 0.2s; }
        .product-card:hover { border-color: #3498db; transform: scale(1.02); }
        .product-card h4 { margin: 0 0 5px 0; font-size: 14px; }
        .product-card .price { color: #27ae60; font-weight: bold; }
        .product-card .stock { font-size: 12px; color: #7f8c8d; }
        
        .cart-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .cart-table th, .cart-table td { padding: 10px; border-bottom: 1px solid #eee; text-align: left; font-size:14px; }
        .cart-table th { background: #f8f9fa; }
        .cart-total { font-size: 18px; font-weight: bold; text-align: right; margin-top:20px; padding-top:10px; border-top:2px solid #eee;}
        
        .checkout-form input { margin-bottom: 10px; }
    </style>
</head>
<body>

    <?php include 'includes/sidebar.php'; ?>

    <div class="main-content">
        <?php include 'includes/header.php'; ?>
        
        <div style="margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">
            <h2>Point of Sale</h2>
            <form method="GET" style="display:flex;">
                <input type="text" name="search" class="form-control" placeholder="Search product..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-save" style="margin-left:5px;">Search</button>
            </form>
        </div>

        <?php if($error): ?>
            <div style="padding:10px; background:#fadbd8; color:#e74c3c; margin-bottom:20px; border-radius:4px;"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="pos-container">
            <!-- Products Panel -->
            <div class="pos-products">
                <h3>Available Products</h3>
                <div class="product-grid">
                    <?php if($items_result && $items_result->num_rows > 0): ?>
                        <?php while($row = $items_result->fetch_assoc()): ?>
                            <div class="product-card" onclick="document.getElementById('add_form_<?php echo $row['id']; ?>').submit();">
                                <h4><?php echo htmlspecialchars($row['item_name']); ?></h4>
                                <div class="price">Rs. <?php echo number_format($row['selling_price'], 2); ?></div>
                                <div class="stock">Stock: <?php echo $row['quantity']; ?></div>
                                <form id="add_form_<?php echo $row['id']; ?>" method="POST" style="display:none;">
                                    <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                                    <input type="hidden" name="qty" value="1">
                                    <input type="hidden" name="add_to_cart" value="1">
                                </form>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No products found/available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Cart Panel -->
            <div class="pos-cart">
                <h3>Current Cart</h3>
                
                <?php if(!empty($_SESSION['cart'])): ?>
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Price</th>
                                <th>Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $grand_total = 0;
                            foreach($_SESSION['cart'] as $id => $details): 
                                $line_total = $details['price'] * $details['qty'];
                                $grand_total += $line_total;
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($details['name']); ?></td>
                                    <td><?php echo $details['qty']; ?></td>
                                    <td>Rs. <?php echo number_format($details['price'], 2); ?></td>
                                    <td>Rs. <?php echo number_format($line_total, 2); ?></td>
                                    <td><a href="?remove_cart=<?php echo $id; ?>" style="color:#e74c3c;">[X]</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <div class="cart-total">
                        Subtotal: Rs. <span id="subtotal"><?php echo number_format($grand_total, 2); ?></span>
                    </div>

                    <form method="POST" class="checkout-form" style="margin-top:20px;">
                        <input type="text" name="customer_name" class="form-control" placeholder="Customer Name (Optional)">
                        <input type="text" name="customer_phone" class="form-control" placeholder="Phone Number (Optional)">
                        <input type="number" name="discount" id="discount_input" class="form-control" placeholder="Discount Amount (Rs.)" min="0" step="1" value="0">
                        
                        <div class="cart-total" style="color:#27ae60; font-size:22px;">
                            Net Payable: Rs. <span id="net_payable"><?php echo number_format($grand_total, 2); ?></span>
                        </div>

                        <button type="submit" name="checkout" class="btn-save" style="width:100%; margin-top:15px; font-size:16px; padding:15px;"> Complete Sale & Print Bill</button>
                    </form>

                    <script>
                        const subtotal = <?php echo $grand_total; ?>;
                        const discountInput = document.getElementById('discount_input');
                        const netPayable = document.getElementById('net_payable');
                        
                        discountInput.addEventListener('input', function() {
                            let discount = parseFloat(this.value) || 0;
                            let net = subtotal - discount;
                            if(net < 0) net = 0;
                            netPayable.innerText = net.toFixed(2);
                        });
                    </script>
                <?php else: ?>
                    <div style="text-align:center; padding:30px 0; color:#95a5a6;">
                        <p>Cart is empty. Click products to add.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</body>
</html>
