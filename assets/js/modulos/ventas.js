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
    stateDuration: 60 * 60 * 24 * 7, // 7 dias: un estado guardado con pagina grande caduca solo
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
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

  // cargar datos con el plugin datatables Notas de Credito
  if (document.getElementById('tblHistorialNC')) {
    var tblHistorialNC = $('#tblHistorialNC').DataTable({
      deferRender: true,
      stateSave: true,
      stateDuration: 60 * 60 * 24 * 7, // 7 dias: un estado guardado con pagina grande caduca solo
      colReorder: true,
      pageLength: 10,
      lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
      ajax: {
        url: base_url + 'notaCredito/listarElectronica',
        dataSrc: function(res){
          if (typeof res.url !== 'undefined' && res.url) { window.location.href = res.url; return []; }
          return res;
        }
      },
      columns: [
        { data: 'cliente' },
        { data: 'secuencial' },
        { data: 'fecha' },
        { data: 'claveacceso' },
        { data: 'estado' },
        { data: 'total_modificar' },
        { data: 'autorizacion' },
        { data: 'acciones' }
      ],
      language: { url: base_url + 'assets/js/espanol.json' },
      dom,
      buttons,
      responsive: true,
      order: [[2, 'desc']]
    });
  }

  // cargar datos con el plugin datatables Factura Electronica
  // Invalidar state previo si la firma de columnas cambio (evita que el listado quede vacio
  // por estado guardado con menos/mas columnas).
  try {
    var __sigKey = 'DataTables_tblHistorialFE_colSig';
    var __sigActual = 'fe_v7'; // bump cuando cambien columnas / limpiar filtros guardados (v7: serverSide)
    if (localStorage.getItem(__sigKey) !== __sigActual) {
      Object.keys(localStorage).filter(function(k){ return k.indexOf('tblHistorialFE') !== -1; }).forEach(function(k){ localStorage.removeItem(k); });
      localStorage.setItem(__sigKey, __sigActual);
    }
  } catch(e){}

  // Filtros por SRI y Correo: ahora se resuelven en el servidor (viajan en cada peticion
  // como filtroSri/filtroCorreo). Se recuerdan en localStorage para que el dropdown
  // muestre siempre el filtro realmente aplicado.
  var fSri = document.getElementById('filtroSri');
  var fCor = document.getElementById('filtroCorreo');
  try {
    if (fSri) fSri.value = localStorage.getItem('tblHistorialFE_filtroSri') || '';
    if (fCor) fCor.value = localStorage.getItem('tblHistorialFE_filtroCorreo') || '';
  } catch(e){}

  tblHistorialFE = $('#tblHistorialFE').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    stateSave: true,
    stateDuration: 60 * 60 * 24 * 7, // 7 dias: un estado guardado con pagina grande caduca solo
    colReorder: true,
    pageLength: 10,
    // Sin "Todos": el servidor limita cada pagina a 200 filas.
    lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
    searchDelay: 400,
    stateLoadParams: function(settings, data){
      // Si el estado guardado tiene distinto numero de columnas que el actual, descartarlo.
      try {
        var actualCols = settings.aoColumns ? settings.aoColumns.length : 0;
        var savedCols  = data && data.columns ? data.columns.length : 0;
        if (actualCols && savedCols && actualCols !== savedCols) { return false; }
      } catch(e){}
      var v=[5,10,20,50,100]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; }
    },

    ajax: {
      url: base_url + 'ventas/listarElectronica',
      type: 'POST',
      data: function(d){
        d.filtroSri    = fSri ? (fSri.value || '') : '';
        d.filtroCorreo = fCor ? (fCor.value || '') : '';
      }
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
      { data: 'autorizacion' },
      { data: 'correoBadge', defaultContent: '' },
      { data: 'duplicadaBadge', defaultContent: '' }
      
    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    responsive: true,
    // El orden lo fija el servidor (factura mas reciente primero); sin ordenar por columna.
    ordering: false
  })

  // Cambio de filtro: guardar y volver a la pagina 1 (draw() envia los filtros via ajax.data).
  if (fSri) fSri.addEventListener('change', function(){
    try { localStorage.setItem('tblHistorialFE_filtroSri', this.value || ''); } catch(e){}
    tblHistorialFE.draw();
  });
  if (fCor) fCor.addEventListener('change', function(){
    try { localStorage.setItem('tblHistorialFE_filtroCorreo', this.value || ''); } catch(e){}
    tblHistorialFE.draw();
  });

  // === Reenvio masivo de correos pendientes ===
  function refrescarBadgePendientes(){
    fetch(base_url + 'ventas/contarPendientesCorreo', { cache: 'no-store' })
      .then(function(r){ return r.json(); })
      .then(function(j){
        var b = document.getElementById('badgePendientes');
        if (b) b.textContent = (j.pendientes != null) ? j.pendientes : '--';
      })
      .catch(function(){});
  }
  refrescarBadgePendientes();
  setInterval(refrescarBadgePendientes, 30000);

  var btnReen = document.getElementById('btnReenviarCorreos');
  if (btnReen) btnReen.addEventListener('click', function(){
    Swal.fire({
      title: 'Reenviar correos pendientes',
      html: '<p class="small text-muted mb-0">Se enviara el RIDE+XML a los clientes con correo válido cuya factura ya está autorizada por el SRI y aún no recibió notificación.</p>',
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#198754',
      confirmButtonText: 'Sí, reenviar',
      cancelButtonText: 'Cancelar'
    }).then(function(r){
      if (!r.isConfirmed) return;
      ejecutarReenvio();
    });
  });

  // === Reenvio masivo SRI ===
  function refrescarBadgePendientesSri(){
    fetch(base_url + 'ventas/contarPendientesSri', { cache: 'no-store' })
      .then(function(r){ return r.json(); })
      .then(function(j){
        var b = document.getElementById('badgePendientesSri');
        if (b) b.textContent = (j.pendientes != null) ? j.pendientes : '--';
      })
      .catch(function(){});
  }
  refrescarBadgePendientesSri();
  setInterval(refrescarBadgePendientesSri, 30000);

  var btnSri = document.getElementById('btnReenviarSri');
  if (btnSri) btnSri.addEventListener('click', function(){
    Swal.fire({
      title: 'Reenviar al SRI las pendientes',
      html: '<p class="small text-muted mb-0">Se reintentara firmar y autorizar al SRI las facturas del mes que estan EN PROCESO o NO AUTORIZADO. Procesa en lotes de 5 (cada lote toma ~30s por la conexion al SRI).</p>',
      icon: 'question', showCancelButton: true,
      confirmButtonColor: '#f59e0b', confirmButtonText: 'Si, reenviar', cancelButtonText: 'Cancelar'
    }).then(function(r){ if (r.isConfirmed) ejecutarReenvioSri(); });
  });

  function ejecutarReenvioSri(){
    var lote = 5, maxIter = 200;
    var acum = { ok: 0, fail: 0, fallidos: [] };
    Swal.fire({
      title: 'Reenviando al SRI…',
      html: '<div class="mb-2 text-muted small">No cierres esta pestaña hasta terminar.</div>'
          + '<div class="d-flex justify-content-between small"><span>Autorizadas: <b id="sriOk" class="text-success">0</b></span>'
          + '<span>Fallidas: <b id="sriErr" class="text-danger">0</b></span></div>',
      allowOutsideClick: false, allowEscapeKey: false,
      showConfirmButton: false, didOpen: function(){ Swal.showLoading(); }
    });
    function siguiente(i){
      if (i >= maxIter) return finalizarSri();
      fetch(base_url + 'ventas/reenviarSriMasivo?limite=' + lote, { cache: 'no-store' })
        .then(function(r){ return r.json(); })
        .then(function(res){
          acum.ok   += (res.procesadas || 0);
          acum.fail += (res.fallidas   || 0);
          if (res.fallidos && res.fallidos.length) acum.fallidos = acum.fallidos.concat(res.fallidos);
          var elOk = document.getElementById('sriOk');  if (elOk) elOk.textContent = acum.ok;
          var elEr = document.getElementById('sriErr'); if (elEr) elEr.textContent = acum.fail;
          if ((res.lote || 0) === 0) return finalizarSri();
          siguiente(i + 1);
        })
        .catch(function(err){ console.error(err); finalizarSri(); });
    }
    function finalizarSri(){
      Swal.close();
      setTimeout(function(){
        var icon = acum.fail > 0 ? 'warning' : 'success';
        var title = (acum.ok === 0 && acum.fail === 0) ? 'No habia facturas pendientes' : 'Reenvio SRI finalizado';
        var html = '<p>Autorizadas: <b class="text-success">' + acum.ok + '</b><br>Fallidas: <b class="text-danger">' + acum.fail + '</b></p>';
        if (acum.fail > 0) {
          html += '<div style="max-height:280px;overflow:auto;"><table class="table table-sm table-striped"><thead><tr><th>Factura</th><th>Cliente</th><th>Error</th></tr></thead><tbody>';
          acum.fallidos.forEach(function(f){
            html += '<tr><td>' + (f.orden_no || '?') + '</td><td>' + ((f.cliente || '').replace(/[<>]/g, '')) + '</td><td class="small text-danger">' + ((f.error || '').replace(/[<>]/g, '').substring(0, 200)) + '</td></tr>';
          });
          html += '</tbody></table></div>';
        }
        Swal.fire({ icon: icon, title: title, html: html, width: 700, confirmButtonText: 'Cerrar' })
          .then(function(){
            refrescarBadgePendientesSri();
            try { tblHistorialFE.ajax.reload(null, false); } catch(e) {}
          });
      }, 200);
    }
    siguiente(0);
  }

  function ejecutarReenvio(){
    var lote = 5, maxIter = 1000;
    var acum = { ok: 0, fail: 0, fallidos: [] };
    Swal.fire({
      title: 'Reenviando correos…',
      html: '<div class="mb-2 text-muted small">No cierres esta pestaña hasta terminar.</div>'
          + '<div class="d-flex justify-content-between small"><span>Enviados: <b id="reOk" class="text-success">0</b></span>'
          + '<span>Fallidos: <b id="reErr" class="text-danger">0</b></span></div>',
      allowOutsideClick: false, allowEscapeKey: false,
      showConfirmButton: false, showCancelButton: false,
      didOpen: function(){ Swal.showLoading(); }
    });
    function siguiente(i){
      if (i >= maxIter) return finalizar();
      fetch(base_url + 'ventas/reenviarCorreoMasivo?limite=' + lote, { cache: 'no-store' })
        .then(function(r){ return r.json(); })
        .then(function(res){
          acum.ok   += (res.procesadas || 0);
          acum.fail += (res.fallidas   || 0);
          if (res.fallidos && res.fallidos.length) acum.fallidos = acum.fallidos.concat(res.fallidos);
          var elOk = document.getElementById('reOk');  if (elOk) elOk.textContent = acum.ok;
          var elErr = document.getElementById('reErr'); if (elErr) elErr.textContent = acum.fail;
          if ((res.lote || 0) === 0) return finalizar();
          siguiente(i + 1);
        })
        .catch(function(err){ console.error(err); finalizar(); });
    }
    function finalizar(){
      Swal.close();
      setTimeout(function(){
        var icon = acum.fail > 0 ? 'warning' : 'success';
        var title = (acum.ok === 0 && acum.fail === 0) ? 'No habia correos pendientes' : 'Reenvio finalizado';
        var html = '<p>Enviados: <b class="text-success">' + acum.ok + '</b><br>Fallidos: <b class="text-danger">' + acum.fail + '</b></p>';
        if (acum.fail > 0) {
          html += '<div style="max-height:280px;overflow:auto;"><table class="table table-sm table-striped"><thead><tr><th>Factura</th><th>Cliente</th><th>Error</th></tr></thead><tbody>';
          acum.fallidos.forEach(function(f){
            html += '<tr><td>' + (f.orden_no || '?') + '</td><td>' + ((f.cliente || '').replace(/[<>]/g, '')) + '</td><td class="small text-danger">' + ((f.error || '').replace(/[<>]/g, '').substring(0, 200)) + '</td></tr>';
          });
          html += '</tbody></table></div>';
        }
        Swal.fire({ icon: icon, title: title, html: html, width: 700, confirmButtonText: 'Cerrar' })
          .then(function(){
            refrescarBadgePendientes();
            try { tblHistorialFE.ajax.reload(null, false); } catch(e) {}
          });
      }, 200);
    }
    siguiente(0);
  }

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
      Swal.fire({
        title: 'Anulando venta...',
        html: 'Por favor espera, esto puede tomar unos segundos.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
      });
      const url = base_url + 'ventas/anular/' + idVenta
      const http = new XMLHttpRequest()
      http.open('GET', url, true)
      http.send()
      http.onreadystatechange = function () {
        if (this.readyState == 4) {
          Swal.close();
          if (this.status == 200) {
            const res = JSON.parse(this.responseText)
            alertaPersonalizada(res.type, res.msg)
            if (res.type == 'success') {
              tblHistorialfisica.ajax.reload()
            }
          } else {
            alertaPersonalizada('error', 'Error al anular la venta');
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
      Swal.fire({
        title: 'Anulando factura electronica...',
        html: 'Por favor espera, esto puede tomar unos segundos.',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
      });
      const url = base_url + 'ventas/anularElectronica/' + idVenta
      const http = new XMLHttpRequest()
      http.open('GET', url, true)
      http.send()
      http.onreadystatechange = function () {
        if (this.readyState == 4) {
          Swal.close();
          if (this.status == 200) {
            const res = JSON.parse(this.responseText)
            alertaPersonalizada(res.type, res.msg)
            if (res.type == 'success') {
              tblHistorialFE.ajax.reload()
            }
          } else {
            alertaPersonalizada('error', 'Error al anular la factura electronica');
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
