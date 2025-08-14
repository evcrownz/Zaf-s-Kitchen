<?php 
// This fixes the "OTP has expired" issue due to mismatched timezones
date_default_timezone_set('Asia/Manila');

session_start();
require "connection.php";
require_once "sendmail.php";


// Function to generate OTP
if(isset($_POST['check'])) {
    // Get email from session or hidden input
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : (isset($_POST['email']) ? $_POST['email'] : '');
    $email = mysqli_real_escape_string($con, $email);

if(empty($email)) {
    $errors['otp-error'] = 'Session expired or email missing. Please sign up again.';
} else {
    // Combine OTP inputs safely with trim
    $entered_otp = '';
    for ($i = 1; $i <= 6; $i++) {
        $entered_otp .= isset($_POST["otp$i"]) ? trim($_POST["otp$i"]) : '';
    }

    // Validate OTP and expiry
    $check_otp = "SELECT * FROM usertable WHERE email = '$email' AND code = '$entered_otp' AND otp_expiry > NOW()";
    $otp_result = mysqli_query($con, $check_otp);

    if(mysqli_num_rows($otp_result) > 0){
        // OTP is correct and valid
        $update_status = "UPDATE usertable SET status = 'verified', code = NULL, otp_expiry = NULL WHERE email = '$email'";
        $update_result = mysqli_query($con, $update_status);

        if($update_result){
            $user_data = mysqli_fetch_assoc($otp_result);
            $_SESSION['name'] = $user_data['name'];
            $_SESSION['email'] = $email;
            unset($_SESSION['show_otp_modal']);

            // Added success message to session
            $_SESSION['verification_success'] = 'Email verified successfully! You can now sign in.';

            // Redirect to the same page to display the success message
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $errors['otp-error'] = 'Failed to update account status. Please try again.';
            $_SESSION['show_otp_modal'] = true;
        }
    } else {
        // OTP not valid or expired
        $check_expired = "SELECT * FROM usertable WHERE email = '$email' AND code = '$entered_otp'";
        $expired_result = mysqli_query($con, $check_expired);

        if(mysqli_num_rows($expired_result) > 0){
            $errors['otp-error'] = 'OTP has expired. Please resend a new one.';
        } else {
            $errors['otp-error'] = 'Invalid OTP. Please check and try again.';
        }

        $_SESSION['show_otp_modal'] = true;
    }
    }
}



// if user signup button
if(isset($_POST['signup'])){
    $name = mysqli_real_escape_string($con, $_POST['name']);
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);

    // Initialize error array if not already initialized
    $errors = [];

    // Check if passwords match
    if($password !== $cpassword){
        $errors['password'] = "Confirm password not matched!";
    }

    // Check for password length
    if(strlen($password) < 8){
        $errors['password_length'] = "Password must be at least 8 characters long!";
    }

    // Check if email already exists
    $email_check = "SELECT * FROM usertable WHERE email = '$email'";
    $res = mysqli_query($con, $email_check);
    if(mysqli_num_rows($res) > 0){
        $errors['email'] = "Email that you have entered already exists!";
    }

    // If no errors, proceed
    if(count($errors) === 0){
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $otp = generateOTP();
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $status = "unverified";

        // Insert user data
        $insert_data = "INSERT INTO usertable (name, email, password, status, code, otp_expiry)
                        VALUES ('$name', '$email', '$encpass', '$status', '$otp', '$otp_expiry')";
        $data_check = mysqli_query($con, $insert_data);

        if($data_check){
            // Send OTP email
            if(sendOTPEmail($email, $otp, $name)) {
                $_SESSION['email'] = $email;
                $_SESSION['name'] = $name;
                $_SESSION['show_otp_modal'] = true;
                $_SESSION['info'] = "OTP has been sent to your email address.";
            } else {
                $errors['email'] = "Failed to send OTP email. Please try again.";
            }
        } else {
            $errors['db-error'] = "Failed while inserting data into database!";
        }
    }
}


