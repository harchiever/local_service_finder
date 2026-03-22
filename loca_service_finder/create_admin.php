<?php
// ============================================================
// create_admin.php — One-time admin account setup script
// Visit this page ONCE, then delete it for security.
// ============================================================
require_once 'db.php';

$name     = 'Admin';
$email    = 'admin@localserv.com';
$password = 'admin123';
$role     = 'admin';
$hash     = password_hash($password, PASSWORD_BCRYPT);

// First make sure 'admin' is in the ENUM (alter if needed)
mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN role ENUM('customer','worker','admin') NOT NULL DEFAULT 'customer'");

// Remove old admin if exists
mysqli_query($conn, "DELETE FROM users WHERE email = 'admin@localserv.com'");

// Insert fresh admin
$stmt = mysqli_prepare($conn, 'INSERT INTO users (name, email, password, role) VALUES (?,?,?,?)');
mysqli_stmt_bind_param($stmt, 'ssss', $name, $email, $hash, $role);
$ok = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <title>Admin Setup</title>
  <style>
    body{font-family:sans-serif;background:#f1f5f9;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;}
    .box{background:#fff;border-radius:16px;padding:40px;max-width:420px;width:100%;box-shadow:0 10px 40px rgba(0,0,0,.1);text-align:center;}
    h1{color:#1e293b;margin-bottom:8px;}
    .ok{color:#166534;background:#dcfce7;padding:16px;border-radius:10px;margin:20px 0;font-weight:600;}
    .err{color:#991b1b;background:#fee2e2;padding:16px;border-radius:10px;margin:20px 0;font-weight:600;}
    table{width:100%;text-align:left;font-size:.9rem;border-collapse:collapse;margin:16px 0;}
    td{padding:8px 12px;border-bottom:1px solid #f1f5f9;}
    td:first-child{font-weight:600;color:#64748b;width:40%;}
    a{display:inline-block;margin-top:20px;background:#4f46e5;color:#fff;padding:12px 28px;border-radius:10px;text-decoration:none;font-weight:600;}
    .warn{background:#fef3c7;color:#92400e;padding:12px;border-radius:8px;font-size:.85rem;margin-top:16px;}
  </style>
</head>
<body>
  <div class="box">
    <h1>🛠️ Admin Setup</h1>
    <?php if ($ok): ?>
      <div class="ok">✅ Admin account created successfully!</div>
      <table>
        <tr><td>Email</td><td><strong><?= $email ?></strong></td></tr>
        <tr><td>Password</td><td><strong><?= $password ?></strong></td></tr>
        <tr><td>Role</td><td><strong>Admin</strong></td></tr>
      </table>
      <a href="login.php">→ Go to Login</a>
      <div class="warn">⚠️ Delete this file after logging in!</div>
    <?php else: ?>
      <div class="err">❌ Failed to create admin. Check DB connection.</div>
    <?php endif; ?>
  </div>
</body>
</html>
