<?php
// admin/dashboard.php
require_once 'header.php';

// --- 1. HANDLE ACTIONS (POST) ---
$action_feedback = "";

// A. Unlock a Specific Lesson
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unlock_lesson_id'])) {
    $lid = (int)$_POST['unlock_lesson_id'];
    if ($conn->query("UPDATE lessons SET is_unlocked = 1 WHERE id = $lid")) {
        $action_feedback = "<div class='alert-success'><i class='fa-solid fa-check-circle'></i> Lesson unlocked successfully!</div>";
    }
}

// B. Activate a New Module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['activate_module_id'])) {
    $mid = (int)$_POST['activate_module_id'];
    
    // 1. Unlock the Module Globally
    $conn->query("UPDATE modules SET is_global_locked = 0 WHERE id = $mid");
    
    // 2. Ensure Day 1 is Unlocked (Fail-safe)
    $conn->query("UPDATE lessons SET is_unlocked = 1 WHERE module_id = $mid AND day_number = 1");
    
    $action_feedback = "<div class='alert-success'><i class='fa-solid fa-rocket'></i> New Week Activated! Cohort advanced.</div>";
}

// --- 2. DETERMINE ACTIVE COHORT CONTEXT ---
$sql_active_mod = "SELECT * FROM modules WHERE is_global_locked = 0 ORDER BY module_number DESC LIMIT 1";
$active_module = $conn->query($sql_active_mod)->fetch_assoc();

if (!$active_module) {
    // Scenario: Not Started
    $current_state = ['type' => 'not_started', 'label' => 'Program Not Started'];
    $target_mod_sql = "SELECT * FROM modules WHERE module_number = 1";
    $target_mod = $conn->query($target_mod_sql)->fetch_assoc();
    $next_action = 'activate_module';
    $next_id = $target_mod['id'] ?? 0;
    $next_title = "Week 1: " . ($target_mod['title'] ?? 'Start');
    $waiting_count = $conn->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'")->fetch_row()[0];

} else {
    // Scenario: Program Running
    $mod_id = $active_module['id'];
    
    $sql_last_day = "SELECT day_number, id FROM lessons WHERE module_id = $mod_id AND is_unlocked = 1 ORDER BY day_number DESC LIMIT 1";
    $res_last_day = $conn->query($sql_last_day);
    
    if ($res_last_day->num_rows == 0) {
        $current_day = 0;
        $current_lesson_id = 0;
    } else {
        $row = $res_last_day->fetch_assoc();
        $current_day = $row['day_number'];
        $current_lesson_id = $row['id'];
    }

    $current_state = [
        'type' => 'running',
        'module_num' => $active_module['module_number'],
        'day_num' => $current_day,
        'label' => "Week " . $active_module['module_number'] . " · Day " . $current_day
    ];

    // --- DETERMINE NEXT TARGET ---
    if ($current_day < 6) {
        // ACTION: Unlock Next Day
        $target_day = $current_day + 1;
        $next_action = 'unlock_lesson';
        
        $sql_next_lesson = "SELECT * FROM lessons WHERE module_id = $mod_id AND day_number = $target_day";
        $next_lesson_row = $conn->query($sql_next_lesson)->fetch_assoc();
        
        $next_id = $next_lesson_row['id'] ?? 0;
        
        $raw_title = $next_lesson_row['title'] ?? 'Content';
        $prefix = "Day " . $target_day;
        if (stripos($raw_title, $prefix) === 0) {
            $clean_title = trim(substr($raw_title, strlen($prefix)));
            $clean_title = ltrim($clean_title, ":- "); 
        } else {
            $clean_title = $raw_title;
        }
        $next_title = $clean_title;

        if ($current_lesson_id > 0) {
            $waiting_count = $conn->query("SELECT COUNT(*) FROM student_lesson_progress WHERE lesson_id = $current_lesson_id AND status = 'completed'")->fetch_row()[0];
        } else {
            $waiting_count = 0; 
        }

    } else {
        // ACTION: Activate Next Week
        $next_mod_num = $active_module['module_number'] + 1;
        $next_action = 'activate_module';
        
        $sql_next_mod = "SELECT * FROM modules WHERE module_number = $next_mod_num";
        $next_mod_row = $conn->query($sql_next_mod)->fetch_assoc();
        
        if ($next_mod_row) {
            $next_id = $next_mod_row['id'];
            $next_title = "Week $next_mod_num: " . $next_mod_row['title'];
            $waiting_count = $conn->query("SELECT COUNT(*) FROM student_lesson_progress WHERE lesson_id = $current_lesson_id AND status = 'completed'")->fetch_row()[0];
        } else {
            $next_action = 'finished'; 
            $waiting_count = 0;
            $next_title = "Curriculum Complete";
            $next_id = 0;
        }
    }
}

