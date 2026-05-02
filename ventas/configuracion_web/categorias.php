<?php
require_once __DIR__ . '/_public_web_store.php';
$error=''; $ok='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    cw_add_category((string)($_POST['nombre'] ?? ''), (string)($_POST['imagen'] ?? ''));
    $ok = 'Categoría creada.';
  } catch (Throwable $e) { $error = $e->getMessage(); }
}
$data = cw_load();
?><h1>Categorías públicas</h1>
<?php if($ok): ?><p><?= htmlspecialchars($ok) ?></p><?php endif; ?>
<?php if($error): ?><p><?= htmlspecialchars($error) ?></p><?php endif; ?>
<form method="post">
  <label>Nombre de categoría <input required name="nombre"></label><br>
  <label>Imagen de categoría <input name="imagen" placeholder="ninos.webp o /ventas/uploads/..." required></label><br>
  <button>Crear</button>
</form>
<ul><?php foreach(($data['categorias'] ?? []) as $c): ?><li><?= htmlspecialchars($c['nombre']) ?> | slug: <?= htmlspecialchars($c['slug']) ?> | url: <?= htmlspecialchars($c['url']) ?></li><?php endforeach; ?></ul>
<p><a href="index.php">Volver</a></p>
