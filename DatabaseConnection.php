<?php

class DatabaseConnection
{
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $database = 'autocare_lanka';
    protected $connection;

    public function __construct()
    {
        $this->connect();
    }

    private function connect()
    {
        $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8";

        try {
            $this->connection = new PDO($dsn, $this->user, $this->password);
            // Set PDO error mode to exception for better error handling
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            error_log('Database connection successful');
        } catch (PDOException $e) {
            die('Connection failed: ' . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->connection;
    }
}