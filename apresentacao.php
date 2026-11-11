
<?php
session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require "connection.php";

function escapar($valor) {
    return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
}

$idPlanilha = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$idPlanilha || $idPlanilha <= 0) {
    header("Location: relatorios.php");
    exit;
}

$stmt = $conn->prepare(
    "SELECT id_plan, nome_plan, produto, descricao, tipo, cor, tamanho,
    uni_med, colaborador, setor, quantidade, entrada, saida
    FROM planilhas
    WHERE id_plan = ?"
);

if (!$stmt) {
    die("Erro ao consultar o registro: " . escapar($conn->error));
}

$stmt->bind_param("i", $idPlanilha);
$stmt->execute();

$resultado = $stmt->get_result();
$item = $resultado->fetch_assoc();

$stmt->close();

if (!$item) {
    header("Location: relatorios.php");
    exit;
}

$produto = $item["produto"];

$stmt = $conn->prepare(
    "SELECT
        COUNT(*) AS total_registros,
        COALESCE(SUM(quantidade), 0) AS quantidade_total,
        COALESCE(AVG(quantidade), 0) AS media_quantidade,
        COALESCE(MAX(quantidade), 0) AS maior_quantidade
    FROM planilhas
    WHERE produto = ?"
);

if (!$stmt) {
    die("Erro ao consultar as estatísticas: " . escapar($conn->error));
}

$stmt->bind_param("s", $produto);
$stmt->execute();

$estatisticas = $stmt->get_result()->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare(
    "SELECT
        COALESCE(NULLIF(setor, ''), 'Não informado') AS setor,
        SUM(quantidade) AS total
    FROM planilhas
    WHERE produto = ?
    GROUP BY setor
    ORDER BY total DESC"
);

if (!$stmt) {
    die("Erro ao consultar os setores: " . escapar($conn->error));
}

$stmt->bind_param("s", $produto);
$stmt->execute();

$resultadoSetores = $stmt->get_result();

$setores = [];
$quantidadesSetores = [];

while ($linha = $resultadoSetores->fetch_assoc()) {
    $setores[] = $linha["setor"];
    $quantidadesSetores[] = (int) $linha["total"];
}

$stmt->close();

$stmt = $conn->prepare(
    "SELECT id_plan, nome_plan, colaborador, setor, quantidade,
    uni_med, entrada
    FROM planilhas
    WHERE produto = ?
    ORDER BY entrada DESC, id_plan DESC"
);

if (!$stmt) {
    die("Erro ao consultar o histórico: " . escapar($conn->error));
}

$stmt->bind_param("s", $produto);
$stmt->execute();

$resultadoHistorico = $stmt->get_result();

$historico = [];

while ($linha = $resultadoHistorico->fetch_assoc()) {
    $historico[] = $linha;
}

$stmt->close();

$quantidadeSelecionada = (int) $item["quantidade"];
$quantidadeTotal = (int) $estatisticas["quantidade_total"];
$mediaQuantidade = (float) $estatisticas["media_quantidade"];
$totalRegistros = (int) $estatisticas["total_registros"];

$diferencaMedia = $quantidadeSelecionada - $mediaQuantidade;

$percentualSelecionado = $quantidadeTotal > 0
    ? ($quantidadeSelecionada / $quantidadeTotal) * 100
    : 0;

$dadosSetores = json_encode(
    [
        "setores" => $setores,
        "quantidades" => $quantidadesSetores
    ],
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);

