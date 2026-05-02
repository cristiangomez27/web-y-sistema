<?php
require_once __DIR__ . '/includes/web_helpers.php';
$data = sw_load_data();
$title = sw_page_title($data, 'Carrito');
require __DIR__ . '/includes/web_header.php';
?>
<section class="page-title">
    <p>Compra directa</p>
    <h1>Carrito</h1>
</section>

<section class="cart-page" data-cart-page data-whatsapp="<?= sw_e($data['negocio']['whatsapp'] ?? '') ?>">
    <div class="empty-state">Cargando carrito...</div>
</section>

<?php require __DIR__ . '/includes/web_footer.php'; ?>
