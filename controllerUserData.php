
<?php 
// This fixes the "OTP has expired" issue due to mismatched timezones
date_default_timezone_set('Asia/Manila');

session_start();
require "connection.php"; // This should contain your Supabase connection
require_once "sendmail.php";

// Initialize variables
$email = "";
$name = "";
$errors = [];

// Function to generate secure reset token
function generateResetToken($length = 32) {
    return bin2hex(random_bytes($length));
}

// Function to generate OTP
if(isset($_POST['check'])) {
    // Get email from session or hidden input
    $email = isset($_SESSION['email']) ? $_SESSION['email'] : (isset($_POST['email']) ? $_POST['email'] : '');
    
    if(empty($email)) {
        $errors['otp-error'] = 'Session expired or email missing. Please sign up again.';
    } else {
        // Combine OTP inputs safely with trim
        $entered_otp = '';
        for ($i = 1; $i <= 6; $i++) {
            $entered_otp .= isset($_POST["otp$i"]) ? trim($_POST["otp$i"]) : '';
        }

        // Validate OTP and expiry using PDO
        try {
            $check_otp = "SELECT * FROM usertable WHERE email = :email AND code = :otp AND otp_expiry > NOW()";
            $stmt = $conn->prepare($check_otp);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':otp', $entered_otp);
            $stmt->execute();

            if($stmt->rowCount() > 0){
                // OTP is correct and valid
                $update_status = "UPDATE usertable SET status = 'verified', code = NULL, otp_expiry = NULL WHERE email = :email";
                $update_stmt = $conn->prepare($update_status);
                $update_stmt->bindParam(':email', $email);
                
                if($update_stmt->execute()){
                    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
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
                $check_expired = "SELECT * FROM usertable WHERE email = :email AND code = :otp";
                $expired_stmt = $conn->prepare($check_expired);
                $expired_stmt->bindParam(':email', $email);
                $expired_stmt->bindParam(':otp', $entered_otp);
                $expired_stmt->execute();

                if($expired_stmt->rowCount() > 0){
                    $errors['otp-error'] = 'OTP has expired. Please resend a new one.';
                } else {
                    $errors['otp-error'] = 'Invalid OTP. Please check and try again.';
                }

                $_SESSION['show_otp_modal'] = true;
            }
        } catch(PDOException $e) {
            $errors['otp-error'] = 'Database error occurred. Please try again.';
            $_SESSION['show_otp_modal'] = true;
        }
    }
}

