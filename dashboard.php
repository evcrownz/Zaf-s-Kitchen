<?php 
session_start();
require_once 'connection.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle booking submission
if ($_POST && isset($_POST['action']) && $_POST['action'] === 'book_event') {
    header('Content-Type: application/json');
    
    try {
        // Get and validate form data
        $full_name = trim($_POST['full_name'] ?? '');
        $contact_number = trim($_POST['contact_number'] ?? '');
        $food_package = trim($_POST['food_package'] ?? '');
        $event_type = trim($_POST['event_type'] ?? '');
        $event_date = trim($_POST['event_date'] ?? '');
        $start_time = trim($_POST['start_time'] ?? '');
        $end_time = trim($_POST['end_time'] ?? '');
        $event_theme = trim($_POST['event_theme'] ?? '');
        $custom_theme = trim($_POST['custom_theme'] ?? '');
        $selected_menus = trim($_POST['selected_menus'] ?? '');
        
        // Basic validation
        if (empty($full_name) || empty($contact_number) || empty($food_package) || 
            empty($event_type) || empty($event_date) || empty($start_time) || empty($end_time)) {
            echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
            exit;
        }
        
        // Force year to be 2025
        $date_parts = explode('-', $event_date);
        if (count($date_parts) === 3) {
            $event_date = '2025-' . $date_parts[1] . '-' . $date_parts[2];
        }
        
        // Validate date is in the future (at least 3 days from now)
        $min_date = date('Y-m-d', strtotime('+3 days'));
        if ($event_date < $min_date) {
            echo json_encode(['success' => false, 'message' => 'Event date must be at least 3 days from today.']);
            exit;
        }
        
        // Validate time format and duration
        if (strtotime($start_time) >= strtotime($end_time)) {
            echo json_encode(['success' => false, 'message' => 'End time must be after start time.']);
            exit;
        }
        
        // Calculate duration in hours
        $start_timestamp = strtotime("2000-01-01 $start_time");
        $end_timestamp = strtotime("2000-01-01 $end_time");
        $duration_hours = ($end_timestamp - $start_timestamp) / 3600;
        
        if ($duration_hours < 4) {
            echo json_encode(['success' => false, 'message' => 'Event duration must be at least 4 hours.']);
            exit;
        }
        
        if ($duration_hours > 8) {
            echo json_encode(['success' => false, 'message' => 'Event duration cannot exceed 8 hours.']);
            exit;
        }
        
        // Check if date already has 3 bookings
        $checkStmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE event_date = ? AND booking_status != 'cancelled'");
        $checkStmt->bind_param("s", $event_date);
        $checkStmt->execute();
        $result = $checkStmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] >= 3) {
            echo json_encode(['success' => false, 'message' => 'This date is fully booked. Maximum 3 events per day allowed.']);
            exit;
        }
        
        // Check for time conflicts
        $conflictStmt = $conn->prepare("SELECT COUNT(*) as count FROM bookings WHERE event_date = ? AND booking_status != 'cancelled' AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))");
        $conflictStmt->bind_param("sssssss", $event_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
        $conflictStmt->execute();
        $conflictResult = $conflictStmt->get_result();
        $conflictRow = $conflictResult->fetch_assoc();
        
        if ($conflictRow['count'] > 0) {
            echo json_encode(['success' => false, 'message' => 'Time conflict detected. Please choose a different time slot.', 'clear_time' => true]);
            exit;
        }
        
        // Insert booking
        $insertStmt = $conn->prepare("INSERT INTO bookings (user_id, full_name, contact_number, food_package, event_type, event_date, start_time, end_time, event_theme, custom_theme, selected_menus, booking_status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
        $insertStmt->bind_param("issssssssss", $user_id, $full_name, $contact_number, $food_package, $event_type, $event_date, $start_time, $end_time, $event_theme, $custom_theme, $selected_menus);
        
        if ($insertStmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking submitted successfully! Waiting for admin approval.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        }
        
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
    }
    exit;
}

