<?php
// app/leaderboard-logic.php

function getWeeklyLeaderboard($conn, $module_id) {
    // 1. Get ALL students (Global Leaderboard)
    $sql_students = "SELECT id, name FROM users WHERE role = 'student'";
    $result = $conn->query($sql_students);
    
    $leaderboard = [];

    while ($student = $result->fetch_assoc()) {
        $sid = $student['id'];
        $points = 0;
        $days_completed = 0;

        // --- A. DAILY LESSON POINTS (10 pts per day, Exclude Day 7) ---
        $sql_days = "
            SELECT COUNT(*) as cnt 
            FROM student_lesson_progress slp
            JOIN lessons l ON slp.lesson_id = l.id
            WHERE slp.student_id = $sid 
            AND l.module_id = $module_id 
            AND slp.status = 'completed'
            AND l.day_number <= 6
        ";
        $res_days = $conn->query($sql_days)->fetch_assoc();
        $days_completed = (int)$res_days['cnt']; 
        $points += ($days_completed * 10);

        // --- B. WEEKLY COMPLETION BONUS (10 pts) ---
        if ($days_completed >= 6) {
            $points += 10;
        }

        // --- C. QUIZ PARTICIPATION (2 pts submit + 1 pt pass) ---
        $sql_quiz = "
            SELECT ssp.status, ssp.score 
            FROM student_step_progress ssp
            JOIN module_steps ms ON ssp.step_id = ms.id
            WHERE ssp.student_id = $sid 
            AND ssp.module_id = $module_id
            AND ms.step_type = 'practice'
        ";
        $res_quiz = $conn->query($sql_quiz);
        while ($q = $res_quiz->fetch_assoc()) {
            if ($q['status'] == 'completed') {
                $points += 2; // Participation
                if ($q['score'] >= 60) {
                    $points += 1; // Performance Bonus
                }
            }
        }

        // --- BUILD DATA ---
        // Only add to leaderboard if they have at least 1 point (optional cleanup)
        // or keep everyone to show full class list. We'll keep everyone.
        $leaderboard[] = [
            'id' => $sid,
            'name' => $student['name'],
            'points' => $points,
            'days_count' => $days_completed,
            'is_me' => ($sid == $_SESSION['user_id'])
        ];
    }

    // 2. Sort by Points DESC, then Days Completed DESC
    usort($leaderboard, function($a, $b) {
        if ($a['points'] == $b['points']) {
            return $b['days_count'] - $a['days_count']; // Tie-breaker
        }
        return $b['points'] - $a['points'];
    });

    // 3. Assign Ranks
    foreach ($leaderboard as $key => $val) {
        $leaderboard[$key]['rank'] = $key + 1;
    }

    return $leaderboard;
}
?>