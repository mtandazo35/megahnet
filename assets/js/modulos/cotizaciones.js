let divLoading = document.querySelector("#divLoading");

const tblNuevaCotizacion = document.querySelector('#tblNuevaCotizacion tbody');

const idCliente = document.querySelector('#idCliente');
const telefonoCliente = document.querySelector('#telefonoCliente');
const direccionCliente = document.querySelector('#direccionCliente');

const errorCliente = document.querySelector('#errorCliente');


const descuento = document.querySelector('#descuento');
const metodo = document.querySelector('#metodo');
const validez = document.querySelector('#validez');

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

    //completar cotizacion
    btnAccion.addEventListener('click', function () {
        let filas = document.querySelectorAll('#tblNuevaCotizacion tr').length;
        if (filas < 2) {
            alertaPersonalizada('warning', 'CARRITO VACIO');
            return;
        } else if (idCliente.value == '') {
            alertaPersonalizada('warning', 'EL CLIENTE ES REQUERIDO');
            return;
        } else if (metodo.value == '') {
            alertaPersonalizada('warning', 'EL METODO ES REQUERIDO');
            return;
        } else if (validez.value == '') {
            alertaPersonalizada('warning', 'LA VALIDEZ ES REQUERIDO');
            return;
        } else {
            const url = base_url + 'cotizaciones/registrarCotizacion';
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
                validez: validez.value
            }));
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    console.log(this.responseText);
                    alertaPersonalizada(res.type, res.msg);
                    if (res.type == 'success') {
                        localStorage.removeItem(nombreKey);
                        setTimeout(() => {
                            Swal.fire({
                                title: 'IMPRIMIR COTIZACION?',
                                text: 'Generando',
                                icon: 'success',
                                showDenyButton: true,
                                showCancelButton: true,
                                confirmButtonText: 'Ticked',
                                denyButtonText: `Cotizacion`,
                            }).then((result) => {
                                /* Read more about isConfirmed, isDenied below */
                                if (result.isConfirmed) {
                                    const ruta = base_url + 'cotizaciones/reporte/ticked/' + res.idCotizacion;
                                    window.open(ruta, '_blank');
                                } else if (result.isDenied) {
                                    const ruta = base_url + 'cotizaciones/reporte/factura/' + res.idCotizacion;
                                    window.open(ruta, '_blank');
                                }
                                window.location.reload();
                            })

                        }, 2000);
                    }
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
            url: base_url + 'cotizaciones/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'nombre' },

            { data: 'fecha' },
            { data: 'hora' },
            { data: 'total' },
            { data: 'validez' },
            { data: 'metodo' },
            { data: 'acciones' }
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[0, 'desc']],
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
                    <td> <input style="width:800px"; type="text" class="form-control inputDescripcion" data-id="${producto.id}" value="${producto.nombre}"></td>                       
                 <td><input style="width:125px"; type="number" class="form-control inputPrecio" data-id="${producto.id}" value="${producto.precio_venta}">      </td>
                    <td><input style="width:100px"; type="number" class="form-control inputCantidad" data-id="${producto.id}" value="${producto.cantidad}">    </td>
                            <td>${producto.subTotalVenta}</td>
                            <td><button class="btn btn-danger btnEliminar" data-id="${producto.id}" type="button"><i class="fas fa-trash"></i></button></td>
                        </tr>`;
                    });
                    tblNuevaCotizacion.innerHTML = html;
                    totalPagar.value = res.totalVenta;
                    btnEliminarProducto();
                    agregarCantidad();
                    agregarPrecioVenta()
                    agregarDescripcion()
                } else {
                    tblNuevaCotizacion.innerHTML = '';
                }
            }
        }
    } else {
        tblNuevaCotizacion.innerHTML = `<tr>
            <td colspan="4" class="text-center">CARRITO VACIO</td>
        </tr>`;
    }
}

function verReporte(idCotizacion) {
    Swal.fire({
        title: 'REEMPRIMIR COTIZACION?',
        icon: 'success',
        txt: 'Generando',
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: 'Ticked',
        denyButtonText: `Cotizacion`,
    }).then((result) => {
        /* Read more about isConfirmed, isDenied below */
        if (result.isConfirmed) {
            const ruta = base_url + 'cotizaciones/reporte/ticked/' + idCotizacion;
            window.open(ruta, '_blank');
        } else if (result.isDenied) {
            const ruta = base_url + 'cotizaciones/reporte/factura/' + idCotizacion;
            window.open(ruta, '_blank');
        }
    })
}