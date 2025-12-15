<?php
session_start();

const FORM_CATEGORIES = [
    'academic' => [
        'title' => 'Academic Excellence',
        'icon' => 'fas fa-graduation-cap',
        'color' => '#3B82F6',
        'bgColor' => '#EFF6FF'
    ],
    'wellness' => [
        'title' => 'Health & Wellness',
        'icon' => 'fas fa-heart-pulse',
        'color' => '#10B981',
        'bgColor' => '#ECFDF5'
    ],
    'technical' => [
        'title' => 'Technical Skills',
        'icon' => 'fas fa-code',
        'color' => '#8B5CF6',
        'bgColor' => '#F3E8FF'
    ],
    'core' => [
        'title' => 'Core Specialization',
        'icon' => 'fas fa-microchip',
        'color' => '#F59E0B',
        'bgColor' => '#FFFBEB'
    ],
    'aptitude' => [
        'title' => 'Logical Reasoning',
        'icon' => 'fas fa-brain',
        'color' => '#EF4444',
        'bgColor' => '#FEF2F2'
    ],
    'soft' => [
        'title' => 'Interpersonal Skills',
        'icon' => 'fas fa-users',
        'color' => '#EC4899',
        'bgColor' => '#FDF2F8'
    ],
    'career' => [
        'title' => 'Career Development',
        'icon' => 'fas fa-rocket',
        'color' => '#06B6D4',
        'bgColor' => '#ECFEFF'
    ]
];

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_roll_number'])) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    $_SESSION['form_message'] = ['type' => 'error', 'text' => 'Please log in as a student to access this page.'];
    header("Location: login.php");
    exit();
}

$loggedInUserId = htmlspecialchars($_SESSION['user_id'] ?? '1');
$loggedInRollNumber = htmlspecialchars($_SESSION['user_roll_number'] ?? 'R12345');
$loggedInName = htmlspecialchars($_SESSION['user_name'] ?? 'SARAVANAN M (7376221EC294)');

$form_message = $_SESSION['form_message'] ?? null;
unset($_SESSION['form_message']);

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

$dataFilePath = __DIR__ . '/student_data.json';

function loadStudentData(string $filePath): array {
    if (!file_exists($filePath)) {
        return [];
    }
    $fileContent = file_get_contents($filePath);
    if ($fileContent === false) {
        return [];
    }
    $data = json_decode($fileContent, true);
    return is_array($data) ? $data : [];
}

function saveStudentData(string $filePath, array $data): bool {
    $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($jsonData === false) {
        error_log("Failed to encode JSON data for saving.");
        return false;
    }
    return file_put_contents($filePath, $jsonData) !== false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !validateCsrfToken($_POST['csrf_token'])) {
        $_SESSION['form_message'] = ['type' => 'error', 'text' => 'Invalid form submission. Please try again.'];
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }

    if (isset($_POST['action']) && $_POST['action'] === 'clear_all_data') {
        $currentData = loadStudentData($dataFilePath);
        $updatedData = array_filter($currentData, function($entry) use ($loggedInUserId) {
            return ($entry['student_user_id'] ?? '') !== $loggedInUserId;
        });

        if (saveStudentData($dataFilePath, array_values($updatedData))) {
            $_SESSION['form_message'] = ['type' => 'success', 'text' => 'All your development data has been cleared successfully.'];
        } else {
            $_SESSION['form_message'] = ['type' => 'error', 'text' => 'Failed to clear data. Please try again.'];
        }
        header("Location: " . $_SERVER['REQUEST_URI']);
        exit();
    }
}

$allStudentData = loadStudentData($dataFilePath);
$currentUserData = array_filter($allStudentData, function($entry) use ($loggedInUserId) {
    return ($entry['student_user_id'] ?? '') === $loggedInUserId;
});

$categoryData = [];
foreach (array_keys(FORM_CATEGORIES) as $key) {
    $categoryData[$key] = [
        'ratingsByDate' => [],
        'focusAreaRatings' => [],
        'allRatings' => []
    ];
}

$overallRatingsByDate = [];
$latestRatings = [];
$allFocusAreaRatings = [];
$allIndividualRatings = [];

foreach ($currentUserData as $entry) {
    $date = $entry['form_date'] ?? 'unknown_date';

    if (!isset($overallRatingsByDate[$date])) {
        $overallRatingsByDate[$date] = [];
    }

    foreach (FORM_CATEGORIES as $key => $details) {
        if (isset($entry[$key]) && ($entry[$key]['rating'] !== null || !empty($entry[$key]['area']))) {
            $rating = isset($entry[$key]['rating']) ? (int)$entry[$key]['rating'] : null;
            $area = $entry[$key]['area'] ?? '';
            $otherInput = $entry[$key]['area_other'] ?? '';

            if ($rating !== null) {
                if (!isset($categoryData[$key]['ratingsByDate'][$date])) {
                    $categoryData[$key]['ratingsByDate'][$date] = [];
                }
                $categoryData[$key]['ratingsByDate'][$date][] = [
                    'area' => $area,
                    'otherInput' => $otherInput,
                    'rating' => $rating,
                    'date' => $date
                ];
                $categoryData[$key]['allRatings'][] = $rating;
                $overallRatingsByDate[$date][] = $rating;
                $allIndividualRatings[] = ['date' => $date, 'category' => $key, 'rating' => $rating];

                if (!isset($latestRatings[$key]) || strtotime($date) > strtotime($latestRatings[$key]['date'])) {
                    $latestRatings[$key] = [
                        'rating' => $rating,
                        'date' => $date,
                        'area' => $area,
                        'otherInput' => $otherInput
                    ];
                }
            }

            $actualAreaName = (!empty($otherInput)) ? $otherInput : ($area ?: 'General ' . $details['title']);
            if (!isset($categoryData[$key]['focusAreaRatings'][$actualAreaName])) {
                $categoryData[$key]['focusAreaRatings'][$actualAreaName] = [];
            }
            $categoryData[$key]['focusAreaRatings'][$actualAreaName][] = [
                'area' => $area,
                'otherInput' => $otherInput,
                'rating' => $rating,
                'date' => $date
            ];

            if ($rating !== null) {
                if (!isset($allFocusAreaRatings[$actualAreaName])) {
                    $allFocusAreaRatings[$actualAreaName] = [];
                }
                $allFocusAreaRatings[$actualAreaName][] = $rating;
            }
        }
    }
}

