<?php
header("Access-Control-Allow-Origin: *"); // Allows any origin
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json'); // Set content type to JSON

// Include the database connector
include '../DatabaseConnection.php';

// Create an instance of DbConnector and get the PDO connection
$dbcon = new DatabaseConnection();
$con = $dbcon->getConnection();
    
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];

    try {
        $sql = "DELETE FROM services WHERE id = :id";
        $stmt = $con->prepare($sql);
        $stmt->execute([':id' => $delete_id]);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["success" => "service deleted successfully."]);
        } else {
            echo json_encode(["error" => "service not deleted."]);
        }
    } catch (PDOException $e) {
        echo json_encode(["error" => $e->getMessage()]);
    }
}
?>


?>
