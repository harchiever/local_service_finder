<?php
// ============================================================
// cancel_booking.php — Customer cancels a pending booking
// ============================================================
session_start();
require_once 'db.php';

// Must be a logged-in customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: login.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: my_bookings.php'); exit;
}

$bookingId  = (int)($_POST['booking_id'] ?? 0);
$customerId = (int)$_SESSION['user_id'];

if ($bookingId <= 0) {
    $_SESSION['flash_error'] = 'Invalid booking.';
    header('Location: my_bookings.php'); exit;
}

// Only allow cancelling OWN pending bookings
$stmt = mysqli_prepare($conn,
    'UPDATE bookings SET status = ? WHERE id = ? AND customer_id = ? AND status = ?');
$newStatus = 'cancelled';
$pending   = 'pending';
mysqli_stmt_bind_param($stmt, 'siis', $newStatus, $bookingId, $customerId, $pending);
mysqli_stmt_execute($stmt);
$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($affected > 0) {
    $_SESSION['flash_success'] = 'Booking cancelled successfully.';
} else {
    $_SESSION['flash_error'] = 'Could not cancel booking. It may already be confirmed or cancelled.';
}

header('Location: my_bookings.php');
exit;
