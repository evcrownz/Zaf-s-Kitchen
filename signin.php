<?php require_once "controllerUserData.php"; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zaf's Kitchen - Login & Registration</title>
     <link rel="icon" type="image/png" href="logo.png">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@400;500;600;700&family=Kalam:wght@300;400;700&family=Caveat:wght@400;500;600;700&family=Cinzel:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap');
    @import url('https://fonts.googleapis.com/css2?family=Cinzel:wght@400;500;600;700&display=swap');
    
    /* LHF Ascribe Font - Download the OTF file and place it in your fonts folder */
    @font-face {
        font-family: 'LHF Ascribe';
        src: url('fonts/CR_LHF-Ascribe-Regular-Regular-otf-400.otf') format('opentype'),
             url('fonts/LHFAscribe.woff2') format('woff2'),
             url('fonts/LHFAscribe.woff') format('woff'),
             url('fonts/LHFAscribe.ttf') format('truetype');
        font-weight: 400;
        font-style: normal;
        font-display: swap;
    }

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Poppins', sans-serif;
}

body {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-image: url("");
    background-color: black;
    background-repeat: no-repeat;
    background-position: center center;
    background-size: cover;
    padding: 20px;
    padding-top: 80px;

}

/* Navigation Styles */
.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    padding: 20px 50px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    z-index: 1000;
    transition: all 0.3s ease;
}

.logo {
    display: flex;
    align-items: center;
    font-size: 16px;
    font-weight: 800;
    color: #E75925;
    text-decoration: none;
    transition: all 0.3s ease;
    font-family: 'LHF Ascribe', 'Cinzel', 'Times New Roman', serif;
    letter-spacing: 2px;
    text-shadow: 3px 3px 6px rgba(0,0,0,0.5);
    font-style: normal;
    transform: skewX(-5deg);
}

.logo .logo-z {
    font-weight: 900;
    font-size: 25px;
    text-shadow: 4px 4px 8px rgba(0,0,0,0.7);
    font-family: 'LHF Ascribe', 'Cinzel', 'Times New Roman', serif;
}

.logo:hover {
    transform: scale(1.05) skewX(-5deg);
    color: #ff6b35;
}

.logo-icon {
    width: 42px;
    height: 42px;
    margin-right: 12px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.logo:hover .logo-icon {
    transform: rotate(10deg);
}

.logo-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain;
    background: transparent;
    mix-blend-mode: screen;
}

.nav-menu {
    display: flex;
    list-style: none;
    gap: 30px;
}

.nav-menu li a {
    color: #fff;
    text-decoration: none;
    font-weight: 500;
    font-size: 16px;
    position: relative;
    transition: color 0.3s ease;
}

.nav-menu li a::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -5px;
    left: 0;
    background: #E75925;
    transition: width 0.3s ease;
}

.nav-menu li a:hover {
    color: #E75925;
}

.nav-menu li a:hover::after {
    width: 100%;
}

.hamburger {
    display: none;
    flex-direction: column;
    cursor: pointer;
    padding: 5px;
}

.hamburger span {
    width: 25px;
    height: 3px;
    background: #fff;
    margin: 3px 0;
    transition: 0.3s;
}

.hamburger.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-5px, 6px);
}

.hamburger.active span:nth-child(2) {
    opacity: 0;
}

.hamburger.active span:nth-child(3) {
    transform: rotate(45deg) translate(-5px, -6px);
}

.container {
    position: relative;
    width: 765px;
    height: 495px;
    background: #ffffff;
    border-radius: 30px;
    box-shadow: 0 0 30px rgba(0, 0, 0, .2);
    overflow: hidden;
    margin: auto;
}

.form-box{
    position: absolute;
    right: 0;
    width: 50%;
    height: 100%;
    background: #fff;
    display: flex;
    align-items: center;
    color: #333;
    text-align: center;
    padding: 36px;
    z-index: 1;
    transition: .6s ease-in-out 0.8s, visibility 0s 1s;
}

.container.active .form-box{
    right: 50%;
}

.container.forgot-active .form-box{
    right: 50%;
}

