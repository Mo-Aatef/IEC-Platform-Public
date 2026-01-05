<?php
// tutor/my-class.php
require_once 'header.php';

$tutor_id = $_SESSION['user_id'];

// 1. GET TUTOR'S GROUP
$group_sql = "SELECT * FROM groups WHERE tutor_id = ? LIMIT 1";
$stmt = $conn->prepare($group_sql);
$stmt->bind_param("i", $tutor_id);
$stmt->execute();
$my_group = $stmt->get_result()->fetch_assoc();

if (!$my_group) {
    echo "<div class='empty-state'>
            <i class='fa-solid fa-chalkboard-user'></i>
            <h3>No Class Assigned</h3>
            <p>You are not assigned to a class yet.</p>
          </div>";
    require_once 'footer.php';
    exit();
}
$group_id = $my_group['id'];

// 2. ADMIN TRUTH (Class Pace)
$sql_pace = "SELECT m.module_number, l.day_number 
             FROM modules m JOIN lessons l ON m.id = l.module_id
             WHERE m.is_global_locked = 0 AND l.is_unlocked = 1
             ORDER BY m.module_number DESC, l.day_number DESC LIMIT 1";
$pace = $conn->query($sql_pace)->fetch_assoc();

$class_mod = $pace['module_number'] ?? 1;
$class_day = $pace['day_number'] ?? 1;
$class_score = ($class_mod * 100) + $class_day;

// 3. FETCH STUDENTS
$sql_students = "
    SELECT u.id, u.name, u.email,
        (SELECT CONCAT(m2.module_number, '-', l2.day_number, '-', l2.id)
         FROM lessons l2 JOIN modules m2 ON l2.module_id = m2.id
         LEFT JOIN student_lesson_progress slp ON l2.id = slp.lesson_id AND slp.student_id = u.id
         WHERE (slp.status IS NULL OR slp.status != 'completed')
         ORDER BY m2.module_number ASC, l2.day_number ASC LIMIT 1
        ) as current_position_str,
        (SELECT AVG(score) 
         FROM student_step_progress ssp 
         JOIN module_steps ms ON ssp.step_id = ms.id 
         WHERE ssp.student_id = u.id AND LOWER(ms.step_type) = 'practice') as avg_score
    FROM users u
    WHERE u.group_id = ? AND u.role = 'student'
    ORDER BY u.name ASC
";
$stmt = $conn->prepare($sql_students);
$stmt->bind_param("i", $group_id);
$stmt->execute();
$result = $stmt->get_result();

// --- HELPER: RENDER ICON CLASS ---
function getStepClass($status, $is_focus) {
    if ($status == 'completed') return 'completed';
    if ($status == 'active' || $is_focus) return 'active';
    return 'pending';
}
function getStepIcon($type) {
    if ($type == 'warmup') return 'fa-mug-hot';
    if ($type == 'watch') return 'fa-play';
    if ($type == 'practice') return 'fa-pen-to-square';
    return 'fa-microphone';
}
?>

