
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
$tipoMensagem = "";
$nome = "";
$email = "";
$idade = "";

if (empty($_SESSION["token_perfil"])) {
    $_SESSION["token_perfil"] = bin2hex(random_bytes(32));
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["token"] ?? "";

    if (!hash_equals($_SESSION["token_perfil"], $token)) {
        $mensagem = "Solicitação inválida. Atualize a página e tente novamente.";
        $tipoMensagem = "erro";
    } else {
        $senhaAtual = $_POST["senha_atual"] ?? "";
        $novaSenha = $_POST["nova_senha"] ?? "";
        $confirmarSenha = $_POST["confirmar_senha"] ?? "";

        $stmt = $conn->prepare(
            "SELECT senha FROM funcionario WHERE id_fun = ?"
        );

        $stmt->bind_param("i", $idFuncionario);
        $stmt->execute();
        $resultadoSenha = $stmt->get_result();
        $dadosSenha = $resultadoSenha->fetch_assoc();
        $stmt->close();

        if (!$dadosSenha || !password_verify($senhaAtual, $dadosSenha["senha"])) {
            $mensagem = "A senha atual está incorreta.";
            $tipoMensagem = "erro";
        } elseif (strlen($novaSenha) < 8) {
            $mensagem = "A nova senha deve ter pelo menos 8 caracteres.";
            $tipoMensagem = "erro";
        } elseif ($novaSenha !== $confirmarSenha) {
            $mensagem = "A confirmação não corresponde à nova senha.";
            $tipoMensagem = "erro";
        } elseif ($senhaAtual === $novaSenha) {
            $mensagem = "A nova senha deve ser diferente da senha atual.";
            $tipoMensagem = "erro";
        } else {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare(
                "UPDATE funcionario SET senha = ? WHERE id_fun = ?"
            );

            $stmt->bind_param("si", $senhaHash, $idFuncionario);

            if ($stmt->execute() && $stmt->affected_rows === 1) {
                $mensagem = "Senha alterada com sucesso!";
                $tipoMensagem = "sucesso";
                $_SESSION["token_perfil"] = bin2hex(random_bytes(32));
            } else {
                $mensagem = "Não foi possível alterar a senha.";
                $tipoMensagem = "erro";
            }

            $stmt->close();
        }
    }
}

$stmt = $conn->prepare(
    "SELECT nome_fun, email, idade FROM funcionario WHERE id_fun = ?"
);

$stmt->bind_param("i", $idFuncionario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $funcionario = $resultado->fetch_assoc();
    $nome = $funcionario["nome_fun"];
    $email = $funcionario["email"];
    $idade = $funcionario["idade"];
} else {
    session_unset();
    session_destroy();
    header("Location: index.php");
    exit;
}

