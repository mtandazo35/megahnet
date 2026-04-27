let divLoading = document.querySelector("#divLoading");

const tblNuevaTipoPago = document.querySelector('#tblNuevaTipoPago tbody');

let tblAbonos,tblCompletados;
const idCredito = document.querySelector('#idCredito');
const cliente = document.querySelector('#buscarCliente');
const telefonoCliente = document.querySelector('#telefonoCliente');
const direccionCliente = document.querySelector('#direccionCliente');

const errorCliente = document.querySelector('#errorCliente');

const abonado = document.querySelector('#abonado');
const restante = document.querySelector('#restante');
const fecha = document.querySelector('#fecha');
const monto_total = document.querySelector('#monto_total');
//const monto_abonar = document.querySelector('#monto_abonar');
//const btnAccion = document.querySelector('#btnAccion');

const total = document.querySelector('#total');


const nuevoAbono = document.querySelector('#nuevoAbono');
const modalAbono = new bootstrap.Modal('#modalAbono');


const monto_total_varios = document.querySelector('#monto_total_varios');
const monto_abonar_varios = document.querySelector('#monto_abonar_varios');
const btnAccionVarios = document.querySelector('#btnAccionVarios');
const codigoPagoVarios = document.querySelector('#codigoPagoVarios');
const tipoPagoVarios = document.querySelector('#tipoPagoVarios');

const contratos = document.querySelector('#contratos');

const modalVariosAbonos = new bootstrap.Modal('#modalVariosAbonos');

const AbonarFacturas = document.querySelector('#AbonarFacturas');



//para filtro por rango de fechas
//const desde = document.querySelector('#desde');
//const hasta = document.querySelector('#hasta');

document.addEventListener('DOMContentLoaded', function () {
    //cargar datos con el plugin datatables
    mostrarProductoTipoPago()
    
    tblHistorial = $('#tblHistorial').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'creditos/listar',
      type: 'POST'
        },
        columns: [
            { data: 'nombre' },
            { data: 'ch' },
            { data: 'monto' },
            { data: 'abonado' },
            { data: 'restante' },
            { data: 'estado' },
            { data: 'notif' },
            { data: 'electronica' },
            { data: 'ordenventa' },
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
      //cargar datos con el plugin datatables



$('#nav-abonos-tab').on('shown.bs.tab', function () {
    if (!$.fn.DataTable.isDataTable('#tblAbonos')) {
        tblAbonos = $('#tblAbonos').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
            ajax: {
                url: base_url + 'creditos/listarAbonos',
      type: 'POST'
            },
            columns: [
                { data: 'cliente' },
                { data: 'fecha' },
                { data: 'abono' },
                { data: 'credito' },
                { data: 'acciones' }
            ],
            language: {
                url: base_url + 'assets/js/espanol.json'
            },
            dom,
            buttons,
            responsive: true,
            order: [[1, 'desc']],
        });
    }
});


