//const tblAutomaticas = document.querySelector('#tblAutomaticas')
//const tblAutomaticasOrdenVenta = document.querySelector('#tblAutomaticasOrdenVenta')

let divLoading = document.querySelector("#divLoading");
let btnAccion = document.querySelector('#btnAccion');


document.addEventListener('DOMContentLoaded', function () {
  // El boton "Completar" y la tabla solo existen cuando el corte F esta abierto
  // (estadoCorteF.total == 0). Cuando el corte ya esta cerrado, la vista pinta
  // un mensaje "corte cerrado" sin esos elementos. Por eso son null-safe:
  // sin guardas, btnAccion.addEventListener tiraba TypeError y rompia
  // TODO el resto del DOMContentLoaded (DataTable nunca se inicializaba,
  // listeners nunca se registraban, la pagina parecia "rota").

  if (btnAccion) {
    btnAccion.addEventListener('click', function () {
      programada();
    });
  }

  if (document.querySelector('#tblAutomaticas')) {
    tblAutomaticas = $('#tblAutomaticas').DataTable({
      deferRender: true,
      stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
      ajax: {
        url: base_url + 'automaticas/listarElectronica',
        dataSrc: ''
      },
      columns: [
        { data: 'nombre' },
        { data: 'total' },
        { data: 'tributario' },
        { data: 'estado' },
        { data: 'estadoEmision' },
        { data: 'id' },
        { data: 'fecha' }
      ],
      language: {
        url: base_url + 'assets/js/espanol.json'
      },
      dom,
      buttons,
      responsive: true,
      order: [[0, 'desc']]
    });

    // Filtro por columna 'Emisión' (col index 4)
    var fSel = document.getElementById('filtroEmision');
    if (fSel) {
      fSel.addEventListener('change', function () {
        var val = this.value;
        var col = tblAutomaticas.column(4);
        // Buscar el texto del badge sin regex special chars
        col.search(val ? val : '', false, false).draw();
      });
    }
  }
})



function programada() {
  divLoading.style.display = "none"; // ocultamos el loading viejo, usamos Swal con progreso

  // Modal de progreso con polling al backend cada 3s
  Swal.fire({
    title: 'Procesando facturación...',
    html: '<div class="mb-2 text-muted small">No cierres esta pestaña hasta terminar.</div>'
        + '<div id="progressBarWrap" class="progress mb-2" style="height: 20px;">'
        + '<div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%;">0%</div>'
        + '</div>'
        + '<div class="d-flex justify-content-between small">'
        + '<span>Emitidas: <b id="pgEmit" class="text-success">0</b></span>'
        + '<span>Errores: <b id="pgErr" class="text-danger">0</b></span>'
        + '<span>Pendientes: <b id="pgPend" class="text-warning">--</b></span>'
        + '<span>Total: <b id="pgTot">--</b></span>'
        + '</div>',
    allowOutsideClick: false,
    allowEscapeKey: false,
    showConfirmButton: false,
    showCancelButton: false,
    didOpen: function(){ Swal.showLoading(); }
  });

  // Polling de estado
  var pollHandle = setInterval(function(){
    fetch(base_url + 'automaticas/estadoBatch', { cache: 'no-store' })
      .then(function(r){ return r.json(); })
      .then(function(j){
        var tot  = j.total || 0;
        var emit = j.emitidas || 0;
        var err  = j.errores || 0;
        var pend = j.pendientes || 0;
        var pct = tot > 0 ? Math.round(((emit + err) * 100) / tot) : 0;
        var bar = document.getElementById('progressBar');
        if (bar) { bar.style.width = pct + '%'; bar.textContent = pct + '%'; }
        var  = document.getElementById('pgEmit');
        var   = document.getElementById('pgErr');
        var  = document.getElementById('pgPend');
        var   = document.getElementById('pgTot');
        if () .textContent = emit;
        if ()  .textContent  = err;
        if () .textContent = pend;
        if ()  .textContent  = tot;
      })
      .catch(function(){ /* silencioso */ });
  }, 3000);

  const url = base_url + 'automaticas/registrarVentaAutomatico/' + 1
  const http = new XMLHttpRequest()
  http.open('POST', url, true)
  http.send(JSON.stringify({}))
  http.onreadystatechange = function () {
    if (this.readyState == 4) {
      clearInterval(pollHandle);
      Swal.close();
    }
    if (this.readyState == 4 && this.status == 200) {
      const res = JSON.parse(this.responseText)
      nombreKey = 'posContrato';
      console.log(this.responseText)
      const tieneFallidos = (res.totalFallidos || 0) > 0;
      if (tieneFallidos) {
        // Construir tabla de fallidos
        let htmlF = '<p class="text-muted small mb-2">' + (res.totalProcesados || 0) + ' procesado(s), <b class="text-danger">' + res.totalFallidos + '</b> fallaron.</p>';
        htmlF += '<div style="max-height:380px; overflow:auto;"><table class="table table-sm table-striped table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>Cliente</th><th>Error</th></tr></thead><tbody>';
        (res.fallidos || []).forEach(function(f){
          htmlF += '<tr><td>' + (f.idContrato || '?') + '</td><td>' + ((f.cliente || '').replace(/[<>]/g, '')) + '</td><td class="small text-danger">' + ((f.error || '').replace(/[<>]/g, '').substring(0, 200)) + '</td></tr>';
        });
        htmlF += '</tbody></table></div>';
        const todosFallaron = (res.totalProcesados || 0) === 0;
        Swal.fire({
          icon: todosFallaron ? 'error' : 'warning',
          title: todosFallaron ? 'Facturacion FALLIDA: ningun contrato procesado' : 'Facturacion completada con errores',
          html: htmlF,
          width: 800,
          showCancelButton: false,
          confirmButtonText: 'Cerrar'
        }).then(function(){
          divLoading.style.display = "none";
          localStorage.removeItem(nombreKey);
          window.location.reload();
        });
      } else if (res.type == 'success') {
        localStorage.removeItem(nombreKey)
        setTimeout(() => {
          Swal.fire({
            icon: 'success',
            title: 'IMPRIMIR REPORTES ELECTRONICÓS?',
            showCancelButton: true,
            confirmButtonText: 'Reportes',
          }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
              divLoading.style.display = "none";

            } else if (result.isDenied) {
              divLoading.style.display = "none";

            }
            window.location.reload()
          })
        }, 2000)

      } else {
        divLoading.style.display = "none";

      }
    } else {
      divLoading.style.display = "none";

    }
  }
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
            tblHistorial.ajax.reload()
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
            tblHistorial.ajax.reload()
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
            tblHistorial.ajax.reload()
          }
        }
      }
    }
  })
}

