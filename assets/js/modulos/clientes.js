let divLoading = document.querySelector("#divLoading");

let tblClientes, editorDireccion;

const formulario = document.querySelector('#formulario');
const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');

const cargarDatosExcel = document.querySelector('#cargarDatosExcel');
const btnCargar = document.querySelector('#btnCargar');
const excel = document.querySelector('#excel');
const errorExcel = document.querySelector('#errorExcel');


const identidad = document.querySelector('#identidad');
const num_identidad = document.querySelector('#num_identidad');
const nombre = document.querySelector('#nombre');
const telefono = document.querySelector('#telefono');
const correo = document.querySelector('#correo');
const direccion = document.querySelector('#direccion');

const id = document.querySelector('#id');

const errorIdentidad = document.querySelector('#errorIdentidad');
const errorNum_identidad = document.querySelector('#errorNum_identidad');
const errorNombre = document.querySelector('#errorNombre');
const errorTelefono = document.querySelector('#errorTelefono');
const errorDireccion = document.querySelector('#errorDireccion');

document.addEventListener('DOMContentLoaded', function () {



    //cargar datos con el plugin datatables
    tblClientes = $('#tblClientes').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'clientes/listar',
            dataSrc: ''
        },
        columns: [
		{ data: 'acciones' },
            { data: 'nombre' },
            { data: 'num_identidad' },
            { data: 'identidad' },
            { data: 'telefono' },
            { data: 'correo' },
            { data: 'direccion' }
            
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[1, 'asc']],
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
    //registrar clientes
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarCampos();
       // editorDireccion.setData('');
        if (identidad.value == '') {
            errorIdentidad.textContent = 'EL TIPO DE IDENTIDAD ES REQUERIDO';
        } else if (num_identidad.value == '') {
            errorNum_identidad.textContent = 'LA CÉDULA / RÚC ES REQUERIDO';
        } else if (nombre.value == '') {
            errorNombre.textContent = 'LA RAZON SOCIAL ES REQUERIDO';
        } else if (direccion.value == '') {
            errorDireccion.textContent = 'LA DIRECCIÓN ES REQUERIDO';
        } else {
            const url = base_url + 'clientes/registrar';
            insertarRegistros(url, this, tblClientes, btnAccion, false);
        }

    })


    cargarDatosExcel.addEventListener('submit', function (e) {
        e.preventDefault();
       // limpiarCampos();
       // editorDireccion.setData('');
       var archivo = document.getElementById('excel');
	   // var archivoRuta = archivo.value;
       // console.log(archivo.files[0].type);

       //alert(archivo.files);
       if(archivo.files[0] == null || archivo.files[0] == ''){
        alertaPersonalizada('error', 'SELECCIONE UN ARCHIVO EXCEL');

    }else if (archivo.files[0].type != 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {
            alertaPersonalizada('error', 'SOLO SE ADMITE FORMATO EXCEL .XLSX');

           // errorExcel.textContent = 'SELECCIONES UN ARCHIVO EXCEL';
        }     
        else {
            const url = base_url + 'clientes/registrarExcel';
            insertarRegistros(url, this, tblClientes, btnCargar, false);
        }

    })
})


function eliminarCliente(idCliente) {
    const url = base_url + 'clientes/eliminar/' + idCliente;
    eliminarRegistros(url, tblClientes);
}

function editarCliente(idCliente) {
    limpiarCampos();

    const url = base_url + 'clientes/editar/' + idCliente;
    const http = new XMLHttpRequest();
    http.open('GET', url, true);
    http.send();
    http.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const res = JSON.parse(this.responseText);
            id.value = res.id;
            identidad.value = res.identidad;
            num_identidad.value = res.num_identidad;
            nombre.value = res.nombre;
            telefono.value = res.telefono;
            correo.value = res.correo;
            direccion.value = res.direccion;

            btnAccion.textContent = 'Actualizar';
            // Abrir modal (la vista define window.abrirModalCliente)
            if (typeof window.abrirModalCliente === 'function') {
                window.abrirModalCliente();
            } else {
                firstTab.show();
            }
        }
    }
}

function limpiarCampos() {
    errorIdentidad.textContent = '';
    errorNum_identidad.textContent = '';
    errorNombre.textContent = '';
    errorTelefono.textContent = '';
    errorDireccion.textContent = '';
}
