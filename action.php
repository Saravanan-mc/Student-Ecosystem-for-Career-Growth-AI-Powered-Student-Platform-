<?php
// action.php - Handles all data operations for the student discussion system

header('Content-Type: application/json'); // Ensure JSON response

// Define the path to your data file
// Changed from 'data/posts.json' to 'data_std/posts.json' as requested.
$dataDir = __DIR__ . '/data_std';
$dataFile = $dataDir . '/posts.json';

// Ensure the data directory exists
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true); // Create directory with write permissions
}

// Ensure the posts.json file exists and is writable
if (!file_exists($dataFile)) {
    file_put_contents($dataFile, json_encode([])); // Create empty JSON array if it doesn't exist
}

// Function to read posts from the JSON file
function getPosts($dataFile) {
    // Acquire a shared lock for reading
    $fp = fopen($dataFile, 'r');
    if (!$fp) {
        error_log("Failed to open data file for reading: " . $dataFile);
        return [];
    }
    if (flock($fp, LOCK_SH)) { // Acquire shared lock
        $json = file_get_contents($dataFile);
        flock($fp, LOCK_UN); // Release the lock
    } else {
        error_log("Failed to acquire shared lock for reading: " . $dataFile);
        $json = file_get_contents($dataFile); // Attempt to read without lock if lock fails (less safe)
    }
    fclose($fp);

    $posts = json_decode($json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decoding JSON from " . $dataFile . ": " . json_last_error_msg());
        return []; // Return empty array on JSON decode error
    }
    return $posts;
}

// Function to write posts to the JSON file
function savePosts($dataFile, $posts) {
    $json = json_encode($posts, JSON_PRETTY_PRINT);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error encoding JSON for " . $dataFile . ": " . json_last_error_msg());
        return false;
    }

    // Acquire an exclusive lock for writing
    $fp = fopen($dataFile, 'w');
    if (!$fp) {
        error_log("Failed to open data file for writing: " . $dataFile);
        return false;
    }
    if (flock($fp, LOCK_EX)) { // Acquire exclusive lock
        fwrite($fp, $json);
        fflush($fp); // Ensure all buffered output is written
        flock($fp, LOCK_UN); // Release the lock
        fclose($fp);
        return true;
    } else {
        error_log("Failed to acquire exclusive lock for writing: " . $dataFile);
        fclose($fp);
        return false; // Indicate failure
    }
}

// Handle different actions based on GET or POST requests
$action = $_GET['action'] ?? ''; // Get action from URL parameter
$method = $_SERVER['REQUEST_METHOD']; // Get request method

switch ($action) {
    case 'get_posts':
        // Return all posts
        echo json_encode(getPosts($dataFile));
        break;

    case 'add_post':
        if ($method === 'POST') {
            $input = file_get_contents('php://input');
            $newPost = json_decode($input, true);

            // Basic validation for new post
            if (empty($newPost['id']) || empty($newPost['type']) || empty($newPost['title']) || empty($newPost['content'])) {
                echo json_encode(['success' => false, 'message' => 'Missing required post fields.']);
                exit;
            }

            // Sanitize input (CRUCIAL FOR SECURITY IN REAL APPS)
            // Example: $newPost['title'] = htmlspecialchars($newPost['title'], ENT_QUOTES, 'UTF-8');
            // For a real app, use prepared statements if using a database.

            $posts = getPosts($dataFile);
            array_unshift($posts, $newPost); // Add new post to the beginning

            if (savePosts($dataFile, $posts)) {
                echo json_encode(['success' => true, 'message' => 'Post added successfully.']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save post.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method for add_post.']);
        }
        break;

    case 'update_post':
        if ($method === 'POST') {
            $input = file_get_contents('php://input');
            $updatedPost = json_decode($input, true);

            // Basic validation for updated post
            if (empty($updatedPost['id'])) {
                echo json_encode(['success' => false, 'message' => 'Missing post ID for update.']);
                exit;
            }

            $posts = getPosts($dataFile);
            $found = false;
            foreach ($posts as &$post) { // Use reference to modify array in place
                if ($post['id'] === $updatedPost['id']) {
                    // Update only specific fields that can change (likes, shares, comments)
                    // In a real app, validate and sanitize all incoming data.
                    $post['likes'] = $updatedPost['likes'] ?? $post['likes'];
                    $post['shares'] = $updatedPost['shares'] ?? $post['shares'];
                    $post['comments'] = $updatedPost['comments'] ?? $post['comments'];
                    $found = true;
                    break;
                }
            }
            unset($post); // Break the reference

            if ($found) {
                if (savePosts($dataFile, $posts)) {
                    echo json_encode(['success' => true, 'message' => 'Post updated successfully.']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to save updated post.']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Post not found for update.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid request method for update_post.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        break;
}

?>
