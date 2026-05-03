let divLoading = document.querySelector("#divLoading");

let tblInventario;
const btnBuscar = document.querySelector('#btnBuscar');
const btnReporte = document.querySelector('#btnReporte');
const btnAjuste = document.querySelector('#btnAjuste');
const mes = document.querySelector('#mes');

const modalAjuste = new bootstrap.Modal('#modalAjuste');
const buscarCodigoAjuste = document.querySelector('#buscarCodigoAjuste');
const buscarNombreAjuste = document.querySelector('#buscarNombreAjuste');
const barcodeAjuste = document.querySelector('#barcodeAjuste');
const nombreAjuste = document.querySelector('#nombreAjuste');
const containerCodigoAjuste = document.querySelector('#containerCodigoAjuste');
const containerNombreAjuste = document.querySelector('#containerNombreAjuste');

const cantidadAjuste = document.querySelector('#cantidadAjuste');
const idProductoAjuste = document.querySelector('#idProductoAjuste');
const btnProcesar = document.querySelector('#btnProcesar');

//####### Kardex ######
const inputBuscarCodigo = document.querySelector('#buscarProductoCodigo');
const inputBuscarNombre = document.querySelector('#buscarProductoNombre');
const barcode = document.querySelector('#barcode');
const nombre = document.querySelector('#nombre');
const containerCodigo = document.querySelector('#containerCodigo');
const containerNombre = document.querySelector('#containerNombre');

document.addEventListener('DOMContentLoaded', function () {
    //cargar todo los datos
    cargarInventario(base_url + 'inventarios/listarMovimientos');

    //filtro
    btnBuscar.addEventListener('click', function () {
        if (mes.value == '') {
            alertaPersonalizada('warning', 'SELECCIONA EL MES');
        } else {
            const url = base_url + 'inventarios/listarMovimientos/' + mes.value;
            cargarInventario(url);
        }
    });
    //reporte de inventario
    btnReporte.addEventListener('click', function () {
        if (mes.value == '') {
            window.open(base_url + 'inventarios/reporte', '_blank')
        } else {
            const url = base_url + 'inventarios/reporte/' + mes.value;
            window.open(url, '_blank');
        }
    });
    //modal ajuste de inventario
    btnAjuste.addEventListener('click', function () {
        idProductoAjuste.value = '';
        buscarCodigoAjuste.value = '';
        buscarNombreAjuste.value = '';
        cantidadAjuste.value = '';
        modalAjuste.show();
    });

    //mostrar input para la busqueda por nombre
    nombreAjuste.addEventListener('click', function () {
        containerCodigoAjuste.classList.add('d-none');
        containerNombreAjuste.classList.remove('d-none');
        buscarNombreAjuste.value = '';
        idProductoAjuste.value = '';
        buscarNombreAjuste.focus();
    })
    //mostrar input para la busqueda por codigo
    barcodeAjuste.addEventListener('click', function () {
        containerNombreAjuste.classList.add('d-none');
        containerCodigoAjuste.classList.remove('d-none');
        buscarCodigoAjuste.value = '';
        idProductoAjuste.value = '';
        buscarCodigoAjuste.focus();
    })

    buscarCodigoAjuste.addEventListener('keyup', function (e) {
        if (e.keyCode === 13) {
            productoPorCodigo(e.target.value);
        }
        return;
    })

    //autocomplete productos
    $("#buscarNombreAjuste").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: base_url + 'productos/buscarPorNombreInventario',
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            idProductoAjuste.value = ui.item.id;
            cantidadAjuste.focus();
        }
    });

    //procesar ajuste de inventario
    btnProcesar.addEventListener('click', function () {
        if (idProductoAjuste.value == '' && buscarNombreAjuste.value == '') {
            alertaPersonalizada('warning', 'EL PRODUCTO ES REQUERIDO');
        } else if (cantidadAjuste.value == '') {
            alertaPersonalizada('warning', 'LA CANTIDAD ES REQUERIDO');
        } else {
            const url = base_url + 'inventarios/procesarAjuste';
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('POST', url, true);
            //Enviar Datos
            http.send(JSON.stringify({
                idProducto: idProductoAjuste.value,
                cantidad: cantidadAjuste.value
            }));
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    console.log(this.responseText);
                    const res = JSON.parse(this.responseText);
                    alertaPersonalizada(res.type, res.msg);
                    if (res.type == 'success') {
                        modalAjuste.hide();
                        cargarInventario(base_url + 'inventarios/listarMovimientos');
                    }
                }
            }
        }
    })

    //###### Kardex ######
    //mostrar input para la busqueda por nombre
    nombre.addEventListener('click', function () {
        containerCodigo.classList.add('d-none');
        containerNombre.classList.remove('d-none');
        inputBuscarNombre.value = '';
        inputBuscarNombre.focus();
    })
    //mostrar input para la busqueda por codigo
    barcode.addEventListener('click', function () {
        containerNombre.classList.add('d-none');
        containerCodigo.classList.remove('d-none');
        inputBuscarCodigo.value = '';
        inputBuscarCodigo.focus();
    })

    inputBuscarCodigo.addEventListener('keyup', function (e) {
        if (e.keyCode === 13) {
            const url = base_url + 'productos/buscarPorCodigo/' + e.target.value;
            //hacer una instancia del objeto XMLHttpRequest 
            const http = new XMLHttpRequest();
            //Abrir una Conexion - POST - GET
            http.open('GET', url, true);
            //Enviar Datos
            http.send();
            //verificar estados
            http.onreadystatechange = function () {
                if (this.readyState == 4 && this.status == 200) {
                    const res = JSON.parse(this.responseText);
                    if (res.estado) {
                        reporteKardex(res.datos.id);
                    } else {
                        alertaPersonalizada('warning', 'CODIGO NO EXISTE');
                    }
                }
            }
        }
        return;
    })

    //autocomplete productos
    $("#buscarProductoNombre").autocomplete({
        source: function (request, response) {
            $.ajax({
                url: base_url + 'productos/buscarPorNombreInventario',
                dataType: "json",
                data: {
                    term: request.term
                },
                success: function (data) {
                    response(data);
                }
            });
        },
        minLength: 2,
        select: function (event, ui) {
            inputBuscarNombre.value = ui.item.value || ui.item.label || '';
            reporteKardex(ui.item.id, ui.item.value || ui.item.label || ui.item.descripcion || '');
            return false;
        }
    });
})

