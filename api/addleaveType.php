<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle OPTIONS request for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
include '../DatabaseConnection.php'; // Adjust path as necessary
$db = new DatabaseConnection();
$pdo = $db->getConnection();

$message = '';

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if leave_type is provided and not empty
    if (isset($_POST['leave_type']) && !empty(trim($_POST['leave_type']))) {
        $leaveTypeName = trim($_POST['leave_type']);
        
        // Prepare the SQL statement to prevent SQL injection
        $insertQuery = "INSERT INTO leave_types (leave_type) VALUES (:leave_type)";
        $stmt = $pdo->prepare($insertQuery);

        // Execute the statement and handle success/failure
        if ($stmt->execute(['leave_type' => $leaveTypeName])) {
            $message = "Leave type added successfully!";
        } else {
            $message = "Error adding leave type.";
        }
    } else {
        $message = "Leave type cannot be empty.";
    }
    
    // Return JSON response
    echo json_encode(['message' => $message]);
    exit();
}

// Method Not Allowed response for non-POST requests
http_response_code(405);
echo json_encode(['message' => 'Method Not Allowed']);
?>