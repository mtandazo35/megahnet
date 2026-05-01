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
  }
})



function programada() {
  // metodopago = 'CREDITO';
  divLoading.style.display = "flex";

  const url = base_url + 'automaticas/registrarVentaAutomatico/' + 1
  // hacer una instancia del objeto XMLHttpRequest 
  const http = new XMLHttpRequest()
  // Abrir una Conexion - POST - GET
  http.open('POST', url, true)
  // Enviar Datos
  http.send(JSON.stringify({
    //credito: metodopago

  }))
  // verificar estados
  http.onreadystatechange = function () {
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
        Swal.fire({
          icon: 'warning',
          title: 'Facturacion completada con errores',
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

