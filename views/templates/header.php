<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <link href="<?php echo BASE_URL; ?>assets/css/modern-theme.css" rel="stylesheet" />
    <link href="<?php echo BASE_URL; ?>assets/css/form-sections.css" rel="stylesheet" />
    <!-- Theme Style CSS -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/dark-theme.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/semi-dark.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/header-colors.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/DataTables/datatables.min.css" />
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/plugins/fullcalendar/css/main.min.css" />

    <title><?php echo TITLE . ' - ' . $data['title']; ?></title>

    <!-- Tab persist: oculta tab-content hasta que tab-persist.js active el tab correcto -->
    <script>
        if (window.location.hash) document.documentElement.classList.add('tab-pending');
    </script>
    <style>
        .tab-pending .tab-content, .tab-pending .nav-tabs { visibility: hidden; }
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

            <div class="sidebar-search px-3 py-2">
              <div class="position-relative">
                <input type="text" id="sidebarSearch" class="form-control form-control-sm rounded-pill ps-4"
                       placeholder="Buscar..." autocomplete="off">
                <i class="bx bx-search position-absolute" style="left:10px;top:50%;transform:translateY(-50%);color:#888"></i>
              </div>
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
    padding: 12px 14px;
    background: #fff;
    border-bottom: 1px solid rgba(0,0,0,0.08);
    min-height: 68px;
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
    padding: 8px 12px;
    min-height: 48px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}
.sidebar-header .logo-wrap img {
    max-width: 100%;
    max-height: 42px;
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

.sidebar-wrapper .sidebar-search { padding: 10px 16px !important; }
.sidebar-wrapper .sidebar-search input {
    background-color: #f3f5f9;
    border: 1px solid #e2e6ee;
    color: #333;
    font-size: 13px;
    height: 34px;
}
.sidebar-wrapper .sidebar-search input:focus {
    background-color: #fff;
    border-color: #7dc4ff;
    box-shadow: 0 0 0 3px rgba(125,196,255,.15);
    color: #222;
}
.sidebar-wrapper .sidebar-search input::placeholder { color: #9aa1b1; }
#menu li.hidden-by-search { display: none !important; }

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

    // 2) Busqueda rapida
    const input = document.getElementById('sidebarSearch');
    if (input) {
        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            document.querySelectorAll('#menu > li').forEach(li => {
                if (li.classList.contains('menu-label')) { li.classList.toggle('hidden-by-search', !!q); return; }
                const text = li.innerText.toLowerCase();
                const match = !q || text.indexOf(q) !== -1;
                li.classList.toggle('hidden-by-search', !match);
                // expandir submenu si match
                if (match && q) {
                    const sub = li.querySelector('ul');
                    if (sub) { sub.classList.add('mm-show'); li.classList.add('mm-active'); }
                }
            });
        });
    }
})();
</script>

        </div>
        <!--end sidebar wrapper -->
        <!--start header -->
        <header>
            <div class="topbar d-flex align-items-center">
                <nav class="navbar navbar-expand">
                    <div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
                    </div>
                    <div class="search-bar flex-grow-1">
                        <div class="position-relative">
                            <h6><?php echo TITLE; ?></h6>
                        </div>
                    </div>
                    <?php if (isset($_SESSION['rol'])) { ?>
                    <div class="user-box dropdown">
                        <a class="d-flex align-items-center nav-link dropdown-toggle dropdown-toggle-nocaret" href="#"
                            role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php if ($_SESSION['perfil_usuario'] == null) {
                                    $perfil = BASE_URL . 'assets/images/logo.png';
                                } else {
                                    $perfil = BASE_URL . $_SESSION['perfil_usuario'];
                                } ?>
                            <img src="<?php echo $perfil; ?>" class="user-img" alt="user avatar">
                            <div class="user-info ps-3">
                                <p class="user-name mb-0"><?php echo $_SESSION['nombre_usuario']; ?></p>
                                <p class="designattion mb-0"><?php echo $_SESSION['correo_usuario']; ?></p>
                            </div>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo BASE_URL . 'usuarios/profile'; ?>"><i
                                        class="bx bx-user"></i><span>Profile</span></a>
                            </li>
                            <li>
                                <div class="dropdown-divider mb-0"></div>
                            </li>
                            <li><a class="dropdown-item" href="<?php echo BASE_URL . 'usuarios/salir'; ?>"><i
                                        class='bx bx-log-out-circle'></i><span>Logout</span></a>
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