<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\AuditLogModel;
use App\Models\JobMonitoringSettingModel;
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
                'jobLogs'           => $jobLogs,
                'auditLogs'         => $auditLogs,
                'successRate24h'    => $jobRunLogModel->successRateLast24h(),
                'jobsToday'         => $jobRunLogModel->countToday(),
                'failedToday'       => $jobRunLogModel->countFailedToday(),
                'monitoringEnabled' => (new JobMonitoringSettingModel())->isEnabled(),
            ],
        ]);
    }

    public function toggleMonitoring()
    {
        $settingModel = new JobMonitoringSettingModel();
        $newState     = ! $settingModel->isEnabled();

        $settingModel->setEnabled($newState, session()->get('user_id'));

        (new AuditLogger())->log(
            session()->get('user_id'),
            $newState ? 'enable_job_monitoring' : 'disable_job_monitoring',
            'job_monitoring_setting'
        );

        session()->setFlashdata('success', $newState
            ? 'Job monitoring diaktifkan — snapshot/publish job berjalan seperti biasa.'
            : 'Job monitoring dinonaktifkan — snapshot/publish job akan dilewati sampai diaktifkan lagi.');

        return redirect()->to('/admin/job-logs');
    }
}