.container.otp-active .form-box{
    right: 50%;
}

.form-box.signup{
    visibility: hidden;
}

.form-box.forgot{
    visibility: hidden;
}

.form-box.otp{
    visibility: hidden;
}

.container.active .form-box.signup{
    visibility: visible;
}

.container.forgot-active .form-box.forgot{
    visibility: visible;
}

.container.otp-active .form-box.otp{
    visibility: visible;
}

form{
    width: 100%;
}

.container h1{
    font-size: 34px;
    margin: -9px 0;
}

.container h4{
    font-size: 11.5px;
    font-weight: 400;
    margin: 5px 0;
    text-align: center;
}

.input-box {
    position: relative;
    margin: 27px 0;
} 

.input-box input{
    width: 100%;
    padding: 12px 45px 12px 18px;
    background: #eee;
    border-radius: 8px;
    border: none;
    outline: none;
    font-size: 14px;
    color: #333;
    font-weight: 500;
}

.input-box input::placeholder{
    color: #888;
    font-weight: 400;
}

.input-box i{
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 20px;
    color: #888;
}

/* OTP Input Styles */
.otp-container {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin: 20px 0;
}

.otp-input {
    width: 45px;
    height: 45px;
    border: 2px solid #ddd;
    border-radius: 8px;
    text-align: center;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    background: #f9f9f9;
    outline: none;
    transition: all 0.3s ease;
}

.otp-instruction {
    font-size: 14px;
    color: #555;
    font-weight: normal;
}

.otp-input:focus {
    border-color: #E75925;
    background: #fff;
    box-shadow: 0 0 5px rgba(231, 89, 37, 0.3);
}

.otp-input.filled {
    background: #E75925;
    color: white;
    border-color: #E75925;
}

.otp-timer {
    font-size: 14px;
    color: #666;
    margin: 10px 0;
}

.resend-link {
    color: #E75925;
    text-decoration: none;
    font-size: 14px;
    cursor: pointer;
}

.resend-link:hover {
    color: #ff6b35;
    text-decoration: underline;
}

.resend-link.disabled {
    color: #ccc;
    cursor: not-allowed;
    text-decoration: none;
}

.forgot-link{
    margin: -15px 0 15px;
}

.forgot-link a{
    font-size: 14.5px;
    color: #333;
    text-decoration: none;
    cursor: pointer;
}

.forgot-link a:hover{
    color: #E75925;
}

.btn{
    width: 100%;
    height: 40px;
    background: #E75925;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, .1);
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #fff;
    font-weight: 600;
}

.btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.container p{
    font-size: 14.5px;
    margin: 15px 0;
}

.social-icons{
    font-size: 14.5px;
    margin: 15px 0;
}

.social-icons a{
    display: inline-flex;
    padding: 10px;
    border: 2px solid #ccc;
    border-radius: 8px;
    font-size: 24px;
    color: #333;
    text-decoration: none;
    margin: 0 8px;    
}

.toggle-box{
    position: absolute;
    width: 100%;
    height: 100%;
}

.toggle-box::before {
    content: '';
    position: absolute;
    left: -250%;
    width: 300%;
    height: 100%;
    
    background-image: linear-gradient(
        rgb(218, 81, 47),  /* top color overlay */
        rgb(201, 49, 38)   /* bottom color overlay */
    );
    border-radius: 150px;
    z-index: 2;
    transition: 1.4s ease-in-out;
}

.container.active .toggle-box::before,
.container.forgot-active .toggle-box::before,
.container.otp-active .toggle-box::before {
    left: 50%;
}

.toggle-panel{
    position: absolute;
    width: 50%;
    height: 100%;
    color: #fff;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    z-index: 2;
    transition: .6s ease-in-out;
}

.toggle-panel.toggle-left {
    left: 0;
    transition-delay: .6s;
}

.container.active .toggle-panel.toggle-left,
.container.forgot-active .toggle-panel.toggle-left,
.container.otp-active .toggle-panel.toggle-left{
    left: -50%;
    transition-delay: .6s;
}

