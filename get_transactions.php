<?php
include 'db.php';

$user_id = 1;

// Use prepared statement for security
$stmt = $conn->prepare("SELECT * FROM transactions WHERE user_id = ? ORDER BY date DESC, id DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$transactions = [];
while($row = $result->fetch_assoc()) {
    // Ensure all required fields are present
    $row['status'] = $row['status'] ?? 'completed';
    $transactions[] = $row;
}

header('Content-Type: application/json');
echo json_encode($transactions);

$stmt->close();
$conn->close();
?>