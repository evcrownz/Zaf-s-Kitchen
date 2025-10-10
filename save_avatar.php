<?php 
date_default_timezone_set('Asia/Manila');
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated.']);
    exit;
}

// Get POST data
$avatarUrl = isset($_POST['avatar_url']) ? trim($_POST['avatar_url']) : '';

if (empty($avatarUrl)) {
    echo json_encode(['success' => false, 'message' => 'Avatar URL is required.']);
    exit;
}

// Validate URL
if (!filter_var($avatarUrl, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'message' => 'Invalid avatar URL.']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    // Update avatar in database
    $query = "UPDATE usertable SET avatar_url = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    
    if ($stmt->execute([$avatarUrl, $userId])) {
        // THIS IS THE KEY FIX: Update the session variable immediately
        $_SESSION['avatar_url'] = $avatarUrl;
        
        echo json_encode(['success' => true, 'message' => 'Avatar saved successfully!', 'avatar_url' => $avatarUrl]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save avatar.']);
    }
    
} catch (PDOException $e) {
    error_log("Avatar save error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred.']);
}
?>