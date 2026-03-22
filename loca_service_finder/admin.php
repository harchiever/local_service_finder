<?php
// ============================================================
// admin.php — Admin dashboard: manage users, services, bookings
// ============================================================
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php'); exit;
}

$adminName = $_SESSION['user_name'];
$flash     = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$flashErr  = $_SESSION['flash_error']   ?? null; unset($_SESSION['flash_error']);

// ── Stats ──────────────────────────────────────────
$totalUsers    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role != 'admin'"))[0];
$totalWorkers  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='worker'"))[0];
$totalCustom   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users WHERE role='customer'"))[0];
$totalServices = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM services"))[0];
$totalBookings = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings"))[0];
$pendingBook   = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM bookings WHERE status='pending'"))[0];

// ── All users ──────────────────────────────────────
$users = mysqli_fetch_all(mysqli_query($conn,
    "SELECT id,name,email,role,phone,created_at FROM users WHERE role!='admin' ORDER BY id DESC"
), MYSQLI_ASSOC);

// ── All services ───────────────────────────────────
$services = mysqli_fetch_all(mysqli_query($conn,
    "SELECT s.id, s.title, s.category, s.price, s.location,
            u.name AS worker_name
     FROM services s JOIN users u ON u.id=s.user_id ORDER BY s.id DESC"
), MYSQLI_ASSOC);

