let divLoading = document.querySelector("#divLoading");

const id = document.querySelector('#id')
const idContrato = document.querySelector('#idContrato')
const idGrupoTrabajo = document.querySelector('#idGrupoTrabajo')

const coordenadaContrato = document.querySelector('#coordenadaContrato')
const direccionContrato = document.querySelector('#direccionContrato')
const buscarCliente = document.querySelector('#buscarCliente')
const comentarioContrato = document.querySelector('#comentarioContrato')
const estado = document.querySelector('#estado')


const buscarGrupoTrabajo = document.querySelector('#buscarGrupoTrabajo')
const responsableGrupoTrabajo = document.querySelector('#responsableGrupoTrabajo')

const problemaReportado = document.querySelector('#problemaReportado')
const trabajoRealizado = document.querySelector('#trabajoRealizado')

const observacion = document.querySelector('#observacion')

const errorCliente = document.querySelector('#errorCliente')
const errorGrupoTrabajo = document.querySelector('#errorGrupoTrabajo')

const errorCoordenada = document.querySelector('#errorCoordenada')
const errorDireccion = document.querySelector('#errorDireccion')

document.addEventListener('DOMContentLoaded', function () {
  // cargar productos de localStorage

  // autocomplete contratos (buscar cliente que tenga contrato activo)
  $('#buscarCliente').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'casos/buscar',
        dataType: 'json',
        data: { term: request.term },
        success: function (data) {
          response(data)
          if (data.length > 0) {
            errorCliente.innerHTML = ''
          } else {
            errorCliente.innerHTML = 'Sin contratos activos para "<b>' + request.term.replace(/[<>]/g,'') + '</b>". Crea un contrato en <a href="' + base_url + 'contratos" class="text-primary">Contratos</a> primero.'
          }
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
      coordenadaContrato.value = ui.item.coordenada || ''
      direccionContrato.value  = ui.item.direccion  || ''
      comentarioContrato.value = ui.item.comentario || ''
      idContrato.value = ui.item.id
      errorCliente.innerHTML = ''
    }
  })
  // autocomplete grupo trabajo
  $('#buscarGrupoTrabajo').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'casos/buscarGrupoTrabajo',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
          if (data.length > 0) {
            errorGrupoTrabajo.textContent = ''
          }else {
            errorGrupoTrabajo.textContent = 'NO HAY GRUPOS DE TRABAJOS'
          }
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
      responsableGrupoTrabajo.value = ui.item.responsable
      idGrupoTrabajo.value = ui.item.id
    }
  })
  // completar cotizacion
  btnAccion.addEventListener('click', function () {
    if (idContrato.value == '') {
      alertaPersonalizada('warning', 'NO EXISTE CONTRATO SELECCIONADO')
      return
    } else if (idGrupoTrabajo.value == '') {
      alertaPersonalizada('warning', 'NO HAY ASIGNADO GRUPO DE TRABAJO')
      return
    } else if (problemaReportado.value == '') {
      alertaPersonalizada('warning', 'DETALLE EL PROBLEMA AH REPORTAR')
      return
    }  else {
      const url = base_url + 'casos/registrarCasos'
      // hacer una instancia del objeto XMLHttpRequest 
      const http = new XMLHttpRequest()
      // Abrir una Conexion - POST - GET
      http.open('POST', url, true)
      // Enviar Datos
      http.send(JSON.stringify({
        id: id.value,
        idContrato: idContrato.value,
        idGrupoTrabajo: idGrupoTrabajo.value,
        trabajoRealizado: trabajoRealizado.value,
        problemaReportado: problemaReportado.value,
        observacion:observacion.value,   
        // tipoBanco: tipoBanco.value,
        // cuentaBancaria: cuentaBancaria.value,
        estado: estado.value
      }))
      // verificar estados
      http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          const res = JSON.parse(this.responseText)
          alertaPersonalizada(res.type, res.msg)
          if (res.type !== 'success') return;

          const estadoSel = (estado.value || '').toUpperCase().trim();
          const rutaReporte = base_url + 'casos/reporte/factura/' + res.idCaso;
          const tipoWa = (estadoSel === 'INGRESADO') ? 'creado' : 'actualizado';

          // Preview WA via API (modal con textarea editable). Si no hay sesion WA o
          // cliente sin telefono, el endpoint devuelve ok:false y simplemente continua.
          const irAReporte = function () {
            if (estadoSel === 'FINALIZADO') {
              try { tblHistorial.ajax.reload(null, false); } catch(e){}
              return;
            }
            setTimeout(() => {
              Swal.fire({
                title: 'Desea Generar Reporte?',
                showCancelButton: true,
                confirmButtonText: 'Imprimir'
              }).then((result) => {
                if (result.isConfirmed) {
                  window.open(rutaReporte, '_blank');
                }
                window.location.reload();
              });
            }, 400);
          };

          if (res.idCaso && typeof previewYEnviarWaCaso === 'function') {
            previewYEnviarWaCaso(res.idCaso, res.telefonoCliente, tipoWa, irAReporte);
          } else {
            irAReporte();
          }
        }
      }
    }
  })

  // cargar datos con el plugin datatables
  tblHistorial = $('#tblHistorial').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
    ajax: {
      url: base_url + 'casos/listar',
      dataSrc: ''
    },
    columns: [
      { data: 'nombre' },
      { data: 'telefono', defaultContent: '' },
      { data: 'fecha' },
      { data: 'direccion' },
      { data: 'coordenada' },
      { data: 'problema_reportado' },
      { data: 'responsable' },
      { data: 'estado' },
      { data: 'acciones' }
    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    responsive: true,
    order: [[0, 'desc']]
  })
})

