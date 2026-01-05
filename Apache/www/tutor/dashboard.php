<?php
// tutor/dashboard.php
require_once 'header.php';

$tutor_id = $_SESSION['user_id'];

// --- HANDLE ACKNOWLEDGMENT ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acknowledge_student_id'])) {
    $sid = (int)$_POST['acknowledge_student_id'];
    $stmt = $conn->prepare("INSERT INTO tutor_acknowledgments (tutor_id, student_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $tutor_id, $sid);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}

// 1. DATA GATHERING
$sql_active_mod = "SELECT * FROM modules WHERE is_global_locked = 0 ORDER BY module_number DESC LIMIT 1";
$active_module = $conn->query($sql_active_mod)->fetch_assoc();

if (!$active_module) {
    $pacing_label = "Course Not Started";
    $pacing_status = "Waiting for Admin to unlock Module 1";
    $current_lesson_id = 0;
} else {
    $mod_id = $active_module['id'];
    $sql_last_day = "SELECT day_number, id, title FROM lessons WHERE module_id = $mod_id AND is_unlocked = 1 ORDER BY day_number DESC LIMIT 1";
    $res_last_day = $conn->query($sql_last_day);
    
    if ($res_last_day->num_rows > 0) {
        $row = $res_last_day->fetch_assoc();
        $current_day = $row['day_number'];
        $current_lesson_id = $row['id'];
        $current_lesson_title = $row['title'];
        $pacing_label = "Module " . $active_module['module_number'] . " · Day " . $current_day;
        $pacing_status = "Active Now &bull; Target: " . htmlspecialchars($current_lesson_title);
    } else {
        $pacing_label = "Module " . $active_module['module_number'];
        $pacing_status = "Week Starting Soon";
        $current_lesson_id = 0;
    }
}

$offline_info = null;
if ($active_module) {
    $sql_offline = "SELECT * FROM offline_sessions WHERE module_id = {$active_module['id']} LIMIT 1";
    $offline_info = $conn->query($sql_offline)->fetch_assoc();
}

// Fetch stats...
$hidden_students = [];
$sql_ack = "SELECT student_id FROM tutor_acknowledgments WHERE tutor_id = $tutor_id AND created_at > DATE_SUB(NOW(), INTERVAL 24 HOUR)";
$res_ack = $conn->query($sql_ack);
while($row = $res_ack->fetch_assoc()) $hidden_students[] = $row['student_id'];

$my_groups = [];
$group_ids = [];
$sql_groups = "SELECT id, name FROM groups WHERE tutor_id = $tutor_id";
$res_groups = $conn->query($sql_groups);
while($g = $res_groups->fetch_assoc()) {
    $my_groups[$g['id']] = $g['name'];
    $group_ids[] = $g['id'];
}

$stats = ['on_track' => 0, 'behind' => 0, 'total' => 0];
$attention_list = [];
$acknowledged_list = [];

if (!empty($group_ids) && $current_lesson_id > 0) {
    $gid_list = implode(',', $group_ids);
    $sql_students = "SELECT u.id, u.name, u.email, u.group_id, (SELECT COUNT(*) FROM student_lesson_progress WHERE student_id = u.id AND lesson_id = $current_lesson_id AND status = 'completed') as is_done FROM users u WHERE u.group_id IN ($gid_list) AND u.role = 'student' AND u.status = 'active'";
    $res_students = $conn->query($sql_students);
    
    while ($stu = $res_students->fetch_assoc()) {
        $stats['total']++;
        if ($stu['is_done'] > 0) {
            $stats['on_track']++;
        } else {
            if (in_array($stu['id'], $hidden_students)) {
                $acknowledged_list[] = $stu;
                $stats['behind']++; 
            } else {
                $stats['behind']++;
                $attention_list[] = $stu;
            }
        }
    }
}

$final_display_list = array_merge($attention_list, $acknowledged_list);
$track_pct = ($stats['total'] > 0) ? round(($stats['on_track'] / $stats['total']) * 100) : 0;
$behind_pct = ($stats['total'] > 0) ? round(($stats['behind'] / $stats['total']) * 100) : 0;
?>

<div class="pacing-hero">
    <div class="ph-content">
        <span class="ph-label"><i class="fa-solid fa-circle-dot" style="font-size:10px; color:#4ade80; margin-right:6px;"></i> Currently Active</span>
        <h1 class="ph-title"><?php echo $pacing_label; ?></h1>
        <p class="ph-subtitle"><?php echo $pacing_status; ?></p>
    </div>
    <div class="ph-stat">
        <div class="ph-stat-num"><?php echo count($attention_list); ?></div>
        <div class="ph-stat-label"><?php echo count($attention_list) > 0 ? "Pending Review" : "All Clear"; ?></div>
    </div>
</div>

<div class="admin-grid">
    <div class="col-main">
        <div class="card">
            <div class="card-header-flex">
                <h3 class="card-header-title"><i class="fa-solid fa-clipboard-list" style="color:#6b7280; margin-right:8px;"></i> Today's Follow-ups</h3>
                <span style="font-size:12px; color:#6b7280; font-weight:500;">Status as of <?php echo date('g:i A'); ?></span>
            </div>

            <?php if (empty($final_display_list)): ?>
                <div class="empty-state">
                    <i class="fa-solid fa-check-circle" style="color:#10b981; font-size:32px; margin-bottom:12px;"></i>
                    <p style="font-weight:600; color:#374151;">All Caught Up!</p>
                    <p style="font-size:13px;">Everyone has completed the active day.</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="data-table">
                        <thead><tr><th>Student</th><th>Group</th><th style="text-align:right;">Action</th></tr></thead>
                        <tbody>
                            <?php foreach ($final_display_list as $stu): 
                                $is_ack = in_array($stu['id'], $hidden_students);
                                $row_style = $is_ack ? "opacity: 0.5; background: #f9fafb;" : "";
                            ?>
                            <tr style="<?php echo $row_style; ?>">
                                <td>
                                    <div style="font-weight:600; color:#111827;">
                                        <?php echo htmlspecialchars($stu['name']); ?>
                                        <?php if($is_ack): ?><span class="status-badge inactive">ACKNOWLEDGED</span><?php endif; ?>
                                    </div>
                                    <div style="font-size:12px; color:#6b7280;">Pending Day <?php echo $current_day; ?></div>
                                    
                                    <?php if(!$is_ack): ?>
                                    <div id="details-<?php echo $stu['id']; ?>" class="inline-details-panel" style="display:none;">
                                        <div class="idp-row"><i class="fa-regular fa-clock idp-icon"></i><span><strong>Last Active:</strong> No recent activity recorded.</span></div>
                                        <div class="idp-row"><i class="fa-solid fa-ban idp-icon"></i><span><strong>Blocker:</strong> Has not started Day <?php echo $current_day; ?> content.</span></div>
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td><span class="status-badge inactive"><?php echo htmlspecialchars($my_groups[$stu['group_id']]); ?></span></td>
                                <td style="text-align:right;">
                                    <?php if($is_ack): ?>
                                        <span style="font-size:12px; color:#9ca3af; font-style:italic;">Hidden for 24h</span>
                                    <?php else: ?>
                                        <div style="display:flex; justify-content:flex-end; gap:8px;">
                                            <button type="button" onclick="toggleDetails(<?php echo $stu['id']; ?>)" class="btn-icon view" title="View Context"><i class="fa-solid fa-chevron-down"></i></button>
                                            <button type="button" data-id="<?php echo $stu['id']; ?>" data-name="<?php echo htmlspecialchars($stu['name']); ?>" onclick="openAckModal(this)" class="btn-icon delete" title="Acknowledge & Hide"><i class="fa-regular fa-eye-slash"></i></button>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-side">
        <div class="side-card">
            <h4 class="sc-title">Progress Overview</h4>
            <div style="margin-bottom:20px;">
                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:600;"><span style="color:#15803d;">Completed</span><span><?php echo $stats['on_track']; ?> / <?php echo $stats['total']; ?></span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill green" style="width: <?php echo $track_pct; ?>%;"></div></div>
            </div>
            <div>
                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:13px; font-weight:600;"><span style="color:#b91c1c;">Pending</span><span><?php echo $stats['behind']; ?></span></div>
                <div class="progress-bar-bg"><div class="progress-bar-fill red" style="width: <?php echo $behind_pct; ?>%;"></div></div>
            </div>
            <div style="margin-top:24px; padding-top:20px; border-top:1px solid #e5e7eb;">
                <p style="font-size:12px; color:#6b7280; line-height:1.5;"><i class="fa-solid fa-info-circle" style="margin-right:4px;"></i> Students listed as "Pending" have not marked today's lesson as complete.</p>
            </div>
        </div>

        <div class="info-card">
            <div class="ic-icon"><i class="fa-solid fa-calendar"></i></div>
            <div class="ic-content">
                <h4 class="ic-title">Offline Session</h4>
                <div class="ic-desc">
                    <?php if ($offline_info && !empty($offline_info['session_date'])): ?>
                        <strong><?php echo date('l, M j', strtotime($offline_info['session_date'])); ?></strong> at <?php echo date('g:i A', strtotime($offline_info['start_time'])); ?>
                        <div style="margin-top:4px; font-size:13px;"><span class="ic-location"><i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($offline_info['location']); ?></span></div>
                    <?php else: ?>
                        No session scheduled yet.
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="resource-card">
            <div class="rc-header"><div class="rc-icon"><i class="fa-solid fa-book-open"></i></div><h4 class="rc-title">Classroom Resources</h4></div>
            <div style="margin-bottom:20px;"><span class="rc-label">Current Topic</span><div class="rc-topic"><?php echo ($current_lesson_id > 0) ? htmlspecialchars($current_lesson_title) : "No Active Lesson"; ?></div></div>
            <?php if ($current_lesson_id > 0): ?>
                <a href="../public/view-lesson.php?id=<?php echo $current_lesson_id; ?>&preview=true" target="_blank" class="btn-preview"><i class="fa-regular fa-eye"></i> Preview Lesson</a>
            <?php else: ?>
                <button disabled class="btn-preview disabled"><i class="fa-solid fa-lock"></i> Content Locked</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<div id="ackModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header"><h3><i class="fa-regular fa-eye-slash"></i> Acknowledge Alert</h3><button class="modal-close" onclick="closeAckModal()">&times;</button></div>
        <div class="modal-body">
            <p style="margin-bottom:12px; font-size:15px; color:#1e293b;">You are about to acknowledge <strong id="ackStudentName"></strong>.</p>
            <div style="background:#f8fafc; border-left:3px solid #94a3b8; padding:12px; border-radius:4px; font-size:13px; color:#475569; line-height:1.5;">This will hide the student from your "Needs Attention" list for <strong>24 hours</strong>. Use this to clear alerts you have seen but cannot resolve immediately.</div>
            <form method="POST" id="ackForm" style="margin-top:24px;">
                <input type="hidden" name="acknowledge_student_id" id="ackStudentId">
                <div class="modal-actions"><button type="button" class="btn-cancel" onclick="closeAckModal()">Cancel</button><button type="submit" class="btn-confirm">Confirm & Hide</button></div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDetails(id) { var x = document.getElementById("details-" + id); x.style.display = (x.style.display === "none") ? "block" : "none"; }
function openAckModal(btn) { document.getElementById('ackStudentId').value = btn.getAttribute('data-id'); document.getElementById('ackStudentName').textContent = btn.getAttribute('data-name'); document.getElementById('ackModal').style.display = 'flex'; }
function closeAckModal() { document.getElementById('ackModal').style.display = 'none'; }
window.onclick = function(e) { if (e.target == document.getElementById('ackModal')) closeAckModal(); }
</script>

</div> </main> </div> </body> </html>