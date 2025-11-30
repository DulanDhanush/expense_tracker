// script.js - Full Expense Tracker JS with PHP integration

document.addEventListener("DOMContentLoaded", () => {
  initializeApp();
});

// ----------------------
// Initialization
// ----------------------
function initializeApp() {
  setCurrentDate();

  const today = new Date().toISOString().split("T")[0];
  document.getElementById("incomeDate").value = today;
  document.getElementById("expenseDate").value = today;

  addEventListeners();
  loadTransactions();
}

// ----------------------
// Current Date Display
// ----------------------
function setCurrentDate() {
  const now = new Date();
  const options = { day: "numeric", month: "long", year: "numeric" };
  document.getElementById("currentDate").textContent = now.toLocaleDateString(
    "en-US",
    options
  );
}

// ----------------------
// Event Listeners
// ----------------------
function addEventListeners() {
  // Tabs
  document.querySelectorAll(".period-tab").forEach((tab) => {
    tab.addEventListener("click", function () {
      document
        .querySelectorAll(".period-tab")
        .forEach((t) => t.classList.remove("active"));
      this.classList.add("active");
      updateExpenseBreakdown(this.textContent.toLowerCase());
    });
  });

  // Chart bars hover
  document.querySelectorAll(".chart-bar").forEach((bar) => {
    bar.addEventListener("mouseenter", function () {
      document
        .querySelectorAll(".chart-bar")
        .forEach((b) => b.classList.remove("active"));
      this.classList.add("active");
    });
  });

  // Sorting
  document.querySelectorAll(".transactions-table th").forEach((header) => {
    header.addEventListener("click", () => sortTable(header.cellIndex));
  });

  // Income / Expense forms
  document.getElementById("incomeForm").addEventListener("submit", (e) => {
    e.preventDefault();
    addIncome();
  });
  document.getElementById("expenseForm").addEventListener("submit", (e) => {
    e.preventDefault();
    addExpense();
  });

  // Export button
  document.querySelector(".export-btn").addEventListener("click", () => {
    showNotification("Export feature coming soon!", "info");
  });
}

// ----------------------
// Modal Functions
// ----------------------
function openIncomeModal() {
  document.getElementById("incomeModal").style.display = "block";
  document.body.style.overflow = "hidden";
}

function openExpenseModal() {
  document.getElementById("expenseModal").style.display = "block";
  document.body.style.overflow = "hidden";
}

function closeModal(modalId) {
  document.getElementById(modalId).style.display = "none";
  document.body.style.overflow = "auto";

  if (modalId === "incomeModal") document.getElementById("incomeForm").reset();
  if (modalId === "expenseModal")
    document.getElementById("expenseForm").reset();
}

// Close modal clicking outside
window.onclick = (event) => {
  const incomeModal = document.getElementById("incomeModal");
  const expenseModal = document.getElementById("expenseModal");
  if (event.target === incomeModal) closeModal("incomeModal");
  if (event.target === expenseModal) closeModal("expenseModal");
};

// ----------------------
// Load Transactions
// ----------------------
function loadTransactions() {
  showLoading(true);

  fetch("get_transactions.php")
    .then((res) => {
      if (!res.ok) {
        throw new Error("Network response was not ok: " + res.status);
      }
      return res.json();
    })
    .then((transactions) => {
      console.log("Loaded transactions:", transactions);
      updateDashboardFromDB(transactions);
      updateTransactionsTableFromDB(transactions);
      updateExpenseBreakdownFromDB(transactions);
    })
    .catch((error) => {
      console.error("Error loading transactions:", error);
      showNotification(
        "Failed to load transactions: " + error.message,
        "error"
      );
      updateTransactionsTableFromDB([]);
    })
    .finally(() => {
      showLoading(false);
    });
}

