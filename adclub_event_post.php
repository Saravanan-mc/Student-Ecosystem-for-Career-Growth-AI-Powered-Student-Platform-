<?php
// Define the JSON file path
$club_events_json_file = 'club_events.json';

// Function to sanitize input data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Initialize message variables
$success_message = '';
$error_message = '';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and get form data
    $club_name = sanitize_input($_POST['club_name']);
    $event_title = sanitize_input($_POST['event_title']);
    $event_date = sanitize_input($_POST['event_date']);
    $event_time = sanitize_input($_POST['event_time']);
    $location = sanitize_input($_POST['location']);
    $description = sanitize_input($_POST['description']);
    $media_link = sanitize_input($_POST['media_link']);

    // Basic validation
    if (empty($club_name) || empty($event_title) || empty($event_date) || empty($event_time) || empty($location) || empty($description)) {
        $error_message = "All required fields must be filled.";
    } else {
        $media_type = '';
        $media_path = '';

        // Handle file upload
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "uploads/";
            // Create uploads directory if it doesn't exist
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            $file_name = basename($_FILES["media_file"]["name"]);
            $target_file = $target_dir . uniqid() . '_' . $file_name; // Add unique ID to prevent overwrites
            $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

            // Check file size (max 40MB)
            if ($_FILES["media_file"]["size"] > 40 * 1024 * 1024) {
                $error_message = "Sorry, your file is too large. Max 40MB allowed.";
            } else {
                // Allow certain file formats
                $allowed_image_types = ['jpg', 'jpeg', 'png', 'gif'];
                $allowed_video_types = ['mp4', 'mov', 'avi', 'webm'];
                
                if (in_array($image_file_type, $allowed_image_types)) {
                    $media_type = 'image';
                } elseif (in_array($image_file_type, $allowed_video_types)) {
                    $media_type = 'video';
                } else {
                    $error_message = "Sorry, only JPG, JPEG, PNG, GIF, MP4, MOV, AVI, WEBM files are allowed for upload.";
                }

                if (empty($error_message)) {
                    if (move_uploaded_file($_FILES["media_file"]["tmp_name"], $target_file)) {
                        $media_path = $target_file;
                        $success_message = "The event has been successfully submitted and media uploaded.";
                    } else {
                        $error_message = "Sorry, there was an error uploading your file.";
                    }
                }
            }
        } elseif (!empty($media_link)) {
            // Handle external link
            if (filter_var($media_link, FILTER_VALIDATE_URL)) {
                $media_type = 'link';
                $media_path = $media_link;
                $success_message = "The event has been successfully submitted with the provided link.";
            } else {
                $error_message = "The provided external link is not a valid URL.";
            }
        } else {
            // No media uploaded or link provided, that's fine as it's optional
            $success_message = "The event has been successfully submitted.";
        }

        // If no upload/link error, proceed to save event data
        if (empty($error_message)) {
            // Create new event array
            $new_event = [
                'club_name' => $club_name,
                'event_title' => $event_title,
                'event_date' => $event_date,
                'event_time' => $event_time,
                'location' => $location,
                'description' => $description,
                'media_type' => $media_type,
                'media_path' => $media_path,
                'timestamp' => time() // Add a timestamp for unique identification and sorting
            ];

            // Read existing events
            $club_events = [];
            if (file_exists($club_events_json_file)) {
                $json_data = file_get_contents($club_events_json_file);
                $club_events = json_decode($json_data, true);
                if ($club_events === null) {
                    $club_events = []; // Initialize as empty array if JSON is invalid
                }
            }

            // Add new event
            $club_events[] = $new_event;

            // Save updated events back to JSON file
            if (file_put_contents($club_events_json_file, json_encode($club_events, JSON_PRETTY_PRINT))) {
                // Success message already set by media handling, or default if no media
                // Clear form fields after successful submission
                $_POST = array(); 
            } else {
                $error_message = "Error saving event data.";
            }
        }
    }
}

