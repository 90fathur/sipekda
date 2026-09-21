<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="ibox">
            <div class="ibox-title">
                <h3 class="font-bold text-navy"><i class="fa fa-key"></i> Ganti Password Akun</h3>
                <small>Silakan ubah password Anda dengan kombinasi huruf dan angka (minimal 6 karakter).</small>
            </div>
            <div class="ibox-content">
                <form id="formGantiPassword" onsubmit="return submitGantiPassword(event)">
                    <div class="form-group mb-3">
                        <label class="font-bold">Password Lama</label>
                        <input type="password" id="OldPassword" class="form-control" required placeholder="Masukkan password saat ini">
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-bold">Password Baru</label>
                        <input type="password" id="NewPassword" class="form-control" required placeholder="Minimal 6 karakter, kombinasi huruf & angka">
                    </div>

                    <div class="form-group mb-4">
                        <label class="font-bold">Konfirmasi Password Baru</label>
                        <input type="password" id="KonfirmasiPassword" class="form-control" required placeholder="Ulangi password baru">
                    </div>

                    <div class="form-group mb-0">
                        <button type="submit" class="btn btn-primary ldSubmit ladda-button" data-style="zoom-in">
                            <span class="ladda-label"><i class="fa fa-save"></i> Simpan Password</span>
                        </button>
                        <a href="<?= base_url('dashboards/main') ?>" class="btn btn-default">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
function submitGantiPassword(e) {
    e.preventDefault();

    var oldPass = $('#OldPassword').val();
    var newPass = $('#NewPassword').val();
    var confirmPass = $('#KonfirmasiPassword').val();

    if (newPass.length < 6) {
        swal.fire('Peringatan', 'Password baru minimal 6 karakter.', 'warning');
        return false;
    }

    if (newPass !== confirmPass) {
        swal.fire('Peringatan', 'Konfirmasi password baru tidak cocok.', 'warning');
        return false;
    }

    var l = $('.ldSubmit').ladda();
    l.ladda('start');

    $.ajax({
        url: '<?= base_url('user/validasipassword') ?>',
        type: 'POST',
        data: {
            OldPassword: oldPass,
            NewPassword: newPass,
            KonfirmasiPassword: confirmPass
        },
        success: function(resp) {
            l.ladda('stop');
            resp = resp.trim();
            if (resp === '0') {
                swal.fire('Sukses', 'Password Anda berhasil diperbarui!', 'success')
                    .then(() => {
                        window.location.href = '<?= base_url('dashboards/main') ?>';
                    });
            } else if (resp === '4') {
                swal.fire('Kesalahan', 'Panjang password minimal 6 karakter.', 'error');
            } else if (resp === '5') {
                swal.fire('Kesalahan', 'Password harus kombinasi angka dan huruf, serta tidak boleh menggunakan kata default "assami".', 'error');
            } else if (resp === '6') {
                swal.fire('Kesalahan', 'Password baru tidak boleh sama dengan password lama.', 'error');
            } else if (resp === '7') {
                swal.fire('Kesalahan', 'Password lama yang Anda masukkan salah.', 'error');
            } else if (resp === '8') {
                swal.fire('Kesalahan', 'Password baru dan konfirmasi tidak sesuai.', 'error');
            } else {
                swal.fire('Kesalahan', 'Gagal memperbarui password (kode: ' + resp + ')', 'error');
            }
        },
        error: function() {
            l.ladda('stop');
            swal.fire('Kesalahan', 'Terjadi kesalahan komunikasi dengan server.', 'error');
        }
    });

    return false;
}
</script>
<?= $this->endSection() ?>
