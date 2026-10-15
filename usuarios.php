
<?php

session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php?erro=Faça login para continuar.");
    exit;
}

if (($_SESSION["tipo"] ?? "") !== "Supervisor") {
    http_response_code(403);
    exit("Acesso negado. Somente o administrador pode acessar esta página.");
}

require "connection.php";

$pesquisa = trim($_GET["pesquisa"] ?? "");

if ($pesquisa !== "") {
    $termo = "%" . $pesquisa . "%";

    $stmt = $conn->prepare(
        "SELECT id_fun, nome_fun, email, idade, tipo
        FROM funcionario
        WHERE nome_fun LIKE ? OR email LIKE ?
        ORDER BY nome_fun"
    );

    $stmt->bind_param("ss", $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conn->query(
        "SELECT id_fun, nome_fun, email, idade, tipo
        FROM funcionario
        ORDER BY nome_fun"
    );
}

$funcionarios = [];

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $funcionarios[] = $linha;
    }
}

$totalFuncionarios = count($funcionarios);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários - SIGEP-EPI</title>
    <link rel="stylesheet" href="usuarios.css">
</head>
<body>
    <div class="dashboard">
        <aside class="menu">
            <div class="logo">
                <div class="icone-logo">⚙</div>
                <div>
                    <h1>SIGEP-EPI</h1>
                    <span>Sistema de Gestão</span>
                </div>
            </div>

            <nav>
                <a href="dashboard.php">🏠 <span>Início</span></a>
                <a href="producao.php">📋 <span>Produção</span></a>
                <a href="analises.php">📊 <span>Análises</span></a>
                <a href="planilhas.php">📁 <span>Planilhas</span></a>
                <a href="relatorios.php">📈 <span>Relatórios</span></a>
                <a href="estoque.php">📦 <span>Estoque</span></a>

                <?php if (($_SESSION["tipo"] ?? "") === "Supervisor"): ?>
                    <a href="usuarios.php" class="ativo">👥 <span>Funcionários</span></a>
                <?php endif; ?>

                <a href="perfil.php">👤 <span>Meu perfil</span></a>
            </nav>

            <a href="logout.php" class="sair">🚪 <span>Sair</span></a>
        </aside>

        <main class="conteudo">
            <header class="topo">
                <div>
                    <span class="subtitulo">CADASTROS</span>
                    <h2>Funcionários</h2>
                    <p>Consulte os funcionários cadastrados no sistema.</p>
                </div>
            </header>

            <section class="resumo">
                <div class="card">
                    <span>Funcionários encontrados</span>
                    <strong><?= $totalFuncionarios ?></strong>
                </div>
            </section>

            <section class="painel">
                <div class="titulo-painel">
                    <h3>Lista de funcionários</h3>
                    <p>Consulte os dados registrados no banco de dados.</p>
                </div>

                <form method="GET" action="usuarios.php" class="pesquisa">
                    <input
                        type="search"
                        name="pesquisa"
                        placeholder="Pesquisar por nome ou e-mail..."
                        value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, "UTF-8") ?>"
                    >

                    <button type="submit">Pesquisar</button>

                    <?php if ($pesquisa !== ""): ?>
                        <a href="usuarios.php">Limpar</a>
                    <?php endif; ?>
                </form>

                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Idade</th>
                                <th>Tipo de acesso</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($totalFuncionarios > 0): ?>
                                <?php foreach ($funcionarios as $funcionario): ?>
                                    <tr>
                                        <td><?= (int) $funcionario["id_fun"] ?></td>
                                        <td><?= htmlspecialchars($funcionario["nome_fun"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($funcionario["email"], ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= (int) $funcionario["idade"] ?></td>
                                        <td>
                                            <span class="tipo">
                                                <?= htmlspecialchars($funcionario["tipo"], ENT_QUOTES, "UTF-8") ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="vazio">
                                        Nenhum funcionário encontrado.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
