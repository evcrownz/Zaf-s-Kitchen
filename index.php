<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Zaf's Kitchen - Premium Catering Services</title>
    <link rel="icon" type="image/png" href="logo/logo.png">
  <script src="https://cdn.tailwindcss.com"></script>
    <style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap');
* {
  font-family: 'Poppins', sans-serif;
}

.floating-element {
  animation: float 6s ease-in-out infinite;
}
.floating-element:nth-child(2) {
  animation-delay: -2s;
}
.floating-element:nth-child(3) {
  animation-delay: -4s;
}

@keyframes float {
  0%, 100% { transform: translateY(0px) rotate(0deg); }
  50% { transform: translateY(-20px) rotate(5deg); }
}

.gradient-text {
  background: linear-gradient(135deg, #E75925, #B8441F);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.circular-clip {
  clip-path: circle(80% at 75% 50%);
}

.pulse-glow {
  animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
  0%, 100% { box-shadow: 0 0 20px rgba(231, 89, 37, 0.5); }
  50% { box-shadow: 0 0 30px rgba(231, 89, 37, 0.8); }
}

/* Scroll animations */
.scroll-animate {
  opacity: 0;
  transform: translateY(60px);
  transition: all 0.9s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.scroll-animate.animate-in {
  opacity: 1;
  transform: translateY(0);
}

.scroll-animate-left {
  opacity: 0;
  transform: translateX(-60px);
  transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.scroll-animate-left.animate-in {
  opacity: 1;
  transform: translateX(0);
}

.scroll-animate-right {
  opacity: 0;
  transform: translateX(60px);
  transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.scroll-animate-right.animate-in {
  opacity: 1;
  transform: translateX(0);
}

.scroll-animate-scale {
  opacity: 0;
  transform: scale(0.8);
  transition: all 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.scroll-animate-scale.animate-in {
  opacity: 1;
  transform: scale(1);
}

.card-hover {
  transition: all 0.3s ease;
}

.card-hover:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(231, 89, 37, 0.3);
}

.image-overlay {
  position: relative;
  overflow: hidden;
  border-radius: 15px;
}

.image-overlay img {
  transition: transform 0.3s ease;
}

.image-overlay:hover img {
  transform: scale(1.05);
}

.image-overlay::after {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: linear-gradient(45deg, rgba(231, 89, 37, 0.1), rgba(184, 68, 31, 0.1));
  opacity: 0;
  transition: opacity 0.3s ease;
}

.image-overlay:hover::after {
  opacity: 1;
}

.bg-gray-800-custom {
  background-color: #d725e70a !important;
}

.bg-gray-900-custom {
  background-color: #e7592517 !important;
}

.service-card {
  background: transparent;
  border: 2px solid rgba(231, 89, 37, 0.2);
  backdrop-filter: blur(10px);
  transition: all 0.3s ease;
}

.service-card:hover {
  border-color: rgba(231, 89, 37, 0.5);
  background: rgba(231, 89, 37, 0.1);
}

.image-overlay {
  position: relative;
  overflow: hidden;
}

.image-overlay::before {
  content: "";
  position: absolute;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 1;
  border-radius: 1rem;
  transition: opacity 0.3s ease;
  opacity: 1;
}

.image-overlay:hover::before {
  opacity: 0;
}

.image-overlay img {
  position: relative;
  z-index: 0;
}

.image-overlay .absolute {
  z-index: 2;
}

/* Custom theme colors */
.bg-theme-primary {
  background-color: #E75925;
}

.text-theme-primary {
  color: #E75925;
}

.border-theme-primary {
  border-color: #E75925;
}

.hover\:bg-theme-primary:hover {
  background-color: #B8441F;
}

.focus\:ring-theme-primary:focus {
  --tw-ring-color: #E75925;
}

/* Mobile Responsive CSS - COMPLETE FIXED VERSION */
@media (max-width: 768px) {
  body {
    overflow-x: hidden;
    overflow-y: auto;
  }

  .flex.min-h-screen {
    flex-direction: column;
    min-height: 100vh;
  }

  /* Left Content - Mobile - FIXED ZAF'S KITCHEN VISIBILITY */
  .w-\[38\%\] {
    width: 100% !important;
    padding: 20px 24px !important;
    order: 1;
    position: relative !important;
    background: linear-gradient(135deg, rgba(0,0,0,0.95) 0%, rgba(20,20,20,0.9) 100%);
    border-bottom: 3px solid rgba(231, 89, 37, 0.3);
    overflow: hidden !important;
    min-height: 580px !important;
    border-bottom-left-radius: 50% 100px !important;
    border-bottom-right-radius: 50% 100px !important;
    margin-bottom: 40px !important;
    padding-bottom: 60px !important;
  }

    .w-\[38\%\]::after {
    content: '';
    position: absolute;
    bottom: -10px;
    left: 0;
    right: 0;
    height: 10px;
    background: linear-gradient(to bottom, rgba(231, 89, 37, 0.2), transparent);
    border-radius: 50%;
    z-index: 1;
  }

  /* Mobile background image - REDUCED OPACITY */
  .w-\[38\%\]::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    width: 100% !important;
    height: 100% !important;
    background-image: url('indexbackground/indexbground.jpg');
    background-size: cover;
    background-position: center;
    border-radius: 0 !important;
    opacity: 0.15 !important;
    z-index: 1;
    border: none;
    box-shadow: none;
  }

  .w-\[38\%\] .relative.z-10.max-w-\[500px\] {
    margin-top: 120px !important;
    max-width: 100% !important;
    text-align: center !important;
    position: relative !important;
    z-index: 3 !important;
  }

  /* Main heading - Mobile - FIXED VISIBILITY */
  .text-6xl {
    font-size: 2.5rem !important;
    line-height: 1.1 !important;
    margin-bottom: 16px !important;
    white-space: normal !important;
    text-shadow: 4px 4px 8px rgba(0,0,0,0.9) !important;
    position: relative !important;
    z-index: 4 !important;
    color: white !important;
    font-weight: 700 !important;
  }

  /* Description text - Mobile - MOVED CLOSER */
  .text-lg.text-gray-300 {
    font-size: 15px !important;
    width: 100% !important;
    max-width: 100% !important;
    margin-bottom: 24px !important;
    text-align: center !important;
    line-height: 1.5 !important;
    text-shadow: 2px 2px 6px rgba(0,0,0,0.9) !important;
    position: relative !important;
    z-index: 4 !important;
    color: #e5e7eb !important;
  }

  /* Button container - Mobile */
  .flex.justify-start {
    justify-content: center !important;
    margin-left: 0 !important;
    margin-bottom: 20px !important;
  }

  /* Enhanced Button - Mobile */
  .group.bg-gradient-to-r {
    width: 200px !important;
    height: 54px !important;
    padding: 0 !important;
    font-size: 15px !important;
    font-weight: 700 !important;
    border-width: 3px !important;
    border-color: rgba(255,255,255,0.9) !important;
    border-radius: 30px !important;
    background: linear-gradient(135deg, #E75925 0%, #B8441F 100%) !important;
    box-shadow: 0 8px 25px rgba(231, 89, 37, 0.5), 0 0 0 1px rgba(255,255,255,0.2) !important;
    transform: translateY(0) !important;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    position: relative !important;
    overflow: hidden !important;
    z-index: 4 !important;
  }

  .group.bg-gradient-to-r:hover {
    transform: translateY(-2px) scale(1.05) !important;
    box-shadow: 0 12px 35px rgba(231, 89, 37, 0.6), 0 0 0 1px rgba(255,255,255,0.3) !important;
  }

  .group.bg-gradient-to-r:active {
    transform: translateY(0) scale(1.02) !important;
  }

  /* Button text centering */
  .group.bg-gradient-to-r span {
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 100% !important;
    height: 100% !important;
    letter-spacing: 0.5px !important;
  }

  /* Button shimmer effect */
  .group.bg-gradient-to-r::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
  }

  .group.bg-gradient-to-r:hover::before {
    left: 100%;
  }

  /* Right Image Section - Mobile - REDUCED HEIGHT */
  .w-2\/3 {
    width: 100% !important;
    height: 280px !important;
    order: 2;
    position: relative !important;
    display: flex !important;
    justify-content: center !important;
    align-items: center !important;
    padding: 16px !important;
    background: transparent !important;
  }

  /* Hide the original circular clip image */
  .circular-clip {
    display: none !important;
  }

  /* Overlay text - MOVED MUCH CLOSER TO BUTTON */
    .absolute.bottom-16.right-16 {
    position: relative !important;
    bottom: auto !important;
    right: auto !important;
    left: auto !important;
    padding: 16px !important;
    text-align: center !important;
    background: transparent !important;
    backdrop-filter: none !important;
    border-radius: 20px !important;
    border: none !important;
    z-index: 2 !important;
    box-shadow: none !important;
    max-width: 380px !important;
    margin: -60px auto 0 !important;
  }
  

  .text-white.max-w-md.text-right {
    max-width: 100% !important;
    text-align: center !important;
  }

  /* Overlay heading - Mobile */
  .text-3xl.font-bold.mb-4.gradient-text {
    font-size: 22px !important;
    margin-bottom: 12px !important;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.8) !important;
  }

  /* Overlay description - Mobile */
  .text-gray-200.opacity-90.text-lg {
    font-size: 14px !important;
    line-height: 1.5 !important;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8) !important;
  }

  /* SERVICES SECTION - REDUCED SPACING */
  section.py-24 {
    padding-top: 48px !important;
    padding-bottom: 48px !important;
  }

  section.py-24.bg-gradient-to-b.from-black.to-gray-900-custom {
    padding-top: 32px !important;
    padding-bottom: 32px !important;
  }

  .text-4xl.lg\:text-6xl {
    font-size: 1.8rem !important;
    margin-bottom: 12px !important;
  }

  .text-center.mb-16 {
    margin-bottom: 24px !important;
  }

  .text-lg.lg\:text-xl {
    font-size: 13px !important;
    margin-bottom: 16px !important;
  }

  /* Services grid - COMPACT LAYOUT */
  .services-grid,
  .grid.grid-cols-1.md\:grid-cols-2.lg\:grid-cols-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 8px !important;
  }

  /* Services cards mobile - COMPACT */
  .service-card {
    padding: 12px !important;
    margin-bottom: 4px !important;
    border-radius: 12px !important;
  }

  .service-card h3 {
    font-size: 14px !important;
    margin-bottom: 6px !important;
    line-height: 1.2 !important;
  }

  .service-card p {
    font-size: 11px !important;
    line-height: 1.3 !important;
  }

  .service-card .w-16.h-16 {
    width: 32px !important;
    height: 32px !important;
    margin-bottom: 8px !important;
  }

  .service-card .w-16.h-16 svg {
    width: 16px !important;
    height: 16px !important;
  }

  /* ALL OTHER SECTIONS - REDUCED SPACING */
  .py-24.bg-gray-900-custom,
  .py-24.bg-gradient-to-b {
    padding-top: 32px !important;
    padding-bottom: 32px !important;
  }

  footer.py-12 {
    padding-top: 24px !important;
    padding-bottom: 24px !important;
  }

  section#contact.py-24 {
    padding-top: 32px !important;
    padding-bottom: 32px !important;
  }

  section#menu.py-24 {
    padding-top: 32px !important;
    padding-bottom: 32px !important;
  }

  .flex.min-h-screen {
    padding-bottom: 0 !important;
    min-height: auto !important;
  }

  .floating-element {
    animation: none !important;
  }

  /* Mobile responsive for other grids */
  .grid-cols-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 10px !important;
  }

  .grid-cols-2 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 10px !important;
  }

  .lg\:grid-cols-2 {
    grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
  }

  .lg\:grid-cols-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 10px !important;
  }

  .lg\:text-left {
    text-align: center !important;
  }

  .lg\:text-6xl {
    font-size: 2.2rem !important;
  }

  .lg\:text-xl {
    font-size: 0.9rem !important;
  }

  /* Gallery images mobile optimization */
  .image-overlay {
    border-radius: 12px !important;
  }

  .image-overlay img {
    height: 160px !important;
    object-fit: cover !important;
  }

  .image-overlay .absolute.bottom-4.left-4 {
    bottom: 6px !important;
    left: 6px !important;
    right: 6px !important;
    display: flex !important;
    flex-direction: column !important;
    align-items: flex-start !important;
  }

  .image-overlay h3 {
    font-size: 13px !important;
    margin-bottom: 0px !important;
    line-height: 1.1 !important;
  }

  .image-overlay p {
    font-size: 10px !important;
    margin-top: 2px !important;
    line-height: 1.1 !important;
  }

  /* Contact section mobile */
  .contact-grid {
    grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    gap: 12px !important;
  }

  .contact-item {
    flex-direction: row !important;
    text-align: left !important;
    justify-content: flex-start !important;
  }

  .contact-item .contact-icon {
    width: 36px !important;
    height: 36px !important;
    margin-right: 12px !important;
    margin-bottom: 0 !important;
  }

  .contact-item .contact-icon svg {
    width: 18px !important;
    height: 18px !important;
  }

  .contact-item h3 {
    font-size: 15px !important;
    margin-bottom: 3px !important;
  }

  .contact-item p {
    font-size: 13px !important;
  }

  /* Menu dishes - compact layout */
  .space-y-10 {
    gap: 16px !important;
  }

  .flex.items-center.space-x-6 {
    flex-direction: column !important;
    space-x: 0 !important;
    text-align: center !important;
    margin-bottom: 16px !important;
  }

  .flex.items-center.space-x-6 img {
    width: 120px !important;
    height: 80px !important;
    margin-bottom: 8px !important;
  }

  .flex.items-center.space-x-6 div {
    text-align: center !important;
  }

  .flex.items-center.space-x-6 h3 {
    font-size: 18px !important;
    margin-bottom: 6px !important;
  }

  .flex.items-center.space-x-6 p {
    font-size: 13px !important;
    line-height: 1.4 !important;
  }

  /* Better text visibility - Add background overlay */
  .w-\[38\%\] .relative.z-10.max-w-\[500px\]::before {
    content: '';
    position: absolute;
    top: -20px;
    left: -20px;
    right: -20px;
    bottom: -20px;
    background: radial-gradient(circle, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 100%);
    z-index: -1;
    border-radius: 20px;
  }
}

