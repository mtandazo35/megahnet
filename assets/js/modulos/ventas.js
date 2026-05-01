let divLoading = document.querySelector("#divLoading");

const tblNuevaVenta = document.querySelector('#tblNuevaVenta tbody')

const idCliente = document.querySelector('#idCliente')
const telefonoCliente = document.querySelector('#telefonoCliente')
const correoCliente = document.querySelector('#correoCliente')
const errorCliente = document.querySelector('#errorCliente')
const chFacturar = document.querySelector('#chFacturar')
const buscarContrato = document.querySelector('#buscarContrato')
const tipopago = document.querySelector('#tipopago')



const descuento = document.querySelector('#descuento')
const metodo = document.querySelector('#metodo')
document.addEventListener('DOMContentLoaded', function () {
  // cargar productos de localStorage
  mostrarProducto()

  // autocomplete clientes
  $('#buscarCliente').autocomplete({
    source: function (request, response) {
      $.ajax({
        url: base_url + 'clientes/buscar',
        dataType: 'json',
        data: {
          term: request.term
        },
        success: function (data) {
          response(data)
          if (data.length > 0) {
            errorCliente.textContent = ''
          } else {
            errorCliente.textContent = 'NO HAY CLIENTE CON ESE NOMBRE'
          }
        }
      })
    },
    minLength: 2,
    select: function (event, ui) {
      telefonoCliente.value = ui.item.telefono
      correoCliente.value = ui.item.correo
      idCliente.value = ui.item.id
    }
  })





  // completar venta
  btnAccion.addEventListener('click', function () {
    let filas = document.querySelectorAll('#tblNuevaVenta tr').length
   // console.log(filas);
    if (filas < 2) {

      alertaPersonalizada('warning', 'CARRITO VACIO')

      // } //else if (idCliente.value == '') {
      // alertaPersonalizada('warning', 'EL CLIENTE ES REQUERIDO')
     //  return
    } else if (metodo.value == '') {
      alertaPersonalizada('warning', 'EL METODO ES REQUERIDO')

      return
    } else {
      divLoading.style.display = "flex";

   //  showLoader();
      const url = base_url + 'ventas/registrarVenta'
      // hacer una instancia del objeto XMLHttpRequest 
      const http = new XMLHttpRequest()
      // Abrir una Conexion - POST - GET
      http.open('POST', url, true)
      // Enviar Datos
      http.send(JSON.stringify({
        productos: listaCarrito,
        idCliente: idCliente.value,
        metodo: metodo.value,
        descuento: descuento.value,
        tipoPago: tipopago.value



      }))
      // verificar estados
      http.onreadystatechange = function () {
        if (this.readyState != 4) return;
        divLoading.style.display = "none";
        if (this.status != 200) {
          mostrarModalFactura({ type: 'error', msg: 'Error de comunicacion con el servidor' });
          return;
        }
        let res;
        try { res = JSON.parse(this.responseText); }
        catch (e) {
          mostrarModalFactura({ type: 'error', msg: 'Respuesta invalida del servidor' });
          return;
        }
        if (res.type === 'success') localStorage.removeItem(nombreKey);
        mostrarModalFactura(res);
      }
    }
  })

  // cargar datos con el plugin datatables
  tblHistorialfisica = $('#tblHistorialfisica').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
    ajax: {
      url: base_url + 'ventas/listar',
      dataSrc: ''
    },
    columns: [
	{ data: 'acciones' },
      { data: 'fecha' },
      { data: 'hora' },
      { data: 'total' },
      { data: 'nombre' },
      { data: 'serie' },
      { data: 'metodo' },
      { data: 'estado' }
      
    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    responsive: true,
    order: [[5, 'desc']]
  })

  // cargar datos con el plugin datatables Factura Electronica
  tblHistorialFE = $('#tblHistorialFE').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
    ajax: {
      url: base_url + 'ventas/listarElectronica',
      dataSrc: ''
    },
    columns: [
	{ data: 'acciones' },
      { data: 'cliente' },
	  { data: 'fecha' },
      { data: 'hora', defaultContent: '' },
      { data: 'factura' },      
      { data: 'claveacceso' },
      { data: 'estado' },
      { data: 'totalfactura' },
      { data: 'autorizacion' }
      
    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    responsive: true,
    order: [[4, 'desc']]
  })

  // Auto-reload cada 15s del listado de Factura Electronica
  // - Usa ajax.reload(null, false) para no resetear paginacion ni filtros
  // - Pausa cuando la pestaña no esta visible (ahorra ancho de banda)
  let tblHistorialAutoReload = setInterval(() => {
    if (!document.hidden && tblHistorialFE) {
      try { tblHistorialFE.ajax.reload(null, false); } catch (e) {}
    }
  }, 15000);

  // Tambien refresca al volver a enfocar la pestaña del navegador
  document.addEventListener('visibilitychange', () => {
    if (!document.hidden && tblHistorialFE) {
      try { tblHistorialFE.ajax.reload(null, false); } catch (e) {}
    }
  });
})




