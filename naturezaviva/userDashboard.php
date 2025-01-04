<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: login.php");
    exit;
}

// Verifica se o usuário tem permissão de 'usuario'
if ($_SESSION['usuario_tipo'] !== 'usuario') {
    // Se o usuário não for 'usuario', redireciona para a página principal
    header("Location: index.php");
    exit;
}

// Incluindo a conexão com o banco e funções
include('db_connection.php');
include('functions.php');

// Consulta para pegar o nome do usuário
$usuario_id = $_SESSION['usuario_id']; // Pegando o ID do usuário da sessão
$query = "SELECT nome FROM usuarios WHERE id = ?";  // A consulta que irá pegar o nome do usuário

// Prepara e executa a consulta usando a conexão mysqli
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $usuario_id);  // "i" para tipo inteiro
$stmt->execute();
$stmt->bind_result($nome_usuario);  // Armazena o resultado na variável $nome_usuario

// Verifica se o nome foi encontrado
if (!$stmt->fetch()) {
    $nome_usuario = 'Usuário'; // Caso não encontre o usuário
}

$stmt->close();  // Fecha a declaração
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Usuário - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <?php include('navbar.php'); ?>

    <div class="container my-5">
        <h2 class="text-center text-success mb-4">Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>!</h2>

        <div class="row">
            <div class="col-md-12">
                <h4 class="text-primary">Área de Usuário</h4>
                <p>Conteúdo exclusivo para usuários.</p>

                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Sala de Reunião</h5>
                        <p class="card-text">Aqui você pode agendar a sala de reunião que precisa.</p>
                        <a href="gerar_agendamentos.php" class="btn btn-primary">Agendar Reunião</a>
                    </div>
                </div>

                <!-- Seção para gerenciar avaliações -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Fazer uma Avaliação</h5>
                        <p class="card-text">Aqui você pode fazer uma avaliação das salas de reuniões.</p>
                        <a href="gerar_avaliacao.php" class="btn btn-primary">Fazer Avaliação</a>
                    </div>
                </div>

                <!-- Seção para visualizar ocorrências -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Registrar Ocorrências</h5>
                        <p class="card-text">Aqui você pode registrar ocorrências.</p>
                        <a href="gerar_ocorrencia.php" class="btn btn-primary">Registrar Ocorrências</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
<?php include('footer.php'); ?>
</html>
