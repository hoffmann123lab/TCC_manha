
<?php

session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require "connection.php";

$tipoAcesso = $_SESSION["tipo"] ?? "Operador";

$sql = "SELECT produto, setor, uni_med, SUM(quantidade) AS quantidade_total
        FROM planilhas
        WHERE produto IS NOT NULL AND produto <> ''
        GROUP BY produto, setor, uni_med
        ORDER BY produto";

$resultado = $conn->query($sql);

$itens = [];
$totalItens = 0;
$totalUnidades = 0;
$itensBaixos = 0;

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $itens[] = $linha;
        $totalItens++;

        $quantidade = (int) ($linha["quantidade_total"] ?? 0);
        $totalUnidades += $quantidade;

        if ($quantidade <= 5) {
            $itensBaixos++;
        }
    }
}

$pesquisa = trim($_GET["pesquisa"] ?? "");

if ($pesquisa !== "") {
    $itens = array_values(array_filter($itens, function ($item) use ($pesquisa) {
        return stripos($item["produto"] ?? "", $pesquisa) !== false
            || stripos($item["setor"] ?? "", $pesquisa) !== false;
    }));
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - SIGEP-EPI</title>
    <link rel="stylesheet" href="estoque.css">
</head>
<body>
    <div class="dashboard">
        <aside class="menu">
            <div class="logo">
                <div class="icone-logo">⚙</div>

                <div class="logo-texto">
                    <h1>SIGEP-EPI</h1>
                    <span>Sistema de Gestão</span>
                </div>
            </div>

            <nav>
                <a href="dashboard.php">
                    <span class="icone-menu">⌂</span>
                    <span>Início</span>
                </a>

                <a href="producao.php">
                    <span class="icone-menu">▤</span>
                    <span>Produção</span>
                </a>

                <a href="analises.php">
                    <span class="icone-menu">▥</span>
                    <span>Análises</span>
                </a>

                <a href="planilhas.php">
                    <span class="icone-menu">▦</span>
                    <span>Planilhas</span>
                </a>

                <a href="relatorios.php">
                    <span class="icone-menu">▧</span>
                    <span>Relatórios</span>
                </a>

                <a href="estoque.php" class="ativo">
                    <span class="icone-menu">▣</span>
                    <span>Estoque</span>
                </a>

                <?php if ($tipoAcesso === "Supervisor"): ?>
                    <a href="usuarios.php">
                        <span class="icone-menu">♙</span>
                        <span>Funcionários</span>
                    </a>
                <?php endif; ?>

                <a href="perfil.php">
                    <span class="icone-menu">⚙</span>
                    <span>Meu perfil</span>
                </a>
            </nav>

            <a href="index.php" class="sair">
                <span class="icone-menu">↪</span>
                <span>Sair</span>
            </a>
        </aside>

        <main class="conteudo">
            <header class="topo">
                <div>
                    <span class="subtitulo">CONTROLE DE MATERIAIS</span>
                    <h2>Estoque</h2>
                    <p>Acompanhe os produtos e suas quantidades registradas.</p>
                </div>
            </header>

            <section class="resumo">
                <article class="card">
                    <div class="card-icone">▣</div>
                    <span>Produtos agrupados</span>
                    <strong><?= $totalItens ?></strong>
                </article>

                <article class="card">
                    <div class="card-icone">▤</div>
                    <span>Total de unidades registradas</span>
                    <strong><?= $totalUnidades ?></strong>
                </article>

                <article class="card">
                    <div class="card-icone alerta">!</div>
                    <span>Registros com quantidade baixa</span>
                    <strong><?= $itensBaixos ?></strong>
                </article>
            </section>

            <section class="painel">
                <div class="titulo-painel">
                    <div>
                        <h3>Produtos em estoque</h3>
                        <p>Os itens são agrupados por produto, setor e unidade de medida.</p>
                    </div>
                </div>

                <form method="GET" action="estoque.php" class="pesquisa">
                    <input
                        type="search"
                        name="pesquisa"
                        placeholder="Pesquisar produto ou setor..."
                        value="<?= htmlspecialchars($pesquisa, ENT_QUOTES, "UTF-8") ?>"
                    >

                    <button type="submit">Pesquisar</button>

                    <?php if ($pesquisa !== ""): ?>
                        <a href="estoque.php" class="limpar">Limpar</a>
                    <?php endif; ?>
                </form>

                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Produto</th>
                                <th>Setor</th>
                                <th>Unidade de medida</th>
                                <th>Quantidade</th>
                                <th>Situação</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($itens) > 0): ?>
                                <?php foreach ($itens as $item): ?>
                                    <?php $quantidade = (int) ($item["quantidade_total"] ?? 0); ?>

                                    <tr>
                                        <td class="produto">
                                            <?= htmlspecialchars($item["produto"] ?? "", ENT_QUOTES, "UTF-8") ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($item["setor"] ?? "", ENT_QUOTES, "UTF-8") ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($item["uni_med"] ?? "", ENT_QUOTES, "UTF-8") ?>
                                        </td>

                                        <td>
                                            <strong><?= $quantidade ?></strong>
                                        </td>

                                        <td>
                                            <?php if ($quantidade <= 5): ?>
                                                <span class="status baixo">Quantidade baixa</span>
                                            <?php else: ?>
                                                <span class="status normal">Quantidade registrada</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="vazio">
                                        <div class="vazio-icone">▣</div>
                                        <strong>Nenhum produto encontrado</strong>
                                        <p>Experimente pesquisar por outro produto ou setor.</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="rodape">
                <span>SIGEP-EPI</span>
                <span>Sistema de Gestão de EPI's</span>
            </footer>
        </main>
    </div>
</body>
</html>

<?php
$conn->close();
?>
