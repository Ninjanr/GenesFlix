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
    <title>Login - GenesFlix</title>
    <link rel="stylesheet" href="styles/styles_login.css">
</head>

<body>
    <video autoplay muted loop playsinline id="background-video">
        <source src="img/dna_background.mp4" type="video/mp4">
        Seu navegador não suporta a reprodução de vídeo.
    </video>
    <div class="login-container">
        <div class="login-form">
            <div class="icon-container">
                <img src="img/logo.png" alt="Logo" class="logo">
            </div>
            <?php
            if (isset($_POST["login"])) {
                $email = trim($_POST["email"]); // Remove espaços extras
                $password = $_POST["password"];
                require_once "conexao.php";

                // Prepare a consulta
                $sql = "SELECT * FROM users WHERE email = ?";
                $stmt = mysqli_stmt_init($conn);

                if (mysqli_stmt_prepare($stmt, $sql)) {
                    mysqli_stmt_bind_param($stmt, "s", $email);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

                    // Depuração
                    echo "<div class='alert alert-info'>E-mail usado: $email</div>";

                    if ($user) {
                        // Verifique a senha
                        if (password_verify($password, $user["password"])) {
                            $_SESSION["user"] = $user["id"]; // Armazena o ID do usuário na sessão
                            header("Location: Home.php");
                            exit();
                        } else {
                            echo "<div class='alert alert-danger'>A senha não coincide</div>";
                        }
                    } else {
                        echo "<div class='alert alert-danger'>O e-mail não coincide</div>";
                    }
                } else {
                    echo "<div class='alert alert-danger'>Erro na consulta ao banco de dados.</div>";
                }
                mysqli_stmt_close($stmt);
                mysqli_close($conn);
            }
            ?>
            <h2>LOGIN:</h2>
            <form action="login.php" method="post">
                <div class="form-group">
                    <input type="email" class="form-control" name="email" placeholder="E-mail" required>
                </div>
                <div class="form-group password-container">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Senha" required>
                    <span class="eye-icon" onclick="togglePassword()">
                        <img src="overlay/eye-icon.png" alt="Mostrar Senha" id="eye-icon" style="width: 20px;">
                    </span>
                </div>
                <div class="form-btn">
                    <input type="submit" class="btn btn-primary" value="Entrar" name="login">
                </div>
            </form>
            <div class="register-link">
                <p>Ainda não tem uma conta? <a href="registro.php">Cadastre-se aqui</a></p>
            </div>
            <div class="reset-password-link">
                <p>Esqueceu sua senha? <a href="recuperar_senha.php">Recupere aqui</a></p>
            </div>
        </div>
    </div>
    <script>
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
    </script>
</body>

</html>