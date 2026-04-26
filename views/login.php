<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!--favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/images/favicon.ico">
    <!-- preconnect a Google Fonts (acelera descarga de Roboto) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <!-- loader-->
    <link href="<?php echo BASE_URL; ?>assets/css/pace.min.css" rel="stylesheet" />
    <script src="<?php echo BASE_URL; ?>assets/js/pace.min.js" defer></script>
    <!-- Bootstrap + estilos propios -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/app.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/icons.css" rel="stylesheet">
    <title><?php echo TITLE . ' - ' . $data['title']; ?></title>
</head>

<body class="bg-login">
    <!--wrapper-->
    <div class="wrapper">
        <div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
            <div class="container-fluid">
                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                    <div class="col mx-auto">
                        <div class="mb-1 text-center">
                            <img src="<?php echo BASE_URL; ?>assets/images/logoedessi.png" width="80%" alt="" />
                        </div>
                        <div class="mb-1 text-center">
                            <span style="font-size: 50px;
  color: #008cff;font-family: initial;" > MEGAHNET</span>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="border p-4 rounded">
                                    <div class="text-center">
                                        <h3 class="">Iniciar Sesión</h3>
                                    </div>
                                    <div class="login-separater text-center mb-4"> <span>OR SIGN IN WITH EMAIL</span>


                                        <hr />
                                    </div>
                                    <div class="form-body">


                                        <form class="row g-3" id="formulario" method="POST" autocomplete="off">

                                            <div class="col-12">
                                                <label for="correo" class="form-label">Correo Electrónico</label>
                                                <input type="email" class="form-control" id="correo" name="correo"
                                                    placeholder="Correo Electrónico">
                                                <span id="errorCorreo" class="text-danger"></span>
                                            </div>
                                            <div class="col-12">
                                                <label for="clave" class="form-label">Contraseña</label>
                                                <div class="input-group" id="show_hide_password">
                                                    <input type="password" class="form-control border-end-0" id="clave"
                                                        name="clave" placeholder="Contraseña">
                                                    <a href="javascript:;" class="input-group-text bg-transparent"><i
                                                            class='bx bx-hide'></i></a>
                                                </div>
                                                <span id="errorClave" class="text-danger"></span>
                                            </div>
                                            <div class="col-md-12 text-end"> <a
                                                    href="<?php echo BASE_URL . 'principal/forgot'; ?>">Olvidaste tu
                                                    contraseña?</a>
                                            </div>
                                            <div class="col-12">
                                                <div class="d-grid">
                                                    <button type="submit" class="btn btn-primary"><i
                                                            class="bx bxs-lock-open"></i>Acceso</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--end row-->
            </div>
        </div>
    </div>
    <!--end wrapper-->
    <script>
    const base_url = '<?php echo BASE_URL; ?>';
    // Show/hide password (vanilla, sin jQuery)
    document.addEventListener('DOMContentLoaded', function () {
        var trigger = document.querySelector('#show_hide_password a');
        if (!trigger) return;
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            var input = document.querySelector('#show_hide_password input');
            var icon  = document.querySelector('#show_hide_password i');
            if (!input || !icon) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bx-hide');
                icon.classList.add('bx-show');
            } else {
                input.type = 'password';
                icon.classList.remove('bx-show');
                icon.classList.add('bx-hide');
            }
        });
    });
    </script>
    <!-- SweetAlert2 (usado por login.js) + login.js -->
    <script src="<?php echo BASE_URL; ?>assets/js/sweetalert2.all.min.js" defer></script>
    <script src="<?php echo BASE_URL; ?>assets/js/modulos/login.js" defer></script>
</body>

</html>