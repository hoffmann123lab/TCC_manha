
<?php
session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require "connection.php";

$idFuncionarioLogado = (int) $_SESSION["id_fun"];
$tipoAcesso = $_SESSION["tipo"] ?? "Operador";

function h($valor) {
    return htmlspecialchars((string) $valor, ENT_QUOTES, "UTF-8");
}

$condicaoUsuario = "";
$parametrosUsuario = [];

if ($tipoAcesso !== "Supervisor") {
    $condicaoUsuario = "WHERE p.idFuncionario = ?";
    $parametrosUsuario[] = $idFuncionarioLogado;
}

$sqlArquivos = "
    SELECT
        MIN(p.id_plan) AS id_arquivo,
        p.nome_plan,
        p.idFuncionario,
        DATE(p.entrada) AS data_importacao,
        f.nome_fun,
        COUNT(*) AS registros,
        COALESCE(SUM(p.quantidade), 0) AS total_quantidade,
        COUNT(DISTINCT p.produto) AS total_produtos
    FROM planilhas p
    LEFT JOIN funcionario f ON f.id_fun = p.idFuncionario
    $condicaoUsuario
    GROUP BY
        p.nome_plan,
        p.idFuncionario,
        DATE(p.entrada),
        f.nome_fun
    ORDER BY MAX(p.entrada) DESC, p.nome_plan ASC
";

$stmtArquivos = $conn->prepare($sqlArquivos);

if (!$stmtArquivos) {
    die("Erro ao consultar as planilhas: " . $conn->error);
}

if (!empty($parametrosUsuario)) {
    $stmtArquivos->bind_param("i", $idFuncionarioLogado);
}

$stmtArquivos->execute();

$resultadoArquivos = $stmtArquivos->get_result();
$arquivos = $resultadoArquivos->fetch_all(MYSQLI_ASSOC);

$stmtArquivos->close();

$arquivosPorId = [];

foreach ($arquivos as $arquivo) {
    $arquivosPorId[(int) $arquivo["id_arquivo"]] = $arquivo;
}

$selecionados = [];
$arquivosComparacao = [];
$totaisProdutos = [];
$produtosPorArquivo = [];
$setoresPorArquivo = [];
$tiposPorArquivo = [];
$totaisSetores = [];
$totaisTipos = [];

