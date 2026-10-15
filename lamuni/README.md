# Lamuni — Online Hausa Loan Management System

A working prototype of a three-tier PHP/MySQL loan management system built as a final-year project. Supports English and Hausa language toggle, borrower loan applications, admin review/approval, repayment tracking, notifications, and a full audit log.

## Tech Stack

| Layer | Technology |
|---|---|
| Frontend | HTML5, CSS3, Bootstrap 5, Vanilla JS |
| Backend | PHP 8 (procedural + OOP, no framework) |
| Database | MySQL (InnoDB, utf8mb4) |
| Environment | XAMPP (Apache + MySQL) |

---

## Setup Instructions

### 1. Copy project into XAMPP

Copy the entire `lamuni/` folder into your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\lamuni\
```

### 2. Start XAMPP

Open the XAMPP Control Panel and start **Apache** and **MySQL**.

### 3. Create the database

Open phpMyAdmin at `http://localhost/phpmyadmin` and:

1. Click **New** → name the database `lamuni` → click **Create**.
2. Select the `lamuni` database.
3. Click **Import** → choose `database/schema.sql` → click **Go**.
4. Click **Import** again → choose `database/seed.sql` → click **Go**.

**Or via command line:**
```bash
mysql -u root -p -e "CREATE DATABASE lamuni CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -u root -p lamuni < database/schema.sql
mysql -u root -p lamuni < database/seed.sql
```

### 4. Configure database credentials

Copy `.env.example` to `.env`:
```bash
copy .env.example .env
```

The default XAMPP configuration uses:
```
DB_HOST=localhost
DB_NAME=lamuni
DB_USER=root
DB_PASS=
```

If you have set a MySQL root password in XAMPP, update `DB_PASS` accordingly. Alternatively, edit `src/Database.php` directly (look for the fallback constants).

### 5. Create demo accounts

Visit the one-time setup page to insert demo user accounts with properly hashed passwords:

```
http://localhost/lamuni/public/setup.php
```

You should see a confirmation page. **Delete `public/setup.php` after running it** (or it will refuse to run again once accounts exist).

### 6. Open the application

```
http://localhost/lamuni/public/
```

---

## Demo Accounts

| Role | Email | Password | Phone | BVN |
|---|---|---|---|---|
| Borrower | demo@lamuni.local | demo1234 | 07012345678 | ••••••3445 (masked) |
| Admin | admin@lamuni.local | admin123 | 08000000000 | — |

The borrower account has a pre-seeded loan (Active, partially repaid) and a pending application so the dashboard is not empty on first run.

---

## Project Structure

```
lamuni/
├── public/                  ← Apache web root (point vhost here)
│   ├── index.php            Landing page
│   ├── register.php         Borrower registration
│   ├── login.php            Borrower login
│   ├── logout.php           Session destroy + redirect
│   ├── dashboard.php        Borrower dashboard
│   ├── apply.php            Loan application form
│   ├── setup.php            One-time demo account seeder (DELETE AFTER USE)
│   ├── admin/
│   │   ├── login.php        Admin sign-in
│   │   ├── dashboard.php    Stats, pending queue, recent activity
│   │   ├── review.php       Approve / reject an application
│   │   ├── repayments.php   Record a repayment
│   │   ├── reports.php      All loans, filterable by status
│   │   └── audit.php        Chronological audit log
│   └── assets/
│       ├── css/style.css    Custom design system (Bootstrap override)
│       └── js/app.js        Client-side validation + language toggle
├── src/
│   ├── Database.php         PDO singleton (.env + fallback)
│   ├── Auth.php             Register, login, logout, route guards
│   ├── Loan.php             Apply, list, decide, calculate schedule
│   ├── Repayment.php        Record payment, update balance
│   ├── Notification.php     Create, list, mark read
│   └── AuditLog.php         Log actions, fetch for admin
├── database/
│   ├── schema.sql           DDL — 6 tables, InnoDB, utf8mb4, FK constraints
│   └── seed.sql             Sample loans, repayments, notifications, audit entries
├── .env.example             Environment config template
├── .gitignore               Excludes .env, vendor/, logs
└── README.md                This file
```

---

## Features

### Borrower
- **Registration** — unique email, 11-digit phone, 11-digit BVN (demo/mock), hashed password
- **Login / Logout** — session-based, role-checked
- **Dashboard** — active loan summary, repayment history, notifications (auto-marked read)
- **Loan Application** — amount (min ₦1,000), duration (3–60 months), purpose
- **Language toggle** — English ↔ Hausa (persisted in localStorage)

### Admin
- **Dashboard** — totals (applications, pending, approved, rejected, completed, outstanding balance, total disbursed) + recent audit activity
- **Review Applications** — select pending loan, Approve or Reject with a note
- **Record Repayment** — select active loan, enter amount, balance updates automatically; loan marked Completed when balance hits zero
- **Reports** — all loans filterable by status, with links to review/repayment actions
- **Audit Log** — chronological record of all key actions

### Security
- `password_hash()` / `password_verify()` (bcrypt) — no plaintext passwords stored
- PDO prepared statements throughout — no raw string-concatenated SQL
- Server-side role checks on every protected route (`requireAdmin()`, `requireBorrower()`)
- Session-bound CSRF tokens on login, registration, loan application, loan decision, and repayment forms
- `session_regenerate_id(true)` on login
- User-supplied content is escaped when rendered; HTML is stripped from administrator decision notes
- Repayment updates are transactional and cannot exceed the current outstanding balance
- `.htaccess` blocks web requests to `.env`, `src/`, and `database/` when using the XAMPP project-directory setup

---

## Language Support

The Hausa/English toggle is implemented via `data-en` / `data-ha` HTML attributes and a small vanilla JS function in `app.js`. The chosen language is stored in `localStorage` and a cookie, then applied on every page load. Technical terms (field labels, error messages, status values) retain their English forms where a Hausa equivalent is unclear, per report §2.2.

---

## Out of Scope (per report §1.4 / §10)

- Real SMS/email delivery
- Real BVN/NIBSS verification
- Payment gateway integration
- Interest calculation (monthly due = amount ÷ duration, no interest)
- Multi-language support beyond English/Hausa

---

## Notes

- The BVN field is a **demo/mock field only** — it is stored and displayed masked (`••••••1234`) but is **not** verified against NIBSS, any bank, or any real BVN service.
- Phone number and BVN validation (11 digits) is applied both client-side (JS `pattern` check) and server-side (PHP `preg_match`).
