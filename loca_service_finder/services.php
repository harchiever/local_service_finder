<?php
// ============================================================
// services.php — Browse all services from database
// ============================================================
session_start();
require_once 'db.php';

$loggedIn  = isset($_SESSION['user_id']);
$userName  = $loggedIn ? $_SESSION['user_name'] : '';
$userRole  = $loggedIn ? $_SESSION['user_role'] : '';
$flash     = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$flashErr  = $_SESSION['flash_error']   ?? null; unset($_SESSION['flash_error']);

// Get filter params
$search   = trim($_GET['search']   ?? '');
$category = trim($_GET['category'] ?? '');

// Build query
$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[]  = '(s.title LIKE ? OR s.category LIKE ? OR s.location LIKE ? OR u.name LIKE ?)';
    $like     = '%' . $search . '%';
    $params[] = $like; $params[] = $like; $params[] = $like; $params[] = $like;
    $types   .= 'ssss';
}
if ($category !== '' && $category !== 'all') {
    $where[]  = 's.category LIKE ?';
    $params[] = '%' . $category . '%';
    $types   .= 's';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sql = "SELECT s.*, u.name AS worker_name FROM services s JOIN users u ON u.id=s.user_id {$whereSQL} ORDER BY s.created_at DESC";

$stmt = mysqli_prepare($conn, $sql);
if ($types && $params) mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);
$result   = mysqli_stmt_get_result($stmt);
$services = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_stmt_close($stmt);

