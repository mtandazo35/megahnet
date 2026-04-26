<?php
// Layout PDF reusable. Espera en scope:
//   $pdfTitulo     - texto del badge (ej. "REPORTE DE INVENTARIO")
//   $pdfSubtitulo  - texto opcional debajo del badge
//   $pdfMeta       - array de [['label' => 'Usuario', 'value' => 'Admin'], ...]
//                    (default: solo "Generado: dd/mm/aaaa HH:MM")
//   $pdfContenido  - HTML del contenido (sections, tables, etc.)
//   $empresa       - array (nombre, ruc, direccion, telefono, correo, mensaje, razon_social)

$_e = function ($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };

// Logo embebido base64 (Dompdf no resuelve URLs externas confiablemente).
$_logoSrc = '';
foreach (['logo.png', 'Logo.jpg', 'logo.jpg'] as $_logoName) {
    $_lp = ROOT_PATH . '/assets/images/' . $_logoName;
    if (is_file($_lp)) {
        $_data = @file_get_contents($_lp);
        if ($_data !== false) {
            $_ext = strtolower(pathinfo($_logoName, PATHINFO_EXTENSION));
            $_mime = $_ext === 'png' ? 'image/png' : 'image/jpeg';
            $_logoSrc = 'data:' . $_mime . ';base64,' . base64_encode($_data);
            break;
        }
    }
}

$_pdfTitulo    = isset($pdfTitulo)    ? $pdfTitulo    : 'REPORTE';
$_pdfSubtitulo = isset($pdfSubtitulo) ? $pdfSubtitulo : '';
$_pdfMeta      = isset($pdfMeta) && is_array($pdfMeta) ? $pdfMeta : [];
$_pdfMeta[]    = ['label' => 'Generado', 'value' => date('d/m/Y H:i')];
$_pdfContenido = isset($pdfContenido) ? $pdfContenido : '';
$_emp          = isset($empresa) && is_array($empresa) ? $empresa : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?php echo $_e($_pdfTitulo); ?></title>
<style>
    /* A4: 210x297mm. Margenes amplios para respiracion + espaciado aireado. */
    @page { size: A4; margin: 28mm 18mm; }
    * { box-sizing: border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        line-height: 1.5;
        color: #1f2937;
        margin: 0;
    }

    .header {
        width: 100%;
        border-bottom: 2px solid #2563eb;
        padding-bottom: 14px;
        margin-bottom: 22px;
    }
    .header table { width: 100%; border-collapse: collapse; }
    .header td { vertical-align: top; padding: 0; }
    .header .logo-cell { width: 110px; padding-right: 10px; }
    .header .logo-cell img { max-width: 100px; max-height: 70px; }
    .header .empresa-cell { padding-left: 4px; padding-right: 12px; }
    .header .empresa-cell .nombre { font-size: 16px; font-weight: bold; color: #111827; margin: 0 0 6px 0; }
    .header .empresa-cell p { margin: 2px 0; font-size: 11px; color: #4b5563; line-height: 1.4; }
    .header .doc-cell { width: 200px; text-align: right; }
    .header .doc-cell .badge {
        display: inline-block;
        background: #2563eb;
        color: #fff;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: bold;
        border-radius: 5px;
        letter-spacing: .5px;
    }
    .header .doc-cell .doc-meta { font-size: 10px; color: #6b7280; margin: 6px 0 0 0; line-height: 1.4; }
    .header .doc-cell .doc-meta strong { color: #111827; }
    .header .doc-cell .doc-sub { font-size: 12px; font-weight: bold; color: #111827; margin: 8px 0 4px 0; }

    h2.section {
        background: #f3f4f6;
        color: #111827;
        font-size: 13px;
        font-weight: bold;
        padding: 9px 12px;
        margin: 22px 0 10px 0;
        border-left: 4px solid #2563eb;
        text-transform: uppercase;
        letter-spacing: .8px;
    }

    table.dt {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 11px;
    }
    table.dt thead th {
        background: #2563eb;
        color: #fff;
        font-weight: bold;
        padding: 9px 10px;
        border: 1px solid #1d4ed8;
        text-align: center;
        line-height: 1.3;
    }
    table.dt tbody td {
        border: 1px solid #e5e7eb;
        padding: 8px 10px;
        line-height: 1.4;
    }
    table.dt tbody tr:nth-child(odd) td { background: #f9fafb; }
    table.dt .num { text-align: right; font-family: DejaVu Sans Mono, monospace; white-space: nowrap; }
    table.dt .ctr { text-align: center; }
    table.dt tfoot td {
        background: #ecfdf5; color: #065f46; font-weight: bold;
        padding: 9px 10px; border: 1px solid #a7f3d0;
    }
    table.dt .col-saldo { background: #ecfdf5 !important; color: #065f46; font-weight: bold; }

    .empty {
        background: #f9fafb; color: #9ca3af;
        text-align: center; padding: 18px;
        border: 1px dashed #d1d5db; font-style: italic;
        margin: 4px 0;
    }
    .footer-msg {
        margin-top: 22px; padding: 12px 14px;
        background: #fffbeb; border-left: 4px solid #f59e0b;
        font-size: 11px; color: #92400e; line-height: 1.5;
    }
    .gen-meta { margin-top: 18px; text-align: right; font-size: 9px; color: #9ca3af; }
</style>
</head>
<body>

<div class="header">
    <table>
        <tr>
            <td class="logo-cell">
                <?php if ($_logoSrc !== '') { ?><img src="<?php echo $_logoSrc; ?>" alt=""><?php } ?>
            </td>
            <td class="empresa-cell">
                <p class="nombre"><?php echo $_e(strtoupper($_emp['nombre'] ?? '')); ?></p>
                <?php if (!empty($_emp['razon_social'])) { ?>
                    <p><?php echo $_e($_emp['razon_social']); ?></p>
                <?php } ?>
                <?php if (!empty($_emp['direccion'])) { ?>
                    <p><?php echo $_e($_emp['direccion']); ?></p>
                <?php } ?>
                <?php if (!empty($_emp['ruc'])) { ?>
                    <p>RUC: <?php echo $_e($_emp['ruc']); ?></p>
                <?php } ?>
                <?php if (!empty($_emp['telefono'])) { ?>
                    <p>Teléfono: <?php echo $_e($_emp['telefono']); ?></p>
                <?php } ?>
                <?php if (!empty($_emp['correo'])) { ?>
                    <p>Email: <?php echo $_e($_emp['correo']); ?></p>
                <?php } ?>
            </td>
            <td class="doc-cell">
                <span class="badge"><?php echo $_e($_pdfTitulo); ?></span>
                <?php if ($_pdfSubtitulo !== '') { ?>
                    <p class="doc-sub"><?php echo $_e($_pdfSubtitulo); ?></p>
                <?php } ?>
                <?php foreach ($_pdfMeta as $_m) { ?>
                    <p class="doc-meta"><strong><?php echo $_e($_m['label']); ?>:</strong> <?php echo $_e($_m['value']); ?></p>
                <?php } ?>
            </td>
        </tr>
    </table>
</div>

<?php echo $_pdfContenido; ?>

<?php if (!empty($_emp['mensaje'])) { ?>
    <div class="footer-msg">
        <?php echo $_emp['mensaje']; ?>
    </div>
<?php } ?>

<div class="gen-meta">
    Generado por <?php echo $_e(defined('TITLE') ? TITLE : 'Sistema'); ?>
</div>

</body>
</html>
