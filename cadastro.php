
<?php
session_start();

$erro = "";
$nome = "";
$usuario = "";
$email = "";
$perfil = "funcionario";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"] ?? "");
    $usuario = trim($_POST["usuario"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $senha = $_POST["senha"] ?? "";
    $confirmarSenha = $_POST["confirmar_senha"] ?? "";
    $perfil = $_POST["perfil"] ?? "funcionario";

    if (
        $nome === "" ||
        $usuario === "" ||
        $email === "" ||
        $senha === "" ||
        $confirmarSenha === ""
    ) {
        $erro = "Preencha todos os campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um e-mail válido.";
    } elseif (!in_array($perfil, ["funcionario", "supervisor"], true)) {
        $erro = "Selecione um perfil válido.";
    } elseif ($senha !== $confirmarSenha) {
        $erro = "As senhas não coincidem.";
    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter pelo menos 8 caracteres.";
    } else {
        /*
         * Aqui será feita a gravação do usuário no banco
         * de dados, utilizando password_hash() para proteger
         * a senha.
         */
        $erro = "Validação concluída! Conecte o cadastro ao banco de dados.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - SIGEP-EPI</title>
    <link rel="stylesheet" href="cadastro.css">
</head>

<body>

    <main class="cadastro-container">

        <!-- PAINEL ESQUERDO -->
        <section class="painel-esquerdo">

            <div class="logo">

                <div class="icone-logo">⚙</div>

                <h1>SIGEP-EPI</h1>

                <span class="selo">
                    ● Gestão simples e eficiente
                </span>

            </div>

        </section>

        <!-- PAINEL DIREITO -->
        <section class="painel-direito">

            <div class="formulario">

                <span class="subtitulo">CRIE SUA CONTA</span>

                <h2>Cadastre-se!</h2>

                <p class="descricao">
                    Preencha os dados para criar sua conta.
                </p>

                <!-- MENSAGEM -->
                <?php if ($erro !== ""): ?>
                    <div class="mensagem-erro" role="alert">
                        <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                    </div>
                <?php endif; ?>

                <!-- FORMULÁRIO -->
                <form method="POST" action="">

                    <!-- NOME COMPLETO -->
                    <div class="campo">

                        <label for="nome">Nome completo</label>

                        <div class="input-container">

                            <span class="campo-icone">♙</span>

                            <input
                                type="text"
                                id="nome"
                                name="nome"
                                placeholder="Digite seu nome completo"
                                value="<?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>"
                                autocomplete="name"
                                required
                            >

                        </div>

                    </div>

                    <!-- USUÁRIO -->
                    <div class="campo">

                        <label for="usuario">Usuário</label>

                        <div class="input-container">

                            <span class="campo-icone">♙</span>

                            <input
                                type="text"
                                id="usuario"
                                name="usuario"
                                placeholder="Crie seu usuário"
                                value="<?= htmlspecialchars($usuario, ENT_QUOTES, "UTF-8") ?>"
                                autocomplete="username"
                                required
                            >

                        </div>

                    </div>

                    <!-- E-MAIL -->
                    <div class="campo">

                        <label for="email">E-mail</label>

                        <div class="input-container">

                            <span class="campo-icone">✉</span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Digite seu e-mail"
                                value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
                                autocomplete="email"
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
                                placeholder="Crie uma senha"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                        </div>

                    </div>

                    <!-- CONFIRMAR SENHA -->
                    <div class="campo">

                        <label for="confirmar_senha">
                            Confirmar senha
                        </label>

                        <div class="input-container">

                            <span class="campo-icone">♢</span>

                            <input
                                type="password"
                                id="confirmar_senha"
                                name="confirmar_senha"
                                placeholder="Digite a senha novamente"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                        </div>

                    </div>

                    <!-- BOTÃO CADASTRAR -->
                    <button type="submit" class="botao-cadastrar">
                        Cadastrar
                        <span>➜</span>
                    </button>

                </form>

                <!-- LINK PARA LOGIN -->
                <div class="link-login">

                    <p>
                        Já tem uma conta?
                        <a href="index.php">Entrar</a>
                    </p>

                </div>

                <!-- RODAPÉ -->
                <p class="rodape">
                    ♧ Ambiente de acesso restrito
                </p>

            </div>

        </section>

    </main>

</body>
</html>