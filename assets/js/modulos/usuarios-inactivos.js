let divLoading = document.querySelector("#divLoading");

let tblUsuarios;

document.addEventListener('DOMContentLoaded', function () {

 //cargar datos con el plugin datatable
 tblUsuarios = $('#tblUsuarios').DataTable({
    deferRender: true,
    pageLength: 25,
    
    ajax: {
        url: base_url + 'usuarios/listarInactivos',
        dataSrc: ''
    },
    columns: [
        { data: 'nombres' },
        { data: 'correo' },
        { data: 'telefono' },
        { data: 'direccion' },
        { data: 'rol' },
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
function restaurarUsuario(idusuario) {
  
            const url = base_url + 'usuarios/restaurar/' + idusuario;
            restaurarRegistros(url,tblUsuarios);
           
}