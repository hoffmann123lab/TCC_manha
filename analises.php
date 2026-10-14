
<?php

require "connection.php";

$totalRegistros = 0;
$totalQuantidade = 0;
$totalSetores = 0;
$quantidadesPorSetor = [];

$resultado = $conn->query(
    "SELECT COUNT(*) AS total,
    COALESCE(SUM(quantidade), 0) AS quantidade
    FROM Planilhas"
);

if ($resultado) {
    $dados = $resultado->fetch_assoc();
    $totalRegistros = (int) $dados["total"];
    $totalQuantidade = (int) $dados["quantidade"];
}

$resultadoSetores = $conn->query(
    "SELECT setor, COUNT(*) AS total
    FROM Planilhas
    WHERE setor IS NOT NULL AND setor <> ''
    GROUP BY setor
    ORDER BY total DESC"
);

if ($resultadoSetores) {
    while ($linha = $resultadoSetores->fetch_assoc()) {
        $quantidadesPorSetor[] = $linha;
    }
}

$totalSetores = count($quantidadesPorSetor);

$maiorTotal = 1;

foreach ($quantidadesPorSetor as $setor) {
    if ((int) $setor["total"] > $maiorTotal) {
        $maiorTotal = (int) $setor["total"];
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análises - SIGEP-EPI</title>
    <link rel="stylesheet" href="analises.css">
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
            <a href="analises.php" class="ativo">▥ <span>Análises</span></a>
            <a href="planilhas.php">▦ <span>Planilhas</span></a>
            <a href="relatorios.php">▧ <span>Relatórios</span></a>
            <a href="configuracoes.php">⚙ <span>Configurações</span></a>
        </nav>

        <a href="index.php" class="sair">↪ <span>Sair</span></a>
    </aside>

    <main class="conteudo">
        <header class="topo">
            <div>
                <span class="subtitulo">INDICADORES DO SISTEMA</span>
                <h2>Análises</h2>
                <p>Acompanhe os dados cadastrados no SIGEP-EPI.</p>
            </div>

            <a href="dashboard.php" class="botao-voltar">Voltar ao início</a>
        </header>

        <section class="cards">
            <article class="card">
                <div class="card-icone">▦</div>
                <div>
                    <span>Total de registros</span>
                    <strong><?= $totalRegistros ?></strong>
                </div>
            </article>

            <article class="card">
                <div class="card-icone">📦</div>
                <div>
                    <span>Quantidade registrada</span>
                    <strong><?= $totalQuantidade ?></strong>
                </div>
            </article>

            <article class="card">
                <div class="card-icone">▤</div>
                <div>
                    <span>Setores com registros</span>
                    <strong><?= $totalSetores ?></strong>
                </div>
            </article>
        </section>

        <section class="painel">
            <h3>Registros por setor</h3>
            <p class="descricao">Quantidade de registros cadastrados em cada setor.</p>

            <?php if (count($quantidadesPorSetor) > 0): ?>

                <div class="grafico">
                    <?php foreach ($quantidadesPorSetor as $setor): ?>

                        <div class="barra-item">
                            <div class="barra-info">
                                <span><?= htmlspecialchars($setor["setor"], ENT_QUOTES, "UTF-8") ?></span>
                                <strong><?= (int) $setor["total"] ?></strong>
                            </div>

                            <div class="barra-fundo">
                                <div
                                    class="barra"
                                    style="width: <?= ((int) $setor["total"] / $maiorTotal) * 100 ?>%;"
                                ></div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                </div>

            <?php else: ?>

                <div class="vazio">
                    Nenhum registro por setor disponível para análise.
                </div>

            <?php endif; ?>
        </section>

        <section class="painel observacao">
            <h3>Sobre os indicadores</h3>
            <p>
                Os indicadores são calculados com base nos dados armazenados
                na tabela Planilhas. Cadastre registros na página Produção
                para atualizar as análises.
            </p>
        </section>
    </main>
</body>
</html>