/* Small mobile screens - 480px and below */
@media (max-width: 480px) {
  .w-\[38\%\] {
    padding: 16px 20px !important;
    min-height: 450px !important;
  }

  .text-6xl {
    font-size: 2.2rem !important;
    margin-bottom: 12px !important;
  }

  .text-lg.text-gray-300 {
    font-size: 14px !important;
    margin-bottom: 20px !important;
  }

  .group.bg-gradient-to-r {
    width: 180px !important;
    height: 50px !important;
    font-size: 14px !important;
  }

  .w-2\/3 {
    height: 240px !important;
  }

  .absolute.bottom-16.right-16 {
    padding: 16px !important;
    margin: -80px auto 0 !important;
  }

  .text-3xl.font-bold.mb-4.gradient-text {
    font-size: 18px !important;
    margin-bottom: 8px !important;
  }

  .text-gray-200.opacity-90.text-lg {
    font-size: 12px !important;
  }

  /* ALL SECTIONS - MINIMAL SPACING */
  section.py-24,
  .py-24 {
    padding-top: 24px !important;
    padding-bottom: 24px !important;
  }

  .services-grid {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 6px !important;
  }

  .service-card {
    padding: 10px !important;
  }

  .service-card h3 {
    font-size: 12px !important;
    margin-bottom: 4px !important;
  }

  .service-card p {
    font-size: 10px !important;
    line-height: 1.2 !important;
  }

  .service-card .w-16.h-16 {
    width: 28px !important;
    height: 28px !important;
    margin-bottom: 6px !important;
  }

  .service-card .w-16.h-16 svg {
    width: 14px !important;
    height: 14px !important;
  }

  .text-center.mb-16 {
    margin-bottom: 16px !important;
  }

  .lg\:grid-cols-3 {
    grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    gap: 6px !important;
  }

  .image-overlay img {
    height: 120px !important;
  }

  .image-overlay h3 {
    font-size: 11px !important;
  }

  .image-overlay p {
    font-size: 9px !important;
  }

  .grid.grid-cols-1.lg\:grid-cols-2 {
    grid-template-columns: 1fr !important;
    gap: 16px !important;
  }

  .flex.items-center.space-x-6 img {
    width: 100px !important;
    height: 70px !important;
  }
}