$dadosComparacao = json_encode(
    [
        "selecionada" => $quantidadeSelecionada,
        "media" => round($mediaQuantidade, 2),
        "maior" => (int) $estatisticas["maior_quantidade"]
    ],
    JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP
);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Apresentação - SIGEP-EPI</title>

    <link rel="stylesheet" href="apresentacao.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="pagina">

    <header class="topo">
        <div>
            <span class="subtitulo">SIGEP-EPI | APRESENTAÇÃO DE DADOS</span>

            <h1><?= escapar($item["produto"]) ?></h1>

            <p><?= escapar($item["descricao"]) ?></p>
        </div>

        <div class="botoes-topo">
            <button
                type="button"
                class="botao-pdf"
                onclick="window.print()"
            >
                Salvar apresentação em PDF
            </button>

            <a href="relatorios.php" class="botao-voltar">
                Voltar aos relatórios
            </a>
        </div>
    </header>

    <section class="identificacao">
        <div>
            <span>Registro selecionado</span>
            <strong>#<?= (int) $item["id_plan"] ?></strong>
        </div>

        <div>
            <span>Planilha de origem</span>
            <strong><?= escapar($item["nome_plan"]) ?></strong>
        </div>

        <div>
            <span>Colaborador</span>
            <strong><?= escapar($item["colaborador"]) ?></strong>
        </div>

        <div>
            <span>Setor</span>
            <strong><?= escapar($item["setor"]) ?></strong>
        </div>
    </section>

    <section class="indicadores">

        <article class="indicador destaque">
            <span>Quantidade do registro</span>

            <strong>
                <?= number_format($quantidadeSelecionada, 0, ",", ".") ?>
            </strong>

            <small><?= escapar($item["uni_med"]) ?></small>
        </article>

        <article class="indicador">
            <span>Total do produto</span>

            <strong>
                <?= number_format($quantidadeTotal, 0, ",", ".") ?>
            </strong>

            <small><?= escapar($item["uni_med"]) ?></small>
        </article>

        <article class="indicador">
            <span>Média por registro</span>

            <strong>
                <?= number_format($mediaQuantidade, 2, ",", ".") ?>
            </strong>

            <small><?= escapar($item["uni_med"]) ?></small>
        </article>

        <article class="indicador">
            <span>Registros encontrados</span>

            <strong><?= $totalRegistros ?></strong>

            <small>Do mesmo produto</small>
        </article>

    </section>

    <section class="painel">

        <div class="titulo-painel">
            <span>01 | INFORMAÇÕES DO ITEM</span>
            <h2>Características do produto</h2>
        </div>

        <div class="informacoes">

            <div>
                <span>Produto</span>
                <strong><?= escapar($item["produto"]) ?></strong>
            </div>

            <div>
                <span>Descrição</span>
                <strong><?= escapar($item["descricao"]) ?></strong>
            </div>

            <div>
                <span>Tipo</span>
                <strong><?= escapar($item["tipo"]) ?></strong>
            </div>

            <div>
                <span>Cor</span>
                <strong><?= escapar($item["cor"] ?: "Não informado") ?></strong>
            </div>

            <div>
                <span>Tamanho</span>
                <strong><?= escapar($item["tamanho"] ?: "Não informado") ?></strong>
            </div>

            <div>
                <span>Unidade de medida</span>
                <strong><?= escapar($item["uni_med"]) ?></strong>
            </div>

            <div>
                <span>Data de entrada</span>
                <strong><?= escapar($item["entrada"]) ?></strong>
            </div>

            <div>
                <span>Data de saída</span>
                <strong><?= escapar($item["saida"] ?: "Não registrada") ?></strong>
            </div>

        </div>

    </section>

    <section class="graficos">

        <article class="painel grafico-painel">

            <div class="titulo-painel">
                <span>02 | DISTRIBUIÇÃO</span>

                <h2>Quantidade por setor</h2>

                <p>
                    Comparação do total deste produto entre os setores.
                </p>
            </div>

            <div class="area-grafico">
                <canvas id="graficoSetores"></canvas>
            </div>

        </article>

        <article class="painel grafico-painel">

            <div class="titulo-painel">
                <span>03 | COMPARAÇÃO</span>

                <h2>Comparativo de quantidades</h2>

                <p>
                    Registro selecionado em relação à média e ao maior registro.
                </p>
            </div>

            <div class="area-grafico">
                <canvas id="graficoComparacao"></canvas>
            </div>

        </article>

    </section>

    <section class="painel analise">

        <div class="titulo-painel">
            <span>04 | ANÁLISE DOS DADOS</span>
            <h2>Resumo comparativo</h2>
        </div>

        <div class="texto-analise">

            <p>
                O registro selecionado possui
                <strong>
                    <?= number_format($quantidadeSelecionada, 0, ",", ".") ?>
                </strong>
                <?= escapar($item["uni_med"]) ?>.
            </p>

            <p>
                Considerando todos os registros encontrados para este produto,
                a quantidade acumulada é de
                <strong>
                    <?= number_format($quantidadeTotal, 0, ",", ".") ?>
                </strong>
                <?= escapar($item["uni_med"]) ?>.
            </p>

            <p>
                A média por registro é de
                <strong>
                    <?= number_format($mediaQuantidade, 2, ",", ".") ?>
                </strong>
                <?= escapar($item["uni_med"]) ?>.
            </p>

            <p>
                O registro selecionado representa
                <strong>
                    <?= number_format($percentualSelecionado, 1, ",", ".") ?>%
                </strong>
                da quantidade total encontrada para este produto.
            </p>

            <p>
                Em relação à média, o registro selecionado está
                <strong>
                    <?= $diferencaMedia > 0
                        ? number_format($diferencaMedia, 2, ",", ".") . " acima"
                        : ($diferencaMedia < 0
                            ? number_format(abs($diferencaMedia), 2, ",", ".") . " abaixo"
                            : "igual")
                    ?>
                </strong>.
            </p>

        </div>

    </section>

    <section class="painel">

        <div class="titulo-painel">
            <span>05 | HISTÓRICO</span>

            <h2>Outros registros do mesmo produto</h2>

            <p>
                Registros encontrados no banco de dados para comparação.
            </p>
        </div>

        <div class="tabela-container">

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Planilha</th>
                        <th>Colaborador</th>
                        <th>Setor</th>
                        <th>Quantidade</th>
                        <th>Unidade</th>
                        <th>Entrada</th>
                    </tr>
                </thead>

                <tbody>

                    <?php foreach ($historico as $registro): ?>

                        <tr class="<?= (int) $registro["id_plan"] === $idPlanilha ? "linha-selecionada" : "" ?>">

                            <td><?= (int) $registro["id_plan"] ?></td>

                            <td><?= escapar($registro["nome_plan"]) ?></td>

                            <td><?= escapar($registro["colaborador"]) ?></td>

                            <td><?= escapar($registro["setor"]) ?></td>

                            <td>
                                <?= number_format((int) $registro["quantidade"], 0, ",", ".") ?>
                            </td>

                            <td><?= escapar($registro["uni_med"]) ?></td>

                            <td><?= escapar($registro["entrada"]) ?></td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>
            </table>

        </div>

    </section>

    <footer>
        Apresentação gerada pelo Sistema SIGEP-EPI.
    </footer>

