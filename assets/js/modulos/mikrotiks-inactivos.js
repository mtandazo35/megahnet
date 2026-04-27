let divLoading = document.querySelector("#divLoading");

let tblMikrotiks;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblMikrotiks = $('#tblMikrotiks').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        ajax: {
            url: base_url + 'mikrotiks/listarInactivos',
            dataSrc: ''
        },
        columns: [
            { data: 'nombre' },
            { data: 'ip' },
            { data: 'usuario' },
            { data: 'clave' },
            { data: 'puerto' }, 
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

function restaurarMikrotik(idMikrotiks) {
    const url = base_url + 'mikrotiks/restaurar/' + idMikrotiks;
    restaurarRegistros(url, tblMikrotiks);
}

// Eliminacion PERMANENTE (DELETE) - solo si no tiene contratos.
function eliminarMikrotikPermanente(idMikrotik) {
    Swal.fire({
        title: '¿Eliminar permanentemente?',
        html: '<div class="text-muted small">Esta accion <b>NO</b> se puede deshacer. El registro se borrara de la base de datos.</div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (r) {
        if (!r.isConfirmed) return;
        fetch(base_url + 'mikrotiks/eliminarPermanente/' + idMikrotik, {
            method: 'GET', credentials: 'same-origin'
        })
        .then(rs => rs.json())
        .then(function (res) {
            Swal.fire({
                toast: true, position: 'top-right',
                icon: res.type, title: res.msg,
                showConfirmButton: false, timer: 3000
            });
            if (res.type === 'success' && tblMikrotiks && tblMikrotiks.ajax) {
                tblMikrotiks.ajax.reload(null, false);
            }
        })
        .catch(function () {
            Swal.fire({ icon: 'error', title: 'Error de red al eliminar' });
        });
    });
}