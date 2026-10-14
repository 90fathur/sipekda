<?= $this->extend('layout/main') ?>

<?= $this->section('styles') ?>
<style>
    /* Styling baris tabel berdasarkan status pemeriksaan */
    .row-verified {
        background-color: #e8f8f5 !important;
        border-left: 5px solid #1ab394 !important;
        transition: background-color 0.3s ease;
    }
    .row-verified td {
        background-color: transparent !important;
    }
    .row-verified:hover {
        background-color: #d1f2eb !important;
    }

    .row-pending {
        border-left: 5px solid #f8ac59 !important;
        transition: background-color 0.3s ease;
    }

    .row-rejected {
        background-color: #fdf2f2 !important;
        border-left: 5px solid #ed5565 !important;
        opacity: 0.85;
    }
    .row-rejected td {
        background-color: transparent !important;
    }

    .chk-verif-npd {
        width: 22px;
        height: 22px;
        cursor: pointer;
        accent-color: #1ab394;
        vertical-align: middle;
        margin: 0;
        transform: scale(1.15);
    }
    .chk-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 24px;
    }
    .legend-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 4px;
    }
</style>
<?= $this->endSection() ?>

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
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                        <div class="btn-group mb-2" role="group" id="btnGroupFilter">
                            <button type="button" class="btn btn-sm btn-primary active btn-filter" data-status="">Semua Pengajuan</button>
                            <button type="button" class="btn btn-sm btn-white btn-filter" data-status="Menunggu"><i class="fa fa-clock-o text-warning"></i> Menunggu Persetujuan</button>
                            <button type="button" class="btn btn-sm btn-white btn-filter" data-status="Diverifikasi"><i class="fa fa-check text-navy font-bold"></i> Selesai Diperiksa</button>
                        </div>
                        <div class="d-flex flex-wrap align-items-center mb-2" style="gap: 8px;">
                            <span class="legend-indicator" style="background-color: #e8f8f5; color: #1ab394; border: 1px solid #1ab394;">
                                <i class="fa fa-check-square"></i> Centang Hijau: Selesai Diperiksa
                            </span>
                            <span class="legend-indicator" style="background-color: #fffbf0; color: #f8ac59; border: 1px solid #f8ac59;">
                                <i class="fa fa-square-o"></i> Kotak Kosong: Menunggu Diperiksa
                            </span>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="dtDaftar" class="table table-striped table-bordered table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 4%;">No</th>
                                    <th class="text-center" style="width: 8%;">Selesai<br>Diperiksa</th>
                                    <th class="text-center" style="width: 14%;">ID Pengajuan</th>
                                    <th class="text-center" style="width: 9%;">Tanggal</th>
                                    <th class="text-center" style="width: 18%;">Nama OPD</th>
                                    <th class="text-center" style="width: 20%;">Program Kegiatan / Sub Kegiatan</th>
                                    <th class="text-center" style="width: 12%;">Anggaran</th>
                                    <th class="text-center" style="width: 11%;">Status Verifikasi</th>
                                    <th class="text-center" style="width: 6%;">Opsi</th>
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

        $('.btn-filter').on('click', function() {
            $('.btn-filter').removeClass('active btn-primary').addClass('btn-white');
            $(this).removeClass('btn-white').addClass('active btn-primary');

            var filterVal = $(this).data('status');
            $('#dtDaftar').DataTable().column(7).search(filterVal).draw();
        });
    });

    setInterval(function () { Reload(); }, 25000);

    function LoadDataList() {
        $('#dtDaftar').DataTable().destroy();
        $('#dtDaftar').DataTable({
            ajax: {
                url: '<?= base_url('bpkad/getlistpengajuan') ?>',
                dataSrc: '',
                type: 'GET',
                data: { IsNPD: true }
            },
            createdRow: function (row, data, dataIndex) {
                var s = parseInt(data.KD_STATUS);
                if (s === 4 || s === 6) {
                    $(row).addClass('row-verified');
                } else if (s === 3) {
                    $(row).addClass('row-pending');
                } else if (s === 5) {
                    $(row).addClass('row-rejected');
                }
            },
            columns: [
                {
                    data: null, className: 'text-center align-middle', width: '4%', render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    data: 'KD_STATUS',
                    className: 'text-center align-middle',
                    width: '8%',
                    orderable: false,
                    render: function (s, type, row) {
                        s = parseInt(s);
                        if (s === 4 || s === 6) {
                            return '<div class="chk-container" title="' + (s === 6 ? 'Sudah di Proses di SIPD' : 'Sudah diverifikasi') + ' (Klik untuk membatalkan)">' +
                                   '<input type="checkbox" class="chk-verif-npd" data-id="' + row.ID_PENGAJUAN + '" checked />' +
                                   '</div>';
                        } else if (s === 3) {
                            return '<div class="chk-container" title="Klik untuk mencentang jika selesai diperiksa">' +
                                   '<input type="checkbox" class="chk-verif-npd" data-id="' + row.ID_PENGAJUAN + '" />' +
                                   '</div>';
                        } else if (s === 5) {
                            return '<span class="badge badge-danger" title="Pengajuan Ditolak"><i class="fa fa-times"></i> Ditolak</span>';
                        }
                        return '<input type="checkbox" disabled style="opacity: 0.3;" />';
                    }
                },
                { 
                    data: 'ID_PENGAJUAN', 
                    className: 'text-center font-bold text-nowrap align-middle',
                    render: function (data, type, row) {
                        var icon = '';
                        var s = parseInt(row.KD_STATUS);
                        if (s === 4) {
                            icon = '<i class="fa fa-check-circle text-primary mr-1" title="Sudah Diverifikasi"></i> ';
                        } else if (s === 6) {
                            icon = '<i class="fa fa-check-double text-primary mr-1" title="Sudah di Proses di SIPD"></i> ';
                        }
                        return icon + '<span class="id">' + data + '</span>';
                    }
                },
                { data: 'TGL_PENGAJUAN', className: 'text-center align-middle', render: function (d) { return d ? moment(d).format("DD-MM-YYYY") : '-' } },
                { data: 'KD_SKPD', className: 'text-left align-middle' },
                { data: 'NM_PROGRAM_KEGIATAN_SUBKEGIATAN', className: 'text-left align-middle' },
                { data: 'ANGGARAN', className: 'text-right font-bold align-middle', render: $.fn.dataTable.render.number('.', ',', 2) },
                {
                    data: 'KD_STATUS',
                    className: 'text-center text-nowrap align-middle',
                    width: '11%',
                    render: function (s, type, row) {
                        s = parseInt(s);
                        if (s === 4) {
                            return '<span class="badge badge-primary font-bold px-2 py-1"><i class="fa fa-check"></i> Sudah Diverifikasi</span>';
                        } else if (s === 6) {
                            return '<span class="badge badge-primary font-bold px-2 py-1" style="background-color: #1c84c6; color: #fff;"><i class="fa fa-check-circle"></i> Sudah di Proses di SIPD</span>';
                        } else if (s === 3) {
                            return '<span class="badge badge-warning font-bold px-2 py-1"><i class="fa fa-clock-o"></i> Menunggu Persetujuan</span>';
                        } else if (s === 5) {
                            return '<span class="badge badge-danger font-bold px-2 py-1" title="' + (row.ALASAN_PENOLAKAN || 'Ditolak') + '"><i class="fa fa-times"></i> Ditolak</span>';
                        } else if (s === 2) {
                            return '<span class="badge badge-warning font-bold px-2 py-1">Verifikasi 2</span>';
                        } else if (s === 1) {
                            return '<span class="badge badge-info font-bold px-2 py-1">Verifikasi 1</span>';
                        }
                        return '<span class="badge badge-secondary px-2 py-1">Draft</span>';
                    }
                },
                {
                    data: null,
                    className: 'text-center align-middle',
                    width: '6%',
                    render: function(data, type, row) {
                        return '<button type="button" class="btn btn-xs btn-info btn-outline btn-detail" data-id="' + row.ID_PENGAJUAN + '" data-toggle="tooltip" data-placement="top" title="Detail"><i class="fa fa-search"></i> Detail</button>';
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

    // Event interaktif saat akun Persetujuan mencentang atau membatalkan centang NPD
    $('#dtDaftar').on('click', '.chk-verif-npd', function (e) {
        e.stopPropagation();
        var chk = $(this);
        var id = chk.data('id');
        var isChecking = chk.is(':checked');

        if (isChecking) {
            Swal.fire({
                title: 'Selesai Diperiksa?',
                html: 'Tandai NPD <b>' + id + '</b> telah selesai diperiksa dan verifikasi/setujui pengajuan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1ab394',
                cancelButtonColor: '#c2c2c2',
                confirmButtonText: '<i class="fa fa-check"></i> Ya, Selesai & Verifikasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('bpkad/acceptpengajuannpd') ?>', { id: id }, function (resp) {
                        if (resp.trim() === '00') {
                            Swal.fire({
                                title: 'Berhasil!',
                                text: 'NPD ' + id + ' telah diverifikasi & selesai diperiksa.',
                                icon: 'success',
                                timer: 1600,
                                showConfirmButton: false
                            });
                            Reload();
                            if ($('#ID_PENGAJUAN').val() === id) {
                                Detail(id);
                            }
                        } else {
                            chk.prop('checked', false);
                            Swal.fire('Gagal', resp.replace('#', '') || 'Gagal memproses verifikasi.', 'error');
                        }
                    }).fail(function() {
                        chk.prop('checked', false);
                        Swal.fire('Kesalahan', 'Terjadi kesalahan sistem saat memproses verifikasi.', 'error');
                    });
                } else {
                    chk.prop('checked', false);
                }
            });
        } else {
            Swal.fire({
                title: 'Batalkan Verifikasi?',
                html: 'Apakah Anda ingin membatalkan status verifikasi NPD <b>' + id + '</b> dan mengembalikannya ke status <b>Menunggu Persetujuan</b>?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ed5565',
                cancelButtonColor: '#c2c2c2',
                confirmButtonText: '<i class="fa fa-undo"></i> Ya, Batalkan Verifikasi',
                cancelButtonText: 'Tetap Diverifikasi'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('<?= base_url('bpkad/unacceptpengajuannpd') ?>', { id: id }, function (resp) {
                        if (resp.trim() === '00') {
                            Swal.fire({
                                title: 'Dibatalkan',
                                text: 'Status NPD ' + id + ' dikembalikan ke Menunggu Persetujuan.',
                                icon: 'info',
                                timer: 1600,
                                showConfirmButton: false
                            });
                            Reload();
                            if ($('#ID_PENGAJUAN').val() === id) {
                                Detail(id);
                            }
                        } else {
                            chk.prop('checked', true);
                            Swal.fire('Gagal', resp.replace('#', '') || 'Gagal membatalkan status verifikasi.', 'error');
                        }
                    }).fail(function() {
                        chk.prop('checked', true);
                        Swal.fire('Kesalahan', 'Terjadi kesalahan sistem saat membatalkan verifikasi.', 'error');
                    });
                } else {
                    chk.prop('checked', true);
                }
            });
        }
    });

    function Accept(id) {
        $.post('<?= base_url('bpkad/acceptpengajuannpd') ?>', { id: id }, function (data) {
            Swal.fire('Informasi', 'Pengajuan NPD telah di setujui.', 'success');
            Reload();
            $('#dvView').empty();
        });
    }

    function Unaccept(id) {
        $.post('<?= base_url('bpkad/unacceptpengajuannpd') ?>', { id: id }, function (data) {
            Swal.fire('Informasi', 'Status verifikasi NPD telah dibatalkan.', 'info');
            Reload();
            if ($('#ID_PENGAJUAN').val() === id) {
                Detail(id);
            }
        });
    }

    function Reject(id, alasan) {
        $.post('<?= base_url('bpkad/rejectpengajuannpd') ?>', { id: id, alasan: alasan }, function (data) {
            Swal.fire('Informasi', 'Pengajuan NPD telah di tolak.', 'success');
            Reload();
            $('#dvView').empty();
        });
    }

    function Detail(id) {
        $.get('<?= base_url('bpkad/spmviewnpd') ?>', { idPengajuan: id }, function (data) {
            $('#dvView').html(data);
            $('html, body').animate({
                scrollTop: $("#dvView").offset().top
            }, 500);

            LoadDataDetailList(id);
            GetAllFiles(id);
        });
    }

    function Reload() {
        $('#dtDaftar').DataTable().ajax.reload(null, false);
    }

    $('#dtDaftar').on('click', 'button.btn-detail', function (e) {
        var id = $(this).data('id') || $(this).closest("tr").find(".id").text().trim();
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
                                        <div class="mt-2 text-center">
                                            <button type="button" class="btn btn-xs btn-danger btn-outline" onclick="deleteLampiran('${file.FileName}', '${idPengajuan}')" title="Hapus Berkas Lampiran">
                                                <i class="fa fa-trash"></i> Hapus
                                            </button>
                                        </div>
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

    function deleteLampiran(fileName, idPengajuan) {
        Swal.fire({
            title: 'Hapus Berkas?',
            text: 'Apakah Anda yakin ingin menghapus berkas "' + fileName + '" ini dari pengajuan?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ed5565',
            cancelButtonColor: '#c2c2c2',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= base_url('spm/deletefile') ?>', { fileName: fileName }, function (resp) {
                    if (resp.trim() === '0') {
                        Swal.fire('Terhapus', 'Berkas berhasil dihapus.', 'success');
                        GetAllFiles(idPengajuan);
                    } else {
                        Swal.fire('Gagal', 'Tidak dapat menghapus berkas. Pastikan Anda memiliki izin akses.', 'error');
                    }
                }).fail(function(xhr) {
                    var errorMsg = 'Terjadi kesalahan sistem saat menghapus berkas.';
                    if (xhr && xhr.status === 403) {
                        errorMsg = 'Akses Ditolak: Anda tidak memiliki izin untuk menghapus berkas ini.';
                    }
                    Swal.fire('Kesalahan', errorMsg, 'error');
                });
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
                { data: 'ANGGARAN', className: 'text-right font-bold', render: $.fn.dataTable.render.number('.', ',', 2), width: '28%' },
                {
                    data: null,
                    className: 'text-center',
                    width: '12%',
                    render: function (data, type, row) {
                        return '<button type="button" class="btn btn-xs btn-danger btn-outline" onclick="deleteRincianBelanja(' + row.ID_DETAIL + ', \'' + id + '\')" title="Hapus Rincian"><i class="fa fa-trash"></i> Hapus</button>';
                    }
                }
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

    function deleteRincianBelanja(idDetail, idPengajuan) {
        Swal.fire({
            title: 'Hapus Rincian Belanja?',
            text: 'Rincian rekening belanja ini akan dihapus dari pengajuan dan total alokasi anggaran akan dihitung ulang secara otomatis.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ed5565',
            cancelButtonColor: '#c2c2c2',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('<?= base_url('spm/deletedatadetail') ?>', { id: idDetail }, function(resp) {
                    var status = (typeof resp === 'object') ? resp.status : resp.trim();
                    if (status === '00') {
                        Swal.fire('Terhapus', 'Rincian belanja berhasil dihapus.', 'success');
                        LoadDataDetailList(idPengajuan);
                        if (typeof resp === 'object' && resp.formattedTotal !== undefined) {
                            $('#TOTAL_ALOKASI_ANGGARAN').val(resp.formattedTotal);
                        }
                        Reload();
                    } else {
                        var msg = (typeof resp === 'object' && resp.message) ? resp.message : (resp.replace('#', '') || 'Gagal menghapus rincian.');
                        Swal.fire('Gagal', msg, 'error');
                    }
                }).fail(function() {
                    Swal.fire('Kesalahan', 'Terjadi kesalahan sistem saat menghapus rincian belanja.', 'error');
                });
            }
        });
    }
</script>
<?= $this->endSection() ?>
