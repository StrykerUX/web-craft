-- Archivo de semilla para crear los módulos y lecciones iniciales
-- Para ejecutar: mysql -u usuario -p base_de_datos < seed_modules.sql

-- Módulo 1: Fundamentos HTML
INSERT INTO modules (module_id, title, description, order_index, icon, is_active) VALUES 
(1, 'Fundamentos HTML', 'Aprende los conceptos básicos de HTML para crear la estructura de tus páginas web.', 1, 'fas fa-code', 1);

-- Lecciones para el módulo HTML
INSERT INTO lessons (lesson_id, module_id, title, content, order_index, xp_reward, estimated_time, is_active) VALUES 
(1, 1, 'Introducción a HTML', '<h2>¿Qué es HTML?</h2>
<p>HTML (HyperText Markup Language) es el lenguaje estándar para crear páginas web. Define la estructura de una página web utilizando una serie de elementos o etiquetas que le indican al navegador cómo mostrar el contenido.</p>

<h2>¿Cómo funciona HTML?</h2>
<p>HTML utiliza "marcado" para anotar texto, imágenes y otros contenidos para su visualización en un navegador web. Los elementos HTML están representados por etiquetas como &lt;p&gt; (párrafo), &lt;h1&gt; (encabezado), &lt;img&gt; (imagen), entre otros.</p>

<div class="info-box">
  <h3>Dato importante</h3>
  <p>HTML no es un lenguaje de programación, sino un lenguaje de marcado. Esto significa que se utiliza para definir la estructura del contenido, pero no para crear funcionalidades dinámicas.</p>
</div>

<h2>Historia breve de HTML</h2>
<p>HTML fue creado por Tim Berners-Lee en 1991 mientras trabajaba en el CERN. Desde entonces, ha evolucionado a través de varias versiones. Actualmente, utilizamos HTML5, que se convirtió en un estándar en 2014 y añadió muchas nuevas características para el desarrollo web moderno.</p>

<h2>Navegadores y HTML</h2>
<p>Los navegadores web (como Chrome, Firefox, Safari y Edge) leen los documentos HTML y los muestran como páginas web. El navegador no muestra las etiquetas HTML, sino que las usa para interpretar el contenido de la página.</p>

<h2>Lo que aprenderás en este módulo</h2>
<ul>
  <li>Estructura básica de un documento HTML</li>
  <li>Elementos y etiquetas principales</li>
  <li>Cómo crear enlaces y navegar entre páginas</li>
  <li>Insertar imágenes y medios</li>
  <li>Trabajar con tablas y formularios</li>
  <li>HTML semántico y buenas prácticas</li>
</ul>

<div class="tip-box">
  <h3>Consejo</h3>
  <p>La mejor forma de aprender HTML es practicar. En WebCraft Academy, te animamos a experimentar con el código y construir proyectos mientras avanzas en tus lecciones.</p>
</div>', 1, 10, 15, 1),

(2, 1, 'Estructura básica de un documento HTML', '<h2>La anatomía de un documento HTML</h2>
<p>Todo documento HTML tiene una estructura básica compuesta por ciertos elementos esenciales. Veamos cómo se estructura:</p>

<pre>&lt;!DOCTYPE html&gt;
&lt;html&gt;
  &lt;head&gt;
    &lt;meta charset="UTF-8"&gt;
    &lt;title&gt;Título de la página&lt;/title&gt;
  &lt;/head&gt;
  &lt;body&gt;
    &lt;h1&gt;Mi primera página web&lt;/h1&gt;
    &lt;p&gt;Este es un párrafo en mi página.&lt;/p&gt;
  &lt;/body&gt;
&lt;/html&gt;</pre>

<h2>Explicación de cada parte</h2>

<h3>&lt;!DOCTYPE html&gt;</h3>
<p>Esta declaración define la versión de HTML que se está usando. En HTML5, simplemente se escribe <code>&lt;!DOCTYPE html&gt;</code> y el navegador interpreta el documento como HTML5.</p>

<h3>&lt;html&gt;</h3>
<p>El elemento raíz que contiene todo el documento HTML. También puede incluir el atributo <code>lang</code> para especificar el idioma del documento, por ejemplo: <code>&lt;html lang="es"&gt;</code>.</p>

<h3>&lt;head&gt;</h3>
<p>Contiene metadatos sobre el documento HTML, como el título de la página, enlaces a hojas de estilo CSS, scripts, y otras informaciones que no se muestran directamente en la página.</p>

<ul>
  <li><code>&lt;meta charset="UTF-8"&gt;</code>: Define la codificación de caracteres del documento, permitiendo mostrar correctamente símbolos y caracteres especiales.</li>
  <li><code>&lt;title&gt;</code>: Define el título de la página que aparece en la pestaña del navegador.</li>
</ul>

<h3>&lt;body&gt;</h3>
<p>Contiene todo el contenido visible de la página web, como texto, imágenes, enlaces, tablas, listas, etc.</p>

<div class="warning-box">
  <h3>Importante</h3>
  <p>Siempre asegúrate de cerrar correctamente las etiquetas HTML. La mayoría de las etiquetas tienen una etiqueta de apertura y una de cierre, como <code>&lt;p&gt;</code> y <code>&lt;/p&gt;</code>.</p>
</div>

<h2>Indentación y formato</h2>
<p>Aunque el navegador no requiere una indentación específica para interpretar correctamente el HTML, es una buena práctica indentar tu código para hacerlo más legible:</p>
<ul>
  <li>Usa espacios o tabulaciones para indentar elementos anidados</li>
  <li>Mantén una estructura consistente</li>
  <li>Separa secciones lógicas con líneas en blanco</li>
</ul>

<h2>Comentarios en HTML</h2>
<p>Puedes añadir comentarios en tu código HTML que no se mostrarán en la página web. Esto es útil para documentar tu código o desactivar temporalmente partes de él:</p>

<pre>&lt;!-- Este es un comentario en HTML --&gt;
&lt;!-- 
  Este es un comentario 
  de múltiples líneas 
--&gt;</pre>

<div class="tip-box">
  <h3>Práctica</h3>
  <p>Intenta crear un documento HTML básico desde cero. Incluye un título, encabezado, párrafo y algún comentario. Usa el editor de WebCraft Academy para experimentar.</p>
</div>', 2, 15, 20, 1),

(3, 1, 'Etiquetas HTML básicas', '<h2>Etiquetas de texto fundamentales</h2>
<p>HTML ofrece varias etiquetas para estructurar y dar formato al texto. Estas son algunas de las más importantes:</p>

<h3>Encabezados</h3>
<p>HTML proporciona seis niveles de encabezados, desde <code>&lt;h1&gt;</code> (el más importante) hasta <code>&lt;h6&gt;</code> (el menos importante):</p>

<pre>&lt;h1&gt;Encabezado de nivel 1&lt;/h1&gt;
&lt;h2&gt;Encabezado de nivel 2&lt;/h2&gt;
&lt;h3&gt;Encabezado de nivel 3&lt;/h3&gt;
&lt;h4&gt;Encabezado de nivel 4&lt;/h4&gt;
&lt;h5&gt;Encabezado de nivel 5&lt;/h5&gt;
&lt;h6&gt;Encabezado de nivel 6&lt;/h6&gt;</pre>

<div class="info-box">
  <h3>Importancia de la jerarquía</h3>
  <p>Los encabezados no solo controlan el tamaño del texto, sino que también definen la estructura de la página. Es importante usarlos en orden jerárquico (empezar con h1, luego h2, etc.) para mejorar la accesibilidad y el SEO.</p>
</div>

<h3>Párrafos</h3>
<p>La etiqueta <code>&lt;p&gt;</code> se utiliza para definir párrafos de texto:</p>

<pre>&lt;p&gt;Este es un párrafo en HTML. Los navegadores automáticamente agregan espacio antes y después de cada párrafo.&lt;/p&gt;
&lt;p&gt;Este es otro párrafo.&lt;/p&gt;</pre>

<h3>Saltos de línea</h3>
<p>La etiqueta <code>&lt;br&gt;</code> inserta un salto de línea (una nueva línea) sin comenzar un nuevo párrafo:</p>

<pre>&lt;p&gt;Este es un párrafo&lt;br&gt;con un salto de línea.&lt;/p&gt;</pre>

<div class="warning-box">
  <h3>Nota importante</h3>
  <p>La etiqueta <code>&lt;br&gt;</code> es una etiqueta vacía, lo que significa que no tiene etiqueta de cierre.</p>
</div>

<h3>Énfasis y texto destacado</h3>
<p>HTML proporciona varias etiquetas para enfatizar o destacar texto:</p>

<pre>&lt;p&gt;Texto en &lt;strong&gt;negrita&lt;/strong&gt; indica importancia.&lt;/p&gt;
&lt;p&gt;Texto en &lt;em&gt;cursiva&lt;/em&gt; agrega énfasis.&lt;/p&gt;
&lt;p&gt;Texto &lt;mark&gt;resaltado&lt;/mark&gt; llama la atención.&lt;/p&gt;
&lt;p&gt;Texto &lt;small&gt;pequeño&lt;/small&gt; para contenido secundario.&lt;/p&gt;
&lt;p&gt;Texto &lt;del&gt;tachado&lt;/del&gt; indica eliminación.&lt;/p&gt;
&lt;p&gt;Texto &lt;ins&gt;subrayado&lt;/ins&gt; indica inserción.&lt;/p&gt;
&lt;p&gt;Text&lt;sub&gt;subíndice&lt;/sub&gt; y text&lt;sup&gt;superíndice&lt;/sup&gt;.&lt;/p&gt;</pre>

<h3>Elementos de cita</h3>
<p>Para citar contenido de otras fuentes:</p>

<pre>&lt;blockquote cite="https://www.ejemplo.com"&gt;
  Esta es una cita larga que ocupa varios renglones y se muestra como un bloque con sangría.
&lt;/blockquote&gt;

&lt;p&gt;Como dijo alguien: &lt;q&gt;Esta es una cita corta dentro de un párrafo.&lt;/q&gt;&lt;/p&gt;</pre>

<h3>Separadores</h3>
<p>La etiqueta <code>&lt;hr&gt;</code> crea una línea horizontal que separa temáticamente contenido:</p>

<pre>&lt;h2&gt;Tema 1&lt;/h2&gt;
&lt;p&gt;Contenido del tema 1...&lt;/p&gt;
&lt;hr&gt;
&lt;h2&gt;Tema 2&lt;/h2&gt;
&lt;p&gt;Contenido del tema 2...&lt;/p&gt;</pre>

<h2>Entidades HTML</h2>
<p>Algunos caracteres están reservados en HTML. Para mostrarlos correctamente, debes usar entidades HTML:</p>

<pre>&amp;lt; representa &lt;
&amp;gt; representa &gt;
&amp;amp; representa &amp;
&amp;quot; representa "
&amp;apos; representa \'
&amp;nbsp; representa un espacio inseparable</pre>

<div class="tip-box">
  <h3>Consejo práctico</h3>
  <p>Experimenta con las diferentes etiquetas de texto en el editor. Observa cómo cada una afecta la apariencia y el significado del texto en tu página web.</p>
</div>', 3, 15, 25, 1),

(4, 1, 'Listas en HTML', '<h2>Tipos de listas en HTML</h2>
<p>Las listas son una parte fundamental de HTML, permitiéndonos organizar y estructurar información de manera clara. HTML ofrece tres tipos principales de listas:</p>

<h3>1. Listas desordenadas (sin orden específico)</h3>
<p>Las listas desordenadas utilizan las etiquetas <code>&lt;ul&gt;</code> (unordered list) y <code>&lt;li&gt;</code> (list item):</p>

<pre>&lt;ul&gt;
  &lt;li&gt;Elemento 1&lt;/li&gt;
  &lt;li&gt;Elemento 2&lt;/li&gt;
  &lt;li&gt;Elemento 3&lt;/li&gt;
&lt;/ul&gt;</pre>

<p>Resultado:</p>
<ul>
  <li>Elemento 1</li>
  <li>Elemento 2</li>
  <li>Elemento 3</li>
</ul>

<p>Por defecto, los navegadores muestran las listas desordenadas con viñetas (bullets). Puedes cambiar el tipo de viñeta utilizando CSS.</p>

<h3>2. Listas ordenadas (numeradas)</h3>
<p>Las listas ordenadas utilizan las etiquetas <code>&lt;ol&gt;</code> (ordered list) y <code>&lt;li&gt;</code>:</p>

<pre>&lt;ol&gt;
  &lt;li&gt;Primer paso&lt;/li&gt;
  &lt;li&gt;Segundo paso&lt;/li&gt;
  &lt;li&gt;Tercer paso&lt;/li&gt;
&lt;/ol&gt;</pre>

<p>Resultado:</p>
<ol>
  <li>Primer paso</li>
  <li>Segundo paso</li>
  <li>Tercer paso</li>
</ol>

<p>Las listas ordenadas pueden ser personalizadas con varios atributos:</p>

<pre>&lt;ol type="A" start="3"&gt;
  &lt;li&gt;Este será el elemento C&lt;/li&gt;
  &lt;li&gt;Este será el elemento D&lt;/li&gt;
  &lt;li&gt;Este será el elemento E&lt;/li&gt;
&lt;/ol&gt;</pre>

<p>El atributo <code>type</code> puede tener los valores:</p>
<ul>
  <li><code>1</code> - Números (predeterminado)</li>
  <li><code>A</code> - Letras mayúsculas</li>
  <li><code>a</code> - Letras minúsculas</li>
  <li><code>I</code> - Números romanos mayúsculos</li>
  <li><code>i</code> - Números romanos minúsculos</li>
</ul>

<h3>3. Listas de definición</h3>
<p>Las listas de definición se utilizan para presentar términos y sus definiciones. Utilizan las etiquetas <code>&lt;dl&gt;</code> (definition list), <code>&lt;dt&gt;</code> (definition term) y <code>&lt;dd&gt;</code> (definition description):</p>

<pre>&lt;dl&gt;
  &lt;dt&gt;HTML&lt;/dt&gt;
  &lt;dd&gt;HyperText Markup Language - Lenguaje estándar para crear páginas web.&lt;/dd&gt;
  
  &lt;dt&gt;CSS&lt;/dt&gt;
  &lt;dd&gt;Cascading Style Sheets - Lenguaje usado para describir la presentación de documentos HTML.&lt;/dd&gt;
  
  &lt;dt&gt;JavaScript&lt;/dt&gt;
  &lt;dd&gt;Lenguaje de programación que permite crear contenido dinámico en páginas web.&lt;/dd&gt;
&lt;/dl&gt;</pre>

<h2>Listas anidadas</h2>
<p>Puedes anidar listas dentro de otras listas para crear estructuras jerárquicas:</p>

<pre>&lt;ul&gt;
  &lt;li&gt;Frutas
    &lt;ul&gt;
      &lt;li&gt;Manzanas&lt;/li&gt;
      &lt;li&gt;Naranjas&lt;/li&gt;
      &lt;li&gt;Plátanos&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/li&gt;
  &lt;li&gt;Verduras
    &lt;ul&gt;
      &lt;li&gt;Zanahorias&lt;/li&gt;
      &lt;li&gt;Brócoli&lt;/li&gt;
      &lt;li&gt;Espinacas&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/li&gt;
&lt;/ul&gt;</pre>

<div class="tip-box">
  <h3>Buena práctica</h3>
  <p>Las listas son excelentes para mejorar la legibilidad de tu contenido. Utiliza listas ordenadas cuando el orden de los elementos sea importante (como pasos a seguir) y listas desordenadas cuando el orden no importe.</p>
</div>

<div class="info-box">
  <h3>¿Sabías que?</h3>
  <p>Las listas HTML no solo son útiles para mostrar contenido en forma de lista, sino que también son fundamentales en la creación de menús de navegación en páginas web.</p>
</div>

<h2>Ejercicio práctico</h2>
<p>Crea una lista ordenada con tus 3 películas favoritas. Luego, para cada película, crea una lista desordenada anidada con al menos 2 actores que aparecen en ella.</p>', 4, 15, 20, 1),

(5, 1, 'Enlaces en HTML', '<h2>Creando hipervínculos en HTML</h2>
<p>Los enlaces (hipervínculos) son uno de los elementos más importantes de HTML, ya que permiten la navegación entre páginas web. Se crean usando la etiqueta <code>&lt;a&gt;</code> (anchor).</p>

<h3>Sintaxis básica</h3>
<p>La estructura básica de un enlace es:</p>

<pre>&lt;a href="url_destino"&gt;Texto del enlace&lt;/a&gt;</pre>

<p>Donde:</p>
<ul>
  <li><code>href</code> es el atributo que especifica la URL de destino</li>
  <li>El texto entre las etiquetas de apertura y cierre es lo que el usuario verá y podrá hacer clic</li>
</ul>

<h3>Tipos de enlaces</h3>

<h4>1. Enlaces a páginas externas</h4>
<p>Para enlazar a otros sitios web, utiliza la URL completa:</p>

<pre>&lt;a href="https://www.ejemplo.com"&gt;Visita Ejemplo.com&lt;/a&gt;</pre>

<div class="tip-box">
  <h3>Buena práctica</h3>
  <p>Para enlaces externos, es recomendable añadir el atributo <code>target="_blank"</code> para que la página se abra en una nueva pestaña:</p>
  <pre>&lt;a href="https://www.ejemplo.com" target="_blank"&gt;Visita Ejemplo.com&lt;/a&gt;</pre>
</div>

<h4>2. Enlaces a páginas internas</h4>
<p>Para enlazar a otras páginas dentro de tu propio sitio web, puedes usar rutas relativas:</p>

<pre>&lt;a href="contacto.html"&gt;Contacto&lt;/a&gt;
&lt;a href="acerca/nosotros.html"&gt;Acerca de nosotros&lt;/a&gt;
&lt;a href="../index.html"&gt;Inicio&lt;/a&gt;</pre>

<div class="info-box">
  <h3>Rutas relativas vs absolutas</h3>
  <p><strong>Ruta relativa:</strong> Se basa en la ubicación actual del archivo HTML (ej: "contacto.html", "../index.html").</p>
  <p><strong>Ruta absoluta:</strong> Especifica la ruta completa desde la raíz del dominio (ej: "/productos/laptops.html", "https://www.misitioweb.com/contacto.html").</p>
</div>

<h4>3. Enlaces a secciones dentro de la misma página</h4>
<p>Puedes crear enlaces que salten a secciones específicas dentro de la misma página usando identificadores (id):</p>

<pre>&lt;!-- Enlace que lleva a la sección --&gt;
&lt;a href="#seccion1"&gt;Ir a Sección 1&lt;/a&gt;

&lt;!-- Más adelante en la página --&gt;
&lt;h2 id="seccion1"&gt;Sección 1&lt;/h2&gt;</pre>

<h4>4. Enlaces para correo electrónico</h4>
<p>Puedes crear enlaces que abran el cliente de correo del usuario:</p>

<pre>&lt;a href="mailto:ejemplo@correo.com"&gt;Envíame un correo&lt;/a&gt;</pre>

<p>También puedes añadir asunto y cuerpo del mensaje:</p>

<pre>&lt;a href="mailto:ejemplo@correo.com?subject=Consulta%20desde%20web&body=Hola,%20quisiera%20más%20información"&gt;Envíame un correo&lt;/a&gt;</pre>

<h4>5. Enlaces para descargar archivos</h4>
<p>Al enlazar a archivos descargables, puedes usar el atributo <code>download</code>:</p>

<pre>&lt;a href="archivos/documento.pdf" download&gt;Descargar PDF&lt;/a&gt;
&lt;a href="archivos/imagen.jpg" download="mi-imagen.jpg"&gt;Descargar imagen&lt;/a&gt;</pre>

<h3>Estilizando enlaces</h3>
<p>Los navegadores aplican estilos predeterminados a los enlaces, generalmente texto azul subrayado. Puedes cambiar estos estilos usando CSS:</p>

<pre>&lt;style&gt;
  a {
    color: #ff6600;
    text-decoration: none;
  }
  a:hover {
    text-decoration: underline;
  }
&lt;/style&gt;</pre>

<h3>Atributos útiles para enlaces</h3>
<ul>
  <li><code>target</code>: Define dónde se abrirá el enlace (_blank, _self, _parent, _top)</li>
  <li><code>title</code>: Proporciona información adicional sobre el enlace</li>
  <li><code>rel</code>: Define la relación entre la página actual y la enlazada</li>
</ul>

<pre>&lt;a href="https://ejemplo.com" target="_blank" rel="noopener" title="Visita nuestro sitio asociado"&gt;Enlace con atributos&lt;/a&gt;</pre>

<div class="warning-box">
  <h3>Seguridad en enlaces</h3>
  <p>Cuando uses <code>target="_blank"</code>, es recomendable añadir <code>rel="noopener noreferrer"</code> para prevenir vulnerabilidades de seguridad, especialmente al enlazar a sitios externos.</p>
</div>

<h2>Ejercicio práctico</h2>
<p>Crea una página HTML con:</p>
<ol>
  <li>Un enlace a tu sitio web favorito</li>
  <li>Un enlace a otra página HTML que hayas creado</li>
  <li>Un enlace a una sección específica dentro de la misma página</li>
  <li>Un enlace de correo electrónico</li>
</ol>', 5, 15, 20, 1),

(6, 1, 'Imágenes en HTML', '<h2>Incorporando imágenes en tus páginas web</h2>
<p>Las imágenes son elementos fundamentales en el diseño web, ya que pueden mejorar significativamente la estética y la experiencia del usuario. En HTML, las imágenes se insertan utilizando la etiqueta <code>&lt;img&gt;</code>.</p>

<h3>Sintaxis básica</h3>
<p>La estructura básica para insertar una imagen es:</p>

<pre>&lt;img src="ruta_de_la_imagen" alt="Texto alternativo"&gt;</pre>

<p>Donde:</p>
<ul>
  <li><code>src</code> (source): Es el atributo obligatorio que especifica la ruta de la imagen</li>
  <li><code>alt</code> (alternative text): También es un atributo obligatorio que proporciona una descripción textual de la imagen</li>
</ul>

<div class="warning-box">
  <h3>Importante</h3>
  <p>La etiqueta <code>&lt;img&gt;</code> es una etiqueta vacía, lo que significa que no tiene etiqueta de cierre ni contenido entre etiquetas de apertura y cierre.</p>
</div>

<h3>Rutas de imágenes</h3>
<p>Las rutas de las imágenes pueden ser relativas o absolutas:</p>

<h4>Rutas relativas</h4>
<pre>&lt;img src="imagen.jpg" alt="Imagen en la misma carpeta"&gt;
&lt;img src="imagenes/foto.jpg" alt="Imagen en subcarpeta"&gt;
&lt;img src="../imagenes/logo.png" alt="Imagen en carpeta superior"&gt;</pre>

<h4>Rutas absolutas</h4>
<pre>&lt;img src="https://www.ejemplo.com/imagenes/banner.jpg" alt="Imagen desde URL externa"&gt;</pre>

<div class="info-box">
  <h3>Formatos de imagen web</h3>
  <p><strong>JPG/JPEG:</strong> Ideal para fotografías e imágenes con muchos colores.</p>
  <p><strong>PNG:</strong> Mejor para imágenes con transparencia o con texto.</p>
  <p><strong>GIF:</strong> Utilizado para animaciones simples.</p>
  <p><strong>SVG:</strong> Gráficos vectoriales escalables, perfectos para iconos y logotipos.</p>
  <p><strong>WebP:</strong> Formato moderno con mejor compresión y calidad.</p>
</div>

<h3>Atributos importantes</h3>

<h4>1. Texto alternativo</h4>
<p>El atributo <code>alt</code> es crucial para:</p>
<ul>
  <li>Accesibilidad: Las personas con discapacidad visual dependen de lectores de pantalla que leen este texto</li>
  <li>SEO: Los motores de búsqueda utilizan el texto alternativo para entender las imágenes</li>
  <li>Fallback: Se muestra cuando la imagen no puede cargarse</li>
</ul>

<pre>&lt;img src="grafica-ventas-2023.jpg" alt="Gráfica de ventas del primer trimestre de 2023 mostrando un incremento del 15%"&gt;</pre>

<h4>2. Dimensiones</h4>
<p>Los atributos <code>width</code> (ancho) y <code>height</code> (alto) permiten especificar el tamaño de la imagen:</p>

<pre>&lt;img src="logo.png" alt="Logo de la empresa" width="200" height="100"&gt;</pre>

<div class="tip-box">
  <h3>Buena práctica</h3>
  <p>Especificar las dimensiones de la imagen ayuda al navegador a reservar espacio mientras se carga la imagen, evitando saltos en el contenido. Sin embargo, es mejor controlar el tamaño con CSS para sitios responsive.</p>
</div>

<h4>3. Título</h4>
<p>El atributo <code>title</code> proporciona información adicional que se muestra al pasar el cursor sobre la imagen:</p>

<pre>&lt;img src="equipo.jpg" alt="Equipo de desarrollo" title="Nuestro equipo durante la hackathon 2023"&gt;</pre>

<h3>Imágenes responsive</h3>
<p>Para hacer que las imágenes se adapten a diferentes tamaños de pantalla, puedes usar CSS o el atributo <code>style</code>:</p>

<pre>&lt;img src="paisaje.jpg" alt="Paisaje montañoso" style="max-width:100%; height:auto;"&gt;</pre>

<p>También puedes usar el moderno atributo <code>srcset</code> para proporcionar diferentes imágenes según la resolución de la pantalla:</p>

<pre>&lt;img src="imagen-small.jpg" 
     srcset="imagen-small.jpg 500w,
             imagen-medium.jpg 1000w,
             imagen-large.jpg 1500w"
     sizes="(max-width: 600px) 500px,
            (max-width: 1200px) 1000px,
            1500px"
     alt="Imagen responsive"&gt;</pre>

<h3>Figure y Figcaption</h3>
<p>El elemento <code>&lt;figure&gt;</code> junto con <code>&lt;figcaption&gt;</code> permite agregar leyendas a las imágenes:</p>

<pre>&lt;figure&gt;
  &lt;img src="grafico.png" alt="Gráfico de usuarios por región"&gt;
  &lt;figcaption&gt;Fig. 1: Distribución de usuarios por región geográfica en 2023.&lt;/figcaption&gt;
&lt;/figure&gt;</pre>

<div class="warning-box">
  <h3>Optimización de imágenes</h3>
  <p>Las imágenes grandes pueden ralentizar tu sitio web. Siempre optimiza las imágenes antes de subirlas:</p>
  <ul>
    <li>Redimensiona las imágenes al tamaño máximo necesario</li>
    <li>Comprime las imágenes para reducir su tamaño de archivo</li>
    <li>Utiliza formatos modernos como WebP cuando sea posible</li>
    <li>Considera usar servicios de CDN para imágenes</li>
  </ul>
</div>

<h2>Ejercicio práctico</h2>
<p>Crea una página HTML con:</p>
<ol>
  <li>Una imagen local con texto alternativo descriptivo</li>
  <li>Una imagen con dimensiones específicas</li>
  <li>Una imagen con leyenda usando figure y figcaption</li>
  <li>Una imagen que sea responsive (se adapte al ancho de la pantalla)</li>
</ol>', 6, 15, 25, 1),

(7, 1, 'Atributos HTML', '<h2>Entendiendo los atributos HTML</h2>
<p>Los atributos HTML proporcionan información adicional sobre los elementos HTML y siempre se especifican en la etiqueta de apertura. Los atributos generalmente vienen en pares de nombre/valor como: <code>nombre="valor"</code>.</p>

<h3>Sintaxis de los atributos</h3>

<pre>&lt;tag attribute1="value1" attribute2="value2"&gt;Contenido&lt;/tag&gt;</pre>

<div class="info-box">
  <h3>Reglas importantes</h3>
  <ul>
    <li>Los valores de los atributos deben estar siempre entre comillas (simples o dobles)</li>
    <li>El nombre del atributo va seguido de un signo igual (=)</li>
    <li>Se escriben en minúsculas (recomendado por HTML5)</li>
  </ul>
</div>

<h3>Atributos globales</h3>
<p>Algunos atributos pueden usarse en prácticamente cualquier elemento HTML. Estos son los más comunes:</p>

<h4>1. id</h4>
<p>Especifica un identificador único para un elemento. Se usa para referenciar el elemento desde CSS, JavaScript o enlaces internos:</p>

<pre>&lt;h2 id="seccion-contacto"&gt;Contacto&lt;/h2&gt;</pre>

<div class="warning-box">
  <h3>Importante</h3>
  <p>Cada valor de <code>id</code> debe ser único en toda la página. Dos elementos no pueden tener el mismo id.</p>
</div>

<h4>2. class</h4>
<p>Especifica una o varias clases para un elemento. Se usa principalmente para aplicar estilos CSS a grupos de elementos:</p>

<pre>&lt;p class="destacado"&gt;Este párrafo está destacado.&lt;/p&gt;
&lt;p class="destacado grande"&gt;Este párrafo tiene dos clases.&lt;/p&gt;</pre>

<h4>3. style</h4>
<p>Permite aplicar estilos CSS directamente a un elemento:</p>

<pre>&lt;p style="color: blue; font-size: 16px;"&gt;Este párrafo tiene estilos inline.&lt;/p&gt;</pre>

<h4>4. title</h4>
<p>Proporciona información adicional que se muestra como tooltip al pasar el cursor sobre el elemento:</p>

<pre>&lt;abbr title="HyperText Markup Language"&gt;HTML&lt;/abbr&gt;</pre>

<h4>5. lang</h4>
<p>Especifica el idioma del contenido del elemento:</p>

<pre>&lt;p lang="es"&gt;Este párrafo está en español.&lt;/p&gt;
&lt;p lang="en"&gt;This paragraph is in English.&lt;/p&gt;</pre>

<h4>6. data-*</h4>
<p>Los atributos personalizados que comienzan con <code>data-</code> permiten almacenar información adicional en elementos HTML. Son muy útiles para JavaScript:</p>

<pre>&lt;button data-action="submit" data-target="form1"&gt;Enviar&lt;/button&gt;</pre>

<h4>7. hidden</h4>
<p>Indica que un elemento aún no es o ya no es relevante y no debe mostrarse:</p>

<pre>&lt;div hidden&gt;Este contenido está oculto.&lt;/div&gt;</pre>

<h3>Atributos específicos de elementos</h3>
<p>Muchos elementos HTML tienen atributos específicos que solo funcionan con esos elementos particulares. Algunos ejemplos:</p>

<h4>Para enlaces (&lt;a&gt;)</h4>
<pre>&lt;a href="https://www.ejemplo.com" target="_blank" rel="noopener"&gt;Enlace externo&lt;/a&gt;</pre>

<h4>Para imágenes (&lt;img&gt;)</h4>
<pre>&lt;img src="imagen.jpg" alt="Descripción" width="300" height="200"&gt;</pre>

<h4>Para formularios e inputs</h4>
<pre>&lt;input type="text" name="username" placeholder="Ingrese su nombre" required&gt;</pre>

<h4>Para tablas</h4>
<pre>&lt;table border="1" cellpadding="5"&gt;
  &lt;tr&gt;
    &lt;td colspan="2"&gt;Esta celda ocupa dos columnas&lt;/td&gt;
  &lt;/tr&gt;
&lt;/table&gt;</pre>

<h3>Atributos booleanos</h3>
<p>Algunos atributos no necesitan un valor específico. Su presencia indica "verdadero" y su ausencia indica "falso":</p>

<pre>&lt;input type="checkbox" checked&gt;
&lt;button disabled&gt;Botón deshabilitado&lt;/button&gt;
&lt;input type="text" readonly&gt;</pre>

<p>En HTML5, estos atributos pueden escribirse de manera abreviada como se muestra arriba, o con la forma tradicional:</p>

<pre>&lt;input type="checkbox" checked="checked"&gt;</pre>

<div class="tip-box">
  <h3>Buena práctica</h3>
  <p>Aunque HTML5 permite omitir las comillas en ciertos casos, siempre es mejor incluirlas para mantener la consistencia y evitar errores.</p>
</div>

<h3>Validación de atributos</h3>
<p>Los navegadores generalmente ignoran los atributos que no reconocen y los valores incorrectos. Sin embargo, es importante usar atributos válidos y valores apropiados para garantizar que tu página web funcione correctamente y pase la validación de HTML.</p>

<div class="warning-box">
  <h3>Accesibilidad con atributos</h3>
  <p>Algunos atributos son cruciales para la accesibilidad, como:</p>
  <ul>
    <li><code>alt</code> para imágenes</li>
    <li><code>aria-*</code> para roles y estados de elementos</li>
    <li><code>role</code> para definir el propósito de un elemento</li>
    <li><code>tabindex</code> para controlar el orden de navegación con teclado</li>
  </ul>
</div>

<h2>Ejercicio práctico</h2>
<p>Crea una página HTML que incluya:</p>
<ol>
  <li>Un párrafo con un <code>id</code> único</li>
  <li>Varios elementos con la misma <code>class</code></li>
  <li>Un elemento con estilos inline usando el atributo <code>style</code></li>
  <li>Un elemento con un atributo <code>title</code> y comprueba el tooltip</li>
  <li>Un elemento con atributos <code>data-*</code> personalizados</li>
</ol>', 7, 15, 20, 1),

(8, 1, 'Tablas en HTML', '<h2>Creando tablas en HTML</h2>
<p>Las tablas HTML permiten organizar datos en filas y columnas, facilitando la presentación de información estructurada. Aunque no se recomienda usar tablas para diseño de páginas, siguen siendo fundamentales para mostrar datos tabulares.</p>

<h3>Estructura básica de una tabla</h3>
<p>Una tabla HTML se compone de estos elementos principales:</p>

<pre>&lt;table&gt;
  &lt;tr&gt;
    &lt;th&gt;Encabezado 1&lt;/th&gt;
    &lt;th&gt;Encabezado 2&lt;/th&gt;
  &lt;/tr&gt;
  &lt;tr&gt;
    &lt;td&gt;Fila 1, Celda 1&lt;/td&gt;
    &lt;td&gt;Fila 1, Celda 2&lt;/td&gt;
  &lt;/tr&gt;
  &lt;tr&gt;
    &lt;td&gt;Fila 2, Celda 1&lt;/td&gt;
    &lt;td&gt;Fila 2, Celda 2&lt;/td&gt;
  &lt;/tr&gt;
&lt;/table&gt;</pre>

<p>Donde:</p>
<ul>
  <li><code>&lt;table&gt;</code>: Define la tabla</li>
  <li><code>&lt;tr&gt;</code> (table row): Define una fila</li>
  <li><code>&lt;th&gt;</code> (table header): Define una celda de encabezado</li>
  <li><code>&lt;td&gt;</code> (table data): Define una celda de datos</li>
</ul>

<h3>Elementos adicionales para mejorar la estructura</h3>
<p>HTML ofrece elementos para organizar mejor el contenido de las tablas:</p>

<pre>&lt;table&gt;
  &lt;caption&gt;Ventas trimestrales por departamento&lt;/caption&gt;
  
  &lt;thead&gt;
    &lt;tr&gt;
      &lt;th&gt;Departamento&lt;/th&gt;
      &lt;th&gt;Q1&lt;/th&gt;
      &lt;th&gt;Q2&lt;/th&gt;
      &lt;th&gt;Q3&lt;/th&gt;
      &lt;th&gt;Q4&lt;/th&gt;
    &lt;/tr&gt;
  &lt;/thead&gt;
  
  &lt;tbody&gt;
    &lt;tr&gt;
      &lt;td&gt;Marketing&lt;/td&gt;
      &lt;td&gt;10,000€&lt;/td&gt;
      &lt;td&gt;12,500€&lt;/td&gt;
      &lt;td&gt;14,800€&lt;/td&gt;
      &lt;td&gt;16,200€&lt;/td&gt;
    &lt;/tr&gt;
    &lt;tr&gt;
      &lt;td&gt;Ventas&lt;/td&gt;
      &lt;td&gt;15,300€&lt;/td&gt;
      &lt;td&gt;18,200€&lt;/td&gt;
      &lt;td&gt;21,500€&lt;/td&gt;
      &lt;td&gt;25,800€&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tbody&gt;
  
  &lt;tfoot&gt;
    &lt;tr&gt;
      &lt;th&gt;Total&lt;/th&gt;
      &lt;td&gt;25,300€&lt;/td&gt;
      &lt;td&gt;30,700€&lt;/td&gt;
      &lt;td&gt;36,300€&lt;/td&gt;
      &lt;td&gt;42,000€&lt;/td&gt;
    &lt;/tr&gt;
  &lt;/tfoot&gt;
&lt;/table&gt;</pre>

<div class="info-box">
  <h3>Elementos para estructura de tabla</h3>
  <ul>
    <li><code>&lt;caption&gt;</code>: Agrega un título a la tabla</li>
    <li><code>&lt;thead&gt;</code>: Agrupa el contenido del encabezado</li>
    <li><code>&lt;tbody&gt;</code>: Agrupa el contenido principal</li>
    <li><code>&lt;tfoot&gt;</code>: Agrupa el contenido del pie de tabla</li>
  </ul>
</div>

<h3>Fusionar celdas</h3>
<p>Puedes combinar celdas horizontal o verticalmente usando los atributos <code>colspan</code> y <code>rowspan</code>:</p>

<h4>Combinando columnas</h4>
<pre>&lt;tr&gt;
  &lt;td colspan="2"&gt;Esta celda ocupa dos columnas&lt;/td&gt;
  &lt;td&gt;Celda normal&lt;/td&gt;
&lt;/tr&gt;</pre>

<h4>Combinando filas</h4>
<pre>&lt;tr&gt;
  &lt;td rowspan="2"&gt;Esta celda ocupa dos filas&lt;/td&gt;
  &lt;td&gt;Fila 1, Celda 2&lt;/td&gt;
&lt;/tr&gt;
&lt;tr&gt;
  &lt;td&gt;Fila 2, Celda 2&lt;/td&gt;
&lt;/tr&gt;</pre>

<div class="tip-box">
  <h3>Consejo práctico</h3>
  <p>Al fusionar celdas, asegúrate de mantener el mismo número de celdas en cada fila, teniendo en cuenta los valores de <code>colspan</code> y <code>rowspan</code>. Esto evitará problemas de diseño y renderizado.</p>
</div>

<h3>Atributos para tablas</h3>
<p>Algunos atributos útiles para tablas (aunque muchos están obsoletos en HTML5 y se recomienda usar CSS):</p>

<h4>Atributos de tabla</h4>
<ul>
  <li><code>border</code>: Define el ancho del borde (mejor usar CSS)</li>
  <li><code>cellspacing</code>: Espacio entre celdas (mejor usar CSS)</li>
  <li><code>cellpadding</code>: Espacio dentro de las celdas (mejor usar CSS)</li>
</ul>

<h4>Atributos de celda</h4>
<ul>
  <li><code>colspan</code>: Número de columnas que ocupa una celda</li>
  <li><code>rowspan</code>: Número de filas que ocupa una celda</li>
  <li><code>headers</code>: Asocia celdas de datos con celdas de encabezado (para accesibilidad)</li>
  <li><code>scope</code>: Indica si un encabezado es para una columna o fila (valores: "col", "row")</li>
</ul>

<h3>Estilizando tablas con CSS</h3>
<p>En lugar de los atributos HTML obsoletos, utiliza CSS para dar estilo a tus tablas:</p>

<pre>&lt;style&gt;
  table {
    border-collapse: collapse;
    width: 100%;
  }
  th, td {
    border: 1px solid #ddd;
    padding: 8px;
    text-align: left;
  }
  th {
    background-color: #f2f2f2;
  }
  tr:nth-child(even) {
    background-color: #f9f9f9;
  }
  caption {
    font-weight: bold;
    margin-bottom: 10px;
  }
&lt;/style&gt;</pre>

<h3>Accesibilidad en tablas</h3>
<p>Para hacer tablas más accesibles para usuarios con lectores de pantalla:</p>

<ul>
  <li>Usa <code>&lt;caption&gt;</code> para proporcionar un título descriptivo</li>
  <li>Usa <code>&lt;th&gt;</code> para encabezados de columnas y filas</li>
  <li>Usa el atributo <code>scope</code> en los encabezados</li>
  <li>Usa el atributo <code>headers</code> para asociar celdas complejas</li>
  <li>Evita tablas anidadas</li>
</ul>

<pre>&lt;table&gt;
  &lt;caption&gt;Horarios de clase&lt;/caption&gt;
  &lt;tr&gt;
    &lt;th scope="col"&gt;Hora&lt;/th&gt;
    &lt;th scope="col"&gt;Lunes&lt;/th&gt;
    &lt;th scope="col"&gt;Martes&lt;/th&gt;
  &lt;/tr&gt;
  &lt;tr&gt;
    &lt;th scope="row"&gt;9:00 - 10:00&lt;/th&gt;
    &lt;td&gt;Matemáticas&lt;/td&gt;
    &lt;td&gt;Ciencias&lt;/td&gt;
  &lt;/tr&gt;
&lt;/table&gt;</pre>

<div class="warning-box">
  <h3>Importante</h3>
  <p>Evita usar tablas para el diseño de la página web. Las tablas están diseñadas para datos tabulares, no para controlar el layout. Usa CSS (Flexbox o Grid) para el diseño de la página.</p>
</div>

<h2>Ejercicio práctico</h2>
<p>Crea una tabla HTML que incluya:</p>
<ol>
  <li>Un título (caption)</li>
  <li>Secciones de encabezado, cuerpo y pie (thead, tbody, tfoot)</li>
  <li>Al menos una celda que abarque múltiples columnas (colspan)</li>
  <li>Al menos una celda que abarque múltiples filas (rowspan)</li>
  <li>Estilos CSS básicos para mejorar la apariencia</li>
</ol>', 8, 15, 25, 1),

(9, 1, 'Formularios en HTML', '<h2>Creando formularios en HTML</h2>
<p>Los formularios HTML permiten recopilar datos de los usuarios, siendo fundamentales para la interacción en páginas web. Un formulario bien diseñado debe ser intuitivo, accesible y fácil de usar.</p>

<h3>Estructura básica de un formulario</h3>
<p>Un formulario se define mediante la etiqueta <code>&lt;form&gt;</code>:</p>

<pre>&lt;form action="/procesar.php" method="post"&gt;
  <!-- Elementos del formulario -->
  &lt;input type="text" name="nombre" placeholder="Tu nombre"&gt;
  &lt;input type="submit" value="Enviar"&gt;
&lt;/form&gt;</pre>

<p>Atributos principales del elemento <code>&lt;form&gt;</code>:</p>
<ul>
  <li><code>action</code>: URL a la que se enviarán los datos</li>
  <li><code>method</code>: Método HTTP utilizado (get o post)</li>
  <li><code>name</code>: Nombre del formulario (útil para JavaScript)</li>
  <li><code>target</code>: Dónde mostrar la respuesta (_self, _blank, etc.)</li>
  <li><code>enctype</code>: Tipo de codificación al enviar (importante para archivos)</li>
</ul>

<div class="info-box">
  <h3>GET vs POST</h3>
  <p><strong>GET:</strong> Añade los datos a la URL, visible en la barra de direcciones. Limitado en cantidad de datos. Ideal para búsquedas y operaciones que no modifican datos.</p>
  <p><strong>POST:</strong> Envía datos en el cuerpo de la solicitud HTTP, no visibles en la URL. Sin limitación práctica de tamaño. Recomendado para envíos de datos sensibles o formularios que modifican información del servidor.</p>
</div>

<h3>Elementos principales de formularios</h3>

<h4>1. Campos de texto</h4>
<pre>&lt;label for="nombre"&gt;Nombre:&lt;/label&gt;
&lt;input type="text" id="nombre" name="nombre" placeholder="Escribe tu nombre" required&gt;</pre>

<h4>2. Contraseñas</h4>
<pre>&lt;label for="password"&gt;Contraseña:&lt;/label&gt;
&lt;input type="password" id="password" name="password" minlength="8"&gt;</pre>

<h4>3. Campos de texto multilínea</h4>
<pre>&lt;label for="comentario"&gt;Comentario:&lt;/label&gt;
&lt;textarea id="comentario" name="comentario" rows="4" cols="50"&gt;&lt;/textarea&gt;</pre>

<h4>4. Casillas de verificación (Checkbox)</h4>
<pre>&lt;input type="checkbox" id="acepto" name="acepto" value="si"&gt;
&lt;label for="acepto"&gt;Acepto los términos y condiciones&lt;/label&gt;</pre>

<h4>5. Botones de radio</h4>
<pre>&lt;p&gt;Selecciona tu género:&lt;/p&gt;
&lt;input type="radio" id="hombre" name="genero" value="hombre"&gt;
&lt;label for="hombre"&gt;Hombre&lt;/label&gt;
&lt;input type="radio" id="mujer" name="genero" value="mujer"&gt;
&lt;label for="mujer"&gt;Mujer&lt;/label&gt;
&lt;input type="radio" id="otro" name="genero" value="otro"&gt;
&lt;label for="otro"&gt;Otro&lt;/label&gt;</pre>

<div class="tip-box">
  <h3>Nota importante</h3>
  <p>En los botones de radio, todos los que pertenecen al mismo grupo deben tener el mismo valor en el atributo <code>name</code> para asegurar que solo se pueda seleccionar uno de ellos.</p>
</div>

<h4>6. Listas desplegables</h4>
<pre>&lt;label for="pais"&gt;País:&lt;/label&gt;
&lt;select id="pais" name="pais"&gt;
  &lt;option value=""&gt;Selecciona un país&lt;/option&gt;
  &lt;option value="es"&gt;España&lt;/option&gt;
  &lt;option value="mx"&gt;México&lt;/option&gt;
  &lt;option value="ar"&gt;Argentina&lt;/option&gt;
  &lt;option value="co"&gt;Colombia&lt;/option&gt;
&lt;/select&gt;</pre>

<p>Para permitir selección múltiple:</p>
<pre>&lt;select id="idiomas" name="idiomas[]" multiple&gt;
  &lt;option value="en"&gt;Inglés&lt;/option&gt;
  &lt;option value="es"&gt;Español&lt;/option&gt;
  &lt;option value="fr"&gt;Francés&lt;/option&gt;
&lt;/select&gt;</pre>

<h4>7. Subida de archivos</h4>
<pre>&lt;label for="archivo"&gt;Selecciona un archivo:&lt;/label&gt;
&lt;input type="file" id="archivo" name="archivo"&gt;</pre>

<p>Para permitir múltiples archivos:</p>
<pre>&lt;input type="file" id="archivos" name="archivos[]" multiple&gt;</pre>

<div class="warning-box">
  <h3>Importante</h3>
  <p>Al trabajar con subida de archivos, debes establecer el atributo <code>enctype="multipart/form-data"</code> en la etiqueta <code>&lt;form&gt;</code>.</p>
</div>

<h4>8. Botones</h4>
<pre>&lt;input type="submit" value="Enviar formulario"&gt;
&lt;input type="reset" value="Restablecer valores"&gt;
&lt;button type="button"&gt;Botón genérico&lt;/button&gt;</pre>

<h3>Nuevos tipos de inputs en HTML5</h3>
<p>HTML5 introdujo varios tipos nuevos de inputs para mejorar la experiencia de usuario y facilitar la validación:</p>

<ul>
  <li><code>email</code>: Para direcciones de correo electrónico</li>
  <li><code>number</code>: Para valores numéricos</li>
  <li><code>tel</code>: Para números telefónicos</li>
  <li><code>url</code>: Para direcciones web</li>
  <li><code>date</code>: Para seleccionar fechas</li>
  <li><code>time</code>: Para seleccionar horas</li>
  <li><code>color</code>: Para seleccionar colores</li>
  <li><code>range</code>: Para seleccionar valores con un deslizador</li>
  <li><code>search</code>: Para campos de búsqueda</li>
</ul>

<pre>&lt;label for="email"&gt;Email:&lt;/label&gt;
&lt;input type="email" id="email" name="email" required&gt;

&lt;label for="edad"&gt;Edad:&lt;/label&gt;
&lt;input type="number" id="edad" name="edad" min="18" max="120"&gt;

&lt;label for="telefono"&gt;Teléfono:&lt;/label&gt;
&lt;input type="tel" id="telefono" name="telefono" pattern="[0-9]{9}"&gt;

&lt;label for="fecha"&gt;Fecha de nacimiento:&lt;/label&gt;
&lt;input type="date" id="fecha" name="fecha"&gt;</pre>

<h3>Agrupando elementos de formulario</h3>
<p>La etiqueta <code>&lt;fieldset&gt;</code> permite agrupar elementos relacionados. La etiqueta <code>&lt;legend&gt;</code> proporciona un título para el grupo:</p>

<pre>&lt;fieldset&gt;
  &lt;legend&gt;Información personal&lt;/legend&gt;
  &lt;label for="nombre"&gt;Nombre:&lt;/label&gt;
  &lt;input type="text" id="nombre" name="nombre"&gt;
  
  &lt;label for="email"&gt;Email:&lt;/label&gt;
  &lt;input type="email" id="email" name="email"&gt;
&lt;/fieldset&gt;</pre>

<h3>Validación de formularios</h3>
<p>HTML5 proporciona validación básica del lado del cliente con atributos como:</p>

<ul>
  <li><code>required</code>: El campo es obligatorio</li>
  <li><code>minlength</code>/<code>maxlength</code>: Longitud mínima/máxima de texto</li>
  <li><code>min</code>/<code>max</code>: Valor mínimo/máximo para campos numéricos</li>
  <li><code>pattern</code>: Expresión regular que debe cumplir el valor</li>
</ul>

<pre>&lt;input type="text" id="usuario" name="usuario" required minlength="4" maxlength="15" pattern="[a-zA-Z0-9]+"&gt;</pre>

<div class="tip-box">
  <h3>Buena práctica</h3>
  <p>Aunque HTML5 ofrece validación del lado del cliente, siempre debes implementar también validación del lado del servidor, ya que la validación HTML puede ser evitada.</p>
</div>

<h3>Accesibilidad en formularios</h3>
<p>Para hacer formularios accesibles:</p>

<ul>
  <li>Usa elementos <code>&lt;label&gt;</code> correctamente vinculados a los campos con <code>for</code> e <code>id</code></li>
  <li>Agrupa campos relacionados con <code>&lt;fieldset&gt;</code> y <code>&lt;legend&gt;</code></li>
  <li>Proporciona instrucciones claras</li>
  <li>Usa atributos <code>aria-*</code> cuando sea necesario</li>
  <li>Asegúrate de que el formulario sea navegable con teclado</li>
</ul>

<h2>Ejercicio práctico</h2>
<p>Crea un formulario de contacto que incluya:</p>
<ol>
  <li>Campos para nombre, email y teléfono con validación</li>
  <li>Un selector de tipo de consulta (lista desplegable)</li>
  <li>Un área de texto para el mensaje</li>
  <li>Una casilla de verificación para aceptar términos</li>
  <li>Botones para enviar y restablecer el formulario</li>
</ol>', 9, 15, 30, 1),

(10, 1, 'HTML Semántico', '<h2>HTML Semántico: Dando significado a tu contenido</h2>
<p>El HTML semántico se refiere al uso de etiquetas HTML que no solo definen la presentación, sino también el significado y la estructura del contenido. Utilizar etiquetas semánticas mejora la accesibilidad, el SEO y hace que tu código sea más claro y mantenible.</p>

<h3>¿Por qué es importante el HTML semántico?</h3>
<ul>
  <li>Mejora la accesibilidad para personas con discapacidades</li>
  <li>Facilita la indexación por parte de los motores de búsqueda</li>
  <li>Hace que el código sea más fácil de leer y mantener</li>
  <li>Ayuda a los navegadores a interpretar correctamente el contenido</li>
  <li>Facilita el desarrollo de estilos CSS más consistentes</li>
</ul>

<div class="info-box">
  <h3>Antes vs. Ahora</h3>
  <p>Antes de HTML5, los diseñadores usaban muchos <code>&lt;div&gt;</code> con IDs y clases para estructurar páginas. Con HTML5, podemos usar elementos semánticos específicos como <code>&lt;header&gt;</code>, <code>&lt;nav&gt;</code>, <code>&lt;main&gt;</code>, que comunican directamente el propósito del contenido.</p>
</div>

<h3>Elementos semánticos principales en HTML5</h3>

<h4>Estructura de página</h4>
<pre>&lt;header&gt;...&lt;/header&gt;               <!-- Encabezado de la página o sección -->
&lt;nav&gt;...&lt;/nav&gt;                   <!-- Navegación principal -->
&lt;main&gt;...&lt;/main&gt;                 <!-- Contenido principal -->
&lt;article&gt;...&lt;/article&gt;           <!-- Contenido independiente y auto-contenido -->
&lt;section&gt;...&lt;/section&gt;           <!-- Sección temática del contenido -->
&lt;aside&gt;...&lt;/aside&gt;               <!-- Contenido relacionado pero secundario -->
&lt;footer&gt;...&lt;/footer&gt;             <!-- Pie de página o sección --></pre>

<p>Ejemplo de estructura semántica:</p>

<pre>&lt;body&gt;
  &lt;header&gt;
    &lt;h1&gt;Mi Sitio Web&lt;/h1&gt;
    &lt;nav&gt;
      &lt;ul&gt;
        &lt;li&gt;&lt;a href="#"&gt;Inicio&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#"&gt;Servicios&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#"&gt;Contacto&lt;/a&gt;&lt;/li&gt;
      &lt;/ul&gt;
    &lt;/nav&gt;
  &lt;/header&gt;
  
  &lt;main&gt;
    &lt;article&gt;
      &lt;header&gt;
        &lt;h2&gt;Título del Artículo&lt;/h2&gt;
        &lt;p&gt;Publicado el &lt;time datetime="2023-09-15"&gt;15 de septiembre de 2023&lt;/time&gt;&lt;/p&gt;
      &lt;/header&gt;
      
      &lt;section&gt;
        &lt;h3&gt;Primera Sección&lt;/h3&gt;
        &lt;p&gt;Contenido de la primera sección...&lt;/p&gt;
      &lt;/section&gt;
      
      &lt;section&gt;
        &lt;h3&gt;Segunda Sección&lt;/h3&gt;
        &lt;p&gt;Contenido de la segunda sección...&lt;/p&gt;
      &lt;/section&gt;
      
      &lt;footer&gt;
        &lt;p&gt;Autor: Nombre del Autor&lt;/p&gt;
      &lt;/footer&gt;
    &lt;/article&gt;
    
    &lt;aside&gt;
      &lt;h3&gt;Artículos Relacionados&lt;/h3&gt;
      &lt;ul&gt;
        &lt;li&gt;&lt;a href="#"&gt;Artículo relacionado 1&lt;/a&gt;&lt;/li&gt;
        &lt;li&gt;&lt;a href="#"&gt;Artículo relacionado 2&lt;/a&gt;&lt;/li&gt;
      &lt;/ul&gt;
    &lt;/aside&gt;
  &lt;/main&gt;
  
  &lt;footer&gt;
    &lt;p&gt;&copy; 2023 Mi Sitio Web. Todos los derechos reservados.&lt;/p&gt;
  &lt;/footer&gt;
&lt;/body&gt;</pre>

<div class="warning-box">
  <h3>Importante</h3>
  <p>Evita usar elementos semánticos incorrectamente. Por ejemplo, no uses <code>&lt;article&gt;</code> para un simple párrafo o <code>&lt;section&gt;</code> como reemplazo directo de <code>&lt;div&gt;</code> sin propósito semántico.</p>
</div>

<h3>Elementos semánticos adicionales</h3>

<h4>Texto y contenido</h4>
<pre>&lt;figure&gt;
  &lt;img src="grafico.jpg" alt="Descripción del gráfico"&gt;
  &lt;figcaption&gt;Figura 1: Explicación del gráfico&lt;/figcaption&gt;
&lt;/figure&gt;

&lt;mark&gt;Texto resaltado&lt;/mark&gt;

&lt;time datetime="2023-09-15T14:30:00Z"&gt;15 de septiembre de 2023 a las 14:30 UTC&lt;/time&gt;

&lt;details&gt;
  &lt;summary&gt;Haz clic para ver más información&lt;/summary&gt;
  &lt;p&gt;Aquí hay información adicional que se muestra al hacer clic.&lt;/p&gt;
&lt;/details&gt;</pre>

<h4>Elementos para aplicaciones web</h4>
<pre>&lt;dialog&gt;
  Este es un cuadro de diálogo que puede ser mostrado/ocultado.
&lt;/dialog&gt;

&lt;progress value="70" max="100"&gt;70%&lt;/progress&gt;

&lt;meter value="0.6"&gt;60%&lt;/meter&gt;</pre>

<h3>Microdata y atributos semánticos</h3>
<p>HTML5 también permite añadir información semántica adicional usando atributos como <code>data-*</code> y microdata:</p>

<pre>&lt;article itemscope itemtype="http://schema.org/BlogPosting"&gt;
  &lt;h2 itemprop="headline"&gt;Título del Post&lt;/h2&gt;
  &lt;p&gt;Escrito por &lt;span itemprop="author"&gt;Nombre del Autor&lt;/span&gt;&lt;/p&gt;
  &lt;div itemprop="articleBody"&gt;
    Contenido del post...
  &lt;/div&gt;
&lt;/article&gt;</pre>

<p>Los atributos <code>data-*</code> te permiten almacenar información personalizada:</p>

<pre>&lt;button data-action="delete" data-id="123"&gt;Eliminar&lt;/button&gt;</pre>

<h3>Buenas prácticas para HTML semántico</h3>

<ol>
  <li><strong>Usa la etiqueta correcta para cada propósito</strong>: Elige el elemento HTML que mejor represente el significado del contenido.</li>
  <li><strong>Estructura jerárquica de encabezados</strong>: Utiliza <code>&lt;h1&gt;</code> a <code>&lt;h6&gt;</code> de manera jerárquica, comenzando con <code>&lt;h1&gt;</code> y sin saltar niveles.</li>
  <li><strong>Evita usar elementos solo por su apariencia</strong>: No uses <code>&lt;h1&gt;</code> solo para texto grande o <code>&lt;blockquote&gt;</code> para indentar texto.</li>
  <li><strong>Usa <code>&lt;div&gt;</code> y <code>&lt;span&gt;</code> para diseño, no para semántica</strong>: Estos elementos no tienen significado semántico y deberían usarse solo cuando no hay una alternativa semántica adecuada.</li>
  <li><strong>Proporciona texto alternativo para imágenes</strong>: Usa siempre el atributo <code>alt</code> en imágenes con descripciones significativas.</li>
  <li><strong>Crea formularios accesibles</strong>: Usa <code>&lt;label&gt;</code>, <code>&lt;fieldset&gt;</code> y <code>&lt;legend&gt;</code> apropiadamente.</li>
</ol>

<div class="tip-box">
  <h3>Comprobando la semántica</h3>
  <p>Puedes validar la estructura semántica de tu página usando herramientas como el Validador de HTML del W3C o extensiones como axe y Lighthouse en las herramientas para desarrolladores de Chrome.</p>
</div>

<h3>Beneficios para SEO</h3>
<p>Los motores de búsqueda utilizan el HTML semántico para entender mejor el contenido de tu página:</p>

<ul>
  <li>Los encabezados (<code>&lt;h1&gt;</code> a <code>&lt;h6&gt;</code>) ayudan a determinar la importancia de diferentes secciones</li>
  <li>Elementos como <code>&lt;article&gt;</code> y <code>&lt;nav&gt;</code> ayudan a identificar el tipo de contenido</li>
  <li>El uso de Schema.org con microdata mejora la visualización en resultados de búsqueda</li>
  <li>El contenido bien estructurado suele obtener una mejor clasificación</li>
</ul>

<h2>Ejercicio práctico</h2>
<p>Convierte la siguiente estructura no semántica en HTML semántico:</p>

<pre>&lt;div class="header"&gt;
  &lt;h1&gt;Mi Blog&lt;/h1&gt;
  &lt;div class="menu"&gt;
    &lt;ul&gt;
      &lt;li&gt;&lt;a href="#"&gt;Inicio&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="#"&gt;Artículos&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="#"&gt;Contacto&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/div&gt;
&lt;/div&gt;

&lt;div class="content"&gt;
  &lt;div class="post"&gt;
    &lt;h2&gt;Título del Post&lt;/h2&gt;
    &lt;div class="post-meta"&gt;Publicado el 15/09/2023&lt;/div&gt;
    &lt;div class="post-content"&gt;
      &lt;p&gt;Contenido del post...&lt;/p&gt;
    &lt;/div&gt;
  &lt;/div&gt;
  
  &lt;div class="sidebar"&gt;
    &lt;h3&gt;Posts Recientes&lt;/h3&gt;
    &lt;ul&gt;
      &lt;li&gt;&lt;a href="#"&gt;Post 1&lt;/a&gt;&lt;/li&gt;
      &lt;li&gt;&lt;a href="#"&gt;Post 2&lt;/a&gt;&lt;/li&gt;
    &lt;/ul&gt;
  &lt;/div&gt;
&lt;/div&gt;

&lt;div class="footer"&gt;
  &lt;p&gt;Copyright 2023&lt;/p&gt;
&lt;/div&gt;</pre>', 10, 15, 25, 1);

-- Módulo 2: Estilización con CSS
INSERT INTO modules (module_id, title, description, order_index, icon, is_active) VALUES 
(2, 'Estilización con CSS', 'Aprende a dar estilo a tus páginas web con CSS para crear diseños atractivos y responsivos.', 2, 'fas fa-palette', 1);

-- Insertar datos iniciales por módulo
-- Inserta los valores iniciales para el módulo HTML (10 lecciones)
-- y después deberías crear el contenido para el módulo CSS (10 lecciones)
