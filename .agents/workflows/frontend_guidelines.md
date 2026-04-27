---
description: pautas de diseño UI/UX, modo oscuro y vidrio (glassmorphism) al escribir vistas frontend
---

# SDGBP - Pautas de Diseño y Frontend Responsive

Cuando se solicite actualizar, modificar o crear nuevas interfaces de usuario en el sistema SDGBP, el agente debe seguir obligatoriamente estas reglas para garantizar una Experiencia de Usuario (UX) Premium que sume valor al proyecto:

## 1. Diseño Premium y "Glassmorphism"
- **Uso Crítico de Sombras:** Utiliza transparencias elegantes (`rgba(255,255,255,0.9)`) sobre fondos con texturas sutiles, junto a desenfoque (`backdrop-filter: blur(10px)`). 
- Evita bordes rígidos y esquinas agudas. Todos los elementos modales, `div`s principales o tarjetas deben tener esquinas redondeadas (ej. `border-radius: 12px` a `15px`). 
- **Transiciones y Micro-Interacciones:** Agrega interactividad suave en botones y *hover effects* (`transition: 0.2s` o `0.3s`, con pequeños `transform: scale(1.05)` o sutiles cambios en el `box-shadow`).

## 2. Responsividad Absoluta
- Bajo ninguna circunstancia un modal, ventana emergente (SweetAlert o similar) o área de chat debe salirse de la pantalla física u obligar a un desplazamiento (scroll) horizontal excesivo.
- Para imágenes pre-cargadas o modales de visor de imágenes: NUNCA utilices herramientas por defecto sin constreñir las alturas. Obliga contenedores a respetar `max-width: 90vw` y `max-height: 85vh` junto con `object-fit: contain`.  
- **Consultas de Rango (Media Queries):** Usa siempre un diseño adaptable (*Mobile First* o ajustes específicos en pantallas `<= 768px`) para garantizar que la App se sienta como una aplicación nativa. 

## 3. Modales Personalizados en SweetAlert
- Si SweetAlert produce un cuadro blanco horrendo o no compagina con imágenes transparentes, reemplaza su renderizado interno insertando plantillas literales de HTML puro (`html: \` <div...>...</div>\``) e ignorando su `padding` normal para renderizados a pantalla completa.
- Incorpora siempre Botones de Cierre flotantes nativos, utilizando posicionamiento absoluto (`top: -15px`, `right: -15px`), íconos estilizados de FontAwesome y la acción `Swal.close()`.

## 4. Funcionalidades Inmiscibles y Eventos Silenciosos (Click-Away)
- Menús desplegables (Respuestas rápidas, Emojis, Selectores) deben tener lógica incorporada (`document.addEventListener('click', ...)`) que escuche clicks de escape fuera de su burbuja para esconderse automáticamente (*Click-Away Modals*).
- Textareas (`<textarea>`) orientados a chats o ingresos de texto de gran capacidad deben expandirse orgánicamente (script automanejable al `oninput` sumando `scrollHeight`) reemplazando campos `input` tradicionales estáticos.

## 5. Compatibilidad Modo Oscuro
- Aunque el CSS esté construido en tonos cálidos o blancos, siempre define colores semitransparentes en el fondo (`rgba()`) para modales oscureciendo la página detrás (por ejemplo, con un `backdrop: rgba(15, 23, 42, 0.9)`), lo cual da contraste y alivia la tensión visual para imágenes grandes.
- Botoneras, flechas e íconos (`color:#1e293b`) deben alternarse correctamente o mantenerse neutrales (`#fff`) dependiendo la envergadura del fondo contrastante.
