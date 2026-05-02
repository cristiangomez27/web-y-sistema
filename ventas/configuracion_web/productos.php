<?php
require_once __DIR__ . '/_public_web_store.php';
require_once __DIR__ . '/layout_web.php';
$data = cw_load();
$msg=''; $err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $catId = (int)($_POST['categoria_id'] ?? 0);
  $nombre = trim((string)($_POST['nombre'] ?? ''));
  $cat = null; foreach (($data['categorias'] ?? []) as $c) { if ((int)($c['id'] ?? 0) === $catId) { $cat = $c; break; } }
  if ($nombre === '' || !$cat) { $err = 'Nombre y categoría válida son obligatorios.'; }
  else {
    $data['productos'][] = ['id'=>cw_next_id($data['productos'] ?? []), 'nombre'=>$nombre, 'slug'=>cw_slug($nombre), 'categoria_id'=>$catId, 'categoria_slug'=>(string)($cat['slug'] ?? ''), 'imagen_principal'=>trim((string)($_POST['imagen_principal'] ?? '')), 'precio'=>(float)($_POST['precio'] ?? 0), 'descripcion'=>trim((string)($_POST['descripcion'] ?? '')), 'descripcion_larga'=>trim((string)($_POST['descripcion_larga'] ?? '')), 'tallas'=>trim((string)($_POST['tallas'] ?? '')), 'colores'=>trim((string)($_POST['colores'] ?? '')), 'activo'=>isset($_POST['activo']) ? 1 : 0, 'precio_oferta'=>0, 'destacado'=>0, 'orden'=>cw_next_order($data['productos'] ?? [])];
    cw_save($data); $msg='Producto guardado.';
  }
}
cw_layout_header('Productos públicos');
?>
<div class="cw-card"><h1>Productos públicos</h1><?php if($msg): ?><p class="cw-msg ok"><?= htmlspecialchars($msg) ?></p><?php endif; ?><?php if($err): ?><p class="cw-msg err"><?= htmlspecialchars($err) ?></p><?php endif; ?>
<form method="post">
<input class="cw-input" name="nombre" required placeholder="Nombre del producto"><br>
<select class="cw-select" name="categoria_id" required><option value="">Categoría</option><?php foreach(($data['categorias'] ?? []) as $c): ?><option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?></select><br>
<input class="cw-input" name="imagen_principal" placeholder="/ventas/uploads/..." required><br>
<input class="cw-input" type="number" step="0.01" name="precio" placeholder="Precio"><br>
<textarea class="cw-textarea" name="descripcion" placeholder="Descripción corta"></textarea><br>
<textarea class="cw-textarea" name="descripcion_larga" placeholder="Descripción larga"></textarea><br>
<input class="cw-input" name="tallas" placeholder="Tallas (S,M,L)"><br>
<input class="cw-input" name="colores" placeholder="Colores (Negro,Blanco)"><br>
<label><input type="checkbox" name="activo" checked> Activo</label><br><br>
<button class="cw-btn">Guardar producto</button></form></div>
<div class="cw-card"><ul><?php foreach(($data['productos'] ?? []) as $p): ?><li><?= htmlspecialchars($p['nombre']) ?> (cat_id: <?= (int)$p['categoria_id'] ?>)</li><?php endforeach; ?></ul></div>
<?php cw_layout_footer(); ?>
