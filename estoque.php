<?php
session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require "connection.php";

$tipoAcesso = $_SESSION["tipo"] ?? "Operador";
$pesquisa = trim($_GET["pesquisa"] ?? "");

$sql = "SELECT produto, setor, uni_med, SUM(quantidade) AS quantidade_total
        FROM planilhas
        WHERE produto IS NOT NULL AND produto <> ''
        GROUP BY produto, setor, uni_med
        ORDER BY produto";

$resultado = $conn->query($sql);

$itensOriginais = [];

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $itensOriginais[] = $linha;
    }
}

$itens = $itensOriginais;

if ($pesquisa !== "") {
    $itens = array_values(array_filter($itens, function ($item) use ($pesquisa) {
        return stripos($item["produto"] ?? "", $pesquisa) !== false
            || stripos($item["setor"] ?? "", $pesquisa) !== false;
    }));
}

$totalItens = count($itens);
$totalUnidades = 0;
$itensBaixos = 0;
$quantidadesPorSetor = [];
$produtosOrdenados = [];

foreach ($itens as $item) {
    $quantidade = (int) ($item["quantidade_total"] ?? 0);
    $produto = $item["produto"] ?? "Sem produto";
    $setor = $item["setor"] ?? "Sem setor";
    $unidade = $item["uni_med"] ?? "";

    $totalUnidades += $quantidade;

    if ($quantidade <= 5) {
        $itensBaixos++;
    }

    if (!isset($quantidadesPorSetor[$setor])) {
        $quantidadesPorSetor[$setor] = 0;
    }

    $quantidadesPorSetor[$setor] += $quantidade;

    $produtosOrdenados[] = [
        "nome" => $produto . " - " . $setor . " (" . $unidade . ")",
        "quantidade" => $quantidade
    ];
}

usort($produtosOrdenados, function ($a, $b) {
    return $b["quantidade"] <=> $a["quantidade"];
});

$produtosOrdenados = array_slice($produtosOrdenados, 0, 10);

arsort($quantidadesPorSetor);

$labelsProdutos = array_column($produtosOrdenados, "nome");
$dadosProdutos = array_column($produtosOrdenados, "quantidade");

$labelsSetores = array_keys($quantidadesPorSetor);
$dadosSetores = array_values($quantidadesPorSetor);

$dadosSituacao = [
    $itensBaixos,
    $totalItens - $itensBaixos
];

function escapar($valor) {
    return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$opcoesJson = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estoque - SIGEP-EPI</title>
    <link rel="stylesheet" href="estoque.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

            <section class="graficos">
                <article class="painel-grafico">
                    <div class="titulo-grafico">
                        <h3>Produtos com maiores quantidades</h3>
                        <p>Os 10 registros com maior quantidade.</p>
                    </div>

                    <div class="area-grafico">
                        <canvas id="graficoProdutos"></canvas>
                    </div>
                </article>

                <article class="painel-grafico">
                    <div class="titulo-grafico">
                        <h3>Quantidade por setor</h3>
                        <p>Distribuição das quantidades registradas por setor.</p>
                    </div>

                    <div class="area-grafico">
                        <canvas id="graficoSetores"></canvas>
                    </div>
                </article>

                <article class="painel-grafico painel-situacao">
                    <div class="titulo-grafico">
                        <h3>Situação do estoque</h3>
                        <p>Comparação entre registros com quantidade baixa e normal.</p>
                    </div>

                    <div class="area-grafico area-donut">
                        <canvas id="graficoSituacao"></canvas>
                    </div>
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
                        value="<?= escapar($pesquisa) ?>"
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
                                            <?= escapar($item["produto"]) ?>
                                        </td>

                                        <td>
                                            <?= escapar($item["setor"]) ?>
                                        </td>

                                        <td>
                                            <?= escapar($item["uni_med"]) ?>
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

    <script>
        const labelsProdutos = <?= json_encode($labelsProdutos, $opcoesJson) ?>;
        const dadosProdutos = <?= json_encode($dadosProdutos, $opcoesJson) ?>;
        const labelsSetores = <?= json_encode($labelsSetores, $opcoesJson) ?>;
        const dadosSetores = <?= json_encode($dadosSetores, $opcoesJson) ?>;
        const dadosSituacao = <?= json_encode($dadosSituacao, $opcoesJson) ?>;

        if (typeof Chart !== "undefined") {
            Chart.defaults.font.family = "Arial";
            Chart.defaults.color = "#64748b";

            new Chart(document.getElementById("graficoProdutos"), {
                type: "bar",
                data: {
                    labels: labelsProdutos,
                    datasets: [{
                        label: "Quantidade",
                        data: dadosProdutos,
                        backgroundColor: "#2563eb",
                        borderRadius: 6,
                        maxBarThickness: 35
                    }]
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true
                        }
                    }
                }
            });

            new Chart(document.getElementById("graficoSetores"), {
                type: "bar",
                data: {
                    labels: labelsSetores,
                    datasets: [{
                        label: "Quantidade",
                        data: dadosSetores,
                        backgroundColor: "#0f766e",
                        borderRadius: 6,
                        maxBarThickness: 45
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            new Chart(document.getElementById("graficoSituacao"), {
                type: "doughnut",
                data: {
                    labels: ["Quantidade baixa", "Quantidade normal"],
                    datasets: [{
                        data: dadosSituacao,
                        backgroundColor: ["#f59e0b", "#2563eb"],
                        borderWidth: 2,
                        borderColor: "#ffffff"
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "bottom"
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>

<?php
$conn->close();
?>