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
            { data: 'estado_badge' },
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
    // Registrar/actualizar Mikrotik con verificacion previa de conexion
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarCampos();
        if (nombre.value == '')      { errorNombre.textContent = 'EL NOMBRE DEL MIKROTIK ES REQUERIDO'; return; }
        if (ip.value == '')          { errorIp.textContent     = 'LA IP DEL MIKROTIK ES REQUERIDO';      return; }
        if (usuario.value == '')     { errorUsuario.textContent= 'EL USUARIO ES REQUERIDO';              return; }
        if (clave.value == '' && id.value == '') { errorClave.textContent = 'LA CLAVE DEL MIKROTIK ES REQUERIDO'; return; }
        if (puerto.value == '')      { errorPuerto.textContent = 'EL PUERTO ES REQUERIDO';               return; }

        var thisForm = this;
        var origLabel = btnAccion.innerHTML;
        btnAccion.disabled = true;
        btnAccion.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Verificando…';

        // Probar conexion ANTES de guardar
        var fd = new FormData();
        fd.append('ip', ip.value);
        fd.append('usuario', usuario.value);
        fd.append('clave', clave.value);
        fd.append('puerto', puerto.value);
        if (id.value) fd.append('id', id.value);

        fetch(base_url + 'mikrotiks/probarConexion', {
            method: 'POST', credentials: 'same-origin', body: fd
        })
        .then(r => r.json())
        .then(function (res) {
            btnAccion.disabled = false;
            btnAccion.innerHTML = origLabel;

            if (res.type === 'success') {
                // Conexion OK => guardar directo
                const url = base_url + 'mikrotiks/registrar';
                insertarRegistros(url, thisForm, tblMikrotiks, btnAccion, false);
                return;
            }
            // Conexion fallo => preguntar si guardar igualmente
            Swal.fire({
                icon: 'warning',
                title: 'No se pudo conectar al MikroTik',
                html: '<div class="small text-muted">' + (res.msg || 'Error al verificar') + '</div>'
                    + '<div class="mt-2">¿Deseas guardar de todas formas?</div>',
                showCancelButton: true,
                confirmButtonText: 'Guardar igualmente',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc2626',
            }).then(function (r) {
                if (r.isConfirmed) {
                    const url = base_url + 'mikrotiks/registrar';
                    insertarRegistros(url, thisForm, tblMikrotiks, btnAccion, false);
                }
            });
        })
        .catch(function () {
            btnAccion.disabled = false;
            btnAccion.innerHTML = origLabel;
            // Si el fetch falla por red, guardar igualmente (no bloquear)
            const url = base_url + 'mikrotiks/registrar';
            insertarRegistros(url, thisForm, tblMikrotiks, btnAccion, false);
        });
    })




})


function eliminarMikrotik(idMikrotik) {
    const url = base_url + 'mikrotiks/eliminar/' + idMikrotik;
    eliminarRegistros(url, tblMikrotiks);
}

// Boton "Verificar conexion" en cada fila de la tabla.
// Hace ping al MikroTik y actualiza estado en BD + UI.
function verificarConexionMikrotik(idMikrotik) {
    if (!idMikrotik) return;
    Swal.fire({
        title: 'Verificando conexión…',
        html: '<div class="text-muted small">Conectando al MikroTik</div>',
        allowOutsideClick: false, allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });
    fetch(base_url + 'mikrotiks/verificarConexion/' + idMikrotik, {
        method: 'GET', credentials: 'same-origin'
    })
    .then(r => r.json())
    .then(function (res) {
        if (res.type === 'success') {
            Swal.fire({
                icon: 'success', title: 'Conectado',
                html: '<div class="small">' + (res.msg || '') + '</div>',
                timer: 2500, showConfirmButton: false
            });
        } else {
            Swal.fire({
                icon: 'error', title: 'Sin conexión',
                html: '<div class="small">' + (res.msg || '') + '</div>'
            });
        }
        if (typeof tblMikrotiks !== 'undefined' && tblMikrotiks && tblMikrotiks.ajax) {
            tblMikrotiks.ajax.reload(null, false);
        }
    })
    .catch(function () {
        Swal.fire({ icon: 'error', title: 'Error de red al verificar' });
    });
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
