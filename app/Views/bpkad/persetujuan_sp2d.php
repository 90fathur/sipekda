<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="wrapper wrapper-content">
    <div class="row animated fadeInRight">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <h2 class="font-bold text-navy typewriter" style="margin-bottom: 1px"><?= esc($Header) ?></h2>
                    <h5 class="typewriter"><?= esc($Keterangan) ?></h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="dtDaftar" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%;">No</th>
                                    <th class="text-center" style="width: 15%;">No. SP2D</th>
                                    <th class="text-center" style="width: 8%;">Tanggal</th>
                                    <th class="text-center" style="width: 20%;">Nama OPD</th>
                                    <th class="text-center" style="width: 30%;">Uraian / Deskripsi</th>
                                    <th class="text-center" style="width: 12%;">Nominal</th>
                                    <th class="text-center" style="width: 10%;">Opsi</th>
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
<script type="text/javascript">
    $(document).ready(function () {
        LoadDataList();
    });

    setInterval(function () { Reload(); }, 25000);

    function LoadDataList() {
        $('#dtDaftar').DataTable().destroy();
        $('#dtDaftar').DataTable({
            ajax: {
                url: '<?= base_url('bpkad/getstatussipd') ?>',
                dataSrc: '',
                type: 'GET',
                data: { LIMIT: 0, CODE: '100' }
            },
            columns: [
                {
                    data: null, className: 'text-center', width: '5%', render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'NO_SP2D', className: 'text-center id font-bold' },
                { data: 'CREATE', className: 'text-center', render: function (d) { return d ? moment(d).format("DD-MM-YYYY") : '-' } },
                { data: 'NOTE', className: 'text-left' },
                { data: 'DESC_SP2D', className: 'text-left' },
                { data: 'NOMINAL_SP2D', className: 'text-right font-bold', render: $.fn.dataTable.render.number('.', ',', 2) },
                {
                    data: null,
                    className: 'text-center',
                    render: function () {
                        return '<button type="button" class="btn btn-xs btn-primary btn-outline btn-setuju mr-1" data-toggle="tooltip" title="Setuju Pencairan"><i class="fa fa-check"></i> Proses</button> ' +
                               '<button type="button" class="btn btn-xs btn-danger btn-outline btn-tolak" data-toggle="tooltip" title="Batalkan"><i class="fa fa-times"></i> Batal</button>';
                    }
                }
            ],
            autoWidth: false,
            language: { search: 'Cari:' },
            aLengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, "All"]
            ],
            iDisplayLength: 25,
            drawCallback: function (settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }

    $('#dtDaftar').on('click', 'button.btn-setuju', function (e) {
        var id = $(this).closest("tr").find(".id").text();

        Swal.fire({
            title: 'Konfirmasi Pencairan SP2D',
            text: 'Apakah Anda yakin ingin memproses pencairan untuk SP2D No: ' + id + '?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Proses Pencairan',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise(function () {
                    Accept(id);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    });

    $('#dtDaftar').on('click', 'button.btn-tolak', function (e) {
        var id = $(this).closest("tr").find(".id").text();

        Swal.fire({
            title: 'Batalkan SP2D?',
            text: 'Apakah Anda yakin ingin membatalkan SP2D No: ' + id + '?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Batalkan',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise(function () {
                    Reject(id);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    });

    function Accept(id) {
        $.post('<?= base_url('bpkad/setproses') ?>', { NO_SP2D: id }, function (data) {
            if (data === '00') {
                Swal.fire('Sukses', 'Pencairan SP2D berhasil diproses.', 'success');
            } else {
                Swal.fire('Informasi', data, 'info');
            }
            Reload();
        }).fail(function(xhr) {
            Swal.fire('Kesalahan', 'Gagal memproses ke Bank Sulselbar.', 'error');
        });
    }

    function Reject(id) {
        $.post('<?= base_url('bpkad/cancelproses') ?>', { NO_SP2D: id }, function (data) {
            if (data === '00') {
                Swal.fire('Sukses', 'SP2D telah berhasil dibatalkan.', 'success');
            } else {
                Swal.fire('Informasi', data, 'info');
            }
            Reload();
        }).fail(function(xhr) {
            Swal.fire('Kesalahan', 'Gagal membatalkan proses.', 'error');
        });
    }

    function Reload() {
        $('#dtDaftar').DataTable().ajax.reload(null, false);
    }
</script>
<?= $this->endSection() ?>
