<?php
// public/view-lesson.php
require_once __DIR__ . '/../app/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$user_role = $_SESSION['user_role'] ?? '';
if (!isset($_SESSION['user_id']) || ($user_role !== 'student' && $user_role !== 'tutor')) {
    header('Location: sign-in.php'); exit;
}

$is_preview = ($user_role === 'tutor');
$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = $_SESSION['user_id'];

// Fetch Lesson
$sql = "SELECT l.*, m.title as module_title, m.module_number FROM lessons l JOIN modules m ON l.module_id = m.id WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();

if (!$lesson) { die("Lesson not found."); }

// Fetch Steps (Merge with standard 4-step flow)
$steps_sql = "SELECT * FROM module_steps WHERE lesson_id = ? ORDER BY step_order ASC";
$stmt_s = $conn->prepare($steps_sql);
$stmt_s->bind_param("i", $lesson_id);
$stmt_s->execute();
$db_steps = $stmt_s->get_result()->fetch_all(MYSQLI_ASSOC);

$existing_map = [];
foreach ($db_steps as $s) $existing_map[$s['step_type']] = $s;

$standard_flow = ['warmup', 'watch', 'practice', 'speak'];
$steps = [];

foreach ($standard_flow as $index => $type) {
    if (isset($existing_map[$type])) {
        $steps[] = $existing_map[$type];
    } else {
        // Ghost Step
        $steps[] = [
            'id' => 0, 
            'lesson_id' => $lesson_id,
            'step_order' => $index + 1,
            'step_type' => $type,
            'title' => ucfirst($type),
            'content_data' => json_encode([]) 
        ];
    }
}

// Get Progress
$user_progress = [];
if (!$is_preview) {
    $prog_sql = "SELECT step_id, status FROM student_step_progress WHERE student_id = ? AND step_id IN (SELECT id FROM module_steps WHERE lesson_id = ?)";
    $stmt_p = $conn->prepare($prog_sql);
    $stmt_p->bind_param("ii", $student_id, $lesson_id);
    $stmt_p->execute();
    $res_p = $stmt_p->get_result();
    while($row = $res_p->fetch_assoc()) $user_progress[$row['step_id']] = $row;
}

