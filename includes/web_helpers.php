<?php
/**
 * Suave Urban - Web pública limpia
 * Lee datos reales del módulo ventas/configuracion_web sin depender del build/parches anteriores.
 */
declare(strict_types=1);

if (!defined('SUAVE_WEB_VERSION')) {
    define('SUAVE_WEB_VERSION', 'clean-web-config-v2');
}

function sw_e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function sw_money($value): string {
    $n = is_numeric($value) ? (float)$value : 0.0;
    return '$' . number_format($n, 0, '.', ',') . ' MXN';
}

function sw_slug(string $txt): string {
    if (function_exists('cw_slug')) {
        return cw_slug($txt);
    }
    $txt = trim(strtolower($txt));
    $txt = strtr($txt, [
        'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u',
        'Á'=>'a','É'=>'e','Í'=>'i','Ó'=>'o','Ú'=>'u','Ñ'=>'n','Ü'=>'u'
    ]);
    $txt = preg_replace('/[^a-z0-9]+/u', '-', $txt);
    return trim((string)$txt, '-') ?: 'item';
}

function sw_abs_url(?string $path): string {
    return sw_public_asset_url($path);
}

function sw_public_asset_url(?string $path): string {
    $p = trim((string)$path);
    if ($p === '') return '';
    $p = str_replace('\\', '/', $p);

    $query = '';
    $hash = '';
    if (str_contains($p, '#')) {
        [$p, $hashPart] = explode('#', $p, 2);
        $hash = '#' . $hashPart;
    }
    if (str_contains($p, '?')) {
        [$p, $queryPart] = explode('?', $p, 2);
        $query = '?' . $queryPart;
    }

    if (preg_match('~^https?://~i', $p)) {
        $parts = parse_url($p);
        $host = strtolower((string)($parts['host'] ?? ''));
        $urlPath = (string)($parts['path'] ?? '');
        if ($host === 'ventas.suaveurbanstudio.com.mx') {
            $p = $urlPath;
        } else {
            return $p . $query . $hash;
        }
    }

    $p = '/' . ltrim($p, '/');

    if (preg_match('~^/public_html/(.+)$~i', $p, $m)) {
        $p = '/' . ltrim($m[1], '/');
    }

    if (preg_match('~^/ventas/uploads/(.+)$~i', $p, $m)) {
        return '/ventas/uploads/' . ltrim($m[1], '/') . $query . $hash;
    }

    if (preg_match('~^/uploads/(.+)$~i', $p, $m)) {
        return '/ventas/uploads/' . ltrim($m[1], '/') . $query . $hash;
    }

    if (preg_match('~^/ventas/(.+)$~i', $p, $m)) {
        return '/ventas/' . ltrim($m[1], '/') . $query . $hash;
    }

    if (preg_match('~^/assets/(.+)$~i', $p, $m)) {
        return '/assets/' . ltrim($m[1], '/') . $query . $hash;
    }

    return '/ventas/' . ltrim($p, '/') . $query . $hash;
}

function sw_public_asset_file(?string $url): string {
    $u = trim((string)$url);
    if ($u === '' || preg_match('~^https?://~i', $u)) return '';
    $path = parse_url($u, PHP_URL_PATH);
    if (!is_string($path) || $path === '') return '';
    $root = realpath(__DIR__ . '/..');
    if (!$root) return '';
    if (str_starts_with($path, '/ventas/')) {
        return $root . '/' . ltrim($path, '/');
    }
    if (str_starts_with($path, '/assets/')) {
        return $root . '/' . ltrim($path, '/');
    }
    return '';
}

function sw_asset_cache_bust(?string $url): string {
    $u = trim((string)$url);
    if ($u === '') return '';
    $file = sw_public_asset_file($u);
    $version = ($file !== '' && is_file($file)) ? (string)filemtime($file) : date('YmdHi');
    $sep = str_contains($u, '?') ? '&' : '?';
    return $u . $sep . 'v=' . rawurlencode($version);
}

function sw_non_empty_path(?string $path): string {
    $p = trim((string)$path);
    if ($p === '') return '';
    return $p;
}


function sw_path_has_http(?string $path): bool {
    return (bool)preg_match('~^https?://~i', trim((string)$path));
}

