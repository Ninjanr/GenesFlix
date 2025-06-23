<?php
session_start();
if (isset($_SESSION["user"])) {
    header("Location: Home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - GenesFlix</title>
    <link rel="stylesheet" href="styles/styles_registro.css">

</head>

<body>
    <video autoplay muted loop playsinline id="background-video">
    <source src="img/dna_background.mp4" type="video/mp4">
        Seu navegador não suporta a reprodução de vídeo.
    </video>
    <div class="registration-container">
        <div class="registration-form">
            <div class="icon-container">
                <img src="img/logo.png" alt="Logo" class="logo">
            </div>
            <p>
            <?php
            if (isset($_POST["submit"])) {
                $fullName = $_POST["fullname"];
                $email = strtolower($_POST["email"]);
                $password = $_POST["password"];
                $passwordRepeat = $_POST["repeat_password"];
                $selectedAvatar = $_POST["selected_avatar"];

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $errors = array();

                if (empty($fullName) || empty($email) || empty($password) || empty($passwordRepeat) || empty($selectedAvatar)) {
                    array_push($errors, "Todos os campos são obrigatórios");
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    array_push($errors, "O e-mail não é válido");
                }
                if (strlen($password) < 8) {
                    array_push($errors, "A senha deve ter pelo menos 8 caracteres");
                }
                if ($password !== $passwordRepeat) {
                    array_push($errors, "As senhas não coincidem");
                }

                require_once "conexao.php";
                $sql = "SELECT * FROM users WHERE email = ?";
                $stmt = mysqli_stmt_init($conn);
                if (mysqli_stmt_prepare($stmt, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $email);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if (mysqli_num_rows($result) > 0) {
                        array_push($errors, "O e-mail já está em uso!");
                    }
                }

                if (count($errors) > 0) {
                    foreach ($errors as $error) {
                        echo "<div class='alert alert-danger'>$error</div>";
                    }
                } else {
                    $profilePicture = $selectedAvatar;

                    $sql = "INSERT INTO users (full_name, email, password, profile_picture, data_cadastro) VALUES (?, ?, ?, ?, now())";
                    if (mysqli_stmt_prepare($stmt, $sql)) {
                        mysqli_stmt_bind_param($stmt, "ssss", $fullName, $email, $passwordHash, $profilePicture);
                        mysqli_stmt_execute($stmt);

                        // Autenticar o usuário após o cadastro
                        $_SESSION["user"] = mysqli_insert_id($conn); // Armazenar o ID do usuário na sessão
                        header("Location: Home.php"); // Redirecionar para a página home
                        exit();
                    } else {
                        die("Algo deu errado");
                    }
                }
            }
            ?>
            <h2>REGISTRAR-SE:</h2>
            <form action="registro.php" method="post">
                <div class="form-group">
                    <input type="text" class="form-control" name="fullname" placeholder="Nome Completo" required>
                </div>
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="E-mail" required>
                </div>
                <div class="form-group password-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required>
                    <span class="eye-icon" onclick="togglePassword()">
                        <img src="overlay/eye-icon.png" alt="Mostrar Senha" id="eye-icon" style="width: 20px;">
                    </span>
                </div>
                <div class="form-group password-container">
                    <input type="password" class="form-control" id="repeat_password" name="repeat_password" placeholder="Repita a Senha" required>
                    <span class="eye-icon" onclick="toggleRepeatPassword()">
                        <img src="overlay/eye-icon.png" alt="Mostrar Senha" id="repeat-eye-icon" style="width: 20px;">
                    </span>
                </div>
                <!-- Botão para escolher o avatar -->
                <button type="button" class="choose-avatar-btn" onclick="openAvatarModal()">Escolher Avatar</button>
                <input type="hidden" id="selected-avatar" name="selected_avatar" required>
                <br><br><br>
                <div class="form-btn">
                    <input type="submit" class="btn btn-primary" value="Cadastrar" name="submit" id="confirm-btn" disabled>
                </div>
            </form>
            <div class="login-link">
                <p>Já possui uma conta? <a href="login.php">Faça login aqui</a></p>
            </div>
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
        avatarPaths.forEach(path => {
            const img = document.createElement('img');
            img.src = path;
            img.alt = 'Avatar';
            img.onclick = () => selectAvatar(path, img);
            avatarGrid.appendChild(img);
        });

        function openAvatarModal() {
            document.getElementById('avatar-modal').classList.add('show');
        }

        function closeAvatarModal() {
            document.getElementById('avatar-modal').classList.remove('show');
        }

        function selectAvatar(path, imgElement) {
            const selectedAvatarInput = document.getElementById('selected-avatar');
            const confirmBtn = document.getElementById('confirm-btn');
            selectedAvatarInput.value = path;

            document.querySelectorAll('.avatar-grid img').forEach(img => img.classList.remove('selected'));
            imgElement.classList.add('selected');

            // Habilitar o botão de cadastro após selecionar o avatar
            confirmBtn.disabled = false;
        }

        function togglePassword() {
            const passwordField = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.src = 'overlay/eye-icon-open.png'; // Substitua pelo ícone do olho aberto
            } else {
                passwordField.type = 'password';
                eyeIcon.src = 'overlay/eye-icon.png'; // Substitua pelo ícone do olho fechado
            }
        }

        function toggleRepeatPassword() {
            const repeatPasswordField = document.getElementById('repeat_password');
            const repeatEyeIcon = document.getElementById('repeat-eye-icon');
            if (repeatPasswordField.type === 'password') {
                repeatPasswordField.type = 'text';
                repeatEyeIcon.src = 'overlay/eye-icon-open.png'; // Substitua pelo ícone do olho aberto
            } else {
                repeatPasswordField.type = 'password';
                repeatEyeIcon.src = 'overlay/eye-icon.png'; // Substitua pelo ícone do olho fechado
            }
        }
    </script>
</body>

</html>