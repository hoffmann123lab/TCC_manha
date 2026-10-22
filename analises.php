
<?php
session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require "connection.php";

$totalRegistros = 0;
$totalQuantidade = 0;
$totalSetores = 0;
$quantidadesPorSetor = [];

$resultado = $conn->query(
    "SELECT COUNT(*) AS total,
    COALESCE(SUM(quantidade), 0) AS quantidade
    FROM planilhas"
);

if ($resultado) {
    $dados = $resultado->fetch_assoc();
    $totalRegistros = (int) $dados["total"];
    $totalQuantidade = (int) $dados["quantidade"];
}

$resultadoSetores = $conn->query(
    "SELECT setor, COUNT(*) AS total
    FROM planilhas
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
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análises - SIGEP-EPI</title>
    <link rel="stylesheet" href="analises.css">
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

                <?php if (($_SESSION["tipo"] ?? "") === "Supervisor"): ?>
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
                    <span class="subtitulo">INDICADORES DO SISTEMA</span>
                    <h2>Análises</h2>
                    <p>Acompanhe os dados cadastrados no SIGEP-EPI.</p>
                </div>

            </header>

            <section class="cards">
                <article class="card">
                    <div class="card-icone azul">▤</div>
                    <div class="card-conteudo">
                        <span>Total de registros</span>
                        <strong><?= number_format($totalRegistros, 0, ",", ".") ?></strong>
                    </div>
                </article>

                <article class="card">
                    <div class="card-icone verde">▦</div>
                    <div class="card-conteudo">
                        <span>Quantidade registrada</span>
                        <strong><?= number_format($totalQuantidade, 0, ",", ".") ?></strong>
                    </div>
                </article>

                <article class="card">
                    <div class="card-icone roxo">▥</div>
                    <div class="card-conteudo">
                        <span>Setores com registros</span>
                        <strong><?= number_format($totalSetores, 0, ",", ".") ?></strong>
                    </div>
                </article>
            </section>

            <section class="painel">
                <div class="painel-topo">
                    <div>
                        <h3>Registros por setor</h3>
                        <p class="descricao">
                            Quantidade de registros cadastrados em cada setor.
                        </p>
                    </div>

                    <span class="contador-setores">
                        <?= $totalSetores ?>
                        <?= $totalSetores === 1 ? "setor" : "setores" ?>
                    </span>
                </div>

                <?php if (count($quantidadesPorSetor) > 0): ?>
                    <div class="grafico">
                        <?php foreach ($quantidadesPorSetor as $indice => $setor): ?>
                            <?php
                            $totalSetor = (int) $setor["total"];
                            $porcentagem = ($totalSetor / $maiorTotal) * 100;
                            ?>

                            <div class="barra-item">
                                <div class="barra-info">
                                    <div class="nome-setor">
                                        <span class="numero-setor">
                                            <?= str_pad($indice + 1, 2, "0", STR_PAD_LEFT) ?>
                                        </span>
                                        <span class="texto-setor">
                                            <?= htmlspecialchars(
                                                $setor["setor"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </span>
                                    </div>

                                    <strong>
                                        <?= number_format($totalSetor, 0, ",", ".") ?>
                                        <?= $totalSetor === 1 ? "registro" : "registros" ?>
                                    </strong>
                                </div>

                                <div class="barra-fundo">
                                    <div
                                        class="barra"
                                        style="width: <?= $porcentagem ?>%;"
                                    ></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="vazio">
                        <div class="vazio-icone">▤</div>
                        <h4>Nenhum registro disponível</h4>
                        <p>
                            Cadastre registros com um setor informado na página Produção
                            para visualizar as análises.
                        </p>
                        <a href="producao.php" class="botao-primario">
                            Cadastrar registro
                        </a>
                    </div>
                <?php endif; ?>
            </section>

            <section class="painel observacao">
                <div class="informacao-icone">i</div>
                <div>
                    <h3>Sobre os indicadores</h3>
                    <p>
                        Os indicadores são calculados com base nos dados armazenados
                        na tabela planilhas. Cadastre registros na página Produção
                        para atualizar as análises.
                    </p>
                </div>
            </section>

            <footer class="rodape">
                <strong>SIGEP-EPI</strong>
                <span>Sistema de Gestão de Produção</span>
            </footer>
        </main>
    </div>
</body>
</html>
