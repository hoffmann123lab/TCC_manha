
<?php

require "connection.php";

$pesquisa = trim($_GET["pesquisa"] ?? "");

if ($pesquisa !== "") {
    $termo = "%" . $pesquisa . "%";

    $stmt = $conn->prepare(
        "SELECT id_plan, nome_plan, produto, descricao, tipo, uni_med,
        colaborador, setor, quantidade, entrada, saida
        FROM planilhas
        WHERE nome_plan LIKE ?
        OR produto LIKE ?
        OR descricao LIKE ?
        OR setor LIKE ?
        ORDER BY id_plan DESC"
    );

    $stmt->bind_param("ssss", $termo, $termo, $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conn->query(
        "SELECT id_plan, nome_plan, produto, descricao, tipo, uni_med,
        colaborador, setor, quantidade, entrada, saida
        FROM planilhas
        ORDER BY id_plan DESC"
    );
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planilhas - SIGEP-EPI</title>
    <link rel="stylesheet" href="planilhas.css">
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
                <a href="planilhas.php" class="ativo">📁 <span>Planilhas</span></a>
                <a href="relatorios.php">📈 <span>Relatórios</span></a>
                <a href="estoque.php">📦 <span>Estoque</span></a>
                <a href="usuarios.php">👥 <span>Funcionários</span></a>
                <a href="perfil.php">👤 <span>Meu perfil</span></a>
            </nav>

            <a href="index.php" class="sair">🚪 <span>Sair</span></a>
        </aside>

        <main class="conteudo">
            <header class="topo">
                <div>
                    <span class="subtitulo">GERENCIAMENTO DE DADOS</span>
                    <h2>Planilhas</h2>
                    <p>Consulte e pesquise os registros armazenados no sistema.</p>
                </div>

                <a href="producao.php" class="botao-novo">+ Novo registro</a>
            </header>

            <section class="painel">
                <div class="painel-topo">
                    <div>
                        <h3>Planilhas cadastradas</h3>
                        <p>Registros encontrados no banco de dados.</p>
                    </div>
                </div>

                <form method="GET" action="planilhas.php" class="pesquisa">
                    <input
                        type="search"
                        name="pesquisa"
                        placeholder="Pesquisar por nome, produto, descrição ou setor..."
                        value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, "UTF-8") ?>"
                    >
                    <button type="submit">Pesquisar</button>

                    <?php if ($pesquisa !== ""): ?>
                        <a href="planilhas.php" class="limpar">Limpar</a>
                    <?php endif; ?>
                </form>

                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome da planilha</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Setor</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado && $resultado->num_rows > 0): ?>
                                <?php while ($linha = $resultado->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= (int) $linha["id_plan"] ?></td>
                                        <td><?= htmlspecialchars($linha["nome_plan"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["produto"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["tipo"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars((string) ($linha["quantidade"] ?? ""), ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["uni_med"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["setor"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["entrada"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["saida"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="vazio">
                                        Nenhuma planilha encontrada.
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
