<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="wrapper wrapper-content">
    <div class="row animated fadeInRight">
        <div class="col-md-12">
            <div class="ibox">
                <div class="ibox-title">
                    <div class="float-right d-flex align-items-center" style="gap: 8px;">
                        <?php if ($JENIS_USER !== 'User'): ?>
                            <select id="filterSKPD" class="form-control input-sm select2" style="width: 250px; display: inline-block;" onchange="LoadDataList();">
                                <option value="">-- Semua OPD / SKPD --</option>
                                <?php foreach ($ListSKPD as $skpd): ?>
                                    <option value="<?= esc($skpd['KD_SKPD']) ?>"><?= esc($skpd['NM_SKPD']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <span class="badge badge-primary p-2" style="font-size: 12px; margin-right: 6px;"><i class="fa fa-building-o"></i> <?= esc($NM_UNITKER) ?></span>
                        <?php endif; ?>

                        <label class="btn btn-primary" for="eFILE" style="margin-bottom: 0;">
                            <i class="fa fa-upload"></i> Upload File Excel
                        </label>
                        <input id="eFILE" type="file" style="display:none" onchange="UploadFile();" accept=".xls,.xlsx" />
                    </div>
                    <h2 class="font-bold text-navy typewriter" style="margin-bottom: 1px"><?= esc($Header) ?></h2>
                    <h5 class="typewriter"><?= esc($Keterangan) ?></h5>
                </div>
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table id="dtDaftar" class="table table-striped table-bordered table-hover" cellpadding="0" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%;">No</th>
                                    <th class="text-center" style="width: 15%;">Kode Rekening</th>
                                    <th class="text-center">Nama Rekening Belanja</th>
                                    <th class="text-center" style="width: 14%;">Pagu (Rp)</th>
                                    <th class="text-center" style="width: 14%;">Realisasi (Rp)</th>
                                    <th class="text-center" style="width: 14%;">Sisa Pagu (Rp)</th>
                                    <th class="text-center" style="width: 7%;">%</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr style="background-color: #f5f5f6; font-weight: bold;">
                                    <th colspan="3" class="text-right" style="vertical-align: middle;">TOTAL KESELURUHAN:</th>
                                    <th class="text-right text-success" id="totPagu" style="font-size: 13px;">Rp 0,00</th>
                                    <th class="text-right text-warning" id="totRealisasi" style="font-size: 13px;">Rp 0,00</th>
                                    <th class="text-right text-navy" id="totSisa" style="font-size: 13px;">Rp 0,00</th>
                                    <th class="text-center" id="totPersen" style="font-size: 13px;">0%</th>
                                </tr>
                            </tfoot>
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
        if ($('#filterSKPD').length) {
            $('#filterSKPD').select2({ theme: 'bootstrap4', placeholder: '-- Semua OPD / SKPD --', allowClear: true });
            $('#filterSKPD').on('change', function () {
                LoadDataList();
            });
        }
        LoadDataList();
    });

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function formatRupiah(val) {
        return 'Rp ' + parseFloat(val || 0).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function LoadDataList() {
        if ($.fn.DataTable.isDataTable('#dtDaftar')) {
            $('#dtDaftar').DataTable().destroy();
        }

        $('#dtDaftar').DataTable({
            ajax: {
                url: '<?= base_url('pagu/getpaguanggaran') ?>',
                dataSrc: '',
                type: 'GET',
                data: function(d) {
                    var skpd = $('#filterSKPD').val();
                    if (skpd) {
                        d.KD_SKPD = skpd;
                    }
                }
            },
            dom: "<'row mb-2'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4 text-center'B><'col-sm-12 col-md-4'f>><'row'<'col-sm-12'tr>><'row mt-3'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
            buttons: [
                { extend: 'copy', className: 'btn-sm btn-white' },
                { extend: 'csv', className: 'btn-sm btn-white' },
                { extend: 'excel', title: 'Pagu_Anggaran', className: 'btn-sm btn-primary' },
                { extend: 'pdf', title: 'Pagu_Anggaran', orientation: 'landscape', pageSize: 'A4', className: 'btn-sm btn-danger' },
                { extend: 'print', className: 'btn-sm btn-white' }
            ],
            order: [],
            columns: [
                {
                    data: null, className: 'text-center', width: '4%', render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'KD_REKENING_BELANJA',
                    width: '15%',
                    className: 'text-center',
                    render: function(d) {
                        return '<span class="badge badge-info" style="font-size: 11px; font-family: monospace;">' + (d || '-') + '</span>';
                    }
                },
                {
                    data: 'NM_REKENING_BELANJA',
                    className: 'text-left font-bold',
                    render: function(data, type, row) {
                        var html = '<div>' + escHtml(data) + '</div>';
                        if (row.NM_SKPD) {
                            html += '<div style="margin-top: 2px;"><span class="badge badge-secondary" style="font-size: 10px; font-weight: normal;"><i class="fa fa-building-o"></i> ' + escHtml(row.NM_SKPD) + '</span></div>';
                        }
                        return html;
                    }
                },
                {
                    data: 'PAGU',
                    width: '14%',
                    className: 'text-right font-bold text-success',
                    render: $.fn.dataTable.render.number('.', ',', 2)
                },
                {
                    data: 'REALISASI',
                    width: '14%',
                    className: 'text-right font-bold text-warning',
                    render: $.fn.dataTable.render.number('.', ',', 2)
                },
                {
                    data: 'SISA_PAGU',
                    width: '14%',
                    className: 'text-right font-bold text-navy',
                    render: $.fn.dataTable.render.number('.', ',', 2)
                },
                {
                    data: 'PERSEN',
                    width: '7%',
                    className: 'text-center font-bold',
                    render: function(d) {
                        var val = parseFloat(d || 0);
                        var badgeClass = val > 80 ? 'badge-danger' : (val > 50 ? 'badge-warning' : 'badge-primary');
                        return '<span class="badge ' + badgeClass + '">' + val.toFixed(1) + '%</span>';
                    }
                }
            ],
            autoWidth: false,
            language: {
                search: 'Cari:',
                lengthMenu: "Tampilkan _MENU_ baris",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada data pagu anggaran"
            },
            aLengthMenu: [
                [10, 25, 50, 100, 200, -1],
                [10, 25, 50, 100, 200, "Semua"]
            ],
            iDisplayLength: 25,
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ? i : 0;
                };

                var totalPagu = api.column(3).data().reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

                var totalRealisasi = api.column(4).data().reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

                var totalSisa = api.column(5).data().reduce(function (a, b) {
                    return intVal(a) + intVal(b);
                }, 0);

                var totalPersen = totalPagu > 0 ? ((totalRealisasi / totalPagu) * 100).toFixed(2) : 0;

                $('#totPagu').html(formatRupiah(totalPagu));
                $('#totRealisasi').html(formatRupiah(totalRealisasi));
                $('#totSisa').html(formatRupiah(totalSisa));
                $('#totPersen').html('<span class="badge badge-primary">' + totalPersen + '%</span>');
            },
            drawCallback: function (settings) {
                $('[data-toggle="tooltip"]').tooltip();
            }
        });
    }

    function UploadFile() {
        var fileInput = $('#eFILE').get(0);
        if (!fileInput.files.length) {
            Swal.fire('Gagal', 'Silakan pilih file terlebih dahulu.', 'error');
            return;
        }

        var formData = new FormData();
        formData.append('excelFile', fileInput.files[0]);

        // Reset the input value to allow re-uploading the same file
        fileInput.value = '';

        Swal.fire({
            title: 'Mengunggah Data...',
            text: 'Mohon tunggu beberapa saat',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '<?= base_url('pagu/import') ?>',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (result) {
                if (result === '80') {
                    Swal.fire('Gagal', "Tipe file tidak sesuai, harus format .xls atau .xlsx", 'error');
                } else if (result === '90') {
                    Swal.fire('Gagal', "Terjadi kesalahan pada saat import file", 'error');
                } else {
                    var totalMsg = (result && result.total) ? ' (' + result.total + ' data rekening belanja berhasil diproses)' : '';
                    Swal.fire('Sukses', "Sukses upload data Pagu Anggaran" + totalMsg + ".", 'success');
                }
                LoadDataList();
            },
            error: function (xhr, status, error) {
                Swal.fire('Kesalahan', 'Terjadi kesalahan saat mengunggah file.', 'error');
                LoadDataList();
            }
        });
    }
</script>
<?= $this->endSection() ?>
