# GESTORTASKS IA

<img width="1584" height="769" alt="image" src="https://github.com/user-attachments/assets/00fc3ac9-3b15-487e-9f0c-308a77702174" />
<img width="1579" height="759" alt="image" src="https://github.com/user-attachments/assets/9290fd1f-8aa3-4745-9486-1aa507c50cb5" />




Un gestor de proyectos web completo y dinámico, diseñado bajo la metodología Scrum. La aplicación permite a los equipos gestionar proyectos, sprints y tareas a través de una interfaz intuitiva y un tablero Kanban interactivo. Su característica principal es un chatbot con IA que actúa como un asistente proactivo, facilitando el acceso a la información.

---

## ✨ Funcionalidades Principales

Este proyecto ha sido desarrollado desde cero, implementando un conjunto de características profesionales para una gestión de proyectos ágil y eficiente:

*   **Autenticación y Roles de Usuario:**
    *   Sistema de registro y login seguro con contraseñas hasheadas.
    *   **Roles de Usuario (Scrum Master, Developer):** La interfaz y los permisos se adaptan al rol del usuario. Los Scrum Masters tienen una vista global y de gestión, mientras que los Developers tienen una vista enfocada en sus tareas.

*   **Gestión de Proyectos y Sprints:**
    *   Creación de proyectos con fechas de inicio y fin.
    *   Gestión de miembros del equipo por proyecto.


*   **Tablero Kanban Interactivo:**
    *   Visualización de tareas en columnas: "Por hacer", "En progreso", "Bloqueada" y "Hecha".
    *   **Funcionalidad Drag-and-Drop:** Mueve tareas entre columnas para actualizar su estado de forma intuitiva y en tiempo real (sin recargar la página).
    *   **Creación de Tareas en Modales:** Añade nuevas tareas y asígnalas a miembros del equipo a través de ventanas modales para una experiencia de usuario fluida.
    *   **Filtrado por Sprints:** Filtra el tablero para visualizar únicamente las tareas de un sprint específico.

*   **Chatbot con IA Proactivo:**
    *   **Procesamiento de Lenguaje Natural (Básico):** Entiende múltiples consultas y corrige errores tipográficos comunes.
    *   **Mantenimiento de Contexto:** Recuerda el último proyecto consultado para responder a preguntas subsecuentes.
    *   **Consultas Múltiples:** Responde a preguntas sobre proyectos, sprints, tareas asignadas y conteo de tareas.

*   **Diseño Profesional y Responsivo:**
    *   Interfaz moderna, limpia y consistente en todas las vistas.
    *   Uso de una paleta de colores y tipografía profesional para una mejor legibilidad.
    *   Diseño completamente responsivo que se adapta a dispositivos de escritorio, tablets y móviles.

---

## 🚀 Tecnologías Utilizadas

*   **Frontend:**
    *   HTML5
    *   CSS3 (con Flexbox y Grid para el layout)
    *   JavaScript (ES6+)
    *   [SortableJS](https://github.com/SortableJS/Sortable ) para la funcionalidad Drag-and-Drop.
    *   [Font Awesome](https://fontawesome.com/ ) para los iconos.
    *   [Google Fonts](https://fonts.google.com/ ) (tipografía 'Inter').

*   **Backend:**
    *   PHP 8.0
    *   MySQL (gestionado a través de XAMPP)

*   **Base de Datos:**
    *   MYSQL

---

## 🛠️ Instalación y Puesta en Marcha Local

Para ejecutar este proyecto en tu propio entorno de desarrollo, sigue estos pasos:

1.  **Clonar el Repositorio (o descargar el ZIP):**
    ```bash
    git clone https://github.com/tu-usuario/tu-repositorio.git
    ```

2.  **Configurar el Entorno XAMPP:**
    *   Asegúrate de tener [XAMPP](https://www.apachefriends.org/index.html ) instalado y los servicios de Apache y MySQL en funcionamiento.
    *   Mueve la carpeta del proyecto clonado al directorio `htdocs` de tu instalación de XAMPP (ej. `C:\xampp\htdocs\`).

3.  **Crear la Base de Datos:**
    *   Abre phpMyAdmin (`http://localhost/phpmyadmin` ).
    *   Crea una nueva base de datos con el nombre `gestscrum`.
    *   Selecciona la base de datos recién creada y ve a la pestaña "Importar".
    *   Sube y ejecuta el archivo `database.sql` (o el nombre que le hayas dado a tu archivo de volcado de BD) que se encuentra en la raíz del proyecto.

4.  **Configurar la Conexión:**
    *   Abre el archivo `includes/db_connect.php`.
    *   Verifica que las credenciales de conexión a la base de datos (`DB_SERVER`, `DB_USERNAME`, `DB_PASSWORD`, `DB_DATABASE`) coincidan con tu configuración local. Por defecto, en XAMPP suelen ser correctas.

5.  **¡Listo para Usar!**
    *   Abre tu navegador y ve a `http://localhost/nombre-de-la-carpeta-del-proyecto/`.

---

## 👤 Credenciales de Prueba

Puedes usar los siguientes perfiles para probar los diferentes roles y funcionalidades:

*   **Rol: Scrum Master**
    *   **Correo:** `scrum@example.com`
    *   **Contraseña:** `password123`
    *   *Permisos: Puede ver todos los proyectos, crear nuevos proyectos y gestionar miembros del equipo.*

*   **Rol: Developer**
    *   **Correo:** `dev@example.com`
    *   **Contraseña:** `password123`
    *   *Permisos: Solo puede ver los proyectos a los que ha sido asignado. Puede añadir tareas y moverlas en el tablero.*

---

## 📄 Licencia

Este proyecto es para fines educativos como proyecto integrador y de demostración. Siéntete libre de usarlo y modificarlo.

