<?php
require_once __DIR__ . '/includes/web_helpers.php';
$data = sw_load_data();
$title = sw_page_title($data, 'Envíos y devoluciones');
require __DIR__ . '/includes/web_header.php';
?>
<section class="page-title"><p>Atención al cliente</p><h1>Envíos y devoluciones</h1></section>
<section class="clean-panel">
    <p>Información editable próximamente desde Configuración Web. Por ahora puedes atender pedidos directamente por WhatsApp.</p>
</section>
<?php require __DIR__ . '/includes/web_footer.php'; ?>
