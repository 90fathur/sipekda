<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    .row-npd-sipd {
        background-color: #f0f7fd !important;
        border-left: 5px solid #1c84c6 !important;
        transition: background-color 0.3s ease;
    }
    .row-npd-sipd td {
        background-color: transparent !important;
    }
    .row-npd-selesai {
        background-color: #e8f8f5 !important;
        border-left: 5px solid #1ab394 !important;
        transition: background-color 0.3s ease;
    }
    .row-npd-selesai td {
        background-color: transparent !important;
    }
    .btn-proses-sipd {
        font-weight: 600;
        box-shadow: 0 2px 4px rgba(28, 132, 198, 0.2);
    }
    .btn-batal-sipd {
        font-weight: 600;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row animated fadeInRight">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5 class="font-bold text-navy"><i class="fa fa-list-alt"></i> <?= esc($Header) ?></h5>
            </div>
            <div class="ibox-content">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div class="btn-group mb-2" role="group" id="btnFilterNPD">
                        <button type="button" class="btn btn-sm btn-primary active btn-filter" data-status="">Semua Riwayat</button>
                        <button type="button" class="btn btn-sm btn-white btn-filter" data-status="Disetujui BPKAD"><i class="fa fa-check text-navy"></i> Selesai / Disetujui</button>
                        <button type="button" class="btn btn-sm btn-white btn-filter" data-status="Proses di SIPD"><i class="fa fa-check-circle text-info"></i> Sudah di Proses di SIPD</button>
                    </div>
                    <div class="text-muted small mb-2">
                        <i class="fa fa-info-circle text-info"></i> Pengajuan NPD yang selesai dapat dicentang/diproses ke status <strong>Sudah di Proses di SIPD</strong>.
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="dtStatusNPD" class="table table-striped table-bordered table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 4%;">No</th>
                                <th class="text-center" style="width: 15%;">ID Pengajuan NPD</th>
                                <th class="text-center" style="width: 11%;">Tanggal</th>
                                <th class="text-center" style="width: 18%;">Nama OPD</th>
                                <th class="text-center" style="width: 21%;">Program / Kegiatan</th>
                                <th class="text-center" style="width: 10%;">Nominal (Rp)</th>
                                <th class="text-center" style="width: 12%;">Status</th>
                                <th class="text-center" style="width: 10%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal View NPD -->
<div class="modal fade" id="mdlView" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="viewModalContent"></div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var isBPKAD = <?= json_encode($isBPKAD ?? false) ?>;

$(document).ready(function() {
    loadStatusNPD();

    $('.btn-filter').on('click', function() {
        $('.btn-filter').removeClass('active btn-primary').addClass('btn-white');
        $(this).removeClass('btn-white').addClass('active btn-primary');

        var filterVal = $(this).data('status');
        $('#dtStatusNPD').DataTable().column(6).search(filterVal).draw();
    });
});

function loadStatusNPD() {
    $('#dtStatusNPD').DataTable({
        ajax: {
            url: '<?= base_url('spm/getlistpengajuan?IsNPD=true') ?>',
            dataSrc: ''
        },
        createdRow: function(row, data, dataIndex) {
            var s = parseInt(data.KD_STATUS);
            if (s === 6) {
                $(row).addClass('row-npd-sipd');
            } else if (s === 4) {
                $(row).addClass('row-npd-selesai');
            }
        },
        columns: [
            {
                data: null,
                className: 'text-center align-middle',
                render: function(data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { 
                data: 'ID_PENGAJUAN', 
                className: 'font-bold text-center text-nowrap align-middle',
                render: function(data, type, row) {
                    var icon = '';
                    var s = parseInt(row.KD_STATUS);
                    if (s === 4) {
                        icon = '<i class="fa fa-check-circle text-navy mr-1" title="Sudah Diverifikasi"></i> ';
                    } else if (s === 6) {
                        icon = '<i class="fa fa-check-double text-primary mr-1" title="Sudah di Proses di SIPD"></i> ';
                    }
                    return icon + data;
                }
            },
            {
                data: 'TGL_PENGAJUAN',
                className: 'text-center align-middle',
                render: function(d) {
                    return d ? moment(d).format('DD-MM-YYYY HH:mm') : '-';
                }
            },
            { data: 'KD_SKPD', className: 'align-middle' },
            { data: 'NM_PROGRAM_KEGIATAN_SUBKEGIATAN', className: 'align-middle' },
            {
                data: 'ANGGARAN',
                className: 'text-right font-bold align-middle',
                render: function(val) {
                    return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 2 });
                }
            },
            {
                data: 'KD_STATUS',
                className: 'text-center text-nowrap align-middle',
                render: function(s, type, row) {
                    s = parseInt(s);
                    if (s === 1) return '<span class="badge badge-info">Verifikasi 1</span>';
                    if (s === 2) return '<span class="badge badge-warning">Verifikasi 2</span>';
                    if (s === 3) return '<span class="badge badge-primary">Menunggu Persetujuan</span>';
                    if (s === 4) return '<span class="badge badge-success font-bold"><i class="fa fa-check"></i> Disetujui BPKAD</span>';
                    if (s === 5) return '<span class="badge badge-danger" title="' + (row.ALASAN_PENOLAKAN || 'Ditolak') + '">Ditolak</span>';
                    if (s === 6) return '<span class="badge badge-primary font-bold px-2 py-1" style="background-color: #1c84c6; color: #fff;"><i class="fa fa-check-circle"></i> Sudah di Proses di SIPD</span>';
                    return '<span class="badge badge-secondary">Draft</span>';
                }
            },
            {
                data: null,
                className: 'text-center text-nowrap align-middle',
                render: function(row) {
                    var s = parseInt(row.KD_STATUS);
                    var html = '<button type="button" class="btn btn-xs btn-primary btn-outline" onclick="openNPDDetail(\'' + row.ID_PENGAJUAN + '\')" title="Lihat Detail"><i class="fa fa-eye"></i> Detail</button>';

                    if (s === 5) {
                        html += ' <a href="<?= base_url('spm/revisinpd') ?>/' + encodeURIComponent(row.ID_PENGAJUAN) + '" class="btn btn-xs btn-warning ml-1" title="Perbaiki / Revisi Pengajuan"><i class="fa fa-pencil"></i> Revisi</a>';
                    } else if (isBPKAD) {
                        if (s === 4) {
                            html += ' <button type="button" class="btn btn-xs btn-primary font-bold ml-1 btn-proses-sipd" data-id="' + row.ID_PENGAJUAN + '" title="Centang / Ubah Menjadi Sudah di Proses di SIPD"><i class="fa fa-check-square-o"></i> Proses di SIPD</button>';
                        } else if (s === 6) {
                            html += ' <button type="button" class="btn btn-xs btn-outline btn-primary font-bold ml-1 btn-batal-sipd" data-id="' + row.ID_PENGAJUAN + '" title="Sudah di Proses di SIPD (Klik untuk membatalkan)"><i class="fa fa-check-circle"></i> Sudah di SIPD</button>';
                        }
                    }
                    return html;
                }
            }
        ],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ pengajuan NPD"
        }
    });
}

