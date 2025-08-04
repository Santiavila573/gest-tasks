<!-- En /includes/footer.php -->

<?php
// Solo mostramos el chatbot si el usuario ha iniciado sesión
if (isset($_SESSION['user_id'])):
?>
<!-- Contenedor del Chatbot -->
<div id="chatbot-container">
    <!-- Ventana del Chat (inicialmente oculta) -->
    <div id="chatbot-window" style="display: none;">
        <div id="chatbot-header">
            <h5>Asistente Scrum</h5>
            <button id="close-chatbot-btn">&times;</button>
        </div>
        <div id="chatbot-messages">
            <!-- Mensaje de bienvenida -->
            <div class="chat-message bot">
                <p>¡Hola! Soy tu asistente. ¿En qué puedo ayudarte? Prueba a escribir "proyectos" o "tareas del proyecto 1".</p>
            </div>
        </div>
        <div id="chatbot-input-container">
            <input type="text" id="chatbot-input" placeholder="Escribe tu consulta...">
            <button id="chatbot-send-btn"><i class="fas fa-paper-plane"></i></button>
        </div>
    </div>
    <!-- Botón Flotante para abrir el chat con efecto de levitación -->
    <button id="open-chatbot-btn" style="animation: float 2s ease-in-out infinite; animation-direction: alternate;">
        <i class="fas fa-robot"></i>
    </button>
</div>
<?php endif; ?>

<!-- Enlazamos nuestro archivo JavaScript -->
<script src="assets/js/main.js"></script>
</body>
</html>