$erro = "";
$comparacaoRealizada = false;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $selecionadosRecebidos = $_POST["arquivos"] ?? [];

    if (!is_array($selecionadosRecebidos)) {
        $selecionadosRecebidos = [];
    }

    $selecionados = array_values(
        array_unique(
            array_filter(
                array_map("intval", $selecionadosRecebidos),
                function ($id) {
                    return $id > 0;
                }
            )
        )
    );

    $selecionados = array_values(
        array_filter($selecionados, function ($id) use ($arquivosPorId) {
            return isset($arquivosPorId[$id]);
        })
    );

    if (count($selecionados) < 2) {
        $erro = "Selecione pelo menos duas planilhas para realizar a comparação.";
    } else {
        foreach ($selecionados as $idArquivo) {
            $arquivo = $arquivosPorId[$idArquivo];

            $idFuncionarioArquivo = (int) $arquivo["idFuncionario"];
            $nomeArquivo = $arquivo["nome_plan"];
            $dataImportacao = $arquivo["data_importacao"];

            $sqlDados = "
                SELECT produto, setor, tipo, quantidade
                FROM planilhas
                WHERE idFuncionario = ?
                AND nome_plan = ?
                AND DATE(entrada) <=> ?
            ";

            $stmtDados = $conn->prepare($sqlDados);

            if (!$stmtDados) {
                $erro = "Erro ao consultar os dados da planilha: " . $conn->error;
                break;
            }

            $stmtDados->bind_param(
                "iss",
                $idFuncionarioArquivo,
                $nomeArquivo,
                $dataImportacao
            );

            $stmtDados->execute();

            $resultadoDados = $stmtDados->get_result();

            $totalQuantidade = 0;
            $totalRegistros = 0;
            $produtosArquivo = [];

            $rotuloArquivo = $nomeArquivo
                . " - "
                . ($dataImportacao ?? "Data não informada");

            if ($tipoAcesso === "Supervisor") {
                $rotuloArquivo .= " - " . ($arquivo["nome_fun"] ?? "Funcionário");
            }

            while ($linha = $resultadoDados->fetch_assoc()) {
                $produto = trim($linha["produto"] ?? "");
                $setor = trim($linha["setor"] ?? "");
                $tipo = trim($linha["tipo"] ?? "");
                $quantidade = (float) $linha["quantidade"];

                if ($produto === "") {
                    $produto = "Produto não informado";
                }

                if ($setor === "") {
                    $setor = "Setor não informado";
                }

                if ($tipo === "") {
                    $tipo = "Tipo não informado";
                }

                $totalQuantidade += $quantidade;
                $totalRegistros++;

                $produtosArquivo[$produto] = true;

                if (!isset($produtosPorArquivo[$rotuloArquivo][$produto])) {
                    $produtosPorArquivo[$rotuloArquivo][$produto] = 0;
                }

                $produtosPorArquivo[$rotuloArquivo][$produto] += $quantidade;

                if (!isset($setoresPorArquivo[$rotuloArquivo][$setor])) {
                    $setoresPorArquivo[$rotuloArquivo][$setor] = 0;
                }

                $setoresPorArquivo[$rotuloArquivo][$setor] += $quantidade;

                if (!isset($tiposPorArquivo[$rotuloArquivo][$tipo])) {
                    $tiposPorArquivo[$rotuloArquivo][$tipo] = 0;
                }

                $tiposPorArquivo[$rotuloArquivo][$tipo] += $quantidade;

                if (!isset($totaisProdutos[$produto])) {
                    $totaisProdutos[$produto] = 0;
                }

                $totaisProdutos[$produto] += $quantidade;

                if (!isset($totaisSetores[$setor])) {
                    $totaisSetores[$setor] = 0;
                }

                $totaisSetores[$setor] += $quantidade;

                if (!isset($totaisTipos[$tipo])) {
                    $totaisTipos[$tipo] = 0;
                }

                $totaisTipos[$tipo] += $quantidade;
            }

            $stmtDados->close();

            $arquivosComparacao[] = [
                "nome" => $nomeArquivo,
                "rotulo" => $rotuloArquivo,
                "registros" => $totalRegistros,
                "produtos" => count($produtosArquivo),
                "quantidade" => $totalQuantidade
            ];
        }

        if (empty($erro) && count($arquivosComparacao) >= 2) {
            $comparacaoRealizada = true;
        }
    }
}

arsort($totaisProdutos);

$produtosPrincipais = array_slice($totaisProdutos, 0, 12, true);
$rotulosProdutos = array_keys($produtosPrincipais);

$dadosProdutos = [];

foreach ($produtosPorArquivo as $nome => $produtos) {
    $valores = [];

    foreach ($rotulosProdutos as $produto) {
        $valores[] = $produtos[$produto] ?? 0;
    }

    $dadosProdutos[] = [
        "label" => $nome,
        "data" => $valores
    ];
}

$rotulosSetores = array_keys($totaisSetores);
$dadosSetores = [];

foreach ($setoresPorArquivo as $nome => $setores) {
    $valores = [];

    foreach ($rotulosSetores as $setor) {
        $valores[] = $setores[$setor] ?? 0;
    }

    $dadosSetores[] = [
        "label" => $nome,
        "data" => $valores
    ];
}

$rotulosTipos = array_keys($totaisTipos);
$valoresTipos = array_values($totaisTipos);

$totalGeral = array_sum($totaisProdutos);
$totalArquivos = count($arquivosComparacao);
$totalProdutosDiferentes = count($totaisProdutos);
$totalSetores = count($totaisSetores);
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comparar Planilhas - SIGEP-EPI</title>
    <link rel="stylesheet" href="comparar_planilhas.css">
</head>

