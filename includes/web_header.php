<?php
/** @var array $data */
/** @var string $title */
$config = $data['config'] ?? [];
$negocio = $data['negocio'] ?? [];
$logo = (string)($config['logo'] ?? '');
$logoSrc = $logo !== '' ? sw_asset_cache_bust($logo) : '';
$name = (string)($negocio['nombre'] ?? 'Suave Urban Studio');
$primary = (string)($config['color_primario'] ?? '#d4af37');
$secondary = (string)($config['color_secundario'] ?? '#ffe08a');
$menuCategorias = array_values(array_filter($data['categorias'] ?? [], fn($c) => (int)($c['menu'] ?? 1) === 1));
$menuPaginas = array_values(array_filter($data['paginas'] ?? [], fn($p) => (int)($p['menu'] ?? 0) === 1));
?>
<!doctype html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= sw_e($title ?? sw_page_title($data)) ?></title>
    <meta name="description" content="<?= sw_e($negocio['descripcion'] ?? 'Suave Urban Studio') ?>">
    <link rel="stylesheet" href="/assets/css/suave-web-clean.css?v=<?= sw_e(SUAVE_WEB_VERSION) ?>">
    <style>
        :root{
            --su-gold: <?= sw_e($primary) ?>;
            --su-gold-soft: <?= sw_e($secondary) ?>;
        }
    </style>
</head>
<body>
<div class="site-bg" aria-hidden="true"></div>
<header class="site-header">
    <a class="brand" href="/">
        <span class="brand__logo">
            <?php if ($logoSrc): ?>
                <img src="<?= sw_e($logoSrc) ?>" alt="<?= sw_e($name) ?>">
            <?php else: ?>
                SU
            <?php endif; ?>
        </span>
        <span class="brand__text">
            <b><?= sw_e($name) ?></b>
            <small>Studio</small>
        </span>
    </a>

    <button class="menu-btn" type="button" data-menu-toggle aria-label="Abrir menú">☰</button>

    <nav class="main-nav" data-main-nav>
        <a href="/">Inicio</a>
        <a href="/colecciones">Colecciones</a>
        <?php foreach ($menuCategorias as $cat): ?>
            <a href="/<?= sw_e($cat['slug'] ?? '') ?>"><?= sw_e($cat['nombre'] ?? 'Categoría') ?></a>
        <?php endforeach; ?>
        <?php foreach ($menuPaginas as $page): ?>
            <a href="<?= sw_e($page['url'] ?? '#') ?>"><?= sw_e($page['titulo'] ?? 'Página') ?></a>
        <?php endforeach; ?>
    </nav>

    <div class="header-actions">
        <a href="/login-clientes" class="header-link">Clientes</a>
        <a href="/trabajador-login" class="header-link header-link--gold">Equipo</a>
        <a href="/favoritos" class="icon-link" aria-label="Favoritos">♡ <span data-fav-count>0</span></a>
        <a href="/carrito" class="icon-link" aria-label="Carrito">🛒 <span data-cart-count>0</span></a>
    </div>
</header>
<main class="page-shell">
