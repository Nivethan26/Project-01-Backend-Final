<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Include the database connection class
include '../DatabaseConnection.php';
$db = new DatabaseConnection();
$pdo = $db->getConnection();

// Fetch leave type to edit if ID is provided
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $query = "SELECT leave_type FROM leave_types WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $leaveType = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($leaveType) {
        echo json_encode(['success' => true, 'leave_type' => $leaveType['leave_type']]);
        exit();
    } else {
        echo json_encode(['success' => false, 'message' => "Leave type not found."]);
        exit();
    }
}

// Handle AJAX request for editing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_type'])) {
    $leaveTypeName = trim($_POST['leave_type']);
    $id = intval($_POST['id']);

    // Validate input
    if (empty($leaveTypeName)) {
        echo json_encode(['success' => false, 'message' => "Leave type cannot be empty."]);
        exit();
    }

    $updateQuery = "UPDATE leave_types SET leave_type = :leave_type WHERE id = :id";
    $stmt = $pdo->prepare($updateQuery);

    try {
        // Execute the update statement
        if ($stmt->execute(['leave_type' => $leaveTypeName, 'id' => $id])) {
            echo json_encode(['success' => true, 'message' => "Edit successful!"]);
        } else {
            // Fetch error info
            $errorInfo = $stmt->errorInfo();
            echo json_encode(['success' => false, 'message' => "Error updating leave type: " . $errorInfo[2]]);
        }
    } catch (PDOException $e) {
        error_log("Error updating leave type: " . $e->getMessage()); // Log error message
        echo json_encode(['success' => false, 'message' => "Error updating leave type: " . $e->getMessage()]);
    }
    exit();
}

// Close the database connection
$pdo = null;
?>