<?php
require_once 'DatabaseConnection.php';

class Messages
{
    private $connection;

    public function __construct()
    {
        $dbConnection = new DatabaseConnection();
        $this->connection = $dbConnection->getConnection();
    }

    public function getMessages()
    {
        $query = "SELECT * FROM messages";

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute();

            // Fetch all results as an associative array
            $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $messages;
        } catch (PDOException $e) {
            error_log('Error fetching messages: ' . $e->getMessage());
            return [];
        }
    }
}

header('Content-Type: application/json');

$messages = new Messages();
echo json_encode($messages->getMessages());
