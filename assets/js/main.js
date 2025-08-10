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
        // Al hacer clic en el botón de abrir el chatbot
        openChatbotBtn.addEventListener('click', function(event) {
            event.preventDefault(); // Para evitar que el enlace "a" se siga
            chatbotWindow.style.display = 'flex';
            openChatbotBtn.style.display = 'none';
        });

        closeChatbotBtn.addEventListener('click', () => {
            chatbotWindow.style.display = 'none';
            openChatbotBtn.style.display = 'block';
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                chatbotWindow.style.display = 'none';
                openChatbotBtn.style.display = 'block';
            }
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

