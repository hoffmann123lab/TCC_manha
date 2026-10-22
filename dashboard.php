
<?php
session_start();

if (!isset($_SESSION["id_fun"])) {
    header("Location: index.php");
    exit;
}

$nome = $_SESSION["nome_fun"] ?? "Funcionário";
$tipoAcesso = $_SESSION["tipo"] ?? "Operador";
$inicial = strtoupper(substr($nome, 0, 1));
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIGEP-EPI</title>
    <link rel="stylesheet" href="dashboard.css">
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

                <a href="perfil.php">
                    <span class="icone-menu">♙</span>
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
                    <span class="subtitulo">PAINEL PRINCIPAL</span>
                    <h2>
                        Olá, <?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>!
                    </h2>
                    <p>O que você deseja fazer hoje?</p>
                </div>

                <div class="usuario">
                    <div class="avatar">
                        <?= htmlspecialchars($inicial, ENT_QUOTES, "UTF-8") ?>
                    </div>

                    <div class="usuario-info">
                        <strong>
                            <?= htmlspecialchars($nome, ENT_QUOTES, "UTF-8") ?>
                        </strong>
                        <span>
                            <?= $tipoAcesso === "Supervisor" ? "Supervisor" : "Funcionário / Operador" ?>
                        </span>
                    </div>
                </div>
            </header>

            <section class="cards">
                <div class="card">
                    <div class="card-icone">📦</div>
                    <div>
                        <span>Total de EPIs</span>
                        <strong>0</strong>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icone">✓</div>
                    <div>
                        <span>Disponíveis</span>
                        <strong>0</strong>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icone">⚠</div>
                    <div>
                        <span>Estoque baixo</span>
                        <strong>0</strong>
                    </div>
                </div>

                <div class="card">
                    <div class="card-icone">!</div>
                    <div>
                        <span>Críticos</span>
                        <strong>0</strong>
                    </div>
                </div>
            </section>

            <section class="acoes">
                <h3>Acesso rápido</h3>

                <div class="acoes-grid">
                    <a href="producao.php" class="acao">
                        <div class="acao-icone">▤</div>
                        <div class="acao-texto">
                            <strong>Produção</strong>
                            <span>Consultar informações de produção</span>
                        </div>
                        <b>→</b>
                    </a>

                    <a href="planilhas.php" class="acao">
                        <div class="acao-icone">▦</div>
                        <div class="acao-texto">
                            <strong>Planilhas</strong>
                            <span>Consultar planilhas do sistema</span>
                        </div>
                        <b>→</b>
                    </a>

                    <a href="analises.php" class="acao">
                        <div class="acao-icone">▥</div>
                        <div class="acao-texto">
                            <strong>Análises</strong>
                            <span>Visualizar indicadores</span>
                        </div>
                        <b>→</b>
                    </a>

                    <a href="relatorios.php" class="acao">
                        <div class="acao-icone">▧</div>
                        <div class="acao-texto">
                            <strong>Relatórios</strong>
                            <span>Consultar relatórios</span>
                        </div>
                        <b>→</b>
                    </a>
                </div>
            </section>

            <section class="atividade">
                <div class="secao-titulo">
                    <div>
                        <h3>Atividade recente</h3>
                        <span>Últimas movimentações do sistema</span>
                    </div>
                </div>

                <div class="atividade-vazia">
                    <div>▤</div>
                    <strong>Nenhuma atividade registrada</strong>
                    <span>As movimentações aparecerão aqui.</span>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
