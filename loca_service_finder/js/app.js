// ========== DATA ==========
const categories = [
  { icon: "fa-wrench", name: "Plumbing", count: "120+ Providers", color: "#3b82f6", bg: "#dbeafe" },
  { icon: "fa-bolt", name: "Electrical", count: "85+ Providers", color: "#f59e0b", bg: "#fef3c7" },
  { icon: "fa-broom", name: "Cleaning", count: "200+ Providers", color: "#10b981", bg: "#d1fae5" },
  { icon: "fa-paint-roller", name: "Painting", count: "65+ Providers", color: "#8b5cf6", bg: "#ede9fe" },
  { icon: "fa-truck-moving", name: "Moving", count: "45+ Providers", color: "#ef4444", bg: "#fee2e2" },
  { icon: "fa-graduation-cap", name: "Tutoring", count: "150+ Providers", color: "#06b6d4", bg: "#cffafe" },
  { icon: "fa-tree", name: "Landscaping", count: "70+ Providers", color: "#22c55e", bg: "#dcfce7" },
  { icon: "fa-camera", name: "Photography", count: "90+ Providers", color: "#ec4899", bg: "#fce7f3" },
];

const providers = [
  {
    name: "Mike Johnson",
    initials: "MJ",
    service: "Plumbing",
    category: "plumbing",
    rating: 4.9,
    reviews: 127,
    location: "Brooklyn, NY",
    experience: "12 yrs",
    price: "$45",
    unit: "/hr",
    tags: ["Emergency", "Licensed", "Insured"],
    color: "#3b82f6",
    verified: true,
  },
  {
    name: "Sarah Williams",
    initials: "SW",
    service: "House Cleaning",
    category: "cleaning",
    rating: 4.8,
    reviews: 203,
    location: "Manhattan, NY",
    experience: "8 yrs",
    price: "$35",
    unit: "/hr",
    tags: ["Eco-Friendly", "Deep Clean", "Same Day"],
    color: "#10b981",
    verified: true,
  },
  {
    name: "David Chen",
    initials: "DC",
    service: "Electrical",
    category: "electrical",
    rating: 4.9,
    reviews: 94,
    location: "Queens, NY",
    experience: "15 yrs",
    price: "$55",
    unit: "/hr",
    tags: ["Certified", "Commercial", "Residential"],
    color: "#f59e0b",
    verified: true,
  },
  {
    name: "Emily Rodriguez",
    initials: "ER",
    service: "Interior Painting",
    category: "painting",
    rating: 4.7,
    reviews: 156,
    location: "Bronx, NY",
    experience: "10 yrs",
    price: "$40",
    unit: "/hr",
    tags: ["Color Consult", "Wallpaper", "Cabinet"],
    color: "#8b5cf6",
    verified: true,
  },
  {
    name: "James Wilson",
    initials: "JW",
    service: "Math Tutoring",
    category: "tutoring",
    rating: 5.0,
    reviews: 88,
    location: "Staten Island, NY",
    experience: "7 yrs",
    price: "$30",
    unit: "/hr",
    tags: ["SAT Prep", "Calculus", "Online"],
    color: "#06b6d4",
    verified: true,
  },
  {
    name: "Lisa Thompson",
    initials: "LT",
    service: "Deep Cleaning",
    category: "cleaning",
    rating: 4.8,
    reviews: 175,
    location: "Harlem, NY",
    experience: "6 yrs",
    price: "$38",
    unit: "/hr",
    tags: ["Move-in/out", "Office", "Sanitize"],
    color: "#22c55e",
    verified: true,
  },
];

const testimonials = [
  {
    text: "Found an amazing plumber within minutes! He arrived the same day and fixed everything perfectly. This platform is a lifesaver.",
    name: "Amanda Foster",
    role: "Homeowner",
    initials: "AF",
    color: "#3b82f6",
    rating: 5,
  },
  {
    text: "I've been using LocalServ for all my home needs. The quality of professionals here is outstanding. Highly recommend!",
    name: "Robert Kim",
    role: "Regular Customer",
    initials: "RK",
    color: "#10b981",
    rating: 5,
  },
  {
    text: "As a service provider, this platform has helped me grow my business significantly. The booking system is seamless.",
    name: "Maria Santos",
    role: "Cleaning Professional",
    initials: "MS",
    color: "#8b5cf6",
    rating: 5,
  },
];

