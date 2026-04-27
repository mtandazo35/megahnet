let divLoading = document.querySelector("#divLoading");

let tblProductos;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblProductos = $('#tblProductos').DataTable({
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'productos/listarInactivos',
            dataSrc: ''
        },
        columns: [
            { data: 'codigo' },
            { data: 'descripcion' },
            { data: 'precio_compra' },
            { data: 'precio_venta' },
            { data: 'cantidad' },
            { data: 'categoria' },
            { data: 'imagen' },
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

function restaurarProducto(idProducto) {
    const url = base_url + 'productos/restaurar/' + idProducto;
    restaurarRegistros(url, tblProductos);
}