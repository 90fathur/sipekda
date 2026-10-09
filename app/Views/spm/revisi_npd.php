<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row animated fadeInRight">
    <!-- Form Revisi NPD -->
    <div class="col-lg-7">
        <!-- Rejection Notice Banner -->
        <div class="alert alert-danger mb-4 shadow-sm" style="border-left: 6px solid #d9534f; background-color: #fff5f5;">
            <div class="d-flex align-items-center mb-2">
                <i class="fa fa-exclamation-triangle fa-2x text-danger mr-3"></i>
                <div>
                    <h4 class="alert-heading font-bold text-danger mb-0">PENGAJUAN NPD DITOLAK OLEH VERIFIKATOR BPKAD</h4>
                    <small class="text-muted">Perhatikan catatan alasan penolakan di bawah ini untuk melakukan perbaikan formulir, rincian, atau berkas pendukung.</small>
                </div>
            </div>
            <hr style="border-top: 1px dashed #ebccd1; margin: 10px 0;">
            <div class="p-3 bg-white rounded border border-danger">
                <label class="font-bold text-danger mb-1"><i class="fa fa-commenting-o"></i> Catatan / Alasan Penolakan:</label>
                <div style="font-size: 15px; color: #a94442; font-weight: 600; white-space: pre-wrap;"><?= esc($npd['ALASAN_PENOLAKAN'] ?: 'Tidak ada keterangan alasan penolakan spesifik.') ?></div>
            </div>
            <div class="mt-2 text-dark font-12">
                <i class="fa fa-info-circle text-info"></i> Setelah Anda memperbaiki data atau memperbarui dokumen di bawah, klik tombol <strong>"Simpan & Kirim Ulang Revisi"</strong>. Status pengajuan akan kembali menjadi <strong>Verifikasi 1</strong> dan notifikasi perbaikan otomatis terkirim ke WhatsApp Verifikator BPKAD.
            </div>
        </div>

        <form id="form-revisi-npd" onsubmit="return submitRevisiNPD(event)">
            <input type="hidden" name="ID_PENGAJUAN" value="<?= esc($npd['ID_PENGAJUAN']) ?>">

            <div class="ibox">
                <div class="ibox-title">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="font-bold text-warning mb-0"><i class="fa fa-pencil-square-o"></i> <?= esc($Header) ?></h3>
                            <small>No. Pengajuan: <strong><?= esc($npd['ID_PENGAJUAN']) ?></strong></small>
                        </div>
                        <a href="<?= base_url('spm/statuspengajuannpdhome') ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> Kembali ke Status NPD</a>
                    </div>
                </div>
                <div class="ibox-content">
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">ID Pengajuan</label>
                        <div class="col-md-5">
                            <input type="text" class="form-control font-bold text-center" value="<?= esc($npd['ID_PENGAJUAN']) ?>" readonly style="background-color: #eef1f5; color: #2c3e50;">
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">Waktu Pengajuan Awal</label>
                        <div class="col-md-5">
                            <input type="text" class="form-control text-center" value="<?= !empty($npd['TGL_PENGAJUAN']) ? date('d-m-Y H:i', strtotime($npd['TGL_PENGAJUAN'])) : '-' ?>" readonly style="background-color: #eef1f5;">
                            <small class="form-text text-muted">Akan diperbarui ke waktu sekarang saat dikirim ulang.</small>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">Unit Kerja / OPD</label>
                        <div class="col-md-9">
                            <?php 
                            $currentSKPDName = $NM_UNITKER ?? '';
                            foreach ($ListSKPD as $skpd) {
                                if ($skpd['KD_SKPD'] === $KD_SKPD) {
                                    $currentSKPDName = $skpd['NM_SKPD'];
                                    break;
                                }
                            }
                            ?>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-light text-warning font-bold"><i class="fa fa-lock"></i></span>
                                </div>
                                <input type="text" class="form-control font-bold" value="<?= esc($KD_SKPD) ?> - <?= esc($currentSKPDName) ?>" readonly style="background-color: #eef1f5; color: #2c3e50;">
                                <input type="hidden" name="KD_SKPD" value="<?= esc($KD_SKPD) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">Program / Kegiatan / Sub Kegiatan <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <textarea name="NM_PROGRAM_KEGIATAN_SUBKEGIATAN" rows="4" class="form-control" placeholder="Tuliskan nama program, kegiatan, dan rincian peruntukan belanja..." required><?= esc($npd['NM_PROGRAM_KEGIATAN_SUBKEGIATAN']) ?></textarea>
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-md-3 col-form-label font-bold">Sumber Dana <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" name="KD_SUMBER_DANA" class="form-control" value="<?= esc($npd['KD_SUMBER_DANA'] ?: ($npd['KD_REKENING_BELANJA'] ?? '')) ?>" placeholder="Contoh: DAU / DAK / PAD / DBH Sawit" required>
                            <small class="form-text text-muted">Tuliskan sumber pendanaan kegiatan ini.</small>
                        </div>
                    </div>

                    <hr>

                    <!-- Table Rincian Belanja -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="font-bold text-navy mb-0"><i class="fa fa-list"></i> Rincian Belanja (Data Detail)</h4>
                        <button type="button" class="btn btn-sm btn-info" onclick="openAddDetailModal()"><i class="fa fa-plus"></i> Tambah Rincian</button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table id="dtDetail" class="table table-bordered table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 25%;">Kode Rekening</th>
                                    <th style="width: 40%;">Nama Rekening</th>
                                    <th class="text-right" style="width: 20%;">Nominal (Rp)</th>
                                    <th class="text-center" style="width: 10%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right font-bold">TOTAL ANGGARAN:</th>
                                    <th id="lblTotalDetail" class="text-right font-bold text-navy">Rp 0,00</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Table Dokumen PDF Pendukung -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="font-bold text-navy mb-0"><i class="fa fa-file-pdf-o"></i> Berkas Digital Pendukung (PDF)</h4>
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#mdlUpload"><i class="fa fa-upload"></i> Upload PDF Baru</button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table id="dtFiles" class="table table-bordered table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 45%;">Nama File</th>
                                    <th style="width: 20%;">Ukuran</th>
                                    <th class="text-center" style="width: 15%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="text-right mt-4">
                        <a href="<?= base_url('spm/statuspengajuannpdhome') ?>" class="btn btn-default btn-lg mr-2"><i class="fa fa-times"></i> Batal</a>
                        <button type="submit" class="btn btn-warning btn-lg font-bold ldSave ladda-button" data-style="zoom-in">
                            <span class="ladda-label"><i class="fa fa-refresh"></i> Simpan & Kirim Ulang Revisi NPD</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Info Pagu Rekening Belanja OPD -->
    <div class="col-lg-5">
        <div class="ibox">
            <div class="ibox-title">
                <h4 class="font-bold text-navy"><i class="fa fa-balance-scale"></i> Informasi Sisa Pagu Anggaran</h4>
                <small>Pastikan nominal revisi tidak melebihi sisa pagu rekening belanja.</small>
            </div>
            <div class="ibox-content" style="max-height: 650px; overflow-y: auto;">
                <ul class="list-group">
                    <?php foreach ($ListRekeningBelanja as $rek): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong><?= esc($rek['KD_REKENING_BELANJA']) ?></strong><br>
                                <small class="text-muted"><?= esc($rek['NM_REKENING_BELANJA']) ?></small>
                            </div>
                            <span class="badge badge-primary badge-pill" style="font-size: 13px;">
                                Rp <?= number_format((float)($rek['SISA_PAGU'] ?? 0), 2, ',', '.') ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Detail Belanja -->
