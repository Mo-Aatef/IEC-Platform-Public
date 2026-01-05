<?php
// public/view-lesson.php
require_once __DIR__ . '/../app/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. UPDATED AUTH CHECK: Allow Student OR Tutor
$user_role = $_SESSION['user_role'] ?? '';
if (!isset($_SESSION['user_id']) || ($user_role !== 'student' && $user_role !== 'tutor')) {
    header('Location: sign-in.php'); exit;
}

// 2. DEFINE PREVIEW MODE
$is_preview = ($user_role === 'tutor');

$lesson_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student_id = $_SESSION['user_id'];

// 3. Fetch Lesson & Parent Module
$sql = "SELECT l.*, m.title as module_title, m.module_number 
        FROM lessons l 
        JOIN modules m ON l.module_id = m.id 
        WHERE l.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$lesson = $stmt->get_result()->fetch_assoc();

if (!$lesson) die("Lesson not found.");

// 4. Fetch Steps (Ordered 1-4)
$steps_sql = "SELECT * FROM module_steps WHERE lesson_id = ? ORDER BY step_order ASC";
$stmt = $conn->prepare($steps_sql);
$stmt->bind_param("i", $lesson_id);
$stmt->execute();
$result = $stmt->get_result();

$steps = [];
$has_speaking = false;

while ($row = $result->fetch_assoc()) {
    $row['data'] = json_decode($row['content_data'], true);
    // Normalize type check
    $t = strtolower($row['step_type']);
    if ($t === 'speak' || $t === 'speaking') {
        $has_speaking = true;
    }
    $steps[] = $row;
}

// --- FORCE SPEAKING STEP (MOCK) ---
if (!$has_speaking) {
    $steps[] = [
        'id' => 'dummy_speak', 
        'step_type' => 'speak',
        'data' => [
            'prompt' => 'Please practice speaking about the topic covered in this lesson. (Demo Content)'
        ]
    ];
}

if (empty($steps)) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'>
            <h2>Content Coming Soon</h2>
            <p>This lesson has no steps yet.</p>
            <a href='daily-lessons.php?module_id={$lesson['module_id']}'>Go Back</a>
         </div>");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($lesson['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/student/view-lesson.css">
</head>
<body class="<?php echo $is_preview ? 'tutor-scroll-view' : ''; ?>">

<?php if ($is_preview): ?>
<div class="tutor-preview-banner">
    <span><i class="fa-solid fa-glasses"></i> <strong>Instructor Preview</strong></span>
    <span style="opacity: 0.8; font-weight: 400;">Read-only mode (Progress not saved)</span>
</div>
<?php endif; ?>

