<?php
require_once __DIR__ . '/_public_web_store.php';
require_once __DIR__ . '/layout_web.php';

function cw_find_category(array $data, int $catId): ?array {
  foreach (($data['categorias'] ?? []) as $c) if ((int)($c['id'] ?? 0) === $catId) return $c;
  return null;
}
$data = cw_load();
$msg=(string)($_GET['ok'] ?? ''); $err='';
$editId = (int)($_GET['edit'] ?? $_POST['edit_id'] ?? 0);
$editProduct = null;
foreach (($data['productos'] ?? []) as $p) if ((int)($p['id'] ?? 0) === $editId) { $editProduct = $p; break; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  try {
    if (($_POST['action'] ?? '') === 'delete') {
      cw_delete_product((int)($_POST['delete_id'] ?? 0));
      header('Location: ' . basename(__FILE__) . '?ok=1'); exit;
    } else {
      $catId = (int)($_POST['categoria_id'] ?? 0);
      $nombre = trim((string)($_POST['nombre'] ?? ''));
      $cat = cw_find_category($data, $catId);
      if ($nombre === '' || !$cat) throw new RuntimeException('Nombre y categoría válida son obligatorios.');
      $img = cw_upload_public_image($_FILES["imagen"] ?? [], "productos");
      $payload = ['nombre'=>$nombre,'slug'=>cw_slug($nombre),'categoria_id'=>$catId,'categoria_slug'=>(string)($cat['slug'] ?? ''),'precio'=>(float)($_POST['precio'] ?? 0),'descripcion'=>trim((string)($_POST['descripcion'] ?? '')),'descripcion_larga'=>trim((string)($_POST['descripcion_larga'] ?? '')),'tallas'=>trim((string)($_POST['tallas'] ?? '')),'colores'=>trim((string)($_POST['colores'] ?? '')),'precio_oferta'=>(float)($_POST['precio_oferta'] ?? 0),'destacado'=>isset($_POST['destacado'])?1:0,'stock'=>trim((string)($_POST['stock'] ?? '')),'orden'=>(int)($_POST['orden'] ?? 0),'activo'=>isset($_POST['activo']) ? 1 : 0];
      if ($img !== '') $payload['imagen_principal'] = $img;
      $id = (int)($_POST['edit_id'] ?? 0);
      if ($id > 0) {
        cw_update_product($id, $payload);
        header('Location: ' . basename(__FILE__) . '?ok=1'); exit;
      } else {
        $payload = array_replace(['imagen_principal'=>'','precio_oferta'=>0,'destacado'=>0,'stock'=>'','orden'=>0], $payload);
        cw_add_product($payload);
        header('Location: ' . basename(__FILE__) . '?ok=1'); exit;
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
<?php if(!empty($editProduct['imagen_principal'])): ?><small>Actual: <?= htmlspecialchars(cw_public_asset_url((string)$editProduct['imagen_principal'])) ?></small><br><?php endif; ?>
<input class="cw-input" type="number" step="0.01" name="precio" placeholder="Precio" value="<?= htmlspecialchars((string)($editProduct['precio'] ?? '')) ?>"><br>
<input class="cw-input" type="number" step="0.01" name="precio_oferta" placeholder="Precio oferta" value="<?= htmlspecialchars((string)($editProduct['precio_oferta'] ?? '')) ?>"><br>
<textarea class="cw-textarea" name="descripcion" placeholder="Descripción corta"><?= htmlspecialchars((string)($editProduct['descripcion'] ?? '')) ?></textarea><br>
<textarea class="cw-textarea" name="descripcion_larga" placeholder="Descripción larga"><?= htmlspecialchars((string)($editProduct['descripcion_larga'] ?? '')) ?></textarea><br>
<input class="cw-input" name="tallas" placeholder="Tallas (S,M,L)" value="<?= htmlspecialchars((string)($editProduct['tallas'] ?? '')) ?>"><br>
<input class="cw-input" name="colores" placeholder="Colores (Negro,Blanco)" value="<?= htmlspecialchars((string)($editProduct['colores'] ?? '')) ?>"><br>
<input class="cw-input" name="stock" placeholder="Stock/disponible" value="<?= htmlspecialchars((string)($editProduct['stock'] ?? '')) ?>"><br>
<input class="cw-input" type="number" name="orden" placeholder="Orden" value="<?= htmlspecialchars((string)($editProduct['orden'] ?? '')) ?>"><br>
<label><input type="checkbox" name="destacado" <?= ((int)($editProduct['destacado'] ?? 0)===1)?'checked':'' ?>> Destacado</label><br>
<label><input type="checkbox" name="activo" <?= ((int)($editProduct['activo'] ?? 1)===1)?'checked':'' ?>> Activo</label><br><br>
<button class="cw-btn"><?= $editProduct ? 'Actualizar producto' : 'Guardar producto' ?></button></form></div>
<div class="cw-card"><h2>Listado de productos</h2>
<?php foreach(($data['productos'] ?? []) as $p): ?>
  <div style="border-bottom:1px solid #2f2f38;padding:10px 0;display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <?php if(!empty($p['imagen_principal'])): ?><img src="<?= htmlspecialchars(cw_public_asset_url((string)$p['imagen_principal'])) ?>" alt="" style="width:56px;height:56px;object-fit:cover;border-radius:8px"><?php endif; ?>
    <div>
      <b><?= htmlspecialchars((string)($p['nombre'] ?? '')) ?></b>
      <?php $ruta=(string)($p['imagen_principal'] ?? ''); $u=cw_public_asset_url($ruta); $f=__DIR__.'/../'.ltrim(str_replace('/ventas/','',$ruta),'/'); ?><div>Categoría: <?= htmlspecialchars((string)($p['categoria_slug'] ?? '')) ?> | Precio: <?= htmlspecialchars((string)($p['precio'] ?? '0')) ?></div><div>Ruta: <?= htmlspecialchars($ruta) ?></div><div>URL renderizada: <?= htmlspecialchars($u) ?></div><div>Archivo físico: <?= is_file($f)?'OK':'NO EXISTE' ?> | <?= htmlspecialchars(cw_drive_status_text()) ?></div>
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
