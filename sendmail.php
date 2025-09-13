<?php

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$email = "";
$name = "";
$errors = array();

function generateOTP($length = 6) {
    // More secure OTP generation
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= random_int(0, 9);
    }
    return $otp;
}

// Function to send OTP email
function sendOTPEmail($email, $otp, $name) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        // Consider moving these to environment variables or config file
        $mail->Username   = 'agbojames00@gmail.com';
        $mail->Password   = 'iounbaayyatmfilf'; // Consider using environment variable
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // More explicit
        $mail->Port       = 465;

        // Recipients
        $mail->setFrom('agbojames00@gmail.com', "Zaf's Kitchen");
        $mail->addAddress($email, htmlspecialchars($name, ENT_QUOTES, 'UTF-8')); // Sanitize name
        $mail->addReplyTo('agbojames00@gmail.com', "Zaf's Kitchen Support");

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Email Verification - Zaf\'s Kitchen';
        
        // Sanitize variables for HTML output
        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safe_otp = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Email Verification</title>
        </head>
        <body style='font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #E75925; margin: 0;'>Zaf's Kitchen</h1>
                </div>
                
                <h2 style='color: #E75925; margin-bottom: 20px;'>Welcome to Zaf's Kitchen!</h2>
                <p style='margin-bottom: 15px;'>Hello <strong>$safe_name</strong>,</p>
                <p style='margin-bottom: 20px;'>Thank you for signing up! Please use the following verification code to complete your registration:</p>
                
                <div style='background: linear-gradient(135deg, #f8f9fa, #e9ecef); padding: 30px; text-align: center; margin: 30px 0; border-radius: 12px; border-left: 5px solid #E75925;'>
                    <p style='margin: 0 0 10px 0; font-size: 14px; color: #666;'>Your Verification Code:</p>
                    <h1 style='color: #E75925; font-size: 36px; letter-spacing: 8px; margin: 0; font-weight: bold; text-shadow: 1px 1px 2px rgba(0,0,0,0.1);'>$safe_otp</h1>
                </div>
                
                <div style='background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 8px; padding: 15px; margin: 20px 0;'>
                    <p style='margin: 0; color: #856404;'><strong>Important:</strong> This code will expire in <strong>10 minutes</strong> for security purposes.</p>
                </div>
                
                <p style='margin-bottom: 20px;'>If you didn't create an account with us, please ignore this email.</p>
                
                <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #666; text-align: center; margin: 0;'>
                        This is an automated message from Zaf's Kitchen.<br>
                        Please do not reply to this email.<br>
                        <br>
                        © " . date('Y') . " Zaf's Kitchen. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Alternative plain text version for email clients that don't support HTML
        $mail->AltBody = "
        Welcome to Zaf's Kitchen!
        
        Hello $safe_name,
        
        Thank you for signing up! Please use the following verification code to complete your registration:
        
        Verification Code: $safe_otp
        
        This code will expire in 10 minutes for security purposes.
        
        If you didn't create an account with us, please ignore this email.
        
        This is an automated message from Zaf's Kitchen.
        ";

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        // Log the error for debugging (don't expose to user)
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}

// Function to send password reset email (bonus function)
function sendPasswordResetEmail($email, $reset_link, $name) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'agbojames00@gmail.com';
        $mail->Password   = 'iounbaayyatmfilf';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;

        $mail->setFrom('agbojames00@gmail.com', "Zaf's Kitchen");
        $mail->addAddress($email, htmlspecialchars($name, ENT_QUOTES, 'UTF-8'));
        $mail->addReplyTo('agbojames00@gmail.com', "Zaf's Kitchen Support");

        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - Zaf\'s Kitchen';
        
        $safe_name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safe_link = htmlspecialchars($reset_link, ENT_QUOTES, 'UTF-8');
        
        $mail->Body = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Password Reset</title>
        </head>
        <body style='font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f4f4;'>
            <div style='max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff;'>
                <div style='text-align: center; margin-bottom: 30px;'>
                    <h1 style='color: #E75925; margin: 0;'>Zaf's Kitchen</h1>
                </div>
                
                <h2 style='color: #E75925; margin-bottom: 20px;'>Password Reset Request</h2>
                <p style='margin-bottom: 15px;'>Hello <strong>$safe_name</strong>,</p>
                <p style='margin-bottom: 20px;'>We received a request to reset your password. Click the button below to create a new password:</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='$safe_link' style='background-color: #E75925; color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; display: inline-block; font-weight: bold;'>Reset Password</a>
                </div>
                
                <p style='margin-bottom: 20px;'>If the button doesn't work, copy and paste this link into your browser:</p>
                <p style='word-break: break-all; color: #666; font-size: 14px; margin-bottom: 20px;'>$safe_link</p>
                
                <div style='background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 8px; padding: 15px; margin: 20px 0;'>
                    <p style='margin: 0; color: #721c24;'><strong>Security Notice:</strong> If you didn't request this password reset, please ignore this email. Your password will remain unchanged.</p>
                </div>
                
                <div style='margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;'>
                    <p style='font-size: 12px; color: #666; text-align: center; margin: 0;'>
                        This is an automated message from Zaf's Kitchen.<br>
                        Please do not reply to this email.<br>
                        <br>
                        © " . date('Y') . " Zaf's Kitchen. All rights reserved.
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";

        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}
?>