<div class="wizard-container">
    
    <header class="wizard-header">
        <?php 
            $back_link = $is_preview ? '../tutor/dashboard.php' : "daily-lessons.php?module_id={$lesson['module_id']}";
        ?>
        <a href="<?php echo $back_link; ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> <?php echo $is_preview ? 'Back to Dashboard' : 'Exit Lesson'; ?>
        </a>
        <div class="lesson-info">
            <span class="l-day">Day <?php echo $lesson['day_number']; ?></span>
            <div class="l-title"><?php echo htmlspecialchars($lesson['title']); ?></div>
        </div>
        <div class="progress-badge">
            <span id="step-counter">1</span> / <?php echo count($steps); ?> Steps
        </div>
    </header>

    <div class="stepper-wrapper">
        <div class="stepper-track">
            <?php foreach($steps as $index => $step): 
                $num = $index + 1;
                $isActive = ($index === 0) ? 'active' : '';
                
                $type = strtolower($step['step_type']);
                $label = 'Activity'; 
                $icon = 'fa-cube';
                
                if ($type == 'warmup')       { $label = 'Warm-up';  $icon = 'fa-coffee'; }
                elseif ($type == 'watch')    { $label = 'Watch';    $icon = 'fa-play'; }
                elseif ($type == 'practice') { $label = 'Practice'; $icon = 'fa-pen-to-square'; }
                elseif ($type == 'speak' || $type == 'speaking') { $label = 'Speaking'; $icon = 'fa-microphone'; }
                elseif (!empty($type))       { $label = ucfirst($type); } 
            ?>
                <?php if ($index > 0): ?>
                    <div class="step-line" id="line-<?php echo $num; ?>"></div>
                <?php endif; ?>

                <div class="step-item <?php echo $isActive; ?>" id="dot-<?php echo $num; ?>" onclick="tryNav(<?php echo $num; ?>)">
                    <div class="step-dot"><i class="fa-solid <?php echo $icon; ?>"></i></div>
                    <span class="step-label"><?php echo $label; ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="wizard-content">
        <?php foreach($steps as $index => $step): 
            $num = $index + 1;
            $data = $step['data'];
            $type = strtolower($step['step_type']);
            
            $headerClass = 'header-warmup';
            $headerTitle = 'Activity';
            if($type == 'warmup') { $headerClass = 'header-warmup'; $headerTitle = 'Warm-up'; }
            if($type == 'watch') { $headerClass = 'header-watch'; $headerTitle = 'Lesson Content'; }
            if($type == 'practice') { $headerClass = 'header-practice'; $headerTitle = 'Practice Quiz'; }
            if($type == 'speak' || $type == 'speaking') { $headerClass = 'header-speak'; $headerTitle = 'Speaking Task'; }
        ?>
        
        <div class="step-section <?php echo ($index === 0) ? 'active' : ''; ?>" 
            id="step-<?php echo $num; ?>" 
            data-db-id="<?php echo $step['id']; ?>"
            style="--step-index: 'Step <?php echo $num; ?>';">

            <div class="content-card">
                <div class="card-header <?php echo $headerClass; ?>">
                    <div class="icon-box">
                        <?php if($type=='warmup'): ?><i class="fa-solid fa-coffee"></i><?php endif; ?>
                        <?php if($type=='watch'): ?><i class="fa-solid fa-play"></i><?php endif; ?>
                        <?php if($type=='practice'): ?><i class="fa-solid fa-pen-to-square"></i><?php endif; ?>
                        <?php if($type=='speak' || $type=='speaking'): ?><i class="fa-solid fa-microphone"></i><?php endif; ?>
                    </div>
                    <h2><?php echo $headerTitle; ?></h2>
                </div>

                <div class="card-body">
                    
                    <?php if($type == 'warmup'): ?>
                        <div class="intro-text"><?php echo nl2br(htmlspecialchars($data['intro'] ?? '')); ?></div>
                        <?php if(!empty($data['vocab'])): ?>
                            <div class="vocab-grid">
                                <?php foreach($data['vocab'] as $v): ?>
                                    <div class="vocab-item">
                                        <span class="v-word"><?php echo htmlspecialchars($v['word']); ?></span>
                                        <span class="v-def"><?php echo htmlspecialchars($v['def']); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    
                    <?php elseif($type == 'watch'): ?>
                        <?php if(($data['type'] ?? '') == 'video' && !empty($data['url'])): 
                             $v_url = $data['url'];
                             if(strpos($v_url, 'watch?v=')) $v_url = str_replace('watch?v=', 'embed/', $v_url);
                             elseif(strpos($v_url, 'youtu.be/')) $v_url = str_replace('youtu.be/', 'youtube.com/embed/', $v_url);
                        ?>
                            <div class="video-wrapper">
                                <iframe src="<?php echo htmlspecialchars($v_url); ?>" frameborder="0" allowfullscreen></iframe>
                            </div>
                        <?php elseif(($data['type'] ?? '') == 'reading'): ?>
                            <div class="reading-box">
                                <?php echo nl2br(htmlspecialchars($data['text'] ?? '')); ?>
                                <?php if(!empty($data['url'])): ?>
                                    <br><br>
                                    <a href="<?php echo htmlspecialchars($data['url']); ?>" target="_blank" class="btn-primary">
                                        <i class="fa-solid fa-file-pdf"></i> Open Document
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                    <?php elseif($type == 'practice'): ?>
                        <form id="quiz-form-<?php echo $num; ?>">
                            <?php foreach(($data['questions'] ?? []) as $qIdx => $q): 
                                $correctIdx = $q['correct'] ?? 0;
                            ?>
                                <div class="quiz-question" data-correct="<?php echo $correctIdx; ?>">
                                    <p class="q-title"><?php echo ($qIdx+1) . '. ' . htmlspecialchars($q['title']); ?></p>
                                    <?php foreach($q['options'] as $oIdx => $opt): 
                                        $is_correct_option = ($correctIdx == $oIdx);
                                        $tutor_class = ($is_preview && $is_correct_option) ? 'tutor-correct-answer' : '';
                                    ?>
                                        <label class="q-option <?php echo $tutor_class; ?>">
                                            <input type="radio" name="q<?php echo $qIdx; ?>" required>
                                            <span><?php echo htmlspecialchars($opt); ?></span>
                                            
                                            <?php if($is_preview && $is_correct_option): ?>
                                                <span class="tutor-key-badge"><i class="fa-solid fa-check"></i> Correct</span>
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>
                        </form>

                    <?php elseif($type == 'speak' || $type == 'speaking'): ?>
                        <div style="background:#eff6ff; padding:24px; border-radius:12px; border:1px solid #dbeafe;">
                            <h3>Your Topic</h3>
                            <p><?php echo nl2br(htmlspecialchars($data['prompt'] ?? '')); ?></p>
                        </div>
                        <div style="text-align:center; padding:40px; color:#9ca3af; border:2px dashed #e5e7eb; margin-top:20px; border-radius:12px;">
                            <i class="fa-solid fa-microphone" style="font-size:32px; margin-bottom:10px;"></i>
                            <p>Recording feature coming soon.</p>
                        </div>
                    <?php endif; ?>

                </div>

                <div class="card-footer">
                    <?php if($num > 1): ?>
                        <button class="btn-secondary" onclick="goStep(<?php echo $num - 1; ?>)">Back</button>
                    <?php else: ?>
                        <div></div> <?php endif; ?>

                    <?php if($num < count($steps)): ?>
                        <button class="btn-primary" onclick="completeStep(<?php echo $num; ?>)">
                            Complete & Continue <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    <?php else: ?>
                        <button class="btn-primary" style="background:#10b981;" onclick="finishLesson(<?php echo $num; ?>)">
                            Finish Lesson <i class="fa-solid fa-check"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

