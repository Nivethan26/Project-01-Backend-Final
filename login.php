<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once 'DatabaseConnection.php';
include_once 'User.php';

// Initialize database connection
$database = new DatabaseConnection();
$db = $database->getConnection();
$user = new User($db);

// Decode input data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['user']) || !isset($data['pass'])) {
    echo json_encode(['success' => 0, 'error' => 'Username and Password are required.']);
    exit();
}

$user->username = $data['user'];
$user->password = $data['pass'];

// Call the login method
$response = $user->login();

// Return the response as JSON
echo json_encode($response);
