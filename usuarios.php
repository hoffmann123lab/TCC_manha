
<?php

session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

if (($_SESSION["tipo"] ?? "") !== "Supervisor") {
    header("Location: dashboard.php");
    exit;
}

require "connection.php";

$idAdministrador = (int) $_SESSION["id_fun"];
$emailAdminBase = "admin@sigep.com";

if (empty($_SESSION["token_usuarios"])) {
    $_SESSION["token_usuarios"] = bin2hex(random_bytes(32));
}

function redirecionarUsuarios(string $mensagem, string $tipo): void
{
    $_SESSION["mensagem_usuarios"] = [
        "texto" => $mensagem,
        "tipo" => $tipo
    ];

    header("Location: usuarios.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["token"] ?? "";
    $acao = $_POST["acao"] ?? "";
    $idAlvo = filter_var($_POST["id_fun"] ?? null, FILTER_VALIDATE_INT);

    if (
        !is_string($token) ||
        !hash_equals($_SESSION["token_usuarios"], $token)
    ) {
        redirecionarUsuarios(
            "Solicitação inválida. Atualize a página e tente novamente.",
            "erro"
        );
    }

    if (!$idAlvo || $idAlvo < 1) {
        redirecionarUsuarios("Funcionário inválido.", "erro");
    }

    if (!in_array($acao, ["promover", "rebaixar", "excluir"], true)) {
        redirecionarUsuarios("Ação inválida.", "erro");
    }

    $stmt = $conn->prepare(
        "SELECT id_fun, nome_fun, email, tipo
         FROM funcionario
         WHERE id_fun = ?"
    );

    $stmt->bind_param("i", $idAlvo);
    $stmt->execute();
    $resultadoAlvo = $stmt->get_result();
    $alvo = $resultadoAlvo->fetch_assoc();
    $stmt->close();

    if (!$alvo) {
        redirecionarUsuarios("Funcionário não encontrado.", "erro");
    }

    $emailAlvo = $alvo["email"];
    $tipoAlvo = $alvo["tipo"];

    if (
        strcasecmp($emailAlvo, $emailAdminBase) === 0 &&
        in_array($acao, ["rebaixar", "excluir"], true)
    ) {
        redirecionarUsuarios(
            "O administrador-base não pode ser rebaixado ou excluído.",
            "erro"
        );
    }

    if (
        $idAlvo === $idAdministrador &&
        in_array($acao, ["rebaixar", "excluir"], true)
    ) {
        redirecionarUsuarios(
            "Você não pode rebaixar ou excluir sua própria conta.",
            "erro"
        );
    }

    if ($acao === "promover") {
        if ($tipoAlvo === "Supervisor") {
            redirecionarUsuarios(
                "Este funcionário já é Supervisor.",
                "erro"
            );
        }

        $stmt = $conn->prepare(
            "SELECT id_cargo FROM cargo WHERE nome_cargo = ? LIMIT 1"
        );

        $cargoSupervisor = "Supervisor";
        $stmt->bind_param("s", $cargoSupervisor);
        $stmt->execute();
        $resultadoCargo = $stmt->get_result();
        $cargo = $resultadoCargo->fetch_assoc();
        $stmt->close();

        if (!$cargo) {
            redirecionarUsuarios(
                "Cargo Supervisor não encontrado no banco de dados.",
                "erro"
            );
        }

        $idCargo = (int) $cargo["id_cargo"];

        $stmt = $conn->prepare(
            "UPDATE funcionario
             SET tipo = 'Supervisor', idCargo = ?
             WHERE id_fun = ?"
        );

        $stmt->bind_param("ii", $idCargo, $idAlvo);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();

            redirecionarUsuarios(
                "Funcionário promovido a Supervisor com sucesso!",
                "sucesso"
            );
        }

        $stmt->close();

        redirecionarUsuarios(
            "Não foi possível promover o funcionário.",
            "erro"
        );
    }

    if ($acao === "rebaixar") {
        if ($tipoAlvo !== "Supervisor") {
            redirecionarUsuarios(
                "Este funcionário já é Operador.",
                "erro"
            );
        }

        $resultadoSupervisores = $conn->query(
            "SELECT COUNT(*) AS total
             FROM funcionario
             WHERE tipo = 'Supervisor'"
        );

        $totalSupervisores = (int) $resultadoSupervisores
            ->fetch_assoc()["total"];

        if ($totalSupervisores <= 1) {
            redirecionarUsuarios(
                "Não é possível rebaixar o último Supervisor.",
                "erro"
            );
        }

        $stmt = $conn->prepare(
            "SELECT id_cargo FROM cargo WHERE nome_cargo = ? LIMIT 1"
        );

        $cargoOperador = "Operador";
        $stmt->bind_param("s", $cargoOperador);
        $stmt->execute();
        $resultadoCargo = $stmt->get_result();
        $cargo = $resultadoCargo->fetch_assoc();
        $stmt->close();

        if (!$cargo) {
            redirecionarUsuarios(
                "Cargo Operador não encontrado no banco de dados.",
                "erro"
            );
        }

        $idCargo = (int) $cargo["id_cargo"];

        $stmt = $conn->prepare(
            "UPDATE funcionario
             SET tipo = 'Operador', idCargo = ?
             WHERE id_fun = ?"
        );

        $stmt->bind_param("ii", $idCargo, $idAlvo);

        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $stmt->close();

            redirecionarUsuarios(
                "Funcionário rebaixado para Operador com sucesso.",
                "sucesso"
            );
        }

        $stmt->close();

        redirecionarUsuarios(
            "Não foi possível alterar o nível de acesso.",
            "erro"
        );
    }

    if ($acao === "excluir") {
        if (
            $idAlvo === $idAdministrador ||
            strcasecmp($emailAlvo, $emailAdminBase) === 0
        ) {
            redirecionarUsuarios(
                "Esta conta não pode ser excluída.",
                "erro"
            );
        }

        if ($tipoAlvo === "Supervisor") {
            $resultadoSupervisores = $conn->query(
                "SELECT COUNT(*) AS total
                 FROM funcionario
                 WHERE tipo = 'Supervisor'"
            );

            $totalSupervisores = (int) $resultadoSupervisores
                ->fetch_assoc()["total"];

            if ($totalSupervisores <= 1) {
                redirecionarUsuarios(
                    "Não é possível excluir o último Supervisor.",
                    "erro"
                );
            }
        }

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS total
             FROM planilhas
             WHERE idFuncionario = ?"
        );

        $stmt->bind_param("i", $idAlvo);
        $stmt->execute();
        $resultadoPlanilhas = $stmt->get_result();
        $totalPlanilhas = (int) $resultadoPlanilhas->fetch_assoc()["total"];
        $stmt->close();

        if ($totalPlanilhas > 0) {
            redirecionarUsuarios(
                "Este funcionário possui registros de produção vinculados. A conta foi preservada para manter o histórico.",
                "erro"
            );
        }

        $stmt = $conn->prepare(
            "DELETE FROM funcionario WHERE id_fun = ?"
        );

        $stmt->bind_param("i", $idAlvo);

        if ($stmt->execute() && $stmt->affected_rows === 1) {
            $stmt->close();

            redirecionarUsuarios(
                "Funcionário excluído com sucesso.",
                "sucesso"
            );
        }

        $stmt->close();

        redirecionarUsuarios(
            "Não foi possível excluir o funcionário.",
            "erro"
        );
    }
}