// --- 3. CHECK FOR UNASSIGNED STUDENTS (NEW) ---
$unassigned_count = $conn->query("SELECT COUNT(*) FROM users WHERE role='student' AND (group_id IS NULL OR group_id = 0)")->fetch_row()[0];

// --- 4. PRE-FLIGHT CHECK ---
$has_video = false;
$has_quiz = false;

if ($next_action == 'unlock_lesson' && $next_id > 0) {
    $res_steps = $conn->query("SELECT step_type FROM module_steps WHERE lesson_id = $next_id");
    while($s = $res_steps->fetch_assoc()) {
        if ($s['step_type'] == 'watch') $has_video = true;
        if ($s['step_type'] == 'practice') $has_quiz = true;
    }
} elseif ($next_action == 'activate_module' && $next_id > 0) {
    $sql_day1 = "SELECT id FROM lessons WHERE module_id = $next_id AND day_number = 1";
    $day1 = $conn->query($sql_day1)->fetch_assoc();
    if ($day1) {
        $res_steps = $conn->query("SELECT step_type FROM module_steps WHERE lesson_id = " . $day1['id']);
        while($s = $res_steps->fetch_assoc()) {
            if ($s['step_type'] == 'watch') $has_video = true;
            if ($s['step_type'] == 'practice') $has_quiz = true;
        }
    }
}

$is_content_ready = ($has_video && $has_quiz);
?>

<?php echo $action_feedback; ?>

