<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Restricts a route to an authenticated session whose role_name is
 * in the list of allowed roles passed as filter arguments, e.g.
 * ['filter' => 'role:admin,content_manager'].
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        if (! $session->get('logged_in')) {
            $session->setFlashdata('error', 'Silakan login terlebih dahulu.');

            return redirect()->to('/login');
        }

        $allowedRoles = $arguments ?? [];

        if ($allowedRoles !== [] && ! in_array($session->get('role_name'), $allowedRoles, true)) {
            return service('response')
                ->setStatusCode(403)
                ->setBody(view('errors/html/error_403', [
                    'message' => 'Anda tidak memiliki akses ke halaman ini.',
                ]));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do.
    }
}