function sw_asset_local_exists(?string $url): bool {
    $u = trim((string)$url);
    if ($u === '') return false;
    if (sw_path_has_http($u)) return true;
    $file = sw_public_asset_file($u);
    return $file !== '' && is_file($file);
}

function sw_usable_logo_url(?string $path): string {
    $raw = trim((string)$path);
    if ($raw === '') return '';

    // Valores de fábrica que no representan un logo subido desde Configuración Web.
    $plain = strtolower(trim(str_replace('\\', '/', $raw), '/'));
    if (in_array($plain, ['logo.png', 'assets/logo.png', 'img/logo.png'], true)) {
        return '';
    }

    $url = sw_public_asset_url($raw);
    if ($url === '') return '';

    // Si es archivo local, solo lo usamos si existe. Así no se queda apuntando a rutas rotas.
    if (!sw_path_has_http($url) && !sw_asset_local_exists($url)) {
        return '';
    }
    return $url;
}

function sw_public_json_logo(): string {
    $candidates = [
        __DIR__ . '/../ventas/uploads/web/public_config.json',
        __DIR__ . '/../ventas/uploads/web/diseno/public_config.json',
    ];
    foreach ($candidates as $file) {
        if (!is_file($file)) continue;
        $json = json_decode((string)@file_get_contents($file), true);
        if (!is_array($json)) continue;
        $logo = sw_usable_logo_url($json['logo'] ?? '');
        if ($logo !== '') return $logo;
    }
    return '';
}

function sw_latest_disk_logo_url(): string {
    $root = realpath(__DIR__ . '/..');
    if (!$root) return '';
    $dirs = [
        $root . '/ventas/uploads/web/diseno',
        $root . '/ventas/uploads/web',
        $root . '/ventas/uploads',
    ];
    $files = [];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) continue;
        foreach (glob($dir . '/*.{webp,png,jpg,jpeg,gif}', GLOB_BRACE) ?: [] as $file) {
            $base = strtolower(basename($file));
            if (!is_file($file)) continue;
            // Solo tomamos archivos claramente de logo para no confundirlo con banner/fondos/modelos.
            if (!str_contains($base, 'logo')) continue;
            $files[] = $file;
        }
    }
    if (!$files) return '';
    usort($files, fn($a, $b) => (@filemtime($b) ?: 0) <=> (@filemtime($a) ?: 0));
    $rel = ltrim(str_replace(str_replace('\\', '/', $root), '', str_replace('\\', '/', $files[0])), '/');
    return sw_usable_logo_url('/' . $rel);
}

function sw_public_logo_fallback(): string {
    $json = sw_public_json_logo();
    if ($json !== '') return $json;
    return sw_latest_disk_logo_url();
}

function sw_whatsapp_url(array $negocio, string $mensaje = 'Hola, quiero información'): string {
    $w = trim((string)($negocio['whatsapp'] ?? ''));
    if ($w === '') return '#';
    if (preg_match('~^https?://~i', $w)) {
        return $w . (str_contains($w, '?') ? '&' : '?') . 'text=' . rawurlencode($mensaje);
    }
    $w = preg_replace('/\D+/', '', $w);
    return 'https://wa.me/' . $w . '?text=' . rawurlencode($mensaje);
}

function sw_parse_list(?string $value): array {
    $value = trim((string)$value);
    if ($value === '') return [];
    $parts = preg_split('/[,|\n\r]+/', $value);
    return array_values(array_filter(array_map('trim', $parts), fn($x) => $x !== ''));
}

function sw_config_data_file(): string {
    return __DIR__ . '/../ventas/configuracion_web/data/web_data.json';
}

