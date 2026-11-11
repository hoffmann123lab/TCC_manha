
<?php
session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require_once "connection.php";

$nome = $_SESSION["nome_fun"] ?? "Funcionário";
$tipoAcesso = $_SESSION["tipo"] ?? "Operador";
$idFuncionario = (int) $_SESSION["id_fun"];
$inicial = strtoupper(substr($nome, 0, 1));

$filtro = "";
$parametros = [];
$tipos = "";

if ($tipoAcesso !== "Supervisor") {
    $filtro = " WHERE idFuncionario = ?";
    $parametros[] = $idFuncionario;
    $tipos = "i";
}

$sqlIndicadores = "
    SELECT
        COUNT(*) AS total_produtos,
        COALESCE(SUM(quantidade_total), 0) AS total_unidades,
        COALESCE(SUM(CASE WHEN quantidade_total <= 5 THEN 1 ELSE 0 END), 0) AS estoque_baixo,
        COALESCE(SUM(CASE WHEN quantidade_total <= 2 THEN 1 ELSE 0 END), 0) AS estoque_critico
    FROM (
        SELECT
            produto,
            cor,
            tamanho,
            setor,
            SUM(quantidade) AS quantidade_total
        FROM planilhas
        $filtro
        GROUP BY produto, cor, tamanho, setor
    ) AS estoque
";

$stmtIndicadores = $conn->prepare($sqlIndicadores);

if (!$stmtIndicadores) {
    die("Erro ao consultar indicadores: " . $conn->error);
}

if ($tipos !== "") {
    $stmtIndicadores->bind_param($tipos, ...$parametros);
}

$stmtIndicadores->execute();
$indicadores = $stmtIndicadores->get_result()->fetch_assoc();

$totalProdutos = (int) $indicadores["total_produtos"];
$totalUnidades = (int) $indicadores["total_unidades"];
$estoqueBaixo = (int) $indicadores["estoque_baixo"];
$estoqueCritico = (int) $indicadores["estoque_critico"];

$stmtIndicadores->close();

$sqlAtividades = "
    SELECT
        produto,
        nome_plan,
        quantidade,
        entrada,
        colaborador
    FROM planilhas
    $filtro
    ORDER BY id_plan DESC
    LIMIT 5
";

$stmtAtividades = $conn->prepare($sqlAtividades);

if (!$stmtAtividades) {
    die("Erro ao consultar atividades: " . $conn->error);
}

if ($tipos !== "") {
    $stmtAtividades->bind_param($tipos, ...$parametros);
}

$stmtAtividades->execute();
$atividades = $stmtAtividades->get_result();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIGEP-EPI</title>
    <link rel="stylesheet" href="dashboard.css">
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
                <a href="dashboard.php" class="ativo">
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
                    <span class="icone-menu">♙</span>
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
                    <span class="subtitulo">PAINEL PRINCIPAL</span>

                    <h2>
                        Olá, <?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>!
                    </h2>

                    <p>O que você deseja fazer hoje?</p>
                </div>

                <div class="usuario">

                    <div class="avatar">
                        <?= htmlspecialchars($inicial, ENT_QUOTES, "UTF-8") ?>
                    </div>

                    <div class="usuario-info">

                        <strong>
                            <?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>
                        </strong>

                        <span>
                            <?= $tipoAcesso === "Supervisor" ? "Supervisor" : "Funcionário / Operador" ?>
                        </span>

                    </div>

                </div>

            </header>

            <section class="cards">

                <div class="card">
                    <div class="card-icone">📦</div>

                    <div>
                        <span>Total de unidades</span>
                        <strong><?= $totalUnidades ?></strong>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icone">▤</div>

                    <div>
                        <span>Produtos cadastrados</span>
                        <strong><?= $totalProdutos ?></strong>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icone">⚠</div>

                    <div>
                        <span>Estoque baixo</span>
                        <strong><?= $estoqueBaixo ?></strong>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icone">!</div>

                    <div>
                        <span>Estoque crítico</span>
                        <strong><?= $estoqueCritico ?></strong>
                    </div>
                </div>

            </section>

            <section class="acoes">

                <h3>Acesso rápido</h3>

                <div class="acoes-grid">

                    <a href="producao.php" class="acao">
                        <div class="acao-icone">▤</div>

                        <div class="acao-texto">
                            <strong>Produção</strong>
                            <span>Consultar informações de produção</span>
                        </div>

                        <b>→</b>
                    </a>

                    <a href="planilhas.php" class="acao">
                        <div class="acao-icone">▦</div>

                        <div class="acao-texto">
                            <strong>Planilhas</strong>
                            <span>Consultar planilhas do sistema</span>
                        </div>

                        <b>→</b>
                    </a>

                    <a href="analises.php" class="acao">
                        <div class="acao-icone">▥</div>

                        <div class="acao-texto">
                            <strong>Análises</strong>
                            <span>Visualizar indicadores</span>
                        </div>

                        <b>→</b>
                    </a>

                    <a href="relatorios.php" class="acao">
                        <div class="acao-icone">▧</div>

                        <div class="acao-texto">
                            <strong>Relatórios</strong>
                            <span>Consultar relatórios</span>
                        </div>

                        <b>→</b>
                    </a>

                </div>

            </section>

            <section class="atividade">

                <div class="secao-titulo">
                    <div>
                        <h3>Atividade recente</h3>
                        <span>Últimos registros importados no sistema</span>
                    </div>
                </div>

                <?php if ($atividades->num_rows > 0): ?>

                    <div class="lista-atividades">

                        <?php while ($atividade = $atividades->fetch_assoc()): ?>

                            <div class="atividade-item">

                                <div class="atividade-icone"> _ </div>

                                <div class="atividade-info">

                                    <strong>
                                        <?= htmlspecialchars($atividade["produto"] ?? "", ENT_QUOTES, "UTF-8") ?>
                                    </strong>

                                    <span>
                                        Planilha:
                                        <?= htmlspecialchars($atividade["nome_plan"] ?? "", ENT_QUOTES, "UTF-8") ?>
                                    </span>

                                    <span>
                                        Colaborador:
                                        <?= htmlspecialchars($atividade["colaborador"] ?? "", ENT_QUOTES, "UTF-8") ?>
                                    </span>

                                </div>

                                <div class="atividade-data">

                                    <strong>
                                        <?= (int) $atividade["quantidade"] ?> unidades
                                    </strong>

                                    <span>
                                        <?= !empty($atividade["entrada"]) ? date("d/m/Y", strtotime($atividade["entrada"])) : "" ?>
                                    </span>

                                </div>

                            </div>

                        <?php endwhile; ?>

                    </div>

                <?php else: ?>

                    <div class="atividade-vazia">
                        <div>▤</div>
                        <strong>Nenhuma atividade registrada</strong>
                        <span>As movimentações aparecerão aqui.</span>
                    </div>

                <?php endif; ?>

            </section>

        </main>

    </div>
</body>
</html>

<?php
$stmtAtividades->close();
$conn->close();
?>
