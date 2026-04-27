let divLoading = document.querySelector("#divLoading");

let tblHistorial;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblHistorial = $('#tblHistorial').DataTable({
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'contratos/listarInactivos',
            dataSrc: ''
        },
        columns: [
		  { data: 'acciones' },
      { data: 'nombre' },
      { data: 'total' },
      { data: 'direccion' },
      { data: 'comentario' },
      { data: 'tributario' }
    
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[1, 'asc']],
    });
})

function restaurarContrato(idContrato) {
    const url = base_url + 'contratos/restaurar/' + idContrato;
    restaurarRegistros(url, tblHistorial);
}