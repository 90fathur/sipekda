<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row animated fadeInRight">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5 class="font-bold text-navy"><i class="fa fa-list-alt"></i> <?= esc($Header) ?></h5>
            </div>
            <div class="ibox-content">
                <div class="table-responsive">
                    <table id="dtStatusSPM" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 5%;">No</th>
                                <th class="text-center" style="width: 15%;">ID Pengajuan</th>
                                <th class="text-center" style="width: 12%;">Tanggal</th>
                                <th class="text-center" style="width: 20%;">Nama OPD</th>
                                <th class="text-center" style="width: 23%;">Program / Kegiatan</th>
                                <th class="text-center" style="width: 13%;">Nominal (Rp)</th>
                                <th class="text-center" style="width: 12%;">Status</th>
                                <th class="text-center" style="width: 8%;">Detail</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal View SPM -->
<div class="modal fade" id="mdlView" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="viewModalContent"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
$(document).ready(function() {
    loadStatusSPM();
});

function loadStatusSPM() {
    $('#dtStatusSPM').DataTable({
        ajax: {
            url: '<?= base_url('spm/getlistpengajuan?IsNPD=false') ?>',
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
            { data: 'ID_PENGAJUAN', className: 'font-bold text-center' },
            {
                data: 'TGL_PENGAJUAN',
                className: 'text-center',
                render: function(d) {
                    return d ? moment(d).format('DD-MM-YYYY HH:mm') : '-';
                }
            },
            { data: 'KD_SKPD' },
            { data: 'NM_PROGRAM_KEGIATAN_SUBKEGIATAN' },
            {
                data: 'ANGGARAN',
                className: 'text-right font-bold',
                render: function(val) {
                    return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 2 });
                }
            },
            {
                data: 'KD_STATUS',
                className: 'text-center',
                render: function(s, type, row) {
                    s = parseInt(s);
                    if (s === 1) return '<span class="badge badge-info">Verifikasi 1</span>';
                    if (s === 2) return '<span class="badge badge-warning">Verifikasi 2</span>';
                    if (s === 3) return '<span class="badge badge-primary">Menunggu Persetujuan</span>';
                    if (s === 4) return '<span class="badge badge-success">Selesai / SP2D</span>';
                    if (s === 5) return '<span class="badge badge-danger" title="' + (row.ALASAN_PENOLAKAN || 'Ditolak') + '">Ditolak</span>';
                    return '<span class="badge badge-secondary">Draft</span>';
                }
            },
            {
                data: null,
                className: 'text-center',
                render: function(row) {
                    return '<button type="button" class="btn btn-xs btn-primary" onclick="openSPMDetail(\'' + row.ID_PENGAJUAN + '\')"><i class="fa fa-eye"></i></button>';
                }
            }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengajuan"
        }
    });
}

function openSPMDetail(id) {
    $.get('<?= base_url('spm/spmview') ?>', { idPengajuan: id }, function(html) {
        $('#viewModalContent').html(html);
        $('#mdlView').modal('show');
    });
}
</script>
<?= $this->endSection() ?>
