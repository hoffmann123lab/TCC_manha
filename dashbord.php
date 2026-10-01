<?php
$nome = "Usuário";
$tipo = "Operador";
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEP-EPI - Início</title>
    <link rel="stylesheet" href="css/inicio.css">
</head>
<body>

<div class="layout">

    <aside class="menu">

        <div class="logo">
            <div class="logo-icon">⚙</div>
            <div>
                <h1>SIGEP-EPI</h1>
                <span>Gestão de Produção</span>
            </div>
        </div>

        <nav>
            <a href="inicio.php" class="ativo">
                <span>⌂</span>
                Início
            </a>

            <a href="#">
                <span>▣</span>
                Produção
            </a>

            <a href="#">
                <span>▤</span>
                Análises
            </a>

            <a href="#">
                <span>▱</span>
                Planilhas
            </a>

            <a href="#">
                <span>▥</span>
                Relatórios
            </a>
        </nav>

        <div class="menu-final">
            <a href="#">
                <span>⚙</span>
                Configurações
            </a>

            <a href="login.php">
                <span>↪</span>
                Sair
            </a>
        </div>

    </aside>

    <main class="conteudo">

        <header class="topo">
            <div>
                <h2>Início</h2>
                <p>Gerencie suas atividades de produção.</p>
            </div>

            <div class="usuario">
                <div class="avatar">U</div>

                <div>
                    <strong><?php echo $nome; ?></strong>
                    <span><?php echo $tipo; ?></span>
                </div>
            </div>
        </header>

        <section class="boas-vindas">
            <h1>Olá, <?php echo $nome; ?>!</h1>
            <p>O que você deseja fazer?</p>
        </section>

        <section class="acoes">

            <a href="#" class="acao">
                <div class="icone azul">＋</div>
                <div>
                    <h3>Nova Ordem de Produção</h3>
                    <p>Inicie uma nova ordem de produção.</p>
                </div>
                <span class="seta">→</span>
            </a>

            <a href="#" class="acao">
                <div class="icone azul">↑</div>
                <div>
                    <h3>Importar Planilha</h3>
                    <p>Envie uma planilha Excel para análise.</p>
                </div>
                <span class="seta">→</span>
            </a>

            <a href="#" class="acao">
                <div class="icone verde">▤</div>
                <div>
                    <h3>Visualizar Produção</h3>
                    <p>Consulte os dados da produção.</p>
                </div>
                <span class="seta">→</span>
            </a>

            <a href="#" class="acao">
                <div class="icone amarelo">▥</div>
                <div>
                    <h3>Relatórios</h3>
                    <p>Visualize e gere relatórios.</p>
                </div>
                <span class="seta">→</span>
            </a>

        </section>

        <section class="informacao">

            <div>
                <h2>Fluxo de trabalho</h2>
                <p>
                    Importe seus dados, organize as informações
                    e acompanhe a produção em um único lugar.
                </p>
            </div>

            <div class="fluxo">

                <div class="etapa">
                    <span>1</span>
                    <p>Importar</p>
                </div>

                <div class="linha"></div>

                <div class="etapa">
                    <span>2</span>
                    <p>Validar</p>
                </div>

                <div class="linha"></div>

                <div class="etapa">
                    <span>3</span>
                    <p>Analisar</p>
                </div>

                <div class="linha"></div>

                <div class="etapa">
                    <span>4</span>
                    <p>Relatório</p>
                </div>

            </div>

        </section>

    </main>

</div>

</body>
</html>