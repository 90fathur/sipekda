<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Default root redirects directly to login or dashboard
$routes->get('/', function () {
    $session = session();
    if ($session->get('LoginData')) {
        $user = $session->get('LoginData');
        if ($user['JENIS_USER'] === 'Admin') {
            return redirect()->to(base_url('user/userhome'));
        }
        return redirect()->to(base_url('dashboards/main'));
    }
    return redirect()->to(base_url('user/login'));
});

// Authentication routes (Public)
$routes->group('user', function ($routes) {
    $routes->get('login', 'User::login');
    $routes->get('menuloginuser', 'User::menuLoginUser');
    $routes->post('validasilogin', 'User::validasiLogin');
    $routes->get('keygenerator', 'User::keyGenerator');
    $routes->get('logout', 'User::logout');

    // Authenticated User routes
    $routes->group('', ['filter' => 'auth'], function ($routes) {
        $routes->get('gantipassword', 'User::gantiPassword');
        $routes->post('validasipassword', 'User::validasiPassword');
        $routes->post('uploadimage', 'User::uploadImage');
        $routes->get('getbase64image', 'User::getBase64Image');

        // Admin only user management
        $routes->group('', ['filter' => 'auth:Admin'], function ($routes) {
            $routes->get('userhome', 'User::userHome');
            $routes->get('getlistuser', 'User::getListUser');
            $routes->match(['get', 'post'], 'createuser', 'User::createUser');
            $routes->match(['get', 'post'], 'edituser/(:num)', 'User::editUser/$1');
            $routes->match(['get', 'post'], 'edituser', 'User::editUser');
            $routes->post('deleteuser', 'User::deleteUser');
            $routes->get('iscreateuserexists', 'User::isCreateUserExists');
            $routes->post('resetpassworduser', 'User::resetPasswordUser');
            $routes->get('daftarrole', 'User::daftarRole');
            $routes->get('getlistrole', 'User::getListRole');
            $routes->post('addrole', 'User::addRole');
            $routes->post('removerole', 'User::removeRole');
        });
    });
});

// Dashboards (Protected)
$routes->group('dashboards', ['filter' => 'auth'], function ($routes) {
    $routes->get('main', 'Dashboards::main');
    $routes->get('totalsp2d', 'Dashboards::totalSP2D');
    $routes->get('nominalspmmonthly', 'Dashboards::nominalSPMMonthly');
    $routes->get('nominalspmyearly', 'Dashboards::nominalSPMYearly');
    $routes->get('getpagugrafik', 'Dashboards::getPaguGrafik');
    $routes->get('getdatagrafik', 'Dashboards::getDataGrafik');
    $routes->get('top5belanjachart', 'Dashboards::top5BelanjaChart');
    $routes->get('rincianrealisasianggaran', 'Dashboards::rincianRealisasiAnggaran');
});

// SPM & NPD (Protected)
$routes->group('spm', ['filter' => 'auth'], function ($routes) {
    // Only User and Admin can create new submissions
    $routes->group('', ['filter' => 'auth:User,Admin'], function ($routes) {
        $routes->get('pengajuanspmhome', 'SPM::pengajuanSPMHome');
        $routes->get('pengajuannpdhome', 'SPM::pengajuanNPDHome');
        $routes->get('getnpdsukseslist', 'SPM::getNPDSuksesList');
        $routes->get('getviewspm', 'SPM::getViewSPM');
        $routes->post('savepengajuan', 'SPM::savePengajuan');
        $routes->post('savepengajuannpd', 'SPM::savePengajuanNPD');
        $routes->post('uploadfile', 'SPM::uploadFile');
        $routes->match(['get', 'post'], 'deletefile', 'SPM::deleteFile');
        $routes->post('adddatadetail', 'SPM::addDataDetail');
        $routes->post('deletedatadetail', 'SPM::deleteDataDetail');
    });

    // Submissions status and monitoring (All roles)
    $routes->get('statuspengajuanhome', 'SPM::statusPengajuanHome');
    $routes->get('statuspengajuannpdhome', 'SPM::statusPengajuanNPDHome');
    $routes->get('getlistpengajuan', 'SPM::getListPengajuan');
    $routes->get('monitoringhome', 'SPM::monitoringHome');
    $routes->get('getlistmonitoring', 'SPM::getListMonitoring');
    $routes->get('getallfiles', 'SPM::getAllFiles');
    $routes->get('spmview', 'SPM::spmView');
    $routes->get('spmviewnpd', 'SPM::spmViewNPD');
    $routes->get('getdatadetail', 'SPM::getDataDetail');
});

// BPKAD Approval (Protected: Verifikator & Approver only)
$routes->group('bpkad', function ($routes) {
    // Verifikasi 1, Verifikasi 2, Persetujuan, Admin
    $routes->group('', ['filter' => 'auth:Verifikasi 1,Verifikasi 2,Persetujuan,Admin'], function ($routes) {
        $routes->get('persetujuanspmhome', 'BPKAD::persetujuanSPMHome');
        $routes->get('persetujuannpdhome', 'BPKAD::persetujuanNPDHome');
        $routes->get('getlistpengajuan', 'BPKAD::getListPengajuan');
        $routes->post('acceptpengajuan', 'BPKAD::acceptPengajuan');
        $routes->post('rejectpengajuan', 'BPKAD::rejectPengajuan');
        $routes->post('acceptpengajuannpd', 'BPKAD::acceptPengajuanNPD');
        $routes->post('rejectpengajuannpd', 'BPKAD::rejectPengajuanNPD');
        $routes->get('getstatussipd', 'BPKAD::getStatusSIPD');
        $routes->get('spmview', 'BPKAD::spmView');
        $routes->get('spmviewnpd', 'BPKAD::spmViewNPD');
    });

    // Persetujuan SP2D only for Persetujuan and Admin
    $routes->group('', ['filter' => 'auth:Persetujuan,Admin'], function ($routes) {
        $routes->get('persetujuansp2dhome', 'BPKAD::persetujuanSP2DHome');
        $routes->match(['get', 'post'], 'setproses', 'BPKAD::setProses');
        $routes->match(['get', 'post'], 'cancelproses', 'BPKAD::cancelProses');
    });
});

// Pagu Anggaran (Protected)
$routes->group('pagu', ['filter' => 'auth'], function ($routes) {
    $routes->get('monitoringpaguhome', 'Pagu::monitoringPaguHome');
    $routes->get('getpaguanggaran', 'Pagu::getPaguAnggaran');
    $routes->post('import', 'Pagu::import');
});

// Monitoring SIPD (Protected)
$routes->group('sipd', ['filter' => 'auth'], function ($routes) {
    $routes->get('monitoringsipdhome', 'SIPD::monitoringSIPDHome');
    $routes->get('getstatussipd', 'SIPD::getStatusSIPD');
});

// Monitoring (Protected)
$routes->group('monitoring', ['filter' => 'auth'], function ($routes) {
    $routes->get('daftartransaksi', 'Monitoring::daftarTransaksi');
    $routes->get('daftarrtgs', 'Monitoring::daftarRTGS');
});

// Coba update git
