<?php
require_once __DIR__ . '/includes/web_helpers.php';
$data = sw_load_data();
$id = (int)($_GET['id'] ?? 0);
$slug = sw_slug((string)($_GET['slug'] ?? ''));
$product = sw_find_product($data, $id, $slug);
$title = sw_page_title($data, $product ? (string)$product['nombre'] : 'Producto');
require __DIR__ . '/includes/web_header.php';
?>

<?php if (!$product): ?>
    <div class="empty-state">Producto no encontrado.</div>
<?php else: ?>
    <?php
        $img = (string)($product['imagen_url'] ?? '');
        $price = (float)($product['precio_oferta'] ?: $product['precio'] ?: 0);
        $name = (string)($product['nombre'] ?? 'Producto');
        $wa = sw_whatsapp_url($data['negocio'], 'Hola, quiero información del modelo: ' . $name);
        $gallery = $product['galeria'] ?: [['imagen_url' => $img, 'alt' => $name]];
    ?>
    <nav class="breadcrumbs">
        <a href="/">Inicio</a><span>/</span>
        <a href="/colecciones">Colecciones</a><span>/</span>
        <b><?= sw_e($name) ?></b>
    </nav>

    <section class="product-detail">
        <div class="product-detail__gallery">
            <div class="main-product-image">
                <?php if ($img): ?><img src="<?= sw_e($img) ?>" alt="<?= sw_e($name) ?>" data-main-product-image><?php else: ?><span class="image-placeholder">SU</span><?php endif; ?>
            </div>
            <?php if (count($gallery) > 1): ?>
            <div class="thumb-row">
                <?php foreach ($gallery as $g): if (empty($g['imagen_url'])) continue; ?>
                    <button type="button" data-thumb="<?= sw_e($g['imagen_url']) ?>">
                        <img src="<?= sw_e($g['imagen_url']) ?>" alt="<?= sw_e($g['alt'] ?? $name) ?>">
                    </button>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <article class="product-detail__info">
            <?php if (!empty($product['categoria'])): ?><p class="eyebrow"><?= sw_e($product['categoria']) ?></p><?php endif; ?>
            <h1><?= sw_e($name) ?></h1>
            <?php if ($price > 0): ?><strong class="detail-price"><?= sw_money($price) ?></strong><?php endif; ?>
            <?php if (!empty($product['descripcion'])): ?><p><?= nl2br(sw_e($product['descripcion'])) ?></p><?php endif; ?>
            <?php if (!empty($product['descripcion_larga'])): ?><p><?= nl2br(sw_e($product['descripcion_larga'])) ?></p><?php endif; ?>

            <?php if (!empty($product['tallas_array'])): ?>
            <div class="option-block">
                <b>Tallas</b>
                <div class="pill-row">
                    <?php foreach ($product['tallas_array'] as $t): ?><button type="button" data-size-option><?= sw_e($t) ?></button><?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if (!empty($product['colores_array'])): ?>
            <div class="option-block">
                <b>Colores</b>
                <div class="pill-row">
                    <?php foreach ($product['colores_array'] as $c): ?><button type="button" data-color-option><?= sw_e($c) ?></button><?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="detail-actions">
                <button class="btn btn--gold" type="button"
                    data-add-cart
                    data-id="<?= (int)$product['id'] ?>"
                    data-name="<?= sw_e($name) ?>"
                    data-price="<?= sw_e((string)$price) ?>"
                    data-image="<?= sw_e($img) ?>">Agregar al carrito</button>
                <a class="btn btn--ghost" href="<?= sw_e($wa) ?>" target="_blank" rel="noopener">Pedir por WhatsApp</a>
            </div>
        </article>
    </section>
<?php endif; ?>

<?php require __DIR__ . '/includes/web_footer.php'; ?>
