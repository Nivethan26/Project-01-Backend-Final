<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
session_start();
include './userEmployee.php';

// Read the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Check if POST data is set and not empty
if (isset($data['id']) && isset($data['pwd'])) {
    $id = $data['id'];
    $password = $data['pwd'];

    $user = new userEmployee();
    $user->setId($id);
    $user->setPassword($password);

    if ($user->login()) {
        // Login successful, set session variables
        $_SESSION['id'] = $id;
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['email'] = $user->getEmail();
        $_SESSION['name'] = $user->getName();
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Please provide both username and password.']);
}
?>
