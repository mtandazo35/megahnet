let divLoading = document.querySelector("#divLoading");

let tblProveedores, editorDireccion;

const formulario = document.querySelector('#formulario');
const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');

const ruc = document.querySelector('#ruc');
const nombre = document.querySelector('#nombre');
const telefono = document.querySelector('#telefono');
const correo = document.querySelector('#correo');
const direccion = document.querySelector('#direccion');
const id = document.querySelector('#id');

const errorRuc = document.querySelector('#errorRuc');
const errorCorreo = document.querySelector('#errorCorreo');
const errorNombre = document.querySelector('#errorNombre');
const errorTelefono = document.querySelector('#errorTelefono');
const errorDireccion = document.querySelector('#errorDireccion');

document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblProveedores = $('#tblProveedores').DataTable({
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'proveedor/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'nombre' },

            { data: 'ruc' },
            { data: 'telefono' },
            { data: 'correo' },
            { data: 'direccion' },
            { data: 'acciones' },
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[0, 'asc']],
    });
    // CKEditor desactivado para Dirección: textarea simple es suficiente
    // (el editor rico fallaba dentro del modal oculto y rompia el flow editar/submit).
    //
    // limpiar campos
    btnNuevo.addEventListener('click', function(){
        id.value = '';
        btnAccion.textContent = 'Registrar';
        if (typeof editorDireccion !== 'undefined' && editorDireccion) {
            editorDireccion.setData('');
        } else if (direccion) {
            direccion.value = '';
        }
        formulario.reset();
        limpiarCampos();
    })
    //registrar proveedores
    formulario.addEventListener('submit', function(e){
        e.preventDefault();
        limpiarCampos();
        // No limpiar la direccion antes de validarla (causaria fallar siempre la validacion)
        if (ruc.value == '') {
            errorRuc.textContent = 'EL RUC ES REQUERIDO';
        } else if (nombre.value == '') {
            errorNombre.textContent = 'EL NOMBRE ES REQUERIDO';
        } else if (direccion.value == '') {
            errorDireccion.textContent = 'LA DIRECCION ES REQUERIDO';
        } else {
            const url = base_url + 'proveedor/registrar';
            insertarRegistros(url, this, tblProveedores, btnAccion, false);
        }
    })
})

function eliminarProveedor(idProveedor){
    const url = base_url + 'proveedor/eliminar/' + idProveedor;
    eliminarRegistros(url, tblProveedores);
}

function editarProveedor(idProveedor) {
    limpiarCampos();
    if (typeof editorDireccion !== 'undefined' && editorDireccion) {
        editorDireccion.setData('');
    } else if (direccion) {
        direccion.value = '';
    }
    const url = base_url + 'proveedor/editar/' + idProveedor;
    const http = new XMLHttpRequest();
    http.open('GET', url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            id.value = res.id;
            ruc.value = res.ruc;
            nombre.value = res.nombre;
            telefono.value = res.telefono;
            correo.value = res.correo;
            // Direccion: limpiar HTML legacy (CKEditor guardaba <p>texto</p>)
            var dirTxt = (res.direccion || '').replace(/<\/?[^>]+(>|$)/g, '').trim();
            if (typeof editorDireccion !== 'undefined' && editorDireccion) {
                editorDireccion.setData(dirTxt);
            } else if (direccion) {
                direccion.value = dirTxt;
            }
            btnAccion.textContent = 'Actualizar';
            // Abrir modal (la vista define window.abrirModalProveedor)
            if (typeof window.abrirModalProveedor === 'function') {
                window.abrirModalProveedor();
            } else {
                firstTab.show();
            }
        }
    }
}

function limpiarCampos() {
    if (errorRuc) errorRuc.textContent = '';
    if (errorNombre) errorNombre.textContent = '';
    if (errorTelefono) errorTelefono.textContent = '';
    if (errorCorreo) errorCorreo.textContent = '';
    if (errorDireccion) errorDireccion.textContent = '';
}