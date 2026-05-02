<?php
require_once __DIR__ . '/includes/web_helpers.php';
$data = sw_load_data();
$title = sw_page_title($data, 'Clientes');
require __DIR__ . '/includes/web_header.php';
?>
<section class="page-title">
    <p>Mi cuenta</p>
    <h1>Acceso para clientes</h1>
    <span>Base limpia preparada para conectar registro, pedidos, mockups y seguimiento.</span>
</section>

<section class="clean-panel">
    <h2>Próxima conexión</h2>
    <p>Este archivo queda listo para conectar el login real de clientes sin mezclarlo con el sistema interno de trabajadores.</p>
    <a class="btn btn--gold" href="/colecciones">Seguir comprando</a>
</section>
<?php require __DIR__ . '/includes/web_footer.php'; ?>
