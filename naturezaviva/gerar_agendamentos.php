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

// Verifica se foi enviado um formulário para agendar
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['agendar'])) {
    $id_espaco = $_POST['id_espaco'];
    $data_inicio = $_POST['data_inicio'];
    $data_fim = $_POST['data_fim'];

    // Verifica se já existe um agendamento para o mesmo espaço e horário
    $query_verifica = "
        SELECT COUNT(*) AS count
        FROM agendamentos
        WHERE id_espaco = ? AND (
            (? BETWEEN data_inicio AND data_fim) OR  -- Verifica se a data de início do novo agendamento está entre o início e o fim de um agendamento existente
            (? BETWEEN data_inicio AND data_fim) OR  -- Verifica se a data de fim do novo agendamento está entre o início e o fim de um agendamento existente
            (data_inicio BETWEEN ? AND ?)            -- Verifica se o agendamento existente começa durante o intervalo do novo agendamento
        ) AND status = 'pendente'
    ";
    $stmt = $conn->prepare($query_verifica);
    $stmt->bind_param("issss", $id_espaco, $data_inicio, $data_fim, $data_inicio, $data_fim);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        $message = "Data e Horário indisponíveis. Por favor, tente outra.";
    } else {
        // Realiza o agendamento
        $query_agendar = "
            INSERT INTO agendamentos (id_usuario, id_espaco, data_inicio, data_fim, status)
            VALUES (?, ?, ?, ?, 'pendente')
        ";
        $stmt = $conn->prepare($query_agendar);
        $stmt->bind_param("iiss", $user_id, $id_espaco, $data_inicio, $data_fim);
        $stmt->execute();
        $message = "Reunião agendada com sucesso! Aguardando confirmação.";
    }
}

// Obtém a lista de espaços disponíveis
$query_espacos = "SELECT * FROM espacos WHERE status = 'disponivel'";
$result = $conn->query($query_espacos);
$espacos = $result->fetch_all(MYSQLI_ASSOC);

// Obtém as solicitações do usuário
$query_solicitacoes = "
    SELECT ag.id, ag.data_inicio, ag.data_fim, esp.nome AS espaco_nome, ag.status
    FROM agendamentos ag
    JOIN espacos esp ON ag.id_espaco = esp.id
    WHERE ag.id_usuario = ?
    ORDER BY ag.data_inicio ASC
";
$stmt = $conn->prepare($query_solicitacoes);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$solicitacoes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Solicitações - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\index.css">
    <script>
        // Função para exibir a descrição e capacidade do espaço ao selecionar
        function carregarDescricao() {
            const espacos = <?= json_encode($espacos); ?>;
            const espacoId = document.getElementById('id_espaco').value;
            const descricaoDiv = document.getElementById('descricao_espaco');
            const capacidadeDiv = document.getElementById('capacidade_espaco');

            const espaco = espacos.find(e => e.id == espacoId);
            if (espaco) {
                descricaoDiv.textContent = espaco.descricao || "Sem descrição disponível.";
                capacidadeDiv.textContent = "Capacidade: " + (espaco.capacidade || "N/A") + " pessoas.";
                descricaoDiv.style.display = 'block';
                capacidadeDiv.style.display = 'block';
            } else {
                descricaoDiv.textContent = '';
                capacidadeDiv.textContent = '';
                descricaoDiv.style.display = 'none';
                capacidadeDiv.style.display = 'none';
            }
        }
    </script>
</head>
<body>
    <div class="container my-5">
        <h2>Minhas Solicitações</h2>

        <!-- Mensagem de sucesso ou erro -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário para agendar reunião -->
        <h3>Agendar Reunião</h3>
        <form action="gerar_agendamentos.php" method="POST" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="id_espaco" class="form-label">Espaço</label>
                    <select id="id_espaco" name="id_espaco" class="form-select" required onchange="carregarDescricao()">
                        <option value="">Selecione</option>
                        <?php foreach ($espacos as $espaco): ?>
                            <option value="<?= $espaco['id']; ?>"><?= $espaco['nome']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <p id="descricao_espaco" style="display: none; margin-top: 1.5rem;"></p>
                    <p id="capacidade_espaco" style="display: none;"></p>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="data_inicio" class="form-label">Data e Hora de Início</label>
                    <input type="datetime-local" id="data_inicio" name="data_inicio" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="data_fim" class="form-label">Data e Hora de Fim</label>
                    <input type="datetime-local" id="data_fim" name="data_fim" class="form-control" required>
                </div>
            </div>
            <div class="mt-3">
                <button type="submit" name="agendar" class="btn btn-primary">Agendar</button>
            </div>
        </form>

        <!-- Tabela de Solicitações -->
        <h3>Solicitações Realizadas</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th> <!-- Nova coluna para o ID do agendamento -->
                    <th>Espaço</th>
                    <th>Data Início</th>
                    <th>Data Fim</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitacoes as $solicitacao): ?>
                    <tr>
                        <td><?= $solicitacao['id']; ?></td> <!-- Exibe o ID do agendamento -->
                        <td><?= $solicitacao['espaco_nome']; ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($solicitacao['data_inicio'])); ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($solicitacao['data_fim'])); ?></td>
                        <td><?= ucfirst($solicitacao['status']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include('footer.php'); ?>
</html>
