<?php include_once 'views/templates/header.php'; ?>

<div class="page-header d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2" id="page-header-modern">
    <div>
        <h4 class="mb-0 fw-semibold"><i class="bx bx-server text-primary me-1"></i>Mikrotiks</h4>
        <small class="text-muted">Routers MikroTik registrados en el sistema</small>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMikrotik" id="btnAbrirNuevoMikrotik">
            <i class="bx bx-plus me-1"></i>Nuevo Mikrotik
        </button>
        <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalInactivos">
            <i class="bx bx-trash me-1"></i>Inactivos
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <?php $tituloListado='Listado de Mikrotiks'; $iconoListado='bx-server'; include 'views/templates/listado_titulo.php'; ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle nowrap" id="tblMikrotiks" style="width: 100%;">
                <thead class="table-light">
                    <tr>
                        <th>Acciones</th>
                        <th>Estado</th>
                        <th>Nombre</th>
                        <th>IP Pública</th>
                        <th>Usuario</th>
                        <th>Clave</th>
                        <th>Puerto</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- ============ MODAL: NUEVO / EDITAR MIKROTIK ============ -->
<div class="modal fade" id="modalMikrotik" tabindex="-1" aria-labelledby="modalMikrotikLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold" id="modalMikrotikLabel"><i class="bx bx-server text-primary me-1"></i>Datos del Mikrotik</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formulario" autocomplete="off">
                <div class="modal-body">
                    <input type="hidden" id="id" name="id">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="nombre">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-purchase-tag-alt"></i></span>
                                <input class="form-control" type="text" name="nombre" id="nombre" placeholder="Nombre del Mikrotik">
                            </div>
                            <span id="errorNombre" class="text-danger small"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small mb-1" for="ip">IP Pública <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-globe"></i></span>
                                <input class="form-control" type="text" name="ip" id="ip" placeholder="ej. 200.1.1.10">
                            </div>
                            <span id="errorIp" class="text-danger small"></span>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="usuario">Usuario</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-user"></i></span>
                                <input class="form-control" type="text" name="usuario" id="usuario" placeholder="Usuario admin">
                            </div>
                            <span id="errorUsuario" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="clave">Clave</label>
                            <div class="input-group" id="show_hide_clave">
                                <span class="input-group-text"><i class="bx bx-lock"></i></span>
                                <input class="form-control" type="password" name="clave" id="clave" placeholder="Contraseña" autocomplete="new-password">
                                <a href="javascript:;" class="input-group-text" aria-label="Mostrar/ocultar contraseña" style="cursor:pointer;"><i class="bx bx-hide"></i></a>
                            </div>
                            <span id="errorClave" class="text-danger small"></span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small mb-1" for="puerto">Puerto API</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bx bx-broadcast"></i></span>
                                <input class="form-control" type="number" name="puerto" id="puerto" placeholder="8728">
                            </div>
                            <span id="errorPuerto" class="text-danger small"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer flex-wrap gap-2 justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-info" id="btnProbarMikrotik">
                            <i class="bx bx-plug me-1"></i>Probar conexión
                        </button>
                        <span id="resultadoProbar" class="ms-2 small"></span>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light" id="btnNuevo">
                            <i class="bx bx-eraser me-1"></i>Limpiar
                        </button>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary" id="btnAccion">
                            <i class="bx bx-save me-1"></i>Registrar
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============ MODAL: MIKROTIKS INACTIVOS ============ -->
<div class="modal fade" id="modalInactivos" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold"><i class="bx bx-trash text-danger me-1"></i>Mikrotiks inactivos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle nowrap" id="tblMikrotiksInactivos" style="width: 100%;">
                        <thead class="table-light"><tr>
                            <th>Nombre</th><th>IP Pública</th><th>Usuario</th><th>Clave</th><th>Puerto</th><th>Acciones</th>
                        </tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modalEl = document.getElementById('modalInactivos');
    if (!modalEl) return;
    var dtInactivos, initialized = false;
    modalEl.addEventListener('show.bs.modal', function () {
        if (initialized) { if (dtInactivos) dtInactivos.ajax.reload(null, false); return; }
        initialized = true;
        dtInactivos = $('#tblMikrotiksInactivos').DataTable({
            deferRender: true, pageLength: 10,
            ajax: { url: base_url + 'mikrotiks/listarInactivos', dataSrc: '' },
            columns: [
                { data: 'nombre' }, { data: 'ip' }, { data: 'usuario' },
                { data: 'clave' }, { data: 'puerto' }, { data: 'acciones' }
            ],
            language: { url: base_url + 'assets/js/espanol.json' },
            responsive: true, order: [[0, 'asc']]
        });
    });
    window.restaurarMikrotik = function (id) {
        if (!dtInactivos) return;
        restaurarRegistros(base_url + 'mikrotiks/restaurar/' + id, dtInactivos);
    };
    // Eliminacion PERMANENTE: solo si no tiene contratos (validado en backend).
    window.eliminarMikrotikPermanente = function (id) {
        Swal.fire({
            title: '¿Eliminar permanentemente?',
            html: '<div class="text-muted small">Esta accion <b>NO</b> se puede deshacer. El registro se borrara de la base de datos.</div>',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, eliminar', cancelButtonText: 'Cancelar'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            fetch(base_url + 'mikrotiks/eliminarPermanente/' + id, {
                method: 'GET', credentials: 'same-origin'
            })
            .then(rs => rs.json())
            .then(function (res) {
                Swal.fire({
                    toast: true, position: 'top-right',
                    icon: res.type, title: res.msg,
                    showConfirmButton: false, timer: 3000
                });
                if (res.type === 'success' && dtInactivos) {
                    dtInactivos.ajax.reload(null, false);
                }
            })
            .catch(function () {
                Swal.fire({ icon: 'error', title: 'Error de red al eliminar' });
            });
        });
    };
    document.addEventListener('mhn:restauradoOk', function () {
        if (typeof tblMikrotiks !== 'undefined' && tblMikrotiks) {
            tblMikrotiks.ajax.reload(null, false);
        }
    });
})();
</script>

