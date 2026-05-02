<?php
declare(strict_types=1);

function cw_data_dir(): string { return __DIR__ . '/data'; }
function cw_data_file(): string { return cw_data_dir() . '/web_data.json'; }
function cw_slug(string $txt): string { $txt=trim(mb_strtolower($txt)); $txt=strtr($txt,['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']); $txt=preg_replace('/[^a-z0-9]+/u','-',$txt); return trim((string)$txt,'-')?:'item'; }
function cw_default_data(): array { return ['config'=>['logo'=>'','banner_principal'=>'/assets/img/hero-suave-modelos.svg','fondo_principal'=>'','titulo_home'=>'Suave Urban Studio','subtitulo_home'=>'','color_primario'=>'#d4af37','color_secundario'=>'#ffe08a','web_activa'=>1,'drive_enabled'=>0,'drive_folder_id'=>'','drive_mode'=>'local','drive_status'=>'No configurado','drive_last_sync'=>''],'negocio'=>['nombre'=>'Suave Urban Studio','descripcion'=>'','footer_descripcion'=>'','correo'=>'','telefono'=>'','whatsapp'=>'','direccion'=>'','texto_atencion'=>'','texto_envios'=>'','redes'=>['tiktok'=>'','facebook'=>'','instagram'=>''],'copyright'=>''],'categorias'=>[],'productos'=>[],'paginas'=>[['titulo'=>'Contacto','url'=>'/contacto','footer'=>1,'menu'=>1,'orden'=>1]],'drive'=>['enabled'=>false,'folder_id'=>'','mode'=>'local','last_sync'=>'','status'=>'Drive no configurado']]; }

function cw_ensure_data_file(): void {
  $dir = cw_data_dir();
  if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) throw new RuntimeException('No se pudo crear carpeta data.');
  if (!is_writable($dir)) throw new RuntimeException('No se pudo guardar. Revisa permisos de la carpeta data.');
  $file = cw_data_file();
  if (!is_file($file)) {
    if (file_put_contents($file, json_encode(cw_default_data(), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) === false) throw new RuntimeException('No se pudo guardar. Revisa permisos de la carpeta data.');
  }
  $raw = (string)@file_get_contents($file);
  $json = json_decode($raw, true);
  if (!is_array($json)) {
    @rename($file, $dir . '/web_data.bak.' . date('YmdHis') . '.json');
    if (file_put_contents($file, json_encode(cw_default_data(), JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT)) === false) throw new RuntimeException('No se pudo guardar. Revisa permisos de la carpeta data.');
  }
  if (!is_writable($file)) throw new RuntimeException('No se pudo guardar. Revisa permisos de la carpeta data.');
}

function cw_public_asset_url(?string $path): string { $p=trim((string)$path); if($p==='') return ''; $p=str_replace('\\','/',$p); if(preg_match('~^https?://~i',$p)) return $p; if(str_starts_with($p,'/ventas/uploads/')) return $p; if(str_starts_with($p,'ventas/uploads/')) return '/'.ltrim($p,'/'); if(str_starts_with($p,'/uploads/')) return '/ventas'.$p; if(str_starts_with($p,'uploads/')) return '/ventas/'.$p; return '/ventas/uploads/web/'.ltrim($p,'/'); }
function cw_load(): array { cw_ensure_data_file(); $j=json_decode((string)file_get_contents(cw_data_file()),true); return is_array($j) ? array_replace_recursive(cw_default_data(),$j) : cw_default_data(); }
function cw_save(array $d): void { cw_ensure_data_file(); if(file_put_contents(cw_data_file(),json_encode($d,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT))===false) throw new RuntimeException('No se pudo guardar. Revisa permisos de la carpeta data.'); }
function cw_next_id(array $rows): int { $ids=array_map(fn($r)=>(int)($r['id']??0),$rows); return $ids?max($ids)+1:1; }
function cw_next_order(array $rows): int { $o=array_map(fn($r)=>(int)($r['orden']??0),$rows); return $o?max($o)+1:1; }
function cw_drive_mode(): string {
  $d = cw_load();
  $mode = (string)($d['drive']['mode'] ?? $d['config']['drive_mode'] ?? 'local');
  if ($mode === 'ambos') $mode = 'both';
  return in_array($mode, ['local','drive','both'], true) ? $mode : 'local';
}
function cw_drive_folder_id(): string {
  $d = cw_load();
  return trim((string)($d['drive']['folder_id'] ?? $d['config']['drive_folder_id'] ?? ''));
}
function cw_drive_enabled(): bool {
  $d = cw_load();
  return (bool)($d['drive']['enabled'] ?? $d['config']['drive_enabled'] ?? false);
}
function cw_drive_configured(): bool { return cw_drive_enabled() && cw_drive_folder_id()!==''; }
function cw_drive_status_text(): string { return cw_drive_configured() ? 'Drive configurado' : 'Drive no configurado'; }