$('#nav-completados-tab').on('shown.bs.tab', function () {
    if (!$.fn.DataTable.isDataTable('#tblCompletados')) {
        tblCompletados = $('#tblCompletados').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
            ajax: {
                url: base_url + 'creditos/listarCompletados',
      type: 'POST'
            },
            columns: [
                { data: 'nombre' },
                { data: 'monto' },
                { data: 'abonado' },
                { data: 'estado' },
                { data: 'electronica' },
                { data: 'ordenventa' },
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
    }
});


   /* tblAbonos = $('#tblAbonos').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'creditos/listarAbonos',
      type: 'POST'
        },
        columns: [
            { data: 'cliente' },
            { data: 'fecha' },
            { data: 'abono' },
            { data: 'credito' },
            { data: 'acciones' }

        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[1, 'desc']],
    });
    tblCompletados = $('#tblCompletados').DataTable({
    processing: true,
    serverSide: true,
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'creditos/listarCompletados',
      type: 'POST'
        },
        columns: [
            { data: 'nombre' },
            { data: 'monto' },
            { data: 'abonado' },
            { data: 'estado' },
            { data: 'electronica' },
            { data: 'ordenventa' },
            { data: 'acciones' }
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[0, 'desc']],
    });*/

    //autocomplete clientes
    $("#buscarCliente").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: base_url + 'creditos/buscar',
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function (data) {
                    response(data);
                    if (data.length > 0) {
                        errorCliente.textContent = '';
                    } else {
                        errorCliente.textContent = 'NO EXISTE EL CLIENTE CON ESE NOMBRE';
                    }
                }
            });
        },
        delay: 300, // espera 300ms después de dejar de escribir
        minLength: 2,
        select: function (event, ui) {
            telefonoCliente.value = ui.item.telefono;
            direccionCliente.innerHTML = ui.item.direccion;
            idCredito.value = ui.item.id;
            abonado.value = ui.item.abonado;
            restante.value = ui.item.restante;
            monto_total.value = ui.item.monto;
            fecha.value = ui.item.fecha;

            if(ui.item.anticipos != 0){
                agregarTipoPago(6, 'ANTICIPOS',ui.item.anticipos)
                return false

            }
            inputBuscarNombreTipoPago.focus();
            //monto_abonar.focus();
        }
    });

    //levantar modal para agregar abono
    nuevoAbono.addEventListener('click', function () {
        idCredito.value = '';
        telefonoCliente.value = '';
        cliente.value = '';
        direccionCliente.innerHTML = '';
        abonado.value = '';
        restante.value = '';
        monto_total.value = '';
        fecha.value = '';
       // monto_abonar.value = '';
       total.value = '';

      // mostrarProductoTipoPago()
      localStorage.removeItem('posTipoPago');
      tblNuevaTipoPago.innerHTML = `<tr>
      <td colspan="4" class="text-center">SIN TIPO PAGOS</td>
  </tr>`;
        modalAbono.show();

    })

    btnAccion.addEventListener('click', function () {
        let filas = document.querySelectorAll('#tblNuevaTipoPago tr').length;
        if (filas < 2) {
            alertaPersonalizada('warning', 'SIN TIPOS PAGOS');
            return;
        }else if (idCredito.value == '' && cliente.value == '') {
            alertaPersonalizada('warning', 'BUSCA Y SELECCIONA CLIENTE');
            return;
        //} 
        //else if (parseFloat(restante.value) < parseFloat(total.value)) {
          //  alertaPersonalizada('warning', 'INGRESE MENOR A RESTANTE');
            //return;
        }else {
            const url = base_url + 'creditos/registrarAbono';
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('POST', url, true);
            //Enviar Datos
            http.send(JSON.stringify({
                tipoPagos: listaCarrito,
                restante: restante.value,
                idCredito: idCredito.value,
                total: total.value


            }));
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertaPersonalizada(res.type, res.msg);
                    if (res.type == 'success') {
                        localStorage.removeItem('posTipoPago');
                        modalAbono.hide();
                        tblHistorial.ajax.reload();
                        setTimeout(() => {
                            const ruta = base_url + 'creditos/reporte/' + idCredito.value;
                            const whatsapp = res.whatsapp;

                           // window.open(ruta, '_blank');
                            window.open(whatsapp, '_blank');
                            tblHistorial.ajax.reload();
                            window.location.reload()

                        }, 2000);
                    }
                }
            }
        }
    })

    AbonarFacturas.addEventListener('click', function () {

        localStorage.removeItem('posTipoPago');
        monto_abonar_varios.value = '';
        codigoPagoVarios.value = '';
        document.getElementById("tipoPagoVarios").options.item(0).selected = 'selected';
        modalVariosAbonos.show();

        var allChecked = $('input[type=checkbox]:checked').map(function () {
            return $(this).prop('id');
        });
        //alert(allChecked.get());
        // console.log(allChecked.get());
        var allCheckedValue = $('input[type=checkbox]:checked').map(function () {
            return $(this).prop('value');
        });
        //alert(allCheckedValue.get());
        var array2 = []
        array2 = allChecked.get()
        var array1 = []
        array1 = allCheckedValue.get()

        let total = 0
        for (let i = 0; i < array1.length; i++) {
            total += parseFloat(array1[i]);

        }
        //console.log(total);
        document.querySelector('#monto_total_varios').value = parseFloat(total).toFixed(2)
        document.querySelector('#contratos').value = array2


    })
    btnAccionVarios.addEventListener('click', function () {
        localStorage.removeItem('posTipoPago');

        modalVariosAbonos.show();


        var allChecked = $('input[type=checkbox]:checked').map(function () {
            return $(this).prop('id');
        });

        var allCheckedValue = $('input[type=checkbox]:checked').map(function () {
            return $(this).prop('value');
        });
       // alert(allChecked.get());
        // console.log(allChecked.get());
      //  alert(allCheckedValue.get());
   


        if (allChecked.get() == '') {
            alertaPersonalizada('warning', 'SELECCIONE CREDITOS');
        } else if(monto_abonar_varios.value != monto_total_varios.value){
            alertaPersonalizada('warning', 'EL ABONO DEBE SER IGUAL AL MONTO TOTAL');

        }else   {
            const url = base_url + 'creditos/registrarAbonoVarios';
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('POST', url, true);
            //Enviar Datos
            http.send(JSON.stringify({


                variosCreditos: allChecked.get(),
                variosValores: allCheckedValue.get(),
                abonarVarios: monto_abonar_varios.value,
                tipoPagoVarios: tipoPagoVarios.value,
                codigoPagoVarios: codigoPagoVarios.value


            }));
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    alertaPersonalizada(res.type, res.msg);
                    if (res.type == 'success') {
                        modalVariosAbonos.hide();
                        tblHistorial.ajax.reload();
                        setTimeout(() => {
                            const ruta = base_url + 'creditos/reporte/' + res.id;
                            const whatsapp = res.whatsapp;

                            // window.open(ruta, '_blank');
                             window.open(whatsapp, '_blank');
                            tblHistorial.ajax.reload();
                        }, 2000);
                    }
                }
            }
        }
    })


  

    //filtro rango de fechas
    /*desde.addEventListener('change', function () {
        tblCreditos.draw();
    })
    hasta.addEventListener('change', function () {
        tblCreditos.draw();
    })

    $.fn.dataTable.ext.search.push(
        function (settings, data, dataIndex) {
            var FilterStart = desde.value;
            var FilterEnd = hasta.value;
            var DataTableStart = data[0].trim();
            var DataTableEnd = data[0].trim();
            if (FilterStart == '' || FilterEnd == '') {
                return true;
            }
            if (DataTableStart >= FilterStart && DataTableEnd <= FilterEnd) {
                return true;
            } else {
                return false;
            }

        });*/
})

