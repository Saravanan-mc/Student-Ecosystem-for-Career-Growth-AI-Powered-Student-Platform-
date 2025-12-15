<?php
// Include the configuration file for shared functions and constants
require_once 'config.php';
include 'admin.php';

// Ensure getMediaType function is defined.
// This is a placeholder; in a real application, it would be in config.php or a dedicated utility file.
if (!function_exists('getMediaType')) {
    function getMediaType($filePath) {
        $ext = pathinfo($filePath, PATHINFO_EXTENSION);
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $videoExtensions = ['mp4', 'webm', 'ogg'];

        if (in_array(strtolower($ext), $imageExtensions)) {
            return 'image';
        } elseif (in_array(strtolower($ext), $videoExtensions)) {
            return 'video';
        }
        return 'unknown';
    }
}

// Get the base path for navigation
$basePath = getBasePath();
// Get the URL path for uploaded media
$uploadUrlPath = getUploadUrlPath();

$message = ''; // To store success or error messages
$messageType = ''; // To determine the styling of the message (success/error)

$postType = htmlspecialchars($_GET['type'] ?? ''); // Get the type of posts to manage (lost or found)
$items = []; // Array to hold the items for display
$file_path = ''; // Will be set based on $postType
$top_posts_file_path = getDataDirPath() . 'top_posts.json'; // Path for top posts tracking

// Load existing top posts data
$currentTopPosts = [];
if (file_exists($top_posts_file_path)) {
    $jsonContent = file_get_contents($top_posts_file_path);
    $currentTopPosts = json_decode($jsonContent, true);
    if (!is_array($currentTopPosts)) {
        $currentTopPosts = [];
    }
}

// Determine which JSON file to read/write based on the 'type' GET parameter
if ($postType === 'lost') {
    $file_path = getDataDirPath() . 'lost_items.json';
    $pageTitle = 'Manage Lost Items';
    $headingColor = 'text-red-700'; // Darker red for emphasis
    $typeIcon = 'fas fa-box-open';
} elseif ($postType === 'found') {
    $file_path = getDataDirPath() . 'found_items.json';
    $pageTitle = 'Manage Found Items';
    $headingColor = 'text-green-700'; // Darker green for emphasis
    $typeIcon = 'fas fa-box';
} else {
    // Default to managing lost items if type is not specified or invalid
    $file_path = getDataDirPath() . 'lost_items.json';
    $postType = 'lost';
    $pageTitle = 'Manage Lost Items (Default)';
    $headingColor = 'text-red-700';
    $typeIcon = 'fas fa-box-open';
}

// Function to save top posts data
function saveTopPosts($data, $filePath) {
    return file_put_contents($filePath, json_encode($data, JSON_PRETTY_PRINT));
}

// Handle actions (Delete, Make Top, Remove Top)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_id'])) {
        $deleteId = htmlspecialchars($_POST['delete_id']);

        if (file_exists($file_path)) {
            $jsonContent = file_get_contents($file_path);
            $existingData = json_decode($jsonContent, true);

            if (is_array($existingData)) {
                $initialCount = count($existingData);
                $deleted_item_media_path = null;

                $updatedData = array_filter($existingData, function($item) use ($deleteId, &$deleted_item_media_path) {
                    if (($item['id'] ?? null) === $deleteId) {
                        $deleted_item_media_path = $item['media_path'] ?? null;
                        return false; // Remove this item
                    }
                    return true; // Keep this item
                });

                if (count($updatedData) < $initialCount) {
                    // Attempt to delete associated media file
                    if ($deleted_item_media_path && file_exists(getUploadDirPath() . $deleted_item_media_path)) {
                        unlink(getUploadDirPath() . $deleted_item_media_path);
                    }

                    if (file_put_contents($file_path, json_encode($updatedData, JSON_PRETTY_PRINT))) {
                        $message = 'Item and associated media (if any) deleted successfully.';
                        $messageType = 'success';

                        // Also remove from top posts if it was a top post
                        if (isset($currentTopPosts[$postType]) && $currentTopPosts[$postType] === $deleteId) {
                            unset($currentTopPosts[$postType]);
                            saveTopPosts($currentTopPosts, $top_posts_file_path);
                        }
                    } else {
                        $message = 'Error deleting item. File write failed. Check permissions for ' . $file_path;
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Item not found for deletion.';
                    $messageType = 'error';
                }
            } else {
                $message = 'Error: Data file is malformed or empty.';
                $messageType = 'error';
            }
        } else {
            $message = 'Data file not found at ' . $file_path;
            $messageType = 'error';
        }
    }
    // Handle "Make Top Post" action
    elseif (isset($_POST['make_top_id'])) {
        $makeTopId = htmlspecialchars($_POST['make_top_id']);
        $currentTopPosts[$postType] = $makeTopId;
        if (saveTopPosts($currentTopPosts, $top_posts_file_path)) {
            $message = 'Item successfully set as top post!';
            $messageType = 'success';
        } else {
            $message = 'Failed to set item as top post. Check file permissions for ' . $top_posts_file_path;
            $messageType = 'error';
        }
    }
    // Handle "Remove from Top" action
    elseif (isset($_POST['remove_top_id'])) {
        if (isset($currentTopPosts[$postType])) {
            unset($currentTopPosts[$postType]);
            if (saveTopPosts($currentTopPosts, $top_posts_file_path)) {
                $message = 'Item successfully removed from top posts.';
                $messageType = 'success';
            } else {
                $message = 'Failed to remove item from top posts. Check file permissions for ' . $top_posts_file_path;
                $messageType = 'error';
            }
        } else {
            $message = 'No item currently set as top post for this category.';
            $messageType = 'error';
        }
    }
}

