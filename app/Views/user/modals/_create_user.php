<div class="modal-header">
    <h4 class="modal-title font-bold text-navy"><i class="fa fa-user-plus"></i> Tambah Pengguna Baru</h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<form id="formCreateUser" onsubmit="return submitCreateUser(event)">
    <div class="modal-body">
        <div class="form-group mb-3">
            <label class="font-bold">Username <span class="text-danger">*</span></label>
            <input type="text" name="USER_NAME" id="new_USER_NAME" class="form-control" required placeholder="Contoh: user_keuangan">
        </div>

        <div class="form-group mb-3">
            <label class="font-bold">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="NAMA_LENGKAP" class="form-control" required placeholder="Nama lengkap pengguna">
        </div>

        <div class="form-group mb-3">
            <label class="font-bold">Jenis User / Peran <span class="text-danger">*</span></label>
            <select name="JENIS_USER" class="form-control" required>
                <?php foreach ($listJenisUser as $jenis): ?>
                    <option value="<?= esc($jenis) ?>"><?= esc($jenis) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group mb-3" style="position: relative;">
            <label class="font-bold">Unit Kerja / OPD <span class="text-danger">*</span></label>
            <select name="KD_UNITKER" id="create_KD_UNITKER" class="form-control select2_modal" style="width:100%" required>
                <option value="">-- Pilih OPD --</option>
                <?php foreach ($listUnitKerja as $skpd): ?>
                    <option value="<?= esc($skpd['KD_SKPD']) ?>"><?= esc($skpd['KD_SKPD']) ?> - <?= esc($skpd['NM_SKPD']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="alert alert-info py-2">
            <i class="fa fa-info-circle"></i> Password awal default adalah <strong>assami</strong>.
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
        <button type="submit" class="btn btn-primary"><i class="fa fa-save"></i> Simpan Pengguna</button>
    </div>
</form>

<script>
$(document).ready(function() {
    $('#create_KD_UNITKER').select2({
        dropdownParent: $('#create_KD_UNITKER').parent(),
        width: '100%',
        placeholder: '-- Pilih OPD --'
    });
});

function submitCreateUser(e) {
    e.preventDefault();
    var formData = $('#formCreateUser').serialize();

    $.post('<?= base_url('user/createuser') ?>', formData, function(resp) {
        if (resp.trim() === '0') {
            $('#mdlCreate').modal('hide');
            swal.fire('Sukses', 'Pengguna baru berhasil ditambahkan.', 'success');
            loadUserData();
        } else {
            swal.fire('Pemberitahuan', 'Gagal menambahkan pengguna. Pastikan username belum pernah terdaftar.', 'error');
        }
    });
    return false;
}
</script>
