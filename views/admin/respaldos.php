<?php include_once "views/templates/header.php"; ?>

<div class="page-content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="mb-0"><i class="bx bx-cloud-download"></i> Respaldos de Base de Datos</h4>
            <div class="d-flex flex-wrap gap-2">
              <button id="btnGenerar" class="btn btn-primary"><i class="bx bx-refresh"></i> Generar respaldo ahora</button>
              <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalSubir"><i class="bx bx-upload"></i> Subir respaldo</button>
              <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalBorrarDatos"><i class="bx bx-eraser"></i> Borrar datos</button>
            </div>
          </div>
          <div class="alert alert-info">
            DB: <b><?= DBNAME ?></b> &middot; Se conservan los ultimos <?= defined("CANTIDADBD") ? CANTIDADBD : 10 ?> respaldos. Cada respaldo es un punto de restauracion.
          </div>
          <table class="table table-bordered table-hover align-middle" id="tblRespaldos" style="width:100%;">
            <thead>
              <tr>
                <th>#</th>
                <th>Archivo</th>
                <th>Fecha</th>
                <th>Tamano</th>
                <th style="min-width:340px;">Acciones</th>
              </tr>
            </thead>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalSubir" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Subir respaldo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formSubir" enctype="multipart/form-data">
        <div class="modal-body">
          <label class="form-label">Archivo <code>.sql</code> o <code>.sql.gz</code></label>
          <input type="file" name="archivo" accept=".sql,.gz,.sql.gz" class="form-control" required>
          <div class="form-check mt-2">
            <input type="checkbox" name="confirmar_cross_db" value="1" class="form-check-input" id="chkCrossDb">
            <label class="form-check-label" for="chkCrossDb">Confirmar si el dump no menciona esta DB</label>
          </div>
          <small class="text-muted">Max: 256 MB. Se guardara como <code><?= DBNAME ?>_uploaded_FECHA.ext</code></small>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Subir</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalEmail" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Enviar por correo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form id="formEmail">
        <input type="hidden" name="nombre" id="emailNombre">
        <div class="modal-body">
          <div class="mb-2"><small>Archivo: <code id="emailFile"></code></small></div>
          <label class="form-label">Destinatarios (separar con comas)</label>
          <input type="text" name="destinos" class="form-control" placeholder="a@ejemplo.com, b@ejemplo.com" required>
          <label class="form-label mt-2">Asunto</label>
          <input type="text" name="asunto" class="form-control" placeholder="(opcional)">
          <label class="form-label mt-2">Mensaje</label>
          <textarea name="mensaje" class="form-control" rows="3" placeholder="(opcional)"></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary">Enviar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
  const tbl = $("#tblRespaldos").DataTable({
    deferRender: true, pageLength: 25, order: [[2, "desc"]],
    ajax: { url: base_url + "admin/listarRespaldos", dataSrc: "" },
    columns: [
      { data: null, orderable: false, render: (d,t,r,m) => m.row + 1 },
      { data: "nombre" },
      { data: "fecha" },
      { data: "tamanio", render: d => d < 1024*1024 ? (d/1024).toFixed(1) + " KB" : (d/1024/1024).toFixed(2) + " MB" },
      { data: "nombre", orderable: false, render: d => {
        return '<a class="btn btn-success btn-sm" title="Descargar" href="' + base_url + 'admin/descargarRespaldo/' + encodeURIComponent(d) + '"><i class="bx bx-download"></i></a> ' +
          '<button class="btn btn-warning btn-sm" title="Restaurar" onclick="restaurar(\'' + d + '\')"><i class="bx bx-undo"></i> Restaurar</button> ' +
          '<button class="btn btn-info btn-sm text-white" title="Email" onclick="emailDialog(\'' + d + '\')"><i class="bx bx-envelope"></i></button> ' +
          '<button class="btn btn-danger btn-sm" title="Eliminar" onclick="eliminar(\'' + d + '\')"><i class="bx bx-trash"></i></button>';
      }}
    ],
    language: { url: base_url + "assets/js/espanol.json" }
  });

  document.getElementById("btnGenerar").addEventListener("click", function(){
    Swal.fire({ title: "Generando respaldo...", didOpen: () => Swal.showLoading(), allowOutsideClick: false });
    fetch(base_url + "admin/generarRespaldo").then(r => r.json()).then(res => {
      Swal.close();
      if (res.ok) { Swal.fire("Listo", res.nombre + " (" + res.tamanio + ")", "success"); tbl.ajax.reload(); }
      else Swal.fire("Error", res.error || "?", "error");
    });
  });

  document.getElementById("formSubir").addEventListener("submit", function(e){
    e.preventDefault();
    const fd = new FormData(this);
    Swal.fire({ title: "Subiendo...", didOpen: () => Swal.showLoading(), allowOutsideClick: false });
    fetch(base_url + "admin/subirRespaldo", { method: "POST", body: fd })
      .then(r => r.json()).then(res => {
        Swal.close();
        if (res.ok) {
          bootstrap.Modal.getInstance(document.getElementById("modalSubir")).hide();
          Swal.fire("Subido", res.nombre + " (" + Math.round(res.tamanio/1024) + " KB)", "success");
          tbl.ajax.reload();
          this.reset();
        } else Swal.fire("Error", res.error, "error");
      });
  });

  window.restaurar = function(nombre){
    Swal.fire({
      title: "Restaurar desde este punto?",
      html: 'Se reemplazara <b>toda</b> la base <b><?= DBNAME ?></b> con el contenido de:<br><code>' + nombre + '</code><br><br><b>Antes de restaurar se crea un respaldo automatico</b> como rollback.<br>Escribi <b>RESTAURAR</b> para confirmar:',
      input: "text", icon: "warning",
      showCancelButton: true, confirmButtonText: "Restaurar", confirmButtonColor: "#dc3545",
      preConfirm: v => v === "RESTAURAR" || Swal.showValidationMessage("Debes escribir RESTAURAR")
    }).then(r => {
      if (!r.isConfirmed) return;
      Swal.fire({ title: "Restaurando...", didOpen: () => Swal.showLoading(), allowOutsideClick: false });
      fetch(base_url + "admin/restaurarRespaldo/" + encodeURIComponent(nombre))
        .then(r => r.json()).then(res => {
          Swal.close();
          if (res.ok) Swal.fire("Restaurado", "Rollback disponible: " + res.backup_previo, "success").then(() => location.reload());
          else Swal.fire("Error", res.error, "error");
        });
    });
  };

  window.emailDialog = function(nombre){
    document.getElementById("emailNombre").value = nombre;
    document.getElementById("emailFile").textContent = nombre;
    new bootstrap.Modal(document.getElementById("modalEmail")).show();
  };

  document.getElementById("formEmail").addEventListener("submit", function(e){
    e.preventDefault();
    const fd = new FormData(this);
    Swal.fire({ title: "Enviando...", didOpen: () => Swal.showLoading(), allowOutsideClick: false });
    fetch(base_url + "admin/enviarRespaldoEmail", { method: "POST", body: fd })
      .then(r => r.json()).then(res => {
        Swal.close();
        if (res.ok) {
          bootstrap.Modal.getInstance(document.getElementById("modalEmail")).hide();
          Swal.fire("Enviado", "A " + res.destinos + " destinatario(s)", "success");
          this.reset();
        } else Swal.fire("Error", res.error, "error");
      });
  });

  window.eliminar = function(nombre){
    Swal.fire({ title: "Eliminar?", text: nombre, icon: "warning", showCancelButton: true, confirmButtonText: "Eliminar" })
      .then(r => {
        if (!r.isConfirmed) return;
        fetch(base_url + "admin/eliminarRespaldo/" + encodeURIComponent(nombre))
          .then(r => r.json()).then(res => {
            if (res.ok) { Swal.fire("Eliminado", "", "success"); tbl.ajax.reload(); }
            else Swal.fire("Error", res.error, "error");
          });
      });
  };
});
</script>

