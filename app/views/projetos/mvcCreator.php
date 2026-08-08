<div id="mvcCreator">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-mvc.css">

    <?php
    $step = $_GET['step'] ?? 'configurar';

    $steps = [
        'configurar' => [
            'title' => 'Configurar Projeto',
            'number' => '1',
            'content' => function () {
                echo '<div class="mvc-etapa">';

                echo '<div class="mvc-aviso-info">💡 <span>As credenciais foram pré-carregadas das <strong>Configurações Globais</strong>. Informe o nome do projeto e selecione o banco de dados.</span></div>';

                echo '<div class="mvc-grade-formulario">';

                echo '<div class="mvc-campo">';
                echo '<label>Nome do Projeto <span class="etiqueta">Sem espaços</span></label>';
                echo '<input type="text" name="nomeProjeto" id="nomeProjeto" value="sistema_oficina" placeholder="Nome do Projeto">';
                echo '</div>';

                echo '<div class="mvc-campo">';
                echo '<label>Servidor <span class="etiqueta">Da config global</span></label>';
                echo '<input type="text" name="servidor" id="servidor" value="localhost" placeholder="localhost">';
                echo '</div>';

                echo '<div class="mvc-campo">';
                echo '<label>Usuário</label>';
                echo '<input type="text" name="usuario" id="usuario" value="root" placeholder="root">';
                echo '</div>';

                echo '<div class="mvc-campo">';
                echo '<label>Senha</label>';
                echo '<input type="password" onblur="carregarBanco();" name="senha" id="senha" value="" placeholder="*********">';
                echo '</div>';

                echo '<div class="mvc-campo mvc-campo-completo">';
                echo '<label>Banco de Dados</label>';
                echo '<div class="mvc-linha-banco">';
                echo '<select name="banco" id="banco"><option value="">— preencha a senha para carregar —</option></select>';
                echo '<button type="button" onclick="carregarBanco();" class="mvc-etapa-botao mvc-etapa-botao-secundario">Recarregar bancos</button>';
                echo '</div>';
                echo '</div>';

                echo '</div>';

                echo '<div class="mvc-acoes">';
                echo '<button type="button" onclick="carregarTabelas();" class="mvc-etapa-botao">Conectar e Detectar Tabelas →</button>';
                echo '</div>';

                echo '</div>';
            }
        ],
        'tabelas' => [
            'title' => 'Tabelas Detectadas',
            'number' => '2',
            'content' => function () {
                echo '<div class="mvc-etapa">';
                echo '<p class="mvc-subtitulo">Selecione as tabelas que farão parte do sistema MVC:</p>';
                echo '<div id="container-tabelas"><p style="color: #666;">Carregando tabelas do banco...</p></div>';
                echo '<div class="mvc-acoes">';
                echo '<a href="?step=opcoes" class="mvc-etapa-botao">Próximo: Opções de Geração →</a>';
                echo '<a href="?step=configurar" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>';
                echo '</div>';
                echo '</div>';
            }
        ],
        'opcoes' => [
            'title' => 'Opções de Geração',
            'number' => '3',
            'content' => function () {
                echo '<div class="mvc-etapa">';
                echo '<p class="mvc-subtitulo">Selecione as camadas que deseja gerar:</p>';
                echo '<form class="mvc-formulario-opcoes">';
                echo '<label><input type="checkbox" checked disabled> Camada Model (Models PSR-4)</label>';
                echo '<label><input type="checkbox" checked disabled> Camada Controller (Controllers RESTful)</label>';
                echo '<label><input type="checkbox" checked disabled> Camada Repositório (DAO / PDO ConnectionFactory)</label>';
                echo '<label><input type="checkbox" checked disabled> Camada Views (Listagem, Cadastro e Edição)</label>';
                echo '</form>';
                echo '<div class="mvc-acoes">';
                echo '<a href="?step=estrutura" class="mvc-etapa-botao">Ver Estrutura de Arquivos →</a>';
                echo '<a href="?step=tabelas" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>';
                echo '</div>';
                echo '</div>';
            }
        ],
        'estrutura' => [
            'title' => 'Estrutura de Arquivos que Será Criada',
            'number' => '4',
            'tag' => 'VISUALIZAÇÃO',
            'content' => function () {
                echo '<div class="mvc-etapa">';
                echo '<p class="mvc-subtitulo">Árvore de diretórios gerada automaticamente:</p>';

                echo '<div style="background: #1e1e1e; color: #d4d4d4; padding: 15px; border-radius: 8px; font-family: monospace; font-size: 14px; margin: 15px 0; overflow-x: auto; white-space: nowrap;">';
                echo '📂 app/<br>';
                echo '&nbsp;&nbsp;├── 📂 controllers/<br>';
                echo '&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 📄 [NomeTabela]Controller.php<br>';
                echo '&nbsp;&nbsp;├── 📂 models/<br>';
                echo '&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 📄 [NomeTabela].php<br>';
                echo '&nbsp;&nbsp;├── 📂 repositories/<br>';
                echo '&nbsp;&nbsp;│&nbsp;&nbsp;&nbsp;└── 📄 [NomeTabela]Repository.php<br>';
                echo '&nbsp;&nbsp;└── 📂 views/<br>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── 📂 [nometabela]s/<br>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📄 index.php<br>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;├── 📄 create.php<br>';
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;└── 📄 edit.php<br>';
                echo '</div>';

                echo '<div class="mvc-acoes">';
                echo '<a href="?step=gerar" class="mvc-etapa-botao">Avançar para Geração →</a>';
                echo '<a href="?step=opcoes" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>';
                echo '</div>';
                echo '</div>';
            }
        ],
        'gerar' => [
            'title' => 'Gerar Sistema',
            'number' => '5',
            'content' => function () {
                echo '<div class="mvc-etapa">';
                echo '<p class="mvc-subtitulo">Clique no botão abaixo para gerar o código MVC e baixar o arquivo .ZIP completo:</p>';
                echo '<div class="mvc-acoes">';
                echo '<button type="button" id="btn-gerar-final" onclick="executarGeracaoMvc();" class="mvc-etapa-botao" style="font-size: 16px; padding: 12px 24px;">🚀 Gerar Todo o Sistema (.ZIP)</button>';
                echo '<a href="?step=estrutura" class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</a>';
                echo '</div>';
                echo '</div>';
            }
        ]
    ];

    if (!isset($steps[$step])) {
        $step = 'configurar';
    }
    ?>

    <div class="mvc-etapa-wrapper">
        <div class="mvc-etapa-cabecalho">
            <span class="mvc-etapa-selo"><?= htmlspecialchars($steps[$step]['number']) ?></span>
            <h2><?= htmlspecialchars($steps[$step]['title']) ?></h2>
            <?php if (!empty($steps[$step]['tag'])): ?>
                <span class="mvc-etapa-selo-novo"><?= htmlspecialchars($steps[$step]['tag']) ?></span>
            <?php endif; ?>
        </div>

        <?php $steps[$step]['content'](); ?>
    </div>

    </main>
    <script src="<?= URL_BASE ?>/assets/js/mvcLoad.js"></script>
</div>
</div>
</body>

</html>