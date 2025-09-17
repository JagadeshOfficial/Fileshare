<?php
session_start();
include("db.php");

require 'vendor/autoload.php'; // Composer autoloader
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name     = $_POST["name"];
    $email    = $_POST["email"];
    $mobile   = $_POST["mobile"];
    $aadhaar  = $_POST["aadhaar"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $otp      = rand(100000, 999999);

    // Store data in session temporarily until OTP is verified
    $_SESSION['registration_data'] = [
        'name' => $name,
        'email' => $email,
        'mobile' => $mobile,
        'aadhaar' => $aadhaar,
        'password' => $password,
        'otp' => $otp
    ];

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'jagadeswararaovana@gmail.com'; // your Gmail
        $mail->Password   = 'jwjwjqhzrukymdkc'; // App Password (NOT your Gmail password!)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // use SSL
        $mail->Port       = 465;

        // For debugging (comment in production)
        // $mail->SMTPDebug = 2;
        // $mail->Debugoutput = 'html';

        // Recipients
        $mail->setFrom('jagadeswararaovana@gmail.com', 'Registration System');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(false);
        $mail->Subject = "Your OTP Code";
        $mail->Body    = "Dear $name,\n\nYour OTP code is: $otp\n\nThank you!";

        $mail->send();
        echo "success";
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
?>
