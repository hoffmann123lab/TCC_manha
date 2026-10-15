
<?php

require "connection.php";

$erro = "";
$sucesso = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $descricao = trim($_POST["descricao"] ?? "");
    $tipo = trim($_POST["tipo"] ?? "");
    $unidade = trim($_POST["unidade"] ?? "");
    $colaborador = trim($_POST["colaborador"] ?? "");
    $setor = trim($_POST["setor"] ?? "");
    $produto = trim($_POST["produto"] ?? "");
    $quantidade = $_POST["quantidade"] ?? "";

    if (
        $descricao === "" ||
        $tipo === "" ||
        $unidade === "" ||
        $colaborador === "" ||
        $setor === "" ||
        $produto === "" ||
        $quantidade === ""
    ) {
        $erro = "Preencha todos os campos.";
    } elseif (!filter_var($quantidade, FILTER_VALIDATE_INT) || (int) $quantidade < 0) {
        $erro = "Informe uma quantidade válida.";
    } else {
        $nomePlanilha = "Registro de produção";
        $idFuncionario = 1;
        $quantidade = (int) $quantidade;

        $stmt = $conn->prepare(
            "INSERT INTO Planilhas
            (idFuncionario, descricao, tipo, uni_med, colaborador, setor, nome_plan, produto, quantidade, entrada)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())"
        );

        if (!$stmt) {
            $erro = "Não foi possível preparar o cadastro.";
        } else {
            $stmt->bind_param(
                "isssssssi",
                $idFuncionario,
                $descricao,
                $tipo,
                $unidade,
                $colaborador,
                $setor,
                $nomePlanilha,
                $produto,
                $quantidade
            );

            if ($stmt->execute()) {
                $sucesso = "Registro de produção cadastrado com sucesso.";
            } else {
                $erro = "Não foi possível cadastrar o registro.";
            }

            $stmt->close();
        }
    }
}

$resultado = $conn->query(
    "SELECT id_plan, descricao, tipo, uni_med, colaborador, setor, produto, quantidade, entrada
    FROM Planilhas
    ORDER BY id_plan DESC"
);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produção - SIGEP-EPI</title>
    <link rel="stylesheet" href="producao.css">
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
                <a href="dashboard.php">
                    🏠
                    <span>Início</span>
                </a>

                <a href="producao.php" class="ativo">
                    📋
                    <span>Produção</span>
                </a>

                <a href="analises.php">
                    📊
                    <span>Análises</span>
                </a>

                <a href="planilhas.php">
                    📁
                    <span>Planilhas</span>
                </a>

                <a href="relatorios.php">
                    📈
                    <span>Relatórios</span>
                </a>

                <a href="estoque.php">
                    📦
                    <span>Estoque</span>
                </a>

                <a href="usuarios.php">
                    👥
                    <span>Funcionários</span>
                </a>

                <a href="perfil.php">
                    👤
                    <span>Meu perfil</span>
                </a>
            </nav>

            <a href="index.php" class="sair">
                🚪
                <span>Sair</span>
            </a>
        </aside>

        <main class="conteudo">
            <header class="topo">
                <div>
                    <span class="subtitulo">GERENCIAMENTO</span>
                    <h2>Produção</h2>
                    <p>Cadastre e consulte os registros de produção.</p>
                </div>

                <a href="dashboard.php" class="botao-voltar">Voltar ao início</a>
            </header>

            <?php if ($erro !== ""): ?>
                <div class="mensagem erro" role="alert">
                    <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <?php if ($sucesso !== ""): ?>
                <div class="mensagem sucesso" role="alert">
                    <?= htmlspecialchars($sucesso, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <section class="painel">
                <h3>Novo registro</h3>

                <form method="POST" action="producao.php">
                    <div class="form-grid">
                        <div class="campo">
                            <label for="descricao">Descrição</label>
                            <input type="text" id="descricao" name="descricao" required>
                        </div>

                        <div class="campo">
                            <label for="tipo">Tipo</label>
                            <input type="text" id="tipo" name="tipo" required>
                        </div>

                        <div class="campo">
                            <label for="unidade">Unidade de medida</label>
                            <input type="text" id="unidade" name="unidade" placeholder="Ex.: unidade" required>
                        </div>

                        <div class="campo">
                            <label for="colaborador">Colaborador</label>
                            <input type="text" id="colaborador" name="colaborador" required>
                        </div>

                        <div class="campo">
                            <label for="setor">Setor</label>
                            <input type="text" id="setor" name="setor" required>
                        </div>

                        <div class="campo">
                            <label for="produto">Produto ou EPI</label>
                            <input type="text" id="produto" name="produto" required>
                        </div>

                        <div class="campo">
                            <label for="quantidade">Quantidade</label>
                            <input type="number" id="quantidade" name="quantidade" min="0" required>
                        </div>
                    </div>

                    <button type="submit" class="botao-salvar">Salvar registro</button>
                </form>
            </section>

            <section class="painel registros">
                <h3>Registros cadastrados</h3>

                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Produto</th>
                                <th>Descrição</th>
                                <th>Tipo</th>
                                <th>Quantidade</th>
                                <th>Setor</th>
                                <th>Entrada</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($resultado && $resultado->num_rows > 0): ?>
                                <?php while ($linha = $resultado->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= (int) $linha["id_plan"] ?></td>
                                        <td><?= htmlspecialchars($linha["produto"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["descricao"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["tipo"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= (int) $linha["quantidade"] ?></td>
                                        <td><?= htmlspecialchars($linha["setor"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                        <td><?= htmlspecialchars($linha["entrada"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="vazio">Nenhum registro cadastrado.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
