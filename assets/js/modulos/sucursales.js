let divLoading = document.querySelector("#divLoading");

let tblSucursales;
const formSucursal = document.querySelector('#formularioSucursal');
const sucId        = document.querySelector('#suc_id');
const sucNombre    = document.querySelector('#suc_nombre');
const sucDireccion = document.querySelector('#suc_direccion');
const sucEstab     = document.querySelector('#suc_establecimiento');
const sucPunto     = document.querySelector('#suc_puntoemi');
const sucAmbiente  = document.querySelector('#suc_ambiente');
const sucSecF      = document.querySelector('#suc_sec_factura');
const sucSecFP     = document.querySelector('#suc_sec_factura_pruebas');
const sucSecNC     = document.querySelector('#suc_sec_notacredito');
const sucSecNCP    = document.querySelector('#suc_sec_notacredito_pruebas');
const sucSecRec    = document.querySelector('#suc_sec_recibo');
const errorSucNombre = document.querySelector('#errorSucNombre');
const btnAccionSucursal = document.querySelector('#btnAccionSucursal');
const modalSucursalEl   = document.querySelector('#modalSucursal');
const modalSucursalTitulo = document.querySelector('#modalSucursalTitulo');

document.addEventListener('DOMContentLoaded', function () {
    tblSucursales = $('#tblSucursales').DataTable({
        deferRender: true,
        stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
        ajax: { url: base_url + 'sucursales/listar', dataSrc: '' },
        columns: [
            { data: 'nombre' },
            { data: 'direccion' },
            { data: 'estab_punto', className: 'text-center' },
            { data: 'ambiente_badge', className: 'text-center' },
            { data: 'acciones', className: 'text-center', orderable: false }
        ],
        language: { url: base_url + 'assets/js/espanol.json' },
        dom, buttons,
        responsive: true,
        order: [[2, 'asc']]
    });

    // Botón "Nueva sucursal" → resetear form
    document.querySelector('#btnAbrirNueva')?.addEventListener('click', limpiarFormSucursal);

    // Submit del formulario
    formSucursal.addEventListener('submit', function (e) {
        e.preventDefault();
        errorSucNombre.textContent = '';
        if (!sucNombre.value.trim()) {
            errorSucNombre.textContent = 'EL NOMBRE DEL ESTABLECIMIENTO ES REQUERIDO';
            return;
        }
        const url = base_url + 'sucursales/registrar';
        insertarRegistros(url, this, tblSucursales, btnAccionSucursal, false);
    });

    // Cerrar modal cuando el guardado es OK (evento estándar del proyecto)
    formSucursal.addEventListener('mhn:registroOk', function () {
        bootstrap.Modal.getOrCreateInstance(modalSucursalEl).hide();
    });

    // Datatable de inactivos (lazy en show.bs.modal)
    const modalInactivosEl = document.querySelector('#modalInactivos');
    let dtInactivos, initialized = false;
    modalInactivosEl?.addEventListener('show.bs.modal', function () {
        if (initialized) { dtInactivos?.ajax.reload(null, false); return; }
        initialized = true;
        dtInactivos = $('#tblSucursalesInactivos').DataTable({
            deferRender: true, pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
            ajax: { url: base_url + 'sucursales/listarInactivos', dataSrc: '' },
            columns: [
                { data: 'nombre' },
                { data: 'estab_punto' },
                { data: 'acciones', className: 'text-center', orderable: false }
            ],
            language: { url: base_url + 'assets/js/espanol.json' },
            responsive: true, order: [[1, 'asc']]
        });
    });

    document.addEventListener('mhn:restauradoOk', function () {
        if (tblSucursales) tblSucursales.ajax.reload(null, false);
    });
});

function limpiarFormSucursal() {
    sucId.value = '0';
    sucNombre.value = '';
    sucDireccion.value = '';
    sucEstab.value = '1';
    sucPunto.value = '1';
    sucAmbiente.value = 'PRUEBAS';
    sucSecF.value = '1';
    sucSecFP.value = '1';
    sucSecNC.value = '1';
    sucSecNCP.value = '1';
    sucSecRec.value = '1';
    errorSucNombre.textContent = '';
    btnAccionSucursal.innerHTML = '<i class="bx bx-save me-1"></i>Guardar';
    modalSucursalTitulo.textContent = 'Nueva Sucursal';
}

function editarSucursal(id) {
    fetch(base_url + 'sucursales/editar/' + id)
        .then(r => r.json())
        .then(res => {
            if (!res || !res.id) {
                alertaPersonalizada('error', 'NO SE PUDO CARGAR LA SUCURSAL');
                return;
            }
            sucId.value = res.id;
            sucNombre.value = res.nombre || '';
            sucDireccion.value = res.direccion || '';
            sucEstab.value = parseInt(res.establecimiento, 10) || 1;
            sucPunto.value = parseInt(res.puntoemi, 10) || 1;
            sucAmbiente.value = res.ambiente || 'PRUEBAS';
            sucSecF.value = res.sec_factura || 1;
            sucSecFP.value = res.sec_factura_pruebas || 1;
            sucSecNC.value = res.sec_notacredito || 1;
            sucSecNCP.value = res.sec_notacredito_pruebas || 1;
            sucSecRec.value = res.sec_recibo || 1;
            modalSucursalTitulo.textContent = 'Editar Sucursal';
            btnAccionSucursal.innerHTML = '<i class="bx bx-save me-1"></i>Actualizar';
            bootstrap.Modal.getOrCreateInstance(modalSucursalEl).show();
        })
        .catch(() => alertaPersonalizada('error', 'ERROR DE RED'));
}

function eliminarSucursal(id) {
    eliminarRegistros(base_url + 'sucursales/eliminar/' + id, tblSucursales);
}

function restaurarSucursal(id) {
    const url = base_url + 'sucursales/restaurar/' + id;
    restaurarRegistros(url, tblSucursales);
}
