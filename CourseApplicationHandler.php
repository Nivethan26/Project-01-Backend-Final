<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'DatabaseConnection.php';

class CourseApplicationHandler
{
    private $connection;

    public function __construct()
    {
        $db = new DatabaseConnection();
        $this->connection = $db->getConnection();
    }

    public function submitApplication($course_id, $course_name, $name, $email, $phone_no)
    {
        $sql = "INSERT INTO courseapplication (course_id, course_name, name, email, phone_no, submitted_at) VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $this->connection->prepare($sql);

        // Check if the statement was prepared correctly
        if (!$stmt) {
            error_log("Failed to prepare statement: " . $this->connection->error);
            return json_encode(["message" => "Failed to prepare statement", "error" => $this->connection->error]);
        }

        $stmt->bind_param("issss", $course_id, $course_name, $name, $email, $phone_no);

        if ($stmt->execute()) {
            error_log("Application submitted successfully");
            return json_encode(["message" => "Application submitted successfully"]);
        } else {
            error_log("Failed to submit application: " . $stmt->error);
            return json_encode(["message" => "Failed to submit application", "error" => $stmt->error]);
        }

        $stmt->close();
    }
}
