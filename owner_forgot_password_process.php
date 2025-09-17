<?php
// Use Composer's autoloader for PHPMailer
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start(); // Start session

// Database connection
include 'db.php'; // Ensure this file sets up $conn properly

header('Content-Type: application/json'); // Set the content type to JSON

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';

    // Validate email input
    if (empty($email)) {
        echo json_encode(['status' => 'error', 'message' => 'Email is required.']);
        exit;
    }

    // Check if the email exists in the admins table
    $stmt = $conn->prepare("SELECT * FROM admins WHERE email = ?");
    if (!$stmt) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
        exit;
    }
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50)); // Generate a 100-character token
        $expires = date('Y-m-d H:i:s', strtotime('+24 hours')); // Token expires in 24 hours

        // Insert the token into the admin_password_resets table
        $stmt = $conn->prepare("INSERT INTO admin_password_resets (email, token, expires) VALUES (?, ?, ?)");
        if (!$stmt) {
            echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
            exit;
        }
        $stmt->bind_param("sss", $email, $token, $expires);
        if ($stmt->execute()) {
            error_log("Stored token for admin: $token, Email: $email, Expires: $expires"); // Debugging
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to store reset token.']);
            exit;
        }

        // Send the email using PHPMailer
        $resetLink = "http://localhost:8000/owner_reset_password.php?token=" . urlencode($token);
        $subject = "Password Reset Request";
        $message = "Dear Admin,\n\nClick the following link to reset your password:\n\n" . $resetLink . "\n\nIf you did not request this, please ignore this email.\n\nRegards,\nHavenist";

        $mail = new PHPMailer(true); // Enable exceptions for error handling
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'jagadeswararaovana@gmail.com'; // Your Gmail address
            $mail->Password = 'jwjwjqhzrukymdkc'; // Use an App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('jagadeswararaovana@gmail.com', 'FileSharePro'); // Your name and emai
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