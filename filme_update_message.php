<?php
$message = isset($_GET['message']) ? $_GET['message'] : 'Nenhuma mensagem.';
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atualização do Perfil</title>
    <link rel="stylesheet" href="styles/styles_message.css">
    <style>
        /* Importação de fontes */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap');

        /* Estilos gerais */
        body {
            font-family: 'Poppins', sans-serif;
            color: #ffffff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: black;
            overflow: hidden;
        }

        #background-video {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            z-index: -1;
            object-fit: cover;
            opacity: 0.7;
        }

        .message-container {
            position: relative;
            z-index: 1;
            background-color: rgba(20, 20, 20, 0.9);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            text-align: center;
            color: #ffffff;
            max-width: 380px;
            width: 80%;
            transition: transform 0.3s ease;
        }

        .message-container h1 {
            margin: 0;
            font-size: 24px;
            color: #ffffff;
            /* Cor do título ajustada */
        }

        .message-container p {
            font-size: 16px;
            margin: 20px 0;
            color: #e0e0e0;
            /* Cor do texto ajustada para contraste */
        }

        .message-container a {
            display: inline-block;
            padding: 10px 20px;
            font-size: 16px;
            font-weight: bold;
            color: #ffffff;
            background-color: #13bfe1;
            /* Azul claro */
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s;
        }

        .message-container a:hover {
            background-color: #094a5c;
            /* Azul escuro */
        }
    </style>
</head>

<body>
    <video autoplay muted loop playsinline id="background-video">
        <source src="img/dna_background.mp4" type="video/mp4">
        Seu navegador não suporta a reprodução de vídeo.
    </video>
    <div class="message-container">
        <h1>Atualização do Perfil</h1>
        <p><?php echo htmlspecialchars($message); ?></p>
        <a href="dashboard_filmes.php">Voltar!</a>
    </div>
</body>

</html>