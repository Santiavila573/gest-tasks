

document.addEventListener('DOMContentLoaded', function() {
    
    // Busca todos los contenedores de tareas en el tablero Kanban
    const taskColumns = document.querySelectorAll('.column-tasks');
    
    // Solo ejecuta el código si existen columnas en la página
    if (taskColumns.length > 0) {
        
        // Itera sobre cada columna para hacerla "ordenable"
        taskColumns.forEach(column => {
            new Sortable(column, {
                group: 'kanban', // Permite mover elementos entre listas con el mismo nombre de grupo
                animation: 150,  // Animación suave al mover
                ghostClass: 'task-ghost', // Clase CSS para el "fantasma" de la tarea mientras se arrastra
                
                // Se dispara cuando una tarea se suelta en una nueva columna
                onEnd: function (evt) {
                    const itemEl = evt.item;  // El elemento de la tarea que se movió
                    const toColumn = evt.to;    // La columna de destino

                    // Obtenemos los datos que añadimos en el HTML
                    const taskId = itemEl.dataset.taskId;
                    const newStatus = toColumn.dataset.columnStatus;

                    console.log(`Tarea ID: ${taskId} movida a la columna: ${newStatus}`);

                    // Ahora, enviamos esta información al servidor para actualizar la BD
                    updateTaskStatus(taskId, newStatus);
                }
            });
        });
    }
});

/**
 * Envía una solicitud al servidor para actualizar el estado de una tarea.
 * @param {number} taskId - El ID de la tarea a actualizar.
 * @param {string} newStatus - El nuevo estado de la tarea.
 */
function updateTaskStatus(taskId, newStatus) {
    // Usamos la API Fetch para enviar los datos de forma asíncrona (sin recargar la página)
    fetch('core/update_task_status.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        // Enviamos los datos en el cuerpo de la solicitud
        body: `task_id=${taskId}&new_status=${newStatus}`
    })
    .then(response => response.json()) // Esperamos una respuesta en formato JSON
    .then(data => {
        if (data.success) {
            console.log('¡Estado de la tarea actualizado con éxito!');
            // Opcional: Mostrar una pequeña notificación de éxito
        } else {
            console.error('Error al actualizar la tarea:', data.error);
            // Opcional: Mostrar una notificación de error y quizás revertir el movimiento visualmente
        }
    })
    .catch(error => {
        console.error('Error de red o en el servidor:', error);
    });
}

// En /assets/js/main.js, dentro del evento DOMContentLoaded

document.addEventListener('DOMContentLoaded', function() {
    // ... (código de SortableJS que ya tienes) ...

    // --- Lógica para el formulario de añadir tarea ---
    const showFormBtn = document.getElementById('show-task-form-btn');
    const taskFormContainer = document.getElementById('add-task-form-container');
    const cancelTaskBtn = document.getElementById('cancel-task-btn');

    // Solo ejecuta si los elementos existen en la página
    if (showFormBtn && taskFormContainer && cancelTaskBtn) {
        // Al hacer clic en "Añadir Tarea"
        showFormBtn.addEventListener('click', () => {
            taskFormContainer.style.display = 'block'; // Muestra el formulario
            showFormBtn.style.display = 'none'; // Oculta el botón
        });

        // Al hacer clic en "Cancelar"
        cancelTaskBtn.addEventListener('click', () => {
            taskFormContainer.style.display = 'none'; // Oculta el formulario
            showFormBtn.style.display = 'block'; // Vuelve a mostrar el botón
        });
    }
});

// En /assets/js/main.js, dentro del evento DOMContentLoaded

document.addEventListener('DOMContentLoaded', function() {
    // ... (código anterior) ...

    // --- Lógica del Chatbot ---
    const openChatbotBtn = document.getElementById('open-chatbot-btn');
    const chatbotWindow = document.getElementById('chatbot-window');
    const closeChatbotBtn = document.getElementById('close-chatbot-btn');
    const chatbotInput = document.getElementById('chatbot-input');
    const chatbotSendBtn = document.getElementById('chatbot-send-btn');
    const chatbotMessages = document.getElementById('chatbot-messages');

    if (openChatbotBtn) {
        openChatbotBtn.addEventListener('click', () => {
            chatbotWindow.style.display = 'flex';
            openChatbotBtn.style.display = 'none';
        });

        closeChatbotBtn.addEventListener('click', () => {
            chatbotWindow.style.display = 'none';
            openChatbotBtn.style.display = 'block';
        });

        const sendMessage = () => {
            const message = chatbotInput.value.trim();
            if (message === '') return;

            // Añadir mensaje del usuario a la ventana
            appendMessage(message, 'user');
            chatbotInput.value = '';

            // Enviar mensaje al backend
            fetch('core/chatbot_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                // Añadir respuesta del bot a la ventana
                appendMessage(data.reply, 'bot');
            })
            .catch(error => console.error('Error en el chatbot:', error));
        };

        chatbotSendBtn.addEventListener('click', sendMessage);
        chatbotInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function appendMessage(content, type) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `chat-message ${type}`;
            
            const p = document.createElement('p');
            p.innerHTML = content; // Usamos innerHTML para que renderice las tablas
            
            messageDiv.appendChild(p);
            chatbotMessages.appendChild(messageDiv);
            
            // Hacer scroll hasta el final
            chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
        }
    }
});

