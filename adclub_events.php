<?php
session_start();
include 'admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Club Events</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
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
            --border-color: rgba(0, 188, 212, 0.3);
            --bg-blue: rgba(0, 188, 212, 0.05);
            --primary-pale: rgba(0, 188, 212, 0.1);
            --success-color: #10b981;
            --warning-color: #fbbf24;
            --error-color: #ef4444;
            --event-card-bg: rgba(255, 255, 255, 0.8);
            --event-title-color: #00838f;
        }

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

        .navbar {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(25px);
            border-bottom: 1px solid var(--border-color);
            padding: 15px 30px; /* Reduced padding */
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
            font-size: 1.6em; /* Reduced font size */
            font-weight: 800;
            color: var(--accent-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px; /* Reduced gap */
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .navbar-brand i {
            color: var(--accent-medium);
            font-size: 1em; /* Reduced font size */
        }

        .navbar-links {
            display: flex;
            gap: 18px; /* Reduced gap */
        }

        .navbar-links a {
            text-decoration: none;
            color: var(--accent-medium);
            font-weight: 600;
            font-size: 0.95em; /* Reduced font size */
            position: relative;
            transition: all 0.3s ease;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 5px; /* Reduced gap */
        }

        .navbar-links a::after {
            content: '';
            position: absolute;
            bottom: -6px; /* Adjusted position */
            left: 50%;
            width: 0;
            height: 2px; /* Reduced height */
            background: var(--gradient-accent);
            transition: all 0.3s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            transform: translateX(-50%);
            border-radius: 2px;
        }

        .navbar-links a:hover {
            color: var(--accent-dark);
            transform: translateY(-2px); /* Reduced translate */
        }

        .navbar-links a:hover::after {
            width: 100%;
        }

        .main-container {
            max-width: 760px; /* Reduced max-width */
            margin: 40px auto; /* Reduced margin */
            padding: 30px 15px; /* Reduced padding */
            text-align: center;
            flex-grow: 1;
            width: 100%;
        }

        h2 {
            font-size: 2.5rem; /* Reduced font size */
            font-weight: 800;
            margin-bottom: 20px; /* Reduced margin */
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Reduced shadow */
            background: linear-gradient(135deg, #00838f, #00acc1, #26c6da);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px; /* Reduced gap */
            animation: slideDown 1s ease-out forwards;
        }

        h2 i {
            font-size: 0.8em; /* Reduced font size */
            color: #00acc1;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-50px); } /* Adjusted translate */
            to { opacity: 1; transform: translateY(0); }
        }

        .events-list-container {
            width: 100%;
            margin-top: 25px; /* Reduced margin */
        }

        .event-container {
            background: var(--event-card-bg);
            backdrop-filter: blur(8px); /* Reduced blur */
            border: 1px solid var(--border-color);
            padding: 20px; /* Reduced padding */
            margin-bottom: 20px; /* Reduced margin */
            border-radius: 12px; /* Reduced border-radius */
            box-shadow: var(--shadow-medium);
            text-align: left;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .event-container:hover {
            transform: translateY(-3px); /* Reduced translate */
            box-shadow: var(--shadow-heavy);
        }

        .event-container h3 {
            margin-top: 0;
            margin-bottom: 12px; /* Reduced margin */
            color: var(--event-title-color);
            font-size: 1.5em; /* Reduced font size */
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px; /* Reduced gap */
        }

        .event-container h3 i {
            color: var(--accent-medium);
            font-size: 0.8em; /* Reduced font size */
        }

        .event-container p {
            margin-bottom: 8px; /* Reduced margin */
            line-height: 1.5; /* Reduced line height */
            color: var(--text-dark);
            font-size: 0.95em; /* Reduced font size */
        }

        .event-container p strong {
            color: var(--accent-dark);
            font-weight: 600;
        }

        .event-media {
            margin-top: 15px; /* Reduced margin */
            margin-bottom: 8px; /* Reduced margin */
            text-align: center;
            background-color: var(--primary-pale);
            padding: 12px; /* Reduced padding */
            border-radius: 8px; /* Reduced border-radius */
            border: 1px solid rgba(0, 188, 212, 0.2);
            overflow: hidden;
        }

        .event-media img, .event-media video {
            width: 100%;
            max-width: 600px; 
            display: block;
            margin: 0 auto;
            border-radius: 6px; /* Reduced border-radius */
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); /* Reduced shadow */
        }

        .event-media iframe {
            width: 100%;
            max-width: 500px; /* Reduced max-width */
            height: 280px; /* Reduced height */
            border: none;
            border-radius: 6px; /* Reduced border-radius */
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1); /* Reduced shadow */
        }

        .event-media p a {
            color: var(--accent-medium);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .event-media p a:hover {
            color: var(--accent-dark);
            text-decoration: underline;
        }

        .no-events-message {
            text-align: center;
            font-size: 1.2em; /* Reduced font size */
            color: var(--text-dark);
            margin-top: 40px; /* Reduced margin */
            padding: 18px; /* Reduced padding */
            background: rgba(255, 255, 255, 0.7);
            border-radius: 8px; /* Reduced border-radius */
            box-shadow: var(--shadow-light);
        }

        .footer {
            margin-top: auto;
            padding: 25px 20px; /* Reduced padding */
            text-align: center;
            color: var(--text-dark);
            font-size: 0.85em; /* Reduced font size */
            background: rgba(255, 255, 255, 0.1);
            width: 100%;
            border-top: 1px solid rgba(0, 188, 212, 0.1);
        }

        @media (max-width: 992px) {
            .navbar {
                padding: 15px 20px;
            }
            .navbar-brand {
                font-size: 1.5em;
            }
            .navbar-links a {
                font-size: 0.9em;
            }
            h2 {
                font-size: 2.2rem;
            }
            .main-container {
                padding: 25px 15px;
            }
            .event-container {
                padding: 18px;
            }
            .event-container h3 {
                font-size: 1.4em;
            }
            .event-container p {
                font-size: 0.9em;
            }
        }

        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 12px 15px; /* Further reduced padding */
            }
            .navbar-brand {
                margin-bottom: 12px; /* Further reduced margin */
            }
            .navbar-links {
                flex-direction: column;
                width: 100%;
                gap: 8px; /* Further reduced gap */
            }
            .navbar-links a {
                padding: 6px 0; /* Further reduced padding */
                font-size: 0.85em; /* Further reduced font size */
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
                width: 25%; /* Adjusted width */
            }

            h2 {
                font-size: 1.8rem; /* Further reduced font size */
                flex-direction: column;
                gap: 5px;
            }
            h2 i {
                font-size: 0.9em;
            }
            .main-container {
                margin: 30px auto; /* Further reduced margin */
                padding: 18px 10px; /* Further reduced padding */
            }
            .event-container {
                padding: 15px; /* Further reduced padding */
            }
            .event-container h3 {
                font-size: 1.2em; /* Further reduced font size */
            }
            .event-media iframe {
                height: 220px; /* Further reduced height */
            }
        }

        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 1.2em; /* Further reduced font size */
            }
            .navbar-links a {
                font-size: 0.8em; /* Further reduced font size */
            }
            h2 {
                font-size: 1.5rem; /* Further reduced font size */
            }
            .main-container {
                padding: 12px; /* Further reduced padding */
            }
            .event-container {
                padding: 12px; /* Further reduced padding */
            }
            .event-container h3 {
                font-size: 1em; /* Further reduced font size */
            }
            .event-container p {
                font-size: 0.85em; /* Further reduced font size */
            }
            .event-media iframe {
                height: 180px; /* Further reduced height */
            }
        }

        .i{
            margin-left: 270px; 
        }
    </style>
