<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header('Content-Type: application/json'); // Ensure content type is set to JSON

include '../DatabaseConnection.php';
$objDb = new DatabaseConnection();
$conn = $objDb->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

if ($method == 'POST') {
    // Decode form data
    $course_id = $_POST['courseId'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $created_at = date('Y-m-d H:i:s');

    // Check if the user has already applied for the same course
    $checkSql = "SELECT * FROM applications WHERE course_id = ? AND email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(1, $course_id);
    $checkStmt->bindParam(2, $email);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        // User has already applied for this course
        echo json_encode(['status' => 0, 'message' => 'You have already applied for this course.']);
    } else {
        // Proceed to insert the application
        $sql = "INSERT INTO applications (course_id, name, email, phone, created_at) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(1, $course_id);
        $stmt->bindParam(2, $name);
        $stmt->bindParam(3, $email);
        $stmt->bindParam(4, $phone);
        $stmt->bindParam(5, $created_at);

        if ($stmt->execute()) {
            echo json_encode(['status' => 1, 'message' => 'Application submitted successfully.']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Failed to submit the application.']);
        }
    }

} else {
    echo json_encode(['status' => 0, 'message' => 'Invalid request method.']);
}
?>