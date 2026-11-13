
<?php

session_start();

?>

<!DOCTYPE html>
<html lang="pt-br">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SIGEP-EPI | Sistema de Gestão</title>

    <link rel="stylesheet" href="index.css?v=3">

</head>

<body>

    <header class="cabecalho">

        <a href="index.php" class="logo">

            <div class="logo-icone">⚙</div>

            <div>
                <strong>SIGEP-EPI</strong>
                <span>Sistema de Gestão</span>
            </div>

        </a>

        <nav>

            <a href="#sobre">Sobre</a>
            <a href="#funcionamento">Funcionamento</a>
            <a href="#recursos">Recursos</a>
            <a href="logar.php" class="botao-entrar">Entrar no sistema</a>

        </nav>

    </header>

    <main>

        <section class="principal">

            <div class="principal-texto">

                <span class="etiqueta">GESTÃO INTELIGENTE DE EPI</span>

                <h1>
                    Gerencie seus EPIs com
                    <span>mais organização e inteligência.</span>
                </h1>

                <p>
                    O SIGEP-EPI é um sistema desenvolvido para facilitar
                    o gerenciamento de equipamentos de proteção individual,
                    permitindo importar planilhas, acompanhar estoques,
                    analisar informações e gerar apresentações.
                </p>

                <div class="principal-botoes">

                    <a href="logar.php" class="botao-principal">
                        Acessar sistema →
                    </a>

                    <a href="#funcionamento" class="botao-secundario">
                        Conhecer funcionamento
                    </a>

                </div>

            </div>

            <div class="principal-card">

                <div class="card-topo">
                    <span>PAINEL SIGEP-EPI</span>
                    <span class="status">● Sistema de gestão</span>
                </div>

                <div class="card-indicadores">

                    <div>
                        <span>Estoque</span>
                        <strong>Organizado</strong>
                    </div>

                    <div>
                        <span>Relatórios</span>
                        <strong>Automáticos</strong>
                    </div>

                    <div>
                        <span>Análises</span>
                        <strong>Visuais</strong>
                    </div>

                </div>

                <div class="grafico-ilustrativo">

                    <div class="grafico-titulo">
                        <strong>Análise de dados</strong>
                        <span>Visualização demonstrativa</span>
                    </div>

                    <div class="barras">
                        <div style="height: 45%"></div>
                        <div style="height: 70%"></div>
                        <div style="height: 55%"></div>
                        <div style="height: 90%"></div>
                        <div style="height: 65%"></div>
                        <div style="height: 100%"></div>
                    </div>

                </div>

                <div class="card-rodape">
                    <span>Importação de planilhas</span>
                    <strong>→ Análises e apresentações</strong>
                </div>

            </div>

        </section>

        <section class="sobre" id="sobre">

            <span class="etiqueta">SOBRE O PROJETO</span>

            <h2>O que é o SIGEP-EPI?</h2>

            <p>
                O Sistema Inteligente de Gestão e Análise de EPI's
                foi desenvolvido para centralizar informações sobre
                equipamentos de proteção individual, facilitando
                o controle, a consulta e a interpretação dos dados.
            </p>

        </section>

        <section class="funcionamento" id="funcionamento">

            <div class="secao-cabecalho">

                <span class="etiqueta">COMO FUNCIONA</span>

                <h2>Do arquivo à análise dos dados</h2>

                <p>
                    Entenda as etapas de utilização do sistema.
                </p>

            </div>

            <div class="etapas">

                <div class="etapa">

                    <div class="etapa-numero">01</div>
                    <div class="etapa-icone">📄</div>

                    <h3>Importação</h3>

                    <p>
                        O usuário seleciona uma planilha CSV com os
                        dados dos equipamentos. O sistema verifica
                        os campos obrigatórios antes da importação.
                    </p>

                </div>

                <div class="etapa">

                    <div class="etapa-numero">02</div>
                    <div class="etapa-icone">🗄️</div>

                    <h3>Armazenamento</h3>

                    <p>
                        Após a validação, os registros são armazenados
                        no banco de dados, permitindo consultas e
                        acompanhamento das informações.
                    </p>

                </div>

                <div class="etapa">

                    <div class="etapa-numero">03</div>
                    <div class="etapa-icone">📊</div>

                    <h3>Análises</h3>

                    <p>
                        Os dados importados são utilizados para gerar
                        indicadores, gráficos e informações sobre
                        produtos e estoques.
                    </p>

                </div>

                <div class="etapa">

                    <div class="etapa-numero">04</div>
                    <div class="etapa-icone">📈</div>

                    <h3>Apresentações</h3>

                    <p>
                        Cada planilha pode gerar uma apresentação
                        individual com gráficos, indicadores e
                        informações comparativas, que pode ser
                        salva em PDF.
                    </p>

                </div>

            </div>

        </section>

        <section class="recursos" id="recursos">

            <div class="secao-cabecalho">

                <span class="etiqueta">FUNCIONALIDADES</span>

                <h2>Recursos disponíveis no sistema</h2>

            </div>

            <div class="recursos-grid">

                <div class="recurso">

                    <div class="recurso-icone">📦</div>

                    <h3>Controle de estoque</h3>

                    <p>
                        Acompanhamento das quantidades disponíveis
                        e identificação de estoques baixos.
                    </p>

                </div>

                <div class="recurso">

                    <div class="recurso-icone">📋</div>

                    <h3>Relatórios</h3>

                    <p>
                        Consulta dos registros importados, com
                        pesquisa e visualização das informações.
                    </p>

                </div>

                <div class="recurso">

                    <div class="recurso-icone">📊</div>

                    <h3>Apresentações individuais</h3>

                    <p>
                        Gráficos e indicadores gerados a partir
                        de cada planilha importada.
                    </p>

                </div>

                <div class="recurso">

                    <div class="recurso-icone">🔍</div>

                    <h3>Comparação de planilhas</h3>

                    <p>
                        Seleção de diferentes planilhas para
                        comparar produtos, setores e quantidades.
                    </p>

                </div>

                <div class="recurso">

                    <div class="recurso-icone">📑</div>

                    <h3>Exportação PDF</h3>

                    <p>
                        Possibilidade de salvar as apresentações
                        em PDF para consulta e compartilhamento.
                    </p>

                </div>

                <div class="recurso">

                    <div class="recurso-icone">📁</div>

                    <h3>Arquivo CSV e Power BI</h3>

                    <p>
                        Possibilidade de exportar dados em CSV
                        para utilização em ferramentas externas,
                        como o Power BI, quando habilitada.
                    </p>

                </div>

            </div>

        </section>

        <section class="csv">

            <div>

                <span class="etiqueta">INTEGRAÇÃO DE DADOS</span>

                <h2>Dados organizados para outras ferramentas</h2>

                <p>
                    O formato CSV permite organizar e transportar
                    informações para ferramentas de análise externa,
                    como o Microsoft Power BI. Essa integração é
                    uma possibilidade complementar ao sistema.
                </p>

            </div>

            <div class="csv-icone">CSV</div>

        </section>

        <section class="chamada">

            <span class="etiqueta">COMECE AGORA</span>

            <h2>Conheça o SIGEP-EPI na prática.</h2>

            <p>
                Acesse o sistema e utilize suas ferramentas
                de gestão, consulta e análise.
            </p>

            <a href="logar.php" class="botao-principal">
                Entrar no sistema →
            </a>

        </section>

    </main>

    <footer>

        <strong>SIGEP-EPI</strong>

        <span>Sistema Inteligente de Gestão e Análise de EPI's</span>

        <span>Projeto acadêmico</span>

    </footer>

</body>

</html>
