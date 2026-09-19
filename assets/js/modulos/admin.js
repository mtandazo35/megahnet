
let divLoading = document.querySelector("#divLoading");

const formulario = document.querySelector('#formulario')
const btnAccion = document.querySelector('#btnAccion')




const ruc = document.querySelector('#ruc')
const nombre = document.querySelector('#nombre')
const telefono = document.querySelector('#telefono')
const correo = document.querySelector('#correo')
const direccion = document.querySelector('#direccion')
const impuesto = document.querySelector('#impuesto')

const razon = document.querySelector('#razon')
const items = document.querySelector('#totalitems')
const establecimiento = document.querySelector('#establecimiento')
const emision = document.querySelector('#emision')
const contabilidad = document.querySelector('#contabilidad')
const firmainicio = document.querySelector('#firmainicio')
const firmafinal = document.querySelector('#firmafinal')
const cantidaddocumento = document.querySelector('#cantidaddocumento')
const electronica = document.querySelector('#chelectronica')

// elementos para mostar errre
const errorRuc = document.querySelector('#errorRuc')
const errorNombre = document.querySelector('#errorNombre')
const errorCorreo = document.querySelector('#errorCorreo')
const errorTelefono = document.querySelector('#errorTelefono')
const errorDireccion = document.querySelector('#errorDireccion')
const errorImpuesto = document.querySelector('#errorImpuesto')

const errorRazon = document.querySelector('#errorRazon')
const errorItems = document.querySelector('#erroritems')
const errorEstablecimiento = document.querySelector('#errorEstablecimiento')
const errorEmision = document.querySelector('#errorEmision')
const errorContabilidad = document.querySelector('#errorContabilidad')
const errorFirmainicio = document.querySelector('#errorFirmainicio')
const errorFirmafinal = document.querySelector('#errorFirmafinal')
const errorCantidaddocumento = document.querySelector('#errorCantidaddocumento')

const foto = document.querySelector('#foto')
const containerPreview = document.querySelector('#containerPreview')
const foto_remove = document.querySelector('#foto_remove')

