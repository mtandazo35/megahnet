<!doctype html>
<html lang="es">

<head>
    <?php
    // Cache busting: agrega ?v=<timestamp> a CSS/JS personalizados que se editan
    // a menudo. Si filemtime falla, fallback al timestamp actual (siempre revalida).
    if (!function_exists('asset_v')) {
        function asset_v($relPath) {
            $abs = ROOT_PATH . '/' . ltrim($relPath, '/');
            $t = @filemtime($abs);
            return $t !== false ? (int)$t : time();
        }
    }
    ?>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Forzar color scheme claro: evita que el browser pinte fondo oscuro
         siguiendo el tema del SO antes de que se carguen los CSS. -->
    <meta name="color-scheme" content="light">
    <meta name="theme-color" content="#f4f6fb">
    <!-- Transicion suave entre paginas (Chrome 126+, Safari 18+; en otros se ignora) -->
    <meta name="view-transition" content="same-origin">
    <!--favicon-->
    <link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>assets/images/favicon.ico">
    <link href="<?php echo BASE_URL; ?>assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />
    <!-- loader-->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/jquery-ui.min.css">
    <!-- Bootstrap CSS -->
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/bootstrap-extended.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&display=swap" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/app.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/icons.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>assets/css/modern-theme.css?v=<?php echo asset_v('assets/css/modern-theme.css'); ?>" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>assets/css/form-sections.css?v=<?php echo asset_v('assets/css/form-sections.css'); ?>" rel="stylesheet" />
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dark-theme.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/semi-dark.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header-colors.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/DataTables/datatables.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/fullcalendar/css/main.min.css" />

    <title><?php echo TITLE . ' - ' . $data['title']; ?></title>

    <!-- Background base: evita flash negro/oscuro durante navegacion (CSS aun cargando) -->
    <style>
        html, body { background-color: #f4f6fb; }
        body.bg-login { background-color: #f4f6fb; }
        .wrapper, .page-wrapper, .page-content { background-color: #f4f6fb; }
        .sidebar-wrapper { background-color: #ffffff; }

        /* === View Transitions API: navegacion estilo SPA (browsers modernos) ===
           Importante: el overlay/group/snapshot deben tener bg explicito
           para evitar el "flash negro" cuando el browser pinta el snapshot
           sobre un fondo transparente. */
        @view-transition { navigation: auto; }

        /* Fondos forzados a TODOS los pseudo-elementos de view-transition */
        ::view-transition,
        ::view-transition-group(root),
        ::view-transition-image-pair(root),
        ::view-transition-old(root),
        ::view-transition-new(root) {
            background-color: #f4f6fb;
        }

        .sidebar-wrapper { view-transition-name: mhn-sidebar; background: #fff; }
        header           { view-transition-name: mhn-topbar;  background: #fff; }
        .page-content    { view-transition-name: mhn-content; background: #f4f6fb; }

        ::view-transition-group(mhn-sidebar),
        ::view-transition-image-pair(mhn-sidebar),
        ::view-transition-old(mhn-sidebar),
        ::view-transition-new(mhn-sidebar) { background: #fff; animation: none !important; }

        ::view-transition-group(mhn-topbar),
        ::view-transition-image-pair(mhn-topbar),
        ::view-transition-old(mhn-topbar),
        ::view-transition-new(mhn-topbar) { background: #fff; animation: none !important; }

        ::view-transition-group(mhn-content),
        ::view-transition-image-pair(mhn-content) { background: #f4f6fb; }
        ::view-transition-old(mhn-content) {
            background: #f4f6fb;
            animation: 100ms ease-out both vt-fade-out;
        }
        ::view-transition-new(mhn-content) {
            background: #f4f6fb;
            animation: 180ms ease-in both vt-fade-in;
        }

        @keyframes vt-fade-out { to { opacity: 0; } }
        @keyframes vt-fade-in  { from { opacity: 0; } to { opacity: 1; } }

        /* Respetar prefer-reduced-motion: sin animacion */
        @media (prefers-reduced-motion: reduce) {
            ::view-transition-old(mhn-content),
            ::view-transition-new(mhn-content) { animation: none; }
        }

        /* === Fade-out manual al hacer click en un link de navegacion ===
           Funciona en TODOS los browsers (no requiere View Transitions).
           Mientras el browser navega, el contenido viejo se va con fade
           => no se ve el flash blanco/oscuro entre paginas. */
        .page-content {
            transition: opacity 120ms ease-out;
        }
        body.mhn-navigating .page-content {
            opacity: 0;
            pointer-events: none;
        }
        /* Loader bar superior durante navegacion */
        body.mhn-navigating::before {
            content: '';
            position: fixed;
            top: 0; left: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563eb, #7c3aed, #2563eb);
            background-size: 200% 100%;
            z-index: 9999;
            animation: mhn-loader-fill 600ms ease-out forwards;
            box-shadow: 0 0 8px rgba(37,99,235,.5);
        }
        @keyframes mhn-loader-fill {
            0%   { width: 0%;  background-position: 0%   50%; }
            60%  { width: 70%; background-position: 100% 50%; }
            100% { width: 100%; background-position: 100% 50%; }
        }
    </style>
    <!-- Tab persist: oculta tab-content hasta que tab-persist.js active el tab correcto -->
    <script>
        if (window.location.hash) document.documentElement.classList.add('tab-pending');
    </script>
    <!-- Sidebar scroll persist: oculta el sidebar hasta restaurar la posicion (evita flicker) -->
    <script>
        try {
            var __t = sessionStorage.getItem('mhn_sidebar_scroll');
            if (__t && parseInt(__t, 10) > 0) {
                document.documentElement.classList.add('sb-restoring');
            }
        } catch(e){}
    </script>
    <style>
        .tab-pending .tab-content, .tab-pending .nav-tabs { visibility: hidden; }
        /* Mientras restauramos el scroll, ocultamos el sidebar para que no se vea el "salto" */
        html.sb-restoring .sidebar-wrapper { visibility: hidden; }
    </style>
</head>

<body>
    <!--wrapper-->
    <div class="wrapper">
        <!--sidebar wrapper -->
        <div class="sidebar-wrapper" data-simplebar="true">
            <div class="sidebar-header">
                <div class="logo-wrap">
                    <img src="<?php echo BASE_URL; ?>assets/images/logoedessi.png" alt="logo">
                </div>
                <button class="sidebar-toggle-btn" type="button" aria-label="Colapsar menu">
                    <i class="bx bx-chevron-left"></i>
                </button>
            </div>

            <div id="loader" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;justify-content:center;align-items:center;">
                <div style="border:8px solid #f3f3f3;border-top:8px solid #3498db;border-radius:50%;width:50px;height:50px;animation:spin 1s linear infinite;"></div>
            </div>
            <div id="divLoading" style="display:none;"><div><img src="<?= BASE_URL; ?>assets/images/loading.svg" alt="Loading"></div></div>

            <ul class="metismenu" id="menu">

                <li class="menu-label">Principal</li>

                <li data-url="admin">
                    <a href="<?php echo BASE_URL . 'admin'; ?>">
                        <div class="parent-icon"><i class="bx bx-home-alt"></i></div>
                        <div class="menu-title">Tablero</div>
                    </a>
                </li>

                <li class="menu-label">Operaciones</li>

                <li data-url="contratos">
                    <a href="<?php echo BASE_URL . 'contratos'; ?>">
                        <div class="parent-icon"><i class="bx bx-file"></i></div>
                        <div class="menu-title">Contratos</div>
                    </a>
                </li>

                <li data-url="creditos">
                    <a href="<?php echo BASE_URL . 'creditos'; ?>">
                        <div class="parent-icon"><i class="bx bx-dollar-circle"></i></div>
                        <div class="menu-title">Administrar Créditos</div>
                    </a>
                </li>

                <li data-url="cotizaciones">
                    <a href="<?php echo BASE_URL . 'cotizaciones'; ?>">
                        <div class="parent-icon"><i class="bx bx-clipboard"></i></div>
                        <div class="menu-title">Cotizaciones</div>
                    </a>
                </li>

                <li class="has-sub">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-cart"></i></div>
                        <div class="menu-title">Ventas</div>
                    </a>
                    <ul>
                        <li data-url="sridashboard"><a href="<?php echo BASE_URL . 'sridashboard'; ?>"><i class="bx bx-bar-chart-alt-2"></i>SRI Dashboard</a></li>
                        <li data-url="factura"><a href="<?php echo BASE_URL . 'factura'; ?>"><i class="bx bx-receipt"></i>Facturas</a></li>
                        <li data-url="automaticas/index"><a href="<?php echo BASE_URL . "automaticas/index"; ?>"><i class="bx bx-check-circle"></i>Cerrar Corte F</a></li>
                        <li data-url="notacredito"><a href="<?php echo BASE_URL . 'notacredito'; ?>"><i class="bx bx-minus-circle"></i>Nota Crédito</a></li>
                        <li data-url="ordenventa"><a href="<?php echo BASE_URL . 'ordenventa'; ?>"><i class="bx bx-package"></i>Orden Venta</a></li>
                        <li data-url="automaticas/indexOrdenVenta"><a href="<?php echo BASE_URL . 'automaticas/indexOrdenVenta'; ?>"><i class="bx bx-check-double"></i>Cerrar Corte OV</a></li>
                    </ul>
                </li>

                <li class="has-sub">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-cart-add"></i></div>
                        <div class="menu-title">Gestión Compra</div>
                    </a>
                    <ul>
                        <li data-url="proveedor"><a href="<?php echo BASE_URL . 'proveedor'; ?>"><i class="bx bx-store"></i>Proveedores</a></li>
                        <li data-url="compras"><a href="<?php echo BASE_URL . 'compras'; ?>"><i class="bx bx-cart"></i>Compras</a></li>
                        <li data-url="retenciones"><a href="<?php echo BASE_URL . 'retenciones'; ?>"><i class="bx bx-receipt"></i>Retenciones</a></li>
                    </ul>
                </li>

                <?php if (isset($_SESSION['id_usuario'])) { ?>
                <li class="menu-label">Clientes &amp; Cajas</li>

                <li data-url="clientes">
                    <a href="<?php echo BASE_URL . 'clientes'; ?>">
                        <div class="parent-icon"><i class="bx bx-group"></i></div>
                        <div class="menu-title">Clientes</div>
                    </a>
                </li>

                <li data-url="cajas">
                    <a href="<?php echo BASE_URL . 'cajas'; ?>">
                        <div class="parent-icon"><i class="bx bx-wallet"></i></div>
                        <div class="menu-title">Cajas</div>
                    </a>
                </li>

                <li data-url="casos">
                    <a href="<?php echo BASE_URL . 'casos'; ?>">
                        <div class="parent-icon"><i class="bx bx-support"></i></div>
                        <div class="menu-title">Casos</div>
                    </a>
                </li>

                <li data-url="mikrotiks">
                    <a href="<?php echo BASE_URL . 'mikrotiks'; ?>">
                        <div class="parent-icon"><i class="bx bx-server"></i></div>
                        <div class="menu-title">Mikrotik</div>
                    </a>
                </li>
                <?php } ?>

                <?php if (isset($_SESSION['id_usuario'])) { ?>
                <li class="menu-label">Inventario</li>

                <li class="has-sub">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-box"></i></div>
                        <div class="menu-title">Mantenimiento</div>
                    </a>
                    <ul>
                        <li data-url="categorias"><a href="<?php echo BASE_URL . 'categorias'; ?>"><i class="bx bx-category"></i>Categorías</a></li>
                        <li data-url="productos"><a href="<?php echo BASE_URL . 'productos'; ?>"><i class="bx bx-cube"></i>Productos</a></li>
                        <li data-url="inventarios"><a href="<?php echo BASE_URL . 'inventarios'; ?>"><i class="bx bx-list-check"></i>Inventario &amp; Kardex</a></li>
                        <li data-url="zonas"><a href="<?php echo BASE_URL . 'zonas'; ?>"><i class="bx bx-map"></i>Zonas</a></li>
                        <li data-url="rangoip"><a href="<?php echo BASE_URL . 'rangoip'; ?>"><i class="bx bx-network-chart"></i>Rango IP</a></li>
                        <li data-url="grupotrabajos"><a href="<?php echo BASE_URL . 'grupotrabajos'; ?>"><i class="bx bx-sitemap"></i>Grupos de Trabajo</a></li>
                        <li data-url="repetidoras"><a href="<?php echo BASE_URL . 'repetidoras'; ?>"><i class="bx bx-broadcast"></i>Repetidoras</a></li>
                    </ul>
                </li>
                <?php } ?>

                <?php if (isset($_SESSION['id_usuario']) && $_SESSION['rol'] == 1) { ?>
                <li class="menu-label">Administración</li>

                <li class="has-sub">
                    <a href="javascript:;" class="has-arrow">
                        <div class="parent-icon"><i class="bx bx-cog"></i></div>
                        <div class="menu-title">Sistema</div>
                    </a>
                    <ul>
                        <li data-url="usuarios"><a href="<?php echo BASE_URL . 'usuarios'; ?>"><i class="bx bx-user"></i>Usuarios</a></li>
                        <li data-url="admin/datos"><a href="<?php echo BASE_URL . 'admin/datos'; ?>"><i class="bx bx-buildings"></i>Configuración</a></li>
                        <li data-url="admin/logs"><a href="<?php echo BASE_URL . 'admin/logs'; ?>"><i class="bx bx-history"></i>Log de Acceso</a></li>
                    </ul>
                </li>

                <li data-url="admin/respaldos">
                    <a href="<?php echo BASE_URL . 'admin/respaldos'; ?>">
                        <div class="parent-icon"><i class="bx bx-cloud-download"></i></div>
                        <div class="menu-title">Respaldos BD</div>
                    </a>
                </li>

                <li data-url="notificaciones">
                    <a href="<?php echo BASE_URL . 'notificaciones'; ?>">
                        <div class="parent-icon"><i class="bx bx-bell"></i></div>
                        <div class="menu-title">Notificaciones</div>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <!--end navigation-->



<style>
/* ===== Sidebar header + logo ===== */
.sidebar-header {
    display: flex !important;
    align-items: center;
    padding: 8px 12px;
    background: #fff;
    border-bottom: 1px solid rgba(0,0,0,0.08);
    min-height: 56px;
    gap: 10px;
}
.sidebar-header .logo-wrap {
    flex: 1 1 auto !important;
    min-width: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
    border-radius: 10px;
    padding: 6px 10px;
    min-height: 42px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.sidebar-header .logo-wrap img {
    max-width: 100%;
    max-height: 38px;
    height: auto;
    object-fit: contain;
    filter: drop-shadow(0 1px 2px rgba(0,0,0,0.3));
}

/* Toggle button mejorado */
.sidebar-toggle-btn {
    width: 32px; height: 32px;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    color: #4b5563;
    cursor: pointer;
    margin-left: 10px;
    flex-shrink: 0;
    transition: background .15s, color .15s, border-color .15s, transform .15s;
    padding: 0;
}
.sidebar-toggle-btn i { font-size: 20px; line-height: 1; }
.sidebar-toggle-btn:hover {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
}
.sidebar-toggle-btn:active { transform: scale(.94); }

/* Cuando el sidebar esta cerrado, rotar el icono */
.wrapper.toggled .sidebar-toggle-btn i,
.toggle-icon.rotate-icon i { transform: rotate(180deg); }

/* ===== Sidebar colapsado: ocultar logo, centrar toggle ===== */
.wrapper.toggled .sidebar-header .logo-wrap {
    display: none !important;
}
.wrapper.toggled .sidebar-header {
    justify-content: center;
    padding: 12px 8px;
    gap: 0;
}
.wrapper.toggled .sidebar-toggle-btn {
    margin: 0 auto;
}

/* Quitar outlines de focus en el sidebar (las franjas azules feas) */
.sidebar-wrapper:focus,
.sidebar-wrapper:focus-visible,
.sidebar-wrapper *:focus:not(input):not(button):not(a):not(select):not(textarea),
.simplebar-content-wrapper:focus,
.simplebar-content-wrapper:focus-visible {
    outline: none !important;
    box-shadow: none !important;
}

/* ===== Menu items (tema claro) ===== */
#menu .menu-label {
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    color: #8a8f9c;
    padding: 12px 22px 4px;
    font-weight: 700;
    pointer-events: none;
    border-top: 1px solid rgba(0,0,0,0.06);
    margin-top: 6px;
}
#menu .menu-label:first-child { padding-top: 8px; border-top: 0; margin-top: 0; }

#menu > li > a {
    border-radius: 8px;
    margin: 1px 8px;
    padding: 9px 12px !important;
    transition: background .15s, color .15s;
    color: #4b5563;
}
#menu > li > a:hover { background-color: #f3f5fa; color: #2563eb; }
#menu > li > a:hover .parent-icon i { color: #2563eb; }
#menu > li.mm-active > a {
    background: linear-gradient(90deg, rgba(37,99,235,0.12), rgba(37,99,235,0.02));
    color: #2563eb;
    font-weight: 500;
}
#menu > li.mm-active > a .parent-icon i { color: #2563eb; }
#menu > li > a .parent-icon { font-size: 20px; color: #6b7280; }

#menu ul { padding: 2px 0 6px; }
#menu ul li a {
    padding: 7px 18px 7px 52px !important;
    font-size: 13px;
    color: #4b5563;
    border-radius: 6px;
    margin: 1px 12px 1px 20px;
}
#menu ul li a:hover { background: #f3f5fa; color: #2563eb; }
#menu ul li a i { margin-right: 10px; font-size: 15px; color: #9aa1b1; }
#menu ul li a:hover i { color: #2563eb; }
#menu ul li.mm-active > a {
    background: rgba(37,99,235,0.08);
    color: #2563eb;
    font-weight: 500;
}
#menu ul li.mm-active > a i { color: #2563eb; }

#menu > li { margin: 0; }

/* ===== Submenus: colapso por defecto, solo mostrar activo ===== */
#menu > li.has-sub > ul { display: none; }
#menu > li.has-sub.mm-active > ul,
#menu > li.has-sub > ul.mm-show { display: block; }
#menu > li.has-sub > ul.mm-collapsing { display: block; }

/* ===== Header de listados (titulo encima de DataTable) ===== */
.listado-header {
    display: flex;
    align-items: center;
    padding-bottom: .55rem;
    margin-bottom: .9rem;
    border-bottom: 1px solid #e5e7eb;
}
.listado-header h6 { color: #111827; font-size: 14px; letter-spacing: .2px; }
.listado-header .bx { font-size: 18px; vertical-align: -3px; }

/* ===== Topbar moderno ===== */
.topbar { padding: 0 1rem; }
.topbar .navbar { padding: 0; }
.topbar-greet-text {
    font-size: 14px;
    color: #4b5563;
    letter-spacing: .2px;
}
.topbar-greet-text strong { color: #111827; font-weight: 600; }

.topbar-iconbtn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px; height: 38px;
    border-radius: 10px;
    color: #4b5563;
    background: transparent;
    border: 1px solid transparent;
    text-decoration: none;
    transition: background .15s, color .15s, border-color .15s;
}
.topbar-iconbtn:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: #fecaca;
}
.topbar-iconbtn i { font-size: 20px; line-height: 1; }

/* Avatar con fallback a iniciales */
.avatar-circle {
    width: 38px; height: 38px;
    border-radius: 50%;
    overflow: hidden;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(79,70,229,.25);
}
.avatar-circle .avatar-img {
    width: 100%; height: 100%;
    object-fit: cover;
    display: block;
}
.avatar-circle .avatar-initials {
    align-items: center;
    justify-content: center;
    width: 100%; height: 100%;
    font-size: 15px;
    font-weight: 600;
    letter-spacing: .5px;
}

/* User info al lado del avatar */
.user-box .user-name {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    line-height: 1.2;
}
.user-box .designattion {
    font-size: 12px;
    color: #6b7280;
    line-height: 1.2;
}
.user-box .nav-link { padding: 4px 6px; border-radius: 10px; }
.user-box .nav-link:hover { background: #f3f4f6; }
.user-box .dropdown-menu { border: 0; min-width: 220px; padding: .35rem; }
.user-box .dropdown-item { border-radius: 8px; padding: .5rem .75rem; }
.user-box .dropdown-item:active { background: #2563eb; color: #fff; }
.user-box .dropdown-item:hover { background: #f3f4f6; }
.user-box .dropdown-item.text-danger:hover { background: #fee2e2; }

</style>
<script>
// Boton toggle del sidebar
document.addEventListener("click", function(e){
    const btn = e.target.closest(".sidebar-toggle-btn");
    if (btn) {
        document.querySelector(".wrapper")?.classList.toggle("toggled");
        btn.querySelector("i")?.classList.toggle("bx-chevron-left");
        btn.querySelector("i")?.classList.toggle("bx-chevron-right");
    }
});
</script>

<script>
// Persistir el scroll del sidebar entre navegaciones (sessionStorage).
// El sidebar esta oculto via .sb-restoring (CSS en <head>) hasta que terminemos
// de restaurar; asi el usuario no ve el "flicker" de scroll en 0 saltando.
(function(){
    var KEY = 'mhn_sidebar_scroll';

    function getScroller(){
        return document.querySelector('.sidebar-wrapper .simplebar-content-wrapper');
    }

    function saveScroll(){
        try {
            var s = getScroller() || document.querySelector('.sidebar-wrapper');
            if (s) sessionStorage.setItem(KEY, String(s.scrollTop));
        } catch(e) { /* noop */ }
    }

    function reveal(){ document.documentElement.classList.remove('sb-restoring'); }

    function tryRestore(){
        var s = getScroller();
        if (!s) return false;
        var t = sessionStorage.getItem(KEY);
        if (t !== null) s.scrollTop = parseInt(t, 10) || 0;
        reveal();
        return true;
    }

    // 1) Intento inmediato si SimpleBar ya inicializo
    if (!tryRestore()) {
        // 2) MutationObserver: detecta el momento exacto en que SimpleBar inyecta
        //    .simplebar-content-wrapper, y restaura sin pausa visible.
        var sw = document.querySelector('.sidebar-wrapper');
        if (sw) {
            var obs = new MutationObserver(function(){
                if (tryRestore()) obs.disconnect();
            });
            obs.observe(sw, { childList: true, subtree: true });
        }
        // 3) Failsafe: a los 600ms, revelar el sidebar de todos modos
        //    (evita dejarlo invisible si algo falla con SimpleBar).
        setTimeout(function(){
            tryRestore();
            reveal();
        }, 600);
    }

    // Guardar al salir de la pagina (pagehide es bfcache-friendly, beforeunload no).
    window.addEventListener('pagehide', saveScroll);

    // Tambien guardar inmediatamente al hacer click en cualquier link del menu
    // (defensivo: si pagehide no dispara, esto si captura).
    document.addEventListener('click', function(e){
        var a = e.target.closest('.sidebar-wrapper a[href]');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (href === '' || href === '#' || href.indexOf('javascript:') === 0) return;
        saveScroll();
    }, true);
})();

// Fade-out manual del contenido al hacer click en navegacion: elimina el
// flash blanco entre paginas. Funciona en TODOS los browsers (no depende
// de View Transitions). El sidebar/topbar quedan estables visualmente.
(function(){
    function isNavigationLink(a){
        if (!a) return false;
        var href = a.getAttribute('href') || '';
        if (!href || href === '#' || href.charAt(0) === '#') return false;
        if (href.indexOf('javascript:') === 0) return false;
        if (href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return false;
        if (a.target === '_blank') return false;
        if (a.hasAttribute('download')) return false;
        // Solo mismo origen
        try {
            var u = new URL(href, location.href);
            if (u.origin !== location.origin) return false;
            if (u.href === location.href) return false;
        } catch(e) { return false; }
        // Saltar links que abren modales/dropdowns BS5
        if (a.hasAttribute('data-bs-toggle')) return false;
        return true;
    }

    document.addEventListener('click', function(e){
        // Ignorar si el usuario aprieta Ctrl/Cmd/Shift (abre nueva pestana)
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) return;
        var a = e.target.closest('a[href]');
        if (!isNavigationLink(a)) return;
        document.body.classList.add('mhn-navigating');
    }, true);

    // Si el browser cancela la navegacion (volver atras dentro de la cache),
    // o pageshow desde bfcache, quitar la clase
    window.addEventListener('pageshow', function(){
        document.body.classList.remove('mhn-navigating');
    });
})();

// Prefetch on hover: cuando el usuario pasa el mouse sobre un link
// del mismo origen, precarga el HTML/recursos en background. Al hacer
// click, la pagina ya esta en cache => navegacion casi instantanea.
(function(){
    var support = (function(){
        try {
            var l = document.createElement('link');
            return l.relList && l.relList.supports && l.relList.supports('prefetch');
        } catch(e) { return false; }
    })();
    if (!support) return;
    if (navigator.connection && navigator.connection.saveData) return; // respetar Save-Data
    var done = new Set();
    var hoverTimer;
    var DELAY = 60; // ms hover antes de iniciar prefetch (evita prefetch accidentales)

    function prefetch(url){
        if (done.has(url)) return;
        done.add(url);
        var link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url;
        link.as = 'document';
        document.head.appendChild(link);
    }

    function onHover(e){
        var a = e.target.closest('a[href]');
        if (!a) return;
        var href = a.getAttribute('href') || '';
        if (!href || href === '#' || href.charAt(0) === '#' || href.indexOf('javascript:') === 0 || href.indexOf('mailto:') === 0 || href.indexOf('tel:') === 0) return;
        if (a.target === '_blank' || a.hasAttribute('download')) return;
        var u;
        try { u = new URL(href, location.href); } catch (err) { return; }
        if (u.origin !== location.origin) return;
        if (u.href === location.href) return;
        clearTimeout(hoverTimer);
        hoverTimer = setTimeout(function(){ prefetch(u.href); }, DELAY);
    }
    function cancelHover(){ clearTimeout(hoverTimer); }

    document.addEventListener('mouseover', onHover, { passive: true, capture: true });
    document.addEventListener('mouseout',  cancelHover, { passive: true, capture: true });
})();
</script>

<script>
(function(){
    // 1) Highlight active item segun URL
    const path = location.pathname.replace(/^\//,'').replace(/\/$/, '');
    const base = "<?php echo BASE_URL; ?>".replace(location.origin,'').replace(/^\//,'').replace(/\/$/, '');
    const rel = path.indexOf(base) === 0 ? path.substring(base.length).replace(/^\//,'') : path;
    const current = rel || 'admin';
    document.querySelectorAll('#menu li[data-url]').forEach(li => {
        const u = li.getAttribute('data-url');
        if (current === u || current.startsWith(u + '/')) {
            li.classList.add('mm-active');
            // expandir submenu padre si lo tiene
            let parent = li.closest('ul');
            while (parent && parent.id !== 'menu') {
                const parentLi = parent.closest('li.has-sub');
                if (parentLi) { parentLi.classList.add('mm-active'); parent.classList.add('mm-show'); }
                parent = parentLi ? parentLi.closest('ul') : null;
            }
        }
    });
})();
</script>

        </div>
        <!--end sidebar wrapper -->
        <!--start header -->
        <header>
            <div class="topbar d-flex align-items-center">
                <nav class="navbar navbar-expand w-100">
                    <div class="mobile-toggle-menu" role="button" aria-label="Abrir menu">
                        <i class='bx bx-menu'></i>
                    </div>

                    <div class="topbar-greet flex-grow-1 ps-2">
                        <?php
                        $h = (int)date('H');
                        $saludo = ($h < 12) ? 'Buenos dias' : (($h < 19) ? 'Buenas tardes' : 'Buenas noches');
                        $nombre_full = trim((string)($_SESSION['nombre_usuario'] ?? ''));
                        $primer_nombre = $nombre_full !== '' ? preg_split('/\s+/', $nombre_full)[0] : '';
                        ?>
                        <span class="topbar-greet-text">
                            <?php echo $saludo; ?><?php if ($primer_nombre !== '') { ?>, <strong><?php echo htmlspecialchars($primer_nombre, ENT_QUOTES, 'UTF-8'); ?></strong><?php } ?>
                        </span>
                    </div>

                    <?php if (isset($_SESSION['rol'])) {
                        $perfilFile = !empty($_SESSION['perfil_usuario']) ? $_SESSION['perfil_usuario'] : '';
                        $perfilUrl  = $perfilFile !== '' ? (BASE_URL . ltrim($perfilFile, '/')) : '';
                        $inicial    = mb_strtoupper(mb_substr($nombre_full !== '' ? $nombre_full : 'U', 0, 1, 'UTF-8'), 'UTF-8');
                    ?>
                    <a href="<?php echo BASE_URL . 'usuarios/salir'; ?>" class="topbar-iconbtn" title="Cerrar sesion" aria-label="Cerrar sesion">
                        <i class='bx bx-log-out-circle'></i>
                    </a>
                    <div class="user-box dropdown ms-2">
                        <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret px-1" href="#"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="avatar-circle">
                                <?php if ($perfilUrl !== '') { ?>
                                    <img src="<?php echo $perfilUrl; ?>" class="avatar-img" alt=""
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <span class="avatar-initials" style="display:none;"><?php echo htmlspecialchars($inicial, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } else { ?>
                                    <span class="avatar-initials" style="display:flex;"><?php echo htmlspecialchars($inicial, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php } ?>
                            </span>
                            <div class="user-info ps-2 d-none d-md-block">
                                <p class="user-name mb-0"><?php echo htmlspecialchars($nombre_full, ENT_QUOTES, 'UTF-8'); ?></p>
                                <p class="designattion mb-0"><?php echo htmlspecialchars($_SESSION['correo_usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li class="px-3 py-2 d-md-none">
                                <div class="fw-semibold small"><?php echo htmlspecialchars($nombre_full, ENT_QUOTES, 'UTF-8'); ?></div>
                                <div class="text-muted small"><?php echo htmlspecialchars($_SESSION['correo_usuario'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
                            </li>
                            <li class="d-md-none"><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="<?php echo BASE_URL . 'usuarios/profile'; ?>">
                                    <i class="bx bx-user me-2"></i>Mi perfil
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="<?php echo BASE_URL . 'usuarios/salir'; ?>">
                                    <i class='bx bx-log-out-circle me-2'></i>Cerrar sesion
                                </a>
                            </li>
                        </ul>
                    </div>
                    <?php } ?>
                </nav>
            </div>
        </header>
        <!--end header -->
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">