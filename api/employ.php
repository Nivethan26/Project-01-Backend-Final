<?php
header("Access-Control-Allow-Origin: *"); // Allows any origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json'); // Set content type to JSON

// Include the database connector
include '../DatabaseConnection.php';

// Create an instance of DbConnector and get the PDO connection
$dbcon = new DatabaseConnection();
$con = $dbcon->getConnection();

// Fetch all employees from the database
try {
    $query = "SELECT id, username, name FROM employees ORDER BY id ASC"; // Ensure 'id' is the correct column
    $pstmt = $con->prepare($query);
    $pstmt->execute();
    $employees = $pstmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the employee data as JSON
    echo json_encode($employees);
} catch (PDOException $exc) {
    // Return error message as JSON
    echo json_encode(["error" => "Error fetching employees: " . htmlspecialchars($exc->getMessage())]);
    exit();
}
?>