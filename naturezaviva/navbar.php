<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container">
        <a class="navbar-brand logo" href="index.php">🌱 Natureza Viva</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="index.php">Início</a></li>
                <li class="nav-item"><a class="nav-link" href="sobrenos.php">Sobre nós</a></li>
                
                <!-- Verificando se o usuário está logado -->
                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <!-- Se o usuário for admin, exibe o link para o painel administrativo -->
                    <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="adminDashboard.php">Painel Administrativo</a></li>
                    <?php endif; ?>

                    <!-- Se o usuário for do tipo 'usuario', exibe o link para o painel de usuário -->
                    <?php if ($_SESSION['usuario_tipo'] === 'usuario'): ?>
                        <li class="nav-item"><a class="nav-link" href="userDashboard.php">Painel Usuário</a></li>
                    <?php endif; ?>

                    <!-- Botão de Logout -->
                    <li class="nav-item">
                        <a class="btn btn-outline-danger" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <!-- Se não estiver logado, exibe o botão de Login -->
                    <li class="nav-item">
                        <a class="btn btn-outline-success" href="login.php">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

