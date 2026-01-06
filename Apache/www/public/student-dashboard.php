<?php
// public/student-dashboard.php
$page_title = "Dashboard - IEC Platform";
require_once 'student-header.php'; 

// Include logic
require_once __DIR__ . '/../app/leaderboard-logic.php'; 

$user_id = $_SESSION['user_id'];
$group_id = $_SESSION['group_id'] ?? null; 

// =================================================================================
// 1. ROBUST "TWO TRUTHS" LOGIC (Handles Empty DB)
// =================================================================================

// A. ADMIN TRUTH
$sql_admin = "SELECT m.module_number, m.id as mod_id, m.title as mod_title, 
                     l.day_number, l.id as lesson_id, l.title as lesson_title
              FROM modules m 
              JOIN lessons l ON m.id = l.module_id 
              WHERE m.is_global_locked = 0 AND l.is_unlocked = 1 
              ORDER BY m.module_number DESC, l.day_number DESC LIMIT 1";
$admin_res = $conn->query($sql_admin);
$admin_pace = ($admin_res && $admin_res->num_rows > 0) ? $admin_res->fetch_assoc() : null;

// FALLBACK: If DB is empty, set default values
if (!$admin_pace) {
    $admin_pace = [
        'module_number' => 1, 
        'day_number' => 1, 
        'mod_title' => 'Welcome', 
        'lesson_title' => 'Program Starting Soon', 
        'lesson_id' => 0,
        'mod_id' => 0
    ];
}

// B. STUDENT TRUTH
$sql_student = "SELECT m.module_number, l.day_number, l.id as lesson_id, l.title
                FROM lessons l
                JOIN modules m ON l.module_id = m.id
                LEFT JOIN student_lesson_progress slp 
                     ON l.id = slp.lesson_id AND slp.student_id = $user_id
                WHERE (slp.status IS NULL OR slp.status != 'completed')
                ORDER BY m.module_number ASC, l.day_number ASC LIMIT 1";
$student_res = $conn->query($sql_student);
$student_cursor = ($student_res && $student_res->num_rows > 0) ? $student_res->fetch_assoc() : null;

// C. CALCULATE DELTA
$a_score = ($admin_pace['module_number'] * 100) + $admin_pace['day_number'];

if ($student_cursor) {
    $s_score = ($student_cursor['module_number'] * 100) + $student_cursor['day_number'];
    $s_lesson_id = $student_cursor['lesson_id'];
    $s_label = "Day " . $student_cursor['day_number'];
    $s_title = $student_cursor['title'];
} else {
    // If no lessons assigned or all done
    $s_score = 9999; 
    $s_lesson_id = 0;
    $s_label = "Complete";
    $s_title = "All caught up!";
}

// D. DETERMINE UI STATE
$admin_truth_text = "Week " . $admin_pace['module_number'] . " · Day " . $admin_pace['day_number'];

$state_class = "on-track"; 
$headline_text = "";
$sub_text = "";
$btn_text = "";
$btn_link = "#";
$btn_icon = "fa-play";
$btn_disabled = false;

// SAFEGUARD: If no lessons exist in DB at all (ID is 0)
if ($admin_pace['lesson_id'] == 0 && $s_lesson_id == 0) {
    $state_class = "ready";
    $headline_text = "Welcome to IEC";
    $sub_text = "Curriculum is currently being updated.";
    $btn_text = "No Lessons Yet";
    $btn_disabled = true; // Prevents clicking
} 
elseif ($s_score < $a_score) {
    // BEHIND
    $state_class = "behind";
    $headline_text = "You are currently on " . $s_label;
    $sub_text = $s_title;
    $btn_text = "Continue " . $s_label;
    $btn_link = "view-lesson.php?id=" . $s_lesson_id;
} 
elseif ($s_score == $a_score) {
    // ON TRACK
    $state_class = "ready";
    $headline_text = "Ready for today's session";
    $sub_text = $admin_pace['lesson_title'];
    $btn_text = "Start " . $s_label;
    $btn_link = "view-lesson.php?id=" . $admin_pace['lesson_id'];
    $btn_icon = "fa-rocket";
} 
else {
    // DONE
    $state_class = "done";
    $headline_text = "You are up to date! 🎉";
    $sub_text = "Great job keeping up with the pace.";
    $btn_text = "Review Today's Lesson";
    $btn_link = "view-lesson.php?id=" . $admin_pace['lesson_id'];
    $btn_icon = "fa-rotate-left";
    $btn_style = "background:rgba(255,255,255,0.2); color:white; border:1px solid rgba(255,255,255,0.4);"; 
}

// Leaderboard Logic (Safe Handling)
$leaderboard_data = [];
if ($admin_pace['mod_id'] > 0) {
    $leaderboard_data = getWeeklyLeaderboard($conn, $admin_pace['mod_id']);
}

