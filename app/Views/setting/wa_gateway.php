<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="wrapper wrapper-content animated fadeInRight">
    <!-- Header info banner -->
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h2 class="font-bold text-navy" style="margin-bottom: 2px;">
                        <i class="fa fa-whatsapp text-success"></i> <?= esc($Header) ?>
                    </h2>
                    <span class="text-muted"><?= esc($Keterangan) ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Configuration Card -->
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><i class="fa fa-cogs"></i> Konfigurasi WhatsApp Gateway</h5>
                </div>
                <div class="ibox-content">
                    <form id="formWaGateway" onsubmit="return saveSetting(event)">
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label font-bold">Status Notifikasi</label>
                            <div class="col-sm-8">
                                <div class="custom-control custom-switch mt-1">
                                    <input type="checkbox" class="custom-control-input" id="IS_ACTIVE" name="IS_ACTIVE" value="1" <?= ((int)($config['IS_ACTIVE'] ?? 0) === 1) ? 'checked' : '' ?>>
                                    <label class="custom-control-label font-bold <?= ((int)($config['IS_ACTIVE'] ?? 0) === 1) ? 'text-success' : 'text-danger' ?>" for="IS_ACTIVE" id="lblStatus">
                                        <?= ((int)($config['IS_ACTIVE'] ?? 0) === 1) ? '<i class="fa fa-check-circle"></i> AKTIF (Notifikasi Otomatis Berjalan)' : '<i class="fa fa-power-off"></i> NONAKTIF' ?>
                                    </label>
                                </div>
                                <small class="form-text text-muted">Jika dinonaktifkan, proses pengajuan dan verifikasi tetap berjalan lancar tanpa mengirimkan WhatsApp.</small>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label font-bold">Penyedia Gateway (Provider) <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <select name="PROVIDER" id="PROVIDER" class="form-control" onchange="handleProviderChange()">
                                    <option value="fonnte" <?= (($config['PROVIDER'] ?? '') === 'fonnte') ? 'selected' : '' ?>>Fonnte (Rekomendasi - Mudah & Cepat)</option>
                                    <option value="wablas" <?= (($config['PROVIDER'] ?? '') === 'wablas') ? 'selected' : '' ?>>Wablas</option>
                                    <option value="starsender" <?= (($config['PROVIDER'] ?? '') === 'starsender') ? 'selected' : '' ?>>Starsender</option>
                                    <option value="custom" <?= (($config['PROVIDER'] ?? '') === 'custom') ? 'selected' : '' ?>>Custom API Endpoint</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label font-bold">API Key / Token <span class="text-danger">*</span></label>
                            <div class="col-sm-8">
                                <div class="input-group">
                                    <input type="password" name="API_KEY" id="API_KEY" class="form-control" value="<?= esc($config['API_KEY'] ?? '') ?>" placeholder="Masukkan API Key / Token Gateway" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleApiKey()"><i class="fa fa-eye" id="eyeIcon"></i></button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">Token API yang didapatkan dari dashboard provider WhatsApp Anda.</small>
                            </div>
                        </div>

                        <div class="form-group row" id="groupEndpoint">
                            <label class="col-sm-4 col-form-label font-bold">URL Endpoint API</label>
                            <div class="col-sm-8">
                                <input type="text" name="ENDPOINT_URL" id="ENDPOINT_URL" class="form-control" value="<?= esc($config['ENDPOINT_URL'] ?? 'https://api.fonnte.com/send') ?>" placeholder="https://api.fonnte.com/send">
                                <small class="form-text text-muted">Endpoint pengiriman pesan API provider (default Fonnte: <code>https://api.fonnte.com/send</code>).</small>
                            </div>
                        </div>

                        <div class="form-group row" id="groupSender">
                            <label class="col-sm-4 col-form-label font-bold">Nomor Pengirim (Opsional)</label>
                            <div class="col-sm-8">
                                <input type="text" name="SENDER_NUMBER" id="SENDER_NUMBER" class="form-control" value="<?= esc($config['SENDER_NUMBER'] ?? '') ?>" placeholder="Contoh: 628123456789">
                                <small class="form-text text-muted">Nomor WhatsApp perangkat Anda (khusus provider tertentu seperti Wablas).</small>
                            </div>
                        </div>

                        <div class="hr-line-dashed"></div>

                        <div class="form-group row">
                            <div class="col-sm-8 col-sm-offset-4 offset-sm-4">
                                <button type="submit" class="btn btn-primary" id="btnSaveConfig">
                                    <i class="fa fa-save"></i> Simpan Pengaturan
                                </button>
                                <span class="text-muted ml-2 text-xs">Terakhir diperbarui: <?= !empty($config['UPDATED_AT']) ? date('d-m-Y H:i', strtotime($config['UPDATED_AT'])) : '-' ?></span>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Test Message & Quick Guide Card -->
        <div class="col-lg-5">
            <!-- Test Kirim Pesan -->
            <div class="ibox">
                <div class="ibox-title">
                    <h5><i class="fa fa-paper-plane text-navy"></i> Uji Coba Pengiriman WhatsApp</h5>
                </div>
                <div class="ibox-content">
                    <form id="formTestWa" onsubmit="return sendTestWa(event)">
                        <div class="form-group mb-3">
                            <label class="font-bold">Nomor WhatsApp Penerima <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-addon"><i class="fa fa-whatsapp text-success"></i></span>
                                </div>
                                <input type="text" id="test_phone" class="form-control" placeholder="Contoh: 081234567890" required>
                            </div>
                            <small class="text-muted">Bisa format 08xxx atau 628xxx.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label class="font-bold">Isi Pesan Uji Coba</label>
                            <textarea id="test_message" class="form-control" rows="3" placeholder="Tulis pesan uji coba...">🔔 Tes Koneksi SIPEKDA Kab. Polewali Mandar: WhatsApp Gateway berhasil terhubung dan aktif!</textarea>
                        </div>
                        <button type="submit" class="btn btn-success btn-block" id="btnTest">
                            <i class="fa fa-send"></i> Kirim Pesan Sekarang
                        </button>
                    </form>
                </div>
            </div>

            <!-- Panduan Penggunaan -->
            <div class="ibox">
                <div class="ibox-title">
                    <h5><i class="fa fa-info-circle text-info"></i> Alur Notifikasi WhatsApp</h5>
                </div>
                <div class="ibox-content">
                    <ul class="list-group list-group-flush" style="font-size: 13px;">
                        <li class="list-group-item px-0">
                            <strong>1. Pengajuan Baru oleh OPD</strong><br>
                            <span class="text-muted">Ketika OPD mengajukan NPD/SPM, notifikasi WA terkirim otomatis ke <span class="text-navy font-bold">Verifikator 1</span> sesuai SKPD terkait.</span>
                        </li>
                        <li class="list-group-item px-0">
                            <strong>2. Verifikasi Berjenjang</strong><br>
                            <span class="text-muted">Setelah Verifikator 1 menyetujui, giliran <span class="text-warning font-bold">Verifikator 2</span> menerima WA. Setelah Verifikator 2 menyetujui, WA masuk ke <span class="text-primary font-bold">KBUD (Persetujuan Akhir)</span>.</span>
                        </li>
                        <li class="list-group-item px-0">
                            <strong>3. Pengajuan Ditolak</strong><br>
                            <span class="text-muted">Jika ditolak pada tahap manapun, notifikasi WA langsung masuk ke <span class="text-danger font-bold">Admin OPD</span> disertai <strong>Catatan / Alasan Penolakan</strong>.</span>
                        </li>
                        <li class="list-group-item px-0">
                            <strong>4. Pengajuan Disetujui (Selesai)</strong><br>
                            <span class="text-muted">Ketika KBUD menyetujui SPM, notifikasi konfirmasi terbit SP2D terkirim ke WhatsApp Admin OPD.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTables Riwayat Notifikasi WA -->
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="ibox-tools">
                        <button type="button" class="btn btn-default btn-sm" onclick="loadWaLogs()">
                            <i class="fa fa-refresh"></i> Segarkan Riwayat
                        </button>
                    </div>
                    <h5><i class="fa fa-history"></i> Riwayat Pengiriman WhatsApp (100 Terakhir)</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="dtWaLogs" class="table table-striped table-bordered table-hover" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center" style="width: 13%;">Waktu</th>
                                    <th class="text-center" style="width: 14%;">No. WhatsApp</th>
                                    <th class="text-center" style="width: 16%;">Penerima</th>
                                    <th class="text-center" style="width: 30%;">Pesan</th>
                                    <th class="text-center" style="width: 8%;">Status</th>
                                    <th class="text-center" style="width: 14%;">Respon Gateway</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    loadWaLogs();

    $('#IS_ACTIVE').on('change', function() {
        if ($(this).is(':checked')) {
            $('#lblStatus').removeClass('text-danger').addClass('text-success')
                .html('<i class="fa fa-check-circle"></i> AKTIF (Notifikasi Otomatis Berjalan)');
        } else {
            $('#lblStatus').removeClass('text-success').addClass('text-danger')
                .html('<i class="fa fa-power-off"></i> NONAKTIF');
        }
    });
});

