
<?php

require "connection.php";

$mensagem = "";

$email = "";
$nome = "";
$idade = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensagem = "Informe um e-mail válido.";
    } else {
        $stmt = $conn->prepare(
            "SELECT nome_fun, email, idade FROM Funcionario WHERE email = ?"
        );

        $stmt->bind_param("s", $email);
        $stmt->execute();

        $resultado = $stmt->get_result();

        if ($resultado->num_rows > 0) {
            $funcionario = $resultado->fetch_assoc();
            $nome = $funcionario["nome_fun"];
            $email = $funcionario["email"];
            $idade = $funcionario["idade"];
            $mensagem = "Dados do funcionário encontrados.";
        } else {
            $mensagem = "Nenhum funcionário encontrado com esse e-mail.";
        }

        $stmt->close();
    }
}

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
            <a href="analises.php">▥ <span>Análises</span></a>
            <a href="planilhas.php">▦ <span>Planilhas</span></a>
            <a href="relatorios.php">▧ <span>Relatórios</span></a>
            <a href="perfil.php" class="ativo">♙ <span>Meu perfil</span></a>
        </nav>

        <a href="index.php" class="sair">↪ <span>Sair</span></a>
    </aside>

    <main class="conteudo">
        <header class="topo">
            <span class="subtitulo">DADOS CADASTRAIS</span>
            <h2>Meu perfil</h2>
            <p>Consulte os dados cadastrados no SIGEP-EPI.</p>
        </header>

        <section class="painel">
            <div class="avatar">♙</div>

            <h3>Consultar funcionário</h3>
            <p class="descricao">
                Digite o e-mail utilizado no cadastro para consultar os dados.
            </p>

            <?php if ($mensagem !== ""): ?>
                <div class="mensagem">
                    <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="perfil.php">
                <div class="campo">
                    <label for="email">E-mail cadastrado</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
                        placeholder="Digite seu e-mail"
                        required
                    >
                </div>

                <button type="submit">Consultar dados</button>
            </form>

            <?php if ($nome !== ""): ?>
                <div class="dados">
                    <div class="item">
                        <span>Nome completo</span>
                        <strong><?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?></strong>
                    </div>

                    <div class="item">
                        <span>E-mail</span>
                        <strong><?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?></strong>
                    </div>

                    <div class="item">
                        <span>Idade</span>
                        <strong><?= (int) $idade ?> anos</strong>
                    </div>

                    <div class="item">
                        <span>Tipo de acesso</span>
                        <strong>Funcionário / Operador</strong>
                    </div>
                </div>
            <?php endif; ?>
        </section>
    </main>

</body>
</html>
