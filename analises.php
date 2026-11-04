
<?php
session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require "connection.php";

$tipoAcesso = $_SESSION["tipo"] ?? "Operador";

$totalRegistros = 0;
$totalQuantidade = 0;
$produtos = [];
$setores = [];

$sql = "SELECT produto, cor, tamanho, setor, quantidade
        FROM planilhas
        WHERE produto IS NOT NULL
        AND produto <> ''";

$resultado = $conn->query($sql);

if (!$resultado) {
    die("Erro ao consultar os dados: " . htmlspecialchars($conn->error));
}

while ($linha = $resultado->fetch_assoc()) {
    $produto = trim($linha["produto"] ?? "");
    $cor = trim($linha["cor"] ?? "");
    $tamanho = trim($linha["tamanho"] ?? "");
    $setor = trim($linha["setor"] ?? "");
    $quantidade = (float) ($linha["quantidade"] ?? 0);

    if ($produto === "") {
        continue;
    }

    $totalRegistros++;
    $totalQuantidade += $quantidade;

    $chaveProduto = mb_strtolower($produto . "|" . $cor . "|" . $tamanho);

    if (!isset($produtos[$chaveProduto])) {
        $produtos[$chaveProduto] = [
            "produto" => $produto,
            "cor" => $cor,
            "tamanho" => $tamanho,
            "quantidade" => 0
        ];
    }

    $produtos[$chaveProduto]["quantidade"] += $quantidade;

    if ($setor !== "") {
        if (!isset($setores[$setor])) {
            $setores[$setor] = 0;
        }

        $setores[$setor] += $quantidade;
    }
}

$produtos = array_values($produtos);

usort($produtos, function ($a, $b) {
    return $b["quantidade"] <=> $a["quantidade"];
});

arsort($setores);

$totalProdutos = count($produtos);
$totalSetores = count($setores);

$produtosGrafico = array_slice($produtos, 0, 10);

$labelsProdutos = [];
$valoresProdutos = [];

foreach ($produtosGrafico as $produto) {
    $nome = $produto["produto"];

    if ($produto["cor"] !== "") {
        $nome .= " - " . $produto["cor"];
    }

    if ($produto["tamanho"] !== "") {
        $nome .= " - " . $produto["tamanho"];
    }

    $labelsProdutos[] = $nome;
    $valoresProdutos[] = $produto["quantidade"];
}

$labelsSetores = array_keys($setores);
$valoresSetores = array_values($setores);

$formatarNumero = function ($numero) {
    return number_format($numero, 0, ",", ".");
};

