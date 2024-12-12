<?php
// Include the database connection file
require_once '../DatabaseConnection.php';

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Create an instance of the DbConnect class
$db = new DatabaseConnection();
$conn = $db->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Collect the data from the form
    $jobId = isset($_POST['jobId']) ? $_POST['jobId'] : null;
    $jobTitle = isset($_POST['jobTitle']) ? $_POST['jobTitle'] : null;
    $closingDate = isset($_POST['closingDate']) ? $_POST['closingDate'] : null;
    $salary = isset($_POST['salary']) ? $_POST['salary'] : null;
    $experience = isset($_POST['experience']) ? $_POST['experience'] : null;
    $jobType = isset($_POST['jobType']) ? $_POST['jobType'] : null;
    $content1 = isset($_POST['content1']) ? $_POST['content1'] : null;
    $content2 = isset($_POST['content2']) ? $_POST['content2'] : null;
    $keyResponsibilities = isset($_POST['keyResponsibilities']) ? $_POST['keyResponsibilities'] : null;
    $requirements = isset($_POST['requirements']) ? $_POST['requirements'] : null;
    $benefits = isset($_POST['benefits']) ? $_POST['benefits'] : null;
    $oldImage1 = isset($_POST['oldImage1']) ? $_POST['oldImage1'] : null;

    if (!$jobId || !$jobTitle || !$closingDate || !$salary || !$experience || !$jobType || !$content1) {
        echo json_encode(['message' => 'Missing required fields']);
        exit();
    }
    
    $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $jobId . '/';
    // Create the directory if it doesn't exist
    if (!file_exists($upload_directory)) {
        mkdir($upload_directory, 0777, true);
    }

    // Handle file uploads
    $image1 = $oldImage1; // Default to old image path
    
    if (isset($_FILES['image1']) && $_FILES['image1']['error'] === UPLOAD_ERR_OK) {
        $image1 = basename($_FILES['image1']['name']);
        if (!move_uploaded_file($_FILES['image1']['tmp_name'], $upload_directory . $image1)) {
            echo json_encode(['message' => 'Error uploading the new image']);
            exit();
        }
    }

    // Prepare the SQL update query
    $sql = "UPDATE jobs 
            SET jobTitle = :jobTitle, closingDate = :closingDate, salary = :salary, experience = :experience, 
                content1 = :content1, content2 = :content2, keyResponsibilities = :keyResponsibilities, 
                requirements = :requirements, benefits = :benefits, image1 = :image1 ,jobType =:jobType
            WHERE jobId = :jobId";

    $stmt = $conn->prepare($sql);
    
    // Bind the parameters
    $stmt->bindParam(':jobId', $jobId);
    $stmt->bindParam(':jobTitle', $jobTitle);
    $stmt->bindParam(':closingDate', $closingDate);
    $stmt->bindParam(':salary', $salary);
    $stmt->bindParam(':experience', $experience);
    $stmt->bindParam(':jobType', $jobType);
    $stmt->bindParam(':content1', $content1);
    $stmt->bindParam(':content2', $content2);
    $stmt->bindParam(':keyResponsibilities', $keyResponsibilities);
    $stmt->bindParam(':requirements', $requirements);
    $stmt->bindParam(':benefits', $benefits);
    $stmt->bindParam(':image1', $image1);
    
    // Execute the query
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Job updated successfully']);
    } else {
        echo json_encode(['message' => 'Failed to update job']);
    }
} else {
    echo json_encode(['message' => 'Invalid Request']);
}
?>
