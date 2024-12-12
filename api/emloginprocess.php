<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
session_start();
include '../DatabaseConnection.php';// Ensure this path is correct

// Read the JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Check if JSON input has 'id' and 'pwd'
if (isset($data['id']) && isset($data['pwd'])) {
    $id = $data['id'];
    $password = $data['pwd'];

    // Create a new instance of userEmployee
    $user = new userEmployee();
    $user->setId($id);
    $user->setPassword($password); // Assuming setPassword sets plain text or hashed

    // Attempt to log in
    if ($user->login()) {
        // Login successful, set session variables
        $_SESSION['id'] = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        $_SESSION['email'] = $user->getEmail();
        $_SESSION['name'] = $user->getName();

        // Return success response (do not redirect for API usage)
        echo json_encode(['success' => true, 'message' => 'Login successful.']);
    } else {
        // Invalid credentials
        echo json_encode(['success' => false, 'message' => 'Invalid credentials.']);
    }
} else {
    // Missing username or password
    echo json_encode(['success' => false, 'message' => 'Please provide both username and password.']);
}
?>