// if user signup button
if(isset($_POST['signup'])){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Check if passwords match
    if($password !== $cpassword){
        $errors['password'] = "Confirm password not matched!";
    }

    // Check for password length
    if(strlen($password) < 8){
        $errors['password_length'] = "Password must be at least 8 characters long!";
    }

    // Check if email already exists using PDO
    try {
        $email_check = "SELECT * FROM usertable WHERE email = :email";
        $stmt = $conn->prepare($email_check);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        if($stmt->rowCount() > 0){
            $errors['email'] = "Email that you have entered already exists!";
        }

        // If no errors, proceed
        if(count($errors) === 0){
            $encpass = password_hash($password, PASSWORD_BCRYPT);
            $otp = generateOTP();
            $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
            $status = "unverified";

            // Insert user data using PDO
            $insert_data = "INSERT INTO usertable (name, email, password, status, code, otp_expiry)
                            VALUES (:name, :email, :password, :status, :code, :otp_expiry)";
            $insert_stmt = $conn->prepare($insert_data);
            $insert_stmt->bindParam(':name', $name);
            $insert_stmt->bindParam(':email', $email);
            $insert_stmt->bindParam(':password', $encpass);
            $insert_stmt->bindParam(':status', $status);
            $insert_stmt->bindParam(':code', $otp);
            $insert_stmt->bindParam(':otp_expiry', $otp_expiry);

            if($insert_stmt->execute()){
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
    } catch(PDOException $e) {
        $errors['db-error'] = "Database error occurred. Please try again.";
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
        
        try {
            $update_otp = "UPDATE usertable SET code = :code, otp_expiry = :otp_expiry WHERE email = :email";
            $stmt = $conn->prepare($update_otp);
            $stmt->bindParam(':code', $new_otp);
            $stmt->bindParam(':otp_expiry', $otp_expiry);
            $stmt->bindParam(':email', $email);
            
            if($stmt->execute() && sendOTPEmail($email, $new_otp, $name)){
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
        } catch(PDOException $e) {
            if(isset($_POST['action'])){
                // AJAX request
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Database error occurred']);
                exit();
            } else {
                $errors['otp-error'] = 'Database error occurred';
            }
        }
    }
}

// if user click signin button
if(isset($_POST['signin'])){    
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
        try {
            $check_email = "SELECT * FROM usertable WHERE email = :email";
            $stmt = $conn->prepare($check_email);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0){
                $fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                $fetch_pass = $fetch['password'];
                
                if($fetch['status'] == 'unverified'){
                    // Resend OTP for unverified accounts
                    $new_otp = generateOTP();
                    $otp_expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                    
                    $update_otp = "UPDATE usertable SET code = :code, otp_expiry = :otp_expiry WHERE email = :email";
                    $update_stmt = $conn->prepare($update_otp);
                    $update_stmt->bindParam(':code', $new_otp);
                    $update_stmt->bindParam(':otp_expiry', $otp_expiry);
                    $update_stmt->bindParam(':email', $email);
                    
                    if($update_stmt->execute() && sendOTPEmail($email, $new_otp, $fetch['name'])){
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
        } catch(PDOException $e) {
            $errors['email'] = "Database error occurred. Please try again.";
        }
    } else {
        $errors['email'] = "Enter a valid email address!";
    }
}

// Handle forgot password form submission
if(isset($_POST['forgot-password'])){
    $email = trim($_POST['email']);
    
    if(filter_var($email, FILTER_VALIDATE_EMAIL)){
        try {
            // Check if email exists in database
            $check_email = "SELECT * FROM usertable WHERE email = :email AND status = 'verified'";
            $stmt = $conn->prepare($check_email);
            $stmt->bindParam(':email', $email);
            $stmt->execute();
            
            if($stmt->rowCount() > 0){
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Generate reset token and expiry (30 minutes from now)
                $reset_token = generateResetToken();
                $reset_expiry = date('Y-m-d H:i:s', strtotime('+30 minutes'));
                
                // Store reset token in database
                $update_token = "UPDATE usertable SET reset_token = :token, reset_expiry = :expiry WHERE email = :email";
                $token_stmt = $conn->prepare($update_token);
                $token_stmt->bindParam(':token', $reset_token);
                $token_stmt->bindParam(':expiry', $reset_expiry);
                $token_stmt->bindParam(':email', $email);
                
                if($token_stmt->execute()){
                    // Create reset link
                    $reset_link = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset-password.php?token=" . $reset_token;
                    
                    // Send reset email
                    if(sendPasswordResetEmail($email, $reset_link, $user['name'])){
                        $_SESSION['forgot_success'] = "Password reset link has been sent to your email address.";
                        $_SESSION['show_forgot_success'] = true;
                    } else {
                        $errors['forgot-error'] = "Failed to send password reset email. Please try again.";
                    }
                } else {
                    $errors['forgot-error'] = "Failed to process password reset. Please try again.";
                }
            } else {
                // Don't reveal if email doesn't exist for security
                $_SESSION['forgot_success'] = "If this email exists in our system, a password reset link will be sent.";
                $_SESSION['show_forgot_success'] = true;
            }
        } catch(PDOException $e) {
            $errors['forgot-error'] = "Database error occurred. Please try again.";
        }
    } else {
        $errors['forgot-error'] = "Please enter a valid email address.";
    }
}

// if login now button click
if(isset($_POST['login-now'])){
    header('Location: auth.php');
    exit();
}
?>