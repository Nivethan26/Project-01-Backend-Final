<?php
include './userEmployee.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Get the user ID from the request (e.g., from GET parameters)
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['error' => 'User ID not provided.']);
    exit();
}

$userId = htmlspecialchars($_GET['id'], ENT_QUOTES, 'UTF-8'); // Sanitize input

// Validate user ID format
if (!preg_match('/^[a-zA-Z0-9]{1,20}$/', $userId)) {
    echo json_encode(['error' => 'Invalid User ID format.']);
    exit();
}

// Create an instance of the userEmployee class
$user = new userEmployee();
$user->setId($userId);

// Fetch user details
$userDetails = $user->getDetails();

if ($userDetails) {
    $response = [
        'success' => true,
        'uname' => htmlspecialchars($userDetails['username']),
        'name' => htmlspecialchars($userDetails['name']),
        'email' => htmlspecialchars($userDetails['email']),
        'address' => htmlspecialchars($userDetails['address']),
        'phone' => htmlspecialchars($userDetails['phone']),
    ];

    echo json_encode($response);
} else {
    echo json_encode(['error' => 'User details not found.']);
}
?>