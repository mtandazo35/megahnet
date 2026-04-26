let divLoading = document.querySelector("#divLoading");

let tblUsuarios;
const formulario = document.querySelector('#formulario');

const id = document.querySelector('#id');
const nombres = document.querySelector('#nombres');
const apellidos = document.querySelector('#apellidos');
const correo = document.querySelector('#correo');
const telefono = document.querySelector('#telefono');
const direccion = document.querySelector('#direccion');
const clave = document.querySelector('#clave');
const rol = document.querySelector('#rol');
const grupotrabajo = document.querySelector('#grupotrabajo');
//elementos para mostar errre
const errorNombre = document.querySelector('#errorNombre');
const errorApellido = document.querySelector('#errorApellido');
const errorCorreo = document.querySelector('#errorCorreo');
const errorTelefono = document.querySelector('#errorTelefono');
const errorDireccion = document.querySelector('#errorDireccion');
const errorClave = document.querySelector('#errorClave');
const errorRol = document.querySelector('#errorRol');
const errorGrupoTrabajo = document.querySelector('#errorGrupoTrabajo');

const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');

document.addEventListener('DOMContentLoaded', function () {
    //cargar datos con el plugin datatable
    tblUsuarios = $('#tblUsuarios').DataTable({
    deferRender: true,
    pageLength: 25,
    
        ajax: {
            url: base_url + 'usuarios/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'nombres' },
            { data: 'correo' },
            { data: 'telefono' },
            { data: 'direccion' },
            { data: 'rol' },
            { data: 'grupotrabajo' },
            { data: 'acciones' }
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[0, 'desc']]
    });

    //limpiar campos
    btnNuevo.addEventListener('click', function (e) {
        id.value = '';
        btnAccion.textContent = 'Registrar';
        clave.removeAttribute('readonly');
        formulario.reset();
        nombres.focus();
        limpiarCampos();

    })

    //registrar usuarios
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarCampos();

       

        if (nombres.value == '') {
            errorNombre.textContent = 'EL NOMBRE ES REQUERIDO';
        } else if (apellidos.value == '') {
            errorApellido.textContent = 'EL APELLIDO ES REQUERIDO';
        }
        else if (correo.value == '') {
            errorCorreo.textContent = 'EL CORREO ES REQUERIDO';
        }
        else if (telefono.value == '') {
            errorTelefono.textContent = 'EL TELEFONO ES REQUERIDO';
        }
        else if (direccion.value == '') {
            errorDireccion.textContent = 'LA DIRECCION ES REQUERIDO';
        }
        else if (clave.value == '') {
            errorClave.textContent = 'LA CLAVE ES REQUERIDO';
        }
        else if (rol.value == '') {
            errorRol.textContent = 'EL ROL ES REQUERIDO';
        } else {
            const url = base_url + 'usuarios/registrar';
           
            insertarRegistros(url, this, tblUsuarios, btnAccion, true);
        }
    })



})

//function para elimnar usuario
function eliminarUsuario(idUsuario) {
    const url = base_url + 'usuarios/eliminar/' + idUsuario;
    eliminarRegistros(url, tblUsuarios);
}

//funciones para editar usuario
function editarUsuario(idusuario) {
    limpiarCampos();
    const url = base_url + 'usuarios/editar/' + idusuario;

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
            nombres.value = res.nombre;
            apellidos.value = res.apellido;
            correo.value = res.correo;
            telefono.value = res.telefono;
            direccion.value = res.direccion;
            rol.value = res.rol;
            grupotrabajo.value = res.id_grupo_trabajo;
            //clave.value = res.clave;
            //clave.setAttribute('readonly', 'readonly');
            btnAccion.textContent = 'Actualizar';
            if (typeof window.abrirModalUsuario === 'function') { window.abrirModalUsuario(); }
            else { firstTab.show(); }
        }
    }

}
function limpiarCampos() {
    errorNombre.textContent = '';
        errorApellido.textContent = '';
        errorCorreo.textContent = '';
        errorTelefono.textContent = '';
        errorDireccion.textContent = '';
        errorClave.textContent = '';
        errorRol.textContent = '';

}