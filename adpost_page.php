<?php
session_start();

// --- Functions (formerly in config.php) ---

// Function to get the base path of the application
function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = dirname($scriptName);
    if ($scriptDir === '/') {
        return '';
    }
    return rtrim($scriptDir, '/');
}

// Function to handle file uploads
function handleFileUpload($fileInput, $targetDir) {
    // Create the target directory if it doesn't exist
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $fileName = basename($fileInput['name']);
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    // Allow certain file formats
    $allowTypes = array('jpg', 'png', 'jpeg', 'gif', 'webp', 'mp4', 'webm', 'ogg');
    if (!in_array($fileType, $allowTypes)) {
        return 'Error: Invalid file type. Only JPG, PNG, GIF, WebP, MP4, WebM, Ogg are allowed.';
    }

    // Check file size (max 40MB)
    if ($fileInput['size'] > 40 * 1024 * 1024) { // 40 MB in bytes
        return 'Error: File size exceeds 40MB limit.';
    }

    // Generate a unique filename to prevent overwrites
    $uniqueFileName = uniqid() . '_' . $fileName;
    $targetFilePath = $targetDir . $uniqueFileName;

    // Upload file to server
    if (move_uploaded_file($fileInput['tmp_name'], $targetFilePath)) {
        // Return path relative to the base of the application's data directory,
        // which will now be 'admin_data_post/uploads/'
        return $targetFilePath; // Return full path, will be processed to relative path later
    } else {
        return 'Error uploading file. Error code: ' . $fileInput['error'];
    }
}

/**
 * Reads data from a JSON file.
 *
 * @param string $filePath The full path to the JSON file.
 * @return array The decoded data, or an empty array if the file doesn't exist or is invalid.
 */
function readJsonFile($filePath) {
    if (file_exists($filePath)) {
        $jsonContent = file_get_contents($filePath);
        $data = json_decode($jsonContent, true);
        if (is_array($data)) {
            return $data;
        }
    }
    return [];
}

/**
 * Writes data to a JSON file.
 *
 * @param string $filePath The full path to the JSON file.
 * @param array $data The data to encode and write.
 * @return bool True on success, false on failure.
 */
