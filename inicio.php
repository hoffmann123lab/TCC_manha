<?php

$nome = "Funcionário";

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Dashboard - SIGEP-EPI</title>

    <link rel="stylesheet" href="inicio.css">

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

                <a href="dashboard.php" class="ativo">
                    🏠
                    <span>Início</span>
                </a>

                <a href="#">
                    📋
                    <span>Produção</span>
                </a>

                <a href="#">
                    📊
                    <span>Análises</span>
                </a>

                <a href="#">
                    📁
                    <span>Planilhas</span>
                </a>

                <a href="#">
                    📈
                    <span>Relatórios</span>
                </a>

                <a href="#">
                    ⚙
                    <span>Configurações</span>
                </a>

            </nav>

            <a href="index.php" class="sair">
                🚪
                <span>Sair</span>
            </a>

        </aside>

        <main class="conteudo">

            <header class="topo">

                <div>

                    <span class="subtitulo">
                        PAINEL PRINCIPAL
                    </span>

                    <h2>
                        Olá, <?= htmlspecialchars($nome) ?>!
                    </h2>

                    <p>
                        O que você deseja fazer hoje?
                    </p>

                </div>

                <div class="usuario">

                    <div class="avatar">
                        F
                    </div>

                    <div>
                        <strong><?= htmlspecialchars($nome) ?></strong>
                        <span>Funcionário</span>
                    </div>

                </div>

            </header>

            <section class="cards">

                <div class="card">

                    <div class="card-icone">
                        📦
                    </div>

                    <div>

                        <span>Total de EPIs</span>

                        <strong>0</strong>

                    </div>

                </div>

                <div class="card">

                    <div class="card-icone">
                        ✅
                    </div>

                    <div>

                        <span>Disponíveis</span>

                        <strong>0</strong>

                    </div>

                </div>

                <div class="card">

                    <div class="card-icone">
                        ⚠️
                    </div>

                    <div>

                        <span>Estoque baixo</span>

                        <strong>0</strong>

                    </div>

                </div>

                <div class="card">

                    <div class="card-icone">
                        🔴
                    </div>

                    <div>

                        <span>Críticos</span>

                        <strong>0</strong>

                    </div>

                </div>

            </section>

            <section class="acoes">

                <h3>Acesso rápido</h3>

                <div class="acoes-grid">

                    <a href="#" class="acao">

                        <div class="acao-icone">
                            📋
                        </div>

                        <div>

                            <strong>Produção</strong>

                            <span>
                                Consultar informações de produção
                            </span>

                        </div>

                        <b>→</b>

                    </a>

                    <a href="#" class="acao">

                        <div class="acao-icone">
                            📁
                        </div>

                        <div>

                            <strong>Planilhas</strong>

                            <span>
                                Consultar planilhas do sistema
                            </span>

                        </div>

                        <b>→</b>

                    </a>

                    <a href="#" class="acao">

                        <div class="acao-icone">
                            📊
                        </div>

                        <div>

                            <strong>Análises</strong>

                            <span>
                                Visualizar indicadores
                            </span>

                        </div>

                        <b>→</b>

                    </a>

                    <a href="#" class="acao">

                        <div class="acao-icone">
                            📈
                        </div>

                        <div>

                            <strong>Relatórios</strong>

                            <span>
                                Consultar relatórios
                            </span>

                        </div>

                        <b>→</b>

                    </a>

                </div>

            </section>

            <section class="atividade">

                <div class="secao-titulo">

                    <div>

                        <h3>Atividade recente</h3>

                        <span>
                            Últimas movimentações do sistema
                        </span>

                    </div>

                </div>

                <div class="atividade-vazia">

                    <div>
                        📋
                    </div>

                    <strong>
                        Nenhuma atividade registrada
                    </strong>

                    <span>
                        As movimentações aparecerão aqui.
                    </span>

                </div>

            </section>

        </main>

    </div>

</body>

</html>