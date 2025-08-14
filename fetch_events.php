<?php
session_start();
require_once 'connection.php';

$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

$query = "SELECT event_date, start_time, end_time, full_name, user_id 
          FROM bookings";
$result = mysqli_query($conn, $query);

$events = [];
$date_counts = [];

while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['event_date'];

    // Count bookings per date
    if (!isset($date_counts[$date])) {
        $date_counts[$date] = 0;
    }
    $date_counts[$date]++;

    // Show full details for own bookings, only time for others
    if ($row['user_id'] == $user_id) {
        $title = $row['full_name'] . " (" . $row['start_time'] . "-" . $row['end_time'] . ")";
    } else {
        $title = $row['start_time'] . "-" . $row['end_time'];
    }

    $events[] = [
        'title' => $title,
        'start' => $date,
        'allDay' => true,
        'extendedProps' => [
            'isUnavailable' => false // will be updated later
        ]
    ];
}

// Mark unavailable days
foreach ($date_counts as $date => $count) {
    if ($count >= 3) {
        $events[] = [
            'title' => 'Unavailable',
            'start' => $date,
            'allDay' => true,
            'display' => 'background',
            'extendedProps' => [
                'isUnavailable' => true
            ]
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($events);
