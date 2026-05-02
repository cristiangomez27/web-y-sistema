<?php
require_once __DIR__ . '/_public_web_store.php';
require_once __DIR__ . '/layout_web.php';

function cw_find_category(array $data, int $catId): ?array {
  foreach (($data['categorias'] ?? []) as $c) if ((int)($c['id'] ?? 0) === $catId) return $c;
  return null;
}
function cw_upload_image(): string {
  if (empty($_FILES['imagen']) || !is_array($_FILES['imagen']) || (int)($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
  if ((int)$_FILES['imagen']['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Error al subir imagen.');
  if ((int)$_FILES['imagen']['size'] > 5 * 1024 * 1024) throw new RuntimeException('La imagen supera 5MB.');
  $tmp = (string)$_FILES['imagen']['tmp_name'];
  $mime = (string)(mime_content_type($tmp) ?: '');
  if (!str_starts_with($mime, 'image/')) throw new RuntimeException('Archivo inválido: solo imágenes.');
  $ext = strtolower(pathinfo((string)$_FILES['imagen']['name'], PATHINFO_EXTENSION));
  if (!in_array($ext, ['jpg','jpeg','png','webp','gif'], true)) $ext = 'webp';
  $relDir = '/ventas/uploads/web/productos';
  $dir = realpath(__DIR__ . '/../..') . '/uploads/web/productos';
  if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('No se pudo crear carpeta de imágenes.');
  $name = 'producto_' . time() . '_' . bin2hex(random_bytes(3)) . '.' . $ext;
  $dest = $dir . '/' . $name;
  if (!move_uploaded_file($tmp, $dest)) throw new RuntimeException('No se pudo guardar la imagen.');
  return $relDir . '/' . $name;
}

$data = cw_load();
$msg=''; $err='';
$editId = (int)($_GET['edit'] ?? $_POST['edit_id'] ?? 0);
$editProduct = null;
foreach (($data['productos'] ?? []) as $p) if ((int)($p['id'] ?? 0) === $editId) { $editProduct = $p; break; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (($_POST['action'] ?? '') === 'delete') {
      cw_delete_product((int)($_POST['delete_id'] ?? 0));
      $msg = 'Producto eliminado.';
    } else {
      $catId = (int)($_POST['categoria_id'] ?? 0);
      $nombre = trim((string)($_POST['nombre'] ?? ''));
      $cat = cw_find_category($data, $catId);
      if ($nombre === '' || !$cat) throw new RuntimeException('Nombre y categoría válida son obligatorios.');
      $img = cw_upload_image();
      $payload = ['nombre'=>$nombre,'slug'=>cw_slug($nombre),'categoria_id'=>$catId,'categoria_slug'=>(string)($cat['slug'] ?? ''),'precio'=>(float)($_POST['precio'] ?? 0),'descripcion'=>trim((string)($_POST['descripcion'] ?? '')),'descripcion_larga'=>trim((string)($_POST['descripcion_larga'] ?? '')),'tallas'=>trim((string)($_POST['tallas'] ?? '')),'colores'=>trim((string)($_POST['colores'] ?? '')),'activo'=>isset($_POST['activo']) ? 1 : 0];
      if ($img !== '') $payload['imagen_principal'] = $img;
      $id = (int)($_POST['edit_id'] ?? 0);
      if ($id > 0) {
        cw_update_product($id, $payload);
        $msg = 'Producto actualizado.';
      } else {
        $data = cw_load();
        $payload = array_replace(['id'=>cw_next_id($data['productos'] ?? []),'imagen_principal'=>'','precio_oferta'=>0,'destacado'=>0,'orden'=>cw_next_order($data['productos'] ?? [])], $payload);
        $data['productos'][] = $payload; cw_save($data);
        $msg = 'Producto guardado.';
      }
    }
  } catch (Throwable $e) { $err = $e->getMessage(); }
  $data = cw_load();
  $editId = (int)($_GET['edit'] ?? 0);
  $editProduct = null; foreach (($data['productos'] ?? []) as $p) if ((int)($p['id'] ?? 0) === $editId) { $editProduct = $p; break; }
}

cw_layout_header('Productos públicos');
?>
<div class="cw-card"><h1>Productos públicos</h1><?php if($msg): ?><p class="cw-msg ok"><?= htmlspecialchars($msg) ?></p><?php endif; ?><?php if($err): ?><p class="cw-msg err"><?= htmlspecialchars($err) ?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="edit_id" value="<?= (int)($editProduct['id'] ?? 0) ?>">
<input class="cw-input" name="nombre" required placeholder="Nombre del producto" value="<?= htmlspecialchars((string)($editProduct['nombre'] ?? '')) ?>"><br>
<select class="cw-select" name="categoria_id" required><option value="">Categoría</option><?php foreach(($data['categorias'] ?? []) as $c): ?><option value="<?= (int)$c['id'] ?>" <?= ((int)($editProduct['categoria_id'] ?? 0)===(int)$c['id'])?'selected':'' ?>><?= htmlspecialchars($c['nombre']) ?></option><?php endforeach; ?></select><br>
<input type="file" class="cw-input" name="imagen" accept="image/*"><br>
<?php if(!empty($editProduct['imagen_principal'])): ?><small>Actual: <?= htmlspecialchars((string)$editProduct['imagen_principal']) ?></small><br><?php endif; ?>
<input class="cw-input" type="number" step="0.01" name="precio" placeholder="Precio" value="<?= htmlspecialchars((string)($editProduct['precio'] ?? '')) ?>"><br>
<textarea class="cw-textarea" name="descripcion" placeholder="Descripción corta"><?= htmlspecialchars((string)($editProduct['descripcion'] ?? '')) ?></textarea><br>
<textarea class="cw-textarea" name="descripcion_larga" placeholder="Descripción larga"><?= htmlspecialchars((string)($editProduct['descripcion_larga'] ?? '')) ?></textarea><br>
<input class="cw-input" name="tallas" placeholder="Tallas (S,M,L)" value="<?= htmlspecialchars((string)($editProduct['tallas'] ?? '')) ?>"><br>
<input class="cw-input" name="colores" placeholder="Colores (Negro,Blanco)" value="<?= htmlspecialchars((string)($editProduct['colores'] ?? '')) ?>"><br>
<label><input type="checkbox" name="activo" <?= ((int)($editProduct['activo'] ?? 1)===1)?'checked':'' ?>> Activo</label><br><br>
<button class="cw-btn"><?= $editProduct ? 'Actualizar producto' : 'Guardar producto' ?></button></form></div>
<div class="cw-card"><h2>Listado de productos</h2>
<?php foreach(($data['productos'] ?? []) as $p): ?>
  <div style="border-bottom:1px solid #2f2f38;padding:10px 0;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <?php if(!empty($p['imagen_principal'])): ?><img src="<?= htmlspecialchars((string)$p['imagen_principal']) ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:8px"><?php endif; ?>
    <div>
      <b><?= htmlspecialchars((string)($p['nombre'] ?? '')) ?></b>
      <div>Categoría: <?= htmlspecialchars((string)($p['categoria_slug'] ?? '')) ?> | Precio: <?= htmlspecialchars((string)($p['precio'] ?? '0')) ?></div>
    </div>
    <a class="cw-btn" href="?edit=<?= (int)$p['id'] ?>">Editar</a>
    <form method="post" onsubmit="return confirm('¿Eliminar producto?');" style="display:inline">
      <input type="hidden" name="action" value="delete"><input type="hidden" name="delete_id" value="<?= (int)$p['id'] ?>">
      <button class="cw-btn" type="submit">Eliminar</button>
    </form>
  </div>
<?php endforeach; ?>
</div>
<?php cw_layout_footer(); ?>
