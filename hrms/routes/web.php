<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Landing Page (using welcome page with public layout)
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

// Guest Authentication Routes (CentraFlow SSO Only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/auth/centraflow', [\App\Http\Controllers\CentraFlowSsoClientController::class, 'redirect'])->name('sso.login');
    Route::get('/auth/callback', [\App\Http\Controllers\CentraFlowSsoClientController::class, 'callback'])->name('sso.callback');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
});

// Authenticated Routes (using admin layout)
Route::middleware(['auth', 'cf.session'])->group(function () {
    Route::get('/dashboard', function () {
        $recentEmployees = \App\Models\Employee::with(['department', 'designation'])->latest()->take(5)->get();
        return view('dashboard', compact('recentEmployees'));
    })->name('dashboard');

    // Module 2: Personnel Information Management (PIM) - Super Admin, HR Admin, Department Manager
    Route::middleware('role:Super Admin,HR Administrator,Department Manager')->group(function () {
        Route::get('/employees/export', [\App\Http\Controllers\EmployeeController::class, 'export'])->name('employees.export');
        Route::post('/employees/import', [\App\Http\Controllers\EmployeeController::class, 'import'])->name('employees.import');
        Route::get('/employees/template', [\App\Http\Controllers\EmployeeController::class, 'template'])->name('employees.template');
        Route::resource('employees', \App\Http\Controllers\EmployeeController::class)->only(['index', 'store', 'show', 'destroy']);
    });

    // Module 3: Attendance & Shifts Tracking - Accessible to all authenticated staff, bulk import/export for admin/managers
    Route::middleware('role:Super Admin,HR Administrator,Department Manager')->group(function () {
        Route::get('/attendance/export', [\App\Http\Controllers\AttendanceController::class, 'export'])->name('attendance.export');
        Route::post('/attendance/import', [\App\Http\Controllers\AttendanceController::class, 'import'])->name('attendance.import');
        Route::get('/attendance/template', [\App\Http\Controllers\AttendanceController::class, 'template'])->name('attendance.template');
    });
    Route::get('/attendance', [\App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/punch-in', [\App\Http\Controllers\AttendanceController::class, 'punchIn'])->name('attendance.punch-in');
    Route::post('/attendance/punch-out', [\App\Http\Controllers\AttendanceController::class, 'punchOut'])->name('attendance.punch-out');

    // Module 4: Leave & Absence Management - All can view/submit, approvals for Admin & Managers
    Route::middleware('role:Super Admin,HR Administrator,Department Manager')->group(function () {
        Route::get('/leaves/export', [\App\Http\Controllers\LeaveController::class, 'export'])->name('leaves.export');
        Route::post('/leaves/import', [\App\Http\Controllers\LeaveController::class, 'import'])->name('leaves.import');
        Route::get('/leaves/template', [\App\Http\Controllers\LeaveController::class, 'template'])->name('leaves.template');
        Route::post('/leaves/{leave}/approve', [\App\Http\Controllers\LeaveController::class, 'approve'])->name('leaves.approve');
        Route::post('/leaves/{leave}/reject', [\App\Http\Controllers\LeaveController::class, 'reject'])->name('leaves.reject');
    });
    Route::get('/leaves', [\App\Http\Controllers\LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [\App\Http\Controllers\LeaveController::class, 'store'])->name('leaves.store');

    // Module 5: Performance Appraisals & OKRs
    Route::get('/performance', [\App\Http\Controllers\PerformanceController::class, 'index'])->name('performance.index');
    Route::post('/performance/reviews/{review}/evaluate', [\App\Http\Controllers\PerformanceController::class, 'evaluate'])->name('performance.evaluate');
    Route::post('/performance/okrs', [\App\Http\Controllers\PerformanceController::class, 'storeOkr'])->name('performance.okrs.store');
    Route::post('/performance/okrs/{okr}/progress', [\App\Http\Controllers\PerformanceController::class, 'updateOkrProgress'])->name('performance.okrs.progress');

    // Module 6: Recruitment & Applicant Tracking (ATS) - Super Admin, HR Admin, Department Manager
    Route::middleware('role:Super Admin,HR Administrator,Department Manager')->group(function () {
        Route::get('/recruitment/export', [\App\Http\Controllers\RecruitmentController::class, 'export'])->name('recruitment.export');
        Route::post('/recruitment/import', [\App\Http\Controllers\RecruitmentController::class, 'import'])->name('recruitment.import');
        Route::get('/recruitment/template', [\App\Http\Controllers\RecruitmentController::class, 'template'])->name('recruitment.template');
        Route::get('/recruitment', [\App\Http\Controllers\RecruitmentController::class, 'index'])->name('recruitment.index');
        Route::post('/recruitment/jobs', [\App\Http\Controllers\RecruitmentController::class, 'storeJob'])->name('recruitment.jobs.store');
        Route::post('/recruitment/applicants', [\App\Http\Controllers\RecruitmentController::class, 'storeApplicant'])->name('recruitment.applicants.store');
        Route::post('/recruitment/applicants/{applicant}/stage', [\App\Http\Controllers\RecruitmentController::class, 'updateStage'])->name('recruitment.applicants.stage');
        Route::post('/recruitment/applicants/{applicant}/convert', [\App\Http\Controllers\RecruitmentController::class, 'convertToEmployee'])->name('recruitment.applicants.convert');
        Route::put('/recruitment/applicants/{applicant}/offer-details', [\App\Http\Controllers\RecruitmentController::class, 'updateOfferDetails'])->name('recruitment.applicants.offer-details');
        Route::get('/recruitment/applicants/{applicant}/offer-letter', [\App\Http\Controllers\RecruitmentController::class, 'offerLetter'])->name('recruitment.applicants.offer-letter');
    });

    // Module 7: Payroll Integration & Data Sync Hub - Super Admin & HR Admin
    Route::middleware('role:Super Admin,HR Administrator')->group(function () {
        Route::get('/payroll-sync', [\App\Http\Controllers\PayrollSyncController::class, 'index'])->name('payroll-sync.index');
        Route::get('/payroll-sync/export', [\App\Http\Controllers\PayrollSyncController::class, 'exportCsv'])->name('payroll-sync.export');
        Route::post('/payroll-sync/tokens', [\App\Http\Controllers\PayrollSyncController::class, 'generateToken'])->name('payroll-sync.tokens.store');
        Route::post('/payroll-sync/tokens/{token}/toggle', [\App\Http\Controllers\PayrollSyncController::class, 'toggleToken'])->name('payroll-sync.tokens.toggle');
    });

    // Module 8: Activity Audit Trail & Enterprise Security - Super Admin & HR Admin
    Route::middleware('role:Super Admin,HR Administrator')->group(function () {
        Route::get('/audit-logs/export', [\App\Http\Controllers\AuditLogController::class, 'export'])->name('audit-logs.export');
        Route::get('/audit-logs', [\App\Http\Controllers\AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    // Organization & Settings - Super Admin & HR Admin
    Route::middleware('role:Super Admin,HR Administrator')->group(function () {
        Route::get('/settings/profile', [\App\Http\Controllers\CompanyProfileController::class, 'edit'])->name('settings.profile');
        Route::put('/settings/profile', [\App\Http\Controllers\CompanyProfileController::class, 'update'])->name('settings.profile.update');
        Route::get('/settings/templates', [\App\Http\Controllers\CompanyProfileController::class, 'templates'])->name('settings.templates');
        Route::put('/settings/templates', [\App\Http\Controllers\CompanyProfileController::class, 'updateTemplate'])->name('settings.templates.update');

        // Enterprise Operational Parameters
        Route::get('/settings/shifts', [\App\Http\Controllers\ShiftGeofenceController::class, 'index'])->name('settings.shifts');
        Route::put('/settings/shifts/geofence', [\App\Http\Controllers\ShiftGeofenceController::class, 'updateGeofence'])->name('settings.shifts.geofence');
        Route::post('/settings/shifts', [\App\Http\Controllers\ShiftGeofenceController::class, 'storeShift'])->name('settings.shifts.store');
        Route::put('/settings/shifts/{shift}', [\App\Http\Controllers\ShiftGeofenceController::class, 'updateShift'])->name('settings.shifts.update');
        Route::delete('/settings/shifts/{shift}', [\App\Http\Controllers\ShiftGeofenceController::class, 'destroyShift'])->name('settings.shifts.destroy');
        Route::post('/settings/shifts/{shift}/set-default', [\App\Http\Controllers\ShiftGeofenceController::class, 'setDefault'])->name('settings.shifts.set-default');

        Route::get('/settings/leave-types', [\App\Http\Controllers\LeavePolicyController::class, 'index'])->name('settings.leave-types');
        Route::put('/settings/leave-types/{leaveType}', [\App\Http\Controllers\LeavePolicyController::class, 'update'])->name('settings.leave-types.update');
        Route::post('/settings/leave-types/{leaveType}/toggle', [\App\Http\Controllers\LeavePolicyController::class, 'toggleStatus'])->name('settings.leave-types.toggle');
        Route::post('/settings/leave-types', [\App\Http\Controllers\LeavePolicyController::class, 'store'])->name('settings.leave-types.store');

        Route::get('/settings/departments', [\App\Http\Controllers\DepartmentTaxonomyController::class, 'index'])->name('settings.departments');
        Route::post('/settings/departments', [\App\Http\Controllers\DepartmentTaxonomyController::class, 'storeDepartment'])->name('settings.departments.store');
        Route::put('/settings/departments/{department}', [\App\Http\Controllers\DepartmentTaxonomyController::class, 'updateDepartment'])->name('settings.departments.update');
        Route::post('/settings/departments/{department}/toggle', [\App\Http\Controllers\DepartmentTaxonomyController::class, 'toggleDepartment'])->name('settings.departments.toggle');
        Route::post('/settings/designations', [\App\Http\Controllers\DepartmentTaxonomyController::class, 'storeDesignation'])->name('settings.designations.store');
    });

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
