<?php
require_once __DIR__ . '/_public_web_store.php'; require_once __DIR__ . '/layout_web.php';
$data=cw_load(); $error=''; $ok=''; $editId=(int)($_GET['edit'] ?? $_POST['edit_id'] ?? 0); $edit=null; foreach(($data['categorias']??[]) as $c){if((int)$c['id']===$editId)$edit=$c;}
if($_SERVER['REQUEST_METHOD']==='POST'){
  try{
    if(($_POST['action']??'')==='delete'){ $id=(int)($_POST['delete_id']??0); cw_delete_category($id); $ok='Categoría eliminada.'; }
    else {
      $img=cw_upload_public_image($_FILES['imagen'] ?? [], 'categorias');
      $name=trim((string)($_POST['nombre']??'')); if($name==='') throw new RuntimeException('Nombre requerido.');
      $id=(int)($_POST['edit_id']??0);
      if($id>0){ $changes=['nombre'=>$name,'activa'=>isset($_POST['activa'])?1:0,'menu'=>isset($_POST['menu'])?1:0,'footer'=>isset($_POST['footer'])?1:0]; if($img!=='') $changes['imagen']=$img; cw_update_category($id,$changes); header('Location: ' . basename(__FILE__) . '?ok=1'); exit; }
      else { cw_add_category($name, $img); header('Location: ' . basename(__FILE__) . '?ok=1'); exit; }
    }
  }catch(Throwable $e){$error=$e->getMessage();}
  $data=cw_load();
}
cw_layout_header('Categorías públicas'); ?>
<div class="cw-card"><h1>Categorías públicas</h1><?php if($ok):?><p class="cw-msg ok"><?=htmlspecialchars($ok)?></p><?php endif;?><?php if($error):?><p class="cw-msg err"><?=htmlspecialchars($error)?></p><?php endif;?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="edit_id" value="<?= (int)($edit['id']??0) ?>">
<label>Nombre de categoría <input class="cw-input" required name="nombre" value="<?= htmlspecialchars((string)($edit['nombre']??'')) ?>"></label><br>
<label>Imagen de categoría <input type="file" class="cw-input" name="imagen" accept="image/*"></label><br>
<?php if(!empty($edit['imagen'])): ?><img src="<?= htmlspecialchars(cw_public_asset_url((string)$edit['imagen'])) ?>" style="width:64px;height:64px;object-fit:cover;border-radius:8px"><?php endif; ?><br>
<label><input type="checkbox" name="activa" <?= ((int)($edit['activa']??1)===1)?'checked':'' ?>> Activa</label>
<label><input type="checkbox" name="menu" <?= ((int)($edit['menu']??1)===1)?'checked':'' ?>> Menú</label>
<label><input type="checkbox" name="footer" <?= ((int)($edit['footer']??1)===1)?'checked':'' ?>> Footer</label><br><br>
<button class="cw-btn"><?= $edit ? 'Actualizar' : 'Crear' ?></button></form></div>
<div class="cw-card"><h2>Listado</h2>
<?php foreach(($data['categorias']??[]) as $c): ?><div style="display:flex;gap:12px;align-items:center;border-bottom:1px solid #2f2f38;padding:8px 0">
<?php if(!empty($c['imagen'])):?><img src="<?= htmlspecialchars(cw_public_asset_url((string)$c['imagen'])) ?>" style="width:56px;height:56px;object-fit:cover;border-radius:8px"><?php endif; ?>
<div><b><?=htmlspecialchars($c['nombre'])?></b><div><?=htmlspecialchars($c['slug'])?> | <?=htmlspecialchars($c['url'])?></div><?php $ruta=(string)($c['imagen'] ?? ''); $f=__DIR__.'/../'.ltrim(str_replace('/ventas/','',$ruta),'/'); ?><div>Ruta: <?= htmlspecialchars($ruta) ?></div><div>Archivo físico: <?= is_file($f) ? 'OK' : 'NO EXISTE' ?></div></div>
<a class="cw-btn" href="?edit=<?= (int)$c['id'] ?>">Editar</a>
<form method="post" style="display:inline"><input type="hidden" name="action" value="delete"><input type="hidden" name="delete_id" value="<?= (int)$c['id'] ?>"><button class="cw-btn" onclick="return confirm('¿Eliminar?')">Eliminar</button></form>
</div><?php endforeach; ?></div>
<?php cw_layout_footer(); ?>