// Handle OTP resend - FIXED: Proper handling
if(isset($_POST['resend-otp']) || (isset($_POST['action']) && $_POST['action'] == 'resend-otp')){
    if(!isset($_SESSION['email'])){
        if(isset($_POST['action'])){
            // AJAX request
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Session expired. Please signup again.']);
            exit();
        } else {
            $errors['otp-error'] = 'Session expired. Please signup again.';
        }
    } else {
        $email = $_SESSION['email'];
        $name = $_SESSION['name'];
        
        $new_otp = generateOTP();
        $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        
        $update_otp = "UPDATE usertable SET code = '$new_otp', otp_expiry = '$otp_expiry' WHERE email = '$email'";
        $update_result = mysqli_query($con, $update_otp);
        
        if($update_result && sendOTPEmail($email, $new_otp, $name)){
            if(isset($_POST['action'])){
                // AJAX request
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'New OTP sent successfully']);
                exit();
            } else {
                $_SESSION['info'] = 'New OTP sent successfully';
                $_SESSION['show_otp_modal'] = true;
            }
        } else {
            if(isset($_POST['action'])){
                // AJAX request
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Failed to send OTP']);
                exit();
            } else {
                $errors['otp-error'] = 'Failed to send OTP';
            }
        }
    }
}

// if user click signin button
if(isset($_POST['signin'])){
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $password = mysqli_real_escape_string($con, $_POST['password']);
    
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
        $check_email = "SELECT * FROM usertable WHERE email = '$email'";
        $res = mysqli_query($con, $check_email);
        
        if(mysqli_num_rows($res) > 0){
            $fetch = mysqli_fetch_assoc($res);
            $fetch_pass = $fetch['password'];
            
            if($fetch['status'] == 'unverified'){
                // Resend OTP for unverified accounts
                $new_otp = generateOTP();
                $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                
                $update_otp = "UPDATE usertable SET code = '$new_otp', otp_expiry = '$otp_expiry' WHERE email = '$email'";
                $update_result = mysqli_query($con, $update_otp);
                
                if($update_result && sendOTPEmail($email, $new_otp, $fetch['name'])){
                    $_SESSION['email'] = $email;
                    $_SESSION['name'] = $fetch['name'];
                    $_SESSION['show_otp_modal'] = true;
                    $_SESSION['info'] = "Please verify your email first. New OTP has been sent to your email.";
                } else {
                    $errors['email'] = "Please verify your email first. Failed to send OTP.";
                }
            } else if($fetch['status'] == 'verified' && password_verify($password, $fetch_pass)){
                // Set all required session variables
                $_SESSION['user_id'] = $fetch['id'];
                $_SESSION['username'] = $fetch['name'];
                $_SESSION['name'] = $fetch['name'];
                $_SESSION['email'] = $email;
                
                // Redirect to dashboard (change 'home.php' to your dashboard filename)
                header('location: dashboard.php');
                exit();
            } else {
                $errors['email'] = "Login failed. Make sure your email and password are correct.";
            }
        } else {
            $errors['email'] = "It looks like you're not yet a member! Click on the bottom link to signup.";
        }
    } else {
        $errors['email'] = "Enter a valid email address!";
    }
}

// if user click continue button in forgot password form
if(isset($_POST['check-email'])){
    $email = mysqli_real_escape_string($con, $_POST['email']);
    $check_email = "SELECT * FROM usertable WHERE email='$email'";
    $run_sql = mysqli_query($con, $check_email);
    
    if(mysqli_num_rows($run_sql) > 0){
        $_SESSION['email'] = $email;
        $info = "Please create a new password that you don't use on any other site.";
        $_SESSION['info'] = $info;
        header('location: new-password.php');
        exit();
    } else {
        $errors['email'] = "This email address does not exist!";
    }
}

// if user click change password button
if(isset($_POST['change-password'])){
    $_SESSION['info'] = "";
    $password = mysqli_real_escape_string($con, $_POST['password']);
    $cpassword = mysqli_real_escape_string($con, $_POST['cpassword']);
    
    if($password !== $cpassword){
        $errors['password'] = "Confirm password not matched!";
    } else {
        $email = $_SESSION['email'];
        $encpass = password_hash($password, PASSWORD_BCRYPT);
        $update_pass = "UPDATE usertable SET password = '$encpass' WHERE email = '$email'";
        $run_query = mysqli_query($con, $update_pass);
        
        if($run_query){
            $info = "Your password changed. Now you can login with your new password.";
            $_SESSION['info'] = $info;
            header('Location: password-changed.php');
            exit();
        } else {
            $errors['db-error'] = "Failed to change your password!";
        }
    }
}

// if login now button click
if(isset($_POST['login-now'])){
    header('Location: log.php');
}
?>