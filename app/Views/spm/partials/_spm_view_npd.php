<div class="modal-header">
    <h4 class="modal-title font-bold text-navy"><i class="fa fa-info-circle"></i> <?= esc($Header) ?> - <?= esc($npd['ID_PENGAJUAN']) ?></h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="font-bold" style="width: 40%;">ID NPD</td>
                    <td>: <strong class="text-navy"><?= esc($npd['ID_PENGAJUAN']) ?></strong></td>
                </tr>
                <tr>
                    <td class="font-bold">Tanggal</td>
                    <td>: <?= !empty($npd['TGL_PENGAJUAN']) ? date('d-m-Y H:i', strtotime($npd['TGL_PENGAJUAN'])) : '-' ?></td>
                </tr>
                <tr>
                    <td class="font-bold">Kode OPD</td>
                    <td>: <?= esc($npd['KD_SKPD']) ?></td>
                </tr>
                <tr>
                    <td class="font-bold">Sumber Dana</td>
                    <td>: <?= esc($npd['KD_SUMBER_DANA'] ?: ($npd['KD_REKENING_BELANJA'] ?: '-')) ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="font-bold" style="width: 40%;">Total Anggaran</td>
                    <td>: <span class="text-success font-bold" style="font-size: 16px;">Rp <?= number_format((float)$npd['ANGGARAN'], 2, ',', '.') ?></span></td>
                </tr>
                <tr>
                    <td class="font-bold">Status Verifikasi</td>
                    <td>: 
                        <?php
                        $s = (int)$npd['KD_STATUS'];
                        if ($s === 1) echo '<span class="badge badge-info">Verifikasi 1</span>';
                        elseif ($s === 2) echo '<span class="badge badge-warning">Verifikasi 2</span>';
                        elseif ($s === 3) echo '<span class="badge badge-primary">Menunggu Persetujuan</span>';
                        elseif ($s === 4) echo '<span class="badge badge-success">Selesai / SP2D</span>';
                        elseif ($s === 5) echo '<span class="badge badge-danger">Ditolak: ' . esc($npd['ALASAN_PENOLAKAN']) . '</span>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="font-bold">Tanggal Verifikasi</td>
                    <td>: <?= !empty($npd['TGL_VEIFIKASI']) ? date('d-m-Y H:i', strtotime($npd['TGL_VEIFIKASI'])) : '-' ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="form-group mt-2">
        <label class="font-bold">Program / Kegiatan / Sub Kegiatan:</label>
        <div class="p-2 bg-light border rounded">
            <?= nl2br(esc($npd['NM_PROGRAM_KEGIATAN_SUBKEGIATAN'])) ?>
        </div>
    </div>

    <?php if (!empty($npd['ALASAN_PENOLAKAN'])): ?>
    <div class="alert alert-danger">
        <h5 class="font-bold"><i class="fa fa-exclamation-triangle"></i> Catatan Penolakan:</h5>
        <p class="mb-0"><?= esc($npd['ALASAN_PENOLAKAN']) ?></p>
    </div>
    <?php endif; ?>

    <h5 class="font-bold text-navy mt-3"><i class="fa fa-list"></i> Rincian Belanja (Data Detail)</h5>
    <div class="table-responsive">
        <table id="dtDetailViewNPD" class="table table-bordered table-striped table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Kode Rekening</th>
                    <th style="width: 45%;">Nama Rekening</th>
                    <th class="text-right" style="width: 25%;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>

    <h5 class="font-bold text-navy mt-3"><i class="fa fa-paperclip"></i> Dokumen Lampiran (PDF)</h5>
    <div class="table-responsive">
        <table id="dtFilesViewNPD" class="table table-bordered table-striped table-sm" style="width:100%">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 65%;">Nama Berkas</th>
                    <th style="width: 15%;">Ukuran</th>
                    <th class="text-center" style="width: 15%;">Aksi</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
</div>

<script>
$(document).ready(function() {
    var idPengajuan = '<?= $npd['ID_PENGAJUAN'] ?>';

    // Load details
    $.get('<?= base_url('spm/getdatadetail') ?>', { NO_NPD_SPM: idPengajuan }, function(items) {
        var tbody = $('#dtDetailViewNPD tbody').empty();
        if (!items || items.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center text-muted">Tidak ada rincian belanja.</td></tr>');
        } else {
            var total = 0;
            items.forEach(function(item, idx) {
                var nominal = parseFloat(item.ANGGARAN || 0);
                total += nominal;
                tbody.append(
                    '<tr>' +
                        '<td class="text-center">' + (idx + 1) + '</td>' +
                        '<td>' + (item.KD_REKENING_BELANJA || '-') + '</td>' +
                        '<td>' + (item.NM_REKENING_BELANJA || '-') + '</td>' +
                        '<td class="text-right font-bold">' + nominal.toLocaleString('id-ID', { minimumFractionDigits: 2 }) + '</td>' +
                    '</tr>'
                );
            });
            tbody.append(
                '<tr class="bg-light font-bold">' +
                    '<td colspan="3" class="text-right">TOTAL:</td>' +
                    '<td class="text-right text-success">' + total.toLocaleString('id-ID', { minimumFractionDigits: 2 }) + '</td>' +
                '</tr>'
            );
        }
    });

    // Load files
    $.get('<?= base_url('spm/getallfiles') ?>', { idPengajuan: idPengajuan }, function(files) {
        var tbody = $('#dtFilesViewNPD tbody').empty();
        if (!files || files.length === 0) {
            tbody.append('<tr><td colspan="4" class="text-center text-muted">Tidak ada berkas terlampir.</td></tr>');
        } else {
            files.forEach(function(file, idx) {
                var fileUrl = '<?= base_url('uploads/pdf/') ?>/' + encodeURIComponent(file.FileName);
                tbody.append(
                    '<tr>' +
                        '<td class="text-center">' + (idx + 1) + '</td>' +
                        '<td><i class="fa fa-file-pdf-o text-danger mr-2"></i> ' + (file.Name || file.FileName) + '</td>' +
                        '<td>' + (file.Length ? (Math.round(file.Length / 1024) + ' KB') : '-') + '</td>' +
                        '<td class="text-center">' +
                            '<a href="' + fileUrl + '" target="_blank" class="btn btn-xs btn-primary"><i class="fa fa-download"></i> Unduh / Buka</a>' +
                        '</td>' +
                    '</tr>'
                );
            });
        }
    });
});
</script>
