# IEC Blended Learning Platform

A secure, role-based Learning Management System (LMS) designed for Business Communication courses. This platform facilitates a "Blended Learning" approach, combining 28 weeks of structured online modules with offline tutor-led sessions.

![Status](https://img.shields.io/badge/Status-Prototype-orange) ![Security](https://img.shields.io/badge/Security-Hardened-green) ![Stack](https://img.shields.io/badge/PHP-8.2-blue)

## 🚀 Key Features

* **Role-Based Access Control (RBAC):** Distinct dashboards for **Students**, **Tutors**, and **Admins**.
* **Secure Authentication:**
    * Prevents **Session Hijacking** via IP/User-Agent fingerprinting and ID regeneration.
    * Protects against **SQL Injection** using Prepared Statements.
    * **XSS Protection** with secure cookie flags (`HttpOnly`, `SameSite`).
* **Progress Tracking:** Students can track completion of Video, Quiz, and Speaking modules.
* **Dockerized Environment:** Fully containerized Setup (Apache, MariaDB, phpMyAdmin) for easy deployment.

## 🛠️ Tech Stack

* **Backend:** Native PHP 8.2 (No frameworks, pure logic).
* **Database:** MariaDB / MySQL.
* **Frontend:** HTML5, CSS3, JavaScript (Vanilla).
* **DevOps:** Docker & Docker Compose.

---

## ⚙️ Installation & Setup

### 1. Clone the Repository
```bash
git clone [https://github.com/Mo-Aatef/IEC-Platform-Public.git]
cd IEC-project/Apache