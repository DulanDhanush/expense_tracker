<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'expense_tracker';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL DEFAULT 'User',
    age INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert default user if not exists
$result = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $conn->query("INSERT INTO users (username, age) VALUES ('User', NULL)");
}

$conn->query("CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('income', 'expense') NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    category VARCHAR(50) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS income_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS expense_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Insert default categories if tables are empty
$result = $conn->query("SELECT COUNT(*) as count FROM income_categories");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $default_income_cats = ['Salary', 'Freelance', 'Business', 'Investment', 'Other'];
    foreach ($default_income_cats as $cat) {
        $conn->query("INSERT IGNORE INTO income_categories (name) VALUES ('$cat')");
    }
}

$result = $conn->query("SELECT COUNT(*) as count FROM expense_categories");
$row = $result->fetch_assoc();
if ($row['count'] == 0) {
    $default_expense_cats = ['Food', 'Entertainment', 'Shopping', 'Investment', 'Transport', 'Utilities', 'Other'];
    foreach ($default_expense_cats as $cat) {
        $conn->query("INSERT IGNORE INTO expense_categories (name) VALUES ('$cat')");
    }
}

// Helper function to add transactions
function addTransaction($conn, $type) {
    $amount = $_POST['amount'];
    $category = $_POST['category'];
    $description = isset($_POST['description']) ? $_POST['description'] : '';
    $date = $_POST['date'];
    
    $stmt = $conn->prepare("INSERT INTO transactions (type, amount, category, description, date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sdsss", $type, $amount, $category, $description, $date);
    
    if ($stmt->execute()) {
        $success_type = $type . '_added';
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=" . $success_type);
        exit();
    }
}

// Helper function to delete transactions
function deleteTransaction($conn) {
    if (isset($_GET['delete_id'])) {
        $id = $_GET['delete_id'];
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF'] . "?success=transaction_deleted");
            exit();
        }
    }
}

// Helper function to get all transactions
function getTransactions($conn) {
    $result = $conn->query("SELECT * FROM transactions ORDER BY date DESC");
    $transactions = [];
    
    while ($row = $result->fetch_assoc()) {
        $transactions[] = $row;
    }
    
    return $transactions;
}

// Helper function to calculate stats
function calculateStats($transactions) {
    $income = 0;
    $expense = 0;
    
    foreach ($transactions as $transaction) {
        if ($transaction['type'] === 'income') {
            $income += $transaction['amount'];
        } else {
            $expense += $transaction['amount'];
        }
    }
    
    return [
        'income' => $income,
        'expense' => $expense,
        'balance' => $income - $expense
    ];
}

// Helper function to get chart data
function getChartData($conn) {
    $chartData = [];
    $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    
    foreach ($months as $index => $month) {
        $monthNum = $index + 1;
        
        // Get income for this month
        $incomeResult = $conn->query("SELECT SUM(amount) as total FROM transactions 
                                       WHERE type = 'income' 
                                       AND MONTH(date) = $monthNum 
                                       AND YEAR(date) = YEAR(CURDATE())");
        $incomeRow = $incomeResult->fetch_assoc();
        $income = $incomeRow['total'] ?? 0;
        
        // Get expense for this month
        $expenseResult = $conn->query("SELECT SUM(amount) as total FROM transactions 
                                        WHERE type = 'expense' 
                                        AND MONTH(date) = $monthNum 
                                        AND YEAR(date) = YEAR(CURDATE())");
        $expenseRow = $expenseResult->fetch_assoc();
        $expense = $expenseRow['total'] ?? 0;
        
        // Only add months that have data
        if ($income > 0 || $expense > 0) {
            $chartData[] = [
                'month' => $month,
                'income' => floatval($income),
                'expense' => floatval($expense)
            ];
        }
    }
    
    return $chartData;
}

// Helper function to get max chart value
function getMaxChartValue($chartData) {
    $max = 0;
    foreach ($chartData as $data) {
        $max = max($max, $data['income'], $data['expense']);
    }
    return $max;
}
?>