// Load items for display (re-load after potential modifications)
if (file_exists($file_path)) {
    $jsonContent = file_get_contents($file_path);
    $items = json_decode($jsonContent, true);
    if (!is_array($items)) {
        $items = []; // Ensure it's an array
    }
    // Sort items by timestamp in descending order (most recent first)
    usort($items, function($a, $b) {
        $timestampA = isset($a['timestamp']) ? strtotime($a['timestamp']) : 0;
        $timestampB = isset($b['timestamp']) ? strtotime($b['timestamp']) : 0;
        return $timestampB - $timestampA;
    });

    // Move the designated top post to the very top of the list for display
    if (isset($currentTopPosts[$postType]) && !empty($items)) {
        $topPostId = $currentTopPosts[$postType];
        $topItem = null;
        $filteredItems = [];

        foreach ($items as $item) {
            if (($item['id'] ?? null) === $topPostId) {
                $topItem = $item;
            } else {
                $filteredItems[] = $item;
            }
        }
        if ($topItem) {
            array_unshift($filteredItems, $topItem); // Place top item at the beginning
            $items = $filteredItems;
        }
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
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
            --accent-light: #38bdf8;
            --ocean: #0284c7;
            --azure: #0369a1;

            --cyan: #06b6d4;
            --teal: #0d9488;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;

            --text-primary: #0f172a;
            --text-secondary: #334155;
            --text-light: #64748b;
            --text-muted: #94a3b8;

            --bg-primary: #ffffff;
            --bg-secondary: #f8fafc;
            --bg-blue: #f0f9ff;
            --bg-ocean: #ecfeff;
            --bg-gradient: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);

            --border: #e2e8f0;
            --border-blue: #bfdbfe;
            --border-light: #f1f5f9;

            --shadow-sm: 0 1px 2px 0 rgba(0, 102, 255, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 102, 255, 0.1), 0 2px 4px -1px rgba(0, 102, 255, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 102, 255, 0.1), 0 4px 6px -2px rgba(0, 102, 255, 0.05);
            --shadow-xl: 0 20px 25px -5px rgba(0, 102, 255, 0.1), 0 10px 10px -5px rgba(0, 102, 255, 0.04);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 102, 255, 0.25);
            --shadow-blue: 0 10px 25px -5px rgba(0, 102, 255, 0.2);

            --gradient-primary: linear-gradient(135deg, #0066ff 0%, #0052cc 100%);
            --gradient-ocean: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            --gradient-sky: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
            --gradient-hero: linear-gradient(135deg, #0066ff 0%, #0284c7 50%, #0ea5e9 100%);
            --gradient-card: linear-gradient(145deg, #ffffff 0%, #f0f9ff 100%);
            --gradient-mesh: radial-gradient(at 40% 20%, #0066ff 0px, transparent 50%),
                                 radial-gradient(at 80% 0%, #0ea5e9 0px, transparent 50%),
                                 radial-gradient(at 0% 50%, #0284c7 0px, transparent 50%),
                                 radial-gradient(at 80% 50%, #38bdf8 0px, transparent 50%),
                                 radial-gradient(at 0% 100%, #0369a1 0px, transparent 50%);

            --shine-gradient: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.6), transparent);
            --wave-gradient: linear-gradient(45deg, rgba(0, 102, 255, 0.1), rgba(14, 165, 233, 0.1), rgba(0, 102, 255, 0.1));
        }

        /* General body styling for font and background */
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.6;
            color: var(--text-primary);
            background: var(--gradient-hero); /* Apply hero gradient to body */
            min-height: 100vh;
            overflow-x: hidden;
            scroll-behavior: smooth;
            position: relative;
            display: flex;
            flex-direction: column;
            padding-top: 85px; /* Adjust for fixed navbar height */
        }

        /* Background mesh for subtle effect */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--gradient-mesh);
            opacity: 0.03;
            z-index: -1;
            pointer-events: none;
        }

        /* Canvas container for particle animation */
        .canvas-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none; /* Allows clicks to pass through to elements below */
            z-index: 0;
        }

        /* General container for main content */
        .container {
            max-width: 1024px; /* Slightly wider container */
            margin: 0 auto;
            padding: 20px;
        }
        /* Card-like styling for main content blocks */
        .card {
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); /* Stronger shadow */
            padding: 32px; /* More padding */
            border: 1px solid #e2e8f0; /* Subtle border */
        }
        /* Styling for individual item cards in admin view */
        .item-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            padding: 24px;
            margin-bottom: 20px; /* Increased margin-bottom */
            border: 1px solid #e2e8f0;
            transition: transform 0.2s ease, box-shadow 0.2s ease; /* Smooth transition on hover */
            display: flex; /* Flexbox for internal layout */
            flex-direction: column; /* Stack content vertically */
            justify-content: space-between; /* Push buttons to the bottom */
        }
        .item-card:hover {
            transform: translateY(-5px); /* Slight lift on hover */
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        /* Highlight for the top post */
        .item-card.top-post {
            border-color: #10b981; /* Emerald green border */
            background-color: #ecfdf5; /* Light emerald background */
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.2); /* More prominent shadow */
        }
        /* Navigation bar styling */
        .navbar {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(25px) saturate(180%);
            border-bottom: 1px solid var(--border-blue);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-sm);
        }

        .navbar.scrolled {
            background: rgba(255, 255, 255, 0.99);
            box-shadow: var(--shadow-lg);
            border-bottom-color: var(--primary-pale);
        }

        .nav-container {
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 85px;
            transition: height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar.scrolled .nav-container {
            height: 75px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.65rem;
            font-weight: 900;
            color: var(--primary);
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .logo:hover {
            transform: scale(1.03);
        }

        .logo-icon {
            width: 45px;
            height: 45px;
            background: var(--gradient-primary);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            box-shadow: var(--shadow-blue);
            position: relative;
            overflow: hidden;
        }

        .logo-icon::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: var(--shine-gradient);
            transform: rotate(45deg);
            animation: logoShine 3s ease-in-out infinite;
        }

        @keyframes logoShine {
            0%, 100% {
                transform: translateX(-100%) translateY(-100%) rotate(45deg);
            }
            50% {
                transform: translateX(100%) translateY(100%) rotate(45deg);
            }
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2.0rem;
            align-items: center;
        }

        .nav-link {
            color: var(--text-secondary);
            text-decoration: none;
            font-weight: 600;
            padding: 0.75rem 0;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            width: 0;
            height: 3px;
            background: var(--gradient-primary);
            border-radius: 2px;
            transform: translateX(-50%);
            transition: width 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary);
            transform: translateY(-1px);
        }

        .nav-link:hover::before,
        .nav-link.active::before {
            width: 100%;
        }

        .nav-link i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .nav-link:hover i {
            transform: scale(1.1);
        }

        .mobile-menu-btn {
            display: none;
            background: var(--gradient-primary);
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            color: white;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: var(--shadow-md);
            position: relative;
            overflow: hidden;
            z-index: 1001;
        }

        .mobile-menu-btn:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        .mobile-menu-btn i {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 1.4rem;
        }

        .mobile-menu-btn .fa-times {
            opacity: 0;
            transform: translate(-50%, -50%) rotate(180deg);
        }

        .mobile-menu-btn.active .fa-bars {
            opacity: 0;
            transform: translate(-50%, -50%) rotate(-180deg);
        }

        .mobile-menu-btn.active .fa-times {
            opacity: 1;
            transform: translate(-50%, -50%) rotate(0deg);
        }

        /* Button styling improvements */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 18px;
            font-size: 0.9rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s ease;
            cursor: pointer;
            border: none;
            text-decoration: none;
            flex-grow: 1; /* Allow buttons to grow and fill space */
        }

        .btn-blue {
            background-color: #3b82f6; /* Blue-500 */
            color: white;
        }
        .btn-blue:hover {
            background-color: #2563eb; /* Blue-600 */
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-yellow {
            background-color: #f59e0b; /* Amber-500 */
            color: white;
        }
        .btn-yellow:hover {
            background-color: #d97706; /* Amber-600 */
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .btn-red {
            background-color: #ef4444; /* Red-600 */
            color: white;
        }
        .btn-red:hover {
            background-color: #dc2626; /* Red-700 */
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        /* Custom Modal for Confirmation */
        .modal {
            display: none; /* Hidden by default */
            position: fixed; /* Stay in place */
            z-index: 1000; /* Sit on top */
            left: 0;
            top: 0;
            width: 100%; /* Full width */
            height: 100%; /* Full height */
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background-color: #fefefe;
            margin: auto;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 90%;
            text-align: center;
        }

        .modal-content h3 {
            font-size: 1.5rem;
            color: #ef4444;
            margin-bottom: 1rem;
        }

        .modal-content p {
            margin-bottom: 1.5rem;
            color: #334155;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .modal-buttons button {
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
        }

        .modal-buttons .confirm-btn {
            background-color: #ef4444;
            color: white;
            border: none;
        }
        .modal-buttons .confirm-btn:hover {
            background-color: #dc2626;
        }

        .modal-buttons .cancel-btn {
            background-color: #e2e8f0;
            color: #334155;
            border: 1px solid #cbd5e1;
        }
        .modal-buttons .cancel-btn:hover {
            background-color: #cbd5e1;
        }

        @media (max-width: 992px) { /* Adjusted breakpoint from 768px to 992px */
            .nav-container {
                flex-wrap: wrap;
                height: auto;
                padding: 1rem 1.5rem;
            }
            .navbar.scrolled .nav-container {
                height: auto;
            }
            .nav-menu {
                display: none;
                flex-direction: column;
                width: 100%;
                text-align: center;
                margin-top: 1rem;
                gap: 0;
                background: rgba(255, 255, 255, 0.98);
                backdrop-filter: blur(25px);
                border-top: 1px solid var(--border-blue);
                padding: 1rem 0;
                box-shadow: var(--shadow-md);
                position: absolute;
                top: 85px; /* Aligned with navbar height */
                left: 0;
                transform: translateY(-100%);
                opacity: 0;
                transition: transform 0.4s ease-out, opacity 0.4s ease-out;
            }

            .nav-menu.active {
                display: flex;
                transform: translateY(0);
                opacity: 1;
            }

            .nav-link {
                padding: 0.8rem 0;
                justify-content: center;
            }

            .mobile-menu-btn {
                display: flex;
            }
            body {
                padding-top: 75px; /* Adjusted for smaller navbar on scroll or mobile */
            }
            /* Specific media queries for elements if needed, example provided in original */
            .hero-title {
                font-size: clamp(2rem, 8vw, 4rem);
            }
            .hero-subtitle {
                font-size: 1.2rem;
            }
            .hero-actions {
                flex-direction: column;
                gap: 1.5rem;
            }
            .btn {
                padding: 1rem 2rem;
                font-size: 1rem;
                width: 80%;
                max-width: 300px;
            }
            .features {
                padding: 80px 0;
            }
            .feature-card {
                padding: 2rem;
            }
            .feature-icon {
                width: 70px;
                height: 70px;
                font-size: 1.8rem;
            }
            .feature-title {
                font-size: 1.5rem;
            }
            .feature-description {
                font-size: 1rem;
            }
            .browse {
                padding: 80px 0;
            }
            .browse-btn {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
                padding: 2rem;
                min-width: unset;
                width: 80%;
                max-width: 300px;
            }
            .browse-icon {
                width: 55px;
                height: 55px;
                font-size: 1.5rem;
            }
            .browse-text .title {
                font-size: 1.2rem;
            }
            .browse-text .subtitle {
                font-size: 0.9rem;
            }
            .footer-content {
                grid-template-columns: 1fr;
                gap: 3rem;
                text-align: center;
            }
            .footer-section h3::after {
                left: 50%;
                transform: translateX(-50%);
            }
            .back-to-top {
                bottom: 1.5rem;
                right: 1.5rem;
                width: 50px;
                height: 50px;
                font-size: 1.4rem;
            }
        }

        canvas {
            display: block; /* Ensure canvas takes up space */
        }

        #ad{
            margin-left:300px;
        }
    </style>
</head>
<body class="flex flex-col min-h-screen" id="ad">
    <canvas id="particleCanvas" class="canvas-container"></canvas>

    <header class="navbar" id="navbar">
        <div class="nav-container">
            <a href="<?php echo $basePath; ?>/index.php" class="logo">
                <span class="logo-icon"><i class="fas fa-search-location"></i></span>
                Lost & Found Hub
            </a>
            <nav>
                <ul class="nav-menu" id="navMenu">
                    <li><a href="<?php echo $basePath; ?>/admin_home.php" class="nav-link <?php echo (str_contains($_SERVER['PHP_SELF'], 'admin_home.php')) ? 'active' : ''; ?>"><i class="fas fa-user-shield"></i> Admin Home</a></li>
                    <li><a href="<?php echo $basePath; ?>/admin_manage_posts.php?type=lost" class="nav-link <?php echo (str_contains($_SERVER['PHP_SELF'], 'admin_manage_posts.php') && ($_GET['type'] ?? '') == 'lost') ? 'active' : ''; ?>"><i class="fas fa-box-open"></i> Manage Lost</a></li>
                    <li><a href="<?php echo $basePath; ?>/admin_manage_posts.php?type=found" class="nav-link <?php echo (str_contains($_SERVER['PHP_SELF'], 'admin_manage_posts.php') && ($_GET['type'] ?? '') == 'found') ? 'active' : ''; ?>"><i class="fas fa-box"></i> Manage Found</a></li>
                    <li><a href="<?php echo $basePath; ?>/faculty_post.php" class="nav-link <?php echo (str_contains($_SERVER['PHP_SELF'], 'faculty_post.php')) ? 'active' : ''; ?>"><i class="fas fa-chalkboard-teacher"></i> Post Faculty Item</a></li>
                    <li><a href="<?php echo $basePath; ?>/faculty_show.php" class="nav-link <?php echo (str_contains($_SERVER['PHP_SELF'], 'faculty_show.php')) ? 'active' : ''; ?>"><i class="fas fa-eye"></i> View Faculty Items</a></li>
                </ul>
            </nav>
            <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle mobile menu">
                <i class="fas fa-bars"></i>
                <i class="fas fa-times"></i>
            </button>
        </div>
    </header>

    <main class="flex-grow container mt-10 mb-10">
        <div class="card">
            <h2 class="text-4xl font-extrabold <?php echo $headingColor; ?> mb-8 text-center flex items-center justify-center gap-3">
                <i class="<?php echo $typeIcon; ?>"></i> <?php echo $pageTitle; ?>
            </h2>

            <?php if ($message): // Display success or error messages ?>
                <div class="p-4 mb-6 rounded-lg text-lg font-medium flex items-center gap-3
                    <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
                    <i class="<?php echo $messageType === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle'; ?> text-2xl"></i>
                    <p><?php echo $message; ?></p>
                </div>
            <?php endif; ?>

            <?php if (empty($items)): // Display message if no items are found to manage ?>
                <div class="bg-gray-50 border border-gray-200 text-gray-700 p-6 rounded-xl flex items-center justify-center gap-3">
                    <i class="fas fa-info-circle text-2xl"></i>
                    <p class="font-medium text-lg">No <?php echo htmlspecialchars($postType); ?> items to manage yet.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-1 lg:grid-cols-1 gap-1 justify-center items-center">
                    <?php
                    $topPostId = $currentTopPosts[$postType] ?? null;
                    foreach ($items as $item):
                        $isTopPost = (($item['id'] ?? null) === $topPostId);
                        $cardClass = $isTopPost ? 'item-card top-post' : 'item-card';
                    ?>
                        <div class="<?php echo $cardClass; ?>">
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 mb-3 leading-tight flex items-center">
                                    <?php echo htmlspecialchars($item['title'] ?? 'N/A'); ?>
                                    <?php if ($isTopPost): ?>
                                        <span class="ml-3 px-3 py-1 bg-emerald-500 text-white text-xs font-bold rounded-full shadow-md"><i class="fas fa-star mr-1"></i> TOP POST</span>
                                    <?php endif; ?>
                                </h3>
                                <p class="text-gray-700 text-base mb-2"><strong class="text-gray-800">Description:</strong> <?php echo htmlspecialchars($item['description'] ?? 'N/A'); ?></p>
                                <p class="text-gray-600 text-sm mb-2"><strong class="text-gray-700">Location:</strong> <?php echo htmlspecialchars($item['location'] ?? 'N/A'); ?></p>
                                <p class="text-gray-600 text-sm mb-4"><strong class="text-gray-700">Date:</strong> <?php echo htmlspecialchars($item['date'] ?? 'N/A'); ?></p>

                                <?php if (!empty($item['media_path'])): ?>
                                    <div class="mb-4 text-center">
                                        <?php
                                        $mediaPath = $uploadUrlPath . htmlspecialchars($item['media_path']);
                                        $mediaType = getMediaType($item['media_path']);
                                        ?>
                                        <?php if ($mediaType === 'image'): ?>
                                            <img src="<?php echo $mediaPath; ?>" alt="Item Image" class="w-full h-48 object-cover rounded-lg shadow-md border border-gray-200">
                                        <?php elseif ($mediaType === 'video'): ?>
                                            <video controls class="w-full h-48 object-cover rounded-lg shadow-md border border-gray-200">
                                                <source src="<?php echo $mediaPath; ?>" type="video/<?php echo pathinfo($item['media_path'], PATHINFO_EXTENSION); ?>">
                                                Your browser does not support the video tag.
                                            </video>
                                        <?php else: ?>
                                            <p class="text-gray-500 text-sm">Unknown media type.</p>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <h4 class="text-xl font-semibold text-gray-800 mt-6 pt-4 border-t border-gray-200 flex items-center gap-2">
                                    <i class="fas fa-address-card text-blue-500"></i> Contact Details:
                                </h4>
                                <p class="text-gray-700 text-base mb-1"><strong class="text-gray-800">Roll Number:</strong> <?php echo htmlspecialchars($item['roll_number'] ?? 'N/A'); ?></p>
                                <p class="text-gray-700 text-base mb-1"><strong class="text-gray-800">Name:</strong> <?php echo htmlspecialchars($item['poster_name'] ?? 'N/A'); ?></p>
                                <p class="text-blue-700 text-base mb-1"><strong class="text-gray-800">Email:</strong> <a href="mailto:<?php echo htmlspecialchars($item['email'] ?? ''); ?>" class="hover:underline hover:text-blue-800"><?php echo htmlspecialchars($item['email'] ?? 'N/A'); ?></a></p>
                                <p class="text-gray-600 text-sm mb-4"><strong class="text-gray-700">Posted On:</strong> <?php echo htmlspecialchars($item['timestamp'] ?? 'N/A'); ?></p>
                            </div>

                            <div class="mt-6 flex flex-col sm:flex-row gap-3">
                                <?php if ($isTopPost): ?>
                                    <form method="POST" action="" class="w-full">
                                        <input type="hidden" name="remove_top_id" value="<?php echo htmlspecialchars($item['id'] ?? ''); ?>">
                                        <button type="submit" class="btn btn-yellow w-full"><i class="fas fa-minus-circle mr-2"></i> Remove from Top</button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" action="" class="w-full">
                                        <input type="hidden" name="make_top_id" value="<?php echo htmlspecialchars($item['id'] ?? ''); ?>">
                                        <button type="submit" class="btn btn-blue w-full"><i class="fas fa-star mr-2"></i> Make Top Post</button>
                                    </form>
                                <?php endif; ?>

                                <button type="button" class="btn btn-red w-full delete-btn" data-id="<?php echo htmlspecialchars($item['id'] ?? ''); ?>">
                                    <i class="fas fa-trash-alt mr-2"></i> Delete
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <h3 class="flex items-center justify-center gap-2"><i class="fas fa-exclamation-triangle"></i> Confirm Deletion</h3>
            <p>Are you sure you want to delete this item? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button class="cancel-btn" id="cancelDelete">Cancel</button>
                <form method="POST" action="" id="confirmDeleteForm" class="inline-block">
                    <input type="hidden" name="delete_id" id="modalDeleteId">
                    <button type="submit" class="confirm-btn">Delete</button>
                </form>
            </div>
        </div>
    </div>

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

        // Close mobile menu when a link is clicked
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                mobileMenuBtn.classList.remove('active');
            });
        });

        // Particle Animation
        const canvas = document.getElementById('particleCanvas');
        const ctx = canvas.getContext('2d');
        let particles = [];
        const numParticles = 70;
        const particleSize = 1.5;
        const particleSpeed = 0.5;
        const lineColor = 'rgba(0, 102, 255, 0.1)';
        const connectionDistance = 100;

        function setCanvasSize() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }

        class Particle {
            constructor(x, y) {
                this.x = x;
                this.y = y;
                this.size = particleSize * (Math.random() * 0.5 + 0.7);
                this.speedX = (Math.random() * 2 - 1) * particleSpeed;
                this.speedY = (Math.random() * 2 - 1) * particleSpeed;
                this.color = `hsl(${Math.random() * 360}, 70%, 60%)`; // Random vibrant colors
            }

            update() {
                this.x += this.speedX;
                this.y += this.speedY;

                if (this.x > canvas.width || this.x < 0) {
                    this.speedX *= -1;
                }
                if (this.y > canvas.height || this.y < 0) {
                    this.speedY *= -1;
                }
            }

            draw() {
                ctx.fillStyle = this.color;
                ctx.beginPath();
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        function initParticles() {
            particles = [];
            for (let i = 0; i < numParticles; i++) {
                const x = Math.random() * canvas.width;
                const y = Math.random() * canvas.height;
                particles.push(new Particle(x, y));
            }
        }

        function animateParticles() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < particles.length; i++) {
                particles[i].update();
                particles[i].draw();

                for (let j = i; j < particles.length; j++) {
                    const dx = particles[i].x - particles[j].x;
                    const dy = particles[i].y - particles[j].y;
                    const distance = Math.sqrt(dx * dx + dy * dy);

                    if (distance < connectionDistance) {
                        ctx.strokeStyle = lineColor;
                        ctx.lineWidth = 0.5;
                        ctx.beginPath();
                        ctx.moveTo(particles[i].x, particles[i].y);
                        ctx.lineTo(particles[j].x, particles[j].y);
                        ctx.stroke();
                    }
                }
            }
            requestAnimationFrame(animateParticles);
        }

        window.addEventListener('resize', () => {
            setCanvasSize();
            initParticles();
        });

        setCanvasSize();
        initParticles();
        animateParticles();


        // Delete Confirmation Modal Logic
        const deleteModal = document.getElementById('deleteModal');
        const cancelDeleteBtn = document.getElementById('cancelDelete');
        const modalDeleteId = document.getElementById('modalDeleteId');
        const deleteButtons = document.querySelectorAll('.delete-btn');

        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const itemId = this.dataset.id;
                modalDeleteId.value = itemId;
                deleteModal.style.display = 'flex'; // Show modal using flex for centering
            });
        });

        cancelDeleteBtn.addEventListener('click', function() {
            deleteModal.style.display = 'none'; // Hide modal
        });

        // Close modal if clicking outside
        window.addEventListener('click', function(event) {
            if (event.target == deleteModal) {
                deleteModal.style.display = 'none';
            }
        });
    </script>
</body>
</html>