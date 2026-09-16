# 🎓 Student Management System (SMS)

> A modern, enterprise-ready, monolithic full-stack web application and REST API built from scratch using **Vanilla PHP 8.2+**, **Object-Oriented Programming (OOP)**, **MVC Architecture**, **MySQL 8+ with PDO**, and **Bootstrap 5**.

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net)
[![Database](https://img.shields.io/badge/Database-MySQL%208.0%2B-4479A1?style=flat-square&logo=mysql&logoColor=white)](https://www.mysql.com)
[![Architecture](https://img.shields.io/badge/Architecture-MVC%20%2B%20Repository-blue?style=flat-square)](#architecture--request-lifecycle)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

---

## 📌 Table of Contents
- [Overview](#-overview)
- [Key Features & Role-Based Access Control](#-key-features--role-based-access-control)
- [System Architecture](#-system-architecture)
- [Database Design & Schema](#-database-design--schema)
- [Tech Stack](#-tech-stack)
- [Installation & Quickstart](#-installation--quickstart)
- [Default Demo Accounts](#-default-demo-accounts)
- [REST API Endpoints](#-rest-api-endpoints)
- [Security Implementations](#-security-implementations)
- [Folder Structure](#-folder-structure)

---

## 🌟 Overview

The **Student Management System** is designed for higher educational institutions to streamline student registration, academic department management, faculty workloads, course offerings, grade computations, and daily attendance roll-calls.

Built without external heavy frameworks, this application demonstrates foundational software engineering principles including:
- **Zero-Magic HTTP Pipeline**: Custom Front Controller, Request/Response wrappers, and Regex Router.
- **Strict Separation of Concerns**: Controllers delegate to Services, and Services delegate to Repositories.
- **Enterprise PDO Security**: 100% prepared statements with native typing, lazy connections, and ACID transaction isolation.
- **Defensive Web Security**: Strict session cookie handling, CSRF token validation, XSS escaping, and RBAC guards.

---

## 👥 Key Features & Role-Based Access Control

| Feature / Module | Administrator | Registrar | Instructor | Student |
| :--- | :---: | :---: | :---: | :---: |
| **System Overview & Security Audit Logs** | ✅ Full | ❌ | ❌ | ❌ |
| **Academic Department Management** | ✅ Full | 👁️ View | 👁️ View | 👁️ View |
| **Faculty & Instructor Profiles** | ✅ Full | 👁️ View | 👁️ View | ❌ |
| **Student Directory & Intake (CRUD)** | ✅ Full | ✅ Full | 👁️ View | ❌ |
| **Course Catalog & Capacity Config** | ✅ Full | ✅ Full | 👁️ View | 👁️ View |
| **Course Enrollment Lifecycle** | ✅ Full | ✅ Full | 👁️ Roster | 👁️ Self |
| **Grading Scale & Letter Evaluation** | ✅ Full | ❌ | ✅ Assigned | 👁️ Transcript |
| **Daily Attendance Roll-Call** | ✅ Full | ❌ | ✅ Assigned | 👁️ Self |
| **Academic Transcripts & GPA** | ✅ Full | ✅ Full | ✅ Roster | 👁️ Personal |

---

## 🏗️ System Architecture

The application implements a **Layered MVC with Service and Repository Architecture**:

```
[ HTTP Request (Browser / Postman / Fetch API) ]
                       │
                       ▼
         [ public/index.php ] (Front Controller)
                       │
                       ▼
              [ Core\Router ]
                       │
                       ▼
         [ Core\Middleware Pipeline ]
         ├── AuthMiddleware (Session Verification)
         ├── RoleMiddleware (RBAC Guard)
         └── CsrfMiddleware (CSRF Validation)
                       │
                       ▼
             [ Controllers Layer ]
      (Handles input, validation via Core\Validator)
                       │
                       ▼
              [ Services Layer ]
      (Business rules, transactions, grade formulas)
                       │
                       ▼
            [ Repositories Layer ]
      (Data Access, Abstracted SQL via PDO Prepared Statements)
                       │
                       ▼
          [ Core\Database (PDO Singleton) ]
                       │
                       ▼
              [ MySQL 8+ Database ]
```

---

## 🗄️ Database Design & Schema

The database consists of **9 normalized relational tables** in Third Normal Form (3NF):

1. `users` — Authentication credentials, roles, and account statuses.
2. `departments` — Academic departments and divisions.
3. `instructors` — Faculty profiles and department assignments.
4. `students` — Comprehensive student records, levels, and enrollment history.
5. `courses` — Course catalog, term schedules, and seat capacity limits.
6. `enrollments` — Student course registrations with composite unique constraints.
7. `grades` — Component scores (assignment, midterm, final) and automated letter calculation.
8. `attendance` — Roll-call tracking with date-uniqueness protection.
9. `audit_logs` — Mutation tracking and security event monitoring.

---

## 💻 Tech Stack

* **Backend:** PHP 8.2+ (OOP, Strict Typing, PDO, Namespaces)
* **Architecture:** MVC + Service Layer + Repository Pattern
* **Database:** MySQL 8.0+ (InnoDB, UTF8mb4)
* **Frontend:** Bootstrap 5, Bootstrap Icons, Modern JavaScript (ES6+)
* **Autoloading:** PSR-4 Standard with standalone native fallback

---

## 🚀 Installation & Quickstart

### Prerequisites
- PHP 8.2 or higher
- MySQL Server 8.0+ (via XAMPP, WAMP, Laragon, or standalone service)
- Git

### 1. Clone the Repository
```bash
git clone https://github.com/YOUR_USERNAME/student-management-system.git
cd student-management-system
```

### 2. Configure Environment Variables
Copy `.env.example` to `.env` and configure your database credentials:
```bash
cp .env.example .env
```

Edit `.env`:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=student_management
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Run Database Migrations & Seeders
Execute the automated migration CLI runner:
```bash
php database/migrate.php
```

### 4. Start the Local Server
```bash
php -S localhost:8000 -t public
```

### 5. Access the Web Application
Open **[http://localhost:8000](http://localhost:8000)** in your browser.

---

## 🔑 Default Demo Accounts

All seed accounts share the default password: **`Admin@123456`**

| Role | Username | Email |
| :--- | :--- | :--- |
| **Administrator** | `admin` | `admin@sms.edu` |
| **Registrar** | `registrar` | `registrar@sms.edu` |
| **Instructor** | `dr.alan` | `alan.turing@sms.edu` |
| **Instructor** | `dr.ada` | `ada.lovelace@sms.edu` |
| **Student** | `john.doe` | `john.doe@student.sms.edu` |
| **Student** | `jane.smith` | `jane.smith@student.sms.edu` |

---

## 📡 REST API Endpoints

The API is accessible under the `/api` prefix with JSON payloads:

### Authentication
* `POST /api/auth/login` — Authenticate and start session
* `GET  /api/auth/me` — Retrieve active user profile
* `POST /api/auth/logout` — Terminate session

### Students
* `GET    /api/students` — Paginated student list with search filters
* `GET    /api/students/{id}` — Student profile with transcript & GPA
* `POST   /api/students` — Register new student *(Admin, Registrar)*
* `PUT    /api/students/{id}` — Update student profile *(Admin, Registrar)*
* `DELETE /api/students/{id}` — Archive student record *(Admin)*

### Courses & Grades
* `GET    /api/courses` — Course offerings with enrollment stats
* `POST   /api/courses` — Create new course *(Admin, Registrar)*
* `POST   /api/grades` — Upsert grade scores with auto-evaluation *(Admin, Instructor)*

---

## 🛡️ Security Implementations

* **SQL Injection Defense**: 100% Prepared Statements via PDO.
* **Session Fixation Defense**: `session_regenerate_id(true)` executed on login.
* **CSRF Mitigation**: Cryptographically secure tokens validated on all state-modifying requests.
* **XSS Defense**: Strict HTML output sanitization (`htmlspecialchars` with `ENT_QUOTES | ENT_SUBSTITUTE`).
* **Session Cookie Security**: `HttpOnly`, `SameSite=Strict`, and configurable `Secure` flags.
* **IDOR & RBAC Protection**: Strict server-side role validation in middleware pipeline.

---

## 📂 Folder Structure

```
student-management-system/
├── app/
│   ├── Controllers/         # HTTP Controllers & API Controllers
│   ├── Core/                # Router, Request, Response, Database, Session, Validator
│   ├── Helpers/             # auth.php, response.php, sanitize.php
│   ├── Middleware/          # AuthMiddleware, RoleMiddleware, CsrfMiddleware
│   ├── Models/              # Entity state representations
│   ├── Repositories/        # Abstract BaseRepository & Concrete Data Access Objects
│   └── Services/            # Business logic & authentication services
├── config/                  # Configuration files (app, database, session, grading)
├── database/                # Schema DDL (schema.sql), Seeders, and migrate.php
├── public/                  # Document root (index.php, .htaccess, static assets)
├── routes/                  # web.php & api.php
├── views/                   # Server-side views and Bootstrap 5 master layouts
├── .env.example             # Environment variable template
├── .gitignore               # Ignored files (secrets, storage, vendor)
├── composer.json            # PSR-4 Autoloading configuration
└── README.md                # Project documentation
```

---

## 📄 License
This project is licensed under the [MIT License](LICENSE).
