<?php
// Use Composer's autoloader for PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start(); // Start session

// Database connection
include 'db.php'; // Ensure this file sets up $conn properly

// Check if database connection is successful
if (!$conn) {
    echo json_encode(['status' => 'error', 'message' => 'Database connection failed.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $token = bin2hex(random_bytes(50)); // Generate a 100-character token
    $expires = date('Y-m-d H:i:s', strtotime('+24 hours')); // Token expires in 24 hours

    // Check if the email exists in the users table
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Insert the token into the password_resets table
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("sss", $email, $token, $expires);
        if ($stmt->execute()) {
            error_log("Stored token: $token, Email: $email, Expires: $expires"); // Debugging
        } else {
            error_log("Failed to store token: " . $stmt->error); // Debugging
            echo json_encode(['status' => 'error', 'message' => 'Failed to store reset token.']);
            exit;
        }

        // Send the email using PHPMailer
        $resetLink = "http://localhost:8000/user_reset_password.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: " . $resetLink;

        $mail = new PHPMailer(true); // Enable exceptions
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jagadeswararaovana@gmail.com'; // Your Gmail address
            $mail->Password = 'rrtxtetsmnkzkffs'; // Use an App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom('jagadeswararaovana@gmail.com', 'FilesharePro');
            $mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $message;

            $mail->send();
            echo json_encode(['status' => 'success', 'message' => 'Reset link sent to your email.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to send email: ' . $mail->ErrorInfo]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Email not found.']);
    }

    $stmt->close();
}

$conn->close();
?>