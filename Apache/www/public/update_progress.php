<?php
// public/update_progress.php
require_once __DIR__ . '/../app/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit(json_encode(['error' => 'Unauthorized']));
}

$data = json_decode(file_get_contents('php://input'), true);
$student_id = $_SESSION['user_id'];

// Inputs
$step_id = isset($data['step_id']) ? (int)$data['step_id'] : 0;
$explicit_finish = isset($data['complete_lesson']) && $data['complete_lesson'];
$payload_lesson_id = isset($data['lesson_id']) ? (int)$data['lesson_id'] : 0;

// Validation: Must have either a step to save OR a finish signal
if (!$step_id && !$explicit_finish) {
    exit(json_encode(['error' => 'Missing Data']));
}

// Variables to hold context
$current_lesson_id = 0;
$current_module_id = 0;

// --- 1. HANDLE STEP PROGRESS (If a real step exists) ---
if ($step_id > 0) {
    // Get Context from Step ID
    $sql_context = "
        SELECT ms.id, ms.lesson_id, l.module_id 
        FROM module_steps ms
        JOIN lessons l ON ms.lesson_id = l.id
        WHERE ms.id = ? LIMIT 1
    ";
    $stmt = $conn->prepare($sql_context);
    $stmt->bind_param("i", $step_id);
    $stmt->execute();
    $context = $stmt->get_result()->fetch_assoc();

    if ($context) {
        $current_lesson_id = $context['lesson_id'];
        $current_module_id = $context['module_id'];

        // Save Step Logic
        $has_score = isset($data['score']);
        $score = $has_score ? (int)$data['score'] : null;

        if ($has_score) {
            $sql = "INSERT INTO student_step_progress (student_id, module_id, lesson_id, step_id, status, score, completed_at) 
                    VALUES (?, ?, ?, ?, 'completed', ?, NOW()) 
                    ON DUPLICATE KEY UPDATE status = 'completed', score = ?, completed_at = NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiiiii", $student_id, $current_module_id, $current_lesson_id, $step_id, $score, $score);
        } else {
            $sql = "INSERT INTO student_step_progress (student_id, module_id, lesson_id, step_id, status, completed_at) 
                    VALUES (?, ?, ?, ?, 'completed', NOW()) 
                    ON DUPLICATE KEY UPDATE status = 'completed', completed_at = NOW()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iiii", $student_id, $current_module_id, $current_lesson_id, $step_id);
        }
        $stmt->execute();
    }
} 
// Use payload lesson_id if we didn't get it from a step (Dummy Step Case)
else if ($payload_lesson_id > 0) {
    $current_lesson_id = $payload_lesson_id;
    // Fetch module ID for this lesson
    $mod_check = $conn->query("SELECT module_id FROM lessons WHERE id = $current_lesson_id");
    if ($mod_check && $row = $mod_check->fetch_assoc()) {
        $current_module_id = $row['module_id'];
    }
}

// --- 2. HANDLE LESSON COMPLETION (Only if Explicitly Requested) ---
$lesson_marked = false;
$module_marked = false;

if ($explicit_finish && $current_lesson_id > 0) {
    // Mark Lesson as Completed
    $stmt = $conn->prepare("
        INSERT INTO student_lesson_progress (student_id, lesson_id, status, completed_at) 
        VALUES (?, ?, 'completed', NOW()) 
        ON DUPLICATE KEY UPDATE status = 'completed', completed_at = NOW()
    ");
    $stmt->bind_param("ii", $student_id, $current_lesson_id);
    if ($stmt->execute()) {
        $lesson_marked = true;
    }

    // Check Module Completion (Rule: 6 Lessons)
    if ($current_module_id > 0) {
        $sql_done_lessons = "
            SELECT COUNT(DISTINCT slp.lesson_id) as done 
            FROM student_lesson_progress slp
            JOIN lessons l ON slp.lesson_id = l.id
            WHERE slp.student_id = ? AND l.module_id = ? AND slp.status = 'completed' AND l.day_number <= 6
        ";
        $stmt = $conn->prepare($sql_done_lessons);
        $stmt->bind_param("ii", $student_id, $current_module_id);
        $stmt->execute();
        $done_lessons = $stmt->get_result()->fetch_assoc()['done'];

        if ($done_lessons >= 6) {
            $stmt = $conn->prepare("
                INSERT INTO student_module_progress (student_id, module_id, status, completed_at) 
                VALUES (?, ?, 'completed', NOW()) 
                ON DUPLICATE KEY UPDATE status = 'completed', completed_at = NOW()
            ");
            $stmt->bind_param("ii", $student_id, $current_module_id);
            $stmt->execute();
            $module_marked = true;
        }
    }
}

echo json_encode([
    'success' => true, 
    'lesson_completed' => $lesson_marked,
    'module_completed' => $module_marked
]);
?>