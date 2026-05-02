<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_public_web_store.php';
$method=$_SERVER['REQUEST_METHOD'] ?? 'GET';
if($method==='GET'){
  $d=cw_load();
  echo json_encode(['ok'=>true,'config'=>$d['config']??[],'negocio'=>$d['negocio']??[],'categorias'=>$d['categorias']??[],'productos'=>$d['productos']??[],'paginas'=>$d['paginas']??[]],JSON_UNESCAPED_UNICODE); exit;
}
if($method==='POST'){
  $payload=json_decode((string)file_get_contents('php://input'),true); if(!is_array($payload)){http_response_code(400);echo json_encode(['ok'=>false,'error'=>'JSON inválido']);exit;}
  $d=cw_load(); foreach(['config','negocio','categorias','productos','paginas'] as $k){ if(array_key_exists($k,$payload)) $d[$k]=$payload[$k]; }
  cw_save($d); echo json_encode(['ok'=>true,'data'=>$d],JSON_UNESCAPED_UNICODE); exit;
}
http_response_code(405); echo json_encode(['ok'=>false,'error'=>'Método no permitido']);
