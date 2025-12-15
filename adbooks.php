<?php
require_once 'admin.php';

class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        $this->conn = new mysqli('localhost', 'root', '', 'library');
        
        if ($this->conn->connect_error) {
            die("Database connection failed: " . $this->conn->connect_error);
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->conn;
    }

    public function query($sql, $params = []) {
        $stmt = $this->conn->prepare($sql);
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        return $stmt;
    }
}

class Book {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM books ORDER BY book_name ASC");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function save($data) {
        if (empty($data['book_number']) || empty($data['book_name'])) {
            throw new Exception("Book number and name are required");
        }

        if (!empty($data['id'])) {
            $stmt = $this->db->prepare(
                "UPDATE books SET 
                book_number = ?, 
                book_name = ?, 
                student_id = ?, 
                available = ? 
                WHERE id = ?"
            );
            $stmt->bind_param(
                'ssssi',
                $data['book_number'],
                $data['book_name'],
                $data['student_id'],
                $data['available'],
                $data['id']
            );
        } else {
            $stmt = $this->db->prepare(
                "INSERT INTO books 
                (book_number, book_name, student_id, available) 
                VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'ssss',
                $data['book_number'],
                $data['book_name'],
                $data['student_id'],
                $data['available']
            );
        }

        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM books WHERE id = ?");
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}


$bookManager = new Book();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    try {
        $data = [
            'id' => $_POST['id'] ?? null,
            'book_number' => htmlspecialchars(trim($_POST['book_number'])),
            'book_name' => htmlspecialchars(trim($_POST['book_name'])),
            'student_id' => htmlspecialchars(trim($_POST['student_id'])),
            'available' => in_array($_POST['available'], ['yes', 'no']) ? $_POST['available'] : 'no'
        ];
        
        $success = $bookManager->save($data);
        $_SESSION['message'] = $success ? 'Operation completed successfully' : 'Error occurred';
        $_SESSION['message_type'] = $success ? 'success' : 'error';
        
        // Redirect to prevent form resubmission
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = $e->getMessage();
        $_SESSION['message_type'] = 'error';
    }
}

// Handle delete requests
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $success = $bookManager->delete($id);
        $_SESSION['message'] = $success ? 'Book deleted successfully' : 'Error deleting book';
        $_SESSION['message_type'] = $success ? 'success' : 'error';
        
        // Redirect to prevent refresh issues
        header("Location: ".$_SERVER['PHP_SELF']);
        exit();
    }
}

// Get book for editing if requested
$editingBook = null;
if (isset($_GET['edit'])) {
    $id = (int)$_GET['edit'];
    if ($id > 0) {
        $editingBook = $bookManager->getById($id);
    }
}

// Get all books for display
$books = $bookManager->getAll();