// ── All bookings ───────────────────────────────────
$bookings = mysqli_fetch_all(mysqli_query($conn,
    "SELECT b.id, b.status, b.booking_date,
            s.title AS service_title,
            c.name  AS customer_name,
            w.name  AS worker_name
     FROM bookings b
     JOIN services s ON s.id = b.service_id
     JOIN users    c ON c.id = b.customer_id
     JOIN users    w ON w.id = s.user_id
     ORDER BY b.id DESC"
), MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Admin Panel — LocalService</title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    *{box-sizing:border-box;}
    body{margin:0;font-family:'Inter',sans-serif;background:#f1f5f9;color:#1e293b;}

    /* ── Sidebar layout ── */
    .admin-wrap{display:flex;min-height:100vh;}
    .sidebar{width:240px;background:linear-gradient(160deg,#1e1b4b,#312e81);color:#fff;flex-shrink:0;display:flex;flex-direction:column;position:fixed;top:0;left:0;height:100vh;z-index:100;}
    .sidebar-logo{padding:24px 20px 16px;font-size:1.25rem;font-weight:800;display:flex;align-items:center;gap:10px;border-bottom:1px solid rgba(255,255,255,.1);}
    .sidebar-logo i{color:#a5b4fc;}
    .sidebar-badge{background:#a5b4fc;color:#1e1b4b;font-size:.65rem;font-weight:800;padding:2px 7px;border-radius:50px;margin-left:auto;}
    .sidebar nav{padding:16px 0;flex:1;}
    .sidebar nav a{display:flex;align-items:center;gap:10px;padding:11px 20px;font-size:.88rem;font-weight:500;color:rgba(255,255,255,.7);text-decoration:none;transition:.2s;border-left:3px solid transparent;}
    .sidebar nav a:hover{background:rgba(255,255,255,.08);color:#fff;}
    .sidebar nav a.active{background:rgba(165,180,252,.15);color:#a5b4fc;border-left-color:#a5b4fc;}
    .sidebar nav a i{width:18px;text-align:center;}
    .sidebar-footer{padding:16px 20px;border-top:1px solid rgba(255,255,255,.1);}
    .sidebar-footer a{display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.6);font-size:.85rem;text-decoration:none;}
    .sidebar-footer a:hover{color:#fff;}

    /* ── Main content ── */
    .main-content{margin-left:240px;flex:1;padding:32px;}

    /* ── Top bar ── */
    .topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:32px;}
    .topbar h1{font-size:1.6rem;font-weight:800;color:#1e293b;}
    .admin-badge{background:#ede9fe;color:#5b21b6;padding:6px 16px;border-radius:50px;font-size:.82rem;font-weight:700;display:flex;align-items:center;gap:6px;}

    /* ── Stats grid ── */
    .stats-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:16px;margin-bottom:32px;}
    .stat-card{background:#fff;border-radius:16px;padding:20px;border:1px solid #e2e8f0;transition:.2s;}
    .stat-card:hover{box-shadow:0 4px 20px rgba(0,0,0,.07);transform:translateY(-2px);}
    .stat-card .icon{width:44px;height:44px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;margin-bottom:14px;}
    .stat-card .num{font-size:1.9rem;font-weight:800;color:#1e293b;line-height:1;}
    .stat-card .label{font-size:.8rem;color:#64748b;margin-top:4px;font-weight:500;}
    .icon-blue{background:#eff6ff;color:#3b82f6;}
    .icon-purple{background:#f5f3ff;color:#7c3aed;}
    .icon-green{background:#f0fdf4;color:#16a34a;}
    .icon-orange{background:#fff7ed;color:#ea580c;}
    .icon-yellow{background:#fefce8;color:#ca8a04;}
    .icon-red{background:#fef2f2;color:#dc2626;}

    /* ── Section card ── */
    .section-card{background:#fff;border-radius:16px;border:1px solid #e2e8f0;margin-bottom:28px;overflow:hidden;}
    .section-header{display:flex;align-items:center;justify-content:space-between;padding:18px 24px;border-bottom:1px solid #f1f5f9;}
    .section-header h2{font-size:1rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:8px;}
    .section-header h2 i{color:#7c3aed;}
    .section-header span{font-size:.82rem;color:#94a3b8;}

    /* ── Tabs ── */
    .admin-tabs{display:flex;gap:0;border-bottom:2px solid #f1f5f9;margin-bottom:28px;}
    .admin-tab{padding:12px 22px;font-weight:600;font-size:.88rem;color:#94a3b8;cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;transition:.2s;}
    .admin-tab.active{color:#7c3aed;border-bottom-color:#7c3aed;}
    .tab-panel{display:none;}
    .tab-panel.active{display:block;}

    /* ── Tables ── */
    .data-table{width:100%;border-collapse:collapse;}
    .data-table th{text-align:left;padding:11px 16px;font-size:.75rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.06em;background:#f8fafc;border-bottom:1px solid #f1f5f9;}
    .data-table td{padding:13px 16px;font-size:.85rem;color:#1e293b;border-bottom:1px solid #f8fafc;vertical-align:middle;}
    .data-table tr:last-child td{border-bottom:none;}
    .data-table tr:hover td{background:#fafafa;}
    .role-badge{display:inline-block;padding:3px 10px;border-radius:50px;font-size:.72rem;font-weight:700;text-transform:capitalize;}
    .role-worker{background:#ede9fe;color:#5b21b6;}
    .role-customer{background:#dcfce7;color:#166534;}
    .role-admin{background:#fee2e2;color:#991b1b;}
    .status-badge{display:inline-block;padding:3px 10px;border-radius:50px;font-size:.72rem;font-weight:700;text-transform:capitalize;}
    .status-pending{background:#fef3c7;color:#92400e;}
    .status-confirmed{background:#dcfce7;color:#166534;}
    .status-cancelled{background:#fee2e2;color:#991b1b;}
    .del-btn{background:#fee2e2;color:#991b1b;border:none;padding:5px 12px;border-radius:7px;font-family:inherit;font-size:.78rem;font-weight:600;cursor:pointer;transition:.2s;}
    .del-btn:hover{background:#fca5a5;}
    .overflow-wrap{overflow-x:auto;}

    /* Alerts */
    .alert{padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:.88rem;}
    .alert-success{background:#dcfce7;color:#166534;border:1px solid #86efac;}
    .alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}

    @media(max-width:768px){
      .sidebar{display:none;}
      .main-content{margin-left:0;}
      .stats-grid{grid-template-columns:repeat(2,1fr);}
    }
  </style>
</head>
<body>
<div class="admin-wrap">

  <!-- ══════════ SIDEBAR ══════════ -->
  <aside class="sidebar">
    <div class="sidebar-logo">
      <i class="fas fa-map-marker-alt"></i> LocalService
      <span class="sidebar-badge">ADMIN</span>
    </div>
    <nav>
      <a href="#" class="active" onclick="showTab('users-tab');return false;">
        <i class="fas fa-users"></i> Users
      </a>
      <a href="#" onclick="showTab('services-tab');return false;">
        <i class="fas fa-tools"></i> Services
      </a>
      <a href="#" onclick="showTab('bookings-tab');return false;">
        <i class="fas fa-calendar-check"></i> Bookings
      </a>
    </nav>
    <div class="sidebar-footer">
      <form action="logout.php" method="POST">
        <button type="submit" style="background:none;border:none;cursor:pointer;padding:0;font-family:inherit;display:flex;align-items:center;gap:8px;color:rgba(255,255,255,.6);font-size:.85rem;">
          <i class="fas fa-right-from-bracket"></i> Log Out
        </button>
      </form>
    </div>
  </aside>

  <!-- ══════════ MAIN ══════════ -->
  <main class="main-content">
    <div class="topbar">
      <h1>Admin Dashboard</h1>
      <div class="admin-badge"><i class="fas fa-shield-halved"></i> <?= htmlspecialchars($adminName) ?></div>
    </div>

    <?php if ($flash): ?>
      <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($flashErr): ?>
      <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($flashErr) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="icon icon-blue"><i class="fas fa-users"></i></div>
        <div class="num"><?= $totalUsers ?></div>
        <div class="label">Total Users</div>
      </div>
      <div class="stat-card">
        <div class="icon icon-purple"><i class="fas fa-hard-hat"></i></div>
        <div class="num"><?= $totalWorkers ?></div>
        <div class="label">Workers</div>
      </div>
      <div class="stat-card">
        <div class="icon icon-green"><i class="fas fa-user"></i></div>
        <div class="num"><?= $totalCustom ?></div>
        <div class="label">Customers</div>
      </div>
      <div class="stat-card">
        <div class="icon icon-orange"><i class="fas fa-tools"></i></div>
        <div class="num"><?= $totalServices ?></div>
        <div class="label">Services</div>
      </div>
      <div class="stat-card">
        <div class="icon icon-yellow"><i class="fas fa-calendar-check"></i></div>
        <div class="num"><?= $totalBookings ?></div>
        <div class="label">Bookings</div>
      </div>
      <div class="stat-card">
        <div class="icon icon-red"><i class="fas fa-clock"></i></div>
        <div class="num"><?= $pendingBook ?></div>
        <div class="label">Pending</div>
      </div>
    </div>

    <!-- Tabs -->
    <div class="admin-tabs">
      <div class="admin-tab active" data-tab="users-tab"><i class="fas fa-users"></i> Users (<?= count($users) ?>)</div>
      <div class="admin-tab" data-tab="services-tab"><i class="fas fa-tools"></i> Services (<?= count($services) ?>)</div>
      <div class="admin-tab" data-tab="bookings-tab"><i class="fas fa-calendar-check"></i> Bookings (<?= count($bookings) ?>)</div>
    </div>

    <!-- ── USERS TAB ── -->
    <div class="tab-panel active" id="users-tab">
      <div class="section-card">
        <div class="section-header">
          <h2><i class="fas fa-users"></i> All Users</h2>
          <span><?= count($users) ?> records</span>
        </div>
        <div class="overflow-wrap">
          <table class="data-table">
            <thead>
              <tr><th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($users as $i => $u): ?>
              <tr>
                <td style="color:#94a3b8;"><?= $i+1 ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($u['name']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><span class="role-badge role-<?= $u['role'] ?>"><?= ucfirst($u['role']) ?></span></td>
                <td><?= htmlspecialchars($u['phone'] ?? '—') ?></td>
                <td>
                  <form action="admin_delete.php" method="POST"
                        onsubmit="return confirm('Delete user <?= htmlspecialchars(addslashes($u['name'])) ?>?')">
                    <input type="hidden" name="type" value="user"/>
                    <input type="hidden" name="id" value="<?= (int)$u['id'] ?>"/>
                    <button class="del-btn"><i class="fas fa-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── SERVICES TAB ── -->
    <div class="tab-panel" id="services-tab">
      <div class="section-card">
        <div class="section-header">
          <h2><i class="fas fa-tools"></i> All Services</h2>
          <span><?= count($services) ?> records</span>
        </div>
        <div class="overflow-wrap">
          <table class="data-table">
            <thead>
              <tr><th>#</th><th>Title</th><th>Category</th><th>Price</th><th>Worker</th><th>Location</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($services as $i => $s): ?>
              <tr>
                <td style="color:#94a3b8;"><?= $i+1 ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($s['title']) ?></td>
                <td><?= htmlspecialchars($s['category']) ?></td>
                <td style="font-weight:700;color:#7c3aed;">$<?= number_format($s['price'],2) ?></td>
                <td><?= htmlspecialchars($s['worker_name']) ?></td>
                <td><?= htmlspecialchars($s['location'] ?? '—') ?></td>
                <td>
                  <form action="admin_delete.php" method="POST"
                        onsubmit="return confirm('Delete this service?')">
                    <input type="hidden" name="type" value="service"/>
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>"/>
                    <button class="del-btn"><i class="fas fa-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ── BOOKINGS TAB ── -->
    <div class="tab-panel" id="bookings-tab">
      <div class="section-card">
        <div class="section-header">
          <h2><i class="fas fa-calendar-check"></i> All Bookings</h2>
          <span><?= count($bookings) ?> records</span>
        </div>
        <div class="overflow-wrap">
          <table class="data-table">
            <thead>
              <tr><th>#</th><th>Service</th><th>Customer</th><th>Worker</th><th>Date</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $i => $bk): ?>
              <tr>
                <td style="color:#94a3b8;"><?= $i+1 ?></td>
                <td style="font-weight:600;"><?= htmlspecialchars($bk['service_title']) ?></td>
                <td><?= htmlspecialchars($bk['customer_name']) ?></td>
                <td><?= htmlspecialchars($bk['worker_name']) ?></td>
                <td><?= $bk['booking_date'] ? date('M j, Y', strtotime($bk['booking_date'])) : '—' ?></td>
                <td><span class="status-badge status-<?= $bk['status'] ?>"><?= ucfirst($bk['status']) ?></span></td>
                <td>
                  <form action="admin_delete.php" method="POST"
                        onsubmit="return confirm('Delete this booking?')">
                    <input type="hidden" name="type" value="booking"/>
                    <input type="hidden" name="id" value="<?= (int)$bk['id'] ?>"/>
                    <button class="del-btn"><i class="fas fa-trash"></i> Delete</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </main>
</div>

<script>
  // Tab switching
  function showTab(tabId) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.admin-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(tabId).classList.add('active');
    document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
    // Update sidebar active state
    document.querySelectorAll('.sidebar nav a').forEach(a => a.classList.remove('active'));
  }
  document.querySelectorAll('.admin-tab').forEach(tab => {
    tab.addEventListener('click', () => showTab(tab.dataset.tab));
  });
</script>
</body>
</html>
