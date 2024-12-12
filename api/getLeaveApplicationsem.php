<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

include '../DatabaseConnection.php';// Ensure this connects to your database

// Establish the database connection using DbConnector
$db = new DatabaseConnection();
$pdo = $db->getConnection();

// Initialize response array
$response = [
    'applications' => [],
    'message' => null,
    'error' => null
];

// Get the employee_id from the request
$employeeId = isset($_GET['employee_id']) ? $_GET['employee_id'] : null;

if ($employeeId) {
    try {
        // Fetch leave applications for the logged-in employee
        $sql = "SELECT  leave_type, start_date, end_date, reason, status FROM leave_applications WHERE employee_id = :employee_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':employee_id' => $employeeId]);
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Check if any applications were found
        if (!empty($applications)) {
            $response['applications'] = $applications;
            $response['message'] = 'Leave applications found.';
        } else {
            $response['message'] = 'No leave applications found for this employee.';
        }
    } catch (PDOException $e) {
        error_log('Database error: ' . $e->getMessage());
        $response['error'] = 'Unable to fetch leave applications. Please try again later.';
    } catch (Exception $e) {
        error_log('General error: ' . $e->getMessage());
        $response['error'] = 'An unexpected error occurred.';
    }
} else {
    $response['error'] = 'Employee ID is required.';
}

// Return JSON response
echo json_encode($response);
?>