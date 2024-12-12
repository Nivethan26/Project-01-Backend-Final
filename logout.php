<?php
session_start();
session_unset();
session_destroy();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, "/"); // Clear the cookie
}

exit();