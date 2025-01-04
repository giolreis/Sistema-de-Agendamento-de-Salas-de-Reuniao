<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado e se é o primeiro acesso
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['primeiro_acesso'])) {
    header("Location: login.php");
    exit;
}

// Incluindo o arquivo de conexão com o banco de dados
include('db_connection.php');

// Tratando o envio do formulário de alteração de senha
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nova_senha = $_POST['nova_senha'];
    $confirmar_senha = $_POST['confirmar_senha'];

    // Verifica se as senhas coincidem
    if ($nova_senha === $confirmar_senha) {
        // Atualiza a senha no banco de dados
        $usuario_id = $_SESSION['usuario_id'];
        $sql = "UPDATE usuarios SET senha = MD5(?), primeiro_login = 'nao' WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $nova_senha, $usuario_id);

        if ($stmt->execute()) {
            // Redireciona para a página inicial após sucesso
            unset($_SESSION['primeiro_acesso']); // Remove a flag
            $_SESSION['senha_alterada_sucesso'] = "Senha alterada com sucesso!";
            header("Location: index.php");
            exit;
        } else {
            $erro = "Erro ao atualizar a senha. Tente novamente.";
        }
    } else {
        $erro = "As senhas não coincidem.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h2 class="text-center text-success mb-4">Alterar Senha</h2>

        <!-- Exibe mensagem de erro ou sucesso -->
        <?php if (isset($erro)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <form action="alterar_senha.php" method="POST">
            <div class="mb-3">
                <label for="nova_senha" class="form-label">Nova Senha</label>
                <input type="password" class="form-control" id="nova_senha" name="nova_senha" placeholder="Digite sua nova senha" required>
            </div>
            <div class="mb-3">
                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" placeholder="Confirme sua nova senha" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Alterar Senha</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