// cargar productos
function mostrarProducto() {
  if (localStorage.getItem(nombreKey) != null) {
    const url = base_url + 'productos/mostrarDatos'
    // hacer una instancia del objeto XMLHttpRequest 
    const http = new XMLHttpRequest()
    // Abrir una Conexion - POST - GET
    http.open('POST', url, true)
    // Enviar Datos
    http.send(JSON.stringify(listaCarrito))
    // verificar estados
    http.onreadystatechange = function () {
      if (this.readyState == 4 && this.status == 200) {
        const res = JSON.parse(this.responseText)
        let html = ''
        if (res.productos.length > 0) {
          res.productos.forEach(producto => {
            // <td>${producto.precio_venta}</td> //reemplazar por el inpuit del precio venta! si desea que el precio no sea modificable
            // <td>${producto.nombre}</td> reemplazar por el input del nombre del producto para no modificar
            html += `<tr>
            <td>
                            <input style="width:800px"; type="text" class="form-control inputDescripcion" data-id="${producto.id}" value="${producto.nombre}">
                            </td>
                            <td>
                            <input style="width:125px"; type="number" class="form-control inputPrecio" data-id="${producto.id}" value="${producto.precio_venta}">
                            </td>
                            <td>
                            <input style="width:100px"; type="number" class="form-control inputCantidad" data-id="${producto.id}" value="${producto.cantidad}">
                            </td>
                            <td>${producto.subTotalVenta}</td>
                            <td><button class="btn btn-danger btnEliminar" data-id="${producto.id}" type="button"><i class="fas fa-trash"></i></button></td>
                        </tr>`
          })
          tblNuevaVenta.innerHTML = html
          totalPagar.value = res.totalVenta
          totalPagarHidden.value = res.totalVentaHidden

          btnEliminarProducto()
          agregarCantidad()
          agregarPrecioVenta()
          agregarDescripcion()
        } else {
          tblNuevaVenta.innerHTML = ''
        }
      }
    }
  } else {
    tblNuevaVenta.innerHTML = ''

  /*  tblNuevaVenta.innerHTML = `<tr>
            <td colspan="4" class="text-center">CARRITO VACIO</td>
        </tr>`*/
  }
}

function verReporte(idVenta) {
  Swal.fire({
    icon: 'success',
    title: 'Desea Generar Reporte?',
    showDenyButton: true,
    showCancelButton: true,
    confirmButtonText: 'Ticked',
    denyButtonText: `Factura`
  }).then((result) => {
    /* Read more about isConfirmed, isDenied below */
    if (result.isConfirmed) {
      const ruta = base_url + 'ventas/reporte/ticked/' + idVenta
      window.open(ruta, '_blank')
    } else if (result.isDenied) {
      const ruta = base_url + 'ventas/reporte/facturaImp/' + idVenta
      window.open(ruta, '_blank')
    }
  })
}

