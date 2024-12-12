<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Import PHPMailer classes
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

class Mailer
{
    private $mail;

    public function __construct()
    {
        $this->mail = new PHPMailer(true);

        // Server settings
        $this->mail->isSMTP();
        $this->mail->Host       = 'smtp.gmail.com';
        $this->mail->SMTPAuth   = true;
        $this->mail->Username   = 'laaviananth11@gmail.com'; // Your SMTP email
        $this->mail->Password   = 'almmeafiiuxiswut'; // Your SMTP password or App Password
        $this->mail->SMTPSecure = 'tls'; // Use 'tls' for port 587
        $this->mail->Port       = 587; // Change to 465 for ssl

        // Optional: Enable SMTP debugging (set to 0 in production)
        $this->mail->SMTPDebug = 2; // Debug level: 0 = off, 1 = client messages, 2 = client and server messages

        // Sender email
        $this->mail->setFrom('lavivetha@gmail.com', 'AutocareLanka');
    }

    // Set email subject and message
    public function setInfo($recipientEmail, $subject, $message)
    {
        $this->mail->addAddress($recipientEmail);
        $this->mail->isHTML(true);
        $this->mail->Subject = $subject;
        $this->mail->Body    = $message;
    }

    // Send the email
    public function send() {
        try {
            $this->mail->send();
            $this->mail->clearAddresses(); // Clear addresses after sending
            return true; // Email sent successfully
        } catch (Exception $e) {
            error_log("Mail not sent. Error: " . $this->mail->ErrorInfo); // Log the error message
            return false; // Return false on failure
        }
    }
}