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

// Verifica se a query foi enviada via GET
if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Prepara a consulta para buscar os filmes com o título correspondente
    $sql = "SELECT 
                filmes.id, 
                filmes.imagem, 
                filmes.titulo, 
                filmes.descricao, 
                filmes.ano, 
                filmes.url, 
                genero.nome AS genero, 
                IFNULL(AVG(avaliacao.nota), 0) AS media_avaliacao 
            FROM 
                filmes
            JOIN 
                filmes_genero ON filmes.id = filmes_genero.id_filmes
            JOIN 
                genero ON filmes_genero.id_genero = genero.id
            LEFT JOIN 
                avaliacao ON filmes.id = avaliacao.filmes_avaliacao
            WHERE 
                filmes.titulo LIKE ?
            GROUP BY 
                filmes.id, filmes.imagem, filmes.titulo, filmes.descricao, filmes.ano, filmes.url, genero.nome";
    
    $stmt = $conn->prepare($sql);
    $searchTerm = "%$query%";
    $stmt->bind_param('s', $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();

    $filmes_favoritos = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $filmes_favoritos[] = $row;
        }
    } else {
        echo "Nenhum filme encontrado.";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GenesFlix - Pesquisa de Filmes</title>
    <link rel="stylesheet" href="styles/home_styles.css">
    <link rel="stylesheet" href="styles/home_tema.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<!-- Header com informações do usuário -->
<div class="header">
    <div class="header-left">
        <img src="img/logo_title.png">
    </div>
    <nav class="navbar">
            <a href="Home.php">Início</a>
            <a href="favoritos.php">Minha lista</a>
            <a href="Jogo/Tela_jogo.html">Diversão</a>
            <a href="genero.php">Gênero</a>
        </nav>
    <div class="user-info">
            <div class="header-right color-border">
                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Foto de Perfil" class="profile-thumbnail" id="profileIcon" onclick="toggleDropdown()">
                <i class="fas fa-chevron-up arrow-icon" id="arrowIcon"></i> <!-- Seta substituída -->
                <div class="dropdown" id="profileDropdown">
                    <a href="profile.php">Gerenciar perfil</a>
                    <a href="#">Central de Ajuda</a>
                    <div class="divider"></div>
                    <a href="logout.php">Sair do GenesFlix</a>
                    <div class="divider"></div>
                    <div class="btn">
                        <?php if ($user['role'] == 'admin') : ?>
                            <a href="cadastro_de_filme.php" class="btn-link">Opção de Admin: Cadastrar Filme</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
    </div>
</div>

<main>
    <h1>Resultados da Pesquisa</h1>

    <!-- Exibição dos filmes pesquisados -->
    <div class="filme-container">
    <?php if (!empty($filmes_favoritos)) { ?>
        <?php foreach ($filmes_favoritos as $filme) { ?>
            <div class="filme" data-id="<?= htmlspecialchars($filme['id']) ?>" data-titulo="<?= htmlspecialchars($filme['titulo']) ?>"
                 data-descricao="<?= htmlspecialchars($filme['descricao']) ?>" data-data-lancamento="<?= htmlspecialchars($filme['ano']) ?>"
                 data-genero="<?= htmlspecialchars($filme['genero']) ?>" data-url="<?= htmlspecialchars($filme['url']) ?>"
                 data-media-avaliacao="<?= number_format($filme['media_avaliacao'], 2) ?>">
                <img src="data:image/jpeg;base64,<?= base64_encode($filme['imagem']) ?>" alt="<?= htmlspecialchars($filme['titulo']) ?>">
                <h2><?= htmlspecialchars($filme['titulo']) ?></h2>
            </div>
        <?php } ?>
    <?php } else { ?>
        <p style="color: white; font-weight: bold; font-size: 35px;">
            Nenhum filme encontrado com o título "<?= htmlspecialchars($query) ?>".
        </p>
    <?php } ?>
    </div>
</main>

<!-- Modal de detalhes do filme -->
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
        </div>
    </div>
    <br>
    <br>
    <br>
    <footer class="footer">
        <p>&copy; 2024 GenesFlix, Inc. <a href="#">Política de Privacidade</a> • <a href="#">Termos de Serviço</a></p>
    </footer>

<script>
     // Função para alternar a visibilidade do dropdown
     function toggleDropdown() {
            var dropdown = document.getElementById("profileDropdown");
            var arrowIcon = document.getElementById("arrowIcon");

            // Alterna a visibilidade do dropdown
            if (dropdown.style.display === "none" || dropdown.style.display === "") {
                dropdown.style.display = "block";
                arrowIcon.classList.add("open"); // Adiciona a classe 'open' para rotacionar a seta
            } else {
                dropdown.style.display = "none";
                arrowIcon.classList.remove("open"); // Remove a classe 'open' para voltar a seta para cima
            }
        }


        // Fechar o dropdown ao clicar fora dele
        window.onclick = function(event) {
            if (!event.target.matches('.profile-thumbnail')) {
                var dropdowns = document.getElementsByClassName("dropdown");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.style.display === "block") {
                        openDropdown.style.display = "none";
                    }
                }
            }
        }
        document.addEventListener('DOMContentLoaded', function() {
            fetch('Home.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const filmeContainer = document.querySelector('.filme-container');
                        data.filmes.forEach(filme => {
                            // Cria a div do filme
                            const filmeDiv = document.createElement('div');
                            filmeDiv.className = 'filme';
                            filmeDiv.dataset.id = filme.id;
                            filmeDiv.dataset.titulo = filme.titulo;
                            filmeDiv.dataset.descricao = filme.descricao;
                            filmeDiv.dataset.dataLancamento = filme.ano;
                            filmeDiv.dataset.genero = filme.genero;
                            filmeDiv.dataset.url = filme.url; // Adicionar URL do filme
                            filmeDiv.dataset.mediaAvaliacao = filme.media_avaliacao;

                            // Conteúdo HTML do filme
                            filmeDiv.innerHTML = `
                        <img src="data:image/jpeg;base64,${filme.imagem}" alt="${filme.titulo}">
                        <h2>${filme.titulo}</h2>
                        <p>Nota: ${parseFloat(filme.media_avaliacao).toFixed(2)}</p>
                    `;
                            filmeContainer.appendChild(filmeDiv);
                        });
                    } else {
                        console.error('Erro ao carregar filmes');
                    }
                })
                .catch(error => console.error('Erro:', error));

            const modal = document.getElementById('filmeModal');
            const closeModal = document.getElementById('closeModal');
            const modalTitulo = document.getElementById('modalTitulo');
            const modalGenero = document.getElementById('modalGenero');
            const modalDataLancamento = document.getElementById('modalDataLancamento');
            const modalDescricao = document.getElementById('modalDescricao');
            const modalNota = document.getElementById('modalNota');
            const playButton = document.getElementById('playButton');
            const videoContainer = document.getElementById('videoContainer');
            const videoPlayer = document.getElementById('videoPlayer');
            const stars = document.querySelectorAll('.star');
            const ratingValue = document.getElementById('rating-value');
            const submitRating = document.getElementById('submit-rating');

            let selectedRating = 0;
            let selectedFilmId = 0;

            // Função para resetar a avaliação
            function resetRating() {
                ratingValue.textContent = '0';
                selectedRating = 0;
                stars.forEach(star => star.classList.remove('active'));
            }

            // Adiciona listeners para estrelas de avaliação
            stars.forEach(star => {
                star.addEventListener('click', () => {
                    const rating = star.getAttribute('data-value');
                    ratingValue.textContent = rating;
                    selectedRating = rating;
                    stars.forEach(s => s.classList.remove('active'));
                    for (let i = 0; i < rating; i++) {
                        stars[i].classList.add('active');
                    }
                });
            });

            // Exibir modal ao clicar em um filme
            document.querySelector('.filme-container').addEventListener('click', e => {
                const filme = e.target.closest('.filme');
                if (filme) {
                    const titulo = filme.dataset.titulo;
                    const descricao = filme.dataset.descricao;
                    const dataLancamento = filme.dataset.dataLancamento;
                    const genero = filme.dataset.genero;
                    const mediaAvaliacao = filme.dataset.mediaAvaliacao; // Captura a nota média
                    const url = filme.dataset.url;
                    selectedFilmId = filme.dataset.id;

                    // Preencher o modal com os detalhes do filme
                    modalTitulo.textContent = titulo;
                    modalGenero.innerHTML = `<strong>Gênero:</strong> ${genero}`;
                    modalDataLancamento.innerHTML = `<strong>Lançamento:</strong> ${dataLancamento}`;
                    modalDescricao.innerHTML = `<strong>Descrição:</strong> ${descricao}`;
                    modalNota.innerHTML = `<strong>Nota Média:</strong> ${mediaAvaliacao}`; // Exibe a nota média corretamente

                    // Configura o botão "Assistir" para exibir o vídeo
                    playButton.onclick = function() {
                        // Verifica se a URL é do YouTube ou um link de vídeo local
                        if (url.includes('youtube.com') || url.includes('youtu.be')) {
                            const youtubeId = url.split('v=')[1] || url.split('youtu.be/')[1];
                            videoPlayer.src = `https://www.youtube.com/embed/${youtubeId}`;
                        } else {
                            videoPlayer.src = url; // URL local ou outra URL
                        }
                        videoContainer.style.display = 'block'; // Exibir o vídeo
                    };

                    // Exibir o modal
                    modal.classList.add('show');
                }
            });

            // Fechar o modal ao clicar no botão X
            closeModal.addEventListener('click', () => {
                modal.classList.remove('show');
                videoPlayer.src = ''; // Limpar o vídeo ao fechar o modal
                videoContainer.style.display = 'none';
            });

            // Fechar o modal ao clicar fora do conteúdo
            window.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.remove('show');
                    videoPlayer.src = ''; // Limpar o vídeo ao fechar o modal
                    videoContainer.style.display = 'none';
                }
            });
        
        
        // Enviar a avaliação
        submitRating.addEventListener('click', () => {
        const rating = ratingValue.textContent;
        const filmeTitulo = modalTitulo.textContent;

        if (rating === '0') {
            alert('Por favor, escolha uma estrela para enviar a avaliação.');
            return;
        }

        fetch('submit_rating.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    rating: rating,
                    filme: filmeTitulo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Avaliação enviada com sucesso!');
                    resetRating();
                } else {
                    alert('Você já avaliou este filme.');
                    resetRating();
                }
            })
            .catch((error) => {
                console.error('Erro:', error);
                alert('Ocorreu um erro ao enviar a avaliação.');
            });
        });
        
    });
</script>
<script src="JS/script_tema.js"></script>
</body>
</html>
