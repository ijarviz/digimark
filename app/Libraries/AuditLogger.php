<?php

namespace App\Libraries;

use App\Models\AuditLogModel;

/**
 * Records who did what, when, for actions that need an audit trail
 * (login, connect_account, add tiktok_link, ...).
 */
class AuditLogger
{
    protected AuditLogModel $model;

    public function __construct()
    {
        $this->model = new AuditLogModel();
    }

    public function log(?int $userId, string $action, ?string $entityType = null, ?int $entityId = null, array $meta = []): void
    {
        $this->model->insert([
            'user_id'     => $userId,
            'action'      => $action,
            'entity_type' => $entityType,
            'entity_id'   => $entityId,
            'meta'        => $meta === [] ? null : json_encode($meta),
            'created_at'  => date('Y-m-d H:i:s'),
        ]);
    }
}
