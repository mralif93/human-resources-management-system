<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display enterprise Activity Audit Logs with category filtering and payload inspection.
     */
    public function index(Request $request): View
    {
        $category = $request->input('category');
        $search = $request->input('search');
        $perPage = in_array((int) $request->input('per_page'), [10, 25, 50, 100]) ? (int) $request->input('per_page') : 25;

        $query = AuditLog::with('user')->latest('id');

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $logs = $query->paginate($perPage)->withQueryString();

        // High Level Metrics
        $totalLogs = AuditLog::count();
        $authEvents = AuditLog::where('category', 'auth')->count();
        $payrollEvents = AuditLog::where('category', 'payroll')->count();
        $activeActors = AuditLog::whereNotNull('user_id')->distinct('user_id')->count('user_id');

        return view('admin.audit.index', compact(
            'logs',
            'category',
            'search',
            'totalLogs',
            'authEvents',
            'payrollEvents',
            'activeActors'
        ));
    }

    /**
     * Export enterprise audit logs to CSV for compliance inspection.
     */
    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $category = $request->input('category');
        $search = $request->input('search');

        $query = AuditLog::with('user')->latest('id');

        if ($category) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('event', 'like', "%{$search}%")
                  ->orWhere('ip_address', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->get();
        $fileName = 'activity_audit_trail_' . date('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        return response()->stream(function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Log ID',
                'Timestamp',
                'Actor Email',
                'Actor Name',
                'Event',
                'Category',
                'Description',
                'IP Address',
            ]);

            foreach ($records as $item) {
                fputcsv($file, [
                    $item->id,
                    $item->created_at?->toDateTimeString(),
                    $item->user?->email ?? 'System Engine',
                    $item->user?->name ?? 'System',
                    $item->event,
                    $item->category,
                    $item->description,
                    $item->ip_address,
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}