//cargar productos
function mostrarProductoTipoPago() {
    if (localStorage.getItem(nombreKey) != null) {
        const url = base_url + 'productos/mostrarDatosTipoPago';
        //hacer una instancia del objeto XMLHttpRequest 
       // localStorage.removeItem('posTipoPago');

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
                if (res.tipoPago.length > 0) {
                    res.tipoPago.forEach(tipoPago => {
                        html += `<tr>
                        <td>
                                        <input style="width:175px"; type="text" class="form-control inputDescripcion" data-id="${tipoPago.id}" value="${tipoPago.nombre}">
                                        </td>
                                         <td>
                                        <input style="width:175px"; type="text" class="form-control inputCodigoComprobante" data-id="${tipoPago.id}" value="${tipoPago.codigoComprobante}">
                                        </td>

                                        <td>                   
                                     <input style="width:100px"; type="number" class="form-control inputPrecio" data-id="${tipoPago.id}" value="${tipoPago.precio}" ${tipoPago.disabled}>
                                                </td>   
                                        
                                        
                                        <td><button class="btn btn-danger btnEliminar" data-id="${tipoPago.id}" type="button" ${tipoPago.none}><i class="fas fa-trash"></i></button></td>
                                    </tr>`;
                    });
                    tblNuevaTipoPago.innerHTML = html;
                    total.value = res.total;
                    btnEliminarTipoPago();
                    agregarPrecio()
                    agregarDescripcion()
                    agregarCodigoComprobante();
                } else {
                    tblNuevaTipoPago.innerHTML = '';
                }
            }
        }
    } else {
        tblNuevaTipoPago.innerHTML = `<tr>
            <td colspan="4" class="text-center">SIN TIPO PAGOS</td>
        </tr>`;
    }
}
function eliminarAbono(idAbono) {
    const url = base_url + 'creditos/eliminarAbono/' + idAbono;

    eliminarRegistros2(url, tblAbonos,tblHistorial);

}



