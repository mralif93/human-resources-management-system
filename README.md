# PulseHR — Enterprise Human Resource Management Suite

<p align="center">
  <a href="https://mralif93.github.io/human-resources-management-system/docs/">
    <img src="https://img.shields.io/badge/Live_Showcase-GitHub_Pages-6366f1?style=for-the-badge&logo=github&logoColor=white" alt="Live Showcase on GitHub Pages">
  </a>
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Tailwind_CSS-v4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="Tailwind CSS">
  <img src="https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/RBAC-Spatie-blue?style=for-the-badge&logo=shield" alt="RBAC">
  <img src="https://img.shields.io/badge/Tests-Pest_PHP-green?style=for-the-badge&logo=pest" alt="Pest PHP">
</p>

---

## 🌐 Live Landing Page & Interactive Showcase
Experience the live, interactive demo and operational specifications deployed on GitHub Pages:  
👉 **[https://mralif93.github.io/human-resources-management-system/docs/](https://mralif93.github.io/human-resources-management-system/docs/)**

Includes interactive client-side sandboxes for:
- 📍 **Geofenced Attendance Punch-In Simulator** (HTML5 Geolocation + 100m perimeter validation)
- 📅 **Multi-Tier Leave Approval Loop** (Automatic overlap validation & manager/HR chain sign-off)
- 💼 **Recruitment & ATS Kanban Pipeline** (Drag/advance candidates & 1-click employee onboarding)
- 🔄 **Decoupled External Payroll Feeder** (Cryptographically signed data payload for [PayFlow MY](https://github.com/mralif93/payroll-management-system))
- 🛡️ **Filterable Role-Based Access Control (RBAC) Matrix** (Super Admin, HR Admin, Department Lead, Staff)

---

## 📋 System Overview & Architecture

**PulseHR** is an enterprise-grade Human Resource Management System engineered with Laravel 12, Tailwind CSS, and Alpine.js. It centralizes employee records, geofenced attendance tracking, multi-tiered leave approvals, quarterly OKRs, and recruitment tracking.

```text
+-----------------------------------------------------------------------------------+
|                                 PulseHR Enterprise                                |
|                                                                                   |
|  [PIM Master Vault]  <--->  [Geofenced Attendance]  <--->  [Leave & Absence Engine] |
|          |                           |                             |              |
|          +---------------------------+-----------------------------+              |
|                                      |                                            |
|                                      v                                            |
|                      [External Payroll Feeder Hub]                                |
|                      (REST Feeds / Signed Exports)                                |
+--------------------------------------|--------------------------------------------+
                                       |
                                       v
                    +-------------------------------------+
                    |     PayFlow MY (Standalone Repo)    |
                    |   Statutory Deductions & Payroll    |
                    +-------------------------------------+
```

> [!NOTE]
> **Decoupled Payroll Architecture**: In accordance with enterprise microservice and clean segregation principles, all statutory tax computations, EPF/SOCSO/PCB deduction rules, and batch disbursement files are handled by the dedicated external system: **[payroll-management-system (PayFlow MY)](https://github.com/mralif93/payroll-management-system)**. PulseHR serves as the verified primary data source for attendance hours, overtime, and unpaid leave days.

---

## 🧩 Core HR Modules

| # | Module | Key Features & Capabilities |
|---|---|---|
| **01** | **Authentication & Security** | TOTP 2FA for Admin/HR, 30-min idle timeout, Spatie Activity Log audit trail. |
| **02** | **Personnel Information (PIM)** | Unique auto-generated ID (`EMP-YYYY-XXXX`), AES-256 encrypted documents, visual department org tree. |
| **03** | **Attendance & Shifts** | HTML5 Geolocation validation (100m radius), rotational shifts, grace periods, OT multiplier. |
| **04** | **Leave & Absence** | Pro-rated accrual engine, multi-level routing (Manager &rarr; HR), blackout overlap guard. |
| **05** | **Performance & OKRs** | Quarterly quantifiable OKR progress, 360-degree reviews (Self, Peer, Manager). |
| **06** | **Recruitment (ATS)** | Public career portal, Kanban pipeline stages, automated offer letter generation, 1-click onboarding. |
| **07** | **Payroll Integration** | Dedicated token-authenticated REST feeds & CSV exports for PayFlow MY synchronization. |

---

## 🔐 Role-Based Access Control (RBAC) Matrix

| Module / Area | Super Admin | HR Administrator | Department Lead | General Staff |
| :--- | :---: | :---: | :---: | :---: |
| **System Settings & Audit Trail** | Full Access | No Access | No Access | No Access |
| **Personnel Information (PIM)** | Full Access | Full Access | Team Only | Self Only |
| **Attendance & Geofencing** | Full Access | Full Access | Approve Team | Clock In/Out |
| **Leave Administration** | Full Access | Manage Policies | Approve Team | Apply & Balance |
| **Performance Reviews & OKRs** | Full Access | Manage Cycles | Review Team | Self Assessment |
| **Recruitment & ATS Pipeline** | Full Access | Full Access | Interviewer | Referrals |
| **Payroll Feed & Token API** | Generate Keys | Export CSV | No Access | No Access |

---

## 🚀 Quick Start (Local Setup)

```bash
# 1. Clone repository
git clone https://github.com/mralif93/human-resources-management-system.git
cd human-resources-management-system/hrms

# 2. Install PHP & Node dependencies
composer install
npm install

# 3. Environment configuration
cp .env.example .env
php artisan key:generate

# 4. Database migrations & seeders
php artisan migrate --seed

# 5. Build frontend assets & run dev server
npm run dev
php artisan serve
```

Default demo accounts:
- **Super Admin:** `admin@hrms.test` / `password`
- **HR Admin:** `hr@hrms.test` / `password`
- **Department Manager:** `manager@hrms.test` / `password`
- **Employee:** `employee@hrms.test` / `password`

---

## 📄 Documentation & Specifications
- 📘 [System Requirements Specification (SRS)](docs/system-requirements-specification.md)
- 🧪 [Software Test Plan (STP)](docs/software-test-plan.md)
- 🌐 [GitHub Pages Landing Page (`docs/index.html`)](docs/index.html)
- 🔗 [External Decoupled Payroll System](https://github.com/mralif93/payroll-management-system)
