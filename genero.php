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
$sql_user = "SELECT full_name, email, profile_picture, role FROM users WHERE id = ?";
$stmt = $conn->prepare($sql_user);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result_user = $stmt->get_result();

if ($result_user->num_rows > 0) {
    $user = $result_user->fetch_assoc();
} else {
    echo "Erro: Usuário não encontrado!";
    exit();
}

// Endpoint para buscar filmes por gênero
if (isset($_GET['genero'])) {
    $genero = $_GET['genero'];

    $sql_filmes = "SELECT 
        filmes.id, 
        filmes.imagem, 
        filmes.titulo, 
        filmes.descricao, 
        filmes.ano, 
        filmes.url, 
        genero.nome AS genero,
        ROUND(IFNULL(AVG(avaliacao.nota), 0), 2) AS media_avaliacao
    FROM 
        filmes
    JOIN 
        filmes_genero ON filmes.id = filmes_genero.id_filmes
    JOIN 
        genero ON filmes_genero.id_genero = genero.id
    LEFT JOIN 
        avaliacao ON filmes.id = avaliacao.filmes_avaliacao
    WHERE 
        genero.nome = ?
    GROUP BY 
        filmes.id";

    $stmt = $conn->prepare($sql_filmes);
    $stmt->bind_param('s', $genero);
    $stmt->execute();
    $result_filmes = $stmt->get_result();

    $filmes = [];
    while ($row = $result_filmes->fetch_assoc()) {
        error_log(print_r($row, true)); // Adiciona log para cada filme
        $row['imagem'] = base64_encode($row['imagem']);
        $filmes[] = $row;
    }

    echo json_encode(['success' => true, 'filmes' => $filmes]);
    exit();
}

