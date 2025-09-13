<?php
$host = 'aws-1-ap-southeast-1.pooler.supabase.com';
$port = '6543';
$db   = 'postgres';
$user = 'postgres.mkcuuneodccwtoxrjwui';
$pass = 'zafs_kitchen123';

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass, [
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    // Test connection (remove this in production)
    // echo "Connected successfully!<br>";
    
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>