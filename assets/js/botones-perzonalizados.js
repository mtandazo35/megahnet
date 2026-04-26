// Layout DataTables: longitud (l), botones (B), busqueda (f) | tabla (t,r) | info (i), paginacion (p)
const dom = "<'row align-items-center mb-2 g-2'<'col-md-4 col-sm-12'l><'col-md-4 col-sm-12 text-md-center text-start'B><'col-md-4 col-sm-12'f>>" +
            "<'row'<'col-sm-12'tr>>" +
            "<'row align-items-center mt-2'<'col-sm-5'i><'col-sm-7'p>>";

const buttons = [
    {
        extend: 'excelHtml5',
        className: 'btn btn-success btn-sm',
        footer: true,
        titleAttr: 'Exportar a Excel',
        text: '<i class="bx bxs-spreadsheet me-1"></i>Excel'
    },
    {
        extend: 'pdfHtml5',
        download: 'open',
        className: 'btn btn-danger btn-sm',
        footer: true,
        titleAttr: 'Exportar a PDF',
        text: '<i class="bx bxs-file-pdf me-1"></i>PDF',
        exportOptions: { columns: [0, ':visible'] }
    },
    {
        extend: 'print',
        className: 'btn btn-dark btn-sm',
        footer: true,
        titleAttr: 'Imprimir',
        text: '<i class="bx bx-printer me-1"></i>Imprimir'
    }
];

// Estilo: separar y suavizar los botones (compatible con la integracion BS5 de DT).
(function injectDtExportStyle(){
    if (document.getElementById('dt-export-style')) return;
    var s = document.createElement('style');
    s.id = 'dt-export-style';
    s.textContent = [
        ".dt-buttons{display:inline-flex;gap:.5rem;flex-wrap:wrap;margin:0}",
        ".dt-buttons .dt-button{margin:0!important}",
        ".dt-buttons .dt-button.btn{font-weight:500;border-radius:.5rem;box-shadow:0 1px 2px rgba(0,0,0,.06);transition:transform .12s ease, box-shadow .12s ease}",
        ".dt-buttons .dt-button.btn:hover{transform:translateY(-1px);box-shadow:0 3px 8px rgba(0,0,0,.10)}",
        ".dt-buttons .dt-button.btn:focus{box-shadow:0 0 0 .2rem rgba(13,110,253,.25)}",
        ".dt-buttons .dt-button.btn i{vertical-align:-2px}"
    ].join('');
    document.head.appendChild(s);
})();