</div>

<script>
const dadosSetores = <?= $dadosSetores ?>;
const dadosComparacao = <?= $dadosComparacao ?>;

const graficoSetores = new Chart(
    document.getElementById("graficoSetores"),
    {
        type: "bar",

        data: {
            labels: dadosSetores.setores,

            datasets: [{
                label: "Quantidade",
                data: dadosSetores.quantidades,
                backgroundColor: "#2563eb",
                borderRadius: 7
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            animation: {
                onComplete: function() {
                    window.graficosProntos = true;
                }
            },

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
    }
);

const graficoComparacao = new Chart(
    document.getElementById("graficoComparacao"),
    {
        type: "bar",

        data: {
            labels: [
                "Registro selecionado",
                "Média por registro",
                "Maior registro"
            ],

            datasets: [{
                label: "Quantidade",

                data: [
                    dadosComparacao.selecionada,
                    dadosComparacao.media,
                    dadosComparacao.maior
                ],

                backgroundColor: [
                    "#2563eb",
                    "#0f766e",
                    "#64748b"
                ],

                borderRadius: 7
            }]
        },

        options: {
            responsive: true,
            maintainAspectRatio: false,

            animation: {
                onComplete: function() {
                    window.graficosProntos = true;
                }
            },

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
    }
);

window.graficosProntos = false;

window.addEventListener("beforeprint", function() {
    graficoSetores.resize();
    graficoComparacao.resize();
});

window.addEventListener("afterprint", function() {
    graficoSetores.resize();
    graficoComparacao.resize();
});
</script>

</body>
</html>