$back_link = $is_preview ? "../tutor/dashboard.php" : "daily-lessons.php?module_id=" . $lesson['module_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($lesson['title']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student/view-lesson.css">
    <style>
        @media (max-width: 600px) {
            .wizard-header { padding: 12px 16px; }
            .l-title { font-size: 14px; }
            .back-link span { display: none; } 
            .stepper-wrapper { padding: 20px 0; justify-content: flex-start; overflow-x: auto; }
            .stepper-track { padding: 0 16px; min-width: min-content; }
            .card-body { padding: 24px 20px; }
            .card-footer { padding: 16px 20px; flex-direction: column-reverse; gap: 16px; }
            .btn-primary, .btn-secondary { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body class="<?php echo $is_preview ? 'tutor-scroll-view' : ''; ?>">

<div class="wizard-container">
    <?php if($is_preview): ?>
        <div class="tutor-preview-banner"><span><i class="fa-solid fa-glasses"></i> TUTOR PREVIEW MODE</span></div>
    <?php endif; ?>

    <div class="wizard-header">
        <a href="<?php echo $back_link; ?>" class="back-link"><i class="fa-solid fa-arrow-left"></i> <span>Back</span></a>
        <div class="lesson-info">
            <span class="l-day">Week <?php echo $lesson['module_number']; ?> &middot; Day <?php echo $lesson['day_number']; ?></span>
            <h1 class="l-title"><?php echo htmlspecialchars($lesson['title']); ?></h1>
        </div>
        <div class="progress-badge"><span id="step-counter">1</span>/<?php echo count($steps); ?></div>
    </div>

    <div class="stepper-wrapper">
        <div class="stepper-track">
            <?php foreach($steps as $index => $step): 
                $is_done = ($step['id'] > 0) && isset($user_progress[$step['id']]) && $user_progress[$step['id']]['status'] == 'completed';
            ?>
            <div class="step-item <?php echo ($index === 0) ? 'active' : ''; ?>" id="dot-<?php echo $index; ?>" onclick="goStep(<?php echo $index; ?>)">
                <div class="step-dot"><?php if($is_done): ?><i class="fa-solid fa-check"></i><?php else: echo ($index + 1); endif; ?></div>
                <div class="step-label"><?php echo ucfirst($step['step_type']); ?></div>
            </div>
            <?php if($index < count($steps) - 1): ?><div class="step-line"></div><?php endif; endforeach; ?>
        </div>
    </div>

    <div class="wizard-content">
        <?php foreach($steps as $index => $step): 
            $content = json_decode($step['content_data'], true);
            $type = $step['step_type'];
            $h_cls = 'header-warmup'; $icon = 'fa-mug-hot';
            if ($type == 'watch') { $h_cls = 'header-watch'; $icon = 'fa-play'; }
            if ($type == 'practice') { $h_cls = 'header-practice'; $icon = 'fa-pen-to-square'; }
            if ($type == 'speak') { $h_cls = 'header-speak'; $icon = 'fa-microphone'; }
        ?>
        <div class="step-section <?php echo ($index === 0) ? 'active' : ''; ?>" id="step-<?php echo $index; ?>">
            <div class="content-card">
                <div class="card-header <?php echo $h_cls; ?>">
                    <div class="icon-box"><i class="fa-solid <?php echo $icon; ?>"></i></div>
                    <h2><?php echo ($index+1) . ". " . ucfirst($type); ?></h2>
                </div>

                <div class="card-body">
                    <?php if($step['id'] == 0 && $type !== 'speak'): ?>
                        <div class="reading-box" style="text-align:center; color:#64748b;">
                            <i class="fa-solid fa-screwdriver-wrench" style="font-size:32px; margin-bottom:16px; opacity:0.5;"></i>
                            <h3>Content Coming Soon</h3>
                            <p>Your tutor hasn't added this section yet.</p>
                        </div>
                    <?php else: ?>
                        <?php if ($type == 'warmup'): ?>
                            <div class="reading-box"><h3>Introduction</h3><p><?php echo nl2br(htmlspecialchars($content['intro_text'] ?? '')); ?></p></div>
                        <?php elseif ($type == 'watch'): ?>
                            <div class="video-wrapper"><iframe src="https://www.youtube.com/embed/<?php echo $content['video_url'] ?? ''; ?>" allowfullscreen></iframe></div>
                        <?php elseif ($type == 'practice'): ?>
                            <form id="quiz-form-<?php echo $step['id']; ?>">
                                <?php foreach (($content['questions'] ?? []) as $q_idx => $q): ?>
                                    <div class="quiz-question"><span class="q-title"><?php echo ($q_idx+1) . ". " . htmlspecialchars($q['question']); ?></span>
                                    <?php foreach ($q['options'] as $o_idx => $opt): ?>
                                        <label class="q-option"><input type="radio" name="answers[<?php echo $q_idx; ?>]" value="<?php echo $o_idx; ?>"><span><?php echo htmlspecialchars($opt); ?></span></label>
                                    <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </form>
                        <?php elseif ($type == 'speak'): ?>
                            <div class="reading-box" style="text-align:center;">
                                <i class="fa-solid fa-microphone-lines" style="font-size:48px; color:#4f46e5; margin-bottom:16px;"></i>
                                <h3 class="q-title">Speaking Task</h3>
                                <p style="font-size:18px; font-weight:500; color:#1e293b;"><?php echo htmlspecialchars($content['prompt'] ?? 'Explain what you learned today.'); ?></p>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <div class="card-footer">
                    <?php if ($index > 0): ?>
                        <button class="btn-secondary" onclick="goStep(<?php echo $index - 1; ?>)">Previous</button>
                    <?php else: ?>
                        <div></div>
                    <?php endif; ?>

                    <?php if ($index < count($steps) - 1): ?>
                        <?php if($step['id'] == 0): ?>
                             <button class="btn-primary" disabled style="opacity:0.6; cursor:not-allowed; background:#94a3b8;"><i class="fa-solid fa-lock"></i> Pending</button>
                        <?php else: ?>
                             <button class="btn-primary" onclick="completeStep(<?php echo $index; ?>, true)">Next Step &rarr;</button>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if($step['id'] == 0): ?>
                            <button class="btn-primary" disabled style="opacity:0.6; cursor:not-allowed; background:#94a3b8;"><i class="fa-solid fa-lock"></i> Content Pending</button>
                        <?php else: ?>
                            <button class="btn-primary" onclick="finishLesson(<?php echo $index; ?>)" style="background:#10b981;">Finish Lesson <i class="fa-solid fa-check"></i></button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    let currentStep = 0;
    const totalSteps = <?php echo count($steps); ?>;
    const isPreview = <?php echo $is_preview ? 'true' : 'false'; ?>;
    const stepIds = <?php echo json_encode(array_column($steps, 'id')); ?>;
    const lessonId = <?php echo $lesson_id; ?>;

    function goStep(n) {
        if (n < 0 || n >= totalSteps) return;
        document.getElementById('step-' + currentStep).classList.remove('active');
        document.getElementById('dot-' + currentStep).classList.remove('active');
        currentStep = n;
        document.getElementById('step-' + currentStep).classList.add('active');
        document.getElementById('dot-' + currentStep).classList.add('active');
        document.getElementById('step-counter').innerText = (currentStep + 1);
    }

    async function completeStep(stepNum, autoNext = false, isFinishing = false) {
        const dbId = stepIds[stepNum];
        
        // JS GUARDRAIL
        if (dbId === 0) {
            alert("This section is empty. Please wait for your tutor to add content.");
            return false;
        }

        let payload = { step_id: dbId };
        
        // QUIZ VALIDATION
        const quizForm = document.getElementById('quiz-form-' + dbId);
        if (quizForm) {
            const formData = new FormData(quizForm);
            const answers = [...formData.keys()];
            if (answers.length === 0) {
                alert("Please answer the questions before proceeding.");
                return false;
            }
        } 
        
        if (isFinishing) {
            payload.complete_lesson = true;
            payload.lesson_id = lessonId; 
        }

        try {
            await fetch('update_progress.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
        } catch(e) { console.error(e); }

        if (autoNext) goStep(stepNum + 1);
        return true; 
    }

    async function finishLesson(stepNum) {
        const isValid = await completeStep(stepNum, false, true);
        if (!isValid) return;
        alert("Lesson Completed!");
        if (isPreview) window.location.href = '../tutor/dashboard.php';
        else window.location.href = 'daily-lessons.php?module_id=<?php echo $lesson['module_id']; ?>'; 
    }
</script>
</body>
</html>