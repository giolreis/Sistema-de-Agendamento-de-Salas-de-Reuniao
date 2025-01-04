<?php
session_start(); // Inicia a sessão
include('navbar.php'); // Inclui o código do navbar
?>

<!-- Conteúdo da página -->


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Natureza Viva - Home</title>
    <!-- Link para o CSS da Navbar -->
    <link rel="stylesheet" href="webapp/css/navbar.css">
    <!-- Incluindo o Bootstrap 5 para garantir a responsividade -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css\index.css">
</head>
<body>

    <!-- Cabeçalho com foto e descrição -->
    <header class="container text-center my-5">
        <img src="img\natureza.png" alt="Foto representativa da Natureza Viva" class="img-fluid mb-4" style="max-width: 100%; height: auto;">
        <h1>Bem-vindo à Natureza Viva</h1>
        <p class="lead">
            A ONG **Natureza Viva** dedica-se a promover a sustentabilidade e a conscientização ambiental. Oferecemos espaços para eventos e workshops com foco na preservação do meio ambiente. Junte-se a nós e faça a diferença!
        </p>
    </header>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
<?php include('footer.php'); ?>
</html>
