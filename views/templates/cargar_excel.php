<?php
// Partial: bloque de carga de archivo Excel. Adaptable: ocupa el ancho del
// contenedor padre (modal o tab pane). El caller puede envolver en row/col
// si quiere centrarlo en pantalla completa.
//   #cargarDatosExcel  (form)
//   #excel             (input file)
//   #errorExcel        (span de error)
//   #btnCargar         (boton submit)
// Variables opcionales en scope:
//   $tituloCarga      - titulo
//   $descripcionCarga - texto debajo
$_tituloCarga      = isset($tituloCarga)      && $tituloCarga      !== '' ? $tituloCarga      : 'Cargar registros desde Excel';
$_descripcionCarga = isset($descripcionCarga) && $descripcionCarga !== '' ? $descripcionCarga : 'Sube un archivo <strong>.xlsx</strong> con los registros a registrar.';
?>
<div class="cargar-excel-box">
    <div class="text-center mb-3">
        <i class="bx bx-cloud-upload text-primary cargar-excel-icon"></i>
        <h6 class="fw-semibold mt-1 mb-1"><?php echo htmlspecialchars($_tituloCarga, ENT_QUOTES, 'UTF-8'); ?></h6>
        <p class="text-muted small mb-0"><?php echo $_descripcionCarga; ?></p>
    </div>
    <form id="cargarDatosExcel" action="" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label small mb-1" for="excel">Archivo Excel</label>
            <input class="form-control" type="file" name="excel" id="excel" accept=".xlsx">
            <span id="errorExcel" class="text-danger small"></span>
        </div>
        <div class="d-grid">
            <button class="btn btn-primary" type="submit" id="btnCargar">
                <i class="bx bx-upload me-1"></i>Registrar
            </button>
        </div>
    </form>
</div>
