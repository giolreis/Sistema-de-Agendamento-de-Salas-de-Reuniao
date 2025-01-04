<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica se o usuário tem permissão de administrador
if ($_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: index.php");
    exit;
}

// Inclui a conexão com o banco de dados
include('db_connection.php');

// Recebe os parâmetros de filtro
$data_inicio_filtro = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : '';
$data_fim_filtro = isset($_GET['data_fim']) ? $_GET['data_fim'] : '';
$espaco_filtro = isset($_GET['espaco']) ? $_GET['espaco'] : '';

// Atualiza o status do agendamento, se necessário
if (isset($_POST['atualizar_status'])) {
    $id_agendamento = $_POST['id_agendamento'];
    $novo_status = $_POST['status'];

    // Se o status for "confirmado", verifica a disponibilidade do espaço
    if ($novo_status === 'confirmado') {
        $sql_verificar = "
        SELECT COUNT(*) 
        FROM agendamentos
        WHERE id_espaco = (
            SELECT id_espaco FROM agendamentos WHERE id = ?
        )
        AND id != ? -- Ignora o próprio agendamento
        AND status IN ('pendente', 'confirmado')
        AND (
            (data_inicio BETWEEN (SELECT data_inicio FROM agendamentos WHERE id = ?) AND DATE_ADD((SELECT data_fim FROM agendamentos WHERE id = ?), INTERVAL 1 HOUR))
            OR
            (data_fim BETWEEN (SELECT data_inicio FROM agendamentos WHERE id = ?) AND DATE_ADD((SELECT data_fim FROM agendamentos WHERE id = ?), INTERVAL 1 HOUR))
            OR
            (? BETWEEN data_inicio AND DATE_ADD(data_fim, INTERVAL 1 HOUR))
            OR
            (? BETWEEN data_inicio AND DATE_ADD(data_fim, INTERVAL 1 HOUR))
        )
        ";

        $stmt_verificar = $conn->prepare($sql_verificar);
        $stmt_verificar->bind_param("iiiiiiii", $id_agendamento, $id_agendamento, $id_agendamento, $id_agendamento, $id_agendamento, $id_agendamento, $id_agendamento, $id_agendamento);
        $stmt_verificar->execute();
        $stmt_verificar->bind_result($count);
        $stmt_verificar->fetch();
        $stmt_verificar->close();

        if ($count > 0) {
            $mensagem = "Este espaço já está reservado para o período selecionado.";
        } else {
            // Atualiza o status para "confirmado"
            $sql = "UPDATE agendamentos SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $novo_status, $id_agendamento);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $mensagem = "Status atualizado com sucesso!";
            } else {
                $mensagem = "Erro ao atualizar o status.";
            }
        }
    } else {
        // Atualiza o status normalmente
        $sql = "UPDATE agendamentos SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $novo_status, $id_agendamento);
        $stmt->execute();

        if ($stmt->affected_rows > 0) {
            $mensagem = "Status atualizado com sucesso!";
        } else {
            $mensagem = "Erro ao atualizar o status.";
        }
    }
}

// Consulta base
$sql = "
    SELECT a.id, u.nome AS usuario, e.nome AS espaco, a.data_inicio, a.data_fim, a.status
    FROM agendamentos a
    JOIN usuarios u ON a.id_usuario = u.id
    JOIN espacos e ON a.id_espaco = e.id
    WHERE 1=1
";

// Adiciona filtros se fornecidos
if ($data_inicio_filtro) {
    $sql .= " AND a.data_inicio >= '{$data_inicio_filtro} 00:00:00'";
}
if ($data_fim_filtro) {
    $sql .= " AND a.data_fim <= '{$data_fim_filtro} 23:59:59'";
}
if ($espaco_filtro) {
    $sql .= " AND a.id_espaco = $espaco_filtro";
}

$sql .= " ORDER BY a.data_criacao DESC";

// Executa a consulta
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Agendamentos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container my-5">
        <h2 class="text-center text-primary mb-4">Gerenciar Agendamentos</h2>

        <!-- Filtro de agendamentos -->
        <div class="mb-4">
            <form method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <label for="data_inicio" class="form-label">Data Início</label>
                        <input type="date" name="data_inicio" class="form-control" value="<?= $data_inicio_filtro ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="data_fim" class="form-label">Data Fim</label>
                        <input type="date" name="data_fim" class="form-control" value="<?= $data_fim_filtro ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="espaco" class="form-label">Espaço</label>
                        <select name="espaco" class="form-select">
                            <option value="">Selecione um espaço</option>
                            <?php
                            $espacos_sql = "SELECT id, nome FROM espacos";
                            $espacos_result = $conn->query($espacos_sql);
                            while ($espaco = $espacos_result->fetch_assoc()) {
                                $selected = ($espaco_filtro == $espaco['id']) ? 'selected' : '';
                                echo "<option value=\"{$espaco['id']}\" $selected>{$espaco['nome']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Filtrar</button>
            </form>
        </div>

        <!-- Mensagem de sucesso ou erro -->
        <?php if (isset($mensagem)) { ?>
            <div class="alert alert-info"><?= $mensagem ?></div>
        <?php } ?>

        <!-- Tabela de agendamentos -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Usuário</th>
                    <th>Espaço</th>
                    <th>Data Início</th>
                    <th>Data Fim</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= $row['usuario'] ?></td>
                        <td><?= $row['espaco'] ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['data_inicio'])) ?></td>
                        <td><?= date('d/m/Y H:i', strtotime($row['data_fim'])) ?></td>
                        <td><?= ucfirst($row['status']) ?></td>
                        <td>
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="id_agendamento" value="<?= $row['id'] ?>">
                                <select name="status" class="form-select d-inline w-auto">
                                    <option value="pendente" <?= $row['status'] === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                    <option value="confirmado" <?= $row['status'] === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                                    <option value="cancelado" <?= $row['status'] === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                                </select>
                                <button type="submit" name="atualizar_status" class="btn btn-sm btn-primary">Atualizar</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
<?php include('footer.php'); ?>
</html>
