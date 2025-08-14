<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Zaf's Kitchen</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script defer>
    function toggleModal() {
      document.getElementById('accountModal').classList.toggle('hidden');
    }
  </script>
</head>
<body class="bg-gray-50 text-gray-800">
  <!-- Header -->
  <header class="bg-white shadow-md sticky top-0 z-50">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
      <h1 class="text-2xl font-bold text-orange-600">Zaf's Kitchen</h1>
      <nav class="space-x-4">
        <a href="#gallery" class="hover:text-orange-500 font-medium">Gallery</a>
        <a href="#about" class="hover:text-orange-500 font-medium">About Us</a>
        <a href="#contact" class="hover:text-orange-500 font-medium">Contact</a>
        <button onclick="toggleModal()" class="bg-orange-500 text-white px-4 py-2 rounded">Account Settings</button>
      </nav>
    </div>
  </header>

  <!-- Hero Section -->
  <section class="text-center py-16 bg-gradient-to-r from-orange-100 to-white">
    <h2 class="text-4xl font-bold mb-4">Welcome to Zaf's Kitchen</h2>
    <p class="text-lg text-gray-600">Delicious food, beautifully presented.</p>
  </section>

  <!-- Gallery -->
  <section id="gallery" class="py-16 container mx-auto px-4">
    <h3 class="text-3xl font-semibold mb-8 text-center">Gallery</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
      <img src="img1.jpg" alt="Food 1" class="rounded-lg shadow">
      <img src="img2.jpg" alt="Food 2" class="rounded-lg shadow">
      <img src="img3.jpg" alt="Food 3" class="rounded-lg shadow">
      <img src="img4.jpg" alt="Food 4" class="rounded-lg shadow">
      <img src="img5.jpg" alt="Food 5" class="rounded-lg shadow">
      <img src="img6.jpg" alt="Food 6" class="rounded-lg shadow">
    </div>
  </section>

  <!-- Food Package -->
  <section class="py-16 bg-gray-100">
    <div class="container mx-auto px-4">
      <h3 class="text-3xl font-semibold mb-8 text-center">Food Packages</h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        <div class="bg-white p-4 rounded shadow text-center">
          <img src="food1.jpg" alt="Package 1" class="mb-4 w-full h-48 object-cover rounded">
          <h4 class="font-semibold">Sweet Pineapple Pork</h4>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <img src="food2.jpg" alt="Package 2" class="mb-4 w-full h-48 object-cover rounded">
          <h4 class="font-semibold">Baked Roast Chicken</h4>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <img src="food3.jpg" alt="Package 3" class="mb-4 w-full h-48 object-cover rounded">
          <h4 class="font-semibold">Beef in Mushroom Sauce</h4>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <img src="food4.jpg" alt="Package 4" class="mb-4 w-full h-48 object-cover rounded">
          <h4 class="font-semibold">Garlic Butter Shrimp</h4>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <img src="food5.jpg" alt="Package 5" class="mb-4 w-full h-48 object-cover rounded">
          <h4 class="font-semibold">Creamy Carbonara</h4>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <img src="food6.jpg" alt="Package 6" class="mb-4 w-full h-48 object-cover rounded">
          <h4 class="font-semibold">Seafood Paella</h4>
        </div>
        <div class="bg-white p-4 rounded shadow text-center">
          <img src="food7.jpg" alt="Package 7" class="mb-4 w-full h-48 object-cover rounded">
          <h4 class="font-semibold">Fruit Salad Dessert</h4>
        </div>
      </div>
    </div>
  </section>

  <!-- About Us -->
  <section id="about" class="py-16 container mx-auto px-4">
    <h3 class="text-3xl font-semibold mb-4 text-center">About Us</h3>
    <p class="text-lg text-center max-w-3xl mx-auto text-gray-600">
      Zaf's Kitchen is a proudly Filipino-owned catering business serving delicious and affordable food for all occasions. With a passion for taste and presentation, we turn meals into unforgettable moments.
    </p>
  </section>

  <!-- Contact -->
  <section id="contact" class="py-16 bg-gray-200">
    <div class="container mx-auto px-4">
      <h3 class="text-3xl font-semibold mb-6 text-center">Contact Us</h3>
      <p class="text-center text-lg">Email: zafskitchen@example.com | Phone: 0917-123-4567</p>
    </div>
  </section>

  <!-- Account Settings Modal --><?php
