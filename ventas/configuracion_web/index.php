<?php
require_once __DIR__ . '/_public_web_store.php';
require_once __DIR__ . '/layout_web.php';
$data = cw_load();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data['config']['logo'] = trim((string)($_POST['logo'] ?? ''));
  $data['negocio']['nombre'] = trim((string)($_POST['nombre'] ?? ''));
  $data['negocio']['descripcion'] = trim((string)($_POST['descripcion'] ?? ''));
  cw_save($data);
  $msg = 'Configuración pública guardada.';
}
cw_layout_header('Configuración Web Pública');
?>
<div class="cw-card"><h1>Configuración Web Pública</h1><?php if($msg): ?><p class="cw-msg ok"><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<form method="post">
<label>Logo (/ventas/uploads/...): <input class="cw-input" name="logo" value="<?= htmlspecialchars($data['config']['logo']) ?>"></label><br>
<label>Nombre negocio: <input class="cw-input" name="nombre" value="<?= htmlspecialchars($data['negocio']['nombre']) ?>"></label><br>
<label>Descripción: <textarea class="cw-textarea" name="descripcion"><?= htmlspecialchars($data['negocio']['descripcion']) ?></textarea></label><br>
<button class="cw-btn">Guardar</button>
</form></div>
<?php cw_layout_footer(); ?>
