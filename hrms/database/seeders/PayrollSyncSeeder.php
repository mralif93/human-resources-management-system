<?php

namespace Database\Seeders;

use App\Models\PayrollExportLog;
use App\Models\PayrollSyncToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PayrollSyncSeeder extends Seeder
{
    /**
     * Run database seeds for Module 7: Payroll Integration & External Feeder.
     * Strictly 1 seeder for this module.
     */
    public function run(): void
    {
        // 1. Seed Dedicated API Token for PayFlow MY
        PayrollSyncToken::updateOrCreate(
            ['name' => 'PayFlow MY Production Engine'],
            [
                'token' => 'payflow_sec_live_9a8b7c6d5e4f3a2b1c0e9d8c7b6a5f4e',
                'is_active' => true,
                'last_used_at' => Carbon::now()->subMinutes(25),
            ]
        );

        PayrollSyncToken::updateOrCreate(
            ['name' => 'PayFlow MY Staging Sandbox'],
            [
                'token' => 'payflow_sec_test_112233445566778899aabbccddeeff00',
                'is_active' => true,
                'last_used_at' => Carbon::now()->subDays(2),
            ]
        );

        // 2. Seed Sample Historical Export Log
        $admin = User::first();
        if ($admin) {
            PayrollExportLog::firstOrCreate(
                [
                    'period' => date('Y-m'),
                    'file_name' => 'payroll_feeder_' . date('Y_m') . '.csv',
                ],
                [
                    'user_id' => $admin->id,
                    'export_format' => 'csv',
                    'records_count' => 4,
                    'created_at' => Carbon::now()->subHours(5),
                ]
            );
        }
    }
}
