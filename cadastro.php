<?php

require "connection.php";

$erro = "";
$sucesso = "";

$nome = "";
$email = "";
$idade = "";
$perfil = "funcionario";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $idade = $_POST["idade"] ?? "";
    $senha = $_POST["senha"] ?? "";
    $confirmarSenha = $_POST["confirmar_senha"] ?? "";
    $perfil = $_POST["perfil"] ?? "funcionario";

    if (
        $nome === "" ||
        $email === "" ||
        $idade === "" ||
        $senha === "" ||
        $confirmarSenha === ""
    ) {

        $erro = "Preencha todos os campos.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $erro = "Digite um e-mail válido.";

    } elseif ($senha !== $confirmarSenha) {

        $erro = "As senhas não coincidem.";

    } elseif (strlen($senha) < 8) {

        $erro = "A senha deve ter pelo menos 8 caracteres.";

    } elseif (!in_array($perfil, ["funcionario", "supervisor"], true)) {

        $erro = "Selecione um perfil válido.";

    } else {

        $verificar = $conn->prepare(
            "SELECT id_fun FROM Funcionario WHERE email = ?"
        );

        $verificar->bind_param("s", $email);
        $verificar->execute();

        $resultado = $verificar->get_result();

        if ($resultado->num_rows > 0) {

            $erro = "Este e-mail já está cadastrado.";

        } else {

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            if ($perfil === "funcionario") {
                $tipo = "Operador";
                $idCargo = 1;
            } else {
                $tipo = "Supervisor";
                $idCargo = 2;
            }

            $stmt = $conn->prepare(
                "INSERT INTO Funcionario 
                (email, nome_fun, idade, senha, tipo, idCargo)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "ssissi",
                $email,
                $nome,
                $idade,
                $senhaHash,
                $tipo,
                $idCargo
            );

            if ($stmt->execute()) {

                $sucesso = "Usuário cadastrado com sucesso.";

                $nome = "";
                $email = "";
                $idade = "";

            } else {

                $erro = "Erro ao cadastrar usuário.";

            }

            $stmt->close();
        }

        $verificar->close();
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