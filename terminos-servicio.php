<?php
require_once __DIR__ . '/includes/web_helpers.php';
$data = sw_load_data();
$title = sw_page_title($data, 'Términos de servicio');
require __DIR__ . '/includes/web_header.php';
?>
<section class="page-title"><p>Legal</p><h1>Términos de servicio</h1></section>
<section class="clean-panel"><p>Términos pendientes de cargar desde Configuración Web.</p></section>
<?php require __DIR__ . '/includes/web_footer.php'; ?>
