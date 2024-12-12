<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Include the DbConnector class
include '../DatabaseConnection.php';

// Create a new instance of DbConnector
$dbConnector = new DatabaseConnection();
$conn = $dbConnector->getConnection(); // Get the PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the raw POST data
    $json = file_get_contents("php://input");

    // Decode the JSON data into an associative array
    $data = json_decode($json, true);

    // Validate that decoding was successful and that required fields are present
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo json_encode(["error" => "Invalid JSON format."]);
        exit();
    }

    $id = $data['id'] ?? null; // User's ID
    $new_password = $data['new_password'] ?? null;

    if (!$id || !$new_password) {
        echo json_encode(['error' => 'Missing required fields.']);
        exit();
    }

    // Start a transaction
    $conn->beginTransaction();

    try {
        // Hash the new password before saving it to the database
        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Ensure new password hash is generated
        if ($new_password_hash === false) {
            throw new Exception('Password hashing failed.');
        }

        // Update the new hashed password in the database
        $update_query = "UPDATE employees SET password = :new_password WHERE id = :id";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bindValue(':new_password', $new_password_hash);
        $update_stmt->bindValue(':id', $id);

        // Execute the update statement
        if ($update_stmt->execute()) {
            if ($update_stmt->rowCount() > 0) {
                // Commit the transaction
                $conn->commit();
                echo json_encode(['message' => 'Password changed successfully!']);
            } else {
                // No rows were updated
                $conn->rollBack();
                echo json_encode(['error' => 'No changes were made to the password.']);
            }
        } else {
            // Rollback if the update fails
            $conn->rollBack();
            echo json_encode(['error' => 'Failed to execute update statement.']);
        }
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log('Error while updating password: ' . $e->getMessage());
        echo json_encode(['error' => 'An error occurred while updating the password.']);
    }
} else {
    echo json_encode(['error' => 'Invalid request method.']);
}
?>