function verReporte (idCaso) {
  Swal.fire({
    title: 'Desea Imprimir Caso?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Imprimir',
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {
      const ruta = base_url + 'casos/reporte/factura/' + idCaso
      window.open(ruta, '_blank')
    } 
  })
}

function eliminarContrato (idContrato) {
  const url = base_url + 'contratos/eliminar/' + idContrato
  eliminarRegistros(url, tblHistorial)
}
function Editar (idCaso) {
  limpiarCampos()
  // editorDireccion.setData('')

  const url = base_url + 'casos/editar/' + idCaso
  // hacer una instancia del objeto XMLHttpRequest 
  const http = new XMLHttpRequest()
  // Abrir una Conexion - POST - GET
  http.open('GET', url, true)
  // Enviar Datos
  http.send()
  // verificar estados
  http.onreadystatechange = function () {
    if (this.readyState == 4 && this.status == 200) {
      const res = JSON.parse(this.responseText)
     // agregarProducto(res.producto[0].id, res.producto[0].cantidad, 10000, res.producto[0].precio, res.producto[0].nombre)
      // console.log(res.producto)
      id.value = res.id
      idContrato.value = res.idContrato
      buscarCliente.value = res.nombre

      // listaCarrito.value = res.productos
      direccionContrato.value = res.direccion
      coordenadaContrato.value = res.coordenada
      comentarioContrato.value = res.comentario

      problemaReportado.value = res.problema_reportado
      trabajoRealizado.value = res.trabajo_realizado
      observacion.value = res.observacion

      idGrupoTrabajo.value=res.grupo_asignado
      responsableGrupoTrabajo.value=res.responsable
  
      buscarGrupoTrabajo.value= res.grupo_asignado + ' ' + res.descripcion + ' ' + res.responsable;
      // tipoBanco.value= res.tipoBanco
      // cuentaBancaria.value= cuentaBancaria



      btnAccion.textContent = 'Actualizar'
      // Abrir modal (la vista define window.abrirModalCaso)
      if (typeof window.abrirModalCaso === 'function') {
        window.abrirModalCaso();
      } else {
        firstTab.show();
      }
    }
  }
}

