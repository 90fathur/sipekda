<div class="modal-header">
    <h4 class="modal-title font-bold text-navy"><i class="fa fa-user-edit"></i> Ubah Data Pengguna</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<form id="formEditUser" onsubmit="return submitEditUser(event)">
    <input type="hidden" name="ID_USER" value="<?= esc($user['ID_USER']) ?>">
    <div class="modal-body">
        <div class="form-group mb-3">
            <label class="font-bold">Username</label>
            <input type="text" class="form-control" value="<?= esc($user['USER_NAME']) ?>" disabled>
        </div>

        <div class="form-group mb-3">
            <label class="font-bold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="NAMA_LENGKAP" class="form-control" required value="<?= esc($user['NAMA_LENGKAP']) ?>">
        </div>

        <div class="form-group mb-3">
            <label class="font-bold">Jenis User / Peran <span class="text-danger">*</span></label>
            <select name="JENIS_USER" class="form-control" required>
                <?php foreach ($listJenisUser as $jenis): ?>
                    <option value="<?= esc($jenis) ?>" <?= ($user['JENIS_USER'] === $jenis) ? 'selected' : '' ?>>
                        <?= esc($jenis) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-3">
            <label class="font-bold">Unit Kerja / OPD <span class="text-danger">*</span></label>
            <select name="KD_UNITKER" class="form-control select2_edit" style="width:100%" required>
                <option value="">-- Pilih OPD --</option>
                <?php foreach ($listUnitKerja as $skpd): ?>
                    <option value="<?= esc($skpd['KD_SKPD']) ?>" <?= ($user['KD_UNITKER'] === $skpd['KD_SKPD']) ? 'selected' : '' ?>>
                        <?= esc($skpd['KD_SKPD']) ?> - <?= esc($skpd['NM_SKPD']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-0">
            <label class="font-bold">Status Akun</label>
            <div>
                <label class="checkbox-inline">
                    <input type="checkbox" name="AKTIF" value="1" <?= ((int)$user['AKTIF'] === 1) ? 'checked' : '' ?>> Aktif
                </label>
            </div>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Perbarui Pengguna</button>
    </div>
</form>

<script>
$('.select2_edit').select2({ dropdownParent: $('#mdlEdit') });

function submitEditUser(e) {
    e.preventDefault();
    var formData = $('#formEditUser').serialize();

    $.post('<?= base_url('user/edituser') ?>', formData, function(resp) {
        if (resp.trim() === '0') {
            $('#mdlEdit').modal('hide');
            swal.fire('Sukses', 'Data pengguna berhasil diperbarui.', 'success');
            loadUserData();
        } else {
            swal.fire('Pemberitahuan', 'Gagal memperbarui pengguna.', 'error');
        }
    });
    return false;
}
</script>
