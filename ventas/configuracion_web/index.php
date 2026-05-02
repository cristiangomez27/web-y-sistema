<?php
require_once __DIR__ . '/_public_web_store.php';
$data = cw_load();
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data['config']['logo'] = trim((string)($_POST['logo'] ?? ''));
  $data['negocio']['nombre'] = trim((string)($_POST['nombre'] ?? ''));
  $data['negocio']['descripcion'] = trim((string)($_POST['descripcion'] ?? ''));
  cw_save($data);
  $msg = 'Configuración pública guardada.';
}
?><h1>Configuración Web Pública</h1><?php if($msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endif; ?>
<form method="post">
<label>Logo (/ventas/uploads/...): <input name="logo" value="<?= htmlspecialchars($data['config']['logo']) ?>"></label><br>
<label>Nombre negocio: <input name="nombre" value="<?= htmlspecialchars($data['negocio']['nombre']) ?>"></label><br>
<label>Descripción: <textarea name="descripcion"><?= htmlspecialchars($data['negocio']['descripcion']) ?></textarea></label><br>
<button>Guardar</button>
</form>
<p><a href="categorias.php">Categorías</a> | <a href="productos.php">Productos</a> | <a href="diseno.php">Diseño</a></p>