<script>
// Cerrar modal tras registro exitoso (evento mhn:registroOk emitido por insertarRegistros).
document.addEventListener('DOMContentLoaded', function () {
    var modalMikrotik = document.getElementById('modalMikrotik');
    var formulario    = document.getElementById('formulario');
    var btnAccion     = document.getElementById('btnAccion');

    if (formulario && modalMikrotik) {
        formulario.addEventListener('mhn:registroOk', function () {
            bootstrap.Modal.getOrCreateInstance(modalMikrotik).hide();
        });
    }
    var btnAbrir = document.getElementById('btnAbrirNuevoMikrotik');
    if (btnAbrir && formulario && btnAccion) {
        btnAbrir.addEventListener('click', function () {
            formulario.reset();
            document.getElementById('id').value = '';
            btnAccion.textContent = 'Registrar';
            ['errorNombre','errorIp','errorUsuario','errorClave','errorPuerto']
                .forEach(function(id){ var el = document.getElementById(id); if (el) el.textContent = ''; });
        });
    }

    // Show/hide para el campo Clave del Mikrotik
    var trigger = document.querySelector('#show_hide_clave a');
    if (trigger) {
        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            var input = document.querySelector('#show_hide_clave input');
            var icon  = document.querySelector('#show_hide_clave a i');
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
    }
});
window.abrirModalMikrotik = function () {
    var el = document.getElementById('modalMikrotik');
    if (el) bootstrap.Modal.getOrCreateInstance(el).show();
};

// Boton "Probar conexion": valida contra el MikroTik real sin guardar.
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('btnProbarMikrotik');
    var out = document.getElementById('resultadoProbar');
    if (!btn) return;

    btn.addEventListener('click', function () {
        var ip      = (document.getElementById('ip')      || {}).value || '';
        var usuario = (document.getElementById('usuario') || {}).value || '';
        var clave   = (document.getElementById('clave')   || {}).value || '';
        var puerto  = (document.getElementById('puerto')  || {}).value || '';
        var idH     = (document.getElementById('id')      || {}).value || '';

        if (!ip.trim() || !usuario.trim()) {
            if (out) {
                out.className = 'ms-2 small text-warning';
                out.textContent = 'Ingresa IP y Usuario primero';
            }
            return;
        }

        // Estado: probando
        var origHTML = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>Probando…';
        if (out) {
            out.className = 'ms-2 small text-muted';
            out.textContent = 'Conectando al MikroTik…';
        }

        var fd = new FormData();
        fd.append('ip', ip);
        fd.append('usuario', usuario);
        fd.append('clave', clave);
        fd.append('puerto', puerto);
        if (idH) fd.append('id', idH);

        fetch(base_url + 'mikrotiks/probarConexion', {
            method: 'POST',
            credentials: 'same-origin',
            body: fd
        })
        .then(function (r) { return r.json(); })
        .then(function (res) {
            btn.disabled = false;
            btn.innerHTML = origHTML;
            if (out) {
                if (res.type === 'success') {
                    out.className = 'ms-2 small text-success fw-semibold';
                    out.innerHTML = '<i class="bx bx-check-circle"></i> ' + (res.msg || 'OK');
                } else if (res.type === 'warning') {
                    out.className = 'ms-2 small text-warning';
                    out.innerHTML = '<i class="bx bx-error-circle"></i> ' + (res.msg || 'Aviso');
                } else {
                    out.className = 'ms-2 small text-danger';
                    out.innerHTML = '<i class="bx bx-x-circle"></i> ' + (res.msg || 'Error');
                }
            }
        })
        .catch(function () {
            btn.disabled = false;
            btn.innerHTML = origHTML;
            if (out) {
                out.className = 'ms-2 small text-danger';
                out.textContent = 'Error de red al probar la conexión';
            }
        });
    });

    // Limpiar el resultado cuando se cierra/abre el modal
    var modal = document.getElementById('modalMikrotik');
    if (modal && out) {
        modal.addEventListener('hidden.bs.modal', function () {
            out.textContent = '';
            out.className = 'ms-2 small';
        });
    }
});
</script>

<?php include_once 'views/templates/footer.php'; ?>
