<?php
declare(strict_types=1);

function cw_data_file(): string { return __DIR__ . '/data/web_data.json'; }

function cw_slug(string $txt): string {
    $txt = trim(mb_strtolower($txt));
    $txt = strtr($txt, ['á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ñ'=>'n','ü'=>'u']);
    $txt = preg_replace('/[^a-z0-9]+/u', '-', $txt);
    return trim((string)$txt, '-') ?: 'item';
}

function cw_default_data(): array {
    return [
        'config' => ['logo'=>'','banner_principal'=>'/assets/img/hero-suave-modelos.svg','fondo_principal'=>'','titulo_home'=>'Suave Urban Studio','subtitulo_home'=>'','color_primario'=>'#d4af37','color_secundario'=>'#ffe08a','web_activa'=>1],
        'negocio' => ['nombre'=>'Suave Urban Studio','descripcion'=>'','footer_descripcion'=>'','correo'=>'','telefono'=>'','whatsapp'=>'','direccion'=>'','redes'=>['tiktok'=>'','facebook'=>'','instagram'=>'']],
        'categorias' => [], 'productos' => [],
        'paginas' => [['titulo'=>'Contacto','url'=>'/contacto','footer'=>1,'menu'=>1,'orden'=>1],['titulo'=>'Aviso de privacidad','url'=>'/aviso-privacidad','footer'=>1,'menu'=>0,'orden'=>2]],
    ];
}

function cw_load(): array {
    $file = cw_data_file();
    if (!is_file($file)) return cw_default_data();
    $json = json_decode((string)file_get_contents($file), true);
    if (!is_array($json)) return cw_default_data();
    return array_replace_recursive(cw_default_data(), $json);
}
function cw_save(array $data): void {
    $file = cw_data_file(); if (!is_dir(dirname($file))) mkdir(dirname($file), 0775, true);
    file_put_contents($file, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}
function cw_next_id(array $rows): int { $ids = array_map(fn($r)=>(int)($r['id'] ?? 0), $rows); return $ids ? (max($ids)+1) : 1; }
function cw_next_order(array $rows): int { $ord = array_map(fn($r)=>(int)($r['orden'] ?? 0), $rows); return $ord ? (max($ord)+1) : 1; }

function cw_add_category(string $name, string $image): array {
    $name = trim($name); if ($name === '') throw new RuntimeException('Nombre requerido.');
    $data = cw_load(); $slug = cw_slug($name);
    foreach (($data['categorias'] ?? []) as $cat) if (($cat['slug'] ?? '') === $slug) throw new RuntimeException('La categoría ya existe.');
    $row = ['id'=>cw_next_id($data['categorias'] ?? []),'nombre'=>$name,'slug'=>$slug,'descripcion'=>'','imagen'=>trim($image),'url'=>'/' . $slug,'orden'=>cw_next_order($data['categorias'] ?? []),'activa'=>1,'menu'=>1,'footer'=>1,'created_at'=>date('c')];
    $data['categorias'][] = $row; cw_save($data); return $row;
}
