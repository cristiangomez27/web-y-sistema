<?php
declare(strict_types=1);
require_once __DIR__ . '/_public_web_store.php';
try {
  $client = cw_drive_oauth_client();
  if (!$client) throw new RuntimeException('Google Client no disponible.');
  if (!isset($_GET['code'])) throw new RuntimeException('Código OAuth faltante.');
  $token = $client->fetchAccessTokenWithAuthCode((string)$_GET['code']);
  if (!empty($token['error'])) throw new RuntimeException('OAuth error: ' . $token['error']);
  file_put_contents(cw_drive_token_path(), json_encode($token, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT));
  $d = cw_load(); $d['drive']['enabled']=true; $d['drive']['status']='Drive conectado'; cw_save($d);
  header('Location: index.php?ok=Drive+conectado'); exit;
} catch (Throwable $e) {
  header('Location: index.php?ok=' . rawurlencode($e->getMessage())); exit;
}