// ----------------------
// Add Income / Expense
// ----------------------
function addIncome() {
  const formData = new FormData(document.getElementById("incomeForm"));
  const amount = parseFloat(formData.get("amount"));

  // Validation
  if (amount <= 0) {
    showNotification("Amount must be greater than 0", "error");
    return;
  }

  if (!formData.get("category")) {
    showNotification("Please select a category", "error");
    return;
  }

  showLoading(true);

  fetch("add_income.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.text())
    .then((data) => {
      console.log("Add income response:", data);
      if (data.trim() === "success") {
        showNotification("Income added successfully!", "success");
        document.getElementById("incomeForm").reset();
        closeModal("incomeModal");
        loadTransactions(); // Reload to get updated data from database
      } else {
        throw new Error(data);
      }
    })
    .catch((error) => {
      console.error("Error adding income:", error);
      showNotification("Error adding income: " + error.message, "error");
    })
    .finally(() => {
      showLoading(false);
    });
}

function addExpense() {
  const formData = new FormData(document.getElementById("expenseForm"));
  const amount = parseFloat(formData.get("amount"));

  // Validation
  if (amount <= 0) {
    showNotification("Amount must be greater than 0", "error");
    return;
  }

  if (!formData.get("category")) {
    showNotification("Please select a category", "error");
    return;
  }

  showLoading(true);

  fetch("add_expense.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.text())
    .then((data) => {
      console.log("Add expense response:", data);
      if (data.trim() === "success") {
        showNotification("Expense added successfully!", "success");
        document.getElementById("expenseForm").reset();
        closeModal("expenseModal");
        loadTransactions(); // Reload to get updated data from database
      } else {
        throw new Error(data);
      }
    })
    .catch((error) => {
      console.error("Error adding expense:", error);
      showNotification("Error adding expense: " + error.message, "error");
    })
    .finally(() => {
      showLoading(false);
    });
}

// ----------------------
// Delete Transaction
// ----------------------
function deleteTransaction(id) {
  if (!confirm("Are you sure you want to delete this transaction?")) return;

  showLoading(true);

  const formData = new FormData();
  formData.append("id", id);

  fetch("delete_transaction.php", { method: "POST", body: formData })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        showNotification("Transaction deleted successfully!", "success");
        loadTransactions();
      } else {
        throw new Error(data.message || "Unknown error");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      showNotification("Error deleting transaction: " + error.message, "error");
    })
    .finally(() => {
      showLoading(false);
    });
}

// ----------------------
// Update Dashboard / Table
// ----------------------
function updateDashboardFromDB(transactions) {
  let income = 0,
    expense = 0;

  transactions.forEach((t) => {
    const amount = parseFloat(t.amount);
    if (t.type === "income") {
      income += amount;
    } else if (t.type === "expense") {
      expense += Math.abs(amount);
    }
  });

  const balance = income - expense;

  document.querySelector(
    ".income-amount"
  ).textContent = `LKR ${income.toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
  document.querySelector(
    ".expense-amount"
  ).textContent = `LKR ${expense.toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;
  document.querySelector(
    ".balance-amount"
  ).textContent = `LKR ${balance.toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;

  // Calculate changes (simplified - in real app, compare with previous period)
  const incomeChange =
    transactions.filter((t) => t.type === "income").length > 0 ? 12 : 0;
  const expenseChange =
    transactions.filter((t) => t.type === "expense").length > 0 ? 8 : 0;

  const changeElements = document.querySelectorAll(".change");
  if (changeElements[0]) {
    changeElements[0].innerHTML = `<i class="fas fa-arrow-up"></i> ${incomeChange}% vs Last month`;
  }
  if (changeElements[1]) {
    changeElements[1].innerHTML = `<i class="fas fa-arrow-up"></i> ${expenseChange}% vs Last month`;
  }
}

function updateTransactionsTableFromDB(transactions) {
  const tbody = document.getElementById("transactionsBody");
  tbody.innerHTML = "";

  const recentTransactions = transactions.slice(0, 10);
  if (recentTransactions.length === 0) {
    tbody.innerHTML = `<tr><td colspan="6" style="text-align:center;color:#6b7280;padding:2rem;">No transactions found. Add your first transaction!</td></tr>`;
    return;
  }

  recentTransactions.forEach((t) => {
    const row = document.createElement("tr");
    const formattedDate = new Date(t.date).toLocaleDateString("en-US", {
      month: "short",
      day: "numeric",
      year: "numeric",
    });

    const amount = parseFloat(t.amount);
    const isIncome = t.type === "income";
    const amountDisplay = isIncome
      ? `+LKR ${Math.abs(amount).toLocaleString("en-US", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })}`
      : `-LKR ${Math.abs(amount).toLocaleString("en-US", {
          minimumFractionDigits: 2,
          maximumFractionDigits: 2,
        })}`;

    // Ensure status exists
    const status = t.status || "completed";

    row.innerHTML = `
      <td>${formattedDate}</td>
      <td>${t.description || "No description"}</td>
      <td>${t.category}</td>
      <td style="color: ${
        isIncome ? "#10b981" : "#ef4444"
      }; font-weight:600;">${amountDisplay}</td>
      <td><span class="status-${status}">${
      status.charAt(0).toUpperCase() + status.slice(1)
    }</span></td>
      <td><button class="action-btn" onclick="deleteTransaction(${
        t.id
      })" title="Delete transaction"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(row);
  });
}

