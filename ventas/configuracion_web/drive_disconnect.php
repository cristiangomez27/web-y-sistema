<?php
declare(strict_types=1);
require_once __DIR__ . '/_public_web_store.php';
@unlink(cw_drive_token_path());
$d=cw_load(); $d['drive']['enabled']=false; $d['drive']['status']='Drive no configurado'; cw_save($d);
header('Location: index.php?ok=Drive+desconectado'); exit;
