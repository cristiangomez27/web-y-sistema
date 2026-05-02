<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../_public_web_store.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if ($method === 'GET') {
  echo json_encode(['ok'=>true,'data'=>cw_load()], JSON_UNESCAPED_UNICODE);
  exit;
}
if ($method === 'POST') {
  $payload = json_decode((string)file_get_contents('php://input'), true);
  if (!is_array($payload)) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'JSON inválido']); exit; }
  $data = cw_load();
  $new = array_replace_recursive($data, $payload);
  cw_save($new);
  echo json_encode(['ok'=>true,'data'=>$new], JSON_UNESCAPED_UNICODE);
  exit;
}
http_response_code(405);
echo json_encode(['ok'=>false,'error'=>'Método no permitido']);
