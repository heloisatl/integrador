<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevStudio</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

    <style>
        * {

            /* Para navegadores baseados no Chromium (Chrome, Edge) */
            ::-webkit-scrollbar {
                width: 5px;
                /* Largura da barra vertical */
                height: 10px;
                /* Altura da barra horizontal */
            }

            ::-webkit-scrollbar-track {
                background: #f1f1f1;
                /* Cor do fundo (trilho) */
                border-radius: 0px;
            }

            ::-webkit-scrollbar-thumb {
                background: #888;
                /* Cor da barra de rolagem */
                border-radius: 0px;
            }

            ::-webkit-scrollbar-thumb:hover {
                background: #555;
                /* Cor da barra ao passar o mouse */
            }

            /* Para o Firefox */
            html {
                scrollbar-width: thin;
                /* Deixa a barra mais fina */
                scrollbar-color: #888 #f1f1f1;
                /* Cor da barra e do fundo */
            }

        }

        :root {
            --bg: #ffffff;
            --surface: #f5f5f5;
            --panel: #ffffff;
            --border: #e0e0e0;
            --accent: #5b6af0;
            --accent-h: #4a59df;
            --text: #1a1a1a;
            --muted: #6b7285;
        }

        :root[data-theme="dark"] {
            --bg: #0d0f14;
            --surface: #13161d;
            --panel: #1a1e28;
            --border: #252a36;
            --accent: #5b6af0;
            --accent-h: #4a59df;
            --text: #e8eaf0;
            --muted: #6b7285;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'DM Sans', sans-serif;
        }

        body {
            background-color: var(--bg);
            color: var(--text);
            overflow: hidden;
        }

        .topbar {
            height: 60px;
            background-color: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 24px;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            gap: 12px;
        }

        .topbar-logo {
            display: flex;
            align-items: center;
            padding-right: 24px;
            gap: 10px;
            flex-shrink: 0;
        }

        .topbar-toggle {
            display: none;
            flex-direction: column;
            justify-content: center;
            gap: 4px;
            width: 42px;
            height: 42px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 8px;
            cursor: pointer;
            margin-left: auto;
        }

        .topbar-toggle span {
            display: block;
            height: 2px;
            background-color: var(--text);
            border-radius: 2px;
        }

        .topbar-nav {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            min-width: 0;
        }

        .topbar-nav .topbar-actions {
            margin-left: auto;
        }

        @media (max-width: 900px) {
            .topbar {
                height: auto;
                min-height: 60px;
                flex-wrap: wrap;
                padding: 12px 16px;
            }

            .topbar-toggle {
                display: flex;
            }

            .topbar-nav {
                display: none;
                width: 100%;
                flex-direction: column;
                align-items: stretch;
                padding-top: 8px;
                gap: 6px;
            }

            .topbar-nav.open {
                display: flex;
            }

            .topbar-item-inicio {
                margin-left: 0;
            }

            .topbar-nav .topbar-actions {
                margin-left: 0;
                justify-content: flex-end;
            }
        }

        @media (max-width: 640px) {
            .topbar {
                padding: 10px 12px;
            }

            .topbar-nav {
                padding-top: 6px;
            }

            .topbar-item,
            .topbar-item-inicio,
            .profile-btn {
                width: 100%;
                justify-content: center;
            }
        }

        /* Especifico pra "Inicio" pra ele ficar mais longe da logo DevStudio */
        .topbar-item-inicio {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            text-decoration: none;
            padding: 10px 12px;
            margin-left: 80px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }

        .topbar-item-inicio:hover {
            background-color: var(--panel);
            color: var(--accent);
        }

        .topbar-item-inicio.active {
            background-color: var(--accent);
            color: #ffffff;
            font-weight: 500;
        }



        .topbar-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;

        }

        .topbar-item:hover {
            background-color: var(--panel);
            color: var(--accent);
        }

        .topbar-item.active {
            background-color: var(--accent);
            color: #ffffff;
            font-weight: 500;
        }

        .title {
            font-family: 'Syne', sans-serif;
            font-size: 18px;
            font-weight: 700;
        }

        .title-link {
            text-decoration: none;
            color: inherit;
        }

        .versao {
            color: var(--text);
            background-color: var(--accent-h);

            border-radius: 4px;
            font-size: 10px;
            padding: 1px 4px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .profile-dropdown-container {
            position: relative;
            display: inline-block;
        }

        .profile-btn {
            background: var(--panel);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 7px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .profile-btn:hover {
            background-color: var(--surface);
            border-color: var(--accent);
            color: var(--accent);
        }

        .profile-dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: calc(100% + 8px);
            background-color: var(--panel);
            border: 1px solid var(--border);
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
            min-width: 200px;
            padding: 8px 0;
            z-index: 1050;
        }

        .profile-dropdown-menu.active {
            display: block;
            animation: profileMenuFadeIn 0.2s ease-out forwards;
        }

        @keyframes profileMenuFadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .profile-dropdown-header {
            padding: 8px 16px 10px 16px;
        }

        .profile-user-name {
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
            word-break: break-word;
        }

        .profile-user-email {
            font-size: 12px;
            color: var(--muted);
            word-break: break-word;
            margin-top: 2px;
        }

        .profile-dropdown-divider {
            height: 1px;
            background-color: var(--border);
            margin: 6px 0;
        }

        .profile-dropdown-item {
            display: block;
            width: 100%;
            padding: 10px 16px;
            background: transparent;
            border: none;
            color: var(--text);
            text-align: left;
            text-decoration: none;
            font-size: 13px;
            cursor: pointer;
            transition: background-color 0.2s ease, color 0.2s ease;
            box-sizing: border-box;
        }

        .profile-dropdown-item:hover {
            background-color: var(--surface);
            color: var(--accent);
        }

        .profile-dropdown-danger {
            color: #f44336;
        }

        .profile-dropdown-danger:hover {
            background-color: rgba(244, 67, 54, 0.1);
            color: #d32f2f;
        }

        .layout-wrapper {
            display: flex;
            margin-top: 60px;
            /* Desconto da altura da Topbar */
            height: calc(100vh - 60px);
        }

        .sidebar {
            width: 15%;
            background-color: var(--surface);
            border-right: 1px solid var(--border);
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            gap: 24px;
            overflow-y: auto;
        }

        .sb-section {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sb-label {
            font-family: 'Syne', sans-serif;
            font-size: 11px;
            text-transform: uppercase;
            color: var(--muted);
            letter-spacing: 1px;
            padding-left: 12px;
            margin-bottom: 6px;
        }

        /* Itens de Menu convertidos para links reais com estilo de botão */
        .sb-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text);
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s ease;
        }



        .sb-item:hover {
            background-color: var(--panel);
            color: var(--accent);
        }

        /* Estado ativo gerenciado dinamicamente pelo PHP */
        .sb-item.active {
            background-color: var(--accent);
            color: #ffffff;
            font-weight: 500;
        }

        .sb-icon {
            font-size: 16px;
        }

        /* Área do Conteúdo da Página */
        /* TODO: deixar responsiiiveeeeeel aaaa */
        .main-content {
            width: 75%;
            flex: 1;
            padding: 32px;
            overflow-y: auto;
            background-color: var(--bg);
        }

        @media (max-width: 900px) {
            .sidebar {
                display: none;
            }

            .main-content {
                width: 100%;
                padding: 20px;
            }
        }

        @media (max-width: 640px) {
            .main-content {
                padding: 16px;
            }
        }
    </style>

    <?php
    // Link global para estilos públicos (migrados para public/assets/css)
    $hrefGlobalStyle = defined('URL_BASE') ? (URL_BASE . '/assets/css/style.css') : '/assets/css/style.css';
    echo '<link rel="stylesheet" href="' . $hrefGlobalStyle . '">';
    ?>

    <script>
        // Função para manter o alternador de temas ativo no ecossistema modular
        function toggleGlobalTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

            //salva o tema selecionado no cache do navegador
            html.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);

        }

        (function() {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                document.documentElement.setAttribute('data-theme', savedTheme);
            }
        })();

        function toggleProfileMenu(event) {
            if (event) event.stopPropagation();
            const menu = document.getElementById('profileDropdownMenu');
            if (menu) {
                menu.classList.toggle('active');
            }
        }

        function toggleTopbarNav(event) {
            if (event) event.stopPropagation();
            const nav = document.getElementById('topbarNav');
            const toggle = document.getElementById('topbarToggle');
            if (!nav || !toggle) return;

            const isOpen = nav.classList.toggle('open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', isOpen ? 'Fechar menu' : 'Abrir menu');
        }

        document.addEventListener('click', function(e) {
            const container = document.querySelector('.profile-dropdown-container');
            const menu = document.getElementById('profileDropdownMenu');
            if (container && menu && !container.contains(e.target)) {
                menu.classList.remove('active');
            }

            const toggle = document.getElementById('topbarToggle');
            const nav = document.getElementById('topbarNav');
            if (toggle && nav && !toggle.contains(e.target) && !nav.contains(e.target)) {
                nav.classList.remove('open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', 'Abrir menu');
            }
        });
    </script>
    <?php
    // Incluir link para stylesheet pública da seção `usuarios`
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($requestUri, '/usuarios') !== false) {
        $href = defined('URL_BASE') ? (URL_BASE . '/assets/css/usuarios.css') : '/assets/css/usuarios.css';
        echo '<link rel="stylesheet" href="' . $href . '">';
    }
    ?>
</head>

<?php
// Definir um id no <body> de acordo com a rota atual para permitir seletores do tipo
// #<pageId> #sb-titulo-... funcionarem mesmo quando a view não envolver todo o layout.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$bodyId = trim(basename(rtrim($requestPath, '/')), '/');
if ($bodyId === '' || $bodyId === 'index.php') {
    $bodyId = 'projetos';
}
echo '<body id="' . htmlspecialchars($bodyId, ENT_QUOTES) . '">';
?>