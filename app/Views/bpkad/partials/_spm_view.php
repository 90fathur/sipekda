<div class="row">
    <div class="col-lg-7">
        <form id="form-data">
            <?= csrf_field() ?>

            <div class="ibox">
                <div class="ibox-title">
                    <h2 class="font-bold text-navy typewriter" style="margin-bottom: 1px"><?= esc($Header) ?></h2>
                    <h5 class="typewriter"><?= esc($Keterangan) ?></h5>
                </div>
                <div class="ibox-content">
                    <div class="row">
                        <div class="col-lg-12">
                            <h4>ID. Pengajuan</h4>
                            <p>ID ini akan terbentuk secara dinamis agar tidak terjadi duplikasi data.</p>
                            <div class="mb-3">
                                <input class="form-control col-md-5 text-center font-bold" id="ID_PENGAJUAN" name="ID_PENGAJUAN" value="<?= esc($spm['ID_PENGAJUAN']) ?>" readonly />
                            </div>

                            <h4>TGL. Pengajuan</h4>
                            <p>Tanggal pengajuan SPP/SPM.</p>
                            <div class="mb-3">
                                <input class="form-control col-md-5 text-center" value="<?= !empty($spm['TGL_PENGAJUAN']) ? date('d-m-Y', strtotime($spm['TGL_PENGAJUAN'])) : '' ?>" readonly />
                            </div>

                            <h4>Nama OPD</h4>
                            <p>Nama dari OPD yang mengajukan SPP/SPM.</p>
                            <div class="mb-3">
                                <select class="form-control" disabled>
                                    <?php foreach ($ListSKPD as $skpd): ?>
                                        <option value="<?= esc($skpd['KD_SKPD']) ?>" <?= $skpd['KD_SKPD'] == $spm['KD_SKPD'] ? 'selected' : '' ?>><?= esc($skpd['NM_SKPD']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <h4>Nama Program Kegiatan &amp; Sub Kegiatan</h4>
                            <p>Informasi lengkap nama kegiatan &amp; sub kegiatan yang akan di usulkan.</p>
                            <div class="mb-3">
                                <textarea class="form-control" rows="4" readonly><?= esc($spm['NM_PROGRAM_KEGIATAN_SUBKEGIATAN']) ?></textarea>
                            </div>

                            <h4 style="margin-bottom: 1px">Sumber Dana</h4>
                            <p>Sumber dana yang di gunakan untuk kegiatan tersebut.</p>
                            <div class="mb-3">
                                <select class="form-control" disabled>
                                    <?php foreach ($ListMataAnggaran as $ma): ?>
                                        <option value="<?= esc($ma['KD_MATA_ANGGARAN']) ?>" <?= $ma['KD_MATA_ANGGARAN'] == $spm['KD_REKENING_BELANJA'] ? 'selected' : '' ?>><?= esc($ma['KD_MATA_ANGGARAN']) ?> - <?= esc($ma['NM_MATA_ANGGARAN']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <hr/>
                            <h4>Rekening Belanja</h4>
                            <p>Daftar semua rekening belanja yang di gunakan.</p>
                            <div class="table-responsive">
                                <table id="dtDaftarBelanja" class="table table-striped table-bordered table-hover" style="width:100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 5%;">No</th>
                                            <th class="text-center">Rekening Belanja</th>
                                            <th class="text-center" style="width: 30%;">Anggaran</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <hr/>
                            <h4>Total Alokasi Anggaran</h4>
                            <p>Jumlah total anggaran yang di alokasikan untuk kegiatan tersebut.</p>
                            <div class="mb-4">
                                <input class="form-control col-md-6 text-right font-bold currency" value="<?= number_format((float)$spm['ANGGARAN'], 2, ',', '.') ?>" readonly />
                            </div>

                            <input type="hidden" id="ID_NPD" value="<?= esc($spm['ID_NPD'] ?: $spm['ID_PENGAJUAN']) ?>" />

                            <?php if (!empty($spm['ALASAN_PENOLAKAN'])): ?>
                                <div class="alert alert-danger">
                                    <h4 class="font-bold">Alasan Penolakan:</h4>
                                    <p><?= esc($spm['ALASAN_PENOLAKAN']) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="col-lg-5">
        <div class="row">
            <div class="col-md-12">
                <div class="ibox">
                    <div class="ibox-title">
                        <h2 class="font-bold text-navy typewriter" style="margin-bottom: 1px">Berkas Digital</h2>
                        <h5 class="typewriter">Daftar berkas digital sebagai lampiran</h5>
                    </div>
                    <div class="ibox-content">
                        <div class="row" id="fileContainer">
                            <!-- File akan ditambahkan di sini melalui AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="ibox">
                    <div class="ibox-content">
                        <div class="row">
                            <div class="col-md-6">
                                <button type="button" id="btnSetujui" class="btn btn-primary btn-block">
                                    <i class="fa fa-check"></i> Setujui
                                </button>
                            </div>
                            <div class="col-md-6">
                                <button type="button" id="btnTolak" class="btn btn-danger btn-block">
                                    <i class="fa fa-times"></i> Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $('#btnSetujui').on('click', function (e) {
        Swal.fire({
            title: 'Konfirmasi',
            text: 'Apakah benar pengajuan akan di setujui?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Proses',
            cancelButtonText: 'Tidak',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                return new Promise(function () {
                    Accept($('#ID_PENGAJUAN').val());
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        });
    });

    $('#btnTolak').on('click', function (e) {
        Swal.fire({
            title: "Tolak Pengajuan?",
            input: "textarea",
            inputLabel: "Alasan Penolakan",
            inputPlaceholder: "Tulis alasan penolakan di sini...",
            showCancelButton: true,
            confirmButtonText: "Iya, Tolak",
            cancelButtonText: "Batal",
            preConfirm: (value) => {
                if (!value) {
                    Swal.showValidationMessage("Alasan wajib diisi");
                }
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Reject($('#ID_PENGAJUAN').val(), result.value);
            }
        });
    });
</script>
