let divLoading = document.querySelector("#divLoading");

const tblNuevaRetencion = document.querySelector('#tblNuevaRetencion tbody')

const idProveedor = document.querySelector('#idProveedor');
const telefonoProveedor = document.querySelector('#telefonoProveedor')
const correoProveedor = document.querySelector('#correoProveedor')
const errorProveedor = document.querySelector('#errorProveedor')

const fechaEmisionFactura = document.querySelector('#fechaEmisionFactura')
const numeroComprobante = document.querySelector('#numeroComprobante')
const totalFactura = document.querySelector('#totalFactura')

document.addEventListener('DOMContentLoaded', function () {
  // cargar productos de localStorage
  mostrarRetenciones()
  // autocomplete clientes
  $("#buscarProveedor").autocomplete({
    source: function (request, response) {
        $.ajax({
            url: base_url + 'proveedor/buscar',
            dataType: "json",
            data: {
                term: request.term
            },
            success: function (data) {
                response(data);
                if(data.length > 0){
                    errorProveedor.textContent= '';
                }else{
                    errorProveedor.textContent='NO HAY PROVEEDOR CON ESE NOMBRE';
                }
            }
        });
    },
    minLength: 2,
    select: function (event, ui) {
        telefonoProveedor.value = ui.item.telefono;
        correoProveedor.value = ui.item.correo;
        idProveedor.value = ui.item.id;
    }
});





  // completar venta
  btnAccion.addEventListener('click', function () {
    let filas = document.querySelectorAll('#tblNuevaRetencion tr').length
    if (filas < 2) {
      alertaPersonalizada('warning', 'CARRITO VACIO')
      return     
    } else if (fechaEmisionFactura.value == '') {
      alertaPersonalizada('warning', 'LA FECHA DE EMISION ES REQUERIDO')
      return
    } else if (numeroComprobante.value == '') {
      alertaPersonalizada('warning', 'EL NUMERO COMPROBANTE ES REQUERIDO')
      return
    } else if (totalFactura.value == '') {
      alertaPersonalizada('warning', 'EL TOTAL DE FACTURA ES REQUERIDO')
      return
    }else if (idProveedor.value == '') {
      alertaPersonalizada('warning', 'EL PROVEEDOR ES REQUERIDO')
      return
    }else {
      divLoading.style.display = "flex";

      const url = base_url + 'retenciones/registrarRetencion'
      // hacer una instancia del objeto XMLHttpRequest 
      const http = new XMLHttpRequest()
      // Abrir una Conexion - POST - GET
      http.open('POST', url, true)
      // Enviar Datos
      http.send(JSON.stringify({
        retenciones: listaCarrito,
        idProveedor: idProveedor.value,
        fechaEmision: fechaEmisionFactura.value,
        numeroComprobante: numeroComprobante.value,
        totalFactura: totalFactura.value



      }))
      // verificar estados
      http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
          const res = JSON.parse(this.responseText)
          console.log(this.responseText)
          alertaPersonalizada(res.type, res.msg)
          if (res.type == 'success') {          
              localStorage.removeItem(nombreKey)
              setTimeout(() => {
                Swal.fire({
                  icon: 'success',
                  title: 'IMPRIMIR RETENCION ELECTRONICA?',
                  showDenyButton: true,
                  showCancelButton: true,
                  denyButtonText: `Retencion`
                }).then((result) => {
                  /* Read more about isConfirmed, isDenied below */
                  divLoading.style.display = "none";

                   if (result.isDenied) {
                    const ruta = base_url + 'facturaelectronica/public/archivos/Retenciones/ride/' + res.ClaveAcceso + '.pdf'
                    window.open(ruta, '_blank')
                  }
                  window.location.reload()
                })
              }, 2000)
            
          }
          divLoading.style.display = "none";

        }
        divLoading.style.display = "none";

      }
    }
  })

 

  // cargar datos con el plugin datatables Factura Electronica
  tblHistorial = $('#tblHistorial').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
    ajax: {
      url: base_url + 'retenciones/listarRetenciones',
      dataSrc: ''
    },
    columns: [
      { data: 'cliente' },
	  { data: 'fecha' },
      { data: 'retencion' },      
      { data: 'claveAcceso' },
      { data: 'estado' },
      { data: 'totalFactura' },
      { data: 'numFactura' },
      { data: 'autorizacion' },
      { data: 'acciones' }
    ],
    language: {
      url: base_url + 'assets/js/espanol.json'
    },
    dom,
    buttons,
    responsive: true,
    order: [[1, 'desc']]
  })
})





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

function envioCorreoRetencion(idRetencion) {
  Swal.fire({
    title: 'Esta seguro de enviar la Retencion Electronica?',
    text: 'Se enviara la Retencion Electronica!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Enviar!'
  }).then((result) => {
    if (result.isConfirmed) {
      divLoading.style.display = "flex";

      const url = base_url + 'retenciones/envioRetencionElectronica/' + idRetencion
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
            divLoading.style.display = "none";

            tblHistorial.ajax.reload()
          }else{
            divLoading.style.display = "none";

          }
        }
        divLoading.style.display = "none";

      }
    }
  })
}

