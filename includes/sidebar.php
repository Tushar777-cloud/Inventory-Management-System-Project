<div class="sidebar">
    <div class="sidebar-header">
        <h3>IMS</h3>
    </div>
    <ul class="sidebar-nav">
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
        <li><a href="inventory.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'inventory.php' ? 'active' : ''; ?>">Inventory List</a></li>
        <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">Categories</a></li>
        <li><a href="add_item.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'add_item.php' ? 'active' : ''; ?>">Add New Item</a></li>
        <li><a href="report.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'report.php' ? 'active' : ''; ?>">Reports</a></li>
    </ul>
    <div class="sidebar-footer">
        <a href="logout.php">[Logout]</a>
    </div>
</div>
