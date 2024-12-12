<?php
include './userEmployee.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // Handle preflight requests
    exit();
}

// Check if the user ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'User ID not provided.']);
    exit();
}

// Sanitize and validate the user ID
$id = htmlspecialchars($_GET['id']);

// Validate user ID format (e.g., check if it matches expected pattern)
if (!preg_match('/^[a-zA-Z0-9]{1,20}$/', $id)) {
    echo json_encode(['success' => false, 'message' => 'Invalid User ID format.']);
    exit();
}

$user = new userEmployee();
$user->setId($id);
$userDetails = $user->getDetails();

if ($userDetails) {
    echo json_encode([
        'success' => true,
        'data' => $userDetails // created_at should not be included if not selected in SQL query
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'User not found with ID: ' . $id]);
}
?>