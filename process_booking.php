<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// process_booking.php
// Receives booking form submission (expects POST). Returns JSON.
date_default_timezone_set('Asia/Manila');
session_start();
require_once 'connection.php'; // provides $con (mysqli)

header('Content-Type: application/json');

function json_err($msg){ echo json_encode(['success'=>false,'message'=>$msg]); exit; }
function json_ok($msg){ echo json_encode(['success'=>true,'message'=>$msg]); exit; }

// Grab POST (sanitise basic)
$full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
$contact_number = isset($_POST['contact_number']) ? trim($_POST['contact_number']) : '';
$food_package = isset($_POST['food_package']) ? trim($_POST['food_package']) : '';
$event_type = isset($_POST['event_type']) ? trim($_POST['event_type']) : '';
$event_date = isset($_POST['event_date']) ? trim($_POST['event_date']) : '';
$start_time = isset($_POST['start_time']) ? trim($_POST['start_time']) : '';
$end_time = isset($_POST['end_time']) ? trim($_POST['end_time']) : '';
$event_theme = isset($_POST['event_theme']) ? trim($_POST['event_theme']) : '';
$custom_theme = isset($_POST['custom_theme']) ? trim($_POST['custom_theme']) : '';

// Collect menus (supports menu_main[], menu_side[], menu_dessert[] or selected_menus[])
$selected_menus = [];
if(isset($_POST['menu_main']) && is_array($_POST['menu_main'])) $selected_menus = array_merge($selected_menus, $_POST['menu_main']);
if(isset($_POST['menu_side']) && is_array($_POST['menu_side'])) $selected_menus = array_merge($selected_menus, $_POST['menu_side']);
if(isset($_POST['menu_dessert']) && is_array($_POST['menu_dessert'])) $selected_menus = array_merge($selected_menus, $_POST['menu_dessert']);
if(isset($_POST['selected_menus']) && is_array($_POST['selected_menus'])) $selected_menus = array_merge($selected_menus, $_POST['selected_menus']);

// Basic validation
if($full_name === '' || $contact_number === '' || $food_package === '' || $event_type === '' || $event_date === '' || $start_time === '' || $end_time === ''){
    json_err('Please fill in all required fields.');
}

// Validate time order
if (strtotime($start_time) >= strtotime($end_time)) {
    json_err('End time must be after start time.');
}

// Validate date (not in past). Your frontend already sets min to tomorrow; keep server-side check too.
$today = new DateTime('today', new DateTimeZone('Asia/Manila'));
$evdate = DateTime::createFromFormat('Y-m-d', $event_date, new DateTimeZone('Asia/Manila'));
if(!$evdate) json_err('Invalid event date.');
if($evdate < $today) json_err('Event date must not be in the past.');

// Prepare menu string
$selected_menus_str = count($selected_menus) ? implode(',', array_map('trim', $selected_menus)) : null;

// Optional: user id from session
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

// 1) Check daily limit
$MAX_PER_DAY = 3;
$stmt = $con->prepare("SELECT COUNT(*) AS cnt FROM bookings WHERE event_date = ? AND booking_status != 'cancelled'");
if(!$stmt) json_err('Database error (stmt prepare).');
$stmt->bind_param('s', $event_date);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
$stmt->close();
$today_count = intval($res['cnt']);

if($today_count >= $MAX_PER_DAY) {
    json_err('Selected date is fully booked (maximum bookings reached). Please choose another date.');
}

// 2) Check time overlap on same date (exclude cancelled)
// Overlap logic: NOT (existing.end_time <= new.start_time OR existing.start_time >= new.end_time)
$q = "SELECT id, start_time, end_time FROM bookings WHERE event_date = ? AND booking_status != 'cancelled' AND NOT ( end_time <= ? OR start_time >= ? )";
$stmt2 = $con->prepare($q);
if(!$stmt2) json_err('Database error (stmt2 prepare).');
$stmt2->bind_param('sss', $event_date, $start_time, $end_time);
$stmt2->execute();
$confRes = $stmt2->get_result();
if($confRes && $confRes->num_rows > 0){
    $conflicts = [];
    while($r = $confRes->fetch_assoc()){
        $conflicts[] = $r['start_time'].' - '.$r['end_time'];
    }
    $stmt2->close();
    json_err('Schedule conflict detected with existing bookings: '.implode(', ', $conflicts));
}
$stmt2->close();

// Insert booking
$booking_status = 'pending'; // change to 'accepted' if you want auto-accept
$created_at = date('Y-m-d H:i:s');

$insert = "INSERT INTO bookings (user_id, full_name, contact_number, food_package, event_type, event_date, start_time, end_time, event_theme, custom_theme, selected_menus, booking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt3 = $con->prepare($insert);
if(!$stmt3) json_err('Database error (prepare insert).');

// Bind params - user_id can be NULL, so use 'i' with null handling
$uid_param = $user_id !== null ? $user_id : null;
$stmt3->bind_param('issssssssssss', $uid_param, $full_name, $contact_number, $food_package, $event_type, $event_date, $start_time, $end_time, $event_theme, $custom_theme, $selected_menus_str, $booking_status, $created_at);

$exec = $stmt3->execute();
if($exec){
    $stmt3->close();
    json_ok('Booking submitted successfully!');
} else {
    $stmt3->close();
    json_err('Failed to submit booking. Please try again later.');
}