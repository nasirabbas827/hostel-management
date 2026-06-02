# Hostel‑management‑final

A simple PHP web application for managing hostels, users, bookings, and feedback. It provides an admin dashboard for CRUD operations on hostels and users, as well as a public interface for room booking and contact handling.

---

## Overview

The **Hostel‑management‑final** project is a lightweight, database‑driven system that allows hostel owners or administrators to:

- Add, edit, and delete hostel listings (including images).  
- Manage user accounts and view booking history.  
- Receive and respond to feedback and contact requests.  
- Enable visitors to browse hostels and book rooms online.

The codebase is organized into a public front‑end and an `admin/` back‑end, both powered by PHP and a MySQL database.

---

## Features

| ✅ | Feature |
|---|---|
| ✅ | **Admin authentication** – secure login/logout flow (`admin_login.php`, `logout.php`). |
| ✅ | **Hostel management** – add, edit, delete hostels with image uploads (`add_hostel.php`, `edit_hostel.php`, `view_hostels.php`). |
| ✅ | **User management** – create, edit, deactivate users (`add_user.php`, `edit_user.php`, `view_users.php`). |
| ✅ | **Booking overview** – list and filter all bookings (`admin_bookings.php`). |
| ✅ | **Feedback handling** – view and manage guest feedback (`admin_feedbacks.php`). |
| ✅ | **Public booking page** – simple room reservation form (`book_room.php`). |
| ✅ | **Contact manager** – process contact form submissions (`contact_manager.php`). |
| ✅ | **Responsive UI** – shared CSS (`css/style.css`, `admin/style.css`, `company.css`) and minimal JavaScript (`company.js`). |
| ✅ | **SQL schema** – ready‑to‑import database file (`Database/hostel_db.sql`). |

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | PHP 7.4+ |
| Database | MySQL / MariaDB |
| Front‑end | HTML5, CSS3, JavaScript (vanilla) |
| Styling | Custom CSS (`style.css`, `company.css`) |
| Server | Apache / Nginx (any LAMP/LEMP stack) |

---

## Installation

### 1. Prerequisites

- PHP 7.4 or newer with `mysqli` extension enabled.  
- MySQL server (or MariaDB).  
- A web server capable of serving PHP (Apache, Nginx, etc.).  
- Git (optional, for cloning).

### 2. Clone the repository

```bash
git clone https://github.com/yourusername/Hostel-management-final.git
cd Hostel-management-final
```

### 3. Set up the database

1. Create a new database (e.g., `hostel_db`).  
2. Import the schema:

```bash
mysql -u root -p hostel_db < Database/hostel_db.sql
```

3. Update the database credentials in both `config.php` (root) and `admin/config.php`:

```php
// config.php (public)
define('DB_HOST', 'localhost');
define('DB_USER', 'YOUR_DB_USER');
define('DB_PASS', 'YOUR_DB_PASSWORD');
define('DB_NAME', 'hostel_db');
```

```php
// admin/config.php (admin)
define('DB_HOST', 'localhost');
define('DB_USER', 'YOUR_DB_USER');
define('DB_PASS', 'YOUR_DB_PASSWORD');
define('DB_NAME', 'hostel_db');
```

> **NOTE:** Replace `YOUR_DB_USER` and `YOUR_DB_PASSWORD` with your own credentials. Do **not** commit real passwords to the repository.

### 4. Configure the web server

- **Apache**: Place the project folder inside `htdocs` (or