include 'admin.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Upcoming Club Event</title>
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
            --border-color: rgba(0, 188, 212, 0.3);
            --bg-blue: rgba(0, 188, 212, 0.05);
            --primary-pale: rgba(0, 188, 212, 0.1);
            --success-color: #10b981;
            --warning-color: #fbbf24;
            --error-color: #ef4444;
            --upload-border-hover: #00acc1; /* New variable for upload area hover */
            --event-button-gradient: linear-gradient(135deg, #2196F3, #64B5F6); /* New gradient for event button */
            --event-button-hover-gradient: linear-gradient(135deg, #1976D2, #42A5F5); /* New gradient for event button hover */
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
            border-bottom: 1px solid var(--border-color);
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
            display: flex;
            align-items: center;
            gap: 6px;
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
            max-width: 900px;
            margin: 60px auto;
            padding: 40px 20px;
            text-align: center;
            flex-grow: 1;
            width: 100%;
        }

        h2 {
            font-size: 3.2rem;
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

        h2 i {
            font-size: 0.9em;
            color: #00acc1;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-60px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Form Styles --- */
        .form-container {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: var(--shadow-medium);
            text-align: left;
            border: 1px solid rgba(0, 188, 212, 0.2);
            animation: formAppear 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes formAppear {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 1.1em;
            display: flex; /* Added for icon alignment */
            align-items: center; /* Added for icon alignment */
            gap: 8px; /* Space between icon and text */
        }

        input[type="text"],
        input[type="url"],
        input[type="date"],
        input[type="time"],
        textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--primary-dark);
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-dark);
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="url"]:focus,
        input[type="date"]:focus,
        input[type="time"]:focus,
        textarea:focus {
            border-color: var(--accent-medium);
            box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.2);
            outline: none;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        /* Custom File Input Styles - Enhanced with "lines" concept */
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: flex; /* Changed to flex for centering content */
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-bottom: 15px;
            border: 2px dashed var(--border-color); /* Dashed border for drag-and-drop */
            border-radius: 10px;
            background-color: var(--bg-blue);
            transition: all 0.3s ease;
            padding: 30px 20px; /* Increased padding for more space */
            text-align: center;
            cursor: pointer; /* Indicate it's clickable */
            min-height: 180px; /* Ensure enough height for content */
        }

        .file-input-wrapper:hover {
            border-color: var(--upload-border-hover);
            background-color: rgba(0, 188, 212, 0.08);
            box-shadow: 0 0 0 4px rgba(0, 188, 212, 0.1);
        }

        .file-input-wrapper.dragover {
            border-color: var(--success-color); /* Green border on dragover */
            background-color: rgba(16, 185, 129, 0.1); /* Lighter green background */
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        }

        .file-input-wrapper input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            cursor: pointer;
            width: 100%;
            height: 100%;
            font-size: 0;
            z-index: 10;
        }

        /* Stylized upload icon (lines concept) */
        .upload-icon-container {
            position: relative;
            width: 60px;
            height: 60px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-medium);
            transition: color 0.3s ease;
        }

        .file-input-wrapper.dragover .upload-icon-container {
            color: var(--success-color);
        }

        .upload-icon-container .fas {
            font-size: 2.8em; /* Larger icon */
            transition: transform 0.3s ease;
        }

        .file-input-wrapper:hover .upload-icon-container .fas {
            transform: translateY(-5px);
        }

        .file-input-button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #00bcd4, #26c6da);
            color: white;
            padding: 12px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--accent-medium);
            width: auto;
            margin-top: 15px; /* Adjust margin for better spacing */
        }

        .file-input-button:hover {
            background: linear-gradient(135deg, #00acc1, #00bcd4);
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .file-input-button i {
            font-size: 1.1em;
        }

        .file-name {
            margin-top: 10px;
            font-size: 0.95em;
            color: var(--text-dark); /* Default color */
            font-weight: 500;
            text-align: center;
            width: 100%;
            display: block;
            min-height: 1.2em;
            transition: color 0.3s ease;
        }

        .file-name.selected {
            color: var(--success-color); /* Green text for selected file */
        }

        .file-upload-info {
            font-size: 0.85em;
            color: var(--text-dark);
            margin-top: 5px;
            text-align: center;
            line-height: 1.4;
            max-width: 80%; /* Limit width for better readability */
        }

        .submit-button {
            background: var(--event-button-gradient); /* Use new event button gradient */
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1.2em;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: var(--shadow-light);
            display: block; /* Changed to block to allow margin auto for centering */
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: fit-content; /* Adjust width to content */
            margin: 20px auto 0 auto; /* Center the button */
        }

        .submit-button:hover {
            background: var(--event-button-hover-gradient); /* Use new event button hover gradient */
            transform: translateY(-3px) scale(1.01);
            box-shadow: var(--shadow-medium);
        }

        .submit-button i {
            font-size: 1.1em;
        }

        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            animation: fadeIn 0.5s ease-out;
        }

        .success {
            background-color: rgba(16, 185, 129, 0.15);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .error {
            background-color: rgba(239, 68, 68, 0.15);
            color: var(--error-color);
            border: 1px solid var(--error-color);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* --- Footer --- */
        .footer {
            margin-top: auto;
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
            h2 {
                font-size: 2.8rem;
            }
            .main-container {
                padding: 30px 15px;
            }
            .form-container {
                padding: 25px;
            }
            label {
                font-size: 1em;
            }
            input[type="text"],
            input[type="url"],
            input[type="date"],
            input[type="time"],
            textarea {
                padding: 10px 12px;
            }
            .submit-button {
                padding: 12px 25px;
                font-size: 1.1em;
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

            h2 {
                font-size: 2.2rem;
                flex-direction: column;
                gap: 5px;
            }
            h2 i {
                font-size: 1em;
            }
            .main-container {
                margin: 40px auto;
                padding: 20px 10px;
            }
            .form-container {
                padding: 20px;
            }
            .submit-button {
                width: 100%;
                margin-left: 0; /* Full width and centered */
                margin-right: 0;
            }
        }

        @media (max-width: 480px) {
            .navbar-brand {
                font-size: 1.4em;
            }
            .navbar-links a {
                font-size: 0.9em;
            }
            h2 {
                font-size: 1.8rem;
            }
            .form-container {
                padding: 15px;
            }
            label {
                font-size: 0.95em;
            }
            input[type="text"],
            input[type="url"],
            input[type="date"],
            input[type="time"],
            textarea {
                padding: 8px 10px;
                font-size: 0.9em;
            }
            .submit-button {
                font-size: 1em;
                padding: 10px 20px;
            }
            .file-input-wrapper {
                padding: 20px 15px; /* Adjust padding for smaller screens */
                min-height: 150px;
            }
            .upload-icon-container .fas {
                font-size: 2.2em;
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
            <a href="adclub_home.php"><i class="fas fa-home"></i> Home</a>
            <a href="adclub_post.php"><i class="fas fa-plus-circle"></i> Submit Club Post</a>
            <a href="adclub_posts.php"><i class="fas fa-th-list"></i> View Club Posts</a>
            <a href="adclub_event_post.php"><i class="fas fa-calendar-plus"></i> Submit Event</a>
            <a href="adclub_events.php"><i class="fas fa-calendar-alt"></i> View Events</a>
        </div>
    </nav>

    <div class="main-container">
        <h2>
            <i class="fas fa-calendar-alt"></i> Submit Upcoming Club Event
        </h2>

        <div class="form-container">
            <?php
            // Display success or error messages
            if (!empty($success_message)) {
                echo "<div class='message success'>" . $success_message . "</div>";
            }
            if (!empty($error_message)) {
                echo "<div class='message error'>" . $error_message . "</div>";
            }
            ?>
            <form action="club_event_post.php" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="club_name"><i class="fas fa-users"></i> Club Name:</label>
                    <input type="text" id="club_name" name="club_name" value="<?php echo isset($_POST['club_name']) ? htmlspecialchars($_POST['club_name']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_title"><i class="fas fa-heading"></i> Event Title:</label>
                    <input type="text" id="event_title" name="event_title" value="<?php echo isset($_POST['event_title']) ? htmlspecialchars($_POST['event_title']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_date"><i class="fas fa-calendar-day"></i> Event Date:</label>
                    <input type="date" id="event_date" name="event_date" value="<?php echo isset($_POST['event_date']) ? htmlspecialchars($_POST['event_date']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="event_time"><i class="fas fa-clock"></i> Event Time:</label>
                    <input type="time" id="event_time" name="event_time" value="<?php echo isset($_POST['event_time']) ? htmlspecialchars($_POST['event_time']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="location"><i class="fas fa-map-marker-alt"></i> Location:</label>
                    <input type="text" id="location" name="location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-info-circle"></i> Event Description:</label>
                    <textarea id="description" name="description" rows="5" required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <p style="margin-bottom: 10px; color: var(--text-dark); font-weight: 600;"><i class="fas fa-photo-video"></i> Event Media (Optional - Upload image/video or provide an external link):</p>
                <div class="form-group">
                    <div class="file-input-wrapper" id="file-input-wrapper">
                        <input type="file" id="media_file" name="media_file" accept="image/*,video/*" capture="camera">
                        <div class="upload-icon-container">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <p class="file-upload-info">Drag & drop files here, or click the button below.</p>
                        <label for="media_file" class="file-input-button">
                            <i class="fas fa-upload"></i> Choose File
                        </label>
                        <span id="file-name" class="file-name">No file chosen</span>
                        <p class="file-upload-info" style="margin-top: 15px;">Accepted formats: Images (JPG, PNG, GIF), Videos (MP4, MOV). Max size: 40MB.</p>
                    </div>
                </div>

                <div class="form-group">
                    <label for="media_link"><i class="fas fa-link"></i> Or provide an External Link (YouTube, Vimeo, etc.):</label>
                    <input type="url" id="media_link" name="media_link" placeholder="e.g., https://www.youtube.com/watch?v=dQw4w9WgXcQ" value="<?php echo isset($_POST['media_link']) ? htmlspecialchars($_POST['media_link']) : ''; ?>">
                </div>

                <button type="submit" class="submit-button">
                    <i class="fas fa-calendar-check"></i> Submit Event
                </button>
            </form>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Talent Post. All rights reserved.</p>
    </footer>

    <script>
        const fileInput = document.getElementById('media_file');
        const fileNameSpan = document.getElementById('file-name');
        const fileInputWrapper = document.getElementById('file-input-wrapper');
        const mediaLinkInput = document.getElementById('media_link');

        // Function to update file name display
        function updateFileNameDisplay() {
            if (fileInput.files && fileInput.files.length > 0) {
                fileNameSpan.textContent = fileInput.files[0].name;
                fileNameSpan.classList.add('selected'); // Add class for green text
            } else {
                fileNameSpan.textContent = 'No file chosen';
                fileNameSpan.classList.remove('selected'); // Remove class to reset color
            }
        }

        // Update file name display on change
        fileInput.addEventListener('change', function() {
            updateFileNameDisplay();
            // If a file is chosen, clear the media link input
            if (this.files && this.files.length > 0) {
                mediaLinkInput.value = '';
            }
        });

        // Clear file input if media link is typed
        mediaLinkInput.addEventListener('input', function() {
            if (this.value !== '') {
                fileInput.value = ''; // Clear selected file
                updateFileNameDisplay(); // Update display to "No file chosen"
            }
        });

        // Drag and drop functionality
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            fileInputWrapper.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            fileInputWrapper.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            fileInputWrapper.addEventListener(eventName, unhighlight, false);
        });

        function highlight(e) {
            fileInputWrapper.classList.add('dragover');
        }

        function unhighlight(e) {
            fileInputWrapper.classList.remove('dragover');
        }

        fileInputWrapper.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            // Assign dropped files to the input
            fileInput.files = files;
            // Trigger change event to update the file name display and clear link
            fileInput.dispatchEvent(new Event('change'));
        }

        // Retain form data on refresh/back button (basic approach)
        window.onload = function() {
            updateFileNameDisplay(); // Ensure file name is correct on load if form was pre-filled
        };
    </script>
</body>
</html>