// Handler para boton "Notificar pagado/pendiente" en tabla creditos
// Flujo: 1) preview (mensaje renderizado) -> 2) confirmar envio -> 3) enviar
$(document).on('click', '.btn-notif-credito', function(){
    var btn = this;
    var id = btn.dataset.id;
    var tipo = btn.dataset.tipo;
    if (!id) return;
    btn.disabled = true;
    var prev = btn.innerHTML;
    btn.innerHTML = '<i class="bx bx-loader bx-spin"></i>';

    // Paso 1: pedir preview del mensaje sin enviar
    fetch(base_url + 'creditos/notificarCliente/' + id + '?preview=1', { method: 'POST', credentials: 'same-origin' })
        .then(function(res){ return res.json(); })
        .then(function(d){
            btn.disabled = false; btn.innerHTML = prev;
            if (!d.ok) {
                Swal.fire({icon:'error', title:'No se puede preparar', text: d.msg || ''});
                return;
            }
            var titulo = (d.tipo === 'pagado') ? 'Confirmar pago al cliente' : 'Recordatorio de pago al cliente';
            // Mostrar el mensaje renderizado en un bloque tipo whatsapp
            Swal.fire({
                title: titulo,
                html: '<div style="text-align:left;font-size:.85rem;color:#374151;margin-bottom:.65rem;">' +
                      '<i class="bx bxl-whatsapp" style="color:#16a34a;font-size:18px;vertical-align:-3px;"></i> ' +
                      'Se enviara a <b>+' + d.telefono + '</b>' +
                      '</div>' +
                      '<div style="background:#dcfce7;color:#0f172a;padding:.85rem 1rem;border-radius:10px;text-align:left;white-space:pre-wrap;font-family:ui-monospace,Menlo,monospace;font-size:.78rem;line-height:1.45;max-height:280px;overflow:auto;border:1px solid #bbf7d0;">' +
                      escapeHtmlPlain(d.mensaje || '') +
                      '</div>' +
                      '<div style="text-align:right;font-size:.7rem;color:#6b7280;margin-top:.4rem;">' +
                      '<a href="' + base_url + 'notificaciones#nav-plantillas" target="_blank">Editar plantilla</a>' +
                      '</div>',
                showCancelButton: true,
                confirmButtonText: '<i class="bx bx-paper-plane me-1"></i>Enviar',
                cancelButtonText: 'Cancelar',
                width: 520
            }).then(function(r){
                if (!r.isConfirmed) return;
                btn.disabled = true; btn.innerHTML = '<i class="bx bx-loader bx-spin"></i>';
                fetch(base_url + 'creditos/notificarCliente/' + id, { method: 'POST', credentials: 'same-origin' })
                    .then(function(res){ return res.json(); })
                    .then(function(dd){
                        if (dd.ok) {
                            Swal.fire({icon:'success', title:'Mensaje enviado', text:'WhatsApp a +' + dd.telefono, timer:2200});
                        } else {
                            Swal.fire({icon:'error', title:'No se pudo enviar', text: dd.msg || ('HTTP ' + (dd.http || '?'))});
                        }
                    })
                    .catch(function(e){ Swal.fire({icon:'error', title:'Error', text:e.message}); })
                    .finally(function(){ btn.disabled = false; btn.innerHTML = prev; });
            });
        })
        .catch(function(e){
            btn.disabled = false; btn.innerHTML = prev;
            Swal.fire({icon:'error', title:'Error', text:e.message});
        });
});

function escapeHtmlPlain(s){
    return String(s||'').replace(/[&<>"']/g, function(c){
        return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c];
    });
}
