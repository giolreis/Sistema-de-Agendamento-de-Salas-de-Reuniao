<?php
session_start();
include('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];
    $cpf = $_POST['cpf'];

    // Verifica se o email já existe
    $sqlCheckEmail = "SELECT * FROM usuarios WHERE email = ?";
    $stmtCheck = $conn->prepare($sqlCheckEmail);
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows > 0) {
        $_SESSION['erro_cadastro'] = "O e-mail informado já está em uso!";
    } else {
        // Criptografar senha com MD5
        $senhaCriptografada = md5($senha);

        // Inserir usuário no banco
        $sqlInsert = "INSERT INTO usuarios (nome, email, senha, cpf, tipo_usuario) VALUES (?, ?, ?, ?, 'usuario')";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bind_param("ssss", $nome, $email, $senhaCriptografada, $cpf);

        if ($stmtInsert->execute()) {
            $_SESSION['sucesso_cadastro'] = "Usuário criado com sucesso!";
            header('Location: login.php');
            exit();
        } else {
            $_SESSION['erro_cadastro'] = "Erro ao cadastrar o usuário. Tente novamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\cadastro.css">
</head>
<body>
    <?php include('navbar.php'); ?>
    <div class="container my-5">
        <h2 class="text-center text-primary mb-4">Cadastro</h2>

        <!-- Exibe mensagens de erro ou sucesso -->
        <?php if (isset($_SESSION['erro_cadastro'])): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $_SESSION['erro_cadastro']; unset($_SESSION['erro_cadastro']); ?>
            </div>
        <?php elseif (isset($_SESSION['sucesso_cadastro'])): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $_SESSION['sucesso_cadastro']; unset($_SESSION['sucesso_cadastro']); ?>
            </div>
        <?php endif; ?>

        <form action="cadastro.php" method="POST">
            <div class="mb-3">
                <label for="nome" class="form-label">Nome Completo</label>
                <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite seu nome completo" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Digite seu e-mail" required>
            </div>
            <div class="mb-3">
                <label for="cpf" class="form-label">CPF</label>
                <input type="text" class="form-control" id="cpf" name="cpf" placeholder="Digite seu CPF" maxlength="14" required>
            </div>
            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input type="password" class="form-control" id="senha" name="senha" placeholder="Crie uma senha" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Cadastrar</button>
        </form>

        <div class="mt-3 text-center">
            <p>Já tem uma conta? <a href="login.php">Faça Login</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include('footer.php'); ?>
</html>