<div class="pacing-hero">
        <div class="ph-content">
            <span class="ph-label">Class Management</span>
            <h1 class="ph-title"><?php echo htmlspecialchars($my_group['name']); ?></h1>
            <p class="ph-subtitle">Monitor real-time student progress, grades, and pacing.</p>
        </div>
        
        <div class="ph-stat">
            <div class="ph-stat-num">
                Week <?php echo $class_mod; ?> <span style="opacity:0.5; font-weight:300;">&middot;</span> Day <?php echo $class_day; ?>
            </div>
            <div class="ph-stat-label">Current Class Pace</div>
        </div>
    </div>

    <div class="card" style="padding:0; overflow:hidden;">
        <div class="table-container"> <table class="data-table telemetry-table">
                <thead>
                    <tr>
                        <th style="width: 250px;">Student</th>
                        <th>Pacing Status</th>
                        <th>Avg Quiz Score</th>
                        <th>Current Position</th>
                        <th>Current Day Progress</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($stu = $result->fetch_assoc()): 
                            // PACING LOGIC
                            $is_finished = false;
                            if ($stu['current_position_str']) {
                                list($s_mod, $s_day, $s_lid) = explode('-', $stu['current_position_str']);
                            } else {
                                $s_mod = $class_mod; $s_day = $class_day; $s_lid = 0; 
                                $is_finished = true;
                            }

                            $s_score = ($s_mod * 100) + $s_day;
                            $gap = $class_score - $s_score;
                            if ($gap < 0) { $s_mod = $class_mod; $s_day = $class_day; $is_finished = true; $s_lid = 0; }
                        ?>
                        <tr>
                            <td>
                                <div class="student-info">
                                    <div class="student-avatar">
                                        <?php echo strtoupper(substr($stu['name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div style="font-weight:600; font-size:14px; color:#1e293b;"><?php echo htmlspecialchars($stu['name']); ?></div>
                                        <div style="font-size:12px; color:#64748b;">
                                            <?php echo ($gap > 0) ? "Currently on Day $s_day" : "Up to date"; ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            
                            <td>
                                <?php if ($is_finished || $gap == 0): ?>
                                    <span class='status-pill sp-green'><i class='fa-solid fa-check'></i> On Track</span>
                                <?php elseif ($gap == 1): ?>
                                    <span class='status-pill sp-amber'><i class='fa-solid fa-clock'></i> 1 Day Behind</span>
                                <?php else: ?>
                                    <span class='status-pill sp-red'><i class='fa-solid fa-triangle-exclamation'></i> Behind</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if ($stu['avg_score'] !== null): ?>
                                    <div class='grade-container'><span class='grade-val'><?php echo round($stu['avg_score']); ?>%</span></div>
                                <?php else: ?>
                                    <span class="text-muted">&mdash;</span>
                                <?php endif; ?>
                            </td>
                            
                            <td>
                                <div style="font-size:14px; font-weight:600; color:#1e293b;">Week <?php echo $s_mod; ?> &middot; Day <?php echo $s_day; ?></div>
                                <div style="font-size:12px; color:#64748b;">
                                    <?php echo $is_finished ? "<span style='color:#10b981;'>Waiting for unlock</span>" : ($gap > 0 ? "Catching up" : "Working on active day"); ?>
                                </div>
                            </td>

                            <td>
                                <?php if ($is_finished): ?>
                                    <span class="status-badge active"><i class='fa-solid fa-check-circle'></i> Day Completed</span>
                                <?php else: ?>
                                    <div class='step-cluster'>
                                        <?php
                                        // Fetch micro-steps
                                        $step_map = ['warmup' => null, 'watch' => null, 'practice' => null, 'speak' => null];
                                        if ($s_lid > 0) {
                                            $sql_steps = "SELECT ms.step_type, ssp.status FROM module_steps ms LEFT JOIN student_step_progress ssp ON ms.id = ssp.step_id AND ssp.student_id = {$stu['id']} WHERE ms.lesson_id = $s_lid";
                                            $res_steps = $conn->query($sql_steps);
                                            while ($row = $res_steps->fetch_assoc()) {
                                                $t = strtolower($row['step_type']);
                                                if ($t == 'speaking') $t = 'speak'; 
                                                $step_map[$t] = $row['status'] ?? 'pending';
                                            }
                                        }
                                        
                                        $found_focus = false;
                                        foreach ($step_map as $type => $status) {
                                            $is_focus = false;
                                            if ($status !== null && $status !== 'completed' && !$found_focus) {
                                                $is_focus = true;
                                                $found_focus = true;
                                            }
                                            $cls = getStepClass($status, $is_focus);
                                            $icon = getStepIcon($type);
                                            echo "<div class='micro-step $cls' title='$type'><i class='fa-solid $icon'></i></div>";
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="empty-state">No students found in this group.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main></div></body></html>