<?php

namespace App\Controllers\Instagram;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Models\IgAccountModel;
use App\Models\PublishQueueModel;

/**
 * Compose/schedule/history for Instagram publishing. Route group already
 * restricts this to role:admin,content_manager; requireCanPublish() below
 * is defense-in-depth in case routing is ever misconfigured.
 */
class PublishController extends BaseController
{
    private const MEDIA_TYPES = ['image', 'carousel', 'reels', 'story'];

    public function index()
    {
        $this->requireCanPublish();

        $items = (new PublishQueueModel())->orderBy('created_at', 'DESC')->findAll(100);

        return view('layouts/main', [
            'title'       => 'Riwayat Publish Instagram',
            'activeNav'   => 'ig-publish',
            'contentView' => 'instagram/publish_index',
            'contentData' => ['items' => $items],
        ]);
    }

    public function new()
    {
        $this->requireCanPublish();

        return view('layouts/main', [
            'title'       => 'Compose Instagram',
            'activeNav'   => 'ig-publish',
            'contentView' => 'instagram/publish_form',
            'contentData' => [],
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

        $mediaType = $this->request->getPost('media_type');
        $urls      = array_values(array_filter((array) $this->request->getPost('media_urls'), static fn ($url) => trim((string) $url) !== ''));
        $caption   = $this->request->getPost('caption');
        $publishNow = $this->request->getPost('publish_now') === '1';
        $scheduledAt = $publishNow ? date('Y-m-d H:i:s') : $this->request->getPost('scheduled_at');

        if (! in_array($mediaType, self::MEDIA_TYPES, true) || $urls === [] || ! $scheduledAt) {
            session()->setFlashdata('error', 'Tipe media, minimal satu URL media, dan jadwal wajib diisi.');

            return redirect()->to('/publish/new')->withInput();
        }

        foreach ($urls as $url) {
            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                session()->setFlashdata('error', "URL tidak valid: {$url}");

                return redirect()->to('/publish/new')->withInput();
            }
        }

        // Carousel needs 2-10 items (Instagram's own limit); every other
        // type is exactly one media item — previously any count silently
        // passed and only failed later, deep inside the Graph API call.
        $urlCountError = match (true) {
            $mediaType === 'carousel' && (count($urls) < 2 || count($urls) > 10) => 'Carousel butuh 2-10 URL media.',
            $mediaType !== 'carousel' && count($urls) !== 1                       => ucfirst($mediaType) . ' hanya boleh 1 URL media.',
            default                                                              => null,
        };

        if ($urlCountError) {
            session()->setFlashdata('error', $urlCountError);

            return redirect()->to('/publish/new')->withInput();
        }

        if (! $publishNow && strtotime($scheduledAt) === false) {
            session()->setFlashdata('error', 'Format jadwal tidak valid.');

            return redirect()->to('/publish/new')->withInput();
        }

        $queueModel = new PublishQueueModel();

        $id = $queueModel->insert([
            'ig_account_id' => $account['id'],
            'caption'       => $mediaType === 'story' ? null : $caption,
            'media_urls'    => $urls,
            'media_type'    => $mediaType,
            'scheduled_at'  => date('Y-m-d H:i:s', strtotime($scheduledAt)),
            'status'        => 'pending',
            'created_by'    => session()->get('user_id'),
            'created_at'    => date('Y-m-d H:i:s'),
        ]);

        (new AuditLogger())->log(session()->get('user_id'), 'schedule_content', 'publish_queue', $id, ['media_type' => $mediaType]);

        session()->setFlashdata('success', 'Item berhasil ditambahkan ke antrian publish.');

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

    private function requireCanPublish(): void
    {
        if (! in_array(session()->get('role_name'), ['admin', 'content_manager'], true)) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException();
        }
    }
}
