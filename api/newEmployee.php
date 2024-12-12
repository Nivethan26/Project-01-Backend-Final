<?php

// Set headers for CORS and request methods
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Require necessary classes for Employee management and Mailer
require_once './userEmployee.php';
require_once '../Mailer.php';

// Function to generate a random secure password
function generateRandomPassword($length = 12) {
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $specialChars = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    // Ensure password contains at least one character from each group
    $password = '';
    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];

    // Fill the remaining characters
    $allChars = $uppercase . $lowercase . $numbers . $specialChars;
    for ($i = 4; $i < $length; $i++) {
        $password .= $allChars[random_int(0, strlen($allChars) - 1)];
    }

    return str_shuffle($password); // Shuffle the password for randomness
}

// Retrieve input data from the request body
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validate input fields and sanitize them
$employeeId = isset($data['employeeId']) ? htmlspecialchars(trim($data['employeeId'])) : null;
$username = isset($data['username']) ? htmlspecialchars(trim($data['username'])) : null;
$fullName = isset($data['fullName']) ? htmlspecialchars(trim($data['fullName'])) : null;
$email = isset($data['email']) ? filter_var(trim($data['email']), FILTER_SANITIZE_EMAIL) : null;
$phone = isset($data['phone']) ? htmlspecialchars(trim($data['phone'])) : null;
$address = isset($data['address']) ? htmlspecialchars(trim($data['address'])) : null;

// Check that all fields are filled
if (empty($employeeId) || empty($username) || empty($fullName) || empty($email) || empty($phone) || empty($address)) {
    http_response_code(400);
    echo json_encode(["message" => "Error: All fields are required."]);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(["message" => "Error: Invalid email format."]);
    exit();
}

// Validate phone format (10 to 15 digits)
if (!preg_match('/^\d{10,15}$/', $phone)) {
    http_response_code(400);
    echo json_encode(["message" => "Error: Invalid phone number format."]);
    exit();
}

// Generate a random password and hash it
$password = generateRandomPassword();
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Create a new Employee record
$newEmployee = new userEmployee();
$result = $newEmployee->createEmployee($employeeId, $username, $hashedPassword, $fullName, $email, $phone, $address);

if ($result) {
    // Prepare email content with the auto-generated password
    $mailer = new Mailer();
    $msg = "Dear $fullName,<br>Your account has been successfully created.<br>" .
           "Your auto-generated password is: <strong>$password</strong><br>" .
           "Your employee ID is: <strong>$employeeId</strong><br>" .
           "Please use this password to log in. For further assistance, feel free to contact us.";
    
    // Set email information and send the email
    $mailer->setInfo($email, 'Welcome to AutoCare', $msg);
    
    try {
        if ($mailer->send()) {
            // Return employeeId and password in the response
            http_response_code(200);
            echo json_encode([
                "success" => true, 
                "message" => "Employee created successfully. A confirmation email has been sent.",
                "employeeId" => $employeeId,
                "password" => $password
            ]);
        } else {
            throw new Exception("Email notification failed.");
        }
    } catch (Exception $e) {
        http_response_code(500);
        error_log("Error: " . $e->getMessage() . " for email: $email."); // Log error
        echo json_encode(["success" => false, "message" => 'Employee created, but email notification failed.']);
    }
} else {
    http_response_code(500);
    error_log("Error: Unable to create employee record for ID: $employeeId."); // Log error
    echo json_encode(["success" => false, "message" => 'Error: Unable to create employee record.']);
}

?>