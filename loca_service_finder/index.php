<?php
// ============================================================
// index.php — Main landing page (PHP version)
// ============================================================
session_start();
$loggedIn  = isset($_SESSION['user_id']);
$userName  = $loggedIn ? $_SESSION['user_name'] : '';
$userRole  = $loggedIn ? $_SESSION['user_role'] : '';
$flash     = $_SESSION['flash_success'] ?? null; unset($_SESSION['flash_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Local Service Finder — Find Trusted Local Services</title>
  <link rel="stylesheet" href="css/style.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .flash-banner{position:fixed;top:80px;left:50%;transform:translateX(-50%);background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;padding:12px 24px;border-radius:10px;font-weight:600;font-size:.95rem;z-index:9999;box-shadow:var(--shadow-md);animation:slideDown .3s ease;}
    @keyframes slideDown{from{opacity:0;transform:translateX(-50%) translateY(-20px)}to{opacity:1;transform:translateX(-50%) translateY(0)}}
    .user-menu{position:relative;display:inline-flex;align-items:center;gap:8px;cursor:pointer;}
    .user-avatar{width:36px;height:36px;border-radius:50%;background:var(--primary);color:#fff;font-weight:700;font-size:.9rem;display:flex;align-items:center;justify-content:center;}
    .user-dropdown{display:none;position:absolute;top:calc(100% + 8px);right:0;background:#fff;border-radius:12px;box-shadow:var(--shadow-md);border:1px solid var(--gray-100);min-width:180px;z-index:999;overflow:hidden;}
    .user-dropdown.open{display:block;}
    .user-dropdown a,.user-dropdown form button{display:flex;align-items:center;gap:10px;padding:12px 16px;font-size:.9rem;font-weight:500;color:var(--gray-700);text-decoration:none;background:none;border:none;width:100%;cursor:pointer;font-family:inherit;transition:var(--transition);}
    .user-dropdown a:hover,.user-dropdown form button:hover{background:var(--gray-100);color:var(--primary);}
    /* City picker */
    .city-menu{position:relative;display:inline-flex;align-items:center;cursor:pointer;}
    .city-dropdown{display:none;position:absolute;top:calc(100% + 8px);left:0;background:#fff;border-radius:12px;box-shadow:0 10px 40px rgba(0,0,0,.12);border:1px solid var(--gray-100);min-width:200px;z-index:999;overflow:hidden;padding:8px 0;}
    .city-dropdown.open{display:block;}
    .city-dropdown-header{padding:10px 16px 6px;font-size:.75rem;font-weight:700;color:var(--gray-500);text-transform:uppercase;letter-spacing:.06em;}
    .city-option{display:flex;align-items:center;gap:10px;padding:10px 16px;font-size:.88rem;font-weight:500;color:var(--gray-700);cursor:pointer;transition:.15s;}
    .city-option:hover{background:var(--primary-light);color:var(--primary);}
    .city-option i{color:var(--primary);width:14px;}
  </style>
</head>
<body>

<?php if ($flash): ?>
  <div class="flash-banner" id="flashBanner">
    <i class="fas fa-circle-check"></i> <?= htmlspecialchars($flash) ?>
  </div>
  <script>setTimeout(()=>{const b=document.getElementById('flashBanner');if(b)b.remove();},4000);</script>
<?php endif; ?>

  <!-- ========== NAVBAR ========== -->
  <nav class="navbar">
    <div class="container nav-container">
      <a href="index.php" class="logo">
        <i class="fas fa-map-marker-alt"></i> LocalService
      </a>

      <div class="nav-links" id="navLinks">
        <a href="#home">Home</a>
        <a href="services.php">Services</a>
        <a href="#how-it-works">How It Works</a>
        <a href="#testimonials">Reviews</a>
        <a href="#contact">Contact</a>
      </div>

      <div class="nav-actions">
        <div class="city-menu" id="cityMenu">
          <div class="location-badge" id="cityBtn">
            <i class="fas fa-location-dot"></i>
            <span id="userLocation">Your City</span>
            <i class="fas fa-chevron-down"></i>
          </div>
          <div class="city-dropdown" id="cityDropdown">
            <div class="city-dropdown-header">Select Your City</div>
            <div class="city-option" onclick="setCity('Mumbai')"><i class="fas fa-location-dot"></i> Mumbai</div>
            <div class="city-option" onclick="setCity('Delhi')"><i class="fas fa-location-dot"></i> Delhi</div>
            <div class="city-option" onclick="setCity('Bangalore')"><i class="fas fa-location-dot"></i> Bangalore</div>
            <div class="city-option" onclick="setCity('Hyderabad')"><i class="fas fa-location-dot"></i> Hyderabad</div>
            <div class="city-option" onclick="setCity('Chennai')"><i class="fas fa-location-dot"></i> Chennai</div>
            <div class="city-option" onclick="setCity('Kolkata')"><i class="fas fa-location-dot"></i> Kolkata</div>
            <div class="city-option" onclick="setCity('Pune')"><i class="fas fa-location-dot"></i> Pune</div>
            <div class="city-option" onclick="setCity('Ahmedabad')"><i class="fas fa-location-dot"></i> Ahmedabad</div>
            <div class="city-option" onclick="setCity('Jaipur')"><i class="fas fa-location-dot"></i> Jaipur</div>
            <div class="city-option" onclick="setCity('Surat')"><i class="fas fa-location-dot"></i> Surat</div>
          </div>
        </div>

        <?php if ($loggedIn): ?>
          <div class="user-menu">
            <div class="user-avatar"><?= strtoupper(substr($userName, 0, 1)) ?></div>
            <span style="font-weight:600;font-size:.9rem;"><?= htmlspecialchars(explode(' ', $userName)[0]) ?></span>
            <i class="fas fa-chevron-down" style="font-size:.75rem;color:var(--gray-500)"></i>
            <div class="user-dropdown">
              <?php if ($userRole === 'worker'): ?>
                <a href="dashboard.php"><i class="fas fa-gauge"></i> Dashboard</a>
              <?php else: ?>
                <a href="services.php"><i class="fas fa-search"></i> Browse Services</a>
                <a href="my_bookings.php"><i class="fas fa-calendar-check"></i> My Bookings</a>
              <?php endif; ?>
              <form action="logout.php" method="POST">
                <button type="submit"><i class="fas fa-right-from-bracket"></i> Log Out</button>
              </form>
            </div>
          </div>
        <?php else: ?>
          <a href="login.php" class="btn btn-outline">Log In</a>
          <a href="signup.php" class="btn btn-primary">Sign Up</a>
        <?php endif; ?>
      </div>

      <button class="hamburger" id="hamburger">
        <i class="fas fa-bars"></i>
      </button>
    </div>
  </nav>

  <!-- ========== HERO ========== -->
  <section class="hero" id="home">
    <div class="hero-bg-shapes">
      <div class="shape shape-1"></div>
      <div class="shape shape-2"></div>
      <div class="shape shape-3"></div>
    </div>
    <div class="container hero-container">
      <div class="hero-content">
        <span class="hero-badge">🏆 #1 Local Service Platform</span>
        <h1>Find Trusted <span class="highlight">Local Services</span> Near You</h1>
        <p class="hero-subtitle">
          Connect with verified professionals in your area. From plumbing to tutoring,
          find the right service provider in minutes.
        </p>

        <div class="search-box">
          <div class="search-field">
            <i class="fas fa-search"></i>
            <input type="text" id="searchInput" placeholder="What service do you need?"/>
          </div>
          <div class="search-field">
            <i class="fas fa-map-marker-alt"></i>
            <input type="text" id="locationInput" placeholder="Your location"/>
          </div>
          <button class="btn btn-primary btn-search" id="searchBtn" onclick="goSearch()">
            <i class="fas fa-search"></i> Search
          </button>
        </div>

        <div class="popular-searches">
          <span>Popular:</span>
          <button class="tag" onclick="quickSearch('Plumbing')">Plumber</button>
          <button class="tag" onclick="quickSearch('Electrical')">Electrician</button>
          <button class="tag" onclick="quickSearch('Cleaning')">House Cleaning</button>
          <button class="tag" onclick="quickSearch('Tutoring')">Tutor</button>
          <button class="tag" onclick="quickSearch('Painting')">Painter</button>
        </div>

        <div class="hero-stats">
          <div class="stat"><strong>10K+</strong><span>Service Providers</span></div>
          <div class="stat"><strong>50K+</strong><span>Happy Customers</span></div>
          <div class="stat"><strong>4.8</strong><span>Avg. Rating ⭐</span></div>
        </div>
      </div>

      <div class="hero-image">
        <div class="hero-card card-1"><i class="fas fa-shield-halved"></i><span>Verified Pros</span></div>
        <div class="hero-card card-2"><i class="fas fa-clock"></i><span>Quick Response</span></div>
        <div class="hero-card card-3"><i class="fas fa-star"></i><span>4.8 Rating</span></div>
        <div class="hero-illustration"><i class="fas fa-people-arrows"></i></div>
      </div>
    </div>
  </section>

  <!-- ========== CATEGORIES ========== -->
  <section class="categories" id="categories">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">Browse by Category</span>
        <h2>Our <span class="highlight">Services</span></h2>
        <p>Find professionals across all major service categories</p>
      </div>
      <div class="categories-grid" id="categoriesGrid"></div>
    </div>
  </section>

  <!-- ========== HOW IT WORKS ========== -->
  <section class="how-it-works" id="how-it-works">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">Simple Process</span>
        <h2>How It <span class="highlight">Works</span></h2>
        <p>Find and book services in just three simple steps</p>
      </div>
      <div class="steps">
        <div class="step">
          <div class="step-number">1</div>
          <div class="step-icon"><i class="fas fa-search"></i></div>
          <h3>Search a Service</h3>
          <p>Enter the service you need and your location to find nearby professionals.</p>
        </div>
        <div class="step-connector"><i class="fas fa-arrow-right"></i></div>
        <div class="step">
          <div class="step-number">2</div>
          <div class="step-icon"><i class="fas fa-user-check"></i></div>
          <h3>Choose a Provider</h3>
          <p>Browse profiles, check reviews, and select the best fit for your needs.</p>
        </div>
        <div class="step-connector"><i class="fas fa-arrow-right"></i></div>
        <div class="step">
          <div class="step-number">3</div>
          <div class="step-icon"><i class="fas fa-calendar-check"></i></div>
          <h3>Book & Confirm</h3>
          <p>Pick a date and confirm your booking. The provider will reach out to you.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== FEATURED SERVICES ========== -->
  <section class="providers" id="providers">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">Featured</span>
        <h2>Available <span class="highlight">Services</span></h2>
        <p>Real services posted by verified local professionals</p>
      </div>
      <div class="providers-grid" id="providersGrid">
        <p style="text-align:center;color:var(--gray-500);grid-column:1/-1;padding:40px 0;">
          <i class="fas fa-spinner fa-spin" style="font-size:2rem;margin-bottom:12px;display:block;"></i>
          Loading services...
        </p>
      </div>
      <div style="text-align:center;margin-top:32px;">
        <a href="services.php" class="btn btn-outline btn-lg">View All Services <i class="fas fa-arrow-right"></i></a>
      </div>
    </div>
  </section>

  <!-- ========== TESTIMONIALS ========== -->
  <section class="testimonials" id="testimonials">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">What People Say</span>
        <h2>Customer <span class="highlight">Reviews</span></h2>
        <p>Real experiences from our community</p>
      </div>
      <div class="testimonials-grid" id="testimonialsGrid"></div>
    </div>
  </section>

  <!-- ========== CONTACT ========== -->
  <section class="contact" id="contact">
    <div class="container">
      <div class="section-header">
        <span class="section-badge">Get in Touch</span>
        <h2>Contact <span class="highlight">Us</span></h2>
        <p>Have questions? We'd love to hear from you</p>
      </div>
      <div class="contact-grid">
        <form class="contact-form" id="contactForm" onsubmit="handleContact(event)">
          <div class="form-row">
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" id="contactName" placeholder="John Doe" required/>
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" id="contactEmail" placeholder="john@example.com" required/>
            </div>
          </div>
          <div class="form-group">
            <label>Subject</label>
            <input type="text" id="contactService" placeholder="How can we help?" required/>
          </div>
          <div class="form-group">
            <label>Message</label>
            <textarea id="contactMessage" rows="5" placeholder="Tell us more..." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary btn-lg btn-full">
            Send Message <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>
    </div>
  </section>

  <!-- Back to Top -->
  <button class="back-to-top" id="backToTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="fas fa-chevron-up"></i>
  </button>

  <script src="js/app.js"></script>
  <script>
    // Search redirect
    function goSearch() {
      const q = document.getElementById('searchInput').value.trim();
      if (q) window.location.href = 'services.php?search=' + encodeURIComponent(q);
      else  window.location.href = 'services.php';
    }
    function quickSearch(term) {
      window.location.href = 'services.php?category=' + encodeURIComponent(term);
    }
    document.getElementById('searchInput').addEventListener('keypress', e => {
      if (e.key === 'Enter') goSearch();
    });

    // Load featured services via AJAX
    document.addEventListener('DOMContentLoaded', () => {
      fetch('fetch_services.php')
        .then(r => r.json())
        .then(res => {
          const grid = document.getElementById('providersGrid');
          if (!res.success || res.data.length === 0) {
            grid.innerHTML = `<p style="text-align:center;color:var(--gray-500);grid-column:1/-1;padding:40px 0;">
              <i class="fas fa-tools" style="font-size:2rem;margin-bottom:12px;display:block;"></i>
              No services yet. <a href="signup.php" style="color:var(--primary);">Be the first to add one!</a></p>`;
            return;
          }
          // Show first 6
          const shown = res.data.slice(0, 6);
          grid.innerHTML = shown.map(s => `
            <div class="provider-card">
              <div class="provider-header">
                <div class="provider-avatar" style="background:var(--primary);">
                  ${s.image
                    ? `<img src="${s.image}" alt="${s.title}" style="width:100%;height:100%;object-fit:cover;border-radius:14px;">`
                    : s.title.charAt(0).toUpperCase()}
                </div>
                <div class="provider-info">
                  <h3>${s.title}</h3>
                  <span class="provider-service">${s.category}</span>
                </div>
              </div>
              <div class="provider-body">
                <div class="provider-meta">
                  <span><i class="fas fa-location-dot"></i> ${s.location || 'N/A'}</span>
                  <span><i class="fas fa-user"></i> ${s.worker_name}</span>
                </div>
                <div class="provider-tags">
                  <span class="provider-tag">${s.category}</span>
                </div>
              </div>
              <div class="provider-footer">
                <div class="provider-price">$${parseFloat(s.price).toFixed(2)}<span>/service</span></div>
                <a href="service_detail.php?id=${s.id}" class="btn btn-primary btn-book">View Details</a>
              </div>
            </div>
          `).join('');
        })
        .catch(() => {
          document.getElementById('providersGrid').innerHTML =
            '<p style="text-align:center;grid-column:1/-1;color:var(--gray-500);padding:40px;">Could not load services.</p>';
        });
    });

    // Contact form
    function handleContact(e) {
      e.preventDefault();
      alert('✅ Thanks for your message! We\'ll get back to you soon.');
      e.target.reset();
    }

    // Hamburger
    document.getElementById('hamburger').addEventListener('click', () => {
      document.getElementById('navLinks').classList.toggle('active');
    });

    // ── Profile dropdown (click-toggle, close on outside click) ──
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
      const ud = userMenu.querySelector('.user-dropdown');
      userMenu.addEventListener('click', () => ud.classList.toggle('open'));
      document.addEventListener('click', e => {
        if (!userMenu.contains(e.target)) ud.classList.remove('open');
      });
    }

    // ── City picker ──
    const cityMenu     = document.getElementById('cityMenu');
    const cityDropdown = document.getElementById('cityDropdown');
    if (cityMenu) {
      document.getElementById('cityBtn').addEventListener('click', () => {
        cityDropdown.classList.toggle('open');
      });
      document.addEventListener('click', e => {
        if (!cityMenu.contains(e.target)) cityDropdown.classList.remove('open');
      });
    }
    function setCity(city) {
      document.getElementById('userLocation').textContent = city;
      cityDropdown.classList.remove('open');
      // Pre-fill the location search box
      const locInput = document.getElementById('locationInput');
      if (locInput) locInput.value = city;
      // Remember choice
      localStorage.setItem('selectedCity', city);
    }
    // Restore saved city
    const savedCity = localStorage.getItem('selectedCity');
    if (savedCity) {
      document.getElementById('userLocation').textContent = savedCity;
      const locInput = document.getElementById('locationInput');
      if (locInput) locInput.value = savedCity;
    }

    // Navbar scroll
    window.addEventListener('scroll', () => {
      document.querySelector('.navbar').classList.toggle('scrolled', window.scrollY > 50);
      const bt = document.getElementById('backToTop');
      if (bt) bt.classList.toggle('visible', window.scrollY > 500);
    });
  </script>
</body>
</html>