$mensagem = "";
$tipoMensagem = "";

if (isset($_SESSION["mensagem_usuarios"])) {
    $flash = $_SESSION["mensagem_usuarios"];
    unset($_SESSION["mensagem_usuarios"]);

    if (is_array($flash)) {
        $mensagem = $flash["texto"] ?? "";
        $tipoMensagem = $flash["tipo"] ?? "";
    }
}

$busca = trim($_GET["busca"] ?? "");

if ($busca !== "") {
    $termo = "%" . $busca . "%";

    $stmt = $conn->prepare(
        "SELECT id_fun, nome_fun, email, idade, tipo
         FROM funcionario
         WHERE nome_fun LIKE ? OR email LIKE ?
         ORDER BY nome_fun ASC"
    );

    $stmt->bind_param("ss", $termo, $termo);
    $stmt->execute();
    $funcionarios = $stmt->get_result();
} else {
    $stmt = $conn->prepare(
        "SELECT id_fun, nome_fun, email, idade, tipo
         FROM funcionario
         ORDER BY nome_fun ASC"
    );

    $stmt->execute();
    $funcionarios = $stmt->get_result();
}

$totalFuncionarios = $funcionarios->num_rows;

$totalSupervisoresResultado = $conn->query(
    "SELECT COUNT(*) AS total
     FROM funcionario
     WHERE tipo = 'Supervisor'"
);

