<?php
// config.sample.php
// INSTRUCTIONS: 
// 1. Rename this file to 'config.php' to use it.
// 2. If using Docker, the values will be pulled from environment variables.
// 3. If running manually, update the fallback values (after the '?:') with your local credentials.

$DB_HOST = getenv('DB_HOST') ?: 'localhost';
$DB_USER = getenv('DB_USER') ?: 'root';
$DB_PASS = getenv('DB_PASSWORD') ?: 'YOUR_PASSWORD_HERE'; // <--- Update this if not using Docker
$DB_NAME = getenv('DB_NAME') ?: 'iec_platform';

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Use UTF8MB4 to support emojis and full unicode
$mysqli->set_charset('utf8mb4');

// Backwards compatibility: some files use $conn
$conn = $mysqli;
?>