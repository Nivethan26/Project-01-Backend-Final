<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header('Content-Type: application/json');

include '../DatabaseConnection.php';

class CourseUpdater
{
    private $conn;

    public function __construct($dbConnection)
    {
        $this->conn = $dbConnection;
    }

    public function updateCourse()
    {
        // Retrieve form data
        $Id = isset($_POST['id']) ? $_POST['id'] : null;
        $courseId = isset($_POST['courseId']) ? $_POST['courseId'] : null;
        $courseName = isset($_POST['courseName']) ? $_POST['courseName'] : null;
        $courseDuration = isset($_POST['courseDuration']) ? $_POST['courseDuration'] : null;
        $courseFee = isset($_POST['courseFee']) ? $_POST['courseFee'] : null;
        $content1 = isset($_POST['content1']) ? $_POST['content1'] : null;
        $content2 = isset($_POST['content2']) ? $_POST['content2'] : null;
        $oldImage1 = isset($_POST['oldImage1']) ? $_POST['oldImage1'] : null;
        $oldImage2 = isset($_POST['oldImage2']) ? $_POST['oldImage2'] : null;

        $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $courseId . '/';
        if (!file_exists($upload_directory)) {
            mkdir($upload_directory, 0777, true);
        }

        // Handle file uploads
        $image1 = $oldImage1; // Default to old image path
        $image2 = $oldImage2; // Default to old image path

        if (isset($_FILES['image1']) && $_FILES['image1']['error'] == UPLOAD_ERR_OK) {
            $image1 = basename($_FILES['image1']['name']);
            move_uploaded_file($_FILES['image1']['tmp_name'], $upload_directory . $image1);
        }

        if (isset($_FILES['image2']) && $_FILES['image2']['error'] == UPLOAD_ERR_OK) {
            $image2 = basename($_FILES['image2']['name']);
            move_uploaded_file($_FILES['image2']['tmp_name'], $upload_directory . $image2);
        }

        // Prepare and execute update query
        $sql = "UPDATE courses 
                SET courseId = :courseId,
                    courseName = :courseName, 
                    courseDuration = :courseDuration, 
                    courseFee = :courseFee, 
                    content1 = :content1, 
                    content2 = :content2, 
                    image1 = :image1, 
                    image2 = :image2 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':courseId', $courseId);
        $stmt->bindParam(':courseName', $courseName);
        $stmt->bindParam(':courseDuration', $courseDuration);
        $stmt->bindParam(':courseFee', $courseFee);
        $stmt->bindParam(':content1', $content1);
        $stmt->bindParam(':content2', $content2);
        $stmt->bindParam(':image1', $image1);
        $stmt->bindParam(':image2', $image2);
        $stmt->bindParam(':id', $Id);

        $response = $stmt->execute()
            ? ['status' => 1, 'message' => 'Record updated successfully.']
            : ['status' => 0, 'message' => 'Failed to update record.'];

        echo json_encode($response);
    }
}

// Initialize and call the update method
$objDb = new DatabaseConnection();
$conn = $objDb->getConnection();

$courseUpdater = new CourseUpdater($conn);
$courseUpdater->updateCourse();
?>
