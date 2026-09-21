<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row animated fadeInRight">
    <div class="col-md-12">
        <div class="ibox">
            <div class="ibox-title">
                <h5><i class="fa fa-filter text-navy"></i> Filter Periode Transaksi SP2D</h5>
                <div class="ibox-tools">
                    <a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                </div>
            </div>
            <div class="ibox-content">
                <div class="row align-items-center">
                    <div class="col-lg-6 col-md-8">
                        <label class="font-bold mb-1"><i class="fa fa-calendar text-navy"></i> Rentang Tanggal Transaksi:</label>
                        <div class="input-group">
                            <input type="date" class="form-control" id="eTglAwal" value="<?= date('Y-01-01') ?>" />
                            <div class="input-group-prepend input-group-append">
                                <span class="input-group-text bg-muted font-bold">s/d</span>
                            </div>
                            <input type="date" class="form-control" id="eTglAkhir" value="<?= date('Y-12-31') ?>" />
                            <div class="input-group-append">
                                <button type="button" id="btnProses" class="btn btn-primary font-bold">
                                    <i class="fa fa-search"></i> Tampilkan
                                </button>
                                <button type="button" id="btnReset" class="btn btn-default font-bold ml-1" title="Tampilkan Semua">
                                    <i class="fa fa-refresh"></i> Semua
                                </button>
                            </div>
                        </div>
                        <small class="form-text text-muted">Periode default tahun berjalan (<?= date('Y') ?>). Klik 'Semua' untuk menampilkan seluruh transaksi tanpa batasan tanggal.</small>
                    </div>
                </div>

                <hr class="my-3"/>

                <div class="table-responsive">
                    <table id="dtDaftar" class="table table-striped table-bordered table-hover" style="width: 100%;">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 4%;">No</th>
                                <th class="text-center" style="width: 18%;">No. SP2D</th>
                                <th class="text-center" style="width: 10%;">Tanggal</th>
                                <th class="text-center" style="width: 22%;">Nama OPD</th>
                                <th class="text-center" style="width: 31%;">Uraian / Deskripsi</th>
                                <th class="text-center" style="width: 15%;">Nominal (Rp)</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script type="text/javascript">
    $(document).ready(function () {
        applyFilter();
    });

    function applyFilter() {
        var tglAwal = $('#eTglAwal').val();
        var tglAkhir = $('#eTglAkhir').val();
        var tmpTgl = (tglAwal && tglAkhir) ? (tglAwal + '|' + tglAkhir) : '';
        LoadDataList(tmpTgl);
    }

    function LoadDataList(TMP_TGL) {
        if ($.fn.DataTable.isDataTable('#dtDaftar')) {
            $('#dtDaftar').DataTable().destroy();
        }

        $('#dtDaftar').DataTable({
            ajax: {
                url: '<?= base_url('sipd/getstatussipd') ?>',
                dataSrc: '',
                type: 'GET',
                data: { LIMIT: 100, TMP_TGL: TMP_TGL }
            },
            dom: "<'row mb-2'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'copy', className: 'btn-sm btn-white' },
                { extend: 'csv', className: 'btn-sm btn-white' },
                { extend: 'excel', title: 'Monitoring_SP2D', className: 'btn-sm btn-primary' },
                { extend: 'pdf', title: 'Monitoring_SP2D', orientation: 'landscape', pageSize: 'A4', className: 'btn-sm btn-danger' },
                { extend: 'print', className: 'btn-sm btn-white' }
            ],
            columns: [
                {
                    data: null,
                    className: 'text-center',
                    width: '4%',
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'NO_SP2D',
                    width: '18%',
                    className: 'text-center font-bold',
                    render: function(d) {
                        return '<span class="text-navy font-bold" style="font-size: 11px; font-family: monospace;">' + (d || '-') + '</span>';
                    }
                },
                {
                    data: 'CREATE',
                    width: '10%',
                    className: 'text-center',
                    render: function (d) {
                        return d ? moment(d).format("DD-MM-YYYY") : '-';
                    }
                },
                {
                    data: 'NOTE',
                    className: 'text-left font-bold',
                    width: '22%'
                },
                {
                    data: 'DESC_SP2D',
                    className: 'text-left',
                    width: '31%'
                },
                {
                    data: 'NOMINAL_SP2D',
                    width: '15%',
                    className: 'text-right font-bold text-success',
                    render: $.fn.dataTable.render.number('.', ',', 2)
                }
            ],
            autoWidth: false,
            language: {
                search: 'Cari:',
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                zeroRecords: "Tidak ada transaksi yang cocok",
                emptyTable: "Belum ada transaksi pencairan SP2D",
                loadingRecords: "<i class='fa fa-spinner fa-spin'></i> Memuat data dari Bank Sulselbar..."
            },
            aLengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, "Semua"]
            ],
            iDisplayLength: 25,
            drawCallback: function (settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }

    $('#btnProses').on('click', function () {
        applyFilter();
    });

    $('#btnReset').on('click', function () {
        $('#eTglAwal').val('');
        $('#eTglAkhir').val('');
        LoadDataList('');
    });
</script>
<?= $this->endSection() ?>
