<?php
session_start();
include 'admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Club Posts</title>
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
            --like-button-bg: #e91e63;
            --comment-button-bg: #4caf50;
            --details-button-bg: #607d8b;
            --post-header-color: #673ab7;
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
            margin-bottom: 40px;
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

        .posts-list-container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding-bottom: 30px;
        }

        .post-container {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(15px);
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: var(--shadow-medium);
            text-align: left;
            border: 1px solid rgba(0, 188, 212, 0.2);
            animation: postAppear 0.8s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes postAppear {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .post-container h3 {
            margin-top: 0;
            margin-bottom: 15px;
            color: var(--post-header-color);
            font-size: 1.8em;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .post-container h3 i {
            color: var(--accent-medium);
            font-size: 0.8em;
        }

        .post-container p {
            margin-bottom: 20px;
            line-height: 1.7;
            color: var(--text-dark);
            font-size: 1.05em;
        }

        .post-media {
            margin-top: 20px;
            margin-bottom: 20px;
            text-align: center;
            background-color: var(--primary-pale);
            padding: 15px;
            border-radius: 10px;
            overflow: hidden;
        }

        .post-media img, .post-media video {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .post-media iframe {
            width: 100%;
            max-width: 640px;
            height: 360px;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        .post-media p a {
            color: var(--accent-medium);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .post-media p a:hover {
            color: var(--accent-dark);
            text-decoration: underline;
        }

        .view-details-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 15px;
            padding: 12px 25px;
            background-color: var(--details-button-bg);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1em;
            font-weight: 600;
            box-shadow: var(--shadow-light);
        }

        .view-details-link:hover {
            background-color: #455a64;
            transform: translateY(-2px);
            box-shadow: var(--shadow-medium);
        }

        .like-comment-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px dashed var(--border-color);
        }

        .like-button {
            padding: 10px 20px;
            cursor: pointer;
            background-color: var(--like-button-bg);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .like-button:hover {
            background-color: #d81b60;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .comment-input-area {
            margin-top: 15px;
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .comment-input {
            flex-grow: 1;
            padding: 12px 15px;
            border: 1px solid var(--primary-dark);
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-dark);
            background-color: rgba(255, 255, 255, 0.8);
            box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .comment-input:focus {
            border-color: var(--accent-medium);
            box-shadow: 0 0 0 3px rgba(0, 188, 212, 0.2);
            outline: none;
        }
        
        .comment-input-wrapper {
            position: relative;
            flex-grow: 1;
        }

        .comment-input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent-medium);
            font-size: 1.1rem;
            pointer-events: none;
        }
        
        .comment-input.with-icon {
            padding-left: 40px;
        }

        .comment-button {
            padding: 10px 20px;
            cursor: pointer;
            background-color: var(--comment-button-bg);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .comment-button:hover {
            background-color: #388e3c;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .comments-list {
            margin-top: 20px;
            font-size: 0.95em;
            background-color: var(--bg-blue);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid var(--border-color);
        }

        .comment-item {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 10px;
            color: var(--text-dark);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .comment-item:last-child {
            margin-bottom: 0;
        }

        .no-posts-message {
            text-align: center;
            font-size: 1.4em;
            color: var(--text-dark);
            margin-top: 50px;
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            padding: 30px;
            border-radius: 15px;
            box-shadow: var(--shadow-light);
            border: 1px solid var(--border-color);
        }

        .message-box {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
            font-weight: 500;
            animation: fadeInOut 3s forwards;
        }

        .message-box.warning {
            background-color: #ffe0b2;
            color: #fb8c00;
        }

        @keyframes fadeInOut {
            0% { opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { opacity: 0; display: none; }
        }

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
            .post-container {
                padding: 25px;
            }
            .post-container h3 {
                font-size: 1.6em;
            }
            .view-details-link, .like-button, .comment-button {
                padding: 10px 20px;
                font-size: 0.95em;
            }
            .comment-input {
                padding: 10px 12px;
            }
            .comment-input.with-icon {
                padding-left: 35px;
            }
            .comment-input-wrapper i {
                left: 10px;
                font-size: 1rem;
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
            .post-container {
                padding: 20px;
            }
            .post-container h3 {
                font-size: 1.4em;
            }
            .post-media iframe {
                height: 250px;
            }
            .comment-input-area {
                flex-direction: column;
                align-items: stretch;
            }
            .comment-input {
                width: 100%;
            }
            .comment-input.with-icon {
                padding-left: 40px;
            }
            .comment-button {
                width: 100%;
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
            .post-container {
                padding: 15px;
            }
            .post-container h3 {
                font-size: 1.2em;
            }
            .post-container p {
                font-size: 0.95em;
            }
            .post-media iframe {
                height: 200px;
            }
            .view-details-link, .like-button, .comment-button {
                font-size: 0.9em;
                padding: 8px 15px;
            }
            .comment-input {
                padding: 8px 10px;
                font-size: 0.9em;
            }
            .comment-input.with-icon {
                padding-left: 30px;
            }
            .comment-input-wrapper i {
                left: 8px;
                font-size: 0.9rem;
            }
            .comments-list {
                padding: 10px;
            }
            .comment-item {
                padding: 8px 12px;
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
            <i class="fas fa-list-alt"></i> All Club Posts
        </h2>

        <div class="posts-list-container">
            <?php
            $club_posts_json_file = 'club_posts.json';
            $club_posts = [];

            if (file_exists($club_posts_json_file)) {
                $json_data = file_get_contents($club_posts_json_file);
                $club_posts = json_decode($json_data, true);
                if ($club_posts === null) {
                    $club_posts = [];
                }
            }

            if (empty($club_posts)) {
                echo "<p class='no-posts-message'>No club posts yet. Be the first to share your talent!</p>";
            } else {
                usort($club_posts, function($a, $b) {
                    return $b['timestamp'] - $a['timestamp'];
                });

                foreach ($club_posts as $post) {
                    $post_id = htmlspecialchars($post['id']);
                    echo "<div class='post-container'>";
                    echo "<h3><i class='fas fa-bookmark'></i> " . htmlspecialchars($post['name']) . " <small>(ID: " . htmlspecialchars($post['roll_number']) . ")</small></h3>";
                    echo "<p>" . nl2br(htmlspecialchars($post['description'])) . "</p>";

                    echo "<div class='post-media'>";
                    if ($post['media_type'] === 'image') {
                        echo "<canvas class='post-image-canvas' data-src='" . htmlspecialchars($post['media_path']) . "' width='640' height='360'></canvas>";
                    } elseif ($post['media_type'] === 'video') {
                        echo "<video controls><source src='" . htmlspecialchars($post['media_path']) . "' type='video/mp4'>Your browser does not support the video tag.</video>";
                    } elseif ($post['media_type'] === 'link') {
                        $link = htmlspecialchars($post['media_path']);
                        $youtube_pattern = '/(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|)([\w-]{11})(?:\S+)?/';
                        $vimeo_pattern = '/(?:https?:\/\/)?(?:www\.)?(?:player\.)?vimeo\.com\/(?:video\/)?(\d+)(?:\S+)?/';

                        if (preg_match($youtube_pattern, $link, $matches)) {
                            $video_id = $matches[1];
                            echo "<iframe src='https://www.youtube.com/embed/" . $video_id . "' allow='accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture' allowfullscreen></iframe>";
                        } elseif (preg_match($vimeo_pattern, $link, $matches)) {
                            $video_id = $matches[1];
                            echo "<iframe src='https://player.vimeo.com/video/" . $video_id . "' allowfullscreen></iframe>";
                        } else {
                            echo "<p><a href='" . $link . "' target='_blank' rel='noopener noreferrer'>External Link: " . $link . "</a></p>";
                        }
                    }
                    echo "</div>";

                    echo "<a href='view_club_post.php?id=" . $post_id . "' class='view-details-link'><i class='fas fa-info-circle'></i> View Details</a>";

                    echo "<div class='like-comment-section'>";
                    echo "<button class='like-button' data-post-id='" . $post_id . "' onclick='toggleLike(\"" . $post_id . "\")'><i class='fas fa-heart'></i> Like (<span id='likes_" . $post_id . "'>0</span>)</button>";
                    echo "<div class='comment-input-area'>";
                    echo "<div class='comment-input-wrapper'>";
                    echo "<i class='fas fa-pencil-alt'></i>";
                    echo "<input type='text' class='comment-input with-icon' id='comment_input_" . $post_id . "' placeholder='Add a comment...'>";
                    echo "</div>";
                    echo "<button class='comment-button' onclick='addComment(\"" . $post_id . "\")'><i class='fas fa-comment-dots'></i> Comment</button>"; echo "</div>";
                    echo "<div class='comments-list' id='comments_list_" . $post_id . "'></div>";
                    echo "</div>";
                    echo "</div>";
                }
            }
            ?>
        </div>
    </div>

    <footer class="footer">
        <p>© 2025 Talent Post. All rights reserved.</p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            <?php
            if (!empty($club_posts)) {
                foreach ($club_posts as $post) {
                    $post_id = htmlspecialchars($post['id']);
                    echo "initPostInteractions('" . $post_id . "');\n";
                }
            }
            ?>
            loadImagesIntoCanvas();
        });

        function loadImagesIntoCanvas() {
            document.querySelectorAll('canvas.post-image-canvas').forEach(canvas => {
                const img = new Image();
                img.src = canvas.dataset.src;
                img.onload = () => {
                    const ctx = canvas.getContext('2d');
                    const aspectRatio = img.width / img.height;
                    let drawWidth = canvas.width;
                    let drawHeight = canvas.height;

                    if (aspectRatio > (canvas.width / canvas.height)) {
                        drawHeight = canvas.width / aspectRatio;
                    } else {
                        drawWidth = canvas.height * aspectRatio;
                    }

                    const xOffset = (canvas.width - drawWidth) / 2;
                    const yOffset = (canvas.height - drawHeight) / 2;

                    ctx.drawImage(img, xOffset, yOffset, drawWidth, drawHeight);
                };
                img.onerror = () => {
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#ccc';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);
                    ctx.fillStyle = '#666';
                    ctx.font = '16px Inter';
                    ctx.textAlign = 'center';
                    ctx.fillText('Image failed to load', canvas.width / 2, canvas.height / 2);
                };
            });
        }

        function initPostInteractions(postId) {
            const likes = JSON.parse(localStorage.getItem('club_post_likes')) || {};
            const likeCount = likes[postId] ? likes[postId] : 0;
            document.getElementById('likes_' + postId).innerText = likeCount;

            const comments = JSON.parse(localStorage.getItem('club_post_comments')) || {};
            renderComments(postId, comments[postId] || []);
        }

        function toggleLike(postId) {
            let likes = JSON.parse(localStorage.getItem('club_post_likes')) || {};
            if (likes[postId]) {
                likes[postId]--;
                if (likes[postId] < 0) likes[postId] = 0;
            } else {
                likes[postId] = 1;
            }
            localStorage.setItem('club_post_likes', JSON.stringify(likes));
            document.getElementById('likes_' + postId).innerText = likes[postId];
        }

        function addComment(postId) {
            const commentInput = document.getElementById('comment_input_' + postId);
            const commentText = commentInput.value.trim();

            if (commentText === '') {
                const messageDiv = document.createElement('div');
                messageDiv.className = 'message-box warning';
                messageDiv.innerText = 'Comment cannot be empty!';
                commentInput.parentNode.insertBefore(messageDiv, commentInput);
                setTimeout(() => messageDiv.remove(), 3000);
                return;
            }

            let comments = JSON.parse(localStorage.getItem('club_post_comments')) || {};
            if (!comments[postId]) {
                comments[postId] = [];
            }
            comments[postId].push(commentText);
            localStorage.setItem('club_post_comments', JSON.stringify(comments));

            renderComments(postId, comments[postId]);
            commentInput.value = '';
        }

        function renderComments(postId, postComments) {
            const commentsListDiv = document.getElementById('comments_list_' + postId);
            commentsListDiv.innerHTML = '';

            if (postComments.length === 0) {
                commentsListDiv.innerHTML = '<p style="font-style: italic; color: #666; text-align: center;">No comments yet.</p>';
                return;
            }

            postComments.forEach(comment => {
                const commentDiv = document.createElement('div');
                commentDiv.className = 'comment-item';
                commentDiv.innerText = comment;
                commentsListDiv.appendChild(commentDiv);
            });
        }
    </script>
</body>
</html>