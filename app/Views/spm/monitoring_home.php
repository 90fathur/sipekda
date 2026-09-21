<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($Title ?? 'Monitoring Pengajuan - SIPEKDA ASSAMI') ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/font-awesome/css/font-awesome.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/animate.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/plugins/dataTables/datatables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/plugins/sweetalert/sweetalert2.min.css') ?>">

    <style>
        body {
            background-color: #f3f3f4;
        }
        .page-header-title {
            padding: 20px 0 10px 0;
        }
        .table > tbody > tr > td {
            vertical-align: middle;
        }
    </style>
</head>

<body class="gray-bg">

<div class="container-fluid p-4 animated fadeInDown">
    <div class="row">
        <div class="col-lg-12 text-center page-header-title">
            <h1 class="font-bold text-navy" style="letter-spacing: 1px;"><i class="fa fa-television"></i> MONITORING NPD &amp; SPP/SPM</h1>
            <h5 class="text-muted">Monitoring pengajuan berkas keuangan daerah yang masuk ke BPKAD secara langsung (Real-Time).</h5>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h3 class="font-bold text-navy"><i class="fa fa-folder-open"></i> PENGAJUAN NPD</h3>
                    <small class="text-muted">Daftar semua pengajuan NPD yang masuk ke BPKAD</small>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="dtDaftarNPD" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center" style="width: 15%;">ID Pengajuan</th>
                                    <th class="text-center" style="width: 10%;">Tanggal</th>
                                    <th class="text-center" style="width: 35%;">Program Kegiatan / Sub Kegiatan</th>
                                    <th class="text-center" style="width: 15%;">Mata Anggaran</th>
                                    <th class="text-center" style="width: 12%;">Anggaran</th>
                                    <th class="text-center" style="width: 8%;">Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12 mt-3">
            <div class="ibox">
                <div class="ibox-title">
                    <h3 class="font-bold text-navy"><i class="fa fa-file-text"></i> PENGAJUAN SPP / SPM</h3>
                    <small class="text-muted">Daftar semua pengajuan SPP/SPM yang masuk ke BPKAD</small>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="dtDaftarSPM" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center" style="width: 15%;">ID Pengajuan</th>
                                    <th class="text-center" style="width: 10%;">Tanggal</th>
                                    <th class="text-center" style="width: 35%;">Program Kegiatan / Sub Kegiatan</th>
                                    <th class="text-center" style="width: 15%;">Mata Anggaran</th>
                                    <th class="text-center" style="width: 12%;">Anggaran</th>
                                    <th class="text-center" style="width: 8%;">Status</th>
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

<script src="<?= base_url('assets/js/jquery-3.1.1.min.js') ?>"></script>
<script src="<?= base_url('assets/js/popper.min.js') ?>"></script>
<script src="<?= base_url('assets/js/bootstrap.js') ?>"></script>
<script src="<?= base_url('assets/js/plugins/metisMenu/jquery.metisMenu.js') ?>"></script>
<script src="<?= base_url('assets/js/plugins/slimscroll/jquery.slimscroll.min.js') ?>"></script>
<script src="<?= base_url('assets/js/plugins/dataTables/datatables.min.js') ?>"></script>
<script src="<?= base_url('assets/js/plugins/dataTables/dataTables.bootstrap4.min.js') ?>"></script>
<script src="<?= base_url('assets/js/moment.min.js') ?>"></script>

<script type="text/javascript">
    $(document).ready(function () {
        LoadDataListNPD();
        LoadDataListSPM();

        // Auto refresh every 30 seconds
        setInterval(function() {
            $('#dtDaftarNPD').DataTable().ajax.reload(null, false);
            $('#dtDaftarSPM').DataTable().ajax.reload(null, false);
        }, 30000);
    });

    function renderStatus(s) {
        s = parseInt(s);
        if (s === 1) return '<span class="badge badge-info p-2">Menunggu Verifikasi 1</span>';
        if (s === 2) return '<span class="badge badge-warning p-2">Menunggu Verifikasi 2</span>';
        if (s === 3) return '<span class="badge badge-primary p-2">Menunggu Persetujuan</span>';
        if (s === 4) return '<span class="badge badge-success p-2">Disetujui / SP2D</span>';
        if (s === 5) return '<span class="badge badge-danger p-2">Ditolak</span>';
        return '<span class="badge badge-secondary p-2">' + s + '</span>';
    }

    function LoadDataListNPD() {
        $('#dtDaftarNPD').DataTable().destroy();
        $('#dtDaftarNPD').DataTable({
            ajax: {
                url: '<?= base_url('spm/getlistmonitoring') ?>',
                dataSrc: '',
                type: 'GET',
                data: { IsNPD: true }
            },
            columns: [
                {
                    data: null, className: 'text-center', width: '5%', render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'ID_PENGAJUAN', className: 'text-center font-bold' },
                { data: 'TGL_PENGAJUAN', className: 'text-center', render: function (d) { return d ? moment(d).format("DD-MM-YYYY") : '-' } },
                { data: 'NM_PROGRAM_KEGIATAN_SUBKEGIATAN', className: 'text-left' },
                { data: 'NM_REKENING_BELANJA', className: 'text-left' },
                { data: 'ANGGARAN', className: 'text-right font-bold', render: $.fn.dataTable.render.number('.', ',', 2) },
                {
                    data: 'KD_STATUS',
                    className: 'text-center',
                    render: function (s) {
                        return renderStatus(s);
                    }
                }
            ],
            autoWidth: false,
            language: { search: 'Cari:' },
            iDisplayLength: 10,
            order: [[2, 'desc']]
        });
    }

    function LoadDataListSPM() {
        $('#dtDaftarSPM').DataTable().destroy();
        $('#dtDaftarSPM').DataTable({
            ajax: {
                url: '<?= base_url('spm/getlistmonitoring') ?>',
                dataSrc: '',
                type: 'GET',
                data: { IsNPD: false }
            },
            columns: [
                {
                    data: null, className: 'text-center', width: '5%', render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'ID_PENGAJUAN', className: 'text-center font-bold' },
                { data: 'TGL_PENGAJUAN', className: 'text-center', render: function (d) { return d ? moment(d).format("DD-MM-YYYY") : '-' } },
                { data: 'NM_PROGRAM_KEGIATAN_SUBKEGIATAN', className: 'text-left' },
                { data: 'NM_REKENING_BELANJA', className: 'text-left' },
                { data: 'ANGGARAN', className: 'text-right font-bold', render: $.fn.dataTable.render.number('.', ',', 2) },
                {
                    data: 'KD_STATUS',
                    className: 'text-center',
                    render: function (s) {
                        return renderStatus(s);
                    }
                }
            ],
            autoWidth: false,
            language: { search: 'Cari:' },
            iDisplayLength: 10,
            order: [[2, 'desc']]
        });
    }
</script>
</body>
</html>
