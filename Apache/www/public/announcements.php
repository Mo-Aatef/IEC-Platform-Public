<?php
// public/announcements.php
$page_title = "Announcements - IEC Platform";
require_once 'student-header.php'; 

$group_id = $_SESSION['group_id'] ?? null;
$view_filter = $_GET['view'] ?? 'global';

// Determine Mode
$is_assigned = !empty($group_id);

// Prepare Query
if ($is_assigned) {
    // Student HAS a group
    if ($view_filter == 'group') {
        $sql = "SELECT * FROM announcements WHERE group_id = ? ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $group_id);
    } else {
        // Global view (default)
        $sql = "SELECT * FROM announcements WHERE group_id IS NULL ORDER BY created_at DESC";
        $stmt = $conn->prepare($sql);
    }
} else {
    // Student has NO group (Unassigned) -> Global ONLY
    $sql = "SELECT * FROM announcements WHERE group_id IS NULL ORDER BY created_at DESC";
    $stmt = $conn->prepare($sql);
    // Force view to be global for UI consistency
    $view_filter = 'global';
}

$stmt->execute();
$result = $stmt->get_result();
?>

    <header class="top-header">
        <div class="header-welcome">
            <h2>Announcements</h2>
            <p><?php echo ($view_filter == 'group') ? 'Updates from your class tutor' : 'Global news and updates'; ?></p>
        </div>
        <button id="sidebarToggle" class="mobile-menu-btn">
            <i class="fa-solid fa-bars"></i>
        </button>
    </header>

    <div class="content-body">
        
        <div class="announcement-list">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($ann = $result->fetch_assoc()): 
                    // Styling Logic
                    $is_urgent = $ann['is_urgent'] ?? 0;
                    $type_badge = $is_urgent ? "URGENT" : ($view_filter == 'group' ? "CLASS" : "NEWS");
                    
                    // Colors
                    $badge_color = "badge-gray";
                    $icon_color = "icon-gray";
                    $icon_class = "fa-bullhorn";

                    if ($is_urgent) {
                        $badge_color = "badge-red"; 
                        $icon_color = "icon-red";
                        $icon_class = "fa-triangle-exclamation";
                    } elseif ($view_filter == 'group') {
                        $badge_color = "badge-blue";
                        $icon_color = "icon-blue";
                        $icon_class = "fa-chalkboard-user";
                    }
                ?>
                <div class="ann-card">
                    <div class="ann-icon <?php echo $icon_color; ?>">
                        <i class="fa-solid <?php echo $icon_class; ?>"></i>
                    </div>
                    
                    <div class="ann-content">
                        <h3 class="ann-title"><?php echo htmlspecialchars($ann['title']); ?></h3>
                        <div class="ann-text"><?php echo nl2br(htmlspecialchars($ann['message'])); ?></div>
                        <div class="ann-author">
                            <i class="fa-regular fa-user-circle"></i> 
                            Posted by <?php echo htmlspecialchars($ann['posted_by'] ?? 'IEC Team'); ?>
                        </div>
                    </div>

                    <div class="ann-meta-side">
                        <span class="badge <?php echo $badge_color; ?>"><?php echo $type_badge; ?></span>
                        <span class="ann-time"><?php echo date('M d, Y', strtotime($ann['created_at'])); ?></span>
                    </div>
                </div>
                <?php endwhile; ?>
            
            <?php else: ?>
                <div style="text-align: center; padding: 60px; color: #9ca3af; background: white; border-radius: 12px; border: 1px solid #e5e7eb;">
                    <i class="fa-regular fa-folder-open" style="font-size: 32px; margin-bottom: 12px; display: block;"></i>
                    <p>No announcements found in this section.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</main>
</div>
</body>
</html>