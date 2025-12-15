<?php
session_start();

// Security headers
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("X-XSS-Protection: 1; mode=block");

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function to initialize videos array in session
function initVideos() {
    if (!isset($_SESSION['videos'])) {
        $_SESSION['videos'] = [];
    }
}

// Load all videos from session
function loadVideos() {
    initVideos();
    return $_SESSION['videos'];
}

// Add a new video to session
function addVideo($videoData) {
    initVideos();
    
    // Validate inputs
    $videoData = [
        'id' => uniqid(),
        'number' => htmlspecialchars(trim($videoData['number'])),
        'name' => htmlspecialchars(trim($videoData['name'])),
        'duration' => (int)$videoData['duration'],
        'link' => filter_var(trim($videoData['link']), FILTER_SANITIZE_URL),
        'created_at' => date('Y-m-d H:i:s')
    ];
    
    // Validate URL
    if (!filter_var($videoData['link'], FILTER_VALIDATE_URL)) {
        return ['success' => false, 'message' => 'Invalid video URL'];
    }
    
    // Check if video number already exists
    foreach ($_SESSION['videos'] as $video) {
        if ($video['number'] === $videoData['number']) {
            return ['success' => false, 'message' => 'Video number already exists'];
        }
    }
    
    // Add video to session
    $_SESSION['videos'][] = $videoData;
    return ['success' => true, 'message' => 'Video added successfully'];
}

// Delete a video from session
function deleteVideo($id) {
    initVideos();
    
    foreach ($_SESSION['videos'] as $key => $video) {
        if ($video['id'] === $id) {
            unset($_SESSION['videos'][$key]);
            $_SESSION['videos'] = array_values($_SESSION['videos']); // Reindex array
            return ['success' => true, 'message' => 'Video deleted successfully'];
        }
    }
    
    return ['success' => false, 'message' => 'Video not found'];
}

// Handle form submissions
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_video'])) {
        $result = addVideo([
            'number' => $_POST['video_number'] ?? '',
            'name' => $_POST['video_name'] ?? '',
            'duration' => $_POST['video_duration'] ?? 0,
            'link' => $_POST['video_link'] ?? ''
        ]);
        
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    } 
    elseif (isset($_POST['delete_video'])) {
        $result = deleteVideo($_POST['video_id'] ?? '');
        $message = $result['message'];
        $messageType = $result['success'] ? 'success' : 'error';
    }
}

// Load current videos
$videos = loadVideos();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-dark: #3a56d4;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --dark: #1f2937;
            --light: #f9fafb;
            --gray: #6b7280;
            --gray-light: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa, #e4e8f0);
            color: var(--dark);
            min-height: 100vh;
            transition: all 0.3s ease;
        }

        .content {
            margin-left: 280px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        h1, h2, h3 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        h1 {
            font-size: 2rem;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 0.5rem;
            margin-bottom: 2rem;
        }

        h2 {
            font-size: 1.5rem;
            margin-top: 2rem;
        }

        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--dark);
        }

        input[type="text"],
        input[type="number"],
        input[type="url"],
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: white;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
        }

        .btn i {
            margin-right: 0.5rem;
        }

        .table-responsive {
            overflow-x: auto;
            margin-bottom: 2rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        th {
            background-color: #f8fafc;
            font-weight: 600;
            color: var(--dark);
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f8fafc;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
        }

        .alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            border-left: 4px solid #ef4444;
        }

        .alert i {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }

        .video-link {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .video-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
        }

        /* Responsive styles */
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .card {
                padding: 1.5rem;
            }
            
            th, td {
                padding: 0.75rem;
            }
            
            .action-buttons {
                flex-direction: column;
            }
        }

        @media (max-width: 576px) {
            h1 {
                font-size: 1.5rem;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
    </style>
</head>
<body>
    <?php include 'admin.php'; ?>
    
    <div class="content">
        <div class="container">
            <h1 class="fade-in">Video Management</h1>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'success' ? 'success' : 'error' ?> fade-in">
                    <i class="fas <?= $messageType === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <div class="card fade-in">
                <h2><i class="fas fa-plus-circle"></i> Add New Video</h2>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="video_number">Video Number</label>
                        <input type="text" id="video_number" name="video_number" required 
                               placeholder="E.g., VID-001">
                    </div>
                    
                    <div class="form-group">
                        <label for="video_name">Video Title</label>
                        <input type="text" id="video_name" name="video_name" required 
                               placeholder="Enter video title">
                    </div>
                    
                    <div class="form-group">
                        <label for="video_duration">Duration (minutes)</label>
                        <input type="number" id="video_duration" name="video_duration" required 
                               min="1" placeholder="Duration in minutes">
                    </div>
                    
                    <div class="form-group">
                        <label for="video_link">Video URL</label>
                        <input type="url" id="video_link" name="video_link" required 
                               placeholder="https://example.com/video">
                    </div>
                    
                    <button type="submit" name="add_video" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Video
                    </button>
                </form>
            </div>
            
            <div class="card fade-in">
                <h2><i class="fas fa-list"></i> Video Library</h2>
                
                <?php if (empty($videos)): ?>
                    <div class="empty-state">
                        <i class="fas fa-video-slash"></i>
                        <h3>No Videos Found</h3>
                        <p>Add your first video using the form above</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Duration</th>
                                    <th>Link</th>
                                    <th>Added On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($videos as $video): ?>
                                <tr class="fade-in">
                                    <td><?= htmlspecialchars($video['number']) ?></td>
                                    <td><?= htmlspecialchars($video['name']) ?></td>
                                    <td><?= htmlspecialchars($video['duration']) ?> min</td>
                                    <td>
                                        <a href="<?= htmlspecialchars($video['link']) ?>" 
                                           target="_blank" class="video-link">
                                            <i class="fas fa-external-link-alt"></i> Watch
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($video['created_at']) ?></td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="video_id" value="<?= $video['id'] ?>">
                                            <button type="submit" name="delete_video" class="btn btn-danger"
                                                    onclick="return confirm('Are you sure you want to delete this video?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Confirm before deleting
        document.querySelectorAll('[name="delete_video"]').forEach(button => {
            button.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to delete this video?')) {
                    e.preventDefault();
                }
            });
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const videoLink = document.getElementById('video_link').value;
            
            if (!isValidUrl(videoLink)) {
                alert('Please enter a valid video URL');
                e.preventDefault();
            }
        });

        function isValidUrl(string) {
            try {
                new URL(string);
                return true;
            } catch (_) {
                return false;
            }
        }
    </script>
</body>
</html>