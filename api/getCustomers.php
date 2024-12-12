<?php
header('Content-Type: application/json');

// Include the database connection class
require_once '../DatabaseConnection.php';

try {
    // Create a new instance of the DatabaseConnection
    $db = new DatabaseConnection();
    $connection = $db->getConnection();

    // SQL query to fetch all users
    $sql = "SELECT id, username, email FROM users";
    $stmt = $connection->prepare($sql);
    $stmt->execute();

    // Fetch all users as an associative array
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Return the user data as JSON
    echo json_encode($users);
} catch (Exception $e) {
    // Handle errors and return a JSON error message
    echo json_encode(['error' => $e->getMessage()]);
}
?>
