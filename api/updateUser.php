<?php
include './userEmployee.php';

header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Get user details from POST request
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['id'])) {
    echo json_encode(['error' => 'User ID not provided.']);
    exit();
}

$id = $data['id'];
$username = $data['username'];
$name = $data['name'];
$email = $data['email'];
$phone = $data['phone'];
$address = $data['address'];

$user = new userEmployee();
$user->setId($id);
$result = $user->updateDetails($username, $name, $email, $address, $phone);

if ($result) {
    echo json_encode(['success' => true, 'message' => 'User details updated successfully']);
} else {
    echo json_encode(['error' => 'Failed to update user details']);
}
?>