<?php

namespace App\Models;

use CodeIgniter\Model;

class PublishQueueModel extends Model
{
    protected $table         = 'publish_queue';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'ig_account_id',
        'caption',
        'media_urls',
        'media_type',
        'scheduled_at',
        'status',
        'container_id',
        'processing_started_at',
        'published_at',
        'ig_media_id_result',
        'error_message',
        'created_by',
        'created_at',
    ];

    protected array $casts = [
        'media_urls' => 'json',
    ];

    public function processingItems(): array
    {
        return $this->where('status', 'processing')->findAll();
    }

    public function duePendingItems(): array
    {
        return $this->where('status', 'pending')
            ->where('scheduled_at <=', date('Y-m-d H:i:s'))
            ->orderBy('scheduled_at', 'ASC')
            ->findAll();
    }

    public function countPublishedToday(): int
    {
        $startOfDay = date('Y-m-d 00:00:00');
        $endOfDay   = date('Y-m-d 00:00:00', strtotime('+1 day'));

        return $this->where('status', 'published')
            ->where('published_at >=', $startOfDay)
            ->where('published_at <', $endOfDay)
            ->countAllResults();
    }

    public function markProcessing(int $id, string $containerId): void
    {
        $this->update($id, [
            'status'                 => 'processing',
            'container_id'           => $containerId,
            'processing_started_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function markPublished(int $id, string $igMediaId): void
    {
        $this->update($id, [
            'status'              => 'published',
            'ig_media_id_result'  => $igMediaId,
            'published_at'        => date('Y-m-d H:i:s'),
            'error_message'       => null,
        ]);
    }

    public function markFailed(int $id, string $errorMessage): void
    {
        $this->update($id, [
            'status'        => 'failed',
            'error_message' => $errorMessage,
        ]);
    }

    public function deferToNextDay(int $id): void
    {
        $row = $this->find($id);

        $this->update($id, [
            'scheduled_at' => date('Y-m-d H:i:s', strtotime($row['scheduled_at'] . ' +1 day')),
        ]);
    }

    public function retry(int $id): void
    {
        $this->update($id, [
            'status'                 => 'pending',
            'container_id'           => null,
            'processing_started_at'  => null,
            'error_message'          => null,
            'scheduled_at'           => date('Y-m-d H:i:s'),
        ]);
    }
}
