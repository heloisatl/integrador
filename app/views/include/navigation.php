<?php
// Antes essa detecção usava basename($_SERVER['PHP_SELF']), que fazia sentido quando cada página era um arquivo .php separado (home.php, configGlobal.php...
// Agora TODA requisição passa pelo mesmo front controller (public/index.php),
// então PHP_SELF sempre retornaria "index.php" e o menu nunca marcaria página ativa corretamente. A detecção agora usa a rota da URL atual.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$paginaAtual = trim(basename(rtrim($requestPath, '/')), '/');
if ($paginaAtual === '' || $paginaAtual === 'index.php') {
    $paginaAtual = 'projetos'; // rota "/projetos" = home
}

$currentSection = $_GET['section'] ?? '';
$currentTab = $_GET['tab'] ?? '';
$currentStep = $_GET['step'] ?? '';

function usuarioEhAdmin(): bool
{
    return isset($_SESSION['usuario_logado']) && strtolower($_SESSION['usuario_logado']->getTipoPerfil()) === 'admin';
}

if ($paginaAtual === 'pagemaker' && $currentSection === '') {
    $currentSection = 'cabecalho';
}
if ($paginaAtual === 'historico' && $currentTab === '') {
    $currentTab = 'visualizar';
}
if ($paginaAtual === 'saida' && $currentTab === '') {
    $currentTab = 'todos';
}
if ($paginaAtual === 'mvc-creator' && $currentStep === '') {
    $currentStep = 'configurar';
}

// Função auxiliar para injetar a classe 'active' de forma dinâmica.
// $slug agora é o pedaço final da rota (ex: 'projetos', 'config-global',
// 'mvc-creator', 'pagemaker', 'historico', 'saida', 'guia').
function verificarAtivo($slug, $paginaAtual, $queryKey = null, $queryValue = null)
{
    if ($slug !== $paginaAtual) {
        return '';
    }

    if ($queryKey === null) {
        return 'active';
    }


    global $currentSection, $currentTab, $currentStep;

    if ($queryKey === 'section') {
        return $currentSection === $queryValue ? 'active' : '';
    }

    if ($queryKey === 'tab') {
        return $currentTab === $queryValue ? 'active' : '';
    }

    if ($queryKey === 'step') {
        return $currentStep === $queryValue ? 'active' : '';
    }

    return '';
}
?>

