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

// Get the current date
$currentDate = date('Y-m-d');

try {
    // Fetch pending leave applications that have expired
    $sql = "SELECT id, end_date, status FROM leave_applications WHERE status = 'Pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process applications to update statuses based on business logic
    foreach ($applications as $application) {
        // Check if the application's end_date is less than the current date
        if ($application['end_date'] < $currentDate) {
            // Update the status to 'Declined' in the database
            $updateSql = "UPDATE leave_applications SET status = 'Declined' WHERE id = :id";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([':id' => $application['id']]);
        }
    }
    
    // Fetch the updated applications from the database
    $updatedSql = "SELECT * FROM leave_applications ";
    $updatedStmt = $pdo->prepare($updatedSql);
    $updatedStmt->execute();
    $response['applications'] = $updatedStmt->fetchAll(PDO::FETCH_ASSOC);

    // Check if any applications were found
    if (empty($response['applications'])) {
        $response['message'] = 'No leave applications found.';
    } else {
        $response['message'] = 'Expired pending applications updated to "Declined".';
    }
} catch (PDOException $e) {
    error_log('Database error: ' . $e->getMessage());
    $response['error'] = 'Unable to fetch leave applications. Please try again later.';
} catch (Exception $e) {
    error_log('General error: ' . $e->getMessage());
    $response['error'] = 'An unexpected error occurred.';
}

// Return JSON response
echo json_encode($response);
?>