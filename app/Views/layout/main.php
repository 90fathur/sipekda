<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASSAMI | <?= esc($Title ?? 'Sistem Penatausahaan Keuangan Daerah') ?></title>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <link rel="icon" type="image/png" href="<?= base_url('assets/Assami.png') ?>">

    <!-- Base Styles -->
    <link rel="stylesheet" href="<?= base_url('assets/lib/bootstrap/dist/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/font-awesome6/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/animate.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/sweetalert2/dist/sweetalert2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/dataTables/datatables.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/select2/dist/css/select2.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/ladda/dist/ladda-themeless.min.css') ?>">

    <!-- jQuery First -->
    <script src="<?= base_url('assets/lib/jquery/dist/jquery.min.js') ?>"></script>

    <style>
        /* DataTables Bootstrap 4 Alignment */
        div.dataTables_wrapper div.dataTables_filter {
            text-align: right;
        }
        div.dataTables_wrapper div.dataTables_filter input {
            margin-left: 0.5em;
            display: inline-block;
            width: auto;
        }
        div.dataTables_wrapper div.dataTables_length label {
            font-weight: normal;
            text-align: left;
            white-space: nowrap;
        }
        div.dataTables_wrapper div.dataTables_length select {
            width: auto;
            display: inline-block;
            margin: 0 5px;
        }
        div.dataTables_wrapper div.dataTables_paginate ul.pagination {
            margin: 2px 0;
            white-space: nowrap;
            justify-content: flex-end;
        }
        table.dataTable {
            width: 100% !important;
            margin-top: 10px !important;
            margin-bottom: 10px !important;
        }
        table.dataTable thead th {
            vertical-align: middle !important;
        }

        /* Sidebar profile and mini-navbar overflow fix */
        body.mini-navbar .profile-element {
            display: none !important;
        }
        body.mini-navbar .logo-element {
            display: block !important;
        }
        .truncate, .truncate2 {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: block;
            max-width: 180px;
        }
        .navbar-default, .sidebar-collapse {
            overflow: hidden;
        }

        /* Select2 Dropdown in Modal Styling & z-index */
        .select2-container {
            width: 100% !important;
        }
        .select2-container--open {
            z-index: 99999 !important;
        }
        .select2-dropdown {
            z-index: 99999 !important;
            border: 1px solid #1ab394 !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
        }
    </style>

    <?= $this->renderSection('styles') ?>
</head>

<body class="light-skin">
    <div id="wrapper">
        <!-- Navigation -->
        <?= $this->include('layout/navigation') ?>

        <!-- Page Wrapper -->
        <div id="page-wrapper" class="gray-bg">
            <!-- Top Navbar -->
            <?= $this->include('layout/topnavbar') ?>

            <!-- Page Title Bar if header set -->
            <?php if (!empty($Header)): ?>
            <div class="row wrapper border-bottom white-bg page-heading">
                <div class="col-lg-10">
                    <h2 class="font-bold text-navy" style="margin-top: 15px; margin-bottom: 5px;"><?= esc($Header) ?></h2>
                    <?php if (!empty($Keterangan)): ?>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item active">
                                <span><?= esc($Keterangan) ?></span>
                            </li>
                        </ol>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Main Content -->
            <div class="wrapper wrapper-content">
                <?= $this->renderSection('content') ?>
            </div>

            <!-- Footer -->
            <?= $this->include('layout/footer') ?>
        </div>
    </div>

    <!-- Core Scripts -->
    <script src="<?= base_url('assets/lib/popper/popper.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/bootstrap/dist/js/bootstrap.js') ?>"></script>
    <script src="<?= base_url('assets/lib/metisMenu/dist/jquery.metisMenu.js') ?>"></script>
    <script src="<?= base_url('assets/lib/pace/pace.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/slimScroll/jquery.slimscroll.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/sweetalert2/dist/sweetalert2.all.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/dataTables/datatables.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/dataTables/dataTables.bootstrap4.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/chartJs/Chart.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/moment/moment.js') ?>"></script>
    <script src="<?= base_url('assets/lib/select2/dist/js/select2.full.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/ladda/dist/spin.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/ladda/dist/ladda.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/ladda/dist/ladda.jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/js/script.js') ?>"></script>

    <?= $this->renderSection('scripts') ?>
</body>
</html>
