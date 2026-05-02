<?php
require_once __DIR__ . '/_public_web_store.php'; require_once __DIR__ . '/layout_web.php';
$data=cw_load(); $msg=(string)($_GET['ok'] ?? '');
if($_SERVER['REQUEST_METHOD']==='POST'){
  $logo = cw_upload_public_image($_FILES['logo'] ?? [], 'diseno');
  if($logo!=='') $data['config']['logo']=$logo;
  $n=&$data['negocio'];
  foreach(['nombre','descripcion','footer_descripcion','correo','telefono','whatsapp','direccion','copyright','texto_atencion','texto_envios'] as $k){$n[$k]=trim((string)($_POST[$k]??''));}
  $n['redes']['tiktok']=trim((string)($_POST['tiktok']??'')); $n['redes']['facebook']=trim((string)($_POST['facebook']??'')); $n['redes']['instagram']=trim((string)($_POST['instagram']??''));
  $c=&$data['config']; $c['drive_enabled']=isset($_POST['drive_enabled'])?1:0; $c['drive_folder_id']=trim((string)($_POST['drive_folder_id']??'')); $c['drive_mode']=trim((string)($_POST['drive_mode']??'local')); $c['drive_status']=$c['drive_enabled'] && $c['drive_folder_id']==='' ? 'Drive activo sin carpeta' : ($c['drive_enabled'] ? 'Listo para sincronizar' : 'No configurado'); $c['drive_last_sync']=(string)($c['drive_last_sync'] ?? '');
  try { cw_save($data); header('Location: ' . basename(__FILE__) . '?ok=1'); exit; } catch (Throwable $e) { $msg = 'No se pudo guardar. Revisa permisos de la carpeta data.'; }
}
cw_layout_header('Negocio / Footer'); $n=$data['negocio']??[]; $r=$n['redes']??[]; $c=$data['config']??[]; ?>
<div class="cw-card"><h1>Datos del negocio / Footer</h1><?php if($msg):?><p class="cw-msg ok"><?=htmlspecialchars($msg)?></p><?php endif; ?>
<form method="post" enctype="multipart/form-data">
<label>Logo <input type="file" class="cw-input" name="logo" accept="image/*"></label><br>
<?php if(!empty($c['logo'])):?><img src="<?= htmlspecialchars(cw_public_asset_url((string)$c['logo'])) ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px"><?php endif; ?>
<input class="cw-input" name="nombre" placeholder="Nombre" value="<?= htmlspecialchars((string)($n['nombre']??'')) ?>"><br>
<textarea class="cw-textarea" name="descripcion" placeholder="Descripción corta"><?= htmlspecialchars((string)($n['descripcion']??'')) ?></textarea><br>
<textarea class="cw-textarea" name="footer_descripcion" placeholder="Descripción footer"><?= htmlspecialchars((string)($n['footer_descripcion']??'')) ?></textarea><br>
<input class="cw-input" name="correo" placeholder="Correo" value="<?= htmlspecialchars((string)($n['correo']??'')) ?>"><br>
<input class="cw-input" name="telefono" placeholder="Teléfono" value="<?= htmlspecialchars((string)($n['telefono']??'')) ?>"><br>
<input class="cw-input" name="whatsapp" placeholder="WhatsApp" value="<?= htmlspecialchars((string)($n['whatsapp']??'')) ?>"><br>
<input class="cw-input" name="direccion" placeholder="Dirección" value="<?= htmlspecialchars((string)($n['direccion']??'')) ?>"><br>
<input class="cw-input" name="tiktok" placeholder="TikTok" value="<?= htmlspecialchars((string)($r['tiktok']??'')) ?>"><br>
<input class="cw-input" name="facebook" placeholder="Facebook" value="<?= htmlspecialchars((string)($r['facebook']??'')) ?>"><br>
<input class="cw-input" name="instagram" placeholder="Instagram" value="<?= htmlspecialchars((string)($r['instagram']??'')) ?>"><br>
<input class="cw-input" name="copyright" placeholder="Copyright" value="<?= htmlspecialchars((string)($n['copyright']??'')) ?>"><br>
<input class="cw-input" name="texto_atencion" placeholder="Texto de atención" value="<?= htmlspecialchars((string)($n['texto_atencion']??'')) ?>"><br>
<input class="cw-input" name="texto_envios" placeholder="Texto envíos/devoluciones" value="<?= htmlspecialchars((string)($n['texto_envios']??'')) ?>"><br>
<h3>Google Drive</h3>
<label><input type="checkbox" name="drive_enabled" <?= ((int)($c['drive_enabled']??0)===1)?'checked':'' ?>> Activar Drive</label><br>
<input class="cw-input" name="drive_folder_id" placeholder="Carpeta Drive ID" value="<?= htmlspecialchars((string)($c['drive_folder_id']??'')) ?>"><br>
<select class="cw-select" name="drive_mode"><option value="local" <?= (($c['drive_mode']??'local')==='local')?'selected':'' ?>>local</option><option value="drive" <?= (($c['drive_mode']??'local')==='drive')?'selected':'' ?>>drive</option><option value="ambos" <?= (($c['drive_mode']??'local')==='ambos')?'selected':'' ?>>ambos</option></select><br>
<p>Estado: <?= htmlspecialchars((string)($c['drive_status']??'No configurado')) ?></p>
<p>Última sincronización: <?= htmlspecialchars((string)($c['drive_last_sync']??'')) ?></p>
<button class="cw-btn">Guardar</button></form></div>
<?php cw_layout_footer(); ?>
