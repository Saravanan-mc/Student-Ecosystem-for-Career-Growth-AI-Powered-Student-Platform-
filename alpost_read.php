<?php
session_start();
// Include the alumni file, though its contents aren't directly outputted here.
// Make sure it contains necessary definitions if your JS or other PHP relies on it.
include 'alumni.php'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Discussion System - Recent Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-gold: #FFD700; /* Gold */
            --primary-gold-dark: #DAA520; /* Darker Gold */
            --secondary-charcoal: #333333; /* Dark charcoal for text */
            --bg-light-gold: #FFFBEB; /* Very light gold for background */
            --bg-card: rgba(255, 255, 255, 0.98); /* Slightly off-white for card background */
            --text-main: #212121;
            --text-secondary: #555555;
            --border-light: #E0E0E0;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition-ease: all 0.3s ease-in-out;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light-gold);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 1.5rem;
            box-sizing: border-box;
            line-height: 1.6;
        }
        
        main {
            flex-grow: 1;
            width: 100%;
        }

        .section-container {
            background: var(--bg-card);
            border: 1px solid rgba(255, 215, 0, 0.2);
            box-shadow: var(--shadow-lg);
            border-radius: 1.5rem;
            padding: 2.5rem;
            animation: fadeIn 0.8s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h2 {
            color: var(--secondary-charcoal);
            text-align: center;
            margin-bottom: 2rem;
            font-weight: 800;
            font-size: 2.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
        }

        .card {
            background-color: #ffffff;
            border-radius: 1rem;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: var(--transition-ease);
            border: 1px solid var(--border-light);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0,0,0,0.1);
        }

        .card-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
        }

        .card-header h3 {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--primary-gold-dark);
            margin-left: 0.75rem;
        }

        .card-header .timestamp {
            margin-left: auto;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }

        .post-content {
            color: var(--text-medium);
            margin-bottom: 1.5rem;
            line-height: 1.7;
        }

        .youtube-embed-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            margin-top: 1.5rem;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .youtube-embed-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        .card-actions {
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding-top: 1rem;
            border-top: 1px solid var(--border-light);
            font-size: 0.95rem;
            color: var(--text-secondary);
        }

        .action-button {
            display: flex;
            align-items: center;
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            transition: var(--transition-ease);
            cursor: pointer;
            font-weight: 500;
        }

        .action-button:hover {
            background-color: rgba(255, 215, 0, 0.1); /* Light gold hover */
            color: var(--primary-gold-dark);
        }

        .action-button i {
            margin-right: 0.5rem;
        }

        .comments-section {
            padding-top: 1.5rem;
            border-top: 1px dashed var(--border-light);
            margin-top: 1.5rem;
            max-height: 500px; /* Limit height for scroll */
            overflow-y: auto;
            transition: max-height 0.5s ease-in-out;
        }
        .comments-section.hidden {
            max-height: 0;
            overflow: hidden;
            padding-top: 0;
            margin-top: 0;
        }


        .comment-item {
            background-color: #FDF9E7; /* Even lighter gold for comments */
            padding: 1rem;
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
            border: 1px solid #FFEBBF; /* Light gold border */
        }
        .comment-item p {
            margin-bottom: 0.25rem;
        }
        .comment-item .comment-author {
            font-weight: 600;
            color: var(--primary-gold-dark);
        }
        .comment-item .comment-content {
            color: var(--text-main);
        }
        .comment-item .comment-timestamp {
            font-size: 0.75rem;
            color: #888;
            text-align: right;
            margin-top: 0.5rem;
        }

        .comment-input-area {
            margin-top: 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .textarea-field {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border-light);
            border-radius: 0.5rem;
            background-color: #fcfcfc;
            resize: vertical;
            min-height: 60px;
            font-size: 1rem;
            color: var(--text-dark);
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        .textarea-field:focus {
            outline: none;
            border-color: var(--primary-gold);
            box-shadow: 0 0 0 3px rgba(255, 215, 0, 0.2);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-gold), var(--primary-gold-dark));
            color: var(--secondary-charcoal);
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            transition: var(--transition-ease);
            box-shadow: var(--shadow-sm);
            border: none;
            cursor: pointer;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(255, 215, 0, 0.3);
            filter: brightness(1.05);
        }
        .btn-primary:active {
            transform: translateY(0);
            box-shadow: var(--shadow-sm);
        }

        #noPostsMessage5 {
            text-align: center;
            color: var(--text-secondary);
            padding: 2rem;
            border: 2px dashed var(--border-light);
            border-radius: 1rem;
            background-color: #fdfdfd;
            font-size: 1.1em;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1rem;
        }
        #noPostsMessage5 i {
            font-size: 3rem;
            color: var(--primary-gold);
        }

        /* Message Box Styling */
        #messageBox {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #212121; /* Dark background */
            color: #FFD700; /* Gold text */
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            z-index: 1000;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInRight 0.5s ease-out forwards;
        }
        #messageBox.hidden {
            animation: slideOutRight 0.5s ease-in forwards;
            pointer-events: none; /* Prevent interaction when hidden */
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }

        #messageBox .close-btn {
            background: none;
            border: none;
            color: #FFD700;
            font-size: 1.2rem;
            cursor: pointer;
            margin-left: 10px;
            opacity: 0.7;
            transition: opacity 0.2s ease;
        }
        #messageBox .close-btn:hover {
            opacity: 1;
        }

        #gg{
            margin-left:250px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col" id="gg">
    <main class="flex-grow container mx-auto px-4 py-8">
        <section id="page5" class="active">
            <div class="max-w-4xl mx-auto section-container">
                <h2><i class="fas fa-history text-primary-gold-dark"></i> Recent Discussions</h2>
                <div id="recentPostsContainer" class="space-y-6">
                    <p class="text-center text-gray-500" id="noPostsMessage5">
                        <i class="fas fa-box-open mb-4"></i><br>
                        No recent posts to display.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <div id="messageBox" class="hidden">
        <span id="messageBoxContent"></span>
        <button class="close-btn" onclick="hideMessageBox()">&times;</button>
    </div>

    <script>
        const recentPostsContainer = document.getElementById('recentPostsContainer');
        const noPostsMessage5 = document.getElementById('noPostsMessage5');
        let posts = [];

        function showMessageBox(message) {
            const messageBox = document.getElementById('messageBox');
            const messageBoxContent = document.getElementById('messageBoxContent');
            messageBoxContent.textContent = message;
            messageBox.classList.remove('hidden');
            clearTimeout(messageBox.hideTimeout);
            messageBox.hideTimeout = setTimeout(() => {
                hideMessageBox();
            }, 5000);
        }

        function hideMessageBox() {
            const messageBox = document.getElementById('messageBox');
            messageBox.classList.add('hidden');
        }

        function getYouTubeVideoId(url) {
            const regExp = /(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|)([\w-]{11})(?:\S+)?/;
            const match = url.match(regExp);
            return (match && match[1].length === 11) ? match[1] : null;
        }

        function generateUniqueId() {
            return Date.now().toString(36) + Math.random().toString(36).substring(2);
        }

        async function loadPosts() {
            try {
                const response = await fetch('action.php?action=get_posts');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                posts = data;
                renderPosts();
            } catch (error) {
                console.error('Error loading posts:', error);
                showMessageBox('Could not load posts from server. Please check your network and backend configuration.');
            }
            updateNoPostsMessages();
        }

        async function updatePostOnBackend(updatedPost) {
            try {
                const response = await fetch('action.php?action=update_post', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updatedPost)
                });
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const result = await response.json();
                console.log('Post updated successfully:', result);
                if (!result.success) {
                    showMessageBox('Failed to update post: ' + result.message);
                }
                loadPosts();
            } catch (error) {
                console.error('Error updating post:', error);
                showMessageBox('Failed to update post. Please check your PHP backend setup and server logs.');
            }
        }

        function createPostCard(post) {
            const youtubeEmbedHtml = post.type === 'youtube' && post.youtubeId
                ? `<div class="youtube-embed-container">
                       <iframe src="https://www.youtube.com/embed/${post.youtubeId}" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                   </div>`
                : '';

            const commentsHtml = post.comments.map(comment => `
                <div class="comment-item">
                    <p class="comment-author"><i class="fas fa-user-circle mr-1"></i>Anonymous User:</p>
                    <p class="comment-content">${escapeHTML(comment.content)}</p>
                    <p class="comment-timestamp">${new Date(comment.timestamp).toLocaleString()}</p>
                </div>
            `).join('');

            const postIcon = (type) => {
                switch(type) {
                    case 'doubt': return '<i class="fas fa-question-circle text-blue-500"></i>';
                    case 'problem': return '<i class="fas fa-exclamation-circle text-red-500"></i>';
                    case 'skill': return '<i class="fas fa-lightbulb text-yellow-500"></i>';
                    case 'youtube': return '<i class="fab fa-youtube text-red-600"></i>';
                    default: return '<i class="fas fa-info-circle text-gray-500"></i>';
                }
            };

            return `
                <div class="card" data-post-id="${post.id}">
                    <div class="card-header">
                        <span class="text-2xl">${postIcon(post.type)}</span>
                        <h3>${escapeHTML(post.title)}</h3>
                        <span class="timestamp"><i class="fas fa-clock mr-1"></i>${new Date(post.timestamp).toLocaleString()}</span>
                    </div>
                    <p class="post-content">${escapeHTML(post.content).replace(/\n/g, '<br>')}</p>
                    ${youtubeEmbedHtml}

                    <div class="card-actions">
                        <button class="action-button" onclick="handleLike('${post.id}')">
                            <i class="fas fa-thumbs-up"></i>
                            <span>${post.likes} Likes</span>
                        </button>
                        <button class="action-button" onclick="handleShare('${post.id}')">
                            <i class="fas fa-share-alt"></i>
                            <span>Share</span>
                        </button>
                        <button class="action-button" onclick="toggleComments('${post.id}')">
                            <i class="fas fa-comment"></i>
                            <span>${post.comments.length} Comments</span>
                        </button>
                    </div>

                    <div id="commentsSection-${post.id}" class="comments-section hidden">
                        ${commentsHtml}
                        <div class="comment-input-area">
                            <textarea id="commentInput-${post.id}" class="textarea-field" placeholder="Add a comment..."></textarea>
                            <button class="btn-primary" onclick="addComment('${post.id}')">Post Comment</button>
                        </div>
                    </div>
                </div>
            `;
        }

        function escapeHTML(str) {
            const div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }

        function renderPosts() {
            const sortedPosts = [...posts].sort((a, b) => b.timestamp - a.timestamp);
            recentPostsContainer.innerHTML = sortedPosts.slice(0, 5).map(createPostCard).join('');
            updateNoPostsMessages();
        }

        function updateNoPostsMessages() {
            noPostsMessage5.classList.toggle('hidden', posts.length > 0);
        }

        async function handleLike(postId) {
            const postIndex = posts.findIndex(p => p.id === postId);
            if (postIndex > -1) {
                posts[postIndex].likes = (posts[postIndex].likes || 0) + 1; // Ensure likes property exists
                await updatePostOnBackend(posts[postIndex]);
            }
        }

        async function handleShare(postId) {
            const postIndex = posts.findIndex(p => p.id === postId);
            if (postIndex > -1) {
                posts[postIndex].shares = (posts[postIndex].shares || 0) + 1; // Ensure shares property exists
                await updatePostOnBackend(posts[postIndex]);
                showMessageBox('Post shared successfully!');
            }
        }

        function toggleComments(postId) {
            const commentsSection = document.getElementById(`commentsSection-${postId}`);
            if (commentsSection) {
                commentsSection.classList.toggle('hidden');
            }
        }

        async function addComment(postId) {
            const commentInput = document.getElementById(`commentInput-${postId}`);
            const commentContent = commentInput.value.trim();

            if (!commentContent) {
                showMessageBox('Comment cannot be empty.');
                return;
            }

            const postIndex = posts.findIndex(p => p.id === postId);
            if (postIndex > -1) {
                const newComment = {
                    id: generateUniqueId(),
                    content: commentContent,
                    timestamp: Date.now(),
                    userId: 'anonymous'
                };
                if (!posts[postIndex].comments) {
                    posts[postIndex].comments = []; // Initialize comments array if it doesn't exist
                }
                posts[postIndex].comments.push(newComment);
                await updatePostOnBackend(posts[postIndex]);
                commentInput.value = '';
                // Re-render the specific post card to show the new comment
                const postCardElement = document.querySelector(`.card[data-post-id="${postId}"]`);
                if (postCardElement) {
                    const newPostCardHtml = createPostCard(posts[postIndex]);
                    postCardElement.outerHTML = newPostCardHtml;
                    document.getElementById(`commentsSection-${postId}`).classList.remove('hidden');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            loadPosts();
        });
    </script>
</body>
</html>