function anularVenta(idVenta) {
  Swal.fire({
    title: 'Esta seguro de anular la venta?',
    text: 'El stock de los productos cambiarán!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Anular!'
  }).then((result) => {
    if (result.isConfirmed) {
      const url = base_url + 'ventas/anular/' + idVenta
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
          alertaPersonalizada(res.type, res.msg)
          if (res.type == 'success') {
            tblHistorialfisica.ajax.reload()
          }
        }
      }
    }
  })
}
function anularVentaElectronica(idVenta) {
   Swal.fire({
    title: 'Esta seguro de anular la Factura Electronica?',
    text: 'El stock de los productos cambiarán!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Anular!'
    }).then((result) => {
    if (result.isConfirmed) {
      const url = base_url + 'ventas/anularElectronica/' + idVenta
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
          alertaPersonalizada(res.type, res.msg)
          if (res.type == 'success') {
            tblHistorialFE.ajax.reload()
          }
        }
      }
    }
  })
}

function envioCorreoElectronica(idVenta) {
  Swal.fire({
    title: 'Esta seguro de enviar la Factura Electronica?',
    text: 'Se enviara la Factura Electronica!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Enviar!'
  }).then((result) => {
    if (result.isConfirmed) {
      const url = base_url + 'ventas/envioFacturaElectronica/' + idVenta
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
          alertaPersonalizada(res.type, res.msg)
          if (res.type == 'success') {
            tblHistorialFE.ajax.reload()
          }
        }
      }
    }
  })
}

function envioSriElectronica(idVenta) {
  Swal.fire({
    title: 'Esta seguro de reenviar la Factura Electronica al SRI ?',
    text: 'Reenviar Factura electronica al SRI!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Enviar!'
  }).then((result) => {
    if (result.isConfirmed) {
      divLoading.style.display = "flex";

      const url = base_url + 'ventas/envioSriElectronica/' + idVenta
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
          alertaPersonalizada(res.type, res.msg)
          if (res.type == 'success') {
            // location.reload()
            tblHistorialFE.ajax.reload()
          }else{
            divLoading.style.display = "none";

          }
        }else{
          divLoading.style.display = "none";

        }
      }
    }
  })
}
function calcularDescuento() {
  // let totalPagar= document.querySelector('#totalPagar').value
  // let con = '1.'
  let tot = totalPagarHidden.value - ((totalPagarHidden.value * descuento.value) / 100)
  // alert (tot)
  document.querySelector('#totalPagar').value = tot.toLocaleString('en-US')
  // tot.toFixed(2)
  // alert(tot.toFixed(2))

}
function showLoader() {
  const loader = document.getElementById('loader');
  loader.style.display = 'flex';
}

// Función para ocultar el loader
function hideLoader() {
  const loader = document.getElementById('loader');
  loader.style.display = 'none';
}

