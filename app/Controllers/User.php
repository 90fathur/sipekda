<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserRoleModel;
use App\Models\UserLogModel;
use App\Models\SkpdModel;

class User extends BaseController
{
    protected UserModel $userModel;
    protected UserRoleModel $userRoleModel;
    protected UserLogModel $userLogModel;
    protected SkpdModel $skpdModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->userRoleModel = new UserRoleModel();
        $this->userLogModel = new UserLogModel();
        $this->skpdModel = new SkpdModel();
        helper(['url', 'form', 'menu']);
    }

    public function login()
    {
        $session = session();
        if ($session->get('LoginData')) {
            $this->logActivity(2, 'LOGOUT');
            $session->remove('LoginData');
            $session->remove('Token');
        }

        $data = [
            'Header' => 'SPPD Online',
            'Title'  => 'Login'
        ];
        return view('user/login', $data);
    }

    public function menuLoginUser()
    {
        return view('user/_menu_login_user');
    }

    public function keyGenerator()
    {
        // Return dummy RSA XML key format if requested by legacy frontend script
        return '<RSAKeyValue><Modulus>s7XzF0=</Modulus><Exponent>AQAB</Exponent></RSAKeyValue>|dummy_private_key';
    }

    public function validasiLogin()
    {
        $request = $this->request;

        $username = $request->getPost('USER_NAME') ?? $request->getPost('CryptUSER_NAME') ?? $request->getPost('username') ?? $request->getPost('Username');
        $password = $request->getPost('PASSWORD') ?? $request->getPost('CryptPASSWORD') ?? $request->getPost('password') ?? $request->getPost('Password');

        if (empty($username) || empty($password)) {
            return $this->response->setBody('9');
        }

        // Search user with joined SKPD
        $db = \Config\Database::connect();
        $user = $db->table('tb_users u')
            ->select('u.*, mk.NM_SKPD')
            ->join('ms_skpd mk', 'u.KD_UNITKER = mk.KD_SKPD', 'left')
            ->where('u.USER_NAME', $username)
            ->get()
            ->getRowArray();

        if (!$user) {
            return $this->response->setBody('9');
        }

        // Check if account is blocked
        if (!empty($user['JAM_BLOCK'])) {
            $blockTime = strtotime($user['JAM_BLOCK']);
            if ($blockTime > time()) {
                return $this->response->setBody('6'); // Blocked, try again in 5 minutes
            } else {
                // Unblock
                $this->userModel->update($user['ID_USER'], [
                    'STS_BLOCK' => 0,
                    'JAM_BLOCK' => null
                ]);
            }
        }

        // Verify SHA-512 password
        $hashedInput = strtolower(hash('sha512', $password));
        if ($user['PASSWORD'] !== $hashedInput) {
            // Increment block attempts
            $attempts = (int)session()->get('login_attempts_' . $username) + 1;
            session()->set('login_attempts_' . $username, $attempts);

            if ($attempts >= 3) {
                $this->userModel->update($user['ID_USER'], [
                    'STS_BLOCK' => 1,
                    'JAM_BLOCK' => date('Y-m-d H:i:s', strtotime('+5 minutes'))
                ]);
                session()->remove('login_attempts_' . $username);
                return $this->response->setBody('6');
            }
            return $this->response->setBody('9');
        }

        // Check if active
        if ((int)$user['AKTIF'] !== 1) {
            return $this->response->setBody('8'); // User inactive/disabled
        }

        // Clear attempts
        session()->remove('login_attempts_' . $username);

        // Fetch last login from log
        $lastLog = $this->userLogModel
            ->where('NAMA_USER', $username)
            ->where('STATUS', 1)
            ->orderBy('TANGGAL_JAM', 'DESC')
            ->first();

        $lastLoginDate = $lastLog['TANGGAL_JAM'] ?? date('Y-m-d H:i:s');

        $loginData = [
            'ID_USER'      => $user['ID_USER'],
            'USER_NAME'    => $user['USER_NAME'],
            'NAMA_LENGKAP' => $user['NAMA_LENGKAP'],
            'JENIS_USER'   => $user['JENIS_USER'],
            'AKTIF'        => (bool)$user['AKTIF'],
            'KD_UNITKER'   => $user['KD_UNITKER'],
            'NM_UNITKER'   => $user['NM_SKPD'] ?? '-',
            'LAST_LOGIN'   => $lastLoginDate
        ];

        session()->set('LoginData', $loginData);
        $this->logActivity(1, 'LOGIN');

        // Check if default weak password
        if ($password === '12345') {
            return $this->response->setBody('7');
        }

        // Determine destination redirect
        if ($user['JENIS_USER'] === 'Admin') {
            return $this->response->setBody(base_url('user/userhome'));
        }

        return $this->response->setBody(base_url('dashboards/main'));
    }

    public function logout()
    {
        $this->logActivity(2, 'LOGOUT');
        session()->remove('LoginData');
        session()->remove('Token');
        return redirect()->to(base_url('user/login'));
    }

    public function gantiPassword()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return redirect()->to(base_url('user/login'));
        }

        return view('user/ganti_password', [
            'Title' => 'Ganti Password',
            'LoginData' => $loginData
        ]);
    }

    public function validasiPassword()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return $this->response->setBody('Anda Sehat..????');
        }

        $oldPassword = $this->request->getPost('OldPassword');
        $newPassword = $this->request->getPost('NewPassword');
        $confirmPassword = $this->request->getPost('KonfirmasiPassword');

        if (strlen($newPassword) < 6) {
            return $this->response->setBody('4'); // Length < 6
        }

        // Must be alphanumeric and not 'assami'
        if (!preg_match('/^(?=[^\s]*?[0-9])(?=[^\s]*?[a-zA-Z])[a-zA-Z0-9\!@#$%^&*()]*$/', $newPassword) || $newPassword === 'assami') {
            return $this->response->setBody('5');
        }

        if ($newPassword !== $confirmPassword) {
            return $this->response->setBody('8'); // Mismatch confirm
        }

        $user = $this->userModel->find($loginData['ID_USER']);
        if (!$user) {
            return $this->response->setBody('9');
        }

        if (strtolower(hash('sha512', $oldPassword)) !== $user['PASSWORD']) {
            return $this->response->setBody('7'); // Old password incorrect
        }

        if ($oldPassword === $newPassword) {
            return $this->response->setBody('6'); // Cannot be identical to old
        }

        $this->userModel->update($user['ID_USER'], [
            'PASSWORD' => strtolower(hash('sha512', $newPassword))
        ]);

        $this->logActivity(7, 'CHANGE PASSWORD : ' . json_encode($user));

        return $this->response->setBody('0');
    }

    public function uploadImage()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (empty($loginData)) {
            return 'ERR';
        }

        $file = $this->request->getFile('FILE_IMAGE');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['png', 'jpg', 'jpeg', 'bmp'])) {
                $targetDir = FCPATH . 'uploads/images/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $filename = $loginData['ID_USER'] . '.png';
                $file->move($targetDir, $filename, true);
                return base_url('uploads/images/' . $filename);
            }
        }
        return 'ERR';
    }

    public function getBase64Image()
    {
        $session = session();
        $loginData = $session->get('LoginData');
        $idUser = $loginData['ID_USER'] ?? 0;

        $path = FCPATH . 'uploads/images/' . $idUser . '.png';
        if (!file_exists($path)) {
            $path = FCPATH . 'assets/images/profile_small.jpg';
        }

        $data = file_exists($path) ? file_get_contents($path) : '';
        return $this->response->setJSON([
            'base64imgage' => base64_encode($data)
        ]);
    }

    public function userHome()
    {
        $session = session();
        $loginData = $session->get('LoginData');

        $data = [
            'Header'        => 'MANAJEMEN USER',
            'Title'         => 'Users',
            'Keterangan'    => 'Pembuatan master data untuk user / pengguna.',
            'NAMA_LENGKAP'  => $loginData['NAMA_LENGKAP'] ?? '',
            'NM_UNITKER'    => $loginData['NM_UNITKER'] ?? '',
            'LAST_LOGIN'    => '[' . date('d-m-Y H:i:s', strtotime($loginData['LAST_LOGIN'] ?? 'now')) . ']',
            'listUnitKerja' => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll(),
            'listJenisUser' => ['Admin', 'User', 'Verifikasi 1', 'Verifikasi 2', 'Persetujuan']
        ];

        return view('user/user_home', $data);
    }

    public function getListUser()
    {
        $db = \Config\Database::connect();
        $list = $db->table('tb_users u')
            ->select('u.ID_USER, u.USER_NAME, u.NAMA_LENGKAP, u.JENIS_USER, u.AKTIF, mk.KD_SKPD, mk.NM_SKPD as NM_UNITKER')
            ->join('ms_skpd mk', 'u.KD_UNITKER = mk.KD_SKPD', 'left')
            ->orderBy('u.ID_USER', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON($list);
    }

    public function createUser()
    {
        if ($this->request->getMethod() === 'POST') {
            $username = trim($this->request->getPost('USER_NAME'));
            $nama = trim($this->request->getPost('NAMA_LENGKAP'));
            $jenis = trim($this->request->getPost('JENIS_USER'));
            $kdSkpd = trim($this->request->getPost('KD_UNITKER'));

            if (empty($username) || empty($nama)) {
                return $this->response->setBody('9');
            }

            if ($this->userModel->where('USER_NAME', $username)->first()) {
                return $this->response->setBody('9');
            }

            $this->userModel->insert([
                'USER_NAME'    => $username,
                'NAMA_LENGKAP' => $nama,
                'PASSWORD'     => strtolower(hash('sha512', 'assami')),
                'JENIS_USER'   => $jenis,
                'KD_UNITKER'   => $kdSkpd,
                'AKTIF'        => 1
            ]);

            return $this->response->setBody('0');
        }

        return view('user/modals/_create_user', [
            'listJenisUser' => ['Admin', 'User', 'Verifikasi 1', 'Verifikasi 2', 'Persetujuan'],
            'listUnitKerja' => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll()
        ]);
    }

    public function editUser($idUser = null)
    {
        $idUser = $idUser ?? $this->request->getPost('ID_USER');
        $user = $this->userModel->find($idUser);

        if (!$user) {
            return $this->response->setBody('9');
        }

        if ($this->request->getMethod() === 'POST') {
            $this->userModel->update($idUser, [
                'NAMA_LENGKAP' => $this->request->getPost('NAMA_LENGKAP'),
                'JENIS_USER'   => $this->request->getPost('JENIS_USER'),
                'KD_UNITKER'   => $this->request->getPost('KD_UNITKER'),
                'AKTIF'        => $this->request->getPost('AKTIF') ? 1 : 0
            ]);

            return $this->response->setBody('0');
        }

        return view('user/modals/_edit_user', [
            'user'          => $user,
            'listJenisUser' => ['Admin', 'User', 'Verifikasi 1', 'Verifikasi 2', 'Persetujuan'],
            'listUnitKerja' => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll()
        ]);
    }

    public function deleteUser()
    {
        $idUser = $this->request->getPost('idUser');
        if (!empty($idUser)) {
            $this->userModel->delete($idUser);
            return $this->response->setBody('0');
        }
        return $this->response->setBody('9');
    }

    public function isCreateUserExists()
    {
        $username = $this->request->getGet('USER_NAME');
        $exists = $this->userModel->where('USER_NAME', $username)->first();
        return $this->response->setJSON(!$exists);
    }

    public function resetPasswordUser()
    {
        $idUser = $this->request->getPost('idUser');
        if (!empty($idUser)) {
            $this->userModel->update($idUser, [
                'PASSWORD' => strtolower(hash('sha512', 'assami'))
            ]);
            return $this->response->setBody('0');
        }
        return $this->response->setBody('9');
    }

    public function daftarRole()
    {
        return view('user/modals/_daftar_role', [
            'listUnitKerja' => $this->skpdModel->orderBy('NM_SKPD', 'ASC')->findAll()
        ]);
    }

    public function getListRole()
    {
        $idUser = $this->request->getGet('ID_USER');
        $user = $this->userModel->find($idUser);
        if (!$user) {
            return $this->response->setJSON([]);
        }

        $db = \Config\Database::connect();
        $roles = $db->table('tb_user_role r')
            ->select('r.ID_USER_ROLE, r.USERNAME, mk.KD_SKPD, mk.NM_SKPD')
            ->join('ms_skpd mk', 'r.KD_SKPD = mk.KD_SKPD')
            ->where('r.USERNAME', $user['USER_NAME'])
            ->get()
            ->getResultArray();

        return $this->response->setJSON($roles);
    }

    public function addRole()
    {
        $idUser = $this->request->getPost('ID_USER');
        $kdSkpd = $this->request->getPost('KD_SKPD');

        $user = $this->userModel->find($idUser);
        if (!$user) {
            return $this->response->setBody('User tidak ditemukan');
        }

        $existing = $this->userRoleModel
            ->where('USERNAME', $user['USER_NAME'])
            ->where('KD_SKPD', $kdSkpd)
            ->first();

        if ($existing) {
            return $this->response->setBody('Data role sudah ada, coba cek terlebih dahulu.');
        }

        $this->userRoleModel->insert([
            'USERNAME' => $user['USER_NAME'],
            'KD_SKPD'  => $kdSkpd
        ]);

        return $this->response->setBody('0');
    }

    public function removeRole()
    {
        $id = $this->request->getPost('ID');
        if (!empty($id)) {
            $this->userRoleModel->delete($id);
            return $this->response->setBody('0');
        }
        return $this->response->setBody('9');
    }

    protected function logActivity(int $status, string $ket)
    {
        $session = session();
        $loginData = $session->get('LoginData');
        if (!empty($loginData)) {
            $this->userLogModel->insert([
                'NAMA_USER'   => $loginData['USER_NAME'],
                'TANGGAL_JAM' => date('Y-m-d H:i:s'),
                'IP_ADDRESS'  => $this->request->getIPAddress(),
                'STATUS'      => $status,
                'KET'         => $ket
            ]);
        }
    }
}
