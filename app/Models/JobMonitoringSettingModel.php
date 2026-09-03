<?php

namespace App\Models;

use CodeIgniter\Model;

class JobMonitoringSettingModel extends Model
{
    protected $table         = 'job_monitoring_setting';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'is_enabled',
        'updated_by',
        'updated_at',
    ];

    /**
     * Single-row config table — migration seeds row #1, so a missing row
     * (table wiped, migration skipped) still fails safe as enabled rather
     * than silently disabling every scheduled job.
     */
    public function isEnabled(): bool
    {
        $row = $this->orderBy('id', 'ASC')->first();

        return $row === null || (bool) $row['is_enabled'];
    }

    public function setEnabled(bool $enabled, ?int $updatedBy): void
    {
        $existing = $this->orderBy('id', 'ASC')->first();

        $data = [
            'is_enabled' => $enabled ? 1 : 0,
            'updated_by' => $updatedBy,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->update($existing['id'], $data);
        } else {
            $this->insert($data);
        }
    }
}
