<?php
include 'db.php';



// Delete Transaction

if (isset($_GET['delete_id'])) {

    $delete_id = (int) $_GET['delete_id'];

    if ($delete_id > 0) {
        $stmt = $conn->prepare("DELETE FROM transactions WHERE id = ?");
        $stmt->bind_param("i", $delete_id);

        if ($stmt->execute()) {
            header("Location: dashboard.php?success=transaction_deleted");
            exit;
        }
    }
}


// Fetch Data

$transactions = getTransactions($conn);
$stats        = calculateStats($transactions);
$chartData    = getChartData($conn);
$maxChartValue = getMaxChartValue($chartData);


// Success Messages

$success_message = '';

if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'income_added':
            $success_message = 'Income added successfully!';
            break;

        case 'expense_added':
            $success_message = 'Expense added successfully!';
            break;

        case 'transaction_deleted':
            $success_message = 'Transaction deleted successfully!';
            break;
    }
}


// Sorting 

$sort_by = $_GET['sort'] ?? 'date';

$sortedTransactions = $transactions;

usort($sortedTransactions, function($a, $b) use ($sort_by) {
    switch ($sort_by) {

        case 'date':
            return strtotime($b['date']) - strtotime($a['date']);

        case 'amount':
            return abs($b['amount']) - abs($a['amount']);

    }
});


// Show All or show less

$show_all = isset($_GET['view']) && $_GET['view'] === 'all';

$transactions_to_show = $show_all
    ? $sortedTransactions
    : array_slice($sortedTransactions, 0, 10);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Expense Tracker - PHP Version</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <!-- Success Notification -->
    <?php if ($success_message): ?>
    <div class="notification notification-success">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
    <?php endif; ?>

    <!-- Header -->
        <div class="dashboard-header">
        <div class="header-content">
        <div class="welcome-section">
            <h1 class="welcome-title">Financial Dashboard</h1>
            <p class="welcome-subtitle">Overview of your income, expenses, and balance</p>
        </div>
        
        <!-- Main Navigation -->
        <nav class="main-navigation">
            <a href="dashboard.php" class="nav-link active">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="income.php" class="nav-link">
                <i class="fas fa-money-bill-wave"></i> Income
            </a>
            <a href="expense.php" class="nav-link">
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
                <span id="currentDate"><?php echo date('F j, Y'); ?></span>
            </div>
            
        </div>
    </div>
