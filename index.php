
<?php
    session_start();

    $erro = "";
    $usuario = "";
    $perfil = "funcionario";

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $usuario = trim($_POST["usuario"] ?? "");
        $senha = $_POST["senha"] ?? "";
        $perfil = $_POST["perfil"] ?? "funcionario";

        if ($usuario === "" || $senha === "") {
            $erro = "Preencha todos os campos.";
        } elseif (!in_array($perfil, ["funcionario", "supervisor"], true)) {
            $erro = "Selecione um perfil válido.";
        } else {
            // Aqui será feita a autenticação
            // do usuário com o banco de dados.
            $erro = "Autenticação ainda não configurada.";
        }
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

        <!-- PAINEL ESQUERDO -->
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

        <!-- PAINEL DIREITO -->
        <section class="painel-direito">

            <div class="formulario">

                <span class="subtitulo">ACESSO AO SISTEMA</span>

                <h2>Bem-vindo!</h2>

                <p class="descricao">
                    Acesse sua conta para continuar.
                </p>

                <?php if ($erro !== ""): ?>
                    <div class="mensagem-erro" role="alert">
                        <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">

                    <!-- USUÁRIO -->
                    <div class="campo">

                        <label for="usuario">Usuário</label>

                        <div class="input-container">

                            <span class="campo-icone">♙</span>

                            <input
                                type="text"
                                id="usuario"
                                name="usuario"
                                placeholder="Digite seu usuário"
                                value="<?= htmlspecialchars($usuario, ENT_QUOTES, "UTF-8") ?>"
                                autocomplete="username"
                                required
                            >

                        </div>
                    </div>

                    <!-- SENHA -->
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

                    <!-- BOTÃO ENTRAR -->
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

                <!-- RODAPÉ -->
                <p class="rodape">
                    ♧ Ambiente de acesso restrito
                </p>

            </div>

        </section>

    </main>

</body>
</html>