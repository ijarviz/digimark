<?php

namespace App\Commands;

use App\Models\JobMonitoringSettingModel;
use App\Models\JobRunLogModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Throwable;

/**
 * Standardizes job_run_log start/finish/error recording so every
 * snapshot/refresh Spark command gets identical logging behavior
 * without repeating the boilerplate in each subclass.
 *
 * Subclasses implement handleJob() and return:
 *   ['status' => 'success'|'partial'|'failed', 'error' => ?string, 'processed' => int, 'failed' => int]
 */
abstract class BaseSnapshotCommand extends BaseCommand
{
    protected $group = 'snapshot';

    abstract protected function jobName(): string;

    /**
     * @return array{status: string, error: string|null, processed: int, failed: int}
     */
    abstract protected function handleJob(array $params): array;

    final public function run(array $params)
    {
        $jobName = $this->jobName();

        if (! (new JobMonitoringSettingModel())->isEnabled()) {
            CLI::write("Job monitoring dinonaktifkan — job '{$jobName}' dilewati.", 'yellow');

            return;
        }

        $startedAt = date('Y-m-d H:i:s');

        CLI::write("Starting job: {$jobName}", 'yellow');

        $status  = 'failed';
        $error   = null;
        $summary = ['processed' => 0, 'failed' => 0];

        try {
            $result   = $this->handleJob($params);
            $status   = $result['status'] ?? 'failed';
            $error    = $result['error'] ?? null;
            $summary  = [
                'processed' => $result['processed'] ?? 0,
                'failed'    => $result['failed'] ?? 0,
            ];
        } catch (Throwable $e) {
            $status = 'failed';
            $error  = $e->getMessage();
            CLI::error("Job {$jobName} crashed: {$error}");
            log_message('error', "[{$jobName}] {$e}");
        }

        (new JobRunLogModel())->insert([
            'job_name'      => $jobName,
            'status'        => $status,
            'started_at'    => $startedAt,
            'finished_at'   => date('Y-m-d H:i:s'),
            'error_message' => $error,
        ]);

        $color = $status === 'success' ? 'green' : ($status === 'partial' ? 'yellow' : 'red');
        CLI::write(
            "Finished job: {$jobName} — status={$status}, processed={$summary['processed']}, failed={$summary['failed']}",
            $color
        );
    }
}
