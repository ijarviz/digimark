<?php

namespace App\Controllers\Instagram;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\IgAccountModel;
use App\Models\PublishQueueModel;

/**
 * Compose/schedule/edit/history for Instagram publishing, plus a month
 * calendar of scheduled + published posts. Route group already restricts
 * this to role:admin,content_manager; requireCanPublish() below is
 * defense-in-depth in case routing is ever misconfigured.
 */
class PublishController extends BaseController
{
    private const MEDIA_TYPES = ['image', 'carousel', 'reels', 'story'];

    public function index()
    {
        $this->requireCanPublish();

        return view('layouts/main', [
            'title'       => 'Riwayat Publish Instagram',
            'subtitle'    => 'Antrian, jadwal, dan performa konten yang sudah tayang.',
            'activeNav'   => 'ig-publish',
            'contentView' => 'instagram/publish_index',
            'contentData' => [
                'items' => (new PublishQueueModel())->historyWithInsights(100),
            ],
        ]);
    }

    public function calendar()
    {
        $this->requireCanPublish();

        $month = (string) $this->request->getGet('month');
        if (! preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $monthStart = $month . '-01';
        $monthEnd   = date('Y-m-t', strtotime($monthStart));

        $itemsByDay = [];
        foreach ((new PublishQueueModel())->itemsForMonth($monthStart, $monthEnd) as $item) {
            $anchor = $item['status'] === 'published' && $item['published_at']
                ? $item['published_at']
                : $item['scheduled_at'];
            $day = substr((string) $anchor, 0, 10);
            $itemsByDay[$day][] = $item;
        }

        return view('layouts/main', [
            'title'       => 'Kalender Publish Instagram',
            'subtitle'    => 'Jadwal & riwayat tayang per tanggal.',
            'activeNav'   => 'ig-publish',
            'contentView' => 'instagram/publish_calendar',
            'contentData' => [
                'month'      => $month,
                'monthStart' => $monthStart,
                'monthEnd'   => $monthEnd,
                'prevMonth'  => date('Y-m', strtotime($monthStart . ' -1 month')),
                'nextMonth'  => date('Y-m', strtotime($monthStart . ' +1 month')),
                'itemsByDay' => $itemsByDay,
            ],
        ]);
    }

    public function new()
    {
        $this->requireCanPublish();

        return view('layouts/main', [
            'title'       => 'Compose Instagram',
            'activeNav'   => 'ig-publish',
            'contentView' => 'instagram/publish_form',
            'contentData' => ['mode' => 'create', 'item' => null],
        ]);
    }

    public function create()
    {
        $this->requireCanPublish();

        $account = (new IgAccountModel())->getActiveAccount();

        if (! $account) {
            session()->setFlashdata('error', 'Hubungkan akun Instagram terlebih dahulu.');

            return redirect()->to('/publish/new')->withInput();
        }

        $parsed = $this->parsePayload();

        if (is_string($parsed)) {
            session()->setFlashdata('error', $parsed);

            return redirect()->to('/publish/new')->withInput();
        }

        $id = (new PublishQueueModel())->insert([
            'ig_account_id' => $account['id'],
            'caption'       => $parsed['media_type'] === 'story' ? null : $parsed['caption'],
            'media_urls'    => $parsed['media_urls'],
            'media_type'    => $parsed['media_type'],
            'scheduled_at'  => $parsed['scheduled_at'],
            'status'        => 'pending',
            'created_by'    => session()->get('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        (new AuditLogger())->log(session()->get('user_id'), 'schedule_content', 'publish_queue', $id, ['media_type' => $parsed['media_type']]);

        session()->setFlashdata('success', 'Item berhasil ditambahkan ke antrian publish.');

        return redirect()->to('/publish');
    }

    public function edit(int $id)
    {
        $this->requireCanPublish();

        $queueModel = new PublishQueueModel();
        $item       = $queueModel->find($id);

        if (! $item || ! $queueModel->canEdit($item)) {
            session()->setFlashdata('error', 'Item tidak ditemukan atau sudah tidak bisa diedit (bukan status pending).');

            return redirect()->to('/publish');
        }

        return view('layouts/main', [
            'title'       => 'Edit Item Publish',
            'activeNav'   => 'ig-publish',
            'contentView' => 'instagram/publish_form',
            'contentData' => ['mode' => 'edit', 'item' => $item],
        ]);
    }

    public function update(int $id)
    {
        $this->requireCanPublish();

        $queueModel = new PublishQueueModel();
        $item       = $queueModel->find($id);

        if (! $item || ! $queueModel->canEdit($item)) {
            session()->setFlashdata('error', 'Item tidak ditemukan atau sudah tidak bisa diedit (bukan status pending).');

            return redirect()->to('/publish');
        }

        $parsed = $this->parsePayload();

        if (is_string($parsed)) {
            session()->setFlashdata('error', $parsed);

            return redirect()->to('/publish/' . $id . '/edit')->withInput();
        }

        $queueModel->updatePending($id, $parsed);
        (new AuditLogger())->log(session()->get('user_id'), 'edit_publish', 'publish_queue', $id, ['media_type' => $parsed['media_type']]);

        session()->setFlashdata('success', 'Item antrian berhasil diperbarui.');

        return redirect()->to('/publish');
    }

    public function cancel(int $id)
    {
        $this->requireCanPublish();

        $queueModel = new PublishQueueModel();
        $item       = $queueModel->find($id);

        if (! $item || ! $queueModel->canEdit($item)) {
            session()->setFlashdata('error', 'Hanya item berstatus pending yang bisa dibatalkan.');

            return redirect()->to('/publish');
        }

        $queueModel->cancel($id);
        (new AuditLogger())->log(session()->get('user_id'), 'cancel_publish', 'publish_queue', $id);

        session()->setFlashdata('success', 'Item antrian dibatalkan.');

        return redirect()->to('/publish');
    }

    public function retry(int $id)
    {
        $this->requireCanPublish();

        $queueModel = new PublishQueueModel();
        $item       = $queueModel->find($id);

        if (! $item || ! $queueModel->canRetry($item)) {
            session()->setFlashdata('error', 'Item tidak ditemukan, bukan status gagal, atau sudah mencapai batas retry.');

            return redirect()->to('/publish');
        }

        $queueModel->retry($id);
        (new AuditLogger())->log(session()->get('user_id'), 'retry_publish', 'publish_queue', $id);

        session()->setFlashdata('success', 'Item dijadwalkan ulang untuk diproses.');

        return redirect()->to('/publish');
    }

    /**
     * Shared parse+validate for create and update. Returns the normalized
     * fields on success, or a human-readable Indonesian error string on
     * failure (callers flash it and redirect back with input).
     *
     * @return array{caption: ?string, media_urls: array<int, string>, media_type: string, scheduled_at: string}|string
     */
    private function parsePayload(): array|string
    {
        $mediaType   = (string) $this->request->getPost('media_type');
        $urls        = array_values(array_filter((array) $this->request->getPost('media_urls'), static fn ($url) => trim((string) $url) !== ''));
        $caption     = $this->request->getPost('caption');
        $publishNow  = $this->request->getPost('publish_now') === '1';
        $scheduledAt = $publishNow ? date('Y-m-d H:i:s') : (string) $this->request->getPost('scheduled_at');

        if (! in_array($mediaType, self::MEDIA_TYPES, true) || $urls === [] || $scheduledAt === '') {
            return 'Tipe media, minimal satu URL media, dan jadwal wajib diisi.';
        }

        foreach ($urls as $url) {
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                return "URL tidak valid: {$url}";
            }
        }

        // Carousel needs 2-10 items (Instagram's own limit); every other
        // type is exactly one media item.
        $countError = match (true) {
            $mediaType === 'carousel' && (count($urls) < 2 || count($urls) > 10) => 'Carousel butuh 2-10 URL media.',
            $mediaType !== 'carousel' && count($urls) !== 1                       => ucfirst($mediaType) . ' hanya boleh 1 URL media.',
            default                                                              => null,
        };

        if ($countError !== null) {
            return $countError;
        }

        if (strtotime($scheduledAt) === false) {
            return 'Format jadwal tidak valid.';
        }

        return [
            'caption'      => $caption !== null ? (string) $caption : null,
            'media_urls'   => $urls,
            'media_type'   => $mediaType,
            'scheduled_at' => date('Y-m-d H:i:s', strtotime($scheduledAt)),
        ];
    }

    private function requireCanPublish(): void
    {
        if (! in_array(session()->get('role_name'), ['admin', 'content_manager'], true)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }
}
