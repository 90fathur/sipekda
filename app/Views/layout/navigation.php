<?php
$menus = get_user_menu();
$headerMenu = $menus['header'] ?? [];
$itemMenu = $menus['items'] ?? [];
$session = session();
$loginData = $session->get('LoginData') ?? [];
$namaLengkap = $loginData['NAMA_LENGKAP'] ?? 'Pengguna';
$nmUnitKerja = $loginData['NM_UNITKER'] ?? 'OPD';
$idUser = $loginData['ID_USER'] ?? 0;
$profilePic = base_url('assets/images/profile_small.jpg');
if (file_exists(FCPATH . 'uploads/images/' . $idUser . '.png')) {
    $profilePic = base_url('uploads/images/' . $idUser . '.png');
}
?>
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            <li class="nav-header">
                <div class="dropdown profile-element">
                    <img alt="image" class="rounded-circle" src="<?= $profilePic ?>" style="width: 48px; height: 48px; object-fit: cover;" />
                    <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                        <span class="text-muted text-xs">&ensp;<i class="fa fa-gear text-navy"></i></span>
                        <span class="block m-t-xs font-bold truncate2"><?= esc($namaLengkap) ?></span>
                        <span class="text-muted text-xs block truncate"><?= esc($nmUnitKerja) ?></span>
                    </a>
                    <ul class="dropdown-menu animated fadeInRight m-t-xs" style="position: absolute; top: 91px; left: 0px;">
                        <li><label class="dropdown-item" for="FILE" style="cursor: pointer;"><i class="fa fa-image text-navy"></i> Upload Foto Profile</label></li>
                        <li><a class="dropdown-item" href="<?= base_url('user/gantipassword') ?>"><i class="fa fa-key text-navy"></i> Ganti Password</a></li>
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= base_url('user/logout') ?>"><i class="fa fa-sign-out"></i> Logout</a></li>
                    </ul>
                </div>
                <div class="logo-element text-default">
                    BSSB+
                </div>
            </li>

            <input id="FILE" type="file" style="display:none" onchange="uploadProfileImage(this);">

            <?php foreach ($headerMenu as $h): ?>
                <?php
                $controller = $h['NM_CONTROLLER'] ?? '';
                $action = $h['NM_ACTION'] ?? '';
                $menuTitle = $h['NM_MENU'] ?? '';
                $hasChild = (int)($h['CHILD'] ?? 0) === 1;
                $kdMenu = (int)($h['KD_MENU'] ?? 0);
                $icon = $h['ICON'] ?? 'fa fa-folder';
                $isPengaturan = (int)($h['PENGATURAN'] ?? 0);
                if ($isPengaturan !== 0) continue;

                $url = !empty($action) ? base_url(strtolower($controller) . '/' . strtolower($action)) : '#';
                $isActiveHeader = is_selected($controller);
                ?>
                <li class="<?= $isActiveHeader ?>">
                    <a href="<?= $url ?>">
                        <i class="<?= esc($icon) ?> text-navy"></i> <span class="nav-label"><?= esc($menuTitle) ?></span>
                        <?php if ($hasChild): ?>
                            <span class="fa arrow"></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($hasChild): ?>
                        <ul class="nav nav-second-level collapse">
                            <?php foreach ($itemMenu as $sub): ?>
                                <?php if ((int)$sub['MASTER_MENU'] === $kdMenu && (int)$sub['PENGATURAN'] === 0): ?>
                                    <?php
                                    $subCtrl = $sub['NM_CONTROLLER'] ?? '';
                                    $subAct = $sub['NM_ACTION'] ?? '';
                                    $subUrl = base_url(strtolower($subCtrl) . '/' . strtolower($subAct));
                                    $isActiveSub = is_selected($subCtrl, $subAct);
                                    ?>
                                    <li class="<?= $isActiveSub ?>">
                                        <a href="<?= $subUrl ?>"><?= esc($sub['NM_MENU']) ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</nav>

<script>
    function uploadProfileImage(input) {
        if (input.files && input.files[0]) {
            var formData = new FormData();
            formData.append('FILE_IMAGE', input.files[0]);
            $.ajax({
                url: '<?= base_url('user/uploadimage') ?>',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(resp) {
                    if (resp !== 'ERR') {
                        location.reload();
                    } else {
                        alert('Gagal mengunggah foto');
                    }
                }
            });
        }
    }
</script>

<style>
    .Cut {
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
    }

    .fa-14x {
        font-size: 1.4em;
    }
</style>