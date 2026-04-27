let divLoading = document.querySelector("#divLoading");

let tblRepetidoras;

const formulario = document.querySelector('#formulario');
const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');

//const cargarDatosExcel = document.querySelector('#cargarDatosExcel');
//const btnCargar = document.querySelector('#btnCargar');
//const excel = document.querySelector('#excel');
//const errorExcel = document.querySelector('#errorExcel');


const marca = document.querySelector('#marca');
const ssid = document.querySelector('#ssid');
const ip = document.querySelector('#ip');
const canal = document.querySelector('#canal');
const seguridad = document.querySelector('#seguridad');
const frecuencia = document.querySelector('#frecuencia');

const id = document.querySelector('#id');

const errorMarca = document.querySelector('#errorMarca');
const errorSsid = document.querySelector('#errorSsid');
const errorIp = document.querySelector('#errorIp');
const errorCanal = document.querySelector('#errorCanal');
const errorSeguridad = document.querySelector('#errorSeguridad');
const errorFrecuencia = document.querySelector('#errorFrecuencia');

document.addEventListener('DOMContentLoaded', function () {



    //cargar datos con el plugin datatables
    tblRepetidoras = $('#tblRepetidoras').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'repetidoras/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'ssid' },

            { data: 'marca' },
            { data: 'ip' },
            { data: 'canal' },
            { data: 'seguridad' },
            { data: 'frecuencia' },
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
    //registrar clientes
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarCampos();
       // editorDireccion.setData('');
        if (marca.value == '') {
            errorMarca.textContent = 'LA MARCA ES REQUERIDO';
        } else if (ssid.value == '') {
            errorSsid.textContent = 'LA SSID ES REQUERIDO';
        } else if (ip.value == '') {
            errorIp.textContent = 'LA IP ES REQUERIDO';
        } else if (canal.value == '') {
            errorCanal.textContent = 'EL CANAL ES REQUERIDO';
        }  else if (seguridad.value == '') {
            errorSeguridad.textContent = 'LA SEGURIDAD ES REQUERIDO';
        } else if (frecuencia.value == '') {
            errorFrecuencia.textContent = 'LA FRECUENCIA ES REQUERIDO';
        }else {
            const url = base_url + 'repetidoras/registrar';
            insertarRegistros(url, this,tblRepetidoras, btnAccion, false);
        }

    })


   /* cargarDatosExcel.addEventListener('submit', function (e) {
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

    })*/
})


function eliminarCliente(idCliente) {
    const url = base_url + 'clientes/eliminar/' + idCliente;
    eliminarRegistros(url, tblClientes);
}

function editarRepetidoras(idRepetidoras) {
    limpiarCampos();
   // editorDireccion.setData('');

    const url = base_url + 'repetidoras/editar/' + idRepetidoras;
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
            marca.value = res.marca;
            ssid.value = res.ssid;
            ip.value = res.ip;
            canal.value = res.canal;
            seguridad.value = res.seguridad;
            frecuencia.value=res.frecuencia;
           
            btnAccion.textContent = 'Actualizar';
            if (typeof window.abrirModalRepetidora === 'function') { window.abrirModalRepetidora(); }
            else { firstTab.show(); }
        }
    }
}

function limpiarCampos() {
    errorMarca.textContent = '';
    errorSsid.textContent = '';
    errorCanal.textContent = '';
    errorIp.textContent = '';
    errorSeguridad.textContent = '';
    errorFrecuencia.textContent = '';
}