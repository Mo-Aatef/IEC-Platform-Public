<?php
// admin/api-toggle-module.php

// 1. Setup
header('Content-Type: application/json');
require_once __DIR__ . '/../app/config.php';

// 2. Check Connection
if (!isset($conn)) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// 3. Get Input
$input = json_decode(file_get_contents('php://input'), true);
$module_id = isset($input['module_id']) ? (int)$input['module_id'] : 0;
// Note: In your DB, 1 = Locked, 0 = Unlocked (The opposite of lessons)
// We will flip this logic in JS to match the UI (Switch ON = Open/Green)
$is_open = isset($input['is_open']) ? (int)$input['is_open'] : 0;

if (!$module_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

// 4. Update DB (is_global_locked: 1=Locked, 0=Open)
$db_status = $is_open ? 0 : 1; 

$stmt = $conn->prepare("UPDATE modules SET is_global_locked = ? WHERE id = ?");
$stmt->bind_param("ii", $db_status, $module_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>