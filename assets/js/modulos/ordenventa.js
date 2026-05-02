let divLoading = document.querySelector("#divLoading");

const tblNuevaOrdenVenta = document.querySelector('#tblNuevaOrdenVenta tbody');

const idCliente = document.querySelector('#idCliente');
const telefonoCliente = document.querySelector('#telefonoCliente');
const direccionCliente = document.querySelector('#direccionCliente');

const errorCliente = document.querySelector('#errorCliente');
const tipopago = document.querySelector('#tipopago')


const cargarDatosExcel = document.querySelector('#cargarDatosExcel');
const btnCargar = document.querySelector('#btnCargar');
const excel = document.querySelector('#excel');
const errorExcel = document.querySelector('#errorExcel');


const descuento = document.querySelector('#descuento');
const metodo = document.querySelector('#metodo');

document.addEventListener('DOMContentLoaded', function () {
    //cargar productos de localStorage
    mostrarProducto();

    //autocomplete clientes
    $("#buscarCliente").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: base_url + 'clientes/buscar',
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function (data) {
                    response(data);
                    if (data.length > 0) {
                        errorCliente.textContent = '';
                    } else {
                        errorCliente.textContent = 'NO HAY CLIENTE CON ESE NOMBRE';
                    }
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            telefonoCliente.value = ui.item.telefono;
            direccionCliente.innerHTML = ui.item.direccion;
            idCliente.value = ui.item.id;
        }
    });
    cargarDatosExcel.addEventListener('submit', function (e) {
        e.preventDefault();
        // limpiarCampos();
        // editorDireccion.setData('');
        var archivo = document.getElementById('excel');
        // var archivoRuta = archivo.value;
        // console.log(archivo.files[0].type);
    
        //alert(archivo.files);
        if (archivo.files[0] == null || archivo.files[0] == '') {
          alertaPersonalizada('error', 'SELECCIONE UN ARCHIVO EXCEL');
    
        } else if (archivo.files[0].type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
          alertaPersonalizada('error', 'SOLO SE ADMITE FORMATO EXCEL .XLSX');
    
          // errorExcel.textContent = 'SELECCIONES UN ARCHIVO EXCEL';
        }
        else {
          const url = base_url + 'ordenventa/registrarExcel';
          insertarRegistros(url, this, tblHistorial, btnCargar, false);
        }
    
      })

    //completar cotizacion
    btnAccion.addEventListener('click', function () {
        let filas = document.querySelectorAll('#tblNuevaOrdenVenta tr').length;
        if (filas < 2) {
            alertaPersonalizada('warning', 'CARRITO VACIO');
            return;
        } else if (idCliente.value == '') {
            alertaPersonalizada('warning', 'EL CLIENTE ES REQUERIDO');
            return;
        } else if (metodo.value == '') {
            alertaPersonalizada('warning', 'EL METODO ES REQUERIDO');
            return;
        } else {
            divLoading.style.display = "flex";

           // showLoader();

            const url = base_url + 'ordenventa/registrarOrdenVenta';
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('POST', url, true);
            //Enviar Datos
            http.send(JSON.stringify({
                productos: listaCarrito,
                idCliente: idCliente.value,
                metodo: metodo.value,
                descuento: descuento.value,
                tipoPago: tipopago.value
            }));
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                   // console.log(this.responseText);
                    alertaPersonalizada(res.type, res.msg);
                    if (res.type == 'success') {
                        localStorage.removeItem(nombreKey);
                        divLoading.style.display = "none";
                        // Mostrar modal embebido con preview del PDF + WhatsApp send
                        mostrarOrdenCreadaModal(res.idOrdenVenta);
                    } else {
                        divLoading.style.display = "none";
                    }
                }else{
                    divLoading.style.display = "none";
          
                  }
            }
        }

    })
   
    //cargar datos con el plugin datatables
    tblHistorial = $('#tblHistorial').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'ordenventa/listar',
            dataSrc: ''
        },
        columns: [
		  { data: 'acciones' },
            { data: 'nombre' },
            { data: 'fecha' },
            { data: 'serie' },

            { data: 'total' },
            { data: 'metodo' },
            { data: 'estado' }

          
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[3, 'desc']],
    });

})

