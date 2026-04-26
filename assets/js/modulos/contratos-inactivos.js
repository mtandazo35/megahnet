let divLoading = document.querySelector("#divLoading");

let tblHistorial;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblHistorial = $('#tblHistorial').DataTable({
    deferRender: true,
    pageLength: 25,
    
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