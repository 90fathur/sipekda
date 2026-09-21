<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-lg-10">
        <h2><?= esc($header) ?></h2>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= base_url('dashboards/main') ?>">Home</a></li>
            <li class="breadcrumb-item">Monitoring</li>
            <li class="breadcrumb-item active"><strong><?= esc($title) ?></strong></li>
        </ol>
    </div>
</div>

<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><?= esc($title) ?></h5>
                    <div class="ibox-tools">
                        <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                    </div>
                </div>
                <div class="ibox-content">
                    <p><?= esc($keterangan) ?></p>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover dataTables-example" id="table-monitoring">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>No Transaksi / SP2D</th>
                                    <th>Tanggal</th>
                                    <th>Keterangan</th>
                                    <th>Nominal</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada data transaksi tersimpan.</td>
                                </tr>
                            </tbody>
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
    $(document).ready(function(){
        $('#table-monitoring').DataTable({
            pageLength: 25,
            responsive: true,
            dom: '<"html5buttons"B>lTfgitp',
            buttons: [
                { extend: 'copy'},
                { extend: 'csv'},
                { extend: 'excel', title: 'DaftarTransaksi'},
                { extend: 'pdf', title: 'DaftarTransaksi'},
                { extend: 'print'}
            ]
        });
    });
</script>
<?= $this->endSection() ?>
