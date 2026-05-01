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
    },
    {
        extend: 'colvis',
        className: 'btn btn-secondary btn-sm dt-btn-colvis',
        titleAttr: 'Mostrar / ocultar columnas',
        text: '<i class="bx bx-columns me-1"></i><span>Columnas</span>',
        columns: ':not(:first-child)'
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
        ".dt-buttons .dt-button.btn i{vertical-align:-2px}",
        ".dt-buttons .dt-button.dt-btn-colvis{background:#6b7280!important;border-color:#6b7280!important;color:#fff!important;display:inline-flex;align-items:center;gap:.35rem}",
        ".dt-buttons .dt-button.dt-btn-colvis:hover{background:#4b5563!important;border-color:#4b5563!important;color:#fff!important}",
        ".dt-buttons .dt-button.dt-btn-colvis i{color:#fff}",
        ".dt-buttons .dt-button.dt-btn-colvis span{color:#fff;font-weight:500}",
        // === Menu desplegable de Columnas (ColVis) ===
        ".dt-button-collection{padding:.4rem 0!important;border-radius:.6rem!important;box-shadow:0 6px 24px rgba(0,0,0,.12)!important;border:1px solid #e5e7eb!important;min-width:220px!important;background:#fff!important}",
        ".dt-button-collection .dt-button{display:flex!important;align-items:center;gap:.6rem;width:100%!important;padding:.5rem 1rem!important;margin:0!important;border-radius:0!important;background:transparent!important;color:#374151!important;font-size:.875rem;border:0!important;text-align:left!important;cursor:pointer;transition:background .12s ease}",
        ".dt-button-collection .dt-button:hover{background:#f3f4f6!important;color:#111827!important}",
        ".dt-button-collection .dt-button:before{content:'00d';font-family:'Font Awesome 6 Free','FontAwesome';font-weight:900;font-size:.75rem;display:inline-flex;align-items:center;justify-content:center;width:18px;height:18px;border-radius:.25rem;background:#fee2e2;color:#dc2626;flex-shrink:0}",
        ".dt-button-collection .dt-button.active:before{content:'00c';background:#d1fae5;color:#059669}",
        ".dt-button-collection .dt-button.active{background:#fff!important;color:#111827!important;font-weight:500}",
        ".dt-button-collection .dt-button.active:hover{background:#f3f4f6!important}",
        ".dt-button-collection .dt-button:focus{box-shadow:none!important;outline:0!important}"
    ].join('');
    document.head.appendChild(s);
})();
