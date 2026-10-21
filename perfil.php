
<?php

session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

require "connection.php";

$tipoAcesso = $_SESSION["tipo"] ?? "Operador";
$idFuncionario = (int) $_SESSION["id_fun"];

$mensagem = "";
$nome = "";
$email = "";
$idade = "";

$stmt = $conn->prepare(
    "SELECT nome_fun, email, idade FROM funcionario WHERE id_fun = ?"
);

$stmt->bind_param("i", $idFuncionario);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $funcionario = $resultado->fetch_assoc();
    $nome = $funcionario["nome_fun"];
    $email = $funcionario["email"];
    $idade = $funcionario["idade"];
} else {
    $mensagem = "Não foi possível encontrar os dados do seu perfil.";
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - SIGEP-EPI</title>
    <link rel="stylesheet" href="perfil.css">
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

                <a href="producao.php">
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

                <?php if ($tipoAcesso === "Supervisor"): ?>
                    <a href="usuarios.php">
                        👥
                        <span>Funcionários</span>
                    </a>
                <?php endif; ?>

                <a href="perfil.php" class="ativo">
                    👤
                    <span>Meu perfil</span>
                </a>
            </nav>

            <a href="logout.php" class="sair">
                🚪
                <span>Sair</span>
            </a>
        </aside>

        <main class="conteudo">
            <header class="topo">
                <span class="subtitulo">DADOS CADASTRAIS</span>
                <h2>Meu perfil</h2>
                <p>Consulte seus dados cadastrados no SIGEP-EPI.</p>
            </header>

            <section class="painel">
                <div class="avatar">👤</div>

                <h3>Dados do funcionário</h3>
                <p class="descricao">
                    Confira as informações vinculadas à sua conta.
                </p>

                <?php if ($mensagem !== ""): ?>
                    <div class="mensagem">
                        <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php endif; ?>

                <?php if ($nome !== ""): ?>
                    <div class="dados">
                        <div class="item">
                            <span>Nome completo</span>
                            <strong>
                                <?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>
                            </strong>
                        </div>

                        <div class="item">
                            <span>E-mail</span>
                            <strong>
                                <?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>
                            </strong>
                        </div>

                        <div class="item">
                            <span>Idade</span>
                            <strong><?= (int) $idade ?> anos</strong>
                        </div>

                        <div class="item">
                            <span>Tipo de acesso</span>
                            <strong>
                                <?= htmlspecialchars($tipoAcesso, ENT_QUOTES, "UTF-8") ?>
                            </strong>
                        </div>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
</body>
</html>
