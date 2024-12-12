<?php
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET'); // Allow only GET requests

include_once '../DatabaseConnection.php'; // Include your database connection file

if (isset($_GET['id'])) {
    $id = intval($_GET['id']); // Get the application ID from the query string
    $db = new DatabaseConnection();
    $conn = $db->getConnection();

    // Query to get the CV file path
    $sql = "SELECT cv FROM job_applications WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($application) {
        $filePath = $application['cv']; // Assuming cv stores the file path

        if (file_exists($filePath)) {
            // Set headers for file download
            header('Content-Description: File Transfer');
            header('Content-Type: application/pdf'); // Change the MIME type as needed
            header('Content-Disposition: attachment; filename=' . basename($filePath));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath); // Read the file and send it to the user
            exit;
        } else {
            echo json_encode(['error' => 'File does not exist.']);
        }
    } else {
        echo json_encode(['error' => 'Application not found.']);
    }
} else {
    echo json_encode(['error' => 'No application ID provided.']);
}
?>
