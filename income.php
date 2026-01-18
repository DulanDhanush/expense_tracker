<?php
session_start();
require_once 'db.php';

// Initialize

$successMessage = '';
$errorMessage   = '';


// Add New Income

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_income'])) {
    try {
        $amount      = floatval($_POST['amount'] ?? 0);
        $category    = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date        = $_POST['date'] ?? date('Y-m-d');

        // Validation
        if ($amount <= 0) {
            throw new Exception("Please enter a valid amount greater than 0.");
        }

        if ($category === '') {
            throw new Exception("Please select a category for this income.");
        }

        // Insert Income
        $stmt = $conn->prepare("
            INSERT INTO transactions (type, amount, category, description, date, status)
            VALUES ('income', ?, ?, ?, ?, 'completed')
        ");
        $stmt->bind_param("dsss", $amount, $category, $description, $date);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "🎉 Income recorded successfully! LKR " . number_format($amount, 2) . " added.";
            header("Location: income.php?success=true");
            exit;
        } else {
            throw new Exception("Sorry, we couldn't save your income. Please try again.");
        }

    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}


// Session Success Message

if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_GET['success']) && $_GET['success'] === 'true') {
    $successMessage = $successMessage ?: "Income added successfully!";
}


// Get Total Income

$totalIncome = 0;
$res = $conn->query("SELECT SUM(amount) AS total FROM transactions WHERE type='income'");
if ($res && ($row = $res->fetch_assoc())) {
    $totalIncome = $row['total'] ?? 0;
}



// Recent Income (Last 10)

