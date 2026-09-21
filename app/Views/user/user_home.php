<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row animated fadeInRight">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <div class="ibox-tools">
                    <button type="button" class="btn btn-primary btn-sm" onclick="openCreateUserModal()">
                        <i class="fa fa-plus"></i> Tambah Pengguna Baru
                    </button>
                </div>
                <h5 class="font-bold text-navy"><i class="fa fa-users"></i> Daftar Pengguna Sistem</h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table id="dtUser" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-center" style="width: 15%;">Username</th>
                                <th class="text-center" style="width: 20%;">Nama Lengkap</th>
                                <th class="text-center" style="width: 15%;">Jenis User</th>
                                <th class="text-center" style="width: 25%;">Unit Kerja / OPD</th>
                                <th class="text-center" style="width: 8%;">Status</th>
                                <th class="text-center" style="width: 12%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Loaded via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Create User -->
<div class="modal fade" id="mdlCreate" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content" id="createModalContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Modal Edit User -->
<div class="modal fade" id="mdlEdit" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content" id="editModalContent">
            <!-- Content loaded via AJAX -->
        </div>
    </div>
</div>

<!-- Modal Role SKPD -->
<div class="modal fade" id="mdlRole" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-bold text-navy"><i class="fa fa-sitemap"></i> Hak Akses SKPD Verifikator</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="role_ID_USER">
                <p>Pengguna: <strong id="role_USERNAME" class="text-navy"></strong></p>
                <div class="input-group mb-3">
                    <select id="role_KD_SKPD" class="form-control select2" style="width: 80%;">
                        <option value="">-- Pilih SKPD untuk Diberikan Akses --</option>
                        <?php foreach ($listUnitKerja as $skpd): ?>
                            <option value="<?= esc($skpd['KD_SKPD']) ?>"><?= esc($skpd['KD_SKPD']) ?> - <?= esc($skpd['NM_SKPD']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="input-group-append">
                        <button type="button" class="btn btn-primary" onclick="addRoleSKPD()"><i class="fa fa-plus"></i> Tambah Role</button>
                    </div>
                </div>

                <div class="table-responsive mt-3">
                    <table id="dtRole" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width: 10%;">No</th>
                                <th style="width: 30%;">Kode SKPD</th>
                                <th style="width: 50%;">Nama SKPD</th>
                                <th class="text-center" style="width: 10%;">Hapus</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function () {
    loadUserData();
    $('.select2').select2({ dropdownParent: $('#mdlRole') });
});

function loadUserData() {
    if ($.fn.DataTable.isDataTable('#dtUser')) {
        $('#dtUser').DataTable().destroy();
    }

    $('#dtUser').DataTable({
        ajax: {
            url: '<?= base_url('user/getlistuser') ?>',
            dataSrc: ''
        },
        columns: [
            {
                data: null,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'USER_NAME', className: 'font-bold' },
            { data: 'NAMA_LENGKAP' },
            {
                data: 'JENIS_USER',
                render: function(val) {
                    var cls = 'label-default';
                    if (val === 'Admin') cls = 'label-danger';
                    else if (val === 'Persetujuan') cls = 'label-primary';
                    else if (val.includes('Verifikasi')) cls = 'label-warning';
                    else cls = 'label-info';
                    return '<span class="label ' + cls + '">' + val + '</span>';
                }
            },
            { data: 'NM_UNITKER' },
            {
                data: 'AKTIF',
                className: 'text-center',
                render: function (val) {
                    return (val == 1)
                        ? '<span class="badge badge-primary">Aktif</span>'
                        : '<span class="badge badge-danger">Nonaktif</span>';
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function (data, type, row) {
                    var btnRole = '';
                    if (row.JENIS_USER.includes('Verifikasi') || row.JENIS_USER === 'Persetujuan') {
                        btnRole = '<button class="btn btn-xs btn-info" onclick="openRoleModal(' + row.ID_USER + ', \'' + row.USER_NAME + '\')" title="Hak Akses SKPD"><i class="fa fa-sitemap"></i></button> ';
                    }
                    return btnRole +
                        '<button class="btn btn-xs btn-warning" onclick="openEditUserModal(' + row.ID_USER + ')" title="Ubah User"><i class="fa fa-edit"></i></button> ' +
                        '<button class="btn btn-xs btn-danger" onclick="resetUserPassword(' + row.ID_USER + ')" title="Reset Password"><i class="fa fa-key"></i></button> ' +
                        '<button class="btn btn-xs btn-default" onclick="deleteUser(' + row.ID_USER + ')" title="Hapus User"><i class="fa fa-trash text-danger"></i></button>';
                }
            }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengguna"
        }
    });
}

function openCreateUserModal() {
    $.get('<?= base_url('user/createuser') ?>', function (html) {
        $('#createModalContent').html(html);
        $('#mdlCreate').modal('show');
    });
}

function openEditUserModal(id) {
    $.get('<?= base_url('user/edituser') ?>/' + id, function (html) {
        $('#editModalContent').html(html);
        $('#mdlEdit').modal('show');
    });
}

function resetUserPassword(id) {
    swal.fire({
        title: 'Reset Password?',
        text: 'Password pengguna akan diatur ulang menjadi default "assami"',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Reset',
        cancelButtonText: 'Batal'
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('<?= base_url('user/resetpassworduser') ?>', { idUser: id }, function (resp) {
                if (resp.trim() === '0') {
                    swal.fire('Sukses', 'Password berhasil direset menjadi "assami".', 'success');
                } else {
                    swal.fire('Gagal', 'Terjadi kesalahan saat mereset password.', 'error');
                }
            });
        }
    });
}

