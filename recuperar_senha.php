<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require './vendor/autoload.php';
require_once "conexao.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);

    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_stmt_init($conn);

    if (mysqli_stmt_prepare($stmt, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_array($result, MYSQLI_ASSOC);

        if ($user) {
            // Gera um token de 6 dígitos
            $token = random_int(100000, 999999);
            $expiration = date("Y-m-d H:i:s", strtotime("+45 seconds"));

            $sql_update = "UPDATE users SET reset_token = ?, token_expiration = ? WHERE email = ?";
            $stmt_update = mysqli_stmt_init($conn);

            if (mysqli_stmt_prepare($stmt_update, $sql_update)) {
                mysqli_stmt_bind_param($stmt_update, "sss", $token, $expiration, $email);
                mysqli_stmt_execute($stmt_update);

                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'genesflix.suporte@gmail.com';
                    $mail->Password = 'xkub ariy ssvy plqd';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('genesflix.suporte@gmail.com', 'GenesFlix Suporte');
                    $mail->addAddress($email);
                    $mail->Subject = 'Token de Senha - GenesFlix';

                    // Corpo do e-mail em HTML
                    $mail->isHTML(true);
                    $emailBody = file_get_contents('email_template.html');

                    // Substituir o marcador {TOKEN} pelo valor real do token
                    $emailBody = str_replace('{TOKEN}', $token, $emailBody);

                    // Configurar o e-mail
                    $mail->Body = $emailBody;

                    $mail->send();

                    // Após envio do e-mail, redireciona para a tela de verificação do token
                    header("Location: verificar_token.php");
                    exit();
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>Erro ao enviar e-mail: {$mail->ErrorInfo}</div>";
                }
            }
            mysqli_stmt_close($stmt_update);
        } else {
            echo "<div class='alert alert-danger'>Usuário não encontrado.</div>";
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles/recuperar_senha.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <title>Recuperar Senha</title>
</head>

<body>
<button class="back-button" onclick="goBack()">
        <i class="material-icons">arrow_back</i>
</button>
    <div class="container">
        <div class="header">
            <img src="./img/cad.png" alt="Logo GenesFlix" class="logo">
            <h2>Recuperar Senha</h2>
            <p>Digite seu e-mail cadastrado para receber o código de recuperação.</p>
        </div>
        <form action="recuperar_senha.php" method="post">
            <div class="form-group">
                <label for="email">E-mail</label>
                <input type="email" id="email" name="email" placeholder="exemplo@dominio.com" required>
            </div>
            <button type="submit" class="btn">Enviar</button>
        </form>
        <div class="footer">
            <p>Voltar para o <a href="login.php">login</a>.</p>
        </div>
    </div>
    <script>
        function goBack() {
            window.history.back(); // Navega de volta na história do navegador
        }
    </script>
</body>

</html>