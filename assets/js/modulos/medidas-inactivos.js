let tblMedidas;

document.addEventListener('DOMContentLoaded', function () {

 //cargar datos con el plugin datatable
 tblMedidas = $('#tblMedidas').DataTable({
    deferRender: true,
    pageLength: 25,
    
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