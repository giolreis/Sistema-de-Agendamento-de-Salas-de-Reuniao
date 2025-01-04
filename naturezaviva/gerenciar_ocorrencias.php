<?php
// Verifica se a sessão está ativa antes de iniciar
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

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

// Inclui o Dompdf
require_once 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Função para exportar em CSV
if (isset($_POST['export_csv'])) {
    // Obtém as ocorrências para exportação
    $sql = "
        SELECT 
            o.id AS ocorrencia_id,
            u.nome AS usuario_nome,
            e.nome AS espaco_nome,
            o.descricao,
            o.status,
            o.data_criacao
        FROM ocorrencias o
        JOIN agendamentos ag ON o.id_agendamento = ag.id
        JOIN usuarios u ON ag.id_usuario = u.id
        JOIN espacos e ON ag.id_espaco = e.id
        ORDER BY o.data_criacao DESC;
    ";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        // Definir os headers para exportação CSV
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="ocorrencias.csv"');
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID Ocorrência', 'Usuário', 'Espaço', 'Descrição', 'Status', 'Data de Criação']);
        
        while ($row = $result->fetch_assoc()) {
            fputcsv($output, [
                $row['ocorrencia_id'],
                $row['usuario_nome'],
                $row['espaco_nome'],
                $row['descricao'],
                $row['status'],
                $row['data_criacao']
            ]);
        }
        fclose($output);
        exit;
    }
}

// Função para exportar em PDF
if (isset($_POST['export_pdf'])) {
    // Obtém as ocorrências para exportação
    $sql = "
        SELECT 
            o.id AS ocorrencia_id,
            u.nome AS usuario_nome,
            e.nome AS espaco_nome,
            o.descricao,
            o.status,
            o.data_criacao
        FROM ocorrencias o
        JOIN agendamentos ag ON o.id_agendamento = ag.id
        JOIN usuarios u ON ag.id_usuario = u.id
        JOIN espacos e ON ag.id_espaco = e.id
        ORDER BY o.data_criacao DESC;
    ";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        // Cria uma instância do Dompdf
        $dompdf = new Dompdf();
        
        // Inicia o conteúdo HTML
        $html = '<h2>Relatório de Ocorrências</h2>';
        $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th>ID Ocorrência</th>
                            <th>Usuário</th>
                            <th>Espaço</th>
                            <th>Descrição</th>
                            <th>Status</th>
                            <th>Data de Criação</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($row['ocorrencia_id']) . '</td>
                        <td>' . htmlspecialchars($row['usuario_nome']) . '</td>
                        <td>' . htmlspecialchars($row['espaco_nome']) . '</td>
                        <td>' . htmlspecialchars($row['descricao']) . '</td>
                        <td>' . htmlspecialchars($row['status']) . '</td>
                        <td>' . htmlspecialchars($row['data_criacao']) . '</td>
                    </tr>';
        }
        
        $html .= '</tbody></table>';
        
        // Carrega o HTML no Dompdf
        $dompdf->loadHtml($html);
        
        // Define o tamanho do papel
        $dompdf->setPaper('A4', 'landscape');
        
        // Renderiza o PDF
        $dompdf->render();
        
        // Envia o PDF para o navegador
        $dompdf->stream("ocorrencias.pdf", array("Attachment" => 0));
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Ocorrências - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container my-5">
    <h2 class="text-center text-danger mb-4">Gerenciar Ocorrências</h2>

    <!-- Filtros -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="usuario">Usuário</label>
                <select name="usuario" id="usuario" class="form-control">
                    <option value="">Todos</option>
                    <?php
                    // Carrega usuários para filtro
                    $sql = "SELECT id, nome FROM usuarios ORDER BY nome";
                    $result = $conn->query($sql);
                    while ($user = $result->fetch_assoc()) {
                        $selected = isset($_GET['usuario']) && $_GET['usuario'] == $user['id'] ? 'selected' : '';
                        echo "<option value='" . $user['id'] . "' $selected>" . htmlspecialchars($user['nome']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="espaco">Espaço</label>
                <select name="espaco" id="espaco" class="form-control">
                    <option value="">Todos</option>
                    <?php
                    // Carrega espaços para filtro
                    $sql = "SELECT id, nome FROM espacos ORDER BY nome";
                    $result = $conn->query($sql);
                    while ($espaco = $result->fetch_assoc()) {
                        $selected = isset($_GET['espaco']) && $_GET['espaco'] == $espaco['id'] ? 'selected' : '';
                        echo "<option value='" . $espaco['id'] . "' $selected>" . htmlspecialchars($espaco['nome']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label for="status">Status</label>
                <select name="status" id="status" class="form-control">
                    <option value="">Todos</option>
                    <option value="pendente" <?php echo isset($_GET['status']) && $_GET['status'] == 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="concluida" <?php echo isset($_GET['status']) && $_GET['status'] == 'concluida' ? 'selected' : ''; ?>>Concluída</option>
                    <option value="em_andamento" <?php echo isset($_GET['status']) && $_GET['status'] == 'em_andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Exportação -->
    <form method="POST" class="mb-4">
        <button type="submit" name="export_csv" class="btn btn-success">Exportar para CSV</button>
        <button type="submit" name="export_pdf" class="btn btn-danger">Exportar para PDF</button>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="table-danger text-center">
                    <th>ID Ocorrência</th>
                    <th>Usuário</th>
                    <th>Espaço</th>
                    <th>Descrição</th>
                    <th>Status</th>
                    <th>Data de Criação</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Obtém as ocorrências
                $sql = "
                    SELECT 
                        o.id AS ocorrencia_id,
                        u.nome AS usuario_nome,
                        e.nome AS espaco_nome,
                        o.descricao,
                        o.status,
                        o.data_criacao
                    FROM ocorrencias o
                    JOIN agendamentos ag ON o.id_agendamento = ag.id
                    JOIN usuarios u ON ag.id_usuario = u.id
                    JOIN espacos e ON ag.id_espaco = e.id
                    ORDER BY o.data_criacao DESC
                ";
                $result = $conn->query($sql);
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['ocorrencia_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['usuario_nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['espaco_nome']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['descricao']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['data_criacao']) . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

</body>
</html>
