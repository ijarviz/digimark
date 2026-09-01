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
}
