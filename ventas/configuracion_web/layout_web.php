<?php
declare(strict_types=1);

function cw_layout_header(string $title = 'Configuración Web Pública'): void {
    $appCss = '/assets/css/app.css';
    ?>
    <!doctype html>
    <html lang="es">
    <head>
      <meta charset="utf-8">
      <meta name="viewport" content="width=device-width,initial-scale=1">
      <title><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8') ?></title>
      <?php if (is_file(__DIR__ . '/../../assets/css/app.css')): ?>
      <link rel="stylesheet" href="<?= $appCss ?>">
      <?php endif; ?>
      <link rel="stylesheet" href="/assets/css/suave-web-clean.css">
      <style>
        body{background:#0f0f11;color:#f5f5f5}.cw-shell{max-width:980px;margin:24px auto;padding:0 14px}
        .cw-card{background:#1a1a1f;border:1px solid #2f2f38;border-radius:16px;padding:18px;margin-bottom:16px;box-shadow:0 10px 30px rgba(0,0,0,.25)}
        .cw-input,.cw-select,.cw-textarea{width:100%;background:#111317;border:1px solid #3a3a44;color:#fff;border-radius:12px;padding:12px;margin-top:6px}
        .cw-btn{display:inline-flex;align-items:center;justify-content:center;background:#d4af37;color:#111;border:0;border-radius:12px;padding:11px 16px;font-weight:800;cursor:pointer;min-height:44px}
        .cw-nav{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}.cw-nav a{color:#ffe08a;padding:8px 10px;border:1px solid rgba(255,224,138,.28);border-radius:10px}
        .cw-msg{padding:10px;border-radius:8px;margin:8px 0}.ok{background:#17341f}.err{background:#3d1c1c}
        @media (max-width:780px){
          .cw-shell{padding:0 10px;margin:14px auto}
          .cw-card{padding:14px;border-radius:14px}
          .cw-btn{width:100%;margin-top:8px}
          .cw-nav{position:sticky;top:8px;z-index:5;background:#0f0f11;padding:8px;border-radius:12px;overflow-x:auto;flex-wrap:nowrap}
          .cw-nav a{white-space:nowrap;font-size:14px}
          h1{font-size:40px;line-height:1.1;margin:0 0 10px}
        }
      </style>
    </head><body><div class="cw-shell"><div class="cw-nav"><a href="index.php">Negocio/Footer</a><a href="diseno.php">Diseño</a><a href="categorias.php">Categorías</a><a href="productos.php">Productos</a><a href="index.php#drive">Drive</a></div>
    <?php
}

function cw_layout_footer(): void {
    echo '</div><script src="/assets/js/suave-web-clean.js" defer></script></body></html>';
}
