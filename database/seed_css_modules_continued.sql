-- Continuación de la lección 20 del módulo CSS
UPDATE lessons SET content = CONCAT(content, '&lt;/html&gt;</pre>

<h3>Configuración de CSS base</h3>
<p>Comenzamos con resetear y establecer valores predeterminados:</p>

<h4>1. CSS Reset/Normalize</h4>
<pre>/* CSS Reset básico */
*, *::before, *::after {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

/* Mejoras de accesibilidad y usabilidad */
:focus {
  outline: 2px solid var(--color-primary);
  outline-offset: 2px;
}

body {
  line-height: 1.5;
  -webkit-font-smoothing: antialiased;
}

img, picture, video, canvas, svg {
  display: block;
  max-width: 100%;
}

input, button, textarea, select {
  font: inherit;
}

/* Evitar desbordamiento de palabras largas */
p, h1, h2, h3, h4, h5, h6 {
  overflow-wrap: break-word;
}</pre>

<h4>2. Variables CSS</h4>
<pre>:root {
  /* Colores */
  --color-primary: #3498db;
  --color-primary-light: #5dade2;
  --color-primary-dark: #2980b9;
  --color-accent: #e74c3c;
  --color-text: #333333;
  --color-text-light: #666666;
  --color-bg: #ffffff;
  --color-bg-alt: #f8f8f8;
  --color-border: #dddddd;
  
  /* Tipografía */
  --font-primary: "Roboto", sans-serif;
  --font-secondary: "Montserrat", sans-serif;
  --font-mono: "Courier New", monospace;
  
  --font-size-base: 1rem;
  --font-size-sm: 0.875rem;
  --font-size-lg: 1.125rem;
  --font-size-xl: 1.25rem;
  --font-size-2xl: 1.5rem;
  --font-size-3xl: 1.875rem;
  --font-size-4xl: 2.25rem;
  
  /* Espaciado */
  --spacing-unit: 0.25rem;
  --spacing-1: calc(var(--spacing-unit) * 1);  /* 0.25rem */
  --spacing-2: calc(var(--spacing-unit) * 2);  /* 0.5rem */
  --spacing-3: calc(var(--spacing-unit) * 3);  /* 0.75rem */
  --spacing-4: calc(var(--spacing-unit) * 4);  /* 1rem */
  --spacing-6: calc(var(--spacing-unit) * 6);  /* 1.5rem */
  --spacing-8: calc(var(--spacing-unit) * 8);  /* 2rem */
  --spacing-12: calc(var(--spacing-unit) * 12); /* 3rem */
  --spacing-16: calc(var(--spacing-unit) * 16); /* 4rem */
  
  /* Sombras */
  --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
  --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
  
  /* Border radius */
  --radius-sm: 0.125rem;
  --radius-md: 0.25rem;
  --radius-lg: 0.5rem;
  --radius-full: 9999px;
  
  /* Container max width */
  --container-max-width: 1200px;
  --container-padding: var(--spacing-4);
}</pre>

<h4>3. Estilos base y tipografía</h4>
<pre>body {
  font-family: var(--font-primary);
  font-size: var(--font-size-base);
  color: var(--color-text);
  background-color: var(--color-bg);
}

h1, h2, h3, h4, h5, h6 {
  font-family: var(--font-secondary);
  font-weight: 700;
  line-height: 1.2;
  margin-bottom: var(--spacing-4);
}

h1 { font-size: var(--font-size-4xl); }
h2 { font-size: var(--font-size-3xl); }
h3 { font-size: var(--font-size-2xl); }
h4 { font-size: var(--font-size-xl); }
h5 { font-size: var(--font-size-lg); }
h6 { font-size: var(--font-size-base); }

p {
  margin-bottom: var(--spacing-4);
}

a {
  color: var(--color-primary);
  text-decoration: none;
  transition: color 0.3s ease;
}

a:hover {
  color: var(--color-primary-dark);
  text-decoration: underline;
}

/* Responsive typography */
@media (max-width: 768px) {
  :root {
    --font-size-base: 0.9375rem;
  }
  
  h1 { font-size: var(--font-size-3xl); }
  h2 { font-size: var(--font-size-2xl); }
  h3 { font-size: var(--font-size-xl); }
}</pre>

<h3>Componentes de Layout</h3>

<h4>1. Contenedor principal</h4>
<pre>.container {
  width: 100%;
  max-width: var(--container-max-width);
  margin-left: auto;
  margin-right: auto;
  padding-left: var(--container-padding);
  padding-right: var(--container-padding);
}

/* Contenedores de ancho completo */
.container-fluid {
  width: 100%;
  padding-left: var(--container-padding);
  padding-right: var(--container-padding);
}</pre>

<h4>2. Sistema de grid para layout</h4>
<pre>.grid {
  display: grid;
  gap: var(--spacing-4);
}

/* Grid de 12 columnas */
.grid-cols-12 {
  grid-template-columns: repeat(12, 1fr);
}

/* Grid responsive con auto-fit */
.auto-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacing-4);
}</pre>

