<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
include '../DatabaseConnection.php'; // Ensure this connects to your database

// Establish the database connection using DbConnector
$db = new DatabaseConnection();
$pdo = $db->getConnection();

// Initialize response array
$response = ['success' => false, 'error' => null];

try {
    // Get the input data
    $data = json_decode(file_get_contents('php://input'), true);

    // Validate input data
    if (!isset($data['id']) || !isset($data['status']) || trim($data['id']) === '' || trim($data['status']) === '') {
        throw new Exception('Missing or invalid input data.');
    }

    $id = $data['id']; // Ensure this is the correct ID for the leave application
    $status = $data['status'];

    // Debugging: Log the ID and status
    error_log('Updating status for ID: ' . $id . ' with status: ' . $status);

    // Update the status in the database
    $sql = "UPDATE leave_applications SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':status' => $status, ':id' => $id]);

    // Check if any row was updated
    if ($stmt->rowCount() === 0) {
        error_log('No rows updated for ID: ' . $id);  // Log for debugging
        throw new Exception('No application found with the provided ID.');
    }

    $response['success'] = true;
} catch (Exception $e) {
    error_log('Update error: ' . $e->getMessage());
    $response['error'] = $e->getMessage();
}

// Return JSON response
echo json_encode($response);
?>