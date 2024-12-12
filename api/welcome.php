<?php
session_start();
header('Content-Type: application/json'); // Set the content type for JSON response
header("Access-Control-Allow-Origin: *"); // Allow requests from any origin
header("Access-Control-Allow-Headers: Content-Type, Authorization"); // Allow headers
header("Access-Control-Allow-Methods: GET, OPTIONS"); // Allow GET and OPTIONS methods

// Check if the user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Assuming user details are stored in session variables
$id = $_SESSION['id'];
$name = $_SESSION['name'];

// You can fetch more user details from the database if needed
// For example, using your userEmployee class
include './userEmployee.php';

$user = new userEmployee();
$user->setId($id);
$userDetails = $user->getDetails(); // Fetch user details from the database

// Prepare the response data
$response = [
    'success' => true,
    'data' => [
        'id' => $id,
        'name' => $name,
        // Add more details as needed
        'details' => $userDetails // Include additional user details here
    ]
];

echo json_encode($response);
?>