let tblSri = null;

document.addEventListener('DOMContentLoaded', () => {
    cargarTabla();
});


function cargarTabla(estado = null) {

    if ($.fn.DataTable.isDataTable('#tblSri')) {
        tblSri.clear().destroy();
        $('#tblSri tbody').empty();
    }

    tblSri = $('#tblSri').DataTable({
    deferRender: true,
    stateSave: true,
    stateDuration: -1,
    colReorder: true,
    pageLength: 10,
    lengthMenu: [[5, 10, 20, 50, 100, -1], [5, 10, 20, 50, 100, "Todos"]],
    stateLoadParams: function(settings, data){ var v=[5,10,20,50,100,-1]; if(data && data.length && v.indexOf(data.length)===-1){ data.length=10; } },
    
        processing: true,
        autoWidth: false,
        responsive: true,
        scrollX: true,
        ajax: {
            url: base_url + 'sridashboard/listar',
            type: 'GET',
            data: { estado: estado },
            dataSrc: ''
        },
        columns: [

            { data: 'orden_no' },        // Factura
            { data: 'cliente' },         // Cliente
            { data: 'fecha' },           // Fecha
            { data: 'totalfactura' },    // Total
            { data: 'estado' },          // Estado SRI (badge)
            { data: 'intentos_sri' },    // 🔥 INTENTOS
            { data: 'correo' },          // 🔥 CORREO
            { data: 'acciones', orderable: false } // 🔥 ACCIONES
        ],
        language: {
            url: base_url + 'assets/DataTables/es-ES.json'
        },
        responsive: true,
        order: [[0, 'desc']]
    });

    // 🔥 FUERZA EL AJUSTE DE ANCHOS

}


function filtrar(estado) {
    cargarTabla(estado);
}

/* =====================
   VER ERROR SRI
   ===================== */

function copiarErrorSri() {
    navigator.clipboard.writeText(
        document.getElementById('errorSriRaw').textContent
    );
    Swal.fire('Copiado', 'Respuesta del SRI copiada', 'success');
}


function verError2(id) {

    fetch(base_url + 'sridashboard/error/' + id)
        .then(res => res.json())
        .then(data => {

            document.getElementById('errorSri').textContent =
                data.mensaje_sri || 'SIN DETALLE DEVUELTO POR EL SRI';

            new bootstrap.Modal(
                document.getElementById('modalError')
            ).show();
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', 'No se pudo cargar la respuesta del SRI', 'error');
        });
}


function verError(id) {

    fetch(base_url + 'sridashboard/error/' + id)
        .then(res => res.json())
        .then(data => {

            const bloque = document.getElementById('bloqueParseado');
            const raw = document.getElementById('errorSriRaw');

            // Limpieza previa
            bloque.style.display = 'none';
            raw.style.display = 'none';
            raw.textContent = '';

            if (data.ok) {

                bloque.style.display = 'block';

                document.getElementById('sriMensaje').textContent = data.mensaje || 'SIN MENSAJE';
                document.getElementById('sriCodigo').textContent = data.codigo || 'N/A';
                document.getElementById('sriEstado').textContent = data.estado || 'ERROR';
                document.getElementById('sriTipo').textContent = data.tipo || 'ERROR';
                document.getElementById('sriSugerencia').textContent = data.sugerencia || 'Revise el XML';

                // 🔥 AQUÍ ESTABA EL PROBLEMA
                raw.textContent = data.raw || 'SIN RESPUESTA RAW';

            } else {
                raw.style.display = 'block';
                raw.textContent = data.raw || 'SIN DETALLE';
            }

            new bootstrap.Modal(document.getElementById('modalError')).show();
        });
}


function toggleRaw() {
    const raw = document.getElementById('errorSriRaw');

    if (!raw.textContent.trim()) {
        raw.textContent = '⚠ No existe información técnica para mostrar';
    }

    raw.style.display = raw.style.display === 'none' ? 'block' : 'none';
}



/* =====================
   REINTENTAR MANUAL
   ===================== */
function reintentar(id) {
    Swal.fire({
        title: '¿Reintentar envío?',
        text: 'El documento volverá a cola de envío',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reintentar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(base_url + 'sridashboard/reintentar/' + id)
                .then(res => res.json())
                .then(data => {
                    Swal.fire(data.msg, '', data.type);
                    tblSri.ajax.reload();
                });
        }
    });
}
