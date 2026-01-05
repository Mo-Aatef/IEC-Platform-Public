<?php
// public/daily-lessons.php
$page_title = "Daily Lessons";
require_once 'student-header.php'; // Includes Auth, DB, Sidebar

if (!isset($_GET['module_id'])) {
    header('Location: modules.php'); exit;
}

$module_id = (int)$_GET['module_id'];
$student_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'] ?? null;

// 1. Fetch Module Info
$sql_mod = "SELECT * FROM modules WHERE id = ?";
$stmt = $conn->prepare($sql_mod);
$stmt->bind_param("i", $module_id);
$stmt->execute();
$module = $stmt->get_result()->fetch_assoc();

if (!$module) die("Module not found");

// 2. Fetch Lessons
$sql_lessons = "
    SELECT l.*, slp.status as user_status 
    FROM lessons l
    LEFT JOIN student_lesson_progress slp ON l.id = slp.lesson_id AND slp.student_id = ?
    WHERE l.module_id = ? 
    ORDER BY l.day_number ASC
";
$stmt = $conn->prepare($sql_lessons);
$stmt->bind_param("ii", $student_id, $module_id);
$stmt->execute();
$lessons = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// 3. Weekly Stats
$completed_days = 0;
foreach($lessons as $l) {
    if(($l['user_status'] ?? '') === 'completed') $completed_days++;
}
$total_days = 6; 
$weekly_percent = round(($completed_days / $total_days) * 100);

// 4. Offline Info
$off_sql = "SELECT * FROM offline_sessions WHERE module_id = $module_id";
$res_off = $conn->query($off_sql);
$offline = ($res_off->num_rows > 0) ? $res_off->fetch_assoc() : null;

$previous_completed = true; 
?>

<link rel="stylesheet" href="../assets/css/student/daily-lessons.css">

<style>
@media (max-width: 900px) {
    .page-header {
        display: flex !important;
        flex-direction: row !important;
        flex-wrap: nowrap !important; /* CRITICAL: Prevents button from dropping down */
        align-items: center;
        justify-content: space-between;
        padding: 16px;
        gap: 12px;
        height: auto;
        min-height: 80px;
    }

    /* Left side takes available space but shrinks if needed */
    .header-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;          /* Grow to fill space */
        min-width: 0;     /* CRITICAL: Allows text truncation to work inside flex */
        width: auto;      /* Override any 100% width settings */
    }

    .back-btn {
        flex-shrink: 0;   /* Don't squash the arrow button */
    }

    .header-title {
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-width: 0;     /* Required for text-overflow to work */
    }

    .header-title h2 { 
        font-size: 16px; 
        line-height: 1.3;
        margin: 0;
        white-space: nowrap;      /* Force single line */
        overflow: hidden;         /* Hide overflow */
        text-overflow: ellipsis;  /* Add ... at end */
    }
    
    .header-title p {
        font-size: 12px;
        color: #6b7280;
        margin: 2px 0 0 0;
        white-space: nowrap;      /* Force single line */
        overflow: hidden;
        text-overflow: ellipsis;  /* Add ... at end */
    }

    /* The Hamburger Button */
    .mobile-menu-btn {
        display: flex !important;
        flex-shrink: 0;    /* NEVER allow this button to shrink */
        width: 40px;
        height: 40px;
        align-items: center;
        justify-content: center;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        margin: 0;         /* Remove margins that might push it */
    }
    
    /* Layout Adjustments for Content Below */
    .progress-section { padding: 16px; margin-bottom: 24px; }
    .lessons-grid { grid-template-columns: 1fr; }
}
</style>

