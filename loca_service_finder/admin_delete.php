<?php
// ============================================================
// admin_delete.php — Admin: delete a user, service, or booking
// ============================================================
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: admin.php'); exit;
}

$type = $_POST['type'] ?? '';
$id   = (int)($_POST['id'] ?? 0);

if ($id <= 0 || !in_array($type, ['user','service','booking'])) {
    $_SESSION['flash_error'] = 'Invalid request.';
    header('Location: admin.php'); exit;
}

$tableMap = ['user' => 'users', 'service' => 'services', 'booking' => 'bookings'];
$table    = $tableMap[$type];

$stmt = mysqli_prepare($conn, "DELETE FROM `{$table}` WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$affected = mysqli_stmt_affected_rows($stmt);
mysqli_stmt_close($stmt);

if ($affected > 0) {
    $_SESSION['flash_success'] = ucfirst($type) . ' deleted successfully.';
} else {
    $_SESSION['flash_error'] = 'Could not delete ' . $type . '.';
}

header('Location: admin.php');
exit;
