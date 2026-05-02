<?php
require_once __DIR__ . '/_public_web_store.php'; require_once __DIR__ . '/layout_web.php';
$data=cw_load(); $msg=(string)($_GET['ok'] ?? '');
if($_SERVER['REQUEST_METHOD']==='POST'){
  foreach(['banner_principal','fondo_principal','logo'] as $f){ $u=cw_upload_public_image($_FILES[$f] ?? [], 'diseno'); if($u!=='') $data['config'][$f]=$u; }
  $data['config']['color_primario']=trim((string)($_POST['color_primario']??'#d4af37'));
  $data['config']['color_secundario']=trim((string)($_POST['color_secundario']??'#ffe08a'));
  cw_save($data); header('Location: ' . basename(__FILE__) . '?ok=1'); exit;
}
cw_layout_header('Diseño Web Pública'); $c=$data['config']??[]; ?>
<div class="cw-card"><h1>Diseño Web Pública</h1><?php if($msg):?><p class="cw-msg ok"><?=htmlspecialchars($msg)?></p><?php endif;?>
<form method="post" enctype="multipart/form-data">
<label>Banner principal <input type="file" class="cw-input" name="banner_principal" accept="image/*"></label><?php if(!empty($c['banner_principal'])):?><img src="<?= htmlspecialchars(cw_public_asset_url((string)$c['banner_principal'])) ?>" style="width:120px"><?php endif; ?><br>
<label>Fondo principal <input type="file" class="cw-input" name="fondo_principal" accept="image/*"></label><?php if(!empty($c['fondo_principal'])):?><img src="<?= htmlspecialchars(cw_public_asset_url((string)$c['fondo_principal'])) ?>" style="width:120px"><?php endif; ?><br>
<label>Logo <input type="file" class="cw-input" name="logo" accept="image/*"></label><br>
<input class="cw-input" name="color_primario" value="<?= htmlspecialchars((string)($c['color_primario']??'')) ?>"><br>
<input class="cw-input" name="color_secundario" value="<?= htmlspecialchars((string)($c['color_secundario']??'')) ?>"><br>
<button class="cw-btn">Guardar</button></form></div>
<?php cw_layout_footer(); ?>