document.addEventListener('DOMContentLoaded', function () {

  // inicializar un editor (solo si existe textarea visible)
  const mensajeEl = document.querySelector('#mensaje')
  if (mensajeEl && mensajeEl.tagName === 'TEXTAREA') {
    ClassicEditor
      .create(mensajeEl)
      .catch(error => {
        console.error(error)
      })
  }

  // ============ Uploader: Logo del Sistema ============
  const logoUploaderSistema = document.querySelector('#logoUploaderSistema')
  const MAX_LOGO_SISTEMA = 5 * 1024 * 1024  // 5 MB
  if (foto) foto.addEventListener('change', function (e) {
    const f = e.target.files[0]
    if (!f) return
    if (!f.type || !f.type.startsWith('image/')) {
      foto.value = ''
      alertaPersonalizada('warning', 'EL ARCHIVO NO ES UNA IMAGEN VALIDA')
      return
    }
    if (f.size > MAX_LOGO_SISTEMA) {
      foto.value = ''
      alertaPersonalizada('warning', 'EL LOGO DEL SISTEMA NO DEBE PESAR MÁS DE 5 MB')
      return
    }
    const tmpUrl = URL.createObjectURL(f)
    foto_remove.value = 'Logo.jpg'
    containerPreview.innerHTML = `<img class="logo-preview-img" src="${tmpUrl}" alt="Logo Sistema">`
    if (logoUploaderSistema) logoUploaderSistema.classList.add('has-image')
  })

  // ============ Uploader: Logo de Facturación ============
  const fotoFactura = document.querySelector('#foto_factura')
  const fotoFacturaRemove = document.querySelector('#foto_factura_remove')
  const containerPreviewFactura = document.querySelector('#containerPreviewFactura')
  const logoUploaderFactura = document.querySelector('#logoUploaderFactura')
  const MAX_LOGO_FACTURA = 5 * 1024 * 1024  // 5 MB
  if (fotoFactura) {
    fotoFactura.addEventListener('change', function (e) {
      const f = e.target.files[0]
      if (!f) return
      if (!f.type || !f.type.startsWith('image/')) {
        fotoFactura.value = ''
        alertaPersonalizada('warning', 'EL ARCHIVO NO ES UNA IMAGEN VALIDA')
        return
      }
      if (f.size > MAX_LOGO_FACTURA) {
        fotoFactura.value = ''
        alertaPersonalizada('warning', 'EL LOGO DE FACTURACIÓN NO DEBE PESAR MÁS DE 5 MB')
        return
      }
      const tmpUrl = URL.createObjectURL(f)
      if (fotoFacturaRemove) fotoFacturaRemove.value = '0'
      if (containerPreviewFactura) containerPreviewFactura.innerHTML = `<img class="logo-preview-img" src="${tmpUrl}" alt="Logo Factura">`
      if (logoUploaderFactura) logoUploaderFactura.classList.add('has-image')
    })
  }
  // Actualizar Datos
  if (formulario) formulario.addEventListener('submit', function (e) {
    e.preventDefault()

    errorRuc.textContent = ''
    errorNombre.textContent = ''
    errorCorreo.textContent = ''
    errorTelefono.textContent = ''
    errorDireccion.textContent = ''
    errorImpuesto.textContent = ''

    if (ruc.value == '') {
      errorRuc.textContent = 'EL RUC ES REQUERIDO'
    } else if (nombre.value == '') {
      errorNombre.textContent = 'EL NOMBRE EMPRESARIAL ES REQUERIDO'
    } else if (razon.value == '') {
      errorRazon.textContent = 'LA RAZON SOCIAL ES REQUERIDO'
    }
    else if (telefono.value == '') {
      errorTelefono.textContent = 'EL TELEFONO ES REQUERIDO'
    }
    else if (correo.value == '') {
      errorCorreo.textContent = 'EL CORREO ES REQUERIDO'
    }
    else if (direccion.value == '') {
      errorDireccion.textContent = 'LA DIRECCION ES REQUERIDO'
    }
    else if (impuesto.value == '') {
      errorImpuesto.textContent = 'EL IMPUESTO ES REQUERIDO'
    }
    else if (items && items.value == '') {
      if (errorItems) errorItems.textContent = 'EL TOTAL DE ITEMS ES REQUERIDO'
    } else if (establecimiento && establecimiento.value == '') {
      if (errorEstablecimiento) errorEstablecimiento.textContent = 'EL ESTABLECIMIENTO ES REQUERIDO'
    } else if (emision && emision.value == '') {
      if (errorEmision) errorEmision.textContent = 'EL PUNTO DE EMISION ES REQUERIDO'
    } else if (contabilidad && contabilidad.value == '') {
      if (errorContabilidad) errorContabilidad.textContent = 'LA CONTABILIDAD ES REQUERIDO'
    } else {
      // Campos de firma electronica (firmainicio/firmafinal/cantidaddocumento) son
      // opcionales: solo se exigen si el usuario empezo a llenar la firma.
      const url = base_url + 'admin/modificar'
      insertarRegistros(url, this, null, btnAccion, false)
    }
  })

  


})



  // Helper: pinta el bloque de estado de la firma con datos del endpoint
  function pintarEstadoFirma(d) {
    const badge = document.querySelector('#firmaEstadoBadge')
    const det   = document.querySelector('#firmaEstadoDetalle')
    const box   = document.querySelector('#firmaEstadoBox')
    if (!badge || !det || !box) return
    if (!d || !d.ok) {
      badge.className = 'badge bg-danger'
      badge.textContent = 'CLAVE INCORRECTA O FIRMA INVALIDA'
      det.innerHTML = `<span class="text-danger">${(d && d.msg) ? d.msg : 'No se pudo verificar la firma'}</span>`
      box.classList.remove('border-success'); box.classList.add('border-danger')
      return
    }
    let cls = 'bg-success'
    if (d.estado === 'POR VENCER') cls = 'bg-warning text-dark'
    else if (d.estado === 'VENCIDA') cls = 'bg-danger'
    badge.className = 'badge ' + cls
    badge.textContent = d.estado
    det.innerHTML = `
      <div><b>Titular:</b> ${d.titular}</div>
      <div><b>Identificacion:</b> ${d.identificacion}</div>
      <div><b>Vence:</b> ${d.validTo} <span class="text-muted">(${d.diasRestantes} dias restantes)</span></div>
    `
    box.classList.remove('border-danger'); box.classList.add('border-success')
  }

  // Helper: llama al endpoint y pinta
  function refrescarEstadoFirma(passOpcional) {
    if (!document.querySelector('#firmaEstadoBox')) return
    const fd = new FormData()
    if (passOpcional) fd.append('firma_password', passOpcional)
    return fetch(base_url + 'admin/verificarFirma', { method: 'POST', body: fd, credentials: 'same-origin' })
      .then(r => r.json())
      .then(pintarEstadoFirma)
      .catch(() => pintarEstadoFirma({ ok: false, msg: 'Error de conexion' }))
  }

  // Cargar al inicio
  refrescarEstadoFirma()

  // Refrescar despues de submit exitoso (tras 1.2s para dejar que el endpoint termine de guardar)
  if (formulario) {
    const _origSubmit = formulario.onsubmit
    formulario.addEventListener('submit', function () {
      setTimeout(() => refrescarEstadoFirma(), 1200)
    }, true)
  }

  // Verificar firma electronica
  const btnVerificarFirma = document.querySelector('#btnVerificarFirma')
  if (btnVerificarFirma) {
    btnVerificarFirma.addEventListener('click', function () {
      const fd = new FormData()
      const passInput = document.querySelector('#firma_password')
      if (passInput && passInput.value) {
        fd.append('firma_password', passInput.value)
      }
      // Si seleccionaste un .p12 nuevo, lo mandamos para validarlo sin guardar.
      const fileInput = document.querySelector('#firma_p12')
      if (fileInput && fileInput.files && fileInput.files[0]) {
        fd.append('firma_p12', fileInput.files[0])
      }
      btnVerificarFirma.disabled = true
      btnVerificarFirma.innerHTML = '<i class="bx bx-loader bx-spin"></i> Verificando...'
      fetch(base_url + 'admin/verificarFirma', { method: 'POST', body: fd, credentials: 'same-origin' })
        .then(r => r.json())
        .then(d => {
          btnVerificarFirma.disabled = false
          btnVerificarFirma.innerHTML = '<i class="bx bx-check-shield"></i> Verificar'
          if (!d.ok) {
            Swal.fire({ icon: 'error', title: 'Firma no valida', text: d.msg || 'Error al verificar la firma' })
            return
          }
          const colorEstado = d.estado === 'VIGENTE' ? '#28a745' : (d.estado === 'POR VENCER' ? '#ffc107' : '#dc3545')
          const fuenteClave = d.claveDesdeBD ? 'guardada en BD' : 'la que ingresaste (no guardada aun)'
          Swal.fire({
            icon: d.estado === 'VENCIDA' ? 'error' : (d.estado === 'POR VENCER' ? 'warning' : 'success'),
            title: 'Firma electronica',
            html: `
              <div style="text-align:left;line-height:1.7;">
                <div><b>Estado:</b> <span style="color:${colorEstado};font-weight:600;">${d.estado}</span></div>
                <div><b>Titular:</b> ${d.titular}</div>
                <div><b>Identificacion:</b> ${d.identificacion}</div>
                <div><b>Organizacion:</b> ${d.organizacion}</div>
                <div><b>Vigente desde:</b> ${d.validFrom}</div>
                <div><b>Vence el:</b> ${d.validTo}</div>
                <div><b>Dias restantes:</b> ${d.diasRestantes}</div>
                <hr style="margin:8px 0;">
                <div style="font-size:0.85em;color:#6c757d;">Clave usada: ${fuenteClave}<br>Archivo: FIRMA.p12 (${d.archivoBytes} bytes)</div>
              </div>
            `,
            width: '500px'
          })
        })
        .catch(err => {
          btnVerificarFirma.disabled = false
          btnVerificarFirma.innerHTML = '<i class="bx bx-check-shield"></i> Verificar'
          Swal.fire({ icon: 'error', title: 'Error de conexion', text: err.message })
        })
    })
  }

