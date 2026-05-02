<?php
require_once __DIR__ . '/_public_web_store.php';
$data = cw_load();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data['config']['banner_principal'] = trim((string)($_POST['banner_principal'] ?? ''));
  $data['config']['color_primario'] = trim((string)($_POST['color_primario'] ?? '#d4af37'));
  $data['config']['color_secundario'] = trim((string)($_POST['color_secundario'] ?? '#ffe08a'));
  cw_save($data);
  $msg = 'Diseño público guardado.';
}
?><h1>Diseño Web Pública</h1><?php if($msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<form method="post">
<input name="banner_principal" value="<?= htmlspecialchars($data['config']['banner_principal']) ?>" placeholder="Banner"><br>
<input name="color_primario" value="<?= htmlspecialchars($data['config']['color_primario']) ?>"><br>
<input name="color_secundario" value="<?= htmlspecialchars($data['config']['color_secundario']) ?>"><br>
<button>Guardar</button></form>
<p><a href="index.php">Volver</a></p>
