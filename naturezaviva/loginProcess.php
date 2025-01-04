<?php
session_start(); // Inicia a sessão

// Verifica se o usuário já está logado e redireciona para a página principal (index.php)
if (isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Incluindo o arquivo de conexão com o banco de dados
include('db_connection.php');

// Verificando se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtendo os dados do formulário
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Verificando se os campos foram preenchidos
    if (!empty($username) && !empty($password)) {
        // Preparando a consulta para verificar o usuário e a senha no banco de dados
        $sql = "SELECT * FROM usuarios WHERE email = ? AND senha = MD5(?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $username, $password);
        $stmt->execute();
        $result = $stmt->get_result();

        // Se o usuário for encontrado
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            $_SESSION['usuario_id'] = $user['id'];
            $_SESSION['usuario_nome'] = $user['nome'];
            $_SESSION['usuario_tipo'] = $user['tipo_usuario'];

            // Verifica se é o primeiro login ou se a senha é padrão, mas exclui o administrador
            if (($user['primeiro_login'] == 'sim' || $user['senha'] === md5('123456')) && $user['tipo_usuario'] !== 'admin') {
                // Redireciona para a página de alteração de senha
                $_SESSION['primeiro_acesso'] = true; // Define uma flag para exibir uma mensagem no alterar_senha.php
                header("Location: alterar_senha.php");
                exit;
            }

            // Verifica o tipo de usuário
            if ($user['tipo_usuario'] === 'admin') {
                // Redireciona o administrador para o dashboard ou página de admin
                header("Location: adminDashboard.php"); // Altere para a página correta de admin
                exit;
            } else {
                // Redireciona o usuário comum para a página principal
                header("Location: userDashboard.php");
                exit;
            }
        } else {
            // Se não encontrar o usuário, exibe uma mensagem de erro
            $_SESSION['login_error'] = "Usuário ou senha inválidos!";
        }
    } else {
        // Mensagem de erro se os campos estiverem vazios
        $_SESSION['login_error'] = "Por favor, preencha todos os campos!";
    }
}

// Fecha a conexão com o banco de dados
$conn->close();
?>
