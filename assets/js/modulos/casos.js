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

  // autocomplete contratos
  $('#buscarCliente').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'casos/buscar',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
          if (data.length > 0) {
            errorCliente.textContent = ''
          }else {
            errorCliente.textContent = 'NO HAY CONTRATOS CON EL CLIENTE'
          }
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
      coordenadaContrato.value = ui.item.coordenada
      direccionContrato.value = ui.item.direccion
      comentarioContrato.value = ui.item.comentario
      idContrato.value = ui.item.id
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
         // console.log(this.responseText)
          alertaPersonalizada(res.type, res.msg)
          if (res.type == 'success') {
            //localStorage.removeItem(nombreKey)
            setTimeout(() => {
              Swal.fire({
                title: 'Desea Generar Reporte?',
                showCancelButton: true,
                confirmButtonText: 'Imprimir'
              }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                  const ruta = base_url + 'casos/reporte/factura/' + res.idCaso
                  const rutawhatsapp = res.whatsapp

                  window.open(ruta, '_blank')
                  window.open(rutawhatsapp, '_blank')
                } 
                window.location.reload()
              })
            }, 2000)
          }
        }
      }
    }
  })

  // cargar datos con el plugin datatables
  tblHistorial = $('#tblHistorial').DataTable({
    deferRender: true,
    pageLength: 25,
    
    ajax: {
      url: base_url + 'casos/listar',
      dataSrc: ''
    },
    columns: [
      { data: 'nombre' },
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



      // editorDireccion.setData(res.direccion)
      btnAccion.textContent = 'Actualizar'
      firstTab.show()
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