<!-- ============ MODAL: BORRAR DATOS ============ -->
<div class="modal fade" id="modalBorrarDatos" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow-lg">
      <div class="modal-header text-white" style="background: linear-gradient(135deg,#dc2626,#7f1d1d); border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;">
        <h5 class="modal-title"><i class="bx bx-error-circle me-1"></i>Borrar datos de la base</h5>
        <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="alert alert-warning border-warning">
          <strong><i class="bx bx-shield-x"></i> Operación destructiva.</strong> Antes de borrar, se generará automáticamente un <b>respaldo</b> que aparecerá en la lista para rollback.<br>
          <small class="text-muted">Las tablas <code>usuarios</code> (excepto el admin actual), <code>configuracion</code> y catálogos SRI <strong>no</strong> aparecen aquí — están protegidas.</small>
        </div>

        <div class="d-flex gap-2 mb-3">
          <button type="button" class="btn btn-outline-danger btn-sm" id="btnSeleccionarTodas"><i class="bx bx-check-square me-1"></i>Seleccionar todas</button>
          <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDeseleccionarTodas"><i class="bx bx-square me-1"></i>Deseleccionar</button>
        </div>

        <div id="listaTablas" class="row g-2 mb-3" style="max-height: 280px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px;">
          <div class="col-12 text-muted small">Cargando lista de tablas…</div>
        </div>

        <div class="mb-2">
          <label class="form-label fw-semibold">Para confirmar, escribe <code>BORRAR DATOS</code> abajo:</label>
          <input type="text" class="form-control" id="confirmBorrar" autocomplete="off" placeholder="Escribe BORRAR DATOS">
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" id="btnBorrarConfirmar" disabled><i class="bx bx-trash me-1"></i>Borrar datos</button>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var modalEl = document.getElementById('modalBorrarDatos');
  if (!modalEl) return;
  var listaCont = document.getElementById('listaTablas');
  var inputConfirm = document.getElementById('confirmBorrar');
  var btnConfirmar = document.getElementById('btnBorrarConfirmar');
  var btnSelAll = document.getElementById('btnSeleccionarTodas');
  var btnDeselAll = document.getElementById('btnDeseleccionarTodas');
  var loaded = false;

  modalEl.addEventListener('show.bs.modal', function(){
    inputConfirm.value = '';
    btnConfirmar.disabled = true;
    if (loaded) return;
    fetch(base_url + 'admin/tablasBorrables').then(r => r.json()).then(function(res){
      if (!res.ok) { listaCont.innerHTML = '<div class="col-12 text-danger small">' + (res.error || 'Error') + '</div>'; return; }
      var html = '';
      res.tablas.forEach(function(t){
        html += '<div class="col-md-4 col-sm-6">'
             +   '<label class="form-check small d-flex align-items-center gap-1">'
             +     '<input type="checkbox" class="form-check-input mhn-tabla-cb" value="' + t + '" checked>'
             +     '<code style="font-size:11.5px">' + t + '</code>'
             +   '</label>'
             + '</div>';
      });
      listaCont.innerHTML = html;
      loaded = true;
    }).catch(function(){ listaCont.innerHTML = '<div class="col-12 text-danger small">Error de red</div>'; });
  });

  btnSelAll.addEventListener('click', function(){
    listaCont.querySelectorAll('.mhn-tabla-cb').forEach(function(cb){ cb.checked = true; });
  });
  btnDeselAll.addEventListener('click', function(){
    listaCont.querySelectorAll('.mhn-tabla-cb').forEach(function(cb){ cb.checked = false; });
  });

  inputConfirm.addEventListener('input', function(){
    btnConfirmar.disabled = inputConfirm.value !== 'BORRAR DATOS';
  });

  btnConfirmar.addEventListener('click', function(){
    var seleccionadas = Array.from(listaCont.querySelectorAll('.mhn-tabla-cb:checked')).map(function(cb){ return cb.value; });
    if (seleccionadas.length === 0) {
      Swal.fire({ icon:'warning', title:'Selecciona al menos una tabla' });
      return;
    }
    Swal.fire({
      title: '¿Última confirmación?',
      html: 'Vas a borrar <b>' + seleccionadas.length + '</b> tabla(s).<br>Se creará un respaldo automático antes para rollback.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#dc2626',
      confirmButtonText: 'Sí, borrar',
      cancelButtonText: 'Cancelar'
    }).then(function(r){
      if (!r.isConfirmed) return;
      Swal.fire({ title: 'Generando respaldo y borrando datos…', didOpen: function(){ Swal.showLoading(); }, allowOutsideClick: false });
      fetch(base_url + 'admin/borrarDatos', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ confirm: 'BORRAR DATOS', tablas: seleccionadas })
      })
      .then(function(rs){ return rs.json(); })
      .then(function(res){
        Swal.close();
        if (res.ok) {
          var detalle = '<small class="text-muted">Respaldo previo (rollback): <code>' + (res.backup_previo||'-') + '</code></small><br><br>'
                     + '<b>Tablas vaciadas:</b><br>'
                     + '<small style="font-size:11px">' + (res.tablas_eliminadas||[]).join(', ') + '</small>';
          if ((res.errores||[]).length) detalle += '<br><br><span class="text-warning">Errores: ' + res.errores.join('; ') + '</span>';
          Swal.fire({ icon:'success', title:'Datos borrados', html: detalle, width: 600 }).then(function(){
            // Recargar la tabla de respaldos para que se vea el rollback
            if (window.location) window.location.reload();
          });
        } else {
          Swal.fire({ icon:'error', title:'Error', text: res.error || 'Falló el borrado' });
        }
      })
      .catch(function(){ Swal.fire({ icon:'error', title:'Error de red' }); });
    });
  });
})();
</script>

<?php include_once "views/templates/footer.php"; ?>
