
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
        "SELECT id_plan, nome_plan, produto, descricao, tipo, uni_med,
        colaborador, setor, quantidade, entrada, saida
        FROM planilhas
        WHERE nome_plan LIKE ?
        OR produto LIKE ?
        OR descricao LIKE ?
        OR setor LIKE ?
        ORDER BY id_plan DESC"
    );

    $stmt->bind_param("ssss", $termo, $termo, $termo, $termo);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conn->query(
        "SELECT id_plan, nome_plan, produto, descricao, tipo, uni_med,
        colaborador, setor, quantidade, entrada, saida
        FROM planilhas
        ORDER BY id_plan DESC"
    );
}

$registros = [];

if ($resultado) {
    while ($linha = $resultado->fetch_assoc()) {
        $registros[] = $linha;
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
    <title>Planilhas - SIGEP-EPI</title>
    <link rel="stylesheet" href="planilhas.css">
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

                <a href="planilhas.php" class="ativo">
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
                    <span class="subtitulo">GERENCIAMENTO DE DADOS</span>
                    <h2>Planilhas</h2>
                    <p>Consulte e pesquise os registros armazenados no sistema.</p>
                </div>

                <a href="producao.php" class="botao-novo">
                    <span class="mais">+</span>
                    Novo registro
                </a>
            </header>

            <section class="indicadores">
                <article class="indicador">
                    <div class="indicador-icone">▦</div>
                    <div>
                        <span>Registros encontrados</span>
                        <strong><?= count($registros) ?></strong>
                    </div>
                </article>

                <article class="indicador">
                    <div class="indicador-icone azul">⌕</div>
                    <div>
                        <span>Pesquisa atual</span>
                        <strong class="texto-pesquisa">
                            <?= $pesquisa !== "" ? "Filtrada" : "Todos os registros" ?>
                        </strong>
                    </div>
                </article>
            </section>

            <section class="painel">
                <div class="painel-topo">
                    <div>
                        <h3>Planilhas cadastradas</h3>
                        <p>Visualize os registros armazenados no sistema.</p>
                    </div>

                    <span class="contador">
                        <?= count($registros) ?>
                        <?= count($registros) === 1 ? "registro" : "registros" ?>
                    </span>
                </div>

                <form method="GET" action="planilhas.php" class="pesquisa">
                    <div class="campo-pesquisa">
                        <span class="icone-pesquisa">⌕</span>
                        <input
                            type="search"
                            name="pesquisa"
                            placeholder="Pesquisar nome, produto, descrição ou setor..."
                            value="<?= escapar($pesquisa) ?>"
                        >
                    </div>

                    <button type="submit">Pesquisar</button>

                    <?php if ($pesquisa !== ""): ?>
                        <a href="planilhas.php" class="limpar">Limpar</a>
                    <?php endif; ?>
                </form>

                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nome da planilha</th>
                                <th>Produto</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Unidade</th>
                                <th>Setor</th>
                                <th>Entrada</th>
                                <th>Saída</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if (count($registros) > 0): ?>
                                <?php foreach ($registros as $linha): ?>
                                    <tr>
                                        <td>
                                            <span class="id-registro">
                                                #<?= (int) $linha["id_plan"] ?>
                                            </span>
                                        </td>
                                        <td class="nome-planilha">
                                            <?= escapar($linha["nome_plan"]) ?>
                                        </td>
                                        <td><?= escapar($linha["produto"]) ?></td>
                                        <td>
                                            <?php if (!empty($linha["tipo"])): ?>
                                                <span class="etiqueta">
                                                    <?= escapar($linha["tipo"]) ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="sem-dado">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="quantidade">
                                            <?= escapar($linha["quantidade"]) ?>
                                        </td>
                                        <td><?= escapar($linha["uni_med"]) ?></td>
                                        <td><?= escapar($linha["setor"]) ?></td>
                                        <td><?= escapar($linha["entrada"]) ?></td>
                                        <td><?= escapar($linha["saida"]) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="vazio">
                                        <div class="vazio-icone">▦</div>
                                        <strong>Nenhum registro encontrado</strong>
                                        <p>
                                            <?php if ($pesquisa !== ""): ?>
                                                Tente pesquisar usando outro termo.
                                            <?php else: ?>
                                                Os registros cadastrados aparecerão aqui.
                                            <?php endif; ?>
                                        </p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="rodape-tabela">
                    <span>
                        Exibindo <?= count($registros) ?>
                        <?= count($registros) === 1 ? "registro" : "registros" ?>
                    </span>
                    <span>SIGEP-EPI · Gestão de dados</span>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
