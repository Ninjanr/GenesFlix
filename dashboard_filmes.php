<?php
session_start();
require 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user'])) {
  header('Location: login.php');
  exit();
}

$user_id = $_SESSION['user'];

// Consulta ajustada
$sql_filmes = "SELECT id, imagem, titulo, descricao, ano, url 
               FROM filmes 
               WHERE titulo IS NOT NULL AND titulo != '' 
                 AND imagem IS NOT NULL 
                 AND descricao IS NOT NULL AND descricao != '' 
                 AND ano IS NOT NULL 
                 AND url IS NOT NULL AND url != ''";

$stmt = $conn->prepare($sql_filmes);
$stmt->execute();
$result_filmes = $stmt->get_result();

$filmes = [];
if ($result_filmes->num_rows > 0) {
  while ($row = $result_filmes->fetch_assoc()) {
    $filmes[] = $row;
  }
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gerenciar Filmes - GenesFlix</title>
  <link rel="stylesheet" href="styles/edit_style.css">
  <link rel="stylesheet" href="styles/home_tema.css">
  <script src="JS/script_tema.js"></script>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />

</head>

<body>
  <button class="back-button" onclick="goBack()">
    <i class="material-icons">arrow_back</i>
  </button>
  <header class="header">
    <div class="header-left">
      <img src="img/logo_title.png" alt="Logo">
    </div>
    <div class="search">
    <input type="text" id="searchInput" placeholder="Pesquise o Filme...">
    <button onclick="searchWord()">
      <i class="fa fa-search search-icon"></i>
    </button>
    <div class="navigation-arrows">
            <button onclick="navigateHighlights('up')">
                <i class="fas fa-arrow-up"></i>
            </button>
            <button onclick="navigateHighlights('down')">
                <i class="fas fa-arrow-down"></i>
            </button>
        </div>
  </div>
  </header>
 
  <br><br>
  <main>
    <h2 class="titulo_h2">Gerenciar Filmes</h2>
    <div class="filme-container">
      <?php foreach ($filmes as $filme) : ?>
        <div class="filme" data-id="<?= $filme['id'] ?>" data-descricao="<?= htmlspecialchars($filme['descricao']) ?>" data-ano="<?= htmlspecialchars($filme['ano']) ?>" data-url="<?= htmlspecialchars($filme['url']) ?>" data-genero="<?= htmlspecialchars($filme['genero'] ?? '') ?>">
          <img src="data:image/jpeg;base64,<?= base64_encode($filme['imagem']) ?>" alt="<?= htmlspecialchars($filme['titulo']) ?>">
          <h2><?= htmlspecialchars($filme['titulo']) ?></h2>
          <div class="btn-group">
            <button class="btn-edit">Editar</button>
            <form action="excluir_filme.php" method="POST" onsubmit="return confirm('Tem certeza que deseja excluir?');">
              <input type="hidden" name="id" value="<?= $filme['id'] ?>">
              <button type="submit">Excluir</button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- Modal de edição -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span id="closeModal" class="close">&times;</span>
      <h3>Editar Filme</h3>
      <form id="editFilmForm">
        <input type="hidden" id="edit_film_id">
        <label for="edit_titulo">Título:</label>
        <input type="text" id="edit_titulo" required>

        <label for="edit_descricao">Descrição:</label>
        <textarea id="edit_descricao" rows="3" required></textarea>

        <label for="imagem">Selecione a Imagem:</label>
        <input type="file" id="edit_imagem" name="imagem" accept="image/*">
        <br>
        <button type="button" id="remove-image" style="display:none;">❌</button>

        <label for="edit_ano">Ano:</label>
        <input type="number" id="edit_ano" required>

        <label for="edit_genero">Gênero:</label>
        <select id="edit_genero" required>
          <option value="">Selecione o Gênero</option>
        </select>

        <label for="edit_url">URL:</label>
        <input type="url" id="edit_url" required>
      </form>
      <button id="saveChanges">Salvar</button>
    </div>
  </div>

  <footer class="footer">
    <p>&copy; 2024 GenesFlix, Inc. <a href="#">Política de Privacidade</a> • <a href="#">Termos de Serviço</a></p>
  </footer>

  <script>
    function goBack() {
      window.history.back(); // Navega de volta na história do navegador
    }
    document.addEventListener('DOMContentLoaded', function() {
      const modal = document.getElementById('editModal');
      const closeModal = document.getElementById('closeModal');
      const saveChangesButton = document.getElementById('saveChanges');
      const imageInput = document.getElementById('edit_imagem');
      const imagePreview = document.createElement('img');
      const removeButton = document.getElementById('remove-image');

      // Configurar pré-visualização da imagem
      imagePreview.style.maxWidth = '40%';
      imagePreview.style.marginTop = '10px';
      imageInput.parentNode.appendChild(imagePreview);

      imageInput.addEventListener('change', function(event) {
        const file = event.target.files[0];
        if (file && file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = (e) => {
            imagePreview.src = e.target.result;
            removeButton.style.display = 'block';
          };
          reader.readAsDataURL(file);
        } else {
          alert('Por favor, selecione um arquivo de imagem válido.');
          imageInput.value = '';
        }
      });

      removeButton.addEventListener('click', () => {
        imagePreview.src = '';
        imageInput.value = '';
        removeButton.style.display = 'none';
      });

      // Abrir modal para edição
      document.querySelectorAll('.btn-edit').forEach(button => {
        button.addEventListener('click', async (e) => {
          const filmeDiv = e.target.closest('.filme');
          const filmId = filmeDiv.dataset.id;

          document.getElementById('edit_film_id').value = filmId;
          document.getElementById('edit_titulo').value = filmeDiv.querySelector('h2').textContent;
          document.getElementById('edit_descricao').value = filmeDiv.dataset.descricao;
          document.getElementById('edit_ano').value = filmeDiv.dataset.ano;
          document.getElementById('edit_url').value = filmeDiv.dataset.url;

          // Carrega os gêneros disponíveis
          const generoSelect = document.getElementById('edit_genero');
          generoSelect.innerHTML = '<option value="">Selecione o Gênero</option>';
          try {
            const response = await fetch('get_generos.php');
            const generos = await response.json();
            generos.forEach(genero => {
              const option = document.createElement('option');
              option.value = genero.id;
              option.textContent = genero.nome;
              generoSelect.appendChild(option);
            });

            // Pré-seleciona o gênero atual
            const currentGenero = filmeDiv.dataset.genero; // Assumindo que o gênero está no dataset
            generoSelect.value = currentGenero || '';
          } catch (error) {
            console.error('Erro ao carregar gêneros:', error);
          }

          modal.style.display = 'block';
        });
      });

      closeModal.addEventListener('click', () => modal.style.display = 'none');

      document.getElementById('saveChanges').addEventListener('click', () => {
        const form = document.getElementById('editFilmForm');
        const formData = new FormData();

        // Captura os valores do formulário
        formData.append('id', document.getElementById('edit_film_id').value);
        formData.append('titulo', document.getElementById('edit_titulo').value);
        formData.append('descricao', document.getElementById('edit_descricao').value);
        formData.append('ano', document.getElementById('edit_ano').value);
        formData.append('url', document.getElementById('edit_url').value);
        formData.append('genero', document.getElementById('edit_genero').value); // Adiciona o gênero

        // Adiciona a imagem se uma nova for selecionada
        const imagemInput = document.getElementById('edit_imagem');
        if (imagemInput.files.length > 0) {
          formData.append('imagem', imagemInput.files[0]);
        }

        // Envia os dados ao servidor
        fetch('edit_filme.php', {
            method: 'POST',
            body: formData
          })
          .then(response => response.json())
          .then(data => {
            if (data.status === 'success') {
              alert('Filme atualizado com sucesso!');
              location.reload();
            } else {
              alert('Erro ao atualizar o filme: ' + data.message);
              modal.style.display = 'block'; // Mantém o modal aberto em caso de erro
            }
          })
          .catch(error => {
            console.error('Erro no envio:', error);
            alert('Erro ao processar a solicitação.');
          });
      });

    });

    let currentHighlightIndex = -1;
let highlights = [];

function searchWord() {
    // Remove previous highlights
    const highlightedElements = document.querySelectorAll('.highlight');
    highlightedElements.forEach(el => {
        el.classList.remove('highlight');
    });

    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    if (!searchInput) return;

    const content = document.querySelector('.filme-container');
    const textNodes = getTextNodes(content);

    highlights = [];
    const regex = new RegExp(`\\b${searchInput}\\b`, 'gi'); // Expressão regular para garantir palavras completas

    textNodes.forEach(node => {
        const text = node.nodeValue.toLowerCase();
        let match;
        
        // Procura pelas ocorrências de palavras inteiras usando a regex
        while ((match = regex.exec(text)) !== null) {
            const span = document.createElement('span');
            span.className = 'highlight';
            span.textContent = match[0]; // A palavra inteira
            const after = node.splitText(match.index);
            after.nodeValue = after.nodeValue.substring(match[0].length);
            node.parentNode.insertBefore(span, after);
            highlights.push(span);
        }
    });

    if (highlights.length > 0) {
        currentHighlightIndex = 0;
        highlights[currentHighlightIndex].scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
    } else {
        alert('Word not found');
    }
}

function getTextNodes(node) {
    const textNodes = [];

    function recurse(node) {
        if (node.nodeType === 3) {
            textNodes.push(node);
        } else {
            for (let i = 0; i < node.childNodes.length; i++) {
                recurse(node.childNodes[i]);
            }
        }
    }
    recurse(node);
    return textNodes;
}

function navigateHighlights(direction) {
    if (highlights.length === 0) return;

    if (direction === 'up') {
        currentHighlightIndex = (currentHighlightIndex - 1 + highlights.length) % highlights.length;
    } else if (direction === 'down') {
        currentHighlightIndex = (currentHighlightIndex + 1) % highlights.length;
    }

    highlights[currentHighlightIndex].scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
}

// Adiciona a função de pesquisa ao pressionar ENTER
document.getElementById('searchInput').addEventListener('keydown', function(event) {
    if (event.key === 'Enter') {
        searchWord();
    }
});

  </script>

</body>

</html>