.toggle-panel.toggle-right{
    right: -50%;
    transition-delay: .6s;
}

.container.active .toggle-panel.toggle-right,
.container.forgot-active .toggle-panel.toggle-right,
.container.otp-active .toggle-panel.toggle-right{
    right: 0;
    transition-delay: .6s;
}

.toggle-panel p{
    margin-bottom: 20px;
}

.toggle-panel .btn{
    width: 160px;
    height: 40px;
    background: transparent;
    border: 2px solid #fff;
    box-shadow: none;
}

.back-btn {
    background: none;
    border: none;
    color: #E75925;
    font-size: 14px;
    cursor: pointer;
    margin: 10px 0;
    text-decoration: underline;
}

.back-btn:hover {
    color: #ff6b35;
}

/* Success message styles */
.success-message {
    background: #d4edda;
    color: #155724;
    padding: 10px;
    border-radius: 8px;
    margin: 10px 0;
    border: 1px solid #c3e6cb;
    display: none;
}

.error-message {
    background: #f8d7da;
    color: #721c24;
    padding: 10px;
    border-radius: 8px;
    margin: 10px 0;
    border: 1px solid #f5c6cb;
    display: none;
}

/* Mobile Navigation */
@media screen and (max-width: 768px) {
    .navbar {
        padding: 15px 20px;
    }
    
    .nav-menu {
        position: fixed;
        left: -100%;
        top: 70px;
        flex-direction: column;
        background: rgba(29, 29, 29, 0.95);
        width: 100%;
        text-align: center;
        transition: 0.3s;
        padding: 30px 0;
        backdrop-filter: blur(10px);
    }

    .nav-menu.active {
        left: 0;
    }

    .nav-menu li {
        margin: 15px 0;
    }

    .hamburger {
        display: flex;
    }

    .logo {
        font-size: 15px;
        letter-spacing: 1.5px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .logo .logo-z {
        font-weight: 900;
        font-size: 20px;
        text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
    }

    .logo-icon {
        width: 30px;
        height: 36px;
        margin-right: 10px;
    }

    .logo-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: transparent;
        mix-blend-mode: screen;
    }
 
}

@media screen and (max-width: 650px) {
    body {
        min-height: 100svh;
        padding: 10px;
        padding-top: 80px;
    }
    
    .container{
        height: 750px;
        width: 95%;
        max-width: 400px;
        position: relative;
        margin: 20px auto;
    }

    .form-box {
        bottom: 0;
        width: 100%;
        height: 550px;
        right: 0;
        padding: 30px;
        overflow: hidden;
    }

    .container.active .form-box,
    .container.forgot-active .form-box,
    .container.otp-active .form-box{
        right: 0;
        bottom: 200px;
    }

    .toggle-box::before{
        left: 0;
        top: -1950px;
        width: 100%;
        height: 2200px;
        border-radius: 20vw;
         background: #E75925;
    }

    .container.active .toggle-box::before,
    .container.forgot-active .toggle-box::before,
    .container.otp-active .toggle-box::before{
        top: 550px;
        left: 0;
    }

      .container h4 {
        font-weight: 300;
        font-size: small;
    }

    .container h1 {
        font-size: 30px;
    }

    .container p {
        font-size: 14px;
        margin-bottom: 5px;
      
    }

    .toggle-panel{
        width: 100%;
        height: 200px;
    }

    .toggle-panel.toggle-left{
        top: 0;
        left: 0;
    }

    .container.active .toggle-panel.toggle-left,
    .container.forgot-active .toggle-panel.toggle-left,
    .container.otp-active .toggle-panel.toggle-left{
        left: 0;
        top: -200px;
    }

    .toggle-panel.toggle-right{
        right: 0;
        bottom: -200px;
    }

    .container.active .toggle-panel.toggle-right,
    .container.forgot-active .toggle-panel.toggle-right,
    .container.otp-active .toggle-panel.toggle-right{
        bottom: 0;
        right: 0;
    }

    .otp-input {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }
}

