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
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'IMPRIMIR ORDEN VENTA?',
                                text: 'Generando',
                                showCancelButton: true,
                                confirmButtonText: 'Orden Venta',
                            }).then((result) => {
                                /* Read more about isConfirmed, isDenied below */
                                divLoading.style.display = "none";

                              //  hideLoader();
                                if (result.isConfirmed) {
                                    const ruta = base_url + 'ordenventa/reporte/factura/' + res.idOrdenVenta;

                                    window.open(ruta, '_blank');
                                } 
                                /*else if (result.isDenied) {
                                    
                                    const ruta = base_url + 'ordenventa/reporte/ticked/' + res.idOrdenVenta;
                                    window.open(ruta, '_blank');
                                }*/
                                window.location.reload();
                            })

                        }, 2000);
                    }else{
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
        const url = base_url + 'ordenventa/anular/' + idOrdenVenta
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