<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AuditTrailSeeder extends Seeder
{
    /**
     * Run database seeds for Module 8: Activity Audit Trail.
     * Strictly 1 seeder for this module.
     */
    public function run(): void
    {
        $admin = User::where('email', 'admin@hrms.test')->first() ?? User::first();
        $adminId = $admin?->id;

        $logs = [
            [
                'user_id' => $adminId,
                'event' => 'auth.login.success',
                'category' => 'auth',
                'description' => 'Administrator successfully authenticated via secure password challenge.',
                'ip_address' => '192.168.1.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/128.0.0.0 Safari/537.36',
                'payload' => ['role' => 'Super Admin', 'guard' => 'web'],
                'created_at' => Carbon::now()->subHours(8),
            ],
            [
                'user_id' => $adminId,
                'event' => 'pim.employee.created',
                'category' => 'pim',
                'description' => 'New employee record generated for Alexander Vance with code EMP-2026-0001.',
                'ip_address' => '192.168.1.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/128.0.0.0 Safari/537.36',
                'payload' => ['employee_code' => 'EMP-2026-0001', 'department' => 'Engineering', 'status' => 'Permanent'],
                'created_at' => Carbon::now()->subHours(7),
            ],
            [
                'user_id' => $adminId,
                'event' => 'attendance.geofence.verified',
                'category' => 'attendance',
                'description' => 'Geofenced biometric clock-in verified for Marcus Brody at Office HQ (3.1391, 101.6870).',
                'ip_address' => '192.168.1.102',
                'user_agent' => 'PulseHR Mobile Android v2.1.0',
                'payload' => ['distance_meters' => 15, 'shift' => 'Morning Core 09:00 - 18:00', 'is_late' => false],
                'created_at' => Carbon::now()->subHours(6),
            ],
            [
                'user_id' => $adminId,
                'event' => 'leaves.application.approved',
                'category' => 'leaves',
                'description' => 'Annual Leave application for Emily Chen (3 days) approved by HR Manager.',
                'ip_address' => '192.168.1.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/128.0.0.0 Safari/537.36',
                'payload' => ['leave_type' => 'Annual Leave (AL)', 'days' => 3, 'balance_remaining' => 11],
                'created_at' => Carbon::now()->subHours(4),
            ],
            [
                'user_id' => $adminId,
                'event' => 'performance.appraisal.graded',
                'category' => 'performance',
                'description' => 'Manager review completed for Marcus Brody: 4.8 / 5.0 (Outstanding).',
                'ip_address' => '192.168.1.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/128.0.0.0 Safari/537.36',
                'payload' => ['cycle' => '2026 Q1 Evaluation', 'score' => 4.8, 'rating' => 'Outstanding'],
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'user_id' => $adminId,
                'event' => 'recruitment.applicant.converted',
                'category' => 'recruitment',
                'description' => 'Hired candidate Zack Fairuz converted to workforce employee dossier.',
                'ip_address' => '192.168.1.45',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/128.0.0.0 Safari/537.36',
                'payload' => ['job' => 'Senior UI/UX Design System Lead', 'stage' => 'hired', 'converted_code' => 'EMP-2026-0005'],
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'user_id' => $adminId,
                'event' => 'payroll.feeder.synced',
                'category' => 'payroll',
                'description' => 'PayFlow MY external engine consumed REST payroll feed for period ' . date('Y-m') . '.',
                'ip_address' => '10.0.0.15',
                'user_agent' => 'PayFlow-MY-Feeder-Client/1.0',
                'payload' => ['period' => date('Y-m'), 'records_synced' => 4, 'client_system' => 'PayFlow MY'],
                'created_at' => Carbon::now()->subMinutes(30),
            ],
        ];

        foreach ($logs as $logData) {
            AuditLog::firstOrCreate(
                [
                    'event' => $logData['event'],
                    'created_at' => $logData['created_at'],
                ],
                $logData
            );
        }
    }
}