// Buscar os gêneros para os botões
$sql_generos = "SELECT nome FROM genero";
$result_generos = $conn->query($sql_generos);
$generos = $result_generos->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenesFlix</title>
    <link rel="stylesheet" href="styles/home_styles.css">
    <link rel="stylesheet" href="styles/home_tema.css">
    <script src="JS/script_tema.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .genre-buttons {
            margin: 20px 0;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: center;
            /* Centraliza os botões horizontalmente */
            align-items: center;
            /* Centraliza os botões verticalmente */
            text-align: center;
        }

        .genre-button {
            padding: 12px 25px;
            background-color: #333;
            color: white;
            border: 2px solid #444;
            /* Borda sutil */
            border-radius: 30px;
            /* Borda arredondada mais suave */
            cursor: pointer;
            font-size: 16px;
            /* Fonte maior para dar mais destaque */
            transition: background-color 0.3s, transform 0.2s;
            /* Transição para o efeito de hover */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
            /* Sombra suave */
        }

        .genre-button:hover {
            background-color: #555;
            transform: scale(1.05);
            /* Efeito de zoom suave no hover */
        }

        .genre-button:focus {
            outline: none;
            /* Remove a borda padrão do foco */
            box-shadow: 0 0 0 2px #f39c12;
            /* Sombra brilhante para foco */
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="header-left">
            <img src="img/logo_title.png" alt="Logo">
        </div>
        <nav class="navbar">
            <a href="Home.php">Início</a>
            <a href="favoritos.php">Minha lista</a>
            <a href="Jogo/Tela_jogo.html">Diversão</a>
            <a href="genero.php">Gênero</a>
        </nav>

        <!-- Barra de pesquisa -->
        <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Pesquise..." autocomplete="off">
            <ul id="suggestions" class="suggestions"></ul>
            <i class="fa fa-search search-icon"></i>
        </div>
        <div class="user-info">
            <div class="header-right color-border">
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Foto de Perfil" class="profile-thumbnail" id="profileIcon" onclick="toggleDropdown()">
                <i class="fas fa-chevron-up arrow-icon" id="arrowIcon"></i> <!-- Seta substituída -->
                <div class="dropdown" id="profileDropdown">
                    <h4 class="user-name"><?php echo htmlspecialchars($user['full_name']); ?></h4>
                    <a href="profile.php">Gerenciar perfil</a>
                    <!--<a href="#">Central de Ajuda</a>-->
                    <div class="divider"></div>
                    <a href="logout.php">Sair do GenesFlix</a>
                    <div class="divider"></div>
                    <div class="btn">
                        <?php if ($user && in_array($user['role'], ['admin', 'admin_boss'])) : ?>
                            <a href="cadastro_de_filme.php" class="btn-link">Cadastrar Filme</a>
                        <?php endif; ?>
                        <?php if ($user && in_array($user['role'], ['admin', 'admin_boss'])) : ?>
                            <a href="dashboard_filmes.php" class="btn-link">Painel de filmes</a>
                        <?php endif; ?>
                        <!--  <a href="chat.php" class="btn-link">Chat</a> -->
                        <?php if ($user['role'] == 'admin_boss') : ?>
                            <a href="dashboard.php" class="btn-link">Painel</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main>
        <div class="filme-container">
            <!-- Filmes carregados dinamicamente aqui -->
        </div>
    </main>
    <div class="genre-buttons">
        <?php foreach ($generos as $genero) { ?>
            <button class="genre-button" data-genero="<?= htmlspecialchars($genero['nome']) ?>">
                <?= htmlspecialchars($genero['nome']) ?>
            </button>
        <?php } ?>
    </div>
    <!-- Modal de Detalhes -->
    <div class="modal" id="filmeModal">
        <div class="modal-content">
            <button class="close-modal" id="closeModal">&times;</button>
            <h2 id="modalTitulo"></h2>
            <p id="modalGenero"></p>
            <p id="modalDataLancamento"></p>
            <p id="modalDescricao"></p>
            <p id="modalNota"></p> <!-- Exibe a nota média -->
            <button class="play-button" id="playButton">Assistir</button>
            <!-- Contêiner de vídeo -->
            <div class="video-container" id="videoContainer">
                <iframe id="videoPlayer" width="100%" height="100%" frameborder="0" allowfullscreen></iframe>
            </div>
            <br>
            <!-- Sistema de avaliação -->
            <div class="rating-container">
                <h3>Avalie este filme:</h3>
                <div class="stars">
                    <span class="star" data-value="1">&#9733;</span>
                    <span class="star" data-value="2">&#9733;</span>
                    <span class="star" data-value="3">&#9733;</span>
                    <span class="star" data-value="4">&#9733;</span>
                    <span class="star" data-value="5">&#9733;</span>
                </div>
                <p>Sua Avaliação: <span id="rating-value">0</span> estrela(s)</p>
                <button id="submit-rating">Enviar Avaliação</button>
            </div>
            <!-- Comentários -->
            <div class="comments-section">
                <h2 class="coment_title">Comentários:</h2>
                <textarea id="new-comment" placeholder="Escreva um comentário..."></textarea>
                <button id="submit-comment">Enviar Comentário</button>
                <br><br>
                <div id="comments-container"></div>
            </div>

        </div>
    </div>
    <br>
    <br>
    <br>
    <footer class="footer">
        <p>&copy; 2024 GenesFlix, Inc. <a href="#">Política de Privacidade</a> • <a href="#">Termos de Serviço</a></p>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const filmeContainer = document.querySelector('.filme-container');
            const modal = document.getElementById('filmeModal');
            const closeModal = document.getElementById('closeModal');
            const modalTitulo = document.getElementById('modalTitulo');
            const modalGenero = document.getElementById('modalGenero');
            const modalDataLancamento = document.getElementById('modalDataLancamento');
            const modalDescricao = document.getElementById('modalDescricao');
            const modalNota = document.getElementById('modalNota');
            const playButton = document.getElementById('playButton');
            const videoPlayer = document.getElementById('videoPlayer');
            const videoContainer = document.getElementById('videoContainer');
            const commentsContainer = document.getElementById('comments-container');
            const submitCommentBtn = document.getElementById('submit-comment');
            const newCommentInput = document.getElementById('new-comment');
            let selectedFilmId = 0;

            // Função para carregar filmes por gênero
            function carregarFilmes(genero = '') {
                filmeContainer.innerHTML = '<p>Carregando...</p>';

                fetch(`?genero=${genero}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            filmeContainer.innerHTML = '';
                            data.filmes.forEach(filme => {
                                const filmeDiv = criarFilmeDiv(filme);
                                filmeContainer.appendChild(filmeDiv);
                            });
                        } else {
                            filmeContainer.innerHTML = '<p>Nenhum filme encontrado.</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao carregar filmes:', error);
                        filmeContainer.innerHTML = '<p>Erro ao carregar filmes.</p>';
                    });
            }

            // Função para criar elemento de filme
            function criarFilmeDiv(filme) {
                const filmeDiv = document.createElement('div');
                filmeDiv.className = 'filme';
                filmeDiv.dataset.id = filme.id;
                filmeDiv.dataset.titulo = filme.titulo;
                filmeDiv.dataset.descricao = filme.descricao;
                filmeDiv.dataset.dataLancamento = filme.ano;
                filmeDiv.dataset.genero = filme.genero;
                filmeDiv.dataset.url = filme.url;
                filmeDiv.dataset.mediaAvaliacao = filme.media_avaliacao;

                filmeDiv.innerHTML = `
                <img src="data:image/jpeg;base64,${filme.imagem}" alt="${filme.titulo}">
                <h2>${filme.titulo}</h2>
            `;

                filmeDiv.addEventListener('click', () => exibirModal(filmeDiv));
                return filmeDiv;
            }

            // Função para exibir o modal com os detalhes do filme
            function exibirModal(filmeDiv) {
                const titulo = filmeDiv.dataset.titulo;
                const descricao = filmeDiv.dataset.descricao;
                const dataLancamento = filmeDiv.dataset.dataLancamento;
                const genero = filmeDiv.dataset.genero;
                const mediaAvaliacao = filmeDiv.dataset.mediaAvaliacao;
                const url = filmeDiv.dataset.url;
                selectedFilmId = filmeDiv.dataset.id;

                modalTitulo.textContent = titulo;
                modalGenero.innerHTML = `<strong>Gênero:</strong> ${genero}`;
                modalDataLancamento.innerHTML = `<strong>Lançamento:</strong> ${dataLancamento}`;
                modalDescricao.innerHTML = `<strong>Descrição:</strong> ${descricao}`;
                modalNota.innerHTML = `<strong>Nota Média:</strong> ${mediaAvaliacao}`;

                playButton.onclick = () => configurarVideo(url);

                loadComments(selectedFilmId);
                modal.classList.add('show');
                document.body.classList.add('no-scroll');
            }

            // Configurar o vídeo no modal
            function configurarVideo(url) {
                if (url.includes('youtube.com') || url.includes('youtu.be')) {
                    const youtubeId = url.split('v=')[1] || url.split('youtu.be/')[1];
                    videoPlayer.src = `https://www.youtube.com/embed/${youtubeId}`;
                } else if (url.includes('drive.google.com')) {
                    const driveId = url.split('/d/')[1].split('/')[0];
                    videoPlayer.src = `https://drive.google.com/file/d/${driveId}/preview`;
                } else {
                    videoPlayer.src = url;
                }
                videoContainer.style.display = 'block';
            }

            // Carregar comentários do filme
            function loadComments(idFilme) {
                fetch(`get_comments.php?id_filme=${idFilme}`)
                    .then(response => response.json())
                    .then(data => {
                        commentsContainer.innerHTML = '';
                        if (data.length) {
                            data.forEach(comment => {
                                const commentDiv = document.createElement('div');
                                commentDiv.className = 'comment';
                                commentDiv.innerHTML = `
                            <img src="${comment.profile_picture}" alt="Foto de Perfil" class="profile-thumbnail" id="profileIcon">
                            <br>    
                            <strong>${comment.full_name}</strong>: ${comment.comentario}
                            <br><small>${comment.data_comentario}</small>
                            `;
                                commentsContainer.appendChild(commentDiv);
                            });
                        } else {
                            commentsContainer.innerHTML = '<p>Seja o primeiro a comentar!</p>';
                        }
                    })
                    .catch(err => console.error('Erro ao carregar comentários:', err));
            }
            // Fechar o modal ao clicar no botão X
            closeModal.addEventListener('click', () => {
                modal.classList.remove('show');
                document.body.classList.remove('no-scroll');
                videoPlayer.src = ''; // Limpar o vídeo ao fechar o modal
                videoContainer.style.display = 'none';
            });
            // Fechar o modal ao clicar fora do conteúdo
            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    document.body.classList.remove('no-scroll');
                    videoPlayer.src = ''; // Limpar o vídeo ao fechar o modal
                    videoContainer.style.display = 'none';
                }
            });

            // Enviar comentário
            submitCommentBtn.addEventListener('click', function() {
                const comentario = newCommentInput.value.trim();
                if (!comentario) {
                    alert('Escreva um comentário!');
                    return;
                }

                fetch('submit_comment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            comentario,
                            id_filme: selectedFilmId
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            newCommentInput.value = '';
                            loadComments(selectedFilmId);
                        } else {
                            alert('Erro ao enviar comentário.');
                        }
                    })
                    .catch(err => console.error('Erro ao enviar comentário:', err));
            });
            newCommentInput.addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    submitCommentBtn.click();
                }
            });
            
            // Inicializar carregamento de filmes
            carregarFilmes();

            // Adicionar eventos aos botões de gênero
            document.querySelectorAll('.genre-button').forEach(button => {
                button.addEventListener('click', () => {
                    const genero = button.getAttribute('data-genero');
                    carregarFilmes(genero);
                });
            });
            // Função para buscar sugestões enquanto o usuário digita
            document.getElementById('searchInput').addEventListener('input', function() {
                const query = this.value;

                if (query.length > 0) {
                    fetch(`search_suggestions.php?query=${query}`)
                        .then(response => response.json())
                        .then(data => {
                            const suggestionsList = document.getElementById('suggestions');
                            suggestionsList.innerHTML = ''; // Limpa as sugestões anteriores
                            data.forEach(filme => {
                                const listItem = document.createElement('li');
                                listItem.textContent = filme.titulo;

                                // Autocompleta o campo de pesquisa ao clicar na sugestão
                                listItem.addEventListener('click', function() {
                                    document.getElementById('searchInput').value = filme.titulo;
                                    window.location.href = `search_results.php?query=${filme.titulo}`;
                                });

                                suggestionsList.appendChild(listItem);
                            });
                        });
                } else {
                    document.getElementById('suggestions').innerHTML = ''; // Limpa as sugestões se o campo estiver vazio
                }
            });

            // Função para redirecionar o usuário ao pressionar ENTER
            document.getElementById('searchInput').addEventListener('keydown', function(event) {
                if (event.key === 'Enter') {
                    const query = this.value;
                    if (query.length > 0) {
                        window.location.href = `search_results.php?query=${query}`;
                    }
                }
            });
        });
    </script>

</body>

</html>