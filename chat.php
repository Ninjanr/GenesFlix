<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user'];

// Buscar dados do usuário logado
$sql_user = "SELECT full_name, email, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();

if ($user['role'] !== 'admin' && $user['role'] !== 'user') {
    echo "Acesso negado.";
    exit();
}

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Chat com Admin</title>
    <style>
        .chat-container {
            width: 400px;
            margin: auto;
            border: 1px solid #ccc;
        }

        .chat-box {
            height: 400px;
            overflow-y: auto;
            padding: 10px;
            background-color: #f9f9f9;
        }

        .user-message {
            background-color: #d1f7d1;
            padding: 8px;
            margin: 5px 0;
            text-align: left;
            border-radius: 8px;
            max-width: 70%;
            margin-left: auto;
        }

        .admin-message {
            background-color: #f7d1d1;
            padding: 8px;
            margin: 5px 0;
            text-align: left;
            border-radius: 8px;
            max-width: 70%;
            margin-right: auto;
        }

        #chat-form {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ccc;
        }

        #message-input {
            flex: 1;
            padding: 10px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        button {
            padding: 10px;
            font-size: 16px;
        }

        /* Estilo de animação */
        .highlight {
            color: #00f; /* cor azul para o @ */
            font-weight: bold;
            animation: highlightAnimation 1s ease-out forwards;
        }

        @keyframes highlightAnimation {
            0% {
                background-color: yellow;
                color: #ff0000;
            }
            100% {
                background-color: transparent;
                color: #00f;
            }
        }
    </style>
</head>

<body>
    <div class="chat-container">
        <div class="chat-box" id="chat-box">
            <!-- Mensagens serão carregadas aqui -->
        </div>
        <form id="chat-form">
            <input type="text" id="message-input" placeholder="Digite sua mensagem" autocomplete="off">
            <button type="submit">Enviar</button>
        </form>
    </div>

    <script>
        const userId = <?= $user_id; ?>;
        let currentReceiver = localStorage.getItem('currentReceiver') || 'admin'; // Armazenando o último destinatário

        // Função para carregar as mensagens
        function loadMessages() {
            fetch('load_messages.php')
                .then(response => response.json())
                .then(data => {
                    const chatBox = document.getElementById('chat-box');
                    chatBox.innerHTML = ''; // Limpa as mensagens anteriores
                    data.forEach(message => {
                        const messageElement = document.createElement('div');
                        if (message.sender_id == userId) {
                            // Mensagem do usuário
                            messageElement.classList.add('user-message');
                            messageElement.innerHTML = `<strong>Você:</strong> ${message.message}`;
                        } else {
                            // Mensagem do admin
                            messageElement.classList.add('admin-message');
                            messageElement.innerHTML = `<strong>Admin:</strong> ${message.message}`;
                        }
                        chatBox.appendChild(messageElement);
                    });
                    chatBox.scrollTop = chatBox.scrollHeight;
                });
        }

        // Função para enviar mensagem
        document.getElementById('chat-form').addEventListener('submit', function (event) {
            event.preventDefault();
            let message = document.getElementById('message-input').value;

            // Identificar o destinatário pela @
            if (message.includes('@')) {
                const atIndex = message.indexOf('@');
                const recipient = message.substring(atIndex + 1).split(' ')[0].trim(); // Pega o nome depois do "@"
                
                // Se já estabeleceu uma conexão com esse destinatário, salva para futuras mensagens
                if (recipient) {
                    localStorage.setItem('currentReceiver', recipient);
                    currentReceiver = recipient;
                }
            }

            // Enviar a mensagem para o destinatário atual (pode ser admin ou outro)
            fetch('send_message.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    message: message,
                    sender_id: userId
                })
            }).then(() => {
                document.getElementById('message-input').value = '';
                loadMessages();
            });
        });

        // Animação no campo de entrada
        const messageInput = document.getElementById('message-input');
        messageInput.addEventListener('input', function () {
            let inputText = messageInput.value;
            if (inputText.includes('@')) {
                const atIndex = inputText.indexOf('@');
                const recipient = inputText.substring(atIndex + 1).split(' ')[0]; // Destinatário após o "@"
                const recipientElement = inputText.replace(`@${recipient}`, `<span class="highlight">@${recipient}</span>`);
                messageInput.innerHTML = recipientElement; // Substituir no campo de entrada
            }
        });

        // Carrega mensagens a cada 2 segundos
        setInterval(loadMessages, 2000);
    </script>
</body>

</html>