function deleteImg() {
  foto_remove.value = 'sinfoto.jpg'
  foto.value = ''
  containerPreview.innerHTML = '<div class="lu-icon"><i class="bx bx-cloud-upload"></i></div>'
    + '<div class="lu-title">Subir logo del sistema</div>'
    + '<div class="lu-hint">Click o arrastra una imagen</div>'
  const lu = document.querySelector('#logoUploaderSistema')
  if (lu) lu.classList.remove('has-image')
}

function deleteImgFactura() {
  const ff = document.querySelector('#foto_factura')
  const ffr = document.querySelector('#foto_factura_remove')
  const cpf = document.querySelector('#containerPreviewFactura')
  const lu = document.querySelector('#logoUploaderFactura')
  if (ff) ff.value = ''
  if (ffr) ffr.value = '1'
  if (cpf) cpf.innerHTML = '<div class="lu-icon"><i class="bx bx-receipt"></i></div>'
    + '<div class="lu-title">Subir logo de facturación</div>'
    + '<div class="lu-hint">Click o arrastra una imagen</div>'
  if (lu) lu.classList.remove('has-image')
}

/* ==========================================================================
   Pantalla "Actualizacion del sistema" (views/admin/actualizacion.php)
   admin.js se carga en varias vistas del panel, por eso todo va detras de una
   guarda: si no existe #actualizacionPanel, este bloque no hace nada.

   Endpoints (controllers/Admin.php):
     GET  admin/actualizacionEstado[?solo_estado=1] -> {ok, configurado, check, status, log, mensaje}
     POST admin/actualizar                          -> {ok, msg, from, to}
     POST admin/revertir                            -> {ok, msg, from, to}
   El trabajo real lo hace /usr/local/sbin/megahnet-update (root), que respalda
   base de datos y codigo antes de aplicar cambios.
   ========================================================================== */
