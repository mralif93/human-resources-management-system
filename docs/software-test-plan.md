# Human Resource Management System (HRMS)
## Master Test Plan & Automated QA Strategy

- **Document Version:** 1.0
- **Test Frameworks:** Pest PHP / PHPUnit, Laravel Dusk / Playwright, Cypress, K6 (Load Testing)
- **Target Environment:** Local (Docker/Sail), CI/CD (GitHub Actions), Staging (AWS/DigitalOcean)
- **Note on Payroll:** Payroll calculation/processing is excluded as it is handled by the dedicated [payroll-management-system](https://github.com/mralif93/payroll-management-system). HRMS tests focus on core HR modules and payroll data export endpoints.

---

## 1. Test Strategy & Scope

This Master Test Plan defines the testing procedures, tools, automated pipelines, and acceptance criteria for the Laravel-based HRMS.

### 1.1 In-Scope Testing

- **Unit Testing:** Individual domain logic, leave accrual calculation services, attendance work-hour calculators, utility helpers.
- **Feature & Integration Testing:** Laravel HTTP endpoints, authorization gates/policies, form requests, database transactions, queued job dispatches, event listeners, and payroll data export feeds.
- **End-to-End (E2E) Browser Testing:** User journeys in Chrome/Firefox (Clock-in flow, Leave submission and approval loop, Drag-and-drop recruitment Kanban).
- **UI/UX & Accessibility Testing:** Tailwind CSS responsive layout validation across mobile/tablet/desktop, Lucide icon SVG rendering, Animate.css animation completion without broken layouts, WCAG 2.1 AA keyboard navigation.
- **Security & Vulnerability Testing:** RBAC privilege escalation tests, multi-tenant/unauthorized record access, SQL injection, XSS filtering, file upload exploits.
- **Performance & Load Testing:** Simultaneous clock-in load spikes (e.g., 5,000 employees punching in at 09:00 AM) and bulk attendance/leave data export.

### 1.2 Out-of-Scope

- **Payroll Calculations & Tax Deductions:** Handled independently in [payroll-management-system](https://github.com/mralif93/payroll-management-system).
- Hardware internal firmware testing for third-party biometric devices (tested solely via incoming HTTP webhook payloads and mock sockets).

---

## 2. Test Environments & CI/CD Pipeline

### 2.1 Environments

- **Development:** Laravel Sail / Docker with SQLite in-memory for lightning-fast unit tests.
- **CI/CD Pipeline (GitHub Actions):** Runs on every pull request to `develop` and `main`:
  - **Static Analysis:** PHPStan / Larastan at Level 8.
  - **Code Style:** Laravel Pint (PSR-12 strict formatting).
  - **Automated Tests:** Pest PHP unit and feature tests with MySQL and Redis service containers.
  - **Frontend Build:** Vite build check with Tailwind compilation, ensuring no unpurged CSS breakage.
- **Staging:** Mirror of production (PHP 8.3, PostgreSQL 15, Redis 7, Nginx) populated with seeded pseudo-anonymized data.

---

## 3. Detailed Test Matrix & Test Cases

### 3.1 Authentication & RBAC Test Suite

| Test ID | Module | Test Scenario | Steps | Expected Result | Type |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-SEC-01** | Auth | Unauthorized route access | Unauthenticated guest requests `/api/v1/employees` | HTTP 401 Unauthorized returned with JSON error envelope. | Pest Feature |
| **TC-SEC-02** | RBAC | Role boundary enforcement | User with role Employee attempts `DELETE /employees/{id}` | HTTP 403 Forbidden thrown by Laravel Policy (`EmployeePolicy@delete`). | Pest Feature |
| **TC-SEC-03** | Auth | 2FA verification challenge | Admin user enters valid credentials | Redirected to `/two-factor-challenge`; access token not issued until valid TOTP provided. | Pest / Dusk |
| **TC-SEC-04** | Security | Malicious document upload | Upload `payload.php.jpg` disguised as an avatar | Upload rejected by `File::types(['jpg', 'png', 'pdf'])->max(5120)` rule. | Pest Feature |

### 3.2 Employee Information (PIM) Test Suite

| Test ID | Module | Test Scenario | Steps | Expected Result | Type |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-PIM-01** | Profile | Auto-generation of Employee ID | HR creates employee with valid payload | DB generates sequential code formatted `EMP-YYYY-XXXX`; event `EmployeeCreated` dispatched. | Unit / Integration |
| **TC-PIM-02** | Profile | Soft delete cascade behavior | HR soft-deletes an employee | `deleted_at` timestamp populated; active queries exclude record; dependent relations remain intact. | Pest Feature |
| **TC-PIM-03** | Profile | Encrypted field persistence | Save national ID / SSN to employee table | Raw database column displays encrypted string; Model decrypts transparently on retrieval. | Pest Unit |

### 3.3 Attendance & Shifts Test Suite

| Test ID | Module | Test Scenario | Steps | Expected Result | Type |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-ATT-01** | Attendance | Valid Geofenced Clock-In | Employee clocks in with GPS within 100m of office coordinates | Record saved with `status = on_time`, lat/lng captured, button updates to "Clock Out". | E2E (Dusk) |
| **TC-ATT-02** | Attendance | Geofence restriction failure | Employee clocks in with GPS coordinates 5km away | System rejects request with validation error: *"Outside designated check-in perimeter."* | Pest Feature |
| **TC-ATT-03** | Attendance | Shift Late Detection | Employee assigned to 09:00 AM shift clocks in at 09:25 AM (grace: 15m) | `attendances.is_late` marked true; late minutes recorded as 25. | Pest Unit |
| **TC-ATT-04** | Attendance | Duplicate Clock-In Prevention | Rapid double-click on Clock In button | Request throttled / lock handled via atomic lock (`Cache::lock`), creating exactly 1 record. | Pest Feature |

### 3.4 Leave Management Test Suite

| Test ID | Module | Test Scenario | Steps | Expected Result | Type |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-LV-01** | Leave | Insufficient Leave Balance | Employee with 2 days Annual Leave applies for 5 days | Validation error: *"Requested days exceed remaining entitlement (2 days remaining)."* | Pest Feature |
| **TC-LV-02** | Leave | Dual-Approval Workflow | Employee applies &rarr; Manager approves &rarr; HR reviews | Status transitions: `pending_manager` &rarr; `pending_hr` &rarr; `approved`. Email notifications dispatched. | Pest Integration |
| **TC-LV-03** | Leave | Overlapping Leave Guard | Apply for leaves on dates already holding an active approved or pending request | Form request throws validation exception with conflicting date markers. | Pest Feature |
| **TC-LV-04** | Leave | Balance Deduction on Approval | HR clicks "Approve" | `leave_balances.used` increments by duration; `leave_balances.remaining` decrements atomically. | Pest Unit |

### 3.5 External Payroll Integration Test Suite

| Test ID | Module | Test Scenario | Steps | Expected Result | Type |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-INT-01** | Payroll Integration | Authorized attendance & leave feed | External payroll service queries `/api/v1/integrations/payroll/sync` with valid API token | Returns JSON payload with active employee IDs, verified work hours, overtime totals, and approved unpaid leave count. | Pest Feature |
| **TC-INT-02** | Payroll Integration | Unauthorized sync rejection | Request `/api/v1/integrations/payroll/sync` with missing or invalid bearer token | HTTP 401 Unauthorized returned; no employee salary or attendance data exposed. | Pest Feature |
| **TC-INT-03** | Payroll Integration | Monthly CSV export formatting | HR Admin downloads monthly payroll export CSV | CSV correctly compiles employee code, worked days, overtime minutes, and unpaid absence days without corrupt rows. | Pest Feature |

### 3.6 Frontend UI, Tailwind, Animate.css & Lucide Icons

| Test ID | Module | Test Scenario | Steps | Expected Result | Type |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **TC-UI-01** | UI/UX | Responsive Sidebar Collapse | View dashboard on viewport width 375px (iPhone) | Sidebar hidden by default; hamburger icon visible; clicking opens drawer with `animate__slideInLeft`. | E2E (Playwright) |
| **TC-UI-02** | UI/UX | Modal Entry & Exit Animation | Click "New Leave Request" button | Modal renders with `animate__animated animate__fadeInUp`; closing removes DOM elements cleanly without flash. | Visual / Dusk |
| **TC-UI-03** | UI/UX | Lucide Icon Asset Loading | Inspect dashboard navigation and action buttons | Lucide SVG elements render with correct width, height, and accessible SVG `aria-hidden` attributes. | E2E (Dusk) |
| **TC-UI-04** | UI/UX | Flash Toast Notifications | Trigger action (e.g., "Employee Updated") | Toast notification slides in with `animate__slideInRight`, auto-dismisses after 4,000ms with fade out. | Visual / Dusk |

---

## 4. Sample Automated Pest PHP Test Implementations

```php
<?php

use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveType;
use App\Models\LeaveRequest;
use App\Enums\LeaveStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an employee cannot apply for leave beyond their remaining entitlement balance', function () {
    // 1. Arrange
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id]);
    $leaveType = LeaveType::factory()->create([
        'name' => 'Annual Leave',
        'days_allowed' => 14,
    ]);

    // Give employee 2 remaining days
    $employee->leaveBalances()->create([
        'leave_type_id' => $leaveType->id,
        'entitled_days' => 14,
        'used_days' => 12,
    ]);

    // 2. Act: Attempt applying for 5 consecutive working days
    $response = $this->actingAs($user)->postJson('/api/v1/leave-requests', [
        'leave_type_id' => $leaveType->id,
        'start_date' => now()->addDays(2)->format('Y-m-d'),
        'end_date' => now()->addDays(6)->format('Y-m-d'),
        'reason' => 'Family vacation',
    ]);

    // 3. Assert
    $response->assertStatus(422)
             ->assertJsonValidationErrors(['days_requested']);

    $this->assertDatabaseMissing('leave_requests', [
        'employee_id' => $employee->id,
        'status' => LeaveStatus::PENDING,
    ]);
});

test('employee clock-in validates geofence correctly', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create([
        'user_id' => $user->id,
        'work_location_latitude' => 3.1390,
        'work_location_longitude' => 101.6869, // Office HQ
    ]);

    // Attempt clocking in from 10km away
    $response = $this->actingAs($user)->postJson('/api/v1/attendance/clock-in', [
        'latitude' => 3.2200,
        'longitude' => 101.7500,
    ]);

    $response->assertStatus(422)
             ->assertJson(['message' => 'You are outside the permitted check-in radius.']);
});
```
