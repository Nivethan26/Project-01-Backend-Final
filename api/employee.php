<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json'); // Only set once

include './userEmployee.php';

// Create a new userEmployee instance
$user = new userEmployee();

// Fetch leave names
$leaveNames = $user->getLeaveNames();

// Check if leave names were fetched successfully and ensure it's an array
$leaveNames = is_array($leaveNames) ? $leaveNames : [];

// Log the fetched leave names for debugging
error_log(print_r($leaveNames, true)); // This will log the leaveNames array to your server log

// Return the leave names to the React app as JSON
echo json_encode([
    'leaveTypes' => $leaveNames,
]);
?>