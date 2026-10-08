<?php

$erro = "";
$mensagem = "";

if (isset($_GET["cadastro"]) && $_GET["cadastro"] === "sucesso") {
    $mensagem = "Cadastro realizado com sucesso!";
}

if (isset($_GET["erro"])) {
    $erro = $_GET["erro"];
}

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login - SIGEP-EPI</title>

    <link rel="stylesheet" href="index.css">

</head>

<body>

    <main class="login-container">

        <section class="painel-esquerdo">

            <div class="logo">

                <div class="icone-logo">⚙</div>

                <h1>SIGEP-EPI</h1>

                <p>
                    Sistema Inteligente de Gestão
                    e Análise de EPI's
                </p>

                <span class="selo">
                    ● Gestão simples e eficiente
                </span>

            </div>

        </section>

        <section class="painel-direito">

            <div class="formulario">

                <span class="subtitulo">ACESSO AO SISTEMA</span>

                <h2>Bem-vindo!</h2>

                <p class="descricao">
                    Acesse sua conta para continuar.
                </p>

                <?php if ($mensagem !== ""): ?>

                    <div class="mensagem-sucesso" role="alert">
                        <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
                    </div>

                <?php endif; ?>

                <?php if ($erro !== ""): ?>

                    <div class="mensagem-erro" role="alert">
                        <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                    </div>

                <?php endif; ?>

                <form method="POST" action="login.php">

                    <div class="campo">

                        <label for="email">E-mail</label>

                        <div class="input-container">

                            <span class="campo-icone">✉</span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Digite seu e-mail"
                                autocomplete="email"
                                required
                            >

                        </div>

                    </div>

                    <div class="campo">

                        <label for="senha">Senha</label>

                        <div class="input-container">

                            <span class="campo-icone">♢</span>

                            <input
                                type="password"
                                id="senha"
                                name="senha"
                                placeholder="Digite sua senha"
                                autocomplete="current-password"
                                required
                            >

                        </div>

                    </div>

                    <button type="submit" class="botao-entrar">

                        Entrar

                        <span>➜</span>

                    </button>

                    <div class="link-cadastro">

                        <p>
                            Não tem uma conta?
                            <a href="cadastro.php">Cadastre-se</a>
                        </p>

                    </div>

                </form>

                <p class="rodape">
                    ♧ Ambiente de acesso restrito
                </p>

            </div>

        </section>

    </main>

</body>

</html>