<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with modular seeders (1 seeder per module).
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class, // Module 1: Authentication, Security & RBAC
            DepartmentAndDesignationSeeder::class, // Module 2: PIM & Org Structure
            AttendanceSeeder::class,               // Module 3: Shifts & Geofence Logs
            LeaveSeeder::class,                    // Module 4: Leave Types & Accruals
            PerformanceSeeder::class,              // Module 5: OKRs & Appraisal Cycles
            RecruitmentSeeder::class,              // Module 6: Jobs & Applicant Kanban
            PayrollSyncSeeder::class,              // Module 7: Payroll Integration & Feeder
            AuditTrailSeeder::class,               // Module 8: Activity Audit Trail
            CompanyProfileSeeder::class,           // Organization: Company Profile & Offer Letter Template
        ]);
    }
}
