<?php
// ============================================================
// dashboard.php — Worker: manage services + see bookings
// ============================================================
session_start();
require_once 'db.php';

// Only workers allowed
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'worker') {
    header('Location: login.php');
    exit;
}

$userId   = (int)$_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$flash    = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
$flashErr = $_SESSION['flash_error']   ?? null; unset($_SESSION['flash_error']);

// ------------------------------------------------------------------
// Fetch this worker's services
// ------------------------------------------------------------------
$stmtS = mysqli_prepare($conn,
    'SELECT * FROM services WHERE user_id = ? ORDER BY created_at DESC');
mysqli_stmt_bind_param($stmtS, 'i', $userId);
mysqli_stmt_execute($stmtS);
$myServices = mysqli_fetch_all(mysqli_stmt_get_result($stmtS), MYSQLI_ASSOC);
mysqli_stmt_close($stmtS);

// ------------------------------------------------------------------
// Fetch bookings for this worker's services
// ------------------------------------------------------------------
$stmtB = mysqli_prepare($conn,
    'SELECT b.id, b.service_id, b.customer_id, b.status, b.booking_date,
            s.title AS service_title,
            u.name AS customer_name, u.email AS customer_email
     FROM bookings b
     JOIN services s ON s.id = b.service_id
     JOIN users   u ON u.id = b.customer_id
     WHERE s.user_id = ?
     ORDER BY b.id DESC');
mysqli_stmt_bind_param($stmtB, 'i', $userId);
mysqli_stmt_execute($stmtB);
$bookings = mysqli_fetch_all(mysqli_stmt_get_result($stmtB), MYSQLI_ASSOC);
mysqli_stmt_close($stmtB);

$categories = ['Plumbing','Electrical','Cleaning','Painting','Moving','Tutoring','Landscaping','Photography','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Dashboard — LocalService</title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    body{padding-top:72px;background:#f8faff;}
    /* Navbar user menu */
    .user-menu{position:relative;display:inline-flex;align-items:center;gap:8px;cursor:pointer;}
    .user-avatar{width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;font-weight:700;font-size:.9rem;display:flex;align-items:center;justify-content:center;}
    .user-dropdown{display:none;position:absolute;top:calc(100% + 8px);right:0;background:#fff;border-radius:12px;box-shadow:var(--shadow-md);border:1px solid var(--gray-100);min-width:180px;z-index:999;overflow:hidden;}
    .user-dropdown.open{display:block;}
    .user-dropdown a,.user-dropdown form button{display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:.9rem;font-weight:500;color:var(--gray-700);text-decoration:none;background:none;border:none;width:100%;cursor:pointer;font-family:inherit;transition:var(--transition);}
    .user-dropdown a:hover,.user-dropdown form button:hover{background:var(--gray-100);color:var(--primary);}

    /* Page hero */
    .dash-hero{background:linear-gradient(135deg,#4f46e5,#7c3aed);padding:48px 0;color:#fff;}
    .dash-hero h1{font-size:2rem;font-weight:800;margin-bottom:6px;}
    .dash-hero p{opacity:.85;font-size:1rem;}
    .dash-stats{display:flex;gap:20px;margin-top:24px;flex-wrap:wrap;}
    .stat-pill{background:rgba(255,255,255,.15);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,.25);border-radius:50px;padding:8px 20px;font-size:.9rem;font-weight:600;display:flex;align-items:center;gap:8px;}

    /* Alert */
    .alert{padding:12px 16px;border-radius:10px;margin-bottom:20px;font-size:.9rem;}
    .alert-success{background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;}
    .alert-danger{background:#fee2e2;color:#991b1b;border:1px solid #fca5a5;}

    /* Tabs */
    .dash-tabs{display:flex;gap:0;border-bottom:2px solid var(--gray-200);margin-bottom:32px;}
    .dash-tab{padding:14px 24px;font-weight:600;font-size:.9rem;color:var(--gray-500);cursor:pointer;border-bottom:3px solid transparent;margin-bottom:-2px;transition:var(--transition);}
    .dash-tab.active{color:var(--primary);border-bottom-color:var(--primary);}
    .tab-panel{display:none;}
    .tab-panel.active{display:block;}

    /* Section card */
    .section-card{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow-sm);border:1px solid var(--gray-100);margin-bottom:32px;}
    .section-card-header{display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--gray-100);}
    .section-card-header h2{font-size:1.1rem;font-weight:700;color:var(--dark);}

    /* Add service form */
    .add-form{padding:24px;}
    .form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
    .form-group{margin-bottom:20px;}
    .form-group label{display:block;font-weight:600;font-size:.88rem;color:var(--dark);margin-bottom:6px;}
    .form-group input,.form-group select,.form-group textarea{width:100%;padding:11px 14px;border:2px solid var(--gray-300);border-radius:10px;font-family:inherit;font-size:.92rem;color:var(--dark);transition:var(--transition);background:#fff;}
    .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(79,70,229,.1);}
    .form-group textarea{resize:vertical;min-height:90px;}
    .file-hint{font-size:.78rem;color:var(--gray-500);margin-top:4px;}

    /* Services table */
    .services-table{width:100%;border-collapse:collapse;}
    .services-table th{text-align:left;padding:12px 16px;font-size:.8rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--gray-100);}
    .services-table td{padding:14px 16px;font-size:.88rem;color:var(--dark);border-bottom:1px solid var(--gray-100);vertical-align:middle;}
    .services-table tr:last-child td{border-bottom:none;}
    .svc-thumb{width:48px;height:48px;border-radius:8px;object-fit:cover;background:var(--gray-100);}
    .svc-no-thumb{width:48px;height:48px;border-radius:8px;background:var(--primary-light);display:flex;align-items:center;justify-content:center;color:var(--primary);font-size:1.1rem;}
    .badge-cat{font-size:.75rem;font-weight:600;color:var(--primary);background:var(--primary-light);padding:3px 10px;border-radius:50px;}
    .action-btns{display:flex;gap:8px;}
    .btn-sm{padding:6px 14px;font-size:.8rem;border-radius:8px;}

    /* Bookings table */
    .bookings-table{width:100%;border-collapse:collapse;}
    .bookings-table th{text-align:left;padding:12px 16px;font-size:.8rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.05em;border-bottom:1px solid var(--gray-100);}
    .bookings-table td{padding:14px 16px;font-size:.88rem;color:var(--dark);border-bottom:1px solid var(--gray-100);vertical-align:middle;}
    .bookings-table tr:last-child td{border-bottom:none;}
    .status-badge{display:inline-block;padding:4px 12px;border-radius:50px;font-size:.75rem;font-weight:700;text-transform:capitalize;}
    .status-pending{background:#fef3c7;color:#92400e;}
    .status-confirmed{background:#d1fae5;color:#065f46;}
    .status-cancelled{background:#fee2e2;color:#991b1b;}
    .empty-msg{text-align:center;padding:48px 16px;color:var(--gray-500);}
    .empty-msg i{font-size:2.5rem;display:block;margin-bottom:12px;opacity:.4;}

    /* Edit modal */
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center;padding:20px;}
    .modal-overlay.show{display:flex;}
    .modal-box{background:#fff;border-radius:20px;padding:32px;width:100%;max-width:560px;max-height:90vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,.25);}
    .modal-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;}
    .modal-header h3{font-size:1.2rem;font-weight:800;color:var(--dark);}
    .modal-close{background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--gray-500);padding:4px;}
    .modal-close:hover{color:var(--dark);}
  </style>
</head>
<body>

  <nav class="navbar scrolled">
    <div class="container nav-container">
      <a href="index.php" class="logo"><i class="fas fa-map-marker-alt"></i> LocalService</a>
      <div class="nav-links" id="navLinks">
        <a href="index.php">Home</a>
        <a href="services.php">Services</a>
        <a href="dashboard.php" class="active">Dashboard</a>
      </div>
      <div class="nav-actions">
        <div class="user-menu">
          <div class="user-avatar"><?= strtoupper(substr($userName,0,1)) ?></div>
          <span style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars(explode(' ',$userName)[0]) ?></span>
          <i class="fas fa-chevron-down" style="font-size:.75rem;color:var(--gray-500)"></i>
          <div class="user-dropdown">
            <a href="dashboard.php"><i class="fas fa-gauge"></i> Dashboard</a>
            <form action="logout.php" method="POST"><button type="submit"><i class="fas fa-right-from-bracket"></i> Log Out</button></form>
          </div>
        </div>
        <button class="hamburger" id="hamburger"><i class="fas fa-bars"></i></button>
      </div>
    </div>
  </nav>

  <!-- Hero -->
  <div class="dash-hero">
    <div class="container">
      <h1><i class="fas fa-gauge" style="margin-right:10px;"></i>Worker Dashboard</h1>
      <p>Welcome back, <?= htmlspecialchars($userName) ?>! Manage your services and track bookings.</p>
      <div class="dash-stats">
        <div class="stat-pill"><i class="fas fa-tools"></i> <?= count($myServices) ?> Service<?= count($myServices)!==1?'s':'' ?></div>
        <div class="stat-pill"><i class="fas fa-calendar-check"></i> <?= count($bookings) ?> Booking<?= count($bookings)!==1?'s':'' ?></div>
      </div>
    </div>
  </div>

  <div class="container" style="padding-top:40px;padding-bottom:80px;">

    <?php if ($flash): ?>
      <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= htmlspecialchars($flash) ?></div>
    <?php endif; ?>
    <?php if ($flashErr): ?>
      <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= htmlspecialchars($flashErr) ?></div>
    <?php endif; ?>

    <!-- Tabs -->
    <div class="dash-tabs">
      <div class="dash-tab active" data-tab="services-tab">
        <i class="fas fa-tools"></i> My Services
      </div>
      <div class="dash-tab" data-tab="bookings-tab">
        <i class="fas fa-calendar-check"></i> Bookings
      </div>
      <div class="dash-tab" data-tab="add-tab">
        <i class="fas fa-plus"></i> Add Service
      </div>
    </div>

    <!-- ========== TAB: My Services ========== -->
    <div class="tab-panel active" id="services-tab">
      <div class="section-card">
        <div class="section-card-header">
          <h2>Your Services</h2>
          <button class="btn btn-primary btn-sm" onclick="openTab('add-tab')">
            <i class="fas fa-plus"></i> Add New
          </button>
        </div>
        <?php if (empty($myServices)): ?>
          <div class="empty-msg">
            <i class="fas fa-tools"></i>
            <p style="font-weight:600;color:var(--dark);">No services yet</p>
            <p style="font-size:.9rem;">Add your first service to start receiving bookings.</p>
            <button class="btn btn-primary" style="margin-top:16px;" onclick="openTab('add-tab')">
              <i class="fas fa-plus"></i> Add Service
            </button>
          </div>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table class="services-table">
              <thead>
                <tr>
                  <th>Image</th>
                  <th>Title</th>
                  <th>Category</th>
                  <th>Price</th>
                  <th>Location</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($myServices as $svc): ?>
                  <tr>
                    <td>
                      <?php if ($svc['image']): ?>
                        <img class="svc-thumb" src="<?= htmlspecialchars($svc['image']) ?>" alt="<?= htmlspecialchars($svc['title']) ?>"/>
                      <?php else: ?>
                        <div class="svc-no-thumb"><i class="fas fa-tools"></i></div>
                      <?php endif; ?>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($svc['title']) ?></td>
                    <td><span class="badge-cat"><?= htmlspecialchars($svc['category']) ?></span></td>
                    <td style="font-weight:700;">$<?= number_format($svc['price'],2) ?></td>
                    <td><?= htmlspecialchars($svc['location'] ?? '—') ?></td>
                    <td>
                      <div class="action-btns">
                        <button class="btn btn-outline btn-sm"
                          onclick="openEditModal(<?= htmlspecialchars(json_encode($svc)) ?>)">
                          <i class="fas fa-pen"></i> Edit
                        </button>
                        <form action="delete_service.php" method="POST" onsubmit="return confirm('Delete this service?')">
                          <input type="hidden" name="service_id" value="<?= (int)$svc['id'] ?>"/>
                          <button type="submit" class="btn btn-sm" style="background:#fee2e2;color:#991b1b;border:none;cursor:pointer;padding:6px 14px;border-radius:8px;font-family:inherit;font-size:.8rem;font-weight:600;">
                            <i class="fas fa-trash"></i> Delete
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ========== TAB: Bookings ========== -->
    <div class="tab-panel" id="bookings-tab">
      <div class="section-card">
        <div class="section-card-header">
          <h2>Incoming Bookings</h2>
          <span style="font-size:.85rem;color:var(--gray-500);"><?= count($bookings) ?> total</span>
        </div>
        <?php if (empty($bookings)): ?>
          <div class="empty-msg">
            <i class="fas fa-calendar-xmark"></i>
            <p style="font-weight:600;color:var(--dark);">No bookings yet</p>
            <p style="font-size:.9rem;">Bookings will appear here once customers book your services.</p>
          </div>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table class="bookings-table">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Service</th>
                  <th>Customer</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Booked On</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($bookings as $i => $bk): ?>
                  <tr>
                    <td style="color:var(--gray-500);font-size:.8rem;"><?= $i+1 ?></td>
                    <td style="font-weight:600;"><?= htmlspecialchars($bk['service_title']) ?></td>
                    <td>
                      <div style="font-weight:600;"><?= htmlspecialchars($bk['customer_name']) ?></div>
                      <div style="font-size:.8rem;color:var(--gray-500);"><?= htmlspecialchars($bk['customer_email']) ?></div>
                    </td>
                    <td><?= $bk['booking_date'] ? date('M j, Y', strtotime($bk['booking_date'])) : '—' ?></td>
                    <td>
                      <span class="status-badge status-<?= $bk['status'] ?>">
                        <?= ucfirst($bk['status']) ?>
                      </span>
                    </td>
                    <td style="color:var(--gray-500);font-size:.82rem;">
                      <?= $bk['booking_date'] ? date('M j, Y', strtotime($bk['booking_date'])) : '—' ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- ========== TAB: Add Service ========== -->
    <div class="tab-panel" id="add-tab">
      <div class="section-card">
        <div class="section-card-header">
          <h2>Add New Service</h2>
        </div>
        <form class="add-form" action="add_service.php" method="POST" enctype="multipart/form-data">
          <div class="form-row">
            <div class="form-group">
              <label for="title">Service Title <span style="color:#ef4444;">*</span></label>
              <input type="text" id="title" name="title" placeholder="e.g. Professional Plumbing Repair" required maxlength="150"/>
            </div>
            <div class="form-group">
              <label for="category">Category <span style="color:#ef4444;">*</span></label>
              <select id="category" name="category" required>
                <option value="">— Select category —</option>
                <?php foreach ($categories as $cat): ?>
                  <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="price">Price (USD) <span style="color:#ef4444;">*</span></label>
              <input type="number" id="price" name="price" placeholder="0.00" min="0" step="0.01" required/>
            </div>
            <div class="form-group">
              <label for="phone">Contact Phone</label>
              <input type="text" id="phone" name="phone" placeholder="+1 555 000 0000" maxlength="20"/>
            </div>
          </div>
          <div class="form-group">
            <label for="location">Location</label>
            <input type="text" id="location" name="location" placeholder="e.g. Brooklyn, NY" maxlength="150"/>
          </div>
          <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" placeholder="Describe what you offer, experience, availability…"></textarea>
          </div>
          <div class="form-group">
            <label for="image">Service Image</label>
            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp,image/gif"/>
            <p class="file-hint"><i class="fas fa-info-circle"></i> JPG, PNG, WebP or GIF — max 5 MB</p>
          </div>
          <button type="submit" class="btn btn-primary btn-lg">
            <i class="fas fa-plus"></i> Publish Service
          </button>
        </form>
      </div>
    </div>

  </div><!-- /container -->

  <!-- ========== Edit Modal ========== -->
  <div class="modal-overlay" id="editModal">
    <div class="modal-box">
      <div class="modal-header">
        <h3><i class="fas fa-pen" style="color:var(--primary);margin-right:8px;"></i>Edit Service</h3>
        <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-xmark"></i></button>
      </div>
      <form action="edit_service.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="service_id" id="edit_service_id"/>
        <div class="form-row">
          <div class="form-group">
            <label>Title *</label>
            <input type="text" name="title" id="edit_title" required maxlength="150"/>
          </div>
          <div class="form-group">
            <label>Category *</label>
            <select name="category" id="edit_category" required>
              <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Price *</label>
            <input type="number" name="price" id="edit_price" min="0" step="0.01" required/>
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" id="edit_phone" maxlength="20"/>
          </div>
        </div>
        <div class="form-group">
          <label>Location</label>
          <input type="text" name="location" id="edit_location" maxlength="150"/>
        </div>
        <div class="form-group">
          <label>Description</label>
          <textarea name="description" id="edit_description"></textarea>
        </div>
        <div class="form-group">
          <label>Replace Image (optional)</label>
          <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"/>
          <p class="file-hint">Leave blank to keep existing image.</p>
        </div>
        <div style="display:flex;gap:12px;">
          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Changes
          </button>
          <button type="button" class="btn btn-outline" onclick="closeEditModal()">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Tab switching
    function openTab(tabId) {
      document.querySelectorAll('.dash-tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      document.getElementById(tabId).classList.add('active');
      document.querySelector(`[data-tab="${tabId}"]`).classList.add('active');
    }
    document.querySelectorAll('.dash-tab').forEach(tab => {
      tab.addEventListener('click', () => openTab(tab.dataset.tab));
    });

    // Hamburger
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('navLinks').classList.toggle('active');
    });
    // Click-toggle dropdown — closes when clicking outside
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
      const dropdown = userMenu.querySelector('.user-dropdown');
      userMenu.addEventListener('click', () => { dropdown.classList.toggle('open'); });
      document.addEventListener('click', (e) => {
        if (!userMenu.contains(e.target)) dropdown.classList.remove('open');
      });
    }

    // Edit modal
    function openEditModal(svc) {
      document.getElementById('edit_service_id').value = svc.id;
      document.getElementById('edit_title').value       = svc.title;
      document.getElementById('edit_category').value    = svc.category;
      document.getElementById('edit_price').value       = svc.price;
      document.getElementById('edit_phone').value       = svc.phone || '';
      document.getElementById('edit_location').value    = svc.location || '';
      document.getElementById('edit_description').value = svc.description || '';
      document.getElementById('editModal').classList.add('show');
    }
    function closeEditModal() {
      document.getElementById('editModal').classList.remove('show');
    }
    document.getElementById('editModal').addEventListener('click', function(e) {
      if (e.target === this) closeEditModal();
    });
  </script>
</body>
</html>
