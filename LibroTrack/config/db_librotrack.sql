-- ============================================
--  LibroTrack - Library Management System
--  Database: MySQL
--  ILDEG Company | Phase 1
-- ============================================

-- Create and select database
CREATE DATABASE IF NOT EXISTS db_librotrack;
USE db_librotrack;

-- ============================================
--  TABLE: tbl_users
--  Stores login credentials for both
--  admin (librarian) and student accounts.
-- ============================================
CREATE TABLE tbl_users (
    userID            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name              VARCHAR(100)    NOT NULL,
    profile_picture   VARCHAR(255)    NULL DEFAULT NULL,
    two_fa_secret     VARCHAR(255)    NULL DEFAULT NULL,
    two_fa_enabled    TINYINT(1)      NOT NULL DEFAULT 0,
    username          VARCHAR(50)     NOT NULL UNIQUE,
    password          VARCHAR(255)    NOT NULL,
    role              ENUM('admin', 'student') NOT NULL,
    dateAdded         TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,


    PRIMARY KEY (userID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
--  TABLE: tbl_student
--  Stores additional profile information
--  for student users.
-- ============================================
CREATE TABLE tbl_student (
    studentID       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    userID          INT UNSIGNED    NOT NULL,
    fname           VARCHAR(50)     NOT NULL,
    mname           VARCHAR(50)     NULL,
    lname           VARCHAR(50)     NOT NULL,
    nameExt         VARCHAR(10)     NULL,
    studentNumber   VARCHAR(20)     NOT NULL UNIQUE,
    course          VARCHAR(100)    NOT NULL,
    email           VARCHAR(100)    NOT NULL UNIQUE,

    PRIMARY KEY (studentID),
    CONSTRAINT fk_student_user
        FOREIGN KEY (userID)
        REFERENCES tbl_users (userID)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
--  TABLE: tbl_books
--  Stores all book records available
--  in the library catalog.
-- ============================================
CREATE TABLE tbl_books (
    bookID      INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    title       VARCHAR(200)    NOT NULL,
    author      VARCHAR(100)    NOT NULL,
    isbn        VARCHAR(20)     NULL UNIQUE,
    genre       VARCHAR(50)     NOT NULL,
    copies      INT UNSIGNED    NOT NULL DEFAULT 1,
    location    VARCHAR(100)    NULL,
    description TEXT            NULL,
    cover_image VARCHAR(255)    NULL,
    dateAdded   TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (bookID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
--  TABLE: tbl_transaction
--  Records all borrowing and returning
--  activities between students and books.
-- ============================================
CREATE TABLE tbl_transaction (
    transactionID   INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    studentID       INT UNSIGNED    NOT NULL,
    bookID          INT UNSIGNED    NOT NULL,
    borrowDate      DATE            NOT NULL,
    dueDate         DATE            NOT NULL,
    returnDate      DATE            NULL,
    status          ENUM('borrowed', 'returned', 'overdue') NOT NULL DEFAULT 'borrowed',
    dateAdded       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (transactionID),
    CONSTRAINT fk_transaction_student
        FOREIGN KEY (studentID)
        REFERENCES tbl_student (studentID)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_transaction_book
        FOREIGN KEY (bookID)
        REFERENCES tbl_books (bookID)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
--  TABLE: tbl_penalties
--  Tracks overdue penalties associated
--  with late book returns.
-- ============================================
CREATE TABLE tbl_penalties (
    penaltyID       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    transactionID   INT UNSIGNED    NOT NULL UNIQUE,
    daysOverdue     INT UNSIGNED    NOT NULL,
    amount          DECIMAL(8,2)    NOT NULL,
    paid            BOOLEAN         NOT NULL DEFAULT FALSE,
    dateAdded       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (penaltyID),
    CONSTRAINT fk_penalty_transaction
        FOREIGN KEY (transactionID)
        REFERENCES tbl_transaction (transactionID)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ============================================
--  SEED DATA: Default admin account
--  Password: admin123 (bcrypt hashed)
--  Change this password immediately!
-- ============================================
INSERT INTO tbl_users (name, username, password, role)
VALUES (
    'Administrator',
    'admin',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'admin'
);


-- ============================================
--  USEFUL VIEWS
-- ============================================

-- View: Book availability (calculates available copies dynamically)
CREATE VIEW view_book_availability AS
SELECT
    b.bookID,
    b.title,
    b.author,
    b.genre,
    b.copies,
    b.copies - COALESCE(
        (SELECT COUNT(*) FROM tbl_transaction t
         WHERE t.bookID = b.bookID AND t.status = 'borrowed'),
        0
    ) AS available,
    b.location
FROM tbl_books b;


-- View: Active borrows with student and book details
CREATE VIEW view_active_borrows AS
SELECT
    t.transactionID,
    CONCAT(s.fname, ' ', s.lname) AS studentName,
    s.studentNumber,
    s.course,
    bk.title AS bookTitle,
    bk.author,
    t.borrowDate,
    t.dueDate,
    t.status,
    CASE
        WHEN t.dueDate < CURDATE() AND t.status = 'borrowed'
        THEN DATEDIFF(CURDATE(), t.dueDate)
        ELSE 0
    END AS daysOverdue
FROM tbl_transaction t
JOIN tbl_student s  ON t.studentID = s.studentID
JOIN tbl_books bk   ON t.bookID    = bk.bookID
WHERE t.status IN ('borrowed', 'overdue');


-- View: Overdue transactions with penalty info
CREATE VIEW view_overdue AS
SELECT
    t.transactionID,
    CONCAT(s.fname, ' ', s.lname) AS studentName,
    s.studentNumber,
    bk.title AS bookTitle,
    t.dueDate,
    DATEDIFF(CURDATE(), t.dueDate) AS daysOverdue,
    DATEDIFF(CURDATE(), t.dueDate) * 5.00 AS penaltyAmount,
    COALESCE(p.paid, FALSE) AS paid
FROM tbl_transaction t
JOIN tbl_student s  ON t.studentID = s.studentID
JOIN tbl_books bk   ON t.bookID    = bk.bookID
LEFT JOIN tbl_penalties p ON t.transactionID = p.transactionID
WHERE t.status = 'overdue'
   OR (t.status = 'borrowed' AND t.dueDate < CURDATE());
