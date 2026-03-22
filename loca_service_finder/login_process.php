<?php
// ============================================================
// login_process.php — Handle user login
// ============================================================
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$email    = trim($_POST['email']    ?? '');
$password = $_POST['password']      ?? '';

// ---- Basic validation ----
if (empty($email) || empty($password)) {
    $_SESSION['login_error'] = 'Email and password are required.';
    header('Location: login.php');
    exit;
}

// ---- Fetch user by email ----
$stmt = mysqli_prepare($conn,
    'SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1'
);
mysqli_stmt_bind_param($stmt, 's', $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user   = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// ---- Verify password ----
if (!$user || !password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = 'Invalid email or password.';
    header('Location: login.php');
    exit;
}

// ---- Set session ----
session_regenerate_id(true); // Security: prevent session fixation
$_SESSION['user_id']    = $user['id'];
$_SESSION['user_name']  = $user['name'];
$_SESSION['user_role']  = $user['role'];
$_SESSION['user_email'] = $user['email'];

$_SESSION['flash_success'] = 'Welcome back, ' . htmlspecialchars($user['name']) . '!';

// Redirect based on role
if ($user['role'] === 'admin')        header('Location: admin.php');
elseif ($user['role'] === 'worker')   header('Location: dashboard.php');
else                                  header('Location: services.php');
exit;
