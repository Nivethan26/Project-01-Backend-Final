<?php
require_once 'DatabaseConnection.php';

class Booking
{
    private $connection;

    public function __construct()
    {
        $dbConnection = new DatabaseConnection();
        $this->connection = $dbConnection->getConnection();
    }

    public function getBookings4()
    {
        $query = "SELECT * FROM bookings";

        try {
            $stmt = $this->connection->prepare($query);
            $stmt->execute();

            // Fetch all results as an associative array
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $bookings;
        } catch (PDOException $e) {
            error_log('Error fetching bookings: ' . $e->getMessage());
            return [];
        }
    }
}

header('Content-Type: application/json');

$booking = new Booking();
echo json_encode($booking->getBookings4());