function sw_default_web_data(): array {
    return [
        'ok' => true,
        'config' => [
            'logo' => sw_public_logo_fallback(),
            'banner_principal' => '/assets/img/hero-suave-modelos.svg',
            'fondo_principal' => '',
            'titulo_home' => 'Suave Urban Studio',
            'subtitulo_home' => 'Diseños personalizados, estilo urbano y pedidos hechos con calidad.',
            'color_primario' => '#d4af37',
            'color_secundario' => '#ffe08a',
            'web_activa' => 1,
        ],
        'negocio' => [
            'nombre' => 'Suave Urban Studio',
            'descripcion' => 'Diseño, personalización y producción para tu estilo.',
            'footer_descripcion' => 'Suave Urban Studio.',
            'correo' => '',
            'telefono' => '',
            'whatsapp' => '',
            'direccion' => '',
            'redes' => ['tiktok'=>'', 'facebook'=>'', 'instagram'=>''],
            'copyright' => '© ' . date('Y') . ' Suave Urban Studio. Todos los derechos reservados.',
        ],
        'categorias' => [
            ['id'=>1,'nombre'=>'Novedades','slug'=>'novedades','descripcion'=>'','imagen'=>'','activa'=>1,'menu'=>1,'footer'=>1,'orden'=>1],
            ['id'=>2,'nombre'=>'Hombre','slug'=>'hombre','descripcion'=>'','imagen'=>'','activa'=>1,'menu'=>1,'footer'=>1,'orden'=>2],
            ['id'=>3,'nombre'=>'Mujer','slug'=>'mujer','descripcion'=>'','imagen'=>'','activa'=>1,'menu'=>1,'footer'=>1,'orden'=>3],
            ['id'=>4,'nombre'=>'Accesorios','slug'=>'accesorios','descripcion'=>'','imagen'=>'','activa'=>1,'menu'=>1,'footer'=>1,'orden'=>4],
        ],
        'productos' => [],
        'paginas' => [
            ['titulo'=>'Contacto','url'=>'/contacto','footer'=>1,'menu'=>1,'orden'=>1],
            ['titulo'=>'Envíos y devoluciones','url'=>'/envios-devoluciones','footer'=>1,'menu'=>0,'orden'=>2],
            ['titulo'=>'Aviso de privacidad','url'=>'/aviso-privacidad','footer'=>1,'menu'=>0,'orden'=>3],
            ['titulo'=>'Términos de servicio','url'=>'/terminos-servicio','footer'=>1,'menu'=>0,'orden'=>4],
        ],
        'secciones' => [],
        'error' => '',
    ];
}

