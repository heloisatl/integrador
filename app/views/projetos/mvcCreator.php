<?php
include_once(__DIR__ . "/../include/head.php");
include_once(__DIR__ . "/../include/navigation.php");
?>

    <link rel="stylesheet" href="<?= URL_BASE_CSS ?>/pagina-mvc.css">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />

    <?php
    $step = $_GET['step'] ?? 'configurar';

    $steps = [
        'configurar' => [
            'title' => 'Configurar Projeto',
            'number' => '1',
            'file' => __DIR__ . '/mvcCreator/configurar.php',
        ],
        'tabelas' => [
            'title' => 'Tabelas Detectadas',
            'number' => '2',
            'file' => __DIR__ . '/mvcCreator/tabelas.php',
        ],
        'opcoes' => [
            'title' => 'Opções de Geração',
            'number' => '3',
            'file' => __DIR__ . '/mvcCreator/opcoes.php',
        ],
        'estrutura' => [
            'title' => 'Estrutura de Arquivos que Será Criada',
            'number' => '4',
            'tag' => 'VISUALIZAÇÃO',
            'file' => __DIR__ . '/mvcCreator/estrutura.php',
        ],
        'gerar' => [
            'title' => 'Gerar Sistema',
            'number' => '5',
            'file' => __DIR__ . '/mvcCreator/gerar.php',
        ],
    ];

    if (!isset($steps[$step])) {
        $step = 'configurar';
    }
    $modoVisitante = isset($modoVisitante) ? (bool) $modoVisitante : empty($_SESSION['usuario_logado']);
    ?>

    <div class="mvc-etapa-wrapper" id="mvcCreatorWrapper" data-visitante="<?= $modoVisitante ? '1' : '0' ?>">
        <header class="page-header mvc-page-header">
            <div>
                <h1 class="page-title">
                    <i class="bi bi-braces"></i> MVC Creator
                </h1>
            </div>
            <?php if ($modoVisitante): ?>
                <div class="page-actions">
                    <span class="mvc-etapa-selo-novo" style="background: rgba(91, 106, 240, 0.18); color: var(--accent); border: 1px solid rgba(91, 106, 240, 0.35);">MODO VISITANTE</span>
                </div>
            <?php endif; ?>
        </header>

        <div class="mvc-etapa-cabecalho">
            <span class="mvc-etapa-selo"><?= htmlspecialchars($steps[$step]['number']) ?></span>
            <h2><?= htmlspecialchars($steps[$step]['title']) ?></h2>
            <?php if (!empty($steps[$step]['tag'])): ?>
                <span class="mvc-etapa-selo-novo"><?= htmlspecialchars($steps[$step]['tag']) ?></span>
            <?php endif; ?>
        </div>

        <div id="alerta-erro" class="alerta alerta-erro oculto" role="alert">
            <span id="alerta-mensagem"></span>
            <button type="button" class="alerta-fechar" onclick="fecharAlerta()">&times;</button>
        </div>

        <?php require $steps[$step]['file']; ?>
    </div>

    <?php require_once __DIR__ . '/../include/footer.php'; ?>
    </main>
    <script>
        window.MODO_VISITANTE = <?= $modoVisitante ? 'true' : 'false' ?>;
    </script>
    <script src="<?= URL_BASE ?>/assets/js/arvoreArquivos.js"></script>
    <script src="<?= URL_BASE ?>/assets/js/mvcLoad.js"></script>
    <!-- <script src="<?= URL_BASE ?>/assets/js/desabilitado.js"></script> -->
</div>
</body>

</html>