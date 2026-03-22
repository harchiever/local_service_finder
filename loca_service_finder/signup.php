<?php
// ============================================================
// signup.php — Registration page
// ============================================================
session_start();

if (isset($_SESSION['user_id'])) {
    header($_SESSION['user_role'] === 'worker' ? 'Location: dashboard.php' : 'Location: services.php');
    exit;
}

$errors  = $_SESSION['signup_errors'] ?? []; unset($_SESSION['signup_errors']);
$old     = $_SESSION['signup_old']    ?? [];  unset($_SESSION['signup_old']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Sign Up — LocalService</title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body { background:linear-gradient(135deg,#f8faff 0%,#eef2ff 50%,#e0e7ff 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; }
    .auth-card { background:#fff; border-radius:20px; box-shadow:0 25px 50px -12px rgba(0,0,0,.15); padding:48px 40px; width:100%; max-width:480px; }
    .auth-logo { text-align:center; margin-bottom:32px; }
    .auth-logo a { font-size:1.8rem; font-weight:800; color:var(--primary); text-decoration:none; }
    .auth-logo a i { margin-right:8px; }
    .auth-card h1 { font-size:1.7rem; font-weight:800; color:var(--dark); margin-bottom:6px; text-align:center; }
    .auth-card .subtitle { text-align:center; color:var(--gray-500); margin-bottom:28px; font-size:.95rem; }
    .form-group { margin-bottom:18px; }
    .form-group label { display:block; font-weight:600; font-size:.9rem; color:var(--dark); margin-bottom:8px; }
    .form-group .input-wrap { position:relative; }
    .form-group .input-wrap i { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--gray-500); font-size:.9rem; }
    .form-group input, .form-group select { width:100%; padding:12px 14px 12px 42px; border:2px solid var(--gray-300); border-radius:10px; font-family:inherit; font-size:.95rem; color:var(--dark); transition:var(--transition); background:#fff; }
    .form-group input:focus, .form-group select:focus { outline:none; border-color:var(--primary); box-shadow:0 0 0 3px rgba(79,70,229,.1); }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .alert { padding:12px 16px; border-radius:10px; font-size:.9rem; margin-bottom:20px; }
    .alert-danger { background:#fee2e2; color:#991b1b; border:1px solid #fca5a5; }
    .alert ul { margin:8px 0 0 16px; }
    .role-selector { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:18px; }
    .role-option { position:relative; }
    .role-option input[type=radio] { position:absolute; opacity:0; }
    .role-option label { display:flex; flex-direction:column; align-items:center; gap:8px; padding:16px 12px; border:2px solid var(--gray-300); border-radius:12px; cursor:pointer; transition:var(--transition); font-size:.9rem; font-weight:600; color:var(--gray-700); }
    .role-option label i { font-size:1.5rem; color:var(--gray-500); }
    .role-option input:checked + label { border-color:var(--primary); background:var(--primary-light); color:var(--primary); }
    .role-option input:checked + label i { color:var(--primary); }
    .auth-footer { text-align:center; margin-top:24px; font-size:.9rem; color:var(--gray-500); }
    .auth-footer a { color:var(--primary); font-weight:600; }
  </style>
</head>
<body>
  <div class="auth-card">
    <div class="auth-logo">
      <a href="index.php"><i class="fas fa-map-marker-alt"></i>LocalService</a>
    </div>
    <h1>Create Account</h1>
    <p class="subtitle">Join thousands of users on LocalService</p>

    <?php if ($errors): ?>
      <div class="alert alert-danger">
        <i class="fas fa-circle-exclamation"></i> Please fix the following:
        <ul><?php foreach($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
      </div>
    <?php endif; ?>

    <form action="signup_process.php" method="POST">

      <!-- Role selector -->
      <div class="form-group">
        <label>I want to</label>
        <div class="role-selector">
          <div class="role-option">
            <input type="radio" id="role_customer" name="role" value="customer"
              <?= (($old['role'] ?? 'customer') === 'customer') ? 'checked' : '' ?>>
            <label for="role_customer"><i class="fas fa-user"></i> Find Services</label>
          </div>
          <div class="role-option">
            <input type="radio" id="role_worker" name="role" value="worker"
              <?= (($old['role'] ?? '') === 'worker') ? 'checked' : '' ?>>
            <label for="role_worker"><i class="fas fa-briefcase"></i> Offer Services</label>
          </div>
        </div>
      </div>

      <div class="form-group">
        <label for="name">Full Name</label>
        <div class="input-wrap">
          <i class="fas fa-user"></i>
          <input type="text" id="name" name="name" placeholder="John Doe" required
            value="<?= htmlspecialchars($old['name'] ?? '') ?>"/>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="email">Email</label>
          <div class="input-wrap">
            <i class="fas fa-envelope"></i>
            <input type="email" id="email" name="email" placeholder="john@example.com" required
              value="<?= htmlspecialchars($old['email'] ?? '') ?>"/>
          </div>
        </div>
        <div class="form-group">
          <label for="phone">Phone (optional)</label>
          <div class="input-wrap">
            <i class="fas fa-phone"></i>
            <input type="tel" id="phone" name="phone" placeholder="+1 234 567"
              value="<?= htmlspecialchars($old['phone'] ?? '') ?>"/>
          </div>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label for="password">Password</label>
          <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" id="password" name="password" placeholder="Min 6 chars" required/>
          </div>
        </div>
        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <div class="input-wrap">
            <i class="fas fa-lock"></i>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Repeat password" required/>
          </div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary btn-full btn-lg" style="margin-top:8px;">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="auth-footer">
      Already have an account? <a href="login.php">Log In</a>
    </div>
  </div>
</body>
</html>
