<?php
session_start();
include('db_connection.php');
include('functions.php');
include('navbar.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['usuario_id']; // ID do usuário logado

// Função para obter os agendamentos do usuário
function getAgendamentos($conn, $user_id) {
    // Consulta para recuperar os agendamentos do usuário junto com a sala
    $query = "
        SELECT ag.id AS agendamento_id, esp.id AS espaco_id, esp.nome AS espaco_nome, 
               ag.data_inicio, ag.data_fim
        FROM agendamentos ag
        JOIN espacos esp ON ag.id_espaco = esp.id
        WHERE ag.id_usuario = ? AND ag.status IN ('finalizado', 'confirmado', 'pendente')  -- Trocamos 'finalizado' por outros status para garantir que algum agendamento seja exibido.
        ORDER BY ag.data_inicio DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Verifique se há resultados
    if ($result->num_rows > 0) {
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return [];  // Se não houver agendamentos, retorna um array vazio
    }
}

// Processa o envio da avaliação
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['avaliar'])) {
    $id_agendamento = $_POST['id_agendamento'];
    $nota = $_POST['nota'];
    $comentario = $_POST['comentario'];

    // Valida a nota
    if ($nota < 1 || $nota > 5) {
        $message = "A nota deve ser entre 1 e 5.";
    } else {
        // Insere a avaliação no banco de dados
        $query_avaliacao = "
            INSERT INTO avaliacoes (id_agendamento, nota, comentario)
            VALUES (?, ?, ?)
        ";
        $stmt = $conn->prepare($query_avaliacao);
        $stmt->bind_param("iis", $id_agendamento, $nota, $comentario);
        $stmt->execute();
        $message = "Avaliação enviada com sucesso!";
    }
}

// Obtém os agendamentos do usuário
$agendamentos = getAgendamentos($conn, $user_id);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Avaliar Agendamento - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\index.css">
    <script>
        // Função para exibir a sala selecionada ao escolher um agendamento
        function mostrarEspaco() {
            const espacoNome = document.getElementById('id_agendamento').options[document.getElementById('id_agendamento').selectedIndex].text;
            const espacoId = document.getElementById('id_agendamento').value;
            document.getElementById('espaco_selecionado').textContent = espacoNome ? 'Sala ID ' + espacoId + ' selecionada: ' + espacoNome : '';
        }
    </script>
</head>
<body>
    <div class="container my-5">
        <h2>Avaliar Agendamento</h2>

        <!-- Mensagem de sucesso ou erro -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de avaliação -->
        <form action="gerar_avaliacao.php" method="POST">
            <div class="mb-3">
                <label for="id_agendamento" class="form-label">Escolha um Agendamento</label>
                <select id="id_agendamento" name="id_agendamento" class="form-select" required onchange="mostrarEspaco()">
                    <option value="">Selecione um agendamento</option>
                    <?php
                    if (count($agendamentos) > 0) {
                        // Exibe todos os agendamentos encontrados
                        foreach ($agendamentos as $agendamento) {
                            echo "<option value='{$agendamento['agendamento_id']}'>";
                            echo "Agendamento ID " . $agendamento['agendamento_id'] . " - " . $agendamento['espaco_nome'] . " - " . date('d/m/Y H:i', strtotime($agendamento['data_inicio']));
                            echo "</option>";
                        }
                    } else {
                        echo "<option value=''>Nenhum agendamento encontrado</option>";
                    }
                    ?>
                </select>
            </div>

            <p id="espaco_selecionado" class="mt-2"></p>

            <div class="mb-3">
                <label for="nota" class="form-label">Nota</label>
                <select id="nota" name="nota" class="form-select" required>
                    <option value="">Selecione a nota</option>
                    <option value="1">1 - Muito Ruim</option>
                    <option value="2">2 - Ruim</option>
                    <option value="3">3 - Regular</option>
                    <option value="4">4 - Bom</option>
                    <option value="5">5 - Excelente</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="comentario" class="form-label">Comentário (opcional)</label>
                <textarea id="comentario" name="comentario" class="form-control" rows="4"></textarea>
            </div>

            <button type="submit" name="avaliar" class="btn btn-primary">Enviar Avaliação</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include('footer.php'); ?>
</html>
