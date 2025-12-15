<?php
include 'admin.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Video Social Network</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: rgba(67, 97, 238, 0.1);
            --secondary: #3f37c9;
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
        }

        .content {
            margin-left: 190px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .container {
            width: 800px;
            margin: 0 auto;
            padding: 0 1rem;
            padding-left: 40px;
            padding-right: 40px;
        }

        h1, h2 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        h2 {
            font-size: 1.8rem;
            text-align: center;
            margin-bottom: 2rem;
            position: relative;
        }

        h2::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--primary);
            margin: 0.5rem auto 0;
            border-radius: 2px;
        }

        /* Post Box */
        .post-box {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .post-box:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .post-input-container {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        #postInput {
            width: 100%;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid var(--gray-light);
            resize: none;
            font-size: 1rem;
            transition: all 0.3s ease;
            min-height: 100px;
        }

        #postInput:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        .media-upload {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 0.5rem;
        }

        .file-input-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .file-input-label:hover {
            background: rgba(67, 97, 238, 0.2);
        }

        #videoInput {
            display: none;
        }

        .post-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 1rem;
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
            gap: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-2px);
        }

        /* Feed Styles */
        #feed {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .post {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .post:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
        }

        .post-content {
            margin-bottom: 1rem;
        }

        .post-content p {
            font-size: 1rem;
            line-height: 1.6;
            color: var(--dark);
            margin-bottom: 1rem;
        }

        .post-media {
            width: 100%;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 1rem;
        }

        .post-media video {
            width: 100%;
            border-radius: 8px;
            display: block;
        }

        .post-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid var(--gray-light);
            padding-top: 1rem;
        }

        .post-actions {
            display: flex;
            gap: 0.5rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.5rem 1rem;
            background: var(--primary-light);
            color: var(--primary);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            border: none;
            font-size: 0.9rem;
        }

        .action-btn:hover {
            background: rgba(67, 97, 238, 0.2);
        }

        .action-btn.liked {
            color: var(--success);
            background: rgba(16, 185, 129, 0.1);
        }

        .action-btn i {
            font-size: 1rem;
        }

        .post-meta {
            font-size: 0.8rem;
            color: var(--gray);
        }

        /* Comment Section */
        .comments-section {
            margin-top: 1rem;
            padding: 1rem;
            background: var(--light);
            border-radius: 8px;
            display: none;
        }

        .comment-form {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .comment-input {
            flex: 1;
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--gray-light);
            font-size: 0.9rem;
        }

        .comment-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .comment-btn {
            padding: 0.75rem 1rem;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .comments-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .comment {
            background: white;
            padding: 0.75rem;
            border-radius: 8px;
            font-size: 0.9rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        /* Empty State */
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

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
        }

        @media (max-width: 768px) {
            .container {
                max-width: 100%;
            }
            
            .post-actions {
                flex-wrap: wrap;
            }
            
            .action-btn {
                padding: 0.5rem;
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="container">
            <h2 class="fade-in">Video Social Feed</h2>
            
            <div class="post-box fade-in">
                <div class="post-input-container">
                    <textarea id="postInput" placeholder="Share your thoughts or upload a video..."></textarea>
                    
                    <div class="media-upload">
                        <label for="videoInput" class="file-input-label">
                            <i class="fas fa-video"></i>
                            <span>Upload Video</span>
                        </label>
                        <input type="file" id="videoInput" accept="video/*">
                        <span id="fileName" style="font-size: 0.9rem; color: var(--gray);"></span>
                    </div>
                </div>
                
                <div class="post-actions">
                    <button onclick="addPost()" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Post
                    </button>
                </div>
            </div>
            
            <div id="feed">
                <!-- Posts will be loaded here -->
                <div class="empty-state fade-in" id="emptyFeed">
                    <i class="fas fa-video-slash"></i>
                    <h3>No Posts Yet</h3>
                    <p>Be the first to share a video or post!</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            loadPosts();
            
            // Show selected file name
            document.getElementById('videoInput').addEventListener('change', function(e) {
                const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
                document.getElementById('fileName').textContent = fileName;
            });
        });

        function addPost() {
            const postInput = document.getElementById("postInput");
            const videoInput = document.getElementById("videoInput");
            const feed = document.getElementById("feed");
            const emptyFeed = document.getElementById("emptyFeed");

            if (postInput.value.trim() === "" && !videoInput.files.length) {
                alert("Please write something or upload a video!");
                return;
            }

            // Hide empty state if it's the first post
            if (emptyFeed) {
                emptyFeed.style.display = "none";
            }

            const postId = Date.now().toString();
            const post = document.createElement("div");
            post.className = "post fade-in";
            post.id = `post-${postId}`;
            
            const postData = { 
                id: postId,
                text: postInput.value.trim(), 
                video: null, 
                likes: 0, 
                comments: [],
                date: new Date().toLocaleString()
            };

            // Create post content
            const postContent = document.createElement("div");
            postContent.className = "post-content";
            
            if (postData.text) {
                const textContent = document.createElement("p");
                textContent.innerText = postData.text;
                postContent.appendChild(textContent);
            }

            if (videoInput.files.length > 0) {
                const videoContainer = document.createElement("div");
                videoContainer.className = "post-media";
                
                const video = document.createElement("video");
                video.controls = true;
                const videoURL = URL.createObjectURL(videoInput.files[0]);
                video.src = videoURL;
                videoContainer.appendChild(video);
                postContent.appendChild(videoContainer);
                
                postData.video = videoURL;
            }

            post.appendChild(postContent);

            // Create post footer with actions
            const postFooter = document.createElement("div");
            postFooter.className = "post-footer";
            
            // Like button
            const likeBtn = document.createElement("button");
            likeBtn.className = "action-btn";
            likeBtn.innerHTML = '<i class="far fa-thumbs-up"></i> Like';
            likeBtn.onclick = () => toggleLike(postId, likeBtn);
            postFooter.appendChild(likeBtn);

            // Comment button
            const commentBtn = document.createElement("button");
            commentBtn.className = "action-btn";
            commentBtn.innerHTML = '<i class="far fa-comment"></i> Comment';
            commentBtn.onclick = () => toggleComments(postId);
            postFooter.appendChild(commentBtn);

            // Delete button
            const deleteBtn = document.createElement("button");
            deleteBtn.className = "action-btn";
            deleteBtn.innerHTML = '<i class="far fa-trash-alt"></i> Delete';
            deleteBtn.onclick = () => deletePost(postId);
            postFooter.appendChild(deleteBtn);

            // Post metadata
            const postMeta = document.createElement("div");
            postMeta.className = "post-meta";
            postMeta.textContent = `Posted just now`;
            postFooter.appendChild(postMeta);

            post.appendChild(postFooter);

            // Add to feed (at the top)
            feed.insertBefore(post, feed.firstChild);
            savePost(postData);

            // Clear inputs
            postInput.value = "";
            videoInput.value = "";
            document.getElementById("fileName").textContent = "";
        }

        function toggleLike(postId, button) {
            const posts = JSON.parse(localStorage.getItem("posts")) || [];
            const postIndex = posts.findIndex(p => p.id === postId);
            
            if (postIndex !== -1) {
                posts[postIndex].likes = posts[postIndex].likes === 0 ? 1 : 0;
                localStorage.setItem("posts", JSON.stringify(posts));
                
                if (posts[postIndex].likes === 1) {
                    button.innerHTML = '<i class="fas fa-thumbs-up"></i> Liked';
                    button.classList.add('liked');
                } else {
                    button.innerHTML = '<i class="far fa-thumbs-up"></i> Like';
                    button.classList.remove('liked');
                }
            }
        }

        function toggleComments(postId) {
            const postElement = document.getElementById(`post-${postId}`);
            let commentsSection = postElement.querySelector(".comments-section");
            
            if (!commentsSection) {
                commentsSection = document.createElement("div");
                commentsSection.className = "comments-section";
                
                // Get existing comments from storage
                const posts = JSON.parse(localStorage.getItem("posts")) || [];
                const postData = posts.find(p => p.id === postId);
                
                // Comment form
                const commentForm = document.createElement("div");
                commentForm.className = "comment-form";
                
                const commentInput = document.createElement("input");
                commentInput.type = "text";
                commentInput.className = "comment-input";
                commentInput.placeholder = "Write a comment...";
                commentInput.onkeydown = function(e) {
                    if (e.key === "Enter" && commentInput.value.trim() !== "") {
                        addComment(postId, commentInput.value);
                        commentInput.value = "";
                    }
                };
                
                const commentBtn = document.createElement("button");
                commentBtn.className = "comment-btn";
                commentBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
                commentBtn.onclick = () => {
                    if (commentInput.value.trim() !== "") {
                        addComment(postId, commentInput.value);
                        commentInput.value = "";
                    }
                };
                
                commentForm.appendChild(commentInput);
                commentForm.appendChild(commentBtn);
                commentsSection.appendChild(commentForm);
                
                // Comments list
                const commentsList = document.createElement("div");
                commentsList.className = "comments-list";
                
                if (postData && postData.comments.length > 0) {
                    postData.comments.forEach(comment => {
                        const commentElement = document.createElement("div");
                        commentElement.className = "comment";
                        commentElement.textContent = comment;
                        commentsList.appendChild(commentElement);
                    });
                }
                
                commentsSection.appendChild(commentsList);
                postElement.appendChild(commentsSection);
            }
            
            commentsSection.style.display = commentsSection.style.display === "none" ? "block" : "none";
        }

        function addComment(postId, commentText) {
            const posts = JSON.parse(localStorage.getItem("posts")) || [];
            const postIndex = posts.findIndex(p => p.id === postId);
            
            if (postIndex !== -1) {
                if (!posts[postIndex].comments) {
                    posts[postIndex].comments = [];
                }
                
                posts[postIndex].comments.push(commentText);
                localStorage.setItem("posts", JSON.stringify(posts));
                
                // Update UI
                const postElement = document.getElementById(`post-${postId}`);
                const commentsList = postElement.querySelector(".comments-list");
                
                const commentElement = document.createElement("div");
                commentElement.className = "comment";
                commentElement.textContent = commentText;
                commentsList.appendChild(commentElement);
            }
        }

        function deletePost(postId) {
            if (!confirm("Are you sure you want to delete this post?")) {
                return;
            }
            
            const posts = JSON.parse(localStorage.getItem("posts")) || [];
            const updatedPosts = posts.filter(p => p.id !== postId);
            localStorage.setItem("posts", JSON.stringify(updatedPosts));
            
            const postElement = document.getElementById(`post-${postId}`);
            if (postElement) {
                postElement.remove();
            }
            
            // Show empty state if no posts left
            if (updatedPosts.length === 0) {
                document.getElementById("emptyFeed").style.display = "block";
            }
        }

        function savePost(postData) {
            const posts = JSON.parse(localStorage.getItem("posts")) || [];
            posts.unshift(postData);
            localStorage.setItem("posts", JSON.stringify(posts));
        }

        function loadPosts() {
            const feed = document.getElementById("feed");
            const emptyFeed = document.getElementById("emptyFeed");
            const posts = JSON.parse(localStorage.getItem("posts")) || [];
            
            if (posts.length === 0) {
                emptyFeed.style.display = "block";
                return;
            } else {
                emptyFeed.style.display = "none";
            }
            
            feed.innerHTML = "";
            
            posts.forEach(postData => {
                const post = document.createElement("div");
                post.className = "post fade-in";
                post.id = `post-${postData.id}`;
                
                // Post content
                const postContent = document.createElement("div");
                postContent.className = "post-content";
                
                if (postData.text) {
                    const textContent = document.createElement("p");
                    textContent.innerText = postData.text;
                    postContent.appendChild(textContent);
                }
                
                if (postData.video) {
                    const videoContainer = document.createElement("div");
                    videoContainer.className = "post-media";
                    
                    const video = document.createElement("video");
                    video.controls = true;
                    video.src = postData.video;
                    videoContainer.appendChild(video);
                    postContent.appendChild(videoContainer);
                }
                
                post.appendChild(postContent);
                
                // Post footer
                const postFooter = document.createElement("div");
                postFooter.className = "post-footer";
                
                // Like button
                const likeBtn = document.createElement("button");
                likeBtn.className = `action-btn ${postData.likes === 1 ? 'liked' : ''}`;
                likeBtn.innerHTML = postData.likes === 1 ? 
                    '<i class="fas fa-thumbs-up"></i> Liked' : 
                    '<i class="far fa-thumbs-up"></i> Like';
                likeBtn.onclick = () => toggleLike(postData.id, likeBtn);
                postFooter.appendChild(likeBtn);
                
                // Comment button
                const commentBtn = document.createElement("button");
                commentBtn.className = "action-btn";
                commentBtn.innerHTML = '<i class="far fa-comment"></i> Comment';
                commentBtn.onclick = () => toggleComments(postData.id);
                postFooter.appendChild(commentBtn);
                
                // Delete button
                const deleteBtn = document.createElement("button");
                deleteBtn.className = "action-btn";
                deleteBtn.innerHTML = '<i class="far fa-trash-alt"></i> Delete';
                deleteBtn.onclick = () => deletePost(postData.id);
                postFooter.appendChild(deleteBtn);
                
                // Post metadata
                const postMeta = document.createElement("div");
                postMeta.className = "post-meta";
                postMeta.textContent = `Posted on ${postData.date || 'unknown time'}`;
                postFooter.appendChild(postMeta);
                
                post.appendChild(postFooter);
                feed.appendChild(post);
            });
        }
    </script>
</body>
</html>