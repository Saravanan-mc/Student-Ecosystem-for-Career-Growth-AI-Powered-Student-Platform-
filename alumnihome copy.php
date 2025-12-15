<?php
include 'alumni.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Portal Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #4a6fa5; /* Alumni blue - remains the primary brand color */
            --primary-dark: #3a5a8a;
            --primary-light: #e6f0ff;
            --secondary: #DAA520; /* Gold accent - a rich, metallic gold */
            --secondary-dark: #B8860B; /* Darker gold for hover/active states */
            --secondary-light: #FFF8E1; /* Lighter gold for backgrounds */
            --success: #2ecc71;
            --success-dark: #27ae60;
            --danger: #e74c3c;
            --danger-dark: #c0392b;
            --warning: #f39c12;
            --gray: #7f8c8d; /* Slightly darker gray for better contrast */
            --gray-light: #ecf0f1;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --lighter: #ffffff;
            --sidebar-width: 280px;
            --sidebar-collapsed-width: 80px;
            --transition-speed: 0.3s ease;
            --shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --border-radius: 12px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', 'Roboto', sans-serif;
        }

        body {
            display: flex;
            min-height: 100vh;
            background-color: var(--primary-light);
            color: var(--dark);
            transition: margin-left var(--transition-speed);
        }

        .main-content {
            flex-grow: 1;
            padding: 2rem;
            transition: margin-left var(--transition-speed);
        }

        /* Buttons (Added for the welcome banner, adjust as needed for other buttons) */
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            background-color: var(--secondary);
            color: white;
            padding: 0.85rem 1.75rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background-color: var(--secondary-dark);
            transform: translateY(-2px);
        }

        .btn-outline {
            background-color: transparent;
            color: var(--lighter);
            border: 2px solid var(--lighter);
        }

        .btn-outline:hover {
            background-color: var(--lighter);
            color: var(--secondary);
            transform: translateY(-2px);
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2.5rem;
            border-radius: var(--border-radius);
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .welcome-banner::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            width: 40%;
            background: url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?ixlib=rb-4.0.3&auto=format&fit=crop&w=1470&q=80') center/cover;
            opacity: 0.15;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            max-width: 70%;
        }

        .welcome-banner h1 {
            font-size: 2.5rem;
            margin-bottom: 0.75rem;
            font-weight: 600;
        }

        .welcome-banner p {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 1.5rem;
        }

        .admin-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: var(--lighter);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--secondary); /* Gold vertical accent */
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .stat-card h3 {
            color: var(--gray);
            font-size: 1rem;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .stat-card .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--secondary); /* Gold value */
            margin-bottom: 0.5rem;
        }

        .stat-card .stat-change {
            font-size: 0.85rem;
            color: var(--success);
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .stat-card .stat-change.negative {
            color: var(--danger);
        }

        /* Quick Actions */
        .quick-actions {
            background: var(--lighter);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--dark);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .view-all {
            color: var(--primary);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .view-all:hover {
            color: var(--primary-dark);
        }

        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1rem;
        }

        .action-card {
            background: var(--lighter);
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            padding: 1.5rem;
            transition: all 0.3s ease;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
        }

        .action-card:hover {
            border-color: var(--secondary); /* Gold border on hover */
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            background: var(--secondary-light); /* Lighter gold background for icons */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: var(--secondary); /* Gold icon color */
            transition: all 0.3s ease;
        }

        .action-card:hover .action-icon {
            background: var(--secondary); /* Solid gold on hover */
            color: white;
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .action-desc {
            font-size: 0.85rem;
            color: var(--gray);
        }

        /* Recent Activity */
        .recent-activity {
            background: var(--lighter);
            padding: 2rem;
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
        }

        .activity-list {
            list-style: none;
        }

        .activity-item {
            padding: 1rem 0;
            border-bottom: 1px solid var(--gray-light);
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: var(--secondary-light); /* Lighter gold for activity icons */
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            color: var(--secondary); /* Gold icon color */
        }

        .activity-content {
            flex-grow: 1;
        }

        .activity-text {
            margin-bottom: 0.25rem;
            line-height: 1.4; /* Improve readability */
        }

        .activity-time {
            font-size: 0.8rem;
            color: var(--gray);
        }

        .activity-user {
            font-weight: 600;
            color: var(--primary); /* Keep user names in primary blue for distinction */
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .admin-stats {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (max-width: 992px) {
            .main-content {
                margin-left: 0;
                padding: 1.5rem;
            }
            
            .welcome-content {
                max-width: 100%;
            }
            
            .admin-stats {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .action-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .welcome-banner h1 {
                font-size: 2rem;
            }
            
            .admin-stats,
            .action-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .welcome-banner,
        .stat-card,
        .quick-actions,
        .recent-activity {
            animation: fadeIn 0.6s forwards;
        }

        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        .stat-card:nth-child(5) { animation-delay: 0.5s; }
        
        .activity-item {
            opacity: 0;
            animation: fadeIn 0.5s forwards;
        }
        
        .activity-item:nth-child(1) { animation-delay: 0.2s; }
        .activity-item:nth-child(2) { animation-delay: 0.3s; }
        .activity-item:nth-child(3) { animation-delay: 0.4s; }
        .activity-item:nth-child(4) { animation-delay: 0.5s; }
        .activity-item:nth-child(5) { animation-delay: 0.6s; }
    </style>
</head>
<body>
    <div class="main-content">
        <div class="welcome-banner">
            <div class="welcome-content">
                <h1><i class="fas fa-user-shield"></i> Alumni Portal Admin</h1>
                <p>Manage your alumni community, track engagement, and oversee all platform activities from this dashboard.</p>
                <div class="btn-group">
                    <button class="btn"><i class="fas fa-rocket"></i> Quick Tour</button>
                    <button class="btn btn-outline"><i class="fas fa-chart-line"></i> View Analytics</button>
                </div>
            </div>
        </div>

        <div class="admin-stats">
            <div class="stat-card">
                <h3><i class="fas fa-users"></i> Total Alumni</h3>
                <div class="stat-value">15,872</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> 12% from last month
                </div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-calendar-alt"></i> Events This Month</h3> <div class="stat-value">24</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> 3 new events added
                </div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-handshake"></i> Mentorships</h3>
                <div class="stat-value">187</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> 15 new connections
                </div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-comments"></i> New Discussions</h3>
                <div class="stat-value">342</div>
                <div class="stat-change negative">
                    <i class="fas fa-arrow-down"></i> 5% from last week
                </div>
            </div>
            <div class="stat-card">
                <h3><i class="fas fa-donate"></i> Donations</h3>
                <div class="stat-value">$48,250</div>
                <div class="stat-change">
                    <i class="fas fa-arrow-up"></i> 22% from last quarter
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
                <a href="#" class="view-all">View All Tools <i class="fas fa-chevron-right"></i></a>
            </div>
            
            <div class="action-grid">
                <a href="admin_dashboard.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-chart-bar"></i> </div>
                    <h3 class="action-title">Analytics Dashboard</h3>
                    <p class="action-desc">View detailed platform analytics</p>
                </a>
                <a href="advideo.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-video"></i>
                    </div>
                    <h3 class="action-title">Upload Video</h3>
                    <p class="action-desc">Add new video content</p>
                </a>
                <a href="adbook.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-book-medical"></i> </div>
                    <h3 class="action-title">Upload Book</h3>
                    <p class="action-desc">Add new book resources</p>
                </a>
                <a href="adbooks.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-book-open"></i> </div>
                    <h3 class="action-title">Manage Books</h3>
                    <p class="action-desc">Organize library content</p>
                </a>
                <a href="adai.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-brain"></i> </div>
                    <h3 class="action-title">AI Management</h3>
                    <p class="action-desc">Configure AI features</p>
                </a>
                <a href="adsocial.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-share-alt"></i> </div>
                    <h3 class="action-title">Social Media</h3> <p class="action-desc">Manage social content</p>
                </a>
                <a href="adupdate.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-bullhorn"></i> </div>
                    <h3 class="action-title">Post Update</h3>
                    <p class="action-desc">Send community updates</p>
                </a>
                <a href="adfeedback.php" class="action-card">
                    <div class="action-icon">
                        <i class="fas fa-envelope-open-text"></i> </div>
                    <h3 class="action-title">View Feedback</h3>
                    <p class="action-desc">Review user comments</p>
                </a>
            </div>
        </div>

        <div class="recent-activity">
            <div class="section-header">
                <h2 class="section-title"><i class="fas fa-history"></i> Recent Activity</h2>
                <a href="#" class="view-all">View Full Log <i class="fas fa-chevron-right"></i></a>
            </div>
            
            <ul class="activity-list">
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-upload"></i> </div>
                    <div class="activity-content">
                        <p class="activity-text"><span class="activity-user">Saravanan</span> uploaded a new alumni success story video</p>
                        <p class="activity-time">2 hours ago</p>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-plus-circle"></i> </div>
                    <div class="activity-content">
                        <p class="activity-text">5 new career development books were added to the library</p>
                        <p class="activity-time">Yesterday, 4:30 PM</p>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-cogs"></i> </div>
                    <div class="activity-content">
                        <p class="activity-text">New AI chatbot feature was deployed for alumni support</p>
                        <p class="activity-time">Yesterday, 1:15 PM</p>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="activity-content">
                        <p class="activity-text"><span class="activity-user">Admin</span> approved 12 new alumni registrations</p>
                        <p class="activity-time">May 15, 10:45 AM</p>
                    </div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon">
                        <i class="fas fa-comment-dots"></i> </div>
                    <div class="activity-content">
                        <p class="activity-text">24 new feedback messages received from alumni</p>
                        <p class="activity-time">May 14, 5:20 PM</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Animate stat numbers
            const statValues = document.querySelectorAll('.stat-value');
            
            statValues.forEach(value => {
                const target = parseInt(value.textContent.replace(/,/g, ''));
                const increment = target / 30; // Divides the animation into 30 steps
                let current = 0;
                
                const timer = setInterval(() => {
                    current += increment;
                    if (current >= target) {
                        clearInterval(timer);
                        value.textContent = target.toLocaleString();
                    } else {
                        value.textContent = Math.floor(current).toLocaleString();
                    }
                }, 30); // Updates every 30 milliseconds
            });
            
            // Sidebar toggle functionality would be here from your previous code
        });
    </script>
</body>
</html>