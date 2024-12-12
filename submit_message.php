<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require 'DatabaseConnection.php';
    require 'Message.php';

    // Get the JSON data from the request body
    $input = json_decode(file_get_contents('php://input'), true);

    // Check if the JSON data was successfully decoded
    if (json_last_error() === JSON_ERROR_NONE) {
        // Create a new database connection
        $db = new DatabaseConnection();
        $message = new Message($db->getConnection());

        // Retrieve the form data
        $firstName = isset($input['firstName']) ? $input['firstName'] : null;
        $phone = isset($input['phone']) ? $input['phone'] : null;
        $email = isset($input['email']) ? $input['email'] : null;
        $messageText = isset($input['message']) ? $input['message'] : null;

        // Check if all required fields are present
        if ($firstName && $phone && $email && $messageText) {
            // Insert the message into the database
            $success = $message->insertMessage($firstName, $phone, $email, $messageText);
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Incomplete form data.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