function writeJsonFile($filePath, $data) {
    return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

// --- End Functions ---

// Get the base path for navigation
$basePath = getBasePath();

$message = ''; // To store success or error messages
$messageType = ''; // To determine the styling of the message (success/error)

// Define the base directory for admin data and uploads
$admin_data_post_dir = __DIR__ . '/admin_data_post/';
$admin_uploads_dir = $admin_data_post_dir . 'uploads/';

// Ensure the admin data and uploads directories exist
if (!is_dir($admin_data_post_dir)) {
    mkdir($admin_data_post_dir, 0777, true);
}
if (!is_dir($admin_uploads_dir)) {
    mkdir($admin_uploads_dir, 0777, true);
}

// Define file path for storing admin posts
$admin_posts_file_path = $admin_data_post_dir . 'admin_posts.json';

// Determine if we are editing an existing item or creating a new one
$itemId = $_GET['id'] ?? null;
$itemData = []; // Data for the item being edited

if ($itemId) {
    $items = readJsonFile($admin_posts_file_path);

    foreach ($items as $item) {
        if ($item['id'] === $itemId) {
            $itemData = $item;
            break;
        }
    }

    if (empty($itemData)) {
        $message = 'Item not found for editing.';
        $messageType = 'error';
        $itemId = null; // Clear item ID if not found
    }
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve form data
    $form_item_id = htmlspecialchars($_POST['id'] ?? ''); // Hidden field for existing item ID
    $roll_number = htmlspecialchars($_POST['roll_number'] ?? '');
    $poster_name = htmlspecialchars($_POST['poster_name'] ?? '');
    $email = htmlspecialchars($_POST['email'] ?? '');
    $phone_number = htmlspecialchars($_POST['phone_number'] ?? '');
    $title = htmlspecialchars($_POST['title'] ?? '');
    $description = htmlspecialchars($_POST['description'] ?? '');
    $location = htmlspecialchars($_POST['location'] ?? '');
    $date = htmlspecialchars($_POST['date'] ?? date('Y-m-d'));
    $status = htmlspecialchars($_POST['status'] ?? 'pending'); // New field for status

    // Basic validation for required fields
    if (empty($roll_number) || empty($poster_name) || empty($email) || empty($title) || empty($description) || empty($location)) {
        $message = 'Roll Number, Name, Email, Title, Description, and Location are required.';
        $messageType = 'error';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
        $messageType = 'error';
    } else {
        $media_filename_relative_path = $itemData['media_path'] ?? null; // Keep existing media if not uploading new

        // Handle file upload if a new file was provided
        if (isset($_FILES['media_file']) && $_FILES['media_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload_result_full_path = handleFileUpload($_FILES['media_file'], $admin_uploads_dir);
            if (is_string($upload_result_full_path) && (strpos($upload_result_full_path, 'Failed') === 0 || strpos($upload_result_full_path, 'Error') === 0 || strpos($upload_result_full_path, 'exceeds') === 0 || strpos($upload_result_full_path, 'Invalid') === 0)) {
                $message = $upload_result_full_path;
                $messageType = 'error';
            } else {
                // Delete old file if a new one is uploaded and old one exists
                if ($media_filename_relative_path && file_exists($admin_data_post_dir . $media_filename_relative_path)) {
                    unlink($admin_data_post_dir . $media_filename_relative_path);
                }
                // Convert full path to relative path for storage in JSON
                $media_filename_relative_path = 'uploads/' . basename($upload_result_full_path);
            }
        } else if (isset($_POST['remove_media']) && $_POST['remove_media'] === 'true') {
            // Option to remove existing media
            if ($media_filename_relative_path && file_exists($admin_data_post_dir . $media_filename_relative_path)) {
                unlink($admin_data_post_dir . $media_filename_relative_path);
            }
            $media_filename_relative_path = null;
        }

        // Only proceed to save if there was no file upload error (or no file was uploaded)
        if ($messageType !== 'error') {
            // Create or update item array
            $newItem = [
                'id' => $form_item_id ?: uniqid(), // Use existing ID or generate new
                'roll_number' => $roll_number,
                'poster_name' => $poster_name,
                'email' => $email,
                'phone_number' => $phone_number,
                'title' => $title,
                'description' => $description,
                'location' => $location,
                'date' => $date,
                'media_path' => $media_filename_relative_path, // Store relative path
                'timestamp' => date('Y-m-d H:i:s'),
                'status' => $status // Save status
            ];

            // Read existing data from the JSON file
            $existingData = readJsonFile($admin_posts_file_path);

            if ($form_item_id) {
                // Update existing item
                $found = false;
                foreach ($existingData as $key => $item) {
                    if ($item['id'] === $form_item_id) {
                        $existingData[$key] = $newItem;
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $existingData[] = $newItem; // Add if somehow not found (shouldn't happen with correct ID)
                }
                $message_text = 'Admin post updated successfully!';
            } else {
                // Add new item
                $existingData[] = $newItem;
                $message_text = 'Admin post created successfully!';
            }

            // Write the updated data back to the JSON file
            if (writeJsonFile($admin_posts_file_path, $existingData)) {
                $message = $message_text;
                $messageType = 'success';
                // Clear form or redirect on success for new posts
                if (!$form_item_id) {
                    $_POST = array(); // Clear post data for new entry form
                }
                $itemData = $newItem; // Update itemData for display if it was an edit
                $itemId = $newItem['id']; // Ensure ID is set for subsequent edits
            } else {
                $message = 'Error saving admin post. Please check file permissions for ' . $admin_posts_file_path;
                $messageType = 'error';
            }
        }
    }
}

// Current page for active navigation link
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $itemId ? 'Edit' : 'Create New'; ?> Admin Post</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #0066ff;
            --primary-dark: #0052cc;
            --primary-darker: #003d99;
            --primary-light: #3385ff;
            --primary-lighter: #66a3ff;
            --primary-pale: #e6f2ff;

            --secondary: #1e40af;
            --accent: #0ea5e9;

            --bg-primary: #f8faff; /* Light background for the page */
            --glass-bg: rgba(255, 255, 255, 0.8); /* For the glassmorphism effect */
            --glass-border: rgba(255, 255, 255, 0.3);

            /* New styles for forms and buttons */
            --input-border: #d1d5db; /* gray-300 */
            --input-focus-border: var(--primary);
            --input-placeholder: #9ca3af; /* gray-400 */

            --btn-primary-bg: var(--primary);
            --btn-primary-hover-bg: var(--primary-dark);
            --btn-text-color: #ffffff;
            --btn-border-radius: 0.75rem; /* 12px */

            /* Section divider */
            --divider-color: #d1d5db;
            --divider-text-bg: #f8faff; /* Same as --bg-primary */

            /* Glass card */
            --glass-card-shadow: rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.06); /* For shadow-2xl */
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
        }
        .canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }
        .glass-card {
            background-color: var(--glass-bg);
            border-radius: 1.5rem; /* Increased border-radius */
            box-shadow: var(--glass-card-shadow);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
        }
        .enhanced-input {
            border-color: var(--input-border);
            transition: all 0.2s ease-in-out;
            position: relative;
            z-index: 1; /* Ensure input is above label during animation */
            background-color: transparent; /* Ensure background is transparent for glass effect */
        }
        .enhanced-input:focus {
            border-color: var(--input-focus-border);
            box-shadow: 0 0 0 4px var(--primary-pale); /* Tailwind's focus-ring-4 equivalent */
        }
        .form-group {
            position: relative;
        }
        .floating-label label {
            position: absolute;
            top: 1rem;
            left: 1rem;
            color: var(--input-placeholder);
            pointer-events: none;
            transition: all 0.2s ease-in-out;
            background-color: transparent;
            padding: 0 0.2rem;
            z-index: 0; /* Ensure label is below input initially */
        }
        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label,
        .floating-label textarea:focus + label,
        .floating-label textarea:not(:placeholder-shown) + label,
        .floating-label select:focus + label,
        .floating-label select:not([value=""]) + label {
            transform: translateY(-1.75rem) scale(0.8); /* Move up and shrink */
            color: var(--primary-dark);
            font-weight: 600;
            background-color: var(--bg-primary); /* Match body background */
            padding: 0 0.4rem;
            border-radius: 0.3rem;
            z-index: 2; /* Bring label above input when active */
        }
        .btn-primary {
            background-color: var(--btn-primary-bg);
            color: var(--btn-text-color);
            border-radius: var(--btn-border-radius);
            transition: background-color 0.2s ease-in-out, transform 0.1s ease-in-out;
        }
        .btn-primary:hover {
            background-color: var(--btn-primary-hover-bg);
            transform: translateY(-1px);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        .alert {
            border-left-width: 4px;
        }

        /* Section Divider */
        .section-divider {
            display: flex;
            align-items: center;
            text-align: center;
            margin: 2.5rem 0;
            color: #4b5563; /* gray-700 */
        }
        .section-divider::before,
        .section-divider::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid var(--divider-color);
        }
        .section-divider:not(:empty)::before {
            margin-right: .75em;
        }
        .section-divider:not(:empty)::after {
            margin-left: .75em;
        }
        .section-divider span {
            background-color: var(--divider-text-bg);
            padding: 0 1rem;
            font-weight: 600;
            font-size: 1.125rem; /* text-lg */
        }
        /* File Upload Area */
        .file-upload {
            border: 2px dashed var(--input-border);
            border-radius: 1rem;
            padding: 2.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            position: relative;
            background-color: rgba(249, 250, 251, 0.7); /* gray-50 with transparency */
        }
        .file-upload:hover, .file-upload.dragover {
            border-color: var(--primary);
            background-color: var(--primary-pale);
        }
        .file-upload input[type="file"] {
            opacity: 0;
            position: absolute;
            width: 100%;
            height: 100%;
            left: 0;
            top: 0;
            cursor: pointer;
        }
        /* Spinner */
        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top: 4px solid #fff;
            width: 1.5rem;
            height: 1.5rem;
            -webkit-animation: spin 1s linear infinite;
            animation: spin 1s linear infinite;
        }

        @-webkit-keyframes spin {
            0% { -webkit-transform: rotate(0deg); }
            100% { -webkit-transform: rotate(360deg); }
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Navbar specific styles (from stdnav.php assumed CSS) */
        .navbar {
            background-color: var(--glass-bg);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
        }
        .navbar.scrolled {
            padding: 0.75rem 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }
        .logo {
            display: flex;
            align-items: center;
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--primary-dark);
            text-decoration: none;
        }
        .logo-icon {
            color: var(--accent);
            margin-right: 0.75rem;
            font-size: 2rem;
        }
        .nav-menu {
            display: flex;
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .nav-link {
            text-decoration: none;
            color: #4a5568; /* gray-700 */
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }
        .nav-link:hover {
            background-color: var(--primary-pale);
            color: var(--primary-dark);
        }
        .nav-link.active {
            background-color: var(--primary);
            color: white;
        }
        .nav-link.active:hover {
            background-color: var(--primary-dark);
            color: white;
        }
        .nav-link i {
            margin-right: 0.5rem;
        }
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.75rem;
            color: var(--primary-dark);
            cursor: pointer;
        }
        .mobile-menu-btn .fa-times {
            display: none;
        }
        .mobile-menu-btn.active .fa-bars {
            display: none;
        }
        .mobile-menu-btn.active .fa-times {
            display: inline-block;
        }

        @media (max-width: 1024px) {
            .nav-menu {
                display: none;
                flex-direction: column;
            }
            .nav-menu.active {
                display: flex;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: var(--glass-bg);
                box-shadow: 0 4px 10px rgba(0,0,0,0.1);
                padding: 1rem 0;
                border-top: 1px solid var(--glass-border);
            }
            .nav-menu li {
                width: 100%;
                text-align: center;
            }
            .nav-menu li a {
                display: block;
                padding: 0.75rem 1rem;
                margin: 0.25rem 1rem;
            }
            .mobile-menu-btn {
                display: block;
            }
        }

        /* Specific styles for media preview */
        .media-preview-container {
            border: 1px solid #d1d5db;
            border-radius: 0.75rem;
            padding: 1rem;
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            background-color: rgba(249, 250, 251, 0.7);
        }
        .media-preview-container img, .media-preview-container video {
            max-width: 100%;
            max-height: 200px;
            border-radius: 0.5rem;
            object-fit: contain;
            margin-bottom: 0.75rem;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen" id="i">
    <canvas id="particleCanvas" class="canvas-container"></canvas>

    <header class="navbar" id="navbar">
        <div class="nav-container">
            <a href="<?php echo $basePath; ?>/index.php" class="logo">
                <span class="logo-icon"><i class="fas fa-search-location"></i></span>
                Lost & Found Hub
            </a>
            <nav>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="<?php echo $basePath; ?>/index.php" class="nav-link"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="<?php echo $basePath; ?>/adpost_page.php" class="nav-link"><i class="fas fa-exclamation-circle"></i> Create New Post</a></li>
                    <li><a href="<?php echo $basePath; ?>/adpost_page.php" class="nav-link"><i class="fas fa-hand-holding-heart"></i> Edit Existing Post</a></li>
                    <li><a href="<?php echo $basePath; ?>/lost_show.php" class="nav-link"><i class="fas fa-eye"></i> View Lost Items</a></li>
                    <li><a href="<?php echo $basePath; ?>/found_show.php" class="nav-link"><i class="fas fa-clipboard-list"></i> View Found Items</a></li>
                    <li><a href="<?php echo $basePath; ?>/adpost_page.php" class="nav-link <?php echo ($currentPage == 'adpost_page.php' || $currentPage == 'adpost_show.php') ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i> Admin Panel</a></li>
                </ul>
            </nav>
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle mobile menu">
                <i class="fas fa-bars"></i>
                <i class="fas fa-times"></i>
            </button>
        </div>
    </header>

    <main class="flex-grow container max-w-4xl mx-auto my-8 px-6 relative z-10">
        <div class="glass-card p-8 shadow-2xl">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-20 h-19">
                    <i class="fa-solid fa-user-shield text-blue-600 text-4xl"></i>
                </div>
                <h2 class="text-4xl font-extrabold bg-gradient-to-r from-blue-600 to-blue-800 bg-clip-text text-transparent mb-3">
                    <?php echo $itemId ? 'Edit Admin Post' : 'Create New Admin Post'; ?>
                </h2>
                <p class="text-gray-600 text-lg">Manage administrative posts for lost and found entries.</p>
            </div>

            <?php if ($message): ?>
                <div class="alert p-4 mb-6 <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800 border-green-300' : 'bg-red-100 text-red-800 border-red-300'; ?> border">
                    <div class="flex items-center">
                        <i class="<?php echo $messageType === 'success' ? 'fa-solid fa-circle-check' : 'fa-solid fa-circle-exclamation'; ?> text-xl mr-3"></i>
                        <p class="font-medium"><?php echo $message; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data" class="space-y-8" id="adminItemForm">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($itemData['id'] ?? ''); ?>">

                <div class="section-divider">
                    <span><i class="fa-solid fa-user-circle mr-2 text-primary"></i>Poster Details</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group floating-label">
                        <input type="text" id="roll_number" name="roll_number" required placeholder=" "
                               class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($itemData['roll_number'] ?? $_POST['roll_number'] ?? ''); ?>">
                        <label for="roll_number"><i class="fa-solid fa-hashtag mr-2"></i>Roll Number</label>
                    </div>

                    <div class="form-group floating-label">
                        <input type="text" id="poster_name" name="poster_name" required placeholder=" "
                               class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($itemData['poster_name'] ?? $_POST['poster_name'] ?? ''); ?>">
                        <label for="poster_name"><i class="fa-solid fa-user-alt mr-2"></i>Full Name</label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group floating-label">
                        <input type="email" id="email" name="email" required placeholder=" "
                               class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($itemData['email'] ?? $_POST['email'] ?? ''); ?>">
                        <label for="email"><i class="fa-solid fa-at mr-2"></i>Email Address</label>
                    </div>

                    <div class="form-group floating-label">
                        <input type="text" id="phone_number" name="phone_number" placeholder=" "
                               class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($itemData['phone_number'] ?? $_POST['phone_number'] ?? ''); ?>">
                        <label for="phone_number"><i class="fa-solid fa-phone mr-2"></i>Phone Number (Optional)</label>
                    </div>
                </div>

                <div class="section-divider">
                    <span><i class="fa-solid fa-box-open mr-2 text-primary"></i>Post Details</span>
                </div>

                <div class="form-group floating-label">
                    <input type="text" id="title" name="title" required placeholder=" "
                           class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                           value="<?php echo htmlspecialchars($itemData['title'] ?? $_POST['title'] ?? ''); ?>">
                    <label for="title"><i class="fa-solid fa-tag mr-2"></i>Post Title</label>
                </div>

                <div class="form-group floating-label">
                    <textarea id="description" name="description" rows="4" required placeholder=" "
                              class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"><?php echo htmlspecialchars($itemData['description'] ?? $_POST['description'] ?? ''); ?></textarea>
                    <label for="description"><i class="fa-solid fa-info-circle mr-2"></i>Post Content / Description</label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="form-group floating-label">
                        <input type="text" id="location" name="location" required placeholder=" "
                               class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($itemData['location'] ?? $_POST['location'] ?? ''); ?>">
                        <label for="location"><i class="fa-solid fa-map-marker-alt mr-2"></i>Relevant Location</label>
                    </div>

                    <div class="form-group floating-label">
                        <input type="date" id="date" name="date" required
                               class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500"
                               value="<?php echo htmlspecialchars($itemData['date'] ?? $_POST['date'] ?? date('Y-m-d')); ?>">
                        <label for="date"><i class="fa-solid fa-calendar-alt mr-2"></i>Date of Post</label>
                    </div>
                </div>

                <div class="form-group floating-label">
                    <select id="status" name="status" required
                            class="enhanced-input w-full py-3 px-4 border rounded-xl focus:outline-none focus:ring-4 focus:ring-blue-100 focus:border-blue-500">
                        <option value="pending" <?php echo (isset($itemData['status']) && $itemData['status'] === 'pending') ? 'selected' : ''; ?>>Active</option>
                        <option value="resolved" <?php echo (isset($itemData['status']) && $itemData['status'] === 'resolved') ? 'selected' : ''; ?>>Archived</option>
                    </select>
                    <label for="status"><i class="fa-solid fa-check-circle mr-2"></i>Status</label>
                </div>

                <div class="form-group">
                    <label for="media_file" class="block text-gray-700 text-sm font-semibold mb-2">Upload Photo or Video (Max 40MB):</label>
                    <div class="file-upload" id="fileUploadArea">
                        <input type="file" id="media_file" name="media_file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" accept="image/*,video/*">
                        <i class="fa-solid fa-cloud-arrow-up text-4xl text-blue-400 mb-2"></i>
                        <p class="text-lg font-medium text-gray-700">Drag & Drop files here or <span class="text-blue-600 font-bold">Click to Browse</span></p>
                        <p class="text-sm text-gray-500 mt-1" id="fileInfo">
                            <?php
                                if (!empty($itemData['media_path'])) {
                                    echo 'Current file: ' . basename($itemData['media_path']) . ' (Leave empty to keep existing)';
                                } else {
                                    echo 'No file chosen';
                                }
                            ?>
                        </p>
                        <p class="text-xs text-gray-500 mt-2">Allowed formats: JPG, PNG, GIF, WebP, MP4, WebM, Ogg.</p>
                    </div>

                    <?php if (!empty($itemData['media_path'])): ?>
                        <div class="media-preview-container">
                            <p class="text-gray-700 text-sm font-semibold mb-2">Current Media:</p>
                            <?php
                            // The path stored in JSON is relative to admin_data_post_dir
                            $full_media_path_for_display = $basePath . '/admin_data_post/' . $itemData['media_path'];
                            $file_ext = strtolower(pathinfo($itemData['media_path'], PATHINFO_EXTENSION));
                            if (in_array($file_ext, ['jpg', 'png', 'jpeg', 'gif', 'webp'])) {
                                echo '<img src="' . htmlspecialchars($full_media_path_for_display) . '" alt="Current Media">';
                            } elseif (in_array($file_ext, ['mp4', 'webm', 'ogg'])) {
                                echo '<video controls src="' . htmlspecialchars($full_media_path_for_display) . '"></video>';
                            } else {
                                echo '<p class="text-red-500">Unsupported media type for preview.</p>';
                            }
                            ?>
                            <label class="inline-flex items-center mt-2">
                                <input type="checkbox" name="remove_media" value="true" class="form-checkbox text-red-600">
                                <span class="ml-2 text-red-600 text-sm">Remove Current Media</span>
                            </label>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" id="submitButton" class="btn-primary w-full py-3 px-6 rounded-xl text-lg flex items-center justify-center gap-2">
                    <span id="buttonText"><?php echo $itemId ? 'Update Post' : 'Create Post'; ?></span>
                    <div id="spinner" class="spinner hidden"></div>
                </button>
            </form>
        </div>
    </main>

    <footer class="footer">
        <div class="footer-container container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Lost & Found Hub</h3>
                    <p>Connecting lost items with their rightful owners, building a more responsible community.</p>
                    <p class="text-sm">"Finders Keepers, Losers Weepers" no more!</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <a href="<?php echo $basePath; ?>/lost_post.php">Post Lost Item</a>
                    <a href="<?php echo $basePath; ?>/found_post.php">Post Found Item</a>
                    <a href="<?php echo $basePath; ?>/lost_show.php">View Lost Items</a>
                    <a href="<?php echo $basePath; ?>/found_show.php">View Found Items</a>
                </div>
                <div class="footer-section">
                    <h3>Admin Links</h3>
                    <a href="<?php echo $basePath; ?>/adpost_page.php">Post/Edit Admin</a>
                    <a href="<?php echo $basePath; ?>/adpost_show.php">Manage Admin Posts</a>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date("Y"); ?> Lost & Found Hub. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('scrolled');
            } else {
                navbar.classList.remove('scrolled');
            }
        });

        // Mobile menu toggle
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const navMenu = document.getElementById('navMenu');

        mobileMenuBtn.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            mobileMenuBtn.classList.toggle('active');
        });

        // Floating labels (JavaScript to handle input focus/blur for placeholder behavior)
        document.querySelectorAll('.floating-label input, .floating-label textarea, .floating-label select').forEach(input => {
            function updateFloatingLabel() {
                const label = input.parentNode.querySelector('label');
                if (input.value !== '' || input.selectedIndex > 0) { // For select, check selectedIndex
                    label.style.transform = 'translateY(-28px) scale(0.8)';
                    label.style.color = 'var(--primary-dark)';
                    label.style.fontWeight = '600';
                    label.style.backgroundColor = 'var(--bg-primary)';
                    label.style.padding = '0 0.4rem';
                    label.style.borderRadius = '0.3rem';
                } else {
                    label.style.transform = '';
                    label.style.color = 'var(--input-placeholder)';
                    label.style.fontWeight = '';
                    label.style.backgroundColor = 'transparent';
                    label.style.padding = '';
                    label.style.borderRadius = '';
                }
            }

            input.addEventListener('focus', () => {
                input.setAttribute('placeholder', '');
                updateFloatingLabel(); // Ensure label lifts on focus
            });
            input.addEventListener('blur', () => {
                if (input.value === '') {
                    input.setAttribute('placeholder', ' ');
                }
                updateFloatingLabel(); // Ensure label behaves correctly on blur
            });
            input.addEventListener('change', updateFloatingLabel); // For select elements
            updateFloatingLabel(); // Initial check for pre-filled values
        });


        // File upload drag & drop and preview
        const fileUploadArea = document.getElementById('fileUploadArea');
        const mediaFileInput = document.getElementById('media_file');
        const fileInfo = document.getElementById('fileInfo');

        fileUploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            fileUploadArea.classList.add('dragover');
        });

        fileUploadArea.addEventListener('dragleave', () => {
            fileUploadArea.classList.remove('dragover');
        });

        fileUploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            fileUploadArea.classList.remove('dragover');
            if (e.dataTransfer.files.length > 0) {
                mediaFileInput.files = e.dataTransfer.files;
                updateFileInfo();
            }
        });

        mediaFileInput.addEventListener('change', updateFileInfo);

        function updateFileInfo() {
            if (mediaFileInput.files.length > 0) {
                const file = mediaFileInput.files[0];
                fileInfo.textContent = `Selected file: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
            } else {
                // If no new file selected, revert to showing existing file info or "No file chosen"
                const existingPath = "<?php echo htmlspecialchars($itemData['media_path'] ?? ''); ?>";
                if (existingPath) {
                    fileInfo.textContent = `Current file: ${existingPath.split('/').pop()} (Leave empty to keep existing)`;
                } else {
                    fileInfo.textContent = 'No file chosen';
                }
            }
        }

        // Form submission loading indicator
        const adminItemForm = document.getElementById('adminItemForm');
        const submitButton = document.getElementById('submitButton');
        const buttonText = document.getElementById('buttonText');
        const spinner = document.getElementById('spinner');

        adminItemForm.addEventListener('submit', function() {
            submitButton.disabled = true;
            buttonText.textContent = 'Processing...';
            spinner.classList.remove('hidden');
        });

        // Particles JS (simplified for demonstration, typically this would be a separate library/file)
        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        const numParticles = 100;
        const particleSize = 1.5;
        const particleSpeed = 0.5;

        // Resize canvas on window resize
        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
            // Re-initialize particles on resize to ensure they fit the new dimensions
            // To prevent particles from disappearing on resize, you might want to recalculate their positions
            // relative to the new dimensions or re-initialize them completely.
            particles = []; // Clear existing particles
            initParticles(); // Re-initialize
        }

        // Particle properties (using a more defined color palette)
        function initParticles() {
            for (let i = 0; i < numParticles; i++) {
                particles.push({
                    x: Math.random() * canvas.width,
                    y: Math.random() * canvas.height,
                    size: Math.random() * particleSize + 0.5, // 0.5 to particleSize + 0.5
                    speedX: (Math.random() - 0.5) * 2 * particleSpeed, // -particleSpeed to particleSpeed
                    speedY: (Math.random() - 0.5) * 2 * particleSpeed,
                    color: `rgba(255, 255, 255, ${Math.random() * 0.4 + 0.1})` // White with low opacity
                });
            }
        }

        // Draw particles
        function drawParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height); // Clear canvas
            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
                ctx.fillStyle = p.color;
                ctx.fill();
            }
        }

        // Update particle positions
        function updateParticles() {
            for (let i = 0; i < particles.length; i++) {
                const p = particles[i];

                p.x += p.speedX;
                p.y += p.speedY;

                // Bounce off walls
                if (p.x < 0 || p.x > canvas.width) p.speedX *= -1;
                if (p.y < 0 || p.y > canvas.height) p.speedY *= -1;
            }
        }

        // Animation loop
        function animateParticles() {
            updateParticles();
            drawParticles();
            requestAnimationFrame(animateParticles);
        }

        // Initialize and start animation when window loads
        window.addEventListener('load', function() {
            resizeCanvas(); // Set initial canvas size
            animateParticles(); // Start the animation loop
        });

        // Event listener for window resize
        window.addEventListener('resize', resizeCanvas);
    </script>
</body>
</html>