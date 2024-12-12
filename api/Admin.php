<?php
// Set the appropriate headers for JSON output
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *"); // Allows any origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");


// Include the userEmployee class to fetch employee and leave data
include './userEmployee.php';

// Create userEmployee instance
$user = new userEmployee();

try {
    // Fetch data for the admin dashboard
    $leaveTypeCount = $user->getLeaveTypeCount();     // Get the leave type count
    $totalEmployees = $user->getEmployeeCount();      // Get the total number of employees

    // These values are currently static, but should ideally be fetched from a database
    $departmentsCount = 8;                            // Example static value (could be dynamic)
    $pendingApplications = 4;                         // Example static value (use database query for dynamic)
    $declinedApplications = 2;                        // Example static value (use database query for dynamic)
    $approvedApplications = 6;                        // Example static value (use database query for dynamic)

    // Prepare the response data
    $response = [
        'leaveTypeCount' => $leaveTypeCount,
        'totalEmployees' => $totalEmployees,
        'departmentsCount' => $departmentsCount,
        'pendingApplications' => $pendingApplications,
        'declinedApplications' => $declinedApplications,
        'approvedApplications' => $approvedApplications
    ];

    // Send the response in JSON format
    echo json_encode($response);

} catch (Exception $e) {
    // Handle any errors
    http_response_code(500);
    echo json_encode(['error' => 'Something went wrong: ' . $e->getMessage()]);
}

?>