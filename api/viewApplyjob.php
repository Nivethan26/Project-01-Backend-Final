<?php
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin
header('Access-Control-Allow-Methods: GET'); // Allow only GET requests
header('Access-Control-Allow-Headers: Content-Type'); // Allow specific headers
include '../DatabaseConnection.php';// Include your database connection file

class Application {
    public function getApplications() {
        $db = new DatabaseConnection();
        $conn = $db->getConnection();

        $sql = "SELECT id, jobId, name, email, phone, cv, application_date FROM job_applications";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $applications;
    }
}

$app = new Application();
header('Content-Type: application/json'); // Set content type to JSON
echo json_encode($app->getApplications()); // Output applications as JSON
?>
