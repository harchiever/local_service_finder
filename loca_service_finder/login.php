<?php
// ============================================================
// login.php — Login page
// ============================================================
session_start();

// Already logged in
if (isset($_SESSION['user_id'])) {
    header($_SESSION['user_role'] === 'worker' ? 'Location: dashboard.php' : 'Location: services.php');
    exit;
}

$error   = $_SESSION['login_error']   ?? null; unset($_SESSION['login_error']);
$success = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Login — LocalService</title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body { background: linear-gradient(135deg,#f8faff 0%,#eef2ff 50%,#e0e7ff 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
    .auth-card { background:#fff; border-radius:20px; box-shadow:0 25px 50px -12px rgba(0,0,0,.15); padding:48px 40px; width:100%; max-width:440px; }
    .auth-logo { text-align:center; margin-bottom:32px; }
    .auth-logo a { font-size:1.8rem; font-weight:800; color:var(--primary); text-decoration:none; }
    .auth-logo a i { margin-right:8px; }
    .auth-card h1 { font-size:1.7rem; font-weight:800; color:var(--dark); margin-bottom:6px; text-align:center; }
    .auth-card .subtitle { text-align:center; color:var(--gray-500); margin-bottom:28px; font-size:.95rem; }
    .form-group { margin-bottom:20px; }
    .form-group label { display:block; font-weight:600; font-size:.9rem; color:var(--dark); margin-bottom:8px; }
    .form-group .input-wrap { position:relative; }
    .form-group .input-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--gray-500); font-size:.9rem; }
    .form-group input { width:100%; padding:12px 14px 12px 42px; border:2px solid var(--gray-300); border-radius:10px; font-family:inherit; font-size:.95rem; color:var(--dark); transition:var(--transition); }
    .form-group input:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,.1); }
    .alert { padding:12px 16px; border-radius:10px; font-size:.9rem; margin-bottom:20px; }
    .alert-danger  { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
    .alert-success { background:#d1fae5; color:#065f46; border:1px solid #6ee7b7; }
    .auth-footer { text-align:center; margin-top:24px; font-size:.9rem; color:var(--gray-500); }
    .auth-footer a { color:var(--primary); font-weight:600; }
  </style>
</head>
<body>
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php"><i class="fas fa-map-marker-alt"></i>LocalService</a>
    </div>
    <h1>Welcome Back</h1>
    <p class="subtitle">Log in to your account to continue</p>

    <?php if ($error): ?>
      <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
      <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form action="login_process.php" method="POST">
      <div class="form-group">
        <label for="email">Email Address</label>
        <div class="input-wrap">
          <i class="fas fa-envelope"></i>
          <input type="email" id="email" name="email" placeholder="john@example.com" required autocomplete="email"/>
        </div>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="current-password"/>
        </div>
      </div>
      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
        <i class="fas fa-right-to-bracket"></i> Log In
      </button>
    </form>

    <div class="auth-footer">
      Don't have an account? <a href="signup.php">Sign Up</a>
    </div>
  </div>
</body>
</html>