// En /assets/js/main.js, dentro del evento DOMContentLoaded

document.addEventListener('DOMContentLoaded', function() {

    // ... (código anterior) ...

    // --- Lógica para el Modal de Crear Proyecto ---
    const modal = document.getElementById('add-project-modal');
    const showModalBtn = document.getElementById('show-project-modal-btn');
    const closeModalBtn = document.getElementById('close-project-modal-btn');

    if (modal && showModalBtn && closeModalBtn) {
        showModalBtn.addEventListener('click', () => {
            modal.style.display = 'block';
        });

        closeModalBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        // Cerrar el modal si se hace clic fuera del contenido
        window.addEventListener('click', (event) => {
            if (event.target == modal) {
                modal.style.display = 'none';
            }

    // --- LLAMADA FINAL PARA LOS RECORDATORIOS PROACTIVOS ---
    checkAndShowReminders();
            
        });
    }
});

/**
 * Comprueba si hay recordatorios pendientes al cargar la página y,
 * si los hay, abre el chatbot y los muestra.
 */
function checkAndShowReminders() {
    // Elementos del DOM que necesitaremos
    const openChatbotBtn = document.getElementById('open-chatbot-btn');
    const chatbotWindow = document.getElementById('chatbot-window');

    // Si no estamos en una página con chatbot, no hacemos nada
    if (!openChatbotBtn || !chatbotWindow) {
        return;
    }

    fetch('core/get_reminders.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.reminders) {
                let reminderMessage = "¡Hola! Tienes algunos elementos importantes que vencen pronto:<ul>";

                // Formatear recordatorios de Sprints
               if (data.reminders.tasks.length > 0) {
    reminderMessage += "<li><strong>Tareas por vencer:</strong><ul>";
    data.reminders.tasks.forEach(task => {
        // --- INICIO DE LA CORRECCIÓN ---
        // Cambiamos 'fecha_estimada' por 'fecha_vencimiento' para que coincida con el PHP
        const fecha = new Date(task.fecha_vencimiento + 'T00:00:00').toLocaleDateString('es-ES', { day: 'numeric', month: 'long' });
        // --- FIN DE LA CORRECCIÓN ---
        reminderMessage += `<li>La tarea "<strong>${task.titulo}</strong>" del proyecto <em>${task.proyecto_nombre}</em> vence el <strong>${fecha}</strong>.</li>`;
    });
    reminderMessage += "</ul></li>";
}

                // Formatear recordatorios de Tareas
                if (data.reminders.tasks.length > 0) {
                    reminderMessage += "<li><strong>Tareas por vencer:</strong><ul>";
                    data.reminders.tasks.forEach(task => {
                        const fecha = new Date(task.fecha_estimada + 'T00:00:00').toLocaleDateString('es-ES', { day: 'numeric', month: 'long' });
                        reminderMessage += `<li>La tarea "<strong>${task.titulo}</strong>" del proyecto <em>${task.proyecto_nombre}</em> vence el <strong>${fecha}</strong>.</li>`;
                    });
                    reminderMessage += "</ul></li>";
                }

                reminderMessage += "</ul>¡Que tengas un día productivo!";

                // Abrir el chatbot y mostrar el mensaje
                chatbotWindow.style.display = 'flex';
                openChatbotBtn.style.display = 'none';
                appendMessage(reminderMessage, 'bot');
            }
        })
        .catch(error => console.error('Error al obtener recordatorios:', error));
}

// --- Lógica para el Filtrado de Sprints ---
const sprintItems = document.querySelectorAll('.sprint-item');
const taskCards = document.querySelectorAll('.task-card');

sprintItems.forEach(sprint => {
    sprint.addEventListener('click', function() {
        // Manejar la clase activa para el feedback visual
        sprintItems.forEach(s => s.classList.remove('active'));
        this.classList.add('active');

        const selectedSprintId = this.dataset.sprintId;

        taskCards.forEach(task => {
            // Si se selecciona "Todos" o si el sprint de la tarea coincide
            if (selectedSprintId === 'all' || task.dataset.sprintId === selectedSprintId) {
                task.style.display = 'block'; // Muestra la tarea
            } else {
                task.style.display = 'none'; // Oculta la tarea
            }
        });
    });
});



