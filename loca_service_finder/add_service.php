<?php
// ============================================================
// add_service.php — Worker adds a new service
// ============================================================
session_start();
require_once 'db.php';

// Must be logged in as worker
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'worker') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

$userId      = (int)$_SESSION['user_id'];
$title       = trim($_POST['title']       ?? '');
$category    = trim($_POST['category']    ?? '');
$description = trim($_POST['description'] ?? '');
$price       = (float)($_POST['price']    ?? 0);
$phone       = trim($_POST['phone']       ?? '');
$location    = trim($_POST['location']    ?? '');

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

// ---- Handle image upload ----
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    $maxSize      = 5 * 1024 * 1024; // 5 MB

    $fileTmp  = $_FILES['image']['tmp_name'];
    $fileType = mime_content_type($fileTmp);
    $fileSize = $_FILES['image']['size'];

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

// ---- Insert service ----
$stmt = mysqli_prepare($conn,
    'INSERT INTO services (user_id, title, category, description, price, phone, location, image)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
mysqli_stmt_bind_param($stmt, 'isssdsss',
    $userId, $title, $category, $description, $price, $phone, $location, $imagePath
);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['flash_success'] = 'Service "' . htmlspecialchars($title) . '" added successfully!';
} else {
    $_SESSION['service_errors'] = ['Failed to save service. Please try again.'];
}

mysqli_stmt_close($stmt);
header('Location: dashboard.php');
exit;
