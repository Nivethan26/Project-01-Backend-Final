<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

session_start();

include './userEmployee.php'; // Include the userEmployee class

// Retrieve and sanitize employee ID from GET request
$employeeId = isset($_GET['id']) ? trim(htmlspecialchars($_GET['id'])) : '';

// Check if the ID is not empty
if (empty($employeeId)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid employee ID']);
    exit();
}

$db = new DatabaseConnection();
$pdo = $db->getConnection();

try {
    // Create an instance of userEmployee and set the ID
    $userEmployee = new userEmployee();
    $userEmployee->setId($employeeId); // Set the ID for subsequent operations

    // Handle POST requests for update or delete operations
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get the posted data
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (!$data) {
            http_response_code(400);
            echo json_encode(['error' => 'Invalid JSON data']);
            exit();
        }

        // Handle update operation
        if (isset($data['update'])) {
            // Fetch the current employee details before updating
            $currentEmployee = $userEmployee->getDetails();

            if (!$currentEmployee) {
                http_response_code(404);
                echo json_encode(['error' => 'Employee not found for update']);
                exit();
            }

            // Update logic, using null coalescing operator to avoid notices
            $updateResult = $userEmployee->updateDetails(
                $data['username'] ?? '', // Use null coalescing operator for safe access
                $data['name'] ?? '', 
                $data['email'] ?? '',
                $data['address'] ?? '',
                $data['phone'] ?? ''
            );

            if ($updateResult) {
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => 'Employee updated successfully',
                    'previous_data' => $currentEmployee // Send previous data if needed
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to update employee']);
            }
            exit(); // Exit after processing POST request to avoid further processing
        }

        // Handle delete operation
        if (isset($data['delete'])) {
            $deleteResult = $userEmployee->deleteDetails();
            
            if ($deleteResult) {
                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
            } else {
                http_response_code(500);
                echo json_encode(['error' => 'Failed to delete employee']);
            }
            exit(); // Exit after processing DELETE request
        }
    }

    // Handle GET request to retrieve employee details
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $details = $userEmployee->getDetails();
        
        if ($details) {
            http_response_code(200);
            echo json_encode($details);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Employee not found']);
        }
    }
} catch (PDOException $exc) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $exc->getMessage()]);
}