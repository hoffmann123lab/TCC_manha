<?php
session_start();
require "connection.php";

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

$mensagem = "";
$erros = [];
$dadosVerificados = false;
$token = "";

$colunasEsperadas = [
    "produto",
    "descricao",
    "tipo",
    "cor",
    "tamanho",
    "unidade",
    "quantidade",
    "setor",
    "colaborador"
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST["acao"] ?? "";

    if ($acao === "verificar") {
        unset($_SESSION["importacao_csv"]);

        if (!isset($_FILES["arquivo"]) || $_FILES["arquivo"]["error"] !== UPLOAD_ERR_OK) {
            $erros[] = "Selecione um arquivo CSV válido.";
        } else {
            $arquivo = $_FILES["arquivo"];

            if (strtolower(pathinfo($arquivo["name"], PATHINFO_EXTENSION)) !== "csv") {
                $erros[] = "O arquivo precisa estar no formato CSV.";
            }

            if ($arquivo["size"] > 10 * 1024 * 1024) {
                $erros[] = "O arquivo não pode ultrapassar 10 MB.";
            }

            if (empty($erros)) {
                $handle = fopen($arquivo["tmp_name"], "r");

                if (!$handle) {
                    $erros[] = "Não foi possível ler o arquivo.";
                } else {
                    $primeiraLinha = fgets($handle);
                    rewind($handle);

                    $separador = substr_count($primeiraLinha, ";") >= substr_count($primeiraLinha, ",") ? ";" : ",";

                    $cabecalho = fgetcsv($handle, 0, $separador, '"', "\\");

                    if (!$cabecalho) {
                        $erros[] = "A planilha está vazia.";
                    } else {
                        $cabecalho[0] = preg_replace('/^\xEF\xBB\xBF/', '', $cabecalho[0]);
                        $cabecalho = array_map("trim", $cabecalho);

                        if ($cabecalho !== $colunasEsperadas) {
                            $erros[] = "Os campos da planilha estão incorretos ou fora da ordem esperada.";
                            $erros[] = "Cabeçalhos esperados: " . implode(", ", $colunasEsperadas);
                        }
                    }

                    $linhasValidas = [];
                    $numeroLinha = 1;

                    if (empty($erros)) {
                        while (($linha = fgetcsv($handle, 0, $separador, '"', "\\")) !== false) {
                            $numeroLinha++;

                            if ($linha === [null] || $linha === []) {
                                continue;
                            }

                            if (count(array_filter($linha, function ($valor) {
                                return trim((string)$valor) !== "";
                            })) === 0) {
                                continue;
                            }

                            if (count($linha) !== count($colunasEsperadas)) {
                                $erros[] = "Linha $numeroLinha: quantidade de colunas incorreta.";
                                continue;
                            }

                            $linha = array_map(function ($valor) {
                                return trim((string)$valor);
                            }, $linha);

                            $registro = array_combine($colunasEsperadas, $linha);

                            $camposObrigatorios = [
                                "produto",
                                "descricao",
                                "tipo",
                                "unidade",
                                "quantidade",
                                "setor",
                                "colaborador"
                            ];

                            foreach ($camposObrigatorios as $campo) {
                                if ($registro[$campo] === "") {
                                    $erros[] = "Linha $numeroLinha: o campo '$campo' está vazio.";
                                }
                            }

                            if (
                                $registro["quantidade"] !== "" &&
                                (!ctype_digit($registro["quantidade"]))
                            ) {
                                $erros[] = "Linha $numeroLinha: a quantidade deve ser um número inteiro igual ou maior que zero.";
                            }

                            if (
                                $registro["quantidade"] !== "" &&
                                ctype_digit($registro["quantidade"]) &&
                                (int)$registro["quantidade"] > 2147483647
                            ) {
                                $erros[] = "Linha $numeroLinha: a quantidade ultrapassa o limite permitido.";
                            }

                            $linhasValidas[] = $registro;
                        }

                        if (empty($linhasValidas) && empty($erros)) {
                            $erros[] = "A planilha não possui registros para importar.";
                        }
                    }

                    fclose($handle);

                    if (empty($erros)) {
                        $token = bin2hex(random_bytes(16));

                        $_SESSION["importacao_csv"] = [
                            "token" => $token,
                            "arquivo" => basename($arquivo["name"]),
                            "linhas" => $linhasValidas
                        ];

                        $dadosVerificados = true;
                        $mensagem = "Verificação concluída! Todos os campos estão preenchidos corretamente.";
                    }
                }
            }
        }
    }

    if ($acao === "importar") {
        $tokenRecebido = $_POST["token"] ?? "";
        $importacao = $_SESSION["importacao_csv"] ?? null;

        if (
            !$importacao ||
            !isset($importacao["token"]) ||
            !hash_equals($importacao["token"], $tokenRecebido)
        ) {
            $erros[] = "A verificação expirou. Selecione o arquivo novamente.";
            unset($_SESSION["importacao_csv"]);
        } else {
            $idFuncionario = (int)$_SESSION["id_fun"];
            $nomeArquivo = $importacao["arquivo"];
            $linhas = $importacao["linhas"];

            try {
                $conn->begin_transaction();

                $sql = "INSERT INTO planilhas
                    (idFuncionario, descricao, tipo, uni_med, colaborador, setor, nome_plan, produto, cor, tamanho, quantidade, entrada)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

                $stmt = $conn->prepare($sql);

                if (!$stmt) {
                    throw new Exception("Erro ao preparar a importação.");
                }

                foreach ($linhas as $registro) {
                    $descricao = $registro["descricao"];
                    $tipo = $registro["tipo"];
                    $unidade = $registro["unidade"];
                    $colaborador = $registro["colaborador"];
                    $setor = $registro["setor"];
                    $produto = $registro["produto"];
                    $cor = $registro["cor"];
                    $tamanho = $registro["tamanho"];
                    $quantidade = (int)$registro["quantidade"];

                    $stmt->bind_param(
                        "isssssssssi",
                        $idFuncionario,
                        $descricao,
                        $tipo,
                        $unidade,
                        $colaborador,
                        $setor,
                        $nomeArquivo,
                        $produto,
                        $cor,
                        $tamanho,
                        $quantidade
                    );

                    if (!$stmt->execute()) {
                        throw new Exception("Erro ao importar um dos registros.");
                    }
                }

                $stmt->close();
                $conn->commit();

                unset($_SESSION["importacao_csv"]);

                $mensagem = count($linhas) . " registros foram importados com sucesso!";
            } catch (Exception $e) {
                $conn->rollback();
                $erros[] = "Não foi possível concluir a importação. Verifique os dados e tente novamente.";
            }
        }
    }
}

