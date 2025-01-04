<?php
// Função para alterar o tipo de usuário
function alterarTipoUsuario($conn, $usuario_id) {
    $sql = "UPDATE usuarios SET tipo_usuario = 'admin' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $usuario_id);
    return $stmt->execute();
}

// Função para alterar a senha de um usuário
function alterarSenha($conn, $usuario_id, $nova_senha) {
    $senha_hash = md5($nova_senha);  // Usando MD5 conforme você preferiu
    $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $senha_hash, $usuario_id);
    return $stmt->execute();
}

// Função para adicionar um novo espaço
function adicionarEspaco($conn, $nome, $descricao, $capacidade, $preco) {
    $sql = "INSERT INTO espacos (nome, descricao, capacidade, preco) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $nome, $descricao, $capacidade, $preco);
    return $stmt->execute();
}

// Função para atualizar um espaço
function atualizarEspaco($conn, $espaco_id, $nome, $descricao, $capacidade, $preco, $status) {
    $sql = "UPDATE espacos SET nome = ?, descricao = ?, capacidade = ?, preco = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssiiis", $nome, $descricao, $capacidade, $preco, $status, $espaco_id);
    return $stmt->execute();
}

// Função para alterar o status de um agendamento
function alterarStatusAgendamento($conn, $agendamento_id, $novo_status) {
    $sql = "UPDATE agendamentos SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $novo_status, $agendamento_id);
    return $stmt->execute();
}
?>
