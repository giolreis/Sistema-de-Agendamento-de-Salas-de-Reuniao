<?php
session_start(); // Inicia a sessão

// Verifica se o usuário já está logado e redireciona para a página principal (index.php)
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Incluindo o arquivo de conexão com o banco de dados
include('db_connection.php');

// Exibe a mensagem de erro, se houver
$login_error = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : null;
unset($_SESSION['login_error']); // Limpa a mensagem de erro da sessão
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login.css"> <!-- Seu estilo personalizado -->
</head>
<body>
    <!-- Navbar -->
    <?php include('navbar.php'); ?>

    <div class="container my-5">
        <h2 class="text-center text-success mb-4">Login</h2>

        <!-- Exibe a mensagem de erro, se houver -->
        <?php if ($login_error): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <!-- Formulário de Login -->
        <form action="loginProcess.php" method="POST">
            <div class="mb-3">
                <label for="username" class="form-label">Usuário (E-mail)</label>
                <input type="email" class="form-control" id="username" name="username" placeholder="Digite seu email" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Entrar</button>
        </form>

        <div class="mt-3 text-center">
            <p>Não tem uma conta? <a href="cadastro.php">Crie uma conta</a></p>
        </div>
    </div>

    <!-- Footer -->
    <?php include('footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
