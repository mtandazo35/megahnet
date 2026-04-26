let divLoading = document.querySelector("#divLoading");

let tblMikrotiks;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblMikrotiks = $('#tblMikrotiks').DataTable({
    deferRender: true,
    pageLength: 25,
    
        ajax: {
            url: base_url + 'mikrotiks/listarInactivos',
            dataSrc: ''
        },
        columns: [
            { data: 'nombre' },
            { data: 'ip' },
            { data: 'usuario' },
            { data: 'clave' },
            { data: 'puerto' }, 
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

function restaurarMikrotik(idMikrotiks) {
    const url = base_url + 'mikrotiks/restaurar/' + idMikrotiks;
    restaurarRegistros(url, tblMikrotiks);
}