$maximoSetor = !empty($valoresSetores) ? max($valoresSetores) : 0;

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análises - SIGEP-EPI</title>
    <link rel="stylesheet" href="analises.css">
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

        <div class="menu-titulo">MENU PRINCIPAL</div>

        <nav>
            <a href="dashboard.php">
                <span class="icone-menu">⌂</span>
                <span>Início</span>
            </a>

            <a href="producao.php">
                <span class="icone-menu">▤</span>
                <span>Produção</span>
            </a>

            <a href="analises.php" class="ativo">
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

            <a href="estoque.php">
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
                <span class="subtitulo">GESTÃO E INDICADORES</span>
                <h2>Análises</h2>
                <p>Visualização e comparação dos dados armazenados no sistema.</p>
            </div>
        </header>

        <section class="cards">

            <div class="card">
                <div class="card-icone azul">▤</div>
                <div class="card-conteudo">
                    <span>Registros armazenados</span>
                    <strong><?= $formatarNumero($totalRegistros) ?></strong>
                </div>
            </div>

            <div class="card">
                <div class="card-icone verde">▣</div>
                <div class="card-conteudo">
                    <span>Quantidade total</span>
                    <strong><?= $formatarNumero($totalQuantidade) ?></strong>
                </div>
            </div>

            <div class="card">
                <div class="card-icone roxo">▦</div>
                <div class="card-conteudo">
                    <span>Produtos diferentes</span>
                    <strong><?= $formatarNumero($totalProdutos) ?></strong>
                </div>
            </div>

        </section>

        <section class="painel">

            <div class="painel-topo">
                <div>
                    <h3>Quantidade por produto</h3>
                    <p class="descricao">
                        Os 10 produtos com maior quantidade registrada.
                    </p>
                </div>
            </div>

            <?php if (!empty($produtosGrafico)): ?>

                <div style="position: relative; height: 360px; margin-top: 25px;">
                    <canvas id="graficoProdutos"></canvas>
                </div>

            <?php else: ?>

                <div class="vazio">
                    <div class="vazio-icone">▤</div>
                    <h4>Nenhum produto encontrado</h4>
                    <p>
                        Ainda não existem produtos registrados para gerar
                        o gráfico de análise.
                    </p>
                    <a href="producao.php" class="botao-primario">
                        Ir para Produção
                    </a>
                </div>

            <?php endif; ?>

        </section>

        <section class="painel">

            <div class="painel-topo">
                <div>
                    <h3>Quantidade por setor</h3>
                    <p class="descricao">
                        Distribuição da quantidade de produtos entre os setores.
                    </p>
                </div>

                <span class="contador-setores">
                    <?= $formatarNumero($totalSetores) ?> setores
                </span>
            </div>

            <?php if (!empty($setores)): ?>

                <div class="grafico">

                    <?php $contador = 1; ?>

                    <?php foreach ($setores as $nomeSetor => $quantidadeSetor): ?>

                        <?php
                        $porcentagem = $maximoSetor > 0
                            ? ($quantidadeSetor / $maximoSetor) * 100
                            : 0;
                        ?>

                        <div class="barra-item">

                            <div class="barra-info">
                                <div class="nome-setor">
                                    <span class="numero-setor">
                                        <?= $contador ?>
                                    </span>

                                    <span class="texto-setor">
                                        <?= htmlspecialchars($nomeSetor) ?>
                                    </span>
                                </div>

                                <strong>
                                    <?= $formatarNumero($quantidadeSetor) ?>
                                </strong>
                            </div>

                            <div class="barra-fundo">
                                <div
                                    class="barra"
                                    style="width: <?= $porcentagem ?>%;"
                                ></div>
                            </div>

                        </div>

                        <?php $contador++; ?>

                    <?php endforeach; ?>

                </div>

            <?php else: ?>

                <div class="vazio">
                    <div class="vazio-icone">⌂</div>
                    <h4>Nenhum setor encontrado</h4>
                    <p>
                        Não existem informações de setores disponíveis
                        para gerar essa análise.
                    </p>
                </div>

            <?php endif; ?>

        </section>

        <section class="painel">

            <div class="painel-topo">
                <div>
                    <h3>Produtos registrados</h3>
                    <p class="descricao">
                        Quantidade acumulada por produto, cor e tamanho.
                    </p>
                </div>
            </div>

            <?php if (!empty($produtos)): ?>

                <div style="overflow-x: auto; margin-top: 20px;">

                    <table style="width: 100%; border-collapse: collapse; text-align: left;">

                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="padding: 14px;">Produto</th>
                                <th style="padding: 14px;">Cor</th>
                                <th style="padding: 14px;">Tamanho</th>
                                <th style="padding: 14px;">Quantidade total</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($produtos as $produto): ?>

                                <tr style="border-bottom: 1px solid #e2e8f0;">

                                    <td style="padding: 14px;">
                                        <?= htmlspecialchars($produto["produto"]) ?>
                                    </td>

                                    <td style="padding: 14px;">
                                        <?= htmlspecialchars($produto["cor"] ?: "-") ?>
                                    </td>

                                    <td style="padding: 14px;">
                                        <?= htmlspecialchars($produto["tamanho"] ?: "-") ?>
                                    </td>

                                    <td style="padding: 14px; color: #2563eb; font-weight: bold;">
                                        <?= $formatarNumero($produto["quantidade"]) ?>
                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="vazio">
                    <div class="vazio-icone">▦</div>
                    <h4>Nenhum registro encontrado</h4>
                    <p>
                        Importe uma planilha na página Produção para
                        visualizar os produtos nesta análise.
                    </p>
                    <a href="producao.php" class="botao-primario">
                        Ir para Produção
                    </a>
                </div>

            <?php endif; ?>

        </section>

        <section class="painel observacao">

            <div class="informacao-icone">i</div>

            <div>
                <h3>Sobre as análises</h3>
                <p>
                    Os indicadores são calculados com base nos registros
                    armazenados no banco de dados. Cada nova importação
                    acrescenta informações ao histórico, e os gráficos
                    consideram os registros acumulados.
                </p>
            </div>

        </section>

        <footer class="rodape">
            <span><strong>SIGEP-EPI</strong> - Sistema de Gestão</span>
            <span>Análises dos registros armazenados</span>
        </footer>

    </main>

</div>

<script>
const labelsProdutos = <?= json_encode($labelsProdutos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
const valoresProdutos = <?= json_encode($valoresProdutos) ?>;

const elementoGrafico = document.getElementById("graficoProdutos");

if (elementoGrafico && typeof Chart !== "undefined") {
    new Chart(elementoGrafico, {
        type: "bar",
        data: {
            labels: labelsProdutos,
            datasets: [{
                label: "Quantidade",
                data: valoresProdutos,
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
}
</script>

</body>
</html>
