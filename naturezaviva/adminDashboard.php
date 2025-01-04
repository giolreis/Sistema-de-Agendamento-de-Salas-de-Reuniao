<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: login.php");
    exit;
}

// Verifica se o usuário tem permissão de administrador
if ($_SESSION['usuario_tipo'] !== 'admin') {
    // Se o usuário não for admin, redireciona para a página principal
    header("Location: index.php");
    exit;
}

// Incluindo a conexão com o banco de dados
include('db_connection.php');  // Certifique-se de que este arquivo está correto

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
    <title>Painel Administrativo - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <?php include('navbar.php'); ?>

    <div class="container my-5">
        <h2 class="text-center text-success mb-4">Bem-vindo, <?php echo htmlspecialchars($nome_usuario); ?>!</h2>
        

        <div class="row">
            <div class="col-md-12">
                <h4 class="text-primary">Área de Administração</h4>
                <p>Conteúdo exclusivo para administradores.</p>

                <!-- Seção para gerenciar espaços -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Gerenciar Espaços</h5>
                        <p class="card-text">Aqui você pode adicionar, editar e visualizar os espaços disponíveis para aluguel.</p>
                        <a href="gerenciar_espacos.php" class="btn btn-primary">Gerenciar Espaços</a>
                    </div>
                </div>

                <!-- Seção para gerenciar agendamentos -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Gerenciar Agendamentos</h5>
                        <p class="card-text">Aqui você pode visualizar os agendamentos e alterar o status.</p>
                        <a href="gerenciar_agendamentos.php" class="btn btn-primary">Gerenciar Agendamentos</a>
                    </div>
                </div>

                <!-- Seção para gerenciar avaliações -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Gerenciar Avaliações</h5>
                        <p class="card-text">Aqui você pode verificar as avaliações feitas pelos usuários e tomar as ações necessárias.</p>
                        <a href="gerenciar_avaliacoes.php" class="btn btn-primary">Gerenciar Avaliações</a>
                    </div>
                </div>

                <!-- Seção para visualizar ocorrências -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Ocorrências</h5>
                        <p class="card-text">Aqui você pode visualizar as ocorrências registradas no sistema e tomar as ações apropriadas.</p>
                        <a href="gerenciar_ocorrencias.php" class="btn btn-primary">Visualizar Ocorrências</a>
                    </div>
                </div>

                <!-- Seção para visualizar ocorrências -->
                <div class="card mb-3">
                    <div class="card-body">
                        <h5 class="card-title">Usuários</h5>
                        <p class="card-text">Aqui você pode visualizar usuários registrados no sistema e tomar as ações necessárias.</p>
                        <a href="gerenciar_usuarios.php" class="btn btn-primary">Visualizar usuarios</a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
<?php include('footer.php'); ?>
</html>
