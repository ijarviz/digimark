<?php

namespace App\Models;

use CodeIgniter\Model;

class JobRunLogModel extends Model
{
    protected $table         = 'job_run_log';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'job_name',
        'status',
        'started_at',
        'finished_at',
        'error_message',
    ];

    /**
     * % of runs with status=success in the last 24h. Null when there were
     * no runs at all in that window (not the same as 0%).
     */
    public function successRateLast24h(): ?float
    {
        $since = date('Y-m-d H:i:s', strtotime('-24 hours'));
        $total = $this->where('started_at >=', $since)->countAllResults();

        if ($total === 0) {
            return null;
        }

        $success = $this->where('started_at >=', $since)->where('status', 'success')->countAllResults();

        return round(($success / $total) * 100, 1);
    }

    public function countToday(): int
    {
        return $this->where('started_at >=', date('Y-m-d 00:00:00'))->countAllResults();
    }

    public function countFailedToday(): int
    {
        return $this->where('started_at >=', date('Y-m-d 00:00:00'))->where('status', 'failed')->countAllResults();
    }
}
