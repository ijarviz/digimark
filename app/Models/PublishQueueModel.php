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

    /** Only a still-queued item can be edited or cancelled. */
    public function canEdit(array $row): bool
    {
        return $row['status'] === 'pending';
    }

    /**
     * History rows joined to the latest daily insight snapshot for each
     * published item (via ig_media_id_result -> ig_content -> the newest
     * ig_content_insight_daily). Non-published rows and posts not yet
     * snapshotted come back with null metric fields.
     *
     * @return list<array<string, mixed>>
     */
    public function historyWithInsights(int $limit = 100): array
    {
        return $this->db->table('publish_queue q')
            ->select('q.*,
                      i.reach AS m_reach, i.impressions AS m_impressions,
                      i.likes AS m_likes, i.comments AS m_comments,
                      i.saves AS m_saves, i.shares AS m_shares,
                      i.snapshot_date AS m_snapshot_date,
                      c.permalink AS m_permalink')
            ->join('ig_content c', 'c.ig_media_id = q.ig_media_id_result', 'left')
            ->join(
                '(SELECT d1.* FROM ig_content_insight_daily d1
                  JOIN (SELECT ig_content_id, MAX(snapshot_date) AS mx
                        FROM ig_content_insight_daily GROUP BY ig_content_id) d2
                    ON d2.ig_content_id = d1.ig_content_id AND d2.mx = d1.snapshot_date) i',
                'i.ig_content_id = c.id',
                'left'
            )
            ->orderBy('q.created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Every queue item whose scheduled_at OR published_at falls inside the
     * given month (Y-m-01 .. last day), for the calendar grid.
     *
     * @return list<array<string, mixed>>
     */
    public function itemsForMonth(string $monthStart, string $monthEnd): array
    {
        return $this->groupStart()
            ->where('scheduled_at >=', $monthStart)->where('scheduled_at <=', $monthEnd . ' 23:59:59')
            ->groupEnd()
            ->orGroupStart()
            ->where('published_at >=', $monthStart)->where('published_at <=', $monthEnd . ' 23:59:59')
            ->groupEnd()
            ->orderBy('scheduled_at', 'ASC')
            ->findAll();
    }

    public function cancel(int $id): void
    {
        $this->update($id, ['status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * @param array{caption: ?string, media_urls: array<int, string>, media_type: string, scheduled_at: string} $data
     */
    public function updatePending(int $id, array $data): void
    {
        $this->update($id, [
            'caption'      => $data['media_type'] === 'story' ? null : $data['caption'],
            'media_urls'   => $data['media_urls'],
            'media_type'   => $data['media_type'],
            'scheduled_at' => $data['scheduled_at'],
            'updated_at'   => date('Y-m-d H:i:s'),
        ]);
    }
}