<h4>3. Header y navegación</h4>
<pre>.site-header {
  background-color: var(--color-bg);
  box-shadow: var(--shadow-sm);
  position: sticky;
  top: 0;
  z-index: 100;
}

.site-header .container {
  display: flex;
  justify-content: space-between;
  align-items: center;
  height: 80px;
}

.logo {
  font-family: var(--font-secondary);
  font-size: var(--font-size-xl);
  font-weight: 700;
}

.main-nav .menu {
  display: flex;
  list-style: none;
  gap: var(--spacing-8);
}

.mobile-menu-toggle {
  display: none;
  background: none;
  border: none;
  cursor: pointer;
}

/* Hamburger icon */
.hamburger {
  display: block;
  width: 24px;
  height: 2px;
  background-color: var(--color-text);
  position: relative;
}

.hamburger::before,
.hamburger::after {
  content: "";
  display: block;
  width: 24px;
  height: 2px;
  background-color: var(--color-text);
  position: absolute;
}

.hamburger::before {
  top: -8px;
}

.hamburger::after {
  bottom: -8px;
}

@media (max-width: 768px) {
  .mobile-menu-toggle {
    display: block;
  }
  
  .main-nav .menu {
    display: none;
    position: absolute;
    top: 80px;
    left: 0;
    right: 0;
    flex-direction: column;
    background-color: var(--color-bg);
    padding: var(--spacing-4);
    box-shadow: var(--shadow-md);
  }
  
  .main-nav .menu.active {
    display: flex;
  }
}</pre>

<h4>4. Secciones de contenido</h4>
<pre>section {
  padding: var(--spacing-16) 0;
}

.section-title {
  text-align: center;
  margin-bottom: var(--spacing-12);
}

.section-title::after {
  content: "";
  display: block;
  width: 60px;
  height: 4px;
  background-color: var(--color-primary);
  margin: var(--spacing-4) auto 0;
}

/* Sección Hero */
.hero {
  background-color: var(--color-primary);
  color: white;
  text-align: center;
  padding: var(--spacing-16) 0;
}

.hero h1 {
  font-size: clamp(2rem, 5vw, 3.5rem);
  margin-bottom: var(--spacing-4);
}

.hero .lead {
  font-size: var(--font-size-xl);
  margin-bottom: var(--spacing-8);
  max-width: 600px;
  margin-left: auto;
  margin-right: auto;
}

/* Sección Features */
.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
  gap: var(--spacing-8);
}

.feature-card {
  background-color: var(--color-bg);
  border-radius: var(--radius-lg);
  padding: var(--spacing-6);
  box-shadow: var(--shadow-md);
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feature-card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
}

.feature-card .icon {
  width: 60px;
  height: 60px;
  background-color: var(--color-primary-light);
  color: white;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto var(--spacing-4);
  font-size: var(--font-size-2xl);
}</pre>

<h4>5. Footer</h4>
<pre>.site-footer {
  background-color: var(--color-bg-alt);
  padding: var(--spacing-16) 0 var(--spacing-8);
  border-top: 1px solid var(--color-border);
}

.footer-widgets {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: var(--spacing-8);
  margin-bottom: var(--spacing-12);
}

.widget h4 {
  margin-bottom: var(--spacing-4);
  padding-bottom: var(--spacing-2);
  border-bottom: 1px solid var(--color-border);
}

.widget ul {
  list-style: none;
}

.widget ul li {
  margin-bottom: var(--spacing-2);
}

.copyright {
  text-align: center;
  color: var(--color-text-light);
  padding-top: var(--spacing-6);
  border-top: 1px solid var(--color-border);
}</pre>

