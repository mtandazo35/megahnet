let divLoading = document.querySelector("#divLoading");

let tblZonas;
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
            url: base_url + 'zonas/listarInactivos',
            dataSrc: ''
        },
        columns: [
            { data: 'id' },
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
})

function restaurarZonas(idZona) {
    const url = base_url + 'zonas/restaurar/' + idZona;
    restaurarRegistros(url, tblZonas);
}