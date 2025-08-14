<?php


// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$email = "";
$name = "";
$errors = array();

function generateOTP($length = 6) {
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= rand(0, 9);
    }
    return $otp;
}

// Function to send OTP email
function sendOTPEmail($email, $otp, $name) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'agbojames00@gmail.com';
        $mail->Password   = 'iounbaayyatmfilf';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;

        $mail->setFrom('agbojames00@gmail.com', "Zaf's Kitchen");
        $mail->addAddress($email, $name);

        $mail->isHTML(true);
        $mail->Subject = 'Email Verification - Zaf\'s Kitchen';
        $mail->Body    = "
        <html>
        <body style='font-family: Arial, sans-serif; color: #333;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                <h2 style='color: #E75925;'>Welcome to Zaf's Kitchen!</h2>
                <p>Hello $name,</p>
                <p>Thank you for signing up! Please use the following verification code to complete your registration:</p>
                <div style='background: #f8f9fa; padding: 20px; text-align: center; margin: 20px 0; border-radius: 8px;'>
                    <h1 style='color: #E75925; font-size: 32px; letter-spacing: 5px; margin: 0;'>$otp</h1>
                </div>
                <p>This code will expire in 10 minutes for security purposes.</p>
                <p>If you didn't create an account with us, please ignore this email.</p>
                <hr>
                <p style='font-size: 12px; color: #666;'>This is an automated message from Zaf's Kitchen. Please do not reply to this email.</p>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
?>