<?php
// public/student-header.php
require_once __DIR__ . '/../app/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Auth Check (Protect all student pages)
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    header('Location: sign-in.php'); 
    exit;
}

// 2. Variables for Sidebar
$user_name = $_SESSION['user_name'] ?? 'Student';
$initials = strtoupper(substr($user_name, 0, 1));
$current_page = basename($_SERVER['PHP_SELF']);
$view_filter = $_GET['view'] ?? 'global'; // For announcements dropdown
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'IEC Platform'; ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="../assets/css/student/student-main.css">
    
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.querySelector('.sidebar');

            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    sidebar.classList.toggle('open');
                });

                document.addEventListener('click', function (e) {
                    // Close sidebar if clicking outside on mobile
                    if (window.innerWidth <= 1024 &&
                        sidebar.classList.contains('open') &&
                        !sidebar.contains(e.target) &&
                        e.target !== toggleBtn) {
                        sidebar.classList.remove('open');
                    }
                });
            }
        });

        // Dropdown Logic
        function toggleDropdown(id) {
            const el = document.getElementById(id);
            const icon = document.getElementById(id + '-icon');
            if (el) {
                el.classList.toggle('open');
                if (icon) {
                    if (el.classList.contains('open')) {
                        icon.style.transform = 'rotate(180deg)';
                    } else {
                        icon.style.transform = 'rotate(0deg)';
                    }
                }
            }
        }
    </script>
</head>
<body>

<div class="app-container">
    
    <aside class="sidebar">
        <div class="sidebar-header">
            <a href="student-dashboard.php" class="brand-link">
                <div class="brand-logo"><i class="fa-solid fa-graduation-cap"></i></div>
                <div class="brand-text"><h1>IEC Platform</h1><p>Student Portal</p></div>
            </a>
        </div>
        
        <nav class="sidebar-nav">
            <ul>
                <li>
                    <a href="student-dashboard.php" class="nav-item <?php echo $current_page == 'student-dashboard.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-table-columns"></i> Dashboard
                    </a>
                </li>
                
                <li>
                    <a href="modules.php" class="nav-item <?php echo $current_page == 'modules.php' ? 'active' : ''; ?>">
                        <i class="fa-solid fa-book-open"></i> Modules
                    </a>
                </li>
                
                <li>
                    <button class="nav-item dropdown-toggle" onclick="toggleDropdown('ann-submenu')">
                        <div style="display:flex; gap:12px; align-items:center;">
                             <i class="fa-solid fa-bullhorn"></i> Announcements
                        </div>
                        <i class="fa-solid fa-chevron-down nav-dropdown-icon" id="ann-submenu-icon" 
                           style="<?php echo $current_page == 'announcements.php' ? 'transform: rotate(180deg);' : ''; ?>"></i>
                    </button>
                    
                    <ul class="nav-submenu <?php echo $current_page == 'announcements.php' ? 'open' : ''; ?>" id="ann-submenu">
                        <li>
                            <a href="announcements.php?view=global" 
                               class="submenu-item <?php echo ($current_page == 'announcements.php' && $view_filter == 'global') ? 'active' : ''; ?>">
                                Global announcements
                            </a>
                        </li>
                        <li>
                            <a href="announcements.php?view=group" 
                               class="submenu-item <?php echo ($current_page == 'announcements.php' && $view_filter == 'group') ? 'active' : ''; ?>">
                                Group announcements
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="avatar-circle"><?php echo $initials; ?></div>
                <div style="flex-grow:1;">
                    <div style="font-size:14px; font-weight:700; color:#111827;">
                        <?php echo htmlspecialchars($user_name); ?>
                    </div>
                    <div style="font-size:12px; color:#6b7280;">Student</div>
                </div>
                <a href="sign-out.php" style="color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i></a>
            </div>
        </div>
    </aside>

    <main class="main-content">