</div>
<!-- Main Content -->
    <div class="main-content">

        <!-- Dashboard Cards -->
        <div class="dashboard-grid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Income</h3>
                </div>
                <div class="amount income-amount">LKR <?php echo number_format($stats['income'], 2); ?></div>
                <div class="change positive">
                    <i class="fas fa-arrow-up"></i>12% vs Last month
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Monthly Expenses</h3>
                </div>
                <div class="amount expense-amount">LKR <?php echo number_format($stats['expense'], 2); ?></div>
                <div class="change positive">
                    <i class="fas fa-arrow-up"></i>8% vs Last month
                </div>
            </div>

            <div class="card balance-card">
                <div class="card-header">
                    <h3 class="card-title">Current Balance</h3>
                
                </div>
                <div class="amount balance-amount">LKR <?php echo number_format($stats['balance'], 2); ?></div>
                <div class="change positive">
                    <i class="fas fa-wallet"></i>Available to spend
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="chart-section">
            <div class="card chart-card">
                <div class="card-header">
                    <h3 class="card-title">Financial Overview</h3>
                </div>
                
                <div class="chart-container">
                    <div class="chart-bars">
                        <?php 
                        // Display chart bars for each month
                        if (empty($transactions)) {
                            echo '<div style="text-align: center; padding: 2rem; color: #6b7280;">No data available for chart. Add some transactions!</div>';
                        } else {

                             $minHeight = 5; 
                            
                            foreach ($chartData as $data): 
                                $incomeAmount = $data['income'];
                                $expenseAmount = $data['expense'];
                                
                            
                                $incomeHeight = $maxChartValue > 0 ? max(($incomeAmount / $maxChartValue) * 100, $minHeight) : $minHeight;
                                $expenseHeight = $maxChartValue > 0 ? max(($expenseAmount / $maxChartValue) * 100, $minHeight) : $minHeight;
                                
                                $month = $data['month'];
                                $total = $incomeAmount + $expenseAmount;
                        ?>
                        <div class="chart-bar" style="height: 200px;">
                            <div class="chart-tooltip">
                                <div class="tooltip-title"><?php echo $month; ?> 2025</div>
                                <div class="tooltip-income">Income: LKR <?php echo number_format($incomeAmount, 2); ?></div>
                                <div class="tooltip-expense">Expenses: LKR <?php echo number_format($expenseAmount, 2); ?></div>
                                <div class="tooltip-total">Total: LKR <?php echo number_format($total, 2); ?></div>
                            </div>
                            <div class="bar-income" style="height: <?php echo $incomeHeight; ?>%"></div>
                            <div class="bar-expense" style="height: <?php echo $expenseHeight; ?>%"></div>
                        </div>
                        <?php endforeach; 
                        } ?>
                    </div>
                </div>
                
                <div class="chart-labels">
                    <?php if (!empty($chartData)): ?>
                        <?php foreach ($chartData as $data): ?>
                            <span><?php echo $data['month']; ?></span>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <span style="width: 100%; text-align: center;">Add data to see chart</span>
                    <?php endif; ?>
                </div>
                
                
            </div>

            <!-- Expense Breakdown -->
            <div class="card expenses-card">
                <div class="card-header">
                    <h3 class="card-title">Expense Breakdown</h3>
                    
                </div>
                <div class="expenses-subtitle">Spending by category</div>
                <div class="expenses-breakdown">
                    <div class="total-expenses">LKR <?php echo number_format($stats['expense'], 2); ?></div>
                    
                    <div class="period-stats">
                        <div class="period-item">
                            <span class="period-label">Daily Average</span>
                            <span class="period-amount">LKR <?php echo number_format($stats['expense'] / 30, 0); ?></span>
                        </div>
                        <div class="period-item">
                            <span class="period-label">Weekly Total</span>
                            <span class="period-amount">LKR <?php echo number_format($stats['expense'] / 4, 0); ?></span>
                        </div>
                        <div class="period-item">
                            <span class="period-label">Monthly Total</span>
                            <span class="period-amount">LKR <?php echo number_format($stats['expense'], 0); ?></span>
                        </div>
                    </div>
                    <div class="color-legend">
                        <div class="legend-title">Spending Categories</div>
                        <div class="legend-items">
                            <?php
                            $categories = [];
                            foreach ($transactions as $transaction) {
                                if ($transaction['type'] === 'expense') {
                                    $category = $transaction['category'];
                                    $categories[$category] = ($categories[$category] ?? 0) + $transaction['amount'];
                                }
                            }
                            
                            $category_colors = [
                                'food' => 'dot-food',
                                'entertainment' => 'dot-entertainment',
                                'shopping' => 'dot-shopping',
                                'investment' => 'dot-investment',
                                'transport' => 'dot-other',
                                'utilities' => 'dot-other',
                                'other' => 'dot-other'
                            ];
                            
                            $category_names = [
                                'food' => 'Food & Health',
                                'entertainment' => 'Entertainment',
                                'shopping' => 'Shopping',
                                'investment' => 'Investment',
                                'transport' => 'Transport',
                                'utilities' => 'Utilities',
                                'other' => 'Other'
                            ];
                            
                            $total_expenses = $stats['expense'];
                            
                            if (empty($categories)) {
                                echo '<div style="text-align: center; color: #6b7280; padding: 1rem;">No expense data available</div>';
                            } else {
                                foreach ($categories as $category => $amount) {
                                    $percentage = $total_expenses > 0 ? round(($amount / $total_expenses) * 100) : 0;
                                    $color_class = $category_colors[$category] ?? 'dot-other';
                                    $display_name = $category_names[$category] ?? ucfirst($category);
                                    echo "
                                    <div class='legend-item'>
                                        <div class='legend-dot $color_class'></div>
                                        <span>$display_name</span>
                                        <span class='legend-amount'>{$percentage}%</span>
                                    </div>";
                                }
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="card transactions-section">
            <div class="card-header">
                <h3 class="card-title">Recent Transactions</h3>
                <div class="filters">
                    <form method="GET" action="" style="display: inline;">
                        <button type="submit" name="sort" value="date" class="filter-btn">
                            <i class="fas fa-sort"></i> Sort by Date
                        </button>
                        <button type="submit" name="sort" value="amount" class="filter-btn">
                            <i class="fas fa-sort"></i> Sort by Amount
                        </button>
                    </form>
                </div>
            </div>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th>
                            <a href="?sort=date" style="color: inherit; text-decoration: none;">
                                Date 
                            </a>
                        </th>
                        <th>Description</th>
                        <th>
                            <a href="?sort=category" style="color: inherit; text-decoration: none;">
                                Category 
                            </a>
                        </th>
                        <th>
                            <a href="?sort=amount" style="color: inherit; text-decoration: none;">
                                Amount 
                            </a>
                        </th>
                        <th>
                            <a href="?sort=status" style="color: inherit; text-decoration: none;">
                                Status 
                            </a>
                        </th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Handle sorting
                    $sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'date';
                    $sort_order = 'DESC'; 
                    
                    $sortedTransactions = $transactions;
                    
                    // Apply sorting
                    usort($sortedTransactions, function($a, $b) use ($sort_by) {
                        switch ($sort_by) {
                            case 'date':
                                return strtotime($b['date']) - strtotime($a['date']);
                            case 'amount':
                                return abs($b['amount']) - abs($a['amount']);
                            
                        }
                    });
                    
                    $show_all = isset($_GET['view']) && $_GET['view'] === 'all';
                    $transactions_to_show = $show_all ? $sortedTransactions : array_slice($sortedTransactions, 0, 10);
                    
                    if (empty($transactions_to_show)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;color:#6b7280;padding:2rem;">
                            No transactions found. Add your first transaction!
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($transactions_to_show as $transaction): ?>
                        <tr>
                            <td><?php echo date('M j, Y', strtotime($transaction['date'])); ?></td>
                            <td><?php echo htmlspecialchars($transaction['description'] ?: 'No description'); ?></td>
                            <td><?php echo htmlspecialchars($transaction['category']); ?></td>
                            <td style="color: <?php echo $transaction['type'] === 'income' ? '#10b981' : '#ef4444'; ?>; font-weight:600;">
                                <?php echo ($transaction['type'] === 'income' ? '+' : '-') . 'LKR ' . number_format(abs($transaction['amount']), 2); ?>
                            </td>
                            <td>
                                <span class="status-<?php echo $transaction['status']; ?>">
                                    <?php echo ucfirst($transaction['status']); ?>
                                </span>
                            </td>
                            <td>
                                <!-- FIXED: Changed from index.php to dashboard.php -->
                                <a href="dashboard.php?delete_id=<?php echo $transaction['id']; ?>" 
                                   class="action-btn" 
                                   onclick="return confirm('Are you sure you want to delete this transaction?')"
                                   title="Delete transaction">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="table-footer">
                <?php if (!$show_all && count($transactions) > 10): ?>
                    <a href="?view=all<?php echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : ''; ?>" class="view-all-btn">
                        View All Transactions (<?php echo count($transactions); ?> total)
                    </a>
                <?php elseif ($show_all): ?>
                    <a href="?view=recent<?php echo isset($_GET['sort']) ? '&sort=' . $_GET['sort'] : ''; ?>" class="view-all-btn">
                        Show Recent Only
                    </a>
                <?php else: ?>
                    <button class="view-all-btn" disabled>
                        View All Transactions (<?php echo count($transactions); ?> total)
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <script>
        // Close modal when clicking outside
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Auto-hide notifications
        setTimeout(function() {
            var notifications = document.querySelectorAll('.notification');
            notifications.forEach(function(notification) {
                notification.style.display = 'none';
            });
        }, 4000);
    </script>
</body>
</html>
<?php $conn->close(); ?>