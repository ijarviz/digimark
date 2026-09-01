<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AuditLogModel;
use App\Models\JobRunLogModel;

class JobLogController extends BaseController
{
    public function index()
    {
        $jobRunLogModel = new JobRunLogModel();

        $jobLogs   = $jobRunLogModel->orderBy('started_at', 'DESC')->findAll(50);
        $auditLogs = (new AuditLogModel())
            ->select('audit_log.*, users.username')
            ->join('users', 'users.id = audit_log.user_id', 'left')
            ->orderBy('audit_log.created_at', 'DESC')
            ->findAll(50);

        return view('layouts/main', [
            'title'       => 'Job Monitoring',
            'subtitle'    => 'Status job snapshot/publish dan riwayat audit.',
            'activeNav'   => 'admin-job-logs',
            'contentView' => 'admin/job_logs_index',
            'contentData' => [
                'jobLogs'        => $jobLogs,
                'auditLogs'      => $auditLogs,
                'successRate24h' => $jobRunLogModel->successRateLast24h(),
                'jobsToday'      => $jobRunLogModel->countToday(),
                'failedToday'    => $jobRunLogModel->countFailedToday(),
            ],
        ]);
    }
}