function updateExpenseBreakdownFromDB(transactions) {
  const expenses = transactions.filter((t) => t.type === "expense");
  const totalExpenses = expenses.reduce(
    (sum, t) => sum + Math.abs(parseFloat(t.amount)),
    0
  );

  // Update total expenses
  document.querySelector(
    ".total-expenses"
  ).textContent = `LKR ${totalExpenses.toLocaleString("en-US", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  })}`;

  // Calculate category breakdown
  const categories = {};
  expenses.forEach((t) => {
    const category = t.category;
    const amount = Math.abs(parseFloat(t.amount));
    categories[category] = (categories[category] || 0) + amount;
  });

  // Update category percentages
  const legendItems = document.querySelectorAll(".legend-items .legend-item");
  legendItems.forEach((item) => {
    const categoryElement = item.querySelector("span:not(.legend-amount)");
    const amountElement = item.querySelector(".legend-amount");
    if (categoryElement && amountElement) {
      const categoryName = categoryElement.textContent.toLowerCase();
      let categoryKey = "";

      // Map display names to database category values
      if (categoryName.includes("food")) categoryKey = "food";
      else if (categoryName.includes("entertainment"))
        categoryKey = "entertainment";
      else if (categoryName.includes("shopping")) categoryKey = "shopping";
      else if (categoryName.includes("investment")) categoryKey = "investment";
      else categoryKey = "other";

      const categoryAmount = categories[categoryKey] || 0;
      const percentage =
        totalExpenses > 0
          ? Math.round((categoryAmount / totalExpenses) * 100)
          : 0;
      amountElement.textContent = `${percentage}%`;
    }
  });

  // Update period stats
  const dailyAverage = totalExpenses / 30; // Rough average
  const weeklyTotal = totalExpenses / 4; // Rough weekly
  const monthlyTotal = totalExpenses;

  const periodAmounts = document.querySelectorAll(".period-amount");
  if (periodAmounts[0])
    periodAmounts[0].textContent = `LKR ${Math.round(
      dailyAverage
    ).toLocaleString()}`;
  if (periodAmounts[1])
    periodAmounts[1].textContent = `LKR ${Math.round(
      weeklyTotal
    ).toLocaleString()}`;
  if (periodAmounts[2])
    periodAmounts[2].textContent = `LKR ${Math.round(
      monthlyTotal
    ).toLocaleString()}`;
}

// ----------------------
// Utility Functions
// ----------------------
function sortTable(column) {
  showNotification("Sorting feature coming soon!", "info");
}

function updateExpenseBreakdown(period) {
  showNotification(`Showing ${period} expense breakdown`, "info");
}

function showLoading(show) {
  if (show) {
    document.body.classList.add("loading");
  } else {
    document.body.classList.remove("loading");
  }
}

// ----------------------
// Notifications
// ----------------------
function showNotification(message, type = "success") {
  // Remove any existing notifications
  document.querySelectorAll(".notification").forEach((n) => n.remove());

  const notification = document.createElement("div");
  notification.className = `notification notification-${type}`;
  notification.textContent = message;
  document.body.appendChild(notification);

  // Auto remove after 4 seconds
  setTimeout(() => {
    if (notification.parentNode) {
      notification.style.animation = "slideOutRight 0.3s ease";
      setTimeout(() => {
        if (notification.parentNode) {
          notification.parentNode.removeChild(notification);
        }
      }, 300);
    }
  }, 4000);
}
