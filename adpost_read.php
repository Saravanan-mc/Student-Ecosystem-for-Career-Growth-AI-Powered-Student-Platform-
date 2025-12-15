<?php
session_start();
include 'admin.php'; // Assuming admin.php handles session or shared configuration
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Discussion System - Popular Posts</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Optional: Custom scrollbar for better aesthetics, if needed */
        /* Works best in modern browsers */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive YouTube Embeds */
        .youtube-embed-container {
            position: relative;
            width: 100%;
            padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
            height: 0;
            overflow: hidden;
            margin-bottom: 1rem; /* Space below video */
            border-radius: 0.5rem; /* Rounded corners for video */
        }
        .youtube-embed-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }

        /* Custom styling for the message box */
        #messageBox {
            background-color: rgba(0, 0, 0, 0.6); /* Semi-transparent black overlay */
            z-index: 1000; /* Ensure it's on top */
        }
        #messageBoxContent {
            white-space: pre-wrap; /* Preserve line breaks if any */
        }

        #rr{
            margin-left:200px;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-green-50 font-inter text-gray-900" id="rr">

    <div id="messageBox" class="hidden fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-sm w-full relative">
            <h3 class="text-xl font-bold mb-4 text-gray-800">Notification</h3>
            <p id="messageBoxContent" class="mb-6 text-gray-700"></p>
            <button onclick="hideMessageBox()" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 focus:outline-none">
                <i class="fas fa-times-circle text-2xl"></i>
            </button>
            <button onclick="hideMessageBox()" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 w-full">
                OK
            </button>
        </div>
    </div>

    <main class="flex-grow container mx-auto px-4 py-8">
        <section id="page6" class="page-section active">
            <div class="max-w-4xl mx-auto bg-white p-8 rounded-xl shadow-lg">
                <h2 class="text-3xl font-bold mb-8 text-green-800 text-center">Discussions</h2>
                <div id="popularPostsContainer" class="space-y-8">
                    <p class="text-center text-gray-500 text-lg py-10" id="noPostsMessage6">
                        <i class="fas fa-info-circle mr-2"></i> No popular posts to display yet.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        // --- Global Variables ---
        const popularPostsContainer = document.getElementById('popularPostsContainer');
        const noPostsMessage6 = document.getElementById('noPostsMessage6');
        let posts = []; // Will store fetched posts

        // --- Utility Functions (common functions for all pages) ---
        /**
         * Displays a custom message box instead of alert().
         * @param {string} message - The message to display.
         */
        function showMessageBox(message) {
            const messageBox = document.getElementById('messageBox');
            const messageBoxContent = document.getElementById('messageBoxContent');
            messageBoxContent.textContent = message;
            messageBox.classList.remove('hidden');
        }

        /**
         * Hides the custom message box.
         */
        function hideMessageBox() {
            const messageBox = document.getElementById('messageBox');
            messageBox.classList.add('hidden');
        }

        /**
         * Extracts YouTube video ID from a URL.
         * @param {string} url - The YouTube video URL.
         * @returns {string|null} The video ID or null if not found.
         */
        function getYouTubeVideoId(url) {
            const regExp = /(?:https?:\/\/)?(?:www\.)?(?:m\.)?(?:youtube\.com|youtu\.be)\/(?:watch\?v=|embed\/|v\/|)([\w-]{11})(?:\S+)?/;
            const match = url.match(regExp);
            return (match && match[1].length === 11) ? match[1] : null;
        }

        /**
         * Generates a unique ID for posts/comments.
         * @returns {string} A unique ID.
         */
        function generateUniqueId() {
            return Date.now().toString(36) + Math.random().toString(36).substring(2, 9); // Shorten random part
        }

        // --- Data Handling (Interacting with PHP Backend) ---

        /**
         * Fetches posts from the PHP backend.
         */
        async function loadPosts() {
            try {
                const response = await fetch('action.php?action=get_posts');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                posts = data; // Update the local posts array with data from PHP
                renderPosts();
            } catch (error) {
                console.error('Error loading posts:', error);
                showMessageBox('Could not load posts from server. Please ensure action.php is accessible and correctly configured.');
            }
            updateNoPostsMessages();
        }

        /**
         * Updates an existing post (e.g., likes, comments) on the PHP backend.
         * @param {object} updatedPost - The post object with updated data.
         */
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
                // Only reload if the update was successful, otherwise the UI might show stale data
                if (result.success) {
                    loadPosts(); // Reload posts to reflect the update
                }
            } catch (error) {
                console.error('Error updating post:', error);
                showMessageBox('Failed to update post. Please check your PHP backend setup and server logs.');
            }
        }

        // --- Post Rendering ---

        /**
         * Creates the HTML structure for a single post.
         * @param {object} post - The post object.
         * @returns {string} The HTML string for the post card.
         */
        function createPostCard(post) {
            const youtubeEmbedHtml = post.type === 'youtube' && post.youtubeId
                ? `<div class="youtube-embed-container">
                       <iframe src="https://www.youtube.com/embed/${post.youtubeId}"
                               frameborder="0"
                               allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                               allowfullscreen></iframe>
                   </div>`
                : '';

            const commentsHtml = post.comments.length > 0 ? post.comments.map(comment => `
                <div class="bg-gray-100 p-3 rounded-lg mt-2 text-sm border border-gray-200">
                    <p class="font-semibold text-gray-700">Anonymous User:</p>
                    <p class="text-gray-600">${comment.content}</p>
                    <p class="text-xs text-gray-500 mt-1">${new Date(comment.timestamp).toLocaleString()}</p>
                </div>
            `).join('') : '<p class="text-gray-500 text-sm mt-3">No comments yet. Be the first to comment!</p>';

            return `
                <div class="bg-white p-6 rounded-xl shadow-md border border-gray-100 transition-all duration-300 hover:shadow-lg" data-post-id="${post.id}">
                    <div class="flex items-center mb-4">
                        <span class="text-2xl mr-3">
                            ${post.type === 'doubt' ? '<i class="fas fa-question-circle text-blue-600"></i>' : ''}
                            ${post.type === 'problem' ? '<i class="fas fa-exclamation-circle text-red-600"></i>' : ''}
                            ${post.type === 'skill' ? '<i class="fas fa-lightbulb text-yellow-600"></i>' : ''}
                            ${post.type === 'youtube' ? '<i class="fab fa-youtube text-red-700"></i>' : ''}
                        </span>
                        <h3 class="text-xl font-bold text-gray-800 flex-grow">${post.title}</h3>
                        <span class="ml-4 text-sm text-gray-500">${new Date(post.timestamp).toLocaleString()}</span>
                    </div>
                    <p class="text-gray-700 mb-4 leading-relaxed">${post.content}</p>
                    ${youtubeEmbedHtml}

                    <div class="flex items-center justify-between text-gray-600 text-sm mt-4 pt-4 border-t border-gray-200">
                        <div class="flex items-center space-x-6">
                            <button class="flex items-center space-x-1 text-gray-600 hover:text-blue-700 transition duration-200 transform hover:scale-105 focus:outline-none" onclick="handleLike('${post.id}')">
                                <i class="fas fa-thumbs-up"></i>
                                <span class="font-medium">${post.likes} Likes</span>
                            </button>
                            <button class="flex items-center space-x-1 text-gray-600 hover:text-green-700 transition duration-200 transform hover:scale-105 focus:outline-none" onclick="handleShare('${post.id}')">
                                <i class="fas fa-share-alt"></i>
                                <span class="font-medium">Share</span>
                            </button>
                            <button class="flex items-center space-x-1 text-gray-600 hover:text-purple-700 transition duration-200 transform hover:scale-105 focus:outline-none" onclick="toggleComments('${post.id}')">
                                <i class="fas fa-comment"></i>
                                <span class="font-medium">${post.comments.length} Comments</span>
                            </button>
                        </div>
                    </div>

                    <div id="commentsSection-${post.id}" class="comments-section mt-6 hidden border-t border-gray-200 pt-4">
                        <h4 class="text-lg font-semibold mb-3 text-gray-800">Comments:</h4>
                        <div class="space-y-3">
                            ${commentsHtml}
                        </div>
                        <div class="mt-5">
                            <textarea id="commentInput-${post.id}" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-400 focus:border-transparent resize-y min-h-[60px]" placeholder="Add a comment..."></textarea>
                            <button class="mt-3 bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-5 rounded-lg transition duration-200 shadow-md hover:shadow-lg" onclick="addComment('${post.id}')">
                                Post Comment
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        /**
         * Renders popular posts into the popularPostsContainer.
         */
        function renderPosts() {
            // Sort by likes for "Popular Posts"
            const sortedPosts = [...posts].sort((a, b) => b.likes - a.likes);
            popularPostsContainer.innerHTML = sortedPosts.map(createPostCard).join('');
            updateNoPostsMessages();
        }

        /**
         * Updates visibility of "No popular posts" messages.
         */
        function updateNoPostsMessages() {
            noPostsMessage6.classList.toggle('hidden', posts.length > 0);
        }

        // --- Interaction Handlers ---

        /**
         * Handles liking a post.
         * @param {string} postId - The ID of the post to like.
         */
        async function handleLike(postId) {
            const postIndex = posts.findIndex(p => p.id === postId);
            if (postIndex > -1) {
                // Prevent multiple likes from the same session (simple client-side check)
                // In a real app, this would be handled by a user session/database
                if (sessionStorage.getItem(`liked_post_${postId}`)) {
                    showMessageBox('You have already liked this post!');
                    return;
                }

                posts[postIndex].likes++;
                sessionStorage.setItem(`liked_post_${postId}`, 'true'); // Mark as liked in session storage
                await updatePostOnBackend(posts[postIndex]); // Send update to PHP
            }
        }

        /**
         * Handles sharing a post. (Simulated)
         * @param {string} postId - The ID of the post to share.
         */
        async function handleShare(postId) {
            const postIndex = posts.findIndex(p => p.id === postId);
            if (postIndex > -1) {
                // posts[postIndex].shares++; // Uncomment if you want to track shares
                // await updatePostOnBackend(posts[postIndex]); // Uncomment if you want to update shares on backend
                showMessageBox('Post shared successfully! (This is a simulated share)');
            }
        }

        /**
         * Toggles the visibility of the comments section for a post.
         * @param {string} postId - The ID of the post.
         */
        function toggleComments(postId) {
            const commentsSection = document.getElementById(`commentsSection-${postId}`);
            commentsSection.classList.toggle('hidden');
        }

        /**
         * Adds a comment to a post.
         * @param {string} postId - The ID of the post to comment on.
         */
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
                    userId: 'anonymous' // Placeholder for user ID. In a real app, get from session.
                };
                posts[postIndex].comments.push(newComment);
                await updatePostOnBackend(posts[postIndex]); // Send update to PHP
                commentInput.value = ''; // Clear input field

                // Re-render the specific post to show the new comment
                // This is more efficient than reloading all posts.
                const postCardElement = document.querySelector(`.card[data-post-id="${postId}"]`);
                if (postCardElement) {
                    // Find the updated post object from the 'posts' array
                    const updatedPost = posts[postIndex];
                    const newPostCardHtml = createPostCard(updatedPost);

                    // Create a temporary div to parse the new HTML string
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = newPostCardHtml;
                    const newPostElement = tempDiv.firstElementChild;

                    // Replace the old element with the new one
                    postCardElement.replaceWith(newPostElement);

                    // Re-open the comments section after re-rendering
                    document.getElementById(`commentsSection-${postId}`).classList.remove('hidden');
                }
            }
        }

        // --- Initialization ---
        document.addEventListener('DOMContentLoaded', () => {
            loadPosts(); // Load posts when the page loads
        });
    </script>
</body>
</html>