<?php
session_start();

// Verifica se o usuário está logado e tem permissão de administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include('db_connection.php');

// Lógica para adicionar, editar ou excluir usuários
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        // Adicionar novo usuário
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = md5($_POST['senha']); // Hash da senha (recomendado usar password_hash)
        $tipo_usuario = $_POST['tipo_usuario'];
        $primeiro_login = 'Sim'; // Definido como primeiro acesso

        $sql = "INSERT INTO usuarios (nome, email, senha, tipo_usuario, primeiro_login) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nome, $email, $senha, $tipo_usuario, $primeiro_login);
        $stmt->execute();
    } elseif (isset($_POST['action']) && $_POST['action'] === 'edit') {
        // Editar usuário existente
        $id = $_POST['id'];
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $tipo_usuario = $_POST['tipo_usuario'];

        $sql = "UPDATE usuarios SET nome = ?, email = ?, tipo_usuario = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $nome, $email, $tipo_usuario, $id);
        $stmt->execute();
    } elseif (isset($_POST['action']) && $_POST['action'] === 'delete') {
        // Excluir usuário
        $id = $_POST['id'];

        $sql = "DELETE FROM usuarios WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }
}

// Busca todos os usuários para exibição na tabela
$sql = "SELECT id, nome, email, tipo_usuario, primeiro_login FROM usuarios";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - Natureza Viva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\index.css">
</head>
<body>
    <!-- Navbar -->
    <?php include('navbar.php'); ?>

    <div class="container my-5">
        <h2 class="text-center text-success mb-4">Gerenciar Usuários</h2>

        <!-- Botão para adicionar novo usuário -->
        <div class="mb-3 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">Adicionar Novo Usuário</button>
        </div>

        <!-- Tabela de Usuários -->
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo de Usuário</th>
                    <th>Primeiro Acesso</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['nome']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo htmlspecialchars($user['tipo_usuario']); ?></td>
                        <td><?php echo $user['primeiro_login'] === 'Sim' ? 'Sim' : 'Não'; ?></td>
                        <td>
                            <!-- Botões de Ação -->
                            <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">Editar</button>
                            <form action="gerenciar_usuarios.php" method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Excluir</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Modal de Edição -->
                    <div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="editUserModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <form action="gerenciar_usuarios.php" method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="editUserModalLabel<?php echo $user['id']; ?>">Editar Usuário</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <div class="mb-3">
                                            <label for="nome<?php echo $user['id']; ?>" class="form-label">Nome</label>
                                            <input type="text" class="form-control" id="nome<?php echo $user['id']; ?>" name="nome" value="<?php echo htmlspecialchars($user['nome']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email<?php echo $user['id']; ?>" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email<?php echo $user['id']; ?>" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tipo_usuario<?php echo $user['id']; ?>" class="form-label">Tipo de Usuário</label>
                                            <select class="form-select" id="tipo_usuario<?php echo $user['id']; ?>" name="tipo_usuario" required>
                                                <option value="admin" <?php echo $user['tipo_usuario'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                                                <option value="usuario" <?php echo $user['tipo_usuario'] === 'usuario' ? 'selected' : ''; ?>>Usuário Comum</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-success">Salvar Alterações</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para Adicionar Novo Usuário -->
    <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="gerenciar_usuarios.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Adicionar Novo Usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="mb-3">
                            <label for="nome" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="nome" name="nome" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="senha" class="form-label">Senha</label>
                            <input type="password" class="form-control" id="senha" name="senha" required>
                        </div>
                        <div class="mb-3">
                            <label for="cpf" class="form-label">VPF</label>
                            <input type="number" class="form-control" id="cpf" name="cpf" required>
                        </div>
                        <div class="mb-3">
                            <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
                            <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                <option value="admin">Administrador</option>
                                <option value="usuario">Usuário Comum</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success">Adicionar Usuário</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include('footer.php'); ?>
</html>
