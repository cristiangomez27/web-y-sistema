<?php
declare(strict_types=1);
require_once __DIR__ . '/_public_web_store.php';
$ok = cw_drive_test_connection();
header('Location: index.php?ok=' . rawurlencode($ok ? 'Conexión Drive OK' : 'Drive no configurado o sin conexión')); exit;