function sw_normalize_web_data(array $data): array {
    $base = sw_default_web_data();
    $data['config'] = array_replace($base['config'], $data['config'] ?? []);
    $data['negocio'] = array_replace_recursive($base['negocio'], $data['negocio'] ?? []);
    $data['categorias'] = array_values($data['categorias'] ?? $base['categorias']);
    $data['productos'] = array_values($data['productos'] ?? []);
    $data['paginas'] = array_values($data['paginas'] ?? $base['paginas']);
    foreach ($data['categorias'] as $i => &$c) {
        $c['id'] = (int)($c['id'] ?? ($i + 1));
        $c['nombre'] = (string)($c['nombre'] ?? 'Categoría');
        $c['slug'] = sw_slug((string)($c['slug'] ?? $c['nombre']));
        $c['descripcion'] = (string)($c['descripcion'] ?? '');
        $c['imagen'] = (string)($c['imagen'] ?? '');
        $c['imagen_url'] = sw_abs_url($c['imagen']);
        $c['url'] = '/' . ltrim($c['slug'], '/');
        $c['activa'] = (int)($c['activa'] ?? 1);
        $c['menu'] = (int)($c['menu'] ?? 1);
        $c['footer'] = (int)($c['footer'] ?? 1);
        $c['orden'] = (int)($c['orden'] ?? ($i + 1));
    }
    unset($c);
    $data['categorias'] = array_values(array_filter($data['categorias'], fn($c) => (int)($c['activa'] ?? 1) === 1));
    usort($data['categorias'], fn($a,$b) => ((int)$a['orden'] <=> (int)$b['orden']) ?: ((int)$a['id'] <=> (int)$b['id']));
    $catById = [];
    foreach ($data['categorias'] as $c) $catById[(int)$c['id']] = $c;
    foreach ($data['productos'] as $i => &$p) {
        $p['id'] = (int)($p['id'] ?? ($i + 1));
        $p['nombre'] = (string)($p['nombre'] ?? 'Producto');
        $p['slug'] = sw_slug((string)($p['slug'] ?? $p['nombre']));
        $p['categoria_id'] = (int)($p['categoria_id'] ?? 0);
        $cat = $catById[$p['categoria_id']] ?? null;
        if (!$cat && !empty($p['categoria_slug'])) { foreach ($data['categorias'] as $catRow) { if (($catRow['slug'] ?? '') === (string)$p['categoria_slug']) { $cat = $catRow; break; } } }
        $p['categoria'] = $cat['nombre'] ?? '';
        $p['categoria_slug'] = $cat['slug'] ?? (string)($p['categoria_slug'] ?? '');
        $p['imagen_principal'] = (string)($p['imagen_principal'] ?? '');
        $p['imagen_url'] = sw_abs_url($p['imagen_principal']);
        $p['url'] = '/producto/' . $p['id'] . '-' . $p['slug'];
        $p['descripcion'] = (string)($p['descripcion'] ?? '');
        $p['descripcion_larga'] = (string)($p['descripcion_larga'] ?? '');
        $p['precio'] = (float)($p['precio'] ?? 0);
        $p['precio_oferta'] = (float)($p['precio_oferta'] ?? 0);
        $p['tallas'] = (string)($p['tallas'] ?? '');
        $p['colores'] = (string)($p['colores'] ?? '');
        $p['tallas_array'] = sw_parse_list($p['tallas']);
        $p['colores_array'] = sw_parse_list($p['colores']);
        $p['galeria'] = !empty($p['imagen_url']) ? [['imagen'=>$p['imagen_principal'], 'imagen_url'=>$p['imagen_url'], 'alt'=>$p['nombre'], 'orden'=>0]] : [];
        $p['destacado'] = (int)($p['destacado'] ?? 0);
        $p['activo'] = (int)($p['activo'] ?? 1);
        $p['orden'] = (int)($p['orden'] ?? ($i + 1));
    }
    unset($p);
    $data['productos'] = array_values(array_filter($data['productos'], fn($p) => (int)($p['activo'] ?? 1) === 1));
    usort($data['productos'], fn($a,$b) => ((int)$b['destacado'] <=> (int)$a['destacado']) ?: ((int)$a['orden'] <=> (int)$b['orden']) ?: ((int)$b['id'] <=> (int)$a['id']));
    $data['config']['logo'] = sw_usable_logo_url($data['config']['logo'] ?? '') ?: sw_public_logo_fallback();
    $data['config']['banner_principal'] = sw_abs_url($data['config']['banner_principal'] ?? '') ?: '/assets/img/hero-suave-modelos.svg';
    $data['config']['fondo_principal'] = sw_abs_url($data['config']['fondo_principal'] ?? '');
    $data['ok'] = true;
    $data['error'] = '';
    return $data;
}

function sw_load_data(): array {
    $file = sw_config_data_file();
    if (!is_file($file)) return sw_default_web_data();
    $json = json_decode((string)@file_get_contents($file), true);
    if (!is_array($json)) return sw_default_web_data();
    return sw_normalize_web_data($json);
}

function sw_products_by_category(array $data, string $slug, int $limit = 5): array {
    $out = [];
    foreach (($data['productos'] ?? []) as $p) {
        if (($p['categoria_slug'] ?? '') === $slug) {
            $out[] = $p;
            if (count($out) >= $limit) break;
        }
    }
    return $out;
}

function sw_featured_products(array $data, int $limit = 8): array {
    $featured = [];
    foreach (($data['productos'] ?? []) as $p) {
        if ((int)($p['destacado'] ?? 0) === 1) $featured[] = $p;
        if (count($featured) >= $limit) return $featured;
    }
    return array_slice($data['productos'] ?? [], 0, $limit);
}

function sw_find_product(array $data, int $id, string $slug = ''): ?array {
    foreach (($data['productos'] ?? []) as $p) {
        if ($id > 0 && (int)($p['id'] ?? 0) === $id) return $p;
        if ($slug !== '' && (string)($p['slug'] ?? '') === $slug) return $p;
    }
    return null;
}

function sw_find_category(array $data, string $slug): ?array {
    foreach (($data['categorias'] ?? []) as $c) {
        if (($c['slug'] ?? '') === $slug) return $c;
    }
    return null;
}

function sw_page_title(array $data, string $page = ''): string {
    $name = $data['negocio']['nombre'] ?? 'Suave Urban Studio';
    return $page ? ($page . ' | ' . $name) : $name;
}

