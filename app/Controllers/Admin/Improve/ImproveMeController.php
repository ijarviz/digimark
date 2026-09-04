<?php

namespace App\Controllers\Admin\Improve;

use App\Controllers\BaseController;
use App\Libraries\AuditLogger;
use App\Libraries\Improve\ImproveMeService;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

/**
 * "Improve Me" (Jarvis Power menu) — admin-only. This can trigger a live
 * code-modifying cloud agent against this repo, so besides the route
 * filter (`role:admin`) the role is re-checked here, per the CLAUDE.md
 * rule that anything sensitive must enforce the role in the controller
 * too. Ported from automedia/app/Controllers/Admin/Improve/ImproveMeController.php.
 */
class ImproveMeController extends BaseController
{
    private function guard(): void
    {
        if (session()->get('role_name') !== 'admin') {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    public function index()
    {
        $this->guard();

        return view('layouts/main', [
            'title'       => 'Improve Me',
            'subtitle'    => 'Kirim permintaan perbaikan/pengembangan codebase ke AI — dikerjakan di branch baru sebagai Pull Request.',
            'activeNav'   => 'admin-improve-me',
            'contentView' => 'admin/improve_me/index',
            'contentData' => [
                'requests' => (new ImproveMeService())->recentRequests(50),
            ],
        ]);
    }

    public function submit()
    {
        $this->guard();

        $prompt = (string) $this->request->getPost('prompt');

        try {
            $request = (new ImproveMeService())->submit($prompt, (int) session()->get('user_id'));
        } catch (RuntimeException $e) {
            return redirect()->to('/admin/improve-me')->with('error', $e->getMessage());
        }

        (new AuditLogger())->log(
            (int) session()->get('user_id'),
            'submit_improve_me',
            'ai_improve_request',
            isset($request['id']) ? (int) $request['id'] : null,
            ['github_issue_number' => $request['github_issue_number'] ?? null]
        );

        return redirect()->to('/admin/improve-me')->with(
            'success',
            "Permintaan terkirim sebagai GitHub issue #{$request['github_issue_number']}. AI akan memproses dan membuka Pull Request untuk direview."
        );
    }
}
