<?php
// Partial: bloque de carga de archivo Excel.
// Mantiene los IDs estandar usados por los modulos:
//   #cargarDatosExcel  (form)
//   #excel             (input file)
//   #errorExcel        (span de error)
//   #btnCargar         (boton submit)
// Variables opcionales en scope:
//   $tituloCarga      - h5 grande   (default: "Cargar registros desde Excel")
//   $descripcionCarga - texto bajo  (default: "Sube un archivo .xlsx con los registros a registrar.")
$_tituloCarga      = isset($tituloCarga)      && $tituloCarga      !== '' ? $tituloCarga      : 'Cargar registros desde Excel';
$_descripcionCarga = isset($descripcionCarga) && $descripcionCarga !== '' ? $descripcionCarga : 'Sube un archivo <strong>.xlsx</strong> con los registros a registrar.';
?>
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card border-0 bg-light-subtle">
            <div class="card-body text-center p-4">
                <i class="bx bx-cloud-upload text-primary" style="font-size:60px;"></i>
                <h5 class="fw-semibold mt-2 mb-1"><?php echo htmlspecialchars($_tituloCarga, ENT_QUOTES, 'UTF-8'); ?></h5>
                <p class="text-muted small mb-3"><?php echo $_descripcionCarga; ?></p>
                <form id="cargarDatosExcel" action="" method="post" enctype="multipart/form-data">
                    <div class="mb-3 text-start">
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
        </div>
    </div>
</div>
