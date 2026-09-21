<div class="ibox-content no-borders" style="padding: 10px;">
    <div class="text-center mb-3">
        <img src="<?= base_url('assets/LogoPolman.png') ?>" alt="Logo Polman" style="height: 50px; margin-right: 10px;">
        <img src="<?= base_url('assets/BSSBLogo.png') ?>" alt="Bank Sulselbar" style="height: 45px;">
    </div>

    <p class="text-warning text-center fw-bold mx-3 mb-0" style="font-size: 24px; font-weight: 800;">
        <strong>- SIPEKDA ASSAMI -</strong>
    </p>
    <div class="divider d-flex align-items-center my-3 text-center">
        <p class="text-navy text-center fw-bold mx-auto mb-0" style="font-size: 15px;">
            Sistem Penatausahaan Keuangan Daerah
        </p>
    </div>

    <div class="text-center mb-4 text-muted">
        <small>Silakan masukkan akun Anda untuk melanjutkan</small>
    </div>

    <form id="loginForm" onsubmit="return handleFormSubmit(event)">
        <!-- Username input -->
        <div class="form-group mb-3">
            <label class="font-bold"><i class="fa fa-user text-navy"></i> Username</label>
            <input type="text" id="USER_NAME" name="USER_NAME" class="form-control form-control-lg" placeholder="Masukkan Username..." required autofocus autocomplete="username">
        </div>

        <!-- Password input -->
        <div class="form-group mb-4">
            <label class="font-bold"><i class="fa fa-lock text-navy"></i> Password</label>
            <input type="password" id="PASSWORD" name="PASSWORD" class="form-control form-control-lg" placeholder="Masukkan Password..." required autocomplete="current-password">
        </div>

        <div class="text-center pt-2">
            <button type="button" id="bLogin" class="btn btn-primary btn-block btn-lg ldLogin ladda-button" data-style="zoom-in" onclick="doLogin()">
                <span class="ladda-label"><i class="fa fa-sign-in"></i> Masuk</span>
            </button>
        </div>
    </form>

    <div class="text-center mt-4">
        <small class="text-muted">Pemerintah Kabupaten Polewali Mandar &copy; <?= date('Y') ?></small>
    </div>
</div>

<script>
function handleFormSubmit(e) {
    e.preventDefault();
    doLogin();
    return false;
}

function doLogin() {
    var username = $('#USER_NAME').val().trim();
    var password = $('#PASSWORD').val();

    if (!username) {
        swal.fire('Perhatian', 'Harap masukkan username Anda', 'warning').then(() => $('#USER_NAME').focus());
        return;
    }
    if (!password) {
        swal.fire('Perhatian', 'Harap masukkan password Anda', 'warning').then(() => $('#PASSWORD').focus());
        return;
    }

    var l = $('.ldLogin').ladda();
    l.ladda('start');

    $.ajax({
        url: '<?= base_url('user/validasilogin') ?>',
        type: 'POST',
        data: {
            USER_NAME: username,
            PASSWORD: password
        },
        success: function (result) {
            result = result.trim();
            if (result.startsWith('http://') || result.startsWith('https://') || result.startsWith('/')) {
                window.location.href = result;
            } else if (result === '7') {
                window.location.href = '<?= base_url('user/gantipassword') ?>';
            } else {
                l.ladda('stop');
                $('#USER_NAME').focus();
                showMessage(result);
            }
        },
        error: function () {
            l.ladda('stop');
            swal.fire('Kesalahan', 'Terjadi gangguan jaringan atau server. Coba beberapa saat lagi.', 'error');
        }
    });
}

function showMessage(kode) {
    if (kode === '9') {
        swal.fire('Kesalahan', 'Maaf, username atau password tidak sesuai...', 'error')
            .then(() => { $('#USER_NAME').focus(); });
    } else if (kode === '8') {
        swal.fire('Kesalahan', 'Maaf, username tidak aktif atau terblokir. Harap hubungi administrator...', 'error')
            .then(() => { $('#USER_NAME').focus(); });
    } else if (kode === '6') {
        swal.fire('Kesalahan', 'Maaf, username anda terblokir sementara. Harap coba 5 menit lagi...', 'error')
            .then(() => { $('#USER_NAME').focus(); });
    } else if (kode === '10') {
        swal.fire('Kesalahan', 'Maaf, kode yang dimasukkan salah...', 'error')
            .then(() => { $('#USER_NAME').focus(); });
    } else {
        swal.fire('Info', 'Respon server: ' + kode, 'info');
    }
}

$(document).ready(function() {
    $('#USER_NAME').focus();
    $('#PASSWORD').on('keypress', function(e) {
        if (e.which === 13) {
            doLogin();
        }
    });
});
</script>
