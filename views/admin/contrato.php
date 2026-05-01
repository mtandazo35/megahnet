<?php include_once "views/templates/header.php"; ?>

<div class="page-content">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <h4 class="mb-0"><i class="bx bx-file-blank"></i> Modelo de Contrato</h4>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <h6 class="text-muted mb-2">Plantilla actual</h6>
                <?php if (!empty($data['plantillaExiste'])): ?>
                  <p class="mb-1"><strong>Archivo:</strong> assets/docs/CONTRATO.docx</p>
                  <p class="mb-1"><strong>Tamano:</strong> <?= number_format($data['plantillaTam'] / 1024, 1) ?> KB</p>
                  <p class="mb-3"><strong>Modificada:</strong> <?= htmlspecialchars($data['plantillaMtime']) ?></p>
                  <a class="btn btn-info btn-sm" href="<?= BASE_URL ?>admin/descargarContrato">
                    <i class="bx bx-download"></i> Descargar plantilla
                  </a>
                <?php else: ?>
                  <p class="text-danger">No existe la plantilla. Subi una para que el sistema pueda generar contratos.</p>
                <?php endif; ?>
              </div>
            </div>

            <div class="col-md-6">
              <div class="border rounded p-3 h-100">
                <h6 class="text-muted mb-2">Subir nueva plantilla</h6>
                <form id="formPlantilla" enctype="multipart/form-data">
                  <div class="mb-2">
                    <input type="file" name="plantilla" id="plantilla" class="form-control" accept=".docx" required>
                    <small class="text-muted">Solo .docx, maximo 5 MB. La anterior se respalda automaticamente en assets/docs/backups/.</small>
                  </div>
                  <button type="submit" class="btn btn-success btn-sm"><i class="bx bx-upload"></i> Subir y reemplazar</button>
                </form>
                <div id="resultUpload" class="mt-2 small"></div>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <h5 class="mb-3"><i class="bx bx-show"></i> Simulador de contrato</h5>
          <p class="text-muted small">Selecciona un contrato real para ver como se veria con la plantilla actual rellenada.</p>
          <div class="row g-2 align-items-end mb-3">
            <div class="col-md-8">
              <label class="form-label small">Contrato</label>
              <select id="selContrato" class="form-select form-select-sm">
                <option value="">-- Cargando contratos --</option>
              </select>
            </div>
            <div class="col-md-4">
              <button id="btnPreview" type="button" class="btn btn-primary btn-sm w-100" disabled>
                <i class="bx bx-show"></i> Generar vista previa PDF
              </button>
            </div>
          </div>
          <div id="previewWrap" class="border rounded" style="display:none;">
            <iframe id="previewIframe" style="width:100%; height:780px; border:0;"></iframe>
          </div>
          <div id="previewError" class="small mt-2"></div>

          <hr class="my-4">

          <h5 class="mb-3"><i class="bx bx-code-curly"></i> Variables disponibles para la plantilla</h5>
          <p class="text-muted small">Insertalas en el .docx con la sintaxis <code>${nombre}</code>. Click sobre una para copiarla.</p>

          <?php foreach ($data['variables'] as $grupo => $vars): ?>
            <div class="mb-3">
              <h6 class="text-primary"><?= htmlspecialchars($grupo) ?></h6>
              <div class="table-responsive">
                <table class="table table-sm table-striped mb-0">
                  <thead class="table-light"><tr><th style="width:30%">Variable</th><th>Descripcion</th></tr></thead>
                  <tbody>
                    <?php foreach ($vars as $nombre => $desc): ?>
                      <tr>
                        <td><code class="copy-var" style="cursor:pointer" data-var="${<?= htmlspecialchars($nombre) ?>}">${<?= htmlspecialchars($nombre) ?>}</code></td>
                        <td><?= htmlspecialchars($desc) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; ?>

        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Cargar contratos recientes
fetch('<?= BASE_URL ?>admin/contratosRecientes')
  .then(function(r){ return r.json(); })
  .then(function(arr){
    const sel = document.getElementById('selContrato');
    sel.innerHTML = '<option value="">-- Selecciona un contrato --</option>';
    (arr || []).forEach(function(c){
      const op = document.createElement('option');
      op.value = c.id;
      op.textContent = '#' + c.id + ' - ' + c.cliente + '  (' + c.fecha + ')';
      sel.appendChild(op);
    });
  })
  .catch(function(){
    document.getElementById('selContrato').innerHTML = '<option value="">No se pudo cargar</option>';
  });
document.getElementById('selContrato').addEventListener('change', function(){
  document.getElementById('btnPreview').disabled = !this.value;
});
document.getElementById('btnPreview').addEventListener('click', function(){
  const id = document.getElementById('selContrato').value;
  if (!id) return;
  const wrap = document.getElementById('previewWrap');
  const iframe = document.getElementById('previewIframe');
  const err = document.getElementById('previewError');
  err.innerHTML = '<span class="text-muted">Generando PDF...</span>';
  wrap.style.display = 'block';
  iframe.src = '<?= BASE_URL ?>admin/previewContrato/' + id + '?t=' + Date.now();
  iframe.onload = function(){
    err.innerHTML = '<span class="text-success small"><i class="bx bx-check"></i> Vista previa generada</span>';
  };
});

document.querySelectorAll('.copy-var').forEach(function(el){
  el.addEventListener('click', function(){
    const txt = el.dataset.var;
    navigator.clipboard.writeText(txt).then(function(){
      const orig = el.textContent;
      el.textContent = 'Copiado!';
      setTimeout(function(){ el.textContent = orig; }, 900);
    });
  });
});
document.getElementById('formPlantilla').addEventListener('submit', function(e){
  e.preventDefault();
  const form = e.target;
  const fd = new FormData(form);
  const out = document.getElementById('resultUpload');
  out.innerHTML = '<span class="text-muted">Subiendo...</span>';
  fetch('<?= BASE_URL ?>admin/subirContrato', { method: 'POST', body: fd })
    .then(function(r){ return r.json(); })
    .then(function(j){
      if (j.ok) {
        out.innerHTML = '<span class="text-success"><i class="bx bx-check"></i> ' + j.msg + ' (' + (j.tam/1024).toFixed(1) + ' KB, ' + j.mtime + ')</span>';
        setTimeout(function(){ window.location.reload(); }, 1500);
      } else {
        out.innerHTML = '<span class="text-danger"><i class="bx bx-x"></i> ' + (j.msg || 'Error') + '</span>';
      }
    })
    .catch(function(err){
      out.innerHTML = '<span class="text-danger">Error: ' + err.message + '</span>';
    });
});
</script>

<?php include_once "views/templates/footer.php"; ?>
