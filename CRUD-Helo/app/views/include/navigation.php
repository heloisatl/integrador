<?php
// Identifica automaticamente o nome do arquivo atual (ex: 'home.php', 'mvcCreator.php')
$paginaAtual = basename($_SERVER['PHP_SELF']);
$currentSection = $_GET['section'] ?? '';
$currentTab = $_GET['tab'] ?? '';
$currentStep = $_GET['step'] ?? '';

if ($paginaAtual === 'PageMaker.php' && $currentSection === '') {
    $currentSection = 'cabecalho';
}
if ($paginaAtual === 'historico.php' && $currentTab === '') {
    $currentTab = 'visualizar';
}
if ($paginaAtual === 'saida.php' && $currentTab === '') {
    $currentTab = 'todos';
}
if ($paginaAtual === 'mvcCreator.php' && $currentStep === '') {
    $currentStep = 'configurar';
}

// Função auxiliar para injetar a classe 'active' de forma dinâmica
function verificarAtivo($nomeArquivo, $paginaAtual, $queryKey = null, $queryValue = null)
{
    if ($nomeArquivo !== $paginaAtual) {
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
        <a href="home.php" class="logo-link">
            <span class="logo">⚡</span>
            <h1 class="title"> DevStudio <span class="versao">v2</span></h1>
        </a>
    </div>

    <a href="home.php" class="topbar-item <?php echo verificarAtivo('home.php', $paginaAtual); ?>">
        <span class="sb-icon">🏠</span> Início
    </a>

    <a href="configGlobal.php" class="topbar-item <?php echo verificarAtivo('configGlobal.php', $paginaAtual); ?>">
        <span class="sb-icon">⚙</span> Config. Global
    </a>
    <a href="mvcCreator.php" class="topbar-item <?php echo verificarAtivo('mvcCreator.php', $paginaAtual); ?>">
        <span class="sb-icon">⚡</span> MVC Creator
    </a>
    <a href="pageMaker.php" class="topbar-item <?php echo verificarAtivo('pageMaker.php', $paginaAtual); ?>">
        <span class="sb-icon">🎨</span> Page Maker </a>

    <a href="historico.php" class="topbar-item <?php echo verificarAtivo('historico.php', $paginaAtual); ?>">
        <span class="sb-icon">📁</span> Histórico </a>

    <a href="saida.php" class="topbar-item <?php echo verificarAtivo('saida.php', $paginaAtual); ?>">
        <span class="sb-icon">📃 </span> Saída </a>

    <div class="topbar-actions">
        <button class="trocar-tema" onclick="toggleGlobalTheme()">
            🌓 Alternar Tema
        </button>
    </div>
</header>

<div class="layout-wrapper">

    <aside class="sidebar">
        <!-- o class está sendo usado para padronizar estilização e o id para identificação especifica de quem vai aparecer ou nn na página -->


        <!-- Inicio e Config Global -->
        <div id="sb-principal" class="sb-section">
            <div class="sb-label">Menu Principal</div>

            <a href="home.php" class="sb-item <?php echo verificarAtivo('home.php', $paginaAtual); ?>">
                <span class="sb-icon">🏠</span> Visão Geral
            </a>

            <a href="configGlobal.php" class="sb-item <?php echo verificarAtivo('configGlobal.php', $paginaAtual); ?>">
                <span class="sb-icon">⚙</span> Config. Globais
            </a>

            <a href="guia.php" class="sb-item <?php echo verificarAtivo('guia.php', $paginaAtual); ?>">
                <span class="sb-icon">❓</span> Guia Rápido
            </a>
        </div>

        <!-- MVC Creator -->
        <div id="sb-titulo-MVC" class="sb-section">
            <div class="sb-label">MVC Creator</div>

            <!-- Agora cada item usa a mesma página mvcCreator.php com parâmetro de etapa -->
            <a href="mvcCreator.php?step=configurar" class="sb-item <?php echo verificarAtivo('mvcCreator.php', $paginaAtual, 'step', 'configurar'); ?>"> 1 - Configurar Projeto
            </a>

            <a href="mvcCreator.php?step=tabelas" class="sb-item <?php echo verificarAtivo('mvcCreator.php', $paginaAtual, 'step', 'tabelas'); ?>"> 2 - Tabelas Detectadas
            </a>

            <a href="mvcCreator.php?step=opcoes" class="sb-item <?php echo verificarAtivo('mvcCreator.php', $paginaAtual, 'step', 'opcoes'); ?>"> 3 - Opções de Geração
            </a>

            <a href="mvcCreator.php?step=estrutura" class="sb-item <?php echo verificarAtivo('mvcCreator.php', $paginaAtual, 'step', 'estrutura'); ?>"> 4 - Estrutura de Arquivos
            </a>

            <a href="mvcCreator.php?step=gerar" class="sb-item <?php echo verificarAtivo('mvcCreator.php', $paginaAtual, 'step', 'gerar'); ?>"> 5 - Gerar e Baixar
            </a>
        </div>


        <!-- Page Maker -->
        <div id="sb-titulo-PageMaker" class="sb-section">
            <div class="sb-label">Page Maker</div>

            <a href="PageMaker.php?section=cabecalho" class="sb-item <?php echo verificarAtivo('PageMaker.php', $paginaAtual, 'section', 'cabecalho'); ?>"> <span class="sb-icon">📌</span>Cabeçalho
            </a>

            <a href="PageMaker.php?section=navegacao" class="sb-item <?php echo verificarAtivo('PageMaker.php', $paginaAtual, 'section', 'navegacao'); ?>"> <span class="sb-icon">📎</span> Navegação
            </a>


            <a href="PageMaker.php?section=conteudo" class="sb-item <?php echo verificarAtivo('PageMaker.php', $paginaAtual, 'section', 'conteudo'); ?>"> <span class="sb-icon">📝</span>Conteúdo
            </a>


            <a href="PageMaker.php?section=elementos" class="sb-item <?php echo verificarAtivo('PageMaker.php', $paginaAtual, 'section', 'elementos'); ?>"> <span class="sb-icon">➕</span>Elementos Extras
            </a>

            <a href="PageMaker.php?section=previsa" class="sb-item <?php echo verificarAtivo('PageMaker.php', $paginaAtual, 'section', 'previsa'); ?>"> <span class="sb-icon">👁️</span>Prévia
            </a>

        </div>

        <!-- Historico -->
        <div id="sb-titulo-historico" class="sb-section">
            <div class="sb-label">Histórico</div>
            <a href="historico.php?tab=visualizar" class="sb-item <?php echo verificarAtivo('historico.php', $paginaAtual, 'tab', 'visualizar'); ?>"> <span class="sb-icon">�</span>Visualizar Histórico
            </a>

            <a href="historico.php?tab=exportar" class="sb-item <?php echo verificarAtivo('historico.php', $paginaAtual, 'tab', 'exportar'); ?>"> <span class="sb-icon">⬇️</span>Exportar todos
            </a>
        </div>


        <!-- Saida -->
        <div id="sb-titulo-saida" class="sb-section">
            <div class="sb-label">Saida</div>

            <a href="saida.php?tab=todos" class="sb-item <?php echo verificarAtivo('saida.php', $paginaAtual, 'tab', 'todos'); ?>"> <span class="sb-icon">📝</span>Todos os arquivos
            </a>

            <a href="saida.php?tab=mvc" class="sb-item <?php echo verificarAtivo('saida.php', $paginaAtual, 'tab', 'mvc'); ?>"> <span class="sb-icon">⚡</span>Arquivos MVC
            </a>

            <a href="saida.php?tab=pagemaker" class="sb-item <?php echo verificarAtivo('saida.php', $paginaAtual, 'tab', 'pagemaker'); ?>"> <span class="sb-icon">🎨</span>Arquivos PageMaker
            </a>

            <a href="saida.php?tab=baixar" class="sb-item <?php echo verificarAtivo('saida.php', $paginaAtual, 'tab', 'baixar'); ?>"> <span class="sb-icon">⬇️</span>Baixar Tudo
            </a>
    </aside>

    <main class="main-content">