$totalSupervisores = (int) $totalSupervisoresResultado->fetch_assoc()["total"];
$totalOperadores = max(0, $totalFuncionarios - $totalSupervisores);

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Funcionários - SIGEP-EPI</title>
    <link rel="stylesheet" href="usuarios.css">
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

                <a href="relatorios.php">
                    <span class="icone-menu">▧</span>
                    <span>Relatórios</span>
                </a>

                <a href="estoque.php">
                    <span class="icone-menu">▣</span>
                    <span>Estoque</span>
                </a>

                <a href="usuarios.php" class="ativo">
                    <span class="icone-menu">♙</span>
                    <span>Funcionários</span>
                </a>

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
                    <span class="subtitulo">ADMINISTRAÇÃO</span>
                    <h2>Funcionários</h2>
                    <p>Gerencie as contas e os níveis de acesso do sistema.</p>
                </div>
            </header>

            <?php if ($mensagem !== ""): ?>
                <div class="mensagem <?= htmlspecialchars($tipoMensagem, ENT_QUOTES, "UTF-8") ?>" role="status">
                    <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <section class="resumo">
                <div>
                    <h3>Equipe SIGEP-EPI</h3>
                    <p>Consulte os funcionários cadastrados e gerencie suas permissões.</p>
                </div>
            </section>

            <section class="card">
                <h3>Resumo da equipe</h3>
                <p>Quantidade de funcionários encontrados na listagem atual.</p>

                <div class="resumo-indicadores">
                    <div class="indicador">
                        <span>Total de funcionários</span>
                        <strong><?= $totalFuncionarios ?></strong>
                    </div>

                    <div class="indicador">
                        <span>Supervisores</span>
                        <strong><?= $totalSupervisores ?></strong>
                    </div>

                    <div class="indicador">
                        <span>Operadores</span>
                        <strong><?= $totalOperadores ?></strong>
                    </div>
                </div>
            </section>

            <section class="painel">
                <h3>Lista de funcionários</h3>
                <p>Pesquise por nome ou e-mail para localizar uma conta.</p>

                <form method="GET" action="usuarios.php" class="busca-form">
                    <input
                        type="search"
                        name="busca"
                        placeholder="Buscar por nome ou e-mail..."
                        value="<?= htmlspecialchars($busca, ENT_QUOTES, "UTF-8") ?>"
                    >

                    <button type="submit">Pesquisar</button>

                    <?php if ($busca !== ""): ?>
                        <a href="usuarios.php" class="botao">Limpar</a>
                    <?php endif; ?>
                </form>

                <div class="tabela-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Funcionário</th>
                                <th>E-mail</th>
                                <th>Idade</th>
                                <th>Nível de acesso</th>
                                <th>Ações</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php if ($totalFuncionarios > 0): ?>
                                <?php while ($funcionario = $funcionarios->fetch_assoc()): ?>
                                    <?php
                                    $idAlvoTabela = (int) $funcionario["id_fun"];
                                    $emailTabela = $funcionario["email"];
                                    $tipoTabela = $funcionario["tipo"];
                                    $ehAdminBase = strcasecmp(
                                        $emailTabela,
                                        $emailAdminBase
                                    ) === 0;
                                    $ehPropriaConta = $idAlvoTabela === $idAdministrador;
                                    ?>

                                    <tr>
                                        <td>
                                            <strong>
                                                <?= htmlspecialchars($funcionario["nome_fun"], ENT_QUOTES, "UTF-8") ?>
                                            </strong>

                                            <?php if ($ehAdminBase): ?>
                                                <br>
                                                <span class="selo-admin">Administrador-base</span>
                                            <?php elseif ($ehPropriaConta): ?>
                                                <br>
                                                <span class="propria-conta">Sua conta</span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($emailTabela, ENT_QUOTES, "UTF-8") ?>
                                        </td>

                                        <td>
                                            <?= (int) $funcionario["idade"] ?> anos
                                        </td>

                                        <td>
                                            <span class="perfil <?= $tipoTabela === "Supervisor" ? "supervisor" : "operador" ?>">
                                                <?= htmlspecialchars($tipoTabela, ENT_QUOTES, "UTF-8") ?>
                                            </span>
                                        </td>

                                        <td>
                                            <div class="acoes">
                                                <?php if ($tipoTabela === "Operador"): ?>
                                                    <form method="POST" action="usuarios.php">
                                                        <input
                                                            type="hidden"
                                                            name="token"
                                                            value="<?= htmlspecialchars($_SESSION["token_usuarios"], ENT_QUOTES, "UTF-8") ?>"
                                                        >

                                                        <input type="hidden" name="id_fun" value="<?= $idAlvoTabela ?>">
                                                        <input type="hidden" name="acao" value="promover">

                                                        <button type="submit" class="botao-promover">
                                                            Promover
                                                        </button>
                                                    </form>
                                                <?php elseif (!$ehAdminBase && !$ehPropriaConta && $totalSupervisores > 1): ?>
                                                    <form method="POST" action="usuarios.php">
                                                        <input
                                                            type="hidden"
                                                            name="token"
                                                            value="<?= htmlspecialchars($_SESSION["token_usuarios"], ENT_QUOTES, "UTF-8") ?>"
                                                        >

                                                        <input type="hidden" name="id_fun" value="<?= $idAlvoTabela ?>">
                                                        <input type="hidden" name="acao" value="rebaixar">

                                                        <button type="submit" class="botao-rebaixar">
                                                            Rebaixar
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if (!$ehAdminBase && !$ehPropriaConta): ?>
                                                    <form
                                                        method="POST"
                                                        action="usuarios.php"
                                                        onsubmit="return confirm('Tem certeza de que deseja excluir este funcionário?');"
                                                    >
                                                        <input
                                                            type="hidden"
                                                            name="token"
                                                            value="<?= htmlspecialchars($_SESSION["token_usuarios"], ENT_QUOTES, "UTF-8") ?>"
                                                        >

                                                        <input type="hidden" name="id_fun" value="<?= $idAlvoTabela ?>">
                                                        <input type="hidden" name="acao" value="excluir">

                                                        <button type="submit" class="botao-excluir">
                                                            Excluir
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <?php if ($ehAdminBase || $ehPropriaConta): ?>
                                                    <span class="propria-conta">Conta protegida</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5">
                                        <div class="vazio">
                                            Nenhum funcionário encontrado.
                                            <br>
                                            Tente pesquisar por outro nome ou e-mail.
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <footer class="rodape">
                <span>SIGEP-EPI</span>
                <span>Sistema de Gestão de EPI's</span>
            </footer>
        </main>
    </div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
