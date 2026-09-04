<?php

namespace App\Libraries\Improve;

use App\Models\AiImproveRequestModel;
use Config\Github as GithubConfig;
use Config\Services;
use RuntimeException;
use Throwable;

/**
 * Bridges an admin-submitted prompt to the "Digimark Improve Me" claude.ai
 * cloud routine by opening a GitHub issue labeled `improve-me` on the
 * project repo — a webhook on that routine fires on every new issue, and
 * the routine re-verifies the label itself before doing anything. The
 * routine always works on a new branch and opens a PR (never pushing to
 * main directly), then comments the result back on the issue.
 *
 * The routine has no way to call back into this app, so `recentRequests()`
 * polls each non-terminal request's GitHub issue on every admin page load:
 * labels (`improve-me:in-progress` / `improve-me:done`) drive the displayed
 * status, and once done, the issue's final comment (the routine always
 * posts one, whether it opened a PR or declined) is cached as the "hasil"
 * shown in the table. Cheap enough at this feature's real usage (an admin
 * occasionally submitting a prompt) to do inline rather than via a queue.
 *
 * Ported from automedia/app/Services/Improve/ImproveMeService.php — Guzzle
 * swapped for CI4's CURLRequest (no guzzle dependency here) and the
 * repository layer collapsed into the model.
 */
class ImproveMeService
{
    private AiImproveRequestModel $requests;
    private GithubConfig $config;

    public function __construct(?AiImproveRequestModel $requests = null, ?GithubConfig $config = null)
    {
        $this->requests = $requests ?? new AiImproveRequestModel();
        $this->config   = $config ?? new GithubConfig();
    }

    /**
     * @return array<string, mixed>
     */
    public function submit(string $prompt, ?int $requestedBy): array
    {
        $prompt = trim($prompt);

        if ($prompt === '') {
            throw new RuntimeException('Prompt tidak boleh kosong.');
        }

        if ($this->config->token === '' || $this->config->repo === '') {
            throw new RuntimeException('GitHub belum dikonfigurasi (github.token / github.repo di .env).');
        }

        $this->ensureLabelExists();

        $title = mb_substr((string) preg_replace('/\s+/', ' ', $prompt), 0, 80);

        try {
            [$status, $issue] = $this->github('POST', "repos/{$this->config->repo}/issues", [
                'title'  => $title,
                'body'   => "Prompt ini dikirim melalui halaman **Improve Me** (Jarvis Power) di admin panel Digimark.\n\n---\n\n" . $prompt,
                'labels' => ['improve-me'],
            ]);

            if ($status < 200 || $status >= 300 || ! isset($issue['number'])) {
                throw new RuntimeException('GitHub menolak pembuatan issue (HTTP ' . $status . '): ' . ($issue['message'] ?? 'unknown'));
            }

            $id = $this->requests->insert([
                'prompt'              => $prompt,
                'status'              => 'submitted',
                'github_issue_number' => $issue['number'],
                'github_issue_url'    => $issue['html_url'] ?? null,
                'requested_by'        => $requestedBy,
            ], true);

            return $this->requests->find((int) $id);
        } catch (Throwable $e) {
            $this->requests->insert([
                'prompt'        => $prompt,
                'status'        => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 500),
                'requested_by'  => $requestedBy,
            ]);

            throw new RuntimeException('Gagal membuat GitHub issue: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function recentRequests(int $limit = 50): array
    {
        $rows = $this->requests->recentForAdmin($limit);

        if ($this->config->token === '' || $this->config->repo === '') {
            return $rows;
        }

        $needsSync = static fn (array $row): bool => in_array($row['status'], ['submitted', 'in_progress'], true)
            || ($row['status'] === 'done' && ($row['result_comment'] ?? null) === null);

        return array_map(
            fn (array $row): array => $needsSync($row) && $row['github_issue_number'] !== null
                ? $this->refreshFromGithub($row)
                : $row,
            $rows
        );
    }

    /**
     * Best-effort: the label must exist before the first issue is created
     * or the routine has nothing to match against. A 422 here just means it
     * already exists, which is fine.
     */
    private function ensureLabelExists(): void
    {
        try {
            $this->github('POST', "repos/{$this->config->repo}/labels", [
                'name'        => 'improve-me',
                'color'       => '5319e7',
                'description' => 'Triggers the Digimark Improve Me cloud routine',
            ]);
        } catch (Throwable) {
            // Already exists (422) or any other non-fatal issue — the
            // issue-creation call surfaces a real error if the label
            // genuinely can't be attached.
        }
    }

    /**
     * Reads the issue's current labels to derive a real status, and — only
     * once it lands on `done` — fetches the routine's final comment (its PR
     * link, or its explanation for declining) exactly once, then caches it
     * so later page loads don't re-fetch.
     *
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function refreshFromGithub(array $row): array
    {
        try {
            [$status, $issue] = $this->github('GET', "repos/{$this->config->repo}/issues/{$row['github_issue_number']}");

            if ($status < 200 || $status >= 300) {
                return $row;
            }
        } catch (Throwable) {
            return $row;
        }

        $labels = array_column($issue['labels'] ?? [], 'name');

        $newStatus = match (true) {
            in_array('improve-me:done', $labels, true)        => 'done',
            in_array('improve-me:in-progress', $labels, true) => 'in_progress',
            default                                           => 'submitted',
        };

        $update = ['status' => $newStatus];

        if ($newStatus === 'done') {
            try {
                [$cStatus, $comments] = $this->github('GET', "repos/{$this->config->repo}/issues/{$row['github_issue_number']}/comments");
                $last = ($cStatus >= 200 && $cStatus < 300 && is_array($comments)) ? end($comments) : false;

                if ($last !== false && isset($last['body'])) {
                    $update['result_comment'] = mb_substr($last['body'], 0, 2000);

                    if (preg_match('#https://github\.com/[\w.-]+/[\w.-]+/pull/\d+#', $last['body'], $m) === 1) {
                        $update['result_pr_url'] = $m[0];
                    }
                }
            } catch (Throwable) {
                // Status still updates to 'done' from the label even if the
                // comment fetch fails — next load retries since
                // result_comment stays null.
            }
        }

        $this->requests->update((int) $row['id'], $update);

        return array_merge($row, $update);
    }

    /**
     * One GitHub REST call via CI4's CURLRequest.
     *
     * @param array<string, mixed>|null $json
     * @return array{0:int,1:array<mixed>} [status code, decoded body]
     */
    private function github(string $method, string $path, ?array $json = null): array
    {
        $options = [
            'headers' => [
                'Authorization'        => 'Bearer ' . $this->config->token,
                'Accept'               => 'application/vnd.github+json',
                'X-GitHub-Api-Version' => '2022-11-28',
                'User-Agent'           => 'Digimark-ImproveMe',
            ],
            'http_errors' => false,
            'timeout'     => 15,
        ];

        if ($json !== null) {
            $options['json'] = $json;
        }

        $client   = Services::curlrequest([], null, null, false);
        $response = $client->request($method, 'https://api.github.com/' . $path, $options);

        $body = json_decode((string) $response->getBody(), true);

        return [$response->getStatusCode(), is_array($body) ? $body : []];
    }
}
