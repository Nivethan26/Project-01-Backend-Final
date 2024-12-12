<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
session_start();
include './userEmployee.php';

if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$user = new userEmployee();
$user->setUsername($_SESSION['username']);
$user->setName($data['name']);
$user->setEmail($data['email']);
$user->setAddress($data['address']);
$user->setPhone($data['phone']);

// Assuming updateDetails is a method to update the user in the database
if ($user->getDetails()) {
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to update profile. Please try again.']);
}
?>