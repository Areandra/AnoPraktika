# AnoPraktika

> Web-based practicum management platform that streamlines class administration for Teaching Assistants and Students, including course management, assignment distribution, report submission, and automated document format validation.

![PHP](https://img.shields.io/badge/PHP-8.3+-777BB4?logo=php&logoColor=white)
![Laravel](https://img.shields.io/badge/Laravel-13.x-FF2D20?logo=laravel&logoColor=white)
![Alpine.js](https://img.shields.io/badge/Alpine.js-3.x-8BC0D0?logo=alpinedotjs&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?logo=tailwindcss&logoColor=white)
![Python](https://img.shields.io/badge/Python-3.x-3776AB?logo=python&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-8.x-646CFF?logo=vite&logoColor=white)
![SQLite](https://img.shields.io/badge/Database-SQLite%2FMySQL-003B57?logo=sqlite&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📑 Table of Contents

- [About the Project](#-about-the-project)
- [Tech Stack](#-tech-stack)
- [Features](#-features)
- [Project Structure](#-project-structure)
- [Database Schema](#-database-schema)
- [System Requirements](#-system-requirements)
- [Installation & Setup](#-installation--setup)
- [Python Virtual Environment (venv)](#-python-virtual-environment-venv)
- [Environment Configuration](#-environment-configuration)
- [Running the Application](#-running-the-application)
- [User Roles & Workflow](#-user-roles--workflow)
- [Document Validation Engine](#-document-validation-engine)
- [Available Commands](#-available-commands)
- [Testing](#-testing)
- [Contributing](#-contributing)
- [License](#-license)
- [Contact](#-contact)

---

## 📖 About the Project

**AnoPraktika** is a practicum management web application built for universities/colleges. It provides a structured workspace where:

- **Assistants (Asprak)** can create and manage practicum classes, configure custom document format rules, create assignments (modules & tasks), and review student submissions with annotations.
- **Students (Mahasiswa)** can join practicums, submit their lab reports (`.docx` + `.pdf`), track submission status, and receive feedback with annotated coordinates on their PDF.

A key feature is the **automated document format validator** — when a student submits a `.docx` report, the system automatically runs a Python script (`validate-doc.py`) that checks margins, font name, font size, and line spacing against the practicum's configured rules, then returns a detailed violation log per line.

---

## 🛠 Tech Stack

| Layer                   | Technology                                        |
| ----------------------- | ------------------------------------------------- |
| **Backend**             | PHP 8.3+, Laravel 13.x                            |
| **Frontend**            | Blade Templates, Alpine.js 3.x, Tailwind CSS v4   |
| **Font**                | Instrument Sans (via Bunny Fonts)                 |
| **Icons**               | Lucide 1.x                                        |
| **Build Tool**          | Vite 8.x (`laravel-vite-plugin`)                  |
| **Database**            | SQLite (default) / MySQL / MariaDB                |
| **Document Validation** | Python 3 (`python-docx`, `pdfplumber`) via `venv` |
| **Word Processing**     | PHPWord 1.x (read `.docx` metadata)               |
| **PDF**                 | DomPDF 3.x                                        |
| **Queue**               | Laravel Queue (database driver)                   |
| **Testing**             | PHPUnit 12.x                                      |
| **Code Quality**        | Laravel Pint                                      |
| **Dev Tools**           | Laravel Pail (log viewer), Concurrently           |

---

## ✨ Features

- **Role-based access** — Assistant and Student roles with different permissions per practicum
- **Practicum management** — Create/join practicum classes with unique join system (request → approval)
- **Assignment types** — Two types: `module` (requires `.docx` + `.pdf`) and `task` (any file)
- **Versioned submissions** — Every re-upload creates a new version; full history is preserved
- **Automated document validation** — Python engine checks margin, font, size, and line spacing on every `.docx` submission, mapped to exact line locations
- **PDF annotation** — Assistants can annotate coordinates on the PDF viewer when reviewing
- **Deadline enforcement** — Submissions rejected automatically past the deadline
- **Custom format rules per practicum** — Each practicum class can define its own margin, font, and spacing requirements

---

## 📁 Project Structure

```
AnoPraktika/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # Login, register, logout
│   │   │   ├── DashboardController.php     # Main workspace view logic
│   │   │   ├── PracticumController.php     # Create, join, accept, kick
│   │   │   ├── AssignmentController.php    # Create modules/tasks
│   │   │   └── SubmissionController.php    # Upload, validate, review submissions
│   │   └── Middleware/
│   │       └── PractikumMiddleware.php     # Guard access to practicum routes
│   ├── Models/
│   │   ├── User.php
│   │   ├── Practicum.php                   # Practicum class + format rules config
│   │   ├── PracticumUser.php               # Pivot: user role & status in practicum
│   │   ├── Assignment.php                  # Module or Task
│   │   ├── Submission.php                  # Per-student submission tracker
│   │   └── SubmissionVersion.php           # Versioned file uploads + validation logs
│   └── Scripts/
│       └── validate-doc.py                 # Python document format validator
├── database/
│   ├── migrations/                         # 8 migration files
│   └── seeders/
│       └── DatabaseSeeder.php              # Demo users, practicums, assignments
├── resources/
│   ├── css/app.css
│   ├── js/app.js
│   └── views/
│       ├── auth/                           # Login & Register pages
│       ├── workspace/                      # Main app workspace
│       └── components/                     # Sidebar, modals, PDF viewer, etc.
├── routes/
│   └── web.php
├── venv/                                   # Python virtual environment (created manually)
├── .env.example
├── composer.json
├── package.json
└── vite.config.js
```

---

## 🗄️ Database Schema

| Table                 | Description                                                                                                                   |
| --------------------- | ----------------------------------------------------------------------------------------------------------------------------- |
| `users`               | All users (students & assistants), with `identifier` (NIM/NIP)                                                                |
| `practicums`          | Practicum classes + configurable format rules (margin, font, spacing)                                                         |
| `practicum_users`     | Pivot table — user role (`student`/`assistant`) and join `status` (`joined`/`request`)                                        |
| `assignments`         | Modules or tasks, each with `deadline` and `type`                                                                             |
| `submissions`         | One submission record per student per assignment, tracks overall `status` (`pending`/`revision`/`approved`)                   |
| `submission_versions` | Versioned file uploads (`.docx`, `.pdf`, or attachment), stores `system_validation_logs` and `annotation_coordinates` as JSON |

---

## ⚙️ System Requirements

| Requirement  | Version                                           |
| ------------ | ------------------------------------------------- |
| **PHP**      | ≥ 8.3                                             |
| **Composer** | ≥ 2.x                                             |
| **Node.js**  | ≥ 20.x (LTS recommended)                          |
| **npm**      | ≥ 10.x                                            |
| **Python**   | ≥ 3.10                                            |
| **pip**      | bundled with Python                               |
| **Database** | SQLite 3 _(default)_, MySQL 8.x, or MariaDB 10.4+ |
| **OS**       | Windows 10/11, Ubuntu 22.04+, macOS               |

---

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/Areandra/AnoPraktika.git
cd AnoPraktika
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Copy the Environment File

```bash
cp .env.example .env
```

### 5. Generate the Application Key

```bash
php artisan key:generate
```

### 6. Setup Python Virtual Environment

> ⚠️ **This step is required.** The document validation engine runs a Python script via `venv/bin/python3`. Without this, `.docx` validation on module submissions will fail.

See the full section: [Python Virtual Environment (venv)](#-python-virtual-environment-venv)

### 7. Run Database Migrations

```bash
php artisan migrate
```

### 8. (Optional) Seed Demo Data

```bash
php artisan db:seed
```

This creates demo users, two practicum classes, and sample assignments:

| Role      | Email                   | Password   |
| --------- | ----------------------- | ---------- |
| Student   | `student@example.com`   | `password` |
| Assistant | `assistant@example.com` | `password` |

### 9. Build Frontend Assets

```bash
npm run build
```

---

### ⚡ One-Command Setup (Shortcut)

```bash
composer setup
```

Runs `composer install` → copies `.env` → generates app key → migrates DB → `npm install` → `npm run build` in one go. **Note: does not setup the Python venv — do that separately.**

---

## 🐍 Python Virtual Environment (venv)

The document format validator (`app/Scripts/validate-doc.py`) is called by `SubmissionController` via `Symfony\Component\Process`. It uses the Python interpreter at `venv/bin/python3` inside the project root.

### Why venv?

To isolate Python dependencies (`python-docx`, `pdfplumber`) from the system Python and avoid version conflicts.

### Setup (Linux / macOS)

```bash
# Create virtual environment in project root
python3 -m venv venv

# Activate it
source venv/bin/activate

# Install required packages
pip install python-docx pdfplumber

# Verify
python3 -c "import docx; import pdfplumber; print('OK')"

# Deactivate when done
deactivate
```

### Setup (Windows)

```bash
# Create virtual environment
python -m venv venv

# Activate it
venv\Scripts\activate

# Install required packages
pip install python-docx pdfplumber

# Deactivate when done
deactivate
```

> ⚠️ **On Windows**, the Python path in `SubmissionController.php` points to `venv/bin/python3` which is the Linux/macOS path. You may need to adjust it to `venv\Scripts\python.exe` for local Windows development.

### Verify the venv path

The controller uses this path:

```php
// app/Http/Controllers/SubmissionController.php
$pythonVenvPath = base_path('venv/bin/python3');
```

Make sure `venv/` exists in the project root after running the setup above.

### Python Script: What it validates

The script (`validate-doc.py`) accepts 3 arguments:

```bash
python3 venv/bin/python3 app/Scripts/validate-doc.py <docx_path> <pdf_path> <rules_json>
```

| Argument     | Description                                                    |
| ------------ | -------------------------------------------------------------- |
| `docx_path`  | Path to the uploaded `.docx` file                              |
| `pdf_path`   | Path to the uploaded `.pdf` file (used for coordinate mapping) |
| `rules_json` | JSON string of format rules from the practicum config          |

**Rules validated:**

| Rule                        | Default         |
| --------------------------- | --------------- |
| `required_margin_top_cm`    | 4.0 cm          |
| `required_margin_bottom_cm` | 3.0 cm          |
| `required_margin_left_cm`   | 4.0 cm          |
| `required_margin_right_cm`  | 3.0 cm          |
| `required_line_spacing`     | 1.5             |
| `required_font_name`        | Times New Roman |
| `required_font_size`        | 12.0 pt         |

Returns a JSON object with `is_valid` (boolean) and detailed `logs` including per-line violations with PDF coordinates.

---

## 🔧 Environment Configuration

Key variables to set in `.env`:

```env
APP_NAME=AnoPraktika
APP_ENV=local
APP_KEY=             # Auto-generated
APP_DEBUG=true
APP_URL=http://localhost

# --- Database (SQLite default — no extra setup needed) ---
DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=anopraktika
# DB_USERNAME=root
# DB_PASSWORD=your_password

# --- Session & Queue ---
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# --- File Storage ---
FILESYSTEM_DISK=local
```

### Using MySQL / MariaDB

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=anopraktika
DB_USERNAME=root
DB_PASSWORD=your_password
```

Create the database manually first:

```sql
CREATE DATABASE anopraktika CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## ▶️ Running the Application

### Development Mode

```bash
composer dev
```

Starts all services concurrently:

| Service                    | Description                  |
| -------------------------- | ---------------------------- |
| `php artisan serve`        | Laravel development server   |
| `php artisan queue:listen` | Background job queue worker  |
| `php artisan pail`         | Real-time log viewer         |
| `npm run dev`              | Vite HMR for frontend assets |

Open: **[http://localhost:8000](http://localhost:8000)**

### Production Build

```bash
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 👥 User Roles & Workflow

### Roles

| Role                    | Description                                                                                      |
| ----------------------- | ------------------------------------------------------------------------------------------------ |
| **Assistant (Asprak)**  | Creates practicum, configures format rules, manages assignments, reviews & annotates submissions |
| **Student (Mahasiswa)** | Joins practicum (pending approval), submits reports, tracks status and feedback                  |

### Workflow

```
[Assistant] Create Practicum (with custom format rules)
      ↓
[Student]   Request to Join → [Assistant] Accept/Kick
      ↓
[Assistant] Create Assignment (Module or Task) with deadline
      ↓
[Student]   Upload submission
           - Module: .docx + .pdf → auto-validated by Python engine
           - Task: any file
      ↓
[System]    Runs validate-doc.py → stores validation logs + format status
      ↓
[Assistant] Review submission → Approve or Request Revision (with PDF annotations)
      ↓
[Student]   Re-upload if revision requested (new version created)
```

### Submission Status Flow

```
pending → revision → pending → ... → approved
```

---

## 📄 Document Validation Engine

When a student submits a **module** (`.docx` + `.pdf`), the system:

1. Stores both files to `storage/app/public/submissions/`
2. Calls `SubmissionController::validateWordFormat()` which runs the Python script via `Symfony\Component\Process`
3. The Python script (`validate-doc.py`) uses `python-docx` to parse the Word document and `pdfplumber` to extract PDF text coordinates
4. Each paragraph is checked against the practicum's configured format rules
5. Violations are returned as JSON with exact page number, line number, and PDF bounding box coordinates
6. Results are stored in `submission_versions.system_validation_logs` and `is_format_valid`
7. The assistant can view violations and draw annotations directly on the PDF viewer in the browser

---

## 📋 Available Commands

### Artisan (Laravel CLI)

```bash
php artisan serve                  # Start local development server
php artisan migrate                # Run database migrations
php artisan migrate:fresh          # Drop all tables and re-migrate
php artisan migrate:fresh --seed   # Reset DB and seed demo data
php artisan db:seed                # Run seeders only
php artisan queue:listen           # Start queue worker
php artisan tinker                 # Interactive REPL
php artisan config:clear           # Clear config cache
php artisan cache:clear            # Clear application cache
php artisan storage:link           # Create public storage symlink (required for file access)
```

### npm Scripts

```bash
npm run dev       # Start Vite HMR (development)
npm run build     # Build production assets
```

### Composer Scripts

```bash
composer setup    # Full first-time setup (no Python venv)
composer dev      # Start all dev services concurrently
composer test     # Clear config cache then run tests
```

---

## 🧪 Testing

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run with coverage (requires Xdebug or PCOV)
php artisan test --coverage
```

### Code Style

```bash
./vendor/bin/pint          # Auto-fix code style
./vendor/bin/pint --test   # Check without fixing
```

---

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m "feat: add your feature"`
4. Push: `git push origin feature/your-feature`
5. Open a Pull Request

Please make sure all tests pass and code style is clean before submitting.

---

## 📜 License

This project is licensed under the **MIT License**.

---

## 📬 Contact

|                |                                                                                   |
| -------------- | --------------------------------------------------------------------------------- |
| **Owner**      | Areandra (Muhammad Ariel)                                                         |
| **GitHub**     | [@Areandra](https://github.com/Areandra)                                          |
| **Repository** | [github.com/Areandra/AnoPraktika](https://github.com/Areandra/AnoPraktika)        |
| **LinkedIn**   | [muhammad-ariel-4899312a0](https://www.linkedin.com/in/muhammad-ariel-4899312a0/) |

---

<p align="center">Built with Laravel · Alpine.js · Python</p>
