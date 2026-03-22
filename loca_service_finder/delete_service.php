<?php
// ============================================================
// delete_service.php — Worker deletes their own service
// ============================================================
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'worker') {
    header('Location: login.php');
    exit;
}

$userId    = (int)$_SESSION['user_id'];
$serviceId = (int)($_POST['service_id'] ?? $_GET['id'] ?? 0);

if ($serviceId <= 0) {
    header('Location: dashboard.php');
    exit;
}

// ---- Verify ownership & get image path ----
$stmt = mysqli_prepare($conn,
    'SELECT id, image FROM services WHERE id = ? AND user_id = ? LIMIT 1'
);
mysqli_stmt_bind_param($stmt, 'ii', $serviceId, $userId);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$service = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$service) {
    $_SESSION['service_errors'] = ['Service not found or you do not have permission to delete it.'];
    header('Location: dashboard.php');
    exit;
}

// ---- Delete record ----
$stmt = mysqli_prepare($conn,
    'DELETE FROM services WHERE id = ? AND user_id = ?'
);
mysqli_stmt_bind_param($stmt, 'ii', $serviceId, $userId);

if (mysqli_stmt_execute($stmt)) {
    // Delete image file if it exists
    if ($service['image'] && file_exists(__DIR__ . '/' . $service['image'])) {
        unlink(__DIR__ . '/' . $service['image']);
    }
    $_SESSION['flash_success'] = 'Service deleted successfully.';
} else {
    $_SESSION['service_errors'] = ['Failed to delete service. Please try again.'];
}

mysqli_stmt_close($stmt);
header('Location: dashboard.php');
exit;
