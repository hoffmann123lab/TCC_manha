
<?php

require "connection.php";

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nomeSistema = trim($_POST["nome_sistema"] ?? "");

    if ($nomeSistema === "") {
        $mensagem = "Informe o nome do sistema.";
    } else {
        $mensagem = "As configurações foram atualizadas nesta página.";
    }
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - SIGEP-EPI</title>
    <link rel="stylesheet" href="configuracoes.css">
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
                <a href="dashboard.php">🏠 <span>Início</span></a>
                <a href="producao.php">📋 <span>Produção</span></a>
                <a href="analises.php">📊 <span>Análises</span></a>
                <a href="planilhas.php">📁 <span>Planilhas</span></a>
                <a href="relatorios.php">📈 <span>Relatórios</span></a>
                <a href="estoque.php">📦 <span>Estoque</span></a>
                <a href="usuarios.php">👥 <span>Funcionários</span></a>
                <a href="perfil.php">👤 <span>Meu perfil</span></a>
                <a href="configuracoes.php" class="ativo">⚙ <span>Configurações</span></a>
            </nav>

            <a href="index.php" class="sair">🚪 <span>Sair</span></a>
        </aside>

        <main class="conteudo">
            <header class="topo">
                <span class="subtitulo">PERSONALIZAÇÃO</span>
                <h2>Configurações</h2>
                <p>Consulte as informações e preferências do sistema.</p>
            </header>

            <?php if ($mensagem !== ""): ?>
                <div class="mensagem">
                    <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
                </div>
            <?php endif; ?>

            <section class="painel">
                <div class="titulo-painel">
                    <h3>Informações do sistema</h3>
                    <p>Dados gerais do SIGEP-EPI.</p>
                </div>

                <form method="POST" action="configuracoes.php">
                    <div class="campo">
                        <label for="nome_sistema">Nome do sistema</label>
                        <input
                            type="text"
                            id="nome_sistema"
                            name="nome_sistema"
                            value="<?= htmlspecialchars($nomeSistema ?? "SIGEP-EPI", ENT_QUOTES, "UTF-8") ?>"
                            required
                        >
                    </div>

                    <div class="campo">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" readonly>Sistema Inteligente de Gestão e Análise de EPI's</textarea>
                    </div>

                    <div class="campo">
                        <label for="banco">Banco de dados</label>
                        <input type="text" id="banco" value="MySQL - sigep_epi" readonly>
                    </div>

                    <button type="submit">Salvar configurações</button>
                </form>
            </section>

            <section class="painel">
                <div class="titulo-painel">
                    <h3>Informações da conta</h3>
                    <p>Características do acesso ao sistema.</p>
                </div>

                <div class="informacao">
                    <span>Tipo de acesso</span>
                    <strong>Funcionário / Operador</strong>
                </div>

                <div class="informacao">
                    <span>Autenticação</span>
                    <strong>E-mail e senha</strong>
                </div>
            </section>

            <section class="painel sobre">
                <div class="titulo-painel">
                    <h3>Sobre o SIGEP-EPI</h3>
                    <p>Sistema para gestão e acompanhamento de registros relacionados à produção e aos equipamentos de proteção individual.</p>
                </div>

                <span class="versao">SIGEP-EPI | Sistema de Gestão</span>
            </section>
        </main>
    </div>
</body>
</html>
