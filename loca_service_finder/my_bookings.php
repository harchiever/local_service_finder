<?php
// ============================================================
// my_bookings.php — Customer: view their bookings
// ============================================================
session_start();
require_once 'db.php';

// Only customers allowed
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'customer') {
    header('Location: login.php');
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$flash    = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$flashErr = $_SESSION['flash_error']   ?? null; unset($_SESSION['flash_error']);

// Fetch this customer's bookings with service & worker info
$stmt = mysqli_prepare($conn,
    'SELECT b.id, b.service_id, b.customer_id, b.status, b.booking_date,
            s.title AS service_title, s.category, s.price, s.image AS service_image,
            s.location, u.name AS worker_name, u.email AS worker_email, u.phone AS worker_phone
     FROM bookings b
     JOIN services s ON s.id = b.service_id
     JOIN users    u ON u.id = s.user_id
     WHERE b.customer_id = ?
     ORDER BY b.id DESC');
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$bookings = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$statusCounts = ['pending'=>0,'confirmed'=>0,'cancelled'=>0];
foreach ($bookings as $b) { $statusCounts[$b['status']]++; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>My Bookings — LocalService</title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body{padding-top:72px;background:#f8faff;}
    .user-menu{position:relative;display:inline-flex;align-items:center;gap:8px;cursor:pointer;}
    .user-avatar{width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;font-weight:700;font-size:.9rem;display:flex;align-items:center;justify-content:center;}
    .user-dropdown{display:none;position:absolute;top:calc(100% + 8px);right:0;background:#fff;border-radius:12px;box-shadow:var(--shadow-md);border:1px solid var(--gray-100);min-width:180px;z-index:999;overflow:hidden;}
    .user-dropdown.open{display:block;}
    .user-dropdown a,.user-dropdown form button{display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:.9rem;font-weight:500;color:var(--gray-700);text-decoration:none;background:none;border:none;width:100%;cursor:pointer;font-family:inherit;transition:var(--transition);}
    .user-dropdown a:hover,.user-dropdown form button:hover{background:var(--gray-100);color:var(--primary);}
    .alert{padding:12px 16px;border-radius:10px;margin-bottom:24px;font-size:.9rem;}
    .alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
    .alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}
    .page-hero{background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:44px 0;color:#fff;}
    .page-hero h1{font-size:1.9rem;font-weight:800;margin-bottom:6px;}
    .page-hero p{opacity:.85;}
    .stats-row{display:flex;gap:16px;margin-top:20px;flex-wrap:wrap;}
    .stat-chip{background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);border-radius:50px;padding:7px 18px;font-size:.85rem;font-weight:600;display:flex;align-items:center;gap:6px;}
    .main-wrap{padding:36px 0 80px;}
    .filter-tabs{display:flex;gap:8px;margin-bottom:28px;flex-wrap:wrap;}
    .filter-tab{padding:8px 18px;border-radius:50px;font-size:.85rem;font-weight:600;border:2px solid var(--gray-200);color:var(--gray-500);cursor:pointer;text-decoration:none;transition:var(--transition);}
    .filter-tab:hover,.filter-tab.active{border-color:var(--primary);color:var(--primary);background:var(--primary-light);}
    .bookings-grid{display:flex;flex-direction:column;gap:20px;}
    .booking-card{background:#fff;border:2px solid var(--gray-100);border-radius:var(--radius);display:flex;overflow:hidden;transition:var(--transition);}
    .booking-card:hover{border-color:var(--primary-light);box-shadow:var(--shadow-md);}
    .booking-thumb{width:120px;min-height:120px;flex-shrink:0;background:linear-gradient(135deg,var(--primary-light),#ddd);display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .booking-thumb img{width:100%;height:100%;object-fit:cover;}
    .booking-thumb i{font-size:2rem;color:var(--primary);opacity:.45;}
    .booking-body{flex:1;padding:18px 22px;display:flex;flex-direction:column;justify-content:space-between;}
    .booking-top{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;}
    .booking-title{font-size:1.05rem;font-weight:700;color:var(--dark);}
    .booking-title a{color:inherit;text-decoration:none;}
    .booking-title a:hover{color:var(--primary);}
    .status-badge{display:inline-block;padding:4px 12px;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:capitalize;}
    .status-pending{background:#fef3c7;color:#92400e;}
    .status-confirmed{background:#d1fae5;color:#065f46;}
    .status-cancelled{background:#fee2e2;color:#991b1b;}
    .booking-meta{display:flex;gap:18px;flex-wrap:wrap;margin-top:10px;}
    .booking-meta span{font-size:.83rem;color:var(--gray-500);display:flex;align-items:center;gap:5px;}
    .booking-meta strong{color:var(--dark);}
    .booking-price{font-size:1.2rem;font-weight:800;color:var(--primary);white-space:nowrap;}
    .booking-date{font-size:.8rem;color:var(--gray-500);margin-top:6px;}
    @media(max-width:540px){.booking-card{flex-direction:column;}.booking-thumb{width:100%;height:140px;}}
    .empty-state{text-align:center;padding:80px 20px;color:var(--gray-500);}
    .empty-state i{font-size:3.5rem;display:block;margin-bottom:18px;opacity:.35;}
    .empty-state h3{font-size:1.2rem;font-weight:700;color:var(--dark);margin-bottom:8px;}
  </style>
</head>
<body>
  <nav class="navbar scrolled">
    <div class="container nav-container">
      <a href="index.php" class="logo"><i class="fas fa-map-marker-alt"></i> LocalService</a>
      <div class="nav-links" id="navLinks">
        <a href="index.php">Home</a>
        <a href="services.php">Services</a>
        <a href="my_bookings.php" class="active">My Bookings</a>
      </div>
      <div class="nav-actions">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($userName,0,1)) ?></div>
          <span style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars(explode(' ',$userName)[0]) ?></span>
          <i class="fas fa-chevron-down" style="font-size:.75rem;color:var(--gray-500)"></i>
          <div class="user-dropdown">
            <a href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
            <form action="logout.php" method="POST"><button type="submit"><i class="fas fa-right-from-bracket"></i> Log Out</button></form>
          </div>
        </div>
        <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <div class="page-hero">
    <div class="container">
      <h1><i class="fas fa-calendar-check" style="margin-right:10px;"></i>My Bookings</h1>
      <p>Track and manage all your service bookings.</p>
      <div class="stats-row">
        <div class="stat-chip"><i class="fas fa-list"></i> <?= count($bookings) ?> Total</div>
        <?php if ($statusCounts['pending']): ?>
          <div class="stat-chip"><i class="fas fa-clock"></i> <?= $statusCounts['pending'] ?> Pending</div>
        <?php endif; ?>
        <?php if ($statusCounts['confirmed']): ?>
          <div class="stat-chip"><i class="fas fa-circle-check"></i> <?= $statusCounts['confirmed'] ?> Confirmed</div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="container main-wrap">
    <?php if ($flash): ?>
      <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($flashErr): ?>
      <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($flashErr) ?></div>
    <?php endif; ?>

    <?php if (empty($bookings)): ?>
      <div class="empty-state">
        <i class="fas fa-calendar-xmark"></i>
        <h3>No bookings yet</h3>
        <p>Browse our services and book your first appointment!</p>
        <a href="services.php" class="btn btn-primary" style="margin-top:20px;">
          <i class="fas fa-magnifying-glass"></i> Find Services
        </a>
      </div>
    <?php else: ?>
      <!-- Status filter tabs -->
      <?php $activeFilter = $_GET['status'] ?? 'all'; ?>
      <div class="filter-tabs">
        <a href="my_bookings.php" class="filter-tab <?= $activeFilter==='all'?'active':'' ?>">All (<?= count($bookings) ?>)</a>
        <a href="my_bookings.php?status=pending" class="filter-tab <?= $activeFilter==='pending'?'active':'' ?>">Pending (<?= $statusCounts['pending'] ?>)</a>
        <a href="my_bookings.php?status=confirmed" class="filter-tab <?= $activeFilter==='confirmed'?'active':'' ?>">Confirmed (<?= $statusCounts['confirmed'] ?>)</a>
        <a href="my_bookings.php?status=cancelled" class="filter-tab <?= $activeFilter==='cancelled'?'active':'' ?>">Cancelled (<?= $statusCounts['cancelled'] ?>)</a>
      </div>

      <div class="bookings-grid">
        <?php foreach ($bookings as $bk):
          if ($activeFilter !== 'all' && $bk['status'] !== $activeFilter) continue; ?>
          <div class="booking-card">
            <div class="booking-thumb">
              <?php if ($bk['service_image']): ?>
                <img src="<?= htmlspecialchars($bk['service_image']) ?>" alt="<?= htmlspecialchars($bk['service_title']) ?>"/>
              <?php else: ?>
                <i class="fas fa-tools"></i>
              <?php endif; ?>
            </div>
            <div class="booking-body">
              <div class="booking-top">
                <div>
                  <div class="booking-title">
                    <a href="service_detail.php?id=<?= (int)$bk['service_id'] ?>"><?= htmlspecialchars($bk['service_title']) ?></a>
                  </div>
                  <div class="booking-meta">
                    <span><i class="fas fa-tag" style="color:var(--primary);"></i>
                      <span class="status-badge status-<?= $bk['status'] ?>"><?= ucfirst($bk['status']) ?></span>
                    </span>
                    <span><i class="fas fa-user" style="color:var(--primary);"></i> Worker: <strong><?= htmlspecialchars($bk['worker_name']) ?></strong></span>
                    <?php if ($bk['worker_phone']): ?>
                      <span><i class="fas fa-phone" style="color:var(--primary);"></i> <?= htmlspecialchars($bk['worker_phone']) ?></span>
                    <?php endif; ?>
                    <?php if ($bk['location']): ?>
                      <span><i class="fas fa-location-dot" style="color:var(--primary);"></i> <?= htmlspecialchars($bk['location']) ?></span>
                    <?php endif; ?>
                    <?php if ($bk['booking_date']): ?>
                      <span><i class="fas fa-calendar" style="color:var(--primary);"></i> Booked for: <strong><?= date('M j, Y', strtotime($bk['booking_date'])) ?></strong></span>
                    <?php endif; ?>
                  </div>
                </div>
                <div class="booking-price">$<?= number_format($bk['price'],2) ?></div>
              </div>
              <div class="booking-date">
                <?= $bk['booking_date'] ? 'Appointment: ' . date('M j, Y', strtotime($bk['booking_date'])) : 'No date specified' ?>
              </div>
              <?php if ($bk['status'] === 'pending'): ?>
              <form action="cancel_booking.php" method="POST" style="margin-top:10px;"
                    onsubmit="return confirm('Cancel this booking?')">
                <input type="hidden" name="booking_id" value="<?= (int)$bk['id'] ?>"/>
                <button type="submit" style="background:#fee2e2;color:#991b1b;border:none;padding:6px 16px;border-radius:8px;font-family:inherit;font-size:.82rem;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.2s;">
                  <i class="fas fa-xmark"></i> Cancel Booking
                </button>
              </form>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div style="margin-top:32px;text-align:center;">
        <a href="services.php" class="btn btn-outline">
          <i class="fas fa-plus"></i> Book Another Service
        </a>
      </div>
    <?php endif; ?>
  </div>

  <script>
    document.getElementById('hamburger').addEventListener('click',()=>{
      document.getElementById('navLinks').classList.toggle('active');
    });
    // Click-toggle dropdown — closes when clicking outside
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
      const dropdown = userMenu.querySelector('.user-dropdown');
      userMenu.addEventListener('click', () => {
        dropdown.classList.toggle('open');
      });
      document.addEventListener('click', (e) => {
        if (!userMenu.contains(e.target)) dropdown.classList.remove('open');
      });
    }
  </script>
</body>
</html>
