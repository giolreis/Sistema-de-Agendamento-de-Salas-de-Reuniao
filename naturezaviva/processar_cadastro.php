<?php
// Conexão com o banco de dados
$host = "localhost";
$user = "root";
$password = "senha"; // Alterar conforme a senha do seu MySQL
$dbname = "NaturezaViva";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Verificando se o formulário foi submetido
if (isset($_POST['submit'])) {
    // Pegando os dados do formulário
    $nome = $_POST['nome'];
    $email = $_POST['email'];
    $senha = md5($_POST['senha']); // Usando MD5 para a senha (lembre-se que MD5 não é seguro em produção)
    $cpf = $_POST['cpf'];
    $status = $_POST['status'];

    // Tipo de usuário é sempre 'usuario' para novos cadastros
    $tipo_usuario = 'usuario'; 

    // Verificando se o e-mail já existe no banco
    $sql_check = "SELECT * FROM usuarios WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Se o e-mail já existir, exibe um erro
        echo "<h3>Este e-mail já está cadastrado!</h3>";
    } else {
        // Caso contrário, insere o novo usuário no banco de dados
        $sql_insert = "INSERT INTO usuarios (nome, email, senha, cpf, tipo_usuario, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("ssssss", $nome, $email, $senha, $cpf, $tipo_usuario, $status);

        if ($stmt_insert->execute()) {
            // Exibe a mensagem flutuante
            echo "
            <div id='message' style='
                position: fixed;
                top: 20px;
                left: 50%;
                transform: translateX(-50%);
                background-color: #4CAF50;
                color: white;
                padding: 15px;
                border-radius: 5px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                z-index: 9999;
                font-size: 18px;
                opacity: 1;
                transition: opacity 1s ease;
            '>USUÁRIO CADASTRADO COM SUCESSO</div>";

            // Redireciona para a página de login após 10 segundos (aguardando a mensagem aparecer)
            echo "<script>
                    setTimeout(function() {
                        window.location.href = 'login.php';
                    }, 10000); // 10000ms = 10 segundos
                  </script>";
        } else {
            echo "<h3>Erro ao cadastrar usuário: " . $stmt_insert->error . "</h3>";
        }
    }
    // Fechando a conexão
    $stmt_check->close();
    $stmt_insert->close();
    $conn->close();
}
?>