<div class="modal fade" id="mdlAddDetail" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-bold text-navy"><i class="fa fa-plus"></i> Tambah Rincian Rekening Belanja</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-3">
                    <label class="font-bold">Rekening Belanja <span class="text-danger">*</span></label>
                    <select id="detail_KD_REKENING" class="form-control select2_modal" style="width: 100%;">
                        <option value="">-- Pilih Rekening Belanja --</option>
                        <?php foreach ($ListRekeningBelanja as $rek): ?>
                            <option value="<?= esc($rek['KD_REKENING_BELANJA']) ?>" data-sisa="<?= (float)$rek['SISA_PAGU'] ?>">
                                <?= esc($rek['KD_REKENING_BELANJA']) ?> - <?= esc($rek['DISPLAY_NAME']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group mb-3">
                    <label class="font-bold">Nominal Anggaran (Rp) <span class="text-danger">*</span></label>
                    <input type="number" id="detail_ANGGARAN" class="form-control" placeholder="Contoh: 15000000" min="1" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="saveDataDetail()"><i class="fa fa-save"></i> Tambahkan</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Berkas -->
<div class="modal fade" id="mdlUpload" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-bold text-navy"><i class="fa fa-upload"></i> Upload Berkas Digital PDF</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-bold">Pilih File PDF</label>
                    <input type="file" id="filePdf" class="form-control" accept=".pdf">
                    <small class="text-muted">Format file harus .PDF (Maks. 15MB)</small>
                </div>
                <div id="uploadProgress" class="progress progress-striped active d-none mt-2">
                    <div style="width: 100%" class="progress-bar progress-bar-success">Mengunggah berkas...</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="uploadPdfFile()"><i class="fa fa-upload"></i> Unggah</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
var idPengajuan = '<?= esc($npd['ID_PENGAJUAN']) ?>';

$(document).ready(function() {
    $('.select2').select2();
    $('#mdlAddDetail').on('shown.bs.modal', function () {
        $('#detail_KD_REKENING').select2({
            dropdownParent: $('#detail_KD_REKENING').parent(),
            width: '100%'
        });
    });
    loadDetailTable();
    loadFileTable();
});

function loadDetailTable() {
    $.get('<?= base_url('spm/getdatadetail') ?>', { NO_NPD_SPM: idPengajuan }, function(items) {
        var tbody = '';
        var total = 0;
        if (!items || items.length === 0) {
            tbody = '<tr><td colspan="5" class="text-center text-muted">Belum ada rincian belanja ditambahkan</td></tr>';
        } else {
            items.forEach(function(item, idx) {
                var nominal = parseFloat(item.ANGGARAN || 0);
                total += nominal;
                tbody += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td>' + item.KD_REKENING_BELANJA + '</td>' +
                    '<td>' + item.NM_REKENING_BELANJA + '</td>' +
                    '<td class="text-right font-bold">Rp ' + nominal.toLocaleString('id-ID', { minimumFractionDigits: 2 }) + '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-xs btn-danger" onclick="deleteDataDetail(' + item.ID_DETAIL + ')" title="Hapus"><i class="fa fa-trash"></i></button></td>' +
                    '</tr>';
            });
        }
        $('#dtDetail tbody').html(tbody);
        $('#lblTotalDetail').text('Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
    });
}

function openAddDetailModal() {
    $('#detail_ANGGARAN').val('');
    $('#mdlAddDetail').modal('show');
}

function saveDataDetail() {
    var kd = $('#detail_KD_REKENING').val();
    var nominal = $('#detail_ANGGARAN').val();

    if (!kd) {
        swal.fire('Peringatan', 'Silakan pilih rekening belanja', 'warning');
        return;
    }
    if (!nominal || parseFloat(nominal) <= 0) {
        swal.fire('Peringatan', 'Nominal anggaran harus lebih besar dari 0', 'warning');
        return;
    }

    $.post('<?= base_url('spm/adddatadetail') ?>', {
        NO_NPD_SPM: idPengajuan,
        KD_REKENING_BELANJA: kd,
        ANGGARAN: nominal
    }, function(resp) {
        if (resp.trim() === '00') {
            $('#mdlAddDetail').modal('hide');
            loadDetailTable();
        } else {
            swal.fire('Peringatan', resp.replace('#', ''), 'warning');
        }
    });
}

function deleteDataDetail(id) {
    swal.fire({
        title: 'Hapus Rincian?',
        text: 'Apakah Anda yakin ingin menghapus rincian belanja ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ed5565'
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('<?= base_url('spm/deletedatadetail') ?>', { id: id }, function(resp) {
                var status = (typeof resp === 'object') ? resp.status : resp.trim();
                if (status === '00') {
                    loadDetailTable();
                } else {
                    swal.fire('Gagal', (resp.replace ? resp.replace('#', '') : 'Gagal menghapus.'), 'error');
                }
            });
        }
    });
}

function loadFileTable() {
    $.get('<?= base_url('spm/getallfiles') ?>', { idPengajuan: idPengajuan }, function(files) {
        var tbody = '';
        if (!files || files.length === 0) {
            tbody = '<tr><td colspan="4" class="text-center text-muted">Belum ada berkas PDF diunggah</td></tr>';
        } else {
            files.forEach(function(f, idx) {
                var sizeKb = (f.Size / 1024).toFixed(1) + ' KB';
                var fileUrl = '<?= base_url('uploads/pdf') ?>/' + f.FileName;
                tbody += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td><a href="' + fileUrl + '" target="_blank"><i class="fa fa-file-pdf-o text-danger"></i> ' + f.Name + '</a></td>' +
                    '<td>' + sizeKb + '</td>' +
                    '<td class="text-center">' +
                    '<a href="' + fileUrl + '" target="_blank" class="btn btn-xs btn-info" title="Lihat PDF"><i class="fa fa-eye"></i></a> ' +
                    '<button type="button" class="btn btn-xs btn-danger ml-1" onclick="deleteFile(\'' + f.FileName + '\')" title="Hapus"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
            });
        }
        $('#dtFiles tbody').html(tbody);
    });
}

function uploadPdfFile() {
    var fileInput = document.getElementById('filePdf');
    if (!fileInput.files || fileInput.files.length === 0) {
        swal.fire('Peringatan', 'Silakan pilih file PDF terlebih dahulu', 'warning');
        return;
    }

    var formData = new FormData();
    formData.append('fileUpload', fileInput.files[0]);
    formData.append('idPengajuan', idPengajuan);

    $('#uploadProgress').removeClass('d-none');

    $.ajax({
        url: '<?= base_url('spm/uploadfile') ?>',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(resp) {
            $('#uploadProgress').addClass('d-none');
            $('#mdlUpload').modal('hide');
            $('#filePdf').val('');
            loadFileTable();
        },
        error: function() {
            $('#uploadProgress').addClass('d-none');
            swal.fire('Kesalahan', 'Gagal mengunggah file', 'error');
        }
    });
}

function deleteFile(fileName) {
    swal.fire({
        title: 'Hapus Berkas?',
        text: 'Apakah Anda yakin ingin menghapus berkas digital ini?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Hapus',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ed5565'
    }).then((res) => {
        if (res.isConfirmed) {
            $.post('<?= base_url('spm/deletefile') ?>', { fileName: fileName }, function(resp) {
                if (resp.trim() === '0') {
                    loadFileTable();
                } else {
                    swal.fire('Gagal', 'Tidak dapat menghapus file.', 'error');
                }
            });
        }
    });
}

function submitRevisiNPD(e) {
    e.preventDefault();

    // Validasi rincian belanja
    var rowCount = $('#dtDetail tbody tr td button').length;
    if (rowCount === 0) {
        swal.fire('Peringatan', 'Silakan tambahkan minimal 1 rincian rekening belanja sebelum mengirim kembali.', 'warning');
        return false;
    }

    var totalText = $('#lblTotalDetail').text();
    swal.fire({
        title: 'Kirim Ulang Revisi NPD?',
        html: 'Pengajuan revisi NPD <b>' + idPengajuan + '</b> akan dikirimkan kembali ke BPKAD dengan total anggaran:<br><h3 class="text-navy font-bold mt-2">' + totalText + '</h3><p class="text-muted mt-2">Status akan direset ke <b>Verifikasi 1</b> dan notifikasi otomatis dikirim ke Verifikator BPKAD.</p>',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Ya, Kirim Ulang Sekarang',
        cancelButtonText: 'Periksa Kembali',
        confirmButtonColor: '#f8ac59',
        cancelButtonColor: '#c2c2c2'
    }).then((result) => {
        if (result.isConfirmed) {
            var formData = $('#form-revisi-npd').serialize();
            var l = $('.ldSave').ladda();
            l.ladda('start');

            $.post('<?= base_url('spm/saverevisinpd') ?>', formData, function(resp) {
                l.ladda('stop');
                if (resp.trim() === '00') {
                    swal.fire('Sukses', 'Perbaikan pengajuan NPD berhasil disimpan dan dikirim kembali ke Verifikator 1 BPKAD!', 'success')
                        .then(() => {
                            window.location.href = '<?= base_url('spm/statuspengajuannpdhome') ?>';
                        });
                } else {
                    swal.fire('Pemberitahuan', resp.replace('#', ''), 'warning');
                }
            }).fail(function() {
                l.ladda('stop');
                swal.fire('Kesalahan', 'Gagal mengirim pengajuan revisi.', 'error');
            });
        }
    });

    return false;
}
</script>
<?= $this->endSection() ?>