function deleteUser(id) {
    swal.fire({
        title: 'Hapus Pengguna?',
        text: 'Data pengguna ini akan dihapus secara permanen!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        confirmButtonColor: '#ed5565',
        cancelButtonText: 'Batal'
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('<?= base_url('user/deleteuser') ?>', { idUser: id }, function (resp) {
                if (resp.trim() === '0') {
                    swal.fire('Sukses', 'Pengguna berhasil dihapus.', 'success');
                    loadUserData();
                } else {
                    swal.fire('Gagal', 'Gagal menghapus pengguna.', 'error');
                }
            });
        }
    });
}

function openRoleModal(idUser, username) {
    $('#role_ID_USER').val(idUser);
    $('#role_USERNAME').text(username);
    loadRoleTable(idUser);
    $('#mdlRole').modal('show');
}

function loadRoleTable(idUser) {
    $.get('<?= base_url('user/getlistrole') ?>', { ID_USER: idUser }, function (list) {
        var tbody = '';
        if (list.length === 0) {
            tbody = '<tr><td colspan="4" class="text-center text-muted">Belum ada pemetaan SKPD khusus (mengakses semua SKPD)</td></tr>';
        } else {
            list.forEach(function (r, idx) {
                tbody += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td>' + r.KD_SKPD + '</td>' +
                    '<td>' + r.NM_SKPD + '</td>' +
                    '<td class="text-center"><button class="btn btn-xs btn-danger" onclick="removeRoleSKPD(' + r.ID_USER_ROLE + ')"><i class="fa fa-times"></i></button></td>' +
                    '</tr>';
            });
        }
        $('#dtRole tbody').html(tbody);
    });
}

function addRoleSKPD() {
    var idUser = $('#role_ID_USER').val();
    var kdSkpd = $('#role_KD_SKPD').val();
    if (!kdSkpd) {
        swal.fire('Peringatan', 'Silakan pilih SKPD terlebih dahulu', 'warning');
        return;
    }

    $.post('<?= base_url('user/addrole') ?>', { ID_USER: idUser, KD_SKPD: kdSkpd }, function (resp) {
        if (resp.trim() === '0') {
            loadRoleTable(idUser);
        } else {
            swal.fire('Pemberitahuan', resp, 'info');
        }
    });
}

function removeRoleSKPD(idRole) {
    var idUser = $('#role_ID_USER').val();
    $.post('<?= base_url('user/removerole') ?>', { ID: idRole }, function (resp) {
        if (resp.trim() === '0') {
            loadRoleTable(idUser);
        }
    });
}
</script>
<?= $this->endSection() ?>
