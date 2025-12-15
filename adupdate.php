<?php
session_start();

if (!isset($_SESSION['feedbacks'])) {
    $_SESSION['feedbacks'] = [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message']) && !empty(trim($_POST['message']))) {
        $message = htmlspecialchars(trim($_POST['message']));
        $username = isset($_POST['username']) ? htmlspecialchars(trim($_POST['username'])) : 'Anonymous';
        
        $_SESSION['feedbacks'][] = [
            'username' => $username,
            'message' => $message,
            'time' => date('Y-m-d H:i:s'),
            'priority' => isset($_POST['priority']) ? $_POST['priority'] : 'normal'
        ];
    } elseif (isset($_POST['delete_index'])) {
        $deleteIndex = intval($_POST['delete_index']);
        if (isset($_SESSION['feedbacks'][$deleteIndex])) {
            array_splice($_SESSION['feedbacks'], $deleteIndex, 1);
        }
    }
}

include 'admin.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Updates</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: rgba(67, 97, 238, 0.1);
            --secondary: #3f37c9;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
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
            margin-left: 200px;
            padding: 2rem;
            transition: margin-left 0.3s ease;
        }

        .container {
            width: 800px;
            margin: 0 auto;
        }

        h1, h2, h3 {
            color: var(--dark);
            margin-bottom: 1.5rem;
            font-weight: 600;
        }

        h1 {
            font-size: 2rem;
            text-align: center;
            position: relative;
            padding-bottom: 1rem;
        }

        h1::after {
            content: '';
            display: block;
            width: 80px;
            height: 4px;
            background: var(--primary);
            margin: 0.5rem auto 0;
            border-radius: 2px;
        }

        .update-form {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
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
        textarea,
        select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
        }

        textarea {
            min-height: 150px;
            resize: vertical;
        }

        .priority-select {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .priority-option {
            flex: 1;
            text-align: center;
        }

        .priority-option input[type="radio"] {
            display: none;
        }

        .priority-option label {
            display: block;
            padding: 0.5rem;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid var(--gray-light);
        }

        .priority-option input[type="radio"]:checked + label {
            background: var(--primary-light);
            border-color: var(--primary);
            color: var(--primary);
            font-weight: 600;
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(67, 97, 238, 0.2);
        }

        .priority-option.low label {
            border-color: var(--success);
        }

        .priority-option.low input[type="radio"]:checked + label {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
        }

        .priority-option.high label {
            border-color: var(--danger);
        }

        .priority-option.high input[type="radio"]:checked + label {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
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
            box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3);
        }

        .updates-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .update-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .update-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
        }

        .update-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
            border-bottom: 1px solid var(--gray-light);
            padding-bottom: 0.75rem;
        }

        .update-username {
            font-weight: 600;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .update-time {
            font-size: 0.8rem;
            color: var(--gray);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .update-message {
            margin: 1rem 0;
            line-height: 1.6;
            color: var(--dark);
        }

        .update-priority {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 0.5rem;
        }

        .priority-low {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .priority-normal {
            background: rgba(67, 97, 238, 0.1);
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .priority-high {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .update-actions {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .btn-danger {
            background-color: var(--danger);
            color: white;
        }

        .btn-danger:hover {
            background-color: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: var(--gray);
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            margin-top: 2rem;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--gray-light);
            text-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out forwards;
            opacity: 0; 
        }

        .update-form.fade-in {
            animation-delay: 0.1s;
        }

        h2.fade-in {
            animation-delay: 0.2s;
        }

        .update-card.fade-in {
            animation-delay: 0.3s; 
        }

        .update-card:nth-child(2).fade-in { animation-delay: 0.4s; }
        .update-card:nth-child(3).fade-in { animation-delay: 0.5s; }
        .update-card:nth-child(4).fade-in { animation-delay: 0.6s; }


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
            
            .update-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            
            .priority-select {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="container">
            <h1 class="fade-in">System Updates</h1>
            
            <form method="POST" class="update-form fade-in">
                <div class="form-group">
                    <label for="message">Update Message</label>
                    <textarea id="message" name="message" required placeholder="Enter your update message..."></textarea>
                </div>
                
                <div class="form-group">
                    <label>Priority</label>
                    <div class="priority-select">
                        <div class="priority-option low">
                            <input type="radio" id="priority-low" name="priority" value="low" checked>
                            <label for="priority-low"><i class="fas fa-arrow-down"></i> Low</label>
                        </div>
                        <div class="priority-option">
                            <input type="radio" id="priority-normal" name="priority" value="normal">
                            <label for="priority-normal"><i class="fas fa-equals"></i> Normal</label>
                        </div>
                        <div class="priority-option high">
                            <input type="radio" id="priority-high" name="priority" value="high">
                            <label for="priority-high"><i class="fas fa-arrow-up"></i> High</label>
                        </div>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Post Update
                </button>
            </form>
            
            <h2 class="fade-in">Recent Updates</h2>
            
            <div class="updates-list">
                <?php if (!empty($_SESSION['feedbacks'])): ?>
                    <?php foreach (array_reverse($_SESSION['feedbacks'], true) as $index => $feedback): ?>
                        <div class="update-card fade-in">
                            <div class="update-header">
                                <span class="update-username">
                                    <i class="fas fa-user"></i> <?= htmlspecialchars($feedback['username']) ?>
                                </span>
                                <span class="update-time">
                                    <i class="far fa-clock"></i> <?= htmlspecialchars($feedback['time']) ?>
                                </span>
                            </div>
                            
                            <span class="update-priority priority-<?= htmlspecialchars($feedback['priority']) ?>">
                                <?= htmlspecialchars($feedback['priority']) ?> priority
                            </span>
                            
                            <div class="update-message">
                                <?= nl2br(htmlspecialchars($feedback['message'])) ?>
                            </div>
                            
                            <div class="update-actions">
                                <form method="POST">
                                    <input type="hidden" name="delete_index" value="<?= $index ?>">
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this update?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state fade-in">
                        <i class="fas fa-bell-slash"></i>
                        <h3>No Updates Yet</h3>
                        <p>Be the first to post an update</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const message = document.getElementById('message');
            if (message.value.trim() === '') {
                e.preventDefault();
                alert('Please enter an update message');
                message.focus();
            }
        });
    </script>
</body>
</html>