@media screen and (max-width: 400px){
    .form-box{
        padding: 20px;
    }

    .toggle-panel h1 {
        font-size: 200px;
    }
    
    .container h1{
        font-size: 28px;
    }
    
    .input-box {
        margin: 20px 0;
    }

    .logo {
        font-size: 13px;
        letter-spacing: 1px;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
    }
    
    .logo .logo-z {
        font-weight: 900;
        font-size: 28px;
        text-shadow: 3px 3px 6px rgba(0,0,0,0.7);
    }

    .logo-icon {
        width: 32px;
        height: 32px;
        margin-right: 8px;
    }

    .logo-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        background: transparent;
        mix-blend-mode: screen;
    }

    .otp-input {
        width: 35px;
        height: 35px;
        font-size: 14px;
    }

    
}

.modal-error-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeInOverlay 0.3s ease-out;
}

@keyframes fadeInOverlay {
    from {
        opacity: 0;
        backdrop-filter: blur(0px);
    }
    to {
        opacity: 1;
        backdrop-filter: blur(8px);
    }
}

.modal-error-content {
    background: linear-gradient(145deg, #ffffff 0%, #fafafa 100%);
    padding: 35px 30px 30px;
    border-radius: 20px;
    width: 90%;
    max-width: 450px;
    text-align: left;
    color: #2c3e50;
    border: 2px solid #e74c3c;
    box-shadow: 
        0 20px 60px rgba(231, 76, 60, 0.15),
        0 8px 25px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.6);
    position: relative;
    z-index: 10000;
    transform: scale(0.7);
    animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

@keyframes popIn {
    to {
        transform: scale(1);
    }
}

.modal-error-content::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg,rgb(51, 6, 6),rgb(247, 137, 124),rgba(117, 34, 34, 0.76));
    border-radius: 22px;
    z-index: -1;
    background-size: 300% 300%;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

.modal-error-content h3 {
    margin-bottom: 20px;
    color:rgb(255, 255, 255);
    font-size: 25px;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 12px;
    font-family: 'Poppins', sans-serif;
}