/* Ensure gradient text is visible on mobile */
@media (max-width: 768px) {
  .gradient-text {
    background: linear-gradient(135deg, #E75925, #B8441F) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
    text-shadow: none !important;
  }
}
  </style>
</head>
<body class="bg-black text-white overflow-x-hidden">

  <!-- Floating Navigation Bar -->
  <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 bg-black/80 backdrop-blur-md border-b border-gray-800/50 transition-transform duration-300 transform">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <!-- Logo -->
        <div class="flex items-center">
          <img src="logo/logo.png" alt="Zaf's Kitchen Logo" class="h-12 w-12 object-contain">
          <span class="ml-3 text-xl font-bold gradient-text">ZAF'S KITCHEN</span>
        </div>

        <!-- Menu Items (Desktop Only) -->
        <div class="hidden md:flex items-center space-x-8">
          <a href="#menu" class="nav-link text-gray-300 hover:text-white transition-colors duration-300 font-medium">Menu</a>
          <a href="#contact" class="nav-link text-gray-300 hover:text-white transition-colors duration-300 font-medium">Contact</a>
          <a href="#about" class="nav-link text-gray-300 hover:text-white transition-colors duration-300 font-medium">About Us</a>
        </div>

        <!-- User Profile (Desktop Only) -->
   <div class="hidden md:flex items-center space-x-4">
  <div class="flex items-center space-x-3">
    <img src="https://cdn-icons-png.flaticon.com/512/6457/6457285.png" alt="User" class="h-10 w-10 rounded-full border-2 border-gray-700">
    <span class="text-gray-300 font-medium">User</span>
  </div>
</div>


        <!-- Mobile Menu Button -->
        <div class="md:hidden">
          <button id="mobile-menu-button" class="text-gray-300 hover:text-white focus:outline-none">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden bg-black/90 backdrop-blur-md border-t border-gray-800/50">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <a href="#menu" class="nav-link block px-3 py-2 text-gray-300 hover:text-white transition-colors duration-300">Menu</a>
        <a href="#contact" class="nav-link block px-3 py-2 text-gray-300 hover:text-white transition-colors duration-300">Contact</a>
        <a href="#about" class="nav-link block px-3 py-2 text-gray-300 hover:text-white transition-colors duration-300">About Us</a>
        
        <!-- User Profile (Mobile) -->
        <div class="border-t border-gray-800/50 pt-3 mt-3">
          <div class="flex items-center space-x-3 px-3 py-2">
          <img src="https://cdn-icons-png.flaticon.com/512/6457/6457285.png" alt="User" class="h-10 w-10 rounded-full border-2 border-gray-700">
            <span class="text-gray-300 font-medium">User</span>
          </div>
        </div>
      </div>
    </div>
  </nav>

  <script>
    let lastScrollY = window.scrollY;
    let isNavbarHidden = false;

    // Mobile menu toggle
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
      const mobileMenu = document.getElementById('mobile-menu');
      mobileMenu.classList.toggle('hidden');
    });

    // Smooth scrolling for navigation links
    document.querySelectorAll('.nav-link').forEach(link => {
      link.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);
        
        if (targetElement) {
          const offsetTop = targetElement.offsetTop - 100; // 100px offset from top
          
          window.scrollTo({
            top: offsetTop,
            behavior: 'smooth'
          });
          
          // Close mobile menu if open
          document.getElementById('mobile-menu').classList.add('hidden');
        }
      });
    });

    // Navbar scroll hide/show effect
    window.addEventListener('scroll', function() {
      const navbar = document.getElementById('navbar');
      const currentScrollY = window.scrollY;
      
      if (currentScrollY > lastScrollY && currentScrollY > 100) {
        // Scrolling down & past 100px - hide navbar
        if (!isNavbarHidden) {
          navbar.style.transform = 'translateY(-100%)';
          isNavbarHidden = true;
        }
      } else {
        // Scrolling up - show navbar
        if (isNavbarHidden) {
          navbar.style.transform = 'translateY(0)';
          isNavbarHidden = false;
        }
      }
      
      lastScrollY = currentScrollY;
      
      // Background opacity effect
      if (currentScrollY > 50) {
        navbar.classList.add('bg-black/90');
        navbar.classList.remove('bg-black/80');
      } else {
        navbar.classList.add('bg-black/80');
        navbar.classList.remove('bg-black/90');
      }
    });
  </script>

  <style>
    .gradient-text {
      background: linear-gradient(135deg, #E75925, #B8441F);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }
  </style>

  <!-- Hero Section -->
  <div class="flex min-h-screen relative">
    <!-- Left Content -->
    <div class="w-[38%] relative px-6 lg:px-16 py-20 lg:py-24 z-10">
      <div class="relative z-10 max-w-[500px] mt-20">
        <h1 class="text-6xl lg:text-8xl font-bold mb-3 leading-tight whitespace-nowrap" style="font-weight: 520;">
          ZAF'S<hr> <span class="gradient-text">KITCHEN</span>
        </h1>

        <div class="mb-3">
          <p class="text-lg lg:text-xl text-gray-300 mb-6 leading-relaxed max-w-[460px]" style="width: 410px;">
            Experience exceptional culinary artistry with our premium catering services. 
            From intimate gatherings to grand celebrations.
          </p>

          <!-- BOOK NOW Button -->
           <a href="auth.php">
          <div class="flex justify-start ml-2">
            <button class="group bg-gradient-to-r from-[#E75925] to-[#B8441F] hover:from-[#B8441F] hover:to-[#8B3318] text-white font-bold py-3 px-14 rounded-full transform transition-all duration-300 hover:scale-105 hover:shadow-2xl pulse-glow border-4 border-white"
              style="width: 220px; height: 55px; border: 5px solid white;">
              <span class="flex items-center space-x-2">
                <span>BOOK NOW!</span>
              </span>
            </button>
          </div>
  </a>
        </div>
      </div>
    </div>
    
    <!-- Right Image -->
    <div class="w-2/3 relative">
      <div class="absolute inset-0 circular-clip">
        <img 
          src="right_image/indexbground.jpg" 
          alt="Zaf's Kitchen Catering" 
          class="w-full h-full object-cover"/>
        <div class="absolute inset-0 bg-gradient-to-l from-transparent to-black opacity-50"></div>
      </div>

      <!-- Overlay Text on Right -->
      <div class="absolute bottom-16 right-16 z-10">
        <div class="text-white max-w-md text-right">
          <h3 class="text-3xl font-bold mb-4 gradient-text" style="font-size: 26px;">Premium Catering Experience</h3>
          <p class="text-gray-200 opacity-90 text-lg leading-relaxed" style="font-size: 15px;">
            From farm-fresh ingredients to expertly crafted presentations, every dish tells a story of culinary excellence and passion.
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Services Section -->
<section class="py-24 bg-gradient-to-b from-black to-gray-900-custom">
  <div class="container mx-auto px-6 lg:px-16">
    <div class="text-center mb-16 scroll-animate">
      <h2 class="text-4xl lg:text-6xl font-bold mb-6 gradient-text">Our Services</h2>
      <p class="text-gray-300 text-lg lg:text-xl max-w-3xl mx-auto">
        We specialize in creating unforgettable culinary experiences for every occasion
      </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 services-grid">
      <!-- Wedding Catering -->
      <div class="service-card rounded-2xl p-8 card-hover scroll-animate-left">
        <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <h3 class="text-2xl font-bold mb-4 text-white">Wedding Catering</h3>
        <p class="text-gray-300 leading-relaxed">
          Make your special day extraordinary with our elegant wedding catering services. From intimate ceremonies to grand receptions with customized menus.
        </p>
      </div>

      <!-- Corporate Events -->
      <div class="service-card rounded-2xl p-8 card-hover scroll-animate">
        <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
          </svg>
        </div>
        <h3 class="text-2xl font-bold mb-4 text-white">Corporate Events</h3>
        <p class="text-gray-300 leading-relaxed">
          Professional catering for corporate meetings, conferences, product launches, and business celebrations. Impress your clients and colleagues.
        </p>
      </div>

      <!-- Birthday Events -->
      <div class="service-card rounded-2xl p-8 card-hover scroll-animate-right">
        <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 0v1.5M12 21l3.5-3.5M12 21l-3.5-3.5M3 7l3.5 3.5L12 6l5.5 4.5L21 7"></path>
          </svg>
        </div>
        <h3 class="text-2xl font-bold mb-4 text-white">Birthday Celebrations</h3>
        <p class="text-gray-300 leading-relaxed">
          Celebrate life's special moments with our personalized birthday catering. From children's parties to milestone celebrations and themed events.
        </p>
      </div>

      <!-- Debut Celebrations -->
      <div class="service-card rounded-2xl p-8 card-hover scroll-animate-left">
        <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
          </svg>
        </div>
        <h3 class="text-2xl font-bold mb-4 text-white">Debut Celebrations</h3>
        <p class="text-gray-300 leading-relaxed">
          Mark this once-in-a-lifetime milestone with our elegant debut catering. Complete with formal dining options and Filipino traditional favorites.
        </p>
      </div>

      <!-- Anniversary Events -->
      <div class="service-card rounded-2xl p-8 card-hover scroll-animate">
        <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
          </svg>
        </div>
        <h3 class="text-2xl font-bold mb-4 text-white">Anniversary Events</h3>
        <p class="text-gray-300 leading-relaxed">
          Celebrate years of love and commitment with our romantic anniversary catering. Perfect for intimate dinners or larger family gatherings.
        </p>
      </div>

      <!-- Graduation Parties -->
      <div class="service-card rounded-2xl p-8 card-hover scroll-animate-right">
        <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center mb-6">
          <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
          </svg>
        </div>
        <h3 class="text-2xl font-bold mb-4 text-white">Graduation Parties</h3>
        <p class="text-gray-300 leading-relaxed">
          Honor academic achievements with our graduation catering services. From high school to university celebrations and professional milestones.
        </p>
      </div>
 
    </div>
  </div>
</section>

  <!-- Gallery Section -->
  <section class="py-24 bg-gray-900-custom">
    <div class="container mx-auto px-6 lg:px-16">
      <div class="text-center mb-16 scroll-animate">
        <h2 class="text-4xl lg:text-6xl font-bold mb-8 gradient-text" >Catering Highlights</h2>
        <p class="text-gray-300 text-lg lg:text-xl max-w-3xl mx-auto">
          A glimpse into our culinary masterpieces and memorable events
        </p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <div class="image-overlay scroll-animate-left">
          <img src="Catering_Photos/wedding.jpg" 
               alt="Elegant Wedding Setup" 
               class="w-full h-64 object-cover rounded-2xl"/>
          <div class="absolute bottom-4 left-4 text-white">
            <h3 class="text-lg font-bold">Elegant Wedding</h3>
            <p class="text-sm text-gray-300">60 guests • Manila</p>
          </div>
        </div>

        <div class="image-overlay scroll-animate">
          <img src="Catering_Photos/company.jpg" 
               alt="Corporate Lunch" 
               class="w-full h-64 object-cover rounded-2xl"/>
          <div class="absolute bottom-4 left-4 text-white">
            <h3 class="text-lg font-bold">Corporate Lunch</h3>
            <p class="text-sm text-gray-300">70 guests • BGC</p>
          </div>
        </div>

        <div class="image-overlay scroll-animate-right">
          <img src="Catering_Photos/birthday.jpg" 
               alt="Birthday Celebration" 
               class="w-full h-64 object-cover rounded-2xl"/>
          <div class="absolute bottom-4 left-4 text-white">
            <h3 class="text-lg font-bold">Birthday Party</h3>
            <p class="text-sm text-gray-300">45 guests • Makati</p>
          </div>
        </div>

        <div class="image-overlay scroll-animate-left">
          <img src="https://images.unsplash.com/photo-1551218808-94e220e084d2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" 
               alt="Fine Dining Setup" 
               class="w-full h-64 object-cover rounded-2xl"/>
          <div class="absolute bottom-4 left-4 text-white">
            <h3 class="text-lg font-bold">DEBUT</h3>
            <p class="text-sm text-gray-300">30 guests • Taguig</p>
          </div>
        </div>

        <div class="image-overlay scroll-animate">
          <img src="https://images.unsplash.com/photo-1544148103-0773bf10d330?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" 
               alt="Outdoor Catering" 
               class="w-full h-64 object-cover rounded-2xl"/>
          <div class="absolute bottom-4 left-4 text-white">
            <h3 class="text-lg font-bold">Garden Party</h3>
            <p class="text-sm text-gray-300">120 guests • Quezon City</p>
          </div>
        </div>

        <div class="image-overlay scroll-animate-right">
          <img src="https://images.unsplash.com/photo-1530103862676-de8c9debad1d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1000&q=80" 
               alt="Buffet Setup" 
               class="w-full h-64 object-cover rounded-2xl"/>
          <div class="absolute bottom-4 left-4 text-white">
            <h3 class="text-lg font-bold">Buffet Catering</h3>
            <p class="text-sm text-gray-300">200 guests • Pasig</p>
          </div>
        </div>
      
      </div>
    </div>
  </section>

<!-- Menu Highlights -->
<section id="menu" class="py-24 bg-gradient-to-b from-gray-900-custom to-black">
  <div class="container mx-auto px-6 lg:px-16">
    <div class="text-center mb-16 scroll-animate">
      <h2 class="text-4xl lg:text-6xl font-bold mb-6 gradient-text">Signature Dishes</h2>
      <p class="text-gray-300 text-lg lg:text-xl max-w-3xl mx-auto">
        Experience our chef's carefully curated menu featuring the finest ingredients and innovative flavors
      </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
      <!-- Left side: Dishes -->
      <div class="scroll-animate-left space-y-10">

        <!-- Dish 1 -->
        <div class="flex items-center space-x-6">
          <img src="Menu/Sweet Pineapple Pork.png"
               alt="Beef Wellington"
               class="w-40 h-28 rounded-md object-cover shadow-md border border-white" />
          <div>
            <h3 class="text-2xl font-bold text-white mb-2">Sweet Pineapple Pork</h3>
            <p class="text-gray-300">Pork in sweet pineapple glaze, wrapped in golden puff pastry with savory mushroom duxelles for a perfect sweet-savory blend.</p>
          </div>
        </div>

        <!-- Dish 2 -->
        <div class="flex items-center space-x-6">
          <img src="Menu/Baked Roast Chicken.png"
               alt="Truffle Risotto"
               class="w-40 h-28 rounded-md object-cover shadow-md border border-white" />
          <div>
            <h3 class="text-2xl font-bold text-white mb-2">Baked Roast Chicken</h3>
            <p class="text-gray-300"> Juicy herb-roasted chicken paired with creamy arborio rice, infused with truffle oil and parmesan.</p>
          </div>
        </div>

         <!-- Dish 3 -->
        <div class="flex items-center space-x-6">
          <img src="Menu/Beef in Creamy Mushroom Sauce.png"
               alt="Grilled Salmon"
               class="w-40 h-28 rounded-md object-cover shadow-md border border-white" />
          <div>
            <h3 class="text-2xl font-bold text-white mb-2">Beef in Creamy Mushroom Sauce</h3>
            <p class="text-gray-300">Tender beef in creamy mushroom sauce paired with fresh Atlantic salmon glazed with teriyaki, served with seasonal vegetables.</p>
          </div>
        </div>

        <!-- See All Menus Button -->
        <div class="text-center mt-8">
          <a href="#full-menu" class="inline-block bg-gradient-to-r from-[#E75925] to-[#B8441F] text-white font-semibold text-lg px-10 py-3 rounded-md shadow-md hover:opacity-90 transition duration-300">
            View All Menus
          </a>
        </div>
      </div>

      <!-- Right side: Image -->
      <div class="scroll-animate-right flex flex-col items-center relative">
        <!-- Large Image -->
        <div class="relative w-full">
          <img src="Catering_Photos/wedding.jpg"
               alt="Gourmet Dishes"
               class="w-full h-[340px] object-cover rounded-2xl shadow-lg" />
        </div>

       <!-- Description about Zaf's Kitchen -->
      <div class="text-center mt-9 text-gray-300 max-w-4xl mx-auto">
        <p class="text-gray-300 text-base lg:text-lg max-w-3xl mx-auto">
          Experience our chef's carefully curated menu featuring the finest ingredients and innovative flavors that will excite your taste buds and bring joy to your dining experience. Let us make your event truly special.
        </p>
      </div>

      </div>

    </div>
  </div>
</section>




  <!-- Contact Section -->
  <section id="contact" class="py-24" style="background: linear-gradient(to bottom, black, rgba(231, 89, 37, 0.3));">
    <div class="container mx-auto px-6 lg:px-16">
      <div class="text-center scroll-animate">
        <h2 class="text-4xl lg:text-6xl font-bold mb-6 gradient-text">Get In Touch</h2>
        <p class="text-gray-300 text-lg lg:text-xl mb-12 leading-relaxed max-w-3xl mx-auto">
          Ready to create an unforgettable culinary experience? Contact us today to discuss your event needs and let us bring your vision to life.
        </p>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto contact-grid">
          <div class="flex flex-col items-center space-y-4 contact-item">
            <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center contact-icon">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
              </svg>
            </div>
            <div class="text-center">
              <h3 class="text-white font-semibold text-xl mb-2">Phone</h3>
              <p class="text-gray-300 text-lg">+63 966 448 5669</p>
            </div>
          </div>

          <div class="flex flex-col items-center space-y-4 contact-item">
            <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center contact-icon">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
              </svg>
            </div>
            <div class="text-center">
              <h3 class="text-white font-semibold text-xl mb-2">Email</h3>
              <p class="text-gray-300 text-lg">zafs.kitchenph@gmail.com</p>
            </div>
          </div>

          <div class="flex flex-col items-center space-y-4 contact-item">
            <div class="w-16 h-16 bg-gradient-to-r from-[#E75925] to-[#B8441F] rounded-full flex items-center justify-center contact-icon">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <div class="text-center">
              <h3 class="text-white font-semibold text-xl mb-2">Location</h3>
              <p class="text-gray-300 text-lg">Quezon City, Metro Manila</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

<!-- Footer -->
<footer class="py-12 bg-[#171717] border-t border-[#B8441F]">
  <div class="container mx-auto px-6 lg:px-16">
    <div class="text-center scroll-animate">
      <h3 class="text-3xl font-bold mb-4 text-white">ZAF'S KITCHEN</h3>
      <p class="text-white/80 mb-6">Creating exceptional culinary experiences since 2020</p>
      <div class="flex justify-center space-x-6">
        <!-- Facebook Icon -->
        <a href="#" class="text-white/70 hover:text-white transition-colors" aria-label="Facebook">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M22.675 0h-21.35C.599 0 0 .6 0 1.337v21.326C0 23.4.599 24 1.325 24h11.494v-9.294H9.691v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.464.099 2.797.143v3.24l-1.918.001c-1.504 0-1.796.715-1.796 1.763v2.31h3.59l-.467 3.622h-3.123V24h6.116C23.4 24 24 23.4 24 22.663V1.337C24 .6 23.4 0 22.675 0z"/>
          </svg>
        </a>

        <!-- Instagram Icon -->
        <a href="#" class="text-white/70 hover:text-white transition-colors" aria-label="Instagram">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2.163c3.204 0 3.584.012 4.85.07 1.366.062 2.633.333 3.608 1.308.974.974 1.246 2.242 1.308 3.608.058 1.266.07 1.646.07 4.85s-.012 3.584-.07 4.85c-.062 1.366-.333 2.633-1.308 3.608-.974.974-2.242 1.246-3.608 1.308-1.266.058-1.646.07-4.85.07s-3.584-.012-4.85-.07c-1.366-.062-2.633-.333-3.608-1.308-.974-.974-1.246-2.242-1.308-3.608C2.175 15.797 2.163 15.417 2.163 12s.012-3.584.07-4.85c.062-1.366.333-2.633 1.308-3.608C4.515 2.566 5.783 2.294 7.15 2.233c1.266-.058 1.646-.07 4.85-.07zM12 0C8.741 0 8.333.012 7.052.07 5.773.127 4.602.36 3.653 1.31 2.703 2.26 2.47 3.43 2.413 4.709 2.355 5.99 2.343 6.398 2.343 9.657v4.686c0 3.259.012 3.667.07 4.948.057 1.279.29 2.449 1.24 3.399.949.95 2.12 1.183 3.399 1.24 1.281.058 1.689.07 4.948.07s3.667-.012 4.948-.07c1.279-.057 2.449-.29 3.399-1.24.95-.95 1.183-2.12 1.24-3.399.058-1.281.07-1.689.07-4.948V9.657c0-3.259-.012-3.667-.07-4.948-.057-1.279-.29-2.449-1.24-3.399C20.447.36 19.277.127 17.998.07 16.717.012 16.309 0 13.05 0h-1.1zM12 5.838A6.162 6.162 0 0 0 5.838 12 6.162 6.162 0 0 0 12 18.162 6.162 6.162 0 0 0 18.162 12 6.162 6.162 0 0 0 12 5.838zm0 10.162A4 4 0 1 1 12 8a4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 1-2.88 0 1.44 1.44 0 0 1 2.88 0z"/>
          </svg>
        </a>

        <!-- Twitter Icon -->
        <a href="#" class="text-white/70 hover:text-white transition-colors" aria-label="Twitter">
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
            <path d="M23.954 4.569c-.885.389-1.83.654-2.825.775 1.014-.611 1.794-1.574 2.163-2.723-.949.561-2.003.97-3.127 1.195-.896-.957-2.173-1.555-3.591-1.555-2.717 0-4.92 2.203-4.92 4.917 0 .39.045.765.127 1.124C7.728 8.094 4.1 6.128 1.671 3.149c-.427.722-.666 1.561-.666 2.475 0 1.708.87 3.213 2.188 4.096-.807-.026-1.566-.248-2.229-.616v.061c0 2.385 1.693 4.374 3.946 4.827-.413.111-.849.171-1.296.171-.317 0-.626-.03-.927-.086.631 1.953 2.445 3.377 4.6 3.418-1.68 1.318-3.809 2.105-6.102 2.105-.396 0-.79-.023-1.17-.069 2.179 1.394 4.768 2.209 7.557 2.209 9.054 0 14-7.496 14-13.986 0-.21-.006-.42-.015-.63.962-.689 1.8-1.56 2.46-2.548l-.047-.02z"/>
          </svg>
        </a>
      </div>
      <p class="text-white/60 text-sm mt-6">© 2024 Zaf's Kitchen. All rights reserved.</p>
    </div>
  </div>
</footer>


  <script>
    // Scroll animation observer
    const observerOptions = {
      threshold: 0.1,
      rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate-in');
        }
      });
    }, observerOptions);

    // Observe all scroll animate elements
    document.querySelectorAll('.scroll-animate, .scroll-animate-left, .scroll-animate-right, .scroll-animate-scale').forEach(el => {
      observer.observe(el);
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
        }
      });
    });


    // Add loading animation
    window.addEventListener('load', function() {
      document.body.classList.add('loaded');
    });

    // Lazy loading for images
    const imageObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const img = entry.target;
          img.classList.add('fade-in');
          observer.unobserve(img);
        }
      });
    });

    document.querySelectorAll('img').forEach(img => {
      imageObserver.observe(img);
    });
  </script>

</body>
</html>