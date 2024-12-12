<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
include '../DatabaseConnection.php'; // Ensure this connects to your database

// Get the input data
$data = json_decode(file_get_contents('php://input'), true);

// Check if the data is received correctly
if (!$data) {
    echo json_encode(['error' => 'No data received.']);
    exit();
}

// Validate the data
$requiredFields = ['employeeId', 'username', 'fullName', 'email', 'phoneNumber', 'leaveType', 'startDate', 'endDate', 'reason'];

foreach ($requiredFields as $field) {
    if (!isset($data[$field])) {
        echo json_encode(['error' => "Missing required field: $field"]);
        exit();
    }
}

// Sanitize inputs
$employeeId = trim($data['employeeId']);
$username = trim($data['username']);
$fullName = trim($data['fullName']);
$email = trim($data['email']);
$phoneNumber = trim($data['phoneNumber']);
$leaveType = trim($data['leaveType']); // Sanitize and ensure this matches the value in leave_types
$startDate = trim($data['startDate']);
$endDate = trim($data['endDate']);
$reason = trim($data['reason']);

// Establish the database connection
try {
    $db = new DatabaseConnection();
    $pdo = $db->getConnection(); // Get the PDO connection

    // Log the leaveType for debugging
    error_log("Leave Type received: $leaveType");

    // Check if the leaveType exists in the leave_types table
    $checkSql = "SELECT COUNT(*) FROM leave_types WHERE leave_type = ?";
    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->execute([$leaveType]);
    $leaveTypeExists = $checkStmt->fetchColumn();

    if (!$leaveTypeExists) {
        echo json_encode(['error' => 'Invalid leave type. Please select a valid leave type.']);
        exit();
    }

    // Insert into the leave applications table
    $sql = "INSERT INTO leave_applications (employee_id, username, full_name, email, phone_number, leave_type, start_date, end_date, reason, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $status = 'pending'; // Default status
    $stmt->execute([$employeeId, $username, $fullName, $email, $phoneNumber, $leaveType, $startDate, $endDate, $reason, $status]);

    echo json_encode(['message' => 'Leave application submitted successfully.']);
} catch (PDOException $e) {
    // Log the exact error message for debugging
    error_log('Database error: ' . $e->getMessage());
    echo json_encode(['error' => 'Database error. Please try again later.']);
}
?>