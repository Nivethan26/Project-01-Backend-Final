<?php
include './userEmployee.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // Handle preflight requests
    exit();
}

$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input['id']) || !isset($input['pwd'])) {
    echo json_encode(['success' => false, 'message' => 'ID or Password not provided.']);
    exit();
}

$id = htmlspecialchars($input['id']);
$password = htmlspecialchars($input['pwd']);

$user = new userEmployee();
$user->setId($id);

// Fetch user from the database
$storedUser = $user->fetchUserById($id); // Implement this method to retrieve user data

if ($storedUser) {
    // Check if the password is hashed or in plain text
    if (password_get_info($storedUser['password'])['algoName'] !== 'unknown') {
        // Hashed password
        if (password_verify($password, $storedUser['password'])) {
            // Login successful
            echo json_encode([
                'success' => true,
                'id' => $storedUser['id'],
                'name' => $storedUser['name'],
                'email' => $storedUser['email'],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        }
    } else {
        // Plain text password
        if ($password === $storedUser['password']) {
            // Login successful
            echo json_encode([
                'success' => true,
                'id' => $storedUser['id'],
                'name' => $storedUser['name'],
                'email' => $storedUser['email'],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
        }
    }
} else {
    echo json_encode(['success' => false, 'message' => 'User not found.']);
}
?>