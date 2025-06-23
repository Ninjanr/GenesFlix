<?php
session_start();
require 'conexao.php'; // Arquivo que faz a conexão com o banco

// Verifique se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Pegue o ID do usuário logado
$user_id = $_SESSION['user'];

// Busque os dados do usuário no banco de dados
$sql = "SELECT full_name, email, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Se os dados forem encontrados, armazene-os em uma variável
    $user = $result->fetch_assoc();
} else {
    echo "Erro: Usuário não encontrado!";
    exit();
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Usuário</title>
    <link rel="stylesheet" href="styles/styles_Perfil.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>

<body>
<button class="back-button" onclick="goBack()">
        <i class="material-icons">arrow_back</i>
    </button>
    <video autoplay muted loop playsinline id="background-video">
        <source src="img/dna_background.mp4" type="video/mp4">
        Seu navegador não suporta a reprodução de vídeo.
    </video>

    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-picture-container">
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Foto de Perfil" class="profile-picture">
                <i class="material-icons edit-avatar-icon" onclick="openAvatarModal()" title="Editar Avatar">edit</i>
                <!-- Campo oculto para armazenar o valor do avatar -->
                <input type="hidden" id="profile_picture" name="profile_picture" value="<?php echo htmlspecialchars($user['profile_picture']); ?>" required>
            </div>
            <h1><?php echo htmlspecialchars($user['full_name']); ?></h1>
        </div>

        <div class="profile-details">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        </div>
        <button id="edit-profile-btn">Editar Perfil</button>
        <br><br>
        <div id="edit-form" class="hidden">
            <form action="update_profile.php" method="POST">
                <label for="name">Nome:</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>">

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>">

                <!-- Campo oculto para o avatar selecionado -->
                <input type="hidden" id="profile_picture_form" name="profile_picture" value="<?php echo htmlspecialchars($user['profile_picture']); ?>">

                <br><br>
                <button type="submit">Salvar</button>
            </form>
            <br>
            <form action="delete_profile.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir seus dados?');">
                <button type="submit">Excluir Dados</button>
            </form>
        </div>
    </div>

    <!-- Modal de seleção de avatar -->
    <div class="avatar-modal" id="avatar-modal">
        <br><br>
        <h3>Escolha seu avatar:</h3>
        <div class="avatar-grid" id="avatar-grid"></div>
        <button class="close-modal-btn" onclick="closeAvatarModal()">OK</button>
        <br><br>
    </div>

    <script>
        const avatarPaths = [
            'img/avatares/coringa.png',
            'img/avatares/deadpool.webp',
            'img/avatares/demolidor.webp',
            'img/avatares/groot.png',
            'img/avatares/homem_aranha.webp',
            'img/avatares/homem_ferro.webp',
            'img/avatares/hulk.png',
            'img/avatares/laterna.webp',
            'img/avatares/mulher_maravilha.webp',
            'img/avatares/nickfury.webp',
            'img/avatares/wolverine.webp',
            'img/avatares/thor.webp',
            'img/avatares/batman.webp',
            'img/avatares/batmulher.webp',
            'img/avatares/capitao_america.webp',
        ];

        const avatarGrid = document.getElementById('avatar-grid');
        const profilePictureInput = document.getElementById('profile_picture_form'); // Atualizei para garantir que o valor seja enviado

        // Cria os avatares na grid
        avatarPaths.forEach(path => {
            const img = document.createElement('img');
            img.src = path;
            img.alt = 'Avatar';
            img.onclick = () => selectAvatar(path, img);
            avatarGrid.appendChild(img);
        });

        // Abre o modal de seleção de avatar
        function openAvatarModal() {
            document.getElementById('avatar-modal').classList.add('show');
        }

        // Fecha o modal de seleção de avatar e atualiza a imagem do perfil
        function closeAvatarModal() {
            document.getElementById('avatar-modal').classList.remove('show');

            // Atualiza a imagem do perfil com o avatar selecionado
            const selectedAvatar = profilePictureInput.value; // Pega o valor do avatar selecionado
            document.querySelector('.profile-picture').src = selectedAvatar; // Atualiza a imagem do perfil
        }
        function goBack() {
            window.history.back(); // Navega de volta na história do navegador
        }
        // Seleciona um avatar e atualiza a imagem do perfil imediatamente
        function selectAvatar(path, imgElement) {
            profilePictureInput.value = path; // Atualiza o valor do input oculto com o caminho do avatar selecionado
            console.log("Avatar selecionado:", path); // Exibe o caminho do avatar no console para debug

            // Atualiza a imagem do perfil com o avatar selecionado
            document.querySelector('.profile-picture').src = path; // Atualiza a imagem do perfil

            document.querySelectorAll('.avatar-grid img').forEach(img => img.classList.remove('selected'));
            imgElement.classList.add('selected');
        }

        // Toggle do formulário de edição de perfil
        document.getElementById('edit-profile-btn').addEventListener('click', function() {
            var form = document.getElementById('edit-form');
            form.classList.toggle('hidden');
        });
    </script>
</body>

</html>