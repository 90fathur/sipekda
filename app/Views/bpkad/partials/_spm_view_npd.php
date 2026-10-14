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
                                <input class="form-control col-md-5 text-center font-bold" id="ID_PENGAJUAN" name="ID_PENGAJUAN" value="<?= esc($npd['ID_PENGAJUAN']) ?>" readonly />
                            </div>

                            <h4>TGL. Pengajuan</h4>
                            <p>Tanggal pengajuan SPP/SPM.</p>
                            <div class="mb-3">
                                <input class="form-control col-md-5 text-center" value="<?= !empty($npd['TGL_PENGAJUAN']) ? date('d-m-Y', strtotime($npd['TGL_PENGAJUAN'])) : '' ?>" readonly />
                            </div>

                            <h4>Nama OPD</h4>
                            <p>Nama dari OPD yang mengajukan SPP/SPM.</p>
                            <div class="mb-3">
                                <select class="form-control" disabled>
                                    <?php foreach ($ListSKPD as $skpd): ?>
                                        <option value="<?= esc($skpd['KD_SKPD']) ?>" <?= $skpd['KD_SKPD'] == $npd['KD_SKPD'] ? 'selected' : '' ?>><?= esc($skpd['NM_SKPD']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <h4>Nama Program Kegiatan &amp; Sub Kegiatan</h4>
                            <p>Informasi lengkap nama kegiatan &amp; sub kegiatan yang akan di usulkan.</p>
                            <div class="mb-3">
                                <textarea class="form-control" rows="4" readonly><?= esc($npd['NM_PROGRAM_KEGIATAN_SUBKEGIATAN']) ?></textarea>
                            </div>

                            <h4 style="margin-bottom: 1px">Sumber Dana</h4>
                            <p>Sumber dana yang di gunakan untuk kegiatan tersebut.</p>
                            <div class="mb-3">
                                <input type="text" class="form-control font-bold" value="<?= esc($npd['KD_SUMBER_DANA'] ?: ($npd['KD_REKENING_BELANJA'] ?: '-')) ?>" readonly style="background-color: #f8f9fa;">
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
                                            <th class="text-center" style="width: 28%;">Anggaran</th>
                                            <th class="text-center" style="width: 12%;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>

                            <hr/>
                            <h4>Total Alokasi Anggaran</h4>
                            <p>Jumlah total anggaran yang di alokasikan untuk kegiatan tersebut.</p>
                            <div class="mb-4">
                                <input class="form-control col-md-6 text-right font-bold currency" id="TOTAL_ALOKASI_ANGGARAN" value="<?= number_format((float)$npd['ANGGARAN'], 2, ',', '.') ?>" readonly />
                            </div>

                            <?php if (!empty($npd['ALASAN_PENOLAKAN'])): ?>
                                <div class="alert alert-danger">
                                    <h4 class="font-bold">Alasan Penolakan:</h4>
                                    <p><?= esc($npd['ALASAN_PENOLAKAN']) ?></p>
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
                        <?php if ((int)($npd['KD_STATUS'] ?? 0) === 4): ?>
                            <div class="alert alert-primary text-center font-bold mb-0" style="background-color: #1ab394; color: #fff;">
                                <i class="fa fa-check-circle fa-2x"></i><br>
                                <span style="font-size: 15px;">NPD INI TELAH SELESAI DIPERIKSA &amp; DIVERIFIKASI</span>
                                <br><small><i class="fa fa-calendar"></i> Waktu Verifikasi: <?= !empty($npd['TGL_VEIFIKASI']) ? date('d-m-Y H:i', strtotime($npd['TGL_VEIFIKASI'])) : '-' ?></small>
                                <div class="mt-3">
                                    <button type="button" id="btnBatalVerif" class="btn btn-sm btn-white text-danger font-bold shadow-sm">
                                        <i class="fa fa-undo"></i> Batalkan Verifikasi
                                    </button>
                                </div>
                            </div>
                        <?php elseif ((int)($npd['KD_STATUS'] ?? 0) === 6): ?>
                            <div class="alert alert-primary text-center font-bold mb-0" style="background-color: #1c84c6; color: #fff;">
                                <i class="fa fa-check-double fa-2x"></i><br>
                                <span style="font-size: 15px;">NPD INI SUDAH DI PROSES DI SIPD</span>
                                <br><small><i class="fa fa-calendar"></i> Waktu Verifikasi: <?= !empty($npd['TGL_VEIFIKASI']) ? date('d-m-Y H:i', strtotime($npd['TGL_VEIFIKASI'])) : '-' ?></small>
                                <div class="mt-3">
                                    <button type="button" id="btnBatalVerif" class="btn btn-sm btn-white text-danger font-bold shadow-sm">
                                        <i class="fa fa-undo"></i> Batalkan Status
                                    </button>
                                </div>
                            </div>
                        <?php elseif ((int)($npd['KD_STATUS'] ?? 0) === 5): ?>
                            <div class="alert alert-danger text-center font-bold mb-0">
                                <i class="fa fa-times-circle fa-2x"></i><br>
                                <span style="font-size: 15px;">NPD INI DITOLAK</span>
                                <?php if (!empty($npd['ALASAN_PENOLAKAN'])): ?>
                                    <br><small>Alasan: <?= esc($npd['ALASAN_PENOLAKAN']) ?></small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
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
                        <?php endif; ?>
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

    $('#btnBatalVerif').on('click', function (e) {
        Swal.fire({
            title: 'Batalkan Verifikasi?',
            text: 'Status pengajuan akan dikembalikan ke Menunggu Persetujuan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fa fa-undo"></i> Ya, Batalkan Verifikasi',
            cancelButtonText: 'Kembali',
            confirmButtonColor: '#ed5565',
            cancelButtonColor: '#c2c2c2'
        }).then((result) => {
            if (result.isConfirmed) {
                if (typeof Unaccept === 'function') {
                    Unaccept($('#ID_PENGAJUAN').val());
                }
            }
        });
    });
</script>
