<?php
// Pantalla de mantenimiento a pantalla completa (HTTP 503).
// Se incluye desde index.php cuando existe storage/update/maintenance.flag.
// Autocontenida: CSS inline, sin assets del panel (pueden estar actualizandose).
$__motivo = isset($__mantMotivo) ? (string)$__mantMotivo : 'actualizacion';
$__titulo = ($__motivo === 'reversion')
    ? 'Estamos revirtiendo la ultima actualizacion'
    : 'Estamos actualizando el sistema';
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="refresh" content="30">
<meta name="robots" content="noindex, nofollow">
<title><?php echo htmlspecialchars($__titulo, ENT_QUOTES, 'UTF-8'); ?></title>
<style>
  :root { color-scheme: light; }
  * { box-sizing: border-box; }
  body {
    margin: 0; min-height: 100vh;
    display: flex; align-items: center; justify-content: center;
    padding: 24px;
    background: #f4f6f9; color: #2f3542;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
  }
  .caja {
    background: #fff; border-radius: 14px; max-width: 560px; width: 100%;
    padding: 40px 32px; text-align: center;
    box-shadow: 0 10px 30px rgba(20, 30, 60, .10);
  }
  .icono {
    width: 88px; height: 88px; margin: 0 auto 20px;
    border-radius: 50%; background: #e8f0fe; color: #1d5ce0;
    display: flex; align-items: center; justify-content: center;
    font-size: 40px; font-weight: 700;
    animation: giro 2.4s linear infinite;
  }
  @keyframes giro { to { transform: rotate(360deg); } }
  @media (prefers-reduced-motion: reduce) { .icono { animation: none; } }
  h1 { font-size: 1.45rem; margin: 0 0 12px; font-weight: 600; }
  p  { margin: 0 0 10px; color: #6b7280; line-height: 1.55; }
  .nota { font-size: .82rem; color: #9aa1ab; margin-top: 22px; }
</style>
</head>
<body>
  <div class="caja">
    <div class="icono">&#10227;</div>
    <h1><?php echo htmlspecialchars($__titulo, ENT_QUOTES, 'UTF-8'); ?></h1>
    <p>El servicio estara disponible en unos minutos. No es necesario que hagas nada:
       esta pagina se recarga sola cada 30 segundos.</p>
    <p>Si llevas mucho rato viendo esta pantalla, contacta con el administrador del sistema.</p>
    <div class="nota">Codigo 503 &middot; Servicio temporalmente no disponible</div>
  </div>
</body>
</html>