.modal-error-content h3::before {
    content: '⚠️';
    font-size: 28px;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.modal-error-content ul {
    padding-left: 0;
    list-style: none;
    font-size: 15px;
    line-height: 1.6;
    margin: 0;
}

.modal-error-content ul li {
    position: relative;
    padding: 8px 0 8px 35px;
    margin-bottom: 5px;
    background: rgba(231, 76, 60, 0.05);
    border-radius: 8px;
    padding-left: 45px;
    padding-right: 15px;
    transition: all 0.3s ease;
}

.modal-error-content ul li:hover {
    background: rgba(231, 76, 60, 0.1);
    transform: translateX(3px);
}

.modal-error-content ul li::before {
    content: '✕';
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #e74c3c;
    font-weight: bold;
    font-size: 14px;
    width: 18px;
    height: 18px;
    background: rgba(231, 76, 60, 0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
}

.close-error-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    width: 35px;
    height: 35px;
    background: white;
    border: 2px solid #E75925;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    color: #E75925;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10001;
}

.close-error-btn:hover {
    background: #E75925;
    color: white;
    transform: rotate(90deg) scale(1.1);
    box-shadow: 0 5px 15px rgba(231, 89, 37, 0.4);
}

.close-error-btn::before {
    content: '×';
    font-size: 20px;
    line-height: 1;
}

/* Add a subtle glow effect */
.modal-error-content {
    animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards,
               subtleGlow 4s ease-in-out infinite alternate;
}

@keyframes subtleGlow {
    from {
        box-shadow: 
            0 20px 60px rgba(231, 76, 60, 0.15),
            0 8px 25px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }
    to {
        box-shadow: 
            0 20px 60px rgba(231, 76, 60, 0.25),
            0 8px 25px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }
}

/* Mobile responsiveness */
@media screen and (max-width: 480px) {
    .modal-error-content {
        width: 95%;
        padding: 30px 25px 25px;
        border-radius: 16px;
        max-width: none;
        margin: 20px;
    }
    
    .modal-error-content h3 {
        font-size: 20px;
        margin-bottom: 18px;
    }
    
    .modal-error-content ul li {
        font-size: 14px;
        padding: 6px 0 6px 40px;
        padding-right: 12px;
    }
    
    .close-error-btn {
        width: 30px;
        height: 30px;
        top: 12px;
        right: 15px;
        font-size: 16px;
    }
}

/* Dark mode support (optional) */
@media (prefers-color-scheme: dark) {
    .modal-error-overlay {
        background: rgba(0, 0, 0, 0.9);
    }
    
    .modal-error-content {
        background: linear-gradient(145deg, #2c3e50 0%, #34495e 100%);
        color: #ecf0f1;
        border-color: #e74c3c;
    }
    
    .modal-error-content ul li {
        background: rgba(231, 76, 60, 0.1);
    }
    
    .modal-error-content ul li:hover {
        background: rgba(231, 76, 60, 0.2);
    }
}

/* OTP Modal Overlay */
.otp-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    animation: fadeInOverlay 0.3s ease-out;
}

@keyframes fadeInOverlay {
    from {
        opacity: 0;
        backdrop-filter: blur(0px);
    }
    to {
        opacity: 1;
        backdrop-filter: blur(8px);
    }
}

/* OTP Modal Content */
.otp-modal-content {
    background: linear-gradient(145deg, #ffffff 0%, #fafafa 100%);
    padding: 40px 35px 35px;
    border-radius: 20px;
    width: 90%;
    max-width: 480px;
    text-align: center;
    color: #2c3e50;
    border: 2px solid #E75925;
    box-shadow: 
        0 20px 60px rgba(231, 89, 37, 0.15),
        0 8px 25px rgba(0, 0, 0, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.6);
    position: relative;
    z-index: 10000;
    transform: scale(0.7);
    animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards;
}

@keyframes popIn {
    to {
        transform: scale(1);
    }
}

/* Animated border effect */
.otp-modal-content::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    background: linear-gradient(45deg,rgb(255, 255, 255),rgb(255, 255, 255),rgba(231, 89, 37, 0.49),rgb(85, 66, 60));
    border-radius: 22px;
    z-index: -1;
    background-size: 300% 300%;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0%, 100% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
}

/* OTP Modal Header */
.otp-modal-content h2 {
    margin-bottom: 15px;
    color: #2c3e50;
    font-size: 28px;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.otp-modal-content h2::before {
    content: '📧';
    font-size: 32px;
    animation: bounce 2s infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-5px); }
}

/* OTP Instruction Text */
.otp-instruction {
    font-size: 15px;
    color: #555;
    font-weight: 400;
    margin-bottom: 25px;
    line-height: 1.5;
}

.otp-instruction span {
    color: #E75925;
    font-weight: 600;
}

/* OTP Input Container */
.otp-container {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin: 25px 0;
}

/* OTP Input Fields */
.otp-input {
    width: 50px;
    height: 50px;
    border: 2px solid #ddd;
    border-radius: 12px;
    text-align: center;
    font-size: 20px;
    font-weight: 700;
    color: #333;
    background: #f9f9f9;
    outline: none;
    transition: all 0.3s ease;
    font-family: 'Poppins', sans-serif;
}

.otp-input:focus {
    border-color: #E75925;
    background: #fff;
    box-shadow: 0 0 10px rgba(231, 89, 37, 0.3);
    transform: scale(1.05);
}

.otp-input.filled {
    background: linear-gradient(145deg, #E75925, #ff6b35);
    color: white;
    border-color: #E75925;
    box-shadow: 0 5px 15px rgba(231, 89, 37, 0.4);
}

.otp-input:invalid {
    border-color: #e74c3c;
    background: #ffeaea;
}

/* OTP Timer */
.otp-timer {
    font-size: 14px;
    color: #666;
    margin: 15px 0;
    font-weight: 500;
}

#countdown {
    color: #E75925;
    font-weight: 700;
}

.resend-link {
    color: #E75925;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-block;
    position: relative;
}

.resend-link::after {
    content: '';
    position: absolute;
    width: 0;
    height: 2px;
    bottom: -2px;
    left: 0;
    background: #E75925;
    transition: width 0.3s ease;
}

