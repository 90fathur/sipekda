<div class="row">
    <div class="col-lg-7">
        <form id="form-spm" onsubmit="return submitSPM(event)">
            <input type="hidden" name="ID_NPD" value="<?= esc($npd['ID_PENGAJUAN'] ?? '') ?>">

            <div class="ibox">
                <div class="ibox-title">
                    <h4 class="font-bold text-navy"><i class="fa fa-pencil-square-o"></i> Form Pengajuan SPP / SPM</h4>
                    <small>Berdasarkan Nota Pencairan Dana (NPD): <strong><?= esc($npd['ID_PENGAJUAN'] ?? '-') ?></strong></small>
                </div>
                <div class="ibox-content">
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">ID SPM</label>
                        <div class="col-md-5">
                            <input type="text" class="form-control font-bold text-center" value="[Otomatis dari Sistem]" readonly style="background-color: #eef1f5;">
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">Tanggal Pengajuan</label>
                        <div class="col-md-5">
                            <input type="text" class="form-control text-center" value="<?= date('d-m-Y H:i') ?>" readonly style="background-color: #eef1f5;">
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">Unit Kerja / OPD</label>
                        <div class="col-md-9">
                            <select name="KD_SKPD" class="form-control select2_spm" required>
                                <?php foreach ($ListSKPD as $skpd): ?>
                                    <option value="<?= esc($skpd['KD_SKPD']) ?>" <?= (($npd['KD_SKPD'] ?? $KD_SKPD) === $skpd['KD_SKPD']) ? 'selected' : '' ?>>
                                        <?= esc($skpd['KD_SKPD']) ?> - <?= esc($skpd['NM_SKPD']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">Klasifikasi Anggaran</label>
                        <div class="col-md-9">
                            <select name="KD_REKENING_BELANJA" class="form-control select2_spm" required>
                                <?php foreach ($ListMataAnggaran as $ma): ?>
                                    <option value="<?= esc($ma['KD_MATA_ANGGARAN']) ?>" <?= (($npd['KD_REKENING_BELANJA'] ?? '') === $ma['KD_MATA_ANGGARAN']) ? 'selected' : '' ?>>
                                        <?= esc($ma['KD_MATA_ANGGARAN']) ?> - <?= esc($ma['NM_MATA_ANGGARAN']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label font-bold">Program / Kegiatan / Sub Kegiatan</label>
                        <div class="col-md-9">
                            <textarea name="NM_PROGRAM_KEGIATAN_SUBKEGIATAN" rows="4" class="form-control" required><?= esc($npd['NM_PROGRAM_KEGIATAN_SUBKEGIATAN'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="form-group row mb-4">
                        <label class="col-md-3 col-form-label font-bold">Sumber Dana</label>
                        <div class="col-md-9">
                            <input type="text" name="KD_SUMBER_DANA" class="form-control" value="<?= esc($npd['KD_SUMBER_DANA'] ?? '') ?>">
                        </div>
                    </div>

                    <hr>

                    <!-- Rincian Belanja -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="font-bold text-navy mb-0"><i class="fa fa-list"></i> Rincian Belanja NPD/SPM</h4>
                    </div>

                    <div class="table-responsive mb-4">
                        <table id="dtDetailSPM" class="table table-bordered table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 25%;">Kode Rekening</th>
                                    <th style="width: 45%;">Nama Rekening</th>
                                    <th class="text-right" style="width: 25%;">Nominal (Rp)</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right font-bold">TOTAL PENGAJUAN SPM:</th>
                                    <th id="lblTotalDetailSPM" class="text-right font-bold text-navy">Rp <?= number_format((float)($npd['ANGGARAN'] ?? 0), 2, ',', '.') ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Berkas Digital PDF -->
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h4 class="font-bold text-navy mb-0"><i class="fa fa-file-pdf-o"></i> Berkas Digital Pendukung (PDF)</h4>
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#mdlUploadSPM"><i class="fa fa-upload"></i> Tambah Berkas</button>
                    </div>

                    <div class="table-responsive mb-4">
                        <table id="dtFilesSPM" class="table table-bordered table-striped" style="width:100%">
                            <thead>
                                <tr>
                                    <th style="width: 5%;">No</th>
                                    <th style="width: 50%;">Nama File</th>
                                    <th style="width: 25%;">Ukuran</th>
                                    <th class="text-center" style="width: 20%;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                    <div class="text-right mt-4">
                        <button type="submit" class="btn btn-primary btn-lg ldSaveSPM ladda-button" data-style="zoom-in">
                            <span class="ladda-label"><i class="fa fa-send"></i> Ajukan SPP / SPM ke BPKAD</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Info NPD Asal -->
    <div class="col-lg-5">
        <div class="ibox">
            <div class="ibox-title">
                <h4 class="font-bold text-navy"><i class="fa fa-info-circle"></i> Ringkasan Nota Pencairan Dana (NPD)</h4>
            </div>
            <div class="ibox-content">
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 40%;">ID NPD</th>
                        <td><strong class="text-navy"><?= esc($npd['ID_PENGAJUAN'] ?? '-') ?></strong></td>
                    </tr>
                    <tr>
                        <th>Tanggal Disetujui</th>
                        <td><?= !empty($npd['TGL_VEIFIKASI']) ? date('d-m-Y H:i', strtotime($npd['TGL_VEIFIKASI'])) : '-' ?></td>
                    </tr>
                    <tr>
                        <th>Total Nominal</th>
                        <td class="font-bold text-success">Rp <?= number_format((float)($npd['ANGGARAN'] ?? 0), 2, ',', '.') ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><span class="badge badge-primary">Disetujui BPKAD</span></td>
                    </tr>
                </table>

                <div class="alert alert-info">
                    <i class="fa fa-lightbulb-o"></i> Dengan mengajukan SPM ini, proses validasi pencairan dana ke Kas Daerah (Bank Sulselbar) akan diteruskan ke tahap persetujuan SP2D oleh BPKAD.
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Upload Berkas SPM -->
<div class="modal fade" id="mdlUploadSPM" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title font-bold text-navy"><i class="fa fa-upload"></i> Upload Berkas Digital PDF</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-bold">Pilih File PDF</label>
                    <input type="file" id="filePdfSPM" class="form-control" accept=".pdf">
                    <small class="text-muted">Format file harus .PDF</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary" onclick="uploadPdfFileSPM()"><i class="fa fa-upload"></i> Unggah</button>
            </div>
        </div>
    </div>
</div>

<script>
function loadDetailTableSPM(idNpd) {
    $.get('<?= base_url('spm/getdatadetail') ?>', { NO_NPD_SPM: idNpd }, function(items) {
        var tbody = '';
        var total = 0;
        if (items.length === 0) {
            tbody = '<tr><td colspan="4" class="text-center text-muted">Rincian belanja dari NPD akan dimuat otomatis</td></tr>';
        } else {
            items.forEach(function(item, idx) {
                var nominal = parseFloat(item.ANGGARAN || 0);
                total += nominal;
                tbody += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td>' + item.KD_REKENING_BELANJA + '</td>' +
                    '<td>' + item.NM_REKENING_BELANJA + '</td>' +
                    '<td class="text-right font-bold">Rp ' + nominal.toLocaleString('id-ID', { minimumFractionDigits: 2 }) + '</td>' +
                    '</tr>';
            });
            $('#lblTotalDetailSPM').text('Rp ' + total.toLocaleString('id-ID', { minimumFractionDigits: 2 }));
        }
        $('#dtDetailSPM tbody').html(tbody);
    });
}

function loadFileTableSPM() {
    $.get('<?= base_url('spm/getallfiles') ?>', function(files) {
        var tbody = '';
        if (files.length === 0) {
            tbody = '<tr><td colspan="4" class="text-center text-muted">Belum ada berkas tambahan diunggah</td></tr>';
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
                    '<button type="button" class="btn btn-xs btn-danger" onclick="deleteFileSPM(\'' + f.FileName + '\')" title="Hapus"><i class="fa fa-trash"></i></button>' +
                    '</td></tr>';
            });
        }
        $('#dtFilesSPM tbody').html(tbody);
    });
}

function uploadPdfFileSPM() {
    var fileInput = document.getElementById('filePdfSPM');
    if (!fileInput.files || fileInput.files.length === 0) {
        swal.fire('Peringatan', 'Silakan pilih file PDF terlebih dahulu', 'warning');
        return;
    }

    var formData = new FormData();
    formData.append('fileUpload', fileInput.files[0]);

    $.ajax({
        url: '<?= base_url('spm/uploadfile') ?>',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function() {
            $('#mdlUploadSPM').modal('hide');
            $('#filePdfSPM').val('');
            loadFileTableSPM();
        },
        error: function() {
            swal.fire('Kesalahan', 'Gagal mengunggah file', 'error');
        }
    });
}

function deleteFileSPM(fileName) {
    $.post('<?= base_url('spm/deletefile') ?>', { fileName: fileName }, function() {
        loadFileTableSPM();
    });
}

function submitSPM(e) {
    e.preventDefault();
    var formData = $('#form-spm').serialize();

    var l = $('.ldSaveSPM').ladda();
    l.ladda('start');

    $.post('<?= base_url('spm/savepengajuan') ?>', formData, function(resp) {
        l.ladda('stop');
        if (resp.trim() === '00') {
            swal.fire('Sukses', 'Pengajuan SPP / SPM berhasil diajukan ke BPKAD!', 'success')
                .then(() => {
                    window.location.href = '<?= base_url('spm/statuspengajuanhome') ?>';
                });
        } else {
            swal.fire('Pemberitahuan', resp.replace('#', ''), 'warning');
        }
    }).fail(function() {
        l.ladda('stop');
        swal.fire('Kesalahan', 'Gagal mengirim pengajuan.', 'error');
    });

    return false;
}
</script>
