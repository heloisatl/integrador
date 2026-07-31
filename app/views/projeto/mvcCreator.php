<div id="mvcCreator">

    <?php
    include_once(__DIR__ . "/../include/head.php");
    include_once(__DIR__ . "/../include/navigation.php");
    ?>

    <link rel="stylesheet" href="../include/estilos/style.css">

    <?php
    $step = $_GET['step'] ?? 'configurar';

    $steps = [
        'configurar' => [
            'title' => 'Configurar Projeto',
            'number' => '1',
            'content' => function () {
                echo '<div class="mvc-etapa-cartao">'; // Ajustado para mvc-etapa-cartao

                echo '<div class="mvc-aviso-info">💡 <span>As credenciais foram pré-carregadas das <strong>Configurações Globais</strong>. Você só precisa informar o nome do projeto e selecionar o banco.</span></div>';

                echo '<div class="mvc-grade-formulario">'; // Ajustado para mvc-grade-formulario

                echo '<div class="mvc-campo">';
                echo '<label>Nome do Projeto <span class="mvc-etiqueta mvc-etiqueta-discreta">Sem espaços</span></label>';
                echo '<input type="text" name="nomeProjeto" id="nomeProjeto" value="sistema_oficina" placeholder="Nome do Projeto">';
                echo '</div>';

                echo '<div class="mvc-campo">';
                echo '<label>Servidor <span class="mvc-etiqueta mvc-etiqueta-discreta">Da config global</span></label>';
                echo '<input type="text" name="servidor" id="servidor" value="localhost" disabled placeholder="localhost">';
                echo '</div>';

                echo '<div class="mvc-campo">';
                echo '<label>Usuário</label>';
                echo '<input type="text" name="usuario" id="usuario" value="root" placeholder="root">';
                echo '</div>';

                echo '<div class="mvc-campo">';
                echo '<label>Senha</label>';
                echo '<input type="password" name="senha" id="senha" value="123456" placeholder="*********">';
                echo '</div>';

                echo '<div class="mvc-campo mvc-campo-completo">';
                echo '<label>Banco de Dados</label>';
                echo '<div class="mvc-linha-banco">'; // Ajustado para mvc-linha-banco
                echo '<select name="banco" id="banco"><option value="">— preencha a senha para carregar —</option><option value="EXEMPLO_BANCO">EXEMPLO</option></select>';
                echo '<button type="button" class="mvc-etapa-botao mvc-etapa-botao-secundario">🔄 Recarregar bancos</button>';
                echo '</div>';
                echo '</div>';
                
                echo '</div>'; // Fecha mvc-grade-formulario

                echo '<div class="mvc-acoes">'; // Ajustado para mvc-acoes
                echo '<button class="mvc-etapa-botao">⚡ Conectar e Detectar Tabelas</button>';
                echo '<button class="mvc-etapa-botao mvc-etapa-botao-secundario">📁 Exemplo: banco "oficina"</button>';
                echo '</div>';

                echo '</div>'; // Fecha mvc-etapa-cartao
            }
        ],
        'tabelas' => [
            'title' => 'Tabelas Detectadas',
            'number' => '2',
            'content' => function () {
                echo '<div class="mvc-etapa-cartao">';
                echo '<p>As tabelas encontradas aparecerão aqui.</p>';
                echo '<button class="mvc-etapa-botao">Próximo →</button>';
                echo '</div>';
            }
        ],
        'opcoes' => [
            'title' => 'Opções de Geração',
            'number' => '3',
            'content' => function () {
                echo '<div class="mvc-etapa-cartao">';
                echo '<p class="mvc-subtitulo">O que será gerado</p>';
                echo '<form class="mvc-formulario-opcoes">';
                echo '<label><input type="checkbox" checked> Camada Model</label>';
                echo '<label><input type="checkbox" checked> Camada Controller</label>';
                echo '<label><input type="checkbox" checked> Camada DAO</label>';
                echo '<label><input type="checkbox" checked> Views</label>';
                echo '<label><input type="checkbox"> Quero receber novidades por e-mail</label>';
                echo '</form>';
                echo '<button class="mvc-etapa-botao">Ver Estrutura de Arquivos →</button>';
                echo '</div>';
            }
        ],
        'estrutura' => [
            'title' => 'Estrutura de Arquivos que Será Criada',
            'number' => '4',
            'tag' => 'NOVO',
            'content' => function () {
                echo '<div class="mvc-etapa-cartao">';
                echo '<p class="mvc-subtitulo">Visualize a árvore de diretórios antes de gerar.</p>';
        
                echo '<div class="mvc-acoes">';
                echo '<button class="mvc-etapa-botao">Confirmar e Gerar →</button>';
                echo '<button class="mvc-etapa-botao mvc-etapa-botao-secundario">← Voltar</button>';
                echo '</div>';
                echo '</div>';
            }
        ],
        'gerar' => [
            'title' => 'Gerar Sistema',
            'number' => '5',
            'content' => function () {
                echo '<div class="mvc-etapa-cartao">';
                echo '<button class="mvc-etapa-botao"> Gerar Todo o Sistema</button>';
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
    </div> </div> </body>
</html>