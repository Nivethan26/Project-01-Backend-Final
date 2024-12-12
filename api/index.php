<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods:*");
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

include '../DatabaseConnection.php';

class Course
{
    private $conn;

    public function __construct()
    {
        $objDb = new DatabaseConnection();
        $this->conn = $objDb->getConnection();
    }

    public function processRequest($method, $path)
    {
        switch ($method) {
            case "GET":
                $this->handleGet($path);
                break;
            case "POST":
                $this->handlePost();
                break;
            case "DELETE":
                $this->handleDelete($path);
                break;
            default:
                echo json_encode(['status' => 0, 'message' => 'Method not allowed']);
                break;
        }
    }

    private function handleGet($path)
    {
        if (isset($path[3]) && is_numeric($path[3])) {
            $sql = "SELECT * FROM courses WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $path[3]);
            $stmt->execute();
            $course = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode($course ?: ['message' => 'Course not found']);
        } else {
            $sql = "SELECT * FROM courses";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
    }

    private function handlePost()
    {
        $image1 = null;
        $image2 = null;
        $this->ensureDirectoryExists($_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $_POST['courseId'] . '/');

        if (isset($_FILES['image1']) && $_FILES['image1']['error'] == UPLOAD_ERR_OK) {
            $image1 = basename($_FILES['image1']['name']);
            move_uploaded_file($_FILES['image1']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $_POST['courseId'] . '/' . $image1);
        }

        if (isset($_FILES['image2']) && $_FILES['image2']['error'] == UPLOAD_ERR_OK) {
            $image2 = basename($_FILES['image2']['name']);
            move_uploaded_file($_FILES['image2']['tmp_name'], $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $_POST['courseId'] . '/' . $image2);
        }

        $sql = "INSERT INTO courses (courseId, courseName, courseDuration, courseFee, content1, content2, image1, image2, created_at) 
                VALUES (:courseId, :courseName, :courseDuration, :courseFee, :content1, :content2, :image1, :image2, :created_at)";
        $stmt = $this->conn->prepare($sql);

        $created_at = date('Y-m-d H:i:s');
        $stmt->bindParam(':courseId', $_POST['courseId']);
        $stmt->bindParam(':courseName', $_POST['courseName']);
        $stmt->bindParam(':courseDuration', $_POST['courseDuration']);
        $stmt->bindParam(':courseFee', $_POST['courseFee']);
        $stmt->bindParam(':content1', $_POST['content1']);
        $stmt->bindParam(':content2', $_POST['content2']);
        $stmt->bindParam(':image1', $image1);
        $stmt->bindParam(':image2', $image2);
        $stmt->bindParam(':created_at', $created_at);

        echo json_encode($stmt->execute() ? ['status' => 1, 'message' => 'Course created successfully.'] : ['status' => 0, 'message' => 'Failed to create course.']);
    }

    private function handleDelete($path)
    {
        if (isset($path[3]) && is_numeric($path[3])) {
            $sql = "DELETE FROM courses WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $path[3]);

            echo json_encode($stmt->execute() ? ['status' => 1, 'message' => 'Course deleted successfully.'] : ['status' => 0, 'message' => 'Failed to delete course.']);
        } else {
            echo json_encode(['status' => 0, 'message' => 'Invalid course ID.']);
        }
    }

    private function ensureDirectoryExists($directory)
    {
        if (!file_exists($directory)) {
            mkdir($directory, 0777, true);
        }
    }
}

$controller = new Course();
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));
$controller->processRequest($method, $path);
