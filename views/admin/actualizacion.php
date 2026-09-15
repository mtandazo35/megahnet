<?php include_once "views/templates/header.php"; ?>

<style>
  .act-header-icon {
    width: 44px; height: 44px;
    display: inline-flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: #fff; border-radius: 12px; font-size: 22px;
    box-shadow: 0 4px 10px rgba(37,99,235,.25);
  }
  .act-kpi { border: 1px solid #eef0f4; border-radius: 10px; background: #fafbfd; padding: .85rem 1rem; height: 100%; }
  .act-kpi .act-kpi-label { font-size: .75rem; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
  .act-kpi .act-kpi-value { font-size: 1.35rem; font-weight: 600; color: #1f2937; font-family: ui-monospace, Menlo, monospace; }
  .act-kpi .act-kpi-sub { font-size: .8rem; color: #6b7280; }
  #actLog {
    background: #0f172a; color: #e2e8f0; border-radius: 8px; padding: .85rem 1rem;
    font-size: .78rem; line-height: 1.45; max-height: 420px; overflow: auto;
    white-space: pre-wrap; word-break: break-word; margin: 0; min-height: 120px;
  }
  .act-dirty-list { font-family: ui-monospace, Menlo, monospace; font-size: .78rem; max-height: 140px; overflow: auto; }
</style>

<div class="page-content" id="actualizacionPanel">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-body">

          <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div class="d-flex align-items-center">
              <div class="act-header-icon"><i class="bx bx-refresh"></i></div>
              <div class="ms-3">
                <h4 class="mb-0 fw-semibold">Actualizacion del sistema</h4>
                <small class="text-muted">Compara la version instalada con la publicada en GitHub y aplica la actualizacion.</small>
              </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button id="actBtnComprobar" class="btn btn-light border"><i class="bx bx-search-alt"></i> Volver a comprobar</button>
              <button id="actBtnActualizar" class="btn btn-primary" disabled><i class="bx bx-cloud-download"></i> Actualizar ahora</button>
            </div>
          </div>

          <!-- Estado general (se pinta por JS) -->
          <div id="actEstado" class="alert alert-secondary mb-3">
            <i class="bx bx-loader bx-spin"></i> Comprobando version instalada y disponible...
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <div class="act-kpi">
                <div class="act-kpi-label"><i class="bx bx-hdd"></i> Version instalada</div>
                <div class="act-kpi-value" id="actLocalShort">&mdash;</div>
                <div class="act-kpi-sub" id="actLocalDate"></div>
                <div class="act-kpi-sub text-truncate" id="actLocalSubject" title=""></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="act-kpi">
                <div class="act-kpi-label"><i class="bx bxl-github"></i> Disponible en GitHub</div>
                <div class="act-kpi-value" id="actRemoteShort">&mdash;</div>
                <div class="act-kpi-sub text-truncate" id="actRemoteUrl" title=""></div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="act-kpi">
                <div class="act-kpi-label"><i class="bx bx-git-commit"></i> Cambios pendientes</div>
                <div class="act-kpi-value" id="actBehind">&mdash;</div>
                <div class="act-kpi-sub" id="actAhead"></div>
              </div>
            </div>
          </div>

          <div id="actDirtyBox" class="alert alert-warning mb-3" hidden>
            <strong><i class="bx bx-error"></i> Cambios locales sin subir.</strong>
            No se puede actualizar automaticamente mientras haya archivos modificados en el servidor:
            <div class="act-dirty-list mt-1" id="actDirtyList"></div>
          </div>

          <div class="alert alert-info mb-3">
            <i class="bx bx-shield-quarter"></i>
            Antes de aplicar cambios se genera automaticamente un <b>respaldo de la base de datos y del codigo</b>.
            La operacion tarda unos minutos; no cierre esta pantalla hasta ver el resultado.
          </div>

          <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
            <h6 class="mb-0 fw-semibold"><i class="bx bx-terminal"></i> Registro de la ultima actualizacion</h6>
            <span id="actStatusBadge" class="badge bg-secondary">sin datos</span>
          </div>
          <div id="actStatusDetalle" class="small text-muted mb-2"></div>
          <pre id="actLog">(sin registro)</pre>

        </div>
      </div>
    </div>
  </div>
</div>

<?php include_once "views/templates/footer.php"; ?>
