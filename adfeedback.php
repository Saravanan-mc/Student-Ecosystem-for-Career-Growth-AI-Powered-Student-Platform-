<?php

// Start the session at the very beginning to ensure it's available for admin functions.
// This is crucial for authentication, authorization, or flash messages.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the admin header/navigation file.
// Using 'require_once' is safer for critical includes like this,
// as it will stop execution if the file is missing or has fatal errors.
require_once 'admin.php'; // Assuming 'admin.php' handles sidebar/navigation for the admin panel

// Database connection parameters.
// In a real-world scenario, consider storing these outside of your web root
// and loading them securely (e.g., from environment variables or a config file).
$formServername = "localhost";
$formUsername = "root";
$formPassword = ""; // WARNING: Never use empty password in production! Use a strong password and restrict database user privileges.
$formDbname = "feedback_db";

// Establish database connection.
$formConn = new mysqli($formServername, $formUsername, $formPassword, $formDbname);

// Check if connection was successful.
if ($formConn->connect_error) {
    // Log the actual error for debugging, but provide a generic message to the user.
    error_log("Database Connection failed: " . $formConn->connect_error);
    // Terminate script execution gracefully.
    die("<div style='text-align: center; padding: 50px; font-family: sans-serif; color: #ef4444;'>
            <h1><i class='fas fa-exclamation-triangle'></i> Database Connection Error</h1>
            <p>We are experiencing technical difficulties. Please try again later.</p>
         </div>");
}

// Initialize delete message variable.
// Setting it to null prevents "Undefined variable" notices on initial page load.
$formDeleteMessage = null;

// Handle feedback deletion request.
if (isset($_GET['delete_id'])) {
    // Sanitize and validate the input ID to ensure it's an integer.
    $formDeleteId = filter_var($_GET['delete_id'], FILTER_VALIDATE_INT);

    if ($formDeleteId === false) {
        // Handle invalid ID.
        $formDeleteMessage = [
            'text' => "Invalid feedback ID provided.",
            'type' => 'error'
        ];
    } else {
        // Prepare a parameterized SQL statement to prevent SQL injection.
        $formDeleteSql = "DELETE FROM feedbacks WHERE id = ?";
        $formStmt = $formConn->prepare($formDeleteSql);

        if ($formStmt === false) {
            // Handle statement preparation error.
            error_log("Failed to prepare delete statement: " . $formConn->error);
            $formDeleteMessage = [
                'text' => "An internal error occurred during deletion preparation.",
                'type' => 'error'
            ];
        } else {
            // Bind the integer ID parameter.
            $formStmt->bind_param("i", $formDeleteId);

            // Execute the prepared statement.
            if ($formStmt->execute()) {
                // Check if any rows were affected (i.e., if a record was actually deleted).
                if ($formStmt->affected_rows > 0) {
                    $formDeleteMessage = [
                        'text' => "Feedback deleted successfully.",
                        'type' => 'success'
                    ];
                } else {
                    // No row found with the given ID.
                    $formDeleteMessage = [
                        'text' => "Feedback with ID '{$formDeleteId}' not found or already deleted.",
                        'type' => 'warning'
                    ];
                }
            } else {
                // Handle execution error.
                error_log("Failed to execute delete statement: " . $formStmt->error);
                $formDeleteMessage = [
                    'text' => "Error deleting feedback. Please try again.",
                    'type' => 'error'
                ];
            }
            // Close the statement.
            $formStmt->close();
        }
    }
}

// Determine current sorting parameters.
$formSort = isset($_GET['sort']) ? $_GET['sort'] : 'submission_time';
$formOrder = isset($_GET['order']) ? $_GET['order'] : 'DESC';

// Define a whitelist of allowed sortable columns to prevent SQL injection.
$formValidSorts = ['name', 'student_id', 'subject', 'submission_time'];

// Validate and sanitize sorting parameters.
$formSort = in_array($formSort, $formValidSorts) ? $formSort : 'submission_time';
$formOrder = ($formOrder === 'ASC' || $formOrder === 'DESC') ? $formOrder : 'DESC';

// Construct the SQL query for fetching feedback records.
// Direct concatenation of $formSort and $formOrder is safe here because
// they have been rigorously validated against a whitelist.
$formSql = "SELECT id, name, student_id, subject, feedback, submission_time
            FROM feedbacks
            ORDER BY {$formSort} {$formOrder}";

// Execute the query.
$formResult = $formConn->query($formSql);

// Handle query execution errors.
if ($formResult === false) {
    error_log("Failed to fetch feedback data: " . $formConn->error);
    $formResult = null; // Ensure $formResult is null if the query fails, for consistent empty state handling.
    // Optionally, set an error message for display to the admin.
    $formDeleteMessage = [
        'text' => "Could not retrieve feedback data. Please try again later.",
        'type' => 'error'
    ];
}

// Close the database connection.
$formConn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6C63FF; /* A vibrant purple */
            --primary-light: #e0eaff;
            --secondary-color: #534CAF;
            --success-color: #10B981; /* Green */
            --danger-color: #EF4444; /* Red */
            --warning-color: #F59E0B; /* Orange */
            --info-color: #3B82F6; /* Blue */
            --dark-text: #333;
            --light-text: #f9fafb;
            --gray-text: #6B7280;
            --border-color: #E5E7EB;
            --bg-light: #F8F9FA; /* Very light gray for background */
            --bg-medium: #e9ecef;
            --card-bg: #FFFFFF;
            --shadow-light: rgba(0, 0, 0, 0.05);
            --shadow-medium: rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif; /* Using Poppins for a modern feel */
        }

        body {
            background: var(--bg-light);
            color: var(--dark-text);
            min-height: 100vh;
            display: flex; /* Use flexbox for main layout */
        }

        /* Assuming admin.php creates a sidebar. Adjust margin-left accordingly. */
        /* For demonstration, I'm setting a fixed margin, in a real app this would be dynamic or handled by a grid/flex layout */
        .form-content {
            flex-grow: 1;
            margin-left: 280px; /* Adjust this based on your admin sidebar width */
            padding: 2.5rem;
            transition: margin-left 0.3s ease;
        }

        .form-container {
            max-width: 1300px; /* Slightly wider container */
            margin: 0 auto;
        }

        h1, h2 {
            color: var(--dark-text);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        h1 {
            font-size: 2.5rem; /* Larger heading */
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
            color: var(--primary-color);
        }

        h1::after {
            content: '';
            display: block;
            width: 100px; /* Wider underline */
            height: 4px;
            background: var(--primary-color);
            margin: 0.75rem auto 0;
            border-radius: 2px;
            opacity: 0.8;
        }

        .form-alert {
            padding: 1.25rem;
            border-radius: 10px; /* Softer corners */
            margin-bottom: 2rem; /* More space below alerts */
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.6s ease-out;
            box-shadow: 0 4px 15px var(--shadow-light); /* Subtle shadow for alerts */
            font-weight: 500;
        }

        .form-alert-success {
            background-color: #d1fae5;
            color: #065f46;
            border-left: 6px solid var(--success-color); /* Thicker border */
        }

        .form-alert-error {
            background-color: #fee2e2;
            color: #b91c1c;
            border-left: 6px solid var(--danger-color);
        }

        .form-alert-warning {
            background-color: #fffbeb;
            color: #b45309;
            border-left: 6px solid var(--warning-color);
        }

        .form-alert i {
            margin-right: 1rem;
            font-size: 1.5rem; /* Larger icons */
        }

        .form-table-container {
            overflow-x: auto;
            margin-bottom: 2rem;
            border-radius: 12px;
            box-shadow: 0 8px 25px var(--shadow-medium); /* More prominent shadow */
            background: var(--card-bg);
            animation: fadeIn 1s ease-out;
        }

        .form-table {
            width: 100%;
            border-collapse: separate; /* Use separate to allow border-radius on cells/rows if needed */
            border-spacing: 0;
            background: var(--card-bg);
        }

        .form-table th, .form-table td {
            padding: 1.25rem 1.5rem; /* More padding */
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .form-table th {
            background-color: var(--bg-medium); /* Slightly darker header background */
            font-weight: 600;
            color: var(--dark-text);
            position: sticky; /* Make header sticky */
            top: 0; /* Stick to the top */
            z-index: 10; /* Ensure it's above table content */
        }

        .form-table thead th:first-child {
            border-top-left-radius: 12px;
        }
        .form-table thead th:last-child {
            border-top-right-radius: 12px;
        }

        .form-table th.form-sortable {
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .form-table th.form-sortable:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px); /* Slight lift on hover */
        }

        .form-sort-icon {
            margin-left: 0.75rem;
            color: var(--primary-color);
            font-size: 0.9em;
        }

        .form-table tr:last-child td {
            border-bottom: none; /* No border on the last row */
        }

        .form-table tbody tr:hover td {
            background-color: var(--primary-light);
            cursor: pointer; /* Indicate row is interactive (though only delete is available) */
        }
        .form-table tbody tr {
            transition: background-color 0.2s ease-in-out;
        }


        .form-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.6rem 1.2rem; /* Slightly larger buttons */
            border-radius: 8px; /* Softer corners */
            font-size: 0.95rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            gap: 0.6rem; /* More space between icon and text */
            border: none; /* Remove default button border */
        }

        .form-btn-danger {
            background-color: var(--danger-color);
            color: white;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2); /* Shadow for danger button */
        }

        .form-btn-danger:hover {
            background-color: #dc2626; /* Darker red on hover */
            transform: translateY(-3px); /* More pronounced lift */
            box-shadow: 0 6px 15px rgba(239, 68, 68, 0.3);
        }
        .form-btn-danger:active {
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(239, 68, 68, 0.2);
        }

        .form-feedback-content {
            max-width: 350px; /* Slightly wider preview */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
        }

        .form-feedback-content.form-expanded {
            white-space: normal;
            overflow: visible;
            text-overflow: unset;
        }

        .form-read-more {
            color: var(--primary-color);
            cursor: pointer;
            font-size: 0.85rem;
            margin-top: 0.6rem; /* More space */
            display: inline-block;
            font-weight: 500;
            text-decoration: underline; /* Emphasize it's a link */
            transition: color 0.2s ease;
        }
        .form-read-more:hover {
            color: var(--secondary-color);
        }

        .form-empty-state {
            text-align: center;
            padding: 4rem; /* More padding */
            color: var(--gray-text);
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-light);
        }

        .form-empty-state i {
            font-size: 4rem; /* Larger icon */
            margin-bottom: 1.5rem;
            color: var(--border-color);
        }
        .form-empty-state h3 {
            font-size: 1.75rem;
            margin-bottom: 0.75rem;
            color: var(--dark-text);
        }
        .form-empty-state p {
            font-size: 1.1rem;
            color: var(--gray-text);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); } /* More pronounced animation */
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsive adjustments */
        @media (max-width: 992px) {
            .form-content {
                margin-left: 0; /* Full width on smaller screens */
                padding: 1.5rem;
            }
        }

        @media (max-width: 768px) {
            h1 {
                font-size: 2rem;
            }
            .form-table th, .form-table td {
                padding: 1rem;
                font-size: 0.85rem;
            }
            
            .form-action-btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.8rem;
            }

            .form-alert {
                padding: 1rem;
                font-size: 0.9rem;
            }
            .form-alert i {
                font-size: 1.2rem;
            }
        }

        @media (max-width: 480px) {
            h1 {
                font-size: 1.75rem;
            }
            .form-content {
                padding: 1rem;
            }
            .form-table th, .form-table td {
                padding: 0.75rem;
            }
            .form-action-btn {
                font-size: 0.75rem;
                padding: 0.3rem 0.6rem;
                gap: 0.3rem;
            }
            .form-feedback-content {
                max-width: 200px; /* Adjust for very small screens */
            }
            .form-empty-state {
                padding: 2rem;
            }
            .form-empty-state i {
                font-size: 3rem;
            }
            .form-empty-state h3 {
                font-size: 1.5rem;
            }
            .form-empty-state p {
                font-size: 1rem;
            }
        }

    </style>
