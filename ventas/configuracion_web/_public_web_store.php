<?php
declare(strict_types=1);
function cw_data_file(): string { return __DIR__ . '/data/web_data.json'; }
function cw_slug(string $txt): string { $txt=trim(mb_strtolower($txt)); $txt=strtr($txt,['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']); $txt=preg_replace('/[^a-z0-9]+/u','-',$txt); return trim((string)$txt,'-')?:'item'; }
function cw_public_asset_url(?string $path): string {
  $p=trim((string)$path); if($p==='') return ''; $p=str_replace('\\','/',$p); if(preg_match('~^https?://~i',$p)) return $p;
  if(str_starts_with($p,'/ventas/uploads/')) return $p; if(str_starts_with($p,'ventas/uploads/')) return '/'.$p;
  if(str_starts_with($p,'/uploads/')) return '/ventas'.$p; if(str_starts_with($p,'uploads/')) return '/ventas/'.$p;
  if(!str_starts_with($p,'/')) $p='/ventas/uploads/web/'.ltrim($p,'/'); return $p;
}
function cw_upload_public_image(array $file, string $folder): string {
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return '';
  if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) throw new RuntimeException('Error al subir imagen.');
  if ((int)($file['size'] ?? 0) > 5*1024*1024) throw new RuntimeException('Imagen supera 5MB.');
  $tmp=(string)($file['tmp_name'] ?? ''); $mime=(string)(mime_content_type($tmp) ?: ''); if(!str_starts_with($mime,'image/')) throw new RuntimeException('Solo imágenes.');
  $ext=strtolower(pathinfo((string)($file['name'] ?? ''),PATHINFO_EXTENSION)); if(!in_array($ext,['jpg','jpeg','png','webp','gif'],true)) $ext='webp';
  $folder=cw_slug($folder); $disk=realpath(__DIR__.'/../..').'/uploads/web/'.$folder; if(!is_dir($disk)) mkdir($disk,0775,true);
  $name=$folder.'_'.time().'_'.bin2hex(random_bytes(3)).'.'.$ext; $dest=$disk.'/'.$name; if(!move_uploaded_file($tmp,$dest)) throw new RuntimeException('No se pudo guardar imagen.');
  return '/ventas/uploads/web/'.$folder.'/'.$name;
}
function cw_default_data(): array { return ['config'=>['logo'=>'','banner_principal'=>'/assets/img/hero-suave-modelos.svg','fondo_principal'=>'','titulo_home'=>'Suave Urban Studio','subtitulo_home'=>'','color_primario'=>'#d4af37','color_secundario'=>'#ffe08a','web_activa'=>1,'drive_enabled'=>0,'drive_folder_id'=>'','drive_mode'=>'local','drive_status'=>'No configurado','drive_last_sync'=>''],'negocio'=>['nombre'=>'Suave Urban Studio','descripcion'=>'','footer_descripcion'=>'','correo'=>'','telefono'=>'','whatsapp'=>'','direccion'=>'','texto_atencion'=>'','texto_envios'=>'','redes'=>['tiktok'=>'','facebook'=>'','instagram'=>''],'copyright'=>''],'categorias'=>[],'productos'=>[],'paginas'=>[['titulo'=>'Contacto','url'=>'/contacto','footer'=>1,'menu'=>1,'orden'=>1],['titulo'=>'Aviso de privacidad','url'=>'/aviso-privacidad','footer'=>1,'menu'=>0,'orden'=>2]]]; }
function cw_load(): array { $f=cw_data_file(); if(!is_file($f)) return cw_default_data(); $j=json_decode((string)file_get_contents($f),true); if(!is_array($j)) return cw_default_data(); return array_replace_recursive(cw_default_data(),$j); }
function cw_save(array $d): void { $f=cw_data_file(); if(!is_dir(dirname($f))) mkdir(dirname($f),0775,true); file_put_contents($f,json_encode($d,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)); }
function cw_next_id(array $rows): int { $ids=array_map(fn($r)=>(int)($r['id']??0),$rows); return $ids?max($ids)+1:1; }
function cw_next_order(array $rows): int { $o=array_map(fn($r)=>(int)($r['orden']??0),$rows); return $o?max($o)+1:1; }
function cw_drive_enabled(): bool { return (int)(cw_load()['config']['drive_enabled'] ?? 0) === 1; }
function cw_drive_folder_id(): string { return (string)(cw_load()['config']['drive_folder_id'] ?? ''); }
function cw_drive_upload_placeholder(string $localPath): array { return ['ok'=>false,'message'=>'Drive pendiente de integrar','local'=>$localPath]; }
function cw_add_category(string $name, string $image): array { $name=trim($name); if($name==='') throw new RuntimeException('Nombre requerido.'); $d=cw_load(); $slug=cw_slug($name); foreach(($d['categorias']??[]) as $cat) if(($cat['slug']??'')===$slug) throw new RuntimeException('La categoría ya existe.'); $row=['id'=>cw_next_id($d['categorias']??[]),'nombre'=>$name,'slug'=>$slug,'descripcion'=>'','imagen'=>cw_public_asset_url($image),'url'=>'/'.$slug,'orden'=>cw_next_order($d['categorias']??[]),'activa'=>1,'menu'=>1,'footer'=>1,'created_at'=>date('c')]; $d['categorias'][]=$row; cw_save($d); return $row; }
function cw_update_product(int $id, array $changes): bool { $d=cw_load(); foreach(($d['productos']??[]) as $i=>$p){ if((int)($p['id']??0)!==$id) continue; if(isset($changes['imagen_principal'])) $changes['imagen_principal']=cw_public_asset_url((string)$changes['imagen_principal']); $d['productos'][$i]=array_replace($p,$changes); cw_save($d); return true; } return false; }
function cw_delete_product(int $id): bool { $d=cw_load(); $b=count($d['productos']??[]); $d['productos']=array_values(array_filter($d['productos']??[],fn($p)=>(int)($p['id']??0)!==$id)); if(count($d['productos'])===$b) return false; cw_save($d); return true; }


