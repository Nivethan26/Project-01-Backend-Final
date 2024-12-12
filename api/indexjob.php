<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header('Content-Type: application/json');

include '../DatabaseConnection.php';

class Job {
    private $conn;

    public function __construct() {
        $db = new DatabaseConnection();
        $this->conn = $db->getConnection();
    }

    public function getJobs($id = null) {
        if ($id) {
            $sql = "SELECT * FROM jobs WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $job = $stmt->fetch(PDO::FETCH_ASSOC);
            return $job ? $job : ['message' => 'Job not found'];
        } else {
            $sql = "SELECT * FROM jobs";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function createJob($data, $files) {
        if (isset($data['jobId'], $data['jobTitle'], $data['content1'])) {
            // Sanitize inputs
            $jobId = htmlspecialchars($data['jobId']);
            $jobTitle = htmlspecialchars($data['jobTitle']);
            $experience = htmlspecialchars($data['experience']);
            $closingDate = htmlspecialchars($data['closingDate']);
            $salary = htmlspecialchars($data['salary']);
            $jobType = htmlspecialchars($data['jobType']);
            $content1 = htmlspecialchars($data['content1']);
            $content2 = htmlspecialchars($data['content2']);
            $keyResponsibilities = htmlspecialchars($data['keyResponsibilities']);
            $requirements = htmlspecialchars($data['requirements']);
            $benefits = htmlspecialchars($data['benefits']);
            $created_at = date('Y-m-d H:i:s');
            $image1 = null;

            // Directory for images
            $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $jobId . '/';
            $this->ensureDirectoryExists($upload_directory);

            // Handle file upload
            if (isset($files['image1']) && $files['image1']['error'] == UPLOAD_ERR_OK) {
                $image1 = basename($files['image1']['name']);
                move_uploaded_file($files['image1']['tmp_name'], $upload_directory . $image1);
            }

            // Prepare SQL for job insertion
            $sql = "INSERT INTO jobs (jobId, jobTitle, experience, closingDate, salary, content1, content2, keyResponsibilities, requirements, benefits, image1, created_at, jobType) 
                    VALUES (:jobId, :jobTitle, :experience, :closingDate, :salary, :content1, :content2, :keyResponsibilities, :requirements, :benefits, :image1, :created_at, :jobType)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':jobId', $jobId);
            $stmt->bindParam(':jobTitle', $jobTitle);
            $stmt->bindParam(':experience', $experience);
            $stmt->bindParam(':closingDate', $closingDate);
            $stmt->bindParam(':salary', $salary);
            $stmt->bindParam(':jobType', $jobType);
            $stmt->bindParam(':content1', $content1);
            $stmt->bindParam(':content2', $content2);
            $stmt->bindParam(':keyResponsibilities', $keyResponsibilities);
            $stmt->bindParam(':requirements', $requirements);
            $stmt->bindParam(':benefits', $benefits);
            $stmt->bindParam(':image1', $image1);
            $stmt->bindParam(':created_at', $created_at);

            return $stmt->execute()
                ? ['status' => 1, 'message' => 'Job created successfully.']
                : ['status' => 0, 'message' => 'Failed to create job.'];
        } else {
            return ['status' => 0, 'message' => 'Invalid input'];
        }
    }

    public function updateJob($data, $files) {
        $id = $data['id'] ?? null;
        $jobId = $data['jobId'] ?? null;
        $jobTitle = $data['jobTitle'] ?? null;
        $experience = $data['experience'] ?? null;
        $closingDate = $data['closingDate'] ?? null;
        $salary = $data['salary'] ?? null;
        $jobType = $data['jobType'] ?? null;
        $content1 = $data['content1'] ?? null;
        $content2 = $data['content2'] ?? null;
        $keyResponsibilities = $data['keyResponsibilities'] ?? null;
        $requirements = $data['requirements'] ?? null;
        $benefits = $data['benefits'] ?? null;
        $oldImage1 = $data['oldImage1'] ?? null;

        if (!$id || !$jobId || !$jobTitle || !$content1) {
            return $this->respond(['status' => 0, 'message' => 'Missing required fields']);
        }

        $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $jobId . '/';
        $this->ensureDirectoryExists($upload_directory);

        $image1 = $oldImage1; // Default to old image path
        if (isset($files['image1']) && $files['image1']['error'] === UPLOAD_ERR_OK) {
            $image1 = basename($files['image1']['name']);
            move_uploaded_file($files['image1']['tmp_name'], $upload_directory . $image1);
            // Delete old image if a new one is uploaded
            if ($oldImage1) {
                $oldImagePath = $upload_directory . $oldImage1;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }

        $sql = "UPDATE jobs 
                SET jobId = :jobId, jobTitle = :jobTitle, experience = :experience, closingDate = :closingDate, 
                    salary = :salary, content1 = :content1, content2 = :content2, 
                    keyResponsibilities = :keyResponsibilities, requirements = :requirements, 
                    benefits = :benefits, image1 = :image1, jobType = :jobType
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':jobId', $jobId);
        $stmt->bindParam(':jobTitle', $jobTitle);
        $stmt->bindParam(':experience', $experience);
        $stmt->bindParam(':closingDate', $closingDate);
        $stmt->bindParam(':salary', $salary);
        $stmt->bindParam(':jobType', $jobType);
        $stmt->bindParam(':content1', $content1);
        $stmt->bindParam(':content2', $content2);
        $stmt->bindParam(':keyResponsibilities', $keyResponsibilities);
        $stmt->bindParam(':requirements', $requirements);
        $stmt->bindParam(':benefits', $benefits);
        $stmt->bindParam(':image1', $image1);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return $this->respond(['status' => 1, 'message' => 'Job updated successfully']);
        } else {
            return $this->respond(['status' => 0, 'message' => 'Failed to update job']);
        }
    }

    public function deleteJob($id) {
        if (is_numeric($id)) {
            $sql = "DELETE FROM jobs WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            return $stmt->execute()
                ? ['status' => 1, 'message' => 'Record deleted successfully.']
                : ['status' => 0, 'message' => 'Failed to delete record.'];
        } else {
            return ['status' => 0, 'message' => 'Invalid ID for deletion.'];
        }
    }

    private function ensureDirectoryExists($directory) {
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    private function respond($response) {
        echo json_encode($response);
        exit();
    }
}

// Handle the request
$jobManager = new Job();
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

switch ($method) {
    case "GET":
        $id = isset($path[3]) ? $path[3] : null;
        $response = $jobManager->getJobs($id);
        echo json_encode($response);
        break;

    case "POST":
        if (isset($_POST['id'])) {
            $response = $jobManager->updateJob($_POST, $_FILES);
        } else {
            $response = $jobManager->createJob($_POST, $_FILES);
        }
        echo json_encode($response);
        break;

    case "DELETE":
        $id = isset($path[3]) ? $path[3] : null;
        $response = $jobManager->deleteJob($id);
        echo json_encode($response);
        break;

    default:
        echo json_encode(['message' => 'Method not allowed']);
        break;
}
?>
