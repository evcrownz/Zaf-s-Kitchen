        <?php 
        session_start();
        require_once 'connection.php';

        // Handle booking submission
        if ($_POST && isset($_POST['action']) && $_POST['action'] === 'book_event') {
            header('Content-Type: application/json');
            
            try {
                // Check if user is logged in
                if (!isset($_SESSION['user_id'])) {
                    echo json_encode(['success' => false, 'message' => 'Please log in to make a booking.', 'redirect' => 'login.php']);
                    exit;
                }
                
                $user_id = $_SESSION['user_id']; // Get user ID from session
                
                // Get and validate form data
                $full_name = trim($_POST['full_name'] ?? '');
                $contact_number = trim($_POST['contact_number'] ?? '');
                $celebrant_name = trim($_POST['celebrant_name'] ?? '');
                $guest_count = trim($_POST['guest_count'] ?? '');
                $celebrant_age = trim($_POST['celebrant_age'] ?? '');
                $food_package = trim($_POST['food_package'] ?? '');
                $event_type = trim($_POST['event_type'] ?? '');
                $location = trim($_POST['location'] ?? '');
                $event_date = trim($_POST['event_date'] ?? '');
                $start_time = trim($_POST['start_time'] ?? '');
                $end_time = trim($_POST['end_time'] ?? '');
                $event_theme = trim($_POST['event_theme'] ?? '');
                $custom_theme = trim($_POST['custom_theme'] ?? '');
                $theme_suggestions = trim($_POST['theme_suggestions'] ?? '');
                $selected_menus = trim($_POST['selected_menus'] ?? '');
                $total_price = floatval($_POST['total_price'] ?? '0');
                
                // Basic validation
                if (empty($full_name) || empty($contact_number) || empty($celebrant_name) || 
                    empty($guest_count) || empty($food_package) || empty($event_type) || 
                    empty($location) || empty($event_date) || empty($start_time) || empty($end_time)) {
                    echo json_encode(['success' => false, 'message' => 'All required fields must be filled.']);
                    exit;
                }
                
                // Validate guest count
                if (!is_numeric($guest_count) || $guest_count < 1) {
                    echo json_encode(['success' => false, 'message' => 'Please enter a valid number of guests.']);
                    exit;
                }
                
                // Validate age if birthday event
                if ($event_type === 'birthday' && (empty($celebrant_age) || !is_numeric($celebrant_age) || $celebrant_age < 1)) {
                    echo json_encode(['success' => false, 'message' => 'Please enter a valid age for the celebrant.']);
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
                $checkStmt->execute([$event_date]);
                $result = $checkStmt->fetch();
                
                if ($result['count'] >= 3) {
                    echo json_encode(['success' => false, 'message' => 'This date is fully booked. Maximum 3 events per day allowed.']);
                    exit;
                }
                    
                // Convert selected_menus to JSON format for JSONB column
                $selected_menus_json = !empty($selected_menus) ? json_encode(explode(',', $selected_menus)) : null;
                
                // Insert booking with user_id and total_price
                $insertStmt = $conn->prepare("INSERT INTO bookings (
                    user_id, full_name, contact_number, celebrant_name, guest_count, celebrant_age, 
                    food_package, event_type, location, event_date, start_time, end_time, 
                    event_theme, custom_theme, theme_suggestions, selected_menus, total_price, booking_status, created_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)");

                $result = $insertStmt->execute([
                    $user_id,
                    $full_name, 
                    $contact_number, 
                    $celebrant_name, 
                    $guest_count, 
                    ($celebrant_age ?: null), 
                    $food_package, 
                    $event_type, 
                    $location, 
                    $event_date, 
                    $start_time, 
                    $end_time, 
                    $event_theme, 
                    $custom_theme, 
                    $theme_suggestions, 
                    $selected_menus_json,
                    $total_price,
                    'pending'
                ]);

                if ($result) {
                    $booking_id = $conn->lastInsertId();
                    error_log("New booking submitted - ID: $booking_id, Customer: $full_name, User ID: $user_id, Total: $total_price");
                    
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Booking submitted successfully! Your booking details have been sent for admin approval.', 
                        'booking_id' => $booking_id,
                        'total_price' => $total_price
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Database error occurred.']);
                }
                        
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
            exit;
        }

        // Get user's own bookings only
        if (isset($_GET['action']) && $_GET['action'] === 'get_my_bookings') {
            header('Content-Type: application/json');
            
            // Check if user is logged in
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['error' => 'Not logged in']);
                exit;
            }
            
            $user_id = $_SESSION['user_id'];
            
            $stmt = $conn->prepare("SELECT 
                id,
                full_name,
                celebrant_name,
                guest_count,
                celebrant_age,
                event_type,
                location,
                event_date,
                start_time,
                end_time,
                food_package,
                event_theme,
                custom_theme,
                theme_suggestions,
                total_price,
                booking_status,
                rejection_reason,
                created_at,
                updated_at
                FROM bookings 
                WHERE user_id = ?
                ORDER BY event_date DESC, created_at DESC
            ");
            $stmt->execute([$user_id]);
            $bookings = $stmt->fetchAll();
            
            echo json_encode($bookings);
            exit;
        }

        // Get all bookings for admin (add admin check)
        if (isset($_GET['action']) && $_GET['action'] === 'get_all_bookings') {
            header('Content-Type: application/json');
            
            // Check if user is admin
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                echo json_encode(['error' => 'Admin access required']);
                exit;
            }
            
            $stmt = $conn->prepare("SELECT 
                b.id,
                b.full_name,
                b.celebrant_name,
                b.guest_count,
                b.celebrant_age,
                b.event_type,
                b.location,
                b.event_date,
                b.start_time,
                b.end_time,
                b.food_package,
                b.event_theme,
                b.custom_theme,
                b.theme_suggestions,
                b.total_price,
                b.booking_status,
                b.rejection_reason,
                b.created_at,
                b.updated_at,
                u.name as user_name,
                u.email as user_email
                FROM bookings b
                JOIN usertable u ON b.user_id = u.id
                ORDER BY b.event_date DESC, b.created_at DESC
            ");
            $stmt->execute();
            $bookings = $stmt->fetchAll();
            
            echo json_encode($bookings);
            exit;
        }

        // Check conflict (remains the same)
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
            
            $stmt = $conn->prepare("SELECT COUNT(*) as count, STRING_AGG(CONCAT(start_time, ' - ', end_time), ', ') as existing_slots FROM bookings WHERE event_date = ? AND booking_status != 'cancelled' AND ((start_time <= ? AND end_time > ?) OR (start_time < ? AND end_time >= ?) OR (start_time >= ? AND end_time <= ?))");
            $stmt->execute([$event_date, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time]);
            $result = $stmt->fetch();
            
            echo json_encode([
                'conflict' => $result['count'] > 0,
                'existing_slots' => $result['existing_slots'] ?? ''
            ]);
            exit;
        }

        // Calendar data - updated to show user's own bookings vs others
        if (isset($_GET['action']) && $_GET['action'] === 'get_calendar_data') {
            header('Content-Type: application/json');
            
            $month = $_GET['month'] ?? date('n');
            $year = $_GET['year'] ?? 2025;
            $current_user_id = $_SESSION['user_id'] ?? null;
            
            $stmt = $conn->prepare("SELECT 
                event_date, 
                start_time, 
                end_time, 
                event_type,
                location,
                full_name,
                booking_status,
                user_id,
                total_price,
                COUNT(*) as booking_count
                FROM bookings 
                WHERE EXTRACT(YEAR FROM event_date) = ? 
                AND EXTRACT(MONTH FROM event_date) = ? 
                AND booking_status != 'cancelled'
                GROUP BY event_date, start_time, end_time, event_type, location, full_name, booking_status, user_id, total_price
                ORDER BY event_date
            ");
            $stmt->execute([$year, $month]);
            $results = $stmt->fetchAll();
            
            $calendar_data = [];
            foreach ($results as $row) {
                $date = $row['event_date'];
                
                $detailStmt = $conn->prepare("SELECT 
                    start_time, 
                    end_time, 
                    event_type,
                    location,
                    full_name,
                    booking_status,
                    user_id,
                    total_price
                    FROM bookings 
                    WHERE event_date = ? 
                    AND booking_status != 'cancelled'
                    ORDER BY start_time
                ");
                $detailStmt->execute([$date]);
                $detailResults = $detailStmt->fetchAll();
                
                $bookings = [];
                foreach ($detailResults as $booking) {
                    $bookings[] = [
                        'start_time' => $booking['start_time'],
                        'end_time' => $booking['end_time'],
                        'event_type' => $booking['event_type'],
                        'location' => $booking['location'],
                        'is_own_booking' => ($current_user_id && $booking['user_id'] == $current_user_id),
                        'full_name' => $booking['full_name'],
                        'booking_status' => $booking['booking_status'],
                        'total_price' => $booking['total_price']
                    ];
                }
                
                $calendar_data[$date] = [
                    'count' => count($bookings),
                    'bookings' => $bookings,
                    'is_full' => count($bookings) >= 3
                ];
            }
            
            echo json_encode($calendar_data);
            exit;
        }

        // Cancel booking - only user can cancel their own booking
        if ($_POST && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
            header('Content-Type: application/json');
            
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please log in to cancel booking.']);
                exit;
            }
            
            $booking_id = trim($_POST['booking_id'] ?? '');
            $user_id = $_SESSION['user_id'];
            
            if (empty($booking_id)) {
                echo json_encode(['success' => false, 'message' => 'Booking ID is required.']);
                exit;
            }
            
            try {
                // Check if booking belongs to user
                $checkStmt = $conn->prepare("SELECT id, booking_status FROM bookings WHERE id = ? AND user_id = ?");
                $checkStmt->execute([$booking_id, $user_id]);
                $booking = $checkStmt->fetch();
                
                if (!$booking) {
                    echo json_encode(['success' => false, 'message' => 'Booking not found or access denied.']);
                    exit;
                }
                
                if ($booking['booking_status'] === 'cancelled') {
                    echo json_encode(['success' => false, 'message' => 'Booking is already cancelled.']);
                    exit;
                }
                
                // Update booking status to cancelled
                $updateStmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
                $result = $updateStmt->execute([$booking_id, $user_id]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Booking cancelled successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to cancel booking.']);
                }
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
            exit;
        }

        // Admin booking approval endpoint
        if ($_POST && isset($_POST['action']) && $_POST['action'] === 'approve_booking') {
            header('Content-Type: application/json');
            
            // Check if user is admin
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }
            
            $booking_id = trim($_POST['booking_id'] ?? '');
            
            if (empty($booking_id)) {
                echo json_encode(['success' => false, 'message' => 'Booking ID is required.']);
                exit;
            }
            
            try {
                $updateStmt = $conn->prepare("UPDATE bookings SET booking_status = 'approved', updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $updateStmt->execute([$booking_id]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Booking approved successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to approve booking.']);
                }
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
            exit;
        }

        // Admin booking rejection endpoint
        if ($_POST && isset($_POST['action']) && $_POST['action'] === 'reject_booking') {
            header('Content-Type: application/json');
            
            // Check if user is admin
            if (!isset($_SESSION['user_id']) || !isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                echo json_encode(['success' => false, 'message' => 'Admin access required']);
                exit;
            }
            
            $booking_id = trim($_POST['booking_id'] ?? '');
            $rejection_reason = trim($_POST['rejection_reason'] ?? '');
            
            if (empty($booking_id)) {
                echo json_encode(['success' => false, 'message' => 'Booking ID is required.']);
                exit;
            }
            
            try {
                $updateStmt = $conn->prepare("UPDATE bookings SET booking_status = 'cancelled', rejection_reason = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $result = $updateStmt->execute([$rejection_reason, $booking_id]);
                
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Booking rejected successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to reject booking.']);
                }
                
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
            exit;
        }

        // Get booking statistics for dashboard
        if (isset($_GET['action']) && $_GET['action'] === 'get_booking_stats') {
            header('Content-Type: application/json');
            
            $user_id = $_SESSION['user_id'] ?? null;
            
            if (!$user_id) {
                echo json_encode(['error' => 'Not logged in']);
                exit;
            }
            
            try {
                // Get user's booking statistics
                $statsStmt = $conn->prepare("SELECT 
                    COUNT(*) as total_bookings,
                    COUNT(CASE WHEN booking_status = 'pending' THEN 1 END) as pending_bookings,
                    COUNT(CASE WHEN booking_status = 'approved' THEN 1 END) as approved_bookings,
                    COUNT(CASE WHEN booking_status = 'cancelled' THEN 1 END) as cancelled_bookings,
                    COALESCE(SUM(CASE WHEN booking_status = 'approved' THEN total_price ELSE 0 END), 0) as total_spent,
                    COUNT(CASE WHEN event_date >= CURRENT_DATE AND booking_status != 'cancelled' THEN 1 END) as upcoming_events
                    FROM bookings 
                    WHERE user_id = ?
                ");
                $statsStmt->execute([$user_id]);
                $stats = $statsStmt->fetch();
                
                echo json_encode($stats);
                
            } catch (Exception $e) {
                echo json_encode(['error' => 'Failed to get statistics: ' . $e->getMessage()]);
            }
            exit;
        }


        // Delete booking - only user can delete their own booking (Fixed version)
        if ($_POST && isset($_POST['action']) && $_POST['action'] === 'delete_booking') {
            header('Content-Type: application/json');
            
            if (!isset($_SESSION['user_id'])) {
                echo json_encode(['success' => false, 'message' => 'Please log in to delete booking.']);
                exit;
            }
            
            $booking_id = trim($_POST['booking_id'] ?? '');
            $user_id = $_SESSION['user_id'];
            
            if (empty($booking_id)) {
                echo json_encode(['success' => false, 'message' => 'Booking ID is required.']);
                exit;
            }
            
            try {
                // Debug logging
                error_log("Delete attempt - Booking ID: $booking_id, User ID: $user_id");
                
                // Check if booking belongs to user and get booking details - FIXED: Use ? parameters
                $checkStmt = $conn->prepare("SELECT id, booking_status, event_date, start_time, celebrant_name FROM bookings WHERE id = ? AND user_id = ?");
                $checkStmt->execute([$booking_id, $user_id]);
                $booking = $checkStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$booking) {
                    error_log("Booking not found - ID: $booking_id, User: $user_id");
                    echo json_encode(['success' => false, 'message' => 'Booking not found or access denied.']);
                    exit;
                }
                
                error_log("Found booking - Status: " . $booking['booking_status']);
                
                // Allow deletion if booking is pending OR if approved but more than 24 hours away
                if ($booking['booking_status'] === 'approved') {
                    $event_datetime = $booking['event_date'] . ' ' . $booking['start_time'];
                    $hours_until_event = (strtotime($event_datetime) - time()) / 3600;
                    
                    if ($hours_until_event <= 24 && $hours_until_event > 0) {
                        echo json_encode(['success' => false, 'message' => 'Cannot delete approved booking within 24 hours of the event. Please contact admin for assistance.']);
                        exit;
                    }
                }
                
                // Allow deletion for pending bookings regardless of timing
                if ($booking['booking_status'] === 'cancelled') {
                    echo json_encode(['success' => false, 'message' => 'This booking is already cancelled.']);
                    exit;
                }
                
                // Delete the booking record completely - FIXED: Use ? parameters
                $deleteStmt = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
                $result = $deleteStmt->execute([$booking_id, $user_id]);
                
                if ($result && $deleteStmt->rowCount() > 0) {
                    error_log("Booking successfully deleted - ID: $booking_id, User ID: $user_id, Celebrant: " . $booking['celebrant_name']);
                    echo json_encode(['success' => true, 'message' => 'Booking cancel successfully.']);
                } else {
                    error_log("Delete failed - No rows affected");
                    echo json_encode(['success' => false, 'message' => 'Failed to delete booking or booking not found.']);
                }
                
            } catch (PDOException $e) {
                error_log("Database error deleting booking ID $booking_id: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
            } catch (Exception $e) {
                error_log("General error deleting booking ID $booking_id: " . $e->getMessage());
                echo json_encode(['success' => false, 'message' => 'An error occurred: ' . $e->getMessage()]);
            }
            exit;
        }

        ?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/png" href="logo/logo.png">
    <title>Zaf's Kitchen Dashboard</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

    <!-- Poppins Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
    /* Global Styles */
    * {
        font-family: 'Poppins', sans-serif;
    }

    /* Navigation Styles */
    .hover-nav:hover {
        background-color: #E75925 !important;
        color: white !important;
    }

    .active-nav {
        background-color: #E75925 !important;
        color: white !important;
    }

    #price-summary {
        background: white !important;
        border: none !important;
        color: #E75925 !important;
        box-shadow: none !important;
    }

    #price-summary * {
        color: #E75925 !important;
    }

    #price-summary-step2 {
        background: white !important;
        border: none !important;
        color: #E75925 !important;
        box-shadow: none !important;
    }

    #price-summary-step2 * {
        color: #E75925 !important;
    }

    #price-summary-step3 {
        background: white !important;
        border: none !important;
        color: #E75925 !important;
        box-shadow: none !important;
    }

    #price-summary-step3 * {
        color: #E75925 !important;
    }

    /* Override any existing price calculator styles */
    .price-calculator {
        background: white !important;
        color: #E75925 !important;
        border: none !important;
        box-shadow: none !important;
    }

    .price-calculator * {
        color: #E75925 !important;
    }

    /* Enhanced Booking Card Styles */
    .booking-card-enhanced {
        transition: all 0.3s ease;
        border-radius: 16px;
        overflow: hidden;
        background: linear-gradient(145deg, #ffffff, #f8fafc);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    }

    .booking-card-enhanced:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    .booking-price-tag {
        background: linear-gradient(135deg, #E75925, #d14d1f);
        color: white;
        font-weight: bold;
        font-size: 1.1em;
        padding: 8px 16px;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(231, 89, 37, 0.3);
    }

    .booking-status-indicator {
        width: 6px;
        min-height: 100%;
        position: absolute;
        left: 0;
        top: 0;
    }

    .status-approved .booking-status-indicator {
        background: linear-gradient(180deg, #10b981, #059669);
    }

    .status-pending .booking-status-indicator {
        background: linear-gradient(180deg, #f59e0b, #d97706);
    }

    .status-cancelled .booking-status-indicator {
        background: linear-gradient(180deg, #ef4444, #dc2626);
    }

    /* Loading Animation */
    .calculating {
        animation: pulse 2s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Theme Button Styles */
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

    /* Form Styles */
    .form-input {
        transition: all 0.2s ease;
    }

    .form-input:focus {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(231, 89, 37, 0.2);
    }

    /* Custom Scrollbar */
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

    /* Loading Animation */
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

    /* Modal Animations */
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

    /* Booking Status Colors */
    .calendar-day.no-bookings,
    .calendar-day.one-booking {
        background-color: #dcfce7;
        border-color: #22c55e;
    }

    .calendar-day.two-bookings {
        background-color: #fef3c7;
        border-color: #f59e0b;
    }

    .calendar-day.three-bookings {
        background-color: #fee2e2;
        border-color: #ef4444;
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

    /* Calendar Navigation */
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

    /* Status Badge Styles */
    .status-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 12px;
        text-transform: uppercase;
    }

    .status-pending {
        background-color: #fef3c7;
        color: #92400e;
        border: 1px solid #f59e0b;
    }

    .status-approved {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #10b981;
    }

    .status-cancelled {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }

    /* Booking Card Styles */
    .booking-card {
        transition: all 0.2s ease;
    }

    .booking-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    /* Status Border Colors */
    .booking-card.status-approved {
        border-left: 4px solid #10b981 !important;
    }

    .booking-card.status-pending {
        border-left: 4px solid #f59e0b !important;
    }

    .booking-card.status-cancelled {
        border-left: 4px solid #ef4444 !important;
    }

    /* Past Events */
    .booking-card.past-event {
        opacity: 0.75;
    }

    .booking-card.status-cancelled.past-event {
        opacity: 0.6;
    }

    /* Step Progress Bar Styles */
    .step-progress {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 24px;
    }

    .step-item {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        margin-bottom: 8px;
        transition: all 0.3s ease;
    }

    .step-line {
        height: 2px;
        width: 100px;
        margin: 0 16px;
        margin-bottom: 28px;
        transition: all 0.3s ease;
    }

    .step-text {
        font-size: 12px;
        font-weight: 600;
        text-align: center;
        transition: color 0.3s ease;
    }

    .step-item.active .step-circle {
        background-color: #E75925;
        color: white;
        box-shadow: 0 0 0 4px rgba(231, 89, 37, 0.2);
    }

    .step-item.completed .step-circle {
        background-color: #22c55e;
        color: white;
    }

    .step-item.inactive .step-circle {
        background-color: #e5e7eb;
        color: #9ca3af;
    }

    .step-line.active {
        background-color: #E75925;
    }

    .step-line.completed {
        background-color: #22c55e;
    }

    .step-line.inactive {
        background-color: #e5e7eb;
    }

    .step-item.active .step-text {
        color: #E75925;
    }

    .step-item.completed .step-text {
        color: #22c55e;
    }

    .step-item.inactive .step-text {
        color: #9ca3af;
    }

    /* Form Step Animations */
    .form-step {
        opacity: 0;
        transform: translateX(20px);
        transition: all 0.4s ease-in-out;
    }

    .form-step.active {
        opacity: 1;
        transform: translateX(0);
    }

    .form-step.slide-out-left {
        opacity: 0;
        transform: translateX(-20px);
    }

    .form-step.slide-out-right {
        opacity: 0;
        transform: translateX(20px);
    }

    /* Date Input Styling */
    .form-input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0.7;
    }

    .form-input[type="date"]:disabled::-webkit-calendar-picker-indicator {
        opacity: 0.3;
    }

    #avatar-modal {
        backdrop-filter: blur(4px);
    }

    #avatar-grid img {
        aspect-ratio: 1/1;
        object-fit: cover;
    }

    .hidden {
        display: none !important;
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
                <img src="logo/logo-border.png" alt="Logo" class="w-26 h-24 rounded-full object-cover mb-1">
                <h1 class="text-x6 font-bold text-center">Zaf's Kitchen</h1>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-3">
                <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
                    <i class="fas fa-calendar-plus text-[1.8rem]"></i>
                    <span class="font-semibold">Book Now</span>
                </a>
                <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
                    <i class="fas fa-list-check text-[1.8rem]"></i>
                    <span class="font-semibold">My Bookings</span>
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

        <!-- My Bookings Section -->
    <section id="section-mybookings" class="hidden">
        <h2 class="text-2xl font-bold mb-2">My Bookings</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-6"></div>
        
        <!-- Filter/Status Legend -->
        <div class="bg-white p-4 rounded-lg shadow-lg border-2 border-gray-300 mb-6">
            <div class="flex flex-wrap gap-4 items-center">
                <span class="font-semibold text-gray-700">Status Legend:</span>
                <div class="flex items-center gap-2">
                    <span class="status-badge status-pending">Pending</span>
                    <span class="text-sm text-gray-600">Waiting for admin approval</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="status-badge status-approved">Approved</span>
                    <span class="text-sm text-gray-600">Confirmed by admin</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="status-badge status-cancelled">Cancelled</span>
                    <span class="text-sm text-gray-600">Booking cancelled</span>
                </div>
            </div>
        </div>
        
        <!-- Bookings Container -->
        <div id="bookings-container" class="space-y-4">
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="text-gray-600 mt-2">Loading your bookings...</p>
            </div>
        </div>
        
        <!-- Refresh Button -->
        <div class="mt-6 text-center">
            <button id="refresh-bookings" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg shadow-md transition-colors">
                <i class="fas fa-refresh mr-2"></i>
                Refresh Status
            </button>
        </div>
    </section>

    <!-- Delete Confirmation Modal -->
    <div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                    <i class="fas fa-trash text-red-600 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-4">Remove</h3>
                <p class="text-sm text-gray-500 mb-6">
                    Are you sure you want to cancel this booking? This action cannot be undone.
                </p>
                <div class="flex gap-3">
                    <button id="delete-modal-cancel" 
                        class="flex-1 bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition-colors">
                        Keep Booking
                    </button>
                    <button id="delete-modal-confirm" 
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition-colors">
                        Cancel Booking
                    </button>
                </div>
            </div>
        </div>
    </div>

        <!-- ENHANCED Book Now Section with 3 Steps -->
    <section id="section-book" class="hidden">
        <h2 class="text-2xl font-bold mb-2">Book Now</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
        
        <!-- Progress Steps -->
        <div class="bg-white p-6 rounded-lg shadow-lg border-2 border-gray-300 mb-6">
            <div class="step-progress">
                <div id="step-1" class="step-item active">
                    <div class="step-circle">1</div>
                    <span class="step-text">Basic Info</span>
                </div>
                <div class="step-line inactive"></div>
                <div id="step-2" class="step-item inactive">
                    <div class="step-circle">2</div>
                    <span class="step-text">Event Details</span>
                </div>
                <div class="step-line inactive"></div>
                <div id="step-3" class="step-item inactive">
                    <div class="step-circle">3</div>
                    <span class="step-text">Theme & Menu</span>
                </div>
            </div>
        </div>

        <!-- Booking Form -->
        <div class="w-full">
            <form id="booking-form" method="POST">
                <input type="hidden" name="action" value="book_event">
                <input type="hidden" id="total_price" name="total_price" value="0">
                
                <!-- Step 1: Basic Information -->
                <div id="booking-step1" class="form-step active bg-white p-6 rounded-lg shadow-lg border-2 border-gray-300">
                    <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
                        <i class="fas fa-user-circle text-[#E75925]"></i>
                        Basic Information
                    </h3>
                    <div class="space-y-4">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-user mr-2 text-[#E75925]"></i>
                                    Your Full Name *
                                </label>
                                <input id="fullname" name="full_name" type="text" 
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" 
                                    placeholder="Enter your full name" required>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-phone mr-2 text-[#E75925]"></i>
                                    Contact Number *
                                </label>
                                <input id="contact" name="contact_number" type="tel" 
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" 
                                    placeholder="e.g. +63 912 345 6789" required>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-star mr-2 text-[#E75925]"></i>
                                    Celebrant's Name *
                                </label>
                                <input id="celebrant-name" name="celebrant_name" type="text" 
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" 
                                    placeholder="Name of the person being celebrated" required>
                                <p class="text-xs text-gray-500 mt-1">For corporate events, you can put company name</p>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-users mr-2 text-[#E75925]"></i>
                                    Number of Guests *
                                </label>
                                <input id="guest-count" name="guest_count" type="number" min="1" max="500"
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" 
                                    placeholder="Expected number of guests" required>
                            </div>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-utensils mr-2 text-[#E75925]"></i>
                                    Food Package *
                                </label>
                                <select id="package" name="food_package" 
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                                    <option value="">Select a package</option>
                                    <option value="budget" data-price="200">Budget Package - ₱200/person</option>
                                    <option value="standard" data-price="350">Standard Package - ₱350/person</option>
                                    <option value="premium" data-price="500">Premium Package - ₱500/person</option>
                                    <option value="deluxe" data-price="750">Deluxe Package - ₱750/person</option>
                                    <option value="luxury" data-price="1000">Luxury Package - ₱1000/person</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-calendar-day mr-2 text-[#E75925]"></i>
                                    Type of Event *
                                </label>
                                <select id="eventtype" name="event_type" 
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
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

                        <!-- Birthday Age Field -->
                        <div id="age-field" class="hidden">
                            <label class="block font-semibold mb-1 text-gray-700">
                                <i class="fas fa-birthday-cake mr-2 text-[#E75925]"></i>
                                Celebrant's Age *
                            </label>
                            <input id="celebrant-age" name="celebrant_age" type="number" min="1" max="150"
                                class="form-input w-full md:w-32 border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" 
                                placeholder="Age">
                        </div>
                        
                        <!-- Integrated Price Calculator -->
                        <div id="price-summary" class="bg-gradient-to-r from-[#E75925] to-[#d14b1f] text-white p-4 rounded-lg shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm opacity-90 mb-1">
                                        <i class="fas fa-calculator mr-1"></i>
                                        Estimated Cost
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-sm">
                                            <span>Base Package:</span>
                                            <span id="base-price">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between text-sm" id="additional-items-container" style="display: none;">
                                            <span>Additional Items:</span>
                                            <span id="additional-price">₱0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold" id="total-display">₱0.00</div>
                                    <div class="text-xs opacity-90">
                                        for <span id="guest-display">0</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-end">
                            <button type="button" id="next-step1" 
                                class="text-white px-8 py-3 rounded-lg shadow-md hover:opacity-90 transition-all transform hover:scale-105 font-semibold" 
                                style="background-color:#E75925;">
                                Next Step <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Event Details -->
                <div id="booking-step2" class="form-step bg-white p-6 rounded-lg shadow-lg border-2 border-gray-300 hidden">
                    <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar-alt text-[#E75925]"></i>
                        Event Schedule & Details
                    </h3>
                    <div class="space-y-6">
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-calendar mr-2 text-[#E75925]"></i>
                                    Event Date *
                                </label>
                                <input id="event-date" name="event_date" type="date" 
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                                <p class="text-xs text-gray-500 mt-1">Must be at least 3 days from today</p>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-clock mr-2 text-[#E75925]"></i>
                                    Start Time *
                                </label>
                                <input id="start-time" name="start_time" type="time" 
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                            </div>
                            <div>
                                <label class="block font-semibold mb-1 text-gray-700">
                                    <i class="fas fa-clock mr-2 text-[#E75925]"></i>
                                    End Time *
                                </label>
                                <input id="end-time" name="end_time" type="time" 
                                    class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                                <p class="text-xs text-gray-500 mt-1">Duration: 4-8 hours</p>
                            </div>
                        </div>
                        
                        <!-- Location Input -->
                        <div>
                            <label for="location" class="block font-semibold mb-1 text-gray-700">
                                <i class="fas fa-map-marker-alt mr-2 text-[#E75925]"></i>
                                Event Location *
                            </label>
                            <input type="text" id="location" name="location" required
                                placeholder="Enter full event address (e.g., 123 Main St, City)"
                                class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" />
                            <p class="text-xs text-gray-500 mt-1">Provide the exact address of the event location.</p>
                        </div>
                        
                        <!-- Time Conflict Warning -->
                        <div id="time-conflict-warning" class="hidden bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <i class="fas fa-exclamation-triangle text-red-500 mr-2"></i>
                                <span class="text-red-700 font-semibold">Time Conflict Detected</span>
                            </div>
                            <p class="text-red-600 mt-1 text-sm" id="conflict-details"></p>
                        </div>

                        <!-- Event Summary Preview -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <h4 class="font-semibold text-blue-800 mb-2 flex items-center gap-2">
                                <i class="fas fa-eye"></i>
                                Event Preview
                            </h4>
                            <div id="event-preview" class="text-sm text-blue-700">
                                <p>Fill in the details above to see your event preview</p>
                            </div>
                        </div>

                        <!-- Price Summary for Step 2 -->
                        <div id="price-summary-step2" class="bg-white border-2 border-gray-300 text-gray-800 p-4 rounded-lg shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-600 mb-1">
                                        <i class="fas fa-calculator mr-1 text-[#E75925]"></i>
                                        Current Estimate
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-sm">
                                            <span>Base Package:</span>
                                            <span id="base-price-step2">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between text-sm" id="additional-items-container-step2" style="display: none;">
                                            <span>Additional Items:</span>
                                            <span id="additional-price-step2">₱0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-[#E75925]" id="total-display-step2">₱0.00</div>
                                    <div class="text-xs text-gray-600">
                                        for <span id="guest-display-step2">0</span> guests
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="flex justify-between">
                            <button type="button" id="back-step2" 
                                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-400 transition-colors font-semibold">
                                <i class="fas fa-arrow-left mr-2"></i>Back
                            </button>
                            <button type="button" id="next-step2" 
                                class="text-white px-8 py-3 rounded-lg shadow-md hover:opacity-90 transition-all transform hover:scale-105 font-semibold" 
                                style="background-color:#E75925;">
                                Next Step <i class="fas fa-arrow-right ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Theme & Menu Selection -->
                <div id="booking-step3" class="form-step bg-white p-6 rounded-lg shadow-lg border-2 border-gray-300 hidden">
                    <h3 class="text-xl font-semibold mb-4 flex items-center gap-2">
                        <i class="fas fa-palette text-[#E75925]"></i>
                        Theme & Menu Customization
                    </h3>
                    <div class="space-y-6">
                        <!-- Theme Selection -->
                        <div>
                            <label class="block font-semibold mb-3 text-gray-700">
                                <i class="fas fa-paint-brush mr-2 text-[#E75925]"></i>
                                Choose Your Event Theme
                            </label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                                <button type="button" class="theme-btn p-4 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-all" data-theme="elegant">
                                    <i class="fas fa-crown text-3xl mb-2" style="color:#E75925;"></i>
                                    <div class="font-semibold text-sm">Elegant</div>
                                    <div class="text-xs text-gray-500">Classic & Sophisticated</div>
                                    <input type="radio" name="event_theme" value="elegant" class="hidden">
                                </button>
                                <button type="button" class="theme-btn p-4 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-all" data-theme="rustic">
                                    <i class="fas fa-leaf text-3xl mb-2" style="color:#E75925;"></i>
                                    <div class="font-semibold text-sm">Rustic</div>
                                    <div class="text-xs text-gray-500">Natural & Cozy</div>
                                    <input type="radio" name="event_theme" value="rustic" class="hidden">
                                </button>
                                <button type="button" class="theme-btn p-4 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-all" data-theme="modern">
                                    <i class="fas fa-star text-3xl mb-2" style="color:#E75925;"></i>
                                    <div class="font-semibold text-sm">Modern</div>
                                    <div class="text-xs text-gray-500">Clean & Minimalist</div>
                                    <input type="radio" name="event_theme" value="modern" class="hidden">
                                </button>
                                <button type="button" class="theme-btn p-4 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-all" data-theme="tropical">
                                    <i class="fas fa-umbrella-beach text-3xl mb-2" style="color:#E75925;"></i>
                                    <div class="font-semibold text-sm">Tropical</div>
                                    <div class="text-xs text-gray-500">Bright & Colorful</div>
                                    <input type="radio" name="event_theme" value="tropical" class="hidden">
                                </button>
                                <button type="button" class="theme-btn p-4 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-all" data-theme="vintage">
                                    <i class="fas fa-camera-retro text-3xl mb-2" style="color:#E75925;"></i>
                                    <div class="font-semibold text-sm">Vintage</div>
                                    <div class="text-xs text-gray-500">Retro & Classic</div>
                                    <input type="radio" name="event_theme" value="vintage" class="hidden">
                                </button>
                                <button type="button" class="theme-btn p-4 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-all" data-theme="custom">
                                    <i class="fas fa-pencil-alt text-3xl mb-2" style="color:#E75925;"></i>
                                    <div class="font-semibold text-sm">Custom</div>
                                    <div class="text-xs text-gray-500">Your Own Style</div>
                                    <input type="radio" name="event_theme" value="custom" class="hidden">
                                </button>
                            </div>
                            
                            <input id="custom-theme" name="custom_theme" type="text" 
                                class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black hidden mb-3" 
                                placeholder="Describe your custom theme">
                        </div>

                        <!-- Theme Suggestions -->
                        <div>
                            <label class="block font-semibold mb-2 text-gray-700">
                                <i class="fas fa-lightbulb mr-2 text-[#E75925]"></i>
                                Additional Theme Suggestions or Special Requests
                            </label>
                            <textarea id="theme-suggestions" name="theme_suggestions" rows="3"
                                class="form-input w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black"
                                placeholder="Tell us about any specific decorations, colors, or special touches..."></textarea>
                        </div>

                        <!-- Menu Selection with Pricing -->
                        <div>
                            <label class="block font-semibold mb-3 text-gray-700">
                                <i class="fas fa-utensils mr-2 text-[#E75925]"></i>
                                Additional Menu Items (Optional)
                            </label>
                            <div class="border-2 border-gray-300 rounded-lg p-4">
                                <p class="text-sm text-gray-600 mb-4">Add extra items to your package for additional cost:</p>
                                <div class="grid md:grid-cols-3 gap-6 text-sm">
                                    <div>
                                        <div class="font-semibold text-[#E75925] mb-3 text-base border-b pb-2">Main Dishes</div>
                                        <div class="space-y-2">
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_main[]" value="lechon_kawali" data-price="50" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Lechon Kawali</span>
                                                <span class="text-[#E75925] font-medium">+₱50</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_main[]" value="chicken_adobo" data-price="30" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Chicken Adobo</span>
                                                <span class="text-[#E75925] font-medium">+₱30</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_main[]" value="beef_caldereta" data-price="75" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Beef Caldereta</span>
                                                <span class="text-[#E75925] font-medium">+₱75</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_main[]" value="sweet_sour_fish" data-price="60" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Sweet & Sour Fish</span>
                                                <span class="text-[#E75925] font-medium">+₱60</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <div class="font-semibold text-[#E75925] mb-3 text-base border-b pb-2">Side Dishes</div>
                                        <div class="space-y-2">
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_side[]" value="pancit_canton" data-price="25" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Pancit Canton</span>
                                                <span class="text-[#E75925] font-medium">+₱25</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_side[]" value="fried_rice" data-price="20" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Fried Rice</span>
                                                <span class="text-[#E75925] font-medium">+₱20</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_side[]" value="lumpiang_shanghai" data-price="35" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Lumpiang Shanghai</span>
                                                <span class="text-[#E75925] font-medium">+₱35</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_side[]" value="mixed_vegetables" data-price="15" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Mixed Vegetables</span>
                                                <span class="text-[#E75925] font-medium">+₱15</span>
                                            </label>
                                        </div>
                                    </div>

                                    <div>
                                        <div class="font-semibold text-[#E75925] mb-3 text-base border-b pb-2">Desserts</div>
                                        <div class="space-y-2">
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_dessert[]" value="leche_flan" data-price="40" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Leche Flan</span>
                                                <span class="text-[#E75925] font-medium">+₱2240</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_dessert[]" value="halo_halo" data-price="45" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Halo-Halo</span>
                                                <span class="text-[#E75925] font-medium">+₱45</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_dessert[]" value="buko_pie" data-price="55" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Buko Pie</span>
                                                <span class="text-[#E75925] font-medium">+₱55</span>
                                            </label>
                                            <label class="flex items-center hover:bg-gray-50 p-2 rounded transition-colors">
                                                <input type="checkbox" name="menu_dessert[]" value="ice_cream" data-price="30" class="mr-3 text-[#E75925] w-4 h-4">
                                                <span class="flex-1">Ice Cream</span>
                                                <span class="text-[#E75925] font-medium">+₱30</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-gray-500 mt-3">* Additional menu prices are per person and will be added to your base package cost.</p>
                            </div>
                        </div>

                        <!-- Final Price Summary for Step 3 -->
                        <div id="price-summary-step3" class="bg-white border-2 border-gray-300 text-gray-800 p-4 rounded-lg shadow-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-sm text-gray-600 mb-1">
                                        <i class="fas fa-calculator mr-1 text-[#E75925]"></i>
                                        Final Estimate
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-sm">
                                            <span>Base Package:</span>
                                            <span id="base-price-step3">₱0.00</span>
                                        </div>
                                        <div class="flex justify-between text-sm" id="additional-items-container-step3" style="display: none;">
                                            <span>Additional Items:</span>
                                            <span id="additional-price-step3">₱0.00</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="text-2xl font-bold text-gray-800" id="total-display-step3">₱0.00</div>
                                    <div class="text-xs text-gray-600">
                                        for <span id="guest-display-step3">0</span> guests
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-300">
                                <div class="text-xs text-center text-gray-600">
                                    <i class="fas fa-info-circle mr-1 text-gray-600"></i>
                                    Final price may vary based on specific requirements and location
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <button type="button" id="back-step3" 
                                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg shadow-md hover:bg-gray-400 transition-colors font-semibold">
                                <i class="fas fa-arrow-left mr-2"></i>Back
                            </button>
                            <button type="submit" id="submit-booking" 
                                class="text-white px-8 py-3 rounded-lg shadow-md hover:opacity-90 transition-all transform hover:scale-105 font-semibold text-lg" 
                                style="background-color:#E75925;">
                                <i class="fas fa-paper-plane mr-2"></i>Submit Booking
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
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
                    <div class="calendar-header-day">Sunday</div>
                    <div class="calendar-header-day">Monday</div>
                    <div class="calendar-header-day">Tuesday</div>
                    <div class="calendar-header-day">Wednesday</div>
                    <div class="calendar-header-day">Thursday</div>
                    <div class="calendar-header-day">Friday</div>
                    <div class="calendar-header-day">Saturday</div>
                </div>
                
                <!-- Calendar Grid -->
                <div id="calendar-grid" class="calendar">
                    <!-- Calendar days will be dynamically generated here -->
                </div>
            </div>
        </section>

    <!-- MENU PACKAGES SECTION-->
    <section id="section-menu" class="hidden">
        <h2 class="text-2xl font-bold mb-2">Menu Packages</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-6"></div>
        
        <!-- Package Selection Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <!-- Budget Package -->
            <div class="package-card bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#E75925] transition-all cursor-pointer transform hover:scale-105" data-package="budget">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=500" alt="Budget Package" class="w-full h-48 object-cover">
                    <div class="absolute top-0 right-0 bg-[#E75925] text-white px-4 py-2 font-bold text-lg rounded-bl-lg">
                        ₱200/person
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2 text-gray-800">Budget Package</h3>
                    <p class="text-gray-600 mb-4">Perfect for intimate gatherings and small celebrations</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-utensils mr-2 text-[#E75925]"></i>
                            5 Dishes
                        </span>
                        <button class="view-menu-btn bg-[#E75925] hover:bg-[#d14d1f] text-white px-4 py-2 rounded-lg transition-colors">
                            View Menu <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Standard Package -->
            <div class="package-card bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#E75925] transition-all cursor-pointer transform hover:scale-105" data-package="standard">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=500" alt="Standard Package" class="w-full h-48 object-cover">
                    <div class="absolute top-0 right-0 bg-[#E75925] text-white px-4 py-2 font-bold text-lg rounded-bl-lg">
                        ₱350/person
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2 text-gray-800">Standard Package</h3>
                    <p class="text-gray-600 mb-4">Great value for medium-sized events and parties</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-utensils mr-2 text-[#E75925]"></i>
                            8 Dishes
                        </span>
                        <button class="view-menu-btn bg-[#E75925] hover:bg-[#d14d1f] text-white px-4 py-2 rounded-lg transition-colors">
                            View Menu <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Premium Package -->
            <div class="package-card bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#E75925] transition-all cursor-pointer transform hover:scale-105" data-package="premium">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=500" alt="Premium Package" class="w-full h-48 object-cover">
                    <div class="absolute top-0 right-0 bg-[#E75925] text-white px-4 py-2 font-bold text-lg rounded-bl-lg">
                        ₱500/person
                    </div>
                    <div class="absolute top-0 left-0 bg-yellow-500 text-white px-3 py-1 text-xs font-bold rounded-br-lg">
                        POPULAR
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2 text-gray-800">Premium Package</h3>
                    <p class="text-gray-600 mb-4">Premium selection for memorable occasions</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-utensils mr-2 text-[#E75925]"></i>
                            10 Dishes
                        </span>
                        <button class="view-menu-btn bg-[#E75925] hover:bg-[#d14d1f] text-white px-4 py-2 rounded-lg transition-colors">
                            View Menu <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Deluxe Package -->
            <div class="package-card bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#E75925] transition-all cursor-pointer transform hover:scale-105" data-package="deluxe">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1485962398705-ef6a13c41e8f?w=500" alt="Deluxe Package" class="w-full h-48 object-cover">
                    <div class="absolute top-0 right-0 bg-[#E75925] text-white px-4 py-2 font-bold text-lg rounded-bl-lg">
                        ₱750/person
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2 text-gray-800">Deluxe Package</h3>
                    <p class="text-gray-600 mb-4">Exceptional dining for special celebrations</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-utensils mr-2 text-[#E75925]"></i>
                            12 Dishes
                        </span>
                        <button class="view-menu-btn bg-[#E75925] hover:bg-[#d14d1f] text-white px-4 py-2 rounded-lg transition-colors">
                            View Menu <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Luxury Package -->
            <div class="package-card bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#E75925] transition-all cursor-pointer transform hover:scale-105" data-package="luxury">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1559339352-11d035aa65de?w=500" alt="Luxury Package" class="w-full h-48 object-cover">
                    <div class="absolute top-0 right-0 bg-[#E75925] text-white px-4 py-2 font-bold text-lg rounded-bl-lg">
                        ₱1000/person
                    </div>
                    <div class="absolute top-0 left-0 bg-purple-600 text-white px-3 py-1 text-xs font-bold rounded-br-lg">
                        PREMIUM
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2 text-gray-800">Luxury Package</h3>
                    <p class="text-gray-600 mb-4">Ultimate culinary experience for grand events</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-utensils mr-2 text-[#E75925]"></i>
                            15 Dishes
                        </span>
                        <button class="view-menu-btn bg-[#E75925] hover:bg-[#d14d1f] text-white px-4 py-2 rounded-lg transition-colors">
                            View Menu <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Custom Package -->
            <div class="package-card bg-white rounded-lg shadow-lg overflow-hidden border-2 border-gray-200 hover:border-[#E75925] transition-all cursor-pointer transform hover:scale-105" data-package="custom">
                <div class="relative">
                    <img src="https://images.unsplash.com/photo-1551218808-94e220e084d2?w=500" alt="Custom Package" class="w-full h-48 object-cover">
                    <div class="absolute top-0 right-0 bg-[#E75925] text-white px-4 py-2 font-bold text-lg rounded-bl-lg">
                        Custom
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="text-2xl font-bold mb-2 text-gray-800">Custom Package</h3>
                    <p class="text-gray-600 mb-4">Create your perfect menu tailored to your needs</p>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-500">
                            <i class="fas fa-utensils mr-2 text-[#E75925]"></i>
                            Your Choice
                        </span>
                        <button class="view-menu-btn bg-[#E75925] hover:bg-[#d14d1f] text-white px-4 py-2 rounded-lg transition-colors">
                            Contact Us <i class="fas fa-arrow-right ml-2"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Menu Details Modal -->
    <div id="menu-modal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden p-4">
        <div class="bg-white rounded-lg shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-[#E75925] to-[#d14d1f] text-white p-6 flex items-center justify-between">
                <div>
                    <h3 id="modal-package-name" class="text-2xl font-bold mb-1"></h3>
                    <p id="modal-package-price" class="text-lg opacity-90"></p>
                </div>
                <button id="close-menu-modal" class="text-white hover:bg-white hover:text-[#E75925] p-2 rounded-full transition-all">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-100px)]">
                <!-- Package Description -->
                <div id="modal-description" class="mb-6 p-4 bg-gray-50 rounded-lg"></div>

                <!-- Menu Items Grid -->
                <div id="modal-menu-items" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Menu items will be dynamically inserted here -->
                </div>

                <!-- Inclusions -->
                <div class="mt-6 p-4 bg-blue-50 border-l-4 border-blue-500 rounded">
                    <h4 class="font-bold text-blue-800 mb-2 flex items-center gap-2">
                        <i class="fas fa-check-circle"></i>
                        Package Inclusions
                    </h4>
                    <ul id="modal-inclusions" class="text-sm text-blue-700 space-y-1">
                        <!-- Inclusions will be dynamically inserted here -->
                    </ul>
                </div>

                <!-- Book Now Button -->
                <div class="mt-6 text-center">
                    <button onclick="navigateToBooking()" class="bg-[#E75925] hover:bg-[#d14d1f] text-white px-8 py-3 rounded-lg font-semibold text-lg transition-colors shadow-lg transform hover:scale-105">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Book This Package
                    </button>
                </div>
            </div>
        </div>
    </div>

    <style>
    /* Package Card Animations */
    .package-card {
        transition: all 0.3s ease;
    }

    .package-card:hover {
        box-shadow: 0 20px 25px -5px rgba(231, 89, 37, 0.2), 0 10px 10px -5px rgba(231, 89, 37, 0.1);
    }

    /* Menu Item Card Styles */
    .menu-item-card {
        background: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .menu-item-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    .menu-item-card img {
        width: 100%;
        height: 120px;
        object-fit: cover;
    }

    .menu-item-card .content {
        padding: 12px;
    }

    .menu-item-card h5 {
        font-weight: 600;
        margin-bottom: 4px;
        color: #1f2937;
    }

    .menu-item-card p {
        font-size: 0.875rem;
        color: #6b7280;
        line-height: 1.4;
    }

    /* Modal Animation */
    #menu-modal.show {
        animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }
        to {
            opacity: 1;
        }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .package-card {
            transform: none !important;
        }
        
        .package-card:hover {
            transform: none !important;
        }
    }
    </style>
        
        <section id="section-gallery" class="hidden">
            <h2 class="text-2xl font-bold mb-2">Gallery</h2>
            <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
            <p>Gallery content here...</p>
        </section>
        
    <section id="section-settings" class="hidden">
        <h2 class="text-2xl font-bold mb-2">Profile Settings</h2>
        <div class="w-full h-0.5 bg-gray-400 mb-6"></div>
        
        <div class="mx-auto">
            <!-- Profile Card -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex flex-col md:flex-row items-center gap-6">
                    <!-- Avatar Selection -->
                    <div class="flex flex-col items-center">
                        <div class="relative">
                            <img id="profile-avatar" 
                                src="<?php 
                                if (!empty($_SESSION['avatar_url'])) {
                                    echo htmlspecialchars($_SESSION['avatar_url']);
                                } else {
                                    echo 'https://api.dicebear.com/7.x/avataaars/svg?seed=' . ($_SESSION['user_id'] ?? 'default');
                                }
                                ?>" 
                                alt="Profile Avatar" 
                                class="w-32 h-32 rounded-full border-4 border-blue-500 shadow-lg">
                            <button id="change-avatar-btn" 
                                    class="absolute bottom-0 right-0 bg-blue-500 text-white p-2 rounded-full hover:bg-blue-600 transition">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                </svg>
                            </button>
                        </div>
                        <p class="text-sm text-gray-600 mt-2">Click to change avatar</p>
                    </div>

                    <!-- User Information -->
                    <div class="flex-1 text-center md:text-left">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-800" id="profile-name">
                                    <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?>
                                </h3>
                                <p class="text-gray-600 mt-1" id="profile-email">
                                    <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?>
                                </p>
                            </div>
                            <div class="relative self-start">
                                <button id="profile-menu-btn" class="text-gray-600 hover:text-gray-800 p-2 rounded-full hover:bg-gray-100 transition">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                                <div id="profile-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                    <button id="change-password-btn" class="w-full text-left px-4 py-3 hover:bg-gray-50 flex items-center gap-3 transition border-b border-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                        </svg>
                                        <span class="text-gray-700">Change Password</span>
                                    </button>
                                    <button id="toggle-darkmode" class="w-full text-left px-4 py-3 hover:bg-gray-50 flex items-center gap-3 transition border-b border-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                        </svg>
                                        <span class="text-gray-700">Dark Mode</span>
                                    </button>
                                    <button id="dropdown-signout" class="w-full text-left px-4 py-3 hover:bg-gray-50 flex items-center gap-3 rounded-b-lg transition">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span class="text-red-600 font-semibold">Sign Out</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Booking Statistics -->
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-4">
                            <div class="bg-blue-50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-blue-600" id="total-bookings">0</p>
                                <p class="text-xs text-gray-600">Total Events</p>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-3 cursor-pointer hover:bg-purple-100 transition" id="upcoming-events-card">
                                <p class="text-2xl font-bold text-purple-600" id="upcoming-events">0</p>
                                <p class="text-xs text-gray-600">Upcoming</p>
                            </div>
                            <div class="bg-green-50 rounded-lg p-3">
                                <p class="text-2xl font-bold text-green-600" id="total-spent">₱0.00</p>
                                <p class="text-xs text-gray-600">Total Spent</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Event Details -->
            <div id="next-event-card" class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg shadow-md p-6 mb-6 hidden">
                <div class="flex items-center gap-3 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h4 class="text-lg font-semibold text-gray-800">Next Event</h4>
                </div>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Event Date</p>
                        <p class="text-lg font-bold text-purple-700" id="next-event-date">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Time</p>
                        <p class="text-lg font-bold text-purple-700" id="next-event-time">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Celebrant</p>
                        <p class="text-lg font-bold text-gray-800" id="next-event-celebrant">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Event Type</p>
                        <p class="text-lg font-bold text-gray-800 capitalize" id="next-event-type">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Location</p>
                        <p class="text-lg font-bold text-gray-800" id="next-event-location">-</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Days Until Event</p>
                        <p class="text-lg font-bold text-pink-600" id="next-event-countdown">-</p>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h4 class="text-lg font-semibold mb-4">Account Information</h4>
                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Member Since:</span>
                        <span class="font-semibold"><?php echo date('F Y', strtotime($_SESSION['created_at'] ?? 'now')); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Change Password Modal -->
        <div id="password-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6 border-b flex items-center justify-between">
                    <h3 class="text-xl font-bold">Change Password</h3>
                    <button id="close-password-modal" class="text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <form id="change-password-form" class="p-6">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                        <input type="password" id="current-password" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                        <input type="password" id="new-password" required minlength="11"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <p class="text-xs text-gray-500 mt-1">Minimum 11 characters</p>
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                        <input type="password" id="confirm-password" required minlength="11"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="flex gap-3">
                        <button type="button" id="cancel-password-btn" 
                                class="flex-1 px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="flex-1 px-6 py-2 text-white rounded-lg hover:opacity-90 transition"
                                style="background-color: #E75925;">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Password Popup Modal -->
        <div id="password-popup-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60]">
            <div class="bg-white rounded-lg shadow-xl w-80 p-6 text-center">
                <div id="password-popup-icon" class="flex justify-center mb-3"></div>
                <p id="password-popup-message" class="text-base font-semibold text-gray-800 mb-4"></p>
                <button id="close-password-popup" class="px-6 py-2 text-white rounded-lg hover:opacity-90 w-full" style="background-color: #E75925;">
                    OK
                </button>
            </div>
        </div>

        <!-- Avatar Selection Modal -->
        <div id="avatar-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
                <div class="p-6 border-b">
                    <h3 class="text-xl font-bold">Choose Your Avatar</h3>
                </div>
                <div class="p-6 overflow-y-auto max-h-[60vh]">
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-4" id="avatar-grid"></div>
                </div>
                <div class="p-6 border-t flex justify-end gap-3">
                    <button id="cancel-avatar-btn" 
                            class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

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
    </section>

        <section id="section-about" class="hidden">
            <h2 class="text-2xl font-bold mb-2">About Us</h2>
            <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
            <p>About Zaf's Kitchen...</p>
        </section>
    </main>



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

        
    // Enhanced Price Calculator Functions with Multi-Step Sync
    const PACKAGE_PRICES = {
        budget: 200,
        standard: 350,
        premium: 500,
        deluxe: 750,
        luxury: 1000
    };

    let currentPriceData = {
        basePrice: 0,
        additionalPrice: 0,
        guestCount: 0,
        packageType: ''
    };

    function updatePriceCalculator() {
        const guestCount = parseInt(document.getElementById('guest-count')?.value) || 0;
        const packageSelect = document.getElementById('package');
        const packageType = packageSelect?.value || '';
        
        // Update all guest displays across all steps
        updateGuestDisplays(guestCount);
        
        if (!packageType || !guestCount) {
            resetPriceDisplay();
            return;
        }
        
        // Calculate base price
        const basePrice = PACKAGE_PRICES[packageType] * guestCount;
        currentPriceData.basePrice = basePrice;
        currentPriceData.guestCount = guestCount;
        currentPriceData.packageType = packageType;
        
        // Calculate additional items
        let additionalPrice = 0;
        document.querySelectorAll('input[name^="menu_"]:checked').forEach(checkbox => {
            const itemPrice = parseInt(checkbox.dataset.price) || 0;
            additionalPrice += itemPrice * guestCount;
        });
        currentPriceData.additionalPrice = additionalPrice;
        
        // Update all price displays across all steps
        updateAllPriceDisplays(basePrice, additionalPrice, guestCount);
        
        // Update hidden input for form submission
        const totalPrice = basePrice + additionalPrice;
        document.getElementById('total_price').value = totalPrice;
        
        // Add animation to all total displays
        animatePriceUpdate();
    }

    function updateGuestDisplays(guestCount) {
        const guestTexts = [`${guestCount} guests`, `${guestCount} guests`, `${guestCount} guests`];
        
        document.getElementById('guest-display').textContent = guestTexts[0];
        
        const step2Display = document.getElementById('guest-display-step2');
        if (step2Display) step2Display.textContent = guestTexts[1];
        
        const step3Display = document.getElementById('guest-display-step3');
        if (step3Display) step3Display.textContent = guestTexts[2];
    }

    function updateAllPriceDisplays(basePrice, additionalPrice, guestCount) {
        const totalPrice = basePrice + additionalPrice;
        const formattedBase = `₱${basePrice.toLocaleString()}.00`;
        const formattedAdditional = `₱${additionalPrice.toLocaleString()}.00`;
        const formattedTotal = `₱${totalPrice.toLocaleString()}.00`;
        
        // Step 1 displays
        document.getElementById('base-price').textContent = formattedBase;
        const additionalContainer1 = document.getElementById('additional-items-container');
        if (additionalPrice > 0) {
            additionalContainer1.style.display = 'flex';
            document.getElementById('additional-price').textContent = formattedAdditional;
        } else {
            additionalContainer1.style.display = 'none';
        }
        document.getElementById('total-display').textContent = formattedTotal;
        
        // Step 2 displays
        const basePriceStep2 = document.getElementById('base-price-step2');
        if (basePriceStep2) basePriceStep2.textContent = formattedBase;
        
        const additionalContainer2 = document.getElementById('additional-items-container-step2');
        if (additionalContainer2) {
            if (additionalPrice > 0) {
                additionalContainer2.style.display = 'flex';
                const additionalPriceStep2 = document.getElementById('additional-price-step2');
                if (additionalPriceStep2) additionalPriceStep2.textContent = formattedAdditional;
            } else {
                additionalContainer2.style.display = 'none';
            }
        }
        
        const totalDisplayStep2 = document.getElementById('total-display-step2');
        if (totalDisplayStep2) totalDisplayStep2.textContent = formattedTotal;
        
        // Step 3 displays
        const basePriceStep3 = document.getElementById('base-price-step3');
        if (basePriceStep3) basePriceStep3.textContent = formattedBase;
        
        const additionalContainer3 = document.getElementById('additional-items-container-step3');
        if (additionalContainer3) {
            if (additionalPrice > 0) {
                additionalContainer3.style.display = 'flex';
                const additionalPriceStep3 = document.getElementById('additional-price-step3');
                if (additionalPriceStep3) additionalPriceStep3.textContent = formattedAdditional;
            } else {
                additionalContainer3.style.display = 'none';
            }
        }
        
        const totalDisplayStep3 = document.getElementById('total-display-step3');
        if (totalDisplayStep3) totalDisplayStep3.textContent = formattedTotal;
    }

    function animatePriceUpdate() {
        // Add animation class to all total displays
        const totalDisplays = [
            'total-display',
            'total-display-step2', 
            'total-display-step3'
        ];
        
        totalDisplays.forEach(displayId => {
            const element = document.getElementById(displayId);
            if (element) {
                element.classList.add('calculating');
                setTimeout(() => {
                    element.classList.remove('calculating');
                }, 1000);
            }
        });
    }

    function resetPriceDisplay() {
        const priceElements = [
            { id: 'base-price', value: '₱0.00' },
            { id: 'base-price-step2', value: '₱0.00' },
            { id: 'base-price-step3', value: '₱0.00' },
            { id: 'additional-price', value: '₱0.00' },
            { id: 'additional-price-step2', value: '₱0.00' },
            { id: 'additional-price-step3', value: '₱0.00' },
            { id: 'total-display', value: '₱0.00' },
            { id: 'total-display-step2', value: '₱0.00' },
            { id: 'total-display-step3', value: '₱0.00' },
            { id: 'guest-display', value: '0 guests' },
            { id: 'guest-display-step2', value: '0 guests' },
            { id: 'guest-display-step3', value: '0 guests' }
        ];
        
        priceElements.forEach(({ id, value }) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });
        
        // Hide additional items containers
        const additionalContainers = [
            'additional-items-container',
            'additional-items-container-step2',
            'additional-items-container-step3'
        ];
        
        additionalContainers.forEach(containerId => {
            const container = document.getElementById(containerId);
            if (container) container.style.display = 'none';
        });
        
        document.getElementById('total_price').value = '0';
        currentPriceData = { basePrice: 0, additionalPrice: 0, guestCount: 0, packageType: '' };
    }

    // Enhanced step navigation with price sync
    function goToStep(stepNumber) {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.add('hidden');
            step.classList.remove('active');
        });
        
        // Show target step
        const targetStep = document.getElementById(`booking-step${stepNumber}`);
        if (targetStep) {
            targetStep.classList.remove('hidden');
            targetStep.classList.add('active');
        }
        
        // Update progress indicators
        updateStepProgress(stepNumber);
        
        // Sync price displays when navigating
        updatePriceCalculator();
        
        // Scroll to top of form
        document.getElementById('section-book').scrollIntoView({ behavior: 'smooth' });
    }

    function updateStepProgress(activeStep) {
        for (let i = 1; i <= 3; i++) {
            const stepItem = document.getElementById(`step-${i}`);
            const stepLine = stepItem.nextElementSibling;
            
            if (i < activeStep) {
                stepItem.classList.add('completed');
                stepItem.classList.remove('active', 'inactive');
                if (stepLine && stepLine.classList.contains('step-line')) {
                    stepLine.classList.add('completed');
                    stepLine.classList.remove('inactive');
                }
            } else if (i === activeStep) {
                stepItem.classList.add('active');
                stepItem.classList.remove('completed', 'inactive');
            } else {
                stepItem.classList.add('inactive');
                stepItem.classList.remove('active', 'completed');
                if (stepLine && stepLine.classList.contains('step-line')) {
                    stepLine.classList.add('inactive');
                    stepLine.classList.remove('completed');
                }
            }
        }
    }

    // Enhanced Booking Card Generation with Price Display and Delete Button
    function generateBookingCardWithPrice(booking, isPast = false) {
        const eventDate = new Date(booking.event_date);
        const formattedDate = eventDate.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        const startTime12 = formatTimeTo12Hour(booking.start_time.substring(0, 5));
        const endTime12 = formatTimeTo12Hour(booking.end_time.substring(0, 5));
        const timeRange = `${startTime12} - ${endTime12}`;
        
        const createdDate = new Date(booking.created_at);
        const formattedCreatedDate = createdDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Calculate price if total_price exists, otherwise estimate
        let displayPrice = '₱0.00';
        if (booking.total_price && booking.total_price > 0) {
            displayPrice = `₱${parseFloat(booking.total_price).toLocaleString()}`;
        } else if (booking.guest_count && booking.food_package) {
            // Estimate price based on package and guest count
            const packagePrice = PACKAGE_PRICES[booking.food_package] || 0;
            const estimatedPrice = packagePrice * parseInt(booking.guest_count);
            displayPrice = `₱${estimatedPrice.toLocaleString()}`;
        }
        
        const statusClass = `status-${booking.booking_status}`;
        const cardOpacity = isPast ? 'opacity-75' : '';
        
        // Status messages and icons
        let statusIcon = '';
        let statusMessage = '';
        
        switch(booking.booking_status) {
            case 'pending':
                statusIcon = '<i class="fas fa-clock text-yellow-600"></i>';
                statusMessage = isPast ? 'Was pending approval' : 'Waiting for admin approval';
                break;
            case 'approved':
                statusIcon = '<i class="fas fa-check-circle text-green-600"></i>';
                statusMessage = isPast ? 'Event completed successfully' : 'Confirmed! Your event is approved';
                break;
            case 'cancelled':
                statusIcon = '<i class="fas fa-times-circle text-red-600"></i>';
                statusMessage = 'This booking was cancelled';
                break;
        }
        
        const ageDisplay = booking.event_type === 'birthday' && booking.celebrant_age ? 
            ` (${booking.celebrant_age} years old)` : '';
        
        // Only pending bookings can be deleted
        const canDelete = (booking.booking_status === 'pending');
        
        return `
            <div class="booking-card-enhanced ${statusClass} ${cardOpacity} relative p-6">
                <div class="booking-status-indicator"></div>
                
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-xl font-semibold text-gray-800 capitalize">${booking.event_type}${ageDisplay}</h3>
                            <span class="status-badge ${statusClass}">${booking.booking_status}</span>
                            ${isPast ? '<span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">PAST EVENT</span>' : ''}
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                            ${statusIcon}
                            <span>${statusMessage}</span>
                        </div>
                        <div class="text-lg font-medium text-[#E75925] mb-1">
                            <i class="fas fa-star mr-2"></i>
                            Celebrating: ${booking.celebrant_name}
                        </div>
                    </div>
                    <div class="text-right flex flex-col items-end gap-2">
                        <div class="booking-price-tag">
                            ${displayPrice}
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-gray-500">Booking ID</div>
                            <div class="font-mono text-sm">#${booking.id.toString().padStart(4, '0')}</div>
                        </div>
                        ${canDelete ? `
                        <button onclick="showDeleteModal(${booking.id}, '${booking.celebrant_name}', '${booking.event_type}')" 
                            class="mt-2 bg-red-500 hover:bg-red-600 text-white px-3 py-1 text-xs rounded-lg transition-colors" 
                            title="Delete this booking">
                            <i class="fas fa-trash mr-1"></i>Cancel     
                        </button>
                        ` : ''}
                    </div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-calendar text-[#E75925] w-4"></i>
                            <span class="font-medium">Date:</span>
                            <span>${formattedDate}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-clock text-[#E75925] w-4"></i>
                            <span class="font-medium">Time:</span>
                            <span>${timeRange}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-users text-[#E75925] w-4"></i>
                            <span class="font-medium">Guests:</span>
                            <span>${booking.guest_count} people</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-utensils text-[#E75925] w-4"></i>
                            <span class="font-medium">Package:</span>
                            <span class="capitalize">${booking.food_package}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-user text-[#E75925] w-4"></i>
                            <span class="font-medium">Contact:</span>
                            <span>${booking.full_name}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-map-marker-alt text-[#E75925] w-4"></i>
                            <span class="font-medium">Location:</span>
                            <span>${booking.location || 'Not specified'}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-palette text-[#E75925] w-4"></i>
                            <span class="font-medium">Theme:</span>
                            <span class="capitalize">${booking.event_theme === 'custom' ? (booking.custom_theme || 'Custom') : booking.event_theme}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-calendar-plus text-[#E75925] w-4"></i>
                            <span class="font-medium">Booked:</span>
                            <span>${formattedCreatedDate}</span>
                        </div>
                    </div>
                </div>
                
                ${booking.theme_suggestions ? `
                <div class="mt-4 p-3 bg-gray-50 border-l-4 border-[#E75925] rounded">
                    <div class="flex items-start gap-2 text-sm">
                        <i class="fas fa-lightbulb text-[#E75925] mt-0.5"></i>
                        <div>
                            <span class="font-medium text-gray-700">Special Requests:</span>
                            <p class="text-gray-600 mt-1">${booking.theme_suggestions}</p>
                        </div>
                    </div>
                </div>
                ` : ''}
                
                ${!isPast ? (booking.booking_status === 'approved' ? `
                <div class="mt-4 p-4 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-check-circle text-green-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-green-800">Event Confirmed!</div>
                            <p class="text-sm text-green-700 mt-1">Your booking has been approved. We look forward to catering your event!</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-green-600 font-medium">Total: ${displayPrice}</div>
                        </div>
                    </div>
                </div>
                ` : booking.booking_status === 'pending' ? `
                <div class="mt-4 p-4 bg-gradient-to-r from-yellow-50 to-yellow-100 border border-yellow-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-hourglass-half text-yellow-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-yellow-800">Pending Approval</div>
                            <p class="text-sm text-yellow-700 mt-1">We're reviewing your booking. You'll be notified once it's approved!</p>
                        </div>
                        <div class="text-right">
                            <div class="text-sm text-yellow-600 font-medium">Est. Total: ${displayPrice}</div>
                        </div>
                    </div>
                </div>
                ` : `
                <div class="mt-4 p-4 bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-lg">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-ban text-red-600 text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-red-800">Booking Cancelled</div>
                            <p class="text-sm text-red-700 mt-1">This booking was cancelled. Contact us if you have questions.</p>
                        </div>
                    </div>
                </div>  
                `) : ''}
            </div>
        `;
    }

    // Enhanced booking display function
    function displayBookingsWithPrice(bookings) {
        const container = document.getElementById('bookings-container');
        
        if (bookings.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">No Bookings Yet</h3>
                    <p class="text-gray-500 mb-6">You haven't made any bookings yet. Start by booking your first event!</p>
                    <button onclick="showBookNowSection()" class="bg-[#E75925] hover:bg-[#d14d1f] text-white px-6 py-3 rounded-lg shadow-md transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Make Your First Booking
                    </button>
                </div>
            `;
            return;
        }
        
        // Separate bookings
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const upcomingBookings = bookings.filter(booking => {
            const eventDate = new Date(booking.event_date);
            eventDate.setHours(0, 0, 0, 0);
            return eventDate >= today;
        }).sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
        
        const pastBookings = bookings.filter(booking => {
            const eventDate = new Date(booking.event_date);
            eventDate.setHours(0, 0, 0, 0);
            return eventDate < today;
        }).sort((a, b) => new Date(b.event_date) - new Date(a.event_date));
        
        let bookingsHtml = '';
        
        // Calculate total spent
        let totalSpent = 0;
        bookings.forEach(booking => {
            if (booking.booking_status === 'approved' && booking.total_price) {
                totalSpent += parseFloat(booking.total_price) || 0;
            }
        });
        
        if (totalSpent > 0) {
            bookingsHtml += `
                <div class="bg-gradient-to-r from-[#E75925] to-[#d14d1f] text-white p-6 rounded-lg shadow-lg mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold">Your Event History</h3>
                            <p class="opacity-90">Total spent on approved events</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold">₱${totalSpent.toLocaleString()}</div>
                            <div class="text-sm opacity-80">${bookings.filter(b => b.booking_status === 'approved').length} events</div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Upcoming bookings
        if (upcomingBookings.length > 0) {
            bookingsHtml += `
                <div class="mb-8">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar-week text-[#E75925]"></i>
                        Upcoming Events (${upcomingBookings.length})
                    </h3>
                    <div class="space-y-6">
            `;
            
            upcomingBookings.forEach(booking => {
                bookingsHtml += generateBookingCardWithPrice(booking);
            });
            
            bookingsHtml += `</div></div>`;
        }
        
        // Past bookings
        if (pastBookings.length > 0) {
            bookingsHtml += `
                <div class="mb-8">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-gray-600"></i>
                        Past Events (${pastBookings.length})
                    </h3>
                    <div class="space-y-6">
            `;
            
            pastBookings.forEach(booking => {
                bookingsHtml += generateBookingCardWithPrice(booking, true);
            });
            
            bookingsHtml += `</div></div>`;
        }
        
        container.innerHTML = bookingsHtml;
    }

    // Global variable for tracking delete booking ID
    let currentDeleteBookingId = null;

    // Show delete confirmation modal
    function showDeleteModal(bookingId, celebrantName, eventType) {
        currentDeleteBookingId = bookingId;
        
        const modal = document.getElementById('delete-modal');
        if (modal) {
            const modalContent = modal.querySelector('p');
            if (modalContent) {
                modalContent.innerHTML = `
                    Are you sure you want to cancel the <strong>${eventType}</strong> booking for <strong>${celebrantName}</strong>? 
                    This action cannot be undone.
                `;
            }
            modal.classList.remove('hidden');
        } else {
            console.error('Delete modal not found. Make sure you have the modal HTML in your page.');
        }
    }

    // Hide delete confirmation modal
    function hideDeleteModal() {
        const modal = document.getElementById('delete-modal');
        if (modal) {
            modal.classList.add('hidden');
        }
        currentDeleteBookingId = null;
    }

    // Delete booking function
    function deleteBooking(bookingId) {
        const formData = new FormData();
        formData.append('action', 'delete_booking');
        formData.append('booking_id', bookingId);
        
        fetch(window.location.pathname, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage('success', 'Success', data.message);
                loadMyBookings(); // Refresh the bookings list
            } else {
                showMessage('error', 'Delete Failed', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showMessage('error', 'Network Error', 'Please check your connection and try again.');
        });
    }

    // FIXED: Create success modal function to prevent duplicate event listeners
    function showSuccessModal() {
        // Remove any existing modal
        const existingModal = document.getElementById('success-modal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const totalCost = currentPriceData.basePrice + currentPriceData.additionalPrice;
        
        const modalHTML = `
            <div id="success-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-8 rounded-lg shadow-xl max-w-md w-full mx-4">
                    <div class="text-center">
                        <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                            <i class="fas fa-check text-green-600 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Booking Submitted Successfully!</h3>
                        <div class="text-sm text-gray-500 mb-6">
                            <p class="mb-2">Your booking has been submitted with a total cost of <strong>₱${totalCost.toLocaleString()}</strong>.</p>
                            <div class="text-left bg-gray-50 p-4 rounded-lg">
                                <p class="font-semibold mb-2">What happens next:</p>
                                <ul class="text-xs space-y-1">
                                    <li>• Our admin will review your booking details</li>
                                    <li>• You'll receive confirmation once approved</li>
                                    <li>• Payment details will be provided upon approval</li>
                                </ul>
                                <p class="italic mt-2 text-xs">Thank you for choosing Zaf's Kitchen!</p>
                            </div>
                        </div>
                        <button id="success-modal-ok" 
                            class="w-full bg-[#E75925] hover:bg-[#d14d1f] text-white px-4 py-2 rounded-lg transition-colors">
                            OK
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Add single event listener for OK button with immediate removal
        const okButton = document.getElementById('success-modal-ok');
        okButton.addEventListener('click', function handleModalClose() {
            // Remove the event listener immediately
            okButton.removeEventListener('click', handleModalClose);
            
            // Close modal
            document.getElementById('success-modal').remove();
            
            // Navigate to My Bookings section
            hideAllSections();
            document.querySelectorAll("nav a").forEach(l => l.classList.remove("active-nav"));
            document.querySelector('nav a[href="#"]:nth-child(2)').classList.add("active-nav");
            document.getElementById("section-mybookings").classList.remove("hidden");
            loadMyBookings();
        });
    }


    // MENU PACKAGES DATA AND FUNCTIONALITY
    // Add this JavaScript to your existing script section

    // Menu Packages Data
    const menuPackages = {
        budget: {
            name: "Budget Package",
            price: "₱200/person",
            priceValue: 200,
            description: "Perfect for intimate gatherings and small celebrations. Simple yet delicious Filipino favorites that won't break the bank.",
            dishes: [
                {
                    name: "Chicken Adobo",
                    description: "Classic Filipino chicken in soy sauce and vinegar",
                    image: "https://images.unsplash.com/photo-1626132647523-66f5bf380027?w=400"
                },
                {
                    name: "Pancit Canton",
                    description: "Traditional stir-fried noodles with vegetables",
                    image: "https://images.unsplash.com/photo-1612929633738-8fe44f7ec841?w=400"
                },
                {
                    name: "Lumpiang Shanghai",
                    description: "Crispy Filipino spring rolls",
                    image: "https://images.unsplash.com/photo-1534422298391-e4f8c172dddb?w=400"
                },
                {
                    name: "Steamed White Rice",
                    description: "Fluffy jasmine rice",
                    image: "https://images.unsplash.com/photo-1516684732162-798a0062be99?w=400"
                },
                {
                    name: "Fresh Fruit Salad",
                    description: "Mixed seasonal fruits in cream",
                    image: "https://images.unsplash.com/photo-1564093497595-593b96d80180?w=400"
                }
            ],
            inclusions: [
                "Standard table setup with basic linens",
                "Disposable plates and utensils",
                "Basic beverage station (water)",
                "1 server per 30 guests",
                "Setup and cleanup service"
            ]
        },
        standard: {
            name: "Standard Package",
            price: "₱350/person",
            priceValue: 350,
            description: "Great value for medium-sized events and parties. A well-rounded selection of Filipino dishes that everyone will love.",
            dishes: [
                {
                    name: "Lechon Kawali",
                    description: "Crispy deep-fried pork belly",
                    image: "https://images.unsplash.com/photo-1544025162-d76694265947?w=400"
                },
                {
                    name: "Sweet & Sour Fish",
                    description: "Crispy fish fillet in tangy sauce",
                    image: "https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=400"
                },
                {
                    name: "Beef Caldereta",
                    description: "Filipino beef stew in tomato sauce",
                    image: "https://images.unsplash.com/photo-1588168333986-5078d3ae3976?w=400"
                },
                {
                    name: "Mixed Vegetables",
                    description: "Sautéed seasonal vegetables",
                    image: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400"
                },
                {
                    name: "Pancit Bihon",
                    description: "Rice noodles with vegetables and meat",
                    image: "https://images.unsplash.com/photo-1582169296194-e4d644c48063?w=400"
                },
                {
                    name: "Fried Rice",
                    description: "Garlic fried rice",
                    image: "https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400"
                },
                {
                    name: "Leche Flan",
                    description: "Creamy Filipino caramel custard",
                    image: "https://images.unsplash.com/photo-1587314168485-3236d6710814?w=400"
                },
                {
                    name: "Iced Tea",
                    description: "Refreshing sweetened iced tea",
                    image: "https://images.unsplash.com/photo-1556679343-c7306c1976bc?w=400"
                }
            ],
            inclusions: [
                "Enhanced table setup with quality linens",
                "China plates and silverware",
                "Full beverage station (water, juice, iced tea)",
                "1 server per 25 guests",
                "Setup, service, and cleanup",
                "Basic centerpieces"
            ]
        },
        premium: {
            name: "Premium Package",
            price: "₱500/person",
            priceValue: 500,
            description: "Premium selection for memorable occasions. Features upgraded dishes and better presentation for a truly special event.",
            dishes: [
                {
                    name: "Whole Lechon Belly",
                    description: "Roasted pork belly with crispy skin",
                    image: "https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=400"
                },
                {
                    name: "Grilled Prawns",
                    description: "Butter-garlic grilled prawns",
                    image: "https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=400"
                },
                {
                    name: "Beef Salpicao",
                    description: "Tender beef cubes in garlic sauce",
                    image: "https://images.unsplash.com/photo-1558030006-450675393462?w=400"
                },
                {
                    name: "Chicken Cordon Bleu",
                    description: "Breaded chicken with ham and cheese",
                    image: "https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400"
                },
                {
                    name: "Kare-Kare",
                    description: "Filipino oxtail peanut stew",
                    image: "https://images.unsplash.com/photo-1455619452474-d2be8b1e70cd?w=400"
                },
                {
                    name: "Seafood Palabok",
                    description: "Noodles with seafood toppings",
                    image: "https://images.unsplash.com/photo-1617093727343-374698b1b08d?w=400"
                },
                {
                    name: "Caesar Salad",
                    description: "Fresh greens with parmesan",
                    image: "https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400"
                },
                {
                    name: "Garlic Rice",
                    description: "Aromatic garlic fried rice",
                    image: "https://images.unsplash.com/photo-1603133872878-684f208fb84b?w=400"
                },
                {
                    name: "Buko Pandan",
                    description: "Young coconut dessert",
                    image: "https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400"
                },
                {
                    name: "Premium Drinks",
                    description: "Assorted juices and soft drinks",
                    image: "https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400"
                }
            ],
            inclusions: [
                "Premium table setup with elegant linens",
                "Fine china and silverware",
                "Complete beverage station with premium drinks",
                "1 server per 20 guests",
                "Professional setup and service",
                "Elegant centerpieces and decorations",
                "Cake cutting service"
            ]
        },
        deluxe: {
            name: "Deluxe Package",
            price: "₱750/person",
            priceValue: 750,
            description: "Exceptional dining for special celebrations. Premium ingredients and sophisticated presentation for discerning guests.",
            dishes: [
                {
                    name: "Whole Roasted Lechon",
                    description: "Traditional roasted pig (portion)",
                    image: "https://images.unsplash.com/photo-1544025162-d76694265947?w=400"
                },
                {
                    name: "Lobster Thermidor",
                    description: "Baked lobster in creamy sauce",
                    image: "https://images.unsplash.com/photo-1625938145312-598290a7919b?w=400"
                },
                {
                    name: "Prime Beef Tenderloin",
                    description: "Grilled beef in mushroom sauce",
                    image: "https://images.unsplash.com/photo-1546833998-877b37c2e5c6?w=400"
                },
                {
                    name: "Salmon Belly",
                    description: "Grilled Norwegian salmon",
                    image: "https://images.unsplash.com/photo-1467003909585-2f8a72700288?w=400"
                },
                {
                    name: "Roasted Chicken",
                    description: "Herb-roasted whole chicken",
                    image: "https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400"
                },
                {
                    name: "Seafood Paella",
                    description: "Spanish rice with seafood",
                    image: "https://images.unsplash.com/photo-1534080564583-6be75777b70a?w=400"
                },
                {
                    name: "Prawn Pasta",
                    description: "Creamy prawn linguine",
                    image: "https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400"
                },
                {
                    name: "Garden Fresh Salad Bar",
                    description: "Assorted fresh salads",
                    image: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400"
                },
                {
                    name: "Spanish Bread Pudding",
                    description: "Rich caramel bread pudding",
                    image: "https://images.unsplash.com/photo-1606313564200-e75d5e30476c?w=400"
                },
                {
                    name: "Ice Cream Station",
                    description: "Assorted premium ice cream",
                    image: "https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400"
                },
                {
                    name: "Assorted Pastries",
                    description: "Mini cakes and tarts",
                    image: "https://images.unsplash.com/photo-1587314168485-3236d6710814?w=400"
                },
                {
                    name: "Premium Beverages",
                    description: "Fresh juices and specialty drinks",
                    image: "https://images.unsplash.com/photo-1546548970-71785318a17b?w=400"
                }
            ],
            inclusions: [
                "Luxury table setup with premium linens",
                "Fine china, crystal glasses, and gold silverware",
                "Full bar setup with mixologist",
                "1 server per 15 guests",
                "Professional catering team",
                "Designer centerpieces and venue decoration",
                "Cake cutting and serving",
                "Photo-worthy food presentation",
                "Waitstaff in formal attire"
            ]
        },
        luxury: {
            name: "Luxury Package",
            price: "₱1000/person",
            priceValue: 1000,
            description: "Ultimate culinary experience for grand events. The finest ingredients, exceptional service, and stunning presentation.",
            dishes: [
                {
                    name: "Whole Roasted Lechon",
                    description: "Premium roasted pig with crispy skin",
                    image: "https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=400"
                },
                {
                    name: "Fresh Oysters on Ice",
                    description: "Premium fresh oysters",
                    image: "https://images.unsplash.com/photo-1626012698431-a204e46a2c8e?w=400"
                },
                {
                    name: "Wagyu Beef",
                    description: "Japanese wagyu with truffle sauce",
                    image: "https://images.unsplash.com/photo-1544025162-d76694265947?w=400"
                },
                {
                    name: "Whole King Crab",
                    description: "Steamed Alaskan king crab",
                    image: "https://images.unsplash.com/photo-1565680018434-b513d5e5fd47?w=400"
                },
                {
                    name: "Duck Confit",
                    description: "French-style duck leg",
                    image: "https://images.unsplash.com/photo-1598103442097-8b74394b95c6?w=400"
                },
                {
                    name: "Seared Tuna",
                    description: "Sesame-crusted ahi tuna",
                    image: "https://images.unsplash.com/photo-1546833999-b9f581a1996d?w=400"
                },
                {
                    name: "Live Pasta Station",
                    description: "Made-to-order pasta dishes",
                    image: "https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400"
                },
                {
                    name: "Live Carving Station",
                    description: "Prime rib and roasted meats",
                    image: "https://images.unsplash.com/photo-1558030006-450675393462?w=400"
                },
                {
                    name: "Artisan Cheese Board",
                    description: "International cheese selection",
                    image: "https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=400"
                },
                {
                    name: "Gourmet Sushi Platter",
                    description: "Assorted premium sushi",
                    image: "https://images.unsplash.com/photo-1579584425555-c3ce17fd4ec2?w=400"
                },
                {
                    name: "Premium Salad Bar",
                    description: "Organic greens with imported dressings",
                    image: "https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400"
                },
                {
                    name: "Dessert Buffet",
                    description: "Selection of French pastries",
                    image: "https://images.unsplash.com/photo-1587314168485-3236d6710814?w=400"
                },
                {
                    name: "Chocolate Fountain",
                    description: "Belgian chocolate with fruits",
                    image: "https://images.unsplash.com/photo-1511381939415-e44015466834?w=400"
                },
                {
                    name: "Gelato Bar",
                    description: "Italian gelato in various flavors",
                    image: "https://images.unsplash.com/photo-1563805042-7684c019e1cb?w=400"
                },
                {
                    name: "Premium Bar",
                    description: "Full bar with premium spirits",
                    image: "https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?w=400"
                }
            ],
            inclusions: [
                "Ultra-luxury table setup with designer linens",
                "Premium china, crystal, and gold-plated silverware",
                "Full premium bar with professional bartender",
                "1 server per 10 guests plus dedicated event coordinator",
                "Professional catering team in formal attire",
                "Custom centerpieces and complete venue transformation",
                "Live cooking stations with chefs",
                "Cake cutting and champagne service",
                "Red carpet entrance setup",
                "Professional food styling and presentation",
                "Complimentary event planning consultation",
                "Valet parking coordination"
            ]
        },
        custom: {
            name: "Custom Package",
            price: "Contact Us",
            priceValue: 0,
            description: "Create your perfect menu tailored to your specific needs and preferences. Work with our culinary team to design a unique experience.",
            dishes: [
                {
                    name: "Your Choice",
                    description: "Select from our extensive menu",
                    image: "https://images.unsplash.com/photo-1551218808-94e220e084d2?w=400"
                }
            ],
            inclusions: [
                "Personalized menu planning session",
                "Flexible dish selection from any package",
                "Custom dietary accommodations",
                "Tailored service level",
                "Custom decoration themes",
                "Flexible pricing based on selections"
            ]
        }
    };

    // Initialize Menu Package Functionality
    document.addEventListener('DOMContentLoaded', function() {
        initializeMenuPackages();
    });

    function initializeMenuPackages() {
        // Add click event to all package cards
        const packageCards = document.querySelectorAll('.package-card');
        packageCards.forEach(card => {
            const viewBtn = card.querySelector('.view-menu-btn');
            viewBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const packageType = card.getAttribute('data-package');
                openMenuModal(packageType);
            });
        });

        // Close modal button
        const closeBtn = document.getElementById('close-menu-modal');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeMenuModal);
        }

        // Close modal when clicking outside
        const modal = document.getElementById('menu-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeMenuModal();
                }
            });
        }
    }

    function openMenuModal(packageType) {
        const modal = document.getElementById('menu-modal');
        const packageData = menuPackages[packageType];
        
        if (!packageData || !modal) return;

        // Store current package type for booking
        modal.setAttribute('data-current-package', packageType);

        // Update modal header
        document.getElementById('modal-package-name').textContent = packageData.name;
        document.getElementById('modal-package-price').textContent = packageData.price;

        // Update description
        document.getElementById('modal-description').innerHTML = `
            <p class="text-gray-700 leading-relaxed">${packageData.description}</p>
        `;

        // Update menu items
        const menuItemsContainer = document.getElementById('modal-menu-items');
        menuItemsContainer.innerHTML = '';
        
        packageData.dishes.forEach(dish => {
            const dishCard = document.createElement('div');
            dishCard.className = 'menu-item-card';
            dishCard.innerHTML = `
                <img src="${dish.image}" alt="${dish.name}" onerror="this.src='https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400'">
                <div class="content">
                    <h5>${dish.name}</h5>
                    <p>${dish.description}</p>
                </div>
            `;
            menuItemsContainer.appendChild(dishCard);
        });

        // Update inclusions
        const inclusionsContainer = document.getElementById('modal-inclusions');
        inclusionsContainer.innerHTML = '';
        
        packageData.inclusions.forEach(inclusion => {
            const li = document.createElement('li');
            li.innerHTML = `<i class="fas fa-check mr-2"></i>${inclusion}`;
            inclusionsContainer.appendChild(li);
        });

        // Update the Book button with package info
        const bookBtn = document.getElementById('book-package-btn');
        if (bookBtn) {
            // Remove old event listener by cloning
            const newBookBtn = bookBtn.cloneNode(true);
            bookBtn.parentNode.replaceChild(newBookBtn, bookBtn);
            
            // Add new event listener with package type
            newBookBtn.addEventListener('click', function() {
                navigateToBooking(packageType);
            });
        }

        // Show modal with animation
        modal.classList.remove('hidden');
        modal.classList.add('show');
        document.body.style.overflow = 'hidden'; // Prevent background scrolling
    }

    function closeMenuModal() {
        const modal = document.getElementById('menu-modal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('show');
            document.body.style.overflow = ''; // Restore scrolling
        }
    }

    function navigateToBooking(selectedPackage) {
        // Get the package type from the modal if not provided
        if (!selectedPackage) {
            const modalTitle = document.getElementById('modal-package-name').textContent;
            // Convert package name to value (e.g., "Budget Package" -> "budget")
            selectedPackage = modalTitle.toLowerCase().replace(' package', '').trim();
        }
        
        closeMenuModal();
        
        // Navigate to Book Now section
        hideAllSections();
        document.querySelectorAll("nav a").forEach(l => l.classList.remove("active-nav"));
        
        // Find and activate the Book Now nav item (assuming it's the first nav link)
        const bookNavLink = document.querySelector('nav a[href="#"]');
        if (bookNavLink) {
            bookNavLink.classList.add("active-nav");
        }
        
        const bookSection = document.getElementById("section-book");
        if (bookSection) {
            bookSection.classList.remove("hidden");
            
            // Auto-select the package in the booking form
            setTimeout(() => {
                selectPackageInForm(selectedPackage);
            }, 100);
            
            // Scroll to top smoothly
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    }

    // Helper function to show specific package in booking form
    function selectPackageInForm(packageType) {
        const packageSelect = document.getElementById('package');
        if (packageSelect) {
            packageSelect.value = packageType;
            
            // Trigger change event to update price calculator
            const changeEvent = new Event('change', { bubbles: true });
            packageSelect.dispatchEvent(changeEvent);
            
            // Also trigger input event for compatibility
            const inputEvent = new Event('input', { bubbles: true });
            packageSelect.dispatchEvent(inputEvent);
            
            // Force update the price calculator if function exists
            if (typeof updatePriceCalculator === 'function') {
                updatePriceCalculator();
            }
            
            // Visual feedback - highlight the selected package
            packageSelect.classList.add('ring-2', 'ring-[#E75925]');
            setTimeout(() => {
                packageSelect.classList.remove('ring-2', 'ring-[#E75925]');
            }, 2000);
            
            // Scroll to the package field
            packageSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Add keyboard support for modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('menu-modal');
            if (modal && !modal.classList.contains('hidden')) {
                closeMenuModal();
            }
        }
    });

    // Export functions for use in other parts of the application
    window.openMenuModal = openMenuModal;
    window.closeMenuModal = closeMenuModal;
    window.navigateToBooking = navigateToBooking;
    window.selectPackageInForm = selectPackageInForm;




    // Helper function to format time
    function formatTimeTo12Hour(time24) {
        if (!time24) return '';
        const [hours, minutes] = time24.split(':');
        const hour = parseInt(hours);
        const ampm = hour >= 12 ? 'PM' : 'AM';
        const hour12 = hour % 12 || 12;
        return `${hour12}:${minutes} ${ampm}`;
    }

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

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleSidebar);
    }
    if (backdrop) {
        backdrop.addEventListener('click', toggleSidebar);
    }

    // Navigation functionality
    const navMap = {
        "Book Now": "section-book",
        "My Bookings": "section-mybookings",
        "Menu Packages": "section-menu",
        "Gallery": "section-gallery",
        "Available Schedule": "section-schedule",
        "Profile Settings": "section-settings",
        "About Us": "section-about"
    };

    function hideAllSections() {
        document.querySelectorAll("main section").forEach(sec => sec.classList.add("hidden"));
    }

    // Save current section to localStorage
    function saveCurrentSection(sectionId) {
        localStorage.setItem('currentSection', sectionId);
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
                // Save to localStorage
                saveCurrentSection(sectionId);
                
                document.getElementById(sectionId).classList.remove("hidden");
                
                if (sectionId === 'section-book') {
                    resetBookingForm();
                } else if (sectionId === 'section-schedule') {
                    loadCalendar();
                } else if (sectionId === 'section-mybookings') {
                    loadMyBookings();
                } else if (sectionId === 'section-settings') {
                    loadProfileSettings();
                    loadBookingStats();
                }
            }
            document.getElementById("section-dashboard").classList.add("hidden");
            if (window.innerWidth < 1024) toggleSidebar();
        });
    });


    // Initialize
    hideAllSections();
    const dashboardSection = document.getElementById("section-dashboard");
    if (dashboardSection) {
        dashboardSection.classList.remove("hidden");
    }

    // Load My Bookings function
    function loadMyBookings() {
        const container = document.getElementById('bookings-container');
        
        // Show loading state
        container.innerHTML = `
            <div class="text-center py-8">
                <div class="loading-spinner mx-auto"></div>
                <p class="text-gray-600 mt-2">Loading your bookings...</p>
            </div>
        `;
        
        fetch(window.location.pathname + '?action=get_my_bookings')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(bookings => {
                displayBookings(bookings);
            })
            .catch(error => {
                console.error('Error loading bookings:', error);
                container.innerHTML = `
                    <div class="text-center py-8">
                        <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                        <p class="text-gray-600">Failed to load bookings. Please try again.</p>
                        <button onclick="loadMyBookings()" class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                            Retry
                        </button>
                    </div>
                `;
            });
    }

    // Display bookings function
    function displayBookings(bookings) {
        const container = document.getElementById('bookings-container');
        
        if (bookings.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <i class="fas fa-calendar-times text-gray-300 text-6xl mb-4"></i>
                    <h3 class="text-xl font-semibold mb-2">No Bookings Yet</h3>
                    <p class="text-gray-500 mb-6">You haven't made any bookings yet. Start by booking your first event!</p>
                    <button onclick="showBookNowSection()" class="bg-[#E75925] hover:bg-[#d14d1f] text-white px-6 py-3 rounded-lg shadow-md transition-colors">
                        <i class="fas fa-plus mr-2"></i>
                        Make Your First Booking
                    </button>
                </div>
            `;
            return;
        }
        
        // Separate upcoming and past bookings
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        const upcomingBookings = bookings.filter(booking => {
            const eventDate = new Date(booking.event_date);
            eventDate.setHours(0, 0, 0, 0);
            return eventDate >= today;
        }).sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
        
        const pastBookings = bookings.filter(booking => {
            const eventDate = new Date(booking.event_date);
            eventDate.setHours(0, 0, 0, 0);
            return eventDate < today;
        }).sort((a, b) => new Date(b.event_date) - new Date(a.event_date));
        
        let bookingsHtml = '';
        
        // Upcoming Bookings Section
        if (upcomingBookings.length > 0) {
            bookingsHtml += `
                <div class="mb-8">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-calendar-week text-[#E75925]"></i>
                        Upcoming Events (${upcomingBookings.length})
                    </h3>
                    <div class="space-y-4">
            `;
            
            upcomingBookings.forEach(booking => {
                bookingsHtml += generateBookingCard(booking);
            });
            
            bookingsHtml += `
                    </div>
                </div>
            `;
        }
        
        // Past Bookings Section
        if (pastBookings.length > 0) {
            bookingsHtml += `
                <div class="mb-8">
                    <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <i class="fas fa-history text-gray-600"></i>
                        Past Events (${pastBookings.length})
                    </h3>
                    <div class="space-y-4">
            `;
            
            pastBookings.forEach(booking => {
                bookingsHtml += generateBookingCard(booking, true);
            });
            
            bookingsHtml += `
                    </div>
                </div>
            `;
        }
        
        container.innerHTML = bookingsHtml;
    }

    function generateBookingCard(booking, isPast = false) {
        const eventDate = new Date(booking.event_date);
        const formattedDate = eventDate.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        
        // Convert times to 12-hour format
        const startTime12 = formatTimeTo12Hour(booking.start_time.substring(0, 5));
        const endTime12 = formatTimeTo12Hour(booking.end_time.substring(0, 5));
        const timeRange = `${startTime12} - ${endTime12}`;
        
        const createdDate = new Date(booking.created_at);
        const formattedCreatedDate = createdDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        
        // Create proper border style based on status
        let borderStyle = '';
        let cardOpacity = '';
        
        switch(booking.booking_status) {
            case 'approved':
                borderStyle = 'border-l-4 border-l-green-500 border-t-2 border-r-2 border-b-2 border-gray-300';
                break;
            case 'pending':
                borderStyle = 'border-l-4 border-l-yellow-500 border-t-2 border-r-2 border-b-2 border-gray-300';
                break;
            case 'cancelled':
                borderStyle = 'border-l-4 border-l-red-500 border-t-2 border-r-2 border-b-2 border-gray-300';
                cardOpacity = 'opacity-70';
                break;
            default:
                borderStyle = 'border-2 border-gray-300';
        }
        
        if (isPast) {
            cardOpacity = cardOpacity ? 'opacity-50' : 'opacity-75';
        }
        
        const statusClass = `status-${booking.booking_status}`;
        const cardClass = `booking-card ${statusClass} bg-white p-6 rounded-lg shadow-lg ${borderStyle} ${cardOpacity}`;
        
        // Determine status icon and message
        let statusIcon = '';
        let statusMessage = '';
        
        switch(booking.booking_status) {
            case 'pending':
                statusIcon = '<i class="fas fa-clock text-yellow-600"></i>';
                statusMessage = isPast ? 'Was pending approval' : 'Waiting for admin approval';
                break;
            case 'approved':
                statusIcon = '<i class="fas fa-check-circle text-green-600"></i>';
                statusMessage = isPast ? 'Event completed successfully' : 'Confirmed! Your event is approved';
                break;
            case 'cancelled':
                statusIcon = '<i class="fas fa-times-circle text-red-600"></i>';
                statusMessage = 'This booking was cancelled';
                break;
        }
        
        // Age display for birthday events
        const ageDisplay = booking.event_type === 'birthday' && booking.celebrant_age ? 
            ` (${booking.celebrant_age} years old)` : '';
        
        return `
            <div class="${cardClass}">
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-2">
                            <h3 class="text-xl font-semibold text-gray-800 capitalize">${booking.event_type}${ageDisplay}</h3>
                            <span class="status-badge ${statusClass}">${booking.booking_status}</span>
                            ${isPast ? '<span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded-full">PAST EVENT</span>' : ''}
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                            ${statusIcon}
                            <span>${statusMessage}</span>
                        </div>
                        <div class="text-lg font-medium text-[#E75925] mb-1">
                            <i class="fas fa-star mr-2"></i>
                            Celebrating: ${booking.celebrant_name}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-gray-500">Booking ID</div>
                        <div class="font-mono text-sm">#${booking.id.toString().padStart(4, '0')}</div>
                    </div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-calendar text-[#E75925] w-4"></i>
                            <span class="font-medium">Date:</span>
                            <span>${formattedDate}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-clock text-[#E75925] w-4"></i>
                            <span class="font-medium">Time:</span>
                            <span>${timeRange}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-users text-[#E75925] w-4"></i>
                            <span class="font-medium">Guests:</span>
                            <span>${booking.guest_count} people</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-utensils text-[#E75925] w-4"></i>
                            <span class="font-medium">Package:</span>
                            <span class="capitalize">${booking.food_package}</span>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-user text-[#E75925] w-4"></i>
                            <span class="font-medium">Contact:</span>
                            <span>${booking.full_name}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-palette text-[#E75925] w-4"></i>
                            <span class="font-medium">Theme:</span>
                            <span class="capitalize">${booking.event_theme === 'custom' ? (booking.custom_theme || 'Custom') : booking.event_theme}</span>
                        </div>
                        <div class="flex items-center gap-2 text-sm">
                            <i class="fas fa-calendar-plus text-[#E75925] w-4"></i>
                            <span class="font-medium">Booked:</span>
                            <span>${formattedCreatedDate}</span>
                        </div>
                    </div>
                </div>
                
                ${booking.theme_suggestions ? `
                <div class="mt-3 p-3 bg-gray-50 border-l-4 border-[#E75925] rounded">
                    <div class="flex items-start gap-2 text-sm">
                        <i class="fas fa-lightbulb text-[#E75925] mt-0.5"></i>
                        <div>
                            <span class="font-medium text-gray-700">Special Requests:</span>
                            <p class="text-gray-600 mt-1">${booking.theme_suggestions}</p>
                        </div>
                    </div>
                </div>
                ` : ''}
                
            ${!isPast ? (booking.booking_status === 'approved' ? `
                <div class="mt-4 p-3">
                    <div class="flex items-center gap-2 text-green-800">
                        <i class="fas fa-check-circle"></i>
                        <span class="font-semibold">Event Confirmed!</span>
                    </div>
                    <p class="text-sm text-green-700 mt-1">Your booking has been approved by our admin. We look forward to catering your event!</p>
                </div>
                ` : booking.booking_status === 'pending' ? `
                <div class="mt-4 p-3">
                    <div class="flex items-center gap-2 text-yellow-800">
                        <i class="fas fa-hourglass-half"></i>
                        <span class="font-semibold">Pending Approval</span>
                    </div>
                    <p class="text-sm text-yellow-700 mt-1">We're reviewing your booking. You'll be notified once it's approved!</p>
                </div>
                ` : `
                <div class="mt-4 p-3">
                    <div class="flex items-center gap-2 text-red-800">
                        <i class="fas fa-ban"></i>
                        <span class="font-semibold">Booking Cancelled</span>
                    </div>
                    <p class="text-sm text-red-700 mt-1">This booking was cancelled. Contact us if you have questions.</p>
                </div>  
                `) : ''}
            </div>
        `;
    }

    // Helper function to show Book Now section
    function showBookNowSection() {
        hideAllSections();
        document.querySelectorAll("nav a").forEach(l => l.classList.remove("active-nav"));
        document.querySelector('nav a').classList.add("active-nav");
        document.getElementById("section-book").classList.remove("hidden");
        resetBookingForm();
    }

    // 3-step navigation
    function showStep(step) {
        const steps = ['booking-step1', 'booking-step2', 'booking-step3'];
        const stepIndicators = ['step-1', 'step-2', 'step-3'];
        
        // Hide all steps
        steps.forEach((stepId, index) => {
            const stepElement = document.getElementById(stepId);
            if (stepElement) {
                stepElement.classList.remove('active');
                stepElement.classList.add('hidden');
            }
        });
        
        // Show current step
        const currentStepElement = document.getElementById(`booking-step${step}`);
        if (currentStepElement) {
            currentStepElement.classList.remove('hidden');
            setTimeout(() => {
                currentStepElement.classList.add('active');
            }, 50);
        }
        
        // Update step indicators
        stepIndicators.forEach((stepId, index) => {
            const stepElement = document.getElementById(stepId);
            const stepNumber = index + 1;
            
            if (stepElement) {
                stepElement.classList.remove('active', 'completed', 'inactive');
                
                if (stepNumber < step) {
                    stepElement.classList.add('completed');
                } else if (stepNumber === step) {
                    stepElement.classList.add('active');
                } else {
                    stepElement.classList.add('inactive');
                }
            }
        });
        
        // Update step lines
        const stepLines = document.querySelectorAll('.step-line');
        stepLines.forEach((line, index) => {
            line.classList.remove('active', 'completed', 'inactive');
            const lineNumber = index + 1;
            
            if (lineNumber < step) {
                line.classList.add('completed');
            } else if (lineNumber === step) {
                line.classList.add('active');
            } else {
                line.classList.add('inactive');
            }
        });
        
        currentStep = step;
    }

    // Event type change handler to show/hide age field
    function setupEventTypeHandler() {
        const eventTypeSelect = document.getElementById('eventtype');
        const ageField = document.getElementById('age-field');
        const celebrantAge = document.getElementById('celebrant-age');
        
        if (eventTypeSelect && ageField) {
            eventTypeSelect.addEventListener('change', function() {
                if (this.value === 'birthday') {
                    ageField.classList.remove('hidden');
                    celebrantAge.setAttribute('required', 'required');
                } else {
                    ageField.classList.add('hidden');
                    celebrantAge.removeAttribute('required');
                    celebrantAge.value = '';
                }
                updateEventPreview();
            });
        }
    }

    // Update event preview
    function updateEventPreview() {
        const previewDiv = document.getElementById('event-preview');
        if (!previewDiv) return;
        
        const celebrantName = document.getElementById('celebrant-name')?.value || '';
        const eventType = document.getElementById('eventtype')?.value || '';
        const guestCount = document.getElementById('guest-count')?.value || '';
        const eventDate = document.getElementById('event-date')?.value || '';
        const startTime = document.getElementById('start-time')?.value || '';
        const endTime = document.getElementById('end-time')?.value || '';
        const celebrantAge = document.getElementById('celebrant-age')?.value || '';
        const foodPackage = document.getElementById('package')?.value || '';
        
        if (!celebrantName || !eventType || !guestCount) {
            previewDiv.innerHTML = '<p>Fill in the details above to see your event preview</p>';
            return;
        }
        
        let preview = `<div class="space-y-2">`;
        
        if (eventType === 'birthday' && celebrantAge) {
            preview += `<p><strong>${celebrantName}'s ${celebrantAge}th Birthday Party</strong></p>`;
        } else {
            preview += `<p><strong>${celebrantName}'s ${eventType.charAt(0).toUpperCase() + eventType.slice(1)}</strong></p>`;
        }
        
        preview += `<p><i class="fas fa-users mr-2"></i>${guestCount} guests expected</p>`;
        
        if (foodPackage) {
            preview += `<p><i class="fas fa-utensils mr-2"></i>${foodPackage.charAt(0).toUpperCase() + foodPackage.slice(1)} package</p>`;
        }
        
        if (eventDate && startTime && endTime) {
            const date = new Date(eventDate);
            const formattedDate = date.toLocaleDateString('en-US', {
                weekday: 'long',
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            });
            const startTime12 = formatTimeTo12Hour(startTime);
            const endTime12 = formatTimeTo12Hour(endTime);
            
            preview += `<p><i class="fas fa-calendar mr-2"></i>${formattedDate}</p>`;
            preview += `<p><i class="fas fa-clock mr-2"></i>${startTime12} - ${endTime12}</p>`;
        }
        
        preview += `</div>`;
        previewDiv.innerHTML = preview;
    }

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
            
            // Force year to 2025 and validate date
            eventDateInput.addEventListener('change', function() {
                if (this.value && this.value.length === 10) {
                    const parts = this.value.split('-');
                    if (parts.length === 3 && parts[0] !== '2025') {
                        this.value = `2025-${parts[1]}-${parts[2]}`;
                    }
                    updateEventPreview();
                }
            });
            
            eventDateInput.addEventListener('blur', function() {
                if (this.value && this.value.length === 10) {
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
                    updateEventPreview();
                }
            });
        }
    }

    // FIXED VALIDATION FUNCTIONS - These prevent step progression when validation fails

    // Enhanced validation for Step 3 - NEW
    function validateStep3() {
        console.log('Validating Step 3...');
        
        // Clear any previous error styling
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.remove('border-red-500');
        });
        
        // Check if a theme is selected
        const selectedTheme = document.querySelector('.theme-btn.selected');
        if (!selectedTheme) {
            console.log('Step 3 validation failed - no theme selected');
            showMessage('error', 'Theme Required', 'Please select an event theme before proceeding.');
            
            // Add red border to all theme buttons to indicate selection is required
            document.querySelectorAll('.theme-btn').forEach(btn => {
                btn.classList.add('border-red-500');
            });
            
            // Scroll to theme selection area
            const themeSection = document.querySelector('.theme-btn').parentElement;
            if (themeSection) {
                themeSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            
            return false;
        }
        
        // If custom theme is selected, check if custom theme text is provided
        if (selectedTheme.dataset.theme === 'custom') {
            const customThemeInput = document.getElementById('custom-theme');
            if (!customThemeInput.value || !customThemeInput.value.trim()) {
                console.log('Step 3 validation failed - custom theme text missing');
                customThemeInput.classList.add('border-red-500');
                showMessage('error', 'Custom Theme Required', 'Please describe your custom theme.');
                customThemeInput.focus();
                return false;
            } else {
                customThemeInput.classList.remove('border-red-500');
            }
        }
        
        console.log('Step 3 validation passed');
        return true;
    }

    // Enhanced validation for Step 1 - FIXED
    function validateStep1() {
        console.log('Validating Step 1...');
        
        // Clear any previous error styling
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.classList.remove('border-red-500');
        });
        
        const requiredFields = [
            { name: 'full_name', element: document.querySelector('[name="full_name"]'), label: 'Full Name' },
            { name: 'contact_number', element: document.querySelector('[name="contact_number"]'), label: 'Contact Number' },
            { name: 'celebrant_name', element: document.querySelector('[name="celebrant_name"]'), label: 'Celebrant Name' },
            { name: 'guest_count', element: document.querySelector('[name="guest_count"]'), label: 'Guest Count' },
            { name: 'food_package', element: document.querySelector('[name="food_package"]'), label: 'Food Package' },
            { name: 'event_type', element: document.querySelector('[name="event_type"]'), label: 'Event Type' }
        ];
        
        let isValid = true;
        let firstInvalidField = null;
        let errorMessages = [];
        
        // Check required fields
        requiredFields.forEach(field => {
            if (field.element && (!field.element.value || !field.element.value.trim())) {
                isValid = false;
                if (!firstInvalidField) firstInvalidField = field.element;
                field.element.classList.add('border-red-500');
                errorMessages.push(field.label + ' is required');
            }
        });
        
        // Validate guest count
        const guestCountField = document.querySelector('[name="guest_count"]');
        const guestCount = guestCountField?.value;
        if (guestCount) {
            const guestNum = parseInt(guestCount);
            if (isNaN(guestNum) || guestNum < 1 || guestNum > 500) {
                isValid = false;
                guestCountField.classList.add('border-red-500');
                errorMessages.push('Guest count must be between 1 and 500');
                if (!firstInvalidField) firstInvalidField = guestCountField;
            }
        }
        
        // Validate age for birthday events
        const eventType = document.querySelector('[name="event_type"]')?.value;
        const celebrantAgeField = document.querySelector('[name="celebrant_age"]');
        
        if (eventType === 'birthday') {
            const celebrantAge = celebrantAgeField?.value;
            if (!celebrantAge || !celebrantAge.trim()) {
                isValid = false;
                celebrantAgeField.classList.add('border-red-500');
                errorMessages.push('Age is required for birthday events');
                if (!firstInvalidField) firstInvalidField = celebrantAgeField;
            } else {
                const age = parseInt(celebrantAge);
                if (isNaN(age) || age < 1 || age > 150) {
                    isValid = false;
                    celebrantAgeField.classList.add('border-red-500');
                    errorMessages.push('Please enter a valid age (1-150)');
                    if (!firstInvalidField) firstInvalidField = celebrantAgeField;
                }
            }
        }
        
        // Show error message and focus first invalid field
        if (!isValid) {
            console.log('Step 1 validation failed:', errorMessages);
            showMessage('error', 'Required Fields Missing', 'Please fill in all required fields:<br>• ' + errorMessages.join('<br>• '));
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return false;
        }
        
        console.log('Step 1 validation passed');
        return true;
    }

    // Enhanced validation for Step 2 - FIXED with Promise support
    function validateStep2() {
        console.log('Validating Step 2...');
        
        // Clear any previous error styling
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.classList.remove('border-red-500');
        });
        
        const requiredFields = [
            { name: 'event_date', element: document.querySelector('[name="event_date"]'), label: 'Event Date' },
            { name: 'start_time', element: document.querySelector('[name="start_time"]'), label: 'Start Time' },
            { name: 'end_time', element: document.querySelector('[name="end_time"]'), label: 'End Time' },
            { name: 'location', element: document.querySelector('[name="location"]'), label: 'Event Location' }
        ];
        
        let isValid = true;
        let firstInvalidField = null;
        let errorMessages = [];
        
        // Check required fields
        requiredFields.forEach(field => {
            if (field.element && (!field.element.value || !field.element.value.trim())) {
                isValid = false;
                if (!firstInvalidField) firstInvalidField = field.element;
                field.element.classList.add('border-red-500');
                errorMessages.push(field.label + ' is required');
            }
        });
        
        if (!isValid) {
            console.log('Step 2 validation failed - missing fields:', errorMessages);
            showMessage('error', 'Required Fields Missing', 'Please fill in all required fields:<br>• ' + errorMessages.join('<br>• '));
            if (firstInvalidField) {
                firstInvalidField.focus();
                firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return Promise.resolve(false);
        }
        
        // Validate date is in future (at least 3 days from today)
        const eventDateField = document.querySelector('[name="event_date"]');
        const eventDate = eventDateField?.value;
        if (eventDate) {
            const selectedDate = new Date(eventDate);
            const minDate = new Date();
            minDate.setDate(minDate.getDate() + 3);
            minDate.setHours(0, 0, 0, 0);
            selectedDate.setHours(0, 0, 0, 0);
            
            if (selectedDate < minDate) {
                console.log('Step 2 validation failed - date too soon');
                eventDateField.classList.add('border-red-500');
                showMessage('error', 'Invalid Date', 'Event date must be at least 3 days from today.');
                eventDateField.focus();
                return Promise.resolve(false);
            }
        }
        
        // Time validation
        const startTimeField = document.querySelector('[name="start_time"]');
        const endTimeField = document.querySelector('[name="end_time"]');
        const startTime = startTimeField?.value;
        const endTime = endTimeField?.value;
        
        if (startTime && endTime) {
            if (startTime >= endTime) {
                console.log('Step 2 validation failed - invalid time range');
                startTimeField.classList.add('border-red-500');
                endTimeField.classList.add('border-red-500');
                showMessage('error', 'Invalid Time Range', 'End time must be after start time.');
                startTimeField.focus();
                return Promise.resolve(false);
            }
            
            // Calculate duration
            const start = new Date('2000-01-01 ' + startTime);
            const end = new Date('2000-01-01 ' + endTime);
            const durationHours = (end - start) / (1000 * 60 * 60);
            
            if (durationHours < 4) {
                console.log('Step 2 validation failed - duration too short');
                startTimeField.classList.add('border-red-500');
                endTimeField.classList.add('border-red-500');
                showMessage('error', 'Invalid Duration', 'Event duration must be at least 4 hours.');
                startTimeField.focus();
                return Promise.resolve(false);
            }
            
            if (durationHours > 8) {
                console.log('Step 2 validation failed - duration too long');
                startTimeField.classList.add('border-red-500');
                endTimeField.classList.add('border-red-500');
                showMessage('error', 'Invalid Duration', 'Event duration cannot exceed 8 hours.');
                startTimeField.focus();
                return Promise.resolve(false);
            }
        }
        
        // Return a Promise for conflict checking
        return new Promise((resolve) => {
            console.log('Checking time conflicts...');
            checkTimeConflictForValidation(resolve);
        });
    }

    // FIXED - Conflict checking specifically for step validation
    function checkTimeConflictForValidation(callback) {
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
                    console.log('Step 2 validation failed - time conflict detected');
                    showConflictWarning(data.existing_slots || '');
                    
                    // Mark time fields as invalid
                    document.querySelector('[name="start_time"]').classList.add('border-red-500');
                    document.querySelector('[name="end_time"]').classList.add('border-red-500');
                    
                    showMessage('error', 'Time Conflict Detected', 
                        `Your selected time conflicts with existing bookings: <strong>${data.existing_slots}</strong><br>Please choose a different time slot to proceed.`);
                    
                    callback(false);
                } else {
                    console.log('Step 2 validation passed - no conflicts');
                    hideConflictWarning();
                    callback(true);
                }
            })
            .catch(err => {
                console.error('Conflict check error:', err);
                // On error, allow to proceed but show warning
                showMessage('warning', 'Cannot Verify Availability', 'Unable to check time conflicts. Please ensure your selected time is available.');
                callback(true);
            });
    }

    function showConflictWarning(existingSlots) {
        const warningDiv = document.getElementById('time-conflict-warning');
        const conflictDetails = document.getElementById('conflict-details');
        
        if (warningDiv && conflictDetails) {
            conflictDetails.innerHTML = `Your selected time conflicts with existing bookings: <strong>${existingSlots}</strong><br>Please choose a different time slot to proceed.`;
            warningDiv.classList.remove('hidden');
            warningDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    function hideConflictWarning() {
        const warningDiv = document.getElementById('time-conflict-warning');
        if (warningDiv) {
            warningDiv.classList.add('hidden');
        }
    }

    function resetBookingForm() {
        // Find the actual form element
        const form = document.querySelector('form[method="POST"]');
        if (form) {
            form.reset();
        }
        
        showStep(1);
        
        // Reset theme selection
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.classList.remove('selected', 'border-red-500');
        });
        
        // Hide custom theme input
        const customThemeInput = document.getElementById('custom-theme');
        if (customThemeInput) {
            customThemeInput.classList.add('hidden');
            customThemeInput.classList.remove('border-red-500');
        }
        
        // Show theme suggestions section (reset to default state)
        const labels = document.querySelectorAll('label');
        let themeSuggestionsContainer = null;
        
        labels.forEach(label => {
            if (label.textContent.includes('Additional Theme Suggestions')) {
                themeSuggestionsContainer = label.parentElement;
            }
        });
        
        if (themeSuggestionsContainer) {
            themeSuggestionsContainer.style.display = 'block';
        }
        
        const ageField = document.getElementById('age-field');
        if (ageField) {
            ageField.classList.add('hidden');
        }
        
        hideConflictWarning();
        
        // Remove error styling
        document.querySelectorAll('input, select, textarea').forEach(field => {
            field.classList.remove('border-red-500');
        });
        
        // Reset event preview
        const previewDiv = document.getElementById('event-preview');
        if (previewDiv) {
            previewDiv.innerHTML = '<p>Fill in the details above to see your event preview</p>';
        }
        
        // Reset price calculator
        resetPriceDisplay();
        
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
        
        const titleElement = document.getElementById('calendar-title');
        if (titleElement) {
            titleElement.textContent = `${months[currentMonth - 1]} ${currentYear}`;
        }
        
        const prevBtn = document.getElementById('prev-month');
        const nextBtn = document.getElementById('next-month');
        
        if (prevBtn) prevBtn.disabled = (currentYear === 2025 && currentMonth === 1);
        if (nextBtn) nextBtn.disabled = (currentYear === 2025 && currentMonth === 12);
    }

    function generateCalendar() {
        const calendarGrid = document.getElementById('calendar-grid');
        if (!calendarGrid) return;
        
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
            
            // Mark past dates as unavailable
            if (isPastDate) {
                dayElement.classList.add('unavailable');
                dayElement.style.backgroundColor = '#e5e7eb';
                dayElement.style.borderColor = '#9ca3af';
                dayElement.style.color = '#6b7280';
                dayElement.style.cursor = 'not-allowed';
                dayElement.style.position = 'relative';
                dayElement.style.opacity = '0.6';
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
                    dayElement.style.opacity = '0.6';
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
                    
                    if (count >= 3) {
                        slotElement.style.opacity = '0.4';
                    }
                    
                    const startTime12 = formatTimeTo12Hour(booking.start_time.substring(0, 5));
                    const endTime12 = formatTimeTo12Hour(booking.end_time.substring(0, 5));
                    const timeStr = `${startTime12}-${endTime12}`;
                    slotElement.textContent = timeStr;
                    dayElement.appendChild(slotElement);
                });
                
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
                
                if (!isPastDate && count < 3) {
                    dayElement.addEventListener('click', () => showBookingDetails(dateStr, bookingInfo));
                }
            } else if (!isPastDate) {
                dayElement.classList.add('no-bookings');
            } else if (isPastDate && bookingInfo) {
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
                    
                    const startTime12 = formatTimeTo12Hour(booking.start_time.substring(0, 5));
                    const endTime12 = formatTimeTo12Hour(booking.end_time.substring(0, 5));
                    const timeStr = `${startTime12}-${endTime12}`;
                    slotElement.textContent = timeStr;
                    dayElement.appendChild(slotElement);
                });
            }
            
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
        
        if (selectedDate) {
            selectedDate.textContent = new Date(dateStr).toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }
        
        if (content) {
            content.innerHTML = '';
            
            bookingInfo.bookings.forEach((booking) => {
                const bookingElement = document.createElement('div');
                bookingElement.classList.add('mb-4', 'p-3', 'border', 'rounded-lg');
                
                if (booking.is_own_booking) {
                    bookingElement.classList.add('border-blue-300', 'bg-blue-50');
                } else {
                    bookingElement.classList.add('border-gray-300', 'bg-gray-50');
                }
                
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
        }
        
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    // Enhanced message modal
    function showMessage(type, title, message) {
        const modal = document.getElementById('message-modal');
        const icon = document.getElementById('message-icon');
        const titleEl = document.getElementById('message-title');
        const textEl = document.getElementById('message-text');
        
        if (!modal || !icon || !titleEl || !textEl) return;
        
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

    // Time conflict checking with debounce (for live preview)
    function checkTimeConflict() {
        const eventDate = document.querySelector('[name="event_date"]')?.value;
        const startTime = document.querySelector('[name="start_time"]')?.value;
        const endTime = document.querySelector('[name="end_time"]')?.value;
        
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
                input.addEventListener('change', () => {
                    checkTimeConflict();
                    updateEventPreview();
                });
                input.addEventListener('input', () => {
                    checkTimeConflict();
                    updateEventPreview();
                });
            });
        }
    }

    // Setup input change handlers for preview updates
    function setupPreviewUpdaters() {
        const previewFields = [
            'celebrant_name', 'event_type', 'guest_count', 'food_package', 
            'celebrant_age', 'event_date', 'start_time', 'end_time'
        ];
        
        previewFields.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field) {
                field.addEventListener('input', updateEventPreview);
                field.addEventListener('change', updateEventPreview);
            }
        });
    }

    // Profile settings placeholder
    function loadProfileSettings() {
        console.log('Loading profile settings...');
        // Add profile settings functionality here
    }

    // Event listeners setup - MAIN INITIALIZATION
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Initializing enhanced booking form');
        
        // Initialize basic components
        setupDateInput();
        setupTimeConflictChecking();
        setupEventTypeHandler();
        setupPreviewUpdaters();
        showStep(1);
        
        // Price calculator listeners
        const priceFields = ['guest-count', 'package'];
        priceFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (field) {
                field.addEventListener('change', updatePriceCalculator);
                field.addEventListener('input', updatePriceCalculator);
            }
        });
        
        // Menu checkboxes for additional pricing
        document.addEventListener('change', function(e) {
            if (e.target.matches('input[name^="menu_"]')) {
                updatePriceCalculator();
            }
        });
        
        // FIXED STEP NAVIGATION LISTENERS - These now properly validate before proceeding
        const nextStep1 = document.getElementById('next-step1');
        if (nextStep1) {
            nextStep1.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Next Step 1 clicked');
                
                if (validateStep1()) {
                    console.log('Step 1 valid, proceeding to step 2');
                    goToStep(2);
                } else {
                    console.log('Step 1 validation failed, staying on step 1');
                    // Stay on current step - validation function already shows errors
                }
            });
        }
        
        const nextStep2 = document.getElementById('next-step2');
        if (nextStep2) {
            nextStep2.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Next Step 2 clicked');
                
                // Disable button to prevent multiple clicks
                const originalText = nextStep2.innerHTML;
                nextStep2.innerHTML = '<span class="loading-spinner"></span>Validating...';
                nextStep2.disabled = true;
                
                const validation = validateStep2();
                
                if (validation instanceof Promise) {
                    validation.then(isValid => {
                        if (isValid) {
                            console.log('Step 2 valid, proceeding to step 3');
                            goToStep(3);
                        } else {
                            console.log('Step 2 validation failed, staying on step 2');
                            // Stay on current step - validation function already shows errors
                        }
                        
                        // Restore button
                        nextStep2.innerHTML = originalText;
                        nextStep2.disabled = false;
                    });
                } else if (validation) {
                    console.log('Step 2 valid, proceeding to step 3');
                    goToStep(3);
                    nextStep2.innerHTML = originalText;
                    nextStep2.disabled = false;
                } else {
                    console.log('Step 2 validation failed, staying on step 2');
                    nextStep2.innerHTML = originalText;
                    nextStep2.disabled = false;
                }
            });
        }
        
        const backStep2 = document.getElementById('back-step2');
        if (backStep2) {
            backStep2.addEventListener('click', function(e) {
                e.preventDefault();
                goToStep(1);
            });
        }
        
        const backStep3 = document.getElementById('back-step3');
        if (backStep3) {
            backStep3.addEventListener('click', function(e) {
                e.preventDefault();
                goToStep(2);
            });
        }
        
        // Calendar navigation
        const prevMonthBtn = document.getElementById('prev-month');
        const nextMonthBtn = document.getElementById('next-month');
        
        if (prevMonthBtn) {
            prevMonthBtn.addEventListener('click', () => {
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
        }

        if (nextMonthBtn) {
            nextMonthBtn.addEventListener('click', () => {
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
        }

        // Refresh bookings button
        const refreshBookingsBtn = document.getElementById('refresh-bookings');
        if (refreshBookingsBtn) {
            refreshBookingsBtn.addEventListener('click', function() {
                loadMyBookings();
                
                const originalText = this.innerHTML;
                this.innerHTML = '<span class="loading-spinner"></span>Refreshing...';
                this.disabled = true;
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 2000);
            });
        }

        // Theme selection with enhanced logic
        document.querySelectorAll('.theme-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove selection and error styling from all buttons
                document.querySelectorAll('.theme-btn').forEach(b => {
                    b.classList.remove('selected', 'border-red-500');
                });
                
                // Select this button
                this.classList.add('selected');
                
                const theme = this.dataset.theme;
                const radioBtn = this.querySelector('input[type="radio"]');
                
                if (radioBtn) {
                    radioBtn.checked = true;
                }
                
                const customInput = document.getElementById('custom-theme');
                const themeSuggestionsSection = document.querySelector('label').parentElement; // Find theme suggestions container
                
                // Find the theme suggestions section properly
                const labels = document.querySelectorAll('label');
                let themeSuggestionsContainer = null;
                
                labels.forEach(label => {
                    if (label.textContent.includes('Additional Theme Suggestions')) {
                        themeSuggestionsContainer = label.parentElement;
                    }
                });
                
                if (theme === 'custom') {
                    customInput.classList.remove('hidden');
                    customInput.focus();
                    
                    // Hide theme suggestions for custom theme
                    if (themeSuggestionsContainer) {
                        themeSuggestionsContainer.style.display = 'none';
                    }
                } else {
                    customInput.classList.add('hidden');
                    customInput.value = '';
                    customInput.classList.remove('border-red-500');
                    
                    // Show theme suggestions for non-custom themes
                    if (themeSuggestionsContainer) {
                        themeSuggestionsContainer.style.display = 'block';
                    }
                }
            });
        });

        // Delete modal event listeners
        const deleteModalCancel = document.getElementById('delete-modal-cancel');
        if (deleteModalCancel) {
            deleteModalCancel.addEventListener('click', function() {
                hideDeleteModal();
            });
        }
        
        const deleteModalConfirm = document.getElementById('delete-modal-confirm');
        if (deleteModalConfirm) {
            deleteModalConfirm.addEventListener('click', function() {
                if (currentDeleteBookingId) {
                    deleteBooking(currentDeleteBookingId);
                    hideDeleteModal();
                }
            });
        }
        
        // Close modal when clicking outside
        const deleteModal = document.getElementById('delete-modal');
        if (deleteModal) {
            deleteModal.addEventListener('click', function(e) {
                if (e.target === deleteModal) {
                    hideDeleteModal();
                }
            });
        }

        // Sign out functionality
        const signoutBtn = document.getElementById('signout-btn');
        if (signoutBtn) {
            signoutBtn.addEventListener('click', () => {
                const signoutModal = document.getElementById('signout-modal');
                if (signoutModal) {
                    signoutModal.classList.remove('hidden');
                }
            });
        }

        const cancelSignout = document.getElementById('cancel-signout');
        if (cancelSignout) {
            cancelSignout.addEventListener('click', () => {
                const signoutModal = document.getElementById('signout-modal');
                if (signoutModal) {
                    signoutModal.classList.add('hidden');
                }
            });
        }

        const confirmSignout = document.getElementById('confirm-signout');
        if (confirmSignout) {
            confirmSignout.addEventListener('click', () => {
                window.location.href = 'auth.php';
            });
        }

        // Modal event listeners
        const messageModal = document.getElementById('message-modal');
        if (messageModal) {
            messageModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        }

        const bookingDetailsModal = document.getElementById('booking-details-modal');
        if (bookingDetailsModal) {
            bookingDetailsModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        }

        const signoutModal = document.getElementById('signout-modal');
        if (signoutModal) {
            signoutModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        }

        const closeBookingDetails = document.getElementById('close-booking-details');
        if (closeBookingDetails) {
            closeBookingDetails.addEventListener('click', () => {
                const bookingDetailsModal = document.getElementById('booking-details-modal');
                if (bookingDetailsModal) {
                    bookingDetailsModal.classList.add('hidden');
                }
            });
        }

        // Message modal OK button
        document.addEventListener('click', function(e) {
            if (e.target && e.target.id === 'message-ok') {
                const messageModal = document.getElementById('message-modal');
                if (messageModal) {
                    messageModal.classList.add('hidden');
                }
            }
        });

        // ENHANCED BOOKING FORM SUBMISSION - FIXED
        const bookingForm = document.querySelector('form[method="POST"]');
        
        // Only attach event listener if form exists and doesn't already have one
        if (bookingForm && !bookingForm.hasAttribute('data-form-initialized')) {
            // Mark form as initialized to prevent duplicate listeners
            bookingForm.setAttribute('data-form-initialized', 'true');
            
            bookingForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                console.log('Form submission started');
                
                // VALIDATE STEP 3 BEFORE SUBMISSION
                if (!validateStep3()) {
                    console.log('Form submission blocked - Step 3 validation failed');
                    return;
                }
                
                // Prevent multiple rapid submissions
                const submitBtn = document.getElementById('submit-booking');
                if (submitBtn.disabled) {
                    console.log('Form already being submitted, ignoring duplicate');
                    return;
                }
                
                // Create FormData object
                const formData = new FormData(this);
                formData.set('action', 'book_event');
                
                // Get selected theme
                const selectedTheme = document.querySelector('.theme-btn.selected');
                if (selectedTheme) {
                    formData.set('event_theme', selectedTheme.dataset.theme);
                }
                
                // Collect selected menus
                const selectedMenus = [];
                document.querySelectorAll('input[name^="menu_"]:checked').forEach(checkbox => {
                    selectedMenus.push(checkbox.value);
                });
                formData.set('selected_menus', selectedMenus.join(','));
                
                const originalText = submitBtn.innerHTML;
                
                // Show loading state and disable button
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
                    console.log('Response received');
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed data:', data);
                        
                        if (data.success) {
                            // Show success modal
                            showSuccessModal();
                            resetBookingForm();
                            
                            // Refresh calendar if visible
                            const scheduleSection = document.getElementById('section-schedule');
                            if (scheduleSection && !scheduleSection.classList.contains('hidden')) {
                                loadCalendar();
                            }
                        } else {
                            if (data.clear_time) {
                                showMessage('error', 'Time Conflict Detected', data.message + '<br><br>Please select a different time slot and try again.');
                                const startTimeField = document.querySelector('[name="start_time"]');
                                const endTimeField = document.querySelector('[name="end_time"]');
                                if (startTimeField) startTimeField.value = '';
                                if (endTimeField) endTimeField.value = '';
                                goToStep(2); // Go back to step 2 to fix times
                            } else {
                                showMessage('error', 'Booking Failed', data.message);
                            }
                        }
                    } catch (parseError) {
                        console.error('JSON parse error:', parseError);
                        showMessage('error', 'Server Error', 'Invalid response from server. Please try again.');
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    showMessage('error', 'Network Error', 'Please check your connection and try again.');
                })
                .finally(() => {
                    // Always restore button state
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                });
            });
            
            console.log('Form submission handler attached successfully');
        }
        
        // Override the original displayBookings function
        window.displayBookings = displayBookingsWithPrice;
        
        // Make functions globally available
        window.goToStep = goToStep;
        window.updatePriceCalculator = updatePriceCalculator;
        window.showDeleteModal = showDeleteModal;
        window.hideDeleteModal = hideDeleteModal;
        window.deleteBooking = deleteBooking;
        window.showBookNowSection = showBookNowSection;
        window.loadMyBookings = loadMyBookings;
        window.loadCalendar = loadCalendar;
        window.showMessage = showMessage;
        
        // Initialize calendar date
        const now = new Date();
        currentMonth = now.getMonth() + 1;
        currentYear = 2025;
        
        console.log('Enhanced 3-step booking form loaded and initialized with fixed step validation');
    });

    // Additional utility functions

    // Function to convert 24-hour to 12-hour format (duplicate removed)
    // formatTimeTo12Hour is already defined above

    // FIXED Navigation functions
    function showStep(step) {
        console.log(`Showing step ${step}`);
        
        const steps = ['booking-step1', 'booking-step2', 'booking-step3'];
        const stepIndicators = ['step-1', 'step-2', 'step-3'];
        
        // Hide all steps
        steps.forEach((stepId, index) => {
            const stepElement = document.getElementById(stepId);
            if (stepElement) {
                stepElement.classList.remove('active');
                stepElement.classList.add('hidden');
            }
        });
        
        // Show current step
        const currentStepElement = document.getElementById(`booking-step${step}`);
        if (currentStepElement) {
            currentStepElement.classList.remove('hidden');
            setTimeout(() => {
                currentStepElement.classList.add('active');
            }, 50);
        }
        
        // Update step indicators
        stepIndicators.forEach((stepId, index) => {
            const stepElement = document.getElementById(stepId);
            const stepNumber = index + 1;
            
            if (stepElement) {
                stepElement.classList.remove('active', 'completed', 'inactive');
                
                if (stepNumber < step) {
                    stepElement.classList.add('completed');
                } else if (stepNumber === step) {
                    stepElement.classList.add('active');
                } else {
                    stepElement.classList.add('inactive');
                }
            }
        });
        
        // Update step lines
        const stepLines = document.querySelectorAll('.step-line');
        stepLines.forEach((line, index) => {
            line.classList.remove('active', 'completed', 'inactive');
            const lineNumber = index + 1;
            
            if (lineNumber < step) {
                line.classList.add('completed');
            } else if (lineNumber === step) {
                line.classList.add('active');
            } else {
                line.classList.add('inactive');
            }
        });
        
        currentStep = step;
        
        // Sync price displays when navigating
        updatePriceCalculator();
        
        // Scroll to top of form section
        const sectionBook = document.getElementById('section-book');
        if (sectionBook) {
            sectionBook.scrollIntoView({ behavior: 'smooth' });
        }
    }

    // Make sure all global functions are available
    window.validateStep1 = validateStep1;
    window.validateStep2 = validateStep2;
    window.validateStep3 = validateStep3;
    window.showStep = showStep;
    window.resetBookingForm = resetBookingForm;
    window.formatTimeTo12Hour = formatTimeTo12Hour;


    // Load booking statistics
    function loadBookingStats() {
        fetch('booking.php?action=get_booking_stats')
            .then(response => response.json())
            .then(data => {
                if (data.error) {
                    console.error('Error loading stats:', data.error);
                    return;
                }
                
                // Update statistics
                document.getElementById('total-bookings').textContent = data.total_bookings || 0;
                document.getElementById('upcoming-events').textContent = data.upcoming_events || 0;
                
                // Format and update total spent
                const totalSpent = parseFloat(data.total_spent || 0);
                document.getElementById('total-spent').textContent = '₱' + totalSpent.toLocaleString('en-PH', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
                
                // Load next upcoming event details
                loadNextEvent();
            })
            .catch(error => {
                console.error('Error fetching stats:', error);
            });
    }

    // Load next upcoming event
    function loadNextEvent() {
        fetch('booking.php?action=get_my_bookings')
            .then(response => response.json())
            .then(bookings => {
                if (bookings.error) return;
                
                // Filter upcoming approved/pending events
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                const upcomingEvents = bookings.filter(booking => {
                    const eventDate = new Date(booking.event_date);
                    eventDate.setHours(0, 0, 0, 0);
                    return eventDate >= today && booking.booking_status !== 'cancelled';
                }).sort((a, b) => new Date(a.event_date) - new Date(b.event_date));
                
                if (upcomingEvents.length > 0) {
                    const nextEvent = upcomingEvents[0];
                    displayNextEvent(nextEvent);
                } else {
                    document.getElementById('next-event-card').classList.add('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading next event:', error);
            });
    }

    // Display next event details
    function displayNextEvent(event) {
        const card = document.getElementById('next-event-card');
        
        // Format date
        const eventDate = new Date(event.event_date);
        const formattedDate = eventDate.toLocaleDateString('en-US', {
            weekday: 'long',
            month: 'long',
            day: 'numeric',
            year: 'numeric'
        });
        
        // Format time
        const startTime = formatTimeTo12Hour(event.start_time.substring(0, 5));
        const endTime = formatTimeTo12Hour(event.end_time.substring(0, 5));
        const timeRange = `${startTime} - ${endTime}`;
        
        // Calculate days until event
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        eventDate.setHours(0, 0, 0, 0);
        const daysUntil = Math.ceil((eventDate - today) / (1000 * 60 * 60 * 24));
        
        // Update card
        document.getElementById('next-event-date').textContent = formattedDate;
        document.getElementById('next-event-time').textContent = timeRange;
        document.getElementById('next-event-celebrant').textContent = event.celebrant_name;
        document.getElementById('next-event-type').textContent = event.event_type;
        document.getElementById('next-event-location').textContent = event.location || 'Not specified';
        document.getElementById('next-event-countdown').textContent = daysUntil === 0 ? 'Today!' : 
            daysUntil === 1 ? 'Tomorrow!' : `${daysUntil} days`;
        
        card.classList.remove('hidden');
    }

    // Event listeners setup - MAIN INITIALIZATION
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM Content Loaded - Initializing enhanced booking form');
        
        // ============= PROFILE SETTINGS EVENT LISTENERS =============
        // Avatar change button
        const changeAvatarBtn = document.getElementById('change-avatar-btn');
        if (changeAvatarBtn) {
            changeAvatarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                openAvatarModal();
            });
        }
        
        // Cancel avatar button
        const cancelAvatarBtn = document.getElementById('cancel-avatar-btn');
        if (cancelAvatarBtn) {
            cancelAvatarBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeAvatarModal();
            });
        }
        
        // Close avatar modal on backdrop click
        const avatarModal = document.getElementById('avatar-modal');
        if (avatarModal) {
            avatarModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAvatarModal();
                }
            });
        }
        
        // Profile dropdown toggle
        const profileMenuBtn = document.getElementById('profile-menu-btn');
        if (profileMenuBtn) {
            profileMenuBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const dropdown = document.getElementById('profile-dropdown');
                if (dropdown) {
                    dropdown.classList.toggle('hidden');
                }
            });
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('profile-dropdown');
            const menuBtn = document.getElementById('profile-menu-btn');
            
            if (dropdown && !dropdown.contains(e.target) && e.target !== menuBtn && !menuBtn?.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });
        
        // Change password button
        const changePasswordBtn = document.getElementById('change-password-btn');
        if (changePasswordBtn) {
            changePasswordBtn.addEventListener('click', function() {
                const profileDropdown = document.getElementById('profile-dropdown');
                const passwordModal = document.getElementById('password-modal');
                if (profileDropdown) profileDropdown.classList.add('hidden');
                if (passwordModal) {
                    passwordModal.classList.remove('hidden');
                    document.body.style.overflow = 'hidden';
                }
            });
        }
        
        // Close password modal
        const closePasswordModal = document.getElementById('close-password-modal');
        if (closePasswordModal) {
            closePasswordModal.addEventListener('click', function() {
                const passwordModal = document.getElementById('password-modal');
                if (passwordModal) {
                    passwordModal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }
        
        const cancelPasswordBtn = document.getElementById('cancel-password-btn');
        if (cancelPasswordBtn) {
            cancelPasswordBtn.addEventListener('click', function() {
                const passwordModal = document.getElementById('password-modal');
                if (passwordModal) {
                    passwordModal.classList.add('hidden');
                    document.body.style.overflow = '';
                }
            });
        }
        
        // Password change form submission
        const changePasswordForm = document.getElementById('change-password-form');
        if (changePasswordForm) {
            changePasswordForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const currentPassword = document.getElementById('current-password').value;
                const newPassword = document.getElementById('new-password').value;
                const confirmPassword = document.getElementById('confirm-password').value;
                
                if (newPassword !== confirmPassword) {
                    showPasswordPopup('error', 'New passwords do not match!');
                    return;
                }
                
                if (newPassword.length < 11) {
                    showPasswordPopup('error', 'Password must be at least 11 characters!');
                    return;
                }
                
                fetch('change_password.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `current_password=${encodeURIComponent(currentPassword)}&new_password=${encodeURIComponent(newPassword)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const passwordModal = document.getElementById('password-modal');
                        if (passwordModal) {
                            passwordModal.classList.add('hidden');
                            document.body.style.overflow = '';
                        }
                        const form = document.getElementById('change-password-form');
                        if (form) form.reset();
                        showPasswordPopup('success', 'Password changed successfully!');
                    } else {
                        showPasswordPopup('error', data.message || 'Failed to change password.');
                    }
                })
                .catch(error => {
                    console.error('Error changing password:', error);
                    showPasswordPopup('error', 'An error occurred.');
                });
            });
        }
        
        // Close password popup
        const closePasswordPopup = document.getElementById('close-password-popup');
        if (closePasswordPopup) {
            closePasswordPopup.addEventListener('click', function() {
                const popup = document.getElementById('password-popup-modal');
                if (popup) popup.classList.add('hidden');
            });
        }
        
        // Upcoming events card click
        const upcomingEventsCard = document.getElementById('upcoming-events-card');
        if (upcomingEventsCard) {
            upcomingEventsCard.addEventListener('click', function() {
                hideAllSections();
                document.querySelectorAll("nav a").forEach(l => l.classList.remove("active-nav"));
                const myBookingsNav = document.querySelector('nav a[href="#"]:nth-child(2)');
                if (myBookingsNav) myBookingsNav.classList.add("active-nav");
                const myBookingsSection = document.getElementById("section-mybookings");
                if (myBookingsSection) myBookingsSection.classList.remove("hidden");
                loadMyBookings();
            });
        }
        
        // Dropdown sign out
        const dropdownSignout = document.getElementById('dropdown-signout');
        if (dropdownSignout) {
            dropdownSignout.addEventListener('click', function() {
                const dropdown = document.getElementById('profile-dropdown');
                const signoutModal = document.getElementById('signout-modal');
                if (dropdown) dropdown.classList.add('hidden');
                if (signoutModal) signoutModal.classList.remove('hidden');
            });
        }
        
        // Sign Out modal handlers
        const cancelSignout = document.getElementById('cancel-signout');
        if (cancelSignout) {
            cancelSignout.addEventListener('click', function() {
                const signoutModal = document.getElementById('signout-modal');
                if (signoutModal) signoutModal.classList.add('hidden');
            });
        }

        const confirmSignout = document.getElementById('confirm-signout');
        if (confirmSignout) {
            confirmSignout.addEventListener('click', function() {
                localStorage.removeItem('currentSection');
                window.location.href = 'auth.php';
            });
        }

        const signoutModal = document.getElementById('signout-modal');
        if (signoutModal) {
            signoutModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.add('hidden');
                }
            });
        }
        
        console.log('Profile settings functionality initialized');
        
        // Load saved section (must be LAST to override dashboard default)
        const savedSection = localStorage.getItem('currentSection');
        if (savedSection && document.getElementById(savedSection)) {
            hideAllSections();
            document.getElementById(savedSection).classList.remove('hidden');
            
            // Update nav
            const navLinks = document.querySelectorAll("nav a");
            navLinks.forEach(link => {
                link.classList.remove('active-nav');
                const navMap = {
                    "Book Now": "section-book",
                    "My Bookings": "section-mybookings",
                    "Menu Packages": "section-menu",
                    "Gallery": "section-gallery",
                    "Available Schedule": "section-schedule",
                    "Profile Settings": "section-settings",
                    "About Us": "section-about"
                };
                if (navMap[link.innerText.trim()] === savedSection) {
                    link.classList.add('active-nav');
                }
            });
            
            // Load section data
            if (savedSection === 'section-settings') {
                loadBookingStats();
            } else if (savedSection === 'section-mybookings') {
                loadMyBookings();
            } else if (savedSection === 'section-schedule') {
                loadCalendar();
            }
        }
        
        console.log('Enhanced 3-step booking form loaded and initialized with fixed step validation');
    });

    // Save form inputs to localStorage
    function saveFormInputs() {
        const formFields = [
            'full_name', 'contact_number', 'celebrant_name', 'guest_count', 
            'celebrant_age', 'food_package', 'event_type', 'location', 
            'event_date', 'start_time', 'end_time', 'theme_suggestions'
        ];
        
        formFields.forEach(fieldName => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (field && field.value) {
                localStorage.setItem(`form_${fieldName}`, field.value);
            }
        });
        
        // Save theme selection
        const selectedTheme = document.querySelector('.theme-btn.selected');
        if (selectedTheme) {
            localStorage.setItem('form_theme', selectedTheme.dataset.theme);
        }
        
        // Save menu selections
        const selectedMenus = [];
        document.querySelectorAll('input[name^="menu_"]:checked').forEach(cb => {
            selectedMenus.push(cb.value);
        });
        localStorage.setItem('form_menus', JSON.stringify(selectedMenus));
    }

    // Load saved form inputs
    function loadFormInputs() {
        const formFields = [
            'full_name', 'contact_number', 'celebrant_name', 'guest_count', 
            'celebrant_age', 'food_package', 'event_type', 'location', 
            'event_date', 'start_time', 'end_time', 'theme_suggestions'
        ];
        
        formFields.forEach(fieldName => {
            const savedValue = localStorage.getItem(`form_${fieldName}`);
            if (savedValue) {
                const field = document.querySelector(`[name="${fieldName}"]`);
                if (field) {
                    field.value = savedValue;
                    
                    // Trigger change event for calculations
                    if (fieldName === 'guest_count' || fieldName === 'food_package') {
                        updatePriceCalculator();
                    }
                }
            }
        });
        
        // Load theme selection
        const savedTheme = localStorage.getItem('form_theme');
        if (savedTheme) {
            const themeBtn = document.querySelector(`.theme-btn[data-theme="${savedTheme}"]`);
            if (themeBtn) {
                themeBtn.click();
            }
        }
        
        // Load menu selections
        const savedMenus = localStorage.getItem('form_menus');
        if (savedMenus) {
            try {
                const menus = JSON.parse(savedMenus);
                menus.forEach(menuValue => {
                    const checkbox = document.querySelector(`input[value="${menuValue}"]`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
                updatePriceCalculator();
            } catch (e) {
                console.error('Error loading menus:', e);
            }
        }
        
        updateEventPreview();
    }

    // Clear form data from localStorage
    function clearFormInputs() {
        const keys = Object.keys(localStorage);
        keys.forEach(key => {
            if (key.startsWith('form_')) {
                localStorage.removeItem(key);
            }
        });
    }

// ============= AVATAR FUNCTIONALITY (CATERING BUSINESS THEME) =============
    const avatarSeeds = [
        'Chef', 'Gourmet', 'Bistro', 'Cuisine', 'Deluxe',
        'Premium', 'Savory', 'Fusion', 'Epicure', 'Banquet',
        'Feast', 'Catering', 'Culinary', 'Flavor', 'Taste',
        'Dining', 'Plate', 'Garnish', 'Seasonal', 'Fresh'
    ];

    function generateAvatarGrid() {
        const grid = document.getElementById('avatar-grid');
        if (!grid) return;
        
        grid.innerHTML = '';
        
        avatarSeeds.forEach(seed => {
            const avatarUrl = `https://api.dicebear.com/7.x/avataaars/svg?seed=${seed}`;
            
            const avatarDiv = document.createElement('div');
            avatarDiv.className = 'cursor-pointer hover:scale-110 transition-transform border-2 border-transparent hover:border-blue-500 rounded-lg overflow-hidden';
            avatarDiv.onclick = () => selectAvatar(avatarUrl);
            
            const img = document.createElement('img');
            img.src = avatarUrl;
            img.alt = `Avatar ${seed}`;
            img.className = 'w-full h-full object-cover';
            
            avatarDiv.appendChild(img);
            grid.appendChild(avatarDiv);
        });
    }

    function selectAvatar(avatarUrl) {
        fetch('save_avatar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `avatar_url=${encodeURIComponent(avatarUrl)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const profileAvatar = document.getElementById('profile-avatar');
                if (profileAvatar) profileAvatar.src = avatarUrl;
                closeAvatarModal();
                showPasswordPopup('success', 'Avatar updated successfully!');
            } else {
                showPasswordPopup('error', data.message || 'Failed to save avatar.');
            }
        })
        .catch(error => {
            console.error('Error saving avatar:', error);
            showPasswordPopup('error', 'An error occurred while saving avatar.');
        });
    }

    function openAvatarModal() {
        const modal = document.getElementById('avatar-modal');
        if (modal) {
            generateAvatarGrid();
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAvatarModal() {
        const modal = document.getElementById('avatar-modal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    // ============= PASSWORD POPUP FUNCTIONALITY =============
    function showPasswordPopup(type, message) {
        const modal = document.getElementById('password-popup-modal');
        const icon = document.getElementById('password-popup-icon');
        const messageEl = document.getElementById('password-popup-message');
        
        if (!modal || !icon || !messageEl) return;
        
        if (type === 'success') {
            icon.innerHTML = '<svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        } else {
            icon.innerHTML = '<svg class="w-16 h-16 text-red-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        }
        
        messageEl.textContent = message;
        modal.classList.remove('hidden');
    }

    // Auto-save on input change
    document.addEventListener('input', function(e) {
        if (e.target.closest('#booking-form')) {
            saveFormInputs();
        }
    });

    document.addEventListener('change', function(e) {
        if (e.target.closest('#booking-form')) {
            saveFormInputs();
        }
    });

    // Load form inputs when Book Now section is shown
    const bookNowNav = document.querySelector('nav a');
    if (bookNowNav) {
        bookNowNav.addEventListener('click', function() {
            setTimeout(loadFormInputs, 100);
        });
    }
    
    </script>
    </body>
    </html>