function limpiarCampos () {
  id.textContent = ''
  //localStorage.removeItem(nombreKey)
idContrato.textContent='';
buscarCliente.textContent='';
direccionContrato.textContent='';
coordenadaContrato.textContent='';
comentarioContrato.textContent='';

problemaReportado.textContent='';
trabajoRealizado.textContent='';
observacion.textContent='';

idGrupoTrabajo.textContent='';
responsableGrupoTrabajo.textContent='';
buscarGrupoTrabajo.textContent='';
  // listaCarrito.value = res.productos

}

// ============================================================================
// Preview del mensaje WhatsApp antes de enviar (post-creacion/actualizacion de caso)
// Reusa el endpoint casos/notificarCaso con preview=1
// ============================================================================
function previewYEnviarWaCaso(idCaso, telefonoCliente, tipoForzar, onClose) {
    if (!idCaso) { if (typeof onClose === 'function') onClose(); return; }
    var qsTipo = tipoForzar ? '&tipo=' + encodeURIComponent(tipoForzar) : '';
    fetch(base_url + 'casos/notificarCaso/' + idCaso + '?preview=1' + qsTipo, {
        method: 'POST', credentials: 'same-origin'
    }).then(function(r){ return r.json(); }).then(function(d){
        if (!d.ok) {
            if (typeof onClose === 'function') onClose();
            return;
        }
        var titulo = (d.tipo === 'creado') ? 'Notificar caso creado' : 'Notificar actualizacion del caso';
        Swal.fire({
            title: titulo,
            html: '<div style="text-align:left;font-size:.85rem;color:#374151;margin-bottom:.5rem;">' +
                  '<i class="bx bxl-whatsapp" style="color:#16a34a;font-size:18px;vertical-align:-3px;"></i> Se enviara a <b>+' + d.telefono + '</b>' +
                  ' <small class="text-muted ms-2">(editable)</small>' +
                  '</div>' +
                  '<textarea id="swalEdMsgCaso" style="width:100%;background:#dcfce7;color:#0f172a;padding:.75rem .9rem;border-radius:10px;font-family:ui-monospace,Menlo,monospace;font-size:.8rem;line-height:1.45;height:200px;border:1px solid #bbf7d0;resize:vertical;">' +
                  String(d.mensaje || '').replace(/[&<>"']/g, function(c){ return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]; }) +
                  '</textarea>' +
                  '<small class="text-muted d-block mt-1" style="font-size:.7rem;">Edita el texto si necesitas corregir algo. Asteriscos para *negrita*, guion bajo para _cursiva_.</small>',
            showCancelButton: true,
            confirmButtonText: '<i class="bx bx-paper-plane me-1"></i>Enviar',
            cancelButtonText: 'No enviar',
            width: 580,
            didOpen: function(){ var ta = document.getElementById('swalEdMsgCaso'); if (ta) ta.focus(); }
        }).then(function(r){
            if (!r.isConfirmed) { if (typeof onClose === 'function') onClose(); return; }
            var ta = document.getElementById('swalEdMsgCaso');
            var mensajeEdit = ta ? ta.value : (d.mensaje || '');
            fetch(base_url + 'casos/notificarCaso/' + idCaso + (tipoForzar ? '?tipo=' + encodeURIComponent(tipoForzar) : ''), {
                method: 'POST', credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ mensaje: mensajeEdit, tipo: tipoForzar || null })
            }).then(function(r2){ return r2.json(); }).then(function(dd){
                if (dd.ok) {
                    Swal.fire({icon:'success', title:'Mensaje enviado', text:'WhatsApp a +' + dd.telefono, timer:1800, showConfirmButton:false});
                } else {
                    Swal.fire({icon:'error', title:'No se pudo enviar', text: dd.msg || ('HTTP ' + (dd.http || '?'))});
                }
                if (typeof onClose === 'function') setTimeout(onClose, 600);
            }).catch(function(){ if (typeof onClose === 'function') onClose(); });
        });
    }).catch(function(){
        if (typeof onClose === 'function') onClose();
    });
}
