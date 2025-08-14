<?php
// get_schedule.php
date_default_timezone_set('Asia/Manila');
session_start();
require_once 'connection.php';
header('Content-Type: application/json');

$month = isset($_GET['month']) ? intval($_GET['month']) : null;
$year  = isset($_GET['year']) ? intval($_GET['year']) : null;
if(!$month || !$year){ echo json_encode(['success'=>false,'message'=>'Month and year required.']); exit; }

$start_date = sprintf('%04d-%02d-01', $year, $month);
$end_date = date('Y-m-t', strtotime($start_date));

// Get bookings in range
$q = "SELECT id, user_id, full_name, food_package, event_type, event_date, start_time, end_time, booking_status FROM bookings WHERE event_date BETWEEN ? AND ? AND booking_status != 'cancelled' ORDER BY event_date, start_time";
$stmt = $con->prepare($q);
$stmt->bind_param('ss', $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();

$schedule_data = [];
$current_user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

while($row = $result->fetch_assoc()){
    $date = $row['event_date'];
    if(!isset($schedule_data[$date])){
        $schedule_data[$date] = ['count'=>0,'user_events'=>[]];
    }
    $schedule_data[$date]['count']++;

    $is_owner = ($current_user_id !== null && intval($row['user_id']) === $current_user_id);
    $display = [];
    $display['time'] = $row['start_time'].' - '.$row['end_time'];
    $display['status'] = $row['booking_status'];

    if($is_owner){
        // full details for owner
        $display['event'] = $row['event_type'];
        $display['package'] = $row['food_package'];
        $display['client'] = $row['full_name'];
        $display['id'] = $row['id'];
    } else {
        // limited info for other users
        $display['event'] = 'Booked';
        $display['package'] = null;
        $display['client'] = null;
        $display['id'] = $row['id'];
    }

    $schedule_data[$date]['user_events'][] = $display;
}

// Build response for all days in month
$response_schedule = [];
$MAX_PER_DAY = 3;
$begin = new DateTime($start_date);
$endd = new DateTime($end_date);
$interval = new DateInterval('P1D');
$period = new DatePeriod($begin, $interval, $endd->add($interval));

foreach($period as $dt){
    $dstr = $dt->format('Y-m-d');
    if(isset($schedule_data[$dstr])){
        $count = $schedule_data[$dstr]['count'];
        $status = ($count >= $MAX_PER_DAY) ? 'unavailable' : 'available';
        $user_events = $schedule_data[$dstr]['user_events'];
    } else {
        $count = 0;
        $status = 'available';
        $user_events = [];
    }

    $response_schedule[$dstr] = ['status'=>$status,'count'=>$count,'user_events'=>$user_events];
}

echo json_encode(['success'=>true,'schedule_data'=>$response_schedule]);
exit;