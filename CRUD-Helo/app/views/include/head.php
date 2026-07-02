<!DOCTYPE html>
<html lang="pt-BR" data-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevStudio</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@600;700&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">

    <style>
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
        }

        .topbar-logo {
            display: flex;
            align-items: center;
            padding-right: 24px;
            gap: 10px;
        }

        .logo-link{
            display: flex;
            align-items: center;
            text-decoration: none;
            color: var(--text);
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

        .versao {
            color: var(--text);
            background-color: var(--accent-h);

            border-radius: 4px;
            font-size: 10px;
            padding: 1px 4px;
        }

        .trocar-tema {
            background: var(--panel);
            border: 1px solid var(--border);
            color: var(--text);
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
        }

        .layout-wrapper {
            display: flex;
            margin-top: 60px;
            /* Desconto da altura da Topbar */
            height: calc(100vh - 60px);
        }

        .sidebar {
            width: 260px;
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
        .main-content {
            flex: 1;
            padding: 32px;
            overflow-y: auto;
            background-color: var(--bg);
        }

        
    </style>

    <script>
        // Função para manter o alternador de temas ativo no ecossistema modular
        function toggleGlobalTheme() {
            const html = document.documentElement;
            const currentTheme = html.getAttribute('data-theme');
            html.setAttribute('data-theme', currentTheme === 'dark' ? 'light' : 'dark');
        }
    </script>
</head>

<body></body>