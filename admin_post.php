<?php
include 'admin.php';
?>

<?php
// admin_post.php

// -----------------------------------------------------------
// 1. Error Reporting (for development - turn off in production)
// -----------------------------------------------------------
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize message variables
$message = '';
$messageType = 'error'; // Default to error

// Define upload directory and allowed file types/size
$uploadDir = 'uploads/';
$allowedTypes = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'video/mp4', 'video/webm',
    'audio/mpeg', 'audio/wav', 'audio/ogg' // Common MIME types for mp3, wav, ogg
];
$maxFileSize = 50 * 1024 * 1024; // 50 MB (in bytes)

// Ensure upload directory exists
if (!is_dir($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) { // Use 0755 for directory permissions
        $message = "Server Error: Could not create upload directory. Please check permissions.";
        // Exit or disable file upload if directory cannot be created
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize all inputs
    $postName = isset($_POST['post_name']) ? htmlspecialchars(trim($_POST['post_name'])) : '';
    $adminId = isset($_POST['admin_id']) ? htmlspecialchars(trim($_POST['admin_id'])) : '';
    $description = isset($_POST['description']) ? htmlspecialchars(trim($_POST['description'])) : '';
    $postType = isset($_POST['post_type']) ? htmlspecialchars(trim($_POST['post_type'])) : '';
    $mediaUrl = ''; // To store the URL of the uploaded image/video/music

    // --- Validation ---
    if (empty($postName) || empty($adminId) || empty($description) || empty($postType)) {
        $message = "Please fill in all required fields (Post Name, Admin ID, Description, Post Type).";
    } else {
        $uploadSuccess = true; // Assume success until an error occurs

        // --- Handle File Upload ---
        if (isset($_FILES['media']) && $_FILES['media']['error'] !== UPLOAD_ERR_NO_FILE) {
            $fileError = $_FILES['media']['error'];
            $fileTmpName = $_FILES['media']['tmp_name'];
            $fileSize = $_FILES['media']['size'];
            $fileNameOriginal = $_FILES['media']['name'];

            // Handle specific upload errors
            switch ($fileError) {
                case UPLOAD_ERR_OK:
                    // All good, proceed with further validation
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $message = "Error: Uploaded file exceeds the maximum allowed size (" . ($maxFileSize / (1024 * 1024)) . "MB).";
                    $uploadSuccess = false;
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = "Error: The uploaded file was only partially uploaded.";
                    $uploadSuccess = false;
                    break;
                case UPLOAD_ERR_NO_FILE:
                    // This case is handled by the main if condition
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = "Server Error: Missing a temporary folder for uploads.";
                    $uploadSuccess = false;
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = "Server Error: Failed to write file to disk. Check server permissions.";
                    $uploadSuccess = false;
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = "Server Error: A PHP extension stopped the file upload.";
                    $uploadSuccess = false;
                    break;
                default:
                    $message = "An unknown file upload error occurred (code: {$fileError}).";
                    $uploadSuccess = false;
                    break;
            }

            if ($uploadSuccess) {
                // Validate file size
                if ($fileSize > $maxFileSize) {
                    $message = "Error: File size (" . round($fileSize / (1024 * 1024), 2) . "MB) exceeds the maximum allowed size (" . ($maxFileSize / (1024 * 1024)) . "MB).";
                    $uploadSuccess = false;
                }

                // Validate MIME type using fileinfo extension for security
                if ($uploadSuccess && function_exists('finfo_open')) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $fileTmpName);
                    finfo_close($finfo);

                    if (!in_array($mimeType, $allowedTypes)) {
                        $message = "Error: Invalid file type detected ({$mimeType}). Allowed types are: " . implode(', ', array_map(function($type) { return explode('/', $type)[1]; }, $allowedTypes));
                        $uploadSuccess = false;
                    }
                } else if ($uploadSuccess) {
                    // Fallback to extension check if fileinfo is not available (less secure)
                    $fileExtension = strtolower(pathinfo($fileNameOriginal, PATHINFO_EXTENSION));
                    $allowedExtensions = array_map(function($type) { return explode('/', $type)[1]; }, $allowedTypes);
                    if (!in_array($fileExtension, $allowedExtensions)) {
                         $message = "Error: Invalid file extension. Allowed extensions are: " . implode(', ', $allowedExtensions) . ". Consider enabling 'fileinfo' PHP extension for better security.";
                         $uploadSuccess = false;
                    }
                }
            }

            // If all checks pass, move the uploaded file
            if ($uploadSuccess) {
                // Generate a unique and secure filename
                $safeFileName = bin2hex(random_bytes(8)) . '-' . uniqid() . '.' . pathinfo($fileNameOriginal, PATHINFO_EXTENSION);
                $targetFilePath = $uploadDir . $safeFileName;

                if (move_uploaded_file($fileTmpName, $targetFilePath)) {
                    $mediaUrl = $targetFilePath;
                } else {
                    $message = "Error: Could not move the uploaded file to '{$uploadDir}'. Check directory permissions (should be 0755 or 0777 temporarily for testing).";
                    $uploadSuccess = false;
                }
            }
        } // End of file upload handling

        // --- Save Data to JSON (only if validation and upload were successful) ---
        if ($uploadSuccess) {
            $postData = [
                'id' => uniqid('post_'), // Unique ID for each post
                'name' => $postName,
                'admin_id' => $adminId,
                'description' => $description,
                'post_type' => $postType,
                'media_url' => $mediaUrl,
                'timestamp' => date('Y-m-d H:i:s')
            ];

            $jsonFilePath = 'posts.json';
            $posts = [];

            // Read existing data
            if (file_exists($jsonFilePath)) {
                $currentData = file_get_contents($jsonFilePath);
                $decodedData = json_decode($currentData, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedData)) {
                    $posts = $decodedData;
                } else {
                    // Handle malformed JSON: log error and initialize as empty array
                    error_log("Malformed JSON in {$jsonFilePath}: " . json_last_error_msg());
                    $message = "Warning: Existing posts data file was corrupted. Creating a new one.";
                    $messageType = 'warning'; // Set message type for user feedback
                    $posts = [];
                }
            }

            // Add the new post to the beginning of the array
            array_unshift($posts, $postData); 

            // Save data to JSON
            if (file_put_contents($jsonFilePath, json_encode($posts, JSON_PRETTY_PRINT))) {
                $message = "Post created successfully!";
                $messageType = 'success';
                // Clear form data on success for a fresh form
                $_POST = [];
            } else {
                $message = "Error: Could not save the post to '{$jsonFilePath}'. Check file permissions (should be writable by web server, e.g., 0664 or 0666).";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Post - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #10b981; /* Emerald green */
            --primary-dark: #059669;
            --secondary: #6366f1; /* Indigo */
            --bg-gradient: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%); /* Light green gradient */
            --bg-primary: #ffffff;
            --text-primary: #1f2937;
            --text-secondary: #4b5563;
            --border: #d1d5db;
            --border-focus: #10b981; /* Emerald for focus */
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --shadow-2xl: 0 25px 50px -12px rgba(0, 0, 0, 0.1); /* Softer shadow */
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ECFDF5; /* Lighter background green */
            color: var(--text-primary);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(15px); /* Slightly less blur */
            border: 1px solid rgba(255, 255, 255, 0.3); /* Softer border */
            box-shadow: var(--shadow-2xl);
            border-radius: 1.5rem;
            animation: fadeInScale 0.8s ease-out forwards;
        }
        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(0.98); }
            to { opacity: 1; transform: scale(1); }
        }
        .form-group {
            opacity: 0;
            transform: translateY(15px); /* Slightly less translation */
            animation: slideInUp 0.6s ease-out forwards;
        }
        @keyframes slideInUp {
            to { opacity: 1; transform: translateY(0); }
        }
        /* Adjusted delays for staggered animation */
        .form-group:nth-child(1) { animation-delay: 0.1s; }
        .form-group:nth-child(2) { animation-delay: 0.15s; }
        .form-group:nth-child(3) { animation-delay: 0.2s; }
        .form-group:nth-child(4) { animation-delay: 0.25s; }
        .form-group:nth-child(5) { animation-delay: 0.3s; }
        .form-group:nth-child(6) { animation-delay: 0.35s; }

        .floating-label { position: relative; }
        .floating-label label {
            position: absolute;
            left: 1rem;
            top: 0.9rem; /* Adjusted top for better alignment */
            transition: all 0.2s ease; /* Faster transition */
            pointer-events: none;
            color: var(--text-secondary);
            font-size: 1rem; /* Base font size */
        }
        .floating-label input:focus ~ label, .floating-label input:not(:placeholder-shown) ~ label,
        .floating-label textarea:focus ~ label, .floating-label textarea:not(:placeholder-shown) ~ label,
        /* Added :not([value=""]) for select to ensure label lifts when an option is selected */
        .floating-label select:focus ~ label, .floating-label select:not([value=""]) ~ label {
            transform: translateY(-1.7rem) scale(0.8); /* Adjusted scale and translateY */
            color: var(--primary);
            background-color: var(--bg-primary);
            padding: 0 0.3rem;
            border-radius: 0.2rem;
            font-weight: 500; /* Make label slightly bolder when active */
        }
        .enhanced-input {
            transition: all 0.2s ease; /* Faster transition */
            background-color: #f9fafb;
            border-color: var(--border);
            /* Add padding-top to prevent text from being hidden by floating label */
            padding-top: 1.25rem; 
            padding-bottom: 0.75rem;
        }
        .enhanced-input:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2); /* Emerald shadow */
            border-color: var(--border-focus);
            background-color: var(--bg-primary);
        }
        /* Specific styles for select to handle arrow and padding */
        select.enhanced-input {
            padding-right: 2.5rem; /* Make space for the custom arrow */
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark)); /* Solid green gradient */
            transition: all 0.3s ease;
            box-shadow: var(--shadow-lg);
        }
        .btn-primary:hover {
            transform: translateY(-2px); /* Slightly less lift */
            box-shadow: 0 12px 18px -4px rgba(16, 185, 129, 0.4); /* Emerald shadow on hover */
        }
        .file-upload { 
            border: 2px dashed var(--border); 
            transition: all 0.3s ease; 
            background-color: #f9fafb; /* Light background for upload area */
        }
        .file-upload:hover, .file-upload.dragover {
            border-color: var(--primary);
            background-color: #d1fae5; /* Lighter green on hover/drag */
        }
        .alert { animation: alertSlideIn 0.5s ease-out; }
        @keyframes alertSlideIn { from { opacity: 0; transform: translateY(-15px); } to { opacity: 1; transform: translateY(0); } }
        #rr{
            margin-top:40px;
            margin-left:200px;
        }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4 bg-emerald-50" id="rr">

    <main class="w-full max-w-2xl">
        <div class="glass-card p-8 sm:p-10">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4 shadow-md">
                    <i class="fas fa-edit text-emerald-600 text-3xl"></i> </div>
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900">Create a New Post</h1>
                <p class="text-gray-600 mt-2 text-lg">Share updates, news, or media with your community.</p>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert p-4 mb-6 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : ($messageType === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                    <div class="flex items-center">
                        <i class="fa-solid <?php echo $messageType === 'success' ? 'fa-circle-check' : ($messageType === 'warning' ? 'fa-triangle-exclamation' : 'fa-circle-exclamation'); ?> text-xl mr-3"></i> <p class="font-medium"><?php echo htmlspecialchars($message); ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" enctype="multipart/form-data" class="space-y-6">
                <div class="form-group floating-label">
                    <input type="text" id="post_name" name="post_name" required placeholder=" "
                           class="enhanced-input w-full py-3 px-4 border rounded-xl"
                           value="<?php echo htmlspecialchars($_POST['post_name'] ?? ''); ?>">
                    <label for="post_name"><i class="fas fa-pencil-alt mr-2"></i>Post Name / Title</label>
                </div>

                <div class="form-group floating-label">
                    <input type="text" id="admin_id" name="admin_id" required placeholder=" "
                           class="enhanced-input w-full py-3 px-4 border rounded-xl"
                           value="<?php echo htmlspecialchars($_POST['admin_id'] ?? 'admin'); ?>">
                    <label for="admin_id"><i class="fas fa-user-tie mr-2"></i>Admin ID</label>
                </div>

                <div class="form-group floating-label">
                    <textarea id="description" name="description" rows="5" required placeholder=" "
                              class="enhanced-input w-full py-3 px-4 border rounded-xl"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    <label for="description"><i class="fas fa-file-alt mr-2"></i>Description</label>
                </div>
                
                <div class="form-group floating-label">
                    <select id="post_type" name="post_type" required 
                            class="enhanced-input w-full py-3 px-4 border rounded-xl appearance-none">
                        <option value="" disabled <?php echo empty($_POST['post_type']) ? 'selected' : ''; ?>>Please select a post type...</option>
                        <option value="text" <?php echo ($_POST['post_type'] ?? '') === 'text' ? 'selected' : ''; ?>>Text</option>
                        <option value="image" <?php echo ($_POST['post_type'] ?? '') === 'image' ? 'selected' : ''; ?>>Image</option>
                        <option value="video" <?php echo ($_POST['post_type'] ?? '') === 'video' ? 'selected' : ''; ?>>Video</option>
                        <option value="music" <?php echo ($_POST['post_type'] ?? '') === 'music' ? 'selected' : ''; ?>>Music</option>
                    </select>
                    <label for="post_type"><i class="fas fa-tag mr-2"></i>Post Type</label>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                    </div>
                </div>

                <div class="form-group file-upload rounded-xl" id="fileUploadArea">
                    <input type="file" id="media" name="media" class="hidden" 
                           accept="image/*,video/*,audio/*" 
                           data-max-size="<?php echo $maxFileSize; ?>">
                    <label for="media" class="block cursor-pointer p-6 text-center">
                        <i class="fas fa-cloud-arrow-up text-4xl text-emerald-500 mb-3"></i> 
                        <p class="font-semibold text-gray-700">Drag & Drop or <span class="text-emerald-600 underline">Click to Upload</span></p>
                        <p class="text-sm text-gray-500 mt-1">Images, Videos, or Music Files (Max <?php echo $maxFileSize / (1024 * 1024); ?>MB)</p>
                        <p id="fileName" class="text-sm text-emerald-600 mt-2 font-medium"></p>
                    </label>
                </div>

                <div class="form-group pt-4">
                    <button type="submit" class="btn-primary w-full py-3 px-4 rounded-xl text-lg font-bold text-white flex items-center justify-center gap-2">
                        <i class="fas fa-share-square"></i> Create Post
                    </button>
                </div>
            </form>
            
            <div class="text-center mt-8">
                <a href="view_posts.php" class="text-emerald-600 hover:text-emerald-800 font-semibold transition-colors">
                    <i class="fas fa-list-alt mr-1"></i> View All Posts
                </a>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Handle file upload preview/name display
            const fileInput = document.getElementById('media');
            const fileNameDisplay = document.getElementById('fileName');
            const fileUploadArea = document.getElementById('fileUploadArea');
            const maxFileSize = parseInt(fileInput.dataset.maxSize); // Get max file size from data attribute

            if (fileInput && fileNameDisplay && fileUploadArea) {
                fileInput.addEventListener('change', function() {
                    if (this.files.length > 0) {
                        const file = this.files[0];
                        if (file.size > maxFileSize) {
                            fileNameDisplay.textContent = `Error: File '${file.name}' (${(file.size / (1024 * 1024)).toFixed(2)}MB) exceeds max size of ${(maxFileSize / (1024 * 1024)).toFixed(0)}MB.`;
                            fileNameDisplay.classList.add('text-red-500'); // Add red text for error
                            this.value = ''; // Clear the file input
                        } else {
                            fileNameDisplay.textContent = file.name;
                            fileNameDisplay.classList.remove('text-red-500'); // Remove red text if valid
                        }
                    } else {
                        fileNameDisplay.textContent = '';
                    }
                });

                // Drag and drop functionality
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, e => {
                        e.preventDefault();
                        e.stopPropagation();
                    }, false);
                });

                ['dragenter', 'dragover'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, () => fileUploadArea.classList.add('dragover'), false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    fileUploadArea.addEventListener(eventName, () => fileUploadArea.classList.remove('dragover'), false);
                });

                fileUploadArea.addEventListener('drop', e => {
                    const dt = e.dataTransfer;
                    fileInput.files = dt.files; // Assign dropped files to the input
                    // Manually trigger change event to update display and perform client-side size check
                    fileInput.dispatchEvent(new Event('change')); 
                }, false);
            }
        });
    </script>
</body>
</html>