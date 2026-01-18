<?php
include 'db.php';

// Handle profile updates
if (isset($_POST['update_profile'])) {
    $username = $_POST['username'];
    $age = $_POST['age'];
    
    $stmt = $conn->prepare("UPDATE users SET username = ?, age = ? WHERE id = 1");
    $stmt->bind_param("si", $username, $age);
    
    if ($stmt->execute()) {
        header("Location: profile.php?success=profile_updated");
        exit();
    }
}

// Handle category additions
if (isset($_POST['add_income_category'])) {
    $category_name = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO income_categories (name) VALUES (?)");
    $stmt->bind_param("s", $category_name);
    
    if ($stmt->execute()) {
        header("Location: profile.php?success=category_added");
        exit();
    }
}

if (isset($_POST['add_expense_category'])) {
    $category_name = $_POST['category_name'];
    $stmt = $conn->prepare("INSERT INTO expense_categories (name) VALUES (?)");
    $stmt->bind_param("s", $category_name);
    
    if ($stmt->execute()) {
        header("Location: profile.php?success=category_added");
        exit();
    }
}

// Handle category deletions
if (isset($_GET['delete_income_cat'])) {
    $cat_id = $_GET['delete_income_cat'];
    $stmt = $conn->prepare("DELETE FROM income_categories WHERE id = ?");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    header("Location: profile.php?success=category_deleted");
    exit();
}

if (isset($_GET['delete_expense_cat'])) {
    $cat_id = $_GET['delete_expense_cat'];
    $stmt = $conn->prepare("DELETE FROM expense_categories WHERE id = ?");
    $stmt->bind_param("i", $cat_id);
    $stmt->execute();
    header("Location: profile.php?success=category_deleted");
    exit();
}

// Get user info
$result = $conn->query("SELECT * FROM users WHERE id = 1");
$user = $result->fetch_assoc();

// Get categories
$income_categories = $conn->query("SELECT * FROM income_categories ORDER BY name");
$expense_categories = $conn->query("SELECT * FROM expense_categories ORDER BY name");

// Success message
$success_message = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'profile_updated':
            $success_message = 'Profile updated successfully!';
            break;
        case 'category_added':
            $success_message = 'Category added successfully!';
            break;
        case 'category_deleted':
            $success_message = 'Category deleted successfully!';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Personal Expense Tracker</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="profile.css">
</head>
<body>
    <?php if ($success_message): ?>
    <div class="notification notification-success">
        <?php echo htmlspecialchars($success_message); ?>
    </div>
    <?php endif; ?>

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
            <a href="expense.php" class="nav-link ">
                <i class="fas fa-receipt"></i> Expenses
            </a>
            <a href="profile.php" class="nav-link active">
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

    <div class="main-content">
        <div class="dashboard-grid">
            <!-- Profile Information Card -->
            <div class="card" style="grid-column: span 2;">
                <div class="card-header">
                    <h3 class="card-title">Profile Information</h3>
                </div>
                <form method="POST" action="profile.php" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="form-input" placeholder="Enter your name">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Age</label>
                        <input type="number" name="age" value="<?php echo $user['age']; ?>" min="1" max="150" class="form-input" placeholder="Enter your age">
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Profile
                    </button>
                </form>
            </div>
        </div>

        <!-- Categories Section -->
        <div class="chart-section" style="margin-top: 2rem;">
            <!-- Income Categories -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Income Categories</h3>
                    <button class="btn btn-primary" onclick="document.getElementById('addIncomeCategoryModal').style.display='block'">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
                <div style="padding: 1.5rem;">
                    <?php if ($income_categories->num_rows > 0): ?>
                        <div class="category-list">
                            <?php while ($cat = $income_categories->fetch_assoc()): ?>
                                <div class="category-item">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <a href="profile.php?delete_income_cat=<?php echo $cat['id']; ?>" 
                                       class="action-btn" 
                                       onclick="return confirm('Delete this category?')"
                                       title="Delete category">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #6b7280; text-align: center;">No income categories yet. Add your first one!</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Expense Categories -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Expense Categories</h3>
                    <button class="btn btn-primary" onclick="document.getElementById('addExpenseCategoryModal').style.display='block'">
                        <i class="fas fa-plus"></i> Add Category
                    </button>
                </div>
                <div style="padding: 1.5rem;">
                    <?php if ($expense_categories->num_rows > 0): ?>
                        <div class="category-list">
                            <?php while ($cat = $expense_categories->fetch_assoc()): ?>
                                <div class="category-item">
                                    <span><?php echo htmlspecialchars($cat['name']); ?></span>
                                    <a href="profile.php?delete_expense_cat=<?php echo $cat['id']; ?>" 
                                       class="action-btn" 
                                       onclick="return confirm('Delete this category?')"
                                       title="Delete category">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #6b7280; text-align: center;">No expense categories yet. Add your first one!</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Income Category Modal -->
    <div id="addIncomeCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add Income Category</h2>
                <button class="close-btn" onclick="document.getElementById('addIncomeCategoryModal').style.display='none'">&times;</button>
            </div>
            <form method="POST" action="profile.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name" required class="form-input" placeholder="e.g., Freelance, Bonus">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addIncomeCategoryModal').style.display='none'">Cancel</button>
                    <button type="submit" name="add_income_category" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Add Expense Category Modal -->
    <div id="addExpenseCategoryModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Add Expense Category</h2>
                <button class="close-btn" onclick="document.getElementById('addExpenseCategoryModal').style.display='none'">&times;</button>
            </div>
            <form method="POST" action="profile.php">
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Category Name</label>
                        <input type="text" name="category_name" required class="form-input" placeholder="e.g., Groceries, Rent">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="document.getElementById('addExpenseCategoryModal').style.display='none'">Cancel</button>
                    <button type="submit" name="add_expense_category" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>


    <script>
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }
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