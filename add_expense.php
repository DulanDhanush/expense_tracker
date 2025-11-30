<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and validate input
    $user_id = 1; // Default user for testing
    
    // Validate required fields
    if (empty($_POST['date']) || empty($_POST['category']) || empty($_POST['amount'])) {
        echo "error: Missing required fields";
        exit;
    }
    
    $date = $conn->real_escape_string($_POST['date']);
    $description = $conn->real_escape_string($_POST['description'] ?? '');
    $category = $conn->real_escape_string($_POST['category']);
    $amount = floatval($_POST['amount']);
    $type = 'expense';
    
    // Validate amount
    if ($amount <= 0) {
        echo "error: Amount must be positive";
        exit;
    }
    
    // Prepare and execute SQL
    $stmt = $conn->prepare("INSERT INTO transactions (user_id, date, description, category, amount, type) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issdds", $user_id, $date, $description, $category, $amount, $type);
    
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
} else {
    echo "error: Invalid request method";
}
?>