<h3>Componentes de UI</h3>

<h4>1. Botones</h4>
<pre>.btn {
  display: inline-block;
  padding: var(--spacing-3) var(--spacing-6);
  border-radius: var(--radius-md);
  font-weight: 500;
  text-align: center;
  cursor: pointer;
  transition: 
    background-color 0.3s ease,
    color 0.3s ease,
    border-color 0.3s ease,
    box-shadow 0.3s ease;
  text-decoration: none;
}

.btn:hover {
  text-decoration: none;
}

.btn-primary {
  background-color: var(--color-primary);
  color: white;
  border: 1px solid var(--color-primary);
}

.btn-primary:hover {
  background-color: var(--color-primary-dark);
  border-color: var(--color-primary-dark);
  color: white;
}

.btn-secondary {
  background-color: transparent;
  color: var(--color-primary);
  border: 1px solid var(--color-primary);
}

.btn-secondary:hover {
  background-color: var(--color-primary-light);
  color: white;
  border-color: var(--color-primary-light);
}

.btn-lg {
  padding: var(--spacing-4) var(--spacing-8);
  font-size: var(--font-size-lg);
}

.btn-sm {
  padding: var(--spacing-2) var(--spacing-4);
  font-size: var(--font-size-sm);
}</pre>

<h4>2. Tarjetas</h4>
<pre>.card {
  background-color: var(--color-bg);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-md);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.card:hover {
  transform: translateY(-5px);
  box-shadow: var(--shadow-lg);
}

.card-img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.card-content {
  padding: var(--spacing-6);
}

.card-title {
  font-size: var(--font-size-xl);
  margin-bottom: var(--spacing-2);
}

.card-text {
  color: var(--color-text-light);
  margin-bottom: var(--spacing-4);
}

.card-footer {
  padding: var(--spacing-4) var(--spacing-6);
  border-top: 1px solid var(--color-border);
  display: flex;
  justify-content: space-between;
  align-items: center;
}</pre>

<h3>Utilidades</h3>

<h4>1. Espaciado</h4>
<pre>/* Margin */
.m-0 { margin: 0; }
.m-1 { margin: var(--spacing-1); }
.m-2 { margin: var(--spacing-2); }
.m-4 { margin: var(--spacing-4); }
.m-8 { margin: var(--spacing-8); }

.mx-auto { 
  margin-left: auto;
  margin-right: auto;
}

.mt-0 { margin-top: 0; }
.mt-2 { margin-top: var(--spacing-2); }
.mt-4 { margin-top: var(--spacing-4); }
.mt-8 { margin-top: var(--spacing-8); }

.mb-0 { margin-bottom: 0; }
.mb-2 { margin-bottom: var(--spacing-2); }
.mb-4 { margin-bottom: var(--spacing-4); }
.mb-8 { margin-bottom: var(--spacing-8); }

/* Padding */
.p-0 { padding: 0; }
.p-1 { padding: var(--spacing-1); }
.p-2 { padding: var(--spacing-2); }
.p-4 { padding: var(--spacing-4); }
.p-8 { padding: var(--spacing-8); }</pre>

<h4>2. Flexbox</h4>
<pre>.flex { display: flex; }
.flex-col { flex-direction: column; }
.flex-wrap { flex-wrap: wrap; }

.items-center { align-items: center; }
.items-start { align-items: flex-start; }
.items-end { align-items: flex-end; }

.justify-center { justify-content: center; }
.justify-between { justify-content: space-between; }
.justify-start { justify-content: flex-start; }
.justify-end { justify-content: flex-end; }

.gap-2 { gap: var(--spacing-2); }
.gap-4 { gap: var(--spacing-4); }
.gap-8 { gap: var(--spacing-8); }</pre>

<h4>3. Texto</h4>
<pre>.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

.text-primary { color: var(--color-primary); }
.text-light { color: var(--color-text-light); }
.text-white { color: white; }

.font-bold { font-weight: 700; }
.font-medium { font-weight: 500; }
.font-normal { font-weight: 400; }

.text-sm { font-size: var(--font-size-sm); }
.text-base { font-size: var(--font-size-base); }
.text-lg { font-size: var(--font-size-lg); }
.text-xl { font-size: var(--font-size-xl); }
.text-2xl { font-size: var(--font-size-2xl); }</pre>

<h4>4. Visibilidad</h4>
<pre>.hidden { display: none; }