function sw_product_card(array $p): string {
    $id = (int)($p['id'] ?? 0);
    $name = (string)($p['nombre'] ?? 'Producto');
    $img = (string)($p['imagen_url'] ?? '');
    $url = (string)($p['url'] ?? ('/producto/' . $id));
    $price = (float)($p['precio_oferta'] ?: $p['precio'] ?: 0);
    $cat = (string)($p['categoria'] ?? '');
    ob_start();
    ?>
    <article class="product-card" data-product-id="<?= $id ?>">
        <a class="product-card__media" href="<?= sw_e($url) ?>" aria-label="<?= sw_e($name) ?>">
            <?php if ($img): ?>
                <img src="<?= sw_e($img) ?>" alt="<?= sw_e($name) ?>" loading="lazy">
            <?php else: ?>
                <span class="image-placeholder">SU</span>
            <?php endif; ?>
            <?php if ((int)($p['destacado'] ?? 0) === 1): ?><em>Destacado</em><?php endif; ?>
        </a>
        <div class="product-card__body">
            <?php if ($cat): ?><small><?= sw_e($cat) ?></small><?php endif; ?>
            <h3><a href="<?= sw_e($url) ?>"><?= sw_e($name) ?></a></h3>
            <?php if ($price > 0): ?><strong><?= sw_money($price) ?></strong><?php endif; ?>
            <div class="product-card__actions">
                <a href="<?= sw_e($url) ?>">Comprar</a>
            </div>
        </div>
    </article>
    <?php
    return trim((string)ob_get_clean());
}



function sw_home_model_card(array $p): string {
    $id = (int)($p['id'] ?? 0);
    $name = (string)($p['nombre'] ?? 'Modelo');
    $img = (string)($p['imagen_url'] ?? '');
    $url = (string)($p['url'] ?? ('/producto/' . $id));
    $price = (float)($p['precio_oferta'] ?: $p['precio'] ?: 0);
    $cat = (string)($p['categoria'] ?? '');
    ob_start();
    ?>
    <article class="lookbook-card" data-product-id="<?= $id ?>">
        <a class="lookbook-card__media" href="<?= sw_e($url) ?>" aria-label="<?= sw_e($name) ?>">
            <?php if ($img): ?>
                <img src="<?= sw_e($img) ?>" alt="<?= sw_e($name) ?>" loading="lazy">
            <?php else: ?>
                <span class="image-placeholder">SU</span>
            <?php endif; ?>
            <span class="lookbook-card__overlay"></span>
        </a>
        <div class="lookbook-card__content">
            <?php if ($cat): ?><small><?= sw_e($cat) ?></small><?php endif; ?>
            <h3><a href="<?= sw_e($url) ?>"><?= sw_e($name) ?></a></h3>
            <div class="lookbook-card__meta">
                <?php if ($price > 0): ?><strong><?= sw_money($price) ?></strong><?php endif; ?>
                <div class="lookbook-card__actions">
                    <a href="<?= sw_e($url) ?>">Comprar</a>
                </div>
            </div>
        </div>
    </article>
    <?php
    return trim((string)ob_get_clean());
}

function sw_category_card(array $c): string {
    $name = (string)($c['nombre'] ?? 'Categoría');
    $slug = (string)($c['slug'] ?? sw_slug($name));
    $img = (string)($c['imagen_url'] ?? '');
    $desc = (string)($c['descripcion'] ?? '');
    ob_start();
    ?>
    <a class="category-card" href="/<?= sw_e($slug) ?>">
        <span class="category-card__image">
            <?php if ($img): ?>
                <img src="<?= sw_e($img) ?>" alt="<?= sw_e($name) ?>" loading="lazy">
            <?php else: ?>
                <span class="image-placeholder"><?= sw_e(substr($name, 0, 2)) ?></span>
            <?php endif; ?>
        </span>
        <span class="category-card__content">
            <b><?= sw_e($name) ?></b>
            <?php if ($desc): ?><small><?= sw_e($desc) ?></small><?php endif; ?>
            <em>Ver colección</em>
        </span>
    </a>
    <?php
    return trim((string)ob_get_clean());
}
