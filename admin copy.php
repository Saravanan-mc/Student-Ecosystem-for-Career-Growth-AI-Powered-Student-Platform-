<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Variables for consistent theming. These define colors, sizes, and animation speeds. */
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --primary-light: rgba(67, 97, 238, 0.1);
            --secondary: #3f37c9;
            --dark: #1f2937;
            --darker: #111827;
            --light: #f9fafb; /* Light background for the content area */
            --lighter: #ffffff;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --sidebar-width: 280px; /* Default sidebar width */
            --sidebar-collapsed-width: 80px; /* Width when collapsed */
            --transition-speed: 0.4s cubic-bezier(0.16, 1, 0.3, 1); /* Smooth animation speed */
            --nav-item-delay: 0.05s; /* Delay for staggered link animations */
            --sidebar-green: #1a5632; /* The "normal green" for the sidebar */
            --sidebar-green-dark: #0d3b1e; /* Darker green for gradient */
        }

        /* Basic CSS Reset and Font Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* Body Layout: Uses flexbox to arrange sidebar and main content */
        body {
            display: flex;
            min-height: 100vh; /* Full viewport height */
            background-color: var(--light);
            transition: margin-left var(--transition-speed); /* Smooth transition for content shift */
            overflow-x: hidden; /* Prevent horizontal scrollbar */
            position: relative; /* Needed for absolute positioning of the mobile menu toggle */
        }

        /* Adjust body margin when sidebar is collapsed (desktop) */
        body.sidebar-collapsed {
            --sidebar-width: var(--sidebar-collapsed-width); /* Override sidebar width variable */
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(160deg, var(--sidebar-green-dark), var(--sidebar-green)); /* Green gradient background */
            color: white;
            height: 100vh;
            position: fixed; /* Fixed position on the left */
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            transform: translateX(-100%); /* Initially hidden off-screen for animation */
            transition: transform var(--transition-speed), width var(--transition-speed);
            z-index: 1000; /* Ensure sidebar is above content */
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.3); /* Subtle shadow */
            overflow: hidden; /* Hide overflowing content during collapse */
        }

        /* Animation for sidebar appearing on load */
        #sidebar.loaded {
            transform: translateX(0);
        }

        /* Sidebar Header: Contains logo and toggle button */
        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08); /* Separator line */
            background: rgba(0, 0, 0, 0.2); /* Semi-transparent overlay */
        }

        /* Logo: Icon and text */
        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .logo-icon {
            font-size: 1.8rem;
            color: white;
            background: rgba(255, 255, 255, 0.15);
            width: 42px;
            height: 42px;
            border-radius: 12px; /* Rounded corners */
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all var(--transition-speed);
            flex-shrink: 0;
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.25rem;
            color: white;
            transition: opacity var(--transition-speed); /* Fade out when collapsed */
        }

        /* Sidebar Toggle Button (desktop) */
        .toggle-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .toggle-btn:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: rotate(15deg);
        }

        /* Sidebar Navigation Area */
        .sidebar-nav {
            flex: 1; /* Takes remaining vertical space */
            overflow-y: auto; /* Enable scrolling for many links */
            padding: 1rem 0.5rem;
            scrollbar-width: thin; /* Firefox scrollbar styling */
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }

        /* Custom scrollbar for Webkit browsers */
        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 10px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.25);
        }

        /* Navigation Section Titles */
        .nav-section {
            margin-bottom: 1.5rem;
            overflow: hidden; /* Helps with text fading during collapse */
        }

        .nav-section-title {
            color: rgba(255, 255, 255, 0.5);
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 1rem;
            margin-bottom: 0.75rem;
            transition: opacity var(--transition-speed);
            white-space: nowrap;
        }

        /* Sidebar Links (common styles for all navigation items) */
        .sidebar-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.85);
            padding: 0.85rem 1rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
            gap: 12px;
            white-space: nowrap; /* Prevent text wrapping */
            position: relative;
            transform-origin: left center;
            opacity: 0; /* Hidden by default for slide-in animation */
            transform: translateX(-20px);
            animation: slideIn 0.4s var(--transition-speed) forwards; /* Slide in on load */
        }

        /* Slide-in animation for sidebar links */
        @keyframes slideIn {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .sidebar-link i {
            font-size: 1.1rem;
            min-width: 24px;
            text-align: center;
            transition: all 0.3s;
            position: relative;
            z-index: 1; /* Ensure icon is above pseudo-element */
        }

        .link-text {
            transition: opacity var(--transition-speed), transform var(--transition-speed);
            position: relative;
            z-index: 1;
        }

        /* Background hover effect for links */
        .sidebar-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: white;
            border-radius: 8px;
            z-index: 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .sidebar-link:hover {
            color: white; /* Text color on hover */
            transform: translateX(5px) scale(1.02); /* Slight movement and scale */
        }

        .sidebar-link:hover i {
            transform: scale(1.15); /* Icon subtle scale */
            color: white;
        }

        .sidebar-link:hover::before {
            opacity: 0.2; /* Show semi-transparent background */
        }

        /* Active link styling */
        .sidebar-link.active {
            background: white;
            color: var(--sidebar-green); /* Text color for active link */
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .sidebar-link.active i {
            color: var(--sidebar-green);
            transform: scale(1.1);
        }

        .sidebar-link.active::before {
            opacity: 1;
        }

        /* Active link left border with pulse animation */
        .sidebar-link.active::after {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: white;
            border-radius: 0 4px 4px 0;
            animation: pulse 2s infinite; /* Pulsing effect */
        }

        /* Pulse animation for the active link indicator */
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        /* Logout button specific styles */
        #logout {
            background: rgba(239, 68, 68, 0.1); /* Light red background */
            color: rgba(239, 68, 68, 0.9); /* Red text */
        }
        
        #logout:hover {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger); /* Darker red on hover */
        }
        
        #logout.active {
            background: var(--danger); /* Solid red when active */
            color: white;
        }

        /* Dropdown Menu (for News Sources) */
        .dropdown-parent {
            position: relative;
        }

        /* Arrow icon for dropdown toggle */
        .dropdown-toggle::after {
            content: '\f078'; /* Font Awesome chevron-down icon */
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.7rem;
           
            transition: transform 0.3s;
        }

        /* Rotate arrow when dropdown is active */
        .dropdown-parent.active .dropdown-toggle::after {
            transform: rotate(180deg);
        }

        .dropdown {
            display: none; /* Hidden by default */
            padding-left: 1.5rem; /* Indent dropdown items */
            padding-top: 0.5rem;
            overflow: hidden; /* For smooth max-height transition */
            max-height: 0; /* Starts collapsed */
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
            opacity: 0; /* Starts invisible */
        }

        /* Show dropdown when parent is active */
        .dropdown-parent.active .dropdown {
            display: block; /* Change display for transition to work */
            max-height: 500px; /* Arbitrarily large value to allow content to show */
            opacity: 1;
        }

        .dropdown-item {
            padding: 0.65rem 1rem;
            display: block;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            transition: all 0.3s;
            font-size: 0.9rem;
            border-radius: 6px;
            margin: 0.15rem 0;
            position: relative;
            transform-origin: left center;
        }

        .dropdown-item:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }

        /* Small dot indicator for dropdown items on hover */
        .dropdown-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 6px;
            height: 6px;
            background: white;
            border-radius: 50%;
            opacity: 0;
            transition: all 0.3s;
        }

        .dropdown-item:hover::before {
            opacity: 1;
            left: 5px;
        }

        .dropdown-item.active {
            color: white;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.1);
        }

        /* Sidebar Footer: User profile section */
        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto; /* Pushes footer to the bottom */
            background: rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s 0.6s both; /* Delayed fade in for footer */
        }

        /* User Profile: Avatar and info */
        .user-profile {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s;
        }

        .user-profile:hover {
            transform: translateX(5px);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #ffffff, #c8e6c9); /* Light gradient for avatar background */
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--sidebar-green);
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .user-profile:hover .user-avatar {
            transform: rotate(15deg) scale(1.1); /* Avatar rotation on hover */
        }

        .user-info {
            flex: 1;
            overflow: hidden;
        }

        .user-name {
            font-weight: 500;
            font-size: 0.95rem;
            margin-bottom: 0.15rem;
            white-space: nowrap;
            transition: all var(--transition-speed);
        }

        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.6);
            white-space: nowrap;
            transition: all var(--transition-speed);
        }

        /* Main Content Area Styling */
       

        .content ul {
            list-style: none; /* Remove default bullet points */
            padding-left: 0;
            margin-bottom: 1.5rem;
        }

        .content ul li {
            position: relative;
            padding-left: 1.5rem;
            margin-bottom: 0.5rem;
            color: var(--dark);
        }

        .content ul li::before {
            content: '\2022'; /* Custom bullet point */
            color: var(--primary); /* Use primary color for bullets */
            font-size: 1.2em;
            position: absolute;
            left: 0;
            top: 0;
        }

        .content strong {
            color: var(--secondary); /* Highlight strong text */
        }

        /* Collapsed sidebar styles (desktop) */
        body.sidebar-collapsed #sidebar {
            width: var(--sidebar-collapsed-width); /* Collapse sidebar */
        }

        body.sidebar-collapsed .logo-text,
        body.sidebar-collapsed .link-text,
        body.sidebar-collapsed .nav-section-title,
        body.sidebar-collapsed .user-name,
        body.sidebar-collapsed .user-role,
        body.sidebar-collapsed .dropdown-toggle::after {
            opacity: 0; /* Fade out text */
            pointer-events: none; /* Disable interaction */
            white-space: nowrap;
        }

        body.sidebar-collapsed .sidebar-link {
            justify-content: center; /* Center icons when text is hidden */
        }

        /* Hide dropdowns when sidebar is collapsed */
        body.sidebar-collapsed .dropdown-parent.active .dropdown {
            display: none;
        }

        body.sidebar-collapsed .sidebar-header {
            justify-content: center; /* Center logo icon */
        }

        body.sidebar-collapsed .toggle-btn i {
            transform: rotate(180deg); /* Rotate collapse icon */
        }

        /* Mobile Menu Toggle Button */
        .menu-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: var(--sidebar-green);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: none; /* Hidden by default, shown only on mobile */
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001; /* Higher than sidebar */
            box-shadow: 0 4px 15px rgba(26, 86, 50, 0.4);
            transition: all 0.3s;
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeInDown 0.5s 0.3s forwards; /* Fade in and slide down */
        }

        @keyframes fadeInDown {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .menu-toggle:hover {
            transform: translateY(0) scale(1.1);
            box-shadow: 0 6px 20px rgba(26, 86, 50, 0.6);
        }

        /* Responsive Design Media Queries */
        @media (max-width: 992px) {
            #sidebar {
                transform: translateX(-100%); /* Hide sidebar on mobile by default */
            }

            #sidebar.active {
                transform: translateX(0); /* Show sidebar when active (mobile) */
                box-shadow: 5px 0 30px rgba(0, 0, 0, 0.5);
            }

            .content {
                : 0; /* No left margin on mobile margin-left*/
                filter: blur(0); /* Initially no blur */
                pointer-events: auto; /* Allow interaction */
                border-radius: 0; /* Remove rounded corners on content for full width */
                margin: 0; /* Remove side margins */
                padding: 1rem; /* Adjust padding for smaller screens */
            }

            /* Apply blur to content when sidebar is active on mobile */
            #sidebar.active ~ .content {
                filter: blur(2px);
                pointer-events: none; /* Disable interaction with content when sidebar is open */
            }

            .menu-toggle {
                display: flex; /* Show mobile menu toggle */
            }
        }

        @media (max-width: 768px) {
            #sidebar {
                width: 260px; /* Slightly narrower sidebar on very small mobiles */
            }
        }

        /* Staggered animation delays for sidebar items */
        .sidebar-link:nth-child(1) { animation-delay: calc(1 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(2) { animation-delay: calc(2 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(3) { animation-delay: calc(3 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(4) { animation-delay: calc(4 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(5) { animation-delay: calc(5 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(6) { animation-delay: calc(6 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(7) { animation-delay: calc(7 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(8) { animation-delay: calc(8 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(9) { animation-delay: calc(9 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(10) { animation-delay: calc(10 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(11) { animation-delay: calc(11 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(12) { animation-delay: calc(12 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(13) { animation-delay: calc(13 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(14) { animation-delay: calc(14 * var(--nav-item-delay)); }
        .sidebar-link:nth-child(15) { animation-delay: calc(15 * var(--nav-item-delay)); }
    </style>
</head>
<body>
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu">
        <i class="fas fa-bars"></i>
    </button>

    <div id="sidebar" class="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon">
                    <i class="fas fa-cube"></i> </div>
                <div class="logo-text">Admin</div>
            </div>
            <button class="toggle-btn" id="sidebarToggle" aria-label="Collapse sidebar">
                <i class="fas fa-chevron-left"></i> </button>
        </div>

        <nav class="sidebar-nav" aria-label="Main navigation">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="adhome.php" class="sidebar-link" id="home-link">
                    <i class="fas fa-home"></i>
                    <span class="link-text">Home</span>
                </a>
                <a href="admin.php" class="sidebar-link" id="admin-dashboard-link">
                    <i class="fas fa-tachometer-alt"></i> <span class="link-text">Admin Dashboard</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Content Management</div>
                <a href="advideo.php" class="sidebar-link" id="upload-video">
                    <i class="fas fa-video"></i>
                    <span class="link-text">Upload Video</span>
                </a>
                <a href="adbooks.php" class="sidebar-link" id="borrow-books">
                    <i class="fas fa-exchange-alt"></i> <span class="link-text">Books Borrow</span>
                </a>
                <a href="book.php" class="sidebar-link" id="book-reading">
                    <i class="fas fa-book-open"></i> <span class="link-text">Book Reading</span>
                </a>
                <a href="post_read.php" class="sidebar-link" id="post-read">
                    <i class="fas fa-file-alt"></i> <span class="link-text">Post Read</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Community & Features</div>
                <a href="adai.php" class="sidebar-link" id="ai-section">
                    <i class="fas fa-robot"></i> <span class="link-text">AI Section</span>
                </a>
                <a href="adsocial.php" class="sidebar-link" id="social-section">
                    <i class="fas fa-users"></i> <span class="link-text">Social</span>
                </a>
                <a href="index.html" class="sidebar-link" id="wellness-section" target="_blank">
                <i class="fas fa-heartbeat"></i> <span class="link-text">Wellness</span>
                </a>
                <a href="adclub_home.php" class="sidebar-link" id="club-events-section">
                    <i class="fas fa-calendar-alt"></i> <span class="link-text">Club Events</span>
                </a>
                <a href="admin_home.php" class="sidebar-link" id="lost-found-section">
                    <i class="fas fa-search-dollar"></i> <span class="link-text">Lost & Found</span>
                </a>
                
                <div class="dropdown-parent" id="news-dropdown">
                    <a href="#" class="sidebar-link dropdown-toggle" id="news-toggle" aria-expanded="false" aria-controls="news-menu">
                        <i class="fas fa-newspaper"></i> <span class="link-text">News Sources</span>
                    </a>
                    <div class="dropdown" id="news-menu">
                        <a href="addailythanthi.php" class="dropdown-item">Dailythanthi</a>
                        <a href="addinamalar.php" class="dropdown-item">Dinamalar</a>
                        <a href="adhindutamil.php" class="dropdown-item">Hindutamil</a>
                        <a href="adjionews.php" class="dropdown-item">Jio News</a>
                        <a href="adnews18.php" class="dropdown-item">News18</a>
                        <a href="adoneindia.php" class="dropdown-item">OneIndia</a>
                    </div>
                </div>
                
                <a href="adupdate.php" class="sidebar-link" id="update-section">
                    <i class="fas fa-bell"></i> <span class="link-text">Updates</span>
                    <span class="badge">3</span> </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">System</div>
                <a href="adfeedback.php" class="sidebar-link" id="feedback-section">
                    <i class="fas fa-comment-alt"></i> <span class="link-text">Feedback</span>
                </a>
                <a href="login.php" class="sidebar-link" id="logout">
                    <i class="fas fa-sign-out-alt"></i> <span class="link-text">Logout</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">AD</div>
                <div class="user-info">
                    <div class="user-name">Admin User</div>
                    <div class="user-role">Super Admin</div>
                </div>
            </div>
        </div>
    </div>

    <div class="content" id="mainContent">
       
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get references to key DOM elements
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle'); // Mobile menu toggle button
            const sidebarToggle = document.getElementById('sidebarToggle'); // Desktop sidebar collapse button
            const body = document.body;
            const mainContent = document.getElementById('mainContent');
            const newsDropdownParent = document.getElementById('news-dropdown');
            const newsToggle = document.getElementById('news-toggle'); // News dropdown toggle link
            const sidebarLinks = document.querySelectorAll('.sidebar-link'); // All sidebar links
            const currentDateTimeElement = document.getElementById('currentDateTime');

            // --- Sidebar Initialization & Animations ---
            // Add 'loaded' class after a short delay to trigger the initial slide-in animation
            setTimeout(() => {
                sidebar.classList.add('loaded');
            }, 100);

            // --- Desktop Sidebar Toggle Functionality ---
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    body.classList.toggle('sidebar-collapsed');
                    // Adjust icon of the toggle button
                    const icon = sidebarToggle.querySelector('i');
                    if (body.classList.contains('sidebar-collapsed')) {
                        icon.classList.remove('fa-chevron-left');
                        icon.classList.add('fa-chevron-right');
                    } else {
                        icon.classList.remove('fa-chevron-right');
                        icon.classList.add('fa-chevron-left');
                    }
                });
            }

            // --- Mobile Menu Toggle Functionality ---
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    // Toggle an icon on the menuToggle itself for open/close state if desired
                    const icon = menuToggle.querySelector('i');
                    if (sidebar.classList.contains('active')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times'); // 'X' icon when open
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars'); // Hamburger icon when closed
                    }
                });
            }

            // --- Close Sidebar when clicking outside on mobile ---
            // This is crucial for mobile UX with the blur effect
            mainContent.addEventListener('click', function() {
                if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    // Reset mobile menu toggle icon
                    const icon = menuToggle.querySelector('i');
                    icon.classList.remove('fa-times');
                    icon.classList.add('fa-bars');
                }
            });

            // --- Dropdown Toggle Functionality for News Sources ---
            if (newsToggle && newsDropdownParent) {
                newsToggle.addEventListener('click', function(e) {
                    e.preventDefault(); // Prevent default link behavior (e.g., navigating to '#')
                    newsDropdownParent.classList.toggle('active');
                    const isExpanded = newsDropdownParent.classList.contains('active');
                    newsToggle.setAttribute('aria-expanded', isExpanded); // Update ARIA attribute
                });
            }

            // --- Active Link Highlighting ---
            // Function to set the active link
            function setActiveLink() {
                const currentPath = window.location.pathname.split('/').pop(); // Get current page filename
                
                // Remove 'active' from all links first
                sidebarLinks.forEach(link => {
                    link.classList.remove('active');
                    link.closest('.dropdown-parent')?.classList.remove('active'); // Also deactivate parent dropdown
                    link.setAttribute('aria-current', 'false'); // Reset ARIA current
                });

                // Find and set 'active' class on the current link
                let foundActive = false;
                sidebarLinks.forEach(link => {
                    const linkPath = link.getAttribute('href');
                    if (linkPath && linkPath.includes(currentPath) && currentPath !== '') {
                        link.classList.add('active');
                        link.setAttribute('aria-current', 'page'); // Indicate current page for accessibility
                        foundActive = true;

                        // If the active link is inside a dropdown, make sure its parent is also active
                        let parentDropdown = link.closest('.dropdown-parent');
                        if (parentDropdown) {
                            parentDropdown.classList.add('active');
                            const dropdownToggle = parentDropdown.querySelector('.dropdown-toggle');
                            if (dropdownToggle) {
                                dropdownToggle.setAttribute('aria-expanded', 'true');
                            }
                        }
                    }
                });

                // If no specific link matched (e.g., on base admin URL),
                // check if 'admin.php' or 'adhome.php' should be active by default.
                // You might adjust this logic based on your default admin page.
                if (!foundActive && (currentPath === 'admin.php' || currentPath === '')) {
                    document.getElementById('admin-dashboard-link')?.classList.add('active');
                    document.getElementById('admin-dashboard-link')?.setAttribute('aria-current', 'page');
                } else if (!foundActive && currentPath === 'adhome.php') {
                    document.getElementById('home-link')?.classList.add('active');
                    document.getElementById('home-link')?.setAttribute('aria-current', 'page');
                }
            }

            // Call setActiveLink on page load
            setActiveLink();

            // Optional: Re-set active link if you're using AJAX or hash changes for navigation
            // window.addEventListener('hashchange', setActiveLink);
            // window.addEventListener('popstate', setActiveLink);

            // --- Display Current Date and Time ---
            function updateDateTime() {
                const now = new Date();
                const options = { 
                    weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                    hour: '2-digit', minute: '2-digit', second: '2-digit',
                    hour12: true // For 12-hour format with AM/PM
                };
                currentDateTimeElement.textContent = now.toLocaleDateString('en-US', options);
            }

            updateDateTime(); // Initial call
            setInterval(updateDateTime, 1000); // Update every second
        });
    </script>
</body>
</html>