<header class="topbar">
    <div class="topbar-logo">
        <span class="logo"></span>
        <h1 class="title"> <a class="title-link" href="<?= URL_BASE ?>/projetos">DevStudio </a></h1>
    </div>

    <button type="button" class="topbar-toggle" id="topbarToggle" aria-label="Abrir menu" aria-expanded="false" aria-controls="topbarNav" onclick="toggleTopbarNav(event)">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="topbar-nav" id="topbarNav">
        <a href="<?= URL_BASE ?>/projetos" class="topbar-item-inicio <?php echo verificarAtivo('projetos', $paginaAtual); ?>">
            Início
        </a>

        <a href="<?= URL_BASE ?>/projetos/config-global" class="topbar-item <?php echo verificarAtivo('config-global', $paginaAtual); ?>">
            Config. Global
        </a>
        <a href="<?= URL_BASE ?>/projetos/mvc-creator" class="topbar-item <?php echo verificarAtivo('mvc-creator', $paginaAtual); ?>">
            MVC Creator
        </a>
        <a href="<?= URL_BASE ?>/projetos/pagemaker" class="topbar-item <?php echo verificarAtivo('pagemaker', $paginaAtual); ?>">
            Page Maker </a>

        <a href="<?= URL_BASE ?>/projetos/historico" class="topbar-item <?php echo verificarAtivo('historico', $paginaAtual); ?>">
            Histórico </a>

        <a href="<?= URL_BASE ?>/projetos/saida" class="topbar-item <?php echo verificarAtivo('saida', $paginaAtual); ?>">
            Saída </a>

        <?php if (defined('URL_BASE') && usuarioEhAdmin()): ?>
            <a href="<?= URL_BASE ?>/usuarios" class="topbar-item <?php echo $paginaAtual === 'usuarios' ? 'active' : ''; ?>">
                Usuários
            </a>
        <?php endif; ?>

        <div class="topbar-actions">
            <div class="profile-dropdown-container">
                <button type="button" class="profile-btn" id="profileDropdownBtn" onclick="toggleProfileMenu(event)">
                    Perfil
                </button>
                <div class="profile-dropdown-menu" id="profileDropdownMenu">
                    <?php if (isset($_SESSION['usuario_logado'])):
                        $usuarioLogado = $_SESSION['usuario_logado'];
                    ?>
                        <div class="profile-dropdown-header">
                            <div class="profile-user-name"><?= htmlspecialchars($usuarioLogado->getNome()) ?></div>
                            <div class="profile-user-email"><?= htmlspecialchars($usuarioLogado->getEmail()) ?></div>
                        </div>
                        <div class="profile-dropdown-divider"></div>
                    <?php endif; ?>

                    <button type="button" class="profile-dropdown-item" onclick="toggleGlobalTheme()">
                        Alternar Tema
                    </button>

                    <?php if (isset($_SESSION['usuario_logado'])): ?>
                        <a href="<?= URL_BASE ?>/logout" class="profile-dropdown-item profile-dropdown-danger">Sair</a>
                    <?php else: ?>
                        <a href="<?= URL_BASE ?>/login" class="profile-dropdown-item">Entrar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="layout-wrapper">

    <aside class="sidebar">
        <!-- o class está sendo usado para padronizar estilização e o id para identificação especifica de quem vai aparecer ou nn na página -->


        <!-- Inicio e Config Global -->
        <div id="sb-principal" class="sb-section">
            <div class="sb-label">Menu Principal</div>

            <a href="<?= URL_BASE ?>/projetos" class="sb-item <?php echo verificarAtivo('projetos', $paginaAtual); ?>">
                Visão Geral
            </a>

            <a href="<?= URL_BASE ?>/projetos/config-global" class="sb-item <?php echo verificarAtivo('config-global', $paginaAtual); ?>">
                Config. Globais
            </a>

            <a href="<?= URL_BASE ?>/projetos/guia" class="sb-item <?php echo verificarAtivo('guia', $paginaAtual); ?>">
                Guia Rápido
            </a>
        </div>

        <!-- MVC Creator -->
        <div id="sb-titulo-MVC" class="sb-section">
            <div class="sb-label">MVC Creator</div>

            <!-- Agora cada item usa a mesma página mvcCreator.php com parâmetro de mvc-etapa -->
            <a href="<?= URL_BASE ?>/projetos/mvc-creator?step=configurar" class="sb-item <?php echo verificarAtivo('mvc-creator', $paginaAtual, 'step', 'configurar'); ?>"> 1 - Configurar Projeto
            </a>

            <a href="<?= URL_BASE ?>/projetos/mvc-creator?step=tabelas" class="sb-item <?php echo verificarAtivo('mvc-creator', $paginaAtual, 'step', 'tabelas'); ?>"> 2 - Tabelas Detectadas
            </a>

            <a href="<?= URL_BASE ?>/projetos/mvc-creator?step=opcoes" class="sb-item <?php echo verificarAtivo('mvc-creator', $paginaAtual, 'step', 'opcoes'); ?>"> 3 - Opções de Geração
            </a>

            <a href="<?= URL_BASE ?>/projetos/mvc-creator?step=estrutura" class="sb-item <?php echo verificarAtivo('mvc-creator', $paginaAtual, 'step', 'estrutura'); ?>"> 4 - Estrutura de Arquivos
            </a>

            <a href="<?= URL_BASE ?>/projetos/mvc-creator?step=gerar" class="sb-item <?php echo verificarAtivo('mvc-creator', $paginaAtual, 'step', 'gerar'); ?>"> 5 - Gerar e Baixar
            </a>
        </div>

        <!-- Page Maker -->
        <div id="sb-titulo-PageMaker" class="sb-section">
            <div class="sb-label">Page Maker</div>

            <a href="<?= URL_BASE ?>/projetos/pagemaker?step=cabecalho" class="sb-item <?php echo verificarAtivo('pagemaker', $paginaAtual, 'step', 'cabecalho'); ?>"> Cabeçalho
            </a>

            <a href="<?= URL_BASE ?>/projetos/pagemaker?section=cabecalho" class="sb-item <?php echo verificarAtivo('pagemaker', $paginaAtual, 'section', 'cabecalho'); ?>"> <span class="sb-icon"></span> Cabeçalho
            </a>

            <a href="<?= URL_BASE ?>/projetos/pagemaker?section=navegacao" class="sb-item <?php echo verificarAtivo('pagemaker', $paginaAtual, 'section', 'navegacao'); ?>"> <span class="sb-icon"></span> Navegação
            </a>


            <a href="<?= URL_BASE ?>/projetos/pagemaker?section=conteudo" class="sb-item <?php echo verificarAtivo('pagemaker', $paginaAtual, 'section', 'conteudo'); ?>"> <span class="sb-icon"></span> Conteúdo
            </a>


            <a href="<?= URL_BASE ?>/projetos/pagemaker?section=elementos" class="sb-item <?php echo verificarAtivo('pagemaker', $paginaAtual, 'section', 'elementos'); ?>"> <span class="sb-icon"></span> Elementos Extras
            </a>

            <a href="<?= URL_BASE ?>/projetos/pagemaker?section=previsa" class="sb-item <?php echo verificarAtivo('pagemaker', $paginaAtual, 'section', 'previsa'); ?>"> <span class="sb-icon"></span> Prévia
            </a>

        </div>

        <!-- Usuários -->
        <?php if (defined('URL_BASE') && usuarioEhAdmin()): ?>
            <div id="sb-titulo-usuarios" class="sb-section">
                <div class="sb-label">Usuários</div>
                <a href="<?= URL_BASE ?>/usuarios" class="sb-item <?php echo $paginaAtual === 'usuarios' ? 'active' : ''; ?>">
                    <span class="sb-icon"></span> Listar Usuários
                </a>
                <a href="<?= URL_BASE ?>/usuarios/cadastrar" class="sb-item <?php echo $paginaAtual === 'cadastrar' ? 'active' : ''; ?>">
                    <span class="sb-icon"></span> Criar Usuário
                </a>
            </div>
        <?php endif; ?>


        <!-- Projetos -->
        <?php if (defined('URL_BASE')): ?>
            <div id="sb-titulo-projetos" class="sb-section">
                <div class="sb-label">Projetos</div>
                <a href="<?= URL_BASE ?>/projetos" class="sb-item <?php echo $paginaAtual === 'projetos' ? 'active' : ''; ?>">
                    <span class="sb-icon"></span> Listar Projetos
                </a>
                <a href="<?= URL_BASE ?>/projetos/cadastrar" class="sb-item <?php echo $paginaAtual === 'cadastrar' ? 'active' : ''; ?>">
                    <span class="sb-icon"></span> Criar Projeto
                </a>
            </div>
        <?php endif; ?>

        <!-- Historico -->
        <div id="sb-titulo-historico" class="sb-section">
            <div class="sb-label">Histórico</div>
            <a href="<?= URL_BASE ?>/projetos/historico?tab=visualizar" class="sb-item <?php echo verificarAtivo('historico', $paginaAtual, 'tab', 'visualizar'); ?>"> <span class="sb-icon"></span> Visualizar Histórico
            </a>

            <a href="<?= URL_BASE ?>/projetos/historico?tab=exportar" class="sb-item <?php echo verificarAtivo('historico', $paginaAtual, 'tab', 'exportar'); ?>"> <span class="sb-icon"></span> Exportar todos
            </a>
        </div>


        <!-- Saida -->
        <div id="sb-titulo-saida" class="sb-section">
            <div class="sb-label">Saida</div>

            <a href="<?= URL_BASE ?>/projetos/saida?tab=todos" class="sb-item <?php echo verificarAtivo('saida', $paginaAtual, 'tab', 'todos'); ?>"> <span class="sb-icon"></span> Todos os arquivos
            </a>

            <a href="<?= URL_BASE ?>/projetos/saida?tab=mvc" class="sb-item <?php echo verificarAtivo('saida', $paginaAtual, 'tab', 'mvc'); ?>"> <span class="sb-icon"></span> Arquivos MVC
            </a>

            <a href="<?= URL_BASE ?>/projetos/saida?tab=pagemaker" class="sb-item <?php echo verificarAtivo('saida', $paginaAtual, 'tab', 'pagemaker'); ?>"> <span class="sb-icon"></span> Arquivos PageMaker
            </a>

            <a href="<?= URL_BASE ?>/projetos/saida?tab=baixar" class="sb-item <?php echo verificarAtivo('saida', $paginaAtual, 'tab', 'baixar'); ?>"> <span class="sb-icon"></span> Baixar Tudo
            </a>
    </aside>

    <main class="main-content">