<?php
include '../DatabaseConnection.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = new DatabaseConnection();
    $conn = $db->getConnection();

    // Retrieve and sanitize form inputs
    $jobId = $_POST['jobId'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];

    // Handle the CV file upload
    $uploadDir = 'Backend/pdf/' . $jobId . '/' . $name . '/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
    }

    // Extract file details
    $cvFile = $_FILES['cv'];
    $cvFileName = $name;
    $cvFilePath = $uploadDir . $cvFileName;

    // Move the uploaded file to the destination
    if (move_uploaded_file($cvFile['tmp_name'], $cvFilePath)) {
        try {
            // Check if an application with the same jobId and email already exists
            $checkStmt = $conn->prepare('SELECT COUNT(*) FROM job_applications WHERE jobId = :jobId AND email = :email');
            $checkStmt->bindParam(':jobId', $jobId);
            $checkStmt->bindParam(':email', $email);
            $checkStmt->execute();
            $applicationExists = $checkStmt->fetchColumn();

            if ($applicationExists > 0) {
                // If application already exists, return this message
                echo json_encode(['message' => 'You have already applied for this job.']);
            } else {
                // Prepare SQL statement for inserting the new application
                $stmt = $conn->prepare('INSERT INTO job_applications (jobId, name, email, phone, cv) VALUES (:jobId, :name, :email, :phone, :cv)');
                $stmt->bindParam(':jobId', $jobId);
                $stmt->bindParam(':name', $name);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':cv', $cvFilePath);

                // Execute the statement
                if ($stmt->execute()) {
                    echo json_encode(['message' => 'Application submitted successfully!']);
                } else {
                    echo json_encode(['message' => 'Error submitting the application.']);
                }
            }
        } catch (Exception $e) {
            echo json_encode(['message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['message' => 'Error uploading the CV file.']);
    }
} else {
    echo json_encode(['message' => 'Invalid request method.']);
}
?>
