# LibroTrack

A web-based Library Management System developed using PHP (MVC Architecture) and MySQL.  
This system allows librarians to manage books, borrowers, and transactions, while students can browse the catalog and track their borrowed books.

---

## Table of Contents
- [Installation](#installation)
- [Usage](#usage)
- [Features](#features)
- [Folder Structure](#folder-structure)
- [Developers](#developers)
- [License](#license)

---

## Installation

### Requirements
- XAMPP (PHP 8.2 or higher)
- MySQL
- Web Browser
- Google Authenticator or Authy (for 2FA)

### Steps to Run

1. Download or clone the repository.
2. Copy the `LibroTrack` folder into the `htdocs` directory of XAMPP.
3. Start **Apache** and **MySQL** in XAMPP Control Panel.
4. Open **phpMyAdmin**.
5. Create a database named:

```plaintext
db_librotrack
```

6. Import the provided `.sql` file located at:

```plaintext
LibroTrack/config/db_librotrack.sql
```

7. Open your browser and go to:

```plaintext
http://localhost/LibroTrack/public/
```

---

## Usage

### Admin (Librarian) Account

```
Username : admin
Password : password
```

> **Note:** Two-Factor Authentication (2FA) is required for the admin account.  
> On first login, scan the QR code using Google Authenticator or Authy.  
> Enter the 6-digit code to complete setup.

### Student Account

Students can register through the Sign Up page, or the librarian can add them directly through Borrower Management.

```
Default credentials (if added by librarian):
Username : [student number]
Password : [student number]
```

### How to Use

**Admin:**
1. Log in using the admin account and complete 2FA setup.
2. Navigate using the top menu.
3. Manage books under **Books**.
4. Manage student borrowers under **Borrowers**.
5. Record borrows and returns under **Transactions**.
6. Monitor overdue books and penalties under **Overdue**.
7. View library statistics under **Reports**.
8. Log out after use.

**Student:**
1. Log in using student credentials.
2. Browse available books under **Browse Books**.
3. View currently borrowed books under **My Borrowed**.
4. View borrowing history under **My History**.
5. Update personal information and profile picture under **Profile**.

---

## Features

### Admin Side
- Secure Login with Two-Factor Authentication (2FA)
- Dashboard with real-time library statistics
- **Book Management** — Add, View, Edit, Delete books with cover image upload
- **Borrower Management** — Add, View, Edit, Delete student borrowers
- **Transaction Management** — Record borrows, process returns with automatic penalty calculation
- Transaction History with search and date range filter
- **Overdue & Penalty Tracking** — Monitor overdue books, track penalties, mark as paid
- **Reports** — Most borrowed books, top borrowers, borrows by genre, print report

### Student Side
- Student Dashboard with active borrow summary and overdue alerts
- Browse Books with grid/list view, search, and genre/availability filter
- My Borrowed Books with due date countdown and penalty display
- My Borrow History with search and status filter
- Profile Management — Edit personal info, change password, upload profile picture

### Security
- Role-based access control (Admin / Student)
- Session guards on all pages
- Direct file access prevention
- Passwords hashed with bcrypt
- Two-Factor Authentication for librarian login

---

## Folder Structure

```plaintext
LibroTrack/
├── app/
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── BookController.php
│   │   ├── BorrowerController.php
│   │   ├── DashboardController.php
│   │   ├── OverdueController.php
│   │   ├── ProfileController.php
│   │   ├── ReportController.php
│   │   ├── StudentController.php
│   │   └── TransactionController.php
│   ├── models/
│   │   ├── Book.php
│   │   ├── Dashboard.php
│   │   ├── Penalty.php
│   │   ├── Profile.php
│   │   ├── Report.php
│   │   ├── Student.php
│   │   ├── StudentDashboard.php
│   │   ├── Transaction.php
│   │   └── User.php
│   └── views/
│       ├── admin/
│       ├── client/
│       ├── login.php
│       ├── signup.php
│       ├── setup_2fa.php
│       └── verify_2fa.php
├── config/
│   ├── database.php
│   └── db_librotrack.sql
├── public/
│   ├── assets/
│   │   ├── css/
│   │   ├── img/
│   │   └── js/
│   └── index.php
├── vendor/
├── composer.json
└── README.md
```

---

## Developers
- Mykel Rey B. De Los Reyes
- Lorenzen S. Ilon
- Joeric Israel A. Gonzales
- 2b_announced

---

## License

This project is developed for educational purposes only.
