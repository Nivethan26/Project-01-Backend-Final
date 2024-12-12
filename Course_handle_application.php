<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'CourseApplicationHandler.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone_no = $_POST['phone'];

    error_log("Received data: course_id=$course_id, course_name=$course_name, name=$name, email=$email, phone=$phone_no");

    $appHandler = new CourseApplicationHandler();
    $response = $appHandler->submitApplication($course_id, $course_name, $name, $email, $phone_no);

    error_log("Response: " . $response);

    echo $response;
} else {
    error_log("Invalid request method");
    echo json_encode(["message" => "Invalid request method"]);
}
