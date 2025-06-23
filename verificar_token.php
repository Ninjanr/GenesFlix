<?php
session_start();
require_once "conexao.php";

$remaining_time = 0; // Inicializar tempo restante como 0
$error_message = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = trim($_POST["token"]);

    // Verificar se o token é válido e ainda não expirou
    $sql = "SELECT * FROM users WHERE reset_token = ? AND token_expiration > NOW()";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $token);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

        if ($user) {
            // Token válido: calcular tempo restante
            $token_expiration = $user['token_expiration'];
            $remaining_time = strtotime($token_expiration) - time();

            if ($remaining_time > 0) {
                // Salvar ID do usuário na sessão
                $_SESSION["reset_user_id"] = $user["id"];

                // Adicionar script para redirecionamento com atraso
                echo "<script>
                        setTimeout(() => {
                            window.location.href = 'alterar_senha.php?token=$token';
                        }, 1000); // 1 segundos de atraso
                      </script>";
            }
        } else {
            // Token inválido ou expirado
            $error_message = "Token inválido ou expirado.";
        }
        mysqli_stmt_close($stmt);
    }
} else {
    // Tentar obter o próximo token de expiração mais próximo para usar como referência
    $sql = "SELECT token_expiration FROM users WHERE token_expiration > NOW() ORDER BY token_expiration ASC LIMIT 1";
    $result = mysqli_query($conn, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $remaining_time = strtotime($row['token_expiration']) - time();
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/token.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Verificar Token</title>
</head>
<body>
<button class="back-button" onclick="goBack()">
        <i class="material-icons">arrow_back</i>
    </button>
<div class="container">
    <div class="header">
        <img src="https://cdn-icons-png.flaticon.com/512/9252/9252943.png" alt="Logo" class="logo">
        <h2>Verificar Token</h2>
        <p>Digite o token recebido por e-mail para verificar sua identidade.</p>
        <br>
        <!-- Exibir contagem regressiva -->
    <div id="countdown" data-time="<?php echo max($remaining_time, 0); ?>"></div>
    </div>
    <form action="verificar_token.php" method="post">
        <div class="form-group">
            <label for="token">Token</label>
            <input type="text" id="token" name="token" placeholder="Digite o token aqui" required>
        </div>
        <button type="submit" class="btn">Verificar</button>
    </form>
    <div class="footer">
        <p>Voltar para o <a href="login.php">login</a>.</p>
    </div>

    <!-- Exibir mensagem de erro se o token for inválido -->
    <?php
    if (isset($error_message)) {
        echo "<div style='color: red;'>$error_message</div>";
    }
    ?>
</div>

<script>
    function goBack() {
            window.history.back(); // Navega de volta na história do navegador
        }
    // JavaScript para contagem regressiva
    const countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        let remainingTime = parseInt(countdownElement.getAttribute('data-time'));

        function updateCountdown() {
            if (remainingTime <= 0) {
                countdownElement.textContent = "O token expirou.";
                countdownElement.classList.add('expired');
                clearInterval(interval);
                return;
            }

            // Converter segundos restantes para minutos e segundos
            const seconds = remainingTime % 60;
            countdownElement.textContent = `Tempo acabando... ${seconds}s`;

            remainingTime -= 1;
        }

        // Atualizar a cada segundo
        const interval = setInterval(updateCountdown, 1000);
        updateCountdown(); // Atualizar imediatamente
    }
</script>
</body>
</html>
