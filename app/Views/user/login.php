<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ASSAMI | Login</title>
    <link rel="icon" type="image/png" href="<?= base_url('assets/Assami.png') ?>">

    <link rel="stylesheet" href="<?= base_url('assets/lib/bootstrap/dist/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/font-awesome6/css/all.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/animate.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/ladda/dist/ladda-themeless.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/lib/sweetalert2/dist/sweetalert2.min.css') ?>">

    <style>
        body {
            background-color: #f3f3f4;
            font-family: 'open sans', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            overflow: hidden;
            width: 100%;
            max-width: 950px;
        }
        .login-img-container {
            background: linear-gradient(135deg, #1ab394 0%, #178a72 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
        }
        .login-img-container img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .login-form-container {
            padding: 40px;
        }
    </style>
</head>

<body class="gray-bg">
    <div class="login-container animated fadeInDown">
        <div class="login-card">
            <div class="row no-gutters">
                <div class="col-lg-6 d-none d-lg-flex login-img-container">
                    <img src="<?= base_url('assets/skin3.png') ?>" alt="SIPEKDA ASSAMI" class="img-fluid">
                </div>
                <div class="col-lg-6 login-form-container">
                    <?= $this->include('user/_menu_login_user') ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="<?= base_url('assets/lib/jquery/dist/jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/popper/popper.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/bootstrap/dist/js/bootstrap.js') ?>"></script>
    <script src="<?= base_url('assets/lib/ladda/dist/spin.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/ladda/dist/ladda.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/ladda/dist/ladda.jquery.min.js') ?>"></script>
    <script src="<?= base_url('assets/lib/sweetalert2/dist/sweetalert2.all.min.js') ?>"></script>
</body>
</html>
