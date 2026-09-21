<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $loginData = $session->get('LoginData');

        if (empty($loginData) || empty($loginData['ID_USER'])) {
            if ($request->isAJAX()) {
                return service('response')
                    ->setStatusCode(401)
                    ->setBody('<script>window.location.href = "' . base_url('user/login') . '";</script>');
            }
            return redirect()->to(base_url('user/login'));
        }

        // Check level access if arguments provided
        if (!empty($arguments)) {
            $userRole = $loginData['JENIS_USER'] ?? '';
            // arguments can be comma-separated or array
            $allowedRoles = [];
            foreach ($arguments as $arg) {
                $roles = array_map('trim', explode(',', $arg));
                $allowedRoles = array_merge($allowedRoles, $roles);
            }

            if (!in_array($userRole, $allowedRoles)) {
                if ($request->isAJAX()) {
                    return service('response')
                        ->setStatusCode(403)
                        ->setBody('Akses Ditolak: Anda tidak memiliki izin untuk tindakan ini.');
                }
                return redirect()->to(base_url('dashboards/main'))->with('error', 'Akses Ditolak: Anda tidak memiliki izin untuk halaman tersebut.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after request
    }
}
