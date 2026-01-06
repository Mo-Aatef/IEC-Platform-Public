<?php
// admin/week-detail.php
require_once 'header.php';

$module_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($module_id === 0) {
    echo "<script>window.location.href='modules.php';</script>";
    exit;
}

// --- 1. HANDLE DAY 7 SAVE (With Timestamp Feedback) ---
$d7_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_offline'])) {
    $title = $_POST['title']; $date = $_POST['session_date'];
    $start = $_POST['start_time']; $end = $_POST['end_time'];
    $loc = $_POST['location']; $notes = $_POST['notes'];
    
    // Check exist
    $chk = $conn->query("SELECT id FROM offline_sessions WHERE module_id=$module_id");
    if($chk->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE offline_sessions SET title=?, session_date=?, start_time=?, end_time=?, location=?, notes=? WHERE module_id=?");
        $stmt->bind_param("ssssssi", $title, $date, $start, $end, $loc, $notes, $module_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO offline_sessions (module_id, title, session_date, start_time, end_time, location, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $module_id, $title, $date, $start, $end, $loc, $notes);
    }
    $stmt->execute();
    $d7_msg = "Changes saved at " . date('H:i:s');
}

// --- 2. FETCH MODULE & LESSONS ---
$module = $conn->query("SELECT * FROM modules WHERE id=$module_id")->fetch_assoc();

// Complex Query: Get Lesson Info + Count of Students in each status
$sql = "SELECT l.*, 
        (SELECT COUNT(*) FROM student_lesson_progress slp WHERE slp.lesson_id = l.id AND slp.status='completed') as completed_count,
        (SELECT COUNT(*) FROM module_steps ms WHERE ms.lesson_id = l.id) as step_count
        FROM lessons l 
        WHERE l.module_id = $module_id AND l.day_number <= 6 
        ORDER BY l.day_number ASC";
$res = $conn->query($sql);

$lessons = [];
while($row = $res->fetch_assoc()) $lessons[$row['day_number']] = $row;

// Fetch Day 7
$off = $conn->query("SELECT * FROM offline_sessions WHERE module_id=$module_id")->fetch_assoc();
if(!$off) $off = ['title'=>'', 'session_date'=>'', 'start_time'=>'', 'end_time'=>'', 'location'=>'', 'notes'=>'', 'updated_at'=>'Never'];
?>

