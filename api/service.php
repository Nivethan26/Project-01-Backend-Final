<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header('Content-Type: application/json');

include '../DatabaseConnection.php';

class ServiceManager {
    private $conn;

    public function __construct() {
        $objDb = new DatabaseConnection();
        $this->conn = $objDb->getConnection();
    }

    public function getServices($id = null) {
        if ($id) {
            // Fetch individual service by ID
            $sql = "SELECT * FROM services WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $service = $stmt->fetch(PDO::FETCH_ASSOC);

            return $service ? $service : ['message' => 'Service not found'];
        } else {
            // Fetch all services
            $sql = "SELECT * FROM services";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    public function createService($data, $files) {
        // Ensure fields are present
        if (isset($data['serviceId'], $data['serviceName'], $data['content1'])) {
            $serviceId = htmlspecialchars($data['serviceId']);
            $serviceName = htmlspecialchars($data['serviceName']);
            $content1 = htmlspecialchars($data['content1']);
            $created_at = date('Y-m-d H:i:s');
            $image1 = null;

            $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $serviceId . '/';
            $this->ensureDirectoryExists($upload_directory);

            if (isset($files['image1']) && $files['image1']['error'] == UPLOAD_ERR_OK) {
                $image1 = basename($files['image1']['name']);
                move_uploaded_file($files['image1']['tmp_name'], $upload_directory . $image1);
            }

            // Prepare SQL for service insertion
            $sql = "INSERT INTO services (serviceId, serviceName, content1, image1, created_at) 
                    VALUES (:serviceId, :serviceName, :content1, :image1, :created_at)";
            $stmt = $this->conn->prepare($sql);

            $stmt->bindParam(':serviceId', $serviceId);
            $stmt->bindParam(':serviceName', $serviceName);
            $stmt->bindParam(':content1', $content1);
            $stmt->bindParam(':image1', $image1);
            $stmt->bindParam(':created_at', $created_at);

            return $stmt->execute()
                ? ['status' => 1, 'message' => 'Service created successfully.']
                : ['status' => 0, 'message' => 'Failed to create service.'];
        } else {
            return ['status' => 0, 'message' => 'Invalid input'];
        }
    }

    public function updateService($data, $files) {
        // Extract data from the input
        $id = $data['id'] ?? null;
        $serviceId = $data['serviceId'] ?? null;
        $serviceName = $data['serviceName'] ?? null;
        $content1 = $data['content1'] ?? null;
        $oldImage1 = $data['oldImage1'] ?? null;

        // Validate required fields
        if (!$id || !$serviceId || !$serviceName || !$content1) {
            return $this->respond(['status' => 0, 'message' => 'Missing required fields']);
        }

        // Directory for images
        $upload_directory = $_SERVER['DOCUMENT_ROOT'] . '/Backend/images/' . $serviceId . '/';
        $this->ensureDirectoryExists($upload_directory);

        // Handle image upload
        $image1 = $oldImage1; // Default to old image
        if (isset($files['image1']) && $files['image1']['error'] === UPLOAD_ERR_OK) {
            $image1 = basename($files['image1']['name']);
            if (move_uploaded_file($files['image1']['tmp_name'], $upload_directory . $image1)) {
                // Delete old image if a new one is uploaded
                if ($oldImage1) {
                    $oldImagePath1 = $upload_directory . $oldImage1;
                    if (file_exists($oldImagePath1)) {
                        unlink($oldImagePath1);  // Delete old image
                    }
                }
            } else {
                return $this->respond(['status' => 0, 'message' => 'Failed to upload new image']);
            }
        }

        // Prepare the update query
        $sql = "UPDATE services 
                SET serviceId = :serviceId, serviceName = :serviceName, content1 = :content1, image1 = :image1 
                WHERE id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':serviceId', $serviceId);
        $stmt->bindParam(':serviceName', $serviceName);
        $stmt->bindParam(':content1', $content1);
        $stmt->bindParam(':image1', $image1);
        $stmt->bindParam(':id', $id);

        if ($stmt->execute()) {
            return $this->respond(['status' => 1, 'message' => 'Service updated successfully']);
        } else {
            return $this->respond(['status' => 0, 'message' => 'Failed to update service']);
        }
    }

    public function deleteService($id) {
        if (is_numeric($id)) {
            $sql = "DELETE FROM services WHERE id = :id";
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
$serviceManager = new ServiceManager();
$method = $_SERVER['REQUEST_METHOD'];
$path = explode('/', trim($_SERVER['REQUEST_URI'], '/'));

switch ($method) {
    case "GET":
        $id = isset($path[3]) ? $path[3] : null;
        $response = $serviceManager->getServices($id);
        echo json_encode($response);
        break;

    case "POST":
        if (isset($_POST['id'])) {
            // Update service if 'id' is provided
            $response = $serviceManager->updateService($_POST, $_FILES);
        } else {
            // Create service if 'id' is not provided
            $response = $serviceManager->createService($_POST, $_FILES);
        }
        echo json_encode($response);
        break;

    case "DELETE":
        $id = isset($path[3]) ? $path[3] : null;
        $response = $serviceManager->deleteService($id);
        echo json_encode($response);
        break;

    default:
        echo json_encode(['status' => 0, 'message' => 'Method not allowed']);
        break;
}
?>
