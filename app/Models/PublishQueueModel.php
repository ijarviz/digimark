<?php

namespace App\Models;

use CodeIgniter\Model;

class PublishQueueModel extends Model
{
    protected $table         = 'publish_queue';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useTimestamps = false;

    /** Manual/automatic retries stop being offered past this many attempts. */
    public const MAX_RETRIES = 5;

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
        'retry_count',
        'updated_at',
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
            'updated_at'             => date('Y-m-d H:i:s'),
        ]);
    }

    public function markPublished(int $id, string $igMediaId): void
    {
        $this->update($id, [
            'status'              => 'published',
            'ig_media_id_result'  => $igMediaId,
            'published_at'        => date('Y-m-d H:i:s'),
            'error_message'       => null,
            'updated_at'          => date('Y-m-d H:i:s'),
        ]);
    }

    public function markFailed(int $id, string $errorMessage): void
    {
        $this->update($id, [
            'status'        => 'failed',
            'error_message' => $errorMessage,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    public function deferToNextDay(int $id): void
    {
        $row = $this->find($id);

        $this->update($id, [
            'scheduled_at' => date('Y-m-d H:i:s', strtotime($row['scheduled_at'] . ' +1 day')),
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @throws \RuntimeException if the item has already hit MAX_RETRIES — callers should check canRetry() first to show a friendly message instead.
     */
    public function retry(int $id): void
    {
        $row = $this->find($id);

        if (($row['retry_count'] ?? 0) >= self::MAX_RETRIES) {
            throw new \RuntimeException('Batas maksimum retry (' . self::MAX_RETRIES . 'x) sudah tercapai.');
        }

        $this->update($id, [
            'status'                 => 'pending',
            'container_id'           => null,
            'processing_started_at'  => null,
            'error_message'          => null,
            'scheduled_at'           => date('Y-m-d H:i:s'),
            'retry_count'            => ($row['retry_count'] ?? 0) + 1,
            'updated_at'             => date('Y-m-d H:i:s'),
        ]);
    }

    public function canRetry(array $row): bool
    {
        return $row['status'] === 'failed' && ($row['retry_count'] ?? 0) < self::MAX_RETRIES;
    }
}
