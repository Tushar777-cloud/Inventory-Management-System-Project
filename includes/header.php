<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<div class="header">
    <button id="sidebarToggle" class="toggle-btn">Menu</button>
    <h2><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h2>
    <div class="user-info">
        Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
    </div>
</div>
