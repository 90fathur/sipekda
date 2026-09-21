<div class="modal-header">
    <h4 class="modal-title font-bold text-navy"><i class="fa fa-info-circle"></i> <?= esc($Header) ?> - <?= esc($spm['ID_PENGAJUAN']) ?></h4>
    <button type="button" class="close" data-dismiss="modal">&times;</button>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="font-bold" style="width: 40%;">ID SPM</td>
                    <td>: <strong class="text-navy"><?= esc($spm['ID_PENGAJUAN']) ?></strong></td>
                </tr>
                <tr>
                    <td class="font-bold">Tanggal</td>
                    <td>: <?= !empty($spm['TGL_PENGAJUAN']) ? date('d-m-Y H:i', strtotime($spm['TGL_PENGAJUAN'])) : '-' ?></td>
                </tr>
                <tr>
                    <td class="font-bold">ID NPD Asal</td>
                    <td>: <?= esc($spm['ID_NPD'] ?: '-') ?></td>
                </tr>
                <tr>
                    <td class="font-bold">Sumber Dana</td>
                    <td>: <?= esc($spm['KD_SUMBER_DANA'] ?: '-') ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-sm table-borderless">
                <tr>
                    <td class="font-bold" style="width: 40%;">Total Anggaran</td>
                    <td>: <span class="text-success font-bold" style="font-size: 16px;">Rp <?= number_format((float)$spm['ANGGARAN'], 2, ',', '.') ?></span></td>
                </tr>
                <tr>
                    <td class="font-bold">Status Verifikasi</td>
                    <td>: 
                        <?php
                        $s = (int)$spm['KD_STATUS'];
                        if ($s === 1) echo '<span class="badge badge-info">Verifikasi 1</span>';
                        elseif ($s === 2) echo '<span class="badge badge-warning">Verifikasi 2</span>';
                        elseif ($s === 3) echo '<span class="badge badge-primary">Menunggu Persetujuan</span>';
                        elseif ($s === 4) echo '<span class="badge badge-success">Selesai / SP2D</span>';
                        elseif ($s === 5) echo '<span class="badge badge-danger">Ditolak: ' . esc($spm['ALASAN_PENOLAKAN']) . '</span>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="font-bold">Tanggal Verifikasi</td>
                    <td>: <?= !empty($spm['TGL_VEIFIKASI']) ? date('d-m-Y H:i', strtotime($spm['TGL_VEIFIKASI'])) : '-' ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="form-group mt-2">
        <label class="font-bold">Program / Kegiatan / Sub Kegiatan:</label>
        <div class="p-2 bg-light border rounded">
            <?= nl2br(esc($spm['NM_PROGRAM_KEGIATAN_SUBKEGIATAN'])) ?>
        </div>
    </div>

    <h5 class="font-bold text-navy mt-3"><i class="fa fa-list"></i> Rincian Belanja (Data Detail)</h5>
    <div class="table-responsive">
        <table id="dtDetailView" class="table table-bordered table-striped table-sm" style="width:100%">
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
        <table id="dtFilesView" class="table table-bordered table-striped table-sm" style="width:100%">
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
    var idPengajuan = '<?= $spm['ID_PENGAJUAN'] ?>';

    // Load details
    $.get('<?= base_url('spm/getdatadetail') ?>', { NO_NPD_SPM: idPengajuan }, function(items) {
        var tbody = '';
        if (items.length === 0) {
            tbody = '<tr><td colspan="4" class="text-center text-muted">Tidak ada rincian belanja</td></tr>';
        } else {
            items.forEach(function(item, idx) {
                tbody += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td>' + item.KD_REKENING_BELANJA + '</td>' +
                    '<td>' + item.NM_REKENING_BELANJA + '</td>' +
                    '<td class="text-right font-bold">Rp ' + parseFloat(item.ANGGARAN || 0).toLocaleString('id-ID', { minimumFractionDigits: 2 }) + '</td>' +
                    '</tr>';
            });
        }
        $('#dtDetailView tbody').html(tbody);
    });

    // Load files
    $.get('<?= base_url('spm/getallfiles') ?>', { idPengajuan: idPengajuan }, function(files) {
        var tbody = '';
        if (files.length === 0) {
            tbody = '<tr><td colspan="4" class="text-center text-muted">Tidak ada berkas terlampir</td></tr>';
        } else {
            files.forEach(function(f, idx) {
                var sizeKb = (f.Size / 1024).toFixed(1) + ' KB';
                var fileUrl = '<?= base_url('uploads/pdf') ?>/' + f.FileName;
                tbody += '<tr>' +
                    '<td class="text-center">' + (idx + 1) + '</td>' +
                    '<td><a href="' + fileUrl + '" target="_blank"><i class="fa fa-file-pdf-o text-danger"></i> ' + f.Name + '</a></td>' +
                    '<td>' + sizeKb + '</td>' +
                    '<td class="text-center"><a href="' + fileUrl + '" target="_blank" class="btn btn-xs btn-info"><i class="fa fa-eye"></i> Buka</a></td>' +
                    '</tr>';
            });
        }
        $('#dtFilesView tbody').html(tbody);
    });
});
</script>
