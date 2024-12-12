<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

// Include the DbConnector class
include '../DatabaseConnection.php';

try {
    // Create an instance of DbConnector
    $db = new DatabaseConnection();
    $conn = $db->getConnection();

    // Check if the connection was successful
    if (!$conn) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to connect to the database.']);
        exit();
    }

    // Prepare and execute the query
    $sql = "SELECT * FROM leave_applications WHERE status = 'Pending'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Fetch all applications
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return results as JSON
    http_response_code(200); // Success
    echo json_encode($applications);

} catch (PDOException $e) {
    // Handle any errors that occurred during the execution
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Database query failed: ' . $e->getMessage()]);
}
?>