$stmt->close();
$conn->close();

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - SIGEP-EPI</title>
    <link rel="stylesheet" href="perfil.css">
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

                <?php if ($tipoAcesso === "Supervisor"): ?>
                    <a href="usuarios.php">
                        <span class="icone-menu">♙</span>
                        <span>Funcionários</span>
                    </a>
                <?php endif; ?>

                <a href="perfil.php" class="ativo">
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
                    <span class="subtitulo">CONTA E SEGURANÇA</span>
                    <h2>Meu perfil</h2>
                    <p>Consulte seus dados pessoais e gerencie sua senha.</p>
                </div>

                <div class="status-conta">
                    <span class="status-ponto"></span>
                    Conta ativa
                </div>
            </header>

            <?php if ($mensagem !== ""): ?>
                <div class="mensagem <?= htmlspecialchars($tipoMensagem, ENT_QUOTES, "UTF-8") ?>" role="status">
                    <span class="mensagem-icone">
                        <?= $tipoMensagem === "sucesso" ? "✓" : "!" ?>
                    </span>
                    <span><?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?></span>
                </div>
            <?php endif; ?>

            <section class="perfil-resumo">
                <div class="avatar">
                    <span>♙</span>
                </div>

                <div class="resumo-info">
                    <span class="etiqueta">MEU CADASTRO</span>
                    <h3><?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?></h3>
                    <p><?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?></p>
                </div>

                <div class="perfil-tipo <?= $tipoAcesso === "Supervisor" ? "supervisor" : "operador" ?>">
                    <span class="tipo-ponto"></span>
                    <?= htmlspecialchars($tipoAcesso, ENT_QUOTES, "UTF-8") ?>
                </div>
            </section>

            <div class="grade-perfil">
                <section class="painel painel-dados">
                    <div class="painel-cabecalho">
                        <div class="icone-painel azul">♙</div>
                        <div>
                            <h3>Informações pessoais</h3>
                            <p>Dados vinculados à sua conta.</p>
                        </div>
                    </div>

                    <div class="dados">
                        <div class="item">
                            <div class="item-icone">♙</div>
                            <div class="item-conteudo">
                                <span>Nome completo</span>
                                <strong><?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?></strong>
                            </div>
                        </div>

                        <div class="item">
                            <div class="item-icone">@</div>
                            <div class="item-conteudo">
                                <span>E-mail cadastrado</span>
                                <strong><?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?></strong>
                            </div>
                        </div>

                        <div class="item">
                            <div class="item-icone">#</div>
                            <div class="item-conteudo">
                                <span>Idade</span>
                                <strong><?= (int) $idade ?> anos</strong>
                            </div>
                        </div>

                        <div class="item">
                            <div class="item-icone">✓</div>
                            <div class="item-conteudo">
                                <span>Nível de acesso</span>
                                <strong><?= htmlspecialchars($tipoAcesso, ENT_QUOTES, "UTF-8") ?></strong>
                            </div>
                        </div>
                    </div>

                    <div class="aviso-dados">
                        <span class="aviso-icone">i</span>
                        <p>Seus dados são utilizados para identificar sua conta no SIGEP-EPI.</p>
                    </div>
                </section>

                <section class="painel painel-senha">
                    <div class="painel-cabecalho">
                        <div class="icone-painel verde">⌑</div>
                        <div>
                            <h3>Segurança da conta</h3>
                            <p>Atualize sua senha de acesso.</p>
                        </div>
                    </div>

                    <div class="seguranca-info">
                        <div class="escudo">✓</div>
                        <div>
                            <strong>Mantenha sua conta protegida</strong>
                            <p>Utilize uma senha exclusiva com pelo menos 8 caracteres.</p>
                        </div>
                    </div>

                    <form method="POST" action="perfil.php">
                        <input
                            type="hidden"
                            name="token"
                            value="<?= htmlspecialchars($_SESSION["token_perfil"], ENT_QUOTES, "UTF-8") ?>"
                        >

                        <div class="campo">
                            <label for="senha_atual">Senha atual</label>
                            <input
                                type="password"
                                id="senha_atual"
                                name="senha_atual"
                                placeholder="Digite sua senha atual"
                                autocomplete="current-password"
                                required
                            >
                        </div>

                        <div class="campo">
                            <label for="nova_senha">Nova senha</label>
                            <input
                                type="password"
                                id="nova_senha"
                                name="nova_senha"
                                placeholder="Crie uma nova senha"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >
                            <small>Mínimo de 8 caracteres.</small>
                        </div>

                        <div class="campo">
                            <label for="confirmar_senha">Confirmar nova senha</label>
                            <input
                                type="password"
                                id="confirmar_senha"
                                name="confirmar_senha"
                                placeholder="Digite a nova senha novamente"
                                minlength="8"
                                autocomplete="new-password"
                                required
                            >
                        </div>

                        <button type="submit">
                            <span>Alterar senha</span>
                            <span class="seta-botao">→</span>
                        </button>
                    </form>
                </section>
            </div>

            <footer class="rodape">
                <span>SIGEP-EPI</span>
                <span>Sistema de Gestão de EPI's</span>
            </footer>
        </main>
    </div>
</body>
</html>
