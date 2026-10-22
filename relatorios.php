
<?php
session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require "connection.php";

$tipoAcesso = $_SESSION["tipo"] ?? "Operador";
$pesquisa = trim($_GET["pesquisa"] ?? "");

if ($pesquisa !== "") {
    $termo = "%" . $pesquisa . "%";

    $stmt = $conn->prepare(
        "SELECT id_plan, nome_plan, produto, descricao, tipo,
        uni_med, colaborador, setor, quantidade, entrada, saida
        FROM planilhas
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
        FROM planilhas
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

function escapar($valor) {
    return htmlspecialchars((string) ($valor ?? ""), ENT_QUOTES, "UTF-8");
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

                <a href="relatorios.php" class="ativo">
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
                    <span class="subtitulo">CONSULTA E EXPORTAÇÃO</span>
                    <h2>Relatórios</h2>
                    <p>Consulte os registros e imprima os dados do sistema.</p>
                </div>

                <button
                    type="button"
                    class="botao-imprimir"
                    onclick="window.print()"
                >
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
                    <h3>Relatório de registros</h3>
                    <p>Dados consultados diretamente no banco de dados.</p>
                </div>

                <form method="GET" action="relatorios.php" class="pesquisa">
                    <input
                        type="search"
                        name="pesquisa"
                        placeholder="Pesquisar produto, setor ou colaborador..."
                        value="<?= escapar($pesquisa) ?>"
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
                                        <td><?= escapar($linha["nome_plan"]) ?></td>
                                        <td><?= escapar($linha["produto"]) ?></td>
                                        <td><?= escapar($linha["descricao"]) ?></td>
                                        <td><?= escapar($linha["tipo"]) ?></td>
                                        <td><?= (int) ($linha["quantidade"] ?? 0) ?></td>
                                        <td><?= escapar($linha["uni_med"]) ?></td>
                                        <td><?= escapar($linha["colaborador"]) ?></td>
                                        <td><?= escapar($linha["setor"]) ?></td>
                                        <td><?= escapar($linha["entrada"]) ?></td>
                                        <td><?= escapar($linha["saida"]) ?></td>
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
    </div>
</body>
</html>