(function () {
    // admin.js se re-ejecuta en cada navegacion PJAX: cortar el sondeo de la
    // instancia anterior para que no se acumulen temporizadores.
    if (window.__actTimer) { clearInterval(window.__actTimer); window.__actTimer = null; }

    const panel = document.querySelector('#actualizacionPanel');
    if (!panel) return;

    const elEstado     = document.querySelector('#actEstado');
    const elLocalShort = document.querySelector('#actLocalShort');
    const elLocalDate  = document.querySelector('#actLocalDate');
    const elLocalSubj  = document.querySelector('#actLocalSubject');
    const elRemShort   = document.querySelector('#actRemoteShort');
    const elRemUrl     = document.querySelector('#actRemoteUrl');
    const elBehind     = document.querySelector('#actBehind');
    const elAhead      = document.querySelector('#actAhead');
    const elDirtyBox   = document.querySelector('#actDirtyBox');
    const elDirtyList  = document.querySelector('#actDirtyList');
    const elBadge      = document.querySelector('#actStatusBadge');
    const elDetalle    = document.querySelector('#actStatusDetalle');
    const elLog        = document.querySelector('#actLog');
    const btnComprobar = document.querySelector('#actBtnComprobar');
    const btnActualizar= document.querySelector('#actBtnActualizar');
    const btnRevertir  = document.querySelector('#actBtnRevertir');
    const elRevInfo    = document.querySelector('#actRevertirInfo');
    const elCommitsBox = document.querySelector('#actCommitsBox');
    const elLocalVer   = document.querySelector('#actLocalVersion');
    const elRemVer     = document.querySelector('#actRemoteVersion');
    const elMejorasBox = document.querySelector('#actMejorasBox');
    const elMejorasLis = document.querySelector('#actMejorasLista');
    const elCommitsList= document.querySelector('#actCommitsLista');
    const elCommitsNum = document.querySelector('#actCommitsCount');

    let temporizador = null;   // polling mientras la actualizacion corre
    let enCurso      = false;  // evita relanzar o comprobar durante el proceso
    let rollback     = null;   // ultimo check.rollback conocido (para el Swal)
    let revirtiendo  = false;  // la corrida en curso la lanzo el boton de revertir

    const PASOS = {
        preflight: 'Verificando requisitos',
        respaldo:  'Generando respaldo de base de datos y codigo',
        'git-pull':'Descargando cambios',
        'patch-sri':'Aplicando parches de facturacion electronica',
        install:   'Aplicando migraciones y dependencias',
        'rollback-respaldo': 'Respaldando el estado actual antes de revertir',
        'rollback-codigo':   'Restaurando el codigo de la version anterior',
        'rollback-bd':       'Restaurando la base de datos del respaldo',
        'rollback-install':  'Reaplicando dependencias y migraciones',
        fin:       'Finalizado'
    };

    function aviso(clase, iconoBx, html) {
        elEstado.className = 'alert ' + clase + ' mb-3';
        elEstado.innerHTML = '<i class="bx ' + iconoBx + '"></i> ' + html;
    }

    function pintarBadge(estado) {
        const mapa = {
            running: ['bg-info',      'en curso'],
            ok:      ['bg-success',   'completada'],
            error:   ['bg-danger',    'con error'],
            vacio:   ['bg-secondary', 'sin datos']
        };
        const [clase, texto] = mapa[estado] || mapa.vacio;
        elBadge.className = 'badge ' + clase;
        elBadge.textContent = texto;
    }

    function pintarLog(texto) {
        const pegadoAbajo = elLog.scrollHeight - elLog.scrollTop - elLog.clientHeight < 40;
        elLog.textContent = (texto && texto.trim() !== '') ? texto : '(sin registro)';
        if (pegadoAbajo) elLog.scrollTop = elLog.scrollHeight;
    }

    /**
     * Mejoras en lenguaje llano (check.mejoras, sacadas del CHANGELOG publicado).
     * Si no vienen -por ser una version anterior al changelog- se oculta y manda
     * el detalle tecnico de los commits.
     */
    function pintarMejoras(mejoras) {
        if (!elMejorasBox || !elMejorasLis) return;
        elMejorasLis.textContent = '';
        if (!Array.isArray(mejoras) || mejoras.length === 0) {
            elMejorasBox.hidden = true;
            return;
        }
        mejoras.forEach(function (m) {
            const li = document.createElement('li');
            li.className = 'mb-1';
            li.textContent = m;
            elMejorasLis.appendChild(li);
        });
        elMejorasBox.hidden = false;
    }

    /** Lista los commits pendientes (check.commits). Oculta la seccion si viene vacia. */
    function pintarCommits(commits) {
        elCommitsList.textContent = '';
        if (!Array.isArray(commits) || commits.length === 0) {
            elCommitsBox.hidden = true;
            return;
        }
        commits.forEach(function (c) {
            if (!c) return;
            const fila = document.createElement('div');
            fila.className = 'act-commit';
            const h = document.createElement('span');
            h.className = 'act-commit-hash';
            h.textContent = c.short || '';
            const f = document.createElement('span');
            f.className = 'act-commit-date';
            f.textContent = c.date ? String(c.date).substring(0, 16) : '';
            const t = document.createElement('span');
            t.className = 'act-commit-subject';
            // textContent y no innerHTML: los asuntos traen comillas y acentos
            t.textContent = c.subject || '';
            t.title = c.subject || '';
            fila.appendChild(h);
            fila.appendChild(f);
            fila.appendChild(t);
            elCommitsList.appendChild(fila);
        });
        elCommitsNum.textContent = commits.length;
        elCommitsBox.hidden = false;
    }

    /** Habilita/deshabilita el boton de reversion segun check.rollback. */
    function pintarRollback(rb) {
        rollback = (rb && rb.disponible) ? rb : null;
        if (!rollback) {
            btnRevertir.disabled = true;
            btnRevertir.title = 'No hay un respaldo previo utilizable para revertir';
            elRevInfo.textContent = '';
            elRevInfo.hidden = true;
            return;
        }
        const texto = 'Se puede volver a la version ' + (rollback.to || '?') +
            ' con el respaldo del ' + (rollback.fecha || 'fecha desconocida') +
            ' (se restaura tambien la base de datos).';
        btnRevertir.disabled = enCurso;
        btnRevertir.title = texto;
        elRevInfo.textContent = texto;
        elRevInfo.hidden = false;
    }

    /** Pinta el resultado de `megahnet-update check` (versiones, pendientes, estado). */
    function pintarCheck(check, configurado, mensaje) {
        if (configurado === false) {
            aviso('alert-warning', 'bx-error',
                '<b>Actualizacion desde el panel no configurada en este servidor.</b><br>' +
                'Ejecute <code>sudo bash install.sh</code> en el servidor para habilitarla.' +
                (mensaje ? '<div class="small mt-1">' + mensaje + '</div>' : ''));
            btnActualizar.disabled = true;
            pintarCommits(null);
            pintarRollback(null);
            return;
        }
        if (!check) return;

        pintarCommits(check.commits);
        pintarMejoras(check.mejoras);
        pintarRollback(check.rollback);

        const local  = check.local  || {};
        const remoto = check.remote || {};
        // La version es lo que se ensena; el hash queda de apoyo para soporte.
        if (elLocalVer) elLocalVer.textContent = local.version ? ('v' + local.version) : 'sin version';
        if (elRemVer)   elRemVer.textContent   = remoto.version ? ('v' + remoto.version) : 'sin version';
        elLocalShort.textContent = local.short || '--';
        elLocalDate.textContent  = local.date ? local.date.substring(0, 16) : '';
        elLocalSubj.textContent  = local.subject || '';
        elLocalSubj.title        = local.subject || '';
        elRemShort.textContent   = remoto.short || '--';
        elRemUrl.textContent     = check.remote_url || '';
        elRemUrl.title           = check.remote_url || '';

        const pendientes = parseInt(check.behind, 10) || 0;
        // La tarjeta cuenta mejoras si las hay; si no, cae en el numero de commits.
        const nMejoras = Array.isArray(check.mejoras) ? check.mejoras.length : 0;
        elBehind.textContent = nMejoras > 0 ? nMejoras : pendientes;
        const adelante = parseInt(check.ahead, 10) || 0;
        elAhead.textContent = adelante > 0 ? (adelante + ' commit(s) locales sin subir') : '';

        // Archivos modificados en el servidor: bloquean la actualizacion
        if (check.dirty && Array.isArray(check.dirty_files) && check.dirty_files.length) {
            elDirtyList.textContent = check.dirty_files.join('\n');
            elDirtyBox.hidden = false;
        } else {
            elDirtyBox.hidden = true;
        }

        if (!check.ok) {
            aviso('alert-danger', 'bx-x-circle', check.reason || 'No se pudo consultar el repositorio.');
            btnActualizar.disabled = true;
        } else if (check.can_update) {
            aviso('alert-primary', 'bx-cloud-download',
                'Hay una actualizacion disponible: <b>' +
                (local.version ? 'v' + local.version : local.short || '') + '</b> &rarr; <b>v' +
                (remoto.version || remoto.short || '') + '</b>.');
            btnActualizar.disabled = false;
        } else if (pendientes === 0) {
            aviso('alert-success', 'bx-check-circle', 'El sistema esta al dia.');
            btnActualizar.disabled = true;
        } else {
            aviso('alert-warning', 'bx-error', check.reason || 'No se puede actualizar en este momento.');
            btnActualizar.disabled = true;
        }
    }

    /** Pinta status.json (progreso o resultado de la ultima corrida). */
    function pintarStatus(status) {
        if (!status || !status.state) { pintarBadge('vacio'); elDetalle.textContent = ''; return false; }
        pintarBadge(status.state);
        const paso = PASOS[status.step] || status.step || '';
        const partes = [];
        if (status.state === 'running' && paso) partes.push(paso + '...');
        if (status.state !== 'running' && status.message) partes.push(status.message);
        if (status.from && status.to) partes.push(status.from + ' → ' + status.to);
        if (status.started_at) partes.push('inicio ' + status.started_at);
        if (status.finished_at) partes.push('fin ' + status.finished_at);
        elDetalle.textContent = partes.join('  ·  ');
        return status.state === 'running';
    }

    /** Consulta el estado. soloEstado=true durante el polling (no corre git fetch). */
    function cargarEstado(soloEstado) {
        const url = base_url + 'admin/actualizacionEstado' + (soloEstado ? '?solo_estado=1' : '');
        return fetch(url, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || res.ok === false) {
                    aviso('alert-danger', 'bx-x-circle', (res && res.mensaje) || 'No se pudo consultar el estado.');
                    return;
                }
                pintarLog(res.log || '');
                const corriendo = pintarStatus(res.status);

                if (corriendo) {
                    enCurso = true;
                    btnActualizar.disabled = true;
                    btnRevertir.disabled = true;
                    btnComprobar.disabled = true;
                    aviso('alert-info', 'bx-loader bx-spin',
                        'Actualizacion en curso. No cierre esta pantalla.');
                    arrancarPolling();
                } else {
                    if (enCurso) {
                        // Acaba de terminar: avisar y refrescar versiones con un check real
                        enCurso = false;
                        detenerPolling();
                        btnComprobar.disabled = false;
                        const ok = res.status && res.status.state === 'ok';
                        const paso = (res.status && typeof res.status.step === 'string') ? res.status.step : '';
                        const esRollback = revirtiendo || paso.indexOf('rollback-') === 0;
                        revirtiendo = false;
                        const okMsg  = esRollback ? 'Reversion completada' : 'Actualizacion completada';
                        const malMsg = esRollback ? 'La reversion fallo' : 'La actualizacion fallo';
                        alertaPersonalizada(ok ? 'success' : 'error',
                            ok ? okMsg : ((res.status && res.status.message) || malMsg));
                        cargarEstado(false);
                        return;
                    }
                    btnComprobar.disabled = false;
                    if (!soloEstado) pintarCheck(res.check, res.configurado, res.mensaje);
                }
            })
            .catch(function () {
                aviso('alert-danger', 'bx-x-circle', 'Error de red al consultar el estado.');
            });
    }

    function arrancarPolling() {
        if (temporizador) return;
        temporizador = setInterval(function () {
            // PJAX no dispara beforeunload: si la pantalla ya no esta en el DOM
            // (el usuario navego a otra seccion), dejar de sondear.
            if (!document.body.contains(panel)) { detenerPolling(); return; }
            cargarEstado(true);
        }, 3000);
        window.__actTimer = temporizador;
    }
    function detenerPolling() {
        if (temporizador) { clearInterval(temporizador); temporizador = null; }
        window.__actTimer = null;
    }

    btnComprobar.addEventListener('click', function () {
        if (enCurso) return;
        btnComprobar.disabled = true;
        aviso('alert-secondary', 'bx-loader bx-spin', 'Comprobando version instalada y disponible...');
        cargarEstado(false);
    });

    btnActualizar.addEventListener('click', function () {
        if (enCurso || btnActualizar.disabled) return;
        Swal.fire({
            title: 'Actualizar el sistema?',
            html: 'Antes de aplicar cambios se generara un <b>respaldo de la base de datos y del codigo</b>.<br><br>' +
                  'La operacion tarda unos minutos y el sistema puede ir mas lento mientras tanto.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Si, actualizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb'
        }).then(function (r) {
            if (!r.isConfirmed) return;
            btnActualizar.disabled = true;
            btnRevertir.disabled = true;
            btnComprobar.disabled = true;
            aviso('alert-info', 'bx-loader bx-spin', 'Iniciando actualizacion...');
            fetch(base_url + 'admin/actualizar', { method: 'POST', cache: 'no-store' })
                .then(function (rs) { return rs.json(); })
                .then(function (res) {
                    if (!res || !res.ok) {
                        btnComprobar.disabled = false;
                        alertaPersonalizada('warning', (res && res.msg) || 'No se pudo iniciar la actualizacion');
                        cargarEstado(false);
                        return;
                    }
                    enCurso = true;
                    alertaPersonalizada('success', res.msg);
                    aviso('alert-info', 'bx-loader bx-spin', 'Actualizacion en curso. No cierre esta pantalla.');
                    pintarBadge('running');
                    arrancarPolling();
                })
                .catch(function () {
                    btnComprobar.disabled = false;
                    alertaPersonalizada('error', 'Error de red al iniciar la actualizacion');
                });
        });
    });

    btnRevertir.addEventListener('click', function () {
        if (enCurso || btnRevertir.disabled) return;
        if (!rollback) {
            alertaPersonalizada('warning', 'No hay un respaldo previo utilizable para revertir.');
            return;
        }
        Swal.fire({
            title: 'Volver a la version anterior?',
            html: 'Se restaurara el sistema a la version <code>' + (rollback.to || '?') + '</code> ' +
                  'con el respaldo del <b>' + (rollback.fecha || 'fecha desconocida') + '</b>.<br><br>' +
                  '<b class="text-danger">Se restaura tambien la BASE DE DATOS</b> al estado de ese ' +
                  'respaldo: <b>se perdera todo lo registrado despues de esa fecha</b> (clientes, pagos, ' +
                  'facturas, tickets y cualquier otro cambio).<br><br>' +
                  'Esta operacion no se puede deshacer desde el panel. Continuar?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si, revertir y restaurar la BD',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            focusCancel: true
        }).then(function (r) {
            if (!r.isConfirmed) return;
            btnRevertir.disabled = true;
            btnActualizar.disabled = true;
            btnComprobar.disabled = true;
            aviso('alert-info', 'bx-loader bx-spin', 'Iniciando reversion...');
            fetch(base_url + 'admin/revertir', { method: 'POST', cache: 'no-store' })
                .then(function (rs) { return rs.json(); })
                .then(function (res) {
                    if (!res || !res.ok) {
                        btnComprobar.disabled = false;
                        alertaPersonalizada('warning', (res && res.msg) || 'No se pudo iniciar la reversion');
                        cargarEstado(false);
                        return;
                    }
                    enCurso = true;
                    revirtiendo = true;
                    alertaPersonalizada('success', res.msg);
                    aviso('alert-info', 'bx-loader bx-spin', 'Reversion en curso. No cierre esta pantalla.');
                    pintarBadge('running');
                    arrancarPolling();
                })
                .catch(function () {
                    btnComprobar.disabled = false;
                    alertaPersonalizada('error', 'Error de red al iniciar la reversion');
                });
        });
    });

    window.addEventListener('beforeunload', detenerPolling);

    // Carga inicial
    cargarEstado(false);
})();