<div class="week-detail-container" style="max-width: 1100px; margin: 0 auto; padding-bottom: 80px;">

    <div class="page-header" style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 12px; color: #6b7280; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                Week <?php echo $module['module_number']; ?> Control Panel
            </div>
            <h1 class="page-title" style="margin: 0; font-size: 24px; color: #111827;"><?php echo htmlspecialchars($module['title']); ?></h1>
        </div>
        <a href="modules.php" class="btn-secondary">
            <i class="fa-solid fa-arrow-left"></i>&nbsp; Back
        </a>
    </div>

    <div class="lessons-grid-admin">
        <?php for ($day = 1; $day <= 6; $day++): 
            $l = $lessons[$day] ?? null;
            $title = $l ? $l['title'] : "Day $day (Empty)";
            $is_unlocked = $l['is_unlocked'] ?? 0;
            $step_count = $l['step_count'] ?? 0;
            $completed_count = $l['completed_count'] ?? 0;
            
            // Logic: Content Readiness
            $content_color = ($step_count > 0) ? '#10b981' : '#ef4444';
            $content_text = ($step_count > 0) ? 'Content Ready' : 'Needs Content';
            $lid = $l['id'] ?? 0;
        ?>
        <div class="card lesson-control-card <?php echo $is_unlocked ? 'is-live' : 'is-locked'; ?>">
            
            <div class="lc-header">
                <span class="day-badge">DAY <?php echo $day; ?></span>
                <div class="content-health" title="<?php echo $content_text; ?>">
                    <div class="health-dot" style="background: <?php echo $content_color; ?>;"></div>
                    <span><?php echo $step_count; ?> Steps</span>
                </div>
            </div>

            <div class="lc-body">
                <h3 class="lc-title"><?php echo htmlspecialchars($title); ?></h3>
                <div class="lc-stats">
                    <i class="fa-solid fa-user-check"></i> <?php echo $completed_count; ?> Students Completed
                </div>
            </div>

            <div class="lc-footer">
                <button class="btn-icon-text" onclick="safeEdit(<?php echo $lid; ?>, <?php echo $is_unlocked; ?>, <?php echo $module_id; ?>, <?php echo $day; ?>)">
                    <i class="fa-solid fa-pen"></i> <?php echo ($lid == 0) ? 'Create' : 'Edit'; ?>
                </button>

                <label class="pacing-toggle">
                    <input type="checkbox" 
                           onchange="toggleLesson(this, <?php echo $lid; ?>)"
                           <?php echo $is_unlocked ? 'checked' : ''; ?>
                           <?php echo ($lid == 0) ? 'disabled' : ''; ?>>
                    <span class="slider round"></span>
                    <span class="toggle-label"><?php echo $is_unlocked ? 'Unlocked' : 'Locked'; ?></span>
                </label>
            </div>
        </div>
        <?php endfor; ?>
    </div>

    <div style="margin: 50px 0; display: flex; align-items: center; gap: 16px;">
        <div style="height: 1px; background: #e2e8f0; flex-grow: 1;"></div>
        <span style="font-size: 12px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">Weekend / Offline</span>
        <div style="height: 1px; background: #e2e8f0; flex-grow: 1;"></div>
    </div>

    <div class="offline-card">
        <form method="POST" id="d7-form">
            <input type="hidden" name="save_offline" value="1">
            <div class="offline-header">
                <div>
                    <div class="offline-badge"><i class="fa-solid fa-users"></i> Day 7 · Offline Session</div>
                </div>
                <div style="text-align: right;">
                    <?php if($d7_msg): ?>
                        <span class="save-feedback success"><i class="fa-solid fa-check"></i> <?php echo $d7_msg; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="offline-form-grid">
                <div><label class="offline-label">Event Title</label><input type="text" name="title" class="offline-input" value="<?php echo htmlspecialchars($off['title']); ?>"></div>
                <div><label class="offline-label">Date</label><input type="date" name="session_date" class="offline-input" value="<?php echo $off['session_date']; ?>"></div>
            </div>
            <div class="offline-form-grid" style="grid-template-columns: 1fr 1fr 2fr;">
                <div><label class="offline-label">Start</label><input type="time" name="start_time" class="offline-input" value="<?php echo $off['start_time']; ?>"></div>
                <div><label class="offline-label">End</label><input type="time" name="end_time" class="offline-input" value="<?php echo $off['end_time']; ?>"></div>
                <div><label class="offline-label">Location</label><input type="text" name="location" class="offline-input" value="<?php echo htmlspecialchars($off['location']); ?>"></div>
            </div>
            <div class="offline-input-group"><label class="offline-label">Notes</label><textarea name="notes" class="offline-input offline-textarea"><?php echo htmlspecialchars($off['notes']); ?></textarea></div>
            <div class="offline-footer"><button type="submit" class="btn-save-offline">Save Logistics</button></div>
        </form>
    </div>

</div>

<script>
    function updateVisuals(card, isUnlocked) {
        const label = card.querySelector('.toggle-label');
        const checkbox = card.querySelector('input[type="checkbox"]');
        checkbox.checked = isUnlocked;
        if (isUnlocked) {
            label.innerText = "UNLOCKED"; label.style.color = "#10b981";
            card.classList.add('is-live'); card.classList.remove('is-locked');
            card.style.borderTopColor = "#10b981"; 
        } else {
            label.innerText = "LOCKED"; label.style.color = "#64748b";
            card.classList.add('is-locked'); card.classList.remove('is-live');
            card.style.borderTopColor = "#cbd5e1";
        }
    }

    async function toggleLesson(checkbox, id) {
        if(!id) return; 
        const card = checkbox.closest('.lesson-control-card');
        const targetState = checkbox.checked; 
        
        updateVisuals(card, targetState); // Optimistic Update

        try {
            const res = await fetch('api-toggle-lesson.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ lesson_id: id, state: targetState ? 1 : 0 })
            });
            
            const rawText = await res.text();
            let data;
            try { data = JSON.parse(rawText); } 
            catch (e) { throw new Error("Server Error: " + rawText); }

            if(!data.success) {
                // REVERT IF BLOCKED
                updateVisuals(card, !targetState); 
                alert(data.message); // Show the specific "Empty Lesson" message
            }
        } catch(e) {
            console.error(e);
            updateVisuals(card, !targetState);
            alert("System Error: " + e.message);
        }
    }

    function safeEdit(id, isUnlocked, moduleId, day) {
        if(!id) {
            window.location.href = `lesson-editor.php?module_id=${moduleId}&day=${day}`;
            return;
        }
        if(isUnlocked) {
            if(!confirm("⚠️ CAUTION: This lesson is LIVE.\nEditing it now might disrupt active students.\n\nContinue?")) return;
        }
        window.location.href = 'lesson-editor.php?id=' + id;
    }
</script>