function cargarInventario(ruta) {
    //cargar datos con el plugin datatables
    tblInventario = $('#tblInventario').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){
        // Limpia filtro de busqueda cacheado: si el usuario escribio algo y se guardo,
        // al recargar la tabla cargaba 28k filas pero las filtraba ocultandolas todas.
        if (data && data.search) data.search.search = '';
        if (data && Array.isArray(data.columns)) {
            data.columns.forEach(function(c){ if (c && c.search) c.search.search = ''; });
        }
        var v = [5,10,20,50,100,-1];
        if (data && data.length && v.indexOf(data.length) === -1) data.length = 10;
        if (data && data.start) data.start = 0;
    },
    
        ajax: {
            url: ruta,
            dataSrc: '',
            error: function(xhr, status, err){
                console.error('inventarios/listarMovimientos fallo:', status, err, xhr.responseText && xhr.responseText.substring(0, 300));
                if (typeof alertaPersonalizada === 'function') {
                    alertaPersonalizada('error', 'No se pudieron cargar los movimientos (HTTP ' + xhr.status + ')');
                }
                if (typeof window.mhnLogClientError === 'function') {
                    window.mhnLogClientError({
                        kind: 'AJAX_ERROR',
                        message: 'inventarios/listarMovimientos HTTP ' + xhr.status,
                        source: ruta, line: 0, col: 0,
                        stack: (xhr.responseText || '').substring(0, 500),
                        page: location.pathname
                    });
                }
            }
        },
        columns: [
            { data: 'descripcion' },
            { data: 'movimiento' },
            { data: 'fecha' },
            { data: 'cantidad' }
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        destroy: true,
        order: [[2, 'desc']],
    });
}

