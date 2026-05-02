<?php
require_once __DIR__ . '/includes/web_helpers.php';
$data = sw_load_data();
$title = sw_page_title($data, 'Contacto');
$negocio = $data['negocio'];
require __DIR__ . '/includes/web_header.php';
?>
<section class="page-title">
    <p>Estamos para ayudarte</p>
    <h1>Contacto</h1>
</section>

<section class="contact-grid">
    <article class="contact-card">
        <h2><?= sw_e($negocio['nombre'] ?? 'Suave Urban Studio') ?></h2>
        <p><?= sw_e($negocio['descripcion'] ?? '') ?></p>
        <?php if (!empty($negocio['whatsapp'])): ?><a class="btn btn--gold" href="<?= sw_e(sw_whatsapp_url($negocio)) ?>" target="_blank" rel="noopener">Enviar WhatsApp</a><?php endif; ?>
    </article>
    <article class="contact-card">
        <h3>Datos</h3>
        <?php if (!empty($negocio['correo'])): ?><p><b>Correo:</b> <?= sw_e($negocio['correo']) ?></p><?php endif; ?>
        <?php if (!empty($negocio['telefono'])): ?><p><b>Teléfono:</b> <?= sw_e($negocio['telefono']) ?></p><?php endif; ?>
        <?php if (!empty($negocio['direccion'])): ?><p><b>Dirección:</b><br><?= nl2br(sw_e($negocio['direccion'])) ?></p><?php endif; ?>
    </article>
</section>

<?php require __DIR__ . '/includes/web_footer.php'; ?>