<body>

    <main class="pagina">

        <header class="topo">
            <div>
                <span class="subtitulo">SIGEP-EPI / ANÁLISES</span>
                <h1>Comparação de Planilhas</h1>
                <p>
                    Selecione arquivos importados para comparar seus dados,
                    produtos, quantidades e setores.
                </p>
            </div>

            <div class="acoes-topo">
                <?php if ($comparacaoRealizada): ?>
                    <button type="button" class="botao-pdf" onclick="window.print()">
                        Salvar em PDF
                    </button>
                <?php endif; ?>

                <a href="relatorios.php" class="botao-voltar">
                    Voltar
                </a>
            </div>
        </header>

        <section class="painel selecao">

            <div class="titulo-painel">
                <span>SELEÇÃO</span>
                <h2>Escolha as planilhas</h2>
                <p>
                    Você pode selecionar arquivos com produtos diferentes.
                    Marque pelo menos duas planilhas.
                </p>
            </div>

            <?php if (empty($arquivos)): ?>

                <div class="mensagem-vazia">
                    Nenhuma planilha importada foi encontrada.
                </div>

            <?php else: ?>

                <form method="POST" action="comparar_planilhas.php">

                    <div class="lista-arquivos">

                        <?php foreach ($arquivos as $arquivo): ?>

                            <?php
                            $idArquivo = (int) $arquivo["id_arquivo"];
                            $estaSelecionado = in_array($idArquivo, $selecionados, true);
                            ?>

                            <label class="arquivo-item">

                                <input
                                    type="checkbox"
                                    name="arquivos[]"
                                    value="<?= $idArquivo ?>"
                                    <?= $estaSelecionado ? "checked" : "" ?>
                                >

                                <div class="arquivo-informacoes">
                                    <strong><?= h($arquivo["nome_plan"] ?: "Planilha sem nome") ?></strong>

                                    <span>
                                        Importação:
                                        <?= h($arquivo["data_importacao"] ?? "Data não informada") ?>
                                    </span>

                                    <?php if ($tipoAcesso === "Supervisor"): ?>
                                        <span>
                                            Funcionário:
                                            <?= h($arquivo["nome_fun"] ?? "Não identificado") ?>
                                        </span>
                                    <?php endif; ?>

                                    <span>
                                        <?= (int) $arquivo["registros"] ?> registros
                                        |
                                        <?= (int) $arquivo["total_produtos"] ?> produtos diferentes
                                    </span>
                                </div>

                                <div class="arquivo-quantidade">
                                    <span>Quantidade total</span>
                                    <strong>
                                        <?= number_format((float) $arquivo["total_quantidade"], 0, ",", ".") ?>
                                    </strong>
                                </div>

                            </label>

                        <?php endforeach; ?>

                    </div>

                    <?php if ($erro !== ""): ?>
                        <div class="mensagem-erro">
                            <?= h($erro) ?>
                        </div>
                    <?php endif; ?>

                    <button type="submit" class="botao-comparar">
                        Comparar planilhas selecionadas
                    </button>

                </form>

            <?php endif; ?>

        </section>

        <?php if ($comparacaoRealizada): ?>

            <section class="resumo">
                <div class="indicador destaque">
                    <span>Planilhas comparadas</span>
                    <strong><?= $totalArquivos ?></strong>
                </div>

                <div class="indicador">
                    <span>Quantidade total</span>
                    <strong><?= number_format($totalGeral, 0, ",", ".") ?></strong>
                </div>

                <div class="indicador">
                    <span>Produtos diferentes</span>
                    <strong><?= $totalProdutosDiferentes ?></strong>
                </div>

                <div class="indicador">
                    <span>Setores encontrados</span>
                    <strong><?= $totalSetores ?></strong>
                </div>
            </section>

            <section class="painel">

                <div class="titulo-painel">
                    <span>COMPARAÇÃO GERAL</span>
                    <h2>Resumo das planilhas selecionadas</h2>
                    <p>Confira os principais números de cada arquivo.</p>
                </div>

                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Planilha</th>
                                <th>Registros</th>
                                <th>Produtos diferentes</th>
                                <th>Quantidade total</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($arquivosComparacao as $arquivo): ?>
                                <tr>
                                    <td><?= h($arquivo["rotulo"]) ?></td>
                                    <td><?= (int) $arquivo["registros"] ?></td>
                                    <td><?= (int) $arquivo["produtos"] ?></td>
                                    <td>
                                        <?= number_format($arquivo["quantidade"], 0, ",", ".") ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </section>

            <section class="painel">

                <div class="titulo-painel">
                    <span>GRÁFICO 01</span>
                    <h2>Quantidade por planilha</h2>
                    <p>Compare a quantidade total registrada em cada arquivo.</p>
                </div>

                <div class="area-grafico">
                    <canvas id="graficoPlanilhas"></canvas>
                </div>

            </section>

            <section class="painel">

                <div class="titulo-painel">
                    <span>GRÁFICO 02</span>
                    <h2>Comparação por produto</h2>
                    <p>
                        Veja como as quantidades dos produtos estão distribuídas
                        entre as planilhas selecionadas.
                    </p>
                </div>

                <div class="area-grafico">
                    <canvas id="graficoProdutos"></canvas>
                </div>

            </section>

            <section class="painel">

                <div class="titulo-painel">
                    <span>GRÁFICO 03</span>
                    <h2>Comparação por setor</h2>
                    <p>Compare a distribuição das quantidades entre os setores.</p>
                </div>

                <div class="area-grafico">
                    <canvas id="graficoSetores"></canvas>
                </div>

            </section>

            <section class="painel">

                <div class="titulo-painel">
                    <span>GRÁFICO 04</span>
                    <h2>Distribuição por tipo de EPI</h2>
                    <p>Visualize a quantidade total de cada tipo de equipamento.</p>
                </div>

                <div class="area-grafico">
                    <canvas id="graficoTipos"></canvas>
                </div>

            </section>

        <?php endif; ?>

        <footer>
            SIGEP-EPI - Sistema Inteligente de Gestão e Análise de EPI's
        </footer>

    </main>

    <?php if ($comparacaoRealizada): ?>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

        <script>
            const nomesPlanilhas = <?= json_encode(
                array_column($arquivosComparacao, "rotulo"),
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) ?>;

            const quantidadesPlanilhas = <?= json_encode(
                array_column($arquivosComparacao, "quantidade")
            ) ?>;

            const rotulosProdutos = <?= json_encode(
                $rotulosProdutos,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) ?>;

            const dadosProdutos = <?= json_encode(
                $dadosProdutos,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) ?>;

            const rotulosSetores = <?= json_encode(
                $rotulosSetores,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) ?>;

            const dadosSetores = <?= json_encode(
                $dadosSetores,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) ?>;

            const rotulosTipos = <?= json_encode(
                $rotulosTipos,
                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
            ) ?>;

            const valoresTipos = <?= json_encode($valoresTipos) ?>;

            new Chart(document.getElementById("graficoPlanilhas"), {
                type: "bar",
                data: {
                    labels: nomesPlanilhas,
                    datasets: [{
                        label: "Quantidade total",
                        data: quantidadesPlanilhas,
                        borderWidth: 1
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

            new Chart(document.getElementById("graficoProdutos"), {
                type: "bar",
                data: {
                    labels: rotulosProdutos,
                    datasets: dadosProdutos.map(function(item) {
                        return {
                            label: item.label,
                            data: item.data,
                            borderWidth: 1
                        };
                    })
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            new Chart(document.getElementById("graficoSetores"), {
                type: "bar",
                data: {
                    labels: rotulosSetores,
                    datasets: dadosSetores.map(function(item) {
                        return {
                            label: item.label,
                            data: item.data,
                            borderWidth: 1
                        };
                    })
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            new Chart(document.getElementById("graficoTipos"), {
                type: "doughnut",
                data: {
                    labels: rotulosTipos,
                    datasets: [{
                        label: "Quantidade",
                        data: valoresTipos,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        </script>

    <?php endif; ?>

</body>

</html>