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
  divLoading.style.display = 'none';

  const lote = 25;
  const maxIter = 500;
  let acum = { totalProcesados: 0, totalFallidos: 0, fallidos: [] };
  let pollHandle = null;

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

  pollHandle = setInterval(function(){
    fetch(base_url + 'automaticas/estadoBatch', { cache: 'no-store' })
      .then(function(r){ return r.json(); })
      .then(function(j){
        var tot = j.total || 0, emit = j.emitidas || 0, err = j.errores || 0, pend = j.pendientes || 0;
        var done = emit + err;
        var pct = tot > 0 ? Math.round((done * 100) / tot) : 0;
        var bar = document.getElementById('progressBar');
        if (bar) { bar.style.width = pct + '%'; bar.textContent = pct + '%'; }
        var $e = document.getElementById('pgEmit'); if ($e) $e.textContent = emit;
        var $r = document.getElementById('pgErr');  if ($r) $r.textContent = err;
        var $p = document.getElementById('pgPend'); if ($p) $p.textContent = pend;
        var $t = document.getElementById('pgTot');  if ($t) $t.textContent = tot;
      })
      .catch(function(){});
  }, 3000);

  function llamarLote(i){
    if (i >= maxIter) { return finalizar(); }
    fetch(base_url + 'automaticas/registrarVentaAutomatico/1?limite=' + lote, {
      method: 'POST', body: JSON.stringify({}), cache: 'no-store'
    })
      .then(function(r){ return r.json(); })
      .then(function(res){
        if (res.lock_held) {
          // Otro proceso ya esta facturando. Detener este bucle y avisar al operador.
          if (pollHandle) clearInterval(pollHandle);
          Swal.close();
          setTimeout(function(){
            Swal.fire({ icon: 'warning', title: 'Facturacion en curso',
              text: 'Otro usuario o pestana ya esta procesando la facturacion. Espera a que termine antes de reintentar.',
              confirmButtonText: 'Cerrar' }).then(function(){ window.location.reload(); });
          }, 200);
          return;
        }
        acum.totalProcesados += (res.totalProcesados || 0);
        acum.totalFallidos   += (res.totalFallidos   || 0);
        if (res.fallidos && res.fallidos.length) acum.fallidos = acum.fallidos.concat(res.fallidos);
        if ((res.totalProcesados || 0) === 0 && (res.totalFallidos || 0) === 0) return finalizar();
        llamarLote(i + 1);
      })
      .catch(function(err){ console.error('Error lote', i, err); finalizar(); });
  }

  function finalizar(){
    if (pollHandle) clearInterval(pollHandle);
    Swal.close();
    nombreKey = 'posContrato';
    localStorage.removeItem(nombreKey);
    setTimeout(function(){ mostrarResumen(); }, 200);
  }

  function mostrarResumen(){
    if (acum.totalFallidos > 0) {
      var todosFallaron = (acum.totalProcesados || 0) === 0;
      var htmlF = '<p class="text-muted small mb-2">' + (acum.totalProcesados || 0) + ' procesado(s), <b class="text-danger">' + acum.totalFallidos + '</b> fallaron.</p>';
      htmlF += '<div style="max-height:380px; overflow:auto;"><table class="table table-sm table-striped table-hover mb-0"><thead class="table-light"><tr><th>#</th><th>Cliente</th><th>Error</th></tr></thead><tbody>';
      (acum.fallidos || []).forEach(function(f){
        htmlF += '<tr><td>' + (f.idContrato || '?') + '</td><td>' + ((f.cliente || '').replace(/[<>]/g, '')) + '</td><td class="small text-danger">' + ((f.error || '').replace(/[<>]/g, '').substring(0, 200)) + '</td></tr>';
      });
      htmlF += '</tbody></table></div>';
      Swal.fire({
        icon: todosFallaron ? 'error' : 'warning',
        title: todosFallaron ? 'Facturacion FALLIDA: ningun contrato procesado' : 'Facturacion completada con errores',
        html: htmlF, width: 800, showCancelButton: false, confirmButtonText: 'Cerrar'
      }).then(function(){ window.location.reload(); });
    } else {
      Swal.fire({
        icon: 'success',
        title: '¡Facturación completada!',
        html: '<b>' + acum.totalProcesados + '</b> contratos facturados con éxito.',
        confirmButtonText: 'Cerrar'
      }).then(function(){ window.location.reload(); });
    }
  }

  llamarLote(0);
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