.resend-link:hover {
    color: #ff6b35;
    transform: translateY(-1px);
}

.resend-link:hover::after {
    width: 100%;
}

.resend-link.disabled {
    color: #ccc;
    cursor: not-allowed;
    transform: none;
}

.resend-link.disabled:hover::after {
    width: 0;
}

.otp-verify-btn {
    width: 100%;
    height: 45px;
    background: linear-gradient(145deg, #E75925, #ff6b35);
    border-radius: 12px;
    box-shadow: 0 5px 15px rgba(231, 89, 37, 0.3);
    border: none;
    cursor: pointer;
    font-size: 16px;
    color: #fff;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
    transition: all 0.3s ease;
    margin: 20px 0 15px 0;
    position: relative;
    overflow: hidden;
}

.otp-verify-btn::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    transition: left 0.5s;
}

.otp-verify-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(231, 89, 37, 0.4);
}

.otp-verify-btn:hover::before {
    left: 100%;
}

.otp-verify-btn:active {
    transform: translateY(0);
    box-shadow: 0 3px 10px rgba(231, 89, 37, 0.3);
}

.otp-verify-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.otp-error {
    background: linear-gradient(145deg, #f8d7da, #f5c6cb);
    color: #721c24;
    padding: 12px;
    border-radius: 8px;
    margin: 15px 0;
    border: 1px solid #f5c6cb;
    font-size: 14px;
    font-weight: 500;
    position: relative;
    animation: slideIn 0.3s ease;
}

.otp-error::before {
    content: '⚠️';
    margin-right: 8px;
}

.otp-success {
    background: linear-gradient(145deg, #d4edda, #c3e6cb);
    color: #155724;
    padding: 12px;
    border-radius: 8px;
    margin: 15px 0;
    border: 1px solid #c3e6cb;
    font-size: 14px;
    font-weight: 500;
    position: relative;
    animation: slideIn 0.3s ease;
}

.otp-success::before {
    content: '✅';
    margin-right: 8px;
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.otp-close-btn {
    position: absolute;
    top: 15px;
    right: 20px;
    width: 35px;
    height: 35px;
    background: white;
    border: 2px solid #E75925;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
    color: #E75925;
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 10001;
}

.otp-close-btn:hover {
    background: #E75925;
    color: white;
    transform: rotate(90deg) scale(1.1);
    box-shadow: 0 5px 15px rgba(231, 89, 37, 0.4);
}

.otp-close-btn::before {
    content: '×';
    font-size: 20px;
    line-height: 1;
}

.otp-modal-content {
    animation: popIn 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55) forwards,
               subtleGlow 4s ease-in-out infinite alternate;
}

@keyframes subtleGlow {
    from {
        box-shadow: 
            0 20px 60px rgba(231, 89, 37, 0.15),
            0 8px 25px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
    }
    to {
        box-shadow: 
            0 20px 60px rgba(231, 89, 37, 0.25),
            0 8px 25px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }
}

@media screen and (max-width: 768px) {
    .otp-modal-content {
        width: 95%;
        padding: 35px 25px 30px;
        border-radius: 16px;
        max-width: none;
        margin: 20px;
    }
    
    .otp-modal-content h2 {
        font-size: 24px;
        margin-bottom: 12px;
    }
    
    .otp-instruction {
        font-size: 14px;
        margin-bottom: 20px;
    }
    
    .otp-container {
        gap: 8px;
        margin: 20px 0;
    }
    
    .otp-input {
        width: 45px;
        height: 45px;
        font-size: 18px;
        border-radius: 10px;
    }
    
    .otp-verify-btn {
        height: 42px;
        font-size: 15px;
        margin: 18px 0 12px 0;
    }
    
    .otp-close-btn {
        width: 32px;
        height: 32px;
        top: 12px;
        right: 15px;
        font-size: 16px;
    }
}

@media screen and (max-width: 480px) {
    .otp-modal-content {
        padding: 30px 20px 25px;
        border-radius: 12px;
    }
    
    .otp-modal-content h2 {
        font-size: 22px;
        margin-bottom: 10px;
    }
    
    .otp-instruction {
        font-size: 13px;
        margin-bottom: 18px;
    }
    
    .otp-container {
        gap: 6px;
        margin: 18px 0;
    }
    
    .otp-input {
        width: 40px;
        height: 40px;
        font-size: 16px;
        border-radius: 8px;
    }
    
    .otp-verify-btn {
        height: 40px;
        font-size: 14px;
        border-radius: 10px;
    }
    
    .otp-timer, .resend-link {
        font-size: 13px;
    }
    
    .otp-error, .otp-success {
        font-size: 13px;
        padding: 10px;
    }
}

@media screen and (max-width: 360px) {
    .otp-container {
        gap: 4px;
    }
    
    .otp-input {
        width: 35px;
        height: 35px;
        font-size: 14px;
        border-radius: 6px;
    }
}

</style>

<body>
  <!-- Navigation Bar -->
    <nav class="navbar">
        <a href="#" class="logo">
            <div class="logo-icon">
                <img src="logo.png" alt="Zaf's Kitchen Logo">
            </div>
            <span class="logo-z">Z</span>af's Kitchen
        </a>
        <ul class="nav-menu">
            <li><a href="#home">Home</a></li>
            <li><a href="#about">About Us</a></li>
            <li><a href="#contact">Contact</a></li>
        </ul>
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Error Modal -->
    <?php if(count($errors) > 0): ?>
        <div class="modal-error-overlay" id="errorModal">
            <div class="modal-error-content">
                <span class="close-error-btn" onclick="closeErrorModal()">&times;</span>
                <h3>Error</h3>
                <ul>
                    <?php foreach($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>


 <!-- OTP Verification Modal -->
<div class="otp-modal-overlay" id="otpModal" style="display: none;">
    <div class="otp-modal-content">
        <h2>Verify Your Email</h2>
        <p class="otp-instruction">We've sent a 6-digit verification code to <span id="userEmail"><?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?></span></p>
        
        <!-- Show success messages -->
        <?php if(isset($_SESSION['info'])): ?>
            <div class="otp-success" style="display: block;">
                <?php echo $_SESSION['info']; unset($_SESSION['info']); ?>
            </div>
        <?php endif; ?>
        
        <!-- Show OTP errors here -->
        <?php if(isset($errors['otp-error'])): ?>
            <div class="otp-error" style="display: block;">
                <?php echo $errors['otp-error']; ?>
            </div>
        <?php endif; ?>
        
        <form id="otpForm" method="POST" action="">
            <div class="otp-container">
                <input type="text" class="otp-input" maxlength="1" data-index="0" name="otp1" required autocomplete="off">
                <input type="text" class="otp-input" maxlength="1" data-index="1" name="otp2" required autocomplete="off">
                <input type="text" class="otp-input" maxlength="1" data-index="2" name="otp3" required autocomplete="off">
                <input type="text" class="otp-input" maxlength="1" data-index="3" name="otp4" required autocomplete="off">
                <input type="text" class="otp-input" maxlength="1" data-index="4" name="otp5" required autocomplete="off">
                <input type="text" class="otp-input" maxlength="1" data-index="5" name="otp6" required autocomplete="off">
            </div>
            
            <div class="otp-timer">
                <p id="timer">Resend available in <span id="countdown">60</span> seconds</p>
            </div>
            
            <div class="otp-error" id="otpError" style="display: none;"></div>
            <div class="otp-success" id="otpSuccess" style="display: none;"></div>
            
            <button type="submit" name="check" class="btn otp-verify-btn" id="verifyBtn" disabled>Verify OTP</button>
        </form>
        
        <!-- Separate form for resend OTP -->
        <form id="resendForm" method="POST" action="" style="display: inline;">
            <button type="submit" name="resend-otp" class="resend-link disabled" id="resendOtp">Resend OTP</button>
        </form>
    </div>
</div>

        <script>
        <?php if((isset($_SESSION['show_otp_modal']) && $_SESSION['show_otp_modal'] === true) || isset($errors['otp-error'])): ?>
    document.addEventListener('DOMContentLoaded', function() {
        showOTPModal();
    });
    <?php unset($_SESSION['show_otp_modal']); ?>
<?php endif; ?>

        </script>


    <!-- Main Container -->
    <div class="container <?php echo (isset($_POST['signup']) || isset($_POST['name']) || (count($errors) > 0 && !empty($name))) ? 'active show-signup' : ''; ?>">
        <!-- Sign In Form -->
        <div class="form-box signin">
            <form action="" id="signinForm" method="POST" autocomplete="">
                <h1>Signin</h1>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required value="<?php echo $email; ?>">
                    <i class="bx bxs-user"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="bx bxs-lock-alt"></i>
                </div>
                <div class="forgot-link">
                    <a href="#" class="forgot-password-link">Forgot password?</a>
                </div>
                <button type="submit" name="signin" class="btn">Signin</button>
              
                <p>Or login with social platforms</p>
                <div class="social-icons">
                    <a href="#"><i class="bx bxl-google"></i></a>
                    <a href="#"><i class="bx bxl-facebook"></i></a>
                </div>
            </form>
        </div>

        <!-- Sign Up Form -->
        <div class="form-box signup">
            <form action="" method="POST" autocomplete="">
                <h1>Signup</h1>

                <div class="input-box">
                    <input type="text" name="name" placeholder="Full name" required value="<?php echo $name; ?>">
                    <i class="bx bxs-user"></i>
                </div>
                <div class="input-box">
                    <input type="email" name="email" placeholder="Email" required value="<?php echo $email; ?>">
                    <i class="bx bxs-envelope"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="password" placeholder="Password" required>
                    <i class="bx bxs-lock-alt"></i>
                </div>
                <div class="input-box">
                    <input type="password" name="cpassword" placeholder="Confirm Password" required>
                    <i class="bx bxs-lock-alt"></i>
                </div>
                <button type="submit" name="signup" class="btn">Signup</button>
            </form>
        </div>

        <!-- Forgot Password Form -->
        <div class="form-box forgot">
            <form action="" id="forgotForm">
                <h1>Reset Password</h1>
                <h4>Enter your email address and we'll send you a link to reset your password</h4>
                <div class="input-box">
                    <input type="email" placeholder="Enter your email" required>
                    <i class="bx bxs-envelope"></i>
                </div>
                <button type="submit" class="btn">Send Reset Link</button>
            </form>
        </div>

        <div class="toggle-box">
            <!-- Left Panel (Welcome Back) -->
            <div class="toggle-panel toggle-left">
                <h1>Welcome back!</h1>
                <h4>Sign in to Zaf's Kitchen to manage <br>your catering events</h4>
                <p>Don't have an account?</p>
                <button class="btn signup-btn">Signup</button>
            </div>

            <!-- Right Panel (Join Now) -->
            <div class="toggle-panel toggle-right">
                <h1>Join now!</h1>
                <h4>let Zaf's Kitchen Bring flavor to your<br> celebrations</h4>
                <p>Already have an account?</p>
                <button class="btn signin-btn">Signin</button>
            </div>
        </div>
    </div>

    <script>
        // Show OTP modal if signup was successful
        <?php if(isset($_SESSION['show_otp_modal']) && $_SESSION['show_otp_modal'] === true): ?>
            document.addEventListener('DOMContentLoaded', function() {
                showOTPModal();
            });
            <?php unset($_SESSION['show_otp_modal']); ?>
        <?php endif; ?>

        // Close error modal function
        function closeErrorModal() {
            const modal = document.getElementById("errorModal");
            if (modal) modal.style.display = "none";
        }

        // Show OTP modal function
        function showOTPModal() {
            const modal = document.getElementById("otpModal");
            const userEmail = document.getElementById("userEmail");
            
            if (modal) {
                modal.style.display = "flex";
                startCountdown();
            }
        }

        // Hide OTP modal function
        function hideOTPModal() {
            const modal = document.getElementById("otpModal");
            if (modal) modal.style.display = "none";
        }
    </script>
    <script src="authscript.js"></script>
</body>
</html>