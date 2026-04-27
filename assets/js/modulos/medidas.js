let tblMedidas;
const btnAccion = document.querySelector('#btnAccion');
const formulario = document.querySelector('#formulario');

const nombre = document.querySelector('#nombre');
const nombre_corto = document.querySelector('#nombre_corto');

const errorNombre = document.querySelector('#errorNombre');
const errorNombreCorto = document.querySelector('#errorNombreCorto');

const btnNuevo = document.querySelector('#btnNuevo');
const id = document.querySelector('#id');

document.addEventListener('DOMContentLoaded', function () {

    //cargar datos con el plugin datatable
    tblMedidas = $('#tblMedidas').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'medidas/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'medida' },
            { data: 'nombre_corto' },
            { data: 'acciones' }
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[0, 'asc']]
    });
    btnNuevo.addEventListener('click', function(){
        id.value = '';
        btnAccion.textContent = 'Registrar';
        formulario.reset();
        limpiarCampos();
    })
    formulario.addEventListener('submit', function (e) {
        e.preventDefault();
        limpiarCampos();

        if (nombre.value == '') {
            errorNombre.textContent = 'EL NOMBRE ES REQUERIDO';
        } else if (nombre_corto.value == '') {
            errorNombreCorto.textContent = 'EL NOMBRE CORTO ES REQUERIDO';
        } else {
            const url = base_url + 'medidas/registrar';
            insertarRegistros(url, this, tblMedidas, btnAccion, false);
        }


    });


})


function eliminarMedida(idMedida) {
    const url = base_url + 'medidas/eliminar/' + idMedida;
    eliminarRegistros(url, tblMedidas);
}

function editarMedida(idMedida) {
    limpiarCampos();
    const url = base_url + 'medidas/editar/' + idMedida;

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
            nombre.value = res.medida;
            nombre_corto.value = res.nombre_corto;
            btnAccion.textContent = 'Actualizar';
            if (typeof window.abrirModalMedida === 'function') { window.abrirModalMedida(); }
            else { firstTab.show(); }
        }
    }
}

function limpiarCampos() {
    errorNombre.textContent = '';
        errorNombreCorto.textContent = '';
}