require_once 'session_check.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Zaf's Kitchen Dashboard</title>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" />

  <!-- Poppins Font -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <style>
    * {
      font-family: 'Poppins', sans-serif;
    }
    .hover-nav:hover {
      background-color: #E75925 !important;
      color: white !important;
    }
    .active-nav {
      background-color: #E75925 !important;
      color: white !important;
    }
    .calendar-day {
      transition: all 0.3s ease;
    }
    .calendar-day:hover {
      transform: scale(1.05);
    }
    .available-day {
      background-color: #dcfce7;
      border: 2px solid #16a34a;
      color: #15803d;
    }
    .unavailable-day {
      background-color: #fee2e2;
      border: 2px solid #dc2626;
      color: #dc2626;
    }
    .user-booked-day {
      background-color: #ddd6fe;
      border: 2px solid #7c3aed;
      color: #7c3aed;
    }
    .today {
      background-color: #fef3c7;
      border: 2px solid #f59e0b;
      color: #d97706;
      font-weight: bold;
    }
  </style>
</head>
<body class="bg-gray-100">

  <!-- Mobile Menu Button -->
  <button id="mobile-menu-btn" class="lg:hidden fixed top-4 left-4 z-30 text-white p-2 rounded-lg shadow-lg" style="background-color:#E75925;">
    <i class="fas fa-bars w-6 h-6"></i>
  </button>

  <!-- Backdrop -->
  <div id="backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden lg:hidden"></div>

  <!-- Sidebar -->
  <aside id="sidebar" class="fixed top-0 left-0 h-screen w-64 bg-gray-200 text-gray-800 flex flex-col justify-between rounded-r-xl z-20 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 ease-in-out"
    style="box-shadow: 6px 0 12px rgba(0, 0, 0, 0.2);">
    <!-- Top -->
    <div>
      <!-- Logo -->
      <div class="p-6 flex flex-col items-center border-b border-gray-300 shadow-md">
        <img src="logo/logo-border.png" alt="Logo" class="w-26 h-24 rounded-full object-cover mb-2">
        <h1 class="text-xl font-bold text-center">Zaf's Kitchen</h1>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-4 py-6 space-y-3">
        <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
          <i class="fas fa-calendar-plus text-[1.8rem]"></i>
          <span class="font-semibold">Book Now</span>
        </a>
        <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
          <i class="fas fa-utensils text-[1.8rem]"></i>
          <span class="font-semibold">Menu Packages</span>
        </a>
        <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
          <i class="fas fa-image text-[1.8rem]"></i>
          <span class="font-semibold">Gallery</span>
        </a>
        <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
          <i class="fas fa-calendar-check text-[1.8rem]"></i>
          <span class="font-semibold">Available Schedule</span>
        </a>
        <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
          <i class="fas fa-clock text-[1.8rem]"></i>
          <span class="font-semibold">My Schedule</span>
        </a>
        <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
          <i class="fas fa-user-cog text-[1.8rem]"></i>
          <span class="font-semibold">Profile Settings</span>
        </a>
        <a href="#" class="flex items-center gap-4 py-2 px-3 rounded hover-nav transition">
          <i class="fas fa-circle-info text-[1.8rem]"></i>
          <span class="font-semibold">About Us</span>
        </a>
      </nav>
    </div>

    <!-- Sign Out -->
    <div class="p-4 border-t border-gray-300">
      <button id="signout-btn" class="flex items-center justify-center gap-3 py-2 px-3 rounded text-white font-semibold transition w-full shadow-md hover:opacity-90" style="background-color:#dc2626;">
        <i class="fas fa-sign-out-alt text-[1.6rem]"></i> 
        <span>Sign Out</span>
      </button>
    </div>
  </aside>

        <!-- Main Content -->
        <main class="lg:ml-64 p-6 lg:p-10 pt-16 lg:pt-10 min-h-screen">
            <!-- Dashboard -->
            <section id="section-dashboard">
            <h2 class="text-3xl font-bold mb-2">Welcome to Zaf's Kitchen Dashboard</h2>
            <div class="w-full h-0.5 bg-gray-400 mb-6"></div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="text-center p-4" style="width: 380px;">
        <img 
            src="dashboard/calendar.png" 
            alt="Book Now" 
            class="w-[650px] h-[350px] object-cover rounded-[10px] mb-3 transform transition-transform duration-500 ease-in-out hover:scale-105 border border-gray-400"
            style="box-shadow: 8px 8px 15px rgba(0,0,0,0.3);">
        </div>
        
      </div>
    </section>

    <!-- Book Now -->
    <section id="section-book" class="hidden">
      <h2 class="text-2xl font-bold mb-2">Book Now</h2>
      <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
      
      <!-- Progress Steps -->
      <div class="bg-white p-4 rounded-lg shadow-lg border-2 border-gray-300 mb-6">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div id="step1-indicator" class="w-8 h-8 rounded-full flex items-center justify-center text-white font-semibold" style="background-color:#E75925;">1</div>
            <span class="ml-2 font-semibold">Basic Info</span>
          </div>
          <div class="flex-1 mx-4 h-0.5 bg-gray-300"></div>
          <div class="flex items-center">
            <div id="step2-indicator" class="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-gray-600 font-semibold">2</div>
            <span class="ml-2 font-semibold text-gray-600">Event Details</span>
          </div>
        </div>
      </div>

      <!-- Booking Form -->
      <form id="booking-form">
        <!-- Step 1: Basic Information -->
        <div id="booking-step1" class="bg-white p-6 rounded-lg shadow-lg border-2 border-gray-300">
          <h3 class="text-xl font-semibold mb-4">Basic Information</h3>
          <div class="space-y-4">
            <div>
              <label class="block font-semibold mb-1">Full Name *</label>
              <input id="fullname" name="full_name" type="text" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" placeholder="Enter your full name" required>
            </div>
            <div>
              <label class="block font-semibold mb-1">Contact Number *</label>
              <input id="contact" name="contact_number" type="tel" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" placeholder="e.g. +63 912 345 6789" required>
            </div>
            <div>
              <label class="block font-semibold mb-1">Food Package *</label>
              <select id="package" name="food_package" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                <option value="">Select a package</option>
                <option value="budget">Budget Package - ₱200/person</option>
                <option value="standard">Standard Package - ₱350/person</option>
                <option value="premium">Premium Package - ₱500/person</option>
                <option value="deluxe">Deluxe Package - ₱750/person</option>
                <option value="luxury">Luxury Package - ₱1000/person</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Type of Event *</label>
              <select id="eventtype" name="event_type" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
                <option value="">Select event type</option>
                <option value="birthday">Birthday Party</option>
                <option value="wedding">Wedding Reception</option>
                <option value="corporate">Corporate Event</option>
                <option value="graduation">Graduation Party</option>
                <option value="anniversary">Anniversary</option>
                <option value="debut">Debut/18th Birthday</option>
                <option value="baptismal">Baptismal</option>
                <option value="funeral">Funeral Service</option>
                <option value="others">Others</option>
              </select>
            </div>
            <div>
              <label class="block font-semibold mb-1">Event Date *</label>
              <input id="event-date" name="event_date" type="date" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block font-semibold mb-1">Start Time *</label>
                <input id="start-time" name="start_time" type="time" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
              </div>
              <div>
                <label class="block font-semibold mb-1">End Time *</label>
                <input id="end-time" name="end_time" type="time" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black" required>
              </div>
            </div>
            <button type="button" id="next-step1" class="text-white px-6 py-2 rounded shadow-md hover:opacity-90 transition-opacity" style="background-color:#E75925;">Next</button>
          </div>
        </div>

        <!-- Step 2: Event Details (Hidden initially) -->
        <div id="booking-step2" class="bg-white p-6 rounded-lg shadow-lg border-2 border-gray-300 hidden opacity-0 transform translate-x-full transition-all duration-500">
          <h3 class="text-xl font-semibold mb-4">Event Details & Customization</h3>
          <div class="space-y-6">
            
            <!-- Theme Selection -->
            <div>
              <label class="block font-semibold mb-2">Event Theme</label>
              <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mb-3">
                <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="elegant">
                  <i class="fas fa-crown text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Elegant</div>
                  <input type="radio" name="event_theme" value="elegant" class="hidden">
                </button>
                <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="rustic">
                  <i class="fas fa-leaf text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Rustic</div>
                  <input type="radio" name="event_theme" value="rustic" class="hidden">
                </button>
                <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="modern">
                  <i class="fas fa-star text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Modern</div>
                  <input type="radio" name="event_theme" value="modern" class="hidden">
                </button>
                <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="tropical">
                  <i class="fas fa-palette text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Tropical</div>
                  <input type="radio" name="event_theme" value="tropical" class="hidden">
                </button>
                <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="vintage">
                  <i class="fas fa-camera-retro text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Vintage</div>
                  <input type="radio" name="event_theme" value="vintage" class="hidden">
                </button>
                <button type="button" class="theme-btn p-3 border-2 border-gray-300 rounded-lg hover:border-[#E75925] focus:border-[#E75925] transition-colors" data-theme="custom">
                  <i class="fas fa-pencil-alt text-2xl mb-1" style="color:#E75925;"></i>
                  <div class="font-semibold text-sm">Custom</div>
                  <input type="radio" name="event_theme" value="custom" class="hidden">
                </button>
              </div>
              <input id="custom-theme" name="custom_theme" type="text" class="w-full border-2 border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#E75925] focus:border-[#E75925] text-black hidden" placeholder="Describe your custom theme...">
            </div>

            <!-- Menu Selection -->
            <div>
              <label class="block font-semibold mb-2">Menu Selection</label>
              <div class="border-2 border-gray-300 rounded-lg p-3">
                <div class="grid grid-cols-3 gap-4 text-sm">
                  <div>
                    <div class="font-medium text-[#E75925] mb-2">Main Dishes</div>
                    <div class="space-y-1">
                      <label class="flex items-center"><input type="checkbox" name="menu_main[]" value="lechon_kawali" class="mr-2 text-[#E75925]"> Lechon Kawali</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_main[]" value="chicken_adobo" class="mr-2 text-[#E75925]"> Chicken Adobo</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_main[]" value="beef_caldereta" class="mr-2 text-[#E75925]"> Beef Caldereta</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_main[]" value="sweet_sour_fish" class="mr-2 text-[#E75925]"> Sweet & Sour Fish</label>
                    </div>
                  </div>
                  
                  <div>
                    <div class="font-medium text-[#E75925] mb-2">Side Dishes</div>
                    <div class="space-y-1">
                      <label class="flex items-center"><input type="checkbox" name="menu_side[]" value="pancit_canton" class="mr-2 text-[#E75925]"> Pancit Canton</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_side[]" value="fried_rice" class="mr-2 text-[#E75925]"> Fried Rice</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_side[]" value="lumpiang_shanghai" class="mr-2 text-[#E75925]"> Lumpiang Shanghai</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_side[]" value="mixed_vegetables" class="mr-2 text-[#E75925]"> Mixed Vegetables</label>
                    </div>
                  </div>

                  <div>
                    <div class="font-medium text-[#E75925] mb-2">Desserts</div>
                    <div class="space-y-1">
                      <label class="flex items-center"><input type="checkbox" name="menu_dessert[]" value="leche_flan" class="mr-2 text-[#E75925]"> Leche Flan</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_dessert[]" value="halo_halo" class="mr-2 text-[#E75925]"> Halo-Halo</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_dessert[]" value="buko_pie" class="mr-2 text-[#E75925]"> Buko Pie</label>
                      <label class="flex items-center"><input type="checkbox" name="menu_dessert[]" value="ice_cream" class="mr-2 text-[#E75925]"> Ice Cream</label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="flex gap-4">
              <button type="button" id="back-step2" class="bg-gray-300 text-gray-700 px-6 py-2 rounded shadow-md hover:bg-gray-400 transition-colors">Back</button>
              <button type="submit" id="submit-booking" class="text-white px-6 py-2 rounded shadow-md hover:opacity-90 transition-opacity" style="background-color:#E75925;">Submit Booking</button>
            </div>
          </div>
        </div>
      </form>
    </section>

    <!-- Other sections (hidden) -->
    <section id="section-menu" class="hidden">
      <h2 class="text-2xl font-bold mb-2">Menu Packages</h2>
      <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
      <p>Display menu packages here...</p>
    </section>
    <section id="section-gallery" class="hidden">
      <h2 class="text-2xl font-bold mb-2">Gallery</h2>
      <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
      <p>Gallery content here...</p>
    </section>
    
    <!-- Available Schedule Section with Calendar -->
    <section id="section-calendar" class="hidden">
      <h2 class="text-2xl font-bold mb-2">Available Schedule</h2>
      <div class="w-full h-0.5 bg-gray-400 mb-6"></div>
      
      <!-- Calendar Container -->
      <div class="bg-white rounded-lg shadow-lg border-2 border-gray-300 p-6">
        <!-- Calendar Header -->
        <div class="flex items-center justify-between mb-6">
          <button id="prev-month" class="flex items-center px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            <i class="fas fa-chevron-left mr-2"></i>
            Previous
          </button>
          <h3 id="calendar-month-year" class="text-2xl font-bold text-gray-800"></h3>
          <button id="next-month" class="flex items-center px-4 py-2 rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            Next
            <i class="fas fa-chevron-right ml-2"></i>
          </button>
        </div>

        <!-- Days of Week Header -->
        <div class="grid grid-cols-7 gap-2 mb-4">
          <div class="text-center font-semibold text-gray-600 py-2">Sunday</div>
          <div class="text-center font-semibold text-gray-600 py-2">Monday</div>
          <div class="text-center font-semibold text-gray-600 py-2">Tuesday</div>
          <div class="text-center font-semibold text-gray-600 py-2">Wednesday</div>
          <div class="text-center font-semibold text-gray-600 py-2">Thursday</div>
          <div class="text-center font-semibold text-gray-600 py-2">Friday</div>
          <div class="text-center font-semibold text-gray-600 py-2">Saturday</div>
        </div>

        <!-- Calendar Grid -->
        <div id="calendar-grid" class="grid grid-cols-7 gap-2">
          <!-- Calendar days will be generated here -->
        </div>

        <!-- Legend -->
        <div class="flex items-center justify-center gap-6 mt-6 pt-4 border-t border-gray-200">
          <div class="flex items-center">
            <div class="w-4 h-4 rounded border-2 border-green-600 bg-green-100 mr-2"></div>
            <span class="text-sm font-medium">Available</span>
          </div>
          <div class="flex items-center">
            <div class="w-4 h-4 rounded border-2 border-red-600 bg-red-100 mr-2"></div>
            <span class="text-sm font-medium">Fully Booked</span>
          </div>
          <div class="flex items-center">
            <div class="w-4 h-4 rounded border-2 border-purple-600 bg-purple-100 mr-2"></div>
            <span class="text-sm font-medium">Your Booking</span>
          </div>
          <div class="flex items-center">
            <div class="w-4 h-4 rounded border-2 border-yellow-600 bg-yellow-100 mr-2"></div>
            <span class="text-sm font-medium">Today</span>
          </div>
        </div>
      </div>

      <!-- Selected Date Info -->
      <div id="selected-date-info" class="mt-6 bg-white rounded-lg shadow-lg border-2 border-gray-300 p-6 hidden">
        <h4 class="text-xl font-semibold mb-4">Schedule for <span id="selected-date-text"></span></h4>
        <div id="schedule-content">
          <!-- Schedule content will be displayed here -->
        </div>
      </div>
    </section>

    <section id="section-schedule" class="hidden">
      <h2 class="text-2xl font-bold mb-2">My Schedule</h2>
      <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
      <p>Schedule content here...</p>
    </section>
    <section id="section-settings" class="hidden">
      <h2 class="text-2xl font-bold mb-2">Profile Settings</h2>
      <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
      <p>Settings form here...</p>
    </section>
    <section id="section-about" class="hidden">
      <h2 class="text-2xl font-bold mb-2">About Us</h2>
      <div class="w-full h-0.5 bg-gray-400 mb-4"></div>
      <p>About Zaf's Kitchen...</p>
    </section>
  </main>

  <!-- Sign Out Modal -->
  <div id="signout-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white p-6 rounded-lg shadow-lg w-80 text-center">
      <h3 class="text-lg font-semibold mb-4">Are you sure you want to sign out?</h3>
      <div class="flex justify-center gap-4">
        <button id="cancel-signout" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 w-24">NO</button>
        <button id="confirm-signout" class="px-4 py-2 rounded text-white w-24" style="background-color:#E75925;">YES</button>
      </div>
    </div>
  </div>

  <!-- JavaScript -->
  <script>
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('backdrop');

    function toggleSidebar() {
      sidebar.classList.toggle('-translate-x-full');
      backdrop.classList.toggle('hidden');
    }
    mobileMenuBtn.addEventListener('click', toggleSidebar);
    backdrop.addEventListener('click', toggleSidebar);

    const navMap = {
      "Book Now": "section-book",
      "Menu Packages": "section-menu",
      "Gallery": "section-gallery",
      "Available Schedule": "section-calendar",
      "My Schedule": "section-schedule",
      "Profile Settings": "section-settings",
      "About Us": "section-about"
    };

    function hideAllSections() {
      document.querySelectorAll("main section").forEach(sec => sec.classList.add("hidden"));
    }

    const navLinks = document.querySelectorAll("nav a");
    navLinks.forEach(link => {
      link.addEventListener("click", e => {
        e.preventDefault();
        hideAllSections();
        navLinks.forEach(l => l.classList.remove("active-nav"));
        link.classList.add("active-nav");
        const text = link.innerText.trim();
        const sectionId = navMap[text];
        if (sectionId) {
          document.getElementById(sectionId).classList.remove("hidden");
          // Load calendar data when Available Schedule is clicked
          if (sectionId === 'section-calendar') {
            loadCalendarData();
          }
        }
        document.getElementById("section-dashboard").classList.add("hidden");
        if (window.innerWidth < 1024) toggleSidebar();
      });
    });

    hideAllSections();
    document.getElementById("section-dashboard").classList.remove("hidden");

    // Sign Out Modal Logic
    const signoutBtn = document.getElementById('signout-btn');
    const signoutModal = document.getElementById('signout-modal');
    const cancelSignout = document.getElementById('cancel-signout');
    const confirmSignout = document.getElementById('confirm-signout');

    signoutBtn.addEventListener('click', () => {
      signoutModal.classList.remove('hidden');
    });

    cancelSignout.addEventListener('click', () => {
      signoutModal.classList.add('hidden');
    });

    confirmSignout.addEventListener('click', () => {
      window.location.href = 'auth.php';
    });

    // Set minimum date to tomorrow
    const eventDateInput = document.getElementById('event-date');
    const tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    eventDateInput.min = tomorrow.toISOString().split('T')[0];

    // Booking Form Multi-Step Logic
    const step1 = document.getElementById('booking-step1');
    const step2 = document.getElementById('booking-step2');
    const nextBtn = document.getElementById('next-step1');
    const backBtn = document.getElementById('back-step2');
    const bookingForm = document.getElementById('booking-form');
    const step1Indicator = document.getElementById('step1-indicator');
    const step2Indicator = document.getElementById('step2-indicator');
    const customThemeInput = document.getElementById('custom-theme');

    // Form validation for step 1
    function validateStep1() {
      const fullname = document.getElementById('fullname').value.trim();
      const contact = document.getElementById('contact').value.trim();
      const package = document.getElementById('package').value;
      const eventtype = document.getElementById('eventtype').value;
      const eventDate = document.getElementById('event-date').value;
      const startTime = document.getElementById('start-time').value;
      const endTime = document.getElementById('end-time').value;
      
      if (!fullname || !contact || !package || !eventtype || !eventDate || !startTime || !endTime) {
        return false;
      }
      
      // Validate time range
      if (startTime >= endTime) {
        alert('End time must be after start time.');
        return false;
      }
      
      return true;
    }

    // Next button click - Step 1 to Step 2
    nextBtn.addEventListener('click', () => {
      if (validateStep1()) {
        // Hide step 1 with animation
        step1.style.transform = 'translateX(-100%)';
        step1.style.opacity = '0';
        
        setTimeout(() => {
          step1.classList.add('hidden');
          step2.classList.remove('hidden');
          
          // Show step 2 with animation
          setTimeout(() => {
            step2.style.transform = 'translateX(0)';
            step2.style.opacity = '1';
          }, 50);
          
          // Update progress indicators
          step1Indicator.style.backgroundColor = '#10B981'; // Green for completed
          step2Indicator.style.backgroundColor = '#E75925';
          step2Indicator.style.color = 'white';
          step2Indicator.nextElementSibling.style.color = '#000';
        }, 500);
      } else {
        alert('Please fill in all required fields.');
      }
    });

    // Back button click - Step 2 to Step 1
    backBtn.addEventListener('click', () => {
      // Hide step 2 with animation
      step2.style.transform = 'translateX(100%)';
      step2.style.opacity = '0';
      
      setTimeout(() => {
        step2.classList.add('hidden');
        step1.classList.remove('hidden');
        
        // Show step 1 with animation
        step1.style.transform = 'translateX(0)';
        step1.style.opacity = '1';
        
        // Update progress indicators
        step1Indicator.style.backgroundColor = '#E75925';
        step2Indicator.style.backgroundColor = '#d1d5db';
        step2Indicator.style.color = '#6b7280';
        step2Indicator.nextElementSibling.style.color = '#6b7280';
      }, 500);
    });

    // Theme selection logic
    const themeButtons = document.querySelectorAll('.theme-btn');
    let selectedTheme = '';
    
    themeButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        // Remove active state from all buttons
        themeButtons.forEach(b => {
          b.style.borderColor = '#d1d5db';
          b.style.backgroundColor = 'white';
          // Uncheck all theme radio buttons
          const radio = b.querySelector('input[type="radio"]');
          if (radio) radio.checked = false;
        });
        
        // Add active state to clicked button
        btn.style.borderColor = '#E75925';
        btn.style.backgroundColor = '#fef2f2';
        
        // Check the radio button for this theme
        const radio = btn.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
        
        selectedTheme = btn.dataset.theme;
        
        // Show/hide custom theme input
        if (selectedTheme === 'custom') {
          customThemeInput.classList.remove('hidden');
        } else {
          customThemeInput.classList.add('hidden');
        }
      });
    });

    // Submit booking form
    bookingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      // Get selected menu items
      const selectedMenus = [];
      document.querySelectorAll('input[type="checkbox"]:checked').forEach(checkbox => {
        selectedMenus.push(checkbox.value);
      });
      
      if (!selectedTheme) {
        alert('Please select a theme for your event.');
        return;
      }
      
      if (selectedMenus.length === 0) {
        alert('Please select at least one menu item.');
        return;
      }
      
      // Show loading state
      const submitBtn = document.getElementById('submit-booking');
      const originalText = submitBtn.textContent;
      submitBtn.textContent = 'Submitting...';
      submitBtn.disabled = true;
      
      try {
        // Prepare form data
        const formData = new FormData(bookingForm);
        
        // Submit to backend
        const response = await fetch('process_booking_debug.php', {
          method: 'POST',
          body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
          alert(result.message);
          
          // Reset form
          bookingForm.reset();
          customThemeInput.classList.add('hidden');
          
          // Reset to step 1
          step2.classList.add('hidden');
          step1.classList.remove('hidden');
          step1.style.transform = 'translateX(0)';
          step1.style.opacity = '1';
          step1Indicator.style.backgroundColor = '#E75925';
          step2Indicator.style.backgroundColor = '#d1d5db';
          step2Indicator.style.color = '#6b7280';
          step2Indicator.nextElementSibling.style.color = '#6b7280';
          
          // Reset theme selection
          themeButtons.forEach(btn => {
            btn.style.borderColor = '#d1d5db';
            btn.style.backgroundColor = 'white';
            const radio = btn.querySelector('input[type="radio"]');
            if (radio) radio.checked = false;
          });
          selectedTheme = '';
          
          // Refresh calendar if it's currently visible
          const calendarSection = document.getElementById('section-calendar');
          if (!calendarSection.classList.contains('hidden')) {
            loadCalendarData();
          }
        } else {
          alert(result.message);
        }
      } catch (error) {
        alert('An error occurred while submitting your booking. Please try again.');
        console.error('Error:', error);
      } finally {
        // Restore button state
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
      }
    });

    // Calendar Functionality
    let currentDate = new Date();
    let selectedDate = null;
    let scheduleData = {};

    function formatDate(date) {
      return date.getFullYear() + '-' + 
             String(date.getMonth() + 1).padStart(2, '0') + '-' + 
             String(date.getDate()).padStart(2, '0');
    }

    function formatDisplayDate(date) {
      const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      };
      return date.toLocaleDateString('en-US', options);
    }

    async function loadCalendarData() {
      try {
        const response = await fetch(`get_schedule.php?month=${currentDate.getMonth() + 1}&year=${currentDate.getFullYear()}`);
        const result = await response.json();
        
        if (result.success) {
          scheduleData = result.schedule_data;
          generateCalendar(currentDate.getFullYear(), currentDate.getMonth());
        } else {
          console.error('Failed to load calendar data:', result.message);
        }
      } catch (error) {
        console.error('Error loading calendar data:', error);
      }
    }

    function generateCalendar(year, month) {
      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const daysInMonth = lastDay.getDate();
      const startingDayOfWeek = firstDay.getDay();
      
      const calendarGrid = document.getElementById('calendar-grid');
      const monthYearDisplay = document.getElementById('calendar-month-year');
      
      // Update month/year display
      const monthNames = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
      ];
      monthYearDisplay.textContent = `${monthNames[month]} ${year}`;
      
      // Clear previous calendar
      calendarGrid.innerHTML = '';
      
      // Add empty cells for days before the first day of the month
      for (let i = 0; i < startingDayOfWeek; i++) {
        const emptyCell = document.createElement('div');
        emptyCell.className = 'h-16';
        calendarGrid.appendChild(emptyCell);
      }
      
      // Add days of the month
      for (let day = 1; day <= daysInMonth; day++) {
        const dayCell = document.createElement('div');
        const currentDateStr = formatDate(new Date(year, month, day));
        const today = formatDate(new Date());
        
        dayCell.className = 'h-16 border border-gray-200 rounded-lg flex flex-col items-center justify-center cursor-pointer calendar-day text-sm font-semibold';
        dayCell.textContent = day;
        dayCell.dataset.date = currentDateStr;
        
        // Determine day status
        const dayData = scheduleData[currentDateStr];
        if (currentDateStr === today) {
          dayCell.classList.add('today');
        } else if (dayData) {
          if (dayData.user_events.length > 0) {
            dayCell.classList.add('user-booked-day');
          } else if (dayData.status === 'unavailable') {
            dayCell.classList.add('unavailable-day');
          } else {
            dayCell.classList.add('available-day');
          }
        } else {
          dayCell.classList.add('available-day');
        }
        
        // Add click event
        dayCell.addEventListener('click', () => {
          // Remove previous selection
          document.querySelectorAll('.calendar-day').forEach(cell => {
            cell.classList.remove('ring-4', 'ring-blue-500');
          });
          
          // Add selection to clicked day
          dayCell.classList.add('ring-4', 'ring-blue-500');
          selectedDate = currentDateStr;
          showScheduleInfo(currentDateStr);
        });
        
        calendarGrid.appendChild(dayCell);
      }
    }

    function showScheduleInfo(dateStr) {
      const selectedDateInfo = document.getElementById('selected-date-info');
      const selectedDateText = document.getElementById('selected-date-text');
      const scheduleContent = document.getElementById('schedule-content');
      
      const date = new Date(dateStr);
      selectedDateText.textContent = formatDisplayDate(date);
      
      const dayData = scheduleData[dateStr];
      
      if (dayData && dayData.user_events.length > 0) {
        // Show user's bookings
        scheduleContent.innerHTML = `
          <div class="space-y-4">
            <h5 class="font-semibold text-purple-700 mb-3">Your Bookings:</h5>
            ${dayData.user_events.map(event => `
              <div class="border border-purple-200 bg-purple-50 p-4 rounded-lg">
                <div class="flex items-center justify-between mb-2">
                  <span class="font-semibold text-purple-700 capitalize">${event.status}</span>
                  <span class="text-sm text-gray-600">${event.time}</span>
                </div>
                <div class="space-y-1 text-sm">
                  <p><span class="font-medium">Event:</span> ${event.event}</p>
                  <p><span class="font-medium">Package:</span> ${event.package}</p>
                  <p><span class="font-medium">Client:</span> ${event.client}</p>
                </div>
              </div>
            `).join('')}
          </div>
        `;
      } else if (dayData && dayData.status === 'unavailable') {
        // Show fully booked status
        scheduleContent.innerHTML = `
          <div class="border border-red-200 bg-red-50 p-4 rounded-lg text-center">
            <div class="flex items-center justify-center mb-2">
              <i class="fas fa-times-circle text-red-600 text-2xl mr-2"></i>
              <span class="font-semibold text-red-700">FULLY BOOKED</span>
            </div>
            <p class="text-sm text-gray-600">This date has reached the maximum booking limit (3 bookings per day)</p>
          </div>
        `;
      } else {
        // Show available status
        scheduleContent.innerHTML = `
          <div class="border border-green-200 bg-green-50 p-4 rounded-lg text-center">
            <div class="flex items-center justify-center mb-2">
              <i class="fas fa-check-circle text-green-600 text-2xl mr-2"></i>
              <span class="font-semibold text-green-700">AVAILABLE</span>
            </div>
            <p class="text-sm text-gray-600 mb-3">This date is available for booking</p>
            <button onclick="goToBooking('${dateStr}')" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
              <i class="fas fa-calendar-plus mr-2"></i>
              Book This Date
            </button>
          </div>
        `;
      }
      
      selectedDateInfo.classList.remove('hidden');
    }

    function goToBooking(dateStr) {
      // Switch to booking section
      hideAllSections();
      navLinks.forEach(l => l.classList.remove("active-nav"));
      navLinks[0].classList.add("active-nav"); // Book Now is first nav item
      document.getElementById("section-book").classList.remove("hidden");
      
      // Pre-fill the date
      document.getElementById('event-date').value = dateStr;
    }

    // Calendar navigation
    document.getElementById('prev-month').addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() - 1);
      loadCalendarData();
      document.getElementById('selected-date-info').classList.add('hidden');
    });

    document.getElementById('next-month').addEventListener('click', () => {
      currentDate.setMonth(currentDate.getMonth() + 1);
      loadCalendarData();
      document.getElementById('selected-date-info').classList.add('hidden');
    });

    // Initialize calendar when page loads
    loadCalendarData();
  </script>
</body>
</html>
  <div id="accountModal" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center hidden">
    <div class="bg-white rounded-lg p-6 w-full max-w-sm relative">
      <button onclick="toggleModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">&times;</button>
      <h3 class="text-xl font-semibold mb-4">User Account Settings</h3>
      <form>
        <label class="block mb-2">Name</label>
        <input type="text" class="w-full border rounded p-2 mb-4">

        <label class="block mb-2">Email</label>
        <input type="email" class="w-full border rounded p-2 mb-4">

        <label class="block mb-2">Password</label>
        <input type="password" class="w-full border rounded p-2 mb-4">

        <button type="submit" class="bg-orange-500 text-white px-4 py-2 rounded">Save Changes</button>
      </form>
    </div>
  </div>
</body>
</html>
