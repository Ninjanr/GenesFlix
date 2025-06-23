<?php
session_start();
include 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit();
}

// Pegue o ID do usuário logado
$user_id = $_SESSION['user'];

// Buscar o papel (role) do usuário logado
$sql_user = "SELECT role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result_user = $stmt->get_result();
$user = $result_user->fetch_assoc();

// Verifica se o usuário é administrador
if (!$user || !in_array($user['role'], ['admin', 'admin_boss'])) {
    header('Location: erro_acesso.php');
    exit();
}

// Insere os gêneros no banco de dados, caso ainda não estejam inseridos
$sqlCheck = "SELECT COUNT(*) as total FROM genero";
$resultCheck = mysqli_query($conn, $sqlCheck);
$rowCheck = mysqli_fetch_assoc($resultCheck);

if ($rowCheck['total'] == 0) {
    // Inserir os gêneros
    $sqlInsertGenres = "INSERT INTO genero (nome) VALUES  ('Fantasia'), ('Comédia'), ('Ação'), ('Drama'), ('Romance'), ('Terror'), ('Suspense'), ('Documentário'), ('Animação');";
    mysqli_query($conn, $sqlInsertGenres);
}

// Variável para armazenar a mensagem de status
$statusMessage = '';

// Verifica se o formulário foi submetido
if (isset($_POST['submit'])) {
    // Captura os dados do formulário
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $ano = $_POST['ano'];
    $id_genero = $_POST['genero']; // Captura o gênero selecionado
    $url_filme = $_POST['url_filme'];

    // Verifica se a imagem foi enviada sem erros
    if ($_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        // Obtém o caminho temporário do arquivo de imagem
        $imagemTmp = $_FILES['imagem']['tmp_name'];

        // Lê o conteúdo da imagem em formato binário
        $imagemConteudo = file_get_contents($imagemTmp);

        // Prepara a instrução SQL para inserir os dados na tabela 'filmes'
        $sql = "INSERT INTO filmes (titulo, descricao, ano, imagem, url) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssiss", $titulo, $descricao, $ano, $imagemConteudo, $url_filme);

        // Executa a instrução SQL e obtém o ID do filme inserido
        if (mysqli_stmt_execute($stmt)) {
            $id_filme = mysqli_insert_id($conn); // Obtém o ID do filme recém cadastrado

            // Relaciona o filme com o gênero selecionado na tabela 'filmes_genero'
            $sqlGenero = "INSERT INTO filmes_genero (id_genero, id_filmes) VALUES (?, ?)";
            $stmtGenero = mysqli_prepare($conn, $sqlGenero);
            mysqli_stmt_bind_param($stmtGenero, "ii", $id_genero, $id_filme);

            if (mysqli_stmt_execute($stmtGenero)) {
                $statusMessage = "<div class='alert alert-success'>Filme cadastrado com sucesso com gênero!</div>";
            } else {
                $statusMessage = "<div class='alert alert-danger'>Erro ao relacionar o gênero ao filme.</div>";
            }
        } else {
            $statusMessage = "<div class='alert alert-danger'>Erro ao cadastrar o filme.</div>";
        }
    } else {
        $statusMessage = "<div class='alert alert-danger'>Erro ao enviar a imagem.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Filmes - GenesFlix</title>
    <link rel="stylesheet" href="styles/style_filme_cad.CSS">

</head>

<body>
    <video autoplay muted loop playsinline id="background-video">
        <source src="img/dna_background.mp4" type="video/mp4">
        Seu navegador não suporta a reprodução de vídeo.
    </video>
    <div class="registration-container">
        <div class="registration-form">
            <div class="icon-container">
                <img src="img/Gear_Movie.png" alt="Logo" class="logo">
            </div>

            <h2>CADASTRAR FILME:</h2>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <input type="text" class="form-control" name="titulo" placeholder="Título do Filme" required>
                </div>

                <div class="form-group">
                    <textarea class="form-control" name="descricao" placeholder="Descrição" required></textarea>
                </div>

                <div class="form-group">
                    <input type="number" class="form-control" name="ano" placeholder="Ano de Lançamento" required>
                </div>

                <div class="form-group">
                    <label for="genero">Gênero:</label>
                    <select class="form-control" name="genero" required>
                        <option value="">Selecione o Gênero</option>
                        <?php
                        $sqlGenero = "SELECT id, nome FROM genero";
                        $resultGenero = mysqli_query($conn, $sqlGenero);

                        while ($row = mysqli_fetch_assoc($resultGenero)) {
                            echo "<option value='" . $row['id'] . "'>" . $row['nome'] . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <!-- Campo para URL do filme -->
                <div class="form-group">
                    <input type="url" class="form-control" name="url_filme" placeholder="URL do Filme" required>
                </div>
                <div class="form-group">
                    <label for="imagem">Selecione a Imagem:</label>
                    <input type="file" name="imagem" accept="image/*" required>
                    <br>
                    <button type="button" id="remove-image" style="display:none;">❌</button>
                </div>

                <div class="form-btn">
                    <input type="submit" class="btn btn-primary" value="Cadastrar" name="submit">
                </div>
            </form>
        </div>
    </div>

    <div class="status-message">
        <?php echo $statusMessage; ?>
        <!-- Botão de redirecionamento para página inicial -->
        <?php if (strpos($statusMessage, 'Filme cadastrado com sucesso') !== false) : ?>
            <br>
            <button onclick="redirecionar()" class="btn btn-success">Ir para a Página Inicial</button>
        <?php endif; ?>
    </div>

    <script>
        function redirecionar() {
            window.location.href = 'Home.php'; // Alterar para a página que deseja redirecionar
        }

        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const imageInput = document.querySelector('input[type="file"]');
            const imagePreview = document.createElement('img');
            const removeButton = document.getElementById('remove-image');

            imagePreview.style.maxWidth = '40%';
            imagePreview.style.marginTop = '0px';

            imageInput.addEventListener('change', function(event) {
                const file = event.target.files[0];

                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();

                    reader.onload = function(e) {
                        imagePreview.src = e.target.result;
                        removeButton.style.display = 'block';
                    };

                    reader.readAsDataURL(file);
                } else {
                    alert('Por favor, selecione um arquivo de imagem válido.');
                    imageInput.value = '';
                }
            });

            imageInput.parentNode.appendChild(imagePreview);

            removeButton.addEventListener('click', function() {
                imagePreview.src = '';
                imageInput.value = '';
                removeButton.style.display = 'none';
            });

            form.addEventListener('submit', function(event) {
                const titulo = form.querySelector('input[name="titulo"]').value.trim();
                const descricao = form.querySelector('textarea[name="descricao"]').value.trim();
                const ano = form.querySelector('input[name="ano"]').value.trim();
                const imagem = imageInput.files[0];
                const url = form.querySelector('input[name="url_filme"]').value.trim();

                let valid = true;

                if (!titulo || !descricao || !ano || !imagem || !url) {
                    alert('Por favor, preencha todos os campos e selecione uma imagem.');
                    valid = false;
                }

                const currentYear = new Date().getFullYear();
                if (ano < 1888 || ano > currentYear) {
                    alert('Por favor, insira um ano válido entre 1888 e o ano atual.');
                    valid = false;
                }

                if (imagem && !imagem.type.startsWith('image/')) {
                    alert('Por favor, selecione um arquivo de imagem válido.');
                    valid = false;
                }

                if (!valid) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>

</html>