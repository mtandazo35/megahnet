<?php
// Partial: titulo de listado (encima de DataTable). Reusable.
// Variables esperadas:
//   $tituloListado  - texto del titulo (ej. "Listado de Creditos")
//   $iconoListado   - clase Boxicons (ej. "bx-credit-card"); opcional, default bx-list-ul
$_iconoListado = isset($iconoListado) && $iconoListado !== '' ? $iconoListado : 'bx-list-ul';
$_tituloListado = isset($tituloListado) ? $tituloListado : 'Listado';
?>
<div class="listado-header">
    <h6 class="mb-0 fw-semibold"><i class="bx <?php echo htmlspecialchars($_iconoListado, ENT_QUOTES, 'UTF-8'); ?> me-1 text-primary"></i><?php echo htmlspecialchars($_tituloListado, ENT_QUOTES, 'UTF-8'); ?></h6>
</div>
