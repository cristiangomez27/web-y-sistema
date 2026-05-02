<?php
require_once __DIR__ . '/includes/web_helpers.php';
$data = sw_load_data();
$slug = sw_slug((string)($_GET['slug'] ?? 'colecciones'));
$cat = $slug !== 'colecciones' && $slug !== 'novedades' ? sw_find_category($data, $slug) : null;

if ($slug === 'colecciones') {
    $pageName = 'Colecciones';
    $products = $data['productos'] ?? [];
} elseif ($slug === 'novedades') {
    $pageName = 'Novedades';
    $products = array_values(array_filter($data['productos'] ?? [], fn($p) => (int)($p['destacado'] ?? 0) === 1 || ($p['categoria_slug'] ?? '') === 'novedades'));
} else {
    $pageName = $cat['nombre'] ?? ucfirst($slug);
    $products = array_values(array_filter($data['productos'] ?? [], fn($p) => ($p['categoria_slug'] ?? '') === $slug));
}

$title = sw_page_title($data, $pageName);
require __DIR__ . '/includes/web_header.php';
?>

<section class="page-title">
    <p>Suave Urban Studio</p>
    <h1><?= sw_e($pageName) ?></h1>
    <?php if (!empty($cat['descripcion'])): ?><span><?= sw_e($cat['descripcion']) ?></span><?php endif; ?>
</section>

<?php if ($slug === 'colecciones' && !empty($data['categorias'])): ?>
<section class="category-grid">
    <?php foreach ($data['categorias'] as $c): ?>
        <?= sw_category_card($c) ?>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<section class="product-grid">
    <?php if ($products): ?>
        <?php foreach ($products as $p): ?>
            <?= sw_product_card($p) ?>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="empty-state">Todavía no hay modelos publicados en esta categoría.</div>
    <?php endif; ?>
</section>

<?php require __DIR__ . '/includes/web_footer.php'; ?>
