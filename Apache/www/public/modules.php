<?php
// public/modules.php
$page_title = "Modules - IEC Platform";
require_once 'student-header.php'; 

// Fetch Data
$user_id = $_SESSION['user_id'];
$sql = "
    SELECT m.id, m.module_number, m.title, m.is_global_locked, p.status as student_status
    FROM modules m
    LEFT JOIN student_module_progress p ON m.id = p.module_id AND p.student_id = ?
    ORDER BY m.module_number ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$modules = [];
$previous_completed = true; 
while ($row = $result->fetch_assoc()) {
    $is_admin_locked = ($row['is_global_locked'] == 1);
    $status = $row['student_status'];
    
    if ($is_admin_locked) $ui_state = 'LOCKED_ADMIN';
    elseif ($status === 'completed') $ui_state = 'DONE';
    elseif ($previous_completed) $ui_state = 'ACTIVE';
    else $ui_state = 'LOCKED_SEQ';

    $row['ui_state'] = $ui_state;
    $modules[] = $row;
    $previous_completed = ($status === 'completed');
}
?>

    <header class="top-header">
        <div class="header-welcome"><h2>Modules</h2></div>
        
        <button id="sidebarToggle" class="mobile-menu-btn">
            <i class="fa-solid fa-bars"></i>
        </button>
    </header>

    <div class="content-body">
        <div class="section-header" style="flex-wrap: wrap; gap: 16px;">
            <h3 class="section-title">All Weeks Overview</h3>
            <div class="legend" style="flex-wrap: wrap;">
                <div class="legend-item"><span class="dot dot-green"></span> Completed</div>
                <div class="legend-item"><span class="dot dot-amber"></span> In Progress</div>
                <div class="legend-item"><span class="dot dot-gray"></span> Locked</div>
            </div>
        </div>

        <div class="modules-grid">
            <?php if (empty($modules)): ?>
                <p style="color:#6b7280;">No modules found.</p>
            <?php else: ?>
                <?php foreach ($modules as $mod): ?>
                    <?php 
                        switch ($mod['ui_state']) {
                            case 'DONE':
                                $style='card-completed'; 
                                $icon_cls='icon-completed'; 
                                $icon='fa-check'; 
                                $meta='Completed'; 
                                $st_txt='DONE'; 
                                $txt_clr='text-green'; 
                                $link="daily-lessons.php?module_id=".$mod['id']; 
                                break;
                            case 'ACTIVE':
                                $style='card-active'; 
                                $icon_cls='icon-active'; 
                                $icon='fa-play'; 
                                $meta=($mod['student_status']=='in_progress')?'Resuming...':'Ready to Start'; 
                                $st_txt='ACTIVE'; 
                                $txt_clr='text-amber'; 
                                $link="daily-lessons.php?module_id=".$mod['id']; 
                                break;
                            case 'LOCKED_ADMIN':
                                $style='card-locked'; 
                                $icon_cls='icon-locked'; 
                                $icon='fa-lock'; 
                                $meta='Admin Locked'; 
                                $st_txt='LOCKED'; 
                                $txt_clr='text-gray'; 
                                $link="#"; 
                                break;
                            default:
                                $style='card-locked'; 
                                $icon_cls='icon-locked'; 
                                $icon='fa-lock'; 
                                $meta='Not Started'; 
                                $st_txt='LOCKED'; 
                                $txt_clr='text-gray'; 
                                $link="#"; 
                                break;
                        }
                    ?>
                    <a <?php if($link !== '#') echo "href='$link'"; ?> class="module-card <?php echo $style; ?>" style="text-decoration:none; cursor: <?php echo $link === '#' ? 'not-allowed' : 'pointer'; ?>;">
                        <div>
                            <div class="m-header">
                                <span class="week-num">Week <?php echo $mod['module_number']; ?></span>
                                <div class="icon-circle <?php echo $icon_cls; ?>"><i class="fa-solid <?php echo $icon; ?>"></i></div>
                            </div>
                            <div class="m-title"><?php echo htmlspecialchars($mod['title']); ?></div>
                        </div>
                        <div class="m-footer">
                            <span class="m-meta"><?php echo $meta; ?></span>
                            <span class="m-status <?php echo $txt_clr; ?>"><?php echo $st_txt; ?></span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</main>
</div>
</body>
</html>