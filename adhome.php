<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Premium Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --primary-light: rgba(67, 97, 238, 0.1);
            --secondary: #3f37c9;
            --dark: #1f2937;
            --darker: #111827;
            --light: #f9fafb;
            --lighter: #ffffff;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition-speed: 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            --nav-item-delay: 0.05s;
            --sidebar-green: #1a5632;
            --sidebar-green-dark: #0d3b1e;

            --dashboard-gradient-start: #28a745;
            --dashboard-gradient-end: #218838;
            --card-bg: #ffffff;
            --card-shadow: rgba(0, 0, 0, 0.08);
            --text-dark: #1f2937;
            --text-medium: #4b5563;
            --text-light: #6b7280;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--light);
            transition: margin-left var(--transition-speed);
            overflow-x: hidden;
            position: relative;
        }

        body.sidebar-collapsed #main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        #sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(160deg, var(--sidebar-green-dark), var(--sidebar-green));
            color: white;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            display: flex;
            flex-direction: column;
            transform: translateX(-100%);
            transition: transform var(--transition-speed), width var(--transition-speed);
            z-index: 1000;
            box-shadow: 5px 0 30px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        #sidebar.loaded {
            transform: translateX(0);
        }

        .sidebar-header {
            padding: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 80px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
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
            background: rgba(255, 255, 255, 0.15);
            width: 42px;
            height: 42px;
            border-radius: 12px;
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
            transition: opacity var(--transition-speed);
            white-space: nowrap;
        }

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

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 1rem 0.5rem;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }

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

        .nav-section {
            margin-bottom: 1.5rem;
            overflow: hidden;
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
            white-space: nowrap;
            position: relative;
            transform-origin: left center;
            opacity: 0;
            transform: translateX(-20px);
            animation: slideIn 0.4s forwards;
            animation-delay: var(--nav-item-delay);
        }

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
            z-index: 1;
        }

        .link-text {
            transition: opacity var(--transition-speed), transform var(--transition-speed);
            position: relative;
            z-index: 1;
        }

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
            color: white;
            transform: translateX(5px) scale(1.02);
        }

        .sidebar-link:hover i {
            transform: scale(1.15);
            color: white;
        }

        .sidebar-link:hover::before {
            opacity: 0.2;
        }

        .sidebar-link.active {
            background: white;
            color: var(--sidebar-green);
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
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }

        #logout {
            background: rgba(239, 68, 68, 0.1);
            color: rgba(239, 68, 68, 0.9);
        }
        
        #logout:hover {
            background: rgba(239, 68, 68, 0.2);
            color: var(--danger);
        }
        
        #logout.active {
            background: var(--danger);
            color: white;
        }

        .dropdown-parent {
            position: relative;
            overflow: hidden;
        }

        .dropdown-toggle::after {
            content: '\f078';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            font-size: 0.7rem;
            margin-left: auto;
            transition: transform 0.3s;
        }

        .dropdown-parent.active .dropdown-toggle::after {
            transform: rotate(180deg);
        }

        .dropdown {
            max-height: 0;
            overflow: hidden;
            padding-left: 1.5rem;
            padding-top: 0.5rem;
            transition: max-height 0.4s ease-out, opacity 0.3s ease-out;
            opacity: 0;
        }

        .dropdown-parent.active .dropdown {
            max-height: 500px;
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
            opacity: 0;
            transform: translateX(-10px);
            animation: fadeInItem 0.3s forwards;
            animation-delay: var(--nav-item-delay);
        }
        
        @keyframes fadeInItem {
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .dropdown-item:hover {
            color: white;
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(5px);
        }

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

        .badge {
            background-color: var(--warning);
            color: white;
            font-size: 0.75rem;
            font-weight: bold;
            padding: 0.2em 0.6em;
            border-radius: 9999px;
            margin-left: auto;
            position: relative;
            z-index: 1;
            transition: opacity var(--transition-speed);
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
            background: rgba(0, 0, 0, 0.2);
            animation: fadeIn 0.5s 0.6s both;
            flex-shrink: 0;
        }

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
            background: linear-gradient(135deg, #ffffff, #c8e6c9);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: var(--sidebar-green);
            transition: all 0.3s;
            flex-shrink: 0;
        }

        .user-profile:hover .user-avatar {
            transform: rotate(15deg) scale(1.1);
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

        #main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 20px;
            transition: margin-left var(--transition-speed);
            overflow-y: auto;
        }

        body.sidebar-collapsed #sidebar {
            width: var(--sidebar-collapsed-width);
        }

        body.sidebar-collapsed .logo-text,
        body.sidebar-collapsed .link-text,
        body.sidebar-collapsed .nav-section-title,
        body.sidebar-collapsed .user-name,
        body.sidebar-collapsed .user-role,
        body.sidebar-collapsed .dropdown-toggle::after,
        body.sidebar-collapsed .badge {
            opacity: 0;
            pointer-events: none;
            white-space: nowrap;
            transform: translateX(-20px);
            transition: opacity var(--transition-speed), transform var(--transition-speed);
        }

        body.sidebar-collapsed .sidebar-link {
            justify-content: center;
        }

        body.sidebar-collapsed .dropdown-parent.active .dropdown {
            max-height: 0;
            opacity: 0;
            padding-top: 0;
        }

        body.sidebar-collapsed .sidebar-header {
            justify-content: center;
        }

        body.sidebar-collapsed .toggle-btn i {
            transform: rotate(180deg);
        }

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
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1001;
            box-shadow: 0 4px 15px rgba(26, 86, 50, 0.4);
            transition: all 0.3s;
            opacity: 0;
            transform: translateY(-20px);
            animation: fadeInDown 0.5s 0.3s forwards;
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

        @media (max-width: 992px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.active {
                transform: translateX(0);
                box-shadow: 5px 0 30px rgba(0, 0, 0, 0.5);
            }

            #main-content {
                margin-left: 0;
                filter: blur(0);
                pointer-events: auto;
                border-radius: 0;
                margin: 0;
                padding: 1rem;
            }

            body.sidebar-active #main-content {
                filter: blur(2px);
                pointer-events: none;
            }

            .menu-toggle {
                display: flex;
            }
            
            body.sidebar-active::after {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 999;
                pointer-events: auto;
            }
        }

        @media (max-width: 768px) {
            #sidebar {
                width: 260px;
            }
            .dashboard-header {
                padding: 2rem 1rem;
            }
            .welcome-message {
                font-size: 2rem;
            }
            .tagline {
                font-size: 1rem;
            }
            .current-datetime {
                font-size: 1rem;
                padding: 0.5rem 1rem;
            }
            .current-datetime .time-part {
                font-size: 1.2em;
            }
            .dashboard-grid {
                gap: 1rem;
            }
            .quick-link-card {
                padding: 1rem;
            }
            .quick-link-card i {
                font-size: 2rem;
            }
            .quick-link-card h3 {
                font-size: 1.1rem;
            }
            .quick-link-card p {
                font-size: 0.85rem;
            }
            .updates-section, .extra-text-section {
                padding: 1rem;
            }
            .updates-section h2, .extra-text-section h2 {
                font-size: 1.5rem;
            }
            .update-icon {
                font-size: 1.2rem;
            }
        }

        .dashboard-header-main {
            background: linear-gradient(45deg, var(--dashboard-gradient-start), var(--dashboard-gradient-end));
            color: white;
            padding: 3rem 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 30px rgba(40, 167, 69, 0.3);
            text-align: center;
            animation: headerEntrance 0.8s ease-out forwards;
            position: relative;
            overflow: hidden;
        }

        .dashboard-header-main::before {
            content: '';
            position: absolute;
            top: -20px;
            left: -20px;
            width: 100px;
            height: 100px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: rotate(45deg);
            animation: bubble 5s infinite ease-in-out alternate;
        }

        .dashboard-header-main::after {
            content: '';
            position: absolute;
            bottom: -30px;
            right: -30px;
            width: 120px;
            height: 120px;
            background: rgba(255, 255, 255, 0.08);
            border-radius: 50%;
            transform: rotate(-30deg);
            animation: bubble 6s infinite ease-in-out alternate-reverse;
        }

        .welcome-message-main {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.2);
        }

        .tagline-main {
            font-size: 1.1rem;
            font-weight: 300;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .current-datetime-main {
            font-size: 1.2rem;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            display: inline-block;
            backdrop-filter: blur(5px);
            animation: fadeIn 1s ease-out 0.5s both;
        }

        .current-datetime-main .date-part-main {
            display: block;
            font-size: 0.9em;
            opacity: 0.8;
        }

        .current-datetime-main .time-part-main {
            display: block;
            font-size: 1.5em;
            font-weight: 700;
            margin-top: 5px;
        }

        .dashboard-grid-main {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .quick-link-card-main {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-shadow: 0 4px 20px var(--card-shadow);
            transition: all 0.3s ease;
            text-decoration: none;
            color: var(--text-dark);
            position: relative;
            overflow: hidden;
            animation: cardFadeIn 0.5s ease-out forwards;
            animation-delay: var(--card-delay, 0s);
        }

        .quick-link-card-main:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            background: linear-gradient(135deg, var(--lighter), var(--gray-light));
        }

        .quick-link-card-main i {
            font-size: 2.8rem;
            color: var(--primary);
            margin-bottom: 0.75rem;
            transition: all 0.3s ease;
        }

        .quick-link-card-main:hover i {
            transform: scale(1.15) rotate(5deg);
            color: var(--primary-dark);
        }

        .quick-link-card-main h3 {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 600;
        }

        .quick-link-card-main p {
            font-size: 0.9rem;
            color: var(--text-medium);
            line-height: 1.5;
            flex-grow: 1;
        }

        .quick-link-card-main.accent-card-main {
            background: linear-gradient(135deg, var(--dashboard-gradient-start), var(--dashboard-gradient-end));
            color: white;
        }

        .quick-link-card-main.accent-card-main i {
            color: white;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }

        .quick-link-card-main.accent-card-main h3,
        .quick-link-card-main.accent-card-main p {
            color: rgba(255, 255, 255, 0.9);
        }

        .quick-link-card-main.accent-card-main:hover {
            background: linear-gradient(135deg, var(--dashboard-gradient-end), var(--dashboard-gradient-start));
            box-shadow: 0 10px 30px rgba(40, 167, 69, 0.4);
            transform: translateY(-8px) scale(1.02);
        }

        .updates-section-main {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            box-shadow: 0 4px 20px var(--card-shadow);
            animation: fadeIn 0.8s 0.6s forwards;
        }

        .updates-section-main h2 {
            font-size: 1.7rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
            padding-bottom: 0.5rem;
        }

        .update-item-main {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed var(--gray-light);
        }

        .update-item-main:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .update-icon-main {
            font-size: 1.5rem;
            color: var(--warning);
            margin-right: 1rem;
            flex-shrink: 0;
            padding-top: 0.2rem;
        }

        .update-content-main h3 {
            font-size: 1.1rem;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
            font-weight: 600;
        }

        .update-content-main p {
            font-size: 0.85rem;
            color: var(--text-medium);
        }

        .update-content-main .date-main {
            font-size: 0.75rem;
            color: var(--text-light);
            margin-top: 0.5rem;
            display: block;
        }

        .extra-text-section-main {
            background-color: var(--card-bg);
            border-radius: 12px;
            padding: 2rem;
            margin-top: 2rem;
            box-shadow: 0 4px 20px var(--card-shadow);
            animation: fadeIn 0.8s 0.8s forwards;
        }

        .extra-text-section-main h2 {
            font-size: 1.7rem;
            color: var(--text-dark);
            margin-bottom: 1rem;
            border-bottom: 2px solid var(--gray-light);
            padding-bottom: 0.5rem;
        }

        .extra-text-section-main p {
            font-size: 1rem;
            color: var(--text-medium);
            line-height: 1.7;
            margin-bottom: 1rem;
        }

        .extra-text-section-main ul {
            list-style: none;
            padding-left: 0;
        }

        .extra-text-section-main ul li {
            position: relative;
            padding-left: 25px;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .extra-text-section-main ul li::before {
            content: '\f058';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            color: var(--success);
            position: absolute;
            left: 0;
            top: 0;
        }
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
                    <i class="fas fa-cube"></i>
                </div>
                <div class="logo-text">Admin</div>
            </div>
            <button class="toggle-btn" id="sidebarToggle" aria-label="Collapse sidebar">
                <i class="fas fa-chevron-left"></i>
            </button>
        </div>

        <nav class="sidebar-nav" aria-label="Main navigation">
            <div class="nav-section">
                <div class="nav-section-title">Main</div>
                <a href="adhome.php" class="sidebar-link" id="home-link">
                    <i class="fas fa-home"></i>
                    <span class="link-text">Home</span>
                </a>
                
                <a href="addash.php" class="sidebar-link" id="admin-dashboard-link">
                    <i class="fas fa-tachometer-alt"></i> <span class="link-text">Admin Dashboard</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Content Management</div>
                <a href="admin_post.php" class="sidebar-link" id="upload-post">
                    <i class="fas fa-upload"></i> <span class="link-text">Post Upload</span>
                </a>

                <a href="adpost_read.php" class="sidebar-link" id="post-read">
                    <i class="fas fa-file-alt"></i> <span class="link-text">Post Read</span>
                </a>

                <a href="colloction/apps3.html" class="sidebar-link" id="apps-link" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-tools"></i> <span class="link-text"> Apps </span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Community & Features</div>
                <a href="adai.php" class="sidebar-link" id="ai-section">
                    <i class="fas fa-robot"></i> <span class="link-text">AI Section</span>
                </a>
                <a href="index.html" class="sidebar-link" id="wellness-section" target="_blank" rel="noopener noreferrer">
                    <i class="fas fa-heartbeat"></i> <span class="link-text">Wellness</span>
                </a>
                <a href="adtalent.php" class="sidebar-link" id="talent-section">
                    <i class="fas fa-feather-pointed"></i> <span class="link-text">Talent</span>
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
                    <span class="badge">3</span>
                </a>
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

    <main id="main-content">
        <div class="dashboard-header-main">
            <h1 class="welcome-message-main">Welcome, Admin User!</h1>
            <p class="tagline-main">Manage your portal effectively and efficiently.</p>
            <div class="current-datetime-main" id="currentDateTimeMain">
                <span class="date-part-main" id="dateDisplayMain">
                    <?php
                        date_default_timezone_set('Asia/Kolkata');
                        echo date("l, F j, Y");
                    ?>
                </span>
                <span class="time-part-main" id="timeDisplayMain">
                    <?php
                        echo date("h:i:s A");
                    ?>
                </span>
            </div>
        </div>

        <div class="dashboard-grid-main">
            <a href="adhome.php" class="quick-link-card-main" style="--card-delay: 0.1s;">
                <i class="fas fa-home"></i>
                <h3>Home</h3>
                <p>Return to the main dashboard home page.</p>
            </a>
            <a href="admin.php" class="quick-link-card-main accent-card-main" style="--card-delay: 0.2s;">
                <i class="fas fa-tachometer-alt"></i>
                <h3>Admin Dashboard</h3>
                <p>Access the main administrative control panel.</p>
            </a>
            <a href="admin_post.php" class="quick-link-card-main" style="--card-delay: 0.3s;">
                <i class="fas fa-upload"></i>
                <h3>Post Upload</h3>
                <p>Upload new content, announcements, or articles.</p>
            </a>
            <a href="adpost_read.php" class="quick-link-card-main" style="--card-delay: 0.4s;">
                <i class="fas fa-file-alt"></i>
                <h3>Post Read</h3>
                <p>Review and manage existing posts and content.</p>
            </a>
            <a href="colloction/apps3.html" class="quick-link-card-main" target="_blank" rel="noopener noreferrer" style="--card-delay: 0.5s;">
                <i class="fas fa-tools"></i>
                <h3>Apps</h3>
                <p>Access integrated tools and applications.</p>
            </a>
            <a href="adai.php" class="quick-link-card-main" style="--card-delay: 0.6s;">
                <i class="fas fa-robot"></i>
                <h3>AI Section</h3>
                <p>Explore AI-powered features and tools.</p>
            </a>
            <a href="index.html" class="quick-link-card-main" target="_blank" rel="noopener noreferrer" style="--card-delay: 0.7s;">
                <i class="fas fa-heartbeat"></i>
                <h3>Wellness</h3>
                <p>Access resources related to well-being.</p>
            </a>
            <a href="adtalent.php" class="quick-link-card-main" style="--card-delay: 0.8s;">
                <i class="fas fa-feather-pointed"></i>
                <h3>Talent</h3>
                <p>Manage and showcase student talents.</p>
            </a>
            <a href="adclub_home.php" class="quick-link-card-main" style="--card-delay: 0.9s;">
                <i class="fas fa-calendar-alt"></i>
                <h3>Club Events</h3>
                <p>Oversee and schedule club activities and events.</p>
            </a>
            <a href="admin_home.php" class="quick-link-card-main" style="--card-delay: 1.0s;">
                <i class="fas fa-search-dollar"></i>
                <h3>Lost & Found</h3>
                <p>Manage reports of lost and found items.</p>
            </a>
            <a href="addailythanthi.php" class="quick-link-card-main" style="--card-delay: 1.1s;">
                <i class="fas fa-newspaper"></i>
                <h3>Dailythanthi News</h3>
                <p>View news from Dailythanthi.</p>
            </a>
            <a href="addinamalar.php" class="quick-link-card-main" style="--card-delay: 1.2s;">
                <i class="fas fa-newspaper"></i>
                <h3>Dinamalar News</h3>
                <p>View news from Dinamalar.</p>
            </a>
            <a href="adhindutamil.php" class="quick-link-card-main" style="--card-delay: 1.3s;">
                <i class="fas fa-newspaper"></i>
                <h3>Hindutamil News</h3>
                <p>View news from Hindutamil.</p>
            </a>
            <a href="adjionews.php" class="quick-link-card-main" style="--card-delay: 1.4s;">
                <i class="fas fa-newspaper"></i>
                <h3>Jio News</h3>
                <p>View news from Jio News.</p>
            </a>
            <a href="adnews18.php" class="quick-link-card-main" style="--card-delay: 1.5s;">
                <i class="fas fa-newspaper"></i>
                <h3>News18</h3>
                <p>View news from News18.</p>
            </a>
            <a href="adoneindia.php" class="quick-link-card-main" style="--card-delay: 1.6s;">
                <i class="fas fa-newspaper"></i>
                <h3>OneIndia</h3>
                <p>View news from OneIndia.</p>
            </a>
            <a href="adupdate.php" class="quick-link-card-main accent-card-main" style="--card-delay: 1.7s;">
                <i class="fas fa-bell"></i>
                <h3>Updates</h3>
                <p>Manage system updates and notifications.</p>
            </a>
            <a href="adfeedback.php" class="quick-link-card-main" style="--card-delay: 1.8s;">
                <i class="fas fa-comment-alt"></i>
                <h3>Feedback</h3>
                <p>Review user feedback and suggestions.</p>
            </a>
            <a href="login.php" class="quick-link-card-main" style="--card-delay: 1.9s;">
                <i class="fas fa-sign-out-alt"></i>
                <h3>Logout</h3>
                <p>Securely sign out of the admin portal.</p>
            </a>
        </div>

        <div class="updates-section-main">
            <h2>Recent Admin Activity & System Alerts</h2>
            <div class="update-item-main">
                <i class="fas fa-info-circle update-icon-main"></i>
                <div class="update-content-main">
                    <h3>New User Registered: John Doe (Student)</h3>
                    <p>A new student account has been successfully created.</p>
                    <span class="date-main">July 17, 2025 - 05:30 PM</span>
                </div>
            </div>
            <div class="update-item-main">
                <i class="fas fa-exclamation-triangle update-icon-main"></i>
                <div class="update-content-main">
                    <h3>Warning: Database Backup Failed Last Night</h3>
                    <p>Please check the backup logs and ensure the next scheduled backup runs successfully.</p>
                    <span class="date-main">July 17, 2025 - 08:00 AM</span>
                </div>
            </div>
            <div class="update-item-main">
                <i class="fas fa-check-circle update-icon-main"></i>
                <div class="update-content-main">
                    <h3>Content Update: "Annual Sports Day" Published</h3>
                    <p>The new article about the Annual Sports Day has been published to the main feed.</p>
                    <span class="date-main">July 16, 2025 - 11:45 AM</span>
                </div>
            </div>
        </div>

        <div class="extra-text-section-main">
            <h2>Admin Resources & Guidelines</h2>
            <p>This Admin Dashboard is your central control panel for the entire student portal. It empowers you to manage content, users, and various features to ensure a seamless experience for all students and alumni. Please adhere to the following best practices:</p>
            <ul>
                <li>Regularly review user feedback to identify areas for improvement.</li>
                <li>Keep the content updated and relevant to the student community.</li>
                <li>Monitor system performance and address any alerts promptly.</li>
                <li>Ensure data privacy and security protocols are always maintained.</li>
                <li>Utilize the reporting tools to gain insights into portal usage.</li>
            </ul>
            <p>For any technical issues or further assistance, please refer to the comprehensive admin documentation or contact the development team.</p>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const body = document.body;
            const mainContent = document.getElementById('main-content');
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            const allDropdownParents = document.querySelectorAll('.dropdown-parent');
            const sidebarLinks = document.querySelectorAll('.sidebar-link');

            setTimeout(() => {
                sidebar.classList.add('loaded');
            }, 100);

            function closeAllDropdowns(exceptToggle = null) {
                allDropdownParents.forEach(parent => {
                    const toggle = parent.querySelector('.dropdown-toggle');
                    if (toggle !== exceptToggle && parent.classList.contains('active')) {
                        parent.classList.remove('active');
                        toggle.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            dropdownToggles.forEach(toggle => {
                toggle.addEventListener('click', function(event) {
                    event.preventDefault();
                    const parent = this.closest('.dropdown-parent');
                    if (!parent) return;

                    const isExpanded = parent.classList.contains('active');

                    if (!body.classList.contains('sidebar-collapsed')) {
                        closeAllDropdowns(this);
                    }

                    parent.classList.toggle('active');
                    this.setAttribute('aria-expanded', (!isExpanded).toString());
                });
            });
            
            if (menuToggle) {
                menuToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('active');
                    const icon = menuToggle.querySelector('i');
                    if (sidebar.classList.contains('active')) {
                        icon.classList.remove('fa-bars');
                        icon.classList.add('fa-times');
                    } else {
                        icon.classList.remove('fa-times');
                        icon.classList.add('fa-bars');
                    }
                    body.classList.toggle('sidebar-active');
                    closeAllDropdowns();
                });
            }
            
            if (mainContent) {
                mainContent.addEventListener('click', function(event) {
                    if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        if (menuToggle) {
                            const icon = menuToggle.querySelector('i');
                            icon.classList.remove('fa-times');
                            icon.classList.add('fa-bars');
                        }
                        body.classList.remove('sidebar-active');
                    }
                    if (!event.target.closest('.dropdown-parent') && !event.target.closest('#sidebar')) {
                        closeAllDropdowns();
                    }
                });
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    body.classList.toggle('sidebar-collapsed');
                    const icon = sidebarToggle.querySelector('i');
                    if (body.classList.contains('sidebar-collapsed')) {
                        icon.classList.remove('fa-chevron-left');
                        icon.classList.add('fa-chevron-right');
                    } else {
                        icon.classList.remove('fa-chevron-right');
                        icon.classList.add('fa-chevron-left');
                    }
                    if (body.classList.contains('sidebar-collapsed')) {
                        closeAllDropdowns();
                    }
                });
            }
            
            const currentPage = window.location.pathname.split('/').pop() || 'adhome.php';
            
            sidebarLinks.forEach((link, index) => {
                const linkHref = link.getAttribute('href');
                const normalizedLinkHref = linkHref ? (linkHref.includes('/') ? linkHref.split('/').pop() : linkHref) : '';

                link.style.setProperty('--nav-item-delay', `${index * 0.05}s`);

                if (normalizedLinkHref === currentPage) {
                    link.classList.add('active');
                    
                    if (link.closest('.dropdown')) {
                        const parentDropdown = link.closest('.dropdown-parent');
                        if (parentDropdown) {
                            parentDropdown.classList.add('active');
                            const toggle = parentDropdown.querySelector('.dropdown-toggle');
                            if (toggle) toggle.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            });

            function updateMainDateTime() {
                const dateDisplay = document.getElementById('dateDisplayMain');
                const timeDisplay = document.getElementById('timeDisplayMain');
                const now = new Date();

                const dateOptions = {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                };
                const formattedDate = now.toLocaleDateString('en-US', dateOptions);

                const timeOptions = {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                };
                const formattedTime = now.toLocaleTimeString('en-US', timeOptions);

                if (dateDisplay) dateDisplay.textContent = formattedDate;
                if (timeDisplay) timeDisplay.textContent = formattedTime;
            }

            updateMainDateTime();
            setInterval(updateMainDateTime, 1000);
        });
    </script>
</body>
</html>
