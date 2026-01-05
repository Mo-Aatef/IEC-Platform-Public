<?php
// public/take-quiz.php
require_once __DIR__ . '/../app/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

// 1. Auth Check (Updated for Tutor Access)
$user_role = $_SESSION['user_role'] ?? '';
if (!isset($_SESSION['user_id']) || ($user_role !== 'student' && $user_role !== 'tutor')) {
    header('Location: sign-in.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $module_id = (int)$_POST['module_id'];
    $user_answers = $_POST['answers'] ?? []; 

    // 2. Fetch Quiz Data
    $sql = "SELECT content_data FROM module_steps WHERE module_id = ? AND step_type = 'practice'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $module_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Default pass if no quiz exists
    if ($result->num_rows === 0) {
        // If student, save. If tutor, just redirect.
        if ($user_role === 'student') {
            markModuleComplete($conn, $user_id, $module_id, 100);
        }
        header("Location: modules.php");
        exit;
    }

    $row = $result->fetch_assoc();
    $quiz_data = json_decode($row['content_data'], true);
    $questions = $quiz_data['questions'] ?? [];

    // 3. Grade the Quiz
    $score = 0;
    $total_questions = count($questions);

    if ($total_questions > 0) {
        foreach ($questions as $index => $q) {
            // Check if answer matches correct index
            if (isset($user_answers[$index]) && $user_answers[$index] == $q['correct']) {
                $score++;
            }
        }
        $percentage = round(($score / $total_questions) * 100);
    } else {
        $percentage = 100; 
    }

    // --- TUTOR GUARD ---
    if ($user_role === 'tutor') {
        // Stop here. Do NOT save progress.
        // Optional: Redirect to dashboard or show a "Preview Complete" message.
        header("Location: ../tutor/dashboard.php?msg=preview_quiz_done&score=$percentage");
        exit;
    }

    // 4. Save Progress + Score (Students Only)
    markModuleComplete($conn, $user_id, $module_id, $percentage);

    header("Location: modules.php");
    exit;
} else {
    header("Location: modules.php");
    exit;
}

// Helper Function
function markModuleComplete($conn, $user_id, $module_id, $score) {
    // Check if exists
    $check = $conn->query("SELECT id FROM student_module_progress WHERE student_id = $user_id AND module_id = $module_id");

    if ($check->num_rows > 0) {
        $sql = "UPDATE student_module_progress SET status = 'completed', score = ?, updated_at = NOW() WHERE student_id = ? AND module_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $score, $user_id, $module_id);
    } else {
        $sql = "INSERT INTO student_module_progress (student_id, module_id, status, score) VALUES (?, ?, 'completed', ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iii", $user_id, $module_id, $score);
    }
    $stmt->execute();
}
?>