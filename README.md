# TFG_VPL (report_vpl_analytics)

Este repositorio contiene el código de un plugin de reportes para Moodle, desarrollado como parte de un Trabajo Fin de Grado (TFG). Su objetivo principal es añadir un panel de analíticas de aprendizaje (Learning Analytics) exclusivo para las tareas de programación hechas con el módulo VPL (Virtual Programming Lab).

Este proyecto busca ayudar a los profesores a ver de un vistazo cómo avanza el grupo, detectar si hay alumnos atascados con el código o si están dejando las prácticas para el último minuto.

## Qué incluye el dashboard

El panel calcula en tiempo real varias estadísticas útiles sin llegar a sobrecargar la base de datos:
*   **KPIs básicos:** Nota media del curso, tasa de aprobados y cantidad de alumnos inactivos.
*   **Gráficas interactivas:** A través de Chart.js, permite visualizar:
    *   La distribución general de las notas.
    *   Una línea de tiempo con la evolución de las entregas.
    *   El desempeño práctico de los alumnos (comparando las ejecuciones de prueba vs. las evaluaciones finales).
    *   Un mapa de calor (Heatmap) para comprobar en qué días de la semana y a qué horas se trabaja más.
*   **Detección de problemas:** El sistema cruza datos para asignar "insignias" visuales a los alumnos que podrían estar procrastinando o que se encuentran estancados en una práctica.
*   **Filtros de vista:** Se puede analizar a toda la clase de golpe, comparar grupos concretos de Moodle, o ver el detalle individual de un estudiante en una matriz de actividad (estudiante vs. actividad VPL).

## Requisitos para usarlo

*   Moodle 3.9 o superior.
*   El módulo VPL (mod_vpl) instalado previamente en el servidor.
*   **Aviso técnico sobre PHP:** El plugin funciona hasta Moodle 5.1 usando PHP 8.2. Moodle 5.2 requiere PHP 8.3, lo cual excede los límites de las distribuciones estándar de XAMPP utilizadas en el desarrollo de este TFG.

## Notas sobre la arquitectura y el código

A nivel técnico, se han respetado las convenciones de desarrollo clásicas de Moodle:
*   Se aplica una separación clara similar a MVC. El archivo `index.php` actúa como controlador inicial y chequea los permisos de seguridad (`report/vpl_analytics:view`).
*   Toda la extracción masiva de datos recae sobre la clase `data_manager.php`, que ataca directamente a la API de la base de datos (`$DB`) y empaqueta el resultado en un único objeto JSON.
*   El frontend (`js/dashboard.js`) toma ese JSON en crudo y dibuja toda la vista. Con esto se evita saturar el servidor a peticiones si el profesor filtra datos o cambia de gráfica continuamente.
*   Para prevenir problemas de seguridad (XSS), todo el texto y HTML generado dinámicamente en Javascript pasa por una función de limpieza antes de inyectarse en el DOM.

## Librerías utilizadas

Todo el código de terceros está aislado en la carpeta `vendor`:
*   Chart.js (y sus plugins de Zoom y Date-fns)
*   Hammer.js
