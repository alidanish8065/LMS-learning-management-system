<div align="center">

# 🎓 LMS — Learning Management System

### A full-stack, role-based academic platform built with PHP & MySQL

[![Status](https://img.shields.io/badge/Status-Prototype%20%2F%20Active%20Development-orange?style=for-the-badge)](https://github.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL%20%2F%20MariaDB-10.4-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mariadb.org)
[![XAMPP](https://img.shields.io/badge/XAMPP-Ready-FB7A24?style=for-the-badge&logo=xampp&logoColor=white)](https://apachefriends.org)

</div>

---

> [!IMPORTANT]
> **🚧 This is a prototype / work-in-progress.**
> The core LMS functionality is implemented and working, but several modules (payments, exam proctoring, bulk operations, fine-grained permissions, etc.) are planned for future releases. The database schema already anticipates these features — see the [Roadmap](#-roadmap) section below.

---

## 📋 Table of Contents

- [About the Project](#-about-the-project)
- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Database Schema](#-database-schema)
- [Project Structure](#-project-structure)
- [Getting Started](#-getting-started)
- [Roles &amp; Access](#-roles--access)
- [Roadmap](#-roadmap)
- [Contributing](#-contributing)

---

## 📖 About the Project

This LMS (Learning Management System) is a web-based academic platform designed for universities. It supports multiple user roles — **Admin**, **Faculty**, and **Student** — each with their own dashboard and feature set.

The project was built from scratch as a university project using plain PHP, vanilla CSS/JS, and a MySQL/MariaDB database. No heavy frameworks were used by design, keeping the stack simple and easy to run locally with XAMPP.

---

## ✅ Features

### 👤 Admin

- Manage **Users** (create, edit, delete, view) with role assignment
- Manage **Faculties**, **Departments**, **Programs**, **Courses**
- Manage **Course Offerings** (semester, academic year, enrollment limits)
- Send and manage **Notifications** (general & targeted)
- View system dashboards

### 🧑‍🏫 Faculty

- View assigned courses and **manage modules & lessons**
- Create, publish, and manage **Assignments**
- **Grade assignments** and manage **grade book**
- Mark student **attendance** per session
- Manage **Exams** and record **exam attempts**
- Participate in **Course Forums**

### 🧑‍🎓 Student

- View enrolled courses and **course content** (lessons, resources)
- **Enroll** in available course offerings
- View **attendance records**
- Submit **assignments** and track submission status
- View **results**, timetable, and invoices
- Read **notifications**

---

## 🛠 Tech Stack

| Layer                  | Technology                             |
| ---------------------- | -------------------------------------- |
| **Backend**      | PHP 8.2 (procedural + OOP helpers)     |
| **Database**     | MariaDB 10.4 / MySQL 8+                |
| **Frontend**     | HTML5, Vanilla CSS, Vanilla JavaScript |
| **Server**       | Apache via XAMPP                       |
| **File Storage** | Local (`public/uploads/`)            |

---

## 🗄 Database Schema

The database (`lms`) contains **~47 active tables** covering all implemented features, plus **~17 reserved tables** (currently empty/unused) that are pre-designed for upcoming features like audit logging, exam proctoring, and advanced permissions.

**Key table groups:**

| Group              | Tables                                                                    |
| ------------------ | ------------------------------------------------------------------------- |
| Users & Auth       | `users`, `roles`, `user_roles`, `student`, `teacher`            |
| Academic Structure | `faculty`, `department`, `program`, `course`, `course_offering` |
| Content            | `module`, `lesson`, `resource`, `lesson_resource`                 |
| Assessments        | `assignment`, `exam`, `assignment_submission`, `exam_attempt`     |
| Grades             | `enrollment`, `grade_appeal`                                          |
| Attendance         | `attendance_session`, `attendance_record`                             |
| Notifications      | `notification`, `notification_queue`, `user_notification`           |
| Financials         | `invoice`, `payment`                                                  |
| Communication      | `forum`, `forum_thread`, `forum_post`                               |

> **Import the schema:** Use `lms.sql` — it includes table structure and seed/demo data.

---

## 📁 Project Structure

```
project1/
├── config.php                  # App config (BASE_PATH, BASE_URL, helpers)
├── bootstrap.php               # Autoloader / session init
├── index.php                   # Entry point (redirect to login)
├── lms.sql                     # Full DB schema + seed data
├── drop_unused_tables.sql      # Script to remove unimplemented tables
│
├── public/                     # Publicly accessible files
│   ├── dbconfig.php            # DB connection
│   ├── adminaccess.php         # Role-based access guard
│   ├── profile.php             # User profile page
│   ├── login_and_authentication/
│   ├── notification/
│   └── uploads/                # User-uploaded files (gitignored)
│
├── roles/                      # Role-specific dashboards & pages
│   ├── admin/
│   ├── Faculty/
│   └── Student/
│
├── admin_tools/                # Admin CRUD modules
│   ├── Academic/               # Faculty, Dept, Program, Course management
│   └── User/                   # User management
│
├── templates/                  # Shared HTML partials (header, sidebar, etc.)
├── css/                        # Global stylesheets
└── js/                         # Global scripts
```

---

## 🚀 Getting Started

### Prerequisites

- [XAMPP](https://www.apachefriends.org/) (Apache + MySQL/MariaDB + PHP 8.x)
- A web browser

### Installation

1. **Clone the repository**

   ```bash
   git clone https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
   ```

2. **Move to XAMPP's web root**

   ```
   C:\xampp\htdocs\project1\
   ```

3. **Create the database**

   - Open [phpMyAdmin](http://localhost/phpmyadmin)
   - Create a new database named `lms`
   - Import `lms.sql`
4. **Configure DB connection**

   - Open `public/dbconfig.php`
   - Update credentials if needed (default: `root` / no password)
5. **Start XAMPP** (Apache + MySQL) and visit:

   ```
   http://localhost/project1/
   ```

### Default Login Credentials (from seed data)

| Role    | Email                | Password                              |
| ------- | -------------------- | ------------------------------------- |
| Admin   | `admin1@gmail.com` | `admin123` *(check hash in seed)* |
| Faculty | `faraz1@gmail.com` | *(check hash in seed)*              |

> ⚠️ Passwords are stored as bcrypt hashes in the seed data. Update them via phpMyAdmin or the app's user management panel.

---

## 👥 Roles & Access

| Role                | Description                                                               |
| ------------------- | ------------------------------------------------------------------------- |
| `admin`           | Full system access — manages all users, academic structure, and settings |
| `faculty`         | Manages course content, assessments, attendance, and grades               |
| `student`         | Accesses enrolled course content, submits work, views results             |
| `student_affairs` | *(Planned)* Manages student records and program changes                 |
| `examination`     | *(Planned)* Manages exam scheduling and results                         |
| `admission`       | *(Planned)* Manages applicant intake and admission decisions            |
| `accounts`        | *(Planned)* Manages invoices, payments, and fee structure               |

---

## 🗺 Roadmap

> This project is actively being developed. The database schema is already designed to support these features — implementation is in progress.

### Phase 2 — Planned Features

- [ ] **Admission Module** — Application intake, document upload, status tracking (`admission_document`, `admission_status_log`)
- [ ] **Fine-Grained Permissions** — Per-role permission management (`permission`, `roles_permissions`)
- [ ] **Course Sections** — Sub-groups within a course offering (`course_section`)
- [ ] **Exam Proctoring Logs** — Integrity tracking for online exams (`exam_integrity_log`)
- [ ] **Multi-Section Exams** — Exams with multiple sections and per-section marks (`exam_section`, `exam_section_mark`)
- [ ] **Grade Components** — Configurable grade breakdown per offering (`grade_component`, `assessment_weight`)
- [ ] **Formal Withdrawal Workflow** — Enrollment withdrawal with refund tracking (`enrollment_withdrawal`)
- [ ] **Fee Structure Management** — Per-program fee configuration (`fee_structure`)
- [ ] **Role Change Request Workflow** — Approval-based role reassignment (`role_change_requests`)
- [ ] **Room Availability Scheduling** — Time-slot based room booking (`room_availability`)

### Phase 3 — Audit & Compliance

- [ ] **Full Audit Logging** — Attendance, grade, enrollment, content, and payment change logs
- [ ] **Security Incident Tracking** — Brute force, SQL injection detection logs
- [ ] **Data Export Logs** — Track all exported reports
- [ ] **Log Retention Policies** — Automated log cleanup rules

---

## 🤝 Contributing

This is currently a solo/academic project, but contributions, suggestions, and feedback are welcome!

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m 'Add some feature'`
4. Push to the branch: `git push origin feature/your-feature`
5. Open a Pull Request

---

## 📄 License

This project is open source and available under the [MIT License](LICENSE).

---

<div align="center">
  <sub>Built with ☕ and PHP · Currently a prototype · More coming soon</sub>
</div>
