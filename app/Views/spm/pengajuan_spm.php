<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row animated fadeInRight">
    <div class="col-lg-8">
        <div class="ibox">
            <div class="ibox-title">
                <h3 class="font-bold text-navy"><i class="fa fa-folder-open"></i> <?= esc($Header) ?></h3>
                <small><?= esc($Keterangan) ?></small>
            </div>
            <div class="ibox-content">
                <div class="form-group row mb-0">
                    <label class="col-md-3 col-form-label font-bold">Pilih NPD yang Disetujui</label>
                    <div class="col-md-9">
                        <div class="input-group">
                            <select id="listNPD" class="form-control select2" style="width: 75%;">
                                <option value="">-- Memuat daftar NPD yang telah disetujui... --</option>
                            </select>
                            <div class="input-group-append">
                                <button type="button" class="btn btn-primary" onclick="loadViewSPM($('#listNPD').val())">
                                    <i class="fa fa-arrow-circle-right"></i> Proses Menjadi SPM
                                </button>
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">Pilih nomor pengajuan NPD dari OPD Anda yang telah disetujui oleh BPKAD.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Container for Loaded SPM View -->
    <div id="viewSPM" class="col-lg-12"></div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    loadNPDSuksesList();
});

function loadNPDSuksesList() {
    $.get('<?= base_url('spm/getnpdsukseslist') ?>', function(items) {
        var options = '<option value="">-- Pilih NPD Disetujui --</option>';
        if (items.length === 0) {
            options = '<option value="">(Belum ada NPD yang berstatus disetujui untuk dijadikan SPM)</option>';
        } else {
            items.forEach(function(item) {
                options += '<option value="' + item.ID_PENGAJUAN + '">' + item.ID_PENGAJUAN + ' - ' + item.NM_PROGRAM_KEGIATAN_SUBKEGIATAN + '</option>';
            });
        }
        $('#listNPD').html(options).select2();
    });
}

function loadViewSPM(idNpd) {
    if (!idNpd) {
        swal.fire('Peringatan', 'Silakan pilih NPD yang telah disetujui terlebih dahulu', 'warning');
        return;
    }

    $('html, body').animate({ scrollTop: $('#viewSPM').offset().top - 20 }, 500);
    $('#viewSPM').html('<div class="text-center py-5"><i class="fa fa-spinner fa-spin fa-3x text-navy"></i><p class="mt-2">Memuat formulir pengajuan SPM...</p></div>');

    $.get('<?= base_url('spm/getviewspm') ?>', { ID_NPD: idNpd }, function(html) {
        $('#viewSPM').html(html);
        $('.select2_spm').select2();
        loadDetailTableSPM(idNpd);
        loadFileTableSPM();
    });
}
</script>
<?= $this->endSection() ?>