</div>

<script>
    let currentStep = 1;
    const totalSteps = <?php echo count($steps); ?>;
    const lessonId = <?php echo $lesson_id; ?>;
    const moduleId = <?php echo $lesson['module_id']; ?>;
    const isPreview = <?php echo $is_preview ? 'true' : 'false'; ?>;

    function goStep(step) {
        const targetSection = document.getElementById('step-' + step);
        if (!targetSection) return;

        document.querySelectorAll('.step-section').forEach(el => el.classList.remove('active'));
        targetSection.classList.add('active');
        
        for(let i=1; i<=totalSteps; i++) {
            const dot = document.getElementById('dot-' + i);
            const line = document.getElementById('line-' + i);
            
            if(i <= step) dot.classList.add('active');
            else dot.classList.remove('active');

            if(line) {
                if(i < step) line.classList.add('filled');
                else line.classList.remove('filled');
            }
        }
        
        document.getElementById('step-counter').innerText = step;
        window.scrollTo({ top: 0, behavior: 'smooth' });
        currentStep = step;
    }

    // UPDATED: Now accepts optional 'isFinishing' flag
    async function completeStep(stepNum, autoNext = true, isFinishing = false) {
        const stepEl = document.getElementById('step-' + stepNum);
        
        // 1. Validation & Grading Logic
        const form = stepEl.querySelector('form');
        let calculatedScore = null;

        if (form) {
            if (!form.checkValidity()) {
                form.reportValidity(); 
                return false; 
            }

            const questions = stepEl.querySelectorAll('.quiz-question');
            if (questions.length > 0) {
                let correctCount = 0;
                questions.forEach(q => {
                    const correctIndex = parseInt(q.getAttribute('data-correct'));
                    const inputs = q.querySelectorAll('input[type="radio"]');
                    let selectedIndex = -1;
                    inputs.forEach((inp, idx) => {
                        if (inp.checked) selectedIndex = idx;
                    });
                    if (selectedIndex === correctIndex) correctCount++;
                });
                calculatedScore = Math.round((correctCount / questions.length) * 100);
            }
        }

        // --- PREVIEW MODE GUARD ---
        if (isPreview) {
            console.log("Tutor Preview. Score: " + calculatedScore);
            if (autoNext) goStep(stepNum + 1);
            return true;
        }

        // 2. Prepare Payload
        const dbId = stepEl.getAttribute('data-db-id');
        let payload = {};

        // CASE A: DUMMY STEP
        if (dbId === 'dummy_speak') {
            if (isFinishing) {
                // If it's dummy AND we are finishing, send explicit finish signal
                payload = { 
                    step_id: 0, // No real step ID
                    lesson_id: lessonId, // Context required
                    complete_lesson: true 
                };
            } else {
                // Just moving around, do nothing
                if (autoNext) goStep(stepNum + 1);
                return true; 
            }
        } 
        // CASE B: REAL STEP
        else {
            payload = { step_id: dbId };
            if (calculatedScore !== null) payload.score = calculatedScore;
            
            // If this is the final step button, include finish flag
            if (isFinishing) {
                payload.complete_lesson = true;
                payload.lesson_id = lessonId; 
            }
        }

        // 3. Save Progress
        try {
            await fetch('update_progress.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });
        } catch(e) { console.error(e); }

        // 4. Move Next
        if (autoNext) {
            goStep(stepNum + 1);
        }
        
        return true; 
    }

    async function finishLesson(stepNum) {
        // Pass 'true' for isFinishing to trigger the final DB update
        const isValid = await completeStep(stepNum, false, true);
        if (!isValid) return;

        alert("Lesson Completed!");

        if (isPreview) {
            window.location.href = '../tutor/dashboard.php';
        } else {
            window.location.href = 'daily-lessons.php?module_id=' + moduleId;
        }
    }

    function tryNav(step) {
        if(step < currentStep) goStep(step);
    }
</script>

</body>
</html>