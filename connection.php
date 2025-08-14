<?php 
$con = mysqli_connect('localhost', 'root', '', 'userform');
?>

<?php
// connection.php
$conn = mysqli_connect('localhost', 'root', '', 'userform');

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Set charset to UTF-8 for proper handling of special characters
mysqli_set_charset($conn, "utf8");
?>