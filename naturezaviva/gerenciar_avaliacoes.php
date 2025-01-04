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

// Verifica se a biblioteca Dompdf foi carregada corretamente
require 'vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Filtra as avaliações por data, se aplicável
$where_clause = "";
if (isset($_POST['data_inicio_filter']) && isset($_POST['data_fim_filter'])) {
    $data_inicio_filter = $_POST['data_inicio_filter'];
    $data_fim_filter = $_POST['data_fim_filter'];
    $where_clause = " WHERE av.data_criacao BETWEEN '$data_inicio_filter' AND '$data_fim_filter' ";
}

// Função para exportar para CSV
if (isset($_GET['exportar_csv'])) {
    // Defina a consulta para pegar as avaliações
    $sql_export = "
        SELECT 
            av.id AS avaliacao_id, 
            u.nome AS usuario_nome, 
            e.nome AS espaco_nome, 
            ag.data_inicio, 
            ag.data_fim, 
            av.nota, 
            av.comentario, 
            av.data_criacao
        FROM avaliacoes av
        JOIN agendamentos ag ON av.id_agendamento = ag.id
        JOIN usuarios u ON ag.id_usuario = u.id
        JOIN espacos e ON ag.id_espaco = e.id
        $where_clause
        ORDER BY av.data_criacao DESC;
    ";
    $result_export = $conn->query($sql_export);
    $filename = "avaliacoes_" . date('Y-m-d_H-i-s') . ".csv";

    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID Avaliação', 'Usuário', 'Espaço', 'Data Início', 'Data Fim', 'Nota', 'Comentário', 'Data de Criação']);
    
    while ($row = $result_export->fetch_assoc()) {
        fputcsv($output, [
            $row['avaliacao_id'],
            $row['usuario_nome'],
            $row['espaco_nome'],
            $row['data_inicio'],
            $row['data_fim'],
            $row['nota'],
            $row['comentario'],
            $row['data_criacao']
        ]);
    }
    
    fclose($output);
    exit;
}

// Função para exportar para PDF (usando Dompdf)
if (isset($_GET['exportar_pdf'])) {
    // Configuração do Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

    $pdf = new Dompdf($options);
    $pdf->set_option('isHtml5ParserEnabled', true);
    
    // Inicia a criação do HTML para o PDF
    $html = '<h2 class="text-center text-success mb-4">Gerenciar Avaliações</h2>';
    $html .= '<table border="1" cellpadding="5" cellspacing="0" style="width:100%">';
    $html .= '<thead><tr>';
    $html .= '<th>ID Avaliação</th>';
    $html .= '<th>Usuário</th>';
    $html .= '<th>Espaço</th>';
    $html .= '<th>Data Início</th>';
    $html .= '<th>Data Fim</th>';
    $html .= '<th>Nota</th>';
    $html .= '<th>Comentário</th>';
    $html .= '<th>Data de Criação</th>';
    $html .= '</tr></thead><tbody>';

    // Consulta para pegar as avaliações
    $sql_pdf = "
        SELECT 
            av.id AS avaliacao_id, 
            u.nome AS usuario_nome, 
            e.nome AS espaco_nome, 
            ag.data_inicio, 
            ag.data_fim, 
            av.nota, 
            av.comentario, 
            av.data_criacao
        FROM avaliacoes av
        JOIN agendamentos ag ON av.id_agendamento = ag.id
        JOIN usuarios u ON ag.id_usuario = u.id
        JOIN espacos e ON ag.id_espaco = e.id
        $where_clause
        ORDER BY av.data_criacao DESC;
    ";
    $result_pdf = $conn->query($sql_pdf);

    // Adiciona os dados das avaliações na tabela do HTML
    while ($row = $result_pdf->fetch_assoc()) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($row['avaliacao_id']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['usuario_nome']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['espaco_nome']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['data_inicio']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['data_fim']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['nota']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['comentario']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['data_criacao']) . '</td>';
        $html .= '</tr>';
    }

    $html .= '</tbody></table>';
    
    // Carrega o HTML no Dompdf
    $pdf->loadHtml($html);
    
    // Renderiza o PDF
    $pdf->render();

    // Envia o PDF para download
    $pdf->stream("avaliacoes_" . date('Y-m-d_H-i-s') . ".pdf", array("Attachment" => 1));
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Avaliações - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\index.css">
</head>
<body>

<?php include('navbar.php'); ?>

<div class="container my-5">
    <h2 class="text-center text-success mb-4">Gerenciar Avaliações</h2>

    <!-- Filtros -->
    <form method="POST" class="mb-4">
        <div class="row">
            <div class="col-md-6">
                <label for="data_inicio_filter" class="form-label">Data Início</label>
                <input type="date" class="form-control" id="data_inicio_filter" name="data_inicio_filter">
            </div>
            <div class="col-md-6">
                <label for="data_fim_filter" class="form-label">Data Fim</label>
                <input type="date" class="form-control" id="data_fim_filter" name="data_fim_filter">
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-3">Filtrar</button>
    </form>

    <!-- Botões de Exportação -->
    <div class="mb-4">
        <a href="?exportar_csv=1" class="btn btn-success">Exportar para CSV</a>
        <a href="?exportar_pdf=1" class="btn btn-danger">Exportar para PDF</a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr class="table-primary text-center">
                    <th>ID Avaliação</th>
                    <th>Usuário</th>
                    <th>Espaço</th>
                    <th>Data Início</th>
                    <th>Data Fim</th>
                    <th>Nota</th>
                    <th>Comentário</th>
                    <th>Data de Criação</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Consulta SQL para obter as avaliações com o filtro de data
                $sql = "
                    SELECT 
                        av.id AS avaliacao_id, 
                        u.nome AS usuario_nome, 
                        e.nome AS espaco_nome, 
                        ag.data_inicio, 
                        ag.data_fim, 
                        av.nota, 
                        av.comentario, 
                        av.data_criacao
                    FROM avaliacoes av
                    JOIN agendamentos ag ON av.id_agendamento = ag.id
                    JOIN usuarios u ON ag.id_usuario = u.id
                    JOIN espacos e ON ag.id_espaco = e.id
                    $where_clause
                    ORDER BY av.data_criacao DESC;
                ";

                $result = $conn->query($sql);
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['avaliacao_id']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['usuario_nome']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['espaco_nome']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['data_inicio']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['data_fim']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['nota']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['comentario']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['data_criacao']) . '</td>';
                        echo '</tr>';
                    }
                } else {
                    echo '<tr><td colspan="8" class="text-center">Nenhuma avaliação encontrada.</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