function cw_update_category(int $id, array $changes): bool {
  $d=cw_load();
  foreach(($d['categorias']??[]) as $i=>$c){
    if((int)($c['id']??0)!==$id) continue;
    if(isset($changes['nombre'])){ $slug=cw_slug((string)$changes['nombre']); $changes['slug']=$slug; $changes['url']='/'.$slug; }
    if(isset($changes['imagen'])) $changes['imagen']=cw_public_asset_url((string)$changes['imagen']);
    $d['categorias'][$i]=array_replace($c,$changes);
    $newSlug=(string)($d['categorias'][$i]['slug']??'');
    foreach(($d['productos']??[]) as $pi=>$p){ if((int)($p['categoria_id']??0)===$id){ $d['productos'][$pi]['categoria_slug']=$newSlug; } }
    cw_save($d); return true;
  }
  return false;
}
function cw_delete_category(int $id): bool {
  $d=cw_load(); $b=count($d['categorias']??[]);
  $d['categorias']=array_values(array_filter($d['categorias']??[],fn($c)=>(int)($c['id']??0)!==$id));
  if(count($d['categorias'])===$b) return false; cw_save($d); return true;
}
function cw_add_product(array $payload): array {
  $d=cw_load();
  $payload['id']=cw_next_id($d['productos']??[]);
  $payload['slug']=cw_slug((string)($payload['nombre'] ?? 'producto'));
  $payload['imagen_principal']=cw_public_asset_url((string)($payload['imagen_principal'] ?? ''));
  $payload['orden']=(int)($payload['orden'] ?? cw_next_order($d['productos'] ?? []));
  $payload['activo']=(int)($payload['activo'] ?? 1);
  $payload['precio_oferta']=(float)($payload['precio_oferta'] ?? 0);
  $payload['destacado']=(int)($payload['destacado'] ?? 0);
  $d['productos'][]=$payload; cw_save($d); return $payload;
}