.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}

@media (max-width: 768px) {
  .hidden-sm { display: none; }
}

@media (min-width: 769px) {
  .hidden-lg { display: none; }
}</pre>

<h3>Media Queries</h3>
<p>Es importante tener una estrategia coherente para las media queries:</p>

<pre>/* Variables para breakpoints */
:root {
  --breakpoint-sm: 576px;
  --breakpoint-md: 768px;
  --breakpoint-lg: 992px;
  --breakpoint-xl: 1200px;
}

/* Mobile First */
/* Estilos base para móviles */

/* Small devices (landscape phones) */
@media (min-width: 576px) {
  /* Estilos para viewports >= 576px */
}

/* Medium devices (tablets) */
@media (min-width: 768px) {
  /* Estilos para viewports >= 768px */
}

/* Large devices (desktops) */
@media (min-width: 992px) {
  /* Estilos para viewports >= 992px */
}

/* Extra large devices */
@media (min-width: 1200px) {
  /* Estilos para viewports >= 1200px */
}</pre>

<h3>Ejemplo de página completa</h3>
<p>Combinando todos estos componentes, podemos crear diseños de páginas completas atractivos y funcionales. Aquí hay un ejemplo de cómo se vería el CSS completo para una página de inicio básica:</p>

<a href="https://codepen.io/ejemplo/pen/yLPQRWJ" target="_blank">Ver ejemplo en CodePen</a>

<h3>Mejores prácticas para maquetación</h3>

<h4>1. Organización del código</h4>
<ul>
  <li>Sigue una metodología como BEM (Block, Element, Modifier) para nombrar clases</li>
  <li>Agrupa el CSS de manera lógica (reset, variables, base, componentes, utilidades)</li>
  <li>Comenta secciones importantes para facilitar la navegación</li>
  <li>Considera usar CSS modular o sistemas como CSS Modules o Styled Components</li>
</ul>

<h4>2. Rendimiento</h4>
<ul>
  <li>Minimiza el uso de selectores complejos y anidados</li>
  <li>Evita el uso excesivo de !important</li>
  <li>Utiliza propiedades que favorezcan el rendimiento (transform, opacity vs. propiedades que causan reflow)</li>
  <li>Optimiza el "critical CSS" para carga rápida</li>
</ul>

<h4>3. Accesibilidad</h4>
<ul>
  <li>Asegura suficiente contraste entre texto y fondo</li>
  <li>Diseña teniendo en cuenta usuarios de teclado (estados focus visibles)</li>
  <li>Incluye estilos para prefers-reduced-motion</li>
  <li>Prueba con zoom de texto (hasta 200%)</li>
</ul>

<h4>4. Responsividad</h4>
<ul>
  <li>Adopta un enfoque Mobile First</li>
  <li>Usa unidades relativas (%, em, rem) en lugar de píxeles absolutos</li>
  <li>Prueba en múltiples dispositivos y tamaños de pantalla</li>
  <li>Considera diferentes densidades de píxeles para imágenes</li>
</ul>

<div class="tip-box">
  <h3>Consejo profesional</h3>
  <p>No reinventes la rueda. Para proyectos reales, considera utilizar frameworks CSS como Bootstrap, Tailwind CSS o Bulma como punto de partida, personalizándolos según tus necesidades. Esto te ahorrará tiempo y te dará una base sólida.</p>
</div>

<h2>Ejercicio final del módulo</h2>
<p>Como proyecto final para este módulo de CSS, crea una página de inicio completa para un negocio ficticio (o real) que incluya:</p>
<ol>
  <li>Un sistema de diseño con variables CSS para colores, tipografía y espaciado</li>
  <li>Header con navegación responsiva (menú hamburguesa en móvil)</li>
  <li>Sección hero con llamado a la acción</li>
  <li>Secciones de características/servicios utilizando Grid o Flexbox</li>
  <li>Sección de testimonios o galería</li>
  <li>Formulario de contacto con estilos personalizados</li>
  <li>Footer con múltiples columnas que se adapten a diferentes tamaños de pantalla</li>
  <li>Animaciones o transiciones sutiles para mejorar la experiencia de usuario</li>
</ol>

<p>¡Felicidades por completar el módulo de CSS! Ahora tienes las habilidades necesarias para crear diseños web profesionales y atractivos.') 
WHERE lesson_id = 20;
