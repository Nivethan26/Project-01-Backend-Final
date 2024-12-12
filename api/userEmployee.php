<?php
include '../DatabaseConnection.php';

class userEmployee {
    private $id;
    private $username;
    private $password;
    private $name;
    private $email;
    private $address;
    private $phone;
    private $pdo;

    function __construct() {
        $dbcon = new DatabaseConnection();
        $this->pdo = $dbcon->getConnection(); // Initialize PDO instance
    }

    // Getters and Setters
    function getId() { return $this->id; }
    function getUsername() { return $this->username; }
    function getPassword() { return $this->password; }
    function getName() { return $this->name; }
    function getEmail() { return $this->email; }
    function getPhone() { return $this->phone; }
    function getAddress() { return $this->address; }

    function setId($id) { $this->id = $id; }  
    function setUsername($username) { $this->username = $username; }
    function setPassword($password) { $this->password = $password; }
    function setName($name) { $this->name = $name; }
    function setEmail($email) { $this->email = $email; }
    function setPhone($phone) { $this->phone = $phone; }
    function setAddress($address) { $this->address = $address; }
    public function fetchUserById($id) {
        try {
            $stmt = $this->pdo->prepare("SELECT id, username, name, email, password FROM employees WHERE id = :id LIMIT 1");
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            error_log("Error fetching user by ID: " . $exc->getMessage());
            return false;
        }
    }
    // Login method
    public function login() {
        try {
            // Prepare the SQL statement
            $stmt = $this->pdo->prepare("SELECT * FROM employees WHERE id = :id");
            $stmt->bindParam(':id', $this->id);
            $stmt->execute();
    
            // Fetch the result
            $rs = $stmt->fetch(PDO::FETCH_ASSOC);
    
            // Check if a record was found
            if ($rs) {
                error_log("User found: " . print_r($rs, true)); // Debug: Log user data
                
                // Check if the password is stored as plain text or hashed
                if (password_get_info($rs['password'])['algo'] !== 0) {
                    // Password is hashed
                    if (password_verify($this->password, $rs['password'])) {
                        return true; // Successful login with hashed password
                    } else {
                        error_log("Hashed password verification failed."); // Debug: Log failure
                    }
                } else {
                    // Password is plain text (initial login case)
                    if ($this->password === $rs['password']) {
                        // Hash the password for future logins
                        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
                        $update_stmt = $this->pdo->prepare("UPDATE employees SET password = :hashed_password WHERE id = :id");
                        $update_stmt->bindParam(':hashed_password', $hashed_password);
                        $update_stmt->bindParam(':id', $this->id);
                        $update_stmt->execute();
                        return true; // Successful login with plain text password
                    } else {
                        error_log("Plain text password mismatch."); // Debug: Log failure
                    }
                }
            } else {
                error_log("No user found with the provided ID."); // Debug: Log absence of user
            }
    
            return false; // Invalid login if no record or password mismatch
        } catch (PDOException $exc) {
            error_log("Login error: " . $exc->getMessage());
            return false; // Return false on error
        }
    }

    public function createEmployee($employeeId, $username, $password, $fullName, $email, $phone, $address) {
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email format.");
        }
    
        // Hash the password for security
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    
        // Check if the employeeId already exists
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM employees WHERE id = ?");
        $stmt->execute([$employeeId]);
    
        if ($stmt->fetchColumn() > 0) {
            throw new InvalidArgumentException("Employee ID already exists."); // Throw exception if ID is duplicate
        }
    
