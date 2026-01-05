<?php
// public/sign-out.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// remove all session data
session_unset();

// destroy session
session_destroy();

// redirect to sign-in
header('Location: index.php');
exit;
