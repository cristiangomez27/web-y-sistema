<?php
require_once __DIR__ . '/_public_web_store.php';
$data = cw_load();
$msg=''; $err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $catId = (int)($_POST['categoria_id'] ?? 0);
  $nombre = trim((string)($_POST['nombre'] ?? ''));
  $cat = null; foreach (($data['categorias'] ?? []) as $c) { if ((int)($c['id'] ?? 0) === $catId) { $cat = $c; break; } }
  if ($nombre === '' || !$cat) { $err = 'Nombre y categoría válida son obligatorios.'; }
  else {
    $data['productos'][] = [
      'id'=>cw_next_id($data['productos'] ?? []), 'nombre'=>$nombre, 'slug'=>cw_slug($nombre),
      'categoria_id'=>$catId, 'categoria_slug'=>(string)($cat['slug'] ?? ''),
      'imagen_principal'=>trim((string)($_POST['imagen_principal'] ?? '')),
      'precio'=>(float)($_POST['precio'] ?? 0), 'descripcion'=>trim((string)($_POST['descripcion'] ?? '')),
      'descripcion_larga'=>trim((string)($_POST['descripcion_larga'] ?? '')),
      'tallas'=>trim((string)($_POST['tallas'] ?? '')), 'colores'=>trim((string)($_POST['colores'] ?? '')),
      'activo'=>isset($_POST['activo']) ? 1 : 0, 'precio_oferta'=>0, 'destacado'=>0, 'orden'=>cw_next_order($data['productos'] ?? []),
    ];
    cw_save($data); $msg='Producto guardado.';
  }
}
?><h1>Productos públicos</h1><?php if($msg): ?><p><?= htmlspecialchars($msg) ?></p><?php endif; ?><?php if($err): ?><p><?= htmlspecialchars($err) ?></p><?php endif; ?>
<form method="post">
<input name="nombre" required placeholder="Nombre del producto"><br>
<select name="categoria_id" required><option value="">Categoría</option><?php foreach(($data['categorias'] ?? []) as $c): ?><option value="<?= (int)$c['id'] ?>"><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?></select><br>
<input name="imagen_principal" placeholder="/ventas/uploads/..." required><br>
<input type="number" step="0.01" name="precio" placeholder="Precio"><br>
<textarea name="descripcion" placeholder="Descripción corta"></textarea><br>
<textarea name="descripcion_larga" placeholder="Descripción larga"></textarea><br>
<input name="tallas" placeholder="Tallas (S,M,L)"><br>
<input name="colores" placeholder="Colores (Negro,Blanco)"><br>
<label><input type="checkbox" name="activo" checked> Activo</label><br>
<button>Guardar producto</button></form>
<ul><?php foreach(($data['productos'] ?? []) as $p): ?><li><?= htmlspecialchars($p['nombre']) ?> (cat_id: <?= (int)$p['categoria_id'] ?>)</li><?php endforeach; ?></ul>
<p><a href="index.php">Volver</a></p>