</head>
<body class="i">
    <nav class="navbar">
        <a href="adclub_home.php" class="navbar-brand">
            <i class="fas fa-users-crown"></i> Talent Post
        </a>
        <div class="navbar-links">
            <a href="adclub_home.php"><i class="fas fa-home"></i> Home</a>
            <a href="adclub_post.php"><i class="fas fa-plus-circle"></i> Submit Club Post</a>
            <a href="adclub_posts.php"><i class="fas fa-th-list"></i> View Club Posts</a>
            <a href="adclub_event_post.php"><i class="fas fa-calendar-plus"></i> Submit Event</a>
            <a href="adclub_events.php"><i class="fas fa-calendar-alt"></i> View Events</a>
        </div>
    </nav>

    <div class="main-container">
        <h2>
            <i class="fas fa-calendar-alt"></i> Upcoming Club Events
        </h2>

        <div class="events-list-container">
            <?php
            $club_events_json_file = 'club_events.json';
            $club_events = [];

            if (file_exists($club_events_json_file)) {
                $json_data = file_get_contents($club_events_json_file);
                $club_events = json_decode($json_data, true);
                if ($club_events === null) {
                    $club_events = [];
                }
            }

            if (empty($club_events)) {
                echo "<p class='no-events-message'><i class='fas fa-exclamation-circle'></i> No upcoming club events yet. Be the first to add one!</p>";
            } else {
                // Filter out past events and sort upcoming events by date
                $current_timestamp = time();
                $upcoming_events = array_filter($club_events, function($event) use ($current_timestamp) {
                    // Combine date and time into a single timestamp for comparison
                    $event_datetime_str = $event['event_date'] . ' ' . $event['event_time'];
                    $event_timestamp = strtotime($event_datetime_str);
                    return $event_timestamp >= $current_timestamp;
                });

                // Sort upcoming events chronologically by date and time
                usort($upcoming_events, function($a, $b) {
                    $datetime_a = strtotime($a['event_date'] . ' ' . $a['event_time']);
                    $datetime_b = strtotime($b['event_date'] . ' ' . $b['event_time']);
                    return $datetime_a - $datetime_b;
                });


                if (empty($upcoming_events)) {
                    echo "<p class='no-events-message'><i class='fas fa-calendar-times'></i> No upcoming club events. All events have passed.</p>";
                } else {
                    foreach ($upcoming_events as $event) {
                        echo "<div class='event-container'>";
                        echo "<h3><i class='fas fa-calendar-day'></i> " . htmlspecialchars($event['event_title']) . " - " . htmlspecialchars($event['club_name']) . "</h3>";
                        echo "<p><strong><i class='fas fa-calendar-alt'></i> Date:</strong> " . htmlspecialchars($event['event_date']) . "</p>";
                        echo "<p><strong><i class='fas fa-clock'></i> Time:</strong> " . htmlspecialchars($event['event_time']) . "</p>";
                        echo "<p><strong><i class='fas fa-map-marker-alt'></i> Location:</strong> " . nl2br(htmlspecialchars($event['location'])) . "</p>";
                        echo "<p><strong><i class='fas fa-info-circle'></i> Description:</strong> " . nl2br(htmlspecialchars($event['description'])) . "</p>";

                        echo "<div class='event-media'>";
                        if ($event['media_type'] === 'image') {
                            echo "<img src='" . htmlspecialchars($event['media_path']) . "' alt='Event Image'>";
                        } elseif ($event['media_type'] === 'video') {
                            echo "<video controls><source src='" . htmlspecialchars($event['media_path']) . "' type='video/mp4'>Your browser does not support the video tag.</video>";
                        } elseif ($event['media_type'] === 'link') {
                            $link = htmlspecialchars($event['media_path']);
                            // The original code had incorrect hardcoded values '1' and '2' in YouTube URLs.
                            // This part of the code has been corrected to properly extract and embed YouTube/Vimeo video IDs.
                            if (strpos($link, 'youtube.com/watch?v=') !== false) {
                                $video_id = explode('v=', $link)[1];
                                $video_id = explode('&', $video_id)[0];
                                echo "<iframe src='https://www.youtube.com/embed/" . $video_id . "' allowfullscreen></iframe>";
                            } elseif (strpos($link, 'youtu.be/') !== false) {
                                $video_id = explode('youtu.be/', $link)[1];
                                $video_id = explode('?', $video_id)[0]; // Remove any query parameters
                                echo "<iframe src='https://www.youtube.com/embed/" . $video_id . "' allowfullscreen></iframe>";
                            } elseif (strpos($link, 'vimeo.com/') !== false) {
                                $video_id = explode('vimeo.com/', $link)[1];
                                echo "<iframe src='https://player.vimeo.com/video/" . $video_id . "' allowfullscreen></iframe>";
                            } else {
                                echo "<p><i class='fas fa-link'></i> <a href='" . $link . "' target='_blank'>" . $link . "</a></p>";
                            }
                        }
                        echo "</div>";
                        echo "</div>";
                    }
                }
            }
            ?>
        </div>
    </div>

    <footer class="footer">
        <p><i class="fas fa-copyright"></i> 2025 Talent Post. All rights reserved.</p>
    </footer>
</body>
</html>