<main class="main-content">
    
    <header class="page-header">
        <div class="header-left">
            <a href="modules.php" class="back-btn">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
            <div class="header-title">
                <h2>Week <?php echo $module['module_number']; ?>: <?php echo htmlspecialchars($module['title']); ?></h2>
                <p><?php echo htmlspecialchars($module['description'] ?? 'Master your weekly topic'); ?></p>
            </div>
        </div>

        <button id="sidebarToggle" class="mobile-menu-btn">
            <i class="fa-solid fa-bars"></i>
        </button>
    </header>

    <div class="content-wrapper">
        
        <section class="progress-section">
            <div class="progress-header">
                <div class="ph-left">
                    <h3>Weekly Progress</h3>
                    <p><?php echo $completed_days; ?> of <?php echo $total_days; ?> days completed</p>
                </div>
                <div class="ph-right">
                    <p class="ph-percent"><?php echo $weekly_percent; ?>%</p>
                    <p class="ph-label">Overall completion</p>
                </div>
            </div>
            <div class="progress-track">
                <div class="progress-fill" style="width: <?php echo $weekly_percent; ?>%;"></div>
            </div>
        </section>

        <div class="section-header">
            <h3 class="section-title">Daily Modules</h3>
            <div class="legend" style="display:none;"> <div class="legend-item"><div class="dot dot-green"></div> Completed</div>
                <div class="legend-item"><div class="dot dot-amber"></div> Active</div>
            </div>
        </div>

        <div class="lessons-grid">
            <?php foreach ($lessons as $lesson): 
                $admin_unlocked = (int)($lesson['is_unlocked'] ?? 0); 
                $user_finished = (($lesson['user_status'] ?? '') == 'completed');
                
                $status = 'locked';
                $card_class = 'card-locked';
                $badge_text = 'LOCKED';
                $icon_class = 'fa-lock';
                $footer_text = 'Locked';

                if ($user_finished) {
                    $status = 'completed';
                    $card_class = 'card-completed';
                    $badge_text = 'COMPLETED';
                    $icon_class = 'fa-check';
                    $previous_completed = true; 
                } elseif (!$admin_unlocked) {
                    $status = 'locked';
                    $card_class = 'card-locked';
                    $badge_text = 'LOCKED';
                    $icon_class = 'fa-lock';
                    $footer_text = 'Instructor has not opened this yet';
                    $previous_completed = false; 
                } elseif ($previous_completed) {
                    $status = 'active';
                    $card_class = 'card-active';
                    $badge_text = 'IN PROGRESS';
                    $icon_class = 'fa-play';
                    $previous_completed = false; 
                } else {
                    $status = 'locked';
                    $card_class = 'card-locked';
                    $badge_text = 'LOCKED';
                    $icon_class = 'fa-lock';
                    $footer_text = 'Complete Day ' . ($lesson['day_number']-1) . ' to unlock';
                    $previous_completed = false;
                }
                $link = ($status == 'locked') ? '#' : "view-lesson.php?id=" . $lesson['id'];
            ?>

            <a href="<?php echo $link; ?>" class="lesson-card <?php echo $card_class; ?>">
                <div class="lc-header">
                    <div class="lc-meta">
                        <div class="lc-badge-row">
                            <p class="lc-day">DAY <?php echo $lesson['day_number']; ?></p>
                            <span class="status-badge"><?php echo $badge_text; ?></span>
                        </div>
                        <h4 class="lc-title"><?php echo htmlspecialchars($lesson['title']); ?></h4>
                    </div>
                    <div class="lc-icon"><i class="fa-solid <?php echo $icon_class; ?>"></i></div>
                </div>

                <p class="lc-desc"><?php echo htmlspecialchars($lesson['description']); ?></p>

                <div class="lc-footer">
                    <?php if ($status == 'completed'): ?>
                        <span class="lc-score">Score: 100%</span>
                        <div class="action-link">Review <i class="fa-solid fa-arrow-right"></i></div>
                    <?php elseif ($status == 'active'): ?>
                        <div class="progress-mini-wrapper">
                            <div class="pm-labels"><span>Progress</span><span>0%</span></div>
                            <div class="pm-track"><div class="pm-fill" style="width: 5%;"></div></div>
                        </div>
                        <div class="action-link" style="color:#4f46e5;">Continue <i class="fa-solid fa-arrow-right"></i></div>
                    <?php else: ?>
                        <span class="footer-text" style="font-size:12px; opacity:0.7;">
                            <i class="fa-solid fa-lock" style="font-size:10px;"></i> <?php echo $footer_text; ?>
                        </span>
                    <?php endif; ?>
                </div>
            </a>
            <?php endforeach; ?>

            <?php if (!empty($group_id)): ?>
            <div class="lesson-card card-offline">
                <div class="lc-header">
                    <div class="lc-meta">
                        <div class="lc-badge-row">
                            <p class="lc-day">DAY 7</p>
                            <span class="status-badge">OFFLINE MEETING</span>
                        </div>
                        <h4 class="lc-title"><?php echo htmlspecialchars($offline['title'] ?? 'Weekly Review'); ?></h4>
                    </div>
                    <div class="lc-icon"><i class="fa-solid fa-users"></i></div>
                </div>

                <p class="lc-desc"><?php echo htmlspecialchars($offline['notes'] ?? 'Join your instructor for the weekly session.'); ?></p>

                <div class="offline-details">
                    <div class="od-item">
                        <i class="fa-regular fa-calendar od-icon"></i>
                        <div class="od-text">
                            <p><?php echo $offline['session_date'] ? date('l, M j', strtotime($offline['session_date'])) : 'Date TBA'; ?></p>
                            <small>
                                <?php echo substr($offline['start_time'] ?? '10:00', 0, 5); ?> - 
                                <?php echo substr($offline['end_time'] ?? '12:00', 0, 5); ?>
                            </small>
                        </div>
                    </div>
                    <div class="od-item">
                        <i class="fa-solid fa-location-dot od-icon"></i>
                        <div class="od-text">
                            <p><?php echo htmlspecialchars($offline['location'] ?? 'Main Hall'); ?></p>
                            <small>IEC Campus</small>
                        </div>
                    </div>
                </div>

                <div class="lc-footer" style="border:none; padding-top:0;">
                    <button class="btn-attendance" onclick="alert('Attendance marking opens on the day of the event.')">Mark Attendance</button>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</main>
</div> </body>
</html>