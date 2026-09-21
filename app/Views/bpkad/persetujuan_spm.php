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
                                    <th class="text-center" style="width: 15%;">ID Pengajuan</th>
                                    <th class="text-center" style="width: 10%;">Tanggal</th>
                                    <th class="text-center" style="width: 25%;">Nama OPD</th>
                                    <th class="text-center" style="width: 25%;">Program Kegiatan / Sub Kegiatan</th>
                                    <th class="text-center" style="width: 12%;">Anggaran</th>
                                    <th class="text-center" style="width: 8%;">Opsi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div id="dvView" class="col-lg-12"></div>
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
                url: '<?= base_url('bpkad/getlistpengajuan') ?>',
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
                { data: 'ID_PENGAJUAN', className: 'text-center id font-bold' },
                { data: 'TGL_PENGAJUAN', className: 'text-center', render: function (d) { return d ? moment(d).format("DD-MM-YYYY") : '-' } },
                { data: 'KD_SKPD', className: 'text-left' },
                { data: 'NM_PROGRAM_KEGIATAN_SUBKEGIATAN', className: 'text-left' },
                { data: 'ANGGARAN', className: 'text-right font-bold', render: $.fn.dataTable.render.number('.', ',', 2) },
                {
                    data: null,
                    className: 'text-center',
                    width: '9%',
                    defaultContent: '<button type="button" class="btn btn-xs btn-info btn-outline btn-detail" data-toggle="tooltip" data-placement="top" title="Detail"><i class="fa fa-search"></i> Detail</button>'
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

    function Accept(id) {
        $.post('<?= base_url('bpkad/acceptpengajuan') ?>', { id: id }, function (data) {
            Swal.fire('Informasi', 'Pengajuan telah di setujui.', 'success');
            Reload();
            $('#dvView').empty();
        });
    }

    function Reject(id, alasan) {
        $.post('<?= base_url('bpkad/rejectpengajuan') ?>', { id: id, alasan: alasan }, function (data) {
            Swal.fire('Informasi', 'Pengajuan telah di tolak.', 'success');
            Reload();
            $('#dvView').empty();
        });
    }

    function Detail(id) {
        $.get('<?= base_url('bpkad/spmview') ?>', { idPengajuan: id }, function (data) {
            $('#dvView').html(data);
            $('html, body').animate({
                scrollTop: $("#dvView").offset().top
            }, 500);

            LoadDataDetailList($('#ID_NPD').val());
            GetAllFiles(id);
        });
    }

    function Reload() {
        $('#dtDaftar').DataTable().ajax.reload(null, false);
    }

    $('#dtDaftar').on('click', 'button.btn-detail', function (e) {
        var id = $(this).closest("tr").find(".id").text();
        Detail(id);
    });

    const colors = ['text-info', 'text-warning', 'text-success', 'text-primary', 'text-danger'];
    let colorIndex = 0;
    function getNextColorClass() {
        const color = colors[colorIndex];
        colorIndex = (colorIndex + 1) % colors.length;
        return color;
    }

    function GetAllFiles(idPengajuan) {
        $.ajax({
            url: '<?= base_url('spm/getallfiles') ?>',
            data: { idPengajuan: idPengajuan },
            method: 'GET',
            dataType: 'json',
            success: function (files) {
                $('#fileContainer').empty();
                if (!files || files.length === 0) {
                    $('#fileContainer').html('<div class="col-12 text-center text-muted p-3">Tidak ada berkas terlampir.</div>');
                    return;
                }
                files.forEach(file => {
                    const fileUrl = `<?= base_url('uploads/pdf/') ?>/${encodeURIComponent(file.FileName)}`;
                    const fileHtml = `
                        <div class="col-sm-6 text-center mb-3">
                            <div class="file-box">
                                <div class="file">
                                    <div class="icon">
                                        <i class="fa fa-file-pdf-o ${getNextColorClass()}"></i>
                                    </div>
                                    <div class="file-name">
                                        <a href="${fileUrl}" target="_blank">${file.Name || file.FileName}</a>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    $('#fileContainer').append(fileHtml);
                });
            },
            error: function (error) {
                console.error('Error fetching files:', error);
            }
        });
    }

    function LoadDataDetailList(id) {
        $('#dtDaftarBelanja').DataTable().destroy();
        $('#dtDaftarBelanja').DataTable({
            ajax: {
                url: '<?= base_url('spm/getdatadetail') ?>',
                dataSrc: '',
                type: 'GET',
                data: { NO_NPD_SPM: id }
            },
            columns: [
                {
                    data: null, className: 'text-center', width: '5%', render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { data: 'NM_REKENING_BELANJA', className: 'text-left' },
                { data: 'ANGGARAN', className: 'text-right font-bold', render: $.fn.dataTable.render.number('.', ',', 2), width: '30%' }
            ],
            autoWidth: false,
            language: { search: 'Cari:' },
            aLengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, "All"]
            ],
            iDisplayLength: 10
        });
    }
</script>
<?= $this->endSection() ?>