// AJAX: check time conflict
if (isset($_GET['action']) && $_GET['action'] === 'check_conflict') {
    header('Content-Type: application/json');
    
    $event_date = $_GET['event_date'] ?? '';
    $start_time = $_GET['start_time'] ?? '';
    $end_time = $_GET['end_time'] ?? '';
    
    if (!$event_date || !$start_time || !$end_time) {
        echo json_encode(['conflict' => false]);
        exit;
    }
    
    $date_parts = explode('-', $event_date);
    if (count($date_parts) === 3) {
        $event_date = '2025-' . $date_parts[1] . '-' . $date_parts[2];
    }
    
    $stmt = $conn->prepare("SELECT COUNT(*) as count, GROUP_CONCAT(CONCAT(start_time, ' - ', end_time) SEPARATOR ', ') as existing_slots FROM bookings WHERE event_date = ? AND booking_status != 'cancelled' AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))");
    $stmt->bind_param("sssssss", $event_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    echo json_encode([
        'conflict' => $row['count'] > 0,
        'existing_slots' => $row['existing_slots'] ?? ''
    ]);
    exit;
}

// AJAX: get calendar data
if (isset($_GET['action']) && $_GET['action'] === 'get_calendar_data') {
    header('Content-Type: application/json');
    
    $month = $_GET['month'] ?? date('n');
    $year = $_GET['year'] ?? 2025;
    
    // Get all bookings for the month
    $stmt = $conn->prepare("SELECT 
        event_date, 
        start_time, 
        end_time, 
        event_type,
        user_id,
        full_name,
        booking_status,
        COUNT(*) as booking_count
        FROM bookings 
        WHERE YEAR(event_date) = ? 
        AND MONTH(event_date) = ? 
        AND booking_status != 'cancelled'
        GROUP BY event_date
        ORDER BY event_date
    ");
    $stmt->bind_param("ii", $year, $month);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $calendar_data = [];
    while ($row = $result->fetch_assoc()) {
        $date = $row['event_date'];
        
        // Get detailed bookings for this date
        $detailStmt = $conn->prepare("SELECT 
            start_time, 
            end_time, 
            event_type,
            user_id,
            full_name,
            booking_status
            FROM bookings 
            WHERE event_date = ? 
            AND booking_status != 'cancelled'
            ORDER BY start_time
        ");
        $detailStmt->bind_param("s", $date);
        $detailStmt->execute();
        $detailResult = $detailStmt->get_result();
        
        $bookings = [];
        while ($booking = $detailResult->fetch_assoc()) {
            $bookings[] = [
                'start_time' => $booking['start_time'],
                'end_time' => $booking['end_time'],
                'event_type' => $booking['event_type'],
                'is_own_booking' => ($booking['user_id'] == $user_id),
                'full_name' => $booking['full_name'],
                'booking_status' => $booking['booking_status']
            ];
        }
        
        $calendar_data[$date] = [
            'count' => $row['booking_count'],
            'bookings' => $bookings,
            'is_full' => $row['booking_count'] >= 3
        ];
    }
    
    echo json_encode($calendar_data);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Zaf's Kitchen Dashboard</title>

<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

<!-- Poppins Font -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
    * {
        font-family: 'Poppins', sans-serif;
    }
    .hover-nav:hover {
        background-color: #E75925 !important;
        color: white !important;
    }
    .active-nav {
        background-color: #E75925 !important;
        color: white !important;
    }
    
    /* Theme button styles */
    .theme-btn.selected {
        border-color: #E75925 !important;
        background-color: #FEF2F2;
        box-shadow: 0 0 0 2px #E75925;
        transform: scale(1.05);
    }
    
    .theme-btn {
        transition: all 0.2s ease;
    }
    
    .theme-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(231, 89, 37, 0.3);
    }
    
    /* Enhanced form styles */
    .form-input {
        transition: all 0.2s ease;
    }
    
    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(231, 89, 37, 0.2);
    }
    
    /* Custom scrollbar for better UX */
    ::-webkit-scrollbar {
        width: 8px;
    }
    
    ::-webkit-scrollbar-track {
        background: #f1f5f9;
    }
    
    ::-webkit-scrollbar-thumb {
        background: #E75925;
        border-radius: 4px;
    }
    
    ::-webkit-scrollbar-thumb:hover {
        background: #d14d1f;
    }
    
    /* Loading animation */
    .loading-spinner {
        border: 2px solid #f3f4f6;
        border-top: 2px solid #E75925;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        animation: spin 1s linear infinite;
        display: inline-block;
        margin-right: 8px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Enhanced modal styles */
    .modal-content {
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    /* Calendar Styles */
    .calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        background-color: #e2e8f0;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .calendar-day {
        background-color: white;
        min-height: 120px;
        padding: 8px;
        position: relative;
        cursor: pointer;
        transition: all 0.2s ease;
        border: 3px solid transparent;
    }
    
    .calendar-day:hover {
        transform: scale(1.02);
        z-index: 1;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    
    .calendar-day.other-month {
        background-color: #f1f5f9;
        color: #94a3b8;
    }
    
    .calendar-day.today {
        box-shadow: 0 0 0 2px #f59e0b;
    }
    
    /* Updated booking status colors with background and border */
    .calendar-day.no-bookings,
    .calendar-day.one-booking {
        background-color: #dcfce7; /* Light green background */
        border-color: #22c55e; /* Green border */
    }
    
    .calendar-day.two-bookings {
        background-color: #fef3c7; /* Light yellow background */
        border-color: #f59e0b; /* Yellow border */
    }
    
    .calendar-day.three-bookings {
        background-color: #fee2e2; /* Light red background */
        border-color: #ef4444; /* Red border */
        cursor: not-allowed;
    }
    
    .calendar-day.unavailable {
        background-color: #fee2e2;
        border-color: #ef4444;
        cursor: not-allowed;
    }
    
    .booking-slot {
        font-size: 10px;
        padding: 2px 4px;
        margin: 1px 0;
        border-radius: 3px;
        background-color: #e2e8f0;
        color: #475569;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .booking-slot.own-booking {
        background-color: #dbeafe;
        color: #1e40af;
        border: 1px solid #3b82f6;
    }
    
    .calendar-header {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        background-color: #E75925;
        border-radius: 8px 8px 0 0;
        overflow: hidden;
    }
    
    .calendar-header-day {
        background-color: #E75925;
        color: white;
        padding: 12px 8px;
        text-align: center;
        font-weight: 600;
        font-size: 14px;
    }
    
    .date-number {
        font-weight: 600;
        font-size: 16px;
        color: #1f2937;
    }
    
    .booking-count {
        position: absolute;
        top: 4px;
        right: 4px;
        background-color: #E75925;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 600;
    }

    /* Date input restriction styles */
    .form-input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0.7;
    }

    .form-input[type="date"]:disabled::-webkit-calendar-picker-indicator {
        opacity: 0.3;
    }
    
    /* Calendar navigation */
    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 16px;
        padding: 0 8px;
    }
    
    .calendar-nav button {
        background-color: #E75925;
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .calendar-nav button:hover {
        background-color: #d14d1f;
        transform: translateY(-1px);
    }
    
    .calendar-nav button:disabled {
        background-color: #9ca3af;
        cursor: not-allowed;
        transform: none;
    }
</style>
</head>
<body class="bg-gray-100">

<!-- Mobile Menu Button -->
<button id="mobile-menu-btn" class="lg:hidden fixed top-4 left-4 z-30 text-white p-2 rounded-lg shadow-lg" style="background-color:#E75925;">
    <i class="fas fa-bars w-6 h-6"></i>
</button>

<!-- Backdrop -->
<div id="backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden lg:hidden"></div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-gray-200 text-gray-800 flex flex-col justify-between rounded-r-xl z-20 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
    style="box-shadow: 6px 0 12px rgba(0, 0, 0, 0.2);">
    <div>
        <div class="p-6 flex flex-col items-center border-b border-gray-300 shadow-md">
            <img src="logo/logo-border.png" alt="Logo" class="w-26 h-24 rounded-full object-cover mb-2">
            <h1 class="text-xl font-bold text-center">Zaf's Kitchen</h1>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-3">
            <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
                <i class="fas fa-calendar-plus text-[1.8rem]"></i>
                <span class="font-semibold">Book Now</span>
            </a>
            <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
                <i class="fas fa-utensils text-[1.8rem]"></i>
                <span class="font-semibold">Menu Packages</span>
            </a>
            <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
                <i class="fas fa-image text-[1.8rem]"></i>
                <span class="font-semibold">Gallery</span>
            </a>
            <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
                <i class="fas fa-calendar-check text-[1.8rem]"></i>
                <span class="font-semibold">Available Schedule</span>
            </a>
            <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
                <i class="fas fa-user-cog text-[1.8rem]"></i>
                <span class="font-semibold">Profile Settings</span>
            </a>
            <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
                <i class="fas fa-circle-info text-[1.8rem]"></i>
                <span class="font-semibold">About Us</span>
            </a>
        </nav>
    </div>

    <div class="p-4 border-t border-gray-300">
        <button id="signout-btn" class="flex items-center justify-center gap-3 py-2 px-3 rounded text-white font-semibold transition w-full shadow-md hover:opacity-90" style="background-color:#dc2626;">
            <i class="fas fa-sign-out-alt text-[1.6rem]"></i> 
            <span>Sign Out</span>
        </button>
    </div>
</aside>

<main class="lg:ml-64 p-6 lg:p-10 pt-16 lg:pt-10 min-h-screen">
    <!-- Dashboard -->
    <section id="section-dashboard">
        <h2 class="text-3xl font-bold mb-2">Welcome to Zaf's Kitchen Dashboard</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-6"></div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-4" style="width: 380px;">
                <img 
                    src="dashboard/calendar.png" 
                    alt="Book Now" 
                    class="w-[650px] h-[350px] object-cover rounded-[10px] mb-3 transform transition-transform duration-500 ease-in-out hover:scale-105 border border-gray-400"
                    style="box-shadow: 8px 8px 15px rgba(0,0,0,0.3);">
            </div>
        </div>
    </section>

    <!-- Book Now -->
    <section id="section-book" class="hidden">
        <h2 class="text-2xl font-bold mb-2">Book Now</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
        
        <!-- Progress Steps -->
        <div class="bg-white p-4 rounded-lg shadow-lg border-2 border-gray-300 mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div id="step1-indicator" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold" style="background-color:#E75925;">1</div>
                    <span class="ml-2 font-semibold">Basic Info</span>
                </div>
                <div class="flex-1 mx-4 h-0.5 bg-gray-300"></div>
                <div class="flex items-center">
                    <div id="step2-indicator" class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold">2</div>
                    <span id="step2-text" class="ml-2 font-semibold text-gray-600">Event Details</span>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <form id="" method="POST">
            <input type="hidden" name="action" value="book_event">
            
            <!-- Step 1: Basic Information -->
            <div id="booking-step1" class="bg-white p-6 rounded-lg shadow-lg border-2 border-gray-300">
                <h3 class="text-xl font-semibold mb-4">Basic Information</h3>
                <div class="space-y-4">
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Full Name *</label>
                            <input id="fullname" name="full_name" type="text" class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" placeholder="Enter your full name" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Contact Number *</label>
                            <input id="contact" name="contact_number" type="tel" class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" placeholder="e.g. +63 912 345 6789" required>
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Food Package *</label>
                            <select id="package" name="food_package" class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                                <option value="">Select a package</option>
                                <option value="budget">Budget Package - ₱200/person</option>
                                <option value="standard">Standard Package - ₱350/person</option>
                                <option value="premium">Premium Package - ₱500/person</option>
                                <option value="deluxe">Deluxe Package - ₱750/person</option>
                                <option value="luxury">Luxury Package - ₱1000/person</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Type of Event *</label>
                            <select id="eventtype" name="event_type" class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                                <option value="">Select event type</option>
                                <option value="birthday">Birthday Party</option>
                                <option value="wedding">Wedding Reception</option>
                                <option value="corporate">Corporate Event</option>
                                <option value="graduation">Graduation Party</option>
                                <option value="anniversary">Anniversary</option>
                                <option value="debut">Debut/18th Birthday</option>
                                <option value="baptismal">Baptismal</option>
                                <option value="funeral">Funeral Service</option>
                                <option value="others">Others</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block font-semibold mb-1">Event Date *</label>
                            <input id="event-date" name="event_date" type="date" class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Start Time *</label>
                            <input id="start-time" name="start_time" type="time" class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">End Time *</label>
                            <input id="end-time" name="end_time" type="time" class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                        </div>
                    </div>
                    
                    <!-- Time Conflict Warning -->
                    <div id="time-conflict-warning" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                            <span class="text-red-700 font-semibold">Time Conflict Detected</span>
                        </div>
                        <p class="text-red-600 mt-1 text-sm" id="conflict-details"></p>
                    </div>
                    
                    <button type="button" id="next-step1" class="text-white px-6 py-2 rounded shadow-md hover:opacity-90 transition-opacity" style="background-color:#E75925;">Next</button>
                </div>
            </div>

            <!-- Step 2: Event Details -->
            <div id="booking-step2" class="bg-white p-6 rounded-lg shadow-lg border-2 border-gray-300 hidden opacity-0 transform translate-x-full transition-all duration-500">
                <h3 class="text-xl font-semibold mb-4">Event Details & Customization</h3>
                <div class="space-y-6">
                    
                   <!-- Theme Selection -->
              <div>
              <label class="block font-semibold mb-2">Event Theme</label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                  <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="elegant">
                  <i class="fas fa-crown text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Elegant</div>
                  <input type="radio" name="event_theme" value="elegant" class="hidden">
                  </button>
                  <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="rustic">
                  <i class="fas fa-leaf text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Rustic</div>
                  <input type="radio" name="event_theme" value="rustic" class="hidden">
                  </button>
                  <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="modern">
                  <i class="fas fa-star text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Modern</div>
                  <input type="radio" name="event_theme" value="modern" class="hidden">
                  </button>
                  <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="tropical">
                  <i class="fas fa-palette text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Tropical</div>
                  <input type="radio" name="event_theme" value="tropical" class="hidden">
                  </button>
                  <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="vintage">
                  <i class="fas fa-camera-retro text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Vintage</div>
                  <input type="radio" name="event_theme" value="vintage" class="hidden">
                  </button>
                  <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="custom">
                  <i class="fas fa-pencil-alt text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Custom</div>
                  <input type="radio" name="event_theme" value="custom" class="hidden">
                  </button>
              </div>
              <input id="custom-theme" name="custom_theme" type="text" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black hidden" placeholder="Describe your custom theme...">
              </div>

              <!-- Menu Selection -->
              <div>
              <label class="block font-semibold mb-2">Menu Selection</label>
              <div class="border-2 border-gray-300 rounded-lg p-3">
                  <div class="grid grid-cols-3 gap-4 text-sm">
                  <div>
                      <div class="font-medium text-[#E75925] mb-2">Main Dishes</div>
                      <div class="space-y-1">
                      <label class="flex items-center"><input type="checkbox" name="menu_main[]" value="lechon_kawali" class="mr-2 text-[#E75925]"> Lechon Kawali</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_main[]" value="chicken_adobo" class="mr-2 text-[#E75925]"> Chicken Adobo</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_main[]" value="beef_caldereta" class="mr-2 text-[#E75925]"> Beef Caldereta</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_main[]" value="sweet_sour_fish" class="mr-2 text-[#E75925]"> Sweet & Sour Fish</label>
                      </div>
                  </div>
                  
                  <div>
                      <div class="font-medium text-[#E75925] mb-2">Side Dishes</div>
                      <div class="space-y-1">
                      <label class="flex items-center"><input type="checkbox" name="menu_side[]" value="pancit_canton" class="mr-2 text-[#E75925]"> Pancit Canton</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_side[]" value="fried_rice" class="mr-2 text-[#E75925]"> Fried Rice</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_side[]" value="lumpiang_shanghai" class="mr-2 text-[#E75925]"> Lumpiang Shanghai</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_side[]" value="mixed_vegetables" class="mr-2 text-[#E75925]"> Mixed Vegetables</label>
                      </div>
                  </div>

                  <div>
                      <div class="font-medium text-[#E75925] mb-2">Desserts</div>
                      <div class="space-y-1">
                      <label class="flex items-center"><input type="checkbox" name="menu_dessert[]" value="leche_flan" class="mr-2 text-[#E75925]"> Leche Flan</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_dessert[]" value="halo_halo" class="mr-2 text-[#E75925]"> Halo-Halo</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_dessert[]" value="buko_pie" class="mr-2 text-[#E75925]"> Buko Pie</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_dessert[]" value="ice_cream" class="mr-2 text-[#E75925]"> Ice Cream</label>
                      </div>
                  </div>
                  </div>
              </div>
              </div>

                    <div class="flex gap-4">
                        <button type="button" id="back-step2" class="bg-gray-300 text-gray-700 px-6 py-2 rounded shadow-md hover:bg-gray-400 transition-colors">Back</button>
                        <button type="submit" id="submit-booking" class="text-white px-6 py-2 rounded shadow-md hover:opacity-90 transition-opacity" style="background-color:#E75925;">Submit Booking</button>
                    </div>
                </div>
            </div>
        </form>
    </section>

    <!-- Available Schedule Section with Calendar -->
    <section id="section-schedule" class="hidden">
        <h2 class="text-2xl font-bold mb-2">Available Schedule</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-6"></div>
        
        <!-- Calendar Container -->
        <div class="bg-white rounded-lg shadow-lg border-2 border-gray-300 p-6">
            <!-- Calendar Navigation -->
            <div class="calendar-nav">
                <button id="prev-month" class="flex items-center gap-2">
                    <i class="fas fa-chevron-left"></i>
                    Previous
                </button>
                
                <h3 id="calendar-title" class="text-xl font-bold text-gray-800">January 2025</h3>
                
                <button id="next-month" class="flex items-center gap-2">
                    Next
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
            
            <!-- Calendar Legend -->
            <div class="mb-4 flex flex-wrap gap-4 text-sm">
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-green-200 border-2 border-green-500 rounded"></div>
                    <span>Available (0-1 bookings)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-yellow-200 border-2 border-yellow-500 rounded"></div>
                    <span>Busy (2 bookings)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-red-200 border-2 border-red-500 rounded"></div>
                    <span>Fully Booked (3 bookings)</span>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-4 h-4 bg-blue-200 border-2 border-blue-700 rounded"></div>
                    <span>Your Bookings</span>
                </div>
            </div>
            
            <!-- Calendar Header -->
            <div class="calendar-header">
                <div class="calendar-header-day">Sun</div>
                <div class="calendar-header-day">Mon</div>
                <div class="calendar-header-day">Tue</div>
                <div class="calendar-header-day">Wed</div>
                <div class="calendar-header-day">Thu</div>
                <div class="calendar-header-day">Fri</div>
                <div class="calendar-header-day">Sat</div>
            </div>
            
            <!-- Calendar Grid -->
            <div id="calendar-grid" class="calendar">
                <!-- Calendar days will be dynamically generated here -->
            </div>
        </div>
    </section>

    <!-- Other sections (hidden) -->
    <section id="section-menu" class="hidden">
        <h2 class="text-2xl font-bold mb-2">Menu Packages</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
        <p>Display menu packages here...</p>
    </section>
    
    <section id="section-gallery" class="hidden">
        <h2 class="text-2xl font-bold mb-2">Gallery</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
        <p>Gallery content here...</p>
    </section>
    
    <section id="section-settings" class="hidden">
        <h2 class="text-2xl font-bold mb-2">Profile Settings</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
        <p>Settings form here...</p>
    </section>
    
    <section id="section-about" class="hidden">
        <h2 class="text-2xl font-bold mb-2">About Us</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
        <p>About Zaf's Kitchen...</p>
    </section>
</main>

<!-- Sign Out Modal -->
<div id="signout-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="modal-content bg-white p-6 rounded-lg shadow-lg w-80 text-center">
        <h3 class="text-lg font-semibold mb-4">Are you sure you want to sign out?</h3>
        <div class="flex justify-center gap-4">
            <button id="cancel-signout" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 w-24">NO</button>
            <button id="confirm-signout" class="px-4 py-2 rounded text-white w-24" style="background-color:#E75925;">YES</button>
        </div>
    </div>
</div>

<!-- Enhanced Success/Error Modal -->
<div id="message-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="modal-content bg-white p-6 rounded-lg shadow-lg w-96 text-center">
        <div id="message-icon" class="text-4xl mb-4"></div>
        <h3 id="message-title" class="text-lg font-semibold mb-2"></h3>
        <p id="message-text" class="text-gray-600 mb-4"></p>
        <div id="message-actions" class="flex justify-center gap-3">
            <button id="message-ok" class="px-6 py-2 rounded text-white" style="background-color:#E75925;">OK</button>
        </div>
    </div>
</div>

<!-- Booking Details Modal -->
<div id="booking-details-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="modal-content bg-white p-6 rounded-lg shadow-lg w-96 max-h-96 overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Bookings for <span id="selected-date"></span></h3>
            <button id="close-booking-details" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="booking-details-content">
            <!-- Booking details will be populated here -->
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Global variables
let currentStep = 1;
let conflictCheckTimeout = null;
let currentMonth = new Date().getMonth() + 1;
let currentYear = 2025;
let calendarData = {};

// Mobile menu functionality
const mobileMenuBtn = document.getElementById('mobile-menu-btn');
const sidebar = document.getElementById('sidebar');
const backdrop = document.getElementById('backdrop');

function toggleSidebar() {
    sidebar.classList.toggle('-translate-x-full');
    backdrop.classList.toggle('hidden');
}

mobileMenuBtn.addEventListener('click', toggleSidebar);
backdrop.addEventListener('click', toggleSidebar);

// Updated navigation functionality
const navMap = {
    "Book Now": "section-book",
    "Menu Packages": "section-menu",
    "Gallery": "section-gallery",
    "Available Schedule": "section-schedule",
    "Profile Settings": "section-settings",
    "About Us": "section-about"
};

function hideAllSections() {
    document.querySelectorAll("main section").forEach(sec => sec.classList.add("hidden"));
}

const navLinks = document.querySelectorAll("nav a");
navLinks.forEach(link => {
    link.addEventListener("click", e => {
        e.preventDefault();
        hideAllSections();
        navLinks.forEach(l => l.classList.remove("active-nav"));
        link.classList.add("active-nav");
        const text = link.innerText.trim();
        const sectionId = navMap[text];
        if (sectionId) {
            document.getElementById(sectionId).classList.remove("hidden");
            
            if (sectionId === 'section-book') {
                resetBookingForm();
            } else if (sectionId === 'section-schedule') {
                loadCalendar();
            }
        }
        document.getElementById("section-dashboard").classList.add("hidden");
        if (window.innerWidth < 1024) toggleSidebar();
    });
});

// Initialize
hideAllSections();
document.getElementById("section-dashboard").classList.remove("hidden");

// Enhanced date input setup
function setupDateInput() {
    const eventDateInput = document.getElementById('event-date');
    if (eventDateInput) {
        // Set minimum date (3 days from now, but in 2025)
        const minDate = new Date();
        minDate.setDate(minDate.getDate() + 3);
        const minDateStr = `2025-${String(minDate.getMonth() + 1).padStart(2, '0')}-${String(minDate.getDate()).padStart(2, '0')}`;
        eventDateInput.min = minDateStr;
        
        // Set maximum date to end of 2025
        eventDateInput.max = '2025-12-31';
        
        // Force year to 2025 and validate date only when user finishes (blur event)
        eventDateInput.addEventListener('blur', function() {
            if (this.value && this.value.length === 10) { // Only validate if complete date (YYYY-MM-DD)
                const parts = this.value.split('-');
                if (parts.length === 3 && parts[0] !== '2025') {
                    this.value = `2025-${parts[1]}-${parts[2]}`;
                }
                
                // Check if selected date is in the past
                const selectedDate = new Date(this.value);
                const minAllowedDate = new Date();
                minAllowedDate.setDate(minAllowedDate.getDate() + 3);
                
                if (selectedDate < minAllowedDate) {
                    showMessage('error', 'Invalid Date', 'Please select a date that is at least 3 days from today.');
                    this.value = '';
                    this.classList.add('border-red-500');
                    return false;
                } else {
                    this.classList.remove('border-red-500');
                }
            }
        });
        
        // Still keep change event but without validation - just for year correction
        eventDateInput.addEventListener('change', function() {
            if (this.value && this.value.length === 10) {
                const parts = this.value.split('-');
                if (parts.length === 3 && parts[0] !== '2025') {
                    this.value = `2025-${parts[1]}-${parts[2]}`;
                }
            }
        });
        
        // Remove input validation to allow user to finish typing
    }
}

// Function to convert 24-hour to 12-hour format
function formatTimeTo12Hour(time24) {
    if (!time24) return '';
    
    const [hours, minutes] = time24.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    
    return `${hour12}:${minutes} ${ampm}`;
}

// Function to convert 12-hour to 24-hour format
function formatTimeTo24Hour(time12) {
    if (!time12) return '';
    
    const [time, period] = time12.split(' ');
    const [hours, minutes] = time.split(':');
    let hour = parseInt(hours);
    
    if (period === 'PM' && hour !== 12) {
        hour += 12;
    } else if (period === 'AM' && hour === 12) {
        hour = 0;
    }
    
    return `${String(hour).padStart(2, '0')}:${minutes}`;
}

// Setup 12-hour time inputs
function setup12HourTimeInputs() {
    const startTimeInput = document.getElementById('start-time');
    const endTimeInput = document.getElementById('end-time');
    
    if (startTimeInput && endTimeInput) {
        // Replace time inputs with text inputs and dropdowns
        replaceTimeInputWith12Hour(startTimeInput, 'start_time');
        replaceTimeInputWith12Hour(endTimeInput, 'end_time');
    }
}

function replaceTimeInputWith12Hour(originalInput, fieldName) {
    const container = document.createElement('div');
    container.className = 'flex gap-1';
    
    // Hour select
    const hourSelect = document.createElement('select');
    hourSelect.className = 'form-input border-2 border-gray-300 rounded px-2 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black flex-1';
    hourSelect.innerHTML = '<option value="">Hour</option>';
    for (let i = 1; i <= 12; i++) {
        hourSelect.innerHTML += `<option value="${i}">${i}</option>`;
    }
    
    // Minute select
    const minuteSelect = document.createElement('select');
    minuteSelect.className = 'form-input border-2 border-gray-300 rounded px-2 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black flex-1';
    minuteSelect.innerHTML = '<option value="">Min</option>';
    for (let i = 0; i < 60; i += 15) {
        const min = String(i).padStart(2, '0');
        minuteSelect.innerHTML += `<option value="${min}">${min}</option>`;
    }
    
    // AM/PM select
    const periodSelect = document.createElement('select');
    periodSelect.className = 'form-input border-2 border-gray-300 rounded px-2 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black flex-1';
    periodSelect.innerHTML = `
        <option value="">AM/PM</option>
        <option value="AM">AM</option>
        <option value="PM">PM</option>
    `;
    
    // Hidden input to store 24-hour format
    const hiddenInput = document.createElement('input');
    hiddenInput.type = 'hidden';
    hiddenInput.name = fieldName;
    hiddenInput.id = originalInput.id;
    
    // Update hidden input when selects change
    function updateHiddenInput() {
        const hour = hourSelect.value;
        const minute = minuteSelect.value;
        const period = periodSelect.value;
        
        if (hour && minute && period) {
            const time12 = `${hour}:${minute} ${period}`;
            hiddenInput.value = formatTimeTo24Hour(time12);
            
            // Trigger change event for validation
            hiddenInput.dispatchEvent(new Event('change'));
        } else {
            hiddenInput.value = '';
        }
    }
    
    hourSelect.addEventListener('change', updateHiddenInput);
    minuteSelect.addEventListener('change', updateHiddenInput);
    periodSelect.addEventListener('change', updateHiddenInput);
    
    // Build container
    container.appendChild(hourSelect);
    container.appendChild(minuteSelect);
    container.appendChild(periodSelect);
    container.appendChild(hiddenInput);
    
    // Replace original input
    originalInput.parentNode.replaceChild(container, originalInput);
}

// Step navigation
function showStep(step) {
    const step1 = document.getElementById('booking-step1');
    const step2 = document.getElementById('booking-step2');
    const step1Indicator = document.getElementById('step1-indicator');
    const step2Indicator = document.getElementById('step2-indicator');
    const step2Text = document.getElementById('step2-text');

    if (step === 1) {
        step1.classList.remove('hidden', 'opacity-0', 'transform', 'translate-x-full');
        step2.classList.add('hidden', 'opacity-0', 'transform', 'translate-x-full');
        
        step1Indicator.style.backgroundColor = '#E75925';
        step1Indicator.classList.remove('bg-gray-300');
        step1Indicator.classList.add('text-white');
        
        step2Indicator.style.backgroundColor = '';
        step2Indicator.classList.add('bg-gray-300');
        step2Indicator.classList.remove('text-white');
        step2Indicator.classList.add('text-gray-600');
        step2Text.classList.add('text-gray-600');
        step2Text.classList.remove('text-black');
        
        currentStep = 1;
    } else if (step === 2) {
        step1.classList.add('hidden');
        step2.classList.remove('hidden');
        
        setTimeout(() => {
            step2.classList.remove('opacity-0', 'transform', 'translate-x-full');
        }, 50);
        
        step1Indicator.style.backgroundColor = '#22c55e';
        step2Indicator.style.backgroundColor = '#E75925';
        step2Indicator.classList.remove('bg-gray-300', 'text-gray-600');
        step2Indicator.classList.add('text-white');
        step2Text.classList.remove('text-gray-600');
        step2Text.classList.add('text-black');
        
        currentStep = 2;
    }
}

// Enhanced validation
function validateStep1() {
    const requiredFields = [
        'full_name', 'contact_number', 'food_package', 'event_type',
        'event_date', 'start_time', 'end_time'
    ];
    
    let isValid = true;
    let firstInvalidField = null;
    
    requiredFields.forEach(fieldName => {
        const field = document.querySelector(`[name="${fieldName}"]`);
        if (field && !field.value.trim()) {
            isValid = false;
            if (!firstInvalidField) firstInvalidField = field;
            field.classList.add('border-red-500');
        } else if (field) {
            field.classList.remove('border-red-500');
        }
    });
    
    // Time validation
    const startTime = document.querySelector('[name="start_time"]').value;
    const endTime = document.querySelector('[name="end_time"]').value;
    
    if (startTime && endTime) {
        if (startTime >= endTime) {
            showMessage('error', 'Invalid Time', 'End time must be after start time.');
            return false;
        }
        
        const start = new Date('2000-01-01 ' + startTime);
        const end = new Date('2000-01-01 ' + endTime);
        const duration = (end - start) / (1000 * 60 * 60);
        
        if (duration < 4) {
            showMessage('error', 'Invalid Duration', 'Event duration must be at least 4 hours.');
            return false;
        }
        
        if (duration > 8) {
            showMessage('error', 'Invalid Duration', 'Event duration cannot exceed 8 hours.');
            return false;
        }
    }
    
    if (!isValid) {
        showMessage('error', 'Required Fields', 'Please fill in all required fields.');
        if (firstInvalidField) firstInvalidField.focus();
        return false;
    }
    
    return new Promise((resolve) => {
        checkTimeConflictSync(resolve);
    });
}

// Conflict checking
function checkTimeConflictSync(callback) {
    const eventDate = document.querySelector('[name="event_date"]').value;
    const startTime = document.querySelector('[name="start_time"]').value;
    const endTime = document.querySelector('[name="end_time"]').value;
    
    if (!eventDate || !startTime || !endTime) {
        callback(true);
        return;
    }
    
    const url = window.location.pathname + `?action=check_conflict&event_date=${encodeURIComponent(eventDate)}&start_time=${encodeURIComponent(startTime)}&end_time=${encodeURIComponent(endTime)}`;
    
    fetch(url)
        .then(r => {
            if (!r.ok) throw new Error('Network response was not ok');
            return r.json();
        })
        .then(data => {
            if (data.conflict) {
                showConflictWarning(data.existing_slots || '');
                callback(false);
            } else {
                hideConflictWarning();
                callback(true);
            }
        })
        .catch(err => {
            console.error('Conflict check error:', err);
            callback(true);
        });
}

function showConflictWarning(existingSlots) {
    const warningDiv = document.getElementById('time-conflict-warning');
    const conflictDetails = document.getElementById('conflict-details');
    
    conflictDetails.innerHTML = `Your selected time conflicts with existing bookings: <strong>${existingSlots}</strong><br>Please choose a different time slot to proceed.`;
    warningDiv.classList.remove('hidden');
    warningDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

function hideConflictWarning() {
    document.getElementById('time-conflict-warning').classList.add('hidden');
}

function resetBookingForm() {
    // Find the actual form element - FIXED selector
    const form = document.querySelector('form[method="POST"]');
    if (form) {
        form.reset();
    }
    
    showStep(1);
    
    document.querySelectorAll('.theme-btn').forEach(btn => {
        btn.classList.remove('selected');
    });
    
    document.getElementById('custom-theme').classList.add('hidden');
    hideConflictWarning();
    
    document.querySelectorAll('input, select').forEach(field => {
        field.classList.remove('border-red-500');
    });
    
    setupDateInput();
}

// Calendar functionality
function loadCalendar() {
    updateCalendarTitle();
    
    const url = window.location.pathname + `?action=get_calendar_data&month=${currentMonth}&year=${currentYear}`;
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            calendarData = data;
            generateCalendar();
        })
        .catch(error => {
            console.error('Error loading calendar:', error);
        });
}

function updateCalendarTitle() {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    document.getElementById('calendar-title').textContent = `${months[currentMonth - 1]} ${currentYear}`;
    
    const prevBtn = document.getElementById('prev-month');
    const nextBtn = document.getElementById('next-month');
    
    prevBtn.disabled = (currentYear === 2025 && currentMonth === 1);
    nextBtn.disabled = (currentYear === 2025 && currentMonth === 12);
}

// UPDATED generateCalendar function with PAST indicator
function generateCalendar() {
    const calendarGrid = document.getElementById('calendar-grid');
    calendarGrid.innerHTML = '';
    
    const firstDay = new Date(currentYear, currentMonth - 1, 1);
    const lastDay = new Date(currentYear, currentMonth, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    const today = new Date();
    const todayStr = today.toISOString().split('T')[0];
    
    // Get minimum date (3 days from today)
    const minDate = new Date();
    minDate.setDate(minDate.getDate() + 3);
    const minDateStr = minDate.toISOString().split('T')[0];
    
    for (let i = 0; i < startingDayOfWeek; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.classList.add('calendar-day', 'other-month');
        calendarGrid.appendChild(emptyDay);
    }
    
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.classList.add('calendar-day');
        
        const dateStr = `${currentYear}-${String(currentMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        const isToday = dateStr === todayStr;
        const isPastDate = dateStr < minDateStr;
        
        if (isToday) {
            dayElement.classList.add('today');
        }
        
        // Mark past dates as unavailable with reduced opacity
        if (isPastDate) {
            dayElement.classList.add('unavailable');
            dayElement.style.backgroundColor = '#e5e7eb';
            dayElement.style.borderColor = '#9ca3af';
            dayElement.style.color = '#6b7280';
            dayElement.style.cursor = 'not-allowed';
            dayElement.style.position = 'relative';
            dayElement.style.opacity = '0.6'; // Reduced opacity for past dates
        }
        
        const bookingInfo = calendarData[dateStr];
        if (bookingInfo && !isPastDate) {
            const count = bookingInfo.count;
            if (count === 1) {
                dayElement.classList.add('one-booking');
            } else if (count === 2) {
                dayElement.classList.add('two-bookings');
            } else if (count >= 3) {
                dayElement.classList.add('three-bookings', 'unavailable');
                dayElement.style.position = 'relative';
                dayElement.style.opacity = '0.6'; // Reduced opacity for fully booked dates
            }
            
            const countIndicator = document.createElement('div');
            countIndicator.classList.add('booking-count');
            countIndicator.textContent = count;
            dayElement.appendChild(countIndicator);
            
            bookingInfo.bookings.forEach(booking => {
                const slotElement = document.createElement('div');
                slotElement.classList.add('booking-slot');
                if (booking.is_own_booking) {
                    slotElement.classList.add('own-booking');
                }
                
                // Lower opacity for time slots if fully booked
                if (count >= 3) {
                    slotElement.style.opacity = '0.4';
                }
                
                // Convert to 12-hour format for display
                const startTime12 = formatTimeTo12Hour(booking.start_time.substring(0, 5));
                const endTime12 = formatTimeTo12Hour(booking.end_time.substring(0, 5));
                const timeStr = `${startTime12}-${endTime12}`;
                slotElement.textContent = timeStr;
                dayElement.appendChild(slotElement);
            });
            
            // Add "UNAVAILABLE" overlay if fully booked
            if (count >= 3) {
                const unavailableOverlay = document.createElement('div');
                unavailableOverlay.style.cssText = `
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    background-color: rgba(239, 68, 68, 0.9);
                    color: white;
                    padding: 4px 8px;
                    border-radius: 4px;
                    font-size: 10px;
                    font-weight: bold;
                    z-index: 10;
                    text-align: center;
                    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
                `;
                unavailableOverlay.textContent = 'UNAVAILABLE';
                dayElement.appendChild(unavailableOverlay);
            }
            
            // Only allow clicking if not past date and not fully booked
            if (!isPastDate && count < 3) {
                dayElement.addEventListener('click', () => showBookingDetails(dateStr, bookingInfo));
            }
        } else if (!isPastDate) {
            dayElement.classList.add('no-bookings');
        } else if (isPastDate && bookingInfo) {
            // Past dates with bookings - show bookings but with reduced opacity
            const count = bookingInfo.count;
            const countIndicator = document.createElement('div');
            countIndicator.classList.add('booking-count');
            countIndicator.textContent = count;
            countIndicator.style.opacity = '0.5';
            dayElement.appendChild(countIndicator);
            
            bookingInfo.bookings.forEach(booking => {
                const slotElement = document.createElement('div');
                slotElement.classList.add('booking-slot');
                if (booking.is_own_booking) {
                    slotElement.classList.add('own-booking');
                }
                slotElement.style.opacity = '0.3';
                
                // Convert to 12-hour format for display
                const startTime12 = formatTimeTo12Hour(booking.start_time.substring(0, 5));
                const endTime12 = formatTimeTo12Hour(booking.end_time.substring(0, 5));
                const timeStr = `${startTime12}-${endTime12}`;
                slotElement.textContent = timeStr;
                dayElement.appendChild(slotElement);
            });
        }
        
        // Add "PAST" overlay for past dates
        if (isPastDate) {
            const pastOverlay = document.createElement('div');
            pastOverlay.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background-color: rgba(107, 114, 128, 0.9);
                color: white;
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 10px;
                font-weight: bold;
                z-index: 10;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            `;
            pastOverlay.textContent = 'PAST';
            dayElement.appendChild(pastOverlay);
        }
        
        const dateNumber = document.createElement('div');
        dateNumber.classList.add('date-number');
        dateNumber.textContent = day;
        dayElement.insertBefore(dateNumber, dayElement.firstChild);
        
        calendarGrid.appendChild(dayElement);
    }
}

function showBookingDetails(dateStr, bookingInfo) {
    const modal = document.getElementById('booking-details-modal');
    const selectedDate = document.getElementById('selected-date');
    const content = document.getElementById('booking-details-content');
    
    selectedDate.textContent = new Date(dateStr).toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
    
    content.innerHTML = '';
    
    bookingInfo.bookings.forEach((booking) => {
        const bookingElement = document.createElement('div');
        bookingElement.classList.add('mb-4', 'p-3', 'border', 'rounded-lg');
        
        if (booking.is_own_booking) {
            bookingElement.classList.add('border-blue-300', 'bg-blue-50');
        } else {
            bookingElement.classList.add('border-gray-300', 'bg-gray-50');
        }
        
        // Convert to 12-hour format for display in modal
        const startTime12 = formatTimeTo12Hour(booking.start_time.substring(0, 5));
        const endTime12 = formatTimeTo12Hour(booking.end_time.substring(0, 5));
        const timeStr = `${startTime12} - ${endTime12}`;
        
        bookingElement.innerHTML = `
            <div class="flex justify-between items-start">
                <div>
                    <div class="font-semibold text-gray-800">${timeStr}</div>
                    <div class="text-sm text-gray-600">${booking.event_type}</div>
                    ${booking.is_own_booking ? `<div class="text-sm text-blue-600 font-medium">Your booking: ${booking.full_name}</div>` : '<div class="text-sm text-gray-500">Other user\'s booking</div>'}
                    <div class="text-xs text-gray-500 mt-1">Status: ${booking.booking_status}</div>
                </div>
                ${booking.is_own_booking ? '<div class="text-blue-500"><i class="fas fa-user"></i></div>' : '<div class="text-gray-400"><i class="fas fa-clock"></i></div>'}
            </div>
        `;
        
        content.appendChild(bookingElement);
    });
    
    modal.classList.remove('hidden');
}

// Calendar navigation
document.getElementById('prev-month').addEventListener('click', () => {
    if (currentMonth === 1) {
        currentMonth = 12;
        currentYear--;
    } else {
        currentMonth--;
    }
    
    if (currentYear < 2025) {
        currentYear = 2025;
        currentMonth = 1;
    }
    
    loadCalendar();
});

document.getElementById('next-month').addEventListener('click', () => {
    if (currentMonth === 12) {
        currentMonth = 1;
        currentYear++;
    } else {
        currentMonth++;
    }
    
    if (currentYear > 2025) {
        currentYear = 2025;
        currentMonth = 12;
    }
    
    loadCalendar();
});

// Event listeners for step navigation
document.getElementById('next-step1').addEventListener('click', function() {
    const validation = validateStep1();
    if (validation instanceof Promise) {
        validation.then(isValid => {
            if (isValid) {
                showStep(2);
            }
        });
    } else if (validation) {
        showStep(2);
    }
});

document.getElementById('back-step2').addEventListener('click', function() {
    showStep(1);
});

// Theme selection
document.querySelectorAll('.theme-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();
        
        document.querySelectorAll('.theme-btn').forEach(b => {
            b.classList.remove('selected');
        });
        
        this.classList.add('selected');
        
        const theme = this.dataset.theme;
        const radioBtn = this.querySelector('input[type="radio"]');
        
        if (radioBtn) {
            radioBtn.checked = true;
        }
        
        const customInput = document.getElementById('custom-theme');
        if (theme === 'custom') {
            customInput.classList.remove('hidden');
            customInput.focus();
        } else {
            customInput.classList.add('hidden');
            customInput.value = '';
        }
    });
});

// MAIN BOOKING FORM SUBMISSION - COMPLETELY REWRITTEN AND FIXED
document.addEventListener('DOMContentLoaded', function() {
    // Wait for DOM to be fully loaded before attaching form listener
    const bookingForm = document.querySelector('form[method="POST"]');
    
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            console.log('Form submission started');
            
            // Create FormData object from the actual form
            const formData = new FormData(this);
            
            // Ensure action is set
            formData.set('action', 'book_event');
            
            // Get selected theme manually since radio buttons might not be in form properly
            const selectedTheme = document.querySelector('.theme-btn.selected');
            if (selectedTheme) {
                formData.set('event_theme', selectedTheme.dataset.theme);
            }
            
            // Collect selected menus manually
            const selectedMenus = [];
            document.querySelectorAll('input[name^="menu_"]:checked').forEach(checkbox => {
                selectedMenus.push(checkbox.value);
            });
            formData.set('selected_menus', selectedMenus.join(','));
            
            // Debug: Log all form data
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
            }
            
            const submitBtn = document.getElementById('submit-booking');
            const originalText = submitBtn.textContent;
            
            // Show loading state
            submitBtn.innerHTML = '<span class="loading-spinner"></span>Submitting...';
            submitBtn.disabled = true;
            
            // Submit form
            fetch(window.location.pathname, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response status:', response.status);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.text();
            })
            .then(text => {
                console.log('Response text:', text);
                try {
                    const data = JSON.parse(text);
                    console.log('Parsed data:', data);
                    
                    if (data.success) {
                        showMessage('success', 'Booking Submitted!', data.message);
                        resetBookingForm();
                        
                        // Refresh calendar if visible
                        if (!document.getElementById('section-schedule').classList.contains('hidden')) {
                            loadCalendar();
                        }
                    } else {
                        if (data.clear_time) {
                            showMessage('error', 'Time Conflict', data.message);
                            document.querySelector('[name="start_time"]').value = '';
                            document.querySelector('[name="end_time"]').value = '';
                            showStep(1); // Go back to step 1 to fix times
                        } else {
                            showMessage('error', 'Booking Failed', data.message);
                        }
                    }
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response text was:', text);
                    showMessage('error', 'Server Error', 'Invalid response from server. Please try again.');
                }
            })
            .catch(error => {
                console.error('Fetch error:', error);
                showMessage('error', 'Network Error', 'Please check your connection and try again.');
            })
            .finally(() => {
                // Restore button state
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        });
    } else {
        console.error('Booking form not found!');
    }
});

