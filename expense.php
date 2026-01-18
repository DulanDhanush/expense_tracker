<?php
// Start session and include database connection
session_start();
require_once 'db.php';

// Initialize variables
$successMessage = '';
$errorMessage = '';

// Handle expense form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_expense'])) {
    try {
        // Validate input
        $amount = floatval($_POST['amount'] ?? 0);
        $category = trim($_POST['category'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $date = $_POST['date'] ?? date('Y-m-d');
        
        // Basic validation
        if ($amount <= 0) {
            throw new Exception("Please enter a valid amount greater than 0.");
        }
        
        if (empty($category)) {
            throw new Exception("Please select a category for this expense.");
        }
        
        // Prepare and execute SQL
        $stmt = $conn->prepare("
            INSERT INTO transactions (type, amount, category, description, date, status) 
            VALUES ('expense', ?, ?, ?, ?, 'completed')
        ");
        
        $stmt->bind_param("dsss", $amount, $category, $description, $date);
        
        if ($stmt->execute()) {
            // Store success message in session for display
            $_SESSION['success_message'] = "💰 Expense recorded successfully! LKR " . number_format($amount, 2) . " has been deducted from your account.";
            
            // Redirect to prevent form resubmission
            header("Location: expense.php?success=true");
            exit;
        } else {
            throw new Exception("Sorry, we couldn't save your expense. Please try again.");
        }
        
    } catch (Exception $e) {
        $errorMessage = $e->getMessage();
    }
}

// Check for success message from session
if (isset($_SESSION['success_message'])) {
    $successMessage = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Check for success in URL 
if (isset($_GET['success']) && $_GET['success'] === 'true') {
    $successMessage = $successMessage ?: "Expense added successfully!";
}

// Get total expenses for display
$totalExpenses = 0;
$expenseResult = $conn->query("SELECT SUM(amount) as total FROM transactions WHERE type = 'expense'");
if ($expenseResult && $row = $expenseResult->fetch_assoc()) {
    $totalExpenses = $row['total'] ?? 0;
}



// Get recent expense transactions
$recentExpensesQuery = $conn->prepare("
    SELECT * FROM transactions 
    WHERE type = 'expense' 
    ORDER BY date DESC, id DESC 
    LIMIT 10
");
$recentExpensesQuery->execute();
$recentExpenses = $recentExpensesQuery->get_result()->fetch_all(MYSQLI_ASSOC);

// Get expense by category for breakdown
$categoryExpensesQuery = $conn->query("
    SELECT 
        category,
        SUM(amount) as total,
        COUNT(*) as count
    FROM transactions 
    WHERE type = 'expense' 
    GROUP BY category
    ORDER BY total DESC
");
$categoryExpenses = $categoryExpensesQuery->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracking | My Money Manager</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="expense.css">
    
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
            <h1 class="welcome-title">Expense Tracker</h1>
            <p class="welcome-subtitle">Track and manage all your spending</p>
        </div>
        
        <!-- Main Navigation -->
        <nav class="main-navigation">
            <a href="dashboard.php" class="nav-link">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="income.php" class="nav-link">
                <i class="fas fa-money-bill-wave"></i> Income
            </a>
            <a href="expense.php" class="nav-link active">
                <i class="fas fa-receipt"></i> Expenses
            </a>
            <a href="profile.php" class="nav-link">
                <i class="fas fa-user"></i> Profile
            </a>
        </nav>
        
        <!-- Header Tools -->
        <div class="header-tools">
        
            <div class="current-date">
                <i class="fas fa-calendar"></i>
                <span id="currentDate"><?php echo date('j F Y'); ?></span>
            </div>
        </div>
    </div>
</div>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Expense Overview -->
        <div class="expense-stats">
            <h2>Total Expenses</h2>
            <div class="total-expenses">LKR <?php echo number_format($totalExpenses, 2); ?></div>
            <p class="expense-message">
                <?php if ($totalExpenses > 0): ?>
                    You've spent LKR <?php echo number_format($totalExpenses, 2); ?> in total.
                <?php else: ?>
                    Start tracking your expenses to better manage your money.
                <?php endif; ?>
            </p>
        </div>

        
        <!-- Quick Action -->
        <div class="quick-action-bar">
            <button class="btn btn-primary btn-large btn-expense" onclick="openExpenseModal()">
                <i class="fas fa-plus-circle"></i> Add New Expense
            </button>
        </div>

        
        <!-- Recent Expense Transactions -->
        <div class="card transactions-section">
            <div class="card-header">
                <h3 class="card-title">Recent Expense Transactions</h3>
                
            </div>
            
            <?php if (empty($recentExpenses)): ?>
                <div style="text-align: center; padding: 3rem; color: #6b7280;">
                    <i class="fas fa-receipt" style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;"></i>
                    <h4>No Expenses Recorded Yet</h4>
                    <p>Click "Add New Expense" to record your first expense transaction.</p>
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
                        <?php foreach ($recentExpenses as $expense): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($expense['date'])); ?></td>
                            <td><?php echo htmlspecialchars($expense['description'] ?: 'Expense'); ?></td>
                            <td>
                                <span style="
                                    padding: 0.25rem 0.75rem;
                                    border-radius: 12px;
                                    font-size: 0.75rem;
                                    font-weight: 500;
                                    background: <?php 
                                        $categoryColors = [
                                            'food' => 'rgba(239, 68, 68, 0.1)',
                                            'entertainment' => 'rgba(245, 158, 11, 0.1)',
                                            'shopping' => 'rgba(139, 92, 246, 0.1)',
                                            'transport' => 'rgba(59, 130, 246, 0.1)',
                                            'utilities' => 'rgba(16, 185, 129, 0.1)',
                                            'other' => 'rgba(107, 114, 128, 0.1)'
                                        ];
                                        echo $categoryColors[$expense['category']] ?? 'rgba(107, 114, 128, 0.1)';
                                    ?>;
                                    color: <?php 
                                        $categoryTextColors = [
                                            'food' => '#ef4444',
                                            'entertainment' => '#f59e0b',
                                            'shopping' => '#8b5cf6',
                                            'transport' => '#3b82f6',
                                            'utilities' => '#10b981',
                                            'other' => '#6b7280'
                                        ];
                                        echo $categoryTextColors[$expense['category']] ?? '#6b7280';
                                    ?>;
                                ">
                                    <i class="fas fa-<?php 
                                        $categoryIcons = [
                                            'food' => 'utensils',
                                            'entertainment' => 'film',
                                            'shopping' => 'shopping-bag',
                                            'transport' => 'car',
                                            'utilities' => 'home',
                                            'other' => 'tag'
                                        ];
                                        echo $categoryIcons[$expense['category']] ?? 'tag';
                                    ?>"></i>
                                    <?php echo ucfirst($expense['category']); ?>
                                </span>
                            </td>
                            <td style="color: #ef4444; font-weight: 600;">
                                -LKR <?php echo number_format($expense['amount'], 2); ?>
                            </td>
                            <td>
                                <span class="status-completed">
                                    <?php echo ucfirst($expense['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="?delete=<?php echo $expense['id']; ?>" 
                                   class="action-btn" 
                                   onclick="return confirm('Are you sure you want to delete this expense?')"
                                   title="Delete expense">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
            <?php endif; ?>
        </div>


        
    <!-- Add Expense Modal -->
    <div id="expenseModal" class="modal expense-modal" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fas fa-receipt"></i> Add New Expense</h2>
                <button class="close-btn" onclick="closeExpenseModal()">&times;</button>
            </div>
            <form method="POST" action="" class="income-form" onsubmit="return validateExpenseForm()">
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
                               placeholder="How much did you spend?"
                               id="expenseAmount"
                               oninput="formatAmount(this)">
                        <small class="form-help">Enter the amount you spent</small>
                    </div>
                    
                    <!-- Category Selection -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-tags"></i> Category *
                        </label>
                        <select name="category" required class="form-select" id="expenseCategory">
                            <option value="">What did you spend on?</option>
                            <option value="food">🍕 Food & Dining</option>
                            <option value="entertainment">🎬 Entertainment</option>
                            <option value="shopping">🛍️ Shopping</option>
                            <option value="transport">🚗 Transportation</option>
                            <option value="utilities">💡 Utilities</option>
                            <option value="health">🏥 Health & Medical</option>
                            <option value="education">📚 Education</option>
                            <option value="other">📝 Other</option>
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
                               placeholder="Optional: Add notes about this expense"
                               maxlength="255">
                        <small class="form-help">e.g., "Grocery shopping" or "Movie tickets"</small>
                    </div>
                    
                    <!-- Date -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar"></i> Date *
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
                            onclick="closeExpenseModal()">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="submit" 
                            name="add_expense" 
                            class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Expense
                    </button>
                </div>
            </form>
        </div>
    </div>


    <script>
        // Modal functions
        function openExpenseModal() {
            document.getElementById('expenseModal').style.display = 'flex';
            document.getElementById('expenseAmount').focus();
        }
        
        function closeExpenseModal() {
            document.getElementById('expenseModal').style.display = 'none';
            document.querySelector('#expenseModal .income-form').reset();
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                closeExpenseModal();
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
        function validateExpenseForm() {
            const amount = document.getElementById('expenseAmount').value;
            const category = document.getElementById('expenseCategory').value;
            
            if (!amount || parseFloat(amount) <= 0) {
                alert('Please enter a valid amount greater than 0.');
                return false;
            }
            
            if (!category) {
                alert('Please select what you spent on.');
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