$categories = ['Plumbing','Electrical','Cleaning','Painting','Moving','Tutoring','Landscaping','Photography','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Browse Services — LocalService</title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body{padding-top:72px;}
    .page-hero{background:linear-gradient(135deg,#f8faff,#eef2ff,#e0e7ff);padding:60px 0 40px;text-align:center;}
    .page-hero h1{font-size:2.5rem;font-weight:800;color:var(--dark);margin-bottom:12px;}
    .page-hero p{color:var(--gray-500);font-size:1.05rem;}
    .search-bar-wrap{max-width:640px;margin:24px auto 0;display:flex;gap:0;background:#fff;border-radius:14px;box-shadow:var(--shadow-md);border:2px solid transparent;overflow:hidden;transition:var(--transition);}
    .search-bar-wrap:focus-within{border-color:var(--primary);}
    .search-bar-wrap input{flex:1;padding:14px 16px;border:none;outline:none;font-family:inherit;font-size:.95rem;color:var(--dark);}
    .search-bar-wrap button{padding:14px 24px;background:var(--primary);color:#fff;border:none;cursor:pointer;font-family:inherit;font-weight:600;font-size:.95rem;transition:var(--transition);}
    .search-bar-wrap button:hover{background:var(--primary-dark);}
    .services-layout{padding:40px 0 80px;}
    .filter-wrap{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:32px;align-items:center;}
    .filter-wrap span{font-weight:600;color:var(--dark);font-size:.9rem;}
    .results-count{color:var(--gray-500);font-size:.9rem;margin-left:auto;}
    .services-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:24px;}
    .service-card{background:#fff;border:2px solid var(--gray-100);border-radius:var(--radius);overflow:hidden;transition:var(--transition);}
    .service-card:hover{transform:translateY(-6px);box-shadow:var(--shadow-md);border-color:var(--primary-light);}
    .service-img{height:180px;background:linear-gradient(135deg,var(--primary-light),#ddd);display:flex;align-items:center;justify-content:center;overflow:hidden;}
    .service-img img{width:100%;height:100%;object-fit:cover;}
    .service-img .no-img{font-size:3rem;color:var(--primary);opacity:.5;}
    .service-body{padding:20px;}
    .service-category{font-size:.78rem;font-weight:600;color:var(--primary);background:var(--primary-light);padding:4px 10px;border-radius:50px;display:inline-block;margin-bottom:8px;}
    .service-body h3{font-size:1.05rem;font-weight:700;color:var(--dark);margin-bottom:8px;}
    .service-meta{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:12px;}
    .service-meta span{font-size:.82rem;color:var(--gray-500);display:flex;align-items:center;gap:4px;}
    .service-footer{display:flex;align-items:center;justify-content:space-between;padding:16px 20px;border-top:1px solid var(--gray-100);}
    .service-price{font-size:1.15rem;font-weight:800;color:var(--dark);}
    .service-price span{font-size:.8rem;font-weight:400;color:var(--gray-500);}
    .empty-state{text-align:center;padding:80px 0;color:var(--gray-500);grid-column:1/-1;}
    .empty-state i{font-size:3rem;margin-bottom:16px;display:block;opacity:.4;}
    .alert{padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:.9rem;}
    .alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
    .alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}

    /* Navbar styles (same as index) */
    .user-menu{position:relative;display:inline-flex;align-items:center;gap:8px;cursor:pointer;}
    .user-avatar{width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;font-weight:700;font-size:.9rem;display:flex;align-items:center;justify-content:center;}
    .user-dropdown{display:none;position:absolute;top:calc(100% + 8px);right:0;background:#fff;border-radius:12px;box-shadow:var(--shadow-md);border:1px solid var(--gray-100);min-width:180px;z-index:999;overflow:hidden;}
    .user-dropdown.open{display:block;}
    .user-dropdown a,.user-dropdown form button{display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:.9rem;font-weight:500;color:var(--gray-700);text-decoration:none;background:none;border:none;width:100%;cursor:pointer;font-family:inherit;transition:var(--transition);}
    .user-dropdown a:hover,.user-dropdown form button:hover{background:var(--gray-100);color:var(--primary);}
  </style>
</head>
<body>

  <nav class="navbar scrolled">
    <div class="container nav-container">
      <a href="index.php" class="logo"><i class="fas fa-map-marker-alt"></i> LocalService</a>
      <div class="nav-links" id="navLinks">
        <a href="index.php">Home</a>
        <a href="services.php" class="active">Services</a>
        <?php if ($loggedIn && $userRole === 'customer'): ?><a href="my_bookings.php">My Bookings</a><?php endif; ?>
        <?php if ($loggedIn && $userRole === 'worker'):   ?><a href="dashboard.php">Dashboard</a><?php endif; ?>
      </div>
      <div class="nav-actions">
        <?php if ($loggedIn): ?>
          <div class="user-menu">
            <div class="user-avatar"><?= strtoupper(substr($userName,0,1)) ?></div>
            <span style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars(explode(' ',$userName)[0]) ?></span>
            <i class="fas fa-chevron-down" style="font-size:.75rem;color:var(--gray-500)"></i>
            <div class="user-dropdown">
              <?php if ($userRole==='worker'): ?><a href="dashboard.php"><i class="fas fa-gauge"></i> Dashboard</a>
              <?php else: ?><a href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
              <?php endif; ?>
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

  <div class="page-hero">
    <div class="container">
      <h1>Browse <span class="highlight">Services</span></h1>
      <p>Find qualified local professionals for any job</p>
      <form action="services.php" method="GET" class="search-bar-wrap">
        <input type="text" name="search" placeholder="Search services, category, or location..."
          value="<?= htmlspecialchars($search) ?>"/>
        <button type="submit"><i class="fas fa-search"></i> Search</button>
      </form>
    </div>
  </div>

  <section class="services-layout">
    <div class="container">

      <?php if ($flash): ?>
        <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
      <?php endif; ?>
      <?php if ($flashErr): ?>
        <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($flashErr) ?></div>
      <?php endif; ?>

      <!-- Category filters -->
      <div class="filter-wrap">
        <span>Filter:</span>
        <a href="services.php" class="filter-tab <?= $category==='' ? 'active' : '' ?>">All</a>
        <?php foreach ($categories as $cat): ?>
          <a href="services.php?category=<?= urlencode($cat) ?>"
             class="filter-tab <?= $category===$cat ? 'active' : '' ?>">
            <?= htmlspecialchars($cat) ?>
          </a>
        <?php endforeach; ?>
        <span class="results-count"><?= count($services) ?> result<?= count($services)!==1?'s':'' ?> found</span>
      </div>

      <!-- Services grid -->
      <div class="services-grid">
        <?php if (empty($services)): ?>
          <div class="empty-state">
            <i class="fas fa-search"></i>
            <h3>No services found</h3>
            <p>Try a different search term or category.</p>
            <?php if ($loggedIn && $userRole==='worker'): ?>
              <a href="dashboard.php" class="btn btn-primary" style="margin-top:16px;">Add Your Service</a>
            <?php elseif (!$loggedIn): ?>
              <a href="signup.php" class="btn btn-primary" style="margin-top:16px;">Join as a Worker</a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <?php foreach ($services as $svc): ?>
            <div class="service-card">
              <div class="service-img">
                <?php if ($svc['image']): ?>
                  <img src="<?= htmlspecialchars($svc['image']) ?>" alt="<?= htmlspecialchars($svc['title']) ?>"/>
                <?php else: ?>
                  <i class="fas fa-tools no-img"></i>
                <?php endif; ?>
              </div>
              <div class="service-body">
                <span class="service-category"><?= htmlspecialchars($svc['category']) ?></span>
                <h3><?= htmlspecialchars($svc['title']) ?></h3>
                <div class="service-meta">
                  <span><i class="fas fa-user"></i> <?= htmlspecialchars($svc['worker_name']) ?></span>
                  <?php if ($svc['location']): ?>
                    <span><i class="fas fa-location-dot"></i> <?= htmlspecialchars($svc['location']) ?></span>
                  <?php endif; ?>
                  <?php if ($svc['phone']): ?>
                    <span><i class="fas fa-phone"></i> <?= htmlspecialchars($svc['phone']) ?></span>
                  <?php endif; ?>
                </div>
                <?php if ($svc['description']): ?>
                  <p style="font-size:.88rem;color:var(--gray-500);line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;">
                    <?= htmlspecialchars($svc['description']) ?>
                  </p>
                <?php endif; ?>
              </div>
              <div class="service-footer">
                <div class="service-price">$<?= number_format($svc['price'],2) ?><span> /service</span></div>
                <a href="service_detail.php?id=<?= $svc['id'] ?>" class="btn btn-primary">
                  View Details <i class="fas fa-arrow-right"></i>
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <script>
    document.getElementById('hamburger').addEventListener('click',()=>{
      document.getElementById('navLinks').classList.toggle('active');
    });
    window.addEventListener('scroll',()=>{
      document.querySelector('.navbar').classList.toggle('scrolled',window.scrollY>0);
    });
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
      const dropdown = userMenu.querySelector('.user-dropdown');
      userMenu.addEventListener('click', () => { dropdown.classList.toggle('open'); });
      document.addEventListener('click', (e) => {
        if (!userMenu.contains(e.target)) dropdown.classList.remove('open');
      });
    }
  </script>
</body>
</html>
