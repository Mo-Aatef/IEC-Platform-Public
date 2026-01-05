<?php
// admin/header.php
require_once '../app/auth.php';
requireRole('admin');

$admin_name = $_SESSION['user_name'];
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IEC Admin Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/admin/admin.css">
    <?php if ($current_page == 'users.php'): ?>
        <link rel="stylesheet" href="../assets/css/admin/users.css">
    <?php endif; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');
            
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('open');
                });

                document.addEventListener('click', function(e) {
                    if (window.innerWidth <= 1024 && 
                        sidebar.classList.contains('open') && 
                        !sidebar.contains(e.target) && 
                        e.target !== toggleBtn) {
                        sidebar.classList.remove('open');
                    }
                });
            }
        });
    </script>
    </head>
<body>

<div class="admin-wrapper">
    
   <aside class="sidebar">
        <div class="sidebar-header">
            <a href="dashboard.php" class="brand-link">
                <div class="brand-logo"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="brand-text">
                    <h1>IEC Admin</h1>
                    <p>Admin Panel</p>
                </div>
            </a>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-chart-line"></i> Overview
            </a>
            <a href="users.php" class="nav-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-users"></i> Users & Tutors
            </a>
            <a href="groups.php" class="nav-item <?php echo $current_page == 'groups.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-layer-group"></i> Groups
            </a>
            <a href="modules.php" class="nav-item <?php echo $current_page == 'modules.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-book-open"></i> Manage Modules
            </a>
            <a href="announcements.php" class="nav-item <?php echo $current_page == 'announcements.php' ? 'active' : ''; ?>">
                <i class="fa-solid fa-bullhorn"></i> Global News
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($admin_name, 0, 1)); ?>
                </div>
                <div style="flex-grow:1;">
                    <div style="font-size:14px; font-weight:700; color:#111827;">
                        <?php echo htmlspecialchars($admin_name); ?>
                    </div>
                    <div style="font-size:12px; color:#6b7280;">Administrator</div>
                </div>
                <a href="../public/sign-out.php" style="color:#ef4444;">
                    <i class="fa-solid fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-header">
            <div>
                <h2 style="margin:0; font-size:20px;">Program Administration</h2>
                <p style="margin:0; color:#6B7280; font-size:13px;">Manage your school settings and data.</p>
            </div>
        
            <button id="sidebarToggle" class="mobile-menu-btn">
                <i class="fa-solid fa-bars"></i>
            </button>
        </header>

        <div class="scrollable-body" <?php if ($current_page == 'group-detail.php' || $current_page == 'group.php'): ?>style="padding-top:0;"<?php endif; ?>>