function toggleApiKey() {
    var input = document.getElementById('API_KEY');
    var icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa fa-eye';
    }
}

function handleProviderChange() {
    var provider = $('#PROVIDER').val();
    if (provider === 'fonnte') {
        $('#ENDPOINT_URL').val('https://api.fonnte.com/send');
    } else if (provider === 'starsender') {
        $('#ENDPOINT_URL').val('https://starsender.online/api/sendText');
    } else if (provider === 'wablas') {
        if ($('#ENDPOINT_URL').val().includes('fonnte') || $('#ENDPOINT_URL').val().includes('starsender')) {
            $('#ENDPOINT_URL').val('https://phone.wablas.com');
        }
    }
}

function saveSetting(e) {
    e.preventDefault();
    var btn = $('#btnSaveConfig');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyimpan...');

    $.post('<?= base_url('setting/savewagateway') ?>', $('#formWaGateway').serialize(), function(res) {
        btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Pengaturan');
        if (res.status === 'success') {
            swal.fire('Berhasil', res.message, 'success');
        } else {
            swal.fire('Peringatan', res.message || 'Gagal menyimpan.', 'error');
        }
    }).fail(function() {
        btn.prop('disabled', false).html('<i class="fa fa-save"></i> Simpan Pengaturan');
        swal.fire('Error', 'Terjadi kesalahan koneksi server.', 'error');
    });

    return false;
}