if (isset($_SESSION["importacao_csv"]) && $_SERVER["REQUEST_METHOD"] !== "POST") {
    unset($_SESSION["importacao_csv"]);
}

$sqlPlanilhas = "SELECT * FROM planilhas ORDER BY id_plan DESC";
$resultadoPlanilhas = $conn->query($sqlPlanilhas);

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
            <div class="logo-texto">
                <h1>SIGEP-EPI</h1>
                <span>Sistema de Gestão</span>
            </div>
        </div>

        <nav>
            <a href="dashboard.php"><span class="icone-menu">⌂</span><span>Início</span></a>
            <a href="producao.php" class="ativo"><span class="icone-menu">▤</span><span>Produção</span></a>
            <a href="analises.php"><span class="icone-menu">▥</span><span>Análises</span></a>
            <a href="planilhas.php"><span class="icone-menu">▦</span><span>Planilhas</span></a>
            <a href="relatorios.php"><span class="icone-menu">▧</span><span>Relatórios</span></a>
            <a href="estoque.php"><span class="icone-menu">▣</span><span>Estoque</span></a>

            <?php if (($_SESSION["tipo"] ?? "") === "Supervisor"): ?>
                <a href="usuarios.php"><span class="icone-menu">♙</span><span>Funcionários</span></a>
            <?php endif; ?>

            <a href="perfil.php"><span class="icone-menu">⚙</span><span>Meu perfil</span></a>
        </nav>

        <a href="index.php" class="sair"><span class="icone-menu">↪</span><span>Sair</span></a>
    </aside>

    <main class="conteudo">
    <header class="cabecalho">
        <div>
            <h1>Importação de produção</h1>
            <p>Verifique os dados da planilha antes de importar para o sistema.</p>
        </div>
    </header>

    <?php if ($mensagem !== ""): ?>
        <div class="mensagem sucesso">
            <?= htmlspecialchars($mensagem) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($erros)): ?>
        <div class="mensagem erro">
            <strong>Não foi possível aprovar a planilha:</strong>
            <ul>
                <?php foreach (array_slice($erros, 0, 30) as $erro): ?>
                    <li><?= htmlspecialchars($erro) ?></li>
                <?php endforeach; ?>
            </ul>

            <?php if (count($erros) > 30): ?>
                <p>Existem mais <?= count($erros) - 30 ?> erros não exibidos.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <section class="painel">
        <h2>Selecionar planilha CSV</h2>

        <p class="descricao">
            Selecione seu arquivo CSV. O sistema verificará os campos e os dados antes de permitir a importação.
        </p>

        <form method="POST" enctype="multipart/form-data" class="formulario">
            <label for="arquivo">Arquivo da planilha</label>
            <input
                type="file"
                name="arquivo"
                id="arquivo"
                accept=".csv"
                required
                <?= $dadosVerificados ? "disabled" : "" ?>
            >

            <p class="ajuda">Formato aceito: CSV. Tamanho máximo: 10 MB.</p>

            <button type="submit" name="acao" value="verificar" <?= $dadosVerificados ? "disabled" : "" ?>>
                Verificar planilha
            </button>
        </form>

        <div class="campos">
            <h3>Campos obrigatórios e opcionais</h3>

            <p>
                <strong>Obrigatórios:</strong>
                produto, descricao, tipo, unidade, quantidade, setor e colaborador.
            </p>

            <p>
                <strong>Opcionais:</strong>
                cor e tamanho.
            </p>

            <p>
                Os cabeçalhos precisam estar escritos exatamente nessa ordem:
            </p>

            <div class="lista-campos">
                produto, descricao, tipo, cor, tamanho, unidade, quantidade, setor, colaborador
            </div>
        </div>
    </section>

    <?php if ($dadosVerificados && isset($_SESSION["importacao_csv"])): ?>
        <section class="painel aprovado">
            <h2>Planilha aprovada</h2>

            <p><?= count($_SESSION["importacao_csv"]["linhas"]) ?> registros foram verificados.</p>

            <p>
                Arquivo:
                <strong><?= htmlspecialchars($_SESSION["importacao_csv"]["arquivo"]) ?></strong>
            </p>

            <p>Todos os campos obrigatórios foram encontrados e os dados passaram pela verificação.</p>

            <form method="POST">
                <input type="hidden" name="acao" value="importar">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                <button type="submit" class="botao-importar">
                    Importar planilha
                </button>
            </form>
        </section>
    <?php endif; ?>

    <section class="painel">
        <h2>Registros cadastrados</h2>

        <div class="tabela-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Produto</th>
                        <th>Descrição</th>
                        <th>Tipo</th>
                        <th>Cor</th>
                        <th>Tamanho</th>
                        <th>Unidade</th>
                        <th>Quantidade</th>
                        <th>Setor</th>
                        <th>Colaborador</th>
                        <th>Data de entrada</th>
                    </tr>
                </thead>

                <tbody>
                    <?php if ($resultadoPlanilhas && $resultadoPlanilhas->num_rows > 0): ?>
                        <?php while ($registro = $resultadoPlanilhas->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($registro["id_plan"]) ?></td>
                                <td><?= htmlspecialchars($registro["produto"]) ?></td>
                                <td><?= htmlspecialchars($registro["descricao"]) ?></td>
                                <td><?= htmlspecialchars($registro["tipo"]) ?></td>
                                <td><?= htmlspecialchars($registro["cor"] ?? "") ?></td>
                                <td><?= htmlspecialchars($registro["tamanho"] ?? "") ?></td>
                                <td><?= htmlspecialchars($registro["uni_med"]) ?></td>
                                <td><?= htmlspecialchars($registro["quantidade"]) ?></td>
                                <td><?= htmlspecialchars($registro["setor"]) ?></td>
                                <td><?= htmlspecialchars($registro["colaborador"]) ?></td>
                                <td><?= htmlspecialchars($registro["entrada"]) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="11" class="vazio">Nenhum registro encontrado.</td>
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