// Display flash messages
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? '';
unset($_SESSION['message'], $_SESSION['message_type']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Library Management System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4361ee;
            --primary-light: #4895ef;
            --secondary: #3f37c9;
            --success: #4cc9f0;
            --danger: #f72585;
            --warning: #f8961e;
            --info: #4895ef;
            --light: #f8f9fa;
            --dark: #212529;
            --white: #ffffff;
            --gray: #6c757d;
            --gray-light: #e9ecef;
            --border-radius: 0.375rem;
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-md: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            min-height: 100vh;
        }

        .container {
            margin-top:20px;
            margin-left:400px ;
            width: 900px;
        }

        header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: var(--white);
            padding: 2rem 0;
            text-align: center;
            margin-bottom: 2rem;
            box-shadow: var(--shadow);
            border-radius: var(--border-radius);
        }

        h1 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .card {
            background-color: var(--white);
            border-radius: var(--border-radius);
            box-shadow: var(--shadow);
            padding: 2rem;
            margin-bottom: 2rem;
            transition: var(--transition);
        }

        .card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
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

        input, select {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--gray-light);
            border-radius: var(--border-radius);
            font-size: 1rem;
            transition: var(--transition);
        }

        input:focus, select:focus {
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
            border-radius: var(--border-radius);
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: var(--transition);
            text-decoration: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--secondary);
            transform: translateY(-1px);
        }

        .btn-danger {
            background-color: var(--danger);
            color: var(--white);
        }

        .btn-danger:hover {
            background-color: #d1145a;
            transform: translateY(-1px);
        }

        .btn-success {
            background-color: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background-color: #3ab4d9;
            transform: translateY(-1px);
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
        }

        .alert {
            padding: 1rem;
            border-radius: var(--border-radius);
            margin-bottom: 1.5rem;
            font-weight: 500;
            animation: fadeIn 0.3s ease-out;
        }

        .alert-success {
            background-color: rgba(76, 201, 240, 0.1);
            color: var(--success);
            border-left: 4px solid var(--success);
        }

        .alert-error {
            background-color: rgba(247, 37, 133, 0.1);
            color: var(--danger);
            border-left: 4px solid var(--danger);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.5rem;
            background-color: var(--white);
            box-shadow: var(--shadow);
            border-radius: var(--border-radius);
            overflow: hidden;
            animation: fadeIn 0.5s ease-out;
        }

        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid var(--gray-light);
        }

        th {
            background-color: var(--primary);
            color: var(--white);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.875rem;
            letter-spacing: 0.05em;
        }

        tr:nth-child(even) {
            background-color: rgba(0, 0, 0, 0.02);
        }

        tr:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .badge {
            display: inline-block;
            padding: 0.35em 0.65em;
            font-size: 0.75em;
            font-weight: 700;
            line-height: 1;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 10rem;
        }

        .badge-success {
            background-color: var(--success);
            color: var(--white);
        }

        .badge-danger {
            background-color: var(--danger);
            color: var(--white);
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }
            
            header {
                padding: 1.5rem 0;
            }
            
            h1 {
                font-size: 2rem;
            }
            
            .card {
                padding: 1.5rem;
            }
            
            .btn-group {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Library Management System</h1>
            <p>Manage your book collection with ease</p>
        </header>
        
        <main>
            <?php if ($message): ?>
                <div class="alert alert-<?= $messageType === 'error' ? 'error' : 'success' ?>">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h2><?= $editingBook ? 'Edit Book' : 'Add New Book' ?></h2>
                <form method="POST">
                    <input type="hidden" name="id" value="<?= $editingBook ? $editingBook['id'] : '' ?>">
                    
                    <div class="form-group">
                        <label for="book_number">Book Number</label>
                        <input type="text" id="book_number" name="book_number" 
                               value="<?= htmlspecialchars($editingBook ? $editingBook['book_number'] : '') ?>" 
                               required placeholder="Enter book number">
                    </div>
                    
                    <div class="form-group">
                        <label for="book_name">Book Title</label>
                        <input type="text" id="book_name" name="book_name" 
                               value="<?= htmlspecialchars($editingBook ? $editingBook['book_name'] : '') ?>" 
                               required placeholder="Enter book title">
                    </div>
                    
                    <div class="form-group">
                        <label for="student_id">Borrower ID (if checked out)</label>
                        <input type="text" id="student_id" name="student_id" 
                               value="<?= htmlspecialchars($editingBook ? $editingBook['student_id'] : '') ?>" 
                               placeholder="Enter student ID">
                    </div>
                    
                    <div class="form-group">
                        <label for="available">Availability Status</label>
                        <select id="available" name="available" required>
                            <option value="yes" <?= $editingBook && $editingBook['available'] == 'yes' ? 'selected' : '' ?>>Available</option>
                            <option value="no" <?= $editingBook && $editingBook['available'] == 'no' ? 'selected' : '' ?>>Checked Out</option>
                        </select>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="submit" class="btn btn-primary">
                            <?= $editingBook ? 'Update Book' : 'Add Book' ?>
                        </button>
                        
                        <?php if ($editingBook): ?>
                            <a href="?" class="btn btn-danger">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
            
            <div class="card">
                <h2>Book Inventory</h2>
                
                <?php if (empty($books)): ?>
                    <div class="empty-state">
                        <i>📚</i>
                        <h3>No Books Found</h3>
                        <p>Add your first book to get started</p>
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Book #</th>
                                <th>Title</th>
                                <th>Borrower</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($books as $book): ?>
                                <tr>
                                    <td><?= htmlspecialchars($book['book_number']) ?></td>
                                    <td><?= htmlspecialchars($book['book_name']) ?></td>
                                    <td><?= $book['student_id'] ? htmlspecialchars($book['student_id']) : '—' ?></td>
                                    <td>
                                        <span class="badge badge-<?= $book['available'] == 'yes' ? 'success' : 'danger' ?>">
                                            <?= $book['available'] == 'yes' ? 'Available' : 'Checked Out' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="?edit=<?= $book['id'] ?>" class="btn btn-success">Edit</a>
                                            <a href="?delete=<?= $book['id'] ?>" class="btn btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this book?')">Delete</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Confirm before deleting
        document.querySelectorAll('.btn-danger').forEach(button => {
            button.addEventListener('click', (e) => {
                if (!confirm('Are you sure you want to delete this book?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>