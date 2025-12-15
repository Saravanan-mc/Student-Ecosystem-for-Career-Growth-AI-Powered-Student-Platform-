<?php
session_start();
require_once 'admin.php';

class BookManager {
    private static function loadBooks(): array {
        return $_SESSION['books'] ?? [];
    }

    private static function saveBooks(array $books): void {
        $_SESSION['books'] = $books;
    }

    public static function addBook(string $number, string $name, int $pages, string $link): bool {
        $books = self::loadBooks();
        
        // Check if book number already exists
        foreach ($books as $book) {
            if ($book['number'] === $number) {
                return false;
            }
        }

        $books[] = [
            'number' => $number,
            'name' => $name,
            'pages' => $pages,
            'link' => $link
        ];

        self::saveBooks($books);
        return true;
    }

    public static function deleteBook(string $number): bool {
        $books = self::loadBooks();
        $initialCount = count($books);
        
        $books = array_filter($books, function($book) use ($number) {
            return $book['number'] !== $number;
        });

        if (count($books) < $initialCount) {
            self::saveBooks(array_values($books));
            return true;
        }
        return false;
    }

    public static function getAllBooks(): array {
        return self::loadBooks();
    }
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_book'])) {
        $number = filter_input(INPUT_POST, 'book_number', FILTER_SANITIZE_STRING);
        $name = filter_input(INPUT_POST, 'book_name', FILTER_SANITIZE_STRING);
        $pages = filter_input(INPUT_POST, 'book_pages', FILTER_VALIDATE_INT);
        $link = filter_input(INPUT_POST, 'book_link', FILTER_SANITIZE_URL);

        if ($number && $name && $pages !== false && $link) {
            $success = BookManager::addBook($number, $name, $pages, $link);
            if (!$success) {
                $error = "A book with this number already exists!";
            }
        } else {
            $error = "Invalid input data!";
        }
    }
}

// Handle delete requests
if (isset($_GET['delete_book_number'])) {
    $number = filter_input(INPUT_GET, 'delete_book_number', FILTER_SANITIZE_STRING);
    if ($number) {
        $success = BookManager::deleteBook($number);
        if (!$success) {
            $error = "Book not found or could not be deleted!";
        }
    }
}

$books = BookManager::getAllBooks();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Management System</title>
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --danger-color: #e74c3c;
            --light-color: #ecf0f1;
            --dark-color: #2c3e50;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #72d7ff, #5ca6ff);
            margin: 0;
            padding: 0;
            color: var(--dark-color);
            line-height: 1.6;
        }
        
        .content {
            margin-left: 300px;
            padding: 2rem;
            animation: fadeIn 0.5s ease-in-out;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
        }
        
        .card {
            background: white;
            border-radius: 8px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        h1, h2 {
            color: var(--dark-color);
            margin-top: 0;
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
            padding-bottom: 0.5rem;
        }
        
        h2 {
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="number"],
        input[type="url"],
        select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 4px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-danger {
            background-color: var(--danger-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-success:hover {
            background-color: #27ae60;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
            background-color: white;
            box-shadow: var(--shadow);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        
        th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #f1f1f1;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .link {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .link:hover {
            color: #2980b9;
            text-decoration: underline;
        }
        
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        @media (max-width: 992px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
        }
        
        @media (max-width: 768px) {
            table {
                display: block;
                overflow-x: auto;
            }
            
            .btn {
                padding: 0.5rem 1rem;
                font-size: 0.9rem;
            }
        }
    </style>
</head>
<body>
    <div class="content">
        <div class="container">
            <div class="card">
                <h1>Book Management</h1>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <h2>Add New Book</h2>
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="book_number">Book Number</label>
                        <input type="text" id="book_number" name="book_number" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="book_name">Book Name</label>
                        <input type="text" id="book_name" name="book_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="book_pages">Number of Pages</label>
                        <input type="number" id="book_pages" name="book_pages" required min="1">
                    </div>
                    
                    <div class="form-group">
                        <label for="book_link">Drive Link</label>
                        <input type="url" id="book_link" name="book_link" required placeholder="https://drive.google.com/...">
                    </div>
                    
                    <button type="submit" name="add_book" class="btn btn-primary">Add Book</button>
                </form>
            </div>
            
            <div class="card">
                <h2>Book List</h2>
                
                <?php if (empty($books)): ?>
                    <p>No books found. Please add some books.</p>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Book Number</th>
                                <th>Book Name</th>
                                <th>Pages</th>
                                <th>Drive Link</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($book['number']); ?></td>
                                    <td><?php echo htmlspecialchars($book['name']); ?></td>
                                    <td><?php echo htmlspecialchars($book['pages']); ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($book['link']); ?>" target="_blank" class="link">View</a>
                                    </td>
                                    <td class="action-buttons">
                                        <a href="?delete_book_number=<?php echo urlencode($book['number']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        // Confirm before deleting
        document.querySelectorAll('.btn-danger').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this book?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Show success message if URL has success parameter
        if (window.location.search.includes('success=true')) {
            alert('Operation completed successfully!');
        }
    </script>
</body>
</html>