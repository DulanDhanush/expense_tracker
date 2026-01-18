# Expense Tracker Web Application

A simple **Expense Tracker** web application built with **PHP** and **CSS**, designed to help users manage and monitor their personal income and expenses through an intuitive dashboard.

---

## 🛠 Features

- **User Dashboard**: View total income, expenses, and balance at a glance.
- **Income Management**: Add, view, and track all income sources.
- **Expense Management**: Add, view, and categorize expenses.
- **Profile Management**: Update personal information.
- **Responsive Design**: Clean and user-friendly interface with CSS styling.
- **Database Integration**: MySQL database to store user data securely.

---

## 💻 Technologies Used

- **Frontend**: HTML, CSS
- **Backend**: PHP
- **Database**: MySQL
- **Version Control**: Git & GitHub

---

## 📂 Project Structure

- expense_tracker/
│
├── db.php # Database connection
├── dashboard.php # Main dashboard page
├── expense.php # Expense management page
├── income.php # Income management page
├── profile.php # User profile page
├── README.md # Project documentation
└── css/
├── dashboard.css
├── expense.css
├── income.css
└── profile.css


---

## ⚡ Installation & Setup

1. **Clone the repository**  
```bash
git clone https://github.com/DulanDhanush/expense_tracker.git

```

2. **Set up a local server (e.g., XAMPP, WAMP, or MAMP).**

3. **Create a MySQL database:**
```bash

CREATE DATABASE expense_tracker;
```

4. **Import the database schema (if available in db.sql).**

5. **Update db.php with your database credentials:**
```bash
<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "expense_tracker";

$conn = mysqli_connect($host, $user, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

6. **Run the application via your local server:**
Visit http://localhost/expense_tracker/dashboard.php

**📷 Demo**



 **📝 License**

This project is licensed under the Apache-2.0 License.

 **👤 Author**

Dulan Dhanush Kandeepan

GitHub: https://github.com/DulanDhanush

LinkedIn: https://www.linkedin.com/in/dulan-dhanush-b76a44300

**🚀 Future Enhancements**

User authentication with login/signup

Advanced analytics and charts for expenses

Mobile-friendly responsive layout

Export reports to PDF or Excel
