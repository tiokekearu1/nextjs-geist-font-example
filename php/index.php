<?php
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'teacher') {
        header('Location: views/teacher/dashboard.php');
        exit;
    } elseif ($_SESSION['user_type'] === 'admin') {
        header('Location: views/admin/dashboard.php');
        exit;
    }
}

// Otherwise, redirect to login page
header('Location: views/login.php');
exit;