$stmt = $conn->prepare("
    SELECT * FROM transactions
    WHERE type='income'
    ORDER BY date DESC, id DESC
    LIMIT 10
");
$stmt->execute();
$recentIncome = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Income Breakdown by Category

$res = $conn->query("
    SELECT category, SUM(amount) AS total, COUNT(*) AS count
    FROM transactions
    WHERE type='income'
    GROUP BY category
    ORDER BY total DESC
");
$categoryIncome = $res->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Income Tracking | My Money Manager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="income.css">
</head>
<body>
    <!-- Success Notification -->
    <?php if ($successMessage): ?>
    <div class="notification notification-success">
        <i class="fas fa-check-circle"></i>
        <span><?php echo htmlspecialchars($successMessage); ?></span>
        <button class="notification-close" onclick="closeNotification()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>
    
    <!-- Error Notification -->
    <?php if ($errorMessage): ?>
    <div class="notification notification-error">
        <i class="fas fa-exclamation-circle"></i>
        <span><?php echo htmlspecialchars($errorMessage); ?></span>
        <button class="notification-close" onclick="closeNotification()">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="dashboard-header">
        <div class="header-content">
            <div class="welcome-section">
                <h1 class="welcome-title">Income Tracker</h1>
                <p class="welcome-subtitle">Manage and track all your incoming money</p>
            </div>
            
            <!-- Navigation -->
            <nav class="main-navigation">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
                <a href="income.php" class="nav-link active">
                    <i class="fas fa-money-bill-wave"></i> Income
                </a>
                <a href="expense.php" class="nav-link">
                    <i class="fas fa-receipt"></i> Expenses
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profile
                </a>
            </nav>
            
            <div class="header-tools">
                <div class="current-date">
                    <i class="fas fa-calendar-alt"></i>
                    <span><?php echo date('F j, Y'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="dashboard-content">
        <!-- Income Overview -->
        <div class="income-stats">
            <h2>Total Income</h2>
            <div class="total-income">LKR <?php echo number_format($totalIncome, 2); ?></div>
            <p class="income-message">
                <?php if ($totalIncome > 0): ?>
                    Great job! You've earned LKR <?php echo number_format($totalIncome, 2); ?> so far.
                <?php else: ?>
                    Start tracking your income! Record your first income below.
                <?php endif; ?>
            </p>
        </div>

        

        <!-- Quick Action -->
        <div class="quick-action-bar">
            <button class="btn btn-primary btn-large" onclick="openIncomeModal()">
                <i class="fas fa-plus-circle"></i> Add New Income
            </button>
        </div>

        
        <!-- Recent Income Transactions -->
        <div class="card transactions-section">
            <div class="card-header">
                <h3 class="card-title">Recent Income Transactions</h3>
                
            </div>
            
            <?php if (empty($recentIncome)): ?>
                <div style="text-align: center; padding: 3rem; color: #6b7280;">
                    <i class="fas fa-money-bill-wave" style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;"></i>
                    <h4>No Income Recorded Yet</h4>
                    <p>Click "Add New Income" to record your first income transaction.</p>
                </div>
            <?php else: ?>
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Description</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentIncome as $income): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($income['date'])); ?></td>
                            <td><?php echo htmlspecialchars($income['description'] ?: 'Income'); ?></td>
                            <td>
                                <span style="
                                    padding: 0.25rem 0.75rem;
                                    border-radius: 12px;
                                    font-size: 0.75rem;
                                    font-weight: 500;
                                    background: <?php 
                                        $categoryColors = [
                                            'salary' => 'rgba(16, 185, 129, 0.1)',
                                            'freelance' => 'rgba(59, 130, 246, 0.1)',
                                            'business' => 'rgba(139, 92, 246, 0.1)',
                                            'investment' => 'rgba(245, 158, 11, 0.1)',
                                            'gift' => 'rgba(239, 68, 68, 0.1)',
                                            'rental' => 'rgba(14, 165, 233, 0.1)',
                                            'other' => 'rgba(107, 114, 128, 0.1)'
                                        ];
                                        echo $categoryColors[$income['category']] ?? 'rgba(107, 114, 128, 0.1)';
                                    ?>;
                                    color: <?php 
                                        $categoryTextColors = [
                                            'salary' => '#10b981',
                                            'freelance' => '#3b82f6',
                                            'business' => '#8b5cf6',
                                            'investment' => '#f59e0b',
                                            'gift' => '#ef4444',
                                            'rental' => '#0ea5e9',
                                            'other' => '#6b7280'
                                        ];
                                        echo $categoryTextColors[$income['category']] ?? '#6b7280';
                                    ?>;
                                ">
                                    <i class="fas fa-<?php 
                                        $categoryIcons = [
                                            'salary' => 'briefcase',
                                            'freelance' => 'laptop-code',
                                            'business' => 'store',
                                            'investment' => 'chart-line',
                                            'gift' => 'gift',
                                            'rental' => 'home',
                                            'other' => 'tag'
                                        ];
                                        echo $categoryIcons[$income['category']] ?? 'tag';
                                    ?>"></i>
                                    <?php echo ucfirst($income['category']); ?>
                                </span>
                            </td>
                            <td style="color: #10b981; font-weight: 600;">
                                +LKR <?php echo number_format($income['amount'], 2); ?>
                            </td>
                            <td>
                                <span class="status-completed">
                                    <?php echo ucfirst($income['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?delete=<?php echo $income['id']; ?>" 
                                   class="action-btn" 
                                   onclick="return confirm('Are you sure you want to delete this income?')"
                                   title="Delete income">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
            <?php endif; ?>
        </div>

        <!-- Income Breakdown by Category -->
        <?php if (!empty($categoryIncome)): ?>
        <div class="income-breakdown">
            <h3>Income Breakdown by Source</h3>
            <div class="breakdown-grid">
                <?php foreach ($categoryIncome as $category): ?>
                <div class="breakdown-item">
                    <div class="breakdown-category">
                        <span class="breakdown-dot dot-<?php echo $category['category']; ?>"></span>
                        <span><?php echo ucfirst($category['category']); ?></span>
                    </div>
                    <div class="breakdown-amount">
                        LKR <?php echo number_format($category['total'], 2); ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Add Income Modal -->
    <div id="incomeModal" class="modal income-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fas fa-money-bill-wave"></i> Add New Income</h2>
                <button class="close-btn" onclick="closeIncomeModal()">&times;</button>
            </div>
            <form method="POST" action="" class="income-form" onsubmit="return validateIncomeForm()">
                <div class="modal-body">
                    <!-- Amount Field -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-money-bill"></i> Amount (LKR) *
                        </label>
                        <input type="number" 
                               step="0.01" 
                               min="0.01" 
                               name="amount" 
                               required 
                               class="form-input"
                               placeholder="How much did you receive?"
                               id="incomeAmount"
                               oninput="formatAmount(this)">
                        <small class="form-help">Enter the amount you received</small>
                    </div>
                    
                    <!-- Category Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-tags"></i> Income Source *
                        </label>
                        <select name="category" required class="form-select" id="incomeCategory">
                            <option value="">Where did this income come from?</option>
                            <option value="salary">💼 Salary (Regular Job)</option>
                            <option value="freelance">👨‍💻 Freelance Work</option>
                            <option value="business">🏪 Business Income</option>
                            <option value="investment">📈 Investment Returns</option>
                            <option value="gift">🎁 Gift or Bonus</option>
                            <option value="rental">🏠 Rental Income</option>
                            <option value="other">📝 Other Source</option>
                        </select>
                    </div>
                    
                    <!-- Description -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-sticky-note"></i> Description
                        </label>
                        <input type="text" 
                               name="description" 
                               class="form-input"
                               placeholder="Optional: Add notes about this income"
                               maxlength="255">
                        <small class="form-help">e.g., "March salary" or "Web design project"</small>
                    </div>
                    
                    <!-- Date -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar"></i> Date Received *
                        </label>
                        <input type="date" 
                               name="date" 
                               value="<?php echo date('Y-m-d'); ?>" 
                               required 
                               class="form-input"
                               max="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-secondary"
                            onclick="closeIncomeModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" 
                            name="add_income" 
                            class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Income
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Modal functions
        function openIncomeModal() {
            document.getElementById('incomeModal').style.display = 'flex';
            document.getElementById('incomeAmount').focus();
        }
        
        function closeIncomeModal() {
            document.getElementById('incomeModal').style.display = 'none';
            document.querySelector('#incomeModal .income-form').reset();
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeIncomeModal();
            }
        }
        
        // Close notifications
        function closeNotification() {
            const notifications = document.querySelectorAll('.notification');
            notifications.forEach(notification => {
                notification.style.display = 'none';
            });
        }
        
        // Auto-hide success notifications after 5 seconds
        setTimeout(() => {
            closeNotification();
        }, 5000);
        
        // Format amount as user types
        function formatAmount(input) {
            // Remove non-numeric characters except decimal point
            let value = input.value.replace(/[^\d.]/g, '');
            
            // Ensure only one decimal point
            const parts = value.split('.');
            if (parts.length > 2) {
                value = parts[0] + '.' + parts.slice(1).join('');
            }
            
            // Limit to 2 decimal places
            if (parts.length === 2 && parts[1].length > 2) {
                value = parts[0] + '.' + parts[1].substring(0, 2);
            }
            
            input.value = value;
        }
        
        // Form validation
        function validateIncomeForm() {
            const amount = document.getElementById('incomeAmount').value;
            const category = document.getElementById('incomeCategory').value;
            
            if (!amount || parseFloat(amount) <= 0) {
                alert('Please enter a valid amount greater than 0.');
                return false;
            }
            
            if (!category) {
                alert('Please select where this income came from.');
                return false;
            }
            
            return true;
        }
        
    </script>
</body>
</html>

<?php 
// Close database connection
if ($conn) {
    $conn->close();
}
?>