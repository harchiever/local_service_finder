<?php
session_start();
require_once 'db.php';

$loggedIn = isset($_SESSION['user_id']);
$userName = $loggedIn ? $_SESSION['user_name'] : '';
$userRole = $loggedIn ? $_SESSION['user_role'] : '';
$flash    = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$flashErr = $_SESSION['flash_error']   ?? null; unset($_SESSION['flash_error']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: services.php'); exit; }

$stmt = mysqli_prepare($conn,
    'SELECT s.*, u.name AS worker_name, u.email AS worker_email, u.phone AS worker_phone
     FROM services s JOIN users u ON u.id = s.user_id WHERE s.id = ?');
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$svc = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if (!$svc) { header('Location: services.php'); exit; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title><?= htmlspecialchars($svc['title']) ?> — LocalService</title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body{padding-top:72px;background:#f8faff;}
    .user-menu{position:relative;display:inline-flex;align-items:center;gap:8px;cursor:pointer;}
    .user-avatar{width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;font-weight:700;font-size:.9rem;display:flex;align-items:center;justify-content:center;}
    .user-dropdown{display:none;position:absolute;top:calc(100% + 8px);right:0;background:#fff;border-radius:12px;box-shadow:var(--shadow-md);border:1px solid var(--gray-100);min-width:180px;z-index:999;overflow:hidden;}
    .user-menu:hover .user-dropdown{display:block;}
    .user-dropdown a,.user-dropdown form button{display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:.9rem;font-weight:500;color:var(--gray-700);text-decoration:none;background:none;border:none;width:100%;cursor:pointer;font-family:inherit;transition:var(--transition);}
    .user-dropdown a:hover,.user-dropdown form button:hover{background:var(--gray-100);color:var(--primary);}
    .alert{padding:12px 16px;border-radius:10px;margin-bottom:24px;font-size:.9rem;}
    .alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
    .alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
    .detail-hero{width:100%;max-height:420px;object-fit:cover;border-radius:var(--radius) var(--radius) 0 0;}
    .detail-hero-placeholder{width:100%;height:280px;background:linear-gradient(135deg,var(--primary-light),#ddd);display:flex;align-items:center;justify-content:center;border-radius:var(--radius) var(--radius) 0 0;}
    .detail-hero-placeholder i{font-size:5rem;color:var(--primary);opacity:.35;}
    .detail-layout{display:grid;grid-template-columns:1fr 360px;gap:32px;padding:32px 0 80px;}
    @media(max-width:768px){.detail-layout{grid-template-columns:1fr;}}
    .detail-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow-sm);border:1px solid var(--gray-100);overflow:hidden;}
    .detail-body{padding:28px;}
    .detail-category{font-size:.8rem;font-weight:700;color:var(--primary);background:var(--primary-light);padding:5px 14px;border-radius:50px;display:inline-block;margin-bottom:12px;}
    .detail-title{font-size:1.7rem;font-weight:800;color:var(--dark);line-height:1.25;margin-bottom:14px;}
    .detail-meta{display:flex;gap:18px;flex-wrap:wrap;margin-bottom:22px;padding-bottom:22px;border-bottom:1px solid var(--gray-100);}
    .detail-meta-item{display:flex;align-items:center;gap:8px;font-size:.9rem;color:var(--gray-500);}
    .detail-meta-item strong{color:var(--dark);}
    .detail-section h3{font-size:.95rem;font-weight:700;color:var(--dark);margin-bottom:10px;}
    .detail-description{color:var(--gray-700);line-height:1.75;font-size:.93rem;white-space:pre-wrap;}
    .price-badge{font-size:1.9rem;font-weight:800;color:var(--primary);margin:18px 0 0;display:flex;align-items:baseline;gap:6px;}
    .price-badge span{font-size:.85rem;font-weight:400;color:var(--gray-500);}
    .sidebar-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow-sm);border:1px solid var(--gray-100);padding:24px;margin-bottom:20px;}
    .sidebar-card h3{font-size:.95rem;font-weight:700;color:var(--dark);margin-bottom:16px;display:flex;align-items:center;gap:8px;}
    .worker-info{display:flex;align-items:center;gap:12px;margin-bottom:18px;}
    .w-avatar{width:48px;height:48px;border-radius:50%;background:var(--primary);color:#fff;font-weight:700;font-size:1.2rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
    .w-name{font-weight:700;color:var(--dark);}
    .w-email{font-size:.82rem;color:var(--gray-500);}
    .contact-row{display:flex;align-items:center;gap:8px;font-size:.88rem;color:var(--gray-600);margin-bottom:10px;}
    .contact-row i{width:16px;color:var(--primary);}
    .form-group{margin-bottom:16px;}
    .form-group label{display:block;font-weight:600;font-size:.88rem;color:var(--dark);margin-bottom:6px;}
    .form-group input{width:100%;padding:11px 14px;border:2px solid var(--gray-300);border-radius:10px;font-family:inherit;font-size:.92rem;color:var(--dark);transition:var(--transition);}
    .form-group input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.1);}
    .login-prompt{text-align:center;padding:20px;background:var(--primary-light);border-radius:12px;border:2px dashed var(--primary);}
    .login-prompt p{color:var(--gray-700);margin-bottom:12px;font-size:.88rem;}
    .breadcrumb{padding:16px 0 0;font-size:.84rem;color:var(--gray-500);}
    .breadcrumb a{color:var(--primary);text-decoration:none;}
    .breadcrumb i{font-size:.65rem;margin:0 6px;}
  </style>
</head>
<body>
  <nav class="navbar scrolled">
    <div class="container nav-container">
      <a href="index.php" class="logo"><i class="fas fa-map-marker-alt"></i> LocalService</a>
      <div class="nav-links" id="navLinks">
        <a href="index.php">Home</a>
        <a href="services.php" class="active">Services</a>
        <?php if ($loggedIn && $userRole==='customer'): ?><a href="my_bookings.php">My Bookings</a><?php endif; ?>
        <?php if ($loggedIn && $userRole==='worker'):   ?><a href="dashboard.php">Dashboard</a><?php endif; ?>
      </div>
      <div class="nav-actions">
        <?php if ($loggedIn): ?>
          <div class="user-menu">
            <div class="user-avatar"><?= strtoupper(substr($userName,0,1)) ?></div>
            <span style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars(explode(' ',$userName)[0]) ?></span>
            <i class="fas fa-chevron-down" style="font-size:.75rem;color:var(--gray-500)"></i>
            <div class="user-dropdown">
              <?php if ($userRole==='worker'): ?><a href="dashboard.php"><i class="fas fa-gauge"></i> Dashboard</a>
              <?php else: ?><a href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a><?php endif; ?>
              <form action="logout.php" method="POST"><button type="submit"><i class="fas fa-right-from-bracket"></i> Log Out</button></form>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" class="btn btn-outline">Log In</a>
          <a href="signup.php" class="btn btn-primary">Sign Up</a>
        <?php endif; ?>
        <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>
      </div>
    </div>
  </nav>

  <div class="container">
    <?php if ($flash): ?>
      <div class="alert alert-success" style="margin-top:20px;"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($flashErr): ?>
      <div class="alert alert-danger" style="margin-top:20px;"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($flashErr) ?></div>
    <?php endif; ?>

    <nav class="breadcrumb">
      <a href="index.php">Home</a><i class="fas fa-chevron-right"></i>
      <a href="services.php">Services</a><i class="fas fa-chevron-right"></i>
      <span><?= htmlspecialchars($svc['title']) ?></span>
    </nav>

    <div class="detail-layout">
      <!-- LEFT: Service detail card -->
      <div>
        <div class="detail-card">
          <?php if ($svc['image']): ?>
            <img class="detail-hero" src="<?= htmlspecialchars($svc['image']) ?>" alt="<?= htmlspecialchars($svc['title']) ?>"/>
          <?php else: ?>
            <div class="detail-hero-placeholder"><i class="fas fa-tools"></i></div>
          <?php endif; ?>
          <div class="detail-body">
            <span class="detail-category"><?= htmlspecialchars($svc['category']) ?></span>
            <h1 class="detail-title"><?= htmlspecialchars($svc['title']) ?></h1>
            <div class="detail-meta">
              <div class="detail-meta-item">
                <i class="fas fa-user" style="color:var(--primary);"></i>
                <span>By <strong><?= htmlspecialchars($svc['worker_name']) ?></strong></span>
              </div>
              <?php if ($svc['location']): ?>
              <div class="detail-meta-item">
                <i class="fas fa-location-dot" style="color:var(--primary);"></i>
                <strong><?= htmlspecialchars($svc['location']) ?></strong>
              </div>
              <?php endif; ?>
              <?php if ($svc['phone']): ?>
              <div class="detail-meta-item">
                <i class="fas fa-phone" style="color:var(--primary);"></i>
                <strong><?= htmlspecialchars($svc['phone']) ?></strong>
              </div>
              <?php endif; ?>
              <div class="detail-meta-item">
                <i class="fas fa-calendar" style="color:var(--primary);"></i>
                <span>Listed <?= date('M j, Y', strtotime($svc['created_at'])) ?></span>
              </div>
            </div>
            <?php if ($svc['description']): ?>
            <div class="detail-section">
              <h3><i class="fas fa-align-left" style="color:var(--primary);margin-right:6px;"></i>About This Service</h3>
              <p class="detail-description"><?= htmlspecialchars($svc['description']) ?></p>
            </div>
            <?php endif; ?>
            <div class="price-badge">$<?= number_format($svc['price'],2) ?><span>per service</span></div>
          </div>
        </div>
      </div>

      <!-- RIGHT: Sidebar -->
      <div>
        <div class="sidebar-card">
          <h3><i class="fas fa-user-circle" style="color:var(--primary);"></i> About the Worker</h3>
          <div class="worker-info">
            <div class="w-avatar"><?= strtoupper(substr($svc['worker_name'],0,1)) ?></div>
            <div>
              <div class="w-name"><?= htmlspecialchars($svc['worker_name']) ?></div>
              <div class="w-email"><?= htmlspecialchars($svc['worker_email']) ?></div>
            </div>
          </div>
          <?php if (!empty($svc['worker_phone'])): ?>
          <div class="contact-row"><i class="fas fa-phone"></i> <?= htmlspecialchars($svc['worker_phone']) ?></div>
          <?php endif; ?>
          <?php if ($svc['location']): ?>
          <div class="contact-row"><i class="fas fa-location-dot"></i> <?= htmlspecialchars($svc['location']) ?></div>
          <?php endif; ?>
        </div>

        <div class="sidebar-card">
          <h3><i class="fas fa-calendar-check" style="color:var(--primary);"></i> Book This Service</h3>
          <?php if ($loggedIn && $userRole==='customer'): ?>
            <form action="book_service.php" method="POST">
              <input type="hidden" name="service_id" value="<?= (int)$svc['id'] ?>"/>
              <div class="form-group">
                <label for="booking_date">Preferred Date</label>
                <input type="date" id="booking_date" name="booking_date" min="<?= date('Y-m-d') ?>" required/>
              </div>
              <div style="background:var(--gray-100);border-radius:10px;padding:16px;margin-bottom:16px;">
                <div style="display:flex;justify-content:space-between;font-size:.9rem;margin-bottom:8px;">
                  <span style="color:var(--gray-500);">Service Price</span>
                  <strong>$<?= number_format($svc['price'],2) ?></strong>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.9rem;border-top:1px solid var(--gray-200);padding-top:8px;">
                  <span style="color:var(--dark);font-weight:700;">Total</span>
                  <strong style="color:var(--primary);font-size:1rem;">$<?= number_format($svc['price'],2) ?></strong>
                </div>
              </div>
              <button type="submit" class="btn btn-primary btn-full btn-lg">
                <i class="fas fa-calendar-check"></i> Confirm Booking
              </button>
            </form>
          <?php elseif ($loggedIn && $userRole==='worker'): ?>
            <div class="login-prompt">
              <i class="fas fa-info-circle" style="font-size:1.4rem;color:var(--primary);display:block;margin-bottom:8px;"></i>
              <p>Workers cannot book services. Use a customer account to book.</p>
              <a href="services.php" class="btn btn-outline" style="margin-top:4px;">Browse Services</a>
            </div>
          <?php else: ?>
            <div class="login-prompt">
              <p>Log in or sign up to book this service.</p>
              <a href="login.php" class="btn btn-primary" style="width:100%;margin-bottom:10px;">
                <i class="fas fa-right-to-bracket"></i> Log In to Book
              </a>
              <a href="signup.php" class="btn btn-outline" style="width:100%;">Create Account</a>
            </div>
          <?php endif; ?>
        </div>

        <a href="services.php" style="display:flex;align-items:center;gap:6px;font-size:.88rem;color:var(--primary);text-decoration:none;font-weight:600;padding:8px 0;">
          <i class="fas fa-arrow-left"></i> Back to All Services
        </a>
      </div>
    </div>
  </div>

  <script>
    document.getElementById('hamburger').addEventListener('click',()=>{
      document.getElementById('navLinks').classList.toggle('active');
    });
    window.addEventListener('scroll',()=>{
      document.querySelector('.navbar').classList.toggle('scrolled',window.scrollY>0);
    });
  </script>
</body>
</html>
