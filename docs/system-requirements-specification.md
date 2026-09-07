# Human Resource Management System (HRMS)
## System Requirements Specification (SRS)

- **Document Version:** 1.0
- **Target Stack:** Laravel 11.x / 12.x, Tailwind CSS v3/v4, Animate.css, Lucide Icons, Alpine.js / Livewire / Inertia.js, MySQL/PostgreSQL, Redis
- **Note on Payroll:** Payroll is handled by a standalone dedicated system: [payroll-management-system](https://github.com/mralif93/payroll-management-system). HRMS excludes internal payroll calculation/processing and provides integration/export points as needed.

---

## 1. Executive Summary & Architecture Overview

The Human Resource Management System (HRMS) is an enterprise-grade web application designed to automate, streamline, and govern core HR operations across an organization. The application centralizes employee data, attendance monitoring, leave administration, performance appraisals, and recruitment workflows.

> [!NOTE]
> Payroll calculation, statutory tax deductions, and batch payroll disbursements are managed externally via the dedicated [Payroll Management System](https://github.com/mralif93/payroll-management-system). This HRMS serves as the primary system of record for employee profiles, attendance summaries, and approved unpaid leave data feeds consumed by the payroll service.

### 1.1 Technical Stack Architecture

- **Backend Framework:** Laravel (PHP 8.2+)
- **Authentication:** Laravel Breeze / Fortify with multi-factor authentication (2FA)
- **Authorization:** Spatie Laravel-Permission (Role-Based Access Control)
- **Background Processing:** Laravel Queues (Redis) for bulk emails, notification triggers, and report exports
- **PDF Generation:** `barryvdh/laravel-dompdf` or `spatie/browsershot` for employment letters, appraisal summaries, and reports
- **Frontend Ecosystem:**
  - **Styling:** Tailwind CSS (Custom color palette, dark/light mode support, responsive container layouts)
  - **Iconography:** Lucide Icons (`lucide-blade` or vanilla SVG components) for consistent, accessible UI visual cues
  - **Motion & Transitions:** Animate.css for entrance/exit transitions on modals, slide-overs, flash notifications, and dropdowns
  - **Interactivity:** Alpine.js / Laravel Livewire for reactive, single-page-feel components without heavy frontend build complexity
- **Persistence & Caching:**
  - **Primary Database:** PostgreSQL 15+ or MySQL 8.0+
  - **Cache & Sessions:** Redis
  - **Object Storage:** AWS S3 or MinIO for secure document repository (resumes, contracts, identification documents)

---

## 2. User Roles & Permission Matrix (RBAC)

The system enforces strict Role-Based Access Control (RBAC):

| Module / Area | Super Admin | HR Administrator | Department Manager / Lead | General Employee |
| :--- | :--- | :--- | :--- | :--- |
| **System Settings & Audit Logs** | Full Access | No Access | No Access | No Access |
| **User & Role Management** | Full Access | Manage (non-admin) | Read-only (team) | No Access |
| **Employee Information (PIM)** | Full Access | Full Access | Read-only (team) | Read & Edit Self Only |
| **Attendance & Shifts** | Full Access | Full Access | Approve/Adjust (team) | Clock In/Out, View Self |
| **Leave Management** | Full Access | Manage policies/types | Approve/Reject (team) | Apply, View Balance/Self |
| **Performance (KPI/Reviews)** | Full Access | Manage Cycles/Forms | Conduct Reviews (team) | Self-Assessment, View |
| **Recruitment & Hiring** | Full Access | Full Access | Interviewer / Feedback | Referral Submissions |

---

## 3. Detailed Functional Requirements

### 3.1 Module 1: Authentication, Security & Onboarding

- **REQ-AUTH-01 (Central SSO & Identity Hub):** Authentication is governed centrally by **CentraFlow** (`:8004`) via standard OAuth 2.0 Authorization Code Grant (`/oauth/authorize`, `/oauth/token`). Local password forms are deprecated in favor of unified Single Sign-On (SSO) and Centralized Single Sign-Out (SLO) with automated role synchronization (`Super Admin`, `HR Administrator`, `Department Manager`, `Employee`).
- **REQ-AUTH-02 (Session Security & Isolation):** Idle session timeout after 30 minutes of inactivity; isolated `SESSION_COOKIE` (`pulsehr_session`) to prevent localhost session bleeding across federated sub-systems.
- **REQ-AUTH-03 (Audit Trail):** Immutable activity log recording `user_id`, `ip_address`, `action`, `model_type`, `old_values`, and `new_values` using `spatie/laravel-activitylog`.
- **REQ-AUTH-04 (Self-Service Profile):** Employees can update emergency contacts, marital status, and profile photos subject to HR approval before changes apply.

### 3.2 Module 2: Employee Information Management (PIM)

- **REQ-PIM-01 (Profile Lifecycle):** Centralized profile housing:
  - **Personal Data:** Legal Name, Date of Birth, National ID / Passport, Tax ID.
  - **Employment Data:** Employee Code (auto-generated, e.g., `EMP-2026-0042`), Designation, Department, Branch/Office, Manager/Supervisor, Joining Date, Employment Status (Probation, Permanent, Contractor, Terminated).
  - **Bank & Compensation Details:** Bank Name, Account Number, SWIFT/IBAN, Basic Salary, Allowance tiers (for export to external payroll).
- **REQ-PIM-02 (Document Vault):** Secure upload with mime-type validation and server-side AES-256 encryption for contracts, medical records, and certificates.
- **REQ-PIM-03 (Org Chart):** Dynamic hierarchical tree visualizer displaying reporting lines, driven by recursive SQL queries or adjacency lists.

### 3.3 Module 3: Attendance & Time Tracking

- **REQ-ATT-01 (Digital Punch):** Single-click Clock In / Clock Out button utilizing geolocation coordinates (HTML5 Geolocation API) and corporate IP address whitelisting.
- **REQ-ATT-02 (Shift Scheduling):** Support for multiple shift schedules (Fixed, Rotational, Flexible) with configurable grace periods (e.g., 15-minute late threshold).
- **REQ-ATT-03 (Overtime Calculation):** Configurable overtime rule engine calculating 1.5x on weekdays, 2.0x on public holidays/weekends, subject to line-manager signoff.
- **REQ-ATT-04 (Biometric Device Integration):** RESTful Webhooks and scheduled artisan console commands to sync logs from hardware devices (ZKTeco, Suprema) via API or direct socket listener.

### 3.4 Module 4: Leave & Absence Management

- **REQ-LV-01 (Leave Policies):** Accrual engine supporting Annual Leave, Sick Leave, Maternity/Paternity Leave, Unpaid Leave, and Compensatory Off. Supports pro-rated allocations for mid-year joiners.
- **REQ-LV-02 (Multi-Level Approval Workflow):** Configurable routing (e.g., Employee &rarr; Direct Manager &rarr; HR Admin). Automatic email notifications and Livewire in-app badges.
- **REQ-LV-03 (Overlap & Blackout Checks):** Immediate UI feedback preventing submission on days already requested, statutory blackout dates, or when department minimum staffing thresholds are breached.
- **REQ-LV-04 (Leave Calendar):** Visual calendar view (filtered by department/team) showing who is on leave, integrated with iCal / Google Calendar feeds.

### 3.5 Module 5: Performance Appraisals & OKRs

- **REQ-PERF-01 (Goal Setting):** Quarterly and annual OKRs (Objectives and Key Results) with quantifiable progress percentages.
- **REQ-PERF-02 (360-Degree Appraisal):** Configurable review forms allowing Self-Assessment, Peer Reviews, and Manager Evaluations with numeric scoring and qualitative remarks.

### 3.6 Module 6: Recruitment & Applicant Tracking (ATS)

- **REQ-ATS-01 (Job Board & Posting):** Public-facing careers portal rendered via Blade/Tailwind with automated slug generation and schema.org job posting metadata.
- **REQ-ATS-02 (Kanban Pipeline):** Drag-and-drop applicant pipeline (Applied &rarr; Screened &rarr; Interview &rarr; Offer &rarr; Hired &rarr; Rejected).
- **REQ-ATS-03 (One-Click Conversion):** Automatic conversion of an accepted applicant into a pre-onboarding employee record with automated welcome email and portal credentials.

### 3.7 Module 7: Payroll Integration & Data Sync (External)

- **REQ-INT-01 (Payroll Data Feed API):** Secure, authenticated REST API endpoint allowing the external [Payroll Management System](https://github.com/mralif93/payroll-management-system) to ingest active employee master records, salary/bank details, verified attendance hours, overtime totals, and unpaid leave days.
- **REQ-INT-02 (CSV/Excel Export):** Manual export capability for HR admins to download monthly attendance and unpaid leave summaries formatted for payroll import.

---

## 4. UI/UX & Design System Requirements

- **REQ-UI-01 (Tailwind Styling System):**
  - Consistent 8-point grid spacing system.
  - Custom semantic color tokens: brand (`indigo`/`slate`), success (`emerald`), warning (`amber`), danger (`rose`), info (`sky`).
  - Strict responsive breakpoints (`sm: 640px`, `md: 768px`, `lg: 1024px`, `xl: 1280px`).
- **REQ-UI-02 (Lucide Icons Integration):**
  - Semantic, monochrome stroke icons (20px / 24px) for all actions, navigation sidebars, status chips, and stats widgets (e.g., `Users`, `CalendarCheck`, `Clock`, `ShieldCheck`, `FileSpreadsheet`).
- **REQ-UI-03 (Animate.css Motion Design):**
  - **Modals and Drawers:** `animate__animated animate__fadeInUp animate__faster` on opening.
  - **Toast / Flash Messages:** `animate__animated animate__slideInRight animate__faster` entering from top-right, with `animate__fadeOutRight` when dismissed.
  - **Micro-interactions:** Subtle pulse (`animate__pulse`) on live tracking indicators (e.g., currently clocked-in status dot).
- **REQ-UI-04 (Accessibility & UX Standards):**
  - WCAG 2.1 Level AA compliance.
  - Screen reader accessible form labels, visible focus rings (`focus:ring-2 focus:ring-offset-2`), and high-contrast color pairings.

---

## 5. Non-Functional Requirements (NFRs)

- **Performance:** Server response time (TTFB) < 200ms for 95% of requests. Database queries optimized with eager loading (`with()`) to eliminate $N+1$ query issues.
- **Scalability:** Horizontal scaling support with stateless Laravel web application instances behind an Nginx / AWS ALB load balancer.
- **Security:**
  - Strict CSRF token validation on all state-changing endpoints.
  - SQL injection prevention via Eloquent PDO parameter binding.
  - Rate limiting (60 requests/minute for general API, 5 attempts/minute for auth endpoints).
- **Data Protection:** Personally Identifiable Information (PII) encrypted at rest using Laravel’s `Crypt` facade.
- **Availability & Reliability:** 99.9% uptime target. Automated daily database snapshots with Point-in-Time Recovery (PITR).

---

## 6. Relational Data Model (Core Entities)

```text
+--------------------+        +------------------------+        +--------------------+
|     departments    |        |       employees        |        |    designations    |
+--------------------+        +------------------------+        +--------------------+
| id (PK)            |<---1:N-| id (PK)                |-N:1--->| id (PK)            |
| name               |        | user_id (FK)           |        | title              |
| manager_id (FK)    |        | employee_code (UNIQUE) |        | department_id (FK) |
+--------------------+        | first_name, last_name  |        +--------------------+
                              | employment_status      |
                              | joined_date            |
                              +-----------+------------+
                                          |
                        +-----------------+-----------------+
                        |                                   |
                        v 1:N                               v 1:N
              +--------------------+              +--------------------+
              |    attendances     |              |   leave_requests   |
              +--------------------+              +--------------------+
              | id (PK)            |              | id (PK)            |
              | employee_id (FK)   |              | employee_id (FK)   |
              | date               |              | leave_type_id (FK) |
              | clock_in, out      |              | start_date         |
              | total_minutes      |              | end_date           |
              | status (late, etc) |              | status (enum)      |
              | ip_address, lat/lng|              | approved_by (FK)   |
              +--------------------+              +--------------------+
```