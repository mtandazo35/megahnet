
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

  // vista Previa
  foto.addEventListener('change', function (e) {
    if (e.target.files[0].type == 'image/jpg' ||
      e.target.files[0].type == 'image/jpeg') {
      const url = e.target.files[0]
      const tmpUrl = URL.createObjectURL(url)
      foto_remove.value = 'Logo.jpg'
      containerPreview.innerHTML = `<img class="img-thumbnail" style="width: 50%;margin-top: 10px;" src="${tmpUrl}" width="150">
            <button class="btn btn-danger" style="width: 50%; margin-top: 5px;" type="button" onclick="deleteImg()"><i class="fas fa-trash"></i></button>`
    } else {
      foto_remove.value = 'sinfoto.jpg'
      foto.value = ''
      alertaPersonalizada('warning', 'SOLO SE PERMITEN IMG DE TIPO JPG-JPEG')
    }
  })
  // Actualizar Datos
  formulario.addEventListener('submit', function (e) {
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
    else if (items.value == '') {
      errorItems.textContent = 'EL TOTAL DE ITEMS ES REQUERIDO'
    } else if (establecimiento.value == '') {
      errorEstablecimiento.textContent = 'EL ESTABLECIMIENTO ES REQUERIDO'
    } else if (emision.value == '') {
      errorEmision.textContent = 'EL PUNTO DE EMISION ES REQUERIDO'
    } else if (contabilidad.value == '') {
      errorContabilidad.textContent = 'LA CONTABILIDAD ES REQUERIDO'
    }
    else if (firmainicio && (firmainicio.value == '' || firmainicio.value == null)) {
      errorFirmainicio.textContent = 'LA FECHA FIRMA INICIO ES REQUERIDO'
    }
    else if (firmafinal && (firmafinal.value == '' || firmafinal.value == null)) {
      errorFirmafinal.textContent = 'LA FECHA FIRMA FINAL ES REQUERIDO'
    } else if (cantidaddocumento && (cantidaddocumento.value == '' || cantidaddocumento.value == null)) {
      errorCantidaddocumento.textContent = 'LA CANTIDAD DOCUMENTO ES REQUERIDO'
    } else {
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
  containerPreview.innerHTML = ''
}