function productoPorCodigo(valor) {
    const url = base_url + 'productos/buscarPorCodigo/' + valor;
    //hacer una instancia del objeto XMLHttpRequest 
    const http = new XMLHttpRequest();
    //Abrir una Conexion - POST - GET
    http.open('GET', url, true);
    //Enviar Datos
    http.send();
    //verificar estados
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            if (res.estado) {
                idProductoAjuste.value = res.datos.id;
                buscarCodigoAjuste.value = res.datos.descripcion;
                cantidadAjuste.focus();
            } else {
                alertaPersonalizada('warning', 'CODIGO NO EXISTE');
            }
        }
    }
}

// Carga el kardex en la tabla inline (sustituye el flujo viejo de abrir PDF directo).
// El boton PDF de la cabecera sigue permitiendo descargar el reporte cuando se desea.
let tblKardex = null;
let kardexProductoActualId = null;
let kardexProductoActualNombre = '';

function cargarKardex(idProducto, nombre) {
    kardexProductoActualId = idProducto;
    kardexProductoActualNombre = nombre || '';

    const header = document.getElementById('kardexHeader');
    const nombreEl = document.getElementById('kardexNombreProducto');
    if (header) { header.classList.remove('d-none'); header.classList.add('d-flex'); }
    if (nombreEl) nombreEl.textContent = nombre || ('Producto #' + idProducto);

    const url = base_url + 'inventarios/listarKardex/' + idProducto;

    if (tblKardex) {
        tblKardex.destroy();
        $('#tblKardex tbody').empty();
    }
    tblKardex = $('#tblKardex').DataTable({
        deferRender: true,
        stateSave: false,
        pageLength: 20,
        lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'Todos']],
        ajax: {
            url: url, dataSrc: '',
            error: function(xhr){
                if (typeof alertaPersonalizada === 'function') {
                    alertaPersonalizada('error', 'No se pudo cargar el kardex (HTTP ' + xhr.status + ')');
                }
            }
        },
        columns: [
            { data: 'fecha' },
            { data: 'movimiento' },
            { data: 'accion' },
            { data: 'entrada', className: 'text-end' },
            { data: 'salida',  className: 'text-end' },
            { data: 'stock_actual', className: 'text-end fw-bold' }
        ],
        language: { url: base_url + 'assets/js/espanol.json' },
        dom,
        buttons,
        responsive: true,
        order: [],
        drawCallback: function(){
            // Calcular totales sobre TODA la data (no solo la pagina visible)
            try {
                const data = tblKardex.rows().data();
                let entradas = 0, salidas = 0, stock = 0;
                for (let i = 0; i < data.length; i++) {
                    entradas += parseInt(data[i].entrada || 0, 10);
                    salidas  += parseInt(data[i].salida  || 0, 10);
                }
                if (data.length > 0) stock = parseInt(data[0].stock_actual || 0, 10); // primer row = mas reciente
                const e = document.getElementById('kardexTotalEntradas');
                const sa = document.getElementById('kardexTotalSalidas');
                const st = document.getElementById('kardexStockActual');
                if (e)  e.textContent  = entradas;
                if (sa) sa.textContent = salidas;
                if (st) st.textContent = stock;
            } catch(_){}
        }
    });
}

// Boton PDF en la cabecera del kardex
document.addEventListener('DOMContentLoaded', function(){
    const btnPdf = document.getElementById('btnKardexPdf');
    if (btnPdf) {
        btnPdf.addEventListener('click', function(){
            if (!kardexProductoActualId) {
                if (typeof alertaPersonalizada === 'function') alertaPersonalizada('warning', 'Selecciona un producto primero');
                return;
            }
            window.open(base_url + 'inventarios/kardex/' + kardexProductoActualId, '_blank');
        });
    }
});

// Compatibilidad: la funcion vieja reporteKardex() ahora carga la tabla.
function reporteKardex(idProducto, nombre) {
    cargarKardex(idProducto, nombre);
}