function sendTestWa(e) {
    e.preventDefault();
    var phone = $('#test_phone').val().trim();
    var message = $('#test_message').val().trim();
    var btn = $('#btnTest');

    if (!phone) {
        swal.fire('Peringatan', 'Nomor WhatsApp wajib diisi.', 'warning');
        return false;
    }

    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Mengirim...');

    $.post('<?= base_url('setting/testsendwa') ?>', {
        phone: phone,
        message: message
    }, function(res) {
        btn.prop('disabled', false).html('<i class="fa fa-send"></i> Kirim Pesan Sekarang');
        if (res.status === 'success') {
            swal.fire('Terkirim!', res.message, 'success');
            loadWaLogs();
        } else {
            swal.fire('Gagal Kirim', res.message, 'error');
            loadWaLogs();
        }
    }).fail(function() {
        btn.prop('disabled', false).html('<i class="fa fa-send"></i> Kirim Pesan Sekarang');
        swal.fire('Error', 'Gagal menghubungi server.', 'error');
    });

    return false;
}

function loadWaLogs() {
    if ($.fn.DataTable.isDataTable('#dtWaLogs')) {
        $('#dtWaLogs').DataTable().destroy();
    }

    $('#dtWaLogs').DataTable({
        ajax: {
            url: '<?= base_url('setting/getwalogs') ?>',
            dataSrc: ''
        },
        columns: [
            {
                data: null,
                className: 'text-center',
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            {
                data: 'CREATED_AT',
                className: 'text-center',
                render: function(val) {
                    return val ? val : '-';
                }
            },
            {
                data: 'NO_TUJUAN',
                render: function(val) {
                    return '<span class="font-bold text-navy"><i class="fa fa-whatsapp text-success"></i> ' + val + '</span>';
                }
            },
            {
                data: 'NAMA_PENERIMA',
                render: function(val) {
                    return val ? val : '<span class="text-muted">-</span>';
                }
            },
            {
                data: 'PESAN',
                render: function(val) {
                    var safe = $('<div>').text(val).html();
                    return '<div style="max-height: 80px; overflow-y: auto; white-space: pre-line; font-size: 11px;">' + safe + '</div>';
                }
            },
            {
                data: 'STATUS',
                className: 'text-center',
                render: function(val) {
                    if (val === 'SENT') {
                        return '<span class="badge badge-primary"><i class="fa fa-check"></i> TERKIRIM</span>';
                    } else if (val === 'PENDING') {
                        return '<span class="badge badge-warning">PENDING</span>';
                    } else {
                        return '<span class="badge badge-danger"><i class="fa fa-times"></i> GAGAL</span>';
                    }
                }
            },
            {
                data: 'RESPON_GATEWAY',
                render: function(val) {
                    if (!val) return '<span class="text-muted">-</span>';
                    var safe = $('<div>').text(val).html();
                    return '<div style="max-height: 60px; overflow: hidden; font-size: 10px; color: #666;" title="' + safe + '">' + safe + '</div>';
                }
            }
        ],
        language: {
            search: "Cari Riwayat:",
            lengthMenu: "Tampilkan _MENU_ log",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pesan",
            emptyTable: "Belum ada riwayat notifikasi WhatsApp."
        },
        order: [[1, 'desc']]
    });
}
</script>
<?= $this->endSection() ?>
