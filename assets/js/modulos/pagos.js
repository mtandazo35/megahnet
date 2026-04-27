let tblPagos
const formulario = document.querySelector('#formulario')

const busqueda = document.querySelector('#busqueda')

const btnAccion = document.querySelector('#btnAccion')

document.addEventListener('DOMContentLoaded', function () {
  // BusquedaOrden('1206773036001')
  // BusquedaOrden('1206773036')
  // completar venta

  btnAccion.addEventListener('click', function () {
    // window.location.reload()


    if (busqueda.value !== '') {
      BusquedaOrden(busqueda.value)
    }
  })
})

/*function verReporte (idOrden) {
  Swal.fire({
    icon: 'success',
    title: 'Desea Generar Reporte?',
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: 'Ticked',
    denyButtonText: `Factura`
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
   /* if (result.isConfirmed) {
      const ruta = base_url + 'verificar/reporte/facturaticket/' + idOrden
      window.open(ruta, '_blank')
    } else if (result.isDenied) {
      const ruta = base_url + 'verificar/reporte/factura/' + idOrden
      window.open(ruta, '_blank')
    }
  })
}*/

function limpiarCampos () {
  errorNombre.textContent = ''
  errorApellido.textContent = ''
  errorCorreo.textContent = ''
  errorTelefono.textContent = ''
  errorDireccion.textContent = ''
  errorClave.textContent = ''
  errorRol.textContent = ''
}

function BusquedaOrden ($busquedaOrden) {
  // console.log($busquedaOrden)
  // cargar datos con el plugin datatable

  tblPagos = $('#tblPagos').DataTable({
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
    ajax: {
      url: base_url + 'pagos/listarFactura/' + $busquedaOrden,
      dataSrc: ''
    },
    columns: [
      { data: 'fecha' },
      { data: 'orden_no' },      
      { data: 'cliente' },
      { data: 'ruc' },
      { data: 'totalfactura' },
      { data: 'tipopago' },
      { data: 'estado' },
      { data: 'acciones' }
    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    destroy: true,
    responsive: true,
    order: [[0, 'desc']]

  })
}
