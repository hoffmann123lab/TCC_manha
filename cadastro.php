
<?php

require "connection.php";

$erro = "";

$nome = "";
$email = "";
$dataNascimento = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $dataNascimento = $_POST["data_nascimento"] ?? "";
    $senha = $_POST["senha"] ?? "";
    $confirmarSenha = $_POST["confirmar_senha"] ?? "";

    $dataValida = false;

    if ($dataNascimento !== "") {
        $data = DateTime::createFromFormat("!Y-m-d", $dataNascimento);

        $dataValida = $data &&
            $data->format("Y-m-d") === $dataNascimento &&
            $data <= new DateTime("today");
    }

    if ($nome === "") {
        $erro = "Preencha o nome.";

    } elseif ($email === "") {
        $erro = "Preencha o e-mail.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Digite um e-mail válido.";

    } elseif (!$dataValida) {
        $erro = "Digite uma data de nascimento válida.";

    } elseif ($senha === "") {
        $erro = "Preencha a senha.";

    } elseif ($confirmarSenha === "") {
        $erro = "Confirme a senha.";

    } elseif ($senha !== $confirmarSenha) {
        $erro = "As senhas não coincidem.";

    } elseif (strlen($senha) < 8) {
        $erro = "A senha deve ter pelo menos 8 caracteres.";

    } else {

        $verificar = $conn->prepare(
            "SELECT id_fun FROM funcionario WHERE email = ?"
        );

        if (!$verificar) {
            $erro = "Erro ao verificar o cadastro.";

        } else {

            $verificar->bind_param("s", $email);
            $verificar->execute();

            $resultado = $verificar->get_result();

            if ($resultado->num_rows > 0) {
                $erro = "Este e-mail já está cadastrado.";

            } else {

                $cargo = $conn->prepare(
                    "SELECT id_cargo FROM cargo WHERE nome_cargo = ? LIMIT 1"
                );

                if (!$cargo) {
                    $erro = "Erro ao verificar o cargo.";

                } else {

                    $nomeCargo = "Operador";

                    $cargo->bind_param("s", $nomeCargo);
                    $cargo->execute();

                    $resultadoCargo = $cargo->get_result();

                    if ($resultadoCargo->num_rows === 0) {
                        $erro = "O cargo Operador não foi encontrado no banco de dados.";

                    } else {

                        $dadosCargo = $resultadoCargo->fetch_assoc();

                        $idCargo = (int)$dadosCargo["id_cargo"];
                        $tipo = "Operador";

                        $senhaHash = password_hash(
                            $senha,
                            PASSWORD_DEFAULT
                        );

                        $stmt = $conn->prepare(
                            "INSERT INTO funcionario
                            (email, nome_fun, data_nascimento, senha, tipo, idCargo)
                            VALUES (?, ?, ?, ?, ?, ?)"
                        );

                        if (!$stmt) {
                            $erro = "Erro ao preparar o cadastro.";

                        } else {

                            $stmt->bind_param(
                                "sssssi",
                                $email,
                                $nome,
                                $dataNascimento,
                                $senhaHash,
                                $tipo,
                                $idCargo
                            );

                            if ($stmt->execute()) {

                                $stmt->close();
                                $cargo->close();
                                $verificar->close();
                                $conn->close();

                                header("Location: logar.php?cadastro=sucesso");
                                exit;

                            } else {
                                $erro = "Não foi possível cadastrar o usuário. Verifique os dados do banco.";
                            }

                            $stmt->close();
                        }
                    }

                    $cargo->close();
                }
            }

            $verificar->close();
        }
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

        <section class="painel-esquerdo">

            <div class="logo">

                <div class="icone-logo">⚙</div>

                <h1>SIGEP-EPI</h1>

                <span class="selo">
                    ● Gestão simples e eficiente
                </span>

            </div>

        </section>

        <section class="painel-direito">

            <div class="formulario">

                <span class="subtitulo">CRIE SUA CONTA</span>

                <h2>Cadastre-se!</h2>

                <p class="descricao">
                    Preencha os dados para criar sua conta.
                </p>

                <?php if ($erro !== ""): ?>

                    <div class="mensagem-erro" role="alert">
                        <?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?>
                    </div>

                <?php endif; ?>

                <form method="POST" action="">

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

                    <div class="campo">

                        <label for="data_nascimento">Data de nascimento</label>

                        <div class="input-container">

                            <input
                                type="date"
                                id="data_nascimento"
                                name="data_nascimento"
                                value="<?= htmlspecialchars($dataNascimento, ENT_QUOTES, "UTF-8") ?>"
                                max="<?= date("Y-m-d") ?>"
                                autocomplete="bday"
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
                                placeholder="Crie uma senha"
                                autocomplete="new-password"
                                minlength="8"
                                required
                            >

                        </div>

                    </div>

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

                    <button type="submit" class="botao-cadastrar">

                        Cadastrar

                        <span>➜</span>

                    </button>

                </form>

                <div class="link-login">

                    <p>
                        Já tem uma conta?
                        <a href="logar.php">Entrar</a>
                    </p>

                </div>

                <p class="rodape">
                    ♧ Ambiente de acesso restrito
                </p>

            </div>

        </section>

    </main>

</body>

</html>
