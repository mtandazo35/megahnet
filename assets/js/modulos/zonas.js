let divLoading = document.querySelector("#divLoading");

let tblZonas;
const formulario = document.querySelector('#formulario');
const id = document.querySelector('#id');
const nombre = document.querySelector('#nombre');
const errorNombre = document.querySelector('#errorNombre');
const btnAccion = document.querySelector('#btnAccion');
const btnNuevo = document.querySelector('#btnNuevo');
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblZonas = $('#tblZonas').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'zonas/listar',
            dataSrc: ''
        },
        columns: [
            { data: 'descripcion' },
            { data: 'acciones' }
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[0, 'asc']],
    });
    btnNuevo.addEventListener('click', function(){
        id.value = '';
        errorNombre.textContent = '';
        btnAccion.textContent = 'Registrar';
        formulario.reset();
    })
    //registrar categorias
    formulario.addEventListener('submit', function(e){
        e.preventDefault();
        errorNombre.textContent = '';
        if (nombre.value == '') {
            errorNombre.textContent = 'LA DESCRIPCION ES REQUERIDO';
        } else {
            const url = base_url + 'zonas/registrar';
            insertarRegistros(url, this, tblZonas, btnAccion, false);
        }        
    });
})

function eliminarZonas(idZonas) {
    const url = base_url + 'zonas/eliminar/' + idZonas;
    eliminarRegistros(url, tblZonas);
}

function editarZonas(idZonas) {
    errorNombre.textContent = '';
    const url = base_url + 'zonas/editar/' + idZonas;
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
            nombre.value = res.descripcion;
            var sm = document.getElementById('id_mikrotik');
            if (sm) sm.value = (res.id_mikrotik !== null && res.id_mikrotik !== undefined) ? res.id_mikrotik : '';
            btnAccion.textContent = 'Actualizar';
            if (typeof window.abrirModalZona === 'function') { window.abrirModalZona(); }
            else { firstTab.show(); }
        }
    }
}

