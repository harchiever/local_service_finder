<?php
// ============================================================
// edit_service.php — Worker edits their own service
// ============================================================
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'worker') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$userId     = (int)$_SESSION['user_id'];
$serviceId  = (int)($_POST['service_id'] ?? 0);
$title       = trim($_POST['title']       ?? '');
$category    = trim($_POST['category']    ?? '');
$description = trim($_POST['description'] ?? '');
$price       = (float)($_POST['price']    ?? 0);
$phone       = trim($_POST['phone']       ?? '');
$location    = trim($_POST['location']    ?? '');

if ($serviceId <= 0) {
    header('Location: dashboard.php');
    exit;
}

// ---- Verify ownership ----
$stmt = mysqli_prepare($conn,
    'SELECT id, image FROM services WHERE id = ? AND user_id = ? LIMIT 1'
);
mysqli_stmt_bind_param($stmt, 'ii', $serviceId, $userId);
mysqli_stmt_execute($stmt);
$result  = mysqli_stmt_get_result($stmt);
$service = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$service) {
    $_SESSION['service_errors'] = ['Service not found or you do not have permission to edit it.'];
    header('Location: dashboard.php');
    exit;
}

// ---- Validation ----
$errors = [];
if (empty($title))    $errors[] = 'Service title is required.';
if (empty($category)) $errors[] = 'Category is required.';
if ($price <= 0)      $errors[] = 'A valid price is required.';

if (!empty($errors)) {
    $_SESSION['service_errors'] = $errors;
    header('Location: dashboard.php');
    exit;
}

// ---- Handle image upload (optional) ----
$imagePath = $service['image']; // keep existing by default

if (!empty($_FILES['image']['name'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize      = 5 * 1024 * 1024;
    $fileTmp      = $_FILES['image']['tmp_name'];
    $fileType     = mime_content_type($fileTmp);
    $fileSize     = $_FILES['image']['size'];

    if (!in_array($fileType, $allowedTypes)) {
        $errors[] = 'Only JPEG, PNG, GIF, and WebP images are allowed.';
    } elseif ($fileSize > $maxSize) {
        $errors[] = 'Image must be smaller than 5 MB.';
    } else {
        $ext       = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $fileName  = uniqid('svc_', true) . '.' . strtolower($ext);
        $uploadDir = __DIR__ . '/uploads/';

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        if (move_uploaded_file($fileTmp, $uploadDir . $fileName)) {
            // Delete old image if it exists
            if ($service['image'] && file_exists(__DIR__ . '/' . $service['image'])) {
                unlink(__DIR__ . '/' . $service['image']);
            }
            $imagePath = 'uploads/' . $fileName;
        } else {
            $errors[] = 'Failed to upload image. Check folder permissions.';
        }
    }

    if (!empty($errors)) {
        $_SESSION['service_errors'] = $errors;
        header('Location: dashboard.php');
        exit;
    }
}

// ---- Update service ----
$stmt = mysqli_prepare($conn,
    'UPDATE services
     SET title = ?, category = ?, description = ?, price = ?, phone = ?, location = ?, image = ?
     WHERE id = ? AND user_id = ?'
);
mysqli_stmt_bind_param($stmt, 'sssdsssii',
    $title, $category, $description, $price, $phone, $location, $imagePath,
    $serviceId, $userId
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_success'] = 'Service updated successfully!';
} else {
    $_SESSION['service_errors'] = ['Failed to update service. Please try again.'];
}

mysqli_stmt_close($stmt);
header('Location: dashboard.php');
exit;
