let divLoading = document.querySelector("#divLoading");

let tblGrupoTrabajo;

document.addEventListener('DOMContentLoaded', function () {

 //cargar datos con el plugin datatable
 tblGrupoTrabajo = $('#tblGrupoTrabajo').DataTable({
    deferRender: true,
    pageLength: 25,
    
    ajax: {
        url: base_url + 'grupotrabajos/listarInactivos',
        dataSrc: ''
    },
    columns: [
        { data: 'id' },
        { data: 'descripcion' },
        { data: 'observacion' },        
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

//funciones para eliminar usuario
function restaurarGrupoTrabajo(idgrupotrabajos) {
  
            const url = base_url + 'grupotrabajos/restaurar/' + idgrupotrabajos;
            restaurarRegistros(url,tblGrupoTrabajo);
           
}