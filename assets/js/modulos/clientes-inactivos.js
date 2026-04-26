let divLoading = document.querySelector("#divLoading");

let tblClientes;
document.addEventListener('DOMContentLoaded', function(){
    //cargar datos con el plugin datatables
    tblClientes = $('#tblClientes').DataTable({
    deferRender: true,
    pageLength: 25,
    
        ajax: {
            url: base_url + 'clientes/listarInactivos',
            dataSrc: ''
        },
        columns: [
		{ data: 'acciones' },
            { data: 'identidad' },
            { data: 'num_identidad' },
            { data: 'nombre' },
            { data: 'telefono' },
            { data: 'correo' },
            { data: 'direccion' }
            
        ],
        language: {
            url: base_url + 'assets/js/espanol.json'
        },
        dom,
        buttons,
        responsive: true,
        order: [[3, 'asc']],
    });
})

function restaurarCliente(idCliente) {
    const url = base_url + 'clientes/restaurar/' + idCliente;
    restaurarRegistros(url, tblClientes);
}

// Eliminacion PERMANENTE (DELETE) — solo si no tiene contratos.
function eliminarClientePermanente(idCliente) {
    Swal.fire({
        title: '¿Eliminar permanentemente?',
        html: '<div class="text-muted small">Esta acción <b>NO</b> se puede deshacer. El cliente se borrará de la base de datos.</div>',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then(function (r) {
        if (!r.isConfirmed) return;
        fetch(base_url + 'clientes/eliminarPermanente/' + idCliente, {
            method: 'GET', credentials: 'same-origin'
        })
        .then(rs => rs.json())
        .then(function (res) {
            Swal.fire({
                toast: true, position: 'top-right',
                icon: res.type, title: res.msg,
                showConfirmButton: false, timer: 3000
            });
            if (res.type === 'success' && tblClientes && tblClientes.ajax) {
                tblClientes.ajax.reload(null, false);
            }
        })
        .catch(function () {
            Swal.fire({ icon: 'error', title: 'Error de red al eliminar' });
        });
    });
}