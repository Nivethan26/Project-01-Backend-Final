<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Start the session
session_start();

// Include the database connector and userEmployee class
include './userEmployee.php';

$db = new DatabaseConnection();
$pdo = $db->getConnection();

// Handle POST request for deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    $idToDelete = intval($_POST['delete']);
    $userEmployee = new userEmployee();

    // Execute deletion
    if ($userEmployee->deleteLeaveType($idToDelete)) {
        echo json_encode(['success' => true, 'message' => "Leave type deleted successfully!"]);
    } else {
        echo json_encode(['success' => false, 'message' => "Error deleting leave type."]);
    }
    exit(); // Terminate the script after handling the POST request
}

// Fetch leave types
$query = "SELECT * FROM leave_types ORDER BY id ASC";
$result = $pdo->query($query);

// Check if the query executed successfully and fetch results
if ($result) {
    $leaveTypes = $result->fetchAll(PDO::FETCH_ASSOC);
} else {
    $leaveTypes = []; // Fallback in case of query failure
}

// Return leave types as JSON
header('Content-Type: application/json');
echo json_encode($leaveTypes);
?>