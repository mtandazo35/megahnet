let divLoading = document.querySelector("#divLoading");

let tblGrupoTrabajo;

document.addEventListener('DOMContentLoaded', function () {

 //cargar datos con el plugin datatable
 tblGrupoTrabajo = $('#tblGrupoTrabajo').DataTable({
    deferRender: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
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