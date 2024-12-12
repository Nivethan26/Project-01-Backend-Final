<?php
class Message
{
    private $connection;

    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    public function insertMessage($firstName, $phone, $email, $message)
    {
        $sql = 'INSERT INTO messages (first_name, phone, email, message) VALUES (:firstName, :phone, :email, :message)';

        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->bindParam(':firstName', $firstName);
            $stmt->bindParam(':phone', $phone);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':message', $message);

            return $stmt->execute(); // Returns true if successful, false otherwise
        } catch (PDOException $e) {
            error_log('Error inserting message: ' . $e->getMessage());
            return false;
        }
    }
}