<div class="pacing-hero">
    <div class="ph-content">
        <span class="ph-label">Active Cohort Spotlight</span>
        <h1 class="ph-title">
            <?php echo $current_state['label']; ?>
        </h1>
        <p class="ph-subtitle">
            This is the furthest point accessible to students. Content beyond this is locked.
        </p>
    </div>
    <div class="ph-stat">
        <div class="ph-stat-num"><?php echo $waiting_count; ?></div>
        
        <div class="ph-stat-label">
            <?php if($waiting_count > 0): ?>
                Students Ready for Next Day
            <?php else: ?>
                Students Waiting
                <div style="font-size:11px; opacity:0.6; font-weight:400; margin-top:2px;">(Ahead of Schedule)</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="admin-grid">
    
    <div class="col-main">
        
        <?php if ($next_action != 'finished'): ?>
            <div class="action-card <?php echo ($next_action == 'activate_module') ? 'highlight' : ''; ?>">
                <div class="ac-header">
                    <div class="ac-icon">
                        <?php if ($next_action == 'activate_module'): ?>
                            <i class="fa-solid fa-rocket"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-lock-open"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="ac-title">
                            <?php echo ($next_action == 'activate_module') ? "Activate Next Week" : "Unlock Next Day"; ?>
                        </h3>
                        <p class="ac-desc">
                            <?php if ($next_action == 'unlock_lesson'): ?>
                                Next: <strong>Day <?php echo $target_day; ?></strong>
                            <?php else: ?>
                                Next: <strong>New Week</strong>
                            <?php endif; ?>
                            <br><span style="font-size:14px; color:#64748b;"><?php echo htmlspecialchars($next_title); ?></span>
                        </p>
                    </div>
                </div>

                <div class="pre-flight-mini">
                    <span style="font-size:11px; color:#64748b; margin-right:8px; display:flex; align-items:center;">CONTENT CHECK:</span>
                    <?php if ($is_content_ready): ?>
                        <div class="pf-item pf-ok">
                            <i class="fa-solid fa-check-circle"></i> Content Ready
                        </div>
                    <?php else: ?>
                        <div class="pf-item pf-warn">
                            <i class="fa-solid fa-triangle-exclamation"></i> This day is missing content
                        </div>
                    <?php endif; ?>
                </div>

                <button class="btn-unlock-big <?php echo ($next_action == 'activate_module') ? 'btn-pulse' : ''; ?>" onclick="openUnlockModal()">
                    Review & <?php echo ($next_action == 'activate_module') ? "Activate" : "Unlock"; ?>
                </button>
            </div>
        <?php else: ?>
             <div class="action-card done">
                <div class="ac-header">
                    <div class="ac-icon"><i class="fa-solid fa-flag-checkered"></i></div>
                    <div>
                        <h3 class="ac-title">Curriculum Complete</h3>
                        <p class="ac-desc">You have reached the end of the program.</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="quick-links-row">
            <a href="modules.php" class="ql-item">
                <i class="fa-solid fa-list-check"></i> Manage Curriculum
            </a>
            <a href="users.php" class="ql-item">
                <i class="fa-solid fa-users"></i> Manage Students
            </a>
            <a href="announcements.php" class="ql-item">
                <i class="fa-solid fa-bullhorn"></i> Post Update
            </a>
        </div>
    </div>

    <div class="col-side">
        <div class="side-card">
            <h4 class="sc-title">System Alerts</h4>
            
            <?php if ($unassigned_count > 0): ?>
                <div class="alert-item urgent">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>Action Required</strong>
                        <p><?php echo $unassigned_count; ?> student(s) have no group.</p>
                        <a href="users.php" style="font-size:11px; font-weight:700; color:#be123c; text-decoration:underline; display:block; margin-top:4px;">Manage Students &rarr;</a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($waiting_count > 0): ?>
                <div class="alert-item urgent">
                    <i class="fa-solid fa-user-clock"></i>
                    <div>
                        <strong>Bottleneck Detected</strong>
                        <p><?php echo $waiting_count; ?> students are waiting for you.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if (!$is_content_ready && $next_action != 'finished'): ?>
                <div class="alert-item warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Content Warning</strong>
                        <p>Next target is incomplete.</p>
                    </div>
                </div>
            <?php endif; ?>
            
            <?php if ($waiting_count == 0 && $is_content_ready && $unassigned_count == 0): ?>
                <div class="alert-item good">
                    <i class="fa-solid fa-check-circle"></i>
                    <div>
                        <strong>Smooth Pacing</strong>
                        <p>You are managing the cohort proactively.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="unlockModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fa-solid fa-shield-halved"></i> Confirm Action</h3>
            <button class="modal-close" onclick="closeUnlockModal()">&times;</button>
        </div>
        
        <div class="modal-body">
            <p class="modal-intro">
                You are about to 
                <strong><?php echo ($next_action == 'activate_module') ? "ACTIVATE A NEW WEEK" : "UNLOCK A NEW DAY"; ?></strong>.
                <br>This action is visible immediately to all students.
            </p>
            
            <div class="checklist">
                <div class="cl-item">
                    <span class="cl-label">Target</span>
                    <span class="cl-val"> 
                        <?php if ($next_action == 'unlock_lesson'): ?>
                                Day: <?php echo $target_day; ?>
                        <?php endif ?>
                         <?php echo htmlspecialchars($next_title); ?>
                    </span>
                </div>
                <div class="cl-item">
                    <span class="cl-label">Audience Impact</span>
                    <span class="cl-val"><?php echo ($waiting_count > 0) ? "$waiting_count Students Waiting" : "None (Pre-Unlock)"; ?></span>
                </div>
                <div class="cl-item">
                    <span class="cl-label">Content Health</span>
                    <span class="cl-val <?php echo $is_content_ready ? 'success' : 'error'; ?>">
                        <?php echo $is_content_ready ? 'Ready' : 'Missing Content'; ?>
                    </span>
                </div>
            </div>

            <?php if (!$is_content_ready): ?>
                <div class="modal-warning">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <div>
                        <strong>Warning: Missing Content</strong>
                        <p style="margin:4px 0 0 0; opacity:0.9;">This target seems empty. Students will see a blank page.</p>
                    </div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <?php if ($next_action == 'activate_module'): ?>
                    <input type="hidden" name="activate_module_id" value="<?php echo $next_id; ?>">
                <?php else: ?>
                    <input type="hidden" name="unlock_lesson_id" value="<?php echo $next_id; ?>">
                <?php endif; ?>
                
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeUnlockModal()">Cancel</button>
                    <button type="submit" class="btn-confirm">
                        <?php echo ($next_action == 'activate_module') ? "Activate Week" : "Confirm Unlock"; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUnlockModal() { document.getElementById('unlockModal').style.display = 'flex'; }
function closeUnlockModal() { document.getElementById('unlockModal').style.display = 'none'; }
</script>