let divLoading = document.querySelector("#divLoading");

let tblCategorias;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblCategorias = $('#tblCategorias').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'categorias/listarInactivos',
            dataSrc: ''
        },
        columns: [
            { data: 'categoria' },
            { data: 'fecha' },
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

function restaurarCategoria(idCategoria) {
    const url = base_url + 'categorias/restaurar/' + idCategoria;
    restaurarRegistros(url, tblCategorias);
}