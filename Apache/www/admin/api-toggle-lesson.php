<?php
// admin/api-toggle-lesson.php

// 1. SILENCE OUTPUT (Crucial for APIs)
// This prevents HTML warnings from breaking the JSON response
error_reporting(E_ALL);
ini_set('display_errors', 0); 

header('Content-Type: application/json');

// 2. DEFINE PATHS (Robust check)
$paths = [
    __DIR__ . '/../app/config.php',  // Standard structure
    __DIR__ . '/../../app/config.php', // Deeper structure
    $_SERVER['DOCUMENT_ROOT'] . '/app/config.php' // Absolute path
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

// 3. LOAD AUTH (If exists)
// We use a try-catch style logic for require to avoid fatal crashes
$auth_path = __DIR__ . '/../app/auth.php';
if (file_exists($auth_path)) {
    require_once $auth_path;
    // Check role if the function exists
    if (function_exists('requireRole')) {
        requireRole('admin');
    }
}

// 4. CHECK DATABASE CONNECTION
if (!isset($conn) || !$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database Error: $conn variable is missing.']);
    exit;
}

// 5. GET INPUT
$input = json_decode(file_get_contents('php://input'), true);
$lesson_id = isset($input['lesson_id']) ? (int)$input['lesson_id'] : 0;
// Note: We use isset for state because 0 is a valid value (Locked)
$state = isset($input['state']) ? (int)$input['state'] : 0; 

if (!$lesson_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid Lesson ID']);
    exit;
}

// 6. UPDATE DATABASE
try {
    $stmt = $conn->prepare("UPDATE lessons SET is_unlocked = ? WHERE id = ?");
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("ii", $state, $lesson_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Execute failed: " . $stmt->error);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>