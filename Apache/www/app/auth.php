<?php
session_start();
require_once __DIR__ . '/config.php'; // Ensures we have the database connection ($conn)

/**
 * Handle User Login
 */
function loginUser($email, $password, $conn) {
    // 1. Sanitize input to prevent SQL Injection
    $email = mysqli_real_escape_string($conn, $email);
    
    // 2. Query the user
    $sql = "SELECT * FROM users WHERE email = '$email' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        
        // 3. Verify Password (using PHP's built-in hashing)
        if (password_verify($password, $user['password'])) {
            
            // 4. Set Session Variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['group_id'] = $user['group_id']; // Key for students

            // 5. Role-Based Redirection (The "Traffic Cop")
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../admin/dashboard.php");
                    break;
                case 'tutor':
                    // We will handle the "Unassigned" logic in the dashboard itself
                    header("Location: ../tutor/dashboard.php");
                    break;
                case 'student':
                    header("Location: ../public/student-dashboard.php");
                    break;
                default:
                    // Fallback
                    header("Location: ../public/sign-in.php?error=invalid_role");
            }
            exit();
        } else {
            return "Incorrect password.";
        }
    } else {
        return "User not found.";
    }
}

/**
 * Security Guard: Check if user is allowed on this page
 */
function requireRole($required_role) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../public/sign-in.php?error=please_login");
        exit();
    }

    if ($_SESSION['user_role'] !== $required_role) {
        // Stop students from seeing Admin pages
        echo "Access Denied. You are not authorized to view this page.";
        exit();
    }
}
?>