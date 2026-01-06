<?php
// admin/api-toggle-lesson.php

// 1. SILENCE OUTPUT (Crucial for APIs)
error_reporting(E_ALL);
ini_set('display_errors', 0); 

header('Content-Type: application/json');

// 2. DEFINE PATHS (Robust check)
$paths = [
    __DIR__ . '/../app/config.php',
    __DIR__ . '/../../app/config.php',
    $_SERVER['DOCUMENT_ROOT'] . '/app/config.php'
];

$config_loaded = false;
foreach ($paths as $path) {
    if (file_exists($path)) {
        require_once $path;
        $config_loaded = true;
        break;
    }
}

if (!$config_loaded) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'System Error: Could not find config.php']);
    exit;
}

// 3. LOAD AUTH
$auth_path = __DIR__ . '/../app/auth.php';
if (file_exists($auth_path)) {
    require_once $auth_path;
    if (function_exists('requireRole')) {
        requireRole('admin');
    }
}

// 4. CHECK DB
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database Error: Connection missing.']);
    exit;
}

// 5. GET INPUT
$input = json_decode(file_get_contents('php://input'), true);
$lesson_id = isset($input['lesson_id']) ? (int)$input['lesson_id'] : 0;
$state = isset($input['state']) ? (int)$input['state'] : 0; 

if (!$lesson_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Lesson ID']);
    exit;
}

// =========================================================
// SECURITY CHECK: THE GATEKEEPER
// =========================================================
if ($state === 1) { // If trying to UNLOCK
    // Count real steps in DB
    $check = $conn->query("SELECT COUNT(*) as cnt FROM module_steps WHERE lesson_id = $lesson_id");
    $row = $check->fetch_assoc();
    
    if ($row['cnt'] == 0) {
        echo json_encode([
            'success' => false, 
            'message' => '🚫 BLOCK: This lesson is empty. You must add content (Edit) before you can unlock it.'
        ]);
        exit;
    }
}

// 6. UPDATE DATABASE
try {
    $stmt = $conn->prepare("UPDATE lessons SET is_unlocked = ? WHERE id = ?");
    $stmt->bind_param("ii", $state, $lesson_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception($stmt->error);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>