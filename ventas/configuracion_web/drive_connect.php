<?php
declare(strict_types=1);
require_once __DIR__ . '/_public_web_store.php';
try {
  $client = cw_drive_oauth_client();
  if (!$client) throw new RuntimeException('Google Client no disponible. Ejecuta composer require google/apiclient');
  $authUrl = $client->createAuthUrl();
  header('Location: ' . $authUrl); exit;
} catch (Throwable $e) {
  header('Location: index.php?ok=' . rawurlencode($e->getMessage())); exit;
}
