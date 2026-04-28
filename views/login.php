<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#f4f6fb">
    <style>html, body { background-color: #f4f6fb; }</style>
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/images/favicon.ico">
    <link href="<?php echo BASE_URL; ?>assets/css/pace.min.css" rel="stylesheet" />
    <script src="<?php echo BASE_URL; ?>assets/js/pace.min.js" defer></script>
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/app.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/icons.css" rel="stylesheet">
    <title><?php echo htmlspecialchars(TITLE, ENT_QUOTES, 'UTF-8') . ' - ' . htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8'); ?></title>
    <style>
        /* Fontstack del sistema: 0ms de carga, se ve igual o mejor que Roboto en desktop */
        body.bg-login { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
        .login-page  { min-height: 100vh; padding: 2rem 1rem; }
        .login-card  { border: 0; border-radius: 18px; box-shadow: 0 12px 40px rgba(0,0,0,.10); }
        .login-brand img { max-height: 90px; width: auto; }
        .login-form .input-group-text { background: #f8f9fa; border-color: #e5e7eb; }
        .login-form .form-control { border-color: #e5e7eb; }
        .login-form .form-control:focus { box-shadow: 0 0 0 .2rem rgba(13,110,253,.12); border-color: #86b7fe; }
        .login-form .btn-primary { padding: .65rem 1rem; font-weight: 500; transition: transform .12s ease, box-shadow .12s ease; }
        .login-form .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(13,110,253,.30); }
        .login-form .show-pass { cursor: pointer; user-select: none; }
        .login-foot a { color: #6c757d; text-decoration: none; }
    </style>
</head>

<body class="bg-login">
    <div class="login-page d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-5 col-xl-4">

                    <div class="text-center mb-4 login-brand">
                        <img src="<?php echo BASE_URL . "assets/images/" . (is_file(ROOT_PATH . "/assets/images/Logo.jpg") ? "Logo.jpg?v=" . filemtime(ROOT_PATH . "/assets/images/Logo.jpg") : "logoedessi.png"); ?>" alt="<?php echo htmlspecialchars(TITLE, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid mb-2">
                        <p class="text-muted small mb-0">Sistema de Gestión y Facturación Electrónica</p>
                    </div>

                    <div class="card login-card">
                        <div class="card-body p-4 p-md-5">
                            <h4 class="fw-semibold text-center mb-1">Iniciar sesión</h4>
                            <p class="text-muted text-center small mb-4">Ingresa tus credenciales para continuar</p>

                            <form id="formulario" method="POST" autocomplete="off" class="login-form">

                                <div class="mb-3">
                                    <label for="correo" class="form-label small fw-medium mb-1">Correo electrónico</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bx bx-envelope text-muted"></i></span>
                                        <input type="email" class="form-control" id="correo" name="correo" placeholder="tu@correo.com" autocomplete="username">
                                    </div>
                                    <span id="errorCorreo" class="text-danger small"></span>
                                </div>

                                <div class="mb-2">
                                    <label for="clave" class="form-label small fw-medium mb-1">Contraseña</label>
                                    <div class="input-group" id="show_hide_password">
                                        <span class="input-group-text"><i class="bx bx-lock text-muted"></i></span>
                                        <input type="password" class="form-control" id="clave" name="clave" placeholder="••••••••" autocomplete="current-password">
                                        <a href="javascript:;" class="input-group-text show-pass" aria-label="Mostrar/ocultar contraseña"><i class="bx bx-hide"></i></a>
                                    </div>
                                    <span id="errorClave" class="text-danger small"></span>
                                </div>

                                <div class="text-end mb-3">
                                    <a href="<?php echo BASE_URL . 'principal/forgot'; ?>" class="small text-decoration-none">¿Olvidaste tu contraseña?</a>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bx bxs-lock-open me-1"></i>Iniciar sesión
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="text-center login-foot mt-3 small text-muted">
                        © <?php echo date('Y') . ' ' . htmlspecialchars(TITLE, ENT_QUOTES, 'UTF-8'); ?>
                        <?php if (defined('CONTACTO') && CONTACTO !== '0000000000' && CONTACTO !== '') { ?>
                            · Soporte: <a href="tel:<?php echo htmlspecialchars(CONTACTO, ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(CONTACTO, ENT_QUOTES, 'UTF-8'); ?></a>
                        <?php } ?>
                    </div>

                </div>
            </div>
        </div>
    </div>

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
    <script src="<?php echo BASE_URL; ?>assets/js/sweetalert2.all.min.js" defer></script>
    <script src="<?php echo BASE_URL; ?>assets/js/modulos/login.js" defer></script>
</body>

</html>
