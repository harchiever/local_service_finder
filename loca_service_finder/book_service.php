<?php
// ============================================================
// book_service.php — Customer books a service
// ============================================================
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    // Return JSON if it's an AJAX call, otherwise redirect
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'You must be logged in as a customer to book a service.']);
    } else {
        $_SESSION['flash_error'] = 'You must be logged in as a customer to book a service.';
        header('Location: login.php');
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: services.php');
    exit;
}

$customerId   = (int)$_SESSION['user_id'];
$serviceId    = (int)($_POST['service_id']   ?? 0);
$bookingDate  = trim($_POST['booking_date']  ?? '');

// ---- Validation ----
$errors = [];
if ($serviceId <= 0)     $errors[] = 'Invalid service.';
if (empty($bookingDate)) $errors[] = 'Please select a booking date.';

// Validate date format and that it's not in the past
if ($bookingDate) {
    $date = DateTime::createFromFormat('Y-m-d', $bookingDate);
    $today = new DateTime('today');
    if (!$date || $date < $today) {
        $errors[] = 'Booking date must be today or a future date.';
    }
}

if (!empty($errors)) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    } else {
        $_SESSION['flash_error'] = implode(' ', $errors);
        header('Location: service_detail.php?id=' . $serviceId);
    }
    exit;
}

// ---- Verify service exists ----
$stmt = mysqli_prepare($conn, 'SELECT id FROM services WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 'i', $serviceId);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) === 0) {
    mysqli_stmt_close($stmt);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Service not found.']);
    } else {
        $_SESSION['flash_error'] = 'Service not found.';
        header('Location: services.php');
    }
    exit;
}
mysqli_stmt_close($stmt);

// ---- Prevent duplicate booking (same customer, same service, same date) ----
$stmt = mysqli_prepare($conn,
    'SELECT id FROM bookings
     WHERE service_id = ? AND customer_id = ? AND booking_date = ? AND status != "cancelled"
     LIMIT 1'
);
mysqli_stmt_bind_param($stmt, 'iis', $serviceId, $customerId, $bookingDate);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    mysqli_stmt_close($stmt);
    $msg = 'You already have a booking for this service on this date.';
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $msg]);
    } else {
        $_SESSION['flash_error'] = $msg;
        header('Location: service_detail.php?id=' . $serviceId);
    }
    exit;
}
mysqli_stmt_close($stmt);

// ---- Insert booking ----
$stmt = mysqli_prepare($conn,
    'INSERT INTO bookings (service_id, customer_id, booking_date) VALUES (?, ?, ?)'
);
mysqli_stmt_bind_param($stmt, 'iis', $serviceId, $customerId, $bookingDate);

if (mysqli_stmt_execute($stmt)) {
    $msg = 'Booking confirmed! The service provider will contact you soon.';
    mysqli_stmt_close($stmt);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $msg]);
    } else {
        $_SESSION['flash_success'] = $msg;
        header('Location: my_bookings.php');
    }
} else {
    $msg = 'Booking failed. Please try again.';
    mysqli_stmt_close($stmt);
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $msg]);
    } else {
        $_SESSION['flash_error'] = $msg;
        header('Location: service_detail.php?id=' . $serviceId);
    }
}
exit;