// === Modal unificado de resultado de emision ===
function mostrarModalFactura(res) {
  const isSuccess = res.type === 'success';
  const isWarning = res.type === 'warning';
  const titulo = isSuccess ? 'Factura emitida correctamente'
               : isWarning ? 'Factura registrada con observaciones'
               : 'No se pudo emitir la factura';
  const icon = isSuccess ? 'success' : (isWarning ? 'warning' : 'error');

  // Construir HTML interno con botones de acciones segun el estado
  let bodyHtml = '<p class="mb-2">' + (res.msg || '') + '</p>';
  if (res.ClaveAcceso) {
    bodyHtml += '<small class="text-muted d-block mb-3">Clave de acceso:<br><code class="user-select-all">' + res.ClaveAcceso + '</code></small>';
  }

  if (isSuccess && res.factura === 'electronica' && res.ClaveAcceso) {
    // Factura electronica autorizada
    bodyHtml += '<div class="d-grid gap-2 mt-3">'
            + '<a class="btn btn-primary" target="_blank" href="' + base_url + 'facturaelectronica/public/archivos/ride/' + res.ClaveAcceso + '.pdf"><i class="fas fa-file-pdf me-1"></i>Descargar RIDE (PDF)</a>'
            + '<a class="btn btn-outline-info" target="_blank" href="' + base_url + 'facturaelectronica/public/archivos/autorizados/' + res.ClaveAcceso + '.xml"><i class="fas fa-file-code me-1"></i>Descargar XML autorizado</a>'
            + '<a class="btn btn-outline-secondary" target="_blank" href="' + base_url + 'ventas/facturaTicked/' + res.ClaveAcceso + '/' + res.idVenta + '"><i class="fas fa-receipt me-1"></i>Ver Ticket</a>'
            + '</div>';
  } else if (isSuccess && res.factura === 'fisica' && res.idVenta) {
    bodyHtml += '<div class="d-grid gap-2 mt-3">'
            + '<a class="btn btn-primary" target="_blank" href="' + base_url + 'ventas/reporte/facturaImp/' + res.idVenta + '"><i class="fas fa-file-pdf me-1"></i>Descargar Factura PDF</a>'
            + '<a class="btn btn-outline-secondary" target="_blank" href="' + base_url + 'ventas/reporte/ticked/' + res.idVenta + '"><i class="fas fa-receipt me-1"></i>Ver Ticket</a>'
            + '</div>';
  } else if (!isSuccess && res.idVenta) {
    // Permite reintentar
    bodyHtml += '<div class="d-grid gap-2 mt-3">'
            + '<button class="btn btn-warning" id="btnReintentarSri"><i class="fa-solid fa-paper-plane me-1"></i>Reintentar envio al SRI</button>'
            + '</div>';
  }

  Swal.fire({
    icon: icon,
    title: titulo,
    html: bodyHtml,
    showCloseButton: true,
    showConfirmButton: true,
    confirmButtonText: 'Cerrar',
    didOpen: () => {
      const btn = document.getElementById('btnReintentarSri');
      if (btn) {
        btn.addEventListener('click', () => {
          Swal.close();
          envioSriElectronica(res.idVenta);
        });
      }
    },
    willClose: () => {
      if (isSuccess) {
        // Cambiar a tab Factura Electronica y refrescar tablas (sin reload de pagina)
        try {
          const targetTab = (res.factura === 'electronica') ? '#nav-historial' : '#nav-historialfisica';
          const btn = document.querySelector('button[data-bs-target="' + targetTab + '"]');
          if (btn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
            new bootstrap.Tab(btn).show();
          }
          // Limpiar carrito visible (productos en la tabla nueva venta)
          document.querySelectorAll('#tblNuevaVenta tbody tr').forEach(tr => tr.remove());
          // Reset campos
          if (document.getElementById('descuento')) document.getElementById('descuento').value = '';
          if (document.getElementById('totalPagar')) document.getElementById('totalPagar').value = '0';
          if (document.getElementById('totalPagarHidden')) document.getElementById('totalPagarHidden').value = '0';
          // Refrescar DataTables
          if (typeof tblHistorialFE !== "undefined" && tblHistorialFE) tblHistorialFE.ajax.reload(null, false);
          if (typeof tblHistorialfisica !== 'undefined' && tblHistorialfisica) tblHistorialfisica.ajax.reload(null, false);
        } catch (e) { console.warn('post-factura ui sync:', e); }
      }
    }
  });
}

// === Persistencia de la pestaña activa entre recargas ===
(function() {
  const KEY = 'venta_active_tab';

  // Guardar al hacer click en cualquier tab
  document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(btn => {
    btn.addEventListener('shown.bs.tab', e => {
      try { localStorage.setItem(KEY, e.target.getAttribute('data-bs-target')); } catch (e) {}
    });
  });

  // Restaurar al cargar
  document.addEventListener('DOMContentLoaded', () => {
    let target = null;
    try { target = localStorage.getItem(KEY); } catch (e) {}
    if (target) {
      const btn = document.querySelector('button[data-bs-toggle="tab"][data-bs-target="' + target + '"]');
      if (btn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
        try { new bootstrap.Tab(btn).show(); } catch (e) {}
      }
    }
  });
})();