$overallAverages = [];
foreach (FORM_CATEGORIES as $key => $details) {
    $ratings = $categoryData[$key]['allRatings'];
    $overallAverages[$key] = count($ratings) > 0 ? array_sum($ratings) / count($ratings) : 0;
}

$overallAverageProgress = [];
uksort($overallRatingsByDate, function($a, $b) {
    return strtotime($a) - strtotime($b);
});

foreach ($overallRatingsByDate as $date => $ratings) {
    if (!empty($ratings)) {
        $overallAverageProgress[$date] = array_sum($ratings) / count($ratings);
    } else {
        $overallAverageProgress[$date] = null;
    }
}
$overallAverageProgress = array_filter($overallAverageProgress, function($val) { return $val !== null; });

$aggregatedFocusAreaAverages = [];
foreach ($allFocusAreaRatings as $areaName => $ratings) {
    if (!empty($ratings)) {
        $aggregatedFocusAreaAverages[$areaName] = array_sum($ratings) / count($ratings);
    }
}
arsort($aggregatedFocusAreaAverages);

$csrfToken = generateCsrfToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Development Analytics Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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

            --sidebar-blue: #1a5632;
            --sidebar-blue-dark: #0d3b1e;
            --gradient-start: #2563eb;
            --gradient-end: #1d4ed8;
        }

        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--gray-light);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
            transition: margin-left var(--transition-speed);
        }

        #sidebar {
            width: var(--sidebar-width);
            background: linear-gradient(160deg, var(--sidebar-blue-dark), var(--sidebar-blue));
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
            animation-delay: calc(var(--i, 0) * var(--nav-item-delay));
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
            color: var(--sidebar-blue);
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }

        .sidebar-link.active i {
            color: var(--sidebar-blue);
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

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
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
            transition: max-height 0.4s ease-out;
        }

        .dropdown-parent.active .dropdown {
            max-height: 500px;
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
            animation-delay: calc(var(--i, 0) * var(--nav-item-delay));
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
        }

        .sidebar-footer {
            padding: 1.5rem;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            margin-top: auto;
            background: rgba(0, 0, 0, 0.2);
            flex-shrink: 0;
            animation: fadeIn 0.5s 0.6s both;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
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
            color: var(--sidebar-blue);
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

        body.sidebar-collapsed #sidebar {
            width: var(--sidebar-collapsed-width);
        }

        body.sidebar-collapsed .logo-text,
        body.sidebar-collapsed .link-text,
        body.sidebar-collapsed .nav-section-title,
        body.sidebar-collapsed .user-name,
        body.sidebar-collapsed .user-role,
        body.sidebar-collapsed .dropdown-toggle::after {
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
        }

        body.sidebar-collapsed .sidebar-header {
            justify-content: center;
        }

        body.sidebar-collapsed .toggle-btn i {
            transform: rotate(180deg);
        }

        body.sidebar-collapsed .logo-icon {
            margin: 0 auto;
        }

        .menu-toggle {
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: var(--sidebar-blue);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 900;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.4);
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
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.6);
        }

        #main-content {
            margin-left: var(--sidebar-width);
            flex-grow: 1;
            padding: 20px;
            transition: margin-left var(--transition-speed);
            overflow-y: auto;
            background: linear-gradient(135deg, #a7bfe8 0%, #619af0 100%);
            min-height: 100vh;
            color: #2c3e50;
        }

        body.sidebar-collapsed #main-content {
            margin-left: var(--sidebar-collapsed-width);
        }

        @media (max-width: 992px) {
            #sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width);
                box-shadow: 5px 0 30px rgba(0, 0, 0, 0.5);
            }

            #sidebar.active {
                transform: translateX(0);
            }

            .menu-toggle {
                display: flex;
            }

            body.sidebar-collapsed .menu-toggle {
                display: flex;
            }

            #main-content {
                margin-left: 0;
            }

            body.sidebar-active #main-content {
                position: absolute;
                left: 0;
                top: 0;
                right: 0;
                bottom: 0;
                overflow: hidden;
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

            body.sidebar-collapsed .logo-text,
            body.sidebar-collapsed .link-text,
            body.sidebar-collapsed .nav-section-title,
            body.sidebar-collapsed .user-name,
            body.sidebar-collapsed .user-role,
            body.sidebar-collapsed .dropdown-toggle::after {
                opacity: 1;
                transform: translateX(0);
            }

            body.sidebar-collapsed #sidebar {
                width: var(--sidebar-width);
                transform: translateX(-100%);
            }
        }

        @media (max-width: 768px) {
            #sidebar {
                width: 260px;
            }
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
            border-radius: 1.5rem;
        }

        .header-gradient {
            background: linear-gradient(45deg, #4F46E5 0%, #7C3AED 100%);
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            transition: all 0.3s ease;
            transform: translateZ(0);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .stat-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 10px 15px rgba(0,0,0,0.2);
        }

        .chart-container {
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }

        .chart-container:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.15);
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(8px);
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.3s ease-out;
        }

        .modal-content {
            background: white;
            padding: 2.5rem;
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 90%;
            text-align: center;
            animation: popIn 0.4s ease-out forwards;
        }

        .message-box-success {
            background-color: #dcfce7;
            color: #16a34a;
            border-left: 5px solid #16a34a;
        }

        .message-box-error {
            background-color: #fee2e2;
            color: #dc2626;
            border-left: 5px solid #dc2626;
        }

        .btn-primary {
            background: linear-gradient(45deg, #4F46E5, #7C3AED);
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #4338CA, #6D28D9);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(67, 56, 202, 0.4);
        }

        .btn-danger {
            background: linear-gradient(45deg, #EF4444, #EC4899);
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }
        .btn-danger:hover {
            background: linear-gradient(45deg, #DC2626, #DB2777);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(220, 38, 38, 0.4);
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
                
                <a href="addash.php" class="sidebar-link" id="admin-dashboard-link">
                    <i class="fas fa-tachometer-alt"></i> <span class="link-text">Admin Dashboard</span>
                </a>
            </div>

            <div class="nav-section">
                <div class="nav-section-title">Content Management</div>
               <a href="admin_post.php" class="sidebar-link" id="upload-post">
    <i class="fas 	fas fa-upload"></i> <span class="link-text">Post Upload</span>
</a>

                <a href="adpost_read.php" class="sidebar-link" id="post-read">
                    <i class="fas fa-file-alt"></i> <span class="link-text">Post Read</span>
                </a>

                 <a href="colloction/apps3.html" class="sidebar-link" id="post-read" target="_blank">
                    <i class="fas fa-tools"></i> <span class="link-text"> Apps </span>
                </a>

            </div>

            <div class="nav-section">
                <div class="nav-section-title">Community & Features</div>
                <a href="adai.php" class="sidebar-link" id="ai-section">
                    <i class="fas fa-robot"></i> <span class="link-text">AI Section</span>
                </a>
                <a href="index.html" class="sidebar-link" id="wellness-section" target="_blank">
                <i class="fas fa-heartbeat"></i> <span class="link-text">Wellness</span>
                </a>
                <a href="adtalent.php" class="sidebar-link" id="club-events-section">
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
    
    <div id="main-content">
        <div class="header-gradient text-white py-8 mb-10 shadow-lg">
            <div class="max-w-7xl mx-auto px-6 lg:px-8">
                <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                    <div class="flex items-center space-x-5 mb-5 lg:mb-0">
                        <div class="bg-white bg-opacity-20 rounded-full p-4 transform rotate-6 animate-pulse-slow">
                            <i class="fas fa-chart-pie text-3xl"></i>
                        </div>
                        <div>
                            <h1 class="text-4xl font-extrabold tracking-tight">Development Analytics</h1>
                            <p class="text-blue-200 text-lg mt-1">Deep insights into your progress and strengths</p>
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-3 sm:space-y-0 sm:space-x-5">
                        <div class="bg-white bg-opacity-15 rounded-xl px-5 py-3 text-sm">
                            <p class="text-blue-100">Currently logged in as:</p>
                            <p class="font-semibold text-white text-lg"><?= $loggedInName ?></p>
                            <p class="text-blue-200"><?= $loggedInRollNumber ?></p>
                        </div>
                        <button id="logoutButton" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-6 py-3 rounded-xl font-semibold transition-all duration-300 transform hover:scale-105 shadow-md">
                            <i class="fas fa-sign-out-alt mr-2"></i>Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-6 lg:px-8 pb-16">
            <div id="messageBox" class="hidden mb-8 px-7 py-5 rounded-xl shadow-xl animate-fade-in transition-all duration-300 ease-in-out text-lg font-medium">
                <?php if ($form_message): ?>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const messageBox = document.getElementById('messageBox');
                            messageBox.innerHTML = '<div class="flex items-center space-x-3"><i class="text-2xl fas fa-<?= $form_message['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' ?>"></i><span><?= addslashes($form_message['text']) ?></span></div>';
                            messageBox.classList.remove('hidden');
                            if ('<?= $form_message['type'] ?>' === 'success') {
                                messageBox.classList.add('message-box-success');
                            } else {
                                messageBox.classList.add('message-box-error');
                            }
                            setTimeout(() => {
                                messageBox.style.opacity = '0';
                                setTimeout(() => messageBox.classList.add('hidden'), 300);
                            }, 5000);
                        });
                    </script>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 animate-slide-up">
                <?php
                $totalEntries = count($currentUserData);
                $avgOverall = 0;
                if (count($overallAverages) > 0) {
                    $validAverages = array_filter($overallAverages, function($avg) { return $avg > 0; });
                    $avgOverall = count($validAverages) > 0 ? array_sum($validAverages) / count($validAverages) : 0;
                }

                $bestCategoryKey = 'none';
                if (!empty($overallAverages)) {
                    $maxAvg = -1;
                    foreach ($overallAverages as $key => $avg) {
                        if ($avg > $maxAvg) {
                            $maxAvg = $avg;
                            $bestCategoryKey = $key;
                        }
                    }
                }
                $bestCategoryTitle = FORM_CATEGORIES[$bestCategoryKey]['title'] ?? 'N/A';

                $activeAreas = 0;
                foreach ($categoryData as $data) {
                    $activeAreas += count(array_filter($data['focusAreaRatings'], function($arr) { return !empty($arr); }));
                }
                ?>

                <div class="stat-card rounded-2xl p-7 text-center text-white flex flex-col items-center justify-center shadow-lg transform hover:scale-105 hover:shadow-2xl transition-all duration-300">
                    <i class="fas fa-file-alt text-4xl text-blue-300 mb-3"></i>
                    <div class="text-5xl font-extrabold text-blue-100 drop-shadow-md"><?= $totalEntries ?></div>
                    <div class="text-sm font-medium text-blue-100 opacity-90">Total Submissions</div>
                </div>

                <div class="stat-card rounded-2xl p-7 text-center text-white flex flex-col items-center justify-center shadow-lg transform hover:scale-105 hover:shadow-2xl transition-all duration-300">
                    <i class="fas fa-star text-4xl text-green-300 mb-3"></i>
                    <div class="text-5xl font-extrabold text-green-100 drop-shadow-md"><?= number_format($avgOverall, 1) ?>/5</div>
                    <div class="text-sm font-medium text-green-100 opacity-90">Overall Average</div>
                </div>

                <div class="stat-card rounded-2xl p-7 text-center text-white flex flex-col items-center justify-center shadow-lg transform hover:scale-105 hover:shadow-2xl transition-all duration-300">
                    <i class="fas fa-trophy text-4xl text-yellow-300 mb-3"></i>
                    <div class="text-2xl lg:text-3xl font-extrabold text-yellow-100 drop-shadow-md leading-tight"><?= $bestCategoryTitle ?></div>
                    <div class="text-sm font-medium text-yellow-100 opacity-90 mt-1">Top Performance</div>
                </div>

                <div class="stat-card rounded-2xl p-7 text-center text-white flex flex-col items-center justify-center shadow-lg transform hover:scale-105 hover:shadow-2xl transition-all duration-300">
                    <i class="fas fa-bullseye text-4xl text-purple-300 mb-3"></i>
                    <div class="text-5xl font-extrabold text-purple-100 drop-shadow-md"><?= $activeAreas ?></div>
                    <div class="text-sm font-medium text-purple-100 opacity-90">Active Focus Areas</div>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Development Progress Timeline</h2>
                    <p class="text-gray-600 text-lg">See how your skills have evolved over time in each domain.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                    <?php foreach (FORM_CATEGORIES as $key => $details): ?>
                        <div class="chart-container bg-white rounded-2xl p-6 shadow-xl border border-gray-200">
                            <div class="flex items-center mb-4 space-x-3">
                                <div class="p-3 rounded-lg flex-shrink-0" style="background-color: <?= $details['bgColor'] ?>;">
                                    <i class="<?= $details['icon'] ?> text-xl" style="color: <?= $details['color'] ?>;"></i>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-gray-800"><?= $details['title'] ?></h3>
                                    <p class="text-md text-gray-600">Average: <span class="font-semibold text-gray-700"><?= number_format($overallAverages[$key], 1) ?>/5</span></p>
                                </div>
                            </div>
                            <div class="h-72 w-full">
                                <canvas id="<?= $key ?>DevelopmentChart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Overall Progress Trend</h2>
                    <p class="text-gray-600 text-lg">Your average development progress across all categories over time.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 h-96">
                    <canvas id="overallProgressLineChart"></canvas>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Category Trends Over Time</h2>
                    <p class="text-gray-600 text-lg">Stacked view of category contributions to overall ratings per date.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 h-96">
                    <canvas id="categoryStackedBarChart"></canvas>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Focus Area Performance</h2>
                    <p class="text-gray-600 text-lg">Dive deeper into your average ratings for specific areas.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                    <?php foreach (FORM_CATEGORIES as $key => $details): ?>
                        <div class="chart-container bg-white rounded-2xl p-6 shadow-xl border border-gray-200">
                            <div class="flex items-center mb-4 space-x-3">
                                <div class="p-3 rounded-lg flex-shrink-0" style="background-color: <?= $details['bgColor'] ?>;">
                                    <i class="<?= $details['icon'] ?> text-xl" style="color: <?= $details['color'] ?>;"></i>
                                </div>
                                <h3 class="text-xl font-bold text-gray-800"><?= $details['title'] ?> Breakdown</h3>
                            </div>
                            <div class="h-72 w-full">
                                <canvas id="<?= $key ?>FocusAreaBarChart" class="w-full h-full"></canvas>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Top & Bottom Focus Areas</h2>
                    <p class="text-gray-600 text-lg">Your highest and lowest performing specific focus areas.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 h-96">
                    <canvas id="topBottomFocusAreasChart"></canvas>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Category Rating Distribution</h2>
                    <p class="text-gray-600 text-lg">Distribution of ratings (1-5) for each category.</p>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-8">
                    <?php foreach (FORM_CATEGORIES as $key => $details): ?>
                        <div class="chart-container bg-white rounded-2xl p-6 shadow-xl border border-gray-200 flex justify-center items-center h-96">
                            <div class="relative w-full max-w-lg h-full">
                                <canvas id="<?= $key ?>RatingDistributionChart"></canvas>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                <div class="glass-effect rounded-3xl p-8 animate-slide-up shadow-2xl">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Overall Skill Distribution</h2>
                        <p class="text-gray-600 text-lg">See the relative proportion of your focus across different skill levels (1-5).</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 flex justify-center items-center h-96">
                        <div class="relative w-full max-w-lg h-full">
                            <canvas id="overallSkillDistributionChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="glass-effect rounded-3xl p-8 animate-slide-up shadow-2xl">
                    <div class="text-center mb-8">
                        <h2 class="text-3xl font-bold text-gray-800 mb-2">Category Performance Radar</h2>
                        <p class="text-gray-600 text-lg">A comparative view of your average performance across all categories.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 flex justify-center items-center h-96">
                        <div class="relative w-full max-w-lg h-full">
                            <canvas id="overallRadarChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Individual Entry Impact</h2>
                    <p class="text-gray-600 text-lg">Visualize individual ratings, their date, and category's average impact.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 h-96">
                    <canvas id="individualEntryBubbleChart"></canvas>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Category Dominance</h2>
                    <p class="text-gray-600 text-lg">Proportional view of each category's average rating.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 flex justify-center items-center h-96">
                    <div class="relative w-full max-w-lg h-full">
                        <canvas id="categoryDominancePolarChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Performance Overview</h2>
                    <p class="text-gray-600 text-lg">Compare your average performance across all key development categories.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 h-96">
                    <canvas id="overallAverageBarChart"></canvas>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Latest Category Ratings</h2>
                    <p class="text-gray-600 text-lg">Your most recent rating for each development category.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 h-96">
                    <canvas id="latestCategoryRatingsChart"></canvas>
                </div>
            </div>

            <div class="glass-effect rounded-3xl p-8 mb-10 animate-slide-up shadow-2xl">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Detailed Submission Log</h2>
                    <p class="text-gray-600 text-lg">A comprehensive list of all your development entries.</p>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-xl border border-gray-200 overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tl-lg">Date</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Focus Area</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider rounded-tr-lg">Rating</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($currentUserData)): ?>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">No development entries found.</td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $sortedUserData = $currentUserData;
                                usort($sortedUserData, function($a, $b) {
                                    return strtotime($b['form_date']) - strtotime($a['form_date']);
                                });
                                foreach ($sortedUserData as $entry):
                                    $entryDate = htmlspecialchars($entry['form_date']);
                                    foreach (FORM_CATEGORIES as $catKey => $catDetails):
                                        if (isset($entry[$catKey]) && ($entry[$catKey]['rating'] !== null || !empty($entry[$catKey]['area']))):
                                            $areaName = htmlspecialchars($entry[$catKey]['area'] ?? 'N/A');
                                            if ($areaName === 'Other' && !empty($entry[$catKey]['area_other'])) {
                                                $areaName = htmlspecialchars($entry[$catKey]['area_other']);
                                            }
                                            $ratingValue = htmlspecialchars($entry[$catKey]['rating'] ?? 'N/A');
                                ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= $entryDate ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $catDetails['title'] ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700"><?= $areaName ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                <?php
                                                    if ($ratingValue >= 4) echo 'bg-green-100 text-green-800';
                                                    else if ($ratingValue >= 3) echo 'bg-yellow-100 text-yellow-800';
                                                    else if ($ratingValue >= 1) echo 'bg-red-100 text-red-800';
                                                    else echo 'bg-gray-100 text-gray-800';
                                                ?>">
                                                <?= $ratingValue ?>/5
                                            </span>
                                        </td>
                                    </tr>
                                <?php
                                        endif;
                                    endforeach;
                                endforeach;
                                ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center items-center space-y-5 sm:space-y-0 sm:space-x-8 animate-fade-in mt-12">
                <a href="datainput.php" class="btn-primary text-white font-semibold py-4 px-10 rounded-xl shadow-lg transform hover:scale-105 transition duration-300 ease-in-out text-lg flex items-center justify-center w-full sm:w-auto">
                    <i class="fas fa-plus-circle mr-3 text-xl"></i>Add New Entry
                </a>
                <button type="button" id="openClearDataModal" class="btn-danger text-white font-semibold py-4 px-10 rounded-xl shadow-lg transform hover:scale-105 transition duration-300 ease-in-out text-lg flex items-center justify-center w-full sm:w-auto">
                    <i class="fas fa-eraser mr-3 text-xl"></i>Clear All Data
                </button>
            </div>
        </div>

        <div id="clearDataModal" class="modal">
            <div class="modal-content">
                <div class="mb-6">
                    <div class="bg-red-100 rounded-full w-20 h-20 flex items-center justify-center mx-auto mb-5 animate-pop-in">
                        <i class="fas fa-triangle-exclamation text-red-600 text-4xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-3">Confirm Data Deletion</h2>
                    <p class="text-gray-600 text-md leading-relaxed">Are you absolutely sure you want to permanently delete all your development data? This action is irreversible and cannot be undone.</p>
                </div>
                <div class="flex flex-col sm:flex-row justify-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <button id="cancelClearData" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200 shadow-md">
                        Cancel
                    </button>
                    <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>" style="display: inline;">
                        <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                        <input type="hidden" name="action" value="clear_all_data">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold py-3 px-8 rounded-lg transition-colors duration-200 shadow-md">
                            <i class="fas fa-trash-alt mr-2"></i>Delete Data
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const menuToggle = document.getElementById('menuToggle');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const body = document.body;
            const mainContent = document.getElementById('main-content');
            const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
            const allDropdownParents = document.querySelectorAll('.dropdown-parent');

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
                    this.classList.toggle('active');
                    body.classList.toggle('sidebar-active');
                    closeAllDropdowns();
                });
            }

            if (mainContent) {
                mainContent.addEventListener('click', function(event) {
                    if (window.innerWidth <= 992 && sidebar.classList.contains('active')) {
                        sidebar.classList.remove('active');
                        if (menuToggle) menuToggle.classList.remove('active');
                        body.classList.remove('sidebar-active');
                    }
                    if (!event.target.closest('.dropdown-parent')) {
                        closeAllDropdowns();
                    }
                });
            }

            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', function() {
                    body.classList.toggle('sidebar-collapsed');
                    if (body.classList.contains('sidebar-collapsed')) {
                        closeAllDropdowns();
                    }
                });
            }

            const currentPage = window.location.pathname.split('/').pop() || 'index.php';
            const navLinks = document.querySelectorAll('.sidebar-link, .dropdown-item');

            navLinks.forEach(link => {
                const linkHref = link.getAttribute('href');
                const normalizedLinkHref = linkHref ? (linkHref.includes('/') ? linkHref.split('/').pop() : linkHref) : '';

                if (normalizedLinkHref === currentPage) {
                    link.classList.add('active');

                    if (link.classList.contains('dropdown-item')) {
                        const parentDropdown = link.closest('.dropdown-parent');
                        if (parentDropdown) {
                            parentDropdown.classList.add('active');
                            const toggle = parentDropdown.querySelector('.dropdown-toggle');
                            if (toggle) toggle.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            });

            const allLinksAndItems = document.querySelectorAll('.sidebar-nav .sidebar-link, .sidebar-nav .dropdown-item');
            allLinksAndItems.forEach((el, index) => {
                el.style.setProperty('--i', index + 1);
            });

            const messageBox = document.getElementById('messageBox');
            if (messageBox.innerHTML.trim() !== '') {
                messageBox.classList.remove('hidden');
                setTimeout(() => {
                    messageBox.style.opacity = '0';
                    setTimeout(() => messageBox.classList.add('hidden'), 300);
                }, 5000);
            }

            document.getElementById('logoutButton').addEventListener('click', function() {
                window.location.href = 'login.php?action=logout';
            });
            
            const clearDataModal = document.getElementById('clearDataModal');
            const openClearDataBtn = document.getElementById('openClearDataModal');
            const cancelClearDataBtn = document.getElementById('cancelClearData');

            openClearDataBtn.addEventListener('click', () => {
                clearDataModal.style.display = 'flex';
            });

            cancelClearDataBtn.addEventListener('click', () => {
                clearDataModal.style.display = 'none';
            });

            window.addEventListener('click', (event) => {
                if (event.target == clearDataModal) {
                    clearDataModal.style.display = 'none';
                }
            });
        });

        const categoryData = <?= json_encode($categoryData) ?>;
        const overallAverages = <?= json_encode($overallAverages) ?>;
        const formCategories = <?= json_encode(FORM_CATEGORIES) ?>;
        const overallAverageProgress = <?= json_encode($overallAverageProgress) ?>;
        const latestRatings = <?= json_encode($latestRatings) ?>;
        const allIndividualRatings = <?= json_encode($allIndividualRatings) ?>;
        const aggregatedFocusAreaAverages = <?= json_encode($aggregatedFocusAreaAverages) ?>;

        function getAverageRatingsForChart(ratingsByDate) {
            const labels = Object.keys(ratingsByDate).sort();
            const data = labels.map(date => {
                const ratings = ratingsByDate[date]
                                        .filter(item => item.rating !== null && item.rating !== undefined)
                                        .map(item => item.rating);
                return ratings.length > 0 ? ratings.reduce((a, b) => a + b, 0) / ratings.length : null;
            }).filter(d => d !== null);
            const filteredLabels = labels.filter(date => {
                const ratings = ratingsByDate[date].filter(item => item.rating !== null && item.rating !== undefined);
                return ratings.length > 0;
            });
            return { labels: filteredLabels, data };
        }

        function calculateAverageOfFocusAreaRatings(focusAreaRatingsObject) {
            const averages = {};
            for (const area in focusAreaRatingsObject) {
                const ratingsArray = focusAreaRatingsObject[area]
                                        .filter(item => item.rating !== null && item.rating !== undefined)
                                        .map(item => item.rating);
                if (ratingsArray.length > 0) {
                    const sum = ratingsArray.reduce((acc, curr) => acc + curr, 0);
                    averages[area] = sum / ratingsArray.length;
                }
            }
            return averages;
        }

        function prepareFocusAreaDataForChart(focusAreaRatingsObject, color) {
            const averages = calculateAverageOfFocusAreaRatings(focusAreaRatingsObject);
            const labels = Object.keys(averages);
            const data = Object.values(averages);
            const rawOtherData = {};

            for (const area of labels) {
                const entries = focusAreaRatingsObject[area];
                const lastEntryWithOther = entries.slice().reverse().find(item => item.area === 'Other' && item.otherInput !== '');
                if (lastEntryWithOther) {
                    rawOtherData[area] = lastEntryWithOther.otherInput;
                }
            }
            return { labels, data, color, rawOtherData };
        }

        function createLineChart(ctx, dataByDate, overallAverage, color, chartTitle = 'Progress') {
            const chartData = getAverageRatingsForChart(dataByDate);
            if (chartData.labels.length === 0) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No data available for this chart yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color + '40');
            gradient.addColorStop(1, color + '10');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: chartTitle,
                            data: chartData.data,
                            backgroundColor: gradient,
                            borderColor: color,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: color,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointHoverBackgroundColor: color,
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 3
                        },
                        {
                            label: 'Category Average',
                            data: Array(chartData.labels.length).fill(overallAverage),
                            borderColor: '#8B5CF6',
                            borderWidth: 2,
                            borderDash: [8, 4],
                            fill: false,
                            pointRadius: 0,
                            tension: 0
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: color,
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return `Date: ${context[0].label}`;
                                },
                                label: function(context) {
                                    if (context.datasetIndex === 0) {
                                        return `${chartTitle}: ${context.raw.toFixed(1)}/5`;
                                    }
                                    return `Category Average: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });
        }

        function createOverallAverageLineChart(ctx, dataByDate) {
            const labels = Object.keys(dataByDate).sort();
            const data = labels.map(date => dataByDate[date]);

            if (data.length === 0) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No data available for this chart yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            const color = '#3B82F6';
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color + '40');
            gradient.addColorStop(1, color + '10');

            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [
                        {
                            label: 'Overall Average Rating',
                            data: data,
                            backgroundColor: gradient,
                            borderColor: color,
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointBackgroundColor: color,
                            pointBorderColor: '#ffffff',
                            pointBorderWidth: 2,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointHoverBackgroundColor: color,
                            pointHoverBorderColor: '#ffffff',
                            pointHoverBorderWidth: 3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: color,
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    return `Date: ${context[0].label}`;
                                },
                                label: function(context) {
                                    return `Overall Average: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });
        }

        function createStackedBarChart(ctx, categoryData, formCategories) {
            const allDates = new Set();
            for (const categoryKey in categoryData) {
                for (const date in categoryData[categoryKey].ratingsByDate) {
                    allDates.add(date);
                }
            }
            const sortedDates = Array.from(allDates).sort();

            if (sortedDates.length === 0) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No data available for this chart yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            const datasets = [];
            for (const categoryKey in formCategories) {
                const categoryRatingsByDate = categoryData[categoryKey].ratingsByDate;
                const data = sortedDates.map(date => {
                    const ratings = categoryRatingsByDate[date]
                                    ?.filter(item => item.rating !== null && item.rating !== undefined)
                                    .map(item => item.rating) || [];
                    return ratings.length > 0 ? ratings.reduce((a, b) => a + b, 0) / ratings.length : 0;
                });

                datasets.push({
                    label: formCategories[categoryKey].title,
                    data: data,
                    backgroundColor: formCategories[categoryKey].color + 'CC',
                    borderColor: formCategories[categoryKey].color,
                    borderWidth: 1,
                    hoverBackgroundColor: formCategories[categoryKey].color
                });
            }

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: sortedDates,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        },
                        y: {
                            stacked: true,
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 12,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                color: '#334155',
                                boxWidth: 20,
                                boxHeight: 10,
                                padding: 10
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.dataset.label}: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function createBarChart(ctx, chartData, color) {
            if (chartData.labels.length === 0) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No data available for this chart yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: [{
                        label: 'Average Rating',
                        data: chartData.data,
                        backgroundColor: chartData.data.map(val => val >= 4 ? color : (val >= 3 ? color + '80' : color + '40')),
                        borderColor: color,
                        borderWidth: 1,
                        borderRadius: 8,
                        hoverBackgroundColor: color
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: color,
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    const label = context[0].label;
                                    return chartData.rawOtherData[label] ? `Focus: ${chartData.rawOtherData[label]}` : `Area: ${label}`;
                                },
                                label: function(context) {
                                    return `Rating: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });
        }

        function createTopBottomFocusAreasChart(ctx, aggregatedFocusAreaAverages, formCategories) {
            const sortedAreas = Object.entries(aggregatedFocusAreaAverages)
                                    .sort(([,a],[,b]) => b - a);
            const topN = 5;
            const bottomN = 5;

            const topAreas = sortedAreas.slice(0, topN);
            const bottomAreas = sortedAreas.slice(Math.max(sortedAreas.length - bottomN, 0));

            const labels = [...topAreas.map(a => a[0]), ...bottomAreas.map(a => a[0])];
            const data = [...topAreas.map(a => a[1]), ...bottomAreas.map(a => a[1])];
            const backgroundColors = [...topAreas.map(() => '#22c55eCC'), ...bottomAreas.map(() => '#ef4444CC')];
            const borderColors = [...topAreas.map(() => '#15803d'), ...bottomAreas.map(() => '#b91c1c')];

            if (labels.length === 0) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No focus area data available yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Rating',
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `Rating: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });
        }

        function createOverallDoughnutChart(ctx, allRatings, chartTitle = 'Skill Distribution', legendPosition = 'right') {
            const ratingCounts = { '1': 0, '2': 0, '3': 0, '4': 0, '5': 0 };
            allRatings.forEach(rating => {
                if (rating >= 1 && rating <= 5) {
                    ratingCounts[rating]++;
                }
            });

            const labels = Object.keys(ratingCounts).map(r => `Rating ${r}`);
            const data = Object.values(ratingCounts);

            if (data.every(val => val === 0)) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No rating data available for this chart yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            const backgroundColors = [
                '#ef4444',
                '#f97316',
                '#facc15',
                '#22c55e',
                '#3b82f6'
            ];
            const borderColors = [
                '#b91c1c',
                '#c2410c',
                '#a16207',
                '#15803d',
                '#2563eb'
            ];

            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 2,
                        hoverOffset: 10
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: legendPosition,
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                color: '#334155',
                                boxWidth: 20,
                                boxHeight: 20,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    let label = context.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    if (context.parsed !== null) {
                                        label += context.parsed;
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    cutout: '70%'
                }
            });
        }

        function createOverallRadarChart(ctx, overallAverages, formCategories) {
            const labels = Object.values(formCategories).map(cat => cat.title);
            const data = Object.values(overallAverages);
            const backgroundColors = Object.values(formCategories).map(cat => cat.color + '40');
            const borderColors = Object.values(formCategories).map(cat => cat.color);

            if (data.every(val => val === 0)) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No data available for this chart yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Performance',
                        data: data,
                        backgroundColor: 'rgba(75, 192, 192, 0.4)',
                        borderColor: '#4F46E5',
                        borderWidth: 2,
                        pointBackgroundColor: '#4F46E5',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4F46E5',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#4F46E5',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    },
                    scales: {
                        r: {
                            angleLines: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)'
                            },
                            pointLabels: {
                                font: {
                                    size: 14,
                                    weight: '600',
                                    family: 'Inter'
                                },
                                color: '#334155'
                            },
                            ticks: {
                                beginAtZero: true,
                                max: 5,
                                stepSize: 1,
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function createIndividualEntryBubbleChart(ctx, allIndividualRatings, formCategories) {
            const datasets = [];
            const dates = Array.from(new Set(allIndividualRatings.map(d => d.date))).sort();

            if (allIndividualRatings.length === 0) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No individual entry data available yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            for (const categoryKey in formCategories) {
                const categoryEntries = allIndividualRatings.filter(d => d.category === categoryKey);
                const data = categoryEntries.map(entry => ({
                    x: dates.indexOf(entry.date),
                    y: entry.rating,
                    r: (formCategories[entry.category].color.length > 0 ? 10 : 0) // Radius based on category presence
                }));

                datasets.push({
                    label: formCategories[categoryKey].title,
                    data: data,
                    backgroundColor: formCategories[categoryKey].color + '80',
                    borderColor: formCategories[categoryKey].color,
                    borderWidth: 2,
                    hoverRadius: 12
                });
            }

            new Chart(ctx, {
                type: 'bubble',
                data: {
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            type: 'category',
                            labels: dates,
                            title: {
                                display: true,
                                text: 'Date',
                                color: '#334155',
                                font: { size: 14, weight: '600' }
                            },
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: { size: 12 }
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: 'Rating (1-5)',
                                color: '#334155',
                                font: { size: 14, weight: '600' }
                            },
                            min: 0,
                            max: 5,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: { size: 12 }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 12,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                color: '#334155',
                                boxWidth: 20,
                                boxHeight: 10,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    const date = dates[context.parsed.x];
                                    const rating = context.parsed.y;
                                    const category = context.dataset.label;
                                    return [`Category: ${category}`, `Date: ${date}`, `Rating: ${rating}/5`];
                                }
                            }
                        }
                    }
                }
            });
        }

        function createCategoryDominancePolarChart(ctx, overallAverages, formCategories) {
            const labels = Object.values(formCategories).map(cat => cat.title);
            const data = Object.values(overallAverages);
            const backgroundColors = Object.values(formCategories).map(cat => cat.color + 'CC');
            const borderColors = Object.values(formCategories).map(cat => cat.color);

            if (data.every(val => val === 0)) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No data available for this chart yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            new Chart(ctx, {
                type: 'polarArea',
                data: {
                    labels: labels,
                    datasets: [{
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        r: {
                            pointLabels: {
                                display: true,
                                font: {
                                    size: 14,
                                    weight: '600',
                                    family: 'Inter'
                                },
                                color: '#334155'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)'
                            },
                            ticks: {
                                display: false,
                                beginAtZero: true,
                                max: 5
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            position: 'right',
                            labels: {
                                font: {
                                    size: 14,
                                    family: 'Inter',
                                    weight: '500'
                                },
                                color: '#334155',
                                boxWidth: 20,
                                boxHeight: 20,
                                padding: 15
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    }
                }
            });
        }

        function createOverallRadarChart(ctx, overallAverages, formCategories) {
            const labels = Object.values(formCategories).map(cat => cat.title);
            const data = Object.values(overallAverages);
            const backgroundColors = Object.values(formCategories).map(cat => cat.color + '40');
            const borderColors = Object.values(formCategories).map(cat => cat.color);

            if (data.every(val => val === 0)) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No data available for this chart yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            new Chart(ctx, {
                type: 'radar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Performance',
                        data: data,
                        backgroundColor: 'rgba(75, 192, 192, 0.4)',
                        borderColor: '#4F46E5',
                        borderWidth: 2,
                        pointBackgroundColor: '#4F46E5',
                        pointBorderColor: '#fff',
                        pointHoverBackgroundColor: '#fff',
                        pointHoverBorderColor: '#4F46E5',
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#4F46E5',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    },
                    scales: {
                        r: {
                            angleLines: {
                                color: 'rgba(0, 0, 0, 0.1)'
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)'
                            },
                            pointLabels: {
                                font: {
                                    size: 14,
                                    weight: '600',
                                    family: 'Inter'
                                },
                                color: '#334155'
                            },
                            ticks: {
                                beginAtZero: true,
                                max: 5,
                                stepSize: 1,
                                display: false
                            }
                        }
                    }
                }
            });
        }

        function createLatestCategoryRatingsBarChart(ctx, latestRatings, formCategories) {
            const labels = [];
            const data = [];
            const backgroundColors = [];
            const borderColors = [];

            for (const key in formCategories) {
                if (latestRatings[key]) {
                    labels.push(formCategories[key].title);
                    data.push(latestRatings[key].rating);
                    backgroundColors.push(formCategories[key].color + 'CC');
                    borderColors.push(formCategories[key].color);
                }
            }

            if (data.length === 0) {
                ctx.font = "16px Inter";
                ctx.fillStyle = "#6B7280";
                ctx.textAlign = "center";
                ctx.fillText("No latest rating data available yet.", ctx.canvas.width / 2, ctx.canvas.height / 2);
                return;
            }

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Latest Rating',
                        data: data,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                        borderRadius: 8,
                        hoverBackgroundColor: backgroundColors.map(color => color.slice(0, -2) + 'FF')
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                title: function(context) {
                                    const categoryKey = Object.keys(formCategories).find(key => formCategories[key].title === context[0].label);
                                    let areaInfo = '';
                                    if (latestRatings[categoryKey]) {
                                        const area = latestRatings[categoryKey].area;
                                        const otherInput = latestRatings[categoryKey].otherInput;
                                        if (area === 'Other' && otherInput) {
                                            areaInfo = `Focus: ${otherInput}`;
                                        } else if (area) {
                                            areaInfo = `Focus: ${area}`;
                                        }
                                    }
                                    return `${context[0].label} ${areaInfo ? `(${areaInfo})` : ''}`;
                                },
                                label: function(context) {
                                    return `Rating: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });
        }


        document.addEventListener('DOMContentLoaded', function() {
            for (const key in formCategories) {
                if (categoryData[key] && categoryData[key].ratingsByDate) {
                    const ctx = document.getElementById(`${key}DevelopmentChart`).getContext('2d');
                    createLineChart(
                        ctx,
                        categoryData[key].ratingsByDate,
                        overallAverages[key],
                        formCategories[key].color,
                        `${formCategories[key].title} Progress`
                    );
                }
            }

            const overallProgressLineCtx = document.getElementById('overallProgressLineChart').getContext('2d');
            createOverallAverageLineChart(overallProgressLineCtx, overallAverageProgress);

            const categoryStackedBarCtx = document.getElementById('categoryStackedBarChart').getContext('2d');
            createStackedBarChart(categoryStackedBarCtx, categoryData, formCategories);

            for (const key in formCategories) {
                if (categoryData[key] && categoryData[key].focusAreaRatings) {
                    const ctx = document.getElementById(`${key}FocusAreaBarChart`).getContext('2d');
                    const chartData = prepareFocusAreaDataForChart(categoryData[key].focusAreaRatings, formCategories[key].color);
                    createBarChart(
                        ctx,
                        chartData,
                        formCategories[key].color
                    );
                }
            }

            const topBottomFocusAreasCtx = document.getElementById('topBottomFocusAreasChart').getContext('2d');
            createTopBottomFocusAreasChart(topBottomFocusAreasCtx, aggregatedFocusAreaAverages, formCategories);

            for (const key in formCategories) {
                if (categoryData[key] && categoryData[key].allRatings) {
                    const ctx = document.getElementById(`${key}RatingDistributionChart`).getContext('2d');
                    createOverallDoughnutChart(ctx, categoryData[key].allRatings, `${formCategories[key].title} Distribution`, 'right');
                }
            }

            const allUserRatings = [];
            for (const key in categoryData) {
                allUserRatings.push(...categoryData[key].allRatings);
            }
            const overallDoughnutCtx = document.getElementById('overallSkillDistributionChart').getContext('2d');
            createOverallDoughnutChart(overallDoughnutCtx, allUserRatings, 'Overall Skill Distribution', 'right');

            const overallRadarCtx = document.getElementById('overallRadarChart').getContext('2d');
            createOverallRadarChart(overallRadarCtx, overallAverages, formCategories);

            const individualEntryBubbleCtx = document.getElementById('individualEntryBubbleChart').getContext('2d');
            createIndividualEntryBubbleChart(individualEntryBubbleCtx, allIndividualRatings, formCategories);

            const categoryDominancePolarCtx = document.getElementById('categoryDominancePolarChart').getContext('2d');
            createCategoryDominancePolarChart(categoryDominancePolarCtx, overallAverages, formCategories);

            const overallAverageBarCtx = document.getElementById('overallAverageBarChart').getContext('2d');
            const overallAvgLabels = Object.values(formCategories).map(cat => cat.title);
            const overallAvgData = Object.values(overallAverages);
            const overallAvgColors = Object.values(formCategories).map(cat => cat.color);

            new Chart(overallAverageBarCtx, {
                type: 'bar',
                data: {
                    labels: overallAvgLabels,
                    datasets: [{
                        label: 'Average Rating',
                        data: overallAvgData,
                        backgroundColor: overallAvgColors.map(color => color + 'CC'),
                        borderColor: overallAvgColors,
                        borderWidth: 1,
                        borderRadius: 8,
                        hoverBackgroundColor: overallAvgColors
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(30, 41, 59, 0.95)',
                            titleColor: '#ffffff',
                            bodyColor: '#ffffff',
                            borderColor: '#6366f1',
                            borderWidth: 1,
                            cornerRadius: 8,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return `Rating: ${context.raw.toFixed(1)}/5`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 5,
                            grid: {
                                color: 'rgba(0, 0, 0, 0.08)',
                                drawBorder: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: '#4B5563',
                                font: {
                                    size: 13,
                                    weight: '500'
                                }
                            }
                        }
                    }
                }
            });

            const latestCategoryRatingsCtx = document.getElementById('latestCategoryRatingsChart').getContext('2d');
            createLatestCategoryRatingsBarChart(latestCategoryRatingsCtx, latestRatings, formCategories);
        });
    </script>
</body>
</html>