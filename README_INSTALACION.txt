WEB LIMPIA SUAVE URBAN - PRIMERA BASE

Objetivo:
- Dejar la web pública sin depender del index.html compilado de Hostinger ni de scripts parcheados.
- Mantener el sistema interno /ventas/ intacto.
- Leer categorías, productos, logo, banners y textos desde ventas/configuracion_web.

Archivos nuevos principales:
- index.php
- categoria.php
- producto.php
- carrito.php
- favoritos.php
- contacto.php
- login-clientes.php
- trabajador-login.php
- includes/web_helpers.php
- includes/web_header.php
- includes/web_footer.php
- assets/css/suave-web-clean.css
- assets/js/suave-web-clean.js
- .htaccess

Cómo probar sin romper:
1) Crea una carpeta de prueba en el hosting, por ejemplo:
   public_html/web_limpia/

2) Sube todos estos archivos ahí.

3) Si la carpeta de prueba NO está junto a /ventas/, la conexión a Configuración Web puede no detectar la ruta.
   Para prueba real completa, estos archivos están pensados para quedar en public_html junto a la carpeta /ventas/.

Cómo activar en raíz sin borrar nada:
1) Sube estos archivos a public_html.
2) Deja el index.html viejo ahí si quieres, pero el .htaccess incluye:
   DirectoryIndex index.php index.html
   Eso hace que cargue primero index.php.
3) No borres /ventas/.
4) No borres tus imágenes ni uploads.

Rutas limpias:
- /
- /colecciones
- /hombre
- /mujer
- /accesorios
- /novedades
- /producto/ID
- /carrito
- /favoritos
- /contacto
- /login-clientes
- /trabajador-login

Notas:
- El carrito y favoritos están en localStorage.
- El botón de Equipo redirige a https://ventas.suaveurbanstudio.com.mx/
- El home muestra productos destacados y 5 modelos por cada categoría.
- Si no hay categorías/productos, muestra avisos limpios en vez de textos de prueba.