// ========== RENDER FUNCTIONS ==========

function renderCategories() {
  const grid = document.getElementById("categoriesGrid");
  grid.innerHTML = categories
    .map(
      (cat) => `
    <div class="category-card" data-category="${cat.name.toLowerCase()}">
      <div class="category-icon" style="background: ${cat.bg}; color: ${cat.color};">
        <i class="fas ${cat.icon}"></i>
      </div>
      <h3>${cat.name}</h3>
      <p>${cat.count}</p>
    </div>
  `
    )
    .join("");

  // Add click events
  document.querySelectorAll(".category-card").forEach((card) => {
    card.addEventListener("click", () => {
      const categoryName = card.dataset.category;
      document.getElementById("searchInput").value =
        categoryName.charAt(0).toUpperCase() + categoryName.slice(1);
      document.getElementById("home").scrollIntoView({ behavior: "smooth" });
    });
  });
}

function renderProviders(filter = "all") {
  const grid = document.getElementById("providersGrid");
  const filtered =
    filter === "all"
      ? providers
      : providers.filter((p) => p.category === filter);

  grid.innerHTML = filtered
    .map(
      (p) => `
    <div class="provider-card" data-category="${p.category}">
      <div class="provider-header">
        <div class="provider-avatar" style="background: ${p.color};">
          ${p.initials}
        </div>
        <div class="provider-info">
          <h3>${p.name} ${p.verified ? '<i class="fas fa-circle-check" style="color: #3b82f6; font-size: 0.9rem;"></i>' : ""}</h3>
          <span class="provider-service">${p.service}</span>
        </div>
      </div>
      <div class="provider-body">
        <div class="provider-meta">
          <span class="provider-rating">
            <i class="fas fa-star"></i> ${p.rating} (${p.reviews})
          </span>
          <span><i class="fas fa-location-dot"></i> ${p.location}</span>
          <span><i class="fas fa-briefcase"></i> ${p.experience}</span>
        </div>
        <div class="provider-tags">
          ${p.tags.map((tag) => `<span class="provider-tag">${tag}</span>`).join("")}
        </div>
      </div>
      <div class="provider-footer">
        <div class="provider-price">${p.price}<span>${p.unit}</span></div>
        <button class="btn btn-primary btn-book">Book Now</button>
      </div>
    </div>
  `
    )
    .join("");

  // Book button click
  document.querySelectorAll(".btn-book").forEach((btn) => {
    btn.addEventListener("click", () => {
      alert("🎉 Booking feature coming soon! This is a UI demo.");
    });
  });
}

function renderTestimonials() {
  const grid = document.getElementById("testimonialsGrid");
  grid.innerHTML = testimonials
    .map(
      (t) => `
    <div class="testimonial-card">
      <div class="testimonial-quote"><i class="fas fa-quote-left"></i></div>
      <div class="testimonial-stars">
        ${'<i class="fas fa-star"></i> '.repeat(t.rating)}
      </div>
      <p>"${t.text}"</p>
      <div class="testimonial-author">
        <div class="testimonial-avatar" style="background: ${t.color};">
          ${t.initials}
        </div>
        <div>
          <h4>${t.name}</h4>
          <span>${t.role}</span>
        </div>
      </div>
    </div>
  `
    )
    .join("");
}

// ========== EVENT LISTENERS ==========

// Hamburger menu
document.getElementById("hamburger").addEventListener("click", () => {
  document.getElementById("navLinks").classList.toggle("active");
});

// Filter tabs
document.querySelectorAll(".filter-tab").forEach((tab) => {
  tab.addEventListener("click", () => {
    document.querySelectorAll(".filter-tab").forEach((t) => t.classList.remove("active"));
    tab.classList.add("active");
    renderProviders(tab.dataset.filter);
  });
});

// Popular tags
document.querySelectorAll(".tag").forEach((tag) => {
  tag.addEventListener("click", () => {
    document.getElementById("searchInput").value = tag.dataset.service;
    performSearch();
  });
});

