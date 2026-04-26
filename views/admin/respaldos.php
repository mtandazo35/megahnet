<?php include_once "views/templates/header.php"; ?>

<div class="page-content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0"><i class="bx bx-cloud-download"></i> Respaldos de Base de Datos</h4>
            <div>
              <button id="btnGenerar" class="btn btn-primary"><i class="bx bx-refresh"></i> Generar respaldo ahora</button>
              <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalSubir"><i class="bx bx-upload"></i> Subir respaldo</button>
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

<?php include_once "views/templates/footer.php"; ?>
