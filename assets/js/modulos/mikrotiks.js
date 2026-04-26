let divLoading = document.querySelector("#divLoading");

let tblMikrotiks;

const formulario = document.querySelector('#formulario');
const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');





const nombre = document.querySelector('#nombre');
const ip = document.querySelector('#ip');
const usuario = document.querySelector('#usuario');
const clave = document.querySelector('#clave');
const puerto = document.querySelector('#puerto');
const id = document.querySelector('#id');

const errorNombre = document.querySelector('#errorNombre');
const errorIp = document.querySelector('#errorIp');
const errorUsuario = document.querySelector('#errorUsuario');
const errorClave = document.querySelector('#errorClave');
const errorPuerto = document.querySelector('#errorPuerto');

document.addEventListener('DOMContentLoaded', function () {



    //cargar datos con el plugin datatables
    tblMikrotiks = $('#tblMikrotiks').DataTable({
    deferRender: true,
    pageLength: 25,
    
        ajax: {
            url: base_url + 'mikrotiks/listar',
            dataSrc: ''
        },
        columns: [
		    { data: 'acciones' },
            { data: 'nombre' },
            { data: 'ip' },
            { data: 'usuario' },
            { data: 'clave' },
            { data: 'puerto' }
        
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[0, 'asc']],
    });
    //Inicializar un Editor
    /* ClassicEditor
         .create(document.querySelector('#direccion'), {
             toolbar: {
                 items: [
                     'selectAll', '|',
                     'heading', '|',
                     'bold', 'italic',
                     'outdent', 'indent', '|',
                     'undo', 'redo',
                     'alignment', '|',
                     'link', 'blockQuote', 'insertTable', 'mediaEmbed'
                 ],
                 shouldNotGroupWhenFull: true
             },
         })
         .then(editor => {
             editorDireccion = editor
         })
         .catch(error => {
             console.error(error);
         });*/

    //limpiar campos
    btnNuevo.addEventListener('click', function () {
        id.value = '';
        btnAccion.textContent = 'Registrar';
        clave.removeAttribute('readonly');
        formulario.reset();
        limpiarCampos();
    })
    //registrar clientes
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarCampos();
        // editorDireccion.setData('');
        if (nombre.value == '') {
            errorNombre.textContent = 'EL NOMBRE DEL MIKROTIK ES REQUERIDO';
        } else if (ip.value == '') {
            errorIp.textContent = 'LA IP DEL MIKROTIK ES REQUERIDO';
        } else if (usuario.value == '') {
            errorUsuario.textContent = 'EL USUARIO ES REQUERIDO';
        } else if (clave.value == '') {
            errorClave.textContent = 'LA CLAVE DEL MIKROTIK ES REQUERIDO';
        } else if (puerto.value == '') {
            errorPuerto.textContent = 'EL PUERTO ES REQUERIDO';
        } else {
            const url = base_url + 'mikrotiks/registrar';
            insertarRegistros(url, this, tblMikrotiks, btnAccion, false);
        }

    })




})


function eliminarMikrotik(idMikrotik) {
    const url = base_url + 'mikrotiks/eliminar/' + idMikrotik;
    eliminarRegistros(url, tblMikrotiks);
}

function editarMikrotik(idMikrotik) {
    limpiarCampos();
    // editorDireccion.setData('');

    const url = base_url + 'mikrotiks/editar/' + idMikrotik;
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
            id.value = res.id;
            nombre.value = res.nombre;
            ip.value = res.ip;
            usuario.value = res.usuario;
            puerto.value = res.puerto;
            clave.value = res.clave;
            btnAccion.textContent = 'Actualizar';
            // Abrir modal (la vista define window.abrirModalMikrotik)
            if (typeof window.abrirModalMikrotik === 'function') {
                window.abrirModalMikrotik();
            } else {
                firstTab.show();
            }
        }
    }
}

function limpiarCampos() {
    errorNombre.textContent = '';
    errorIp.textContent = '';
    errorUsuario.textContent = '';
    errorClave.textContent = '';
    errorPuerto.textContent = '';
}