// Search
document.getElementById("searchBtn").addEventListener("click", performSearch);
document.getElementById("searchInput").addEventListener("keypress", (e) => {
  if (e.key === "Enter") performSearch();
});

function performSearch() {
  const query = document.getElementById("searchInput").value.toLowerCase().trim();
  const location = document.getElementById("locationInput").value;

  if (!query) {
    alert("Please enter a service to search for.");
    return;
  }

  const results = providers.filter(
    (p) =>
      p.service.toLowerCase().includes(query) ||
      p.name.toLowerCase().includes(query) ||
      p.category.toLowerCase().includes(query) ||
      p.tags.some((tag) => tag.toLowerCase().includes(query))
  );

  const modal = document.getElementById("searchModal");
  const info = document.getElementById("searchResultsInfo");
  const list = document.getElementById("searchResults");

  info.textContent = `Found ${results.length} result(s) for "${query}" in ${location}`;

  if (results.length === 0) {
    list.innerHTML = `
      <div style="text-align: center; padding: 40px; color: var(--gray-500);">
        <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 12px; display: block;"></i>
        <p>No providers found. Try a different search term.</p>
      </div>
    `;
  } else {
    list.innerHTML = results
      .map(
        (p) => `
      <div class="search-result-item">
        <div class="result-avatar" style="background: ${p.color};">${p.initials}</div>
        <div class="result-info">
          <h4>${p.name}</h4>
          <p>${p.service} • ${p.location} • ${p.price}${p.unit}</p>
          <span class="result-rating"><i class="fas fa-star"></i> ${p.rating} (${p.reviews} reviews)</span>
        </div>
      </div>
    `
      )
      .join("");
  }

  modal.classList.add("active");
}

// Login modal
document.getElementById("loginBtn")?.addEventListener("click", () => {
  document.getElementById("loginModal").classList.add("active");
});

document.getElementById("closeLogin").addEventListener("click", () => {
  document.getElementById("loginModal").classList.remove("active");
});

document.getElementById("closeSearch").addEventListener("click", () => {
  document.getElementById("searchModal").classList.remove("active");
});

// Close modals on overlay click
document.querySelectorAll(".modal-overlay").forEach((overlay) => {
  overlay.addEventListener("click", (e) => {
    if (e.target === overlay) overlay.classList.remove("active");
  });
});

// Sign up button
document.getElementById("signupBtn")?.addEventListener("click", () => {
  alert("📝 Sign up page coming soon! This is a UI demo.");
});

// Contact form
document.getElementById("contactForm").addEventListener("submit", (e) => {
  e.preventDefault();
  alert("✅ Message sent successfully! (Demo)");
  e.target.reset();
});

// Navbar scroll effect
window.addEventListener("scroll", () => {
  const navbar = document.querySelector(".navbar");
  const backToTop = document.getElementById("backToTop");

  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }

  if (window.scrollY > 500) {
    backToTop.classList.add("visible");
  } else {
    backToTop.classList.remove("visible");
  }
});

// Back to top
document.getElementById("backToTop").addEventListener("click", () => {
  window.scrollTo({ top: 0, behavior: "smooth" });
});

// Active nav link on scroll
const sections = document.querySelectorAll("section[id]");
window.addEventListener("scroll", () => {
  const scrollY = window.scrollY + 100;
  sections.forEach((section) => {
    const top = section.offsetTop;
    const height = section.offsetHeight;
    const id = section.getAttribute("id");
    const link = document.querySelector(`.nav-links a[href="#${id}"]`);
    if (link) {
      if (scrollY >= top && scrollY < top + height) {
        link.classList.add("active");
      } else {
        link.classList.remove("active");
      }
    }
  });
});

// ========== INIT ==========
document.addEventListener("DOMContentLoaded", () => {
  renderCategories();
  renderProviders();
  renderTestimonials();
});


document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("contactForm");

  form.addEventListener("submit", async function(e) {
    e.preventDefault();

    const name = document.getElementById("contactName").value;
    const service = document.getElementById("contactService").value;

    console.log(name, service); // debug

    const res = await fetch("http://localhost:3000/submit", {
      method: "POST",
      headers: {
        "Content-Type": "application/json"
      },
      body: JSON.stringify({ name, service })
    });

    const data = await res.json();
    alert(data.message);
  });
});