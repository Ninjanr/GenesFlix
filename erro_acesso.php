<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Acesso Negado</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            background-color: #1b1b1b;
            color: #f1f1f1;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            text-align: center;
            background-color: #2c2c2c;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.2);
        }
        .container h1 {
            font-size: 45px;
            color: #e74c3c;
            margin-bottom: 15px;
        }
        .container p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #d3d3d3;
        }
        .container a {
            text-decoration: none;
            color: #1abc9c;
            font-weight: bold;
            border: 2px solid #1abc9c;
            padding: 10px 25px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .container a:hover {
            background-color: #1abc9c;
            color: #fff;
        }
        .icon {
            font-size: 80px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1>Acesso Negado</h1>
        <p>Você não tem permissão para acessar esta página.<br> Somente administradores podem realizar esta ação.</p>
        <a href="Home.php">Voltar à Página Inicial</a>
    </div>
</body>
</html>