<?php
// Inicia a sessão e verifica permissões
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_tipo'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Inclui a conexão com o banco de dados
include('db_connection.php');

// Variáveis para mensagens de feedback
$mensagem = "";
$tipo_mensagem = "";

// Adicionar ou editar espaço
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome_espaco']);
    $descricao = trim($_POST['descricao']);
    $capacidade = intval($_POST['capacidade']);
    $status = $_POST['status'];

    // Garantir que o status não seja vazio
    if (empty($status)) {
        $mensagem = "Por favor, selecione o status!";
        $tipo_mensagem = "warning";
    } else {
        if (!empty($nome) && !empty($descricao) && $capacidade > 0) {
            if (isset($_POST['id_espaco']) && !empty($_POST['id_espaco'])) {
                // Atualizar espaço existente
                $id_espaco = intval($_POST['id_espaco']);
                $sql = "UPDATE espacos SET nome = ?, descricao = ?, capacidade = ?, status = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $nome, $descricao, $capacidade, $status, $id_espaco);

                if ($stmt->execute()) {
                    $mensagem = "Espaço atualizado com sucesso!";
                    $tipo_mensagem = "success";
                } else {
                    $mensagem = "Erro ao atualizar espaço: " . $conn->error;
                    $tipo_mensagem = "danger";
                }
                $stmt->close();
            } else {
                // Inserir novo espaço
                $sql = "INSERT INTO espacos (nome, descricao, capacidade, status) VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssi", $nome, $descricao, $capacidade, $status);

                if ($stmt->execute()) {
                    $mensagem = "Espaço adicionado com sucesso!";
                    $tipo_mensagem = "success";
                } else {
                    $mensagem = "Erro ao adicionar espaço: " . $conn->error;
                    $tipo_mensagem = "danger";
                }
                $stmt->close();
            }
        } else {
            $mensagem = "Por favor, preencha todos os campos corretamente!";
            $tipo_mensagem = "warning";
        }
    }
}

// Deletar espaço
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id_espaco = intval($_GET['delete']);
    $sql = "DELETE FROM espacos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_espaco);

    if ($stmt->execute()) {
        $mensagem = "Espaço excluído com sucesso!";
        $tipo_mensagem = "success";
    } else {
        $mensagem = "Erro ao excluir espaço: " . $conn->error;
        $tipo_mensagem = "danger";
    }
    $stmt->close();
}

// Verificar se existe um espaço para editar
$espaco_editar = null;
if (isset($_GET['editar']) && !empty($_GET['editar'])) {
    $id_espaco = intval($_GET['editar']);
    $sql = "SELECT * FROM espacos WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_espaco);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $espaco_editar = $result->fetch_assoc();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Espaços</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\index.css">
</head>
<body>
    <?php include('navbar.php'); ?>

    <div class="container my-5">
        <h2 class="text-center text-success mb-4">Gerenciar Espaços</h2>
        
        <?php if (!empty($mensagem)): ?>
            <div class="alert alert-<?php echo $tipo_mensagem; ?>" role="alert">
                <?php echo $mensagem; ?>
            </div>
        <?php endif; ?>

        <!-- Formulário para adicionar/editar espaço -->
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">Adicionar/Editar Espaço</div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="id_espaco" id="id_espaco" value="<?php echo $espaco_editar['id'] ?? ''; ?>">
                    <div class="mb-3">
                        <label for="nome_espaco" class="form-label">Nome do Espaço</label>
                        <input type="text" class="form-control" id="nome_espaco" name="nome_espaco" required value="<?php echo $espaco_editar['nome'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="descricao" class="form-label">Descrição</label>
                        <textarea class="form-control" id="descricao" name="descricao" rows="3" required><?php echo $espaco_editar['descricao'] ?? ''; ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="capacidade" class="form-label">Capacidade</label>
                        <input type="number" class="form-control" id="capacidade" name="capacidade" min="1" required value="<?php echo $espaco_editar['capacidade'] ?? ''; ?>">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="disponivel" <?php echo (isset($espaco_editar['status']) && $espaco_editar['status'] == 'disponivel') ? 'selected' : ''; ?>>Disponível</option>
                            <option value="indisponivel" <?php echo (isset($espaco_editar['status']) && $espaco_editar['status'] == 'indisponivel') ? 'selected' : ''; ?>>Indisponível</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">Salvar Espaço</button>
                </form>
            </div>
        </div>

        <!-- Lista de espaços -->
        <div>
            <h4>Espaços Cadastrados</h4>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Capacidade</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Exibir todos os espaços cadastrados (disponível ou indisponível)
                    $sql = "SELECT * FROM espacos ORDER BY nome ASC";
                    $result = $conn->query($sql);

                    if ($result && $result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                            // Traduzindo o status para "Disponível" ou "Indisponível"
                            $status_text = ($row['status'] === 'disponivel') ? 'Disponível' : 'Indisponível';
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nome']); ?></td>
                                <td><?php echo htmlspecialchars($row['descricao']); ?></td>
                                <td><?php echo $row['capacidade']; ?></td>
                                <td><?php echo $status_text; ?></td> <!-- Exibe o status traduzido -->
                                <td>
                                    <a href="?editar=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                                    <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza de que deseja excluir?')">Excluir</a>
                                </td>
                            </tr>
                            <?php
                        endwhile;
                    else:
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">Nenhum espaço cadastrado</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