</head>
<body>
    <div class="form-content">
        <div class="form-container">
            <h1 class="form-fade-in">Feedback Management</h1>
            
            <?php if (isset($formDeleteMessage)): ?>
                <div class="form-alert form-alert-<?= htmlspecialchars($formDeleteMessage['type']) ?> form-fade-in">
                    <i class="fas <?= $formDeleteMessage['type'] === 'success' ? 'fa-check-circle' : ($formDeleteMessage['type'] === 'warning' ? 'fa-exclamation-triangle' : 'fa-exclamation-circle') ?>"></i>
                    <?= htmlspecialchars($formDeleteMessage['text']) ?>
                </div>
            <?php endif; ?>
            
            <div class="form-table-container form-fade-in">
                <?php if ($formResult && $formResult->num_rows > 0): ?>
                    <table class="form-table">
                        <thead>
                            <tr>
                                <th class="form-sortable" onclick="sortTable('name')">
                                    Name 
                                    <span class="form-sort-icon">
                                        <?= ($formSort === 'name' && $formOrder === 'ASC') ? '↑' : (($formSort === 'name' && $formOrder === 'DESC') ? '↓' : '') ?>
                                    </span>
                                </th>
                                <th class="form-sortable" onclick="sortTable('student_id')">
                                    Student ID 
                                    <span class="form-sort-icon">
                                        <?= ($formSort === 'student_id' && $formOrder === 'ASC') ? '↑' : (($formSort === 'student_id' && $formOrder === 'DESC') ? '↓' : '') ?>
                                    </span>
                                </th>
                                <th class="form-sortable" onclick="sortTable('subject')">
                                    Subject 
                                    <span class="form-sort-icon">
                                        <?= ($formSort === 'subject' && $formOrder === 'ASC') ? '↑' : (($formSort === 'subject' && $formOrder === 'DESC') ? '↓' : '') ?>
                                    </span>
                                </th>
                                <th>Feedback</th>
                                <th class="form-sortable" onclick="sortTable('submission_time')">
                                    Submission Time 
                                    <span class="form-sort-icon">
                                        <?= ($formSort === 'submission_time' && $formOrder === 'ASC') ? '↑' : (($formSort === 'submission_time' && $formOrder === 'DESC') ? '↓' : '') ?>
                                    </span>
                                </th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($formRow = $formResult->fetch_assoc()): ?>
                                <tr>
                                    <td><?= htmlspecialchars($formRow["name"]) ?></td>
                                    <td><?= htmlspecialchars($formRow["student_id"]) ?></td>
                                    <td><?= htmlspecialchars($formRow["subject"]) ?></td>
                                    <td>
                                        <div class="form-feedback-content">
                                            <?= nl2br(htmlspecialchars($formRow["feedback"])) ?>
                                        </div>
                                        <?php if (strlen($formRow["feedback"]) > 100): // Adjust character count as needed for "read more" ?>
                                            <span class="form-read-more" onclick="toggleReadMore(this)">Read more</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($formRow["submission_time"]) ?></td>
                                    <td>
                                        <a href="?delete_id=<?= htmlspecialchars($formRow['id']) ?>&sort=<?= urlencode($formSort) ?>&order=<?= urlencode($formOrder) ?>" 
                                            onclick="return confirm('Are you sure you want to delete this feedback?');"
                                            class="form-action-btn form-btn-danger">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="form-empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <h3>No Feedback Found</h3>
                        <p>It looks like no feedback has been submitted yet. Come back later!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function toggleReadMore(element) {
            const content = element.previousElementSibling;
            content.classList.toggle('form-expanded');
            element.textContent = content.classList.contains('form-expanded') ? 'Read less' : 'Read more';
        }

        function sortTable(column) {
            const urlParams = new URLSearchParams(window.location.search);
            let sort = urlParams.get('sort') || 'submission_time';
            let order = urlParams.get('order') || 'DESC';
            
            if (sort === column) {
                order = order === 'ASC' ? 'DESC' : 'ASC';
            } else {
                sort = column;
                order = 'ASC'; // Default to ASC when changing sort column
            }
            
            urlParams.set('sort', sort);
            urlParams.set('order', order);
            window.location.search = urlParams.toString();
        }

        // Auto-hide success and warning alerts after 5 seconds
        setTimeout(() => {
            const successAlert = document.querySelector('.form-alert-success');
            if (successAlert) {
                successAlert.style.opacity = '0';
                successAlert.style.transition = 'opacity 1s ease-out';
                setTimeout(() => successAlert.style.display = 'none', 1000); // Fully hide after transition
            }
            const warningAlert = document.querySelector('.form-alert-warning');
            if (warningAlert) {
                warningAlert.style.opacity = '0';
                warningAlert.style.transition = 'opacity 1s ease-out';
                setTimeout(() => warningAlert.style.display = 'none', 1000); // Fully hide after transition
            }
        }, 5000); // 5000 milliseconds = 5 seconds
    </script>
</body>
</html>