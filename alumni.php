<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Portal - Enhanced Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS Variables: Combines Alumni theme with advanced dashboard structure */
        :root {
            --primary-gold: #FFD700;
            --primary-gold-dark: #B8860B;
            --text-on-gold: #ffffff;
            --text-dark: #333333;
            --text-medium: #666666;
            --bg-light: #f9fafb; /* Light background for the main content area */
            --bg-content: #ffffff; /* White background for the content block */
            --danger: #DC3545;
            --danger-dark: #C82333;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 88px;
            --transition-speed: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            --nav-item-delay: 0.05s;
        }

        /* Basic Reset and Font Styling */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        /* Body Layout */
        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--bg-light);
            transition: margin-left var(--transition-speed);
            overflow-x: hidden;
            position: relative;
        }

        /* Adjust body margin for sidebar state (desktop) */
        @media (min-width: 993px) {
            body {
                margin-left: var(--sidebar-width);
            }
            body.sidebar-collapsed {
                margin-left: var(--sidebar-collapsed-width);
            }
        }

        /* --- Sidebar Styling --- */
        #sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(160deg, var(--primary-gold-dark), var(--primary-gold));
            color: var(--text-on-gold);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            z-index: 1000;
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.2);
            transition: width var(--transition-speed), transform var(--transition-speed);
        }
        
        /* Sidebar initial animation state (off-screen) */
        #sidebar:not(.loaded) {
            transform: translateX(-100%);
        }
        #sidebar.loaded {
            transform: translateX(0);
        }

        /* Sidebar Header: Contains logo/title and toggle button */
        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
            background: rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            overflow: hidden;
        }

        .logo-icon {
            font-size: 1.8rem;
            color: white;
            background: rgba(0,0,0, 0.25);
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: inset 0 0 5px rgba(0,0,0,0.3);
        }

        .logo-text {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--text-on-gold);
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
            transition: opacity var(--transition-speed);
            white-space: nowrap;
        }

        /* Sidebar Toggle Button (Desktop) */
        .toggle-btn {
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: var(--text-on-gold);
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
            transform: rotate(15deg) scale(1.05);
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.75rem;
        }

        .sidebar-nav ul {
            list-style: none;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.9);
            padding: 0.85rem 1.25rem;
            margin: 0.25rem 0;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-weight: 500;
            gap: 15px;
            white-space: nowrap;
            position: relative;
            transform-origin: left center;
            opacity: 0;
            transform: translateX(-20px);
            animation: slideIn 0.4s var(--transition-speed) forwards;
        }

        @keyframes slideIn {
            to { opacity: 1; transform: translateX(0); }
        }

        .sidebar-link i {
            font-size: 1.15rem;
            min-width: 24px;
            text-align: center;
            transition: all 0.3s;
        }

        .link-text {
            transition: opacity var(--transition-speed);
        }

        /* Hover & Active States for Links */
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateX(5px);
        }

        .sidebar-link:hover i {
            transform: scale(1.1);
        }

        .sidebar-link.active {
            background: white;
            color: var(--primary-gold-dark);
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transform: translateX(5px);
        }

        .sidebar-link.active i {
            color: var(--primary-gold-dark);
        }

        .sidebar-link.active::after {
            content: '';
            position: absolute;
            left: 0px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: var(--primary-gold-dark);
            border-radius: 0 4px 4px 0;
            box-shadow: 0 0 10px var(--primary-gold-dark);
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; box-shadow: 0 0 10px var(--primary-gold-dark); }
            50% { opacity: 0.7; box-shadow: 0 0 15px var(--primary-gold-dark); }
        }

        /* Logout button */
        #logout-link {
            margin-top: 1rem;
            background-color: rgba(220, 53, 69, 0.8);
        }
        #logout-link:hover {
            background-color: var(--danger-dark);
            color: white;
        }

        /* --- Sidebar Footer & User Profile --- */
        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(0, 0, 0, 0.2);
            margin-top: auto; /* Pushes to the bottom */
            flex-shrink: 0;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .user-profile:hover {
            transform: translateX(3px);
        }

        .user-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-gold-dark));
            border: 2px solid rgba(255,255,255,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.1rem;
            color: var(--text-on-gold);
            flex-shrink: 0;
            transition: transform 0.3s;
        }
        
        .user-profile:hover .user-avatar {
            transform: scale(1.1) rotate(10deg);
        }

        .user-info {
            flex: 1;
            overflow: hidden;
        }

        .user-name {
            font-weight: 500;
            font-size: 0.95rem;
            white-space: nowrap;
            transition: opacity var(--transition-speed);
        }

        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.7);
            white-space: nowrap;
            transition: opacity var(--transition-speed);
        }
        
        /* --- Collapsed Sidebar Styles (Desktop) --- */
        body.sidebar-collapsed #sidebar {
            width: var(--sidebar-collapsed-width);
        }
        body.sidebar-collapsed .logo-text,
        body.sidebar-collapsed .link-text,
        body.sidebar-collapsed .user-name,
        body.sidebar-collapsed .user-role {
            opacity: 0;
            pointer-events: none;
        }
        body.sidebar-collapsed .sidebar-header {
            justify-content: center;
        }
        body.sidebar-collapsed .toggle-btn {
            transform: rotate(180deg);
        }
        body.sidebar-collapsed .sidebar-link,
        body.sidebar-collapsed .user-profile {
            justify-content: center;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        /* --- Main Content --- */
        .main-content {
            flex-grow: 1;
            padding: 2rem;
            transition: filter 0.3s;
        }
        
        .content-block {
            background-color: var(--bg-content);
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        /* --- Mobile Menu & Responsive Design --- */
        .menu-toggle {
            display: none; /* Hidden on desktop */
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: var(--primary-gold-dark);
            color: white;
            border: none;
            width: 44px;
            height: 44px;
            border-radius: 12px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeInDown 0.5s 0.3s forwards;
        }

        @keyframes fadeInDown {
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 992px) {
            body { margin-left: 0; }
            #sidebar {
                transform: translateX(-100%);
                transition: transform var(--transition-speed);
                box-shadow: 5px 0 40px rgba(0, 0, 0, 0.4);
            }
            #sidebar.active {
                transform: translateX(0);
            }
            .main-content { padding: 1rem; }
            .content-block { padding: 1.5rem; margin-top: 4rem; }
            #sidebar.active ~ .main-content {
                filter: blur(3px);
                pointer-events: none;
            }
            .menu-toggle {
                display: flex;
            }
            .toggle-btn {
                display: none; /* Hide desktop toggle on mobile */
            }
        }

        /* Staggered animation delays for sidebar items */
        .sidebar-nav li:nth-child(1) .sidebar-link { animation-delay: calc(1 * var(--nav-item-delay)); }
        .sidebar-nav li:nth-child(2) .sidebar-link { animation-delay: calc(2 * var(--nav-item-delay)); }
        .sidebar-nav li:nth-child(3) .sidebar-link { animation-delay: calc(3 * var(--nav-item-delay)); }
        .sidebar-nav li:nth-child(4) .sidebar-link { animation-delay: calc(4 * var(--nav-item-delay)); }
        .sidebar-nav li:nth-child(5) .sidebar-link { animation-delay: calc(5 * var(--nav-item-delay)); }
        
        /* Content Styling from Alumni Page */
        .content-block h1 { color: var(--primary-gold-dark); margin-bottom: 20px; font-size: 2.2rem; }
        .content-block h2 { color: var(--primary-gold-dark); margin-top: 30px; margin-bottom: 15px; font-size: 1.6rem; border-bottom: 2px solid rgba(184, 134, 11, 0.2); padding-bottom: 5px; }
        .content-block p { color: var(--text-medium); line-height: 1.7; margin-bottom: 15px; }
        .content-block ul { list-style: none; padding-left: 0; margin-bottom: 20px; }
        .content-block ul li { position: relative; padding-left: 25px; margin-bottom: 8px; color: var(--text-dark); }
        .content-block ul li::before { content: '\\2022'; color: var(--primary-gold); font-size: 1.5em; position: absolute; left: 0; top: -2px; }
        .content-block strong { color: var(--primary-gold-dark); }
        .content-block a { color: var(--primary-gold-dark); text-decoration: none; transition: color 0.3s ease; }
        .content-block a:hover { text-decoration: underline; color: var(--primary-gold); }
        .community-member-card { background-color: var(--bg-light); border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; margin-bottom: 15px; display: flex; align-items: center; gap: 15px; }
        .community-member-card .member-avatar { width: 60px; height: 60px; border-radius: 50%; background-color: var(--primary-gold); display: flex; align-items: center; justify-content: center; font-size: 1.8rem; color: white; flex-shrink: 0; }
        .community-member-card .member-info h4 { margin: 0 0 5px 0; color: var(--primary-gold-dark); font-size: 1.2rem; }
        .community-member-card .member-info p { margin: 0; font-size: 0.95rem; color: var(--text-medium); }
    </style>
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div id="sidebar">
        <div class="sidebar-header">
            <div class="logo">
                <div class="logo-icon"><i class="fas fa-graduation-cap"></i></div>
                <span class="logo-text">Alumni Portal</span>
            </div>
            <button class="toggle-btn" id="sidebarToggle" aria-label="Collapse sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li><a href="alumnihome.php" class="sidebar-link" id="alumni-home-link"><i class="fas fa-home"></i><span class="link-text">Alumni Home</span></a></li>
                <li><a href="alumni_post.php" class="sidebar-link" id="alumni-book-link"><i class="fas fa-upload"></i><span class="link-text">Upload Post</span></a></li>
                <li><a href="alpost_read.php" class="sidebar-link" id="alumni-post-read-link"><i class="fas fa-file-alt"></i><span class="link-text">Post Read</span></a></li>
                <a href="altalent.php" class="sidebar-link" id="club-events-section">
                    <i class="fas fa-feather-pointed"></i> <span class="link-text">Talent</span>
                </a>
                <li><a href="adai2.php" class="sidebar-link" id="alumni-post-read-link"><i class="fas fa-robot"></i><span class="link-text">AI Bot</span></a></li>
                <li><a href="index.html" class="sidebar-link" id="alumni-wellness-link" target="_blank"><i class="fas fa-heartbeat"></i><span class="link-text">Wellness</span></a></li>
                 <li><a href="colloction/apps3.html" class="sidebar-link" id="alumni-book-link" target="_blank" ><i class="fas fa-tools"></i><span class="link-text">Apps</span></a></li>
                 <a href="stdsee1.php" class="sidebar-link" id="notifications-link">
                    <i class="fas fa-bell"></i>
                    <span class="link-text">Notifications</span>
                </a>
                <li><a href="login.php" class="sidebar-link" id="logout-link"><i class="fas fa-sign-out-alt"></i><span class="link-text">Logout</span></a></li>
            </ul>
        </nav>

        <!-- Sidebar Footer with User Profile -->
        <div class="sidebar-footer">
            <div class="user-profile">
                <div class="user-avatar">AL</div>
                <div class="user-info">
                    <div class="user-name">Alumni User</div>
                    <div class="user-role">Alumni</div>
                </div>
            </div>
        </div>
    </div>

    

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get all necessary DOM elements
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const menuToggle = document.getElementById('menuToggle');
            const mainContent = document.getElementById('mainContent');
            const body = document.body;

            // --- Sidebar Initialization ---
            setTimeout(() => {
                sidebar.classList.add('loaded');
            }, 200);

            // --- Desktop Sidebar Collapse ---
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', () => {
                    body.classList.toggle('sidebar-collapsed');
                    // Change icon based on state
                    const icon = sidebarToggle.querySelector('i');
                    icon.className = body.classList.contains('sidebar-collapsed') 
                        ? 'fas fa-chevron-right' 
                        : 'fas fa-chevron-left';
                });
            }
            
            // --- Mobile Sidebar Toggle ---
            if (menuToggle) {
                menuToggle.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                    // Change icon based on state
                    const icon = menuToggle.querySelector('i');
                    icon.className = sidebar.classList.contains('active') 
                        ? 'fas fa-times' 
                        : 'fas fa-bars';
                });
            }
            
            // --- Close mobile sidebar when clicking on main content ---
            if (mainContent) {
                mainContent.addEventListener('click', () => {
                    if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        // Reset mobile menu icon
                        menuToggle.querySelector('i').className = 'fas fa-bars';
                    }
                });
            }

            // --- Active Link Highlighting ---
            function setActiveLink() {
                const currentPath = window.location.pathname.split('/').pop() || 'alumni.php';
                const sidebarLinks = document.querySelectorAll('.sidebar-link');

                sidebarLinks.forEach(link => {
                    link.classList.remove('active');
                    link.removeAttribute('aria-current');

                    const linkPath = link.getAttribute('href').split('/').pop();
                    if (linkPath === currentPath) {
                        link.classList.add('active');
                        link.setAttribute('aria-current', 'page');
                    }
                });
            }
            setActiveLink();

            // --- Display Current Date ---
            function updateAlumniDate() {
                const dateElement = document.getElementById('currentAlumniDate');
                if (dateElement) {
                    const now = new Date();
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    dateElement.textContent = now.toLocaleDateString('en-US', options);
                }
            }
            updateAlumniDate();
        });
    </script>
</body>
</html>