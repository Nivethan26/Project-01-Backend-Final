<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests for CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include_once 'DatabaseConnection.php';
include_once 'User.php';

// Initialize database connection using PDO
$database = new DatabaseConnection();
$db = $database->getConnection();
$user = new User($db);

// Decode the input data from the request body
$data = json_decode(file_get_contents("php://input"), true);

// Validate input data
if (!isset($data['user']) || !isset($data['email']) || !isset($data['pass']) || !isset($data['cpass'])) {
    echo json_encode(["error" => "Invalid input data. All fields are required."]);
    exit;
}

// Extract input data without sanitizing
$username = $data['user'];
$email = $data['email'];
$password = $data['pass'];
$confirmPassword = $data['cpass'];

// Validation regex patterns
$usernameRegex = "/^[a-zA-Z0-9@_]{4,20}$/";
$emailRegex = "/^[^\s@]+@[^\s@]+\.[^\s@]+$/";
$passwordRegex = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*[@!?\/\-_])[A-Za-z\d@!?\/\-_]{8,20}$/";


// Validate username
if (!preg_match($usernameRegex, $username)) {
    echo json_encode(["error" => "Username must be between 4 and 20 characters and may include letters, numbers, @, and _."]);
    exit;
}

// Validate email
if (!preg_match($emailRegex, $email)) {
    echo json_encode(["error" => "Please enter a valid email address."]);
    exit;
}

// Check if the username or email already exists
if ($user->userExists($username, $email)) {
    echo json_encode(["error" => "User already exists."]);
    exit;
}

// Validate password
if (!preg_match($passwordRegex, $password)) {
    echo json_encode(["error" => "Password must be 8-20 characters long and include at least one uppercase letter, one lowercase letter, and one special character (@,!,?,/,_,-)."]);
    exit;
}

// Check if passwords match
if ($password !== $confirmPassword) {
    echo json_encode(["error" => "Passwords do not match."]);
    exit;
}

// Set user properties
$user->username = $username;
$user->email = $email;
$user->password = $password;//password_hash($password, PASSWORD_BCRYPT); // Hashing password for security

// Call the register method to save the user in the database
if ($user->register()) {
    echo json_encode(["message" => "Registration successful."]);
} else {
    echo json_encode(["error" => "Registration failed. Please try again later."]);
}
