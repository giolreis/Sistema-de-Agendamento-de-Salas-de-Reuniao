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
    // Consulta para recuperar os agendamentos do usuário
    $query = "
        SELECT ag.id AS agendamento_id, esp.id AS espaco_id, esp.nome AS espaco_nome, 
               ag.data_inicio, ag.data_fim
        FROM agendamentos ag
        JOIN espacos esp ON ag.id_espaco = esp.id
        WHERE ag.id_usuario = ? AND ag.status IN ('pendente', 'confirmado', 'finalizado')
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

// Processa o envio da ocorrência
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['gerar_ocorrencia'])) {
    $id_agendamento = $_POST['id_agendamento'];
    $descricao = $_POST['descricao'];

    // Valida a descrição
    if (empty($descricao)) {
        $message = "A descrição da ocorrência não pode estar vazia.";
    } else {
        // Insere a ocorrência no banco de dados
        $query_ocorrencia = "
            INSERT INTO ocorrencias (id_agendamento, descricao)
            VALUES (?, ?)
        ";
        $stmt = $conn->prepare($query_ocorrencia);
        $stmt->bind_param("is", $id_agendamento, $descricao);
        $stmt->execute();
        $message = "Ocorrência registrada com sucesso!";
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
    <title>Gerar Ocorrência - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\index.css">
</head>
<body>
    <div class="container my-5">
        <h2>Gerar Ocorrência para Agendamento</h2>

        <!-- Mensagem de sucesso ou erro -->
        <?php if (isset($message)): ?>
            <div class="alert alert-info">
                <?= $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário para gerar ocorrência -->
        <form action="gerar_ocorrencia.php" method="POST">
            <div class="mb-3">
                <label for="id_agendamento" class="form-label">Escolha um Agendamento</label>
                <select id="id_agendamento" name="id_agendamento" class="form-select" required>
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

            <div class="mb-3">
                <label for="descricao" class="form-label">Descrição da Ocorrência</label>
                <textarea id="descricao" name="descricao" class="form-control" rows="4" required></textarea>
            </div>

            <button type="submit" name="gerar_ocorrencia" class="btn btn-primary">Registrar Ocorrência</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include('footer.php'); ?>
</html>
