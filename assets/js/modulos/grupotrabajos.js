let divLoading = document.querySelector("#divLoading");

let tblGrupoTrabajos, editorDireccion;

const formulario = document.querySelector('#formulario');
const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');
const buscarUsuario = document.querySelector('#buscarUsuario')
const idUsuario = document.querySelector('#idUsuario');

const descripcion = document.querySelector('#descripcion');
const observacion = document.querySelector('#observacion');
const id = document.querySelector('#id');

const errorDescripcion = document.querySelector('#errorDescripcion');
const errorObservacion = document.querySelector('#errorObservacion');
const errorUsuarios = document.querySelector('#errorUsuario');

document.addEventListener('DOMContentLoaded', function () {

    // autocomplete contratos
    $('#buscarUsuario').autocomplete({
        source: function (request, response) {
            $.ajax({
                url: base_url + 'grupotrabajos/buscar',
                dataType: 'json',
                data: {
                    term: request.term
                },
                success: function (data) {
                    response(data)
                    if (data.length > 0) {
                        errorUsuarios.textContent = ''
                    } else {
                        errorUsuarios.textContent = 'NO HAY USUARIOS CON ESE NOMBRE'
                    }
                }
            })
        },
        minLength: 2,
        select: function (event, ui) {

            idUsuario.value = ui.item.id
        }
    })

    //cargar datos con el plugin datatables
    tblGrupoTrabajos = $('#tblGrupoTrabajos').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'grupotrabajos/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'responsable' },
            { data: 'descripcion' },
            { data: 'observacion' },
            { data: 'empleados' },
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
        // editorDireccion.setData('');
        formulario.reset();
        limpiarCampos();
    })
    //registrar tblGrupoTrabajos
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarCampos();
        // editorDireccion.setData('');
        if (idUsuario.value == '') {
            alertaPersonalizada('warning', 'SELECCIONE UN USUARIO')
            return
        } else if (descripcion.value == '') {
            alertaPersonalizada('warning', 'LA DESCRIPCIÓN ES REQUERIDO')
            return  
        } else {
            const url = base_url + 'grupotrabajos/registrar';
            insertarRegistros(url, this, tblGrupoTrabajos, btnAccion, false);
        }

    })
})


function eliminarGrupoTrabajos(idGrupoTrabajos) {
    const url = base_url + 'grupotrabajos/eliminar/' + idGrupoTrabajos;
    eliminarRegistros(url, tblGrupoTrabajos);
}

function editarGrupoTrabajos(idGrupoTrabajos) {
    limpiarCampos();
    // editorDireccion.setData('');

    const url = base_url + 'grupotrabajos/editar/' + idGrupoTrabajos;
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
            idUsuario.value = res.id_responsable;
buscarUsuario.value=res.responsable;
            descripcion.value = res.descripcion;
            observacion.value = res.observacion;

            btnAccion.textContent = 'Actualizar';
            if (typeof window.abrirModalGrupoTrabajo === 'function') { window.abrirModalGrupoTrabajo(); }
            else { firstTab.show(); }
        }
    }
}

function limpiarCampos() {

    errorDescripcion.textContent = '';
}