function envioSriRetencion(idRetencion) {
  Swal.fire({
    title: 'Esta seguro de reenviar la Retencion Electronica al SRI ?',
    text: 'Reenviar Retencion Electronica al SRI!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Si, Enviar!'
  }).then((result) => {
    if (result.isConfirmed) {
      divLoading.style.display = "flex";

      const url = base_url + 'retenciones/envioSriElectronica/' + idRetencion
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
            divLoading.style.display = "none";

            tblHistorial.ajax.reload()
          }else{
            divLoading.style.display = "none";

          }
        }
        divLoading.style.display = "none";

      }
    }
  })
}

//cargar productos
function mostrarRetenciones() {
  if (localStorage.getItem(nombreKey) != null) {
      const url = base_url + 'productos/mostrarDatosRetenciones';
      //hacer una instancia del objeto XMLHttpRequest 
      const http = new XMLHttpRequest();
      //Abrir una Conexion - POST - GET
      http.open('POST', url, true);
      //Enviar Datos
      http.send(JSON.stringify(listaCarrito));
      //verificar estados
      http.onreadystatechange = function () {
          if (this.readyState == 4 && this.status == 200) {
              const res = JSON.parse(this.responseText);
              let html = '';
              if (res.Retenciones.length > 0) {
                  res.Retenciones.forEach(Retenciones => {
                      html += `<tr>
                      <td>
                                      <input style="width:175px"; type="text" class="form-control " disabled data-id="${Retenciones.id}" value="${Retenciones.tipo}">
                                      </td>
                                       <td>
                                      <input style="width:175px"; type="text" class="form-control inputCodigo" disabled data-id="${Retenciones.id}" value="${Retenciones.codigo}">
                                      </td>
                                       <td>
                                      <input style="width:175px"; type="number" class="form-control inputBaseImponible" data-id="${Retenciones.id}" value="${Retenciones.baseImponible}">
                                      </td>

                                      <td>                   
                                   <input style="width:100px"; type="text" class="form-control inputValorRetenido" disabled data-id="${Retenciones.id}" value="${Retenciones.valorRetenido}">
                                              </td>   
                                      
                                      
                                      <td><button class="btn btn-danger btnEliminar" data-id="${Retenciones.id}" type="button"><i class="fas fa-trash"></i></button></td>
                                  </tr>`;
                  });
                  tblNuevaRetencion.innerHTML = html;
                  totalPagar.value =   (totalFactura.value - res.totalRetenido).toFixed(2);

                  btnEliminarRetencion();
                  agregarBaseImponible();
                  //agregarDescripcion()
                  //agregarCodigoComprobante();
              } else {
                tblNuevaRetencion.innerHTML = '';
              }
          }
      }
  } else {
    tblNuevaRetencion.innerHTML = '';

    /*tblNuevaRetencion.innerHTML = `<tr>
          <td colspan="4" class="text-center">SIN RETENCIONES</td>
      </tr>`;*/
  }
}

// ============================================================================
// Autocompletar datos al escribir el numero de comprobante (busca en compras)
// ============================================================================
(function(){
    var inp = document.getElementById('numeroComprobante');
    if (!inp) return;
    var timer = null;
    function autocompletarPorComprobante() {
        var raw = inp.value.trim();
        var clean = raw.replace(/[^0-9]/g, '');
        if (clean.length < 6) return;
        fetch(base_url + 'retenciones/buscarPorComprobante?numero=' + encodeURIComponent(raw), {credentials:'same-origin'})
            .then(function(r){ return r.json(); })
            .then(function(d){
                if (!d || !d.ok) return;
                var idProv = document.getElementById('idProveedor');
                var inpProv = document.getElementById('buscarProveedor');
                var inpTel  = document.getElementById('telefonoProveedor');
                var inpCor  = document.getElementById('correoProveedor');
                var inpFec  = document.getElementById('fechaEmisionFactura');
                var inpTot  = document.getElementById('totalFactura');
                var spErr   = document.getElementById('errorProveedor');
                if (idProv)  idProv.value  = d.idProveedor;
                if (inpProv) inpProv.value = d.nombreProveedor;
                if (inpTel)  inpTel.value  = d.telefono || '';
                if (inpCor)  inpCor.value  = d.correo   || '';
                if (inpFec && !inpFec.value) inpFec.value = d.fechaEmision;
                if (inpTot)  { inpTot.value = d.totalFactura; inpTot.dispatchEvent(new Event('keyup')); }
                if (spErr)   spErr.textContent = '';
                if (typeof mostrarRetenciones === 'function') mostrarRetenciones();
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon:'success', title:'Datos cargados',
                        text:'Compra ' + d.serieCompra + ' - ' + d.nombreProveedor,
                        timer:1500, showConfirmButton:false
                    });
                }
            })
            .catch(function(){});
    }
    inp.addEventListener('input', function(){
        clearTimeout(timer);
        timer = setTimeout(autocompletarPorComprobante, 400);
    });
    inp.addEventListener('blur', autocompletarPorComprobante);
})();
