<?php
require_once __DIR__ . '/_public_web_store.php';
require_once __DIR__ . '/layout_web.php';
$error=''; $ok='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    cw_add_category((string)($_POST['nombre'] ?? ''), (string)($_POST['imagen'] ?? ''));
    $ok = 'Categoría creada.';
  } catch (Throwable $e) { $error = $e->getMessage(); }
}
$data = cw_load();
cw_layout_header('Categorías públicas');
?>
<div class="cw-card">
  <h1>Categorías públicas</h1>
  <?php if($ok): ?><p class="cw-msg ok"><?= htmlspecialchars($ok) ?></p><?php endif; ?>
  <?php if($error): ?><p class="cw-msg err"><?= htmlspecialchars($error) ?></p><?php endif; ?>
  <form method="post">
    <label>Nombre de categoría <input class="cw-input" required name="nombre"></label><br>
    <label>Imagen de categoría <input class="cw-input" name="imagen" placeholder="ninos.webp o /ventas/uploads/..." required></label><br><br>
    <button class="cw-btn">Crear</button>
  </form>
</div>
<div class="cw-card">
  <h2>Listado</h2>
  <ul><?php foreach(($data['categorias'] ?? []) as $c): ?><li><?= htmlspecialchars($c['nombre']) ?> | slug: <?= htmlspecialchars($c['slug']) ?> | url: <?= htmlspecialchars($c['url']) ?></li><?php endforeach; ?></ul>
</div>
<?php cw_layout_footer(); ?>