function openNPDDetail(id) {
    $.get('<?= base_url('spm/spmviewnpd') ?>', { idPengajuan: id }, function(html) {
        $('#viewModalContent').html(html);
        $('#mdlView').modal('show');
    });
}

// Tombol Proses di SIPD NPD
$('#dtStatusNPD').on('click', '.btn-proses-sipd', function(e) {
    var id = $(this).data('id');
    Swal.fire({
        title: 'Proses di SIPD?',
        html: 'Apakah NPD <b>' + id + '</b> ini sudah selesai diproses di SIPD dan ingin ditandai statusnya menjadi <b>Sudah di Proses di SIPD</b>?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#1c84c6',
        cancelButtonColor: '#c2c2c2',
        confirmButtonText: '<i class="fa fa-check"></i> Ya, Sudah di SIPD',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('spm/prosessipd') ?>', { id: id, isNpd: true }, function(resp) {
                if (resp.trim() === '00') {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: 'NPD ' + id + ' telah ditandai Sudah di Proses di SIPD.',
                        icon: 'success',
                        timer: 1600,
                        showConfirmButton: false
                    });
                    $('#dtStatusNPD').DataTable().ajax.reload(null, false);
                } else {
                    Swal.fire('Gagal', resp.replace('#', '') || 'Gagal mengubah status.', 'error');
                }
            }).fail(function() {
                Swal.fire('Kesalahan', 'Terjadi kesalahan sistem saat memproses ke SIPD.', 'error');
            });
        }
    });
});

// Tombol Batalkan Status SIPD NPD
$('#dtStatusNPD').on('click', '.btn-batal-sipd', function(e) {
    var id = $(this).data('id');
    Swal.fire({
        title: 'Batalkan Status SIPD?',
        html: 'Kembalikan status NPD <b>' + id + '</b> menjadi <b>Disetujui BPKAD</b>?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ed5565',
        cancelButtonColor: '#c2c2c2',
        confirmButtonText: '<i class="fa fa-undo"></i> Ya, Kembalikan',
        cancelButtonText: 'Tetap di SIPD'
    }).then((result) => {
        if (result.isConfirmed) {
            $.post('<?= base_url('spm/batalprosessipd') ?>', { id: id, isNpd: true }, function(resp) {
                if (resp.trim() === '00') {
                    Swal.fire({
                        title: 'Dibatalkan',
                        text: 'Status NPD ' + id + ' dikembalikan ke Disetujui BPKAD.',
                        icon: 'info',
                        timer: 1600,
                        showConfirmButton: false
                    });
                    $('#dtStatusNPD').DataTable().ajax.reload(null, false);
                } else {
                    Swal.fire('Gagal', resp.replace('#', '') || 'Gagal membatalkan status.', 'error');
                }
            }).fail(function() {
                Swal.fire('Kesalahan', 'Terjadi kesalahan sistem.', 'error');
            });
        }
    });
});
</script>
<?= $this->endSection() ?>
