<?php

class User
{
    private $conn;
    private $table_name = "users";

    public $username;
    public $email;
    public $password;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function register()
    {
        $query = "INSERT INTO users (username, email, password) VALUES (:username, :email, :password)";

        $stmt = $this->conn->prepare($query);
        if ($stmt === false) {
            return ['error' => 'Database error: failed to prepare statement'];
        }

         $this->password = password_hash($this->password, PASSWORD_BCRYPT);

        // Bind parameters
        $stmt->bindParam(':username', $this->username);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);

        // Execute the query
        if ($stmt->execute()) {
            return ['success' => 'User registered successfully.'];
        } else {
            return ['error' => 'Registration failed. Please try again.'];
        }
    }

    public function login($rememberMe = false) {
        // Prepare the SQL query
        $query = "SELECT * FROM users WHERE username = :username";
        $stmt = $this->conn->prepare($query);
    
        if ($stmt === false) {
            return ['success' => 0, 'error' => 'Database error: failed to prepare statement'];
        }
    
        // Bind the username parameter
        $stmt->bindParam(':username', $this->username);
    
        // Execute the query
        if (!$stmt->execute()) {
            return ['success' => 0, 'error' => 'Database error: failed to execute query'];
        }
    
        // Check if the user exists
        if ($stmt->rowCount() === 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
            if (!$row) {
                return ['success' => 0, 'error' => 'Database error: failed to fetch user data'];
            }
            
            // Verify password
            if (password_verify($this->password , $row['password'])) {
                // Successful login
                return ['success' => 1, 'userrole' => $row['userole'], 'message' => 'User logged in successfully.'];
            } else {
                // Invalid password
                return ['success' => 0, 'error' => 'Invalid password.'];
            }
        } else {
            // User not found
            return ['success' => 0, 'error' => 'User not found.'];
        }
    }
    
    


    public function userExists($username, $email) {
        $query = "SELECT id FROM users WHERE username = :username OR email = :email LIMIT 1";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        
        $stmt->execute();
        
        return $stmt->rowCount() > 0; // Return true if user exists
    }
    
}