//cargar productos
function mostrarProducto() {
    if (localStorage.getItem(nombreKey) != null) {
        // Limpiar entradas legacy de tipoPago (TRANSFERENCIA, etc.) que se pudieron colar
        var antes = listaCarrito.length;
        listaCarrito = listaCarrito.filter(function(it){ return !it.codigoComprobante; });
        if (listaCarrito.length !== antes) localStorage.setItem(nombreKey, JSON.stringify(listaCarrito));
        if (listaCarrito.length === 0) {
            tblNuevaOrdenVenta.innerHTML = '';
            if (typeof totalPagar !== 'undefined' && totalPagar) totalPagar.value = '0.00';
            return;
        }
        const url = base_url + 'productos/mostrarDatos';
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
                if (res.productos.length > 0) {
                    res.productos.forEach(producto => {
                        html += `<tr class="ordenventa-row">
                                        <td class="cell-producto">
                                            <input type="text" class="form-control form-control-sm inputDescripcion" data-id="${producto.id}" value="${producto.nombre}">
                                            <div class="row-mini d-md-none mt-1">
                                                <small class="text-muted me-2">Precio:</small>
                                                <input type="number" class="form-control form-control-sm inputPrecio d-inline-block" style="width:90px;" data-id="${producto.id}" value="${producto.precio_venta}">
                                                <small class="text-muted ms-2 me-2">Cant:</small>
                                                <input type="number" class="form-control form-control-sm inputCantidad d-inline-block" style="width:80px;" data-id="${producto.id}" value="${producto.cantidad}">
                                                <small class="text-muted ms-2 me-1">Sub:</small>
                                                <span class="fw-semibold">$${producto.subTotalVenta}</span>
                                            </div>
                                        </td>
                                        <td class="cell-precio d-none d-md-table-cell text-end">
                                            <input type="number" class="form-control form-control-sm inputPrecio text-end" style="max-width:110px;display:inline-block;" data-id="${producto.id}" value="${producto.precio_venta}">
                                        </td>
                                        <td class="cell-cantidad d-none d-md-table-cell text-center">
                                            <input type="number" class="form-control form-control-sm inputCantidad text-center" style="max-width:90px;display:inline-block;" data-id="${producto.id}" value="${producto.cantidad}">
                                        </td>
                                        <td class="cell-subtotal d-none d-md-table-cell text-end fw-semibold">$${producto.subTotalVenta}</td>
                                        <td class="text-center"><button class="btn btn-danger btn-sm btnEliminar" data-id="${producto.id}" type="button" title="Quitar"><i class="fas fa-trash"></i></button></td>
                                    </tr>`;
                    });
                    tblNuevaOrdenVenta.innerHTML = html;
                    totalPagar.value = res.totalVenta;
                    btnEliminarProducto();
                    agregarCantidad();
                    agregarPrecioVenta()
                    agregarDescripcion()
                } else {
                    tblNuevaOrdenVenta.innerHTML = '';
                }
            }
        }
    } else {
        tblNuevaOrdenVenta.innerHTML = '';

        /*tblNuevaOrdenVenta.innerHTML = `<tr>
            <td colspan="4" class="text-center">CARRITO VACIO</td>
        </tr>`;*/
    }
}
function anularOrdenVenta (idOrdenVenta) {
    Swal.fire({
      title: 'Esta seguro de Anular la Orden Venta?',
      text: 'El stock de los productos cambiarán!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Anular!'
    }).then((result) => {
      if (result.isConfirmed) {
        Swal.fire({
          title: 'Anulando orden de venta...',
          html: 'Por favor espera, esto puede tomar unos segundos.',
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => Swal.showLoading()
        });
        const url = base_url + 'ordenventa/anular/' + idOrdenVenta
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
                  tblHistorial.ajax.reload()
              }
            } else {
              alertaPersonalizada('error', 'Error al anular la orden venta');
            }
          }
        }
      }
    })
  }
function verReporte(idordenVenta) {
    Swal.fire({
        icon: 'success',
        title: 'REEMPRIMIR ORDEN VENTA?',
        text: 'Generando',
        showCancelButton: true,
        confirmButtonText: 'Orden Venta',
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            const ruta = base_url + 'ordenventa/reporte/factura/' + idordenVenta;
            window.open(ruta, '_blank');
        }
        /* else if (result.isDenied) {
            const ruta = base_url + 'ordenventa/reporte/ticked/' + idordenVenta;
            window.open(ruta, '_blank');
        }*/
    })
}
function envioCorreoOrdenVenta(idOrden) {
    Swal.fire({
      title: 'Esta seguro de enviar la Orden Venta?',
      text: 'Se enviara la Orden Venta!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'Si, Enviar!'
    }).then((result) => {
      if (result.isConfirmed) {
        const url = base_url + 'ordenventa/envioOrdenVenta/' + idOrden
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
  function showLoader() {
    const loader = document.getElementById('loader');
    loader.style.display = 'flex';
  }
  
  // Función para ocultar el loader
  function hideLoader() {
    const loader = document.getElementById('loader');
    loader.style.display = 'none';
  }

// ============================================================================
// Override robusto del btn eliminar para Orden Venta:
//   - Si el id no esta en listaCarrito, intenta eliminar por nombre (de la fila)
//   - Re-renderiza la tabla manualmente si el backend deja un huerfano
// ============================================================================
(function(){
    if (typeof tblNuevaOrdenVenta === 'undefined' || !tblNuevaOrdenVenta) return;
    // Delegacion sobre el tbody — funciona aunque btnEliminarProducto re-bind cambie
    tblNuevaOrdenVenta.addEventListener('click', function(e){
        var btn = e.target.closest('.btnEliminar');
        if (!btn) return;
        var id = btn.getAttribute('data-id');
        var row = btn.closest('tr');
        var nombreInput = row ? row.querySelector('.inputDescripcion') : null;
        var nombre = nombreInput ? nombreInput.value : '';
        // Eliminar de listaCarrito por id O por nombre (cubre entradas sin id valido)
        if (typeof listaCarrito !== 'undefined' && Array.isArray(listaCarrito)) {
            var antes = listaCarrito.length;
            listaCarrito = listaCarrito.filter(function(it){
                if (String(it.id) === String(id)) return false;
                if (nombre && it.nombre === nombre) return false;
                return true;
            });
            if (listaCarrito.length !== antes) {
                if (typeof nombreKey !== 'undefined') {
                    localStorage.setItem(nombreKey, JSON.stringify(listaCarrito));
                }
                // Quitar la fila visualmente al instante (no esperar al backend)
                if (row) row.remove();
                // Re-render para recalcular totales
                if (typeof mostrarProducto === 'function') {
                    setTimeout(mostrarProducto, 50);
                }
            }
        }
    });
})();

// Boton "Limpiar carrito" — wipe total del listaCarrito (boton ahora en HTML del view)
(function(){
    function bind() {
        var btn = document.getElementById('btnLimpiarCarrito');
        if (!btn || btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', function(){
            var vacio = (typeof listaCarrito === 'undefined' || !Array.isArray(listaCarrito) || listaCarrito.length === 0);
            if (vacio) {
                Swal.fire({icon:'info', title:'Carrito vacio', text:'No hay nada que limpiar.', timer:1500, showConfirmButton:false});
                return;
            }
            Swal.fire({
                icon: 'warning', title: 'Limpiar carrito?',
                text: 'Se quitaran todos los productos.',
                showCancelButton: true, confirmButtonText: 'Si, limpiar', cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545'
            }).then(function(r){
                if (!r.isConfirmed) return;
                if (typeof nombreKey !== 'undefined') localStorage.removeItem(nombreKey);
                if (typeof listaCarrito !== 'undefined') listaCarrito = [];
                if (typeof tblNuevaOrdenVenta !== 'undefined') tblNuevaOrdenVenta.innerHTML = '';
                var tp = document.getElementById('totalPagar');
                if (tp) tp.value = '0.00';
                Swal.fire({icon:'success', title:'Carrito vaciado', timer:1200, showConfirmButton:false});
            });
        });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', bind);
    else bind();
})();


// ============================================================================
// Modal: muestra PDF embebido tras crear orden + envio por WhatsApp
// ============================================================================
function mostrarOrdenCreadaModal(idOrden) {
    var existing = document.getElementById('modalOrdenCreada');
    if (existing) existing.remove();

    var telCliente = '';
    var telInput = document.getElementById('telefonoCliente');
    if (telInput) telCliente = (telInput.value || '').replace(/[^0-9]/g, '');

    var pdfUrl = base_url + 'ordenventa/reporte/factura/' + idOrden;
    var modalHtml = `
    <div class="modal fade" id="modalOrdenCreada" tabindex="-1" data-bs-backdrop="static">
      <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title"><i class="bx bx-check-circle me-2"></i>Orden de venta #${idOrden} creada</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-0">
            <iframe src="${pdfUrl}" style="width:100%; height:65vh; border:0; display:block;"></iframe>
          </div>
          <div class="modal-footer flex-wrap gap-2 align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2 flex-wrap">
              <i class="bx bxl-whatsapp text-success" style="font-size:24px;"></i>
              <input type="tel" id="ocTel" class="form-control form-control-sm" placeholder="Telefono"
                value="${telCliente}" style="width:150px;" inputmode="numeric" maxlength="13">
              <button class="btn btn-success btn-sm" type="button" id="ocBtnWa">
                <i class="bx bx-paper-plane me-1"></i>Enviar WhatsApp
              </button>
            </div>
            <div class="d-flex gap-2">
              <a class="btn btn-outline-primary btn-sm" href="${pdfUrl}" target="_blank" download>
                <i class="bx bx-download me-1"></i>Descargar PDF
              </a>
              <button class="btn btn-secondary btn-sm" type="button" id="ocBtnCerrar">
                <i class="bx bx-plus me-1"></i>Nueva orden
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHtml);
    var modalEl = document.getElementById('modalOrdenCreada');
    var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    var btnCerrar = document.getElementById('ocBtnCerrar');
    btnCerrar.addEventListener('click', function(){
        modal.hide();
        window.location.href = base_url + 'ordenventa';
    });
    modalEl.addEventListener('hidden.bs.modal', function(){
        modalEl.remove();
    });


    var btnWa = document.getElementById('ocBtnWa');
    if (btnWa) btnWa.addEventListener('click', function(){
        var tel = document.getElementById('ocTel').value.trim().replace(/[^0-9]/g, '');
        if (!tel) {
            Swal.fire({icon:'warning', title:'Telefono requerido', text:'Escribe un numero para enviar.'});
            return;
        }
        btnWa.disabled = true;
        var prev = btnWa.innerHTML;
        btnWa.innerHTML = '<i class="bx bx-loader bx-spin me-1"></i>Enviando...';
        // URL al PDF GUARDADO (publico, sin auth) — el endpoint reporte/factura
        // requiere sesion PHP que la WA API no tiene.
        var pdfUrlAbs = base_url + 'facturaelectronica/public/archivos/facturables/Facturable_' + idOrden + '.pdf';
        fetch(base_url + 'notificaciones/waEnviarPrueba', {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({
                number: tel,
                message: 'Adjunto orden de venta #' + idOrden,
                mediaUrl: pdfUrlAbs,
                mediaPath: 'facturaelectronica/public/archivos/facturables/Facturable_' + idOrden + '.pdf',
                regenerateOrdenId: idOrden,
                fileName: 'OrdenVenta_' + idOrden + '.pdf'
            })
        }).then(function(r){ return r.text(); }).then(function(text){
            var d;
            try { d = JSON.parse(text); }
            catch(e){
                Swal.fire({icon:'error', title:'Respuesta invalida', html:'<small>Cloudflare bloqueo el endpoint. Considera whitelist.</small>'});
                throw e;
            }
            if (d.ok) {
                var telN = tel; if (telN.length===10 && telN[0]==='0') telN='593'+telN.substring(1); else if (telN.length===9) telN='593'+telN;
                Swal.fire({icon:'success', title:'PDF enviado', text:'WhatsApp a +' + telN, timer:2200, showConfirmButton:false});
            } else {
                Swal.fire({icon:'error', title:'No se pudo enviar', text: d.msg || ('HTTP ' + (d.http || '?'))});
            }
        }).catch(function(e){
            // already handled in catch above
        }).finally(function(){
            btnWa.disabled = false;
            btnWa.innerHTML = prev;
        });
    });
}