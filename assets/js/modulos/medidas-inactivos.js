let tblMedidas;

document.addEventListener('DOMContentLoaded', function () {

 //cargar datos con el plugin datatable
 tblMedidas = $('#tblMedidas').DataTable({
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
    ajax: {
        url: base_url + 'medidas/listarInactivos',
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

})

//funciones para restaurar
function restaurarMedida(idMedida) {
  
            const url = base_url + 'medidas/restaurar/' + idMedida;
            restaurarRegistros(url,tblMedidas);
           
}