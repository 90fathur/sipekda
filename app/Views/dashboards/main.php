<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="wrapper wrapper-content animated fadeInRight" style="padding-top: 0;">
    <!-- Stat Widgets -->
    <div class="row">
        <div class="col-md-4">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="ibox-tools">
                        <span class="label label-warning float-right">Year</span>
                    </div>
                    <h5>Total SP2D</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins text-navy" id="txtTotalMonth">...</h1>
                    <div class="stat-percent text-info"><?= date('d-m-Y') ?> <i class="fa fa-clock"></i></div>
                    <small>Total SP2D Yang Di Proses</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="ibox-tools">
                        <span class="label label-success float-right">Monthly</span>
                    </div>
                    <h5>Nominal SP2D</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins text-success" id="txtNominalMonth">...</h1>
                    <div class="stat-percent text-info"><?= date('d-m-Y') ?> <i class="fa fa-clock"></i></div>
                    <small>Total Nominal Pencairan Bulan Ini</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="ibox-tools">
                        <span class="label label-primary float-right">Year</span>
                    </div>
                    <h5>Total Nominal SP2D</h5>
                </div>
                <div class="ibox-content">
                    <h1 class="no-margins text-primary" id="txtNominalYear">...</h1>
                    <div class="stat-percent text-info"><?= date('d-m-Y') ?> <i class="fa fa-clock"></i></div>
                    <small>Total Keseluruhan Nominal Pencairan</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row">
        <div class="col-lg-5">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><i class="fa fa-pie-chart text-navy"></i> Pagu & Realisasi Anggaran</h5>
                </div>
                <div class="ibox-content text-center">
                    <div style="height: 290px; position: relative;">
                        <canvas id="TierChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-7">
            <div class="ibox">
                <div class="ibox-title">
                    <h5><i class="fa fa-bar-chart text-navy"></i> 5 Realisasi Belanja Terbesar</h5>
                </div>
                <div class="ibox-content text-center">
                    <div style="height: 290px; position: relative;">
                        <canvas id="RekeningChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Table 50 Pencairan Terakhir -->
    <div class="row">
        <div class="col-lg-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h5 class="font-bold text-navy"><i class="fa fa-list"></i> Daftar 50 Pencairan Terakhir SP2D</h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="dtDaftar" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center" style="width: 20%;">No. SP2D</th>
                                    <th class="text-center" style="width: 12%;">Tanggal</th>
                                    <th class="text-center" style="width: 23%;">Nama OPD</th>
                                    <th class="text-center" style="width: 25%;">Uraian / Deskripsi</th>
                                    <th class="text-center" style="width: 15%;">Nominal</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Data filled via AJAX -->
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
var tierChartInstance = null;
var rekeningChartInstance = null;

$(document).ready(function () {
    loadDataList();
    reloadStats();

    // Auto reload every 30s
    setInterval(function () {
        reloadStats();
        if ($.fn.DataTable.isDataTable('#dtDaftar')) {
            $('#dtDaftar').DataTable().ajax.reload(null, false);
        }
    }, 30000);
});

function reloadStats() {
    getPaguChart();
    getRekeningChart();
    getTotalSP2D();
    getNominalMonth();
    getNominalYear();
}

function loadDataList() {
    if ($.fn.DataTable.isDataTable('#dtDaftar')) {
        $('#dtDaftar').DataTable().destroy();
    }

    $('#dtDaftar').DataTable({
        ajax: {
            url: '<?= base_url('sipd/getstatussipd') ?>',
            dataSrc: '',
            type: 'GET',
            data: { LIMIT: 50 },
            error: function() {
                // fallback to local spm if external api unreachable
                console.log('Using local fallback for table');
            }
        },
        columns: [
            {
                data: null,
                className: 'text-center',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { data: 'NO_SP2D', className: 'text-center font-bold' },
            {
                data: 'CREATE',
                className: 'text-center',
                render: function (d) {
                    return d ? moment(d).format('DD-MM-YYYY') : '-';
                }
            },
            { data: 'NOTE', className: 'text-left' },
            { data: 'DESC_SP2D', className: 'text-left' },
            {
                data: 'NOMINAL_SP2D',
                className: 'text-right font-bold',
                render: function(val) {
                    return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 2 });
                }
            }
        ],
        autoWidth: false,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ baris",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            zeroRecords: "Belum ada transaksi pencairan SP2D",
            emptyTable: "Belum ada transaksi pencairan SP2D"
        },
        pageLength: 10,
        aLengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Semua"]]
    });
}

function getPaguChart() {
    $.get('<?= base_url('dashboards/getpagugrafik') ?>', function (data) {
        var ctx = document.getElementById("TierChart");
        if (!ctx) return;
        
        if (tierChartInstance) {
            tierChartInstance.destroy();
        }

        tierChartInstance = new Chart(ctx.getContext("2d"), {
            type: "doughnut",
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, chartData) {
                            var val = chartData.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                            return chartData.labels[tooltipItem.index] + ': Rp ' + parseFloat(val).toLocaleString('id-ID');
                        }
                    }
                }
            }
        });
    });
}

function getRekeningChart() {
    $.get('<?= base_url('dashboards/top5belanjachart') ?>', function (data) {
        var ctx = document.getElementById("RekeningChart");
        if (!ctx) return;

        if (rekeningChartInstance) {
            rekeningChartInstance.destroy();
        }

        rekeningChartInstance = new Chart(ctx.getContext("2d"), {
            type: "bar",
            data: {
                labels: data.labels,
                datasets: data.datasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: {
                    position: 'bottom'
                },
                scales: {
                    xAxes: [{
                        ticks: { display: false },
                        gridLines: { display: false }
                    }],
                    yAxes: [{
                        ticks: {
                            beginAtZero: true,
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toLocaleString('id-ID') + ' Jt';
                            }
                        }
                    }]
                },
                tooltips: {
                    callbacks: {
                        label: function(tooltipItem, chartData) {
                            var ds = chartData.datasets[tooltipItem.datasetIndex];
                            return ds.label + ': Rp ' + parseFloat(tooltipItem.yLabel).toLocaleString('id-ID');
                        }
                    }
                }
            }
        });
    });
}

function getTotalSP2D() {
    $.get('<?= base_url('dashboards/totalsp2d') ?>', function (data) {
        $('#txtTotalMonth').text(data);
    });
}

function getNominalMonth() {
    $.get('<?= base_url('dashboards/nominalspmmonthly') ?>', function (data) {
        $('#txtNominalMonth').text('Rp ' + data);
    });
}

function getNominalYear() {
    $.get('<?= base_url('dashboards/nominalspmyearly') ?>', function (data) {
        $('#txtNominalYear').text('Rp ' + data);
    });
}
</script>
<?= $this->endSection() ?>
