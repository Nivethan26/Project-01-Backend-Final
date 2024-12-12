<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include the database connection parameters
include '../DatabaseConnection.php'; // Include the database connection file

try {
    // Create a new DbConnector instance
    $dbConnector = new DatabaseConnection();
    $pdo = $dbConnector->getConnection();
   
    // Get today's date in 'YYYY-MM-DD' format
    $today = date('Y-m-d');

    // Prepare the SQL query to count total employees
    $totalEmployeesStmt = $pdo->prepare("SELECT COUNT(*) as total FROM employees");
    $totalEmployeesStmt->execute();
    $totalEmployees = $totalEmployeesStmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Prepare the SQL query to count approved leave applications for today
    $leaveCountStmt = $pdo->prepare("SELECT COUNT(*) as count FROM leave_applications WHERE (start_date <= :today AND end_date >= :today) AND status = 'approved'");
    $leaveCountStmt->bindParam(':today', $today);
    
    // Execute the query for leave count
    $leaveCountStmt->execute();
    $leaveCount = $leaveCountStmt->fetch(PDO::FETCH_ASSOC)['count'];

    // Calculate the active employee count
    $activeEmployeeCount = $totalEmployees - $leaveCount;

    // Return the result as JSON
    echo json_encode([
        'activeEmployeeCount' => $activeEmployeeCount,
        'totalEmployees' => $totalEmployees,
        'employeesOnLeave' => $leaveCount // Add this if you need it in React
    ]);
} catch (PDOException $e) {
    // Handle any errors
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>