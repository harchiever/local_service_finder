<?php
// ============================================================
// signup_process.php — Handle user registration
// ============================================================
session_start();
require_once 'db.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit;
}

// Collect & sanitise inputs
$name     = trim($_POST['name']  ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password']   ?? '';
$confirm  = $_POST['confirm_password'] ?? '';
$role     = $_POST['role']       ?? 'customer';
$phone    = trim($_POST['phone'] ?? '');

// ---- Validation ----
$errors = [];

if (empty($name))                          $errors[] = 'Full name is required.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'A valid email is required.';
if (strlen($password) < 6)                $errors[] = 'Password must be at least 6 characters.';
if ($password !== $confirm)               $errors[] = 'Passwords do not match.';
if (!in_array($role, ['customer','worker'])) $errors[] = 'Invalid role selected.';

if (!empty($errors)) {
    $_SESSION['signup_errors'] = $errors;
    $_SESSION['signup_old']    = compact('name','email','role','phone');
    header('Location: signup.php');
    exit;
}

// ---- Check duplicate email ----
$stmt = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {
    $_SESSION['signup_errors'] = ['This email address is already registered.'];
    $_SESSION['signup_old']    = compact('name','email','role','phone');
    mysqli_stmt_close($stmt);
    header('Location: signup.php');
    exit;
}
mysqli_stmt_close($stmt);

// ---- Insert user ----
$hashed = password_hash($password, PASSWORD_BCRYPT);

$stmt = mysqli_prepare($conn,
    'INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, ?, ?)'
);
mysqli_stmt_bind_param($stmt, 'sssss', $name, $email, $hashed, $role, $phone);

if (mysqli_stmt_execute($stmt)) {
    $userId = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    // Auto-login after signup
    $_SESSION['user_id']   = $userId;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = $role;
    $_SESSION['user_email']= $email;

    $_SESSION['flash_success'] = 'Account created successfully! Welcome, ' . htmlspecialchars($name) . '!';

    // Redirect based on role
    header($role === 'worker' ? 'Location: dashboard.php' : 'Location: services.php');
    exit;
} else {
    $_SESSION['signup_errors'] = ['Registration failed. Please try again.'];
    mysqli_stmt_close($stmt);
    header('Location: signup.php');
    exit;
}
