
<?php

require "connection.php";

$pesquisa = trim($_GET["pesquisa"] ?? "");

if ($pesquisa !== "") {
    $termo = "%" . $pesquisa . "%";

    $stmt = $conn->prepare(
        "SELECT id_plan, nome_plan, produto, descricao, tipo,
        uni_med, colaborador, setor, quantidade, entrada, saida
        FROM Planilhas
        WHERE produto LIKE ?
        OR descricao LIKE ?
        OR setor LIKE ?
        OR colaborador LIKE ?
        ORDER BY id_plan DESC"
    );

    $stmt->bind_param("ssss", $termo, $termo, $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conn->query(
        "SELECT id_plan, nome_plan, produto, descricao, tipo,
        uni_med, colaborador, setor, quantidade, entrada, saida
        FROM Planilhas
        ORDER BY id_plan DESC"
    );
}

$totalRegistros = 0;
$totalQuantidade = 0;
$registros = [];

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $registros[] = $linha;
        $totalRegistros++;
        $totalQuantidade += (int) ($linha["quantidade"] ?? 0);
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - SIGEP-EPI</title>
    <link rel="stylesheet" href="relatorios.css">
</head>
<body>
    <aside class="menu">
        <div class="logo">
            <div class="icone-logo">⚙</div>
            <div>
                <h1>SIGEP-EPI</h1>
                <span>Sistema de Gestão</span>
            </div>
        </div>

        <nav>
            <a href="dashboard.php">⌂ <span>Início</span></a>
            <a href="producao.php">▤ <span>Produção</span></a>
            <a href="analises.php">▥ <span>Análises</span></a>
            <a href="planilhas.php">▦ <span>Planilhas</span></a>
            <a href="relatorios.php" class="ativo">▧ <span>Relatórios</span></a>
            <a href="configuracoes.php">⚙ <span>Configurações</span></a>
        </nav>

        <a href="index.php" class="sair">↪ <span>Sair</span></a>
    </aside>

    <main class="conteudo">
        <header class="topo">
            <div>
                <span class="subtitulo">CONSULTA E EXPORTAÇÃO</span>
                <h2>Relatórios</h2>
                <p>Consulte os registros e imprima os dados do sistema.</p>
            </div>

            <button type="button" class="botao-imprimir" onclick="window.print()">
                Imprimir relatório
            </button>
        </header>

        <section class="resumo">
            <article class="card">
                <span>Total de registros</span>
                <strong><?= $totalRegistros ?></strong>
            </article>

            <article class="card">
                <span>Quantidade somada</span>
                <strong><?= $totalQuantidade ?></strong>
            </article>
        </section>

        <section class="painel">
            <div class="titulo-painel">
                <div>
                    <h3>Relatório de registros</h3>
                    <p>Dados consultados diretamente no banco de dados.</p>
                </div>
            </div>

            <form method="GET" action="relatorios.php" class="pesquisa">
                <input
                    type="search"
                    name="pesquisa"
                    placeholder="Pesquisar produto, setor ou colaborador..."
                    value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, "UTF-8") ?>"
                >
                <button type="submit">Pesquisar</button>
                <?php if ($pesquisa !== ""): ?>
                    <a href="relatorios.php">Limpar</a>
                <?php endif; ?>
            </form>

            <div class="tabela-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Planilha</th>
                            <th>Produto</th>
                            <th>Descrição</th>
                            <th>Tipo</th>
                            <th>Quantidade</th>
                            <th>Unidade</th>
                            <th>Colaborador</th>
                            <th>Setor</th>
                            <th>Entrada</th>
                            <th>Saída</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($registros) > 0): ?>
                            <?php foreach ($registros as $linha): ?>
                                <tr>
                                    <td><?= (int) $linha["id_plan"] ?></td>
                                    <td><?= htmlspecialchars($linha["nome_plan"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($linha["produto"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($linha["descricao"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($linha["tipo"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= (int) ($linha["quantidade"] ?? 0) ?></td>
                                    <td><?= htmlspecialchars($linha["uni_med"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($linha["colaborador"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($linha["setor"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($linha["entrada"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($linha["saida"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="vazio">
                                    Nenhum registro encontrado.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <p class="rodape-relatorio">
                Relatório gerado pelo SIGEP-EPI.
            </p>
        </section>
    </main>
</body>
</html>