function cw_drive_diagnostics(): array {
  $diag = ['local'=>'N/A','drive'=>'N/A','token'=>'N/A','folder_id'=>'N/A'];
  $diag['folder_id'] = cw_drive_folder_id() !== '' ? 'OK' : 'FALTA';
  $diag['token'] = cw_drive_is_connected() ? 'OK' : 'FALTA';
  if (!cw_drive_enabled()) {
    $diag['drive'] = 'DESACTIVADO';
  } elseif (!cw_drive_configured()) {
    $diag['drive'] = 'NO CONFIGURADO';
  } else {
    $diag['drive'] = cw_drive_test_connection() ? 'OK' : 'ERROR';
  }
  return $diag;
}

function cw_upload_public_image(array $file, string $folder): string {
  $GLOBALS['cw_last_upload_meta'] = [];
  if (($file['error'] ?? UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE) return '';
  if(($file['error']??UPLOAD_ERR_OK)!==UPLOAD_ERR_OK) throw new RuntimeException('Error al subir imagen.');
  $size=(int)($file['size']??0);
  $driveMode = cw_drive_mode();
  $driveReady = cw_drive_configured() && cw_drive_is_connected();
  if($size>5*1024*1024 && !$driveReady) throw new RuntimeException('Imagen mayor a 5MB: conecta Google Drive para permitir este tamaño.');
  $tmp=(string)($file['tmp_name']??'');
  if(@getimagesize($tmp)===false) throw new RuntimeException('Archivo inválido: no es imagen real.');
  $ext=strtolower(pathinfo((string)($file['name']??''),PATHINFO_EXTENSION));
  if(!in_array($ext,['jpg','jpeg','png','webp'],true)) throw new RuntimeException('Formato no permitido. Usa jpg, jpeg, png o webp.');
  $mime=(string)($file['type'] ?? 'application/octet-stream');
  $folder=cw_slug($folder);
  $disk=__DIR__.'/../uploads/web/'.$folder;
  if(!is_dir($disk) && !mkdir($disk,0775,true) && !is_dir($disk)) throw new RuntimeException('No se pudo crear carpeta de destino.');
  $name=$folder.'_'.time().'_'.bin2hex(random_bytes(3)).'.'.$ext;
  $localPath = $disk.'/'.$name;

  $savedLocal = false;
  if ($driveMode !== 'drive' || !$driveReady) {
    if(!move_uploaded_file($tmp,$localPath)) throw new RuntimeException('No se pudo mover la imagen subida.');
    $savedLocal = true;
  }

  if ($driveMode === 'drive' || $driveMode === 'both') {
    if (!$driveReady) {
      throw new RuntimeException('Modo Drive activo pero no hay conexión válida. Revisa token/folder ID.');
    }
    if (!$savedLocal) {
      if(!move_uploaded_file($tmp,$localPath)) throw new RuntimeException('No se pudo preparar archivo para enviar a Drive.');
      $savedLocal = true;
    }
    $up = cw_drive_upload_file($localPath, $name, $mime);
    if (empty($up['ok'])) throw new RuntimeException('Error al subir a Drive: ' . (string)($up['error'] ?? 'desconocido'));
    $localUrl = '/ventas/uploads/web/'.$folder.'/'.$name;
    $source = $driveMode === 'both' ? 'both' : 'drive';
    $GLOBALS['cw_last_upload_meta'] = [
      'imagen_drive_id' => (string)($up['id'] ?? ''),
      'imagen_drive_url' => (string)($up['url'] ?? ''),
      'imagen_thumb_url' => (string)($up['thumb'] ?? ''),
      'imagen_original_source' => $source
    ];
    if ($driveMode === 'drive') {
      return (string)($up['url'] ?? '');
    }
    return $localUrl;
  }

  $GLOBALS['cw_last_upload_meta'] = [
    'imagen_drive_id' => '',
    'imagen_drive_url' => '',
    'imagen_thumb_url' => '',
    'imagen_original_source' => 'local'
  ];
  return '/ventas/uploads/web/'.$folder.'/'.$name;
}

function cw_add_category(string $name, string $image): array { $d=cw_load(); $slug=cw_slug($name); $row=['id'=>cw_next_id($d['categorias']??[]),'nombre'=>trim($name),'slug'=>$slug,'descripcion'=>'','imagen'=>cw_public_asset_url($image),'url'=>'/'.$slug,'orden'=>cw_next_order($d['categorias']??[]),'activa'=>1,'menu'=>1,'footer'=>1,'created_at'=>date('c')]; $d['categorias'][]=$row; cw_save($d); return $row; }
function cw_update_category(int $id,array $changes): bool { $d=cw_load(); foreach(($d['categorias']??[]) as $i=>$c){ if((int)$c['id']!==$id) continue; if(isset($changes['nombre'])){$changes['slug']=cw_slug((string)$changes['nombre']);$changes['url']='/'.$changes['slug'];} if(isset($changes['imagen']))$changes['imagen']=cw_public_asset_url((string)$changes['imagen']); $d['categorias'][$i]=array_replace($c,$changes); foreach(($d['productos']??[]) as $pi=>$p){ if((int)($p['categoria_id']??0)===$id) $d['productos'][$pi]['categoria_slug']=$d['categorias'][$i]['slug']; } cw_save($d); return true;} return false; }
function cw_delete_category(int $id): bool { $d=cw_load(); $d['categorias']=array_values(array_filter($d['categorias']??[],fn($c)=>(int)$c['id']!==$id)); cw_save($d); return true; }
function cw_add_product(array $payload): array { $d=cw_load(); $payload['id']=cw_next_id($d['productos']??[]); $payload['slug']=cw_slug((string)($payload['nombre']??'producto')); $payload['imagen_principal']=cw_public_asset_url((string)($payload['imagen_principal']??'')); $payload['orden']=(int)($payload['orden']??cw_next_order($d['productos']??[])); $d['productos'][]=$payload; cw_save($d); return $payload; }
function cw_update_product(int $id,array $changes): bool { $d=cw_load(); foreach(($d['productos']??[]) as $i=>$p){ if((int)$p['id']!==$id) continue; if(isset($changes['imagen_principal']))$changes['imagen_principal']=cw_public_asset_url((string)$changes['imagen_principal']); $d['productos'][$i]=array_replace($p,$changes); cw_save($d); return true;} return false; }
function cw_delete_product(int $id): bool { $d=cw_load(); $d['productos']=array_values(array_filter($d['productos']??[],fn($p)=>(int)$p['id']!==$id)); cw_save($d); return true; }


function cw_drive_private_dir(): string { return __DIR__ . '/private'; }
function cw_drive_client_path(): string { return cw_drive_private_dir() . '/google-oauth-client.json'; }
function cw_drive_token_path(): string { return cw_drive_private_dir() . '/google-drive-token.json'; }
function cw_drive_oauth_client(): ?object {
  if (!class_exists('Google_Client')) {
    $autoload = __DIR__ . '/../../vendor/autoload.php';
    if (is_file($autoload)) require_once $autoload;
  }
  if (!class_exists('Google_Client')) return null;
  if (!is_file(cw_drive_client_path())) throw new RuntimeException('Falta google-oauth-client.json');
  $client = new Google_Client();
  $client->setAuthConfig(cw_drive_client_path());
  $client->setAccessType('offline');
  $client->setPrompt('consent');
  $client->setScopes(['https://www.googleapis.com/auth/drive.file']);
  $client->setRedirectUri('https://ventas.suaveurbanstudio.com.mx/configuracion_web/drive_callback.php');
  return $client;
}
function cw_drive_is_connected(): bool { return is_file(cw_drive_token_path()); }
function cw_drive_refresh_token_if_needed(): ?array {
  $client = cw_drive_oauth_client(); if(!$client) return null;
  if (!is_file(cw_drive_token_path())) return null;
  $token = json_decode((string)file_get_contents(cw_drive_token_path()), true); if(!is_array($token)) return null;
  $client->setAccessToken($token);
  if ($client->isAccessTokenExpired() && !empty($token['refresh_token'])) {
    $new = $client->fetchAccessTokenWithRefreshToken($token['refresh_token']);
    $token = array_replace($token, is_array($new)?$new:[]);
    file_put_contents(cw_drive_token_path(), json_encode($token, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
  }
  return $token;
}
function cw_drive_upload_file(string $localPath, string $filename, string $mimeType='application/octet-stream'): array {
  $folderId = cw_drive_folder_id();
  if ($folderId === '') return ['ok'=>false,'error'=>'Falta folder ID de Drive'];
  $client = cw_drive_oauth_client(); if(!$client) return ['ok'=>false,'error'=>'Google Client no disponible'];
  $token = cw_drive_refresh_token_if_needed(); if(!$token) return ['ok'=>false,'error'=>'Drive no configurado'];
  $client->setAccessToken($token);
  try {
    $service = new Google_Service_Drive($client);
    $meta = new Google_Service_Drive_DriveFile(['name'=>$filename,'parents'=>[$folderId]]);
    $file = $service->files->create($meta, ['data'=>(string)file_get_contents($localPath),'mimeType'=>$mimeType,'uploadType'=>'multipart','fields'=>'id']);
    $id = (string)$file->id; cw_drive_make_public($id, $service);
    return ['ok'=>true,'id'=>$id,'url'=>cw_drive_public_url($id),'thumb'=>cw_drive_thumb_url($id)];
  } catch (Throwable $e) {
    return ['ok'=>false,'error'=>$e->getMessage()];
  }
}
function cw_drive_make_public(string $fileId, $service=null): void {
  if (!$service) { $client=cw_drive_oauth_client(); if(!$client) return; $tok=cw_drive_refresh_token_if_needed(); if(!$tok) return; $client->setAccessToken($tok); $service = new Google_Service_Drive($client); }
  $perm = new Google_Service_Drive_Permission(['type'=>'anyone','role'=>'reader']);
  $service->permissions->create($fileId, $perm);
}
function cw_drive_public_url(string $fileId): string { return 'https://drive.google.com/uc?id=' . rawurlencode($fileId); }
function cw_drive_thumb_url(string $fileId): string { return 'https://drive.google.com/thumbnail?id=' . rawurlencode($fileId) . '&sz=w1000'; }
function cw_drive_test_connection(): bool { return cw_drive_is_connected() && cw_drive_configured() && (cw_drive_oauth_client()!==null); }

function cw_last_upload_meta(): array { return is_array($GLOBALS['cw_last_upload_meta'] ?? null) ? $GLOBALS['cw_last_upload_meta'] : []; }
