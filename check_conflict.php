<?php
require_once 'connection.php';

$date = $_GET['date'] ?? '';
$response = ['conflict' => false];

if (!empty($date)) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM bookings WHERE date = ?");
    $stmt->bind_param("s", $date);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    if ($count > 0) {
        $response['conflict'] = true;
    }
    $stmt->close();
}

header('Content-Type: application/json');
echo json_encode($response);