// Announcements (Safe Handling)
if (empty($group_id)) {
    $ann_query = "SELECT * FROM announcements WHERE group_id IS NULL ORDER BY created_at DESC LIMIT 3";
    $stmt = $conn->prepare($ann_query);
} else {
    $ann_query = "SELECT * FROM announcements WHERE group_id IS NULL OR group_id = ? ORDER BY created_at DESC LIMIT 3";
    $stmt = $conn->prepare($ann_query);
    $stmt->bind_param('i', $group_id);
}
$stmt->execute();
$announcements = $stmt->get_result();
?>

    <header class="top-header">
        <div class="header-welcome">
            <h2>Welcome back, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?>!</h2>
            <p>Here is your personalized focus for today.</p>
        </div>
        
        <div style="display:flex; align-items:center; gap:16px;">
            <button class="notif-btn"><i class="fa-regular fa-bell"></i><span class="notif-dot"></span></button>
        </div>
    </header>

    <div class="content-body">
        
        <?php if (empty($group_id)): ?>
        <div class="notice-card">
            <div class="notice-icon"><i class="fa-solid fa-user-group"></i></div>
            <div class="notice-content">
                <h4>Group Assignment Pending</h4>
                <p>You can continue learning normally. A tutor will be assigned to you soon.</p>
            </div>
        </div>
        <?php endif; ?>

        <div class="dashboard-hero <?php echo $state_class; ?>">
            <div class="hero-meta-row">
                <span class="hero-badge-pill">Class Pace</span>
                <span class="hero-meta-text"><?php echo $admin_truth_text; ?></span>
            </div>
            <h1 class="hero-title"><?php echo $headline_text; ?></h1>
            <p class="hero-sub"><?php echo htmlspecialchars($sub_text); ?></p>
            
            <?php if(!$btn_disabled): ?>
            <a href="<?php echo $btn_link; ?>" class="btn-hero" style="<?php echo isset($btn_style) ? $btn_style : ''; ?>">
                <i class="fa-solid <?php echo $btn_icon; ?>"></i> <?php echo $btn_text; ?>
            </a>
            <?php else: ?>
            <div class="btn-hero" style="background:rgba(255,255,255,0.5); cursor:not-allowed;">
                <i class="fa-solid fa-ban"></i> <?php echo $btn_text; ?>
            </div>
            <?php endif; ?>
        </div>

        <div class="dashboard-bottom">
            
            <div class="lb-card-container">
                <div class="lb-header"><h3 class="lb-title">Class Leaderboard</h3></div>
                <div class="lb-grid">
                    <?php if (empty($leaderboard_data)): ?>
                        <div class="empty-lb-state">
                            <i class="fa-solid fa-chart-simple"></i>
                            <p>Leaderboard updates as the week progresses.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($leaderboard_data as $student): 
                            $rank = $student['rank'];
                            $initials = strtoupper(substr($student['name'], 0, 2));
                            $row_class = 'lb-standard'; $circle_class = 'c-gray';
                            if ($rank == 1) { $row_class = 'lb-gold'; $circle_class = 'c-gold'; }
                            elseif ($rank == 2) { $row_class = 'lb-silver'; $circle_class = 'c-silver'; }
                            elseif ($rank == 3) { $row_class = 'lb-bronze'; $circle_class = 'c-bronze'; }
                            elseif ($student['is_me']) { $row_class = 'lb-me'; $circle_class = 'c-indigo'; }
                        ?>
                        <div class="lb-row <?php echo $row_class; ?>">
                            <div class="lb-flex-left">
                                <div class="lb-circle <?php echo $circle_class; ?>"><?php echo $rank; ?></div>
                                <div class="lb-avatar-initials"><?php echo $initials; ?></div>
                                <div>
                                    <p class="lb-name">
                                        <?php echo htmlspecialchars($student['name']); ?> 
                                        <?php if($student['is_me']): ?> <span>(You)</span><?php endif; ?>
                                    </p>
                                    <p class="lb-status"><?php echo ($student['days_count'] >= 6) ? "Week Completed" : "In Progress"; ?></p>
                                </div>
                            </div>
                            <div class="lb-stats">
                                <p class="lb-stat-label">Points</p>
                                <p class="lb-stat-value"><?php echo $student['points']; ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div>
                <div class="content-card">
                    <div class="card-header"><h3 class="card-title">Recent Announcements</h3></div>
                    <div class="announcement-list">
                        <?php if ($announcements && $announcements->num_rows > 0): ?>
                            <?php while($ann = $announcements->fetch_assoc()): ?>
                                <div class="ann-item">
                                    <div class="ann-date"><span><?php echo date('M', strtotime($ann['created_at'])); ?></span><span><?php echo date('d', strtotime($ann['created_at'])); ?></span></div>
                                    <div>
                                        <h4 style="font-size:14px; font-weight:700; margin:0 0 4px 0;"><?php echo htmlspecialchars($ann['title']); ?></h4>
                                        <p style="font-size:12px; color:#6b7280; margin:0;"><?php echo htmlspecialchars(substr($ann['message'], 0, 40)); ?>...</p>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div style="text-align:center; padding:40px 20px; color:#9ca3af;">
                                <i class="fa-regular fa-folder-open" style="font-size:32px; margin-bottom:12px; opacity:0.5;"></i>
                                <p style="font-size:13px;">No announcements yet.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

</main>
</div>
</body>
</html>