-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 04/01/2025 às 06:49
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `naturezaviva`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_espaco` int(11) NOT NULL,
  `data_inicio` datetime NOT NULL,
  `data_fim` datetime NOT NULL,
  `status` enum('pendente','confirmado','finalizado','cancelado') DEFAULT 'pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `agendamentos`
--

INSERT INTO `agendamentos` (`id`, `id_usuario`, `id_espaco`, `data_inicio`, `data_fim`, `status`, `data_criacao`) VALUES
(1, 2, 1, '2024-12-25 16:00:00', '2024-12-25 19:30:00', 'confirmado', '2024-12-17 03:00:58'),
(6, 3, 2, '2024-12-26 08:30:00', '2024-12-26 12:15:00', 'confirmado', '2024-12-17 06:14:04'),
(7, 3, 1, '2024-12-19 03:14:00', '2024-12-20 03:14:00', 'cancelado', '2024-12-17 06:15:02'),
(8, 3, 2, '2024-12-26 08:30:00', '2024-12-26 12:15:00', 'pendente', '2024-12-17 06:49:43'),
(9, 4, 3, '2024-12-24 12:15:00', '2024-12-24 19:30:00', 'pendente', '2024-12-19 00:14:43'),
(10, 6, 1, '2024-12-23 09:00:00', '2024-12-23 12:00:00', 'cancelado', '2024-12-19 01:01:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `avaliacoes`
--

CREATE TABLE `avaliacoes` (
  `id` int(11) NOT NULL,
  `id_agendamento` int(11) NOT NULL,
  `nota` int(11) NOT NULL,
  `comentario` text DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `avaliacoes`
--

INSERT INTO `avaliacoes` (`id`, `id_agendamento`, `nota`, `comentario`, `data_criacao`) VALUES
(1, 8, 3, 'Pode melhorar', '2024-12-17 07:05:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `espacos`
--

CREATE TABLE `espacos` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `descricao` text DEFAULT NULL,
  `capacidade` int(11) DEFAULT NULL,
  `preco` decimal(10,2) DEFAULT NULL,
  `status` enum('disponivel','indisponivel') DEFAULT 'disponivel',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `espacos`
--

INSERT INTO `espacos` (`id`, `nome`, `descricao`, `capacidade`, `preco`, `status`, `data_criacao`) VALUES
(1, 'Sala Bacia de Santos', 'Contem 1 tv e 1 projetor , 2 câmeras Logitech e um notebook', 20, NULL, 'disponivel', '2024-12-17 02:09:13'),
(2, 'Sala Petronas', 'Contem 1 tv e 1 projetor , 2 câmeras Logitech e um notebook', 12, NULL, 'disponivel', '2024-12-17 05:43:32'),
(3, 'Sala Copacabana', 'Contem 2 tvs 3 câmeras Logitech e um notebook', 10, NULL, 'disponivel', '2024-12-17 06:31:46');

-- --------------------------------------------------------

--
-- Estrutura para tabela `ocorrencias`
--

CREATE TABLE `ocorrencias` (
  `id` int(11) NOT NULL,
  `id_agendamento` int(11) NOT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('pendente','resolvido') DEFAULT 'pendente',
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `ocorrencias`
--

INSERT INTO `ocorrencias` (`id`, `id_agendamento`, `descricao`, `status`, `data_criacao`) VALUES
(1, 8, 'Sala estava seja e com coisas de reunioes passadas', 'pendente', '2024-12-17 07:46:27');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `tipo_usuario` enum('admin','usuario') DEFAULT 'usuario',
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `primeiro_login` enum('sim','nao') DEFAULT 'sim'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `cpf`, `tipo_usuario`, `data_cadastro`, `status`, `ultimo_login`, `primeiro_login`) VALUES
(1, 'Administrador', 'admin@naturezaviva.org', 'e10adc3949ba59abbe56e057f20f883e', '85725356868', 'admin', '2024-12-16 16:19:20', 'ativo', NULL, 'nao'),
(2, 'Gustavo', 'gustavoalmeida@gmail.com', 'a5f926dd78102fa9b2dcbb74d8524bc4', '', 'usuario', '2024-12-17 01:40:10', 'ativo', NULL, 'nao'),
(3, 'lara Kimberly', 'iarakim@gmail.com', 'a5f926dd78102fa9b2dcbb74d8524bc4', '', 'usuario', '2024-12-17 05:56:41', 'ativo', NULL, 'nao'),
(4, 'augusto trefilho', 'augusto@gmail.com', 'a5f926dd78102fa9b2dcbb74d8524bc4', '4572356868', 'usuario', '2024-12-19 00:03:31', 'ativo', NULL, 'nao'),
(5, 'vitor quiroga', 'vitor@gmail.com', 'a5f926dd78102fa9b2dcbb74d8524bc4', '85769853535', 'usuario', '2024-12-19 00:05:11', 'ativo', NULL, 'nao'),
(6, 'grota', 'grota@gmail.com', 'a5f926dd78102fa9b2dcbb74d8524bc4', '45872536969', 'usuario', '2024-12-19 01:00:14', 'ativo', NULL, 'nao');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_espaco` (`id_espaco`);

--
-- Índices de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agendamento` (`id_agendamento`);

--
-- Índices de tabela `espacos`
--
ALTER TABLE `espacos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_agendamento` (`id_agendamento`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `avaliacoes`
--
ALTER TABLE `avaliacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `espacos`
--
ALTER TABLE `espacos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `ocorrencias`
--
ALTER TABLE `ocorrencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `agendamentos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `agendamentos_ibfk_2` FOREIGN KEY (`id_espaco`) REFERENCES `espacos` (`id`);

--
-- Restrições para tabelas `avaliacoes`
--
ALTER TABLE `avaliacoes`
  ADD CONSTRAINT `avaliacoes_ibfk_1` FOREIGN KEY (`id_agendamento`) REFERENCES `agendamentos` (`id`);

--
-- Restrições para tabelas `ocorrencias`
--
ALTER TABLE `ocorrencias`
  ADD CONSTRAINT `ocorrencias_ibfk_1` FOREIGN KEY (`id_agendamento`) REFERENCES `agendamentos` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