// Enhanced message modal
function showMessage(type, title, message) {
    const modal = document.getElementById('message-modal');
    const icon = document.getElementById('message-icon');
    const titleEl = document.getElementById('message-title');
    const textEl = document.getElementById('message-text');
    
    if (type === 'success') {
        icon.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
    } else if (type === 'warning') {
        icon.innerHTML = '<i class="fas fa-exclamation-triangle text-yellow-500"></i>';
    } else {
        icon.innerHTML = '<i class="fas fa-exclamation-triangle text-red-500"></i>';
    }
    
    titleEl.textContent = title;
    textEl.innerHTML = message;
    modal.classList.remove('hidden');
}

// Time conflict checking
function checkTimeConflict() {
    const eventDate = document.querySelector('[name="event_date"]').value;
    const startTime = document.querySelector('[name="start_time"]').value;
    const endTime = document.querySelector('[name="end_time"]').value;
    
    if (!eventDate || !startTime || !endTime) {
        hideConflictWarning();
        return;
    }
    
    if (conflictCheckTimeout) {
        clearTimeout(conflictCheckTimeout);
    }
    
    conflictCheckTimeout = setTimeout(() => {
        const url = window.location.pathname + `?action=check_conflict&event_date=${encodeURIComponent(eventDate)}&start_time=${encodeURIComponent(startTime)}&end_time=${encodeURIComponent(endTime)}`;
        
        fetch(url)
            .then(r => {
                if (!r.ok) throw new Error('Network response was not ok');
                return r.json();
            })
            .then(data => {
                if (data.conflict) {
                    showConflictWarning(data.existing_slots || '');
                } else {
                    hideConflictWarning();
                }
            })
            .catch(err => console.error('Conflict check error:', err));
    }, 500);
}

