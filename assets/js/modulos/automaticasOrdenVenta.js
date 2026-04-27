//const tblAutomaticas = document.querySelector('#tblAutomaticas')
//const tblAutomaticasOrdenVenta = document.querySelector('#tblAutomaticasOrdenVenta')

let divLoading = document.querySelector("#divLoading");
const btnAccionO = document.querySelector('#btnAccionO');


document.addEventListener('DOMContentLoaded', function () {
  // cargar productos de localStorage





  // Null-safety: cuando el corte OV esta cerrado, btnAccionO y la tabla no
  // existen en el DOM. Sin estas guardas, btnAccionO.addEventListener tiraba
  // TypeError y abortaba TODO el resto del DOMContentLoaded (DataTable nunca
  // se inicializaba, etc.) — la pagina parecia rota.
  if (!btnAccionO) {
    return;
  }

  btnAccionO.addEventListener('click', function () {
    programada();
  })


  tblAutomaticasOrdenVenta = $('#tblAutomaticasOrdenVenta').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
    ajax: {
      url: base_url + 'automaticas/listarOrdenVenta',
      dataSrc: ''
    },
    columns: [      
      { data: 'nombre' },
      { data: 'total' },
      { data: 'tributario' },
      { data: 'estado' },
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
  })
})



function programada(){
  // metodopago = 'CREDITO';
  divLoading.style.display = "flex";
    let valor = 2;
  const url = base_url + 'automaticas/registrarVentaAutomatico/' + valor;
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
      alertaPersonalizada(res.type, res.msg)
      if (res.type == 'success') {
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
                let or = res.OrdenesVenta
                let fa = res.FacturasElectronicas
                const ruta = base_url + 'automaticas/ReporteContratos?orden='+ or + '&' +'facturas='+ fa
                window.open(ruta, '_blank')
              } else if (result.isDenied) {
                divLoading.style.display = "none";

                const ruta = base_url + 'facturaelectronica/public/archivos/ride/' + res.ClaveAcceso
                window.open(ruta, '_blank')
              }
              window.location.reload()
            })
          }, 2000)
        
      }else{
        divLoading.style.display = "none";

      }
    }else{
      divLoading.style.display = "none";

    }
  }
}



function anularVentaElectronica (idVenta) {
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

function envioCorreoElectronica (idVenta) {
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

function envioSriElectronica (idVenta) {
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

