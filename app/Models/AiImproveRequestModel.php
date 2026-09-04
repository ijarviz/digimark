<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Backs the "Improve Me" (Jarvis Power) admin page. Ported from automedia's
 * AiImproveRequestModel — same shape, minus the repository wrapper (digimark
 * controllers/services use models directly).
 */
class AiImproveRequestModel extends Model
{
    protected $table         = 'ai_improve_requests';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $allowedFields  = [
        'prompt', 'status', 'github_issue_number', 'github_issue_url',
        'error_message', 'requested_by', 'result_comment', 'result_pr_url',
    ];
    protected $useTimestamps = true;

    /**
     * @return list<array<string, mixed>>
     */
    public function recentForAdmin(int $limit = 50): array
    {
        return $this
            ->select('ai_improve_requests.*, users.name AS requested_by_name')
            ->join('users', 'users.id = ai_improve_requests.requested_by', 'left')
            ->orderBy('ai_improve_requests.id', 'DESC')
            ->findAll($limit);
    }
}