function setupTimeConflictChecking() {
    const eventDateInput = document.querySelector('[name="event_date"]');
    const startTimeInput = document.querySelector('[name="start_time"]');
    const endTimeInput = document.querySelector('[name="end_time"]');
    
    if (eventDateInput && startTimeInput && endTimeInput) {
        [eventDateInput, startTimeInput, endTimeInput].forEach(input => {
            input.addEventListener('change', checkTimeConflict);
            input.addEventListener('input', checkTimeConflict);
        });
    }
}

// Sign out functionality
document.getElementById('signout-btn').addEventListener('click', () => {
    document.getElementById('signout-modal').classList.remove('hidden');
});

document.getElementById('cancel-signout').addEventListener('click', () => {
    document.getElementById('signout-modal').classList.add('hidden');
});

document.getElementById('confirm-signout').addEventListener('click', () => {
    window.location.href = 'auth.php';
});

// Modal event listeners
document.getElementById('message-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

document.getElementById('booking-details-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

document.getElementById('signout-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

document.getElementById('close-booking-details').addEventListener('click', () => {
    document.getElementById('booking-details-modal').classList.add('hidden');
});

// Message modal OK button
document.addEventListener('click', function(e) {
    if (e.target && e.target.id === 'message-ok') {
        document.getElementById('message-modal').classList.add('hidden');
    }
});

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    setupDateInput();
    setup12HourTimeInputs(); // Add 12-hour time setup
    setupTimeConflictChecking();
    showStep(1);
    
    document.querySelectorAll('section').forEach(section => {
        section.style.transition = 'opacity 0.3s ease-in-out';
    });
    
    const now = new Date();
    currentMonth = now.getMonth() + 1;
    currentYear = 2025;
    
    console.log('DOM loaded and initialized');
});
</script>
</body>
</html>