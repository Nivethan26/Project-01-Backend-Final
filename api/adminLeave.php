<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header('Content-Type: application/json');
include '../DatabaseConnection.php'; // Make sure this file connects to your database

// Fetch leave applications
$sql = "SELECT * FROM leave_applications";
$stmt = $pdo->query($sql);
$applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($applications);
?>