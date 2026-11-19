
<?php

session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: logar.php");
    exit;
}

require_once "connection.php";

$nome = $_SESSION["nome_fun"] ?? "Funcionário";
$tipoAcesso = $_SESSION["tipo"] ?? "Operador";
$idFuncionario = (int) $_SESSION["id_fun"];
$inicial = strtoupper(substr($nome, 0, 1));

$ehSupervisor = $tipoAcesso === "Supervisor";

if ($ehSupervisor) {

    $resultadoFuncionarios = $conn->query(
        "SELECT
            COUNT(*) AS total_funcionarios,
            SUM(CASE WHEN tipo = 'Supervisor' THEN 1 ELSE 0 END) AS total_supervisores,
            SUM(CASE WHEN tipo = 'Operador' THEN 1 ELSE 0 END) AS total_operadores
         FROM funcionario"
    );

    if (!$resultadoFuncionarios) {
        die("Erro ao consultar funcionários: " . $conn->error);
    }

    $dadosFuncionarios = $resultadoFuncionarios->fetch_assoc();

    $totalFuncionarios = (int) $dadosFuncionarios["total_funcionarios"];
    $totalSupervisores = (int) $dadosFuncionarios["total_supervisores"];
    $totalOperadores = (int) $dadosFuncionarios["total_operadores"];

    $resultadoTotalPlanilhas = $conn->query(
        "SELECT COUNT(*) AS total
         FROM (
             SELECT nome_plan, idFuncionario, DATE(entrada)
             FROM planilhas
             GROUP BY nome_plan, idFuncionario, DATE(entrada)
         ) AS importacoes"
    );

    if (!$resultadoTotalPlanilhas) {
        die("Erro ao consultar planilhas: " . $conn->error);
    }

    $totalPlanilhas = (int) $resultadoTotalPlanilhas
        ->fetch_assoc()["total"];

    $sqlImportacoes = "
        SELECT
            p.nome_plan,
            p.idFuncionario,
            f.nome_fun,
            DATE(p.entrada) AS data_importacao,
            COUNT(*) AS total_registros,
            COALESCE(SUM(p.quantidade), 0) AS total_unidades,
            MAX(p.entrada) AS ultima_entrada
        FROM planilhas p
        LEFT JOIN funcionario f
            ON p.idFuncionario = f.id_fun
        GROUP BY
            p.nome_plan,
            p.idFuncionario,
            f.nome_fun,
            DATE(p.entrada)
        ORDER BY ultima_entrada DESC
        LIMIT 8
    ";

    $importacoes = $conn->query($sqlImportacoes);

    if (!$importacoes) {
        die("Erro ao consultar importações: " . $conn->error);
    }

} else {

    $filtro = " WHERE idFuncionario = ?";
    $parametros = [$idFuncionario];
    $tipos = "i";

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

    $stmtIndicadores->bind_param($tipos, ...$parametros);
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

    $stmtAtividades->bind_param($tipos, ...$parametros);
    $stmtAtividades->execute();

    $atividades = $stmtAtividades->get_result();

}

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

                <?php if (!$ehSupervisor): ?>

                    <a href="producao.php">
                        <span class="icone-menu">▤</span>
                        <span>Produção</span>
                    </a>

                <?php endif; ?>

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

                <?php if ($ehSupervisor): ?>

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

            <a href="logar.php" class="sair">
                <span class="icone-menu">↪</span>
                <span>Sair</span>
            </a>

        </aside>

        <main class="conteudo">

            <header class="topo">

                <div>

                    <span class="subtitulo">
                        <?= $ehSupervisor ? "PAINEL ADMINISTRATIVO" : "PAINEL PRINCIPAL" ?>
                    </span>

                    <h2>
                        Olá, <?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>!
                    </h2>

                    <p>
                        <?= $ehSupervisor
                            ? "Acompanhe os funcionários e as planilhas importadas no sistema."
                            : "O que você deseja fazer hoje?" ?>
                    </p>

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
                            <?= $ehSupervisor ? "Supervisor" : "Funcionário / Operador" ?>
                        </span>

                    </div>

                </div>

            </header>

            <?php if ($ehSupervisor): ?>

                <section class="cards">

                    <div class="card">

                        <div class="card-icone">♙</div>

                        <div>
                            <span>Total de funcionários</span>
                            <strong><?= $totalFuncionarios ?></strong>
                        </div>

                    </div>

                    <div class="card">

                        <div class="card-icone">★</div>

                        <div>
                            <span>Supervisores</span>
                            <strong><?= $totalSupervisores ?></strong>
                        </div>

                    </div>

                    <div class="card">

                        <div class="card-icone">♙</div>

                        <div>
                            <span>Operadores</span>
                            <strong><?= $totalOperadores ?></strong>
                        </div>

                    </div>

                    <div class="card">

                        <div class="card-icone">▦</div>

                        <div>
                            <span>Planilhas importadas</span>
                            <strong><?= $totalPlanilhas ?></strong>
                        </div>

                    </div>

                </section>

                <section class="acoes">

                    <h3>Gerenciamento do sistema</h3>

                    <div class="acoes-grid">

                        <a href="usuarios.php" class="acao">

                            <div class="acao-icone">♙</div>

                            <div class="acao-texto">
                                <strong>Funcionários</strong>
                                <span>Consultar e gerenciar os funcionários cadastrados</span>
                            </div>

                            <b>→</b>

                        </a>

                        <a href="planilhas.php" class="acao">

                            <div class="acao-icone">▦</div>

                            <div class="acao-texto">
                                <strong>Planilhas importadas</strong>
                                <span>Consultar todas as planilhas da equipe</span>
                            </div>

                            <b>→</b>

                        </a>

                    </div>

                </section>

                <section class="atividade">

                    <div class="secao-titulo">

                        <div>
                            <h3>Planilhas importadas recentemente</h3>
                            <span>Últimas importações realizadas pelos funcionários</span>
                        </div>

                    </div>

                    <?php if ($importacoes->num_rows > 0): ?>

                        <div class="lista-atividades">

                            <?php while ($importacao = $importacoes->fetch_assoc()): ?>

                                <div class="atividade-item">

                                    <div class="atividade-icone">▦</div>

                                    <div class="atividade-info">

                                        <strong>
                                            <?= htmlspecialchars(
                                                $importacao["nome_plan"] ?? "Planilha sem nome",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </strong>

                                        <span>
                                            Funcionário:
                                            <?= htmlspecialchars(
                                                $importacao["nome_fun"] ?? "Não identificado",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </span>

                                        <span>
                                            <?= (int) $importacao["total_registros"] ?> registros
                                        </span>

                                    </div>

                                    <div class="atividade-data">

                                        <strong>
                                            <?= (int) $importacao["total_unidades"] ?> unidades
                                        </strong>

                                        <span>
                                            <?= !empty($importacao["data_importacao"])
                                                ? date("d/m/Y", strtotime($importacao["data_importacao"]))
                                                : "Data não informada" ?>
                                        </span>

                                    </div>

                                </div>

                            <?php endwhile; ?>

                        </div>

                    <?php else: ?>

                        <div class="atividade-vazia">

                            <div>▦</div>

                            <strong>Nenhuma planilha importada</strong>

                            <span>
                                As importações realizadas pela equipe aparecerão aqui.
                            </span>

                        </div>

                    <?php endif; ?>

                </section>

            <?php else: ?>

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
                                <span>Consultar suas planilhas no sistema</span>
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
                            <span>Últimos registros da sua produção</span>
                        </div>

                    </div>

                    <?php if ($atividades->num_rows > 0): ?>

                        <div class="lista-atividades">

                            <?php while ($atividade = $atividades->fetch_assoc()): ?>

                                <div class="atividade-item">

                                    <div class="atividade-icone">▤</div>

                                    <div class="atividade-info">

                                        <strong>
                                            <?= htmlspecialchars(
                                                $atividade["produto"] ?? "",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </strong>

                                        <span>
                                            Planilha:
                                            <?= htmlspecialchars(
                                                $atividade["nome_plan"] ?? "",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </span>

                                        <span>
                                            Colaborador:
                                            <?= htmlspecialchars(
                                                $atividade["colaborador"] ?? "",
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </span>

                                    </div>

                                    <div class="atividade-data">

                                        <strong>
                                            <?= (int) $atividade["quantidade"] ?> unidades
                                        </strong>

                                        <span>
                                            <?= !empty($atividade["entrada"])
                                                ? date("d/m/Y", strtotime($atividade["entrada"]))
                                                : "" ?>
                                        </span>

                                    </div>

                                </div>

                            <?php endwhile; ?>

                        </div>

                    <?php else: ?>

                        <div class="atividade-vazia">

                            <div>▤</div>

                            <strong>Nenhuma atividade registrada</strong>

                            <span>
                                As movimentações aparecerão aqui.
                            </span>

                        </div>

                    <?php endif; ?>

                </section>

            <?php endif; ?>

        </main>

    </div>

</body>

</html>

<?php

if (!$ehSupervisor) {
    $stmtAtividades->close();
}

$conn->close();

?>
