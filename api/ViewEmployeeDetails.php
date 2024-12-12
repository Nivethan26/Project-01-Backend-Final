<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include '../DatabaseConnection.php'; // Include your database connector

$dbConnector = new DatabaseConnection();
$pdo = $dbConnector->getConnection(); // Establish the database connection

// Check if employee_id is set and not empty
if (!isset($_GET['employee_id']) || empty($_GET['employee_id'])) {
    echo json_encode(['error' => 'Employee ID is required']);
    exit;
}

$employee_id = $_GET['employee_id'];

try {
    // Prepare and execute the SQL statement
    $stmt = $pdo->prepare('SELECT * FROM employees WHERE id = :employee_id');
    $stmt->execute(['employee_id' => $employee_id]);
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    // Check if employee exists
    if ($employee) {
        echo json_encode($employee);  // Return employee data
    } else {
        echo json_encode(['error' => 'Employee not found']);  // Employee not found
    }
} catch (PDOException $e) {
    // Return error message and log the error
    echo json_encode(['error' => 'Error fetching employee details: ' . $e->getMessage()]);
    error_log($e->getMessage());
}
?>