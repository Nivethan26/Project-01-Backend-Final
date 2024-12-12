<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header('Content-Type: application/json');

include '../DatabaseConnection.php';

class CourseManager {
    private $conn;

    public function __construct() {
        $objDb = new DatabaseConnection();
        $this->conn = $objDb->getConnection();
    }

    // Method to handle GET requests (fetch courses or individual course by ID)
    public function getCourses($courseId = null) {
        if ($courseId) {
            // Fetch individual course
            $sql = "SELECT * FROM courses WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $courseId);
            $stmt->execute();
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($course ?: ['message' => 'Course not found']);
        } else {
            // Fetch all courses
            $sql = "SELECT * FROM courses";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($courses);
        }
    }

    // Method to create a course
    public function createCourse($data, $files) {
        $image1 = null;
        $image2 = null;
        
        $this->ensureDirectoryExists($data['courseId']);

        // Handle image uploads
        if (isset($files['image1']) && $files['image1']['error'] == UPLOAD_ERR_OK) {
            $image1 = $this->uploadFile($files['image1'], $data['courseId']);
        }

        if (isset($files['image2']) && $files['image2']['error'] == UPLOAD_ERR_OK) {
            $image2 = $this->uploadFile($files['image2'], $data['courseId']);
        }

        // Prepare SQL for course insertion
        $sql = "INSERT INTO courses (courseId, courseName, courseDuration, courseFee, content1, content2, image1, image2, created_at) 
                VALUES (:courseId, :courseName, :courseDuration, :courseFee, :content1, :content2, :image1, :image2, :created_at)";
        $stmt = $this->conn->prepare($sql);

        $created_at = date('Y-m-d H:i:s');
        $stmt->bindParam(':courseId', $data['courseId']);
        $stmt->bindParam(':courseName', $data['courseName']);
        $stmt->bindParam(':courseDuration', $data['courseDuration']);
        $stmt->bindParam(':courseFee', $data['courseFee']);
        $stmt->bindParam(':content1', $data['content1']);
        $stmt->bindParam(':content2', $data['content2']);
        $stmt->bindParam(':image1', $image1);
        $stmt->bindParam(':image2', $image2);
        $stmt->bindParam(':created_at', $created_at);

        $response = $stmt->execute()
            ? ['status' => 1, 'message' => 'Course created successfully.']
            : ['status' => 0, 'message' => 'Failed to create course.'];

        echo json_encode($response);
    }

    // Method to update a course
    public function updateCourse($data, $files) {
        $this->ensureDirectoryExists($data['courseId']);

        // Handle file uploads or retain old image paths
        $image1 = isset($files['image1']) && $files['image1']['error'] == UPLOAD_ERR_OK
            ? $this->uploadFile($files['image1'], $data['courseId'])
            : $data['oldImage1'];

        $image2 = isset($files['image2']) && $files['image2']['error'] == UPLOAD_ERR_OK
            ? $this->uploadFile($files['image2'], $data['courseId'])
            : $data['oldImage2'];

        // Prepare SQL for course update
        $sql = "UPDATE courses 
                SET courseId = :courseId, courseName = :courseName, courseDuration = :courseDuration, 
                    courseFee = :courseFee, content1 = :content1, content2 = :content2, 
                    image1 = :image1, image2 = :image2 
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':courseId', $data['courseId']);
        $stmt->bindParam(':courseName', $data['courseName']);
        $stmt->bindParam(':courseDuration', $data['courseDuration']);
        $stmt->bindParam(':courseFee', $data['courseFee']);
        $stmt->bindParam(':content1', $data['content1']);
        $stmt->bindParam(':content2', $data['content2']);
        $stmt->bindParam(':image1', $image1);
        $stmt->bindParam(':image2', $image2);
        $stmt->bindParam(':id', $data['id']);

        $response = $stmt->execute()
            ? ['status' => 1, 'message' => 'Record updated successfully.']
            : ['status' => 0, 'message' => 'Failed to update record.'];

        echo json_encode($response);
    }

    // Helper method to handle file uploads
    private function uploadFile($file, $courseId) {
        $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $courseId . '/';
        $filename = basename($file['name']);
        move_uploaded_file($file['tmp_name'], $upload_directory . $filename);
        return $filename;
    }

    // Helper method to ensure the upload directory exists
    private function ensureDirectoryExists($courseId) {
        $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $courseId . '/';
        if (!file_exists($upload_directory)) {
            mkdir($upload_directory, 0777, true); // Create the directory if it doesn't exist
        }
    }
}

// Create an instance of the class and handle the request
$courseManager = new CourseManager();
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

switch ($method) {
    case "GET":
        $courseId = isset($path[3]) && is_numeric($path[3]) ? $path[3] : null;
        $courseManager->getCourses($courseId);
        break;

    case "POST":
        $courseManager->createCourse($_POST, $_FILES);
        break;

    case "PUT":
        // Decode PUT data (since PHP doesn't parse PUT automatically)
        parse_str(file_get_contents("php://input"), $putData);
        $courseManager->updateCourse($putData, $_FILES);
        break;

    default:
        echo json_encode(['status' => 0, 'message' => 'Method not allowed']);
        break;
}