        // Prepare SQL statement to prevent SQL injection
        $stmt = $this->pdo->prepare("INSERT INTO employees (id, username, password, name, email, phone, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
        // Bind parameters to the statement
        $stmt->bindParam(1, $employeeId);
        $stmt->bindParam(2, $username);
        $stmt->bindParam(3, $hashedPassword);
        $stmt->bindParam(4, $fullName);
        $stmt->bindParam(5, $email);
        $stmt->bindParam(6, $phone);
        $stmt->bindParam(7, $address);
    
        // Execute the statement and check if it was successful
        if ($stmt->execute()) {
            return true; // Return true if employee created successfully
        } else {
            return false; // Return false if there was an error
        }
    }


    
    // Get employee details
     public function getDetails() {
        try {
            $query = "SELECT * FROM employees WHERE id = ?";
            $pstmt = $this->pdo->prepare($query);
            $pstmt->bindValue(1, $this->id);
            $pstmt->execute();
            $rs = $pstmt->fetch(PDO::FETCH_ASSOC);

            if ($rs) {
                $this->name = $rs['username'];
                $this->name = $rs['name'];
                $this->email = $rs['email'];  
                $this->phone = $rs['phone'];
                $this->address = $rs['address'];
                return $rs;
            }
            return false;
        } catch (PDOException $exc) {
            error_log("Error fetching details: " . $exc->getMessage());
            return false;
        }
    }
    

    // Update employee details
    public function updateDetails($username, $name, $email, $address, $phone) {
        try {
            $query = "UPDATE employees SET username = ?, name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $pstmt = $this->pdo->prepare($query);
            $pstmt->bindValue(1, $username);
            $pstmt->bindValue(2, $name);
            $pstmt->bindValue(3, $email);
            $pstmt->bindValue(4, $phone);
            $pstmt->bindValue(5, $address);
            $pstmt->bindValue(6, $this->id); // Make sure to bind the ID

            return $pstmt->execute(); // Return true if update is successful
        } catch (PDOException $exc) {
            error_log("Error updating details: " . $exc->getMessage());
            return false;
        }
    }

    // Delete employee details
    public function deleteDetails() {
        try {
            $query = "DELETE FROM employees WHERE id = ?";
            $pstmt = $this->pdo->prepare($query);
            $pstmt->bindValue(1, $this->id); // Bind the ID
            return $pstmt->execute(); // Return true if deletion is successful
        } catch (PDOException $exc) {
            error_log("Error deleting details: " . $exc->getMessage());
            return false;
        }
    }

    // Get the count of leave types
    public function getLeaveTypeCount() {
        try {
            $query = "SELECT COUNT(*) AS leave_type_count FROM leave_types";
            $stmt = $this->pdo->query($query);
            return $stmt->fetch(PDO::FETCH_ASSOC)['leave_type_count'];
        } catch (PDOException $exc) {
            error_log("Error fetching leave type count: " . $exc->getMessage());
            return 0; // Return 0 if there's an error
        }
    }

    // Get all leave types
    public function getAllLeaveTypes() {
        try {
            $query = "SELECT * FROM leave_types";
            $result = $this->pdo->query($query);
            return $result->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $exc) {
            error_log("Error fetching leave types: " . $exc->getMessage());
            return []; // Return empty array if there's an error
        }
    }
    public function getLeaveNames() {
        try {
            $query = "SELECT leave_type FROM leave_types";
            $result = $this->pdo->query($query);

            // Log the result for debugging purposes
            $leaveNames = $result->fetchAll(PDO::FETCH_ASSOC);
            error_log(print_r($leaveNames, true)); // Log the fetched data

            return $leaveNames;
        } catch (PDOException $exc) {
            error_log("Error fetching leave names: " . $exc->getMessage());
            return []; // Return empty array if there's an error
        }
    }
    
    

    

    // Delete a leave type
    public function deleteLeaveType($id) {
        try {
            $deleteQuery = "DELETE FROM leave_types WHERE id = :id";
            $stmt = $this->pdo->prepare($deleteQuery);
            $stmt->bindValue(':id', $id);
            return $stmt->execute(); // Return true if deletion is successful
        } catch (PDOException $exc) {
            error_log("Error deleting leave type: " . $exc->getMessage());
            return false; // Return false if there's an error
        }
    }

    public function getEmployeeCount() {
        try {
            $query = "SELECT COUNT(*) FROM employees"; // Adjust the table name if necessary
            $stmt = $this->pdo->query($query);
            return $stmt->fetchColumn();
        } catch (PDOException $exc) {
            error_log("Error fetching employee count: " . $exc->getMessage());
            return 0; // Return 0 if there's an error
        }
    }
}
?>