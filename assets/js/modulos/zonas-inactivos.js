let divLoading = document.querySelector("#divLoading");

let tblZonas;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblZonas = $('#tblZonas').DataTable({
    deferRender: true,
    pageLength: 25,
    
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