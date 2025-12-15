<?php
session_start();
include 'admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talent Post - Club Posts</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* --- Variables --- */
        :root {
            --primary-light: #e0f7fa;
            --primary-medium: #b2ebf2;
            --primary-dark: #80deea;
            --accent-light: #00bcd4;
            --accent-medium: #00acc1;
            --accent-dark: #00838f;
            --text-dark: #0d4650;
            --text-green: #00695c;
            --gradient-1: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-medium) 50%, var(--primary-dark) 100%);
            --gradient-accent: linear-gradient(90deg, #00bcd4, #26c6da);
            --shadow-light: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-medium: 0 8px 30px rgba(0, 188, 212, 0.15);
            --shadow-heavy: 0 15px 45px rgba(0, 0, 0, 0.25);
        }

        /* --- Base Styles --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--gradient-1);
            min-height: 100vh;
            color: var(--text-dark);
            position: relative;
            overflow-x: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Animated background elements */
        body::before, body::after {
            content: '';
            position: fixed;
            border-radius: 50%;
            background: rgba(0, 188, 212, 0.08);
            z-index: -1;
            filter: blur(10px);
        }

        body::before {
            top: -10%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            animation: floatShape 25s ease-in-out infinite;
        }

        body::after {
            bottom: -10%;
            right: -10%;
            width: 60vw;
            height: 60vw;
            animation: floatShape 20s ease-in-out infinite reverse;
        }

        @keyframes floatShape {
            0%, 100% { transform: translate(0, 0) scale(1); opacity: 0.7; }
            50% { transform: translate(20px, 30px) scale(1.05); opacity: 0.4; }
        }

        /* --- Navbar Styles --- */
        .navbar {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(0, 188, 212, 0.3);
            padding: 18px 40px;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: var(--shadow-medium);
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 1.8em;
            font-weight: 800;
            color: var(--accent-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .navbar-brand i {
            color: var(--accent-medium);
            font-size: 1.1em;
        }

        .navbar-links {
            display: flex;
            gap: 22px;
        }

        .navbar-links a {
            text-decoration: none;
            color: var(--accent-medium);
            font-weight: 600;
            font-size: 1.05em;
            position: relative;
            transition: all 0.3s ease;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            display: flex; /* Added for icon alignment */
            align-items: center; /* Added for icon alignment */
            gap: 6px; /* Space between icon and text */
        }

        .navbar-links a::after {
            content: '';
            position: absolute;
            bottom: -7px;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--gradient-accent);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .navbar-links a:hover {
            color: var(--accent-dark);
            transform: translateY(-3px);
        }

        .navbar-links a:hover::after {
            width: 100%;
        }

        /* --- Main Content Styles --- */
        .main-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 40px 20px;
            text-align: center;
            flex-grow: 1;
            width: 100%; /* Ensure it takes full width within its max-width */
        }

        h1 {
            font-size: 4.2rem;
            font-weight: 800;
            margin-bottom: 25px;
            text-shadow: 0 5px 12px rgba(0, 0, 0, 0.12);
            background: linear-gradient(135deg, #00838f, #00acc1, #26c6da);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 15px;
            animation: slideDown 1s ease-out forwards;
        }

        h1 i {
            font-size: 0.9em;
            color: #00acc1;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-60px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .subtitle {
            font-size: 1.5rem;
            color: var(--text-green);
            margin-bottom: 70px;
            max-width: 850px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.7;
            animation: fadeIn 1.2s ease-out 0.4s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Action Buttons Grid --- */
        .action-buttons-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); /* Responsive grid columns */
            gap: 30px;
            margin-top: 50px;
            padding: 0 20px; /* Add some horizontal padding */
            justify-content: center; /* Center grid items */
        }

        .action-button {
            background: var(--gradient-accent);
            color: #fff;
            padding: 20px 30px;
            border: none;
            border-radius: 10px;
            font-size: 1.2em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-medium);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            text-decoration: none; /* For when converted to anchor */
            text-align: center;
        }

        .action-button:hover {
            transform: translateY(-5px) scale(1.03);
            box-shadow: var(--shadow-heavy);
            background: linear-gradient(90deg, #26c6da, #00bcd4); /* Slight gradient shift on hover */
        }

        .action-button i {
            font-size: 1.4em;
        }

        /* Example specific button styles for visual distinction */
        .submit-club-button { background: linear-gradient(135deg, #00acc1, #00bcd4); }
        .view-club-posts-button { background: linear-gradient(135deg, #29b6f6, #03a9f4); }
        .submit-event-button { background: linear-gradient(135deg, #42a5f5, #2196f3); }
        .view-events-button { background: linear-gradient(135deg, #66bb6a, #4caf50); }

        .submit-club-button:hover { background: linear-gradient(135deg, #00bcd4, #00acc1); }
        .view-club-posts-button:hover { background: linear-gradient(135deg, #03a9f4, #29b6f6); }
        .submit-event-button:hover { background: linear-gradient(135deg, #2196f3, #42a5f5); }
        .view-events-button:hover { background: linear-gradient(135deg, #4caf50, #66bb6a); }

        /* --- Footer (Optional but good practice) --- */
        .footer {
            margin-top: auto; /* Pushes footer to the bottom */
            padding: 30px 20px;
            text-align: center;
            color: var(--text-dark);
            font-size: 0.9em;
            background: rgba(255, 255, 255, 0.1);
            width: 100%;
            border-top: 1px solid rgba(0, 188, 212, 0.1);
        }

        /* --- Responsive Design --- */
        @media (max-width: 992px) {
            .navbar {
                padding: 18px 20px;
            }
            .navbar-brand {
                font-size: 1.6em;
            }
            .navbar-links a {
                font-size: 1em;
            }
            h1 {
                font-size: 3.5rem;
            }
            .subtitle {
                font-size: 1.3rem;
            }
            .action-button {
                font-size: 1.1em;
                padding: 18px 25px;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 15px 20px;
            }
            .navbar-brand {
                margin-bottom: 15px;
            }
            .navbar-links {
                flex-direction: column;
                width: 100%;
                gap: 10px;
            }
            .navbar-links a {
                padding: 8px 0;
                text-align: left;
                width: 100%;
                border-bottom: 1px solid rgba(0, 188, 212, 0.1);
            }
            .navbar-links a:last-child {
                border-bottom: none;
            }
            .navbar-links a::after {
                left: 0;
                transform: translateX(0);
                width: 0;
            }
            .navbar-links a:hover::after {
                width: 30%;
            }

            h1 {
                font-size: 2.8rem;
                flex-direction: column;
                gap: 5px;
            }
            h1 i {
                font-size: 1em;
            }
            .subtitle {
                font-size: 1.1rem;
                padding: 0 15px;
                margin-bottom: 50px;
            }
            .action-buttons-grid {
                grid-template-columns: 1fr; /* Single column on smaller screens */
                padding: 0 15px;
                gap: 25px;
            }
            .action-button {
                padding: 20px;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 1.4em;
            }
            .navbar-links a {
                font-size: 0.9em;
            }
            h1 {
                font-size: 2.2rem;
            }
            .subtitle {
                font-size: 1rem;
            }
            .main-container {
                padding: 40px 10px;
            }
            .action-button {
                font-size: 1em;
                padding: 15px;
            }
        }

        .i{
            margin-left: 270px;
        }
    </style>
</head>
<body class="i">
    <nav class="navbar">
        <a href="club_home.php" class="navbar-brand">
            <i class="fas fa-users-crown"></i> Talent Post
        </a>
        <div class="navbar-links">
            <div class="navbar-links">
            <a href="adclub_home.php"><i class="fas fa-home"></i> Home</a>
            <a href="adclub_post.php"><i class="fas fa-plus-circle"></i> Submit Club Post</a>
            <a href="adclub_posts.php"><i class="fas fa-th-list"></i> View Club Posts</a>
            <a href="adclub_event_post.php"><i class="fas fa-calendar-plus"></i> Submit Event</a>
            <a href="adclub_events.php"><i class="fas fa-calendar-alt"></i> View Events</a>
        </div>
        </div>
    </nav>

    <div class="main-container">
        <h1>
            <i class="fas fa-th-list"></i> Club Posts
        </h1>
        <p class="subtitle">
            Explore the latest updates, projects, and achievements from various clubs on campus. Get inspired and see what the community is creating!
        </p>

        <div class="action-buttons-grid">
            <a href="adclub_post.php" class="action-button submit-club-button">
                <i class="fas fa-paper-plane"></i> Submit Club Post
            </a>
            <a href="adclub_posts.php" class="action-button view-club-posts-button">
                <i class="fas fa-eye"></i> View Club Posts
            </a>
            <a href="adclub_event_post.php" class="action-button submit-event-button">
                <i class="fas fa-bullhorn"></i> Submit Event
            </a>
            <a href="adclub_events.php" class="action-button view-events-button">
                <i class="fas fa-calendar-check"></i> View Events
            </a>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Talent Post. All rights reserved.</p>
    </footer>
</body>
</html>