let divLoading = document.querySelector("#divLoading");

let tblProveedores;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblProveedores = $('#tblProveedores').DataTable({
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'proveedor/listarInactivos',
            dataSrc: ''
        },
        columns: [
            { data: 'ruc' },
            { data: 'nombre' },
            { data: 'telefono' },
            { data: 'correo' },
            { data: 'direccion' },
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
})

function restaurarProveedor(idProveedor) {
    const url = base_url + 'proveedor/restaurar/' + idProveedor